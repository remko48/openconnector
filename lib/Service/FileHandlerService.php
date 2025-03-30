<?php

/**
 * Service for handling file operations during synchronization.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Initial creation of the FileHandlerService class
 */

namespace OCA\OpenConnector\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use OCA\OpenConnector\Db\CallLog;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Service\StorageService;
use OCP\Files\File;
use OCP\Files\GenericFileException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\SystemTag\TagNotFoundException;
use OC\User\NoUserException;
use OCP\Files\LockedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Service for handling file operations during synchronization.
 *
 * This class provides methods for fetching, writing, and tagging files.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Initial creation of the FileHandlerService class
 */
class FileHandlerService
{
    /**
     * Type constant for file tagging
     */
    private const FILE_TAG_TYPE = 'files';

    /**
     * The call service instance.
     *
     * @var CallService
     */
    private readonly CallService $callService;

    /**
     * The storage service instance.
     *
     * @var StorageService
     */
    private readonly StorageService $storageService;

    /**
     * The container interface.
     *
     * @var ContainerInterface
     */
    private readonly ContainerInterface $containerInterface;

    /**
     * The system tag manager instance.
     *
     * @var ISystemTagManager
     */
    private readonly ISystemTagManager $systemTagManager;

    /**
     * The system tag object mapper instance.
     *
     * @var ISystemTagObjectMapper
     */
    private readonly ISystemTagObjectMapper $systemTagMapper;


    /**
     * Constructor.
     *
     * @param CallService            $callService        The service for making HTTP calls
     * @param StorageService         $storageService     The storage service
     * @param ContainerInterface     $containerInterface The container interface
     * @param ISystemTagManager      $systemTagManager   The system tag manager
     * @param ISystemTagObjectMapper $systemTagMapper    The system tag object mapper
     */
    public function __construct(
        CallService $callService,
        StorageService $storageService,
        ContainerInterface $containerInterface,
        ISystemTagManager $systemTagManager,
        ISystemTagObjectMapper $systemTagMapper
    ) {
        $this->callService        = $callService;
        $this->storageService     = $storageService;
        $this->containerInterface = $containerInterface;
        $this->systemTagManager   = $systemTagManager;
        $this->systemTagMapper    = $systemTagMapper;

    }//end __construct()


    /**
     * Write a file to the filesystem.
     *
     * @param string $fileName The filename
     * @param string $content  The file content
     * @param string $objectId The object ID
     *
     * @return File|bool The created file or false on failure
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GenericFileException
     * @throws LockedException
     * @throws Exception
     */
    public function writeFile(
        string $fileName,
        string $content,
        string $objectId
    ): mixed {
        $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
        $object        = $objectService->getOpenRegisters()
            ->getMapper('objectEntity')
            ->find($objectId);

        try {
            $file = $this->storageService->writeFile(
                path: $object->getFolder(),
                fileName: $fileName,
                content: $content
            );
        } catch (NotFoundException | NotPermittedException | NoUserException $e) {
            return false;
        }

        return $file;

    }//end writeFile()


    /**
     * Fetch a file from a source.
     *
     * @param Source              $source   The source configuration
     * @param string              $endpoint The file endpoint
     * @param array<string,mixed> $config   The action configuration
     * @param string              $objectId The object ID
     * @param array<string>|null  $tags     Optional tags to assign
     * @param string|null         $filename Optional filename
     *
     * @return string The file URL or base64 content
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GenericFileException
     * @throws LockedException
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     * @throws Exception
     */
    public function fetchFile(
        Source $source,
        string $endpoint,
        array $config,
        string $objectId,
        ?array $tags=[],
        ?string $filename=null
    ): string {
        $originalEndpoint = $endpoint;

        // Clean endpoint if it contains source location
        $endpoint = str_contains($endpoint, $source->getLocation()) ? substr($endpoint, strlen($source->getLocation())) : $endpoint;

        // Make API call
        $result   = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            method: ($config['method'] ?? 'GET'),
            config: ($config['sourceConfiguration'] ?? [])
        );
        $response = $result->getResponse();

        // Return base64 content if write is disabled
        if (isset($config['write']) && $config['write'] === false) {
            return base64_encode($response['body']);
        }

        // Get filename from response if not provided
        if ($filename === null) {
            $filename = $this->getFilenameFromHeaders(
                response: $response,
                result: $result
            );
        }

        // Write file using ObjectService
        $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
        $file          = $objectService->addFile(
            object: $objectId,
            fileName: $filename,
            base64Content: $response['body'],
            share: isset($config['autoShare']) ? $config['autoShare'] : false
        );

        // Attach tags to file
        $tags[] = "object:$objectId";
        if ($file instanceof File && count($tags) > 0) {
            $this->attachTagsToFile(fileId: $file->getId(), tags: $tags);
        }

        return $originalEndpoint;

    }//end fetchFile()


    /**
     * Extract filename from response headers.
     *
     * @param array<string,mixed> $response The response data
     * @param CallLog             $result   The call log
     *
     * @return string|null The extracted filename
     */
    public function getFilenameFromHeaders(array $response, CallLog $result): ?string
    {
        // Try Content-Disposition header first
        if (isset($response['headers']['Content-Disposition'])
            && str_contains($response['headers']['Content-Disposition'][0], 'filename')
        ) {
            $explodedContentDisposition = explode(
                '=',
                $response['headers']['Content-Disposition'][0]
            );
            return trim($explodedContentDisposition[1], '"');
        }

        // Fall back to URL path and content type
        $parsedUrl = parse_url($result->getRequest()['url']);
        $path      = explode('/', $parsedUrl['path']);
        $filename  = end($path);

        // Add extension from content type if missing
        if (count(explode('.', $filename)) === 1
            && (isset($response['headers']['Content-Type'])
            || isset($response['headers']['content-type']))
        ) {
            $contentType = isset($response['headers']['Content-Type']) ? $response['headers']['Content-Type'][0] : $response['headers']['content-type'][0];

            $explodedMimeType = explode(
                '/',
                explode(';', $contentType)[0]
            );

            $filename = $filename.'.'.end($explodedMimeType);
        }

        return $filename;

    }//end getFilenameFromHeaders()


    /**
     * Attach tags to a file.
     *
     * @param string        $fileId The file ID
     * @param array<string> $tags   The tags to attach
     *
     * @return void
     *
     * @throws TagNotFoundException When a tag is not found and cannot be created
     */
    public function attachTagsToFile(string $fileId, array $tags): void
    {
        $tagIds = [];
        foreach ($tags as $tagName) {
            try {
                $tag = $this->systemTagManager->getTag(
                    tagName: $tagName,
                    userVisible: true,
                    userAssignable: true
                );
            } catch (TagNotFoundException $exception) {
                $tag = $this->systemTagManager->createTag(
                    tagName: $tagName,
                    userVisible: true,
                    userAssignable: true
                );
            }

            $tagIds[] = $tag->getId();
        }

        $this->systemTagMapper->assignTags(
            objId: $fileId,
            objectType: self::FILE_TAG_TYPE,
            tagIds: $tagIds
        );

    }//end attachTagsToFile()


}//end class

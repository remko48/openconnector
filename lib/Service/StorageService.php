<?php

namespace OCA\OpenConnector\Service;

use Exception;
use OC\Files\Node\Node;
use OC\Files\ObjectStore\ObjectStoreStorage;
use OC\Memcache\Memcached;
use OC\Memcache\Redis;
use OCA\DAV\Upload\UploadFolder;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\GenericFileException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\ObjectStore\IObjectStoreMultiPartUpload;
use OCP\Files\Storage\IChunkedFileWrite;
use OCP\Files\StorageInvalidException;
use OCP\IAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Lock\LockedException;
use Symfony\Component\Uid\Uuid;

class StorageService
{
	private ICache $cache;
	private \OCP\Files\Node $uploadFolder;

	private const TEMP_TARGET = '.target';
	public const CACHE_KEY = 'openconnector-upload';
	public const UPLOAD_TARGET_PATH = 'upload-target-path';
	public const UPLOAD_TARGET_ID = 'upload-target-id';

    public const NUMBER_OF_PARTS = 'number-of-parts';
	public const UPLOAD_ID = 'upload-id';


	public function __construct(
		private readonly IRootFolder $rootFolder,
		private readonly IAppConfig $config,
		ICacheFactory $cacheFactory,
        private readonly IUserSession $userSession,
	){
		$this->cache = $cacheFactory->createDistributed(self::CACHE_KEY);
	}

	private function checkPreconditions(int $size, bool $checkMetadata = false): bool
	{
		if($size < $this->config->getValueInt('openconnector', 'upload-size', 2000000)) {
			return false;
		}

		if($this->cache instanceof Redis === false && $this->cache instanceof Memcached === false) {
			return false;
		}
		if($this->uploadFolder->getStorage()->instanceOfStorage(IChunkedFileWrite::class) === false) {
			return false;
		}
		if($this->uploadFolder->getStorage()->instanceOfStorage(ObjectStoreStorage::class) === false
			&& $this->uploadFolder->getStorage()->getObjectStore() instanceof IObjectStoreMultiPartUpload === false
		) {
			return false;
		}
		if ($checkMetadata === true && ($this->uploadId === null || $this->uploadPath === null)) {
			return false;
		}

		return true;
	}

	private function getFile(string $path, bool $createIfNotExists = false): File
	{
		try{
			$file = $this->rootFolder->get($path);
			if($file instanceof File && $this->uploadFolder->getStorage()->getId() === $file->getStorage()->getId()) {
				return $file;
			}
		} catch (NotFoundException $e) {

		}

		if ($createIfNotExists === true
			&& $this->uploadFolder instanceof Folder === true
		) {
			$file = $this->uploadFolder->newFile(self::TEMP_TARGET);
		}

		return $file;
	}

	/**
	 * @return array [IStorage, string]
	 */
	private function getUploadStorage(string $targetPath): array {
		$storage = $this->uploadFolder->getStorage();
		$targetFile = $this->getFile(path: $targetPath);
		return [$storage, $targetFile->getInternalPath()];
	}

    /**
     * Based upon the webDAV partial files plugin
     *
     * @param string $path
     * @param string $fileName
     * @param int $size
     * @return array
     * @throws NotFoundException
     * @throws \OCP\Files\InvalidPathException
     */
	public function createUpload(string $path, string $fileName, int $size): array
	{
        $currentUser = $this->userSession->getUser();
        $userFolder = $this->rootFolder->getUserFolder(userId: $currentUser ? $currentUser->getUID() : 'Guest');

        $uploadFolder = $userFolder->get($path);

        $partSize = $this->config->getValueInt('openconnector', 'part-size', 1000000);

        $numParts = ceil($size / $partSize);

        $remainingSize = $size;
        $parts = [];


//		if ($this->checkPreconditions($size) === false && $uploadFolder instanceof Folder === true)
//        {
            $target = $uploadFolder->newFile($fileName);

            $escapedFilename = str_replace(search: '.', replace: '_', subject: $fileName);
            $partsFolder = $uploadFolder->newFolder("{$escapedFilename}_parts");


            for ($i = 0; $i < $numParts; $i++) {
                $partNumber = $i + 1;
                $partUuid   = Uuid::v4();

                $this->cache->set("upload_$partUuid", [
                    self::UPLOAD_TARGET_ID => $target->getId(),
                    self::UPLOAD_TARGET_PATH => $partsFolder->getPath(),
                    self::NUMBER_OF_PARTS => $numParts,
                ]);

                $parts[] = [
                    'id'    => $partUuid,
                    'size'  => $partSize < $remainingSize ? $partSize : $remainingSize,
                    'order' => $partNumber,
                ];
                $remainingSize -= $partSize;
            }

			return $parts;
//		}

        //@TODO: use only the part in the if statement if that works until we have the opportunity to test the code below.
//
//		$this->uploadPath = $path.$fileName;
//
//		$targetFile = $this->getFile(path: $path.'/'.$fileName, createIfNotExists: true);
//		[$storage, $storagePath] = $this->getUploadStorage($this->uploadPath);
//		$this->uploadId = $storage->startChunkedWrite($storagePath);
//
//		$this->cache->set($this->uploadFolder->getName(), [
//			self::UPLOAD_ID => $this->uploadId,
//			self::UPLOAD_TARGET_PATH => $this->uploadPath,
//			self::UPLOAD_TARGET_ID => $targetFile->getId(),
//		], 86400);
//
//
//        for ($i = 0; $i < $numParts; $i++) {
//            $parts[] = [
//                'size'  => $partSize < $remainingSize ? $partSize : $remainingSize,
//                'order' => $i+1,
//            ];
//            $remainingSize -= $partSize;
//        }
//
//        return $parts;
	}

    public function writeFile(string $path, string $fileName, string $content): File
    {
        $currentUser = $this->userSession->getUser();
        $userFolder = $this->rootFolder->getUserFolder(userId: $currentUser ? $currentUser->getUID() : 'Guest');

        $uploadFolder = $userFolder->get($path);

        try{
            $target = $uploadFolder->get($fileName);
        } catch (NotFoundException $e) {
            $target = $uploadFolder->newFile($fileName, $content);
        }

        return $target;
    }

    /**
     * @param Node[] $folderContents
     * @param File $target
     * @return bool
     */
    private function attemptCloseUpload (array $folderContents, File $target, int $numParts): bool
    {
        $contentFilenames = array_map(function ($node) {
            return $node->getName();
        }, $folderContents);

        $folder = $folderContents[0]->getParent();

        $files = array_combine($contentFilenames, $folderContents);
        ksort($files);

        $contentFilenames = array_filter($contentFilenames,
            function($string) use ($target) {
                $result = preg_match("#^[0-9]+\.part\.{$target->getExtension()}$#", $string);
                return $result !== false && $result > 0;
            }
        );
        asort($contentFilenames);
        $sortedFilenames = array_values($contentFilenames);

        $contentFilenamesWithoutExtensions = array_map(function($filename) use ($target) {
            return intval(str_replace(search: ".part.{$target->getExtension()}", replace: '', subject: $filename));

        }, $contentFilenames);

        if($contentFilenamesWithoutExtensions !== range(start: 1, end: $numParts)) {
            return false;
        }

        $totalContent = '';
        foreach($files as $filename => $filePart) {
            if($filePart instanceof File === false) {
                continue;
            }
            $totalContent .= $filePart->getContent();


            $filePart->delete();
        }

        if ($folder->getDirectoryListing() === []) {
            $folder->delete();
        }

        $target->putContent($totalContent);

        return true;
    }

    /**
     * Based upon the webDAV partial files plugin
     *
     * @param int $partId
     * @param string $data
     * @param string $path
     * @param $numParts
     * @return bool
     * @throws NotFoundException
     * @throws \OCP\Files\InvalidPathException
     */
	public function writePart(int $partId, string $partUuid, string $data): bool
	{
        $partData = $this->cache->get("upload_$partUuid");

        $targetFile  = $this->rootFolder->getById($partData[self::UPLOAD_TARGET_ID])[0];
        $partsFolder = $this->rootFolder->get($partData[self::UPLOAD_TARGET_PATH]);
        $numParts    = $partData[self::NUMBER_OF_PARTS];

        if($partsFolder instanceof Folder === false) {
            throw new NotFoundException('target folder is not a folder');
        }

        if($targetFile instanceof  File === false) {
            throw new NotFoundException('target file is not a file');
        }
        $partsFolder->newFile("$partId.part.{$targetFile->getExtension()}", $data);

        $this->rootFolder->get($partsFolder->getPath());
        $folderContents = $partsFolder->getDirectoryListing();



        if(count($folderContents) >= $numParts) {
            $this->attemptCloseUpload($folderContents, $targetFile, $numParts);
        }


        // @TODO: Code below only works in certain setups, should be tested when the opportunity arises.
//		try {
//			$this->uploadFolder = $this->rootFolder->get($path);
//
//			$uploadMetadata   = $this->cache->get($this->uploadFolder->getName());
//			$this->uploadId   = $uploadMetadata[self::UPLOAD_ID] ?? null;
//			$this->uploadPath = $uploadMetadata[self::UPLOAD_TARGET_PATH] ?? null;
//
//			[$storage, $storagePath] = $this->getUploadStorage($this->uploadPath);
//
//			$file = $this->getFile(path: $this->uploadPath);
//			if($this->uploadFolder instanceof Folder) {
//				$tempTarget = $this->uploadFolder->get(self::TEMP_TARGET);
//			}
//
//			$storage->putChunkedWritePart($storagePath, $this->uploadId, (string)$partId, $data, strlen($data));
//
//			$storage->getCache()->update($file->getId(), ['size' => $file->getSize() + strlen($data)]);
//			if ($tempTarget) {
//				$storage->getPropagator()->propagateChange($tempTarget->getInternalPath(), time(), strlen($data));
//			}
//
//			if(count($storage->getObjectStore()->getMultipartUploads($storage->getUrn($file->getId()), $this->uploadId)) === $numParts) {
//				$storage->completeChunkedWrite($path, $this->uploadId);
//			}
//		} catch(Exception $e) {
//			throw $e;
//		}

		return true;
	}
}

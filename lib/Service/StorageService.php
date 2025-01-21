<?php

namespace OCA\OpenConnector\Service;

use Exception;
use OC\Files\ObjectStore\ObjectStoreStorage;
use OC\Memcache\Memcached;
use OC\Memcache\Redis;
use OCA\DAV\Upload\UploadFolder;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\ObjectStore\IObjectStoreMultiPartUpload;
use OCP\Files\Storage\IChunkedFileWrite;
use OCP\Files\StorageInvalidException;
use OCP\IAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;

class StorageService
{
	private ICache $cache;
	private \OCP\Files\Node $uploadFolder;

	private const TEMP_TARGET = '.target';
	public const CACHE_KEY = 'openconnector-upload';
	public const UPLOAD_TARGET_PATH = 'upload-target-path';
	public const UPLOAD_TARGET_ID = 'upload-target-id';
	public const UPLOAD_ID = 'upload-id';


	public function __construct(
		private readonly IRootFolder $rootFolder,
		private readonly IAppConfig $config,
		ICacheFactory $cacheFactory
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
		$this->uploadFolder = $this->rootFolder->get($path);

		if ($this->checkPreconditions($size) === false
		) {
			return [];
		}
		$this->uploadPath = $path.$fileName;

		$targetFile = $this->getFile(path: $path.'/'.$fileName, createIfNotExists: true);
		[$storage, $storagePath] = $this->getUploadStorage($this->uploadPath);
		$this->uploadId = $storage->startChunkedWrite($storagePath);

		$this->cache->set($this->uploadFolder->getName(), [
			self::UPLOAD_ID => $this->uploadId,
			self::UPLOAD_TARGET_PATH => $this->uploadPath,
			self::UPLOAD_TARGET_ID => $targetFile->getId(),
		], 86400);

        $partSize = $this->config->getValueInt('openconnector', 'part-size', 500000);

		$numParts = ceil($size / $partSize);

        $remainingSize = $size;
        $parts = [];

        for ($i = 0; $i < $numParts; $i++) {
            $parts[] = [
                'size'  => $partSize < $remainingSize ? $partSize : $remainingSize,
                'order' => $i+1,
            ];
            $remainingSize -= $partSize;
        }

        return $parts;
	}

    public function writeFile(string $path, string $fileName, string $content): File
    {
        $uploadFolder = $this->rootFolder->get($path);

        $target = $this->getFile(path: $path.'/'.$fileName, createIfNotExists: true);
        $target->putContent($content);

        return $target;
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
	public function writePart(int $partId, string $data, string $path, $numParts): bool
	{
		try {
			$this->uploadFolder = $this->rootFolder->get($path);

			$uploadMetadata   = $this->cache->get($this->uploadFolder->getName());
			$this->uploadId   = $uploadMetadata[self::UPLOAD_ID] ?? null;
			$this->uploadPath = $uploadMetadata[self::UPLOAD_TARGET_PATH] ?? null;

			[$storage, $storagePath] = $this->getUploadStorage($this->uploadPath);

			$file = $this->getFile(path: $this->uploadPath);
			if($this->uploadFolder instanceof Folder) {
				$tempTarget = $this->uploadFolder->get(self::TEMP_TARGET);
			}

			$storage->putChunkedWritePart($storagePath, $this->uploadId, (string)$partId, $data, strlen($data));

			$storage->getCache()->update($file->getId(), ['size' => $file->getSize() + strlen($data)]);
			if ($tempTarget) {
				$storage->getPropagator()->propagateChange($tempTarget->getInternalPath(), time(), strlen($data));
			}

			if(count($storage->getObjectStore()->getMultipartUploads($storage->getUrn($file->getId()), $this->uploadId)) === $numParts) {
				$storage->completeChunkedWrite($path, $this->uploadId);
			}
		} catch(Exception $e) {
			throw $e;
		}

		return true;
	}
}

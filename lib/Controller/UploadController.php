<?php

namespace OCA\OpenConnector\Controller;

use OCA\OpenConnector\Service\UploadService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class UploadController extends Controller
{
	/**
	 * Constructor for the UploadController
	 *
	 * @param string $appName The name of the app
	 * @param IRequest $request The request object
	 * @param IAppConfig $config The app configuration object
	 * @param UploadService $uploadService The Upload Service.
	 */
    public function __construct(
        $appName,
        IRequest $request,
        private IAppConfig $config,
		private readonly UploadService $uploadService
    )
    {
        parent::__construct($appName, $request);
    }

	/**
	 * Creates a new object or updates an existing one using a json text/string as input.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function upload(): JSONResponse
    {
		return $this->uploadService->upload($this->request->getParams());
    }
}

<?php

namespace OCA\OpenConnector\Controller;

use OCA\OpenConnector\Service\DownloadService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class DownloadController extends Controller
{
	/**
	 * Constructor for the DownloadController
	 *
	 * @param string $appName The name of the app
	 * @param IRequest $request The request object
	 * @param IAppConfig $config The app configuration object
	 * @param DownloadService $downloadService The Download Service.
	 */
    public function __construct(
        $appName,
        IRequest $request,
        private IAppConfig $config,
		private readonly DownloadService $downloadService
    )
    {
        parent::__construct($appName, $request);
    }

	/**
	 * Creates and return a json file for a specific object.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $type The object type we want to download an object for.
	 * @param string $id The id used to find an existing object to download.
	 *
	 * @return JSONResponse
	 */
	public function download(string $type, string $id): JSONResponse
    {
		$accept = $this->request->getHeader('Accept');

		if (empty($accept) === true) {
			return new JSONResponse(data: ['error' => 'Request is missing header Accept'], statusCode: 400);
		}

		return $this->downloadService->download(objectType: $type, id: $id, accept: $accept);
    }
}

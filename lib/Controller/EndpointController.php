<?php

namespace OCA\OpenConnector\Controller;

use OCA\OpenConnector\Service\ObjectService;
use OCA\OpenConnector\Service\SearchService;
use OCA\OpenConnector\Db\Endpoint;
use OCA\OpenConnector\Db\EndpointMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCA\OpenConnector\Service\EndpointService;

/**
 * Controller for managing endpoints in the OpenConnector app
 */
class EndpointController extends Controller
{
    /**
     * Constructor for the EndpointController
     *
     * @param string $appName The name of the app
     * @param IRequest $request The request object
     * @param IAppConfig $config The app configuration object
     * @param EndpointMapper $endpointMapper Mapper for endpoint database operations
     * @param EndpointService $endpointService Service for endpoint business logic
     */
    public function __construct(
        $appName,
        IRequest $request,
        private IAppConfig $config,
        private EndpointMapper $endpointMapper,
        private EndpointService $endpointService,
    )
    {
        parent::__construct($appName, $request);
    }

    /**
     * Returns the template of the main app's page
     *
     * This method renders the main page of the application, adding any necessary data to the template.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse The rendered template response
     */
    public function page(): TemplateResponse
    {
        return new TemplateResponse(
            'openconnector',
            'index',
            []
        );
    }

    /**
     * Retrieves a list of all endpoints
     *
     * This method returns a JSON response containing an array of all endpoints in the system.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the list of endpoints
     */
    public function index(ObjectService $objectService, SearchService $searchService): JSONResponse
    {
        $filters = $this->request->getParams();
        $fieldsToSearch = ['name', 'description', 'url'];

        $searchParams = $searchService->createMySQLSearchParams(filters: $filters);
        $searchConditions = $searchService->createMySQLSearchConditions(filters: $filters, fieldsToSearch: $fieldsToSearch);
        $filters = $searchService->unsetSpecialQueryParams(filters: $filters);

        return new JSONResponse(['results' => $this->endpointMapper->findAll(limit: null, offset: null, filters: $filters, searchConditions: $searchConditions, searchParams: $searchParams)]);
    }

    /**
     * Retrieves a single endpoint by its ID
     *
     * This method returns a JSON response containing the details of a specific endpoint.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the endpoint to retrieve
     * @return JSONResponse A JSON response containing the endpoint details
     */
    public function show(string $id): JSONResponse
    {
        try {
            return new JSONResponse($this->endpointMapper->find(id: (int) $id));
        } catch (DoesNotExistException $exception) {
            return new JSONResponse(data: ['error' => 'Not Found'], statusCode: 404);
        }
    }

    /**
     * Creates a new endpoint
     *
     * This method creates a new endpoint based on POST data.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the created endpoint
     */
    public function create(): JSONResponse
    {
        $data = $this->request->getParams();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_')) {
                unset($data[$key]);
            }
        }

        if (isset($data['id'])) {
            unset($data['id']);
        }

        // Create the endpoint
        $endpoint = $this->endpointMapper->createFromArray(object: $data);
        
        return new JSONResponse($endpoint);
    }

    /**
     * Updates an existing endpoint
     *
     * This method updates an existing endpoint based on its ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the endpoint to update
     * @return JSONResponse A JSON response containing the updated endpoint details
     */
    public function update(int $id): JSONResponse
    {
        $data = $this->request->getParams();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_')) {
                unset($data[$key]);
            }
        }
        if (isset($data['id'])) {
            unset($data['id']);
        }

        // Update the endpoint
        $endpoint = $this->endpointMapper->updateFromArray(id: (int) $id, object: $data);

        return new JSONResponse($endpoint);
    }

    /**
     * Deletes an endpoint
     *
     * This method deletes an endpoint based on its ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the endpoint to delete
     * @return JSONResponse An empty JSON response
     */
    public function destroy(int $id): JSONResponse
    {
        $this->endpointMapper->delete($this->endpointMapper->find((int) $id));

        return new JSONResponse([]);
    }
}

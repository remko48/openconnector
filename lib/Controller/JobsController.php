<?php

namespace OCA\OpenConnector\Controller;

use OCA\OpenConnector\Service\ObjectService;
use OCA\OpenConnector\Service\SearchService;
use OCA\OpenConnector\Db\Job;
use OCA\OpenConnector\Db\JobMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class JobsController extends Controller
{
    /**
     * Constructor for the JobController
     *
     * @param string $appName The name of the app
     * @param IRequest $request The request object
     * @param IAppConfig $config The app configuration object
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config,
        private readonly JobMapper $jobMapper
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
     * Retrieves a list of all jobs
     * 
     * This method returns a JSON response containing an array of all jobs in the system.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the list of jobs
     */
    public function index(ObjectService $objectService, SearchService $searchService): JSONResponse
    {
        $filters = $this->request->getParams();
        $fieldsToSearch = ['name', 'description'];

        $searchParams = $searchService->createMySQLSearchParams(filters: $filters);
        $searchConditions = $searchService->createMySQLSearchConditions(filters: $filters, fieldsToSearch:  $fieldsToSearch);
        $filters = $searchService->unsetSpecialQueryParams(filters: $filters);

        return new JSONResponse(['results' => $this->jobMapper->findAll(limit: null, offset: null, filters: $filters, searchConditions: $searchConditions, searchParams: $searchParams)]);
    }

    /**
     * Retrieves a single job by its ID
     * 
     * This method returns a JSON response containing the details of a specific job.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the job to retrieve
     * @return JSONResponse A JSON response containing the job details
     */
    public function show(string $id): JSONResponse
    {
        try {
            return new JSONResponse($this->jobMapper->find(id: (int) $id));
        } catch (DoesNotExistException $exception) {
            return new JSONResponse(data: ['error' => 'Not Found'], statusCode: 404);
        }
    }

    /**
     * Creates a new job
     * 
     * This method creates a new job based on POST data.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the created job
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
        
        return new JSONResponse($this->jobMapper->createFromArray(object: $data));
    }

    /**
     * Updates an existing job
     * 
     * This method updates an existing job based on its ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the job to update
     * @return JSONResponse A JSON response containing the updated job details
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
        return new JSONResponse($this->jobMapper->updateFromArray(id: (int) $id, object: $data));
    }

    /**
     * Deletes a job
     * 
     * This method deletes a job based on its ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $id The ID of the job to delete
     * @return JSONResponse An empty JSON response
     */
    public function destroy(int $id): JSONResponse
    {
        $this->jobMapper->delete($this->jobMapper->find((int) $id));

        return new JSONResponse([]);
    }
}
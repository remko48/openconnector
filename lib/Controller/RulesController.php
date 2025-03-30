<?php
/**
 * OpenConnector Rules Controller
 *
 * This file contains the controller for handling rule related operations
 * in the OpenConnector application.
 *
 * @category  Controller
 * @package   OpenConnector
 * @author    NextCloud Development Team <dev@nextcloud.com>
 * @copyright 2023 NextCloud GmbH
 * @license   AGPL-3.0 https://www.gnu.org/licenses/agpl-3.0.en.html
 * @version   GIT: <git-id>
 * @link      https://nextcloud.com
 */

namespace OCA\OpenConnector\Controller;

use Exception;
use OCA\OpenConnector\Service\ObjectService;
use OCA\OpenConnector\Service\SearchService;
use OCA\OpenConnector\Db\Rule;
use OCA\OpenConnector\Db\RuleMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Controller for handling rule related operations
 */
class RulesController extends Controller
{


    /**
     * Constructor for the RuleController
     *
     * @param string     $appName    The name of the app
     * @param IRequest   $request    The request object
     * @param IAppConfig $config     The app configuration object
     * @param RuleMapper $ruleMapper The rule mapper object
     *
     * @return void
     */
    public function __construct(
        $appName,
        IRequest $request,
        private IAppConfig $config,
        private RuleMapper $ruleMapper
    ) {
        parent::__construct($appName, $request);

    }//end __construct()


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

    }//end page()


    /**
     * Retrieves a list of all rules
     *
     * This method returns a JSON response containing an array of all rules in the system.
     *
     * @param ObjectService $objectService Service for object operations
     * @param SearchService $searchService Service for search operations
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the list of rules
     */
    public function index(ObjectService $objectService, SearchService $searchService): JSONResponse
    {
        $filters        = $this->request->getParams();
        $fieldsToSearch = [
            'name',
            'description',
        ];

        $searchParams     = $searchService->createMySQLSearchParams(filters: $filters);
        $searchConditions = $searchService->createMySQLSearchConditions(filters: $filters, fieldsToSearch: $fieldsToSearch);
        $filters          = $searchService->unsetSpecialQueryParams(filters: $filters);

        return new JSONResponse(
            [
                'results' => $this->ruleMapper->findAll(
                    limit: null,
                    offset: null,
                    filters: $filters,
                    searchConditions: $searchConditions,
                    searchParams: $searchParams
                ),
            ]
        );

    }//end index()


    /**
     * Retrieves a single rule by its ID
     *
     * This method returns a JSON response containing the details of a specific rule.
     *
     * @param string $id The ID of the rule to retrieve
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the rule details
     */
    public function show(string $id): JSONResponse
    {
        try {
            return new JSONResponse($this->ruleMapper->find(id: (int) $id));
        } catch (DoesNotExistException $exception) {
            return new JSONResponse(data: ['error' => 'Not Found'], statusCode: 404);
        }

    }//end show()


    /**
     * Creates a new rule
     *
     * This method creates a new rule based on POST data.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the created rule
     */
    public function create(): JSONResponse
    {
        $data = $this->request->getParams();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_') === true) {
                unset($data[$key]);
            }
        }

        if (isset($data['id']) === true) {
            unset($data['id']);
        }

        // Create the rule.
        $rule = $this->ruleMapper->createFromArray(object: $data);

        return new JSONResponse($rule);

    }//end create()


    /**
     * Updates an existing rule
     *
     * This method updates an existing rule based on its ID.
     *
     * @param int $id The ID of the rule to update
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse A JSON response containing the updated rule details
     */
    public function update(int $id): JSONResponse
    {
        $data = $this->request->getParams();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_') === true) {
                unset($data[$key]);
            }
        }

        if (isset($data['id']) === true) {
            unset($data['id']);
        }

        // Update the rule.
        $rule = $this->ruleMapper->updateFromArray(id: (int) $id, object: $data);

        return new JSONResponse($rule);

    }//end update()


    /**
     * Deletes a rule
     *
     * This method deletes a rule based on its ID.
     *
     * @param int $id The ID of the rule to delete
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse An empty JSON response
     */
    public function destroy(int $id): JSONResponse
    {
        $this->ruleMapper->delete($this->ruleMapper->find((int) $id));

        return new JSONResponse([]);

    }//end destroy()


}//end class

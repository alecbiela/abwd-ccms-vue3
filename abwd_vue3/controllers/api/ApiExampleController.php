<?php
/**
 * This is an example of an API controller that can be used to send JSON back
 * to the SFC based on routes defined in src/RouteList.php
 */
namespace Concrete\Package\AbwdVue3\Controller\Api;

defined('C5_EXECUTE') or die('Access Denied');

use Concrete\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\User\User;

class ApiExampleController extends Controller
{
    // Attributes


    /**
     * If the constructor takes args, the Route inside RouteList.php can use an anonymous function
     * to instantiate a new ApiExampleController() and pass them
     */
    public function __construct()
    {

    }

    /**
     * This is a sample API endpoint in which we will load a bit of data from the DB and send it
     * @param null
     * @return JsonResponse
     */
    public function loadExampleData()
    {
        // This class extends the core controller, so we can do things like get request data
        $userData = array(
            'name'=>'',
            'id'=>''
        );
        $uID = intval($this->request('uID'));
        $u = User::getByUserID($uID);
        $found = true;

        // Get the current user if one doesn't exist at that ID
        if(!is_object($u)) {
            $u = $this->app->make(User::class);
            $found = false;
        }

        $userData = array(
            'name'=>$u->getUserName(),
            'id'=>$u->getUserID(),
            'found'=>$found
        );

        /**
         * @param array|null $data        Assoc. array of data (will pass through json_encode later)
         * @param int|null   $statusCode  HTTP status code (default 200)
         * @param array|null $headers     Any custom response headers 
         */
        return new JsonResponse($userData, JsonResponse::HTTP_OK);
    }
}

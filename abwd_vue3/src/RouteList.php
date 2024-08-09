<?php
/**
 * RouteList class - Holds a list of our routes and route groups
 * This is loaded in the package controller's on_start() method
 * The syntax is akin to Laravel routing with wildcard/optional route vars
 * Package handle (in this case abwd_vue3) appended to each route, see below
 */
namespace AbwdVue3;

defined('C5_EXECUTE') or die('Access Denied');

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Concrete\Package\AbwdVue3\Controller\Api\ApiPageListController;

class RouteList implements RouteListInterface
{
    public function loadRoutes($router)
    {
        // Option 1: Pass an anonymous function with URL slug parts as parameters
        $router->get('/api/page_list/{bid}/{page?}', function($bid, $page){
            $apl = new ApiPageListController($bid);
            return $apl->loadPages($page);
        }, 'abwd_vue3');

        // Option 2: Route directly to a controller method
        $router->get('/api/example', 'Concrete\Package\AbwdVue3\Controller\Api\ApiExampleController::loadExampleData','abwd_vue3');
    }
}
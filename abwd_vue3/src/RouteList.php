<?php
namespace AbwdVue3;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Concrete\Package\AbwdVue3\Controller\Api\ApiPageListController;

class RouteList implements RouteListInterface
{
    public function loadRoutes($router)
    {
        // Routes and route groups go here.
        $router->get('/api/page_list/{bid}/{page?}', function($bid, $page){
            $apl = new ApiPageListController($bid);
            return $apl->loadPages($page);
        }, 'abwd_vue3');
    }
}
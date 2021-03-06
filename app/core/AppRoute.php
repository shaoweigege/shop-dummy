<?php

class AppRoute
{
    private static $redirect = [
        'props' => []
    ];

    private static $route = [
        'controller' => '',
        'action' => ''
    ];

    private static $routeUrl;
    private static $routes;
    private static $fallBack;
    private static $isRequest;

    public static function setRoute ()
    {
        self::setRouteProps();

        if (!self::$isRequest) {
            
            if (!self::setRouteValidation()) {
                AppRedirect::setHeader(self::$routeUrl, self::$redirect['props']);
            }
        }

        return (!self::$isRequest) ? true : false;
    }

    public static function getRoute ()
    {
        return self::$route['controller'];
    }

    public static function getRouteAction ()
    {
        return self::$route['action'];
    }

    public static function getRouteController ()
    {
        $ControllerClass = ucfirst(self::$route['controller']).'Controller';
        $ControllerInstance = new $ControllerClass();

        self::setControllerAct($ControllerClass, $ControllerInstance);

        return $ControllerInstance;
    }

    public static function setRequest ()
    {
        $RequestController = RequestController::get_instance();
        $RequestController->getRequest(self::$route['controller']);
    }

    private static function setControllerAct ($controllerClass, $controllerInstance)
    {
        $act = (!empty(self::$route['action'])) ? 'set' . ucFirst(trim(self::$route['action'])) : '';

        if ($act !== '') {
            
            $methodVariable = array($controllerInstance, $act);

            if (method_exists($controllerClass, $act) && is_callable($methodVariable, true, $callable_name)) {

                $controllerInstance->{$act}();
    
            }
        }
    }

    private static function setRouteProps ()
    {

        self::$routeUrl = AppConfig::getConfig('view', ['url']);
        self::$routes = AppConfig::getConfig('route');
        self::$fallBack = AppConfig::getConfig('route', ['fallback']);
        self::$isRequest = false;

        $rt = (isset($_GET['rt']) && !empty(trim($_GET['rt']))) ? trim($_GET['rt']) : self::$fallBack;
        $rq = (isset($_GET['rq']) && !empty(trim($_GET['rq']))) ? trim($_GET['rq']) : '';
        $act = (isset($_GET['act']) && !empty(trim($_GET['act']))) ? trim($_GET['act']) : '';

        if ($rq !== '') {
            self::$route['controller'] = $rq;
            self::$isRequest = true;
        } else {
            self::$route['controller'] = $rt;
            self::$route['action'] = $act;
        }
    }

    private static function setRouteValidation ()
    {
        $controller = self::$route['controller'];
        $routes = ['shopFallBack', 'public', 'private', 'admin'];
        $isValid = true;

        if ($controller !==  self::$fallBack) {

            foreach($routes as $route) {
                if (in_array($controller, self::$routes[$route])) {
                    $meth = 'set'.ucfirst($route).'Route';
                    if (!$isValid = self::{$meth}()) {
                        break;
                    }
                }
            }
        }
        
        return $isValid;
    }

    private static function setShopFallBackRoute ()
    {
        if ( ! AppSession::hasValue('shopCart')) {
            return false;
        } 

        return true;
    }

    private static function setAdminRoute ()
    {
        if (!AppSession::isUsersession() || AppSession::isUsersession() && AppSession::getValue('role') !== '1') {
            return false;
        }

        return true;
    }

    private static function setPublicRoute ()
    {
        if (AppSession::isUsersession() && self::$route['controller'] === 'login') {
            return false;
        } 

        return true;
    }

    private static function setPrivateRoute ()
    {
        if (!AppSession::isUsersession()) {

            $redirect = AppConfig::getConfig('route', ['redirect', self::$route['controller']]);

            if (!empty($redirect)) {

                AppSession::setValues([
                    'redirect' => self::$route['controller'], 
                    'redirectMsg' => 'Bitte melden Sie sich an, um den Vorgang abzuschließen!'
                ]);

                self::$redirect['props'] = ['rt='.$redirect];
            }

            return false;

        } else {
            return true;
        }

        
    }

    private static function resetAct ()
    {
        unset($_GET['act']);
    }
}

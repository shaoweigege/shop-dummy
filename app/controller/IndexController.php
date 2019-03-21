<?php

class IndexController
{
    private static $instance;
    private $RouteController;
    private $View;
    private $CatModel;
    private $ArticleModel;

    public function __construct ($appConfig) 
    {
        $this->RouteController = RouteController::get_instance($appConfig);
        $this->View = BaseView::get_instance($appConfig);
        $this->CatModel = new CategoriesModel($appConfig['mysql']);
        $this->ArticleModel = new ArticleModel($appConfig['mysql']);
        Validator::setConfig($appConfig['validation']);
    }

    public static function get_instance($appConfig)
    {
        if( ! isset(self::$instance)){self::$instance = new IndexController($appConfig);}

        return self::$instance;
    }

    public function setRouteController ()
    {
        $this->RouteController->setRoute();
        $this->RouteController->setRequest();

        if (!$this->RouteController->isRequest()) {
            return false;
        } else {

            $Controller = $this->RouteController->getRouteController();

            $this->View->setView($Controller->getView());
            $this->View->setView(["cats" => $this->CatModel->getCategories()]);
            
            if (!empty($_SESSION['shopCart'])) {
                $this->View->setView(["shopCart" => $this->ArticleModel->getShopCart(AppSession::getShopCartList())]);
            }

            return true;
        }
    }

    public function setRequestController ()
    {
        $this->RouteController->getRequest();
    }

    public function getView ()
    {
        return $this->View->getView();
    }
}
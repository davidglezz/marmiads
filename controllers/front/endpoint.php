<?php

class MarmiadsEndpointModuleFrontController extends ModuleFrontController
{

    /**
     * Initializes endpoint controller: check access, sets class properties, etc.
     *
     * @return void
     */
    public function init()
    {
        // Prestashop does not support json responses in case of authentication error so I must check it here.
        if (!$this->checkAccess()) {
            http_response_code(401);
            $this->ajaxRenderJson(['error' => 'Invalid Token']);
        }

        // This is necessary to avoid rendering html templates, as we want a json response
        $this->ajax = true;
        $this->content_only = true;

        // Prestashop does not support custom urls with slash
        // This trick allows Prestashop to handle requests with slash in "action" parameter
        // /module/marmiads/endpoint?action=/feed/products will call displayAjaxFeedProducts()
        $_GET['action'] = str_replace('/', '_', $_GET['action']);

        parent::init();
    }

    /**
     * Check if the controller is available for the current user/visitor.
     *
     * @see Controller::checkAccess()
     *
     * @return bool
     */
    public function checkAccess()
    {
        // Check token in headers
        if (isset($_SERVER['HTTP_TOKEN'])) {
            return $_SERVER['HTTP_TOKEN'] === Configuration::get('MARMIADS_TOKEN');
        }

        // Check token in GET or POST
        return Tools::getValue('token') === Configuration::get('MARMIADS_TOKEN');
    }

    /**
     * Handles requests for unknown endpoints
     */
    public function displayAjax()
    {
        http_response_code(400);
        $this->ajaxRenderJson(['error' => 'Invalid endpoint']);
    }

    /**
     * Handles the request for the action/path: /config
     */
    public function displayAjaxConfig()
    {
        $config = new Config();
        $this->ajaxRenderJson($config->get());
    }

    /**
     * Handles the request for the action/path: /feed/products
     */
    public function displayAjaxFeedProducts()
    {
        $productFeed = new ProductFeed();
        $store = (int) Tools::getValue('store', 0);
        $start = (int) Tools::getValue('start', 0);
        $rows = Tools::getValue('rows', 'all');
        $includeVariations = (bool) Tools::getValue('include_variations', false);

        $this->ajaxRenderJson($productFeed->get($store, $start, $rows, $includeVariations));
    }

    /**
     * Output a json response and terminate the current script
     *
     * @param $content json-serializable data
     *
     * @throws PrestaShopException
     */
    protected function ajaxRenderJson($content)
    {
        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/Config.php';
require_once __DIR__ . '/classes/ProductFeed.php';

/**
 * MarmiAds Prestashop module
 *
 * @author David González García <davidglez@marmitakoo.com>
 */
class marmiads extends Module
{
    public function __construct()
    {
        $this->name = 'marmiads';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'Marmitakoo';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = $this->l('MarmiAds');
        $this->description = $this->l('MarmiAds API integration');
    }

    public function install()
    {
        Configuration::updateValue('MARMIADS_TOKEN', $this->randomToken());
        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName('MARMIADS_TOKEN');
        return parent::uninstall();
    }

    /**
     * Module configuration page
     *
     * @return string html form
     */
    public function getContent()
    {
        $html = '';

        if (Tools::isSubmit('regenerate-token')) {
            Configuration::updateValue('MARMIADS_TOKEN', $this->randomToken());
            $html .= $this->displayConfirmation($this->l('New token has been generated.'));
        }

        $html .= $this->renderForm();

        return $html;
    }

    protected function renderForm()
    {
        $base_url = $this->context->link->getModuleLink($this->name, 'endpoint') . '?action=';

        $form = [
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Marmiads endpoint info'),
                        'icon' => 'icon-info',
                    ],
                    'input' => [
                        [
                            'type' => 'html',
                            'label' => $this->l('{base_url}'),
                            'name' => 'base_url',
                            'html_content'  => '<label class="control-label">' . $base_url . '</label>',
                        ],
                    ],
                ],
            ], [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Marmiads module token configuration'),
                        'icon' => 'icon-key',
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Token'),
                            'name' => 'MARMIADS_TOKEN',
                            'readonly'  => true,
                            'desc'  => $this->l('The token must be included in the headers or in the GET or POST parameters'),
                        ],
                    ],
                    'submit' => [
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Regenerate'),
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'regenerate-token';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues()
        ];

        return $helper->generateForm($form);
    }

    public function getConfigFieldsValues()
    {
        return [
            'MARMIADS_TOKEN' => Configuration::get('MARMIADS_TOKEN'),
        ];
    }

    /**
     * Generate 40-character hexadecimal random token
     *
     * @return string token
     */
    private function randomToken()
    {
        return sha1(uniqid());
    }
}

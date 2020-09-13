<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * marmiads Prestashop module
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
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = $this->l('MarmiAds');
        $this->description = $this->l('MarmiAds API integration');
    }
}
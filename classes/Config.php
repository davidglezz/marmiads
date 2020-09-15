<?php

/**
 * This class allows get the response data required by the endpoint /config
 */
class Config
{
    /**
     * Main method to get /config endpoint response data
     */
    public function get()
    {
        return [
            'config' => [
                'default_language' => $this->getDefaultLanguage(),
                'default_currency' => $this->getDefaultCurrency(),
                'shops' => $this->getShops(),
            ]
        ];
    }

    /**
     * Gets shop/s defaul language
     * 
     * @return array with id, name and iso_code keys
     */
    private function getDefaultLanguage()
    {
        $idDefaultLanguage = Configuration::get('PS_LANG_DEFAULT');
        $lang = new Language($idDefaultLanguage);

        return [
            'id' => $lang->id,
            'name' => $lang->name,
            'iso_code' => $lang->iso_code,
        ];
    }

    /**
     * Gets shop/s defaul currency
     * 
     * @return array with id, name and iso_code keys
     */
    private function getDefaultCurrency()
    {
        $idDefaultCurrency = Configuration::get('PS_CURRENCY_DEFAULT');
        $currency = new Currency($idDefaultCurrency);

        return [
            'id' => $currency->id,
            'name' => $currency->name,
            'iso_code' => $currency->iso_code,
        ];
    }

    /**
     * Return shops information about name, languages and currencies.
     * 
     * @return array Shops with id, name, languages and currencies keys
     */
    private function getShops()
    {
        $shops = [];
        foreach (Shop::getShops() as $shop) {
            $shops[] = [
                'id' => $shop['id_shop'],
                'name' => $shop['name'],
                'languages' => $this->getShopLanguages($shop['id_shop']),
                'currencies' => $this->getShopCurrencies($shop['id_shop']),
            ];
        }

        return $shops;
    }

    /**
     * Get an array of languages.
     *
     * @param int $idShop
     *
     * @return array Languages with id, name and iso_code keys
     */
    private function getShopLanguages($idShop)
    {
        return array_map(function ($lang) {
            return [
                'id' => $lang['id_lang'],
                'name' => $lang['name'],
                'iso_code' => $lang['iso_code'],
            ];
        }, Language::getLanguages(true, $idShop));
    }

    /**
     * Get an array of currencies.
     *
     * @param int $idShop
     *
     * @return array Currencies with id and iso_code keys
     */
    private function getShopCurrencies($idShop)
    {
        return Db::getInstance()->executeS(
            'SELECT c.`id_currency` as id, `iso_code`
		    FROM `' . _DB_PREFIX_ . 'currency` c
		    JOIN `' . _DB_PREFIX_ . 'currency_shop` cs ON cs.`id_currency` = c.`id_currency`
            WHERE cs.`id_shop` = ' . (int) $idShop
        );
    }
}

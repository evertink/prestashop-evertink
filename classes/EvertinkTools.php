<?php
/**
 * 2014-2016 Reservation Partner LT
 *
 * NOTICE OF LICENSE
 *
 * This source file is a property of Reservation Partner LT.
 * Redistribution or republication of any part of this code is prohibited.
 * A single module license strictly limits the usage of this module
 * to one (1) shop / domain / website.
 * If you want to use this module in more than one shop / domain / website
 * you must purchase additional licenses.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * this module to newer versions in the future.
 *
 * @author    Reservation Partner LT <info@reservationpartner.com>
 * @copyright 2014-2016 Reservation Partner LT
 * @license   Commercial License
 * Property of Reservation Partner LT
 */

/**
 * Class EvertinkTools
 */
class EvertinkTools
{
    public static $js_def = array();

    /**
     * Add a new javascript definition at bottom of page
     *
     * @param mixed $js_def
     *
     * @return void
     */
    public static function addJsDef($js_def)
    {
        if (is_array($js_def)) {
            foreach ($js_def as $key => $js) {
                self::$js_def[$key] = $js;
            }
        } elseif ($js_def) {
            self::$js_def[] = $js_def;
        }
    }

    /**
     * Get order id by order reference code
     *
     * @param $reference
     * @return false|null|string
     */
    public static function getOrderIdByReference($reference)
    {
        $query = new DbQuery();
        $query->select('id_order')->from('orders')->where('reference=\''.pSQL($reference).'\'');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $args
     * @return bool|int
     */
    public static function sendEmail($args)
    {
        return Mail::Send(
            $args['language_id'], // language id
            $args['template_name'], // template name
            $args['subject'], // subject
            $args['template_vars'], // template {vars}
            $args['email'], // recipient email
            $args['name'], // recipient name
            null, // sender email
            null, // sender name
            null, // file attachment array
            null, // smtp mode
            $args['template_path'], // template path
            false // bool die
        );
    }

    /**
     * Returns shop's languages IDs array
     *
     * @param bool $active
     * @param bool $id_shop
     * @return array - integer array of IDs
     */
    public static function getLangIDs($active = false, $id_shop = false)
    {
        $lang_ids = array();
        foreach (Language::getLanguages($active, $id_shop) as $lang) {
            $lang_ids[] = (int)$lang['id_lang'];
        }

        return $lang_ids;
    }

    /**
     * Returns an array of a value that was submitted in multiple languages (PrestaShop format):
     * key_1, key_2, key_3, ... (1, 2, 3 - language IDs)
     *
     * @param string $key
     * @return array
     */
    public static function getValueLangArray($key)
    {
        $array = array();
        foreach (self::getLangIDs() as $id_lang) {
            $key_lang = $key.'_'.(string)$id_lang;
            if (Tools::isSubmit($key_lang)) {
                $array[$id_lang] = Tools::getValue($key_lang);
            }
        }
        return $array;
    }

    /**
     * Makes a language array, keys are language IDs, values are the same ($value)
     *
     * @param mixed $value
     * @param mixed $value_lt
     * @return array
     */
    public static function makeValueLangArray($value, $value_lt = null)
    {
        $arr = array_fill_keys(self::getLangIDs(), $value);

        if (is_null($value_lt)) {
            return $arr;
        }

        $id_lang_lt = Language::getIdByIso('LT');
        if (array_key_exists($id_lang_lt, $arr)) {
            $arr[$id_lang_lt] = $value_lt;
        }

        return $arr;
    }
}

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

if (!class_exists('EvertinkTools.php')) {
    require_once(dirname(__FILE__).'/EvertinkTools.php');
}

/**
 * Class EvertinkConfig
 */
class EvertinkConfig
{
    public $mail_delay;
    public $trigger_order_state;
    public $cron_link;
    public $shop_id;
    public $cron_token;

    /**
     * Maps configuration table keys to class properties
     *
     * @var array
     */
    protected static $definition = array(
        'mail_delay' => array(
            'key'  => 'RPEVERTINK_MAIL_DELAY',
            'type' => 'int',
        ),
        'trigger_order_state' => array(
            'key'  => 'RPEVERTINK_TRIGGER_ORDER_STATE',
            'type' => 'int',
        ),
        'cron_link' => array(
            'key'  => 'RPEVERTINK_CRON_LINK',
            'type' => 'text',
        ),
        'shop_id' => array(
            'key'  => 'RPEVERTINK_SHOP_ID',
            'type' => 'text',
        ),
        'cron_token' => array(
            'key'  => 'RPEVERTINK_CRON_TOKEN',
            'type' => 'text',
        ),
    );

    /**
     * Loads configuration values on instantiation
     *
     * @param int|null $id_lang
     */
    public function __construct($id_lang = null)
    {
        $this->load($id_lang);
    }

    /**
     * Loads configuration values from DB
     *
     * @param int|null $id_lang
     */
    public function load($id_lang = null)
    {
        $this->id_loaded_lang = (int)$id_lang;

        $config_keys = array();
        foreach (self::$definition as $property => $def) {
            $config_keys[] = $def['key'];
        }

        // Load all values as non-language first
        $config_values = Configuration::getMultiple($config_keys);

        unset($config_keys);

        foreach (self::$definition as $property => $def) {
            $config_key = $def['key'];

            if (isset($def['lang']) && $def['lang']) {
                if ($this->id_loaded_lang > 0) {
                    // Load value in specified language
                    $value = Configuration::get($config_key, $this->id_loaded_lang);
                    $this->{$property} = self::decode($value, $def['type']);
                } else {
                    // If no language is specified, load full language array
                    $value_lang = array();
                    foreach (Language::getLanguages() as $lang) {
                        $id_lang = (int)$lang['id_lang'];
                        $value_lang[$id_lang] = Configuration::get($config_key, $id_lang);
                    }

                    $this->{$property} = self::decode($value_lang, $def['type'], true);
                }
            } else {
                $this->{$property} =  self::decode($config_values[$config_key], $def['type']);
            }
        }
    }

    /**
     * Sets configuration values and saves them
     *
     * @param array $values
     */
    public function set($values)
    {
        foreach ($values as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        $this->save();
    }

    /**
     * Saves current properties to configuration table in DB
     */
    public function save()
    {
        foreach (self::$definition as $property => $def) {
            $lang = isset($def['lang']) && $def['lang'] ? true : false;
            $html = isset($def['html']) && $def['html'] ? true : false;
            $type = empty($def['type']) ? 'string' : $def['type'];

            if ($lang && $this->id_loaded_lang > 0) {
                $val = array();
                $val[$this->id_loaded_lang] = self::encode($this->{$property}, $type, $lang);
            } else {
                $val = self::encode($this->{$property}, $type, $lang);
            }

            self::saveValue($def['key'], $val, $html);
        }

        $this->load($this->id_loaded_lang);
    }

    /**
     * Fixes inserting HTML config variables first time
     *
     * @param string $key
     * @param mixed $value
     * @param bool $html
     * @return bool
     */
    protected static function saveValue($key, $value, $html)
    {
        /**
         * Non-existing keys are added via ObjectModel::add() method,
         * where 'value' property does not have a validate field -> does not set "allow HTML" before insert.
         * If key exists, then value is added directly by SQL query (and "allow HTML" is set).
         */
        if ($html && !Configuration::hasKey($key)) {
            Configuration::updateValue($key, $value, $html);
        }

        /**
         * Insert dummy value to fix consecutive foreach calls in
         * @see Configuration::updateValue() @ Line 345
         * @see https://github.com/PrestaShop/PrestaShop/commit/10ea735f2fa1d2b6f714c5336d068a486627ce2d
         */
        if ($html && is_array($value)) {
            $value[12345] = ' ';
        }

        return Configuration::updateValue($key, $value, $html);
    }


    /**
     * Deletes configuration values from DB and resets object values
     */
    public function delete()
    {
        foreach (self::$definition as $property => $def) {
            Configuration::deleteByName($def['key']);

            if (property_exists($this, $property)) {
                unset($this->{$property});
            }
        }
    }

    /**
     * Decodes a value retrieved from configuration table
     *
     * @param string $value
     * @param string $type
     * @param bool $lang
     * @return mixed
     */
    public static function decode($value, $type, $lang = false)
    {
        switch ($type) {
            case 'int':
                return $lang ? array_map('intval', $value) : (int)$value;
            case 'array':
                return $lang ? array_map('unserialize', $value) : unserialize($value);
            case 'string':
                return $lang ? array_map('strval', $value) : (string)$value;
            default:
                return $value;
        }
    }

    /**
     * Encodes a value before storing it in configuration table
     *
     * @param mixed $value
     * @param string $type
     * @param bool $lang
     * @return mixed
     */
    public static function encode($value, $type, $lang = false)
    {
        switch ($type) {
            case 'int':
                return $value;
            case 'array':
                return $lang ? array_map('serialize', $value) : serialize($value);
            case 'string':
                return $value;
            default:
                return $value;
        }
    }
}

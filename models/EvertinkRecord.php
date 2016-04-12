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
 * Class QueryRecord
 */
class EvertinkRecord extends ObjectModel
{
    /* @var string */
    public $stamp;

    /* @var int */
    public $stamp_unix;

    /* @var int */
    public $id_order;

    /* @var string */
    public $order_reference;

    public function __construct($id_evertink_record = null, $id_order = null, $order_reference = null, $stamp = '', $stamp_unix = 0)
    {
        parent::__construct($id_evertink_record);

        $this->id_order = $id_order;
        $this->order_reference = $order_reference;
        $this->stamp = $stamp;
        if ($stamp_unix === 0) {
            $date = new DateTime();
            $this->stamp_unix = $date->getTimestamp();
        } else {
            $this->stamp_unix = $stamp_unix;
        }
    }

    /**
     * @see ValidateCore
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'evertink_record',
        'primary' => 'id_evertink_record',
        'fields' => array(
            'stamp'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'stamp_unix'      => array('type' => self::TYPE_INT,    'validate' => 'isInt'),
            'id_order'      => array('type' => self::TYPE_INT,    'validate' => 'isInt'),
            'order_reference'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
        ),
    );

    /**
     * Returns model's table name
     *
     * @param bool $prefix
     * @return string
     */
    public static function table($prefix = false)
    {
        return ($prefix ? _DB_PREFIX_ : '').self::$definition['table'];
    }

    /**
     * @param $id_order
     * @return false|null|string
     */
    public static function getRecordIdByOrderId($id_order)
    {
        $sql = new DbQuery();
        $sql->select('id_evertink_record')->from(self::table())->where('id_order = '.(int)$id_order);
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Creates model's table
     *
     * @return bool
     */
    public static function installSchema()
    {
        $sql = sprintf('CREATE TABLE IF NOT EXISTS `%s` (
            `id_evertink_record`           int(11) NOT NULL AUTO_INCREMENT,
            `stamp`    varchar(20) NOT NULL,
            `stamp_unix` int(20) NOT NULL,
            `id_order`      int(11) NOT NULL,
            `order_reference`    varchar(20) NOT NULL,
            PRIMARY KEY (`id_evertink_record`)
        ) ENGINE = %s DEFAULT CHARSET = UTF8;', self::table(true), _MYSQL_ENGINE_);

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drops model's table
     *
     * @return bool
     */
    public static function uninstallSchema()
    {
        return Db::getInstance()->execute(sprintf('DROP TABLE IF EXISTS `%s`;', self::table(true)));
    }
}

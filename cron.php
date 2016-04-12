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

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

$dir_name = dirname(__FILE__);
$dir_name_arr = explode('/', str_replace('\\', '/', $dir_name));
$module_name = array_pop($dir_name_arr);

if (!Module::isEnabled($module_name) || !Module::isInstalled($module_name)) {
    die('MODULE IS DISABLED');
}

$module = Module::getInstanceByName($module_name);
$token = (string)Tools::getValue('token');

if (empty($token) || $token !== $module->cfg->cron_token) {
    die('INVALID TOKEN');
}

echo '<pre>';
echo "\rCron action started\r";
$module->cronAction();
echo "\rCron action ended\r";
echo '</pre>';

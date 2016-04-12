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
 * Class AdminEvertinkRecordParentController
 *
 * @property MyModule      $module
 * @property MyObjectModel $object
 */
class AdminEvertinkRecordParentController extends ModuleAdminController
{
    /**
     * Controller initialization
     */
    public function __construct()
    {
        $this->className   = 'EvertinkRecordParent';
        $this->bootstrap = true;

        parent::__construct();
    }
}

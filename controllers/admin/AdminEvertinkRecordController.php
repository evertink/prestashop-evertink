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
 * Class AdminEvertinkRecordController
 *
 * @property MyModule      $module
 * @property MyObjectModel $object
 */
class AdminEvertinkRecordController extends ModuleAdminController
{
    /**
     * Controller initialization
     */
    public function __construct()
    {
        $this->className   = 'EvertinkRecord';
        $this->table       = 'evertink_record';

        // If not specified, will be constructed like this: 'id_'.$this->table
        $this->identifier = 'id_evertink_record';

        // Used when table has position field
        $this->position_identifier = 'id_evertink_record';

        $this->_defaultOrderBy  = 'id_evertink_record';
        $this->_defaultOrderWay = 'desc';
        $this->bootstrap = true;

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ),
        );

        $this->fields_options = array(
            'general' => array(
                'title' =>    $this->l('Options'),
                'fields' =>    array(
                    'EVERTINK_SEND_EMAILS' => array(
                        'title' => $this->l('Send emails'),
                        'desc' => $this->l('Mail all customers meeting the delay criteria.'),
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
                'class' => (_PS_VERSION_ < 1.6 ? 'button' : null)
            ),
        );

        parent::__construct();
    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        // @TODO Format list values in $this->_list to be more user friendly
    }

    /**
     * Renders a list of item objects.
     * Position column is only visible in the shop context
     *
     * @return false|string
     */
    public function renderList()
    {
        unset($this->toolbar_btn['new']);
        $this->addRowAction('delete');
        $this->fields_list = array(
            'id_evertink_record' => array('title' => $this->l('ID'),),
            'id_order' => array('title' => $this->l('Order ID'),),
            'order_reference' => array('title' => $this->l('Order Reference'),),
            'stamp' => array('title' => $this->l('Stamp'),),
        );

        $this->informations[] = $this->l('Emailing can be automated - please contact your server administrator for automated task (cron) creation.');

        return parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOptions'.$this->table)) {
            if (Tools::getValue('EVERTINK_SEND_EMAILS') == 1) {
                $this->module->cronAction();
                unset($_POST['EVERTINK_SEND_EMAILS']);
            }
        }
        return parent::postProcess();
    }
}

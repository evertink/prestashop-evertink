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

require_once(dirname(__FILE__).'/models/EvertinkRecord.php');
require_once(dirname(__FILE__).'/classes/EvertinkCore.php');
require_once(dirname(__FILE__).'/classes/EvertinkConfig.php');


/**
 * Class Evertink
 */
class Evertink extends EvertinkCore
{
    /* @var EvertinkConfig|null */
    public $cfg = null;

    /**
     * Module object constructor
     */
    public function __construct()
    {
        $this->name             = 'evertink';
        $this->tab              = 'advertising_marketing';
        $this->version          = '1.0.2';
        $this->author           = 'reservationpartner.com';
        $this->module_key       = '';

        $this->ps_versions_compliancy = array('min' => '1.5.0.17', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName      = $this->l('Evertink');
        $this->description      = $this->l('Invite customers to rate your store');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        /**
         * Initializes custom module variables
         */
        $this->cfg = new EvertinkConfig($this->context->language->id);
    }

    /**
     * Installs module and it's resources
     *
     * @return bool
     */
    public function install()
    {
        try {
            if (!parent::install()) {
                throw new Exception($this->l('Could not install new module into database.'));
            }

            if (!EvertinkRecord::installSchema()) {
                throw new Exception($this->l('Failed to install model QueryRecord.'));
            }

            $hook_names = array(
                'displayBackOfficeHeader',
                'actionObjectOrderUpdateAfter'
            );
            if (!$this->registerHooks($hook_names)) {
                throw new Exception($this->l('Failed to register module hooks.'));
            }

            $cron_token = rand();
            $this->cfg->set(array(
                'mail_delay' => 7,
                'cron_token' => $cron_token,
                'cron_link' => _PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/cron.php?token='.$cron_token,
            ));
            $this->cfg->save();


            if (_PS_VERSION_ < 1.6) {
                if (!$this->installAdminController('AdminEvertinkRecordParent', 'Evertink', '')) {
                    throw new Exception(sprintf($this->l('Failed to install %s controller.'), 'AdminEvertinkRecordParent'));
                }
            }
            if (!$this->installAdminController('AdminEvertinkRecord', 'Evertink Records', (_PS_VERSION_ < 1.6 ? 'AdminEvertinkRecordParent' : ''))) {
                throw new Exception(sprintf($this->l('Failed to install %s controller.'), 'AdminEvertinkRecord'));
            }
        } catch (Exception $e) {
            $msg = get_class($e).': '.$e->getMessage();
            $this->_errors[] = sprintf($this->l('An error occurred during installation process: %s'), $msg);
            $this->uninstall();
            return false;
        }

        return true;
    }

    /**
     * Uninstalls module and it's resources
     *
     * @return bool
     */
    public function uninstall()
    {
        try {
            if (!EvertinkRecord::uninstallSchema()) {
                throw new Exception($this->l('Failed to uninstall model Record.'));
            }

            if (!$this->uninstallAdminController('AdminEvertinkRecord')) {
                throw new Exception(sprintf($this->l('Failed to uninstall %s controller.'), 'AdminEvertinkRecord'));
            }

            if (_PS_VERSION_ < 1.6) {
                if (!$this->uninstallAdminController('AdminEvertinkRecordParent')) {
                    throw new Exception(sprintf($this->l('Failed to uninstall %s controller.'), 'AdminEvertinkRecordParent'));
                }
            }

            $this->cfg->delete();

            parent::uninstall();
        } catch (Exception $e) {
            $msg = get_class($e).': '.$e->getMessage();
            $this->_errors[] = sprintf($this->l('An error occurred during uninstallation process: %s'), $msg);
            return false;
        }

        return true;
    }

    /**
     * Renders module configuration page
     *
     * @see HelperOptionsCore::generateOptions
     * @return string HTML
     */
    public function getContent()
    {
        $this->context->controller->addJS($this->_path.'views/js/'.$this->name.'-bo-opt.js');

        $msg = '';
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->cfg->set(array(
                'shop_id' => (string)Tools::getValue('RPEVERTINK_SHOP_ID'),
                'mail_delay' => (int)Tools::getValue('RPEVERTINK_MAIL_DELAY'),
                'trigger_order_state' => (int)Tools::getValue('RPEVERTINK_TRIGGER_ORDER_STATE'),
                'cron_token' => (string)Tools::getValue('RPEVERTINK_CRON_TOKEN'),
                'cron_link' => _PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/cron.php?token='.(string)Tools::getValue('RPEVERTINK_CRON_TOKEN'),
            ));
            unset($_POST['RPEVERTINK_CRON_LINK']);
            $this->cfg->save();
            $msg = $this->displayConfirmation('Module settings have been updated successfully.');
        }

        $moduleUrl = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name;

        $fieldset = array(
            'title'  => $this->l('Module settings'),
            'class' => (_PS_VERSION_ < 1.6 ? 'form-15-compatibility' : null),
            'fields' => array(
                'RPEVERTINK_SHOP_ID' => array(
                    'title' => $this->l('Shop ID').' *',
                    'desc'  => $this->l('Your Evertink ID. Usually matches shop domain name.'),
                    'cast'  => 'strval',
                    'type'  => 'text',
                    'size'  => 3,
                ),
                'RPEVERTINK_MAIL_DELAY' => array(
                    'title' => $this->l('Delay in days').' *',
                    'desc'  => $this->l('Set how many days should pass before sending an email becomes possible.'),
                    'cast'  => 'intval',
                    'type'  => 'text',
                    'size'  => 3,
                ),
                'RPEVERTINK_TRIGGER_ORDER_STATE' => array(
                    'title' => $this->l('Order state').' *',
                    'desc'  => $this->l('Select an order state after which email queue record will be created for specific order.'),
                    'cast'  => 'intval',
                    'type'  => 'select',
                    'list'  => OrderState::getOrderStates($this->context->language->id),
                    'identifier' => 'id_order_state',
                ),
                'RPEVERTINK_CRON_TOKEN' => array(
                    'title' => $this->l('Cron token'),
                    'desc'  => $this->l('CRON security token. Used so that random people couldn\'t spam your CRON task.'),
                    'cast'  => 'strval',
                    'type'  => 'text',
                    'size'  => 3,
                ),
                'RPEVERTINK_CRON_LINK' => array(
                    'title' => $this->l('Cron link'),
                    'desc'  => $this->l('This CRON link can be used to setup automated emails.'),
                    'cast'  => 'strval',
                    'type'  => 'text',
                    'size'  => 3,
                ),
            ),
            'buttons' => array(
                'cancelBlock' => array(
                    'title' => $this->l('Cancel'),
                    'href'  => $moduleUrl,
                    'icon'  => 'process-icon-cancel'
                ),
            ),
            'submit' => array(
                'name'  => 'submit'.$this->name,
                'title' => $this->l('Save'),
                'class' => (_PS_VERSION_ < 1.6 ? 'button' : null)
            ),
        );

        return $msg.$this->renderConfigForm($fieldset);
    }

    /**
     * Adds JS and CSS file to page header
     *
     * @return string
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/'.$this->name.'-bo.css');
        if (Tools::getValue('controller') == 'AdminEvertinkRecord') {
            $this->context->controller->addJS($this->_path.'views/js/'.$this->name.'-bo.js');
        }
        if (_PS_VERSION_ < 1.6) {
            $this->context->controller->addJS($this->_path.'views/js/'.$this->name.'-bo-global.js');
        }
    }

    /**
     * Hook fires after order update
     *
     * @param $args
     */
    public function hookActionObjectOrderUpdateAfter($args)
    {
        $evertinkTriggerStateId = (int)Configuration::get('RPEVERTINK_TRIGGER_ORDER_STATE');
        if ((int)$args['object']->current_state === $evertinkTriggerStateId) {
            if (!EvertinkRecord::getRecordIdByOrderId($args['object']->id)) {
                $record = new EvertinkRecord(null, $args['object']->id, $args['object']->reference, $args['object']->date_upd);
                $record->save();
            }
        }
    }

    /**
     * Mail customers exceeding day offset
     *
     * @throws PrestaShopDatabaseException
     */
    public function cronAction()
    {
        error_log('cron action');
        $delayInDays = (int)Configuration::get('RPEVERTINK_MAIL_DELAY');
        $shopId = (string)Configuration::get('RPEVERTINK_SHOP_ID');
        $query = new DbQuery();
        $query->select('*')->from(EvertinkRecord::table())->where('stamp_unix < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.pSQL($delayInDays).' DAY))');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        $toBeDeleted = array();
        foreach ($result as $record) {
            error_log('foreach');
            // send mail for every matching record
            $order = new Order($record['id_order']);
            $customer = new Customer($order->id_customer);
            $args = array(
                'language_id' => $customer->id_lang,
                'template_name' => 'evaluation_email',
                'subject' => $this->l('Evaluation email'),
                'template_vars' => array(
                    '{order_reference}' => $order->reference,
                    '{order_date}' => $order->date_add,
                    '{evaluation_link}' => 'http://www.evertink.lt/review/'.$shopId.'/add?email='.$customer->email.'&orderid='.$order->reference,
                ),
                'email' => $customer->email,
                'name' => $customer->firstname.' '.$customer->lastname,
                'template_path' => _PS_MODULE_DIR_.$this->name.'/mails/',
            );
            $result = EvertinkTools::sendEmail($args);

            // records to be removed afterwards
            if ($result == true) {
                $toBeDeleted[] = $record['id_evertink_record'];
            }

            $deletion_target_ids = '';
            $length = count($toBeDeleted);
            if ($length > 0) {
                for ($i = 0; $i < $length; $i++) {
                    $deletion_target_ids .= (string)$toBeDeleted[$i];
                    if (($i+1) < $length) {
                        $deletion_target_ids .= ', ';
                    }
                }
                $query = 'DELETE FROM `'.EvertinkRecord::table(true).'` WHERE id_evertink_record IN ('.pSQL($deletion_target_ids).')';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);
            }
        }
    }

    /**
     * Registers a module admin controller and install a back-office tab (optional)
     *
     * @param string     $tabClass  Controller class name without word 'Controller' at the end
     * @param string     $tabTitle  Single string or a language string array
     * @param string|int $tabParent Parent tab class name or ID
     *
     * @return int|false
     */
    protected function installAdminController($tabClass, $tabTitle = '', $tabParent = -1)
    {
        $tab = new Tab();
        $tab->module = $this->name;
        $tab->class_name = $tabClass;

        $tabTitle = empty($tabTitle) ? $tabClass : $tabTitle;
        if (is_array($tabTitle)) {
            $tab->name = $tabTitle;
        } else {
            $tab->name= array();
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $tabTitle;
            }
        }

        if (!empty($tabParent) && is_string($tabParent)) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParent);
        } elseif (is_int($tabParent)) {
            $tab->id_parent = $tabParent;
        } else {
            $tab->id_parent = 0;
        }

        return $tab->add() ? (int)$tab->id : false;
    }

    /**
     * Installs a specified module admin controller
     *
     * @param string $tabClass - Controller class name without word 'Controller' at the end
     *
     * @return bool
     */
    protected function uninstallAdminController($tabClass)
    {
        $id_tab = (int)Tab::getIdFromClassName($tabClass);
        $tab = new Tab($id_tab);

        return $tab->delete();
    }
}

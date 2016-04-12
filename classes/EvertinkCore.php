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
 * Class SwedbankBankLinkPaymentCore
 */
abstract class EvertinkCore extends Module
{
    /* @var bool */
    public $bootstrap = true;

    /**
     * Register an array of module hooks and return true or false
     *
     * @param array $hook_names
     * @return bool
     */
    protected function registerHooks(array $hook_names)
    {
        foreach ($hook_names as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Renders configuration form
     *
     * @param array $option_list - single fieldset (without key) or multiple fieldsets
     * @param bool $has_overrides - enables overriding Helper templates
     * @return string - HTML
     */
    public function renderConfigForm($option_list, $has_overrides = false)
    {
        $link_admin_modules = $this->context->link->getAdminLink('AdminModules');

        /** Define default/base options */
        $fieldsets = array(
            'general' => array(
                'title'  => $this->l('Module settings'),
                'submit' => array(
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ),
            ),
        );

        /** Version specific tweaks */
        if ($this->bootstrap) {
            $fieldsets['general']['buttons'] = array(
                'cancelBlock' => array(
                    'title' => $this->l('Cancel'),
                    'href'  => $link_admin_modules,
                    'icon'  => 'process-icon-cancel'
                )
            );
        } else {
            $fieldsets['general']['image'] = '../img/admin/information.png';
        }

        /** Merge option arrays, single or multiple fieldsets */
        if (array_key_exists('fields', $option_list)) {
            $fieldsets['general'] = array_merge($fieldsets['general'], $option_list);
        } else {
            $general   = array_merge($fieldsets['general'], $option_list['general']);
            $fieldsets = $option_list;
            $fieldsets['general'] = $general;
            unset($general);
        }

        $h = new HelperOptions();
        $h->token = Tools::getAdminTokenLite('AdminModules');
        $h->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $h->id = Tab::getIdFromClassName('AdminTools');

        /** Define extra properties for PS 1.5 */
        if (!$this->bootstrap) {
            $h->title = $this->displayName;
            $h->show_toolbar = true;
            $h->toolbar_scroll = true;
            $h->toolbar_btn = array(
                'save' => array(
                    'desc' => $this->l('Save'),
                    'href' => $link_admin_modules.'&configure='.$this->name.'&save'.$this->name,
                ),
                'back' => array(
                    'desc' => $this->l('Back to list'),
                    'href' => $link_admin_modules,
                )
            );
        }

        /** @see Helper::createTemplate() */
        if ($has_overrides) {
            $h->module = $this;
        }

        /** TinyMCE fix */
        $rte_fields = array();
        foreach ($fieldsets as $fieldset) {
            if (isset($fieldset['fields'])) {
                foreach ($fieldset['fields'] as $field_key => $field_def) {
                    $is_autoload_rte = !empty($field_def['autoload_rte']) && $field_def['autoload_rte'];
                    if ($is_autoload_rte || (!empty($field_def['rte']) && $field_def['rte'])) {
                        $rte_fields[] = $field_key;
                    }
                }
            }
        }

        if (!empty($field_def)) {
            $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');

            $iso = $this->context->language->iso_code;
            EvertinkTools::addJsDef(array(
                'iso'        => file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en',
                'path_css'   => _THEME_CSS_DIR_,
                'ad'         => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
                'rte_fields' => $rte_fields,
            ));
        }

        return $h->generateOptions($fieldsets);
    }

    /**
     * Returns a link to modules's configuration page
     *
     * @return string|false
     */
    public function getConfigLink()
    {
        if (self::isInstalled($this->name)) {
            return $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name;
        }

        return false;
    }
}

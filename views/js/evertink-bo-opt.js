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

$(document).ready(function() {
    var $form = $('#configuration_fieldset_general'),
        $retro = $('.form-15-compatibility');
    if ($form.length > 0) {
        $form.find('input[name=RPEVERTINK_CRON_LINK]').prop("readonly", true);
    } else {
        $retro.first().find('input[name=RPEVERTINK_CRON_LINK]').prop("readonly", true);
    }
});
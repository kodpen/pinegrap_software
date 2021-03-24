<?php

/**
 *
 * liveSite - Enterprise Website Platform
 * 
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include('init.php');
$user = validate_user();
validate_ecommerce_access($user);

$liveform = new liveform('edit_key_code');

$key_code = db_item(
    "SELECT
        code,
        offer_code,
        enabled,
        expiration_date,
        notes,
        single_use,
        report
    FROM key_codes
    WHERE id = '" . e($_REQUEST['id']) . "'");

if (!$_POST) {
    
    // If the form has not been submitted yet, then pre-populate fields with data.
    if (!$liveform->field_in_session('id')) {
        $liveform->assign_field_value('code', $key_code['code']);
        $liveform->assign_field_value('offer_code', $key_code['offer_code']);
        $liveform->assign_field_value('enabled', $key_code['enabled']);
        
        if ($key_code['expiration_date'] != '0000-00-00') {
            $liveform->assign_field_value('expiration_date', prepare_form_data_for_output($key_code['expiration_date'], 'date'));
        }

        $liveform->set('notes', $key_code['notes']);
        $liveform->set('single_use', $key_code['single_use']);
        $liveform->set('report', $key_code['report']);
    }

    $screen = 'edit';

    echo output_header();

    require('templates/edit_key_code.php');

    echo output_footer();
    
    $liveform->remove_form();

} else {

    validate_token_field();

    $liveform->add_fields_to_session();

    // If the user selected to delete this key code, then delete it.
    if ($liveform->field_in_session('delete')) {

        db("DELETE FROM key_codes WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('key code (' . $key_code['code'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_key_codes = new liveform('view_key_codes');
        $liveform_view_key_codes->add_notice('The key code has been deleted.');

    // Otherwise the user selected to save the key code, so save it.
    } else {

        $liveform->validate_required_field('code', 'Key Code is required.');
        $liveform->validate_required_field('offer_code', 'Offer Code is required.');

        // If there is not already an error for the code field,
        // and that code is already in use, then output error.
        if (
            (!$liveform->check_field_error('code'))
            && (db_value("SELECT COUNT(*) FROM key_codes WHERE (code = '" . e($liveform->get_field_value('code')) . "') AND (id != '" . e($liveform->get_field_value('id')) . "')") != 0)
        ) {
            $liveform->mark_error('code', 'Sorry, the key code that you entered is already in use, so please enter a different key code.');
        }

        // If there is not already an error for the offer code field,
        // and an offer cannot be found for that code, then output error.
        if (
            (!$liveform->check_field_error('offer_code'))
            && (db_value("SELECT COUNT(*) FROM offers WHERE code = '" . e($liveform->get_field_value('offer_code')) . "'") == 0)
        ) {
            $liveform->mark_error('offer_code', 'Sorry, we cannot find an offer for the offer code that you entered.');
        }

        $expiration_date = $liveform->get_field_value('expiration_date');

        // If an expiration date was entered and it is not valid, then add error.
        if (($expiration_date != '') && (validate_date($expiration_date) == false)) {
            $liveform->mark_error('expiration_date', 'Please enter a valid expiration date.');
        }
        
        // If there is an error, forward user back to previous screen.
        if ($liveform->check_form_errors()) {
            go($_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
        }
        
        db(
            "UPDATE key_codes
            SET
                code = '" . e($liveform->get_field_value('code')) . "',
                offer_code = '" . e($liveform->get_field_value('offer_code')) . "',
                enabled = '" . e($liveform->get_field_value('enabled')) . "',
                expiration_date = '" . e(prepare_form_data_for_input($expiration_date, 'date')) . "',
                notes = '" . e($liveform->get('notes')) . "',
                single_use = '" . e($liveform->get_field_value('single_use')) . "',
                report = '" . e($liveform->get('report')) . "',
                user = '" . USER_ID . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('key code (' . $liveform->get_field_value('code') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_key_codes = new liveform('view_key_codes');
        $liveform_view_key_codes->add_notice('The key code has been saved.');
    }

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_key_codes.php');
}
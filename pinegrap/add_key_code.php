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

$liveform = new liveform('add_key_code');

if (!$_POST) {

    // If the form has not been submitted yet, then pre-populate fields with data.
    if (!$liveform->field_in_session('quantity')) {
        $liveform->set('limit', $_GET['limit']);
        $liveform->set('enabled', 1);
        $liveform->set('report', 'key_code');
    }

    $screen = 'create';

    // If the user has disabled the quantity limit via the query string, then deal with that.
    // This is a feature for certain sites that need to create large batches of key codes at once.
    if ($liveform->get('limit') == 'false') {
        $quantity_max = '';

    // Otherwise the user has not disabled the quantity limit,
    // so set the default quantity limit to 1,000.
    } else {
        $quantity_max = '1000';
    }

    echo output_header();

    require('templates/edit_key_code.php');

    echo output_footer();

    $liveform->remove_form();

} else {

    validate_token_field();

    $liveform->add_fields_to_session();

    $liveform->validate_required_field('quantity', 'Quantity is required.');

    $quantity = $liveform->get('quantity');

    // Remove commas from quantity.
    $quantity = str_replace(',', '', $quantity);

    // If there is not already an error for the quantity field,
    // and value is not a number greater than 0, then add error.
    if (
        (!$liveform->check_field_error('quantity'))
        &&
        (
            !is_numeric($quantity)
            || ($quantity <= 0)
        )
    ) {
        $liveform->mark_error('quantity', 'Please enter a valid quantity.');
    }

    // If there is not already an error for the quantity field,
    // and the quantity is too high, and the limit has not been disabled
    // via the query string, then add error.
    if (
        !$liveform->check_field_error('quantity')
        and ($quantity > 1000)
        and ($liveform->get('limit') != 'false')
    ) {
        $liveform->mark_error('quantity', 'Sorry, the maximum quantity is 1,000.');
    }
    
    $liveform->validate_required_field('offer_code', 'Offer Code is required.');

    // If there is not already an error for the code field,
    // and that code is already in use, then output error.
    if (
        (!$liveform->check_field_error('code'))
        && (db_value("SELECT COUNT(*) FROM key_codes WHERE code = '" . e($liveform->get_field_value('code')) . "'") != 0)
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

    if ($liveform->check_form_errors()) {
        go($_SERVER['PHP_SELF']);
    }

    // Create a gift card for each quantity.
    for ($i = 1; $i <= $quantity; $i++) {

        // If the quantity is greater than 1 or no code was entered, then generate code.
        if ($quantity > 1 or !$liveform->get('code')) {
            $code = generate_key_code();

        // Otherwise the quantity is 1, and the user entered a code, so use that.
        } else {
            $code = $liveform->get('code');
        }

        db(
            "INSERT INTO key_codes (
                code,
                offer_code,
                enabled,
                expiration_date,
                notes,
                single_use,
                report,
                user,
                timestamp)
            VALUES (
                '" . e($code) . "',
                '" . e($liveform->get('offer_code')) . "',
                '" . e($liveform->get('enabled')) . "',
                '" . e(prepare_form_data_for_input($expiration_date, 'date')) . "',
                '" . e($liveform->get('notes')) . "',
                '" . e($liveform->get('single_use')) . "',
                '" . e($liveform->get('report')) . "',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");
    }

    $liveform_view_key_codes = new liveform('view_key_codes');

    // If one key code was created, then prepare log and notice for that.
    if ($quantity == 1) {

        log_activity('Key code (' . $code . ') was created.');
        
        $liveform_view_key_codes->add_notice('The key code has been created (' . h($code) . ').');

    // Otherwise more than 1 key code was created, so prepare log and notice for that.
    } else {

        log_activity(number_format($quantity) . ' key codes were created.');
        
        $liveform_view_key_codes->add_notice('The key codes have been created.');
    }

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_key_codes.php');
}

function generate_key_code() {

    $characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    $key_code = '';

    for ($i = 1; $i <= 10; $i++) {
        $index = mt_rand(0, 35);
        $key_code .= $characters[$index];
    }
    
    // If code is already in use, use recursion to generate a new code.
    if (db("SELECT id FROM key_codes WHERE code = '" . e($key_code) . "'")) {
        return generate_key_code();
        
    // Otherwise code is not already in use, so return code.
    } else {
        return $key_code;
    }
}
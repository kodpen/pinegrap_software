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

// Increase execution time because this script can be used to create many gift cards.
ini_set('max_execution_time', '9999');

include('init.php');
$user = validate_user();
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('add_gift_card');

// If the form has not been submitted, then output it.
if (!$_POST) {
    // If the form has not been submitted yet, then prefill default value.
    if ($liveform->field_in_session('amount') == false) {
        // If a number of validity days has been entered in the settings,
        // then prefill expiration date field with an appropriate date.
        if (ECOMMERCE_GIFT_CARD_VALIDITY_DAYS) {
            // If the date format is month and then day, then use that format.
            if (DATE_FORMAT == 'month_day') {
                $month_and_day_format = 'n/j';

            // Otherwise the date format is day and then month, so use that format.
            } else {
                $month_and_day_format = 'j/n';
            }
            
            // Set the default expiration date to the number of validity days
            // from today's date.
            $expiration_date = date($month_and_day_format . '/Y', strtotime('+' . ECOMMERCE_GIFT_CARD_VALIDITY_DAYS . ' day'));

            $liveform->assign_field_value('expiration_date', $expiration_date);
        }

        $liveform->set('limit', $_GET['limit']);

    }

    // If the user has disabled the quantity limit via the query string, then deal with that.
    // This is a feature for certain sites that need to create large batches of gift cards at once.
    if ($liveform->get('limit') == 'false') {
        $quantity_max = '';

    // Otherwise the user has not disabled the quantity limit,
    // so set the default quantity limit to 1,000.
    } else {
        $quantity_max = '1000';
    }

    echo
        output_header() . '
        <div id="subnav">
            <h1>[new gift card]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Gift Card</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create one or more new gift cards by entering an amount.  The code will be generated for you.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->field(array(
                        'type' => 'hidden',
                        'name' => 'limit')) . '
                <table class="field">
                    <tr>
                        <td>Amount:</td>
                        <td>
                            ' . BASE_CURRENCY_SYMBOL . $liveform->output_field(array('type' => 'text', 'id' => 'amount', 'name' => 'amount', 'size' => '5')) . '
                            <script>$("#amount").focus()</script>
                        </td>
                    </tr>
                    <tr>
                        <td>Expiration Date:</td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'text',
                                'id' => 'expiration_date',
                                'name' => 'expiration_date',
                                'size' => '10',
                                'maxlength' => '10')) . '&nbsp;
                            (leave blank for no expiration)
                            ' . get_date_picker_format() . '
                            <script>
                                $("#expiration_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Notes:</td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'textarea',
                                'name' => 'notes',
                                'style' => 'width: 600px; height: 100px')) . '
                        </td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'number',
                                'name' => 'quantity',
                                'value' => '1',
                                'min' => '1',
                                'max' => $quantity_max,
                                'style' => 'width: 5em')) . '&nbsp;
                            (increase quantity to create multiple gift cards at once)
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();

    $amount = $liveform->get_field_value('amount');
    $expiration_date = $liveform->get_field_value('expiration_date');
    $notes = $liveform->get_field_value('notes');
    $quantity = $liveform->get_field_value('quantity');
    
    $liveform->validate_required_field('amount', 'Amount is required.');
    $liveform->validate_required_field('quantity', 'Quantity is required.');

    // Remove commas from amount and quantity.
    $amount = str_replace(',', '', $amount);
    $quantity = str_replace(',', '', $quantity);

    // If there is not already an error for the amount field,
    // and value is not a number greater than 0, then add error.
    if (
        ($liveform->check_field_error('amount') == false)
        &&
        (
            (is_numeric($amount) == false)
            || ($amount <= 0)
        )
    ) {
        $liveform->mark_error('amount', 'Please enter a valid amount.');
    }

    // If an expiration date was entered and it is not valid, then add error.
    if (($expiration_date != '') && (validate_date($expiration_date) == false)) {
        $liveform->mark_error('expiration_date', 'Please enter a valid expiration date.');
    }

    // If there is not already an error for the quantity field,
    // and value is not a number greater than 0, then add error.
    if (
        ($liveform->check_field_error('quantity') == false)
        &&
        (
            (is_numeric($quantity) == false)
            || ($quantity <= 0)
        )
    ) {
        $liveform->mark_error('quantity', 'Please enter a valid quantity.');
    }

    // If there is not already an error for the quantity field,
    // and the quantity is too high, and the limit has not been disabled
    // via the query string, then add error.
    if (
        ($liveform->check_field_error('quantity') == false)
        and ($quantity > 1000)
        and ($liveform->get('limit') != 'false')
    ) {
        $liveform->mark_error('quantity', 'Sorry, the maximum quantity is 1,000.');
    }
    
    if ($liveform->check_form_errors() == true) {
        go(PATH . SOFTWARE_DIRECTORY . '/add_gift_card.php');
    }

    // Convert amount into cents.
    $amount = $amount * 100;

    // Create a gift card for each quantity.
    for ($i = 1; $i <= $quantity; $i++) { 
        $code = generate_gift_card_code();

        db(
            "INSERT INTO gift_cards (
                code,
                amount,
                balance,
                notes,
                expiration_date,
                created_user_id,
                created_timestamp,
                last_modified_user_id,
                last_modified_timestamp)
            VALUES (
                '" . $code . "',
                '" . e($amount) . "',
                '" . e($amount) . "',
                '" . e($notes) . "',
                '" . e(prepare_form_data_for_input($expiration_date, 'date')) . "',
                '" . USER_ID . "',
                UNIX_TIMESTAMP(),
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");
    }

    $liveform_view_gift_cards = new liveform('view_gift_cards');

    // If one gift card was created, then prepare log and notice for that situation.
    if ($quantity == 1) {
        log_activity('gift card (' . output_gift_card_code($code) . ') was created', $_SESSION['sessionusername']);
        
        $liveform_view_gift_cards->add_notice('The gift card has been created.  You may now give the code (' . output_gift_card_code($code) . ') to the customer.');

    // Otherwise more than 1 gift card was created, so prepare log and notice for that situation.
    } else {
        log_activity(number_format($quantity) . ' gift cards were created', $_SESSION['sessionusername']);
        
        $liveform_view_gift_cards->add_notice('The gift cards have been created.');
    }

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_gift_cards.php');
}
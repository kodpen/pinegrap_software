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

include_once('liveform.class.php');
$liveform = new liveform('add_ship_date_adjustment');

// Get shipping methods for pick list.
$shipping_methods = db_items(
    "SELECT
        id,
        name,
        code
    FROM shipping_methods
    ORDER BY
        name ASC,
        code ASC");

$shipping_method_options = array();

$shipping_method_options[] = array(
    'label' => '',
    'value' => '');

// Loop through the shipping methods in order to prepare pick list options.
foreach ($shipping_methods as $shipping_method) {
    $output_label = h($shipping_method['name']);
    
    if ($shipping_method['code'] != '') {
        $output_label .= ' (' . h($shipping_method['code']) . ')';
    }

    $shipping_method_options[] = array(
        'label' => $output_label,
        'value' => $shipping_method['id']);
}

// If the form has not been submitted, then output it.
if (!$_POST) {
    echo
        output_header() . '
        <div id="subnav">
            <h1>[new ship date adjustment]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Ship Date Adjustment</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a new adjustment for a specific zip code prefix and shipping method.</div>
            <form method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <td>Zip Code Prefix:</td>
                        <td>
                            ' . $liveform->output_field(array(
                                'type' => 'text',
                                'name' => 'zip_code_prefix',
                                'size' => '3',
                                'maxlength' => '3')) . '
                            &nbsp;(first 3 numbers)
                        </td>
                    </tr>
                    <tr>
                        <td>Shipping Method:</td>
                        <td>
                            ' . $liveform->output_field(array(
                                'type' => 'select',
                                'name' => 'shipping_method_id',
                                'options' => $shipping_method_options)) . '
                        </td>
                    </tr>
                    <tr>
                        <td>Adjustment:</td>
                        <td>
                            ' . $liveform->output_field(array(
                                'type' => 'text',
                                'name' => 'adjustment_days',
                                'size' => '3',
                                'maxlength' => '3')) . '
                            &nbsp;day(s)&nbsp;
                            ' . $liveform->output_field(array(
                                'type' => 'select',
                                'name' => 'adjustment_type',
                                'value' => 'later',
                                'options' => array(
                                    'earlier' => 'earlier',
                                    'later' => 'later'))) . '
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
    
    $liveform->validate_required_field('zip_code_prefix', 'Zip Code Prefix is required.');
    $liveform->validate_required_field('shipping_method_id', 'Shipping Method is required.');
    $liveform->validate_required_field('adjustment_days', 'The number of days is required.');
    $liveform->validate_required_field('adjustment_type', '"earlier" or "later" is required.');

    // If there is not already an error for the zip code prefix,
    // and the user did not enter 3 characters for the prefix,
    // then add an error.
    if (
        ($liveform->check_field_error('zip_code_prefix') == false)
        && (mb_strlen($liveform->get_field_value('zip_code_prefix')) != 3)
    ) {
        $liveform->mark_error('zip_code_prefix', 'Sorry, the zip code prefix must contain 3 characters.');
    }

    // If there is not already an error for the zip code prefix and shipping method fields,
    // and there is already an adjustment in the system for the values that the user entered,
    // then add error.
    if (
        ($liveform->check_field_error('zip_code_prefix') == false)
        && ($liveform->check_field_error('shipping_method_id') == false)
        &&
        (
            db_value(
                "SELECT COUNT(*)
                FROM ship_date_adjustments
                WHERE
                    (zip_code_prefix = '" . escape($liveform->get_field_value('zip_code_prefix')) . "')
                    AND (shipping_method_id = '" . escape($liveform->get_field_value('shipping_method_id')) . "')")
            > 0
        )
    ) {
        $liveform->mark_error('zip_code_prefix', 'Sorry, there is already a ship date adjustment for the zip code prefix and shipping method you selected.');
        $liveform->mark_error('shipping_method_id');
    }

    // If there is not already an error for the days,
    // and the value is not a number greater than 0, then add error.
    if (
        ($liveform->check_field_error('adjustment_days') == false)
        &&
        (
            (is_numeric($liveform->get_field_value('adjustment_days')) == false)
            || ($liveform->get_field_value('adjustment_days') <= 0)
        )
    ) {
        $liveform->mark_error('adjustment_days', 'Please enter a valid number of days.');
    }
    
    if ($liveform->check_form_errors() == true) {
        go(PATH . SOFTWARE_DIRECTORY . '/add_ship_date_adjustment.php');
    }

    // If the adjustment type is earlier, then set adjustment to a negative value for the db.
    if ($liveform->get_field_value('adjustment_type') == 'earlier') {
        $adjustment = -$liveform->get_field_value('adjustment_days');

    // Otherwise the adjustment type is later, so set the adjustment to a positive value.
    } else {
        $adjustment = $liveform->get_field_value('adjustment_days');
    }
    
    db(
        "INSERT INTO ship_date_adjustments (
            zip_code_prefix,
            shipping_method_id,
            adjustment,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('zip_code_prefix')) . "',
            '" . escape($liveform->get_field_value('shipping_method_id')) . "',
            '" . escape($adjustment) . "',
            '" . USER_ID . "',
            UNIX_TIMESTAMP(),
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");
    
    log_activity('ship date adjustment (' . $liveform->get_field_value('zip_code_prefix') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_ship_date_adjustments = new liveform('view_ship_date_adjustments');
    $liveform_view_ship_date_adjustments->add_notice('The ship date adjustment has been created.');

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_ship_date_adjustments.php');
}
?>
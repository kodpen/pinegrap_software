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

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('edit_offer');

// get original offer's info
$query = "SELECT * FROM offers WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$original_code = $row['code'];
$description = $row['description'];
$require_code = $row['require_code'];
$status = $row['status'];
$start_date = $row['start_date'];
$end_date = $row['end_date'];
$offer_rule_id = $row['offer_rule_id'];
$upsell = $row['upsell'];
$upsell_message = $row['upsell_message'];
$upsell_trigger_subtotal = $row['upsell_trigger_subtotal'];
$upsell_trigger_quantity = $row['upsell_trigger_quantity'];
$upsell_action_button_label = $row['upsell_action_button_label'];
$upsell_action_page_id = $row['upsell_action_page_id'];
$scope = $row['scope'];
$multiple_recipients = $row['multiple_recipients'];
$only_apply_best_offer = $row['only_apply_best_offer'];

$code = $original_code . '[' . get_duplicate_number($original_code) . ']';

// create duplicate offer
$query = "INSERT INTO offers (
            code,
            description,
            require_code,
            status,
            start_date,
            end_date,
            offer_rule_id,
            upsell,
            upsell_message,
            upsell_trigger_subtotal,
            upsell_trigger_quantity,
            upsell_action_button_label,
            upsell_action_page_id,
            scope,
            multiple_recipients,
            only_apply_best_offer,
            user,
            timestamp)
        VALUES (
            '" . escape($code) . "',
            '" . escape($description) . "',
            '" . escape($require_code) . "',
            '" . escape($status) . "',
            '" . escape($start_date) . "',
            '" . escape($end_date) . "',
            '" . escape($offer_rule_id) . "',
            '" . escape($upsell) . "',
            '" . escape($upsell_message) . "',
            '" . escape($upsell_trigger_subtotal) . "',
            '" . escape($upsell_trigger_quantity) . "',
            '" . escape($upsell_action_button_label) . "',
            '" . escape($upsell_action_page_id) . "',
            '" . escape($scope) . "',
            '" . escape($multiple_recipients) . "',
            '" . escape($only_apply_best_offer) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$new_offer_id = mysqli_insert_id(db::$con);

// get all offer actions for original offer
$query = "SELECT offer_action_id as id FROM offers_offer_actions_xref WHERE offer_id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$offer_actions = mysqli_fetch_items($result);

// loop through the offer actions in order to connect new offer to them
foreach ($offer_actions as $offer_action) {
    $query =
        "INSERT INTO offers_offer_actions_xref (
            offer_id,
            offer_action_id)
        VALUES (
            '" . $new_offer_id . "',
            '" . $offer_action['id'] . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

log_activity("offer ($original_code) was duplicated", $_SESSION['sessionusername']);

$liveform->add_notice('The offer has been duplicated.');

header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_offer.php?id=' . $new_offer_id);

function get_duplicate_number($offer_code, $number = 1)
{
    // check to see if there is already an offer code for the one that we want to use
    $query = "SELECT id FROM offers where code = '" . escape($offer_code) . "[" . $number . "]'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if there is a result, then there is already an offer with that code
    if (mysqli_num_rows($result) > 0) {
        return get_duplicate_number($offer_code, $number + 1);
        
    // else there is not an offer with that code already, so use that number
    } else {
        return $number;
    }
}
?>
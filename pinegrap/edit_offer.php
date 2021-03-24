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
$liveform = new liveform('edit_offer');

// get all offer actions (we will use this in several places below)
$query =
    "SELECT
        id,
        name
    FROM offer_actions
    ORDER BY name ASC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$offer_actions = mysqli_fetch_items($result);

if (!$_POST) {
    // get offer data
    $query = "SELECT * FROM offers WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $code = $row['code'];
    $description = $row['description'];
    $require_code = $row['require_code'];
    $status = $row['status'];
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $offer_rule_id = $row['offer_rule_id'];
    $upsell = $row['upsell'];
    $upsell_message = $row['upsell_message'];
    $upsell_trigger_subtotal = sprintf("%01.2lf", $row['upsell_trigger_subtotal'] / 100);
    $upsell_trigger_quantity = $row['upsell_trigger_quantity'];
    $upsell_action_button_label = $row['upsell_action_button_label'];
    $upsell_action_page_id = $row['upsell_action_page_id'];
    $scope = $row['scope'];
    $multiple_recipients = $row['multiple_recipients'];
    $only_apply_best_offer = $row['only_apply_best_offer'];
    
    // get selected offer actions for this offer
    $query =
        "SELECT
            offer_actions.id,
            offer_actions.name
        FROM offers_offer_actions_xref
        LEFT JOIN offer_actions ON offers_offer_actions_xref.offer_action_id = offer_actions.id
        WHERE offers_offer_actions_xref.offer_id = '" . escape($_GET['id']) . "'
        ORDER BY offer_actions.name ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $offer_actions_for_output = array();
    $selected_offer_action_ids = array();
    
    // loop through the selected offers in order to add them to arrays (we are doing this so that the selected actions appear first in the list)
    while ($row = mysqli_fetch_assoc($result)) {
        $offer_actions_for_output[] = $row;
        $selected_offer_action_ids[] = $row['id'];
    }
    
    // loop through all offer actions, in order to add unselected actions to array
    foreach ($offer_actions as $offer_action) {
        // if this offer action was not selected (i.e. has not already been added), then add it to array
        if (in_array($offer_action['id'], $selected_offer_action_ids) == FALSE) {
            $offer_actions_for_output[] = $offer_action;
        }
    }
    
    $output_offer_actions = '';
    
    // loop through actions in order to output check boxes for each one
    foreach ($offer_actions_for_output as $offer_action) {
        $checked = '';
        
        // if this action is selected for this offer, then check it
        if (in_array($offer_action['id'], $selected_offer_action_ids) == TRUE) {
            $checked = ' checked="checked"';
        }
        
        $output_offer_actions .= '<input type="checkbox" id="offer_action_' . $offer_action['id'] . '" name="offer_action_' . $offer_action['id'] . '" value="1"' . $checked . ' class="checkbox" /><label for="offer_action_' . $offer_action['id'] . '"> ' . h($offer_action['name']) . '</label><br />';
    }

    // prepare checked status for require code checkbox
    if ($require_code == 1) {
        $require_code_checked = ' checked="checked"';
    } else {
        $require_code_checked = '';
    }

    // prepare checked status for status radio buttons
    if ($status == 'enabled') {
        $status_enabled_checked = ' checked="checked"';
        $status_disabled_checked = '';
    } else {
        $status_enabled_checked = '';
        $status_disabled_checked = ' checked="checked"';
    }
    
    // prepare checked status for upsell checkbox
    if ($upsell == 1) {
        $upsell_checked = ' checked="checked"';

    } else {
        $upsell_checked = '';
        
        $upsell_message_row_style = 'display: none';
        $upsell_triggers_row_style = 'display: none';
        $upsell_trigger_subtotal_row_style = 'display: none';
        $upsell_and_or_row_style = 'display: none';
        $upsell_trigger_quantity_row_style = 'display: none';
        $upsell_action_button_label_row_style = 'display: none';
        $upsell_action_page_id_row_style = 'display: none';
    }
    
    // prepare checked status for scope radio buttons
    if ($scope == 'order') {
        $scope_order_checked = ' checked="checked"';
        $scope_recipient_checked = '';
        $multiple_recipients_row_style = 'display: none';
    } else {
        $scope_order_checked = '';
        $scope_recipient_checked = ' checked="checked"';
    }
    
    // prepare checked status for multiple recipients checkbox
    if ($multiple_recipients == 1) {
        $multiple_recipients_checked = ' checked="checked"';
    } else {
        $multiple_recipients_checked = '';
    }
    
    // prepare checked status for only apply best offer checkbox
    if ($only_apply_best_offer == 1) {
        $only_apply_best_offer_checked = ' checked="checked"';
    } else {
        $only_apply_best_offer_checked = '';
    }

    $output =
        output_header() . '
        ' . get_date_picker_format() . '
        <div id="subnav">
            <h1>' . h($code) . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Offer</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit an offer to calculate discounts based on a promotion code entered or a product combination found during checkout.</div>
            ' . $liveform->output_notices() . '
            <form name="form" action="edit_offer.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Offer Code for Redemption & Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Offer Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . h($code) . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Commerce Pages Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Message:</td>
                        <td><input type="text" name="description" size="80" maxlength="255" value="' . h($description) . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Terms & Conditions</h2></th>
                    </tr>
                    <tr>
                        <td>Offer Rule:</td>
                        <td><select name="offer_rule_id"><option value="">-Select-</option>' .  select_offer_rule($offer_rule_id) . '</select></td>
                    </tr>
                    <tr>
                        <td>Offer Actions:</td>
                        <td>
                            <div class="scrollable" style="max-height: 15em">
                                ' . $output_offer_actions . '
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Require Code:</td>
                        <td><input type="checkbox" name="require_code" value="1"' . $require_code_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Up-sell Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Display Up-sell Message:</td>
                        <td><input type="checkbox" name="upsell" id="upsell" value="1"' . $upsell_checked . ' class="checkbox" onclick="show_or_hide_upsell()" /></td>
                    </tr>
                    <tr id="upsell_message_row" style="' . $upsell_message_row_style . '">
                        <td style="padding-left: 20px">Up-sell Message:</td>
                        <td><input type="text" name="upsell_message" size="80" maxlength="255" value="' . h($upsell_message) . '" /></td>
                    </tr>
                    <tr id="upsell_triggers_row" style="' . $upsell_triggers_row_style . '">
                        <td style="padding-left: 20px">Triggers:</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="upsell_trigger_subtotal_row" style="' . $upsell_trigger_subtotal_row_style . '">
                        <td colspan="2" style="padding-left: 40px">Subtotal within $<input type="text" name="upsell_trigger_subtotal" value="' . $upsell_trigger_subtotal . '" size="5" /> of required subtotal.</td>
                    </tr>
                    <tr id="upsell_and_or_row" style="' . $upsell_and_or_row_style . '">
                        <td colspan="2" style="padding-left: 40px">and/or</td>
                    </tr>
                    <tr id="upsell_trigger_quantity_row" style="' . $upsell_trigger_quantity_row_style . '">
                        <td colspan="2" style="padding-left: 40px">Quantity within <input type="text" name="upsell_trigger_quantity" size="3" maxlength="10" value="' . $upsell_trigger_quantity . '" /> of required quantity.</td>
                    </tr>
                    <tr id="upsell_action_button_label_row" style="' . $upsell_action_button_label_row_style . '">
                        <td style="padding-left: 20px">Action Button Label:</td>
                        <td><input name="upsell_action_button_label" type="text" value="' . h($upsell_action_button_label) . '" maxlength="50" /></td>
                    </tr>
                    <tr id="upsell_action_page_id_row" style="' . $upsell_action_page_id_row_style . '">
                        <td style="padding-left: 20px">Action Page:</td>
                        <td><select name="upsell_action_page_id"><option value="">-Select-</option>' . select_page($upsell_action_page_id) . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Apply Offer to Order or each Recipient</h2></th>
                    </tr>
                    <tr>
                        <td>Scope:</td>
                        <td><input type="radio" name="scope" id="order" value="order"' . $scope_order_checked . ' class="radio" onclick="show_or_hide_multiple_recipients()" /><label for="order">Order</label> <input type="radio" name="scope" id="recipient" value="recipient"' . $scope_recipient_checked . ' class="radio" onclick="show_or_hide_multiple_recipients()" /><label for="recipient">Recipient</label></td>
                    </tr>
                    <tr id="multiple_recipients_row" style="' . $multiple_recipients_row_style . '">
                        <td>Allow offer to be applied<br />to multiple recipients:</td>
                        <td><input type="checkbox" name="multiple_recipients" value="1"' . $multiple_recipients_checked . ' class="checkbox" /> (only used if offer action adds a product)</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Availability</h2></th>
                    </tr>
                    <tr>
                        <td>Start Date:</td>
                        <td>
                            <input type="text" id="start_date" name="start_date" size="10" maxlength="10" value="' . prepare_form_data_for_output($start_date, 'date') . '" />
                            <script>
                                $("#start_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>End Date:</td>
                        <td>
                            <input type="text" id="end_date" name="end_date" size="10" maxlength="10" value="' . prepare_form_data_for_output($end_date, 'date') . '" />
                            <script>
                                $("#end_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><input type="radio" name="status" id="enabled" value="enabled"' . $status_enabled_checked . ' class="radio" /><label for="enabled">Enabled</label> <input type="radio" name="status" id="disabled" value="disabled"' . $status_disabled_checked . ' class="radio" /><label for="disabled">Disabled</label></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>For Offers that Share this Offer\'s Code</h2></th>
                    </tr>
                    <tr>
                        <td><label for="only_apply_best_offer">Only Apply Best Offer:</label></td>
                        <td><input type="checkbox" id="only_apply_best_offer" name="only_apply_best_offer" value="1"' . $only_apply_best_offer_checked . ' class="checkbox" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="duplicate" value="Duplicate" onclick="javascript:window.location.href=\'duplicate_offer.php?id=' . h(escape_javascript($_GET['id'])) . get_token_query_string_field() . '\';" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This offer will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;
    
    $liveform->clear_notices();

} else {
    validate_token_field();
    
    // delete records for selected offer actions (we do this regardless of whether we are deleting the offer or updating it)
    $query = "DELETE FROM offers_offer_actions_xref WHERE offer_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if offer was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete offer
        $query = "DELETE FROM offers WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('offer (' . $_POST['code'] . ') was deleted', $_SESSION['sessionusername']);
    // else offer was not selected for delete
    } else {
        // If the offer code is blank, then output error.
        if (trim($_POST['code']) == '') {
            output_error('Please enter an offer code. <a href="javascript:history.go(-1);">Go back</a>.');
        }
        
        // convert upsell trigger subtotal from dollars to cents
        $upsell_trigger_subtotal = $_POST['upsell_trigger_subtotal'] * 100;
        
        // update offer
        $query = "UPDATE offers SET
                    code = '" . escape($_POST['code']) . "',
                    description = '" . escape($_POST['description']) . "',
                    require_code = '" . escape($_POST['require_code']) . "',
                    status = '" . escape($_POST['status']) . "',
                    start_date = '" . escape(prepare_form_data_for_input($_POST['start_date'], 'date')) . "',
                    end_date = '" . escape(prepare_form_data_for_input($_POST['end_date'], 'date')) . "',
                    offer_rule_id = '" . escape($_POST['offer_rule_id']) . "',
                    upsell = '" . escape($_POST['upsell']) . "',
                    upsell_message = '" . escape($_POST['upsell_message']) . "',
                    upsell_trigger_subtotal = '" . escape($upsell_trigger_subtotal) . "',
                    upsell_trigger_quantity = '" . escape($_POST['upsell_trigger_quantity']) . "',
                    upsell_action_button_label = '" . escape($_POST['upsell_action_button_label']) . "',
                    upsell_action_page_id = '" . escape($_POST['upsell_action_page_id']) . "',
                    scope = '" . escape($_POST['scope']) . "',
                    multiple_recipients = '" . escape($_POST['multiple_recipients']) . "',
                    only_apply_best_offer = '" . escape($_POST['only_apply_best_offer']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through actions in order to add records to database for selected actions
        foreach ($offer_actions as $offer_action) {
            // if the action was checked, then insert record
            if ($_POST['offer_action_' . $offer_action['id']] == 1) {
                $query =
                    "INSERT INTO offers_offer_actions_xref (
                        offer_id,
                        offer_action_id)
                    VALUES (
                        '" . escape($_POST['id']) . "',
                        '" . $offer_action['id'] . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        log_activity('offer (' . $_POST['code'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view offers screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_offers.php');
}
?>
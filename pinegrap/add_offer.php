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
    $output_offer_actions = '';
    
    // loop through actions in order to output check boxes for each one
    foreach ($offer_actions as $offer_action) {
        $output_offer_actions .= '<input type="checkbox" id="offer_action_' . $offer_action['id'] . '" name="offer_action_' . $offer_action['id'] . '" value="1" class="checkbox" /><label for="offer_action_' . $offer_action['id'] . '"> ' . h($offer_action['name']) . '</label><br />';
    }

    if (DATE_FORMAT == 'month_day') {
        $output_start_date = date('n/j/Y');
        $output_end_date = '12/31/2099';
    } else {
        $output_start_date = date('j/n/Y');
        $output_end_date = '31/12/2099';
    }
    
    $output =
        output_header() . '
        ' . get_date_picker_format() . '
        <div id="subnav">
            <h1>[new offer]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Offer</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new offer to calculate discounts based on a promotion code entered or a product combination found during checkout.</div>
            <form name="form" action="add_offer.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Offer Code for Redemption & Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Offer Code:</td>
                        <td><input type="text" name="code" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Commerce Pages Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Message:</td>
                        <td><input type="text" name="description" size="80" maxlength="255" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Terms & Conditions</h2></th>
                    </tr>
                    <tr>
                        <td>Offer Rule:</td>
                        <td><select name="offer_rule_id"><option value="">-Select-</option>' .  select_offer_rule() . '</select></td>
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
                        <td><input type="checkbox" name="require_code" value="1" class="checkbox" checked="checked" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Up-sell Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Display Up-sell Message:</td>
                        <td><input type="checkbox" name="upsell" id="upsell" value="1" class="checkbox" onclick="show_or_hide_upsell()" /></td>
                    </tr>
                    <tr id="upsell_message_row" style="display: none">
                        <td style="padding-left: 20px">Up-sell Message:</td>
                        <td><input type="text" name="upsell_message" size="80" maxlength="255" /></td>
                    </tr>
                    <tr id="upsell_triggers_row" style="display: none">
                        <td style="padding-left: 20px">Triggers:</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="upsell_trigger_subtotal_row" style="display: none">
                        <td colspan="2" style="padding-left: 40px">Subtotal within $<input type="text" name="upsell_trigger_subtotal" size="5" /> of required subtotal.</td>
                    </tr>
                    <tr id="upsell_and_or_row" style="display: none">
                        <td colspan="2" style="padding-left: 40px">and/or</td>
                    </tr>
                    <tr id="upsell_trigger_quantity_row" style="display: none">
                        <td colspan="2" style="padding-left: 40px">Quantity within <input type="text" name="upsell_trigger_quantity" size="3" maxlength="10" /> of required quantity.</td>
                    </tr>
                    <tr id="upsell_action_button_label_row" style="display: none">
                        <td style="padding-left: 20px">Action Button Label:</td>
                        <td><input name="upsell_action_button_label" type="text" maxlength="50" /></td>
                    </tr>
                    <tr id="upsell_action_page_id_row" style="display: none">
                        <td style="padding-left: 20px">Action Page:</td>
                        <td><select name="upsell_action_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Apply Offer to Order or each Recipient</h2></th>
                    </tr>
                    <tr>
                        <td>Scope:</td>
                        <td><input type="radio" name="scope" id="order" value="order" checked="checked" class="radio" onclick="show_or_hide_multiple_recipients()" /><label for="order">Order</label> <input type="radio" name="scope" id="recipient" value="recipient" class="radio" onclick="show_or_hide_multiple_recipients()" /><label for="recipient">Recipient</label></td>
                    </tr>
                    <tr id="multiple_recipients_row" style="display: none">
                        <td>Allow offer to be applied<br />to multiple recipients:</td>
                        <td><input type="checkbox" name="multiple_recipients" value="1" class="checkbox" /> (only used if offer action adds a product)</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Offer Availability</h2></th>
                    </tr>
                    <tr>
                        <td>Start Date:</td>
                        <td>
                            <input type="text" id="start_date" name="start_date" value="' . $output_start_date . '" size="10" maxlength="10" />
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
                            <input type="text" id="end_date" name="end_date" value="' . $output_end_date . '" size="10" maxlength="10" />
                            <script>
                                $("#end_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><input type="radio" name="status" id="enabled" value="enabled" checked="checked" class="radio" /><label for="enabled">Enabled</label> <input type="radio" name="status" id="disabled" value="disabled" class="radio" /><label for="disabled">Disabled</label></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>For Offers that Share this Offer\'s Code</h2></th>
                    </tr>
                    <tr>
                        <td><label for="only_apply_best_offer">Only Apply Best Offer:</label></td>
                        <td><input type="checkbox" id="only_apply_best_offer" name="only_apply_best_offer" value="1" class="checkbox" checked="checked" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();

    // If the offer code is blank, then output error.
    if (trim($_POST['code']) == '') {
        output_error('Please enter an offer code. <a href="javascript:history.go(-1);">Go back</a>.');
    }
    
    // convert upsell trigger subtotal from dollars to cents
    $upsell_trigger_subtotal = $_POST['upsell_trigger_subtotal'] * 100;
    
    // create offer
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
                '" . escape($_POST['code']) . "',
                '" . escape($_POST['description']) . "',
                '" . escape($_POST['require_code']) . "',
                '" . escape($_POST['status']) . "',
                '" . escape(prepare_form_data_for_input($_POST['start_date'], 'date')) . "',
                '" . escape(prepare_form_data_for_input($_POST['end_date'], 'date')) . "',
                '" . escape($_POST['offer_rule_id']) . "',
                '" . escape($_POST['upsell']) . "',
                '" . escape($_POST['upsell_message']) . "',
                '" . escape($upsell_trigger_subtotal) . "',
                '" . escape($_POST['upsell_trigger_quantity']) . "',
                '" . escape($_POST['upsell_action_button_label']) . "',
                '" . escape($_POST['upsell_action_page_id']) . "',
                '" . escape($_POST['scope']) . "',
                '" . escape($_POST['multiple_recipients']) . "',
                '" . escape($_POST['only_apply_best_offer']) . "',
                " . $user['id'] . ",
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $offer_id = mysqli_insert_id(db::$con);
    
    // loop through actions in order to add records to database for selected actions
    foreach ($offer_actions as $offer_action) {
        // if the action was checked, then insert record
        if ($_POST['offer_action_' . $offer_action['id']] == 1) {
            $query =
                "INSERT INTO offers_offer_actions_xref (
                    offer_id,
                    offer_action_id)
                VALUES (
                    '" . $offer_id . "',
                    '" . $offer_action['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    log_activity('offer (' . $_POST['code'] . ') was created', $_SESSION['sessionusername']);

    // forward user to view offers screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_offers.php');
}
?>
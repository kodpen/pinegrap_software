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
$liveform = new liveform('edit_gift_card');

$gift_card = db_item(
    "SELECT
        gift_cards.id,
        gift_cards.code,
        gift_cards.amount,
        gift_cards.balance,
        gift_cards.expiration_date,
        gift_cards.notes,
        gift_cards.order_id,
        orders.order_number,
        gift_cards.from_name,
        user.user_id,
        contacts.id AS contact_id,
        gift_cards.recipient_email_address,
        gift_cards.message,
        gift_cards.delivery_date
    FROM gift_cards
    LEFT JOIN orders ON gift_cards.order_id = orders.id
    LEFT JOIN user ON orders.user_id = user.user_id
    LEFT JOIN contacts ON orders.contact_id = contacts.id
    WHERE gift_cards.id = '" . e($_REQUEST['id']) . "'");

// If the form has not just been submitted, then output form.
if (!$_POST) {
    $request_uri = get_request_uri();

    // If the form has not been submitted yet, then pre-populate fields with data.
    if ($liveform->field_in_session('id') == false) {
        $liveform->assign_field_value('balance', number_format($gift_card['balance'] / 100, 2));

        if ($gift_card['expiration_date'] != '0000-00-00') {
            $liveform->assign_field_value('expiration_date', prepare_form_data_for_output($gift_card['expiration_date'], 'date'));
        }

        $liveform->assign_field_value('notes', $gift_card['notes']);
    }

    // If this gift card has a balance and has not expired,
    // then use class that shows green color.
    if (
        ($gift_card['balance'])
        &&
        (
            ($gift_card['expiration_date'] == '0000-00-00')
            || ($gift_card['expiration_date'] >= date('Y-m-d'))
        )
    ) {
        $output_status_class = 'status_enabled';
    
    // Otherwise this gift card has expired, so use class that shows red color.
    } else {
        $output_status_class = 'status_disabled';
    }

    $output_order_info_rows = '';

    // If this gift card was created from an order, then output order info.
    if ($gift_card['order_id']) {
        $output_order_number = '';

        // If the order still exists (i.e. has not been deleted),
        // then output order number with link.
        if ($gift_card['order_number']) {
            $output_order_number = '<a href="view_order.php?id=' . $gift_card['order_id'] . '&amp;send_to=' . h(urlencode($request_uri)) . '">' . h($gift_card['order_number']) . '</a>';

        // Otherwise the order has been deleted, so output message.
        } else {
            $output_order_number = 'The order has been deleted.';
        }

        if ($gift_card['from_name'] != '') {
            $output_name = h($gift_card['from_name']);

        } else {
            $output_name = 'Anonymous';
        }

        // If we know the user that purchased the gift card,
        // then link name to that user.
        if ($gift_card['user_id'] != '') {
            $output_from = '<a href="edit_user.php?id=' . $gift_card['user_id'] . '&amp;send_to=' . h(urlencode($request_uri)) . '">' . $output_name . '</a>';

        // Otherwise if we know the contact that purchased the gift card,
        // then link name to that contact.
        } else if ($gift_card['contact_id'] != '') {
            $output_from = '<a href="edit_contact.php?id=' . $gift_card['contact_id'] . '&amp;send_to=' . h(urlencode($request_uri)) . '">' . $output_name . '</a>';

        // Otherwise we don't know the user or the contact
        // so just output the name without a link.
        } else {
            $output_from = $output_name;
        }

        $output_delivery_date = '';

        // If there is a recipient, then output the delivery date.
        if ($gift_card['recipient_email_address'] != '') {
            if ($gift_card['delivery_date'] == '0000-00-00') {
                $output_delivery_date = 'Immediate';

            } else {
                $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($gift_card['delivery_date']), 'type' => 'date'));
            }
        }

        $output_order_info_rows =
            '<tr>
                <td>Order #:</td>
                <td>' . $output_order_number . '</td>
            </tr>
            <tr>
                <td>From:</td>
                <td>' . $output_from . '</td>
            </tr>
            <tr>
                <td>Recipient:</td>
                <td><a href="mailto:' . h($gift_card['recipient_email_address']) . '">' . h($gift_card['recipient_email_address']) . '</a></td>
            </tr>
            <tr>
                <td style="vertical-align: top">Message:</td>
                <td>' . nl2br(h($gift_card['message'])) . '</td>
            </tr>
            <tr>
                <td style="white-space: nowrap">Delivery Date:</td>
                <td>' . $output_delivery_date . '</td>
            </tr>';

    // Otherwise, this gift card was created manually, so explain that.
    } else {
        $output_order_info_rows =
            '<tr>
                <td colspan="2">This gift card was created manually, so there is no order info.</td>
            </tr>';
    }

    $output_redemption_history = '';

    $redemptions = db_items(
        "SELECT
            orders.id AS order_id,
            orders.order_number,
            applied_gift_cards.amount,
            applied_gift_cards.new_balance,
            orders.order_date
        FROM applied_gift_cards
        LEFT JOIN orders ON applied_gift_cards.order_id = orders.id
        WHERE
            (applied_gift_cards.gift_card_id = '" . e($gift_card['id']) . "')
            AND (orders.status != 'incomplete')
        ORDER BY orders.order_date DESC");

    // If there is at least one redemption, then output them.
    if ($redemptions) {
        $output_redemption_history_rows = '';

        foreach ($redemptions as $redemption) {
            $output_redemption_history_rows .=
                '<tr>
                    <td><a href="view_order.php?id=' . $redemption['order_id'] . '&amp;send_to=' . h(urlencode($request_uri)) . '">' . h($redemption['order_number']) . '</a></td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($redemption['amount'] / 100, 2, '.', ',') . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($redemption['new_balance'] / 100, 2, '.', ',') . '</td>
                    <td>' . get_relative_time(array('timestamp' => $redemption['order_date'])) . '</td>
                </tr>';
        }

        $output_redemption_history =
            '<table cellpadding="4" class="order_details">
                <tr style="background-color: #dbdbdb">
                    <th>Order</th>
                    <th style="text-align: right">Amount</th>
                    <th style="text-align: right">Remaining Balance</th>
                    <th>Redeemed</th>
                </tr>
                ' . $output_redemption_history_rows . '
            </table>';

    } else {
        $output_redemption_history = 'This gift card has not been redeemed yet.';
    }
    
    echo
        output_header() . '
        <div id="subnav"></div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Gift Card</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Update the properties for this gift card and view the details.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'id', 'value' => $_GET['id'])) . '
                <table class="field">
                    <tr>
                        <td>Code:</td>
                        <td class="' . $output_status_class . '" style="font-size: 125%; font-weight: bold">' . output_gift_card_code($gift_card['code']) . '</td>
                    </tr>
                    <tr>
                        <td>Balance:</td>
                        <td>' . BASE_CURRENCY_SYMBOL . $liveform->output_field(array('type' => 'text', 'name' => 'balance', 'size' => '6')) . ' &nbsp;&nbsp;(Original Amount: ' . BASE_CURRENCY_SYMBOL . number_format($gift_card['amount'] / 100, 2) . ')</td>
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
                        <th colspan="2"><h2>Order Info</h2></th>
                    </tr>
                    ' . $output_order_info_rows . '
                    <tr>
                        <th colspan="2"><h2>Redemption History</h2></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            ' . $output_redemption_history . '
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This gift card will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted, so process it.
} else {

    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // If the user selected to delete this gift card, then delete it.
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        db("DELETE FROM gift_cards WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('gift card (' . output_gift_card_code($gift_card['code']) . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_gift_cards = new liveform('view_gift_cards');
        $liveform_view_gift_cards->add_notice('The gift card has been deleted.');

        $liveform->remove_form();

        go(PATH . SOFTWARE_DIRECTORY . '/view_gift_cards.php');
        
    // Otherwise the user selected to save the gift card, so save it.
    } else {
        $balance = $liveform->get_field_value('balance');
        $expiration_date = $liveform->get_field_value('expiration_date');
        $notes = $liveform->get_field_value('notes');

        // Remove commas from balance.
        $balance = str_replace(',', '', $balance);

        // If a balance was entered, and the value is not a number
        // greater than or equal to 0, then add error.
        if (
            ($balance != '')
            &&
            (
                (is_numeric($balance) == false)
                || ($balance < 0)
            )
        ) {
            $liveform->mark_error('balance', 'Please enter a valid balance.');
        }

        // If an expiration date was entered and it is not valid, then add error.
        if (($expiration_date != '') && (validate_date($expiration_date) == false)) {
            $liveform->mark_error('expiration_date', 'Please enter a valid expiration date.');
        }
        
        // If there is an error, forward user back to previous screen.
        if ($liveform->check_form_errors() == true) {
            go($_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
        }

        // Convert balance into cents.
        $balance = $balance * 100;
        
        // Update gift card properties.
        db(
            "UPDATE gift_cards
            SET
                balance = '" . e($balance) . "',
                expiration_date = '" . e(prepare_form_data_for_input($expiration_date, 'date')) . "',
                notes = '" . e($notes) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('gift card (' . output_gift_card_code($gift_card['code']) . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_gift_cards = new liveform('view_gift_cards');
        $liveform_view_gift_cards->add_notice('The gift card has been saved.');

        $liveform->remove_form();

        go(PATH . SOFTWARE_DIRECTORY . '/view_gift_cards.php');
    }
}
?>
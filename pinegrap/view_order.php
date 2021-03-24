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
$liveform = new liveform('view_order');

// if the form has not just been submitted, then output form
if (!$_POST) {
    $query = "SELECT
                orders.*,
                INET_NTOA(ip_address) AS ip_address,
                user.user_username AS username,
                contacts.id AS contact_id,
                contacts.first_name AS contact_first_name,
                contacts.last_name AS contact_last_name,
                contacts.email_address AS contact_email_address,
                contacts.member_id
             FROM orders
             LEFT JOIN user ON orders.user_id = user.user_id
             LEFT JOIN contacts ON orders.contact_id = contacts.id
             WHERE orders.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $status = $row['status'];
    $order_number = $row['order_number'];
    $order_date = get_absolute_time(array('timestamp' => $row['order_date']));
    $subtotal = number_format($row['subtotal'] / 100, 2, '.', ',');
    $discount = number_format($row['discount'] / 100, 2, '.', ',');
    $tax = number_format($row['tax'] / 100, 2, '.', ',');
    $shipping = number_format($row['shipping'] / 100, 2, '.', ',');
    $gift_card_discount = number_format($row['gift_card_discount'] / 100, 2, '.', ',');
    $surcharge = number_format($row['surcharge'] / 100, 2, '.', ',');
    $payment_installment = $row['payment_installment'];
    $installment_charges = number_format($row['installment_charges'] / 100, 2, '.', ',');
    $total = number_format($row['total'] / 100, 2, '.', ',');
    $commission = number_format($row['commission'] / 100, 2, '.', ',');
    $transaction_id = $row['transaction_id'];
    $authorization_code = $row['authorization_code'];
    $special_offer_code = $row['special_offer_code'];
    $referral_source_code = $row['referral_source_code'];
    $reference_code = $row['reference_code'];
    $tracking_code = $row['tracking_code'];
    $utm_source = $row['utm_source'];
    $utm_medium = $row['utm_medium'];
    $utm_campaign = $row['utm_campaign'];
    $utm_term = $row['utm_term'];
    $utm_content = $row['utm_content'];
    $affiliate_code = $row['affiliate_code'];
    $currency_code = $row['currency_code'];
    $http_referer = $row['http_referer'];
    $user_id = $row['user_id'];
    $username = $row['username'];
    $contact_id = $row['contact_id'];
    $contact_first_name = $row['contact_first_name'];
    $contact_last_name = $row['contact_last_name'];
    $contact_email_address = $row['contact_email_address'];
    $member_id = $row['member_id'];
    $billing_salutation = $row['billing_salutation'];
    $billing_first_name = $row['billing_first_name'];
    $billing_last_name = $row['billing_last_name'];
    $billing_email_address = $row['billing_email_address'];
    $billing_company = $row['billing_company'];
    $billing_address_1 = $row['billing_address_1'];
    $billing_address_2 = $row['billing_address_2'];
    $billing_city = $row['billing_city'];
    $billing_state = $row['billing_state'];
    $billing_country = $row['billing_country'];
    $billing_zip_code = $row['billing_zip_code'];
    $billing_address_verified = $row['billing_address_verified'];
    $billing_phone_number = $row['billing_phone_number'];
    $billing_fax_number = $row['billing_fax_number'];
    $custom_field_1 = $row['custom_field_1'];
    $custom_field_2 = $row['custom_field_2'];
    $po_number = $row['po_number'];
    $payment_method = $row['payment_method'];
    $ip_address = $row['ip_address'];

    $source = '';

    if ($tracking_code or $http_referer or $referral_source_code or $utm_source) {

        $source .=
            '<fieldset>
                <legend><strong>Source</strong></legend>
                <div style="padding: 10px">
                    <table>';

        if ($tracking_code) {
            $source .=
                '<tr>
                    <td><strong>Tracking Code:</strong></td>
                    <td>' . h($tracking_code) . '</td>
                </tr>';
        }

        if ($http_referer) {

            // if http referer is greater than 25 characters, then shorten text version
            if (mb_strlen($http_referer) > 25) {
                $http_referer_text = mb_substr($http_referer, 0, 25) . '...';
            } else {
                $http_referer_text = $http_referer;
            }

            $source .=
                '<tr>
                    <td>Referring URL:</td>
                    <td><a href="' . h(escape_url($http_referer)) . '" target="_blank">' . h($http_referer_text) . '</a></td>
                </tr>';
        }

        if ($referral_source_code) {
            $source .=
                '<tr>
                    <td>Referral Source:</td>
                    <td>' . h($referral_source_code) . '</td>
                </tr>';
        }

        if ($utm_source) {

            $source .=
                '<tr>
                    <th colspan="2" style="text-align: left; padding-top: 10px">UTM</th>
                </tr>
                <tr>
                    <td>Source:</td>
                    <td>' . h($utm_source) . '</td>
                </tr>';

            if ($utm_medium) {
                $source .=
                    '<tr>
                        <td>Medium:</td>
                        <td>' . h($utm_medium) . '</td>
                    </tr>';
            }

            if ($utm_campaign) {
                $source .=
                    '<tr>
                        <td>Campaign:</td>
                        <td>' . h($utm_campaign) . '</td>
                    </tr>';
            }

            if ($utm_term) {
                $source .=
                    '<tr>
                        <td>Term:</td>
                        <td>' . h($utm_term) . '</td>
                    </tr>';
            }

            if ($utm_content) {
                $source .=
                    '<tr>
                        <td>Content:</td>
                        <td>' . h($utm_content) . '</td>
                    </tr>';
            }
        }


        $source .=
            '        </table>
                </div>
            </fieldset>';
    }

    $fax = '';

    if ($billing_fax_number) {
        $fax =
            '<tr>
                <td>Fax:</td>
                <td>' . h($billing_fax_number) . '</td>
            </tr>';
    }

    if ($payment_method == 'Credit/Debit Card') {
        $card_type = $row['card_type'];
        $cardholder = $row['cardholder'];
        $card_number = $row['card_number'];
        
        // if the credit card number is encrypted
        if ((mb_substr($card_number, 0, 1) != '*') && (mb_strlen($card_number) > 16)) {
            // if encryption is enabled, then decrypt the credit card number
            if (
                (defined('ENCRYPTION_KEY') == TRUE)
                && (extension_loaded('mcrypt') == TRUE)
                && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
            ) {
                $card_number = decrypt_credit_card_number($card_number, ENCRYPTION_KEY);
                
                // if the credit card number is not numeric, then there was a decryption error
                if (is_numeric($card_number) == FALSE) {
                    $card_number = '[decryption error]';
                    
                // else the credit card number was decrypted successfully,
                // so if the user does not have access to view card data,
                // then protect the credit card number
                } else if (($user['role'] == 3) && ($user['view_card_data'] == FALSE)) {
                    $card_number = protect_credit_card_number($card_number);
                }
                
            // else encryption is disabled, so output error
            } else {
                $card_number = '[decryption error]';
            }
        }
        
        $expiration_month = $row['expiration_month'];
        $expiration_year = $row['expiration_year'];
        $card_verification_number = $row['card_verification_number'];
        
        // if the card verification number is not already protected,
        // and the user does not have access to view card data,
        // then protect it
        if (
            (mb_substr($card_verification_number, 0, 1) != '*')
            && (($user['role'] == 3) && ($user['view_card_data'] == FALSE))
        ) {
            $card_verification_number = protect_card_verification_number($card_verification_number);
        }
    }

    // if the billing address has been verified, then output "yes"
    if ($billing_address_verified == '1') {
        $billing_address_verified = 'Yes';

    // else it has not been verified so output "no"
    } else {
        $billing_address_verified = 'No';
    }

    if ($row['opt_in']) {
        $opt_in = 'Yes';
    } else {
        $opt_in = 'No';
    }

    if ($row['tax_exempt']) {
        $tax_exempt = 'Yes';
    } else {
        $tax_exempt = 'No';
    }

    $output_gift_card_discount_row = '';

    // if there is a gift card discount, then prepare to output row for it
    if ($gift_card_discount > 0) {
        // get applied gift cards in order to output them
        $query =
            "SELECT
                gift_card_id,
                code,
                amount,
                new_balance,
                givex,
                authorization_number
            FROM applied_gift_cards
            WHERE order_id = '" . escape($_GET['id']) . "'
            ORDER BY id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $applied_gift_cards = array();
        
        // loop through applied gift cards in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $applied_gift_cards[] = $row;
        }
        
        $output_gift_card_label_plural_suffix = '';
        
        // if there is more than 1 applied gift card, then prepare to output gift card label plural suffix
        if (count($applied_gift_cards) > 1) {
            $output_gift_card_label_plural_suffix = 's';
        }
        
        $output_gift_card_discount_row =
            '<tr>
                <td>Gift Card' . $output_gift_card_label_plural_suffix . ':</td>
                <td>-' . BASE_CURRENCY_SYMBOL . $gift_card_discount . '</td>
            </tr>';
    }

    $output_surcharge_row = '';

    // If there is a credit card surcharge, then output row for it.
    if ($surcharge > 0) {
        $output_surcharge_row =
            '<tr>
                <td>Surcharge:</td>
                <td>' . BASE_CURRENCY_SYMBOL . $surcharge . '</td>
            </tr>';
    }

    //if there is installment charge and payment installment((1) is no installment)
    $output_number_of_installment_row = '';
    $output_installment_charges_row = '';
    if(($installment_charges != 0)&&($payment_installment >= 2)){
        $output_number_of_installment_row =
            '<tr>
                <td>Number of Installments:</td>
                <td>' . $payment_installment . '</td>
            </tr>';
        $output_installment_charges_row =
            '<tr>
                <td>Installment Charge:</td>
                <td>' . BASE_CURRENCY_SYMBOL . $installment_charges . '</td>
            </tr>';
    }
    $output_payment_information = '';

    // if there was a payment method, then prepare to output payment information
    if ($payment_method != '') {
        $output_credit_debit_card_information = '';
        
        // if Credit/Debit Card payment method was used for order, then prepare to output values for that payment method
        if ($payment_method == 'Credit/Debit Card') {

            $cardholder_row = '';

            if ($cardholder) {
                $cardholder_row =
                    '<tr>
                        <td>Cardholder:</td>
                        <td>' . h($cardholder) . '</td>
                    </tr>';
            }

            $expiration_row = '';

            if ($expiration_month and $expiration_year) {
                $expiration_row =
                    '<tr>
                        <td>Expiration:</td>
                        <td>' . h($expiration_month . '/' . $expiration_year) . '</td>
                    </tr>';
            }

            $card_verification_number_row = '';

            if ($card_verification_number) {
                $card_verification_number_row =
                    '<tr>
                        <td>Verification Number:</td>
                        <td>' . h($card_verification_number) . '</td>
                    </tr>';
            }
            
            $output_credit_debit_card_information =
                '<tr>
                    <td>Card Type:</td>
                    <td>' . h($card_type) . '</td>
                </tr>
                ' . $cardholder_row . '
                <tr>
                    <td>Card Number:</td>
                    <td>' . $card_number . '</td>
                </tr>
                ' . $expiration_row . '
                ' . $card_verification_number_row;
            
        }

        $transaction_id_row = '';

        if ($transaction_id) {
            $transaction_id_row .=
                '<tr>
                    <td>Transaction ID:</td>
                    <td>' . h($transaction_id) . '</td>
                </tr>';
        }

        $authorization_code_row = '';

        if ($authorization_code) {
            $authorization_code_row .=
                '<tr>
                    <td>Authorization Code:</td>
                    <td>' . h($authorization_code) . '</td>
                </tr>';
        }
        
        $output_payment_information =
            '<fieldset style="margin-bottom: 15px;">
                <legend><strong>Payment Information</strong></legend>
                <div style="padding: 10px;">
                    <table>
                        <tr>
                            <td>Payment Method:</td>
                            <td>' . $payment_method . '</td>
                        </tr>
                        ' . $output_credit_debit_card_information . '
                        ' . $transaction_id_row . '
                        ' . $authorization_code_row . '
                    </table>
                </div>
            </fieldset>';
    }

    $output_user_row = '';

    // If this order has a user, then show user info.
    if ($username != '') {
        $output_user_row =
            '<tr>
                <td>User:</td>
                <td><a href="edit_user.php?id=' . $user_id . '">' . h($username) . '</a></td>
            </tr>';
    }

    $output_contact_row = '';

    // If this order has a contact, then show contact info.
    if ($contact_id != '') {

        $output_contact = '';
        
        // If there is a first name or last name, then output name.
        if (($contact_first_name != '') or ($contact_last_name != '')) {
            
            // If there is a first name, then start name with that.
            if ($contact_first_name != '') {
                $output_contact .= h($contact_first_name);
            }
            
            // If there is a last name, then add it to the name.
            if ($contact_last_name != '') {
                
                if ($output_contact != '') {
                    $output_contact .= ' ';
                }
                
                $output_contact .= h($contact_last_name);

            }
         
        // Otherwise, if there is an email address then use that.
        } else if ($contact_email_address != '') {
            $output_contact = h($contact_email_address);

        // Otherwise show ID.
        } else {
            $output_contact = $contact_id;
        }

        $output_contact_row =
            '<tr>
                <td>Contact:</td>
                <td><a href="edit_contact.php?id=' . $contact_id . '">' . $output_contact . '</a></td>
            </tr>';

    }

    $output_member_id_row = '';

    if ($member_id != '') {
        $output_member_id_row =
            '<tr>
                <td>' . h(MEMBER_ID_LABEL) . ':</td>
                <td>' . h($member_id) . '</td>
            </tr>';
    }

    // If we don't know the IP address for the order, then set it to empty string.
    if ($ip_address == '0.0.0.0') {
        $ip_address = '';
    }

    $output_ip_address_row = '';

    // If this order has an ip, then show ip.
    if ($ip_address != '') {
        $output_ip_address_row =
            '<tr>
                <td>IP Address:</td>
                <td>' . h($ip_address) . '</td>
            </tr>';
    }

    $output_affiliate = '';

    // If the affiliate program is enabled and this order had an affiliate code, prepare affiliate output.
    if (AFFILIATE_PROGRAM and $affiliate_code) {

        // get affiliate information from contact
        $query = "SELECT id, affiliate_name FROM contacts WHERE affiliate_code = '" . escape($affiliate_code) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if a contact was found for affiliate code, prepare affilate name with link to contact
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $affiliate_contact_id = $row['id'];
            $affiliate_name = $row['affiliate_name'];
            
            if ($affiliate_name) {
                $output_affiliate_name = '<a href="edit_contact.php?id=' . $affiliate_contact_id . '">' . h($affiliate_name) . '</a>';
            }
        }
        
        $output_affiliate =
            '<fieldset style="margin-bottom: 15px;">
                <legend><strong>Affiliate Information</strong></legend>
                <div style="padding: 10px;">
                    <table>
                        <tr>
                            <td>Affiliate Name:</td>
                            <td>' . $output_affiliate_name . '</td>
                        </tr>
                        <tr>
                            <td>Affiliate Code:</td>
                            <td>' . h($affiliate_code) . '</td>
                        </tr>
                        <tr>
                            <td>Commission:</td>
                            <td>' . BASE_CURRENCY_SYMBOL . $commission . '</td>
                        </tr>
                    </table>
                </div>
            </fieldset>';
    }

    $output_custom_billing_information = '';

    $output_custom_billing_form = get_submitted_form_content_without_form_fields(array('type' => 'custom_billing_form', 'order_id' => $_GET['id'], 'style' => 'padding: 10px'));

    if ($output_custom_billing_form != '') {
        $output_custom_billing_information =
            '<fieldset style="margin-bottom: 15px;">
                <legend><strong>Custom Billing Information</strong></legend>
                ' . $output_custom_billing_form . '
            </fieldset>';
    }

    // get all ship tos for order
    $query = "SELECT
                DISTINCT ship_to_id,
                ship_to_name,
                salutation,
                first_name,
                last_name,
                company,
                address_1,
                address_2,
                city,
                state,
                zip_code,
                country,
                address_verified,
                phone_number,
                address_type,
                arrival_date,
                arrival_date_code,
                ship_date,
                delivery_date,
                shipping_method_code,
                shipping_methods.id AS shipping_method_id,
                shipping_methods.name AS shipping_method_name,
                shipping_cost,
                packages
             FROM order_items
             LEFT JOIN ship_tos ON order_items.ship_to_id = ship_tos.id
             LEFT JOIN shipping_methods ON ship_tos.shipping_method_id = shipping_methods.id
             WHERE order_items.order_id = '" . escape($_GET['id']) . "'
             ORDER BY ship_to_id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $ship_tos = array();

    // assume that there are no real ship tos until we find out that there is one
    $ship_to_exists = false;

    // foreach ship to, add ship to to array
    while ($row = mysqli_fetch_assoc($result)) {
        $ship_tos[] = $row;
        
        // if this is a real ship to, remember that
        if ($row['ship_to_id'] > 0) {
            $ship_to_exists = true;
        }
    }
    
    // the save and cancel buttons will only be outputted if a real ship to exists
    $output_save_and_cancel_buttons = '';

    if ($ship_to_exists == true) {
        // get all custom shipping form data for this order
        $query =
            "SELECT
                ship_to_id,
                data,
                name,
                type
            FROM form_data
            WHERE
                (order_id = '" . escape($_GET['id']) . "')
                AND (ship_to_id != '0')
            ORDER BY id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // initialize array that will be used for storing custom shipping form data for ship tos
        $ship_tos_for_custom_fields = array();
        
        // initialize array that will be used for storing custom field names for custom shipping form data
        $custom_field_names = array();
        
        // loop through the fields in order to determine custom field names and add fields to ship tos array for custom shipping form data
        while ($field = mysqli_fetch_assoc($result)) {
            // if this field name has not been added to the custom field names array, then add it,
            // so we can keep track of all necessary field names
            if (in_array($field['name'], $custom_field_names) == FALSE) {
                $custom_field_names[] = $field['name'];
            }
            
            // if there is not already form data for this field in the ship tos array, then just add data to the array
            if ((isset($ship_tos_for_custom_fields[$field['ship_to_id']][$field['name']]) == FALSE) || ($ship_tos_for_custom_fields[$field['ship_to_id']][$field['name']]['data'] == '')) {
                $ship_tos_for_custom_fields[$field['ship_to_id']][$field['name']]['data'] = $field['data'];
                $ship_tos_for_custom_fields[$field['ship_to_id']][$field['name']]['type'] = $field['type'];
                
            // else there is already form data for this field, so this is probably a field that supports multiple values,
            // so just append this additional value
            } else {
                $ship_tos_for_custom_fields[$field['ship_to_id']][$field['name']]['data'] .= ', ' . $field['data'];
            }
        }
        
        $output_custom_field_headings = '';
        
        // loop through the custom field names in order to output headings for them
        foreach ($custom_field_names as $custom_field_name) {
            $output_custom_field_headings .= '<th>' . h($custom_field_name) . '</th>';
        }
        
        $output_colspan = 13 + count($custom_field_names);
        
        // loop through all ship tos
        foreach ($ship_tos as $key => $recipient) {
            // if ship to is a real ship to, prepare header with ship to information
            if ($ship_tos[$key]['ship_to_id'] > 0) {
                // if this shipping address is verified, then convert salutation and country to all uppercase
                if ($ship_tos[$key]['address_verified'] == 1) {
                    $ship_tos[$key]['salutation'] = mb_strtoupper($ship_tos[$key]['salutation']);
                    $ship_tos[$key]['country'] = mb_strtoupper($ship_tos[$key]['country']);
                }
                
                if ($ship_tos[$key]['salutation']) {
                    $name = $ship_tos[$key]['salutation'] . ' ' . $ship_tos[$key]['first_name'] . ' ' . $ship_tos[$key]['last_name'];
                } else {
                    $name = $ship_tos[$key]['first_name'] . ' ' . $ship_tos[$key]['last_name'];
                }
                
                $address = '';
                
                if ($ship_tos[$key]['address_1']) {
                    $address .= $ship_tos[$key]['address_1'] . ', ';
                }

                if ($ship_tos[$key]['address_2']) {
                    $address .= $ship_tos[$key]['address_2'] . ', ';
                }
                
                if ($ship_tos[$key]['city']) {
                    $address .= $ship_tos[$key]['city'] . ', ';
                }
                
                if ($ship_tos[$key]['state']) {
                    $address .= $ship_tos[$key]['state'] . ', ';
                }
                
                if ($ship_tos[$key]['zip_code']) {
                    $address .= $ship_tos[$key]['zip_code'] . ', ';
                }
                
                if ($ship_tos[$key]['country']) {
                    $address .= $ship_tos[$key]['country'];
                }
                
                $output_address_verified_check_mark = '';
                
                // if the address has been verified, then prepare to output check mark
                if ($ship_tos[$key]['address_verified'] == 1) {
                    $output_address_verified_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
                }

                $output_arrival_date = '';

                if ($ship_tos[$key]['arrival_date'] != '0000-00-00') {
                    $output_arrival_date = get_absolute_time(array('timestamp' => strtotime($ship_tos[$key]['arrival_date']), 'type' => 'date'));
                }

                $arrival_date_code = '';
                
                if ($ship_tos[$key]['arrival_date_code']) {
                    $arrival_date_code = ' (' . $ship_tos[$key]['arrival_date_code'] . ')';
                }

                $output_ship_date_field = '';
                $output_delivery_date_field = '';
                $output_shipping_tracking_numbers = '';
                $output_shipping_tracking_numbers_field = '';
                
                // If the order is complete, then output ship date and shipping tracking numbers.
                if ($status != 'incomplete') {
                    // If the form has not been submitted yet, and the ship date is not blank,
                    // then prefill ship date field.
                    if (($liveform->field_in_session('id') == false) && ($ship_tos[$key]['ship_date'] != '0000-00-00')) {
                        $liveform->assign_field_value('ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_ship_date', prepare_form_data_for_output($ship_tos[$key]['ship_date'], 'date'));
                    }

                    $output_ship_date_field =
                        $liveform->output_field(array('type' => 'text', 'id' => 'ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_ship_date', 'name' => 'ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_ship_date', 'size' => '10', 'maxlength' => '10')) . '
                        <script>
                            $("#ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_ship_date").datepicker({
                                dateFormat: date_picker_format
                            });
                        </script>';

                    // If the form has not been submitted yet, and the delivery date is not blank,
                    // then prefill delivery date field.
                    if (
                        !$liveform->field_in_session('id')
                        and $ship_tos[$key]['delivery_date'] != '0000-00-00'
                    ) {
                        $liveform->set('ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_delivery_date', prepare_form_data_for_output($ship_tos[$key]['delivery_date'], 'date'));
                    }

                    $output_delivery_date_field =
                        $liveform->output_field(array('type' => 'text', 'id' => 'ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_delivery_date', 'name' => 'ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_delivery_date', 'size' => '10', 'maxlength' => '10')) . '
                        <script>
                            $("#ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_delivery_date").datepicker({
                                dateFormat: date_picker_format
                            });
                        </script>';
 
                    $query =
                        "SELECT number
                        FROM shipping_tracking_numbers
                        WHERE ship_to_id = '" . $ship_tos[$key]['ship_to_id'] . "'
                        ORDER BY id ASC";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $shipping_tracking_numbers = mysqli_fetch_items($result);
                    
                    // loop through the shipping tracking numbers in order to output them
                    foreach ($shipping_tracking_numbers as $shipping_tracking_number) {
                        // if this is not the first shipping tracking number then add a comma and space for separation
                        if ($output_shipping_tracking_numbers != '') {
                            $output_shipping_tracking_numbers .= '<br />';
                        }
                        
                        $shipping_tracking_url = get_shipping_tracking_url($shipping_tracking_number['number'],$recipient['shipping_method_code']);
                        
                        // if a shipping tracking url was found, then output link
                        if ($shipping_tracking_url != '') {
                            $output_shipping_tracking_numbers .= '<a href="' . h($shipping_tracking_url) . '" target="_blank">' . h($shipping_tracking_number['number']) . '</a>';
                            
                        } else {
                            $output_shipping_tracking_numbers .= h($shipping_tracking_number['number']);
                        }
                    }
                    
                    // if there shipping tracking numbers then add container around them
                    if ($output_shipping_tracking_numbers != '') {
                        $output_shipping_tracking_numbers = '<div style="margin-bottom: .5em">' . $output_shipping_tracking_numbers . '</div>';
                    }
                    
                    // if the form has not been submitted yet, then prefill tracking number field with tracking numbers
                    if ($liveform->field_in_session('id') == FALSE) {
                        $shipping_tracking_numbers_for_field = '';
                        
                        // loop through the shipping tracking numbers in order to prepare value for field
                        foreach ($shipping_tracking_numbers as $shipping_tracking_number) {
                            // if this is not the first shipping tracking number then add a line break
                            if ($shipping_tracking_numbers_for_field != '') {
                                $shipping_tracking_numbers_for_field .= "\n";
                            }
                        
                            $shipping_tracking_numbers_for_field .= $shipping_tracking_number['number'];
                        }
                        
                        $liveform->assign_field_value('ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_tracking_numbers', $shipping_tracking_numbers_for_field);
                    }
                    
                    $output_shipping_tracking_numbers_field = $liveform->output_field(array('type'=>'textarea', 'name'=>'ship_to_id_' . $ship_tos[$key]['ship_to_id'] . '_tracking_numbers', 'style'=>'width: 175px; height: 50px'));
                }

                // If the shipping method still exists, then show name and code and link to it.
                if ($recipient['shipping_method_id']) {

                    $shipping_method =
                        '<a href="edit_shipping_method.php?id=' . h($recipient['shipping_method_id']) . '">' .
                            h($recipient['shipping_method_name']) . ' (' . h($recipient['shipping_method_code']) . ')' .
                        '</a>';

                // Otherwise the shipping method no longer exists, so just show code.
                } else {
                    $shipping_method = h($recipient['shipping_method_code']);
                }

                $packages = '';

                if ($ship_tos[$key]['packages']) {
                    $packages =
                        '<div style="margin-top: 10px; text-align: left">
                            Packages: ' . h($ship_tos[$key]['packages']) . '
                        </div>';
                }
                
                $output_custom_field_cells = '';
                
                // loop through the custom field names in order to output cells for values
                foreach ($custom_field_names as $custom_field_name) {
                    $data = $ship_tos_for_custom_fields[$ship_tos[$key]['ship_to_id']][$custom_field_name]['data'];
                    $type = $ship_tos_for_custom_fields[$ship_tos[$key]['ship_to_id']][$custom_field_name]['type'];
                    
                    // assume that we need to prepare data for HTML until we find out otherwise
                    $prepare_for_html = TRUE;
                    
                    // if type is html, then don't prepare data for html, because data is already html
                    if ($type == 'html') {
                        $prepare_for_html = FALSE;
                    }
                    
                    $output_custom_field_cells .= '<td>' . prepare_form_data_for_output($data, $type, $prepare_for_html) . '</td>';
                }
                
                $output_order_details .=
                    '<tr style="background-color: #dbdbdb">
                        <th>Ship to Name</th>
                        <th>Full Name</th>
                        <th>Company</th>
                        <th>Address</th>
                        <th>Address Type</th>
                        <th>Address Verified</th>
                        <th>Phone</th>
                        <th>Req. Arrival Date</th>
                        <th>Ship Date</th>
                        <th>Delivery Date</th>
                        <th>Shipping Method</th>
                        <th>Tracking Numbers</th>
                        <th>Shipping Cost</th>
                        ' . $output_custom_field_headings . '
                    </tr>
                    <tr>
                        <td>
                            <div>' . h($ship_tos[$key]['ship_to_name']) . '</div>
                            <div class="print_packing_slip" style="margin: .7em .5em .5em .5em"><a href="print_packing_slip.php?ship_to_id=' . $ship_tos[$key]['ship_to_id'] . '" onclick="window.open(\'print_packing_slip.php?ship_to_id=' . $ship_tos[$key]['ship_to_id'] . '\', \'\', \'width=750, height=600, resizable=1, scrollbars=1\'); return false;"><img src="images/icon_print.png" width="24" height="24" border="0" alt="Print Packing Slip" title="Print Packing Slip" /></a></div>
                        </td>
                        <td>' . h($name) . '</td>
                        <td>' . h($ship_tos[$key]['company']) . '</td>
                        <td>' . h($address) . '</td>
                        <td>' . ucwords($ship_tos[$key]['address_type']) . '</td>
                        <td style="text-align: center">' . $output_address_verified_check_mark . '</td>
                        <td>' . h($ship_tos[$key]['phone_number']) . '</td>
                        <td>' . $output_arrival_date . $arrival_date_code . '</td>
                        <td>' . $output_ship_date_field . '</td>
                        <td>' . $output_delivery_date_field . '</td>
                        <td>' . $shipping_method . '</td>
                        <td>
                            ' . $output_shipping_tracking_numbers . '
                            ' . $output_shipping_tracking_numbers_field . '
                        </td>
                        <td style="text-align: right">
                            ' . BASE_CURRENCY_SYMBOL . number_format($ship_tos[$key]['shipping_cost'] / 100, 2, '.', ',') . '
                            ' . $packages . '
                        </td>
                        ' . $output_custom_field_cells . '
                    </tr>';
            }
            
            if ($ship_tos[$key]['ship_to_id'] > 0) {
                $product_list_style = ' style="background-color: #eeeeee"';
            } else {
                $product_list_style = ' style="border: 0"';
            }

            $output_custom_field_1_heading = '';

            // If the first custom product field is active, then output heading for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                $output_custom_field_1_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '</th>';
            }

            $output_custom_field_2_heading = '';

            // If the second custom product field is active, then output heading for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                $output_custom_field_2_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '</th>';
            }

            $output_custom_field_3_heading = '';

            // If the third custom product field is active, then output heading for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                $output_custom_field_3_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '</th>';
            }

            $output_custom_field_4_heading = '';

            // If the fourth custom product field is active, then output heading for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                $output_custom_field_4_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '</th>';
            }
            
            $output_order_details .=
                '<tr>
                    <td colspan="' . $output_colspan . '"' . $product_list_style . '>
                        <table cellpadding="4" class="order_details">
                            <tr style="background-color: #dbdbdb">
                                <th>Product Image</th>
                                <th>Product ID</th>
                                <th>Short Description</th>
                                <th>Qty</th>
                                <th>Shipped Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Payment Period</th>
                                <th>Number of Payments</th>
                                <th>Start Date</th>
                                ' . $output_custom_field_1_heading . '
                                ' . $output_custom_field_2_heading . '
                                ' . $output_custom_field_3_heading . '
                                ' . $output_custom_field_4_heading . '
                            </tr>';
            
            // get all order items in order for this ship to
            $query = "SELECT
                        order_items.id,
                        order_items.product_name,
                        order_items.quantity,
                        order_items.price,
                        order_items.recurring_payment_period,
                        order_items.recurring_number_of_payments,
                        order_items.recurring_start_date,
                        order_items.calendar_event_id,
                        order_items.recurrence_number,
                        order_items.show_shipped_quantity,
                        order_items.shipped_quantity,
                        products.id as product_id,
                        products.image_name as image_name,
                        products.short_description,
                        products.custom_field_1,
                        products.custom_field_2,
                        products.custom_field_3,
                        products.custom_field_4
                     FROM order_items
                     LEFT JOIN products ON order_items.product_id = products.id
                     WHERE order_id = '" . escape($_GET['id']) . "' AND ship_to_id = '" . $ship_tos[$key]['ship_to_id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $order_items = array();
            
            // loop through order items in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $order_items[] = $row;
            }
            
            // loop through order items in order to output them
            foreach ($order_items as $order_item) {
                $order_item_id = $order_item['id'];
                $product_name = $order_item['product_name'];
                $image_name = $order_item['image_name'];
                $quantity = $order_item['quantity'];
                $product_price = $order_item['price'] / 100;
                $product_total = ($order_item['price'] * $quantity) / 100;
                $recurring_payment_period = $order_item['recurring_payment_period'];
                $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                $recurring_start_date = $order_item['recurring_start_date'];
                $calendar_event_id = $order_item['calendar_event_id'];
                $recurrence_number = $order_item['recurrence_number'];
                $show_shipped_quantity = $order_item['show_shipped_quantity'];
                $shipped_quantity = $order_item['shipped_quantity'];
                $product_id = $order_item['product_id'];
                $short_description = h($order_item['short_description']);
                
                $output_image = '';
                // if a product image_name found, output
                if ($image_name) {
                    $output_image = '<img style="width:50px;height:30px;-webkit-object-fit:cover;object-fit:cover;"  src="' .  PATH . $image_name . '"/>';
                } 

                // if a product was found, include link to product
                if ($product_id) {
                    $output_product_name = '<a href="edit_product.php?id=' . $product_id . '">' .  h($product_name) . '</a>';
                
                // else a product was not found, so do not include a link to product   
                } else {
                    $output_product_name = h($product_name);
                }
                
                // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to short description
                if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                    $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                    
                    $short_description .=
                        '<p>
                            ' . h($calendar_event['name']) . '<br />
                            ' . $calendar_event['date_and_time_range'] . '
                        </p>';
                }
                
                $output_shipped_quantity_field = '';
                
                // if the order is complete, then show shipped quantity field
                if ($status != 'incomplete') {
                    // if the form has not been submitted yet and show shipped quantity is enabled, then prefill shipped quantity field
                    if (
                        ($liveform->field_in_session('id') == FALSE)
                        && ($show_shipped_quantity == 1)
                    ) {
                        $liveform->assign_field_value('order_item_id_' . $order_item_id . '_shipped_quantity', $shipped_quantity);
                    }
                    
                    $output_shipped_quantity_field = $liveform->output_field(array('type'=>'text', 'name'=>'order_item_id_' . $order_item_id . '_shipped_quantity', 'size'=>'2', 'maxlength'=>'9'));
                }
                
                $output_recurring_payment_period = '';
                $output_recurring_number_of_payments = '';
                $output_recurring_start_date = '';
                
                // if order item is a recurring order item, then prepare 
                if ($recurring_payment_period != '') {
                    $output_recurring_payment_period = $recurring_payment_period;
                    
                    // if the number of payments is set to 0, then change value to [no limit]
                    if ($recurring_number_of_payments == 0) {
                        $output_recurring_number_of_payments = '[no limit]';
                        
                    // else the number of payments is greater than 0, so show value
                    } else {
                        $output_recurring_number_of_payments = number_format($recurring_number_of_payments);
                    }
                    
                    $output_recurring_start_date = get_absolute_time(array('timestamp' => strtotime($recurring_start_date), 'type' => 'date'));
                }

                $output_custom_field_1_cell = '';

                // If the first custom product field is active, then output cell for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                    $output_custom_field_1_cell = '<td>' . h($order_item['custom_field_1']) . '</td>';
                }

                $output_custom_field_2_cell = '';

                // If the second custom product field is active, then output cell for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                    $output_custom_field_2_cell = '<td>' . h($order_item['custom_field_2']) . '</td>';
                }

                $output_custom_field_3_cell = '';

                // If the third custom product field is active, then output cell for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                    $output_custom_field_3_cell = '<td>' . h($order_item['custom_field_3']) . '</td>';
                }

                $output_custom_field_4_cell = '';

                // If the fourth custom product field is active, then output cell for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                    $output_custom_field_4_cell = '<td>' . h($order_item['custom_field_4']) . '</td>';
                }

                $output_gift_cards = '';
                
                // get maximum quantity number, so we can determine how many gift cards there are for this order item
                $query = "SELECT MAX(quantity_number) as number_of_gift_cards FROM order_item_gift_cards WHERE order_item_id = '$order_item_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $number_of_gift_cards = $row['number_of_gift_cards'];
                
                // if there is a gift card for this order item, then prepare to output gift card data
                if ($number_of_gift_cards > 0) {
                    // create loop in order to output all gift cards
                    for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                        $output_top_margin = '';
                        
                        // if this is not the first gift card, then prepare to ouput top margin on fieldset
                        if ($quantity_number != 1) {
                            $output_top_margin = ' style="margin-top: 1em"';
                        }
                        
                        $output_legend_content = 'Gift Card';
                        
                        // if number of gift cards is greater than 1, then add quantity number to legend
                        if ($number_of_gift_cards > 1) {
                            $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
                        }
                        
                        $output_legend = '';
                        
                        // if the legend content is not blank, then output a legend
                        if ($output_legend_content != '') {
                            $output_legend = '<legend><strong>' . $output_legend_content . '</strong></legend>';
                        }

                        // Get gift card data from database.
                        $order_item_gift_card = db_item(
                            "SELECT
                                gift_cards.id,
                                gift_cards.code,
                                order_item_gift_cards.from_name,
                                order_item_gift_cards.recipient_email_address,
                                order_item_gift_cards.message,
                                order_item_gift_cards.delivery_date
                            FROM order_item_gift_cards
                            LEFT JOIN gift_cards ON ((order_item_gift_cards.order_item_id = gift_cards.order_item_id) AND (order_item_gift_cards.quantity_number = gift_cards.quantity_number))
                            WHERE
                                (order_item_gift_cards.order_item_id = '" . $order_item_id . "')
                                AND (order_item_gift_cards.quantity_number = '" . $quantity_number . "')");

                        $output_code = '';

                        if ($order_item_gift_card['id']) {
                            $output_code = '<a href="edit_gift_card.php?id=' . $order_item_gift_card['id'] . '">' . output_gift_card_code($order_item_gift_card['code']) . '</a>';
                        }

                        $output_delivery_date = '';

                        if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                            $output_delivery_date = 'Immediate';

                        } else {
                            $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($order_item_gift_card['delivery_date']), 'type' => 'date'));
                        }
                        
                        $output_gift_cards .=
                            '<fieldset' . $output_top_margin . '>
                                ' . $output_legend . '
                                <div style="padding: 0.7em">
                                    <table cellpadding="4" class="order_details">
                                        <tr>
                                            <td>Code:</td>
                                            <td>' . $output_code . '</td>
                                        </tr>
                                        <tr>
                                            <td>Amount:</td>
                                            <td>' . prepare_amount($product_price) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Recipient Email:</td>
                                            <td>' . h($order_item_gift_card['recipient_email_address']) . '</td>
                                        </tr>
                                        <tr>
                                            <td>From Name:</td>
                                            <td>' . h($order_item_gift_card['from_name']) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align: top">Message:</td>
                                            <td>' . nl2br(h($order_item_gift_card['message'])) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date:</td>
                                            <td>' . $output_delivery_date . '</td>
                                        </tr>
                                    </table>
                                </div>
                            </fieldset>';
                    }
                }

                // assume that there is not a form to output until we find out otherwse
                $output_forms = '';
                
                // get maximum quantity number, so we can determine how many product forms there are for this order item
                $query = "SELECT MAX(quantity_number) as number_of_forms FROM form_data WHERE order_item_id = '$order_item_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $number_of_forms = $row['number_of_forms'];
                
                // if there is a form for this order item, then prepare to output form
                if ($number_of_forms > 0) {
                    // create loop in order to output all forms
                    for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                        $output_top_margin = '';
                        
                        // if this is not the first form or there were gift cards, then prepare to ouput top margin on fieldset
                        if (($quantity_number != 1) || ($output_gift_cards != '')) {
                            $output_top_margin = ' style="margin-top: 1em"';
                        }
                        
                        $output_legend_content = 'Form';
                        
                        // if number of forms is greater than 1, then add quantity number to legend
                        if ($number_of_forms > 1) {
                            $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_forms . ')';
                        }
                        
                        $output_legend = '';
                        
                        // if the legend content is not blank, then output a legend
                        if ($output_legend_content != '') {
                            $output_legend = '<legend><strong>' . $output_legend_content . '</strong></legend>';
                        }
                        
                        $output_forms .=
                            '<fieldset' . $output_top_margin . '>
                                ' . $output_legend . '
                                <div style="padding: 0.7em">
                                    <table cellpadding="4" class="order_details">
                                        ' . get_submitted_product_form_content_without_form_fields($order_item_id, $quantity_number, 'backend') . '
                                    </table>
                                </div>
                            </fieldset>';
                    }
                }
                
                $output_form_row = '';
                
                // if there is a form to output, then prepare form row
                if (($output_forms != '') || ($output_gift_cards != '')) {
                    $colspan = 8;

                    // If there is an extra column for the first custom product field, the add one to the colspan.
                    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                        $colspan++;
                    }

                    // If there is an extra column for the second custom product field, the add one to the colspan.
                    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                        $colspan++;
                    }

                    // If there is an extra column for the third custom product field, the add one to the colspan.
                    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                        $colspan++;
                    }

                    // If there is an extra column for the fourth custom product field, the add one to the colspan.
                    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                        $colspan++;
                    }

                    $output_form_row =
                        '<tr style="background-color: #ffffff">
                            <td>&nbsp;</td>
                            <td colspan="' . $colspan . '">
                                <div style="padding: .7em">
                                    ' . $output_gift_cards . '
                                    ' . $output_forms . '
                                </div>
                            </td>
                        </tr>';
                }
                
                $output_order_details .=
                    '<tr style="background-color: #ffffff">
                        <td>' .  $output_image . '</td>
                        <td>' . $output_product_name . '</td>
                        <td>' . $short_description . '</td>
                        <td>' . $quantity . '</td>
                        <td>' . $output_shipped_quantity_field . '</td>
                        <td style="text-align: right">' . prepare_amount($product_price) . '</td>
                        <td style="text-align: right">' . prepare_amount($product_total) . '</td>
                        <td>' . $output_recurring_payment_period . '</td>
                        <td>' . $output_recurring_number_of_payments . '</td>
                        <td>' . $output_recurring_start_date . '</td>
                        ' . $output_custom_field_1_cell . '
                        ' . $output_custom_field_2_cell . '
                        ' . $output_custom_field_3_cell . '
                        ' . $output_custom_field_4_cell . '
                    </tr>
                    ' . $output_form_row;
            }
            
            $output_order_details .=
                '       </table>
                    </td>
                </tr>';
            
            // if this is not the last ship to, output empty row for spacing
            if ($key < (count($ship_tos) - 1)) {
                $output_order_details .=
                    '<tr>
                        <td colspan="' . $output_colspan . '" style="border: 0">&nbsp;</td>
                    </tr>';
            }
        }
        
        // if the order is complete, then output save and cancel buttons
        if ($status != 'incomplete') {
            $output_save_and_cancel_buttons = '<input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp';
        }

    // else a real ship to does not exist
    } else {
        $output_custom_field_1_heading = '';

        // If the first custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_field_1_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '</th>';
        }

        $output_custom_field_2_heading = '';

        // If the second custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_field_2_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '</th>';
        }

        $output_custom_field_3_heading = '';

        // If the third custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_field_3_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '</th>';
        }

        $output_custom_field_4_heading = '';

        // If the fourth custom product field is active, then output heading for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_field_4_heading = '<th>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '</th>';
        }

        $output_order_details .=
            '<tr style="background-color: #dbdbdb">
                <th>Product ID</th>
                <th>Short Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Payment Period</th>
                <th>Number of Payments</th>
                <th>Start Date</th>
                ' . $output_custom_field_1_heading . '
                ' . $output_custom_field_2_heading . '
                ' . $output_custom_field_3_heading . '
                ' . $output_custom_field_4_heading . '
            </tr>';
        
        // get all order items in order for this ship to
        $query = "SELECT
                    order_items.id,
                    order_items.product_name,
                    order_items.quantity,
                    order_items.price,
                    order_items.recurring_payment_period,
                    order_items.recurring_number_of_payments,
                    order_items.recurring_start_date,
                    order_items.calendar_event_id,
                    order_items.recurrence_number,
                    products.id as product_id,
                    products.short_description,
                    products.custom_field_1,
                    products.custom_field_2,
                    products.custom_field_3,
                    products.custom_field_4
                 FROM order_items
                 LEFT JOIN products ON order_items.product_id = products.id
                 WHERE order_id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $order_items = array();
        
        // loop through order items in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $order_items[] = $row;
        }
        
        // loop through order items in order to output them
        foreach ($order_items as $order_item) {
            $order_item_id = $order_item['id'];
            $product_name = $order_item['product_name'];
            $quantity = $order_item['quantity'];
            $product_price = $order_item['price'] / 100;
            $product_total = ($order_item['price'] * $quantity) / 100;
            $recurring_payment_period = $order_item['recurring_payment_period'];
            $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
            $recurring_start_date = $order_item['recurring_start_date'];
            $calendar_event_id = $order_item['calendar_event_id'];
            $recurrence_number = $order_item['recurrence_number'];
            $product_id = $order_item['product_id'];
            $short_description = h($order_item['short_description']);
            
            // if a product was found, include link to product
            if ($product_id) {
                $output_product_name = '<a href="edit_product.php?id=' . $product_id . '">' .  h($product_name) . '</a>';
            
            // else a product was not found, so do not include a link to product   
            } else {
                $output_product_name = h($product_name);
            }
            
            // if calendars is enabled and this order item is for a calendar event reservation, then add calendar event name and date and time range to short description
            if ((CALENDARS == TRUE) && ($calendar_event_id != 0)) {
                $calendar_event = get_calendar_event($calendar_event_id, $recurrence_number);
                
                $short_description .=
                    '<p>
                        ' . h($calendar_event['name']) . '<br />
                        ' . $calendar_event['date_and_time_range'] . '
                    </p>';
            }
            
            $output_recurring_payment_period = '';
            $output_recurring_number_of_payments = '';
            $output_recurring_start_date = '';
            
            // if order item is a recurring order item, then prepare 
            if ($recurring_payment_period != '') {
                $output_recurring_payment_period = $recurring_payment_period;
                
                // if the number of payments is set to 0, then change value to [no limit]
                if ($recurring_number_of_payments == 0) {
                    $output_recurring_number_of_payments = '[no limit]';
                    
                // else the number of payments is greater than 0, so show value
                } else {
                    $output_recurring_number_of_payments = number_format($recurring_number_of_payments);
                }
                
                $output_recurring_start_date = get_absolute_time(array('timestamp' => strtotime($recurring_start_date), 'type' => 'date'));
            }

            $output_custom_field_1_cell = '';

            // If the first custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                $output_custom_field_1_cell = '<td>' . h($order_item['custom_field_1']) . '</td>';
            }

            $output_custom_field_2_cell = '';

            // If the second custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                $output_custom_field_2_cell = '<td>' . h($order_item['custom_field_2']) . '</td>';
            }

            $output_custom_field_3_cell = '';

            // If the third custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                $output_custom_field_3_cell = '<td>' . h($order_item['custom_field_3']) . '</td>';
            }

            $output_custom_field_4_cell = '';

            // If the fourth custom product field is active, then output cell for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                $output_custom_field_4_cell = '<td>' . h($order_item['custom_field_4']) . '</td>';
            }

            $output_gift_cards = '';
            
            // get maximum quantity number, so we can determine how many gift cards there are for this order item
            $query = "SELECT MAX(quantity_number) as number_of_gift_cards FROM order_item_gift_cards WHERE order_item_id = '$order_item_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $number_of_gift_cards = $row['number_of_gift_cards'];
            
            // if there is a gift card for this order item, then prepare to output gift card data
            if ($number_of_gift_cards > 0) {
                // create loop in order to output all gift cards
                for ($quantity_number = 1; $quantity_number <= $number_of_gift_cards; $quantity_number++) {
                    $output_top_margin = '';
                    
                    // if this is not the first gift card, then prepare to ouput top margin on fieldset
                    if ($quantity_number != 1) {
                        $output_top_margin = ' style="margin-top: 1em"';
                    }
                    
                    $output_legend_content = 'Gift Card';
                    
                    // if number of gift cards is greater than 1, then add quantity number to legend
                    if ($number_of_gift_cards > 1) {
                        $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_gift_cards . ')';
                    }
                    
                    $output_legend = '';
                    
                    // if the legend content is not blank, then output a legend
                    if ($output_legend_content != '') {
                        $output_legend = '<legend><strong>' . $output_legend_content . '</strong></legend>';
                    }

                    // Get gift card data from database.
                    $order_item_gift_card = db_item(
                        "SELECT
                            gift_cards.id,
                            gift_cards.code,
                            order_item_gift_cards.from_name,
                            order_item_gift_cards.recipient_email_address,
                            order_item_gift_cards.message,
                            order_item_gift_cards.delivery_date
                        FROM order_item_gift_cards
                        LEFT JOIN gift_cards ON ((order_item_gift_cards.order_item_id = gift_cards.order_item_id) AND (order_item_gift_cards.quantity_number = gift_cards.quantity_number))
                        WHERE
                            (order_item_gift_cards.order_item_id = '" . $order_item_id . "')
                            AND (order_item_gift_cards.quantity_number = '" . $quantity_number . "')");

                    $output_code = '';

                    if ($order_item_gift_card['id']) {
                        $output_code = '<a href="edit_gift_card.php?id=' . $order_item_gift_card['id'] . '">' . output_gift_card_code($order_item_gift_card['code']) . '</a>';
                    }

                    $output_delivery_date = '';

                    if ($order_item_gift_card['delivery_date'] == '0000-00-00') {
                        $output_delivery_date = 'Immediate';

                    } else {
                        $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($order_item_gift_card['delivery_date']), 'type' => 'date'));
                    }
                    
                    $output_gift_cards .=
                        '<fieldset' . $output_top_margin . '>
                            ' . $output_legend . '
                            <div style="padding: 0.7em">
                                <table cellpadding="4" class="order_details">
                                    <tr>
                                        <td>Code:</td>
                                        <td>' . $output_code . '</td>
                                    </tr>
                                    <tr>
                                        <td>Amount:</td>
                                        <td>' . prepare_amount($product_price) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Recipient Email:</td>
                                        <td>' . h($order_item_gift_card['recipient_email_address']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>From Name:</td>
                                        <td>' . h($order_item_gift_card['from_name']) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top">Message:</td>
                                        <td>' . nl2br(h($order_item_gift_card['message'])) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Date:</td>
                                        <td>' . $output_delivery_date . '</td>
                                    </tr>
                                </table>
                            </div>
                        </fieldset>';
                }
            }
            
            // assume that there is not a form to output until we find out otherwse
            $output_forms = '';
            
            // get maximum quantity number, so we can determine how many product forms there are for this order item
            $query = "SELECT MAX(quantity_number) as number_of_forms FROM form_data WHERE order_item_id = '$order_item_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $number_of_forms = $row['number_of_forms'];
            
            // if there is a form for this order item, then prepare to output form
            if ($number_of_forms > 0) {
                // create loop in order to output all forms
                for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                    $output_top_margin = '';
                    
                    // if this is not the first form or there were gift cards, then prepare to ouput top margin on fieldset
                    if (($quantity_number != 1) || ($output_gift_cards != '')) {
                        $output_top_margin = ' style="margin-top: 1em"';
                    }
                    
                    $output_legend_content = 'Form';
                    
                    // if number of forms is greater than 1, then add quantity number to legend
                    if ($number_of_forms > 1) {
                        $output_legend_content .= ' (' . $quantity_number . ' of ' . $number_of_forms . ')';
                    }
                    
                    $output_legend = '';
                    
                    // if the legend content is not blank, then output a legend
                    if ($output_legend_content != '') {
                        $output_legend = '<legend><strong>' . $output_legend_content . '</strong></legend>';
                    }
                    
                    $output_forms .=
                        '<fieldset' . $output_top_margin . '>
                            ' . $output_legend . '
                            <div style="padding: 0.7em">
                                <table cellpadding="4" class="order_details">
                                    ' . get_submitted_product_form_content_without_form_fields($order_item_id, $quantity_number, 'backend') . '
                                </table>
                            </div>
                        </fieldset>';
                }
            }
            
            $output_form_row = '';
            
            // if there is a form to output, then prepare form row
            if (($output_forms != '') || ($output_gift_cards != '')) {
                $colspan = 7;

                // If there is an extra column for the first custom product field, the add one to the colspan.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                    $colspan++;
                }

                // If there is an extra column for the second custom product field, the add one to the colspan.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                    $colspan++;
                }

                // If there is an extra column for the third custom product field, the add one to the colspan.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                    $colspan++;
                }

                // If there is an extra column for the fourth custom product field, the add one to the colspan.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                    $colspan++;
                }

                $output_form_row =
                    '<tr style="background-color: #ffffff">
                        <td>&nbsp;</td>
                        <td colspan="' . $colspan . '">
                            <div style="padding: .7em">
                                ' . $output_gift_cards . '
                                ' . $output_forms . '
                            </div>
                        </td>
                    </tr>';
            }
            
            $output_order_details .=
                '<tr style="background-color: #ffffff">
                    <td>' . $output_product_name . '</td>
                    <td>' . $short_description . '</td>
                    <td>' . $quantity . '</td>
                    <td style="text-align: right">' . prepare_amount($product_price) . '</td>
                    <td style="text-align: right">' . prepare_amount($product_total) . '</td>
                    <td>' . $output_recurring_payment_period . '</td>
                    <td>' . $output_recurring_number_of_payments . '</td>
                    <td>' . $output_recurring_start_date . '</td>
                    ' . $output_custom_field_1_cell . '
                    ' . $output_custom_field_2_cell . '
                    ' . $output_custom_field_3_cell . '
                    ' . $output_custom_field_4_cell . '
                </tr>
                ' . $output_form_row;
        }
    }

    // if multi currency is enabled, output miscellaneous fieldset with currency information
    if (ECOMMERCE_MULTICURRENCY === true) {
        $currency_name_and_code = '';
        
        // if there is a currency code, then output it
        if ($currency_code != '') {
            $currency_name_and_code = get_currency_name_from_code($currency_code) . ' (' . $currency_code . ')';
        }
        
        $output_currency_code_row =
            '<tr>
                <td>Currency:</td>
                <td>' . h($currency_name_and_code) . '</td>
            </tr>';
        
    } else {
        $output_currency_code_row = '';
    }

    $output_applied_gift_cards = '';

    // if there is a gift card discount and there is at least one applied gift card, then prepare to output applied gift cards
    // this double check is not redundant, because there can be a situation where there is a discount with no gift cards if there was an error when the gift card transaction was submitted
    if (($gift_card_discount > 0)  && (count($applied_gift_cards) > 0)) {
        // loop through applied gift cards in order to prepare to output them
        foreach ($applied_gift_cards as $applied_gift_card) {
            if ($applied_gift_card['givex'] == 0) {
                $output_gift_card_code = '<a href="edit_gift_card.php?id=' . $applied_gift_card['gift_card_id'] . '">' . output_gift_card_code($applied_gift_card['code']) . '</a>';
            } else {
                $output_gift_card_code = h($applied_gift_card['code']);
            }

            $output_applied_gift_card_rows .=
                '<tr>
                    <td>' . $output_gift_card_code . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($applied_gift_card['amount'] / 100, 2, '.', ',') . '</td>
                    <td>' . h($applied_gift_card['authorization_number']) . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($applied_gift_card['new_balance'] / 100, 2, '.', ',') . '</td>
                </tr>';
        }
        
        $output_applied_gift_cards =
            '<fieldset style="margin-bottom: 1em">
                <legend><strong>Applied Gift Cards</strong></legend>
                <div style="padding: 10px;">
                    <table cellpadding="4" class="order_details">
                        <tr style="background-color: #dbdbdb">
                            <th>Code</th>
                            <th>Amount</th>
                            <th>Givex Auth #</th>
                            <th>Remaining Balance</th>
                        </tr>
                        ' . $output_applied_gift_card_rows . '
                    </table>
                </div>
            </fieldset>';
    }


	// Get Payment Gateway for button bar output
	$output_button_bar = '';
	if(ECOMMERCE_PAYMENT_GATEWAY == 'Iyzipay'){

	
		if($transaction_id){
			// if test or live mode for iyzipay gateway. 
			if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
				$output_button_bar = '
					<div id="button_bar">
						<a href="#" onclick="window.open(\'https://sandbox-merchant.iyzipay.com/transactions/' . $transaction_id . '\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\');">View Order: ' . ECOMMERCE_PAYMENT_GATEWAY . '</a>
					</div>';
			}else {
				$output_button_bar = '
					<div id="button_bar">
						<a href="#" onclick="window.open(\'https://merchant.iyzipay.com/transactions/' . $transaction_id . '\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\');">View Order: ' . ECOMMERCE_PAYMENT_GATEWAY . '</a>
					</div>';
			}
		}else{
			// if test or live mode for iyzipay gateway. 
			if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
				$output_button_bar = '
					<div id="button_bar">
						<a href="#" onclick="window.open(\'https://sandbox-merchant.iyzipay.com/dashboard\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\');">View Orders: ' . ECOMMERCE_PAYMENT_GATEWAY . '</a>
					</div>';
			}else {
				$output_button_bar = '
					<div id="button_bar">
						<a href="#" onclick="window.open(\'https://merchant.iyzipay.com/dashboard\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\');">View Orders: ' . ECOMMERCE_PAYMENT_GATEWAY . '</a>
					</div>';
			}
		}

	}


    echo
        output_header() . '
        ' . get_date_picker_format() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
        </div>
		' . $output_button_bar . '
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>View Order</h1>
            <div class="subheading">View or <a href="#" onclick="window.print(); return false">print</a> the details of this order and update shipping information.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>$_GET['send_to'])) . '
                <table style="margin-bottom: 10px">
                    <tr>
                        <td style="vertical-align: top; padding-right: 20px">
                            <fieldset style="margin-bottom: 15px;">
                                <legend><strong>Order Information</strong></legend>
                                <div style="padding: 10px;">
                                    <table>
                                        <tr>
                                            <td>Status:</td>
                                            <td>' . h(ucwords($status)) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Order Number:</td>
                                            <td><strong style="font-size: 120%">' . $order_number . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Order Date:</td>
                                            <td>' . $order_date . '</td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td>' . prepare_amount($subtotal) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Discount:</td>
                                            <td>-' . BASE_CURRENCY_SYMBOL . $discount . '</td>
                                        </tr>
                                        <tr>
                                            <td>Tax:</td>
                                            <td>' . BASE_CURRENCY_SYMBOL . $tax . '</td>
                                        </tr>
                                        <tr>
                                            <td>Shipping:</td>
                                            <td>' . BASE_CURRENCY_SYMBOL . $shipping . '</td>
                                        </tr>
                                        ' . $output_gift_card_discount_row . '
                                        ' . $output_surcharge_row . '
                                        ' .  $output_number_of_installment_row . '
                                        ' . $output_installment_charges_row . '
                                        <tr>
                                            <td>Total:</td>
                                            <td><strong style="font-size: 120%">' . prepare_amount($total) . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Special Offer Code:</td>
                                            <td>' . $special_offer_code . '</td>
                                        </tr>
                                        <tr>
                                            <td>Reference Code:</td>
                                            <td>' . $reference_code . '</td>
                                        </tr>
                                        ' . $output_currency_code_row . '
                                    </table>
                                </div>
                            </fieldset>
                            ' . $source . '
                        </td>
                        <td style="vertical-align: top; padding-right: 20px">
                            <fieldset style="margin-bottom: 15px;">
                                <legend><strong>Billing Information</strong></legend>
                                <div style="padding: 10px;">
                                    <table>
                                        <tr>
                                            <td>Custom Field #1:</td>
                                            <td>' . h($custom_field_1) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Custom Field #2:</td>
                                            <td>' . h($custom_field_2) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Salutation:</td>
                                            <td>' . h($billing_salutation) . '</td>
                                        </tr>
                                        <tr>
                                            <td>First Name:</td>
                                            <td>' . h($billing_first_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Last Name:</td>
                                            <td>' . h($billing_last_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Company:</td>
                                            <td>' . h($billing_company) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Address 1:</td>
                                            <td>' . h($billing_address_1) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Address 2:</td>
                                            <td>' . h($billing_address_2) . '</td>
                                        </tr>
                                        <tr>
                                            <td>City:</td>
                                            <td>' . h($billing_city) . '</td>
                                        </tr>
                                        <tr>
                                            <td>State:</td>
                                            <td>' . h($billing_state) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Country:</td>
                                            <td>' . h($billing_country) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Zip Code:</td>
                                            <td>' . h($billing_zip_code) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Address Verified:</td>
                                            <td>' . $billing_address_verified . '</td>
                                        </tr>
                                        <tr>
                                            <td>Phone:</td>
                                            <td>' . h($billing_phone_number) . '</td>
                                        </tr>
                                        ' . $fax . '
                                        <tr>
                                            <td>Email:</td>
                                            <td>' . h($billing_email_address) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Opt-In:</td>
                                            <td>' . $opt_in . '</td>
                                        </tr>
                                        <tr>
                                            <td>PO Number:</td>
                                            <td>' . h($po_number) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Tax-Exempt:</td>
                                            <td>' . $tax_exempt . '</td>
                                        </tr>
                                    </table>
                                </div>
                            </fieldset>
                        </td>
                        <td style="vertical-align: top">
                            ' . $output_payment_information . '

                            <fieldset style="margin-bottom: 15px">
                                <legend><strong>Customer Information</strong></legend>
                                <div style="padding: 10px">
                                    <table>
                                        ' . $output_user_row . '
                                        ' . $output_contact_row . '
                                        ' . $output_member_id_row . '
                                        ' . $output_ip_address_row . '
                                    </table>
                                </div>
                            </fieldset>

                            ' . $output_applied_gift_cards . '
                            ' . $output_affiliate . '
                            ' . $output_custom_billing_information . '
                        </td>
                    </tr>
                </table>
                <fieldset style="margin-bottom: 1em">
                    <legend><strong>Order Details</strong></legend>
                    <div style="padding: 10px;">
                        <table cellpadding="4" class="order_details">
                            ' . $output_order_details . '
                        </table>
                    </div>
                </fieldset>
                <div class="buttons">
                    ' . $output_save_and_cancel_buttons . '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This order will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
// else the form has been submitted
} else {
    
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // if the user selected to delete this order, then delete it
    if ($liveform->get_field_value('submit_delete') == 'Delete') {

        $order['id'] = $liveform->get('id');

        require_once(dirname(__FILE__) . '/delete_order.php');

        $response = delete_order(array('order' => $order));

        if ($response['status'] == 'error') {
            output_error(h($response['message']));
        }
        
        // if the user is going to be sent to the view order screen, then prepare notice
        if (mb_substr($liveform->get_field_value('send_to'), -15) == 'view_orders.php') {
            $liveform_view_orders = new liveform('view_orders');
            $liveform_view_orders->add_notice('The order has been deleted.');
        }

        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
        
    // else the user selected to save the order
    } else {

        // Prepare all info in $order array that needs to be updated, so we can pass that to the
        // update_order() function.

        $order = array();
        $order['id'] = $liveform->get('id');

        $order['recipients'] = db_items(
            "SELECT id
            FROM ship_tos
            WHERE order_id = '" . e($liveform->get('id')) . "'");

        // If there are recipients in this order, then loop through them in order to prepare data.
        if (is_array($order['recipients'])) {

            foreach ($order['recipients'] as $key => $recipient) {

                $recipient['ship_date'] = prepare_form_data_for_input($liveform->get('ship_to_id_' . $recipient['id'] . '_ship_date'), 'date');

                $recipient['delivery_date'] = prepare_form_data_for_input($liveform->get('ship_to_id_' . $recipient['id'] . '_delivery_date'), 'date');

                $recipient['tracking_numbers'] = array();

                if ($liveform->get('ship_to_id_' . $recipient['id'] . '_tracking_numbers')) {

                    $tracking_numbers = explode("\n", $liveform->get('ship_to_id_' . $recipient['id'] . '_tracking_numbers'));

                    foreach ($tracking_numbers as $tracking_number) {
                        if ($tracking_number) {
                            $recipient['tracking_numbers'][] = $tracking_number;
                        }
                    }
                }

                $recipient['items'] = db_items(
                    "SELECT id
                    FROM order_items
                    WHERE ship_to_id = '" . e($recipient['id']) . "'");

                // Loop through order items in order to prepare shipped quantity.
                foreach ($recipient['items'] as $item_key => $item) {

                    $item['shipped_quantity'] =
                        $liveform->get('order_item_id_' . $item['id'] . '_shipped_quantity');

                    $recipient['items'][$item_key] = $item;
                }

                $order['recipients'][$key] = $recipient;
            }
        }

        require_once(dirname(__FILE__) . '/update_order.php');

        $response = update_order(array('order' => $order));

        if ($response['status'] == 'error') {
            output_error(h($response['message']));
        }
        
        // if the user is going to be sent to the view order screen, then prepare notice
        if (mb_substr($liveform->get_field_value('send_to'), -15) == 'view_orders.php') {
            $liveform_view_orders = new liveform('view_orders');
            $liveform_view_orders->add_notice('The order has been saved.');
        }
        
        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
    }
    
    $liveform->remove_form();
    exit();
}
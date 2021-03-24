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

require('init.php');

// if credit/debit card payment method is enabled and payment gateway is PayPal Payments Pro, then check status of recurring profiles
if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')) {
    // if cURL is not installed, then output error
    if (function_exists('curl_init') == false) {
        $error_message = 'The recurring payment job failed.  This website cannot communicate with the payment gateway.  The administrator of this website should install cURL.';
        log_activity($error_message, 'UNKNOWN');
        exit($error_message);
    }
    
    // if the payment gateway mode is set to test, set the payment gatway host to the sandbox posting url.
    if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
        $payment_gateway_host = 'https://api-3t.sandbox.paypal.com/nvp';
        
    // else, the payment gameway mode is set to live, so use the live payment gateway host.
    } else {
        $payment_gateway_host = 'https://api-3t.paypal.com/nvp';
    }
    
    // get current date
    $current_date = date('Y-m-d');
    
    // get previous date
    $previous_date = date('Y-m-d', time() - 86400);
    
    // get enabled recurring profiles, so we can check if recurring profiles are still enabled
    $query =
        "SELECT
            order_items.id as order_item_id,
            order_items.recurring_profile_id as id,
            products.recurring_profile_disabled_perform_actions,
            products.recurring_profile_disabled_expire_membership,
            products.recurring_profile_disabled_revoke_private_access,
            products.recurring_profile_disabled_email,
            products.recurring_profile_disabled_email_subject,
            products.recurring_profile_disabled_email_page_id,
            products.grant_private_access,
            products.private_folder,
            products.send_to_page as product_send_to_page_id,
            user.user_id,
            user.user_email as email_address,
            user.user_home as user_send_to_page_id,
            contacts.id as contact_id,
            contacts.member_id,
            contacts.expiration_date
        FROM order_items
        LEFT JOIN products ON order_items.product_id = products.id
        LEFT JOIN orders ON order_items.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts ON user.user_contact = contacts.id
        WHERE order_items.recurring_profile_enabled = '1'";
    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
    
    $recurring_profiles = array();
    
    // loop through recurring profiles in order to add them to array
    while ($row = mysqli_fetch_assoc($result)) {
        $recurring_profiles[] = $row;
    }
    
    // initialize variables for counters for log
    $number_of_expired_memberships = 0;
    $number_of_revoked_private_access = 0;
    $number_of_emails_sent = 0;
    
    // loop through recurring profiles in order to check if they are still enabled
    foreach ($recurring_profiles as $recurring_profile) {
        // Setup the transaction values
        $transaction_values = array(
            'METHOD' => 'GetRecurringPaymentsProfileDetails',
            'VERSION' => '50.0',
            'PWD' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_PASSWORD,
            'USER' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_USERNAME,
            'SIGNATURE' => ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_SIGNATURE,
            'PROFILEID' => $recurring_profile['id']);
        
        $post_data = '';
        
        // Place the transaction values into the correct format
        foreach ($transaction_values as $name => $value) {
            if ($post_data) {
                $post_data .= '&';
            }
            
            $value = str_replace('"', '', $value);
            
            $post_data .= $name . '=' . urlencode($value);
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $payment_gateway_host);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        // if there is a proxy address, then send cURL request through proxy
        if (PROXY_ADDRESS != '') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
        }
        
        $response_data = curl_exec($ch);
        
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        parse_str($response_data, $response);
        
        // if there was a response,
        // and the profile ID is invalid or the status is cancelled, suspended, or expired,
        // then disable recurring profile in software
        if (
            ($response)
            &&
            (
                ($response['L_ERRORCODE0'] == '11552')
                || ($response['STATUS'] == 'CancelledProfile')
                || ($response['STATUS'] == 'Cancelled')
                || ($response['STATUS'] == 'SuspendedProfile')
                || ($response['STATUS'] == 'Suspended')
                || ($response['STATUS'] == 'ExpiredProfile')
                || ($response['STATUS'] == 'Expired')
            )
        ) {
            $query = "UPDATE order_items SET recurring_profile_enabled = '0' WHERE id = '" . $recurring_profile['order_item_id'] . "'";
            $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
            
            // if actions should be performed
            if ($recurring_profile['recurring_profile_disabled_perform_actions'] == 1) {
                // if membership should be expired,
                // and a contact was found
                // and there is a member id
                // and membership has not already been expired
                // then expire membership, by setting expiration date to previous date
                if (
                    ($recurring_profile['recurring_profile_disabled_expire_membership'] == 1)
                    && ($recurring_profile['contact_id'])
                    && ($recurring_profile['member_id'] != '')
                    &&
                    (
                        ($recurring_profile['expiration_date'] == '0000-00-00')
                        || ($recurring_profile['expiration_date'] >= $current_date)
                    )
                ) {
                    $query = "UPDATE contacts SET expiration_date = '" . $previous_date . "' WHERE id = '" . $recurring_profile['contact_id'] . "'";
                    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
                    
                    // update number of expired memberships
                    $number_of_expired_memberships++;
                }
                
                // if product grants private access,
                // and private access should be revoked,
                // and a user was found
                // then revoke private access
                if (
                    ($recurring_profile['grant_private_access'] == 1)
                    && ($recurring_profile['recurring_profile_disabled_revoke_private_access'] == 1)
                    && ($recurring_profile['user_id'])
                ) {
                    // if private folder is set, then revoke private access
                    if ($recurring_profile['private_folder']) {
                        $query =
                            "UPDATE aclfolder
                            SET aclfolder_rights = '0'
                            WHERE
                                (aclfolder_user = '" . $recurring_profile['user_id'] . "')
                                && (aclfolder_folder = '" . $recurring_profile['private_folder'] . "')
                                && (aclfolder_rights = '1')";
                        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
                        
                        // update number of revoked private access
                        $number_of_revoked_private_access++;
                    }
                    
                    // if the user has a send to page,
                    // and the user's send to page is set to the product's send to page,
                    // then remove send to page for user
                    if (
                        ($recurring_profile['user_send_to_page_id'])
                        && ($recurring_profile['user_send_to_page_id'] == $recurring_profile['product_send_to_page_id'])
                    ) {
                        $query = "UPDATE user SET user_home = '0' WHERE user_id = '" . $recurring_profile['user_id'] . "'";
                        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
                    }
                }
                
                // if e-mail should be sent to customer,
                // and an e-mail page is selected for product,
                // and an e-mail address was found for the user
                // then send e-mail to customer
                if (
                    ($recurring_profile['recurring_profile_disabled_email'] == 1)
                    && ($recurring_profile['recurring_profile_disabled_email_page_id'])
                    && ($recurring_profile['email_address'])
                ) {

                    require_once(dirname(__FILE__) . '/get_page_content.php');
                    
                    $body = get_page_content($recurring_profile['recurring_profile_disabled_email_page_id'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);
                    
                    email(array(
                        'to' => $recurring_profile['email_address'],
                        'bcc' => ECOMMERCE_EMAIL_ADDRESS,
                        'from_name' => ORGANIZATION_NAME,
                        'from_email_address' => EMAIL_ADDRESS,
                        'subject' => $recurring_profile['recurring_profile_disabled_email_subject'],
                        'format' => 'html',
                        'body' => $body));
                    
                    // update number of e-mail sent
                    $number_of_emails_sent++;

                }
            }
        }
    }
    
    // if recurring payment job did something, then log activity
    if (($number_of_expired_memberships > 0) || ($number_of_revoked_private_access > 0) || ($number_of_emails_sent > 0)) {
        log_activity('recurring payment job expired membership for ' . $number_of_expired_memberships . ' user(s), revoked private access for ' . $number_of_revoked_private_access . ' user(s), and sent e-mail to ' . $number_of_emails_sent . ' user(s)', 'UNKNOWN');
    }
}
?>
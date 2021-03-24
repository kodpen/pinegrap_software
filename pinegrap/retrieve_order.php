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

// Used by shopping cart and express order pages to retrieve order for customer if there is a
// reference code in the URL.

function retrieve_order($request) {

    $order_label = $request['order_label'];

    if (!$order_label) {
        $order_label = 'cart';
    }

    if (!$_GET['r'] and !$_GET['reference_code']) {
        $message = 'The ' . $order_label . ' could not be retrieved, because there is no reference code in the URL.';
        log_activity($message);
        return error_response($message);
    }

    if ($_GET['r']) {
        $reference_code = $_GET['r'];
    } else {
        $reference_code = $_GET['reference_code'];
    }
    
    $order = db_item("
        SELECT id, status FROM orders WHERE reference_code = '" . e($reference_code) . "'");

    if (!$order) {
        $message = 'The ' . $order_label . ' could not be retrieved, because it could not be found (' . $reference_code . ').';
        log_activity($message);
        return error_response($message);
    }

    if ($order['status'] != 'incomplete') {
        $message = 'The ' . $order_label . ' could not be retrieved, because it has already been completed (' . $reference_code . ').';
        log_activity($message);
        return error_response($message);
    }

    // If the order is already active for the customer, then return success.  It is important that
    // we not continue below and clear billing info if the customer is already using this order.
    if ($_SESSION['ecommerce']['order_id'] == $order['id']) {
        return success_response();
    }

    $_SESSION['ecommerce']['order_id'] = $order['id'];

    // If this visitor has a tracking code, then update tracking code in order.  If this visitor
    // does not have a tracking code, then we don't update the tracking code for the order, because
    // we don't want to lose a previous tracking code.

    $tracking_code = get_tracking_code();

    $sql_tracking_code = "";

    if ($tracking_code != '') {
        $sql_tracking_code = "tracking_code = '" . e($tracking_code) . "',";
    }

    $sql_utm = "";

    if ($_SESSION['software']['utm_source']) {
        $sql_utm =
            "utm_source = '" . e($_SESSION['software']['utm_source']) . "',
            utm_medium = '" . e($_SESSION['software']['utm_medium']) . "',
            utm_campaign = '" . e($_SESSION['software']['utm_campaign']) . "',
            utm_term = '" . e($_SESSION['software']['utm_term']) . "',
            utm_content = '" . e($_SESSION['software']['utm_content']) . "',";
    }

    // Clear billing information for order, in case the billing information contains information
    // for a different person (e.g. the person that setup the order). Update the IP address for the
    // order because the visitor might have a different IP address now.

    db("
        UPDATE orders SET
            billing_salutation = '',
            billing_first_name = '',
            billing_last_name = '',
            billing_company = '',
            billing_address_1 = '',
            billing_address_2 = '',
            billing_city = '',
            billing_state = '',
            billing_zip_code = '',
            billing_country = '',
            billing_address_verified = '0',
            billing_phone_number = '',
            billing_fax_number = '',
            billing_email_address = '',
            billing_complete = '0',
            opt_in = '1',
            referral_source_code = '',
            $sql_tracking_code
            $sql_utm
            ip_address = IFNULL(INET_ATON('" . e($_SERVER['REMOTE_ADDR']) . "'), 0)
        WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'");

    // If visitor tracking is on, update visitor record with order information, if visitor has not
    // already created order or retrieved an order.

    if (VISITOR_TRACKING) {

        db("
            UPDATE visitors SET
                order_id = '" . e($_SESSION['ecommerce']['order_id']) . "',
                order_retrieved = '1',
                stop_timestamp = UNIX_TIMESTAMP()
            WHERE
                (id = '" . e($_SESSION['software']['visitor_id']) . "')
                AND (order_created = '0')
                AND (order_retrieved = '0')");
    }

    log_activity('The ' . $order_label . ' was retrieved (' . $reference_code . ').');
    return success_response('The ' . $order_label . ' was retrieved.');
} 

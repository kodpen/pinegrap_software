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

$liveform = new liveform('view_orders');

$user = validate_user();
validate_ecommerce_access($user);

// If the reset parameter was sent in the query string,
// then clear all session values for this screen.
// The shipping report screen uses this feature in order to link to this screen
// with a fresh view.
if ($_GET['reset'] == 'true') {
    unset($_SESSION['software']['ecommerce']['view_orders']);
}

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_orders'][$key] = trim($value);
    }
}

// if advanced filters value was passed in the query string
if (isset($_REQUEST['advanced_filters']) == true) {
    // if advanced filters should be turned on
    if ($_REQUEST['advanced_filters'] == 'true') {
        $_SESSION['software']['ecommerce']['view_orders']['advanced_filters'] = true;

    // else advanced filters should be turned off
    } else {
        $_SESSION['software']['ecommerce']['view_orders']['advanced_filters'] = false;
    }
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_orders']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ecommerce']['view_orders']['query']) == true) && ($_SESSION['software']['ecommerce']['view_orders']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

// Set default values for some fields.

if (isset($_SESSION['software']['ecommerce']['view_orders']['date_type']) == false) {
    $_SESSION['software']['ecommerce']['view_orders']['date_type'] = 'order_date';
}

if (isset($_SESSION['software']['ecommerce']['view_orders']['start_month']) == false) {
    $_SESSION['software']['ecommerce']['view_orders']['start_month'] = date('m', time() - 2678400);
    $_SESSION['software']['ecommerce']['view_orders']['start_day'] = date('d', time() - 2678400);
    $_SESSION['software']['ecommerce']['view_orders']['start_year'] = date('Y', time() - 2678400);

    $_SESSION['software']['ecommerce']['view_orders']['stop_month'] = date('m');
    $_SESSION['software']['ecommerce']['view_orders']['stop_day'] = date('d');
    $_SESSION['software']['ecommerce']['view_orders']['stop_year'] = date('Y');
}

if (isset($_SESSION['software']['ecommerce']['view_orders']['status']) == false) {
    $_SESSION['software']['ecommerce']['view_orders']['status'] = 'complete_or_exported';
}

$sql_status = "";

// Prepare SQL status filter differently based on the selected status.
switch ($_SESSION['software']['ecommerce']['view_orders']['status']) {
    case 'any':
        $sql_status = "";
        break;

    case 'incomplete':
        $sql_status = "AND (orders.status = 'incomplete')";
        break;

    case 'complete':
        $sql_status = "AND (orders.status = 'complete')";
        break;

    case 'exported':
        $sql_status = "AND (orders.status = 'exported')";
        break;

    case 'complete_or_exported':
    default:
        $sql_status = "AND ((orders.status = 'complete') OR (orders.status = 'exported'))";
        break;
}

$decrease_year['start_month'] = '01';
$decrease_year['start_day'] = '01';
$decrease_year['start_year'] = $_SESSION['software']['ecommerce']['view_orders']['start_year'] - 1;
$decrease_year['stop_month'] = '12';
$decrease_year['stop_day'] = '31';
$decrease_year['stop_year'] = $_SESSION['software']['ecommerce']['view_orders']['start_year'] - 1;

$current_year['start_month'] = '01';
$current_year['start_day'] = '01';
$current_year['start_year'] = date('Y');
$current_year['stop_month'] = '12';
$current_year['stop_day'] = '31';
$current_year['stop_year'] = date('Y');

$increase_year['start_month'] = '01';
$increase_year['start_day'] = '01';
$increase_year['start_year'] = $_SESSION['software']['ecommerce']['view_orders']['start_year'] + 1;
$increase_year['stop_month'] = '12';
$increase_year['stop_day'] = '31';
$increase_year['stop_year'] = $_SESSION['software']['ecommerce']['view_orders']['start_year'] + 1;

$decrease_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'] - 1, 1, $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$decrease_month['new_month'] = date('m', $decrease_month['new_time']);
$decrease_month['new_year'] = date('Y', $decrease_month['new_time']);
$decrease_month['start_month'] = $decrease_month['new_month'];
$decrease_month['start_day'] = '01';
$decrease_month['start_year'] = $decrease_month['new_year'];
$decrease_month['stop_month'] = $decrease_month['new_month'];
$decrease_month['stop_day'] = date('t', $decrease_month['new_time']);
$decrease_month['stop_year'] = $decrease_month['new_year'];

$current_month['new_month'] = date('m');
$current_month['new_year'] = date('Y');
$current_month['start_month'] = $current_month['new_month'];
$current_month['start_day'] = '01';
$current_month['start_year'] = $current_month['new_year'];
$current_month['stop_month'] = $current_month['new_month'];
$current_month['stop_day'] = date('t');
$current_month['stop_year'] = $current_month['new_year'];

$increase_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'] + 1, 1, $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$increase_month['new_month'] = date('m', $increase_month['new_time']);
$increase_month['new_year'] = date('Y', $increase_month['new_time']);
$increase_month['start_month'] = $increase_month['new_month'];
$increase_month['start_day'] = '01';
$increase_month['start_year'] = $increase_month['new_year'];
$increase_month['stop_month'] = $increase_month['new_month'];
$increase_month['stop_day'] = date('t', $increase_month['new_time']);
$increase_month['stop_year'] = $increase_month['new_year'];

$decrease_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'], $_SESSION['software']['ecommerce']['view_orders']['start_day'], $_SESSION['software']['ecommerce']['view_orders']['start_year']);
// if start date is a Sunday, use last Sunday (add 12:00:00 to prevent a bug that results in Saturday being returned)
if (date('l', $decrease_week['start_date_timestamp']) == 'Sunday') {
    $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['start_date_timestamp']);

// else start date is not a Sunday, so we need to do last sunday twice (add 12:00:00 to prevent a bug that results in Saturday being returned)
} else {
    $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['start_date_timestamp']);
    $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['new_time_start']);
}
$decrease_week['new_time_stop'] = strtotime('Saturday', $decrease_week['new_time_start']);
$decrease_week['start_month'] = date('m', $decrease_week['new_time_start']);
$decrease_week['start_day'] = date('d', $decrease_week['new_time_start']);
$decrease_week['start_year'] = date('Y', $decrease_week['new_time_start']);
$decrease_week['stop_month'] = date('m', $decrease_week['new_time_stop']);
$decrease_week['stop_day'] = date('d', $decrease_week['new_time_stop']);
$decrease_week['stop_year'] = date('Y', $decrease_week['new_time_stop']);

// if today is Sunday
if (date('l') == 'Sunday') {
    $current_week['new_time_start'] = strtotime('Sunday');
} else {
    $current_week['new_time_start'] = strtotime('last Sunday');
}
$current_week['new_time_stop'] = strtotime('Saturday', $current_week['new_time_start']);
$current_week['start_month'] = date('m', $current_week['new_time_start']);
$current_week['start_day'] = date('d', $current_week['new_time_start']);
$current_week['start_year'] = date('Y', $current_week['new_time_start']);
$current_week['stop_month'] = date('m', $current_week['new_time_stop']);
$current_week['stop_day'] = date('d', $current_week['new_time_stop']);
$current_week['stop_year'] = date('Y', $current_week['new_time_stop']);

$increase_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'], $_SESSION['software']['ecommerce']['view_orders']['start_day'], $_SESSION['software']['ecommerce']['view_orders']['start_year']);
// if start date is a Sunday
if (date('l', $increase_week['start_date_timestamp']) == 'Sunday') {
    $increase_week['new_time_start'] = strtotime('2 Sunday', $increase_week['start_date_timestamp']);
} else {
    $increase_week['new_time_start'] = strtotime('Sunday', $increase_week['start_date_timestamp']);
}
$increase_week['new_time_stop'] = strtotime('Saturday', $increase_week['new_time_start']);
$increase_week['start_month'] = date('m', $increase_week['new_time_start']);
$increase_week['start_day'] = date('d', $increase_week['new_time_start']);
$increase_week['start_year'] = date('Y', $increase_week['new_time_start']);
$increase_week['stop_month'] = date('m', $increase_week['new_time_stop']);
$increase_week['stop_day'] = date('d', $increase_week['new_time_stop']);
$increase_week['stop_year'] = date('Y', $increase_week['new_time_stop']);

$decrease_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'], $_SESSION['software']['ecommerce']['view_orders']['start_day'] - 1, $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$decrease_day['new_month'] = date('m', $decrease_day['new_time']);
$decrease_day['new_day'] = date('d', $decrease_day['new_time']);
$decrease_day['new_year'] = date('Y', $decrease_day['new_time']);
$decrease_day['start_month'] = $decrease_day['new_month'];
$decrease_day['start_day'] = $decrease_day['new_day'];
$decrease_day['start_year'] = $decrease_day['new_year'];
$decrease_day['stop_month'] = $decrease_day['new_month'];
$decrease_day['stop_day'] = $decrease_day['new_day'];
$decrease_day['stop_year'] = $decrease_day['new_year'];

$current_day['new_month'] = date('m');
$current_day['new_day'] = date('d');
$current_day['new_year'] = date('Y');
$current_day['start_month'] = $current_day['new_month'];
$current_day['start_day'] = $current_day['new_day'];
$current_day['start_year'] = $current_day['new_year'];
$current_day['stop_month'] = $current_day['new_month'];
$current_day['stop_day'] = $current_day['new_day'];
$current_day['stop_year'] = $current_day['new_year'];

$increase_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'], $_SESSION['software']['ecommerce']['view_orders']['start_day'] + 1, $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$increase_day['new_month'] = date('m', $increase_day['new_time']);
$increase_day['new_day'] = date('d', $increase_day['new_time']);
$increase_day['new_year'] = date('Y', $increase_day['new_time']);
$increase_day['start_month'] = $increase_day['new_month'];
$increase_day['start_day'] = $increase_day['new_day'];
$increase_day['start_year'] = $increase_day['new_year'];
$increase_day['stop_month'] = $increase_day['new_month'];
$increase_day['stop_day'] = $increase_day['new_day'];
$increase_day['stop_year'] = $increase_day['new_year'];

// get timestamps for start and stop dates
$start_timestamp = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_orders']['start_month'], $_SESSION['software']['ecommerce']['view_orders']['start_day'], $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$stop_timestamp = mktime(23, 59, 59, $_SESSION['software']['ecommerce']['view_orders']['stop_month'], $_SESSION['software']['ecommerce']['view_orders']['stop_day'], $_SESSION['software']['ecommerce']['view_orders']['stop_year']);

// Output start date range time
$output_date_range_time = h(get_month_name_from_number($_SESSION['software']['ecommerce']['view_orders']['start_month']) . ' ' . $_SESSION['software']['ecommerce']['view_orders']['start_day'] . ', ' . $_SESSION['software']['ecommerce']['view_orders']['start_year']);
$output_date_range_time .= ' - ';

// Output end date range time
$output_date_range_time .= h(get_month_name_from_number($_SESSION['software']['ecommerce']['view_orders']['stop_month']) . ' ' . $_SESSION['software']['ecommerce']['view_orders']['stop_day'] . ', ' . $_SESSION['software']['ecommerce']['view_orders']['stop_year']);

// If the advanced filters are disabled or the date type is set to order date,
// then prepare SQL filter for order date.
if (
    ($_SESSION['software']['ecommerce']['view_orders']['advanced_filters'] == false)
    || ($_SESSION['software']['ecommerce']['view_orders']['date_type'] == 'order_date')
) {
    $where = "WHERE (orders.order_date >= $start_timestamp) AND (orders.order_date <= $stop_timestamp)";

// Otherwise the advanced filters are enabled and the date type is ship date,
// so prepare SQL filter for ship date.
} else {
    $where = "WHERE (ship_tos.ship_date >= '" . date('Y-m-d', $start_timestamp) . "') AND (ship_tos.ship_date <= '" . date('Y-m-d', $stop_timestamp) . "')";
}

if ($_SESSION['software']['ecommerce']['view_orders']['query']) {
    // We had to start using CAST(orders.order_number AS CHAR) in order to avoid an issue
    // where the lower function would not work in some version of MySQL.

    $where .=
        " AND (LOWER(CONCAT_WS(',',
        orders.custom_field_1,
        orders.custom_field_2,
        orders.billing_first_name,
        orders.billing_last_name,
        orders.billing_email_address,
        orders.billing_company,
        orders.billing_address_1,
        orders.billing_address_2,
        orders.billing_city,
        orders.billing_state,
        orders.billing_zip_code,
        orders.billing_country,
        orders.billing_phone_number,
        orders.billing_fax_number,
        orders.payment_method,
        orders.card_type,
        orders.cardholder,
        orders.card_number,
        orders.referral_source_code,
        orders.po_number,
        CAST(orders.order_number AS CHAR),
        user.user_username,
        orders.transaction_id,
        orders.authorization_code,
        orders.special_offer_code,
        orders.reference_code,
        orders.tracking_code,
        contacts.member_id,
        orders.affiliate_code,
        orders.currency_code,
        INET_NTOA(orders.ip_address))) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ecommerce']['view_orders']['query'])) . "%')";
}

// if advanced filters are on, prepare SQL
if ($_SESSION['software']['ecommerce']['view_orders']['advanced_filters'] == true) {
    if ($_SESSION['software']['ecommerce']['view_orders']['order_number']) {$where .= " AND (orders.order_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['order_number']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['transaction_id']) {$where .= " AND (orders.transaction_id LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['transaction_id']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['authorization_code']) {$where .= " AND (orders.authorization_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['authorization_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['special_offer_code']) {$where .= " AND (orders.special_offer_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['special_offer_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['referral_source_code']) {$where .= " AND (orders.referral_source_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['referral_source_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['reference_code']) {$where .= " AND (orders.reference_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['reference_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['tracking_code']) {$where .= " AND (orders.tracking_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['tracking_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['http_referer']) {$where .= " AND (orders.http_referer LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['http_referer']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['ip_address']) {$where .= " AND (INET_NTOA(orders.ip_address) LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['ip_address']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['product_name']) {$where .= " AND (order_items.product_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['product_name']) . "%')";}

    // If payment method has not been set yet, then set it to any by default.
    if (isset($_SESSION['software']['ecommerce']['view_orders']['payment_method']) == false) {
        $_SESSION['software']['ecommerce']['view_orders']['payment_method'] = 'any';
    }

    if (($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == '') || ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'Credit/Debit Card') || ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'PayPal Express Checkout') || ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'Offline Payment')) {
        $where .= " AND (orders.payment_method = '" . escape($_SESSION['software']['ecommerce']['view_orders']['payment_method']) . "')";
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['card_type']) {$where .= " AND (orders.card_type LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['card_type']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['cardholder']) {$where .= " AND (orders.cardholder LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['cardholder']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['card_number']) {$where .= " AND (orders.card_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['card_number']) . "%')";}

    if (AFFILIATE_PROGRAM == true) {
        if ($_SESSION['software']['ecommerce']['view_orders']['affiliate_code']) {$where .= " AND (orders.affiliate_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['affiliate_code']) . "%')";}
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['custom_field_1']) {$where .= " AND (orders.custom_field_1 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['custom_field_1']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['custom_field_2']) {$where .= " AND (orders.custom_field_2 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['custom_field_2']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_salutation']) {$where .= " AND (orders.billing_salutation LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_salutation']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_first_name']) {$where .= " AND (orders.billing_first_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_first_name']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_last_name']) {$where .= " AND (orders.billing_last_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_last_name']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_company']) {$where .= " AND (orders.billing_company LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_company']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_address_1']) {$where .= " AND (orders.billing_address_1 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_address_1']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_address_2']) {$where .= " AND (orders.billing_address_2 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_address_2']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_city']) {$where .= " AND (orders.billing_city LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_city']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_state']) {$where .= " AND ((orders.billing_state LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_state']) . "%') OR (billing_states.name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_state']) . "%'))";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_zip_code']) {$where .= " AND (orders.billing_zip_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_zip_code']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_country']) {$where .= " AND ((orders.billing_country LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_country']) . "%') OR (billing_countries.name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_country']) . "%'))";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_phone_number']) {$where .= " AND (orders.billing_phone_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_phone_number']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_fax_number']) {$where .= " AND (orders.billing_fax_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_fax_number']) . "%')";}
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_email_address']) {$where .= " AND (orders.billing_email_address LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['billing_email_address']) . "%')";}

    if ($_SESSION['software']['ecommerce']['view_orders']['opt_in_status'] == 'opt_in') {
        $where .= " AND (orders.opt_in = '1')";
    } else if ($_SESSION['software']['ecommerce']['view_orders']['opt_in_status'] == 'opt_out') {
        $where .= " AND (orders.opt_in = '0')";
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['po_number']) {$where .= " AND (orders.po_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['po_number']) . "%')";}

    if ($_SESSION['software']['ecommerce']['view_orders']['tax_status'] == 'tax_exempt') {
        $where .= " AND (orders.tax_exempt = '1')";
    } else if ($_SESSION['software']['ecommerce']['view_orders']['tax_status'] == 'not_tax_exempt') {
        $where .= " AND (orders.tax_exempt = '0')";
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['ship_to_name']) {$where .= " AND (ship_tos.ship_to_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['ship_to_name']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_salutation']) {$where .= " AND (ship_tos.salutation LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_salutation']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_first_name']) {$where .= " AND (ship_tos.first_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_first_name']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_last_name']) {$where .= " AND (ship_tos.last_name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_last_name']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_company']) {$where .= " AND (ship_tos.company LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_company']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_address_1']) {$where .= " AND (ship_tos.address_1 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_address_1']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_address_2']) {$where .= " AND (ship_tos.address_2 LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_address_2']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_city']) {$where .= " AND (ship_tos.city LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_city']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_state']) {$where .= " AND ((ship_tos.state LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_state']) . "%') OR (shipping_states.name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_state']) . "%'))"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_zip_code']) {$where .= " AND (ship_tos.zip_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_zip_code']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_country']) {$where .= " AND ((ship_tos.country LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_country']) . "%') OR (shipping_countries.name LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_country']) . "%'))"; $shipping = true;}
    
    // If address type has not been set yet, then set it to any by default.
    if (isset($_SESSION['software']['ecommerce']['view_orders']['address_type']) == false) {
        $_SESSION['software']['ecommerce']['view_orders']['address_type'] = 'any';
    }

    if (($_SESSION['software']['ecommerce']['view_orders']['address_type'] == '') || ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == 'residential') || ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == 'business')) {
        $where .= " AND (ship_tos.address_type = '" . escape($_SESSION['software']['ecommerce']['view_orders']['address_type']) . "')";
        $shipping = true;
    }
    
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_phone_number']) {$where .= " AND (ship_tos.phone_number LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_phone_number']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['arrival_date_code']) {$where .= " AND (ship_tos.arrival_date_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['arrival_date_code']) . "%')"; $shipping = true;}
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_method_code']) {$where .= " AND (ship_tos.shipping_method_code LIKE '%" . escape($_SESSION['software']['ecommerce']['view_orders']['shipping_method_code']) . "%')"; $shipping = true;}

    // If shipping status has not been set yet, then set it to any by default.
    if (isset($_SESSION['software']['ecommerce']['view_orders']['shipping_status']) == false) {
        $_SESSION['software']['ecommerce']['view_orders']['shipping_status'] = 'any';
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_status'] == 'shipped') {
        $where .=
            " AND
            ((SELECT COUNT(*)
            FROM order_items
            WHERE
                (order_items.order_id = orders.id)
                AND (order_items.ship_to_id != '0')
                AND (order_items.quantity > order_items.shipped_quantity)) = 0)";

    } else if ($_SESSION['software']['ecommerce']['view_orders']['shipping_status'] == 'unshipped') {
        $where .=
            " AND
            ((SELECT COUNT(*)
            FROM order_items
            WHERE
                (order_items.order_id = orders.id)
                AND (order_items.ship_to_id != '0')
                AND (order_items.quantity > order_items.shipped_quantity)) > 0)";
    }

    if ((ECOMMERCE_MULTICURRENCY === true) && ($_SESSION['software']['ecommerce']['view_orders']['currency_code'])) {$where .= " AND (orders.currency_code = '" . escape($_SESSION['software']['ecommerce']['view_orders']['currency_code']) . "')";}

    if ($_SESSION['software']['ecommerce']['view_orders']['date_type'] == 'ship_date') {
        $shipping = true;
    }

    // if user is searching by product name, add a left join
    if ($_SESSION['software']['ecommerce']['view_orders']['product_name']) {
        $join_order_items = " LEFT JOIN order_items ON orders.id = order_items.order_id";
    }

    // if user is searching by a shipping field, add a left join
    if ($shipping == true) {
        $join_ship_tos = " LEFT JOIN ship_tos ON orders.id = ship_tos.order_id";
    }

    // if user is searching by billing state, add a left join
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_state']) {
        $join_billing_states = " LEFT JOIN states as billing_states ON orders.billing_state = billing_states.code";
    }

    // if user is searching by billing country, add a left join
    if ($_SESSION['software']['ecommerce']['view_orders']['billing_country']) {
        $join_billing_countries = " LEFT JOIN countries as billing_countries ON orders.billing_country = billing_countries.code";
    }

    // if user is searching by shipping state, add a left join
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_state']) {
        $join_shipping_states = " LEFT JOIN states as shipping_states ON ship_tos.state = shipping_states.code";
    }

    // if user is searching by shipping country, add a left join
    if ($_SESSION['software']['ecommerce']['view_orders']['shipping_country']) {
        $join_shipping_countries = " LEFT JOIN countries as shipping_countries ON ship_tos.country = shipping_countries.code";
    }
}

// if user requested to export orders, export orders
if ($_GET['submit_data'] == 'Export Orders (multiple files)') {
    $orders = array();
    
    // Prepare array in order to store which orders will appear in this report
    // we will use this later for dealing with custom shipping/billing form data.
    $order_ids = array();

    // get order data
    $query = "SELECT
                orders.*,
                INET_NTOA(ip_address) as ip_address,
                contacts.member_id
             FROM orders
             LEFT JOIN user ON orders.user_id = user.user_id
             LEFT JOIN contacts on orders.contact_id = contacts.id
             $join_order_items
             $join_ship_tos
             $join_billing_states
             $join_billing_countries
             $join_shipping_states
             $join_shipping_countries
             $where
             $sql_status
             ORDER BY orders.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
        $order_ids[] = $row['id'];
    }

    // Initialize array that will be used for storing custom billing form data for all orders.
    $custom_billing_form_data = array();
    
    // Initialize array that will be used for storing custom field names for custom billing form data.
    $custom_billing_field_names = array();

    // Get all custom billing form data in database.
    $fields = db_items(
        "SELECT
            order_id,
            data,
            name
        FROM form_data
        WHERE
            (order_id != '0')
            AND (ship_to_id = '0')
            AND (order_item_id = '0')
        ORDER BY id ASC");

    // Loop through the fields in order to determine custom field names
    // and add fields to orders array for custom billing form data.
    foreach ($fields as $field) {
        // If this field is for an order that will appear in this export then deal with this field.
        if (in_array($field['order_id'], $order_ids) == true) {
            // If this field name has not been added to the custom field names array, then add it,
            // so we can keep track of all necessary field names
            if (in_array($field['name'], $custom_billing_field_names) == false) {
                $custom_billing_field_names[] = $field['name'];
            }
            
            // If there is not already form data for this field, then just add data to the array.
            if ((isset($custom_billing_form_data[$field['order_id']][$field['name']]) == FALSE) || ($custom_billing_form_data[$field['order_id']][$field['name']] == '')) {
                $custom_billing_form_data[$field['order_id']][$field['name']] = $field['data'];
                
            // Otherwise there is already form data for this field, so this is probably a field that supports multiple values,
            // so just append this additional value.
            } else {
                $custom_billing_form_data[$field['order_id']][$field['name']] .= ', ' . $field['data'];
            }
        }
    }

    // if multi currency is enabled
    if (ECOMMERCE_MULTICURRENCY === true) {
        $multi_currency_column_name = '"currency_code",';
    } else {
        $multi_currency_column_name = '';
    }

    // prepare first row of orders.csv data
    $order_data =
        '"order_id",' .
        '"order_number",' .
        '"custom_field_1",' .
        '"custom_field_2",' .
        '"billing_salutation",' .
        '"billing_first_name",' .
        '"billing_last_name",' .
        '"billing_email_address",' .
        '"billing_company",' .
        '"billing_address_1",' .
        '"billing_address_2",' .
        '"billing_city",' .
        '"billing_state",' .
        '"billing_zip_code",' .
        '"billing_country",' .
        '"billing_phone_number",' .
        '"billing_fax_number",' .
        '"payment_method",' .
        '"card_type",' .
        '"cardholder",' .
        '"card_number",' .
        '"expiration_month",' .
        '"expiration_year",' .
        '"card_verification_number",' .
        '"referral_source_code",' .
        '"po_number",' .
        '"tax_exempt",' .
        '"opt_in",' .
        '"subtotal",' .
        '"discount",' .
        '"tax",' .
        '"shipping",' .
        '"gift_card_discount",' .
        '"surcharge",' .
        '"total",' .
        '"commission",' .
        '"order_date",' .
        '"transaction_id",' .
        '"authorization_code",' .
        '"special_offer_code",' .
        '"reference_code",' .
        '"tracking_code",' .
        '"utm_source",' .
        '"utm_medium",' .
        '"utm_campaign",' .
        '"utm_term",' .
        '"utm_content",' .
        '"member_id",' .
        '"affiliate_code",' .
        $multi_currency_column_name .
        '"http_referer",' .
        '"ip_address"';

    // Loop through the custom billing field names in order to
    // output them in the first row of the orders.csv data.
    foreach ($custom_billing_field_names as $custom_billing_field_name) {
        $order_data .= ',"' . str_replace('"', '""', $custom_billing_field_name) . '"';
    }
    
    $order_data .= "\n";

    // Loop through all orders in order to prepare CSV rows.
    foreach ($orders as $order) {
        // for each value in the row
        foreach ($order as $key => $value) {
           // replace quotation mark with two quotation marks
           $value = str_replace('"', '""', $value);
           // set new value
           $order[$key] = $value;
        }
        
        $card_number = $order['card_number'];
        
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
        
        $card_verification_number = $order['card_verification_number'];
        
        // if the card verification number is not already protected,
        // and the user does not have access to view card data,
        // then protect it
        if (
            (mb_substr($card_verification_number, 0, 1) != '*')
            && (($user['role'] == 3) && ($user['view_card_data'] == FALSE))
        ) {
            $card_verification_number = protect_card_verification_number($card_verification_number);
        }

        if ($order['tax_exempt']) {
            $tax_exempt = 'yes';
        } else {
            $tax_exempt = 'no';
        }

        if ($order['opt_in']) {
            $opt_in = 'yes';
        } else {
            $opt_in = 'no';
        }

        // If the date format is month and then day, then use that format.
        if (DATE_FORMAT == 'month_day') {
            $month_and_day_format = 'n/j';

        // Otherwise the date format is day and then month, so use that format.
        } else {
            $month_and_day_format = 'j/n';
        }

        // if multi currency is enabled
        if (ECOMMERCE_MULTICURRENCY === true) {
            $currency_code = '"' . $order['currency_code'] . '",';
        } else {
            $currency_code = '';
        }
        
        $ip_address = $order['ip_address'];
        
        // if the IP address is 0.0.0.0, then we don't know the IP address, so set the value to empty string
        if ($ip_address == '0.0.0.0') {
            $ip_address = '';
        }

        // prepare row for orders.csv data
        $order_data .=
            '"' . $order['id'] . '",' .
            '"' . $order['order_number'] . '",' .
            '"' . $order['custom_field_1'] . '",' .
            '"' . $order['custom_field_2'] . '",' .
            '"' . $order['billing_salutation'] . '",' .
            '"' . $order['billing_first_name'] . '",' .
            '"' . $order['billing_last_name'] . '",' .
            '"' . $order['billing_email_address'] . '",' .
            '"' . $order['billing_company'] . '",' .
            '"' . $order['billing_address_1'] . '",' .
            '"' . $order['billing_address_2'] . '",' .
            '"' . $order['billing_city'] . '",' .
            '"' . $order['billing_state'] . '",' .
            '"' . $order['billing_zip_code'] . '",' .
            '"' . $order['billing_country'] . '",' .
            '"' . $order['billing_phone_number'] . '",' .
            '"' . $order['billing_fax_number'] . '",' .
            '"' . $order['payment_method'] . '",' .
            '"' . $order['card_type'] . '",' .
            '"' . $order['cardholder'] . '",' .
            '"#' . $card_number . '",' .
            '"' . $order['expiration_month'] . '",' .
            '"' . $order['expiration_year'] . '",' .
            '"' . $card_verification_number . '",' .
            '"' . $order['referral_source_code'] . '",' .
            '"' . $order['po_number'] . '",' .
            '"' . $tax_exempt . '",' .
            '"' . $opt_in . '",' .
            '"' . sprintf("%01.2lf", $order['subtotal'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['discount'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['tax'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['shipping'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['gift_card_discount'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['surcharge'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['total'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['commission'] / 100) . '",' .
            '"' . date($month_and_day_format . '/Y g:i:s A T', $order['order_date']) . '",' .
            '"' . $order['transaction_id'] . '",' .
            '"' . $order['authorization_code'] . '",' .
            '"' . $order['special_offer_code'] . '",' .
            '"' . $order['reference_code'] . '",' .
            '"' . $order['tracking_code'] . '",' .
            '"' . $order['utm_source'] . '",' .
            '"' . $order['utm_medium'] . '",' .
            '"' . $order['utm_campaign'] . '",' .
            '"' . $order['utm_term'] . '",' .
            '"' . $order['utm_content'] . '",' .
            '"' . $order['member_id'] . '",' .
            '"' . $order['affiliate_code'] . '",' .
            $currency_code .
            '"' . $order['http_referer'] . '",' .
            '"' . $ip_address . '"';

        // Loop through the custom billing field names in order to
        // output custom billing form data for this order row.
        foreach ($custom_billing_field_names as $custom_billing_field_name) {
            $order_data .= ',"' . str_replace('"', '""', $custom_billing_form_data[$order['id']][$custom_billing_field_name]) . '"';
        }

        $order_data .= "\n";

        // If the status of this order is complete, then update the status to be exported.
        if ($order['status'] == 'complete') {
            db("UPDATE orders SET status = 'exported' WHERE id = '" . $order['id'] . "'");
        }
    }
    
    // get all custom shipping form data in database
    $query =
        "SELECT
            order_id,
            ship_to_id,
            data,
            name
        FROM form_data
        WHERE ship_to_id != '0'
        ORDER BY id ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // initialize array that will be used for storing custom shipping form data for ship tos
    $ship_tos = array();
    
    // initialize array that will be used for storing custom field names for custom shipping form data
    $custom_field_names = array();
    
    // loop through the fields in order to determine custom field names and add fields to ship tos array for custom shipping form data
    while ($field = mysqli_fetch_assoc($result)) {
        // if this field is for an order that will appear in this export then deal with this field
        if (in_array($field['order_id'], $order_ids) == TRUE) {
            // if this field name has not been added to the custom field names array, then add it,
            // so we can keep track of all necessary field names
            if (in_array($field['name'], $custom_field_names) == FALSE) {
                $custom_field_names[] = $field['name'];
            }
            
            // if there is not already form data for this field in the ship tos array, then just add data to the array
            if ((isset($ship_tos[$field['ship_to_id']][$field['name']]) == FALSE) || ($ship_tos[$field['ship_to_id']][$field['name']] == '')) {
                $ship_tos[$field['ship_to_id']][$field['name']] = $field['data'];
                
            // else there is already form data for this field, so this is probably a field that supports multiple values,
            // so just append this additional value
            } else {
                $ship_tos[$field['ship_to_id']][$field['name']] .= ', ' . $field['data'];
            }
        }
    }

    // prepare first row of ship_tos.csv data
    $ship_to_data =
        '"ship_to_id",' .
        '"order_id",' .
        '"order_number",' .
        '"ship_to_name",' .
        '"salutation",' .
        '"first_name",' .
        '"last_name",' .
        '"company",' .
        '"address_1",' .
        '"address_2",' .
        '"city",' .
        '"state",' .
        '"zip_code",' .
        '"country",' .
        '"address_type",' .
        '"arrival_date_code",' .
        '"arrival_date",' .
        '"ship_date",' .
        '"delivery_date",' .
        '"shipping_method_code",' .
        '"shipping_cost"';
    
    // loop through the custom field names in order to output them in the first row of ship_tos.csv data
    foreach ($custom_field_names as $custom_field_name) {
        $ship_to_data .= ',"' . str_replace('"', '""', $custom_field_name) . '"';
    }
    
    $ship_to_data .= "\n";

    // get ship to data
    $query =
        "SELECT
            ship_tos.*,
            orders.order_number
        FROM ship_tos
        LEFT JOIN orders on ship_tos.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        $join_order_items
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY ship_tos.order_id, ship_tos.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // assume that there is not a ship to until we find one
    $ship_to_exists = false;

    while ($row = mysqli_fetch_assoc($result)) {
        // for each value in the row
        foreach ($row as $key => $value) {
           // replace quotation mark with two quotation marks
           $value = str_replace('"', '""', $value);
           // set new value
           $row[$key] = $value;
        }
        
        // if this shipping address is verified, then convert salutation and country to all uppercase
        if ($row['address_verified'] == 1) {
            $row['salutation'] = mb_strtoupper($row['salutation']);
            $row['country'] = mb_strtoupper($row['country']);
        }

        // prepare row for ship_tos.csv data
        $ship_to_data .=
            '"' . $row['id'] . '",' .
            '"' . $row['order_id'] . '",' .
            '"' . $row['order_number'] . '",' .
            '"' . $row['ship_to_name'] . '",' .
            '"' . $row['salutation'] . '",' .
            '"' . $row['first_name'] . '",' .
            '"' . $row['last_name'] . '",' .
            '"' . $row['company'] . '",' .
            '"' . $row['address_1'] . '",' .
            '"' . $row['address_2'] . '",' .
            '"' . $row['city'] . '",' .
            '"' . $row['state'] . '",' .
            '"' . $row['zip_code'] . '",' .
            '"' . $row['country'] . '",' .
            '"' . $row['address_type'] . '",' .
            '"' . $row['arrival_date_code'] . '",' .
            '"' . prepare_form_data_for_output($row['arrival_date'], 'date') . '",' .
            '"' . prepare_form_data_for_output($row['ship_date'], 'date') . '",' .
            '"' . prepare_form_data_for_output($row['delivery_date'], 'date') . '",' .
            '"' . $row['shipping_method_code'] . '",' .
            '"' . sprintf("%01.2lf", $row['shipping_cost'] / 100) . '"';
        
        // loop through the custom field names in order to output custom shipping form data for this ship to row
        foreach ($custom_field_names as $custom_field_name) {
            $ship_to_data .= ',"' . str_replace('"', '""', $ship_tos[$row['id']][$custom_field_name]) . '"';
        }
        
        $ship_to_data .= "\n";

        $ship_to_exists = true;
    }

    $output_custom_field_1_heading = '';

    // If the first custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
        $output_custom_field_1_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '",';
    }

    $output_custom_field_2_heading = '';

    // If the second custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
        $output_custom_field_2_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '",';
    }

    $output_custom_field_3_heading = '';

    // If the third custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
        $output_custom_field_3_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '",';
    }

    $output_custom_field_4_heading = '';

    // If the fourth custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
        $output_custom_field_4_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '",';
    }

    // prepare first row of order_items.csv data
    $order_item_data =
        '"order_item_id",' .
        '"order_id",' .
        '"order_number",' .
        '"ship_to_id",' .
        '"product_name",' .
        '"quantity",' .
        '"shipped_quantity",' .
        '"price",' .
        '"tax",' .
        '"recurring_payment_period",' .
        '"recurring_number_of_payments",' .
        '"recurring_start_date",' .
        $output_custom_field_1_heading .
        $output_custom_field_2_heading .
        $output_custom_field_3_heading .
        $output_custom_field_4_heading .
        '"notes"' . "\n";

    // get order item data
    $query =
        "SELECT
            order_items.*,
            orders.order_number,
            products.custom_field_1,
            products.custom_field_2,
            products.custom_field_3,
            products.custom_field_4,
            products.notes
        FROM order_items
        LEFT JOIN orders on order_items.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        LEFT JOIN products on order_items.product_id = products.id
        $join_ship_tos
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY order_items.order_id, order_items.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
        // for each value in the row
        foreach ($row as $key => $value) {
           // replace quotation mark with two quotation marks
           $value = str_replace('"', '""', $value);
           // set new value
           $row[$key] = $value;
        }
        
        $shipped_quantity = '';
        
        // if shipped quantity should be shown then show it
        if ($row['show_shipped_quantity'] == 1) {
            $shipped_quantity = $row['shipped_quantity'];
        }
        
        $recurring_payment_period = '';
        $recurring_number_of_payments = '';
        $recurring_start_date = '';

        // if order item is a recurring order item, then prepare values
        if ($row['recurring_payment_period'] != '') {
            $recurring_payment_period = $row['recurring_payment_period'];
            $recurring_number_of_payments = $row['recurring_number_of_payments'];
            $recurring_start_date = prepare_form_data_for_output($row['recurring_start_date'], 'date');
        }

        $output_custom_field_1 = '';

        // If the first custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_field_1 = '"' . escape_csv($row['custom_field_1']) . '",';
        }

        $output_custom_field_2 = '';

        // If the second custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_field_2 = '"' . escape_csv($row['custom_field_2']) . '",';
        }

        $output_custom_field_3 = '';

        // If the third custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_field_3 = '"' . escape_csv($row['custom_field_3']) . '",';
        }

        $output_custom_field_4 = '';

        // If the fourth custom product field is active, then output value for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_field_4 = '"' . escape_csv($row['custom_field_4']) . '",';
        }

        // prepare row for order_items.csv data
        $order_item_data .=
            '"' . $row['id'] . '",' .
            '"' . $row['order_id'] . '",' .
            '"' . $row['order_number'] . '",' .
            '"' . $row['ship_to_id'] . '",' .
            '"' . $row['product_name'] . '",' .
            '"' . $row['quantity'] . '",' .
            '"' . $shipped_quantity . '",' .
            '"' . sprintf("%01.2lf", $row['price'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $row['tax'] / 100) . '",' .
            '"' . $recurring_payment_period . '",' .
            '"' . $recurring_number_of_payments . '",' .
            '"' . $recurring_start_date . '",' .
            $output_custom_field_1 .
            $output_custom_field_2 .
            $output_custom_field_3 .
            $output_custom_field_4 .
            '"' . $row['notes'] . '"' . "\n";
    }
    
    $shipping_tracking_number_data = '';

    // get shipping tracking numbers
    $query =
        "SELECT
            shipping_tracking_numbers.id,
            shipping_tracking_numbers.order_id,
            orders.order_number,
            shipping_tracking_numbers.ship_to_id,
            shipping_tracking_numbers.number
        FROM shipping_tracking_numbers
        LEFT JOIN orders on shipping_tracking_numbers.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        $join_order_items
        $join_ship_tos
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY shipping_tracking_numbers.order_id, shipping_tracking_numbers.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $shipping_tracking_numbers = mysqli_fetch_items($result);
    
    // if there is at least one shipping tracking number, then prepare CSV data
    if (count($shipping_tracking_numbers) > 0) {
        // prepare first row of shipping_tracking_numbers.csv data
        $shipping_tracking_number_data .=
            '"shipping_tracking_number_id",' .
            '"order_id",' .
            '"order_number",' .
            '"ship_to_id",' .
            '"number"' . "\n";
            
        // loop through the shipping tracking numbers in order to prepare CSV data
        foreach ($shipping_tracking_numbers as $shipping_tracking_number) {
            // prepare row for shipping_tracking_numbers.csv data
            $shipping_tracking_number_data .=
                '"' . $shipping_tracking_number['id'] . '",' .
                '"' . $shipping_tracking_number['order_id'] . '",' .
                '"' . $shipping_tracking_number['order_number'] . '",' .
                '"' . $shipping_tracking_number['ship_to_id'] . '",' .
                '"' . str_replace('"', '""', $shipping_tracking_number['number']) . '"' . "\n";
        }
    }
    
    $zipfile = new zipfile();

    $zipfile->add_file($order_data, 'orders.csv');

    // if at least one ship to exists, then include ship tos file
    if ($ship_to_exists == true) {
        $zipfile->add_file($ship_to_data, 'ship_tos.csv');
    }

    $zipfile->add_file($order_item_data, 'order_items.csv');
    
    // if there is at least one shipping tracking number, then include file for it
    if ($shipping_tracking_number_data != '') {
        $zipfile->add_file($shipping_tracking_number_data, 'shipping_tracking_numbers.csv');
    }

    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment; filename=orders.zip");
    print $zipfile->file();

} else if ($_GET['submit_data'] == 'Export Orders (single file)') {
    // get orders
    $query =
        "SELECT
            orders.id,
            orders.order_number,
            orders.custom_field_1,
            orders.custom_field_2,
            orders.billing_salutation,
            orders.billing_first_name,
            orders.billing_last_name,
            orders.billing_email_address,
            orders.billing_company,
            orders.billing_address_1,
            orders.billing_address_2,
            orders.billing_city,
            orders.billing_state,
            orders.billing_zip_code,
            orders.billing_country,
            orders.billing_phone_number,
            orders.billing_fax_number,
            orders.payment_method,
            orders.card_type,
            orders.cardholder,
            orders.card_number,
            orders.expiration_month,
            orders.expiration_year,
            orders.card_verification_number,
            orders.referral_source_code,
            orders.po_number,
            orders.tax_exempt,
            orders.opt_in,
            orders.subtotal,
            orders.discount,
            orders.tax,
            orders.shipping,
            orders.gift_card_discount,
            orders.surcharge,
            orders.total,
            orders.commission,
            orders.order_date,
            orders.transaction_id,
            orders.authorization_code,
            orders.special_offer_code,
            orders.status,
            orders.reference_code,
            orders.tracking_code,
            orders.utm_source,
            orders.utm_medium,
            orders.utm_campaign,
            orders.utm_term,
            orders.utm_content,
            orders.affiliate_code,
            orders.currency_code,
            orders.http_referer,
            INET_NTOA(ip_address) as ip_address,
            contacts.member_id
        FROM orders
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        $join_order_items
        $join_ship_tos
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY orders.order_number";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $orders = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $orders[$row['id']] = $row;
        $orders[$row['id']]['order_items'] = array();
        $orders[$row['id']]['ship_tos'] = array();
        $orders[$row['id']]['custom_fields'] = array();
    }

    // get ship tos
    $query =
        "SELECT
            ship_tos.id,
            ship_tos.order_id,
            ship_tos.ship_to_name,
            ship_tos.salutation,
            ship_tos.first_name,
            ship_tos.last_name,
            ship_tos.company,
            ship_tos.address_1,
            ship_tos.address_2,
            ship_tos.city,
            ship_tos.state,
            ship_tos.zip_code,
            ship_tos.country,
            ship_tos.address_type,
            ship_tos.address_verified,
            ship_tos.arrival_date_code,
            ship_tos.arrival_date,
            ship_tos.ship_date,
            ship_tos.delivery_date,
            ship_tos.shipping_method_code,
            ship_tos.shipping_cost
        FROM ship_tos
        LEFT JOIN orders on ship_tos.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        $join_order_items
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY ship_tos.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if a ship to exists, add ship tos to orders array
    if (mysqli_num_rows($result) > 0) {
        $ship_tos_exist = true;

        while ($row = mysqli_fetch_assoc($result)) {
            $orders[$row['order_id']]['ship_tos'][$row['id']] = $row;
            $orders[$row['order_id']]['ship_tos'][$row['id']]['order_items'] = array();
            $orders[$row['order_id']]['ship_tos'][$row['id']]['custom_fields'] = array();
            $orders[$row['order_id']]['ship_tos'][$row['id']]['shipping_tracking_numbers'] = array();
        }

    // else a ship to does not exist
    } else {
        $ship_tos_exist = false;
    }

    // get order items
    $query =
        "SELECT
            order_items.id,
            order_items.order_id,
            order_items.ship_to_id,
            order_items.product_name,
            products.short_description,
            order_items.quantity,
            order_items.price,
            order_items.tax,
            order_items.recurring_payment_period,
            order_items.recurring_number_of_payments,
            order_items.recurring_start_date,
            order_items.show_shipped_quantity,
            order_items.shipped_quantity,
            products.custom_field_1,
            products.custom_field_2,
            products.custom_field_3,
            products.custom_field_4,
            products.notes
        FROM order_items
        LEFT JOIN orders on order_items.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        LEFT JOIN products on order_items.product_id = products.id
        $join_ship_tos
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY order_items.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
        // if order item does not have a ship to, then add order item to orders array
        if ($row['ship_to_id'] == 0) {
            $orders[$row['order_id']]['order_items'][$row['id']] = $row;

        // else order item does have a ship to, so add order item to ship tos array in orders array
        } else {
            $orders[$row['order_id']]['ship_tos'][$row['ship_to_id']]['order_items'][$row['id']] = $row;
        }
    }
    
    // get shipping tracking numbers
    $query =
        "SELECT
            shipping_tracking_numbers.id,
            shipping_tracking_numbers.order_id,
            shipping_tracking_numbers.ship_to_id,
            shipping_tracking_numbers.number
        FROM shipping_tracking_numbers
        LEFT JOIN orders on shipping_tracking_numbers.order_id = orders.id
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts on orders.contact_id = contacts.id
        $join_order_items
        $join_ship_tos
        $join_billing_states
        $join_billing_countries
        $join_shipping_states
        $join_shipping_countries
        $where
        $sql_status
        ORDER BY shipping_tracking_numbers.id ASC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
         $orders[$row['order_id']]['ship_tos'][$row['ship_to_id']]['shipping_tracking_numbers'][$row['id']] = $row;
    }

    // if multi currency is enabled
    if (ECOMMERCE_MULTICURRENCY === true) {
        $multi_currency_column_name = '"currency_code",';
    } else {
        $multi_currency_column_name = '';
    }

    // Initialize array that will be used for storing custom field names for custom billing form data.
    $custom_billing_field_names = array();

    // Get all custom billing form data in database.
    $fields = db_items(
        "SELECT
            order_id,
            data,
            name
        FROM form_data
        WHERE
            (order_id != '0')
            AND (ship_to_id = '0')
            AND (order_item_id = '0')
        ORDER BY id ASC");

    // Loop through the fields in order to determine custom field names
    // and add fields to orders array for custom billing form data.
    foreach ($fields as $field) {
        // If this field is for an order that will appear in this export then deal with this field.
        if (isset($orders[$field['order_id']]) == true) {
            // If this field name has not been added to the custom field names array, then add it,
            // so we can keep track of all necessary field names
            if (in_array($field['name'], $custom_billing_field_names) == false) {
                $custom_billing_field_names[] = $field['name'];
            }

            // If there is not already form data for this field, then just add data to the array.
            if ((isset($orders[$field['order_id']]['custom_fields'][$field['name']]) == false) || ($orders[$field['order_id']]['custom_fields'][$field['name']] == '')) {
                $orders[$field['order_id']]['custom_fields'][$field['name']] = $field['data'];
                
            // Otherwise there is already form data for this field, so this is probably a field that supports multiple values,
            // so just append this additional value.
            } else {
                $orders[$field['order_id']]['custom_fields'][$field['name']] .= ', ' . $field['data'];
            }
        }
    }

    // initialize download dialog
    header("Content-type: text/csv");
    header("Content-disposition: attachment; filename=orders.csv");

    // prepare column headings for order records
    echo
        '"order_number",' .
        '"custom_field_1",' .
        '"custom_field_2",' .
        '"billing_salutation",' .
        '"billing_first_name",' .
        '"billing_last_name",' .
        '"billing_email_address",' .
        '"billing_company",' .
        '"billing_address_1",' .
        '"billing_address_2",' .
        '"billing_city",' .
        '"billing_state",' .
        '"billing_zip_code",' .
        '"billing_country",' .
        '"billing_phone_number",' .
        '"billing_fax_number",' .
        '"payment_method",' .
        '"card_type",' .
        '"cardholder",' .
        '"card_number",' .
        '"expiration_month",' .
        '"expiration_year",' .
        '"card_verification_number",' .
        '"referral_source_code",' .
        '"po_number",' .
        '"tax_exempt",' .
        '"opt_in",' .
        '"subtotal",' .
        '"discount",' .
        '"tax",' .
        '"shipping",' .
        '"gift_card_discount",' .
        '"surcharge",' .
        '"total",' .
        '"commission",' .
        '"order_date",' .
        '"transaction_id",' .
        '"authorization_code",' .
        '"special_offer_code",' .
        '"reference_code",' .
        '"tracking_code",' .
        '"utm_source",' .
        '"utm_medium",' .
        '"utm_campaign",' .
        '"utm_term",' .
        '"utm_content",' .
        '"member_id",' .
        '"affiliate_code",' .
        $multi_currency_column_name .
        '"http_referer",' . 
        '"ip_address"';
        
    // Loop through the custom billing field names in order to output them in the order heading row.
    foreach ($custom_billing_field_names as $custom_billing_field_name) {
        echo ',"' . str_replace('"', '""', $custom_billing_field_name) . '"';
    }
    
    echo "\n";

    // if ship tos exist
    if ($ship_tos_exist == true) {
        // get all custom shipping form data in database
        $query =
            "SELECT
                order_id,
                ship_to_id,
                data,
                name
            FROM form_data
            WHERE ship_to_id != '0'
            ORDER BY id ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // initialize array that will be used for storing custom field names for custom shipping form data
        $custom_field_names = array();
        
        // loop through the fields in order to determine custom field names and add fields to orders array for custom shipping form data
        while ($field = mysqli_fetch_assoc($result)) {
            // if this field is for an order that will appear in this export then deal with this field
            if (isset($orders[$field['order_id']]) == TRUE) {
                // if this field name has not been added to the custom field names array, then add it,
                // so we can keep track of all necessary field names
                if (in_array($field['name'], $custom_field_names) == FALSE) {
                    $custom_field_names[] = $field['name'];
                }
                
                // if there is not already form data for this field in the orders array, then just add data to the array
                if ((isset($orders[$field['order_id']]['ship_tos'][$field['ship_to_id']]['custom_fields'][$field['name']]) == FALSE) || ($orders[$field['order_id']]['ship_tos'][$field['ship_to_id']]['custom_fields'][$field['name']] == '')) {
                    $orders[$field['order_id']]['ship_tos'][$field['ship_to_id']]['custom_fields'][$field['name']] = $field['data'];
                    
                // else there is already form data for this field, so this is probably a field that supports multiple values,
                // so just append this additional value
                } else {
                    $orders[$field['order_id']]['ship_tos'][$field['ship_to_id']]['custom_fields'][$field['name']] .= ', ' . $field['data'];
                }
            }
        }
        
        $ship_to_indentation = '"",';
        $order_item_indentation = '"","",';
        $submitted_product_form_indentation = '"","","",';

        // prepare column headings for ship to records
        print
            $ship_to_indentation .
            '"ship_to_name",' .
            '"salutation",' .
            '"first_name",' .
            '"last_name",' .
            '"company",' .
            '"address_1",' .
            '"address_2",' .
            '"city",' .
            '"state",' .
            '"zip_code",' .
            '"country",' .
            '"address_type",' .
            '"arrival_date_code",' .
            '"arrival_date",' .
            '"ship_date",' .
            '"delivery_date",' .
            '"shipping_method_code",' .
            '"shipping_tracking_numbers",' .
            '"shipping_cost"';
        
        // loop through the custom field names in order to output them in the ship to heading row
        foreach ($custom_field_names as $custom_field_name) {
            print ',"' . str_replace('"', '""', $custom_field_name) . '"';
        }
        
        print "\n";
        
    } else {
        $order_item_indentation = '"",';
        $submitted_product_form_indentation = '"","",';
    }

    $output_custom_field_1_heading = '';

    // If the first custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
        $output_custom_field_1_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '",';
    }

    $output_custom_field_2_heading = '';

    // If the second custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
        $output_custom_field_2_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '",';
    }

    $output_custom_field_3_heading = '';

    // If the third custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
        $output_custom_field_3_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '",';
    }

    $output_custom_field_4_heading = '';

    // If the fourth custom product field is active, then output heading for it.
    if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
        $output_custom_field_4_heading = '"' . escape_csv(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '",';
    }

    // prepare column headings for order item records
    print
        $order_item_indentation .
        '"product_name",' .
        '"product_description",' .
        '"quantity",' .
        '"shipped_quantity",' .
        '"price",' .
        '"tax",' .
        '"recurring_payment_period",' .
        '"recurring_number_of_payments",' .
        '"recurring_start_date",' .
        $output_custom_field_1_heading .
        $output_custom_field_2_heading .
        $output_custom_field_3_heading .
        $output_custom_field_4_heading .
        '"notes"' . "\n";

    // loop through orders in order to prepare data
    foreach ($orders as $order) {
        // loop through all values for this order in order to replace quotation marks with two quotation marks
        foreach ($order as $key => $value) {
            // if value is not the order items or ship tos array, replace quotation mark with two quotation marks
            if (($key != 'order_items') && ($key != 'ship_tos')) {
               $value = str_replace('"', '""', $value);
               $order[$key] = $value;
            }
        }
        
        $card_number = $order['card_number'];
        
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
        
        $card_verification_number = $order['card_verification_number'];
        
        // if the card verification number is not already protected,
        // and the user does not have access to view card data,
        // then protect it
        if (
            (mb_substr($card_verification_number, 0, 1) != '*')
            && (($user['role'] == 3) && ($user['view_card_data'] == FALSE))
        ) {
            $card_verification_number = protect_card_verification_number($card_verification_number);
        }

        if ($order['tax_exempt']) {
            $tax_exempt = 'yes';
        } else {
            $tax_exempt = 'no';
        }

        if ($order['opt_in']) {
            $opt_in = 'yes';
        } else {
            $opt_in = 'no';
        }

        // If the date format is month and then day, then use that format.
        if (DATE_FORMAT == 'month_day') {
            $month_and_day_format = 'n/j';

        // Otherwise the date format is day and then month, so use that format.
        } else {
            $month_and_day_format = 'j/n';
        }

        // if multi currency is enabled
        if (ECOMMERCE_MULTICURRENCY === true) {
            $currency_code = '"' . $order['currency_code'] . '",';
        } else {
            $currency_code = '';
        }
        
        $ip_address = $order['ip_address'];
        
        // if the IP address is 0.0.0.0, then we don't know the IP address, so set the value to empty string
        if ($ip_address == '0.0.0.0') {
            $ip_address = '';
        }

        // prepare order row
        echo
            '"' . $order['order_number'] . '",' .
            '"' . $order['custom_field_1'] . '",' .
            '"' . $order['custom_field_2'] . '",' .
            '"' . $order['billing_salutation'] . '",' .
            '"' . $order['billing_first_name'] . '",' .
            '"' . $order['billing_last_name'] . '",' .
            '"' . $order['billing_email_address'] . '",' .
            '"' . $order['billing_company'] . '",' .
            '"' . $order['billing_address_1'] . '",' .
            '"' . $order['billing_address_2'] . '",' .
            '"' . $order['billing_city'] . '",' .
            '"' . $order['billing_state'] . '",' .
            '"' . $order['billing_zip_code'] . '",' .
            '"' . $order['billing_country'] . '",' .
            '"' . $order['billing_phone_number'] . '",' .
            '"' . $order['billing_fax_number'] . '",' .
            '"' . $order['payment_method'] . '",' .
            '"' . $order['card_type'] . '",' .
            '"' . $order['cardholder'] . '",' .
            '"#' . $card_number . '",' .
            '"' . $order['expiration_month'] . '",' .
            '"' . $order['expiration_year'] . '",' .
            '"' . $card_verification_number . '",' .
            '"' . $order['referral_source_code'] . '",' .
            '"' . $order['po_number'] . '",' .
            '"' . $tax_exempt . '",' .
            '"' . $opt_in . '",' .
            '"' . sprintf("%01.2lf", $order['subtotal'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['discount'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['tax'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['shipping'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['gift_card_discount'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['surcharge'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['total'] / 100) . '",' .
            '"' . sprintf("%01.2lf", $order['commission'] / 100) . '",' .
            '"' . date($month_and_day_format . '/Y g:i:s A T', $order['order_date']) . '",' .
            '"' . $order['transaction_id'] . '",' .
            '"' . $order['authorization_code'] . '",' .
            '"' . $order['special_offer_code'] . '",' .
            '"' . $order['reference_code'] . '",' .
            '"' . $order['tracking_code'] . '",' .
            '"' . $order['utm_source'] . '",' .
            '"' . $order['utm_medium'] . '",' .
            '"' . $order['utm_campaign'] . '",' .
            '"' . $order['utm_term'] . '",' .
            '"' . $order['utm_content'] . '",' .
            '"' . $order['member_id'] . '",' .
            '"' . $order['affiliate_code'] . '",' .
            $currency_code .
            '"' . $order['http_referer'] . '",' .
            '"' . $ip_address . '"';
        
        // Loop through the custom billing field names in order to output custom billing form data for this order row.
        foreach ($custom_billing_field_names as $custom_billing_field_name) {
            echo ',"' . str_replace('"', '""', $order['custom_fields'][$custom_billing_field_name]) . '"';
        }
        
        echo "\n";

        // loop through order items for this order in order to prepare data
        foreach ($order['order_items'] as $order_item) {
            // for each value in the row
            foreach ($order_item as $key => $value) {
               // replace quotation mark with two quotation marks
               $value = str_replace('"', '""', $value);
               // set new value
               $order_item[$key] = $value;
            }
            
            $shipped_quantity = '';
            
            // if shipped quantity should be shown then show it
            if ($order_item['show_shipped_quantity'] == 1) {
                $shipped_quantity = $order_item['shipped_quantity'];
            }

            $recurring_payment_period = '';
            $recurring_number_of_payments = '';
            $recurring_start_date = '';

            // if order item is a recurring order item, then prepare values
            if ($order_item['recurring_payment_period'] != '') {
                $recurring_payment_period = $order_item['recurring_payment_period'];
                $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                $recurring_start_date = prepare_form_data_for_output($order_item['recurring_start_date'], 'date');
            }

            $output_custom_field_1 = '';

            // If the first custom product field is active, then output value for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                $output_custom_field_1 = '"' . escape_csv($order_item['custom_field_1']) . '",';
            }

            $output_custom_field_2 = '';

            // If the second custom product field is active, then output value for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                $output_custom_field_2 = '"' . escape_csv($order_item['custom_field_2']) . '",';
            }

            $output_custom_field_3 = '';

            // If the third custom product field is active, then output value for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                $output_custom_field_3 = '"' . escape_csv($order_item['custom_field_3']) . '",';
            }

            $output_custom_field_4 = '';

            // If the fourth custom product field is active, then output value for it.
            if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                $output_custom_field_4 = '"' . escape_csv($order_item['custom_field_4']) . '",';
            }

            // prepare order item row
            print
                $order_item_indentation .
                '"' . $order_item['product_name'] . '",' .
                '"' . $order_item['short_description'] . '",' .
                '"' . $order_item['quantity'] . '",' .
                '"' . $shipped_quantity . '",' .
                '"' . sprintf("%01.2lf", $order_item['price'] / 100) . '",' .
                '"' . sprintf("%01.2lf", $order_item['tax'] / 100) . '",' .
                '"' . $recurring_payment_period . '",' .
                '"' . $recurring_number_of_payments . '",' .
                '"' . $recurring_start_date . '",' .
                $output_custom_field_1 .
                $output_custom_field_2 .
                $output_custom_field_3 .
                $output_custom_field_4 .
                '"' . $order_item['notes'] . '"' . "\n";

            // get maximum quantity number, so we can determine how many product forms there are for this order item
            $query = "SELECT MAX(quantity_number) as number_of_forms FROM form_data WHERE order_item_id = '" . $order_item['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $number_of_forms = $row['number_of_forms'];

            // if there is a form for this order item, then prepare to output form
            if ($number_of_forms > 0) {
                $heading_row = $submitted_product_form_indentation;
                $data_rows = '';

                // create loop in order to output all forms
                for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                    // get form data items for order item
                    $query =
                        "SELECT
                            form_field_id,
                            data,
                            name,
                            count(*) as number_of_values
                        FROM form_data
                        WHERE
                            (order_item_id = '" . $order_item['id'] . "')
                            AND (quantity_number = '$quantity_number')
                        GROUP BY form_field_id
                        ORDER BY id";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $form_data_items = array();

                    // loop through form data items in order to add them to array
                    while ($row = mysqli_fetch_assoc($result)) {
                        $form_data_items[] = $row;
                    }

                    // loop through form data items in order to prepare rows of data
                    foreach ($form_data_items as $key => $form_data_item) {
                        // if this is the first product form, then prepare heading row
                        if ($quantity_number == 1) {
                            // if this is not the first form data item, then add a comma to the heading
                            if ($key != 0) {
                                $heading_row .= ',';
                            }

                            $heading_row .= '"' . str_replace('"', '""', $form_data_item['name']) . '"';

                            // if this is the last data item, then add a new line
                            if ($key == (count($form_data_items) - 1)) {
                                $heading_row .= "\n";
                            }
                        }

                        // if this is the first form data item, then add indentation
                        if ($key == 0) {
                            $data_rows .= $submitted_product_form_indentation;

                        // else this is not the first form data item, so add a comma
                        } else {
                            $data_rows .= ',';
                        }

                        $data = '';

                        // if there is more than one value, then get all values so data can be set to all values
                        if ($form_data_item['number_of_values'] > 1) {
                            $query =
                                "SELECT data
                                FROM form_data
                                WHERE
                                    (order_item_id = '" . $order_item['id'] . "')
                                    AND (quantity_number = '$quantity_number')
                                    AND (form_field_id = '" . $form_data_item['form_field_id'] . "')
                                ORDER BY id";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            while ($row = mysqli_fetch_assoc($result)) {
                                // if data is not empty, then add comma and space
                                if ($data != '') {
                                    $data .= ', ';
                                }

                                $data .= $row['data'];
                            }

                        // else there is just one value, so set data
                        } else {
                            $data = $form_data_item['data'];
                        }

                        $data_rows .= '"' . str_replace('"', '""', $data) . '"';

                        // if this is the last data item, then add a new line
                        if ($key == (count($form_data_items) - 1)) {
                            $data_rows .= "\n";
                        }
                    }
                }

                print $heading_row;
                print $data_rows;
            }
        }

        // loop through ship tos for this order in order to prepare data
        foreach ($order['ship_tos'] as $ship_to) {
            // loop through all values for this ship to in order to replace quotation marks with two quotation marks
            foreach ($ship_to as $key => $value) {
                // if value is not the order items and shipping_tracking_numbers arrays, replace quotation mark with two quotation marks
                if (($key != 'order_items') && ($key != 'shipping_tracking_numbers')) {
                   $value = str_replace('"', '""', $value);
                   $ship_to[$key] = $value;
                }
            }
            
            // if this shipping address is verified, then convert salutation and country to all uppercase
            if ($ship_to['address_verified'] == 1) {
                $ship_to['salutation'] = mb_strtoupper($ship_to['salutation']);
                $ship_to['country'] = mb_strtoupper($ship_to['country']);
            }
            
            $shipping_tracking_number_list = '';

            if (is_array($ship_to['shipping_tracking_numbers']) == true) {
                foreach ($ship_to['shipping_tracking_numbers'] as $shipping_tracking_number) {
                    // if this is not the first shipping tracking number then add a comma and space for separation
                    if ($shipping_tracking_number_list != '') {
                        $shipping_tracking_number_list .= ', ';
                    }
                    
                    $shipping_tracking_number_list .= str_replace('"', '""', $shipping_tracking_number['number']);
                }
            }

            // prepare ship to row
            print
                $ship_to_indentation .
                '"' . $ship_to['ship_to_name'] . '",' .
                '"' . $ship_to['salutation'] . '",' .
                '"' . $ship_to['first_name'] . '",' .
                '"' . $ship_to['last_name'] . '",' .
                '"' . $ship_to['company'] . '",' .
                '"' . $ship_to['address_1'] . '",' .
                '"' . $ship_to['address_2'] . '",' .
                '"' . $ship_to['city'] . '",' .
                '"' . $ship_to['state'] . '",' .
                '"' . $ship_to['zip_code'] . '",' .
                '"' . $ship_to['country'] . '",' .
                '"' . $ship_to['address_type'] . '",' .
                '"' . $ship_to['arrival_date_code'] . '",' .
                '"' . prepare_form_data_for_output($ship_to['arrival_date'], 'date') . '",' .
                '"' . prepare_form_data_for_output($ship_to['ship_date'], 'date') . '",' .
                '"' . prepare_form_data_for_output($ship_to['delivery_date'], 'date') . '",' .
                '"' . $ship_to['shipping_method_code'] . '",' .
                '"' . $shipping_tracking_number_list . '",' .
                '"' . sprintf("%01.2lf", $ship_to['shipping_cost'] / 100) . '"';
            
            // loop through the custom field names in order to output custom shipping form data for this ship to row
            foreach ($custom_field_names as $custom_field_name) {
                print ',"' . str_replace('"', '""', $ship_to['custom_fields'][$custom_field_name]) . '"';
            }
            
            print "\n";

            // loop through order items for this ship to in order to prepare data
            foreach ($ship_to['order_items'] as $order_item) {
                // for each value in the row
                foreach ($order_item as $key => $value) {
                   // replace quotation mark with two quotation marks
                   $value = str_replace('"', '""', $value);
                   // set new value
                   $order_item[$key] = $value;
                }
                
                $shipped_quantity = '';
                
                // if shipped quantity should be shown then show it
                if ($order_item['show_shipped_quantity'] == 1) {
                    $shipped_quantity = $order_item['shipped_quantity'];
                }

                $recurring_payment_period = '';
                $recurring_number_of_payments = '';
                $recurring_start_date = '';

                // if order item is a recurring order item, then prepare values
                if ($order_item['recurring_payment_period'] != '') {
                    $recurring_payment_period = $order_item['recurring_payment_period'];
                    $recurring_number_of_payments = $order_item['recurring_number_of_payments'];
                    $recurring_start_date = prepare_form_data_for_output($order_item['recurring_start_date'], 'date');
                }

                $output_custom_field_1 = '';

                // If the first custom product field is active, then output value for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
                    $output_custom_field_1 = '"' . escape_csv($order_item['custom_field_1']) . '",';
                }

                $output_custom_field_2 = '';

                // If the second custom product field is active, then output value for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
                    $output_custom_field_2 = '"' . escape_csv($order_item['custom_field_2']) . '",';
                }

                $output_custom_field_3 = '';

                // If the third custom product field is active, then output value for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
                    $output_custom_field_3 = '"' . escape_csv($order_item['custom_field_3']) . '",';
                }

                $output_custom_field_4 = '';

                // If the fourth custom product field is active, then output value for it.
                if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
                    $output_custom_field_4 = '"' . escape_csv($order_item['custom_field_4']) . '",';
                }

                // prepare order item row
                print
                    $order_item_indentation .
                    '"' . $order_item['product_name'] . '",' .
                    '"' . $order_item['short_description'] . '",' .
                    '"' . $order_item['quantity'] . '",' .
                    '"' . $shipped_quantity . '",' .
                    '"' . sprintf("%01.2lf", $order_item['price'] / 100) . '",' .
                    '"' . sprintf("%01.2lf", $order_item['tax'] / 100) . '",' .
                    '"' . $recurring_payment_period . '",' .
                    '"' . $recurring_number_of_payments . '",' .
                    '"' . $recurring_start_date . '",' .
                    $output_custom_field_1 .
                    $output_custom_field_2 .
                    $output_custom_field_3 .
                    $output_custom_field_4 .
                    '"' . $order_item['notes'] . '"' . "\n";

                // get maximum quantity number, so we can determine how many product forms there are for this order item
                $query = "SELECT MAX(quantity_number) as number_of_forms FROM form_data WHERE order_item_id = '" . $order_item['id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $number_of_forms = $row['number_of_forms'];

                // if there is a form for this order item, then prepare to output form
                if ($number_of_forms > 0) {
                    $heading_row = $submitted_product_form_indentation;
                    $data_rows = '';

                    // create loop in order to output all forms
                    for ($quantity_number = 1; $quantity_number <= $number_of_forms; $quantity_number++) {
                        // get form data items for order item
                        $query =
                            "SELECT
                                form_field_id,
                                data,
                                name,
                                count(*) as number_of_values
                            FROM form_data
                            WHERE
                                (order_item_id = '" . $order_item['id'] . "')
                                AND (quantity_number = '$quantity_number')
                            GROUP BY form_field_id
                            ORDER BY id";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $form_data_items = array();

                        // loop through form data items in order to add them to array
                        while ($row = mysqli_fetch_assoc($result)) {
                            $form_data_items[] = $row;
                        }

                        // loop through form data items in order to prepare rows of data
                        foreach ($form_data_items as $key => $form_data_item) {
                            // if this is the first product form, then prepare heading row
                            if ($quantity_number == 1) {
                                // if this is not the first form data item, then add a comma to the heading
                                if ($key != 0) {
                                    $heading_row .= ',';
                                }

                                $heading_row .= '"' . str_replace('"', '""', $form_data_item['name']) . '"';

                                // if this is the last data item, then add a new line
                                if ($key == (count($form_data_items) - 1)) {
                                    $heading_row .= "\n";
                                }
                            }

                            // if this is the first form data item, then add indentation
                            if ($key == 0) {
                                $data_rows .= $submitted_product_form_indentation;

                            // else this is not the first form data item, so add a comma
                            } else {
                                $data_rows .= ',';
                            }

                            $data = '';

                            // if there is more than one value, then get all values so data can be set to all values
                            if ($form_data_item['number_of_values'] > 1) {
                                $query =
                                    "SELECT data
                                    FROM form_data
                                    WHERE
                                        (order_item_id = '" . $order_item['id'] . "')
                                        AND (quantity_number = '$quantity_number')
                                        AND (form_field_id = '" . $form_data_item['form_field_id'] . "')
                                    ORDER BY id";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                                while ($row = mysqli_fetch_assoc($result)) {
                                    // if data is not empty, then add comma and space
                                    if ($data != '') {
                                        $data .= ', ';
                                    }

                                    $data .= $row['data'];
                                }

                            // else there is just one value, so set data
                            } else {
                                $data = $form_data_item['data'];
                            }

                            $data_rows .= '"' . str_replace('"', '""', $data) . '"';

                            // if this is the last data item, then add a new line
                            if ($key == (count($form_data_items) - 1)) {
                                $data_rows .= "\n";
                            }
                        }
                    }

                    print $heading_row;
                    print $data_rows;
                }
            }
        }

        // If the status of this order is complete, then update the status to be exported.
        if ($order['status'] == 'complete') {
            db("UPDATE orders SET status = 'exported' WHERE id = '" . $order['id'] . "'");
        }
    }

// else user did not request to export orders, so view orders
} else {
    $statuses = array();

    $statuses[] = array('label' => '[Any]', 'value' => 'any');
    $statuses[] = array('label' => 'Incomplete', 'value' => 'incomplete');
    $statuses[] = array('label' => 'Complete', 'value' => 'complete');
    $statuses[] = array('label' => 'Exported', 'value' => 'exported');
    $statuses[] = array('label' => 'Complete or Exported', 'value' => 'complete_or_exported');

    $output_status_options = '';

    // Loop through the statuses in order to prepare pick list options.
    foreach ($statuses as $status) {
        $selected = '';
        
        // If this is the selected status, then select it.
        if ($status['value'] == $_SESSION['software']['ecommerce']['view_orders']['status']) {
            $selected = ' selected="selected"';
        }
        
        $output_status_options .= '<option value="' . h($status['value']) . '"' . $selected . '>' . h($status['label']) . '</option>';
    }

    // get oldest timestamp
    $query = "SELECT MIN(order_date) FROM orders";
    $result = mysqli_query(db::$con, $query) or output_error("Query failed.");
    $row = mysqli_fetch_row($result);
    $oldest_timestamp = $row[0];

    // get minimum year from oldest timestamp
    $oldest_year = date('Y', $oldest_timestamp);
    if ($_SESSION['software']['ecommerce']['view_orders']['start_year'] < $oldest_year) {
        $oldest_year = $_SESSION['software']['ecommerce']['view_orders']['start_year'];
    }

    $this_year = date('Y', strtotime('+1 year'));
    if ($_SESSION['software']['ecommerce']['view_orders']['stop_year'] > $this_year) {
        $this_year = $_SESSION['software']['ecommerce']['view_orders']['stop_year'];
    }

    $years = array();

    // create html for year options
    for ($i = $oldest_year; $i <= $this_year; $i++) {
        $years[] = $i;
    }

    // if sort was set, update session
    if (isset($_REQUEST['sort'])) {
        // store sort in session
        $_SESSION['software']['ecommerce']['view_orders']['sort'] = $_REQUEST['sort'];

        // clear order
        $_SESSION['software']['ecommerce']['view_orders']['order'] = '';
    }

    // if order was set, update session
    if (isset($_REQUEST['order'])) {
        $_SESSION['software']['ecommerce']['view_orders']['order'] = $_REQUEST['order'];
    }

    // If a screen was passed and it is a positive integer, then use it.
    // These checks are necessary in order to avoid SQL errors below for a bogus screen value.
    if (
        $_REQUEST['screen']
        and is_numeric($_REQUEST['screen'])
        and $_REQUEST['screen'] > 0
        and $_REQUEST['screen'] == round($_REQUEST['screen'])
    ) {
        $screen = (int) $_REQUEST['screen'];

    // Otherwise, use the default, which is the first screen.
    } else {
        $screen = 1;
    }

    switch ($_SESSION['software']['ecommerce']['view_orders']['sort']) {
        case 'First Name':
            $sort_column = 'orders.billing_first_name';
            break;

        case 'Last Name':
            $sort_column = 'orders.billing_last_name';
            break;

        case 'Status':
            $sort_column = 'orders.status';
            break;

        case 'Order Number':
            $sort_column = 'orders.order_number';
            break;

        case 'User':
            $sort_column = 'user.user_username';
            break;

        case 'Tracking Code':
            $sort_column = 'orders.tracking_code';
            break;

        case MEMBER_ID_LABEL:
            $sort_column = 'contacts.member_id';
            break;

        case 'Affiliate Code':
            $sort_column = 'orders.affiliate_code';
            break;

        case 'Total':
            $sort_column = 'orders.total';
            break;

        case 'Order Date':
            $sort_column = 'orders.order_date';
            break;

        default:
            $sort_column = 'orders.order_date';
            $_SESSION['software']['ecommerce']['view_orders']['sort'] = 'Order Date';
    }

    if ($_SESSION['software']['ecommerce']['view_orders']['order']) {
        $asc_desc = $_SESSION['software']['ecommerce']['view_orders']['order'];
    } elseif ($sort_column == 'orders.order_date') {
        $asc_desc = 'desc';
        $_SESSION['software']['ecommerce']['view_orders']['order'] = 'desc';
    } else {
        $asc_desc = 'asc';
        $_SESSION['software']['ecommerce']['view_orders']['order'] = 'asc';
    }

    /* define range depending on screen value by using a limit clause in the SQL statement */
    // define the maximum number of results
    $max = 100;
    // determine where result set should start
    $start = $screen * $max - $max;
    $limit = "LIMIT $start, $max";

    // Get all orders
    $query = "SELECT count(distinct(orders.id)) as count
             FROM orders";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $all_orders = $row['count'];

    // get total number of results for all screens, so that we can output links to different screens
    $query = "SELECT count(distinct(orders.id)) as count
             FROM orders
             LEFT JOIN user ON orders.user_id = user.user_id
             LEFT JOIN contacts on orders.contact_id = contacts.id
             $join_order_items
             $join_ship_tos
             $join_billing_states
             $join_billing_countries
             $join_shipping_states
             $join_shipping_countries
             $where
             $sql_status";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $number_of_results = $row['count'];

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_orders.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_orders.php?screen=\' + this.options[this.selectedIndex].value)">';

        // build HTML output for links to screens
        for ($i = 1; $i <= $number_of_screens; $i++) {
            // if this number is the current screen, then select option
            if ($i == $screen) {
                $selected = ' selected="selected"';
            // else this number is not the current screen, so do not select option
            } else {
                $selected = '';
            }

            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }

        $output_screen_links .= '</select>';
    }

    // build Next button if necessary
    $next = $screen + 1;
    // if next screen is less than or equal to the total number of screens, output next link
    if ($next <= $number_of_screens) {
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_orders.php?screen=' . $next . '">&gt;</a>';
    }
    
    if (AFFILIATE_PROGRAM == true) {
        $output_affiliate_heading = '<th>' . get_column_heading('Affiliate Code', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>';
    }

    $output_shipping_tracking_heading = '';

    if (ECOMMERCE_SHIPPING == true) {
        $output_shipping_tracking_heading = '<th>&nbsp;</th>';
    }

    /* get results for just this screen*/
    $query = "SELECT
                orders.id,
                orders.billing_first_name,
                orders.billing_last_name,
                orders.status,
                orders.order_number,
                user.user_username as username,
                orders.tracking_code,
                contacts.member_id,
                orders.affiliate_code,
                orders.card_number,
                orders.card_verification_number,
                orders.total,
                orders.order_date
             FROM orders
             LEFT JOIN user ON orders.user_id = user.user_id
             LEFT JOIN contacts on orders.contact_id = contacts.id
             $join_order_items
             $join_ship_tos
             $join_billing_states
             $join_billing_countries
             $join_shipping_states
             $join_shipping_countries
             $where
             $sql_status
             GROUP BY orders.id
             ORDER BY $sort_column $asc_desc
             $limit";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $billing_first_name = $row['billing_first_name'];
        $billing_last_name = $row['billing_last_name'];
        $status = $row['status'];
        $order_number = $row['order_number'];
        $username = $row['username'];
        $tracking_code = $row['tracking_code'];
        $member_id = $row['member_id'];
        $affiliate_code = $row['affiliate_code'];
        $card_number = $row['card_number'];
        $card_verification_number = $row['card_verification_number'];
        $total = $row['total'] / 100;
        $order_date = get_relative_time(array('timestamp' => $row['order_date']));
        
        $output_link_url = 'view_order.php?id=' . $id . '&send_to=' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/view_orders.php';

        if (AFFILIATE_PROGRAM == true) {
            $output_affiliate_code = '<td onclick="window.location.href=\'' . $output_link_url . '\'" class="' . $row_style .' pointer">' . h($affiliate_code) . '</td>';
        } else {
            $output_affiliate_code = '';
        }
        
        $output_card_data_check_mark = '';
        
        // if there is a credit card number and the credit card number is not protected or there is a card verification number and it is not protected, then output check mark
        if (
            (($card_number != '') && (mb_substr($card_number, 0, 1) != '*'))
            || (($card_verification_number != '') && (mb_substr($card_verification_number, 0, 1) != '*'))
        ) {
            $output_card_data_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }

        $output_shipping_tracking_cell = '';

        // If shipping is enabled, then get shipping tracking numbers and output a icon for each one.
        if (ECOMMERCE_SHIPPING == true) {
            $output_shipping_tracking_icons = '';

            // If the order is complete, then continue to get shipping tracking info.
            if ($status != 'incomplete') {
                $shipping_tracking_numbers = db_values(
                    "SELECT number
                    FROM shipping_tracking_numbers
                    WHERE order_id = '" . $id . "'
                    ORDER BY
                        ship_to_id ASC,
                        id ASC");
                
                // Loop through the shipping tracking numbers in order to output icons for them.
                foreach ($shipping_tracking_numbers as $shipping_tracking_number) {
                    $shipping_tracking_url = get_shipping_tracking_url($shipping_tracking_number,$ship_to['shipping_method_code']);
                    
                    // If a shipping tracking url was found, then output icon with a link.
                    if ($shipping_tracking_url != '') {
                        $output_shipping_tracking_icons .= '<a href="' . h($shipping_tracking_url) . '" target="_blank"><img src="images/icon_truck.png" width="25" height="17" border="0" alt="Shipment Tracking" title="Shipment Tracking" style="padding: 0.2em" /></a>';
                    
                    // Otherwise, just output the icon with no link.
                    } else {
                        $output_shipping_tracking_icons .= '<img src="images/icon_truck.png" width="25" height="17" border="0" alt="Shipment Tracking" title="Shipment Tracking" style="padding: 0.2em" />';
                    }
                }
            }
            
            $output_shipping_tracking_cell = '<td style="text-align: center">' . $output_shipping_tracking_icons . '</td>';
        }

        $firstChar = strtoupper (mb_substr($billing_first_name, 0, 1, "UTF-8")) . strtoupper (mb_substr($billing_last_name, 0, 1, "UTF-8"));
        $output_rows .=
            '<tr id="' . $id . '">
                <td class="selectall"><input type="checkbox" name="orders[]" value="' . $id . '" class="checkbox" /></td>
                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 37 37" style="height:37px;width:37px;">
                        <g>
                          <circle style="fill:#689f38;" cx="18.5" cy="18.5" r="18.5"></circle>
                          <text style="fill:#f5f5f6;font-size:12.5" x="18.5" y="18.5" text-anchor="middle" dy=".3em">' . $firstChar  . '</text>
                        </g>
                    </svg>
                </td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($billing_first_name) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($billing_last_name) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . ucwords($status) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $order_number . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($username) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($tracking_code) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($member_id) . '</td>
                ' . $output_affiliate_code . '
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_card_data_check_mark . '</td>
                ' . $output_shipping_tracking_cell . '
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: right; padding-right: 1em;">' . prepare_amount($total) . '</td>
                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $order_date . '</td>
            </tr>';
    }

    // if the advanced filters are off
    if ($_SESSION['software']['ecommerce']['view_orders']['advanced_filters'] == false) {
        $output_advanced_filters_value = 'true';
        $output_advanced_filters_label = 'Add Advanced Filters';
        $output_advanced_filters = '';
        $advanced_filters_icon = 'off';

    // else the advanced filters are on
    } else {
        $output_advanced_filters_value = 'false';
        $output_advanced_filters_label = 'Remove Advanced Filters';
        $advanced_filters_icon = 'on';

        // prepare selection for payment method field
        if ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'any') {
            $payment_method_any_selected = ' selected="selected"';
        } elseif ((isset($_SESSION['software']['ecommerce']['view_orders']['payment_method']) == true) && ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == '')) {
            $payment_method_none_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'Credit/Debit Card') {
            $payment_method_credit_debit_card_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'PayPal Express Checkout') {
            $payment_method_paypal_express_checkout_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['payment_method'] == 'Offline Payment') {
            $payment_method_offline_selected = ' selected="selected"';
        }

        if (AFFILIATE_PROGRAM == true) {
            $output_affiliate =
                '<fieldset style="padding: 0px 10px 10px 10px">
                    <legend><strong>Affiliate</strong></legend>
                    <table>
                        <tr>
                            <td>Affiliate Code:</td>
                            <td><input type="text" name="affiliate_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['affiliate_code']) . '" /></td>
                        </tr>
                    </table>
                </fieldset>';
        }

        // prepare selection for opt-in status
        if ($_SESSION['software']['ecommerce']['view_orders']['opt_in_status'] == 'any') {
            $opt_in_status_any_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['opt_in_status'] == 'opt_in') {
            $opt_in_status_opt_in_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['opt_in_status'] == 'opt_out') {
            $opt_in_status_opt_out_selected = ' selected="selected"';
        }

        // prepare selection for tax status
        if ($_SESSION['software']['ecommerce']['view_orders']['tax_status'] == 'any') {
            $tax_status_any_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['tax_status'] == 'tax_exempt') {
            $tax_status_tax_exempt_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['tax_status'] == 'not_tax_exempt') {
            $tax_status_not_tax_exempt_selected = ' selected="selected"';
        }

        // if multi currency is enabled, output miscellaneous fieldset with currency pick list
        if (ECOMMERCE_MULTICURRENCY === true) {
            $output_currency_row =
                '<tr>
                    <td>Currency:</td>
                    <td><select name="currency_code"><option value="">Any</option>' . get_currency_options($_SESSION['software']['ecommerce']['view_orders']['currency_code']) . '</select></td>
                </tr>';
        } else {
            $output_currency_row = '';
        }
        
        // prepare selection for address type field
        if ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == 'any') {
            $address_type_any_selected = ' selected="selected"';
        } elseif ((isset($_SESSION['software']['ecommerce']['view_orders']['address_type']) == true) && ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == '')) {
            $address_type_none_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == 'residential') {
            $address_type_residential_selected = ' selected="selected"';
        } elseif ($_SESSION['software']['ecommerce']['view_orders']['address_type'] == 'business') {
            $address_type_business_selected = ' selected="selected"';
        }

        // Prepare selection for shipping status field.
        if ($_SESSION['software']['ecommerce']['view_orders']['shipping_status'] == 'any') {
            $shipping_status_any_selected = ' selected="selected"';

        } elseif ($_SESSION['software']['ecommerce']['view_orders']['shipping_status'] == 'unshipped') {
            $shipping_status_unshipped_selected = ' selected="selected"';

        } elseif ($_SESSION['software']['ecommerce']['view_orders']['shipping_status'] == 'shipped') {
            $shipping_status_shipped_selected = ' selected="selected"';
        }

        $output_date_type_field = '';

        // If shipping is enabled then output date type field.
        if (ECOMMERCE_SHIPPING == true) {
            if ($_SESSION['software']['ecommerce']['view_orders']['date_type'] == 'order_date') {
                $date_type_order_date_selected = ' selected="selected"';

            } elseif ($_SESSION['software']['ecommerce']['view_orders']['date_type'] == 'ship_date') {
                $date_type_ship_date_selected = ' selected="selected"';
            }

            $output_date_type_field = '<select name="date_type"><option value="order_date"' . $date_type_order_date_selected . '>Order Date</option><option value="ship_date"' . $date_type_ship_date_selected . '>Ship Date</option></select>&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        $output_advanced_filters =
            '<div class="advanced_filters">
             <div style="margin-bottom: 10px">
                <table style="width: 100%">
                    <tr>
                        <td style="vertical-align: top; padding-right: 10px">
                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">
                                <legend><strong>Order</strong></legend>
                                <table>
                                    <tr>
                                        <td>Order Number:</td>
                                        <td><input type="text" name="order_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['order_number']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Transaction ID:</td>
                                        <td><input type="text" name="transaction_id" value="' . h($_SESSION['software']['ecommerce']['view_orders']['transaction_id']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Authorization Code:</td>
                                        <td><input type="text" name="authorization_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['authorization_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Special Offer Code:</td>
                                        <td><input type="text" name="special_offer_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['special_offer_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Referral Source Code:</td>
                                        <td><input type="text" name="referral_source_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['referral_source_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Reference Code:</td>
                                        <td><input type="text" name="reference_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['reference_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Tracking Code:</td>
                                        <td><input type="text" name="tracking_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['tracking_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Referring URL:</td>
                                        <td><input type="text" name="http_referer" value="' . h($_SESSION['software']['ecommerce']['view_orders']['http_referer']) . '" /></td>
                                    </tr>
                                    ' . $output_currency_row . '
                                    <tr>
                                        <td>Customer\'s IP Address:</td>
                                        <td><input type="text" name="ip_address" value="' . h($_SESSION['software']['ecommerce']['view_orders']['ip_address']) . '" /></td>
                                    </tr>
                                </table>
                            </fieldset>
                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">
                                <legend><strong>Product</strong></legend>
                                <table>
                                    <tr>
                                        <td>Product Name:</td>
                                        <td><input type="text" name="product_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['product_name']) . '" /></td>
                                    </tr>
                                </table>
                            </fieldset>
                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">
                                <legend><strong>Payment</strong></legend>
                                <table>
                                    <tr>
                                        <td>Payment Method:</td>
                                        <td><select name="payment_method"><option value="any"' . $payment_method_any_selected . '>Any</option><option value=""' . $payment_method_none_selected . '>None</option><option value="Credit/Debit Card"' . $payment_method_credit_debit_card_selected . '>Credit/Debit Card</option><option value="PayPal Express Checkout"' . $payment_method_paypal_express_checkout_selected . '>PayPal Express Checkout</option><option value="Offline Payment"' . $payment_method_offline_selected . '>Offline Payment</option></select></td>
                                    </tr>
                                    <tr>
                                        <td>Card Type:</td>
                                        <td><input type="text" name="card_type" value="' . h($_SESSION['software']['ecommerce']['view_orders']['card_type']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Cardholder:</td>
                                        <td><input type="text" name="cardholder" value="' . h($_SESSION['software']['ecommerce']['view_orders']['cardholder']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Card Number:</td>
                                        <td><input type="text" name="card_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['card_number']) . '" /></td>
                                    </tr>
                                </table>
                            </fieldset>
                            ' . $output_affiliate . '
                        </td>
                        <td style="vertical-align: top; padding-right: 10px">
                            <fieldset style="padding: 0px 10px 10px 10px">
                                <legend><strong>Billing</strong></legend>
                                <table>
                                    <tr>
                                        <td>Custom Field #1:</td>
                                        <td><input type="text" name="custom_field_1" value="' . h($_SESSION['software']['ecommerce']['view_orders']['custom_field_1']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Custom Field #2:</td>
                                        <td><input type="text" name="custom_field_2" value="' . h($_SESSION['software']['ecommerce']['view_orders']['custom_field_2']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Salutation:</td>
                                        <td><input type="text" name="billing_salutation" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_salutation']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>First Name:</td>
                                        <td><input type="text" name="billing_first_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_first_name']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Last Name:</td>
                                        <td><input type="text" name="billing_last_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_last_name']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Company:</td>
                                        <td><input type="text" name="billing_company" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_company']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Address 1:</td>
                                        <td><input type="text" name="billing_address_1" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_address_1']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Address 2:</td>
                                        <td><input type="text" name="billing_address_2" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_address_2']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>City:</td>
                                        <td><input type="text" name="billing_city" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_city']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>State:</td>
                                        <td><input type="text" name="billing_state" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_state']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Zip Code:</td>
                                        <td><input type="text" name="billing_zip_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_zip_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Country:</td>
                                        <td><input type="text" name="billing_country" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_country']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Phone:</td>
                                        <td><input type="text" name="billing_phone_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_phone_number']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Fax:</td>
                                        <td><input type="text" name="billing_fax_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_fax_number']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Email:</td>
                                        <td><input type="text" name="billing_email_address" value="' . h($_SESSION['software']['ecommerce']['view_orders']['billing_email_address']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Opt-In Status:</td>
                                        <td><select name="opt_in_status"><option value="any"' . $opt_in_status_any_selected . '>Any</option><option value="opt_in"' . $opt_in_status_opt_in_selected . '>Opt-In</option><option value="opt_out"' . $opt_in_status_opt_out_selected . '>Opt-Out</option></select></td>
                                    </tr>
                                    <tr>
                                        <td>PO Number:</td>
                                        <td><input type="text" name="po_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['po_number']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Status:</td>
                                        <td><select name="tax_status"><option value="any"' . $tax_status_any_selected . '>Any</option><option value="tax_exempt"' . $tax_status_tax_exempt_selected . '>Tax-Exempt</option><option value="not_tax_exempt"' . $tax_status_not_tax_exempt_selected . '>Not Tax-Exempt</option></select></td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                        <td style="vertical-align: top">
                            <fieldset style="padding: 0px 10px 10px 10px">
                                <legend><strong>Shipping</strong></legend>
                                <table>
                                    <tr>
                                        <td>Salutation:</td>
                                        <td><input type="text" name="shipping_salutation" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_salutation']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>First Name:</td>
                                        <td><input type="text" name="shipping_first_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_first_name']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Last Name:</td>
                                        <td><input type="text" name="shipping_last_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_last_name']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Company:</td>
                                        <td><input type="text" name="shipping_company" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_company']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Address 1:</td>
                                        <td><input type="text" name="shipping_address_1" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_address_1']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Address 2:</td>
                                        <td><input type="text" name="shipping_address_2" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_address_2']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>City:</td>
                                        <td><input type="text" name="shipping_city" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_city']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>State:</td>
                                        <td><input type="text" name="shipping_state" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_state']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Zip Code:</td>
                                        <td><input type="text" name="shipping_zip_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_zip_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Country:</td>
                                        <td><input type="text" name="shipping_country" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_country']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Address Type:</td>
                                        <td><select name="address_type"><option value="any"' . $address_type_any_selected . '>Any</option><option value=""' . $address_type_none_selected . '>None</option><option value="residential"' . $address_type_residential_selected . '>Residential</option><option value="business"' . $address_type_business_selected . '>Business</option></select></td>
                                    </tr>
                                    <tr>
                                        <td>Phone:</td>
                                        <td><input type="text" name="shipping_phone_number" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_phone_number']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Ship to Name:</td>
                                        <td><input type="text" name="ship_to_name" value="' . h($_SESSION['software']['ecommerce']['view_orders']['ship_to_name']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Arrival Date Code:</td>
                                        <td><input type="text" name="arrival_date_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['arrival_date_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping Method Code:</td>
                                        <td><input type="text" name="shipping_method_code" value="' . h($_SESSION['software']['ecommerce']['view_orders']['shipping_method_code']) . '" /></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping Status:</td>
                                        <td><select name="shipping_status"><option value="any"' . $shipping_status_any_selected . '>Any</option><option value="unshipped"' . $shipping_status_unshipped_selected . '>Unshipped</option><option value="shipped"' . $shipping_status_shipped_selected . '>Shipped</option></select></td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" name="submit_data" value="Update" class="submit_small_secondary" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="margin: 0em 0em 1em 0em;">
                <fieldset style="padding: 10px 10px 18px 10px">
                    <legend><strong>Date Range</strong></legend>
                    ' . $output_date_type_field . 'From:&nbsp;<select name="start_month">' . select_month($_SESSION['software']['ecommerce']['view_orders']['start_month']) . '</select><select name="start_day">' . select_day($_SESSION['software']['ecommerce']['view_orders']['start_day']) . '</select><select name="start_year">' . select_year($years, $_SESSION['software']['ecommerce']['view_orders']['start_year']) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;<select name="stop_month">' . select_month($_SESSION['software']['ecommerce']['view_orders']['stop_month']) . '</select><select name="stop_day">' . select_day($_SESSION['software']['ecommerce']['view_orders']['stop_day']) . '</select><select name="stop_year">' . select_year($years, $_SESSION['software']['ecommerce']['view_orders']['stop_year']) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_data" value="Update" class="submit_small_secondary" />
                </fieldset>
            </div>
            </div>';
    }
	
	
	// Get Payment Gateway for button bar output
	$output_button_bar = '';
	if(ECOMMERCE_PAYMENT_GATEWAY == 'Iyzipay'){
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



    $output .=
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
	' . $output_button_bar . '
    <div id="content">
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Orders</h1>
        <div class="subheading">View and export website orders.</div>
        <form id="search" action="view_orders.php" method="get" style="margin: 0">
            <div style="margin: 1em 0em 0em 0em; padding: 0em">
                <table class="field" style="width: 100%">
                    <tr>
                        <td style="padding-left: 0;"><a href="view_orders.php?advanced_filters=' . $output_advanced_filters_value . '" class="button_small" style="white-space: nowrap;">' . $output_advanced_filters_label . ' <img style="vertical-align: top; padding-left: 3px ; margin-top: 2px" src="'. OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/advanced_filters_'. $advanced_filters_icon . '.png"></a></td>
                        <td style="text-align: right; vertical-align: middle; padding-right: 0;">Status: <select name="status" onchange="submit_form(\'search\')">' . $output_status_options . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_orders']['query']) . '" /> <input type="submit" name="submit_data" value="Search" class="submit_small_secondary" />' . $output_clear_button . '</td>
                    </tr>
                </table>
            </div>
            ' . $output_advanced_filters . '
            <table style="width: 100%; margin-bottom: .5em; padding: 0em; border-collapse: collapse">
                <tr>
                    <td style="vertical-align: bottom; padding-left: 0;">
                        <span style="font-size: 150%; font-weight: bold;">    ' . $output_date_range_time . '</span>
                        <div style="margin-top: 5px; margin-bottom: 5px"><a href="view_orders.php?start_month=' . $decrease_year['start_month'] . '&start_day=' . $decrease_year['start_day'] . '&start_year=' . $decrease_year['start_year'] . '&stop_month=' . $decrease_year['stop_month'] . '&stop_day=' . $decrease_year['stop_day'] . '&stop_year=' . $decrease_year['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_orders.php?start_month=' . $current_year['start_month'] . '&start_day=' . $current_year['start_day'] . '&start_year=' . $current_year['start_year'] . '&stop_month=' . $current_year['stop_month'] . '&stop_day=' . $current_year['stop_day'] . '&stop_year=' . $current_year['stop_year'] . '" class="button_3d_secondary">&nbsp;Year&nbsp;</a><a href="view_orders.php?start_month=' . $increase_year['start_month'] . '&start_day=' . $increase_year['start_day'] . '&start_year=' . $increase_year['start_year'] . '&stop_month=' . $increase_year['stop_month'] . '&stop_day=' . $increase_year['stop_day'] . '&stop_year=' . $increase_year['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_orders.php?start_month=' . $decrease_month['start_month'] . '&start_day=' . $decrease_month['start_day'] . '&start_year=' . $decrease_month['start_year'] . '&stop_month=' . $decrease_month['stop_month'] . '&stop_day=' . $decrease_month['stop_day'] . '&stop_year=' . $decrease_month['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_orders.php?start_month=' . $current_month['start_month'] . '&start_day=' . $current_month['start_day'] . '&start_year=' . $current_month['start_year'] . '&stop_month=' . $current_month['stop_month'] . '&stop_day=' . $current_month['stop_day'] . '&stop_year=' . $current_month['stop_year'] . '" class="button_3d_secondary">&nbsp;Month&nbsp;</a><a href="view_orders.php?start_month=' . $increase_month['start_month'] . '&start_day=' . $increase_month['start_day'] . '&start_year=' . $increase_month['start_year'] . '&stop_month=' . $increase_month['stop_month'] . '&stop_day=' . $increase_month['stop_day'] . '&stop_year=' . $increase_month['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_orders.php?start_month=' . $decrease_week['start_month'] . '&start_day=' . $decrease_week['start_day'] . '&start_year=' . $decrease_week['start_year'] . '&stop_month=' . $decrease_week['stop_month'] . '&stop_day=' . $decrease_week['stop_day'] . '&stop_year=' . $decrease_week['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_orders.php?start_month=' . $current_week['start_month'] . '&start_day=' . $current_week['start_day'] . '&start_year=' . $current_week['start_year'] . '&stop_month=' . $current_week['stop_month'] . '&stop_day=' . $current_week['stop_day'] . '&stop_year=' . $current_week['stop_year'] . '" class="button_3d_secondary">&nbsp;Week&nbsp;</a><a href="view_orders.php?start_month=' . $increase_week['start_month'] . '&start_day=' . $increase_week['start_day'] . '&start_year=' . $increase_week['start_year'] . '&stop_month=' . $increase_week['stop_month'] . '&stop_day=' . $increase_week['stop_day'] . '&stop_year=' . $increase_week['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_orders.php?start_month=' . $decrease_day['start_month'] . '&start_day=' . $decrease_day['start_day'] . '&start_year=' . $decrease_day['start_year'] . '&stop_month=' . $decrease_day['stop_month'] . '&stop_day=' . $decrease_day['stop_day'] . '&stop_year=' . $decrease_day['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_orders.php?start_month=' . $current_day['start_month'] . '&start_day=' . $current_day['start_day'] . '&start_year=' . $current_day['start_year'] . '&stop_month=' . $current_day['stop_month'] . '&stop_day=' . $current_day['stop_day'] . '&stop_year=' . $current_day['stop_year'] . '" class="button_3d_secondary">&nbsp;Day&nbsp;</a><a href="view_orders.php?start_month=' . $increase_day['start_month'] . '&start_day=' . $increase_day['start_day'] . '&start_year=' . $increase_day['start_year'] . '&stop_month=' . $increase_day['stop_month'] . '&stop_day=' . $increase_day['stop_day'] . '&stop_year=' . $increase_day['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a></div>
                    </td>
                    <td style="vertical-align: bottom; padding-right: 0;">
                        <div class="view_summary">
                            Viewing '. number_format($number_of_results) .' of ' . number_format($all_orders) . ' Total&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_data" value="Export Orders (multiple files)" class="submit_small_secondary" /> <input type="submit" name="submit_data" value="Export Orders (single file)" class="submit_small_secondary" />
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <form name="form" action="edit_orders.php" method="post" style="margin: 0em 0em 1em 0em">
            ' . get_token_field() . '
            <script type="text/javascript">
                function edit_orders(action)
                {
                    var result;

                    switch (action) {
                        case "remove_card_data":
                            document.form.action.value = "remove_card_data";
                            result = confirm("WARNING: Card data will be permanently removed from the selected order(s).")
                            break;

                        case "delete":
                            document.form.action.value = "delete";
                            result = confirm("WARNING: The selected order(s) will be permanently deleted.")
                            break;
                    }

                    // if user select ok to confirmation, submit form
                    if (result == true) {
                        document.form.submit();
                    }
                }
            </script>
            <input type="hidden" name="action" />
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th></th>
                    <th>' . get_column_heading('First Name', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('Last Name', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('Status', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('Order Number', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('User', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('Tracking Code', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading(MEMBER_ID_LABEL, $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    ' . $output_affiliate_heading . '
                    <th style="text-align: center">Card Data</th>
                    ' . $output_shipping_tracking_heading . '
                    <th style="text-align: right; padding-right: 1em;">' . get_column_heading('Total', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                    <th>' . get_column_heading('Order Date', $_SESSION['software']['ecommerce']['view_orders']['sort'], $_SESSION['software']['ecommerce']['view_orders']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <div class="buttons">
                <input type="button" value="Remove Card Data for Selected" class="submit-secondary" onclick="edit_orders(\'remove_card_data\')" />&nbsp;&nbsp;&nbsp;<input type="button" value="Delete Selected" class="delete" onclick="edit_orders(\'delete\')" />
            </div>
        </form>
    </div>' .
    output_footer();

    print $output;
    
    $liveform->remove_form('view_orders');
}

class zipfile
{

    var $datasec = array(); // array to store compressed data
    var $ctrl_dir = array(); // central directory
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
    var $old_offset = 0;

    function add_dir($name)

    // adds "directory" to archive - do this before putting any files in directory!
    // $name - name of directory... like this: "path/"
    // ...then you can add files using add_file with names like "path/file.txt"
    {
        $name = str_replace("\\", "/", $name);

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x0a\x00";    // ver needed to extract
        $fr .= "\x00\x00";    // gen purpose bit flag
        $fr .= "\x00\x00";    // compression method
        $fr .= "\x00\x00\x00\x00"; // last mod time and date

        $fr .= pack("V",0); // crc32
        $fr .= pack("V",0); //compressed filesize
        $fr .= pack("V",0); //uncompressed filesize
        $fr .= pack("v", strlen($name) ); //length of pathname
        $fr .= pack("v", 0 ); //extra field length
        $fr .= $name;
        // end of "local file header" segment

        // no "file data" segment for path

        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr .= pack("V",$crc); //crc32
        $fr .= pack("V",$c_len); //compressed filesize
        $fr .= pack("V",$unc_len); //uncompressed filesize

        // add this entry to array
        $this -> datasec[] = $fr;

        $new_offset = strlen(implode("", $this->datasec));

        // ext. file attributes mirrors MS-DOS directory attr byte, detailed
        // at http://support.microsoft.com/support/kb/articles/Q125/0/19.asp

        // now add to central record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .="\x00\x00";    // version made by
        $cdrec .="\x0a\x00";    // version needed to extract
        $cdrec .="\x00\x00";    // gen purpose bit flag
        $cdrec .="\x00\x00";    // compression method
        $cdrec .="\x00\x00\x00\x00"; // last mod time & date
        $cdrec .= pack("V",0); // crc32
        $cdrec .= pack("V",0); //compressed filesize
        $cdrec .= pack("V",0); //uncompressed filesize
        $cdrec .= pack("v", strlen($name) ); //length of filename
        $cdrec .= pack("v", 0 ); //extra field length
        $cdrec .= pack("v", 0 ); //file comment length
        $cdrec .= pack("v", 0 ); //disk number start
        $cdrec .= pack("v", 0 ); //internal file attributes
        $ext = "\x00\x00\x10\x00";
        $ext = "\xff\xff\xff\xff";
        $cdrec .= pack("V", 16 ); //external file attributes  - 'directory' bit set

        $cdrec .= pack("V", $this -> old_offset ); //relative offset of local header
        $this -> old_offset = $new_offset;

        $cdrec .= $name;
        // optional extra field, file comment goes here
        // save to array
        $this -> ctrl_dir[] = $cdrec;


    }


    function add_file($data, $name)

    // adds "file" to archive
    // $data - file contents
    // $name - name of file in archive. Add path if your want

    {
        $name = str_replace("\\", "/", $name);
        //$name = str_replace("\\", "\\\\", $name);

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";    // ver needed to extract
        $fr .= "\x00\x00";    // gen purpose bit flag
        $fr .= "\x08\x00";    // compression method
        $fr .= "\x00\x00\x00\x00"; // last mod time and date

        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr( substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len = strlen($zdata);
        $fr .= pack("V",$crc); // crc32
        $fr .= pack("V",$c_len); //compressed filesize
        $fr .= pack("V",$unc_len); //uncompressed filesize
        $fr .= pack("v", strlen($name) ); //length of filename
        $fr .= pack("v", 0 ); //extra field length
        $fr .= $name;
        // end of "local file header" segment

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr .= pack("V",$crc); //crc32
        $fr .= pack("V",$c_len); //compressed filesize
        $fr .= pack("V",$unc_len); //uncompressed filesize

        // add this entry to array
        $this -> datasec[] = $fr;

        $new_offset = strlen(implode("", $this->datasec));

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .="\x00\x00";    // version made by
        $cdrec .="\x14\x00";    // version needed to extract
        $cdrec .="\x00\x00";    // gen purpose bit flag
        $cdrec .="\x08\x00";    // compression method
        $cdrec .="\x00\x00\x00\x00"; // last mod time & date
        $cdrec .= pack("V",$crc); // crc32
        $cdrec .= pack("V",$c_len); //compressed filesize
        $cdrec .= pack("V",$unc_len); //uncompressed filesize
        $cdrec .= pack("v", strlen($name) ); //length of filename
        $cdrec .= pack("v", 0 ); //extra field length
        $cdrec .= pack("v", 0 ); //file comment length
        $cdrec .= pack("v", 0 ); //disk number start
        $cdrec .= pack("v", 0 ); //internal file attributes
        $cdrec .= pack("V", 32 ); //external file attributes - 'archive' bit set

        $cdrec .= pack("V", $this -> old_offset ); //relative offset of local header
//        echo "old offset is ".$this->old_offset.", new offset is $new_offset<br>";
        $this -> old_offset = $new_offset;

        $cdrec .= $name;
        // optional extra field, file comment goes here
        // save to central directory
        $this -> ctrl_dir[] = $cdrec;
    }

    function file() { // dump out file
        $data = implode("", $this -> datasec);
        $ctrldir = implode("", $this -> ctrl_dir);

        return
            $data.
            $ctrldir.
            $this -> eof_ctrl_dir.
            pack("v", sizeof($this -> ctrl_dir)).     // total # of entries "on this disk"
            pack("v", sizeof($this -> ctrl_dir)).     // total # of entries overall
            pack("V", strlen($ctrldir)).             // size of central dir
            pack("V", strlen($data)).                 // offset to start of central dir
            "\x00\x00";                             // .zip file comment length
    }
}
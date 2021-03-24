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
validate_visitors_access($user);

// If the base currency symbol is not defined, then that is because commerce
// is disabled, so we need to set a base currency.  This is the only area
// that shows currency when commerce is disabled.  In the future,
// we should probably consider not showing commerce data on this screen
// if commerce is disabled, however we are not going to spend time on that for now.
if (defined('BASE_CURRENCY_SYMBOL') == false) {
    define('BASE_CURRENCY_SYMBOL', '$');
}

// if the user was creating a visitor report on the previous screen, then prepare previous screen link
if (isset($_GET['visitor_report_id']) == false) {
    $output_previous_screen_link = '<a href="view_visitor_report.php">Create Visitor Report</a>';
    
// else the user was editing a visitor report on the previous screen, so prepare previous screen link
} else {
    $output_previous_screen_link = '<a href="view_visitor_report.php?id=' . h($_GET['visitor_report_id']) . '">View Visitor Report</a>';
}

// get visitor information
$query =
    "SELECT
        id,
        INET_NTOA(ip_address) as ip_address,
        http_referer,
        referring_host_name,
        referring_search_engine,
        referring_search_terms,
        first_visit,
        landing_page_name,
        tracking_code,
        affiliate_code,
        currency_code,
        utm_source,
        utm_medium,
        utm_campaign,
        utm_term,
        utm_content,
        page_views,
        custom_form_submitted,
        custom_form_name,
        order_created,
        order_retrieved,
        order_checked_out,
        order_completed,
        order_total,
        city,
        state,
        zip_code,
        country,
        start_timestamp,
        stop_timestamp,
        site_search_terms
    FROM visitors
    WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$id = $row['id'];
$ip_address = $row['ip_address'];
$http_referer = $row['http_referer'];
$referring_host_name = $row['referring_host_name'];
$referring_search_engine = $row['referring_search_engine'];
$referring_search_terms = $row['referring_search_terms'];
$first_visit = $row['first_visit'];
$landing_page_name = $row['landing_page_name'];
$tracking_code = $row['tracking_code'];
$affiliate_code = $row['affiliate_code'];
$currency_code = $row['currency_code'];
$utm_source = $row['utm_source'];
$utm_medium = $row['utm_medium'];
$utm_campaign = $row['utm_campaign'];
$utm_term = $row['utm_term'];
$utm_content = $row['utm_content'];
$page_views = $row['page_views'];
$custom_form_submitted = $row['custom_form_submitted'];
$custom_form_name = $row['custom_form_name'];
$order_created = $row['order_created'];
$order_retrieved = $row['order_retrieved'];
$order_checked_out = $row['order_checked_out'];
$order_completed = $row['order_completed'];
$order_total = $row['order_total'];
$city = $row['city'];
$state = $row['state'];
$zip_code = $row['zip_code'];
$country = $row['country'];
$start_timestamp = $row['start_timestamp'];
$stop_timestamp = $row['stop_timestamp'];
$site_search_terms = $row['site_search_terms'];

$output_currency_row = '';

// if multi-currency is enabled, then output currency row
if ((ECOMMERCE == true) && (ECOMMERCE_MULTICURRENCY == true)) {
    $currency_name_and_code = '';
    
    // if there is a currency code, then output it
    if ($currency_code != '') {
        $currency_name_and_code = get_currency_name_from_code($currency_code) . ' (' . $currency_code . ')';
    }
    
    $output_currency_row =
        '<tr>
            <td>Currency:</td>
            <td>' . h($currency_name_and_code) . '</td>
        </tr>';
}

// if http referer is too long, then shorten
if (mb_strlen($http_referer) > 100) {
    $output_http_referer = mb_substr($http_referer, 0, 100) . '...';
} else {
    $output_http_referer = $http_referer;
}

// if referring host name is too long, then shorten
if (mb_strlen($referring_host_name) > 100) {
    $output_referring_host_name = mb_substr($referring_host_name, 0, 100) . '...';
} else {
    $output_referring_host_name = $referring_host_name;
}

// if pay per click flag is in tracking code, this visitor is pay per click
if ((defined('PAY_PER_CLICK_FLAG') == true) && (PAY_PER_CLICK_FLAG != '') && (mb_strpos($tracking_code, PAY_PER_CLICK_FLAG) !== false)) {
    $output_pay_per_click_organic = 'Pay Per Click';
    
// else if referring seraching engine is not blank, this visitor is organic
} elseif ($referring_search_engine != '') {
    $output_pay_per_click_organic = 'Organic';
    
// else this visitor is neither
} else {
    $output_pay_per_click_organic = '';
}

$output_first_visit = ($first_visit) ? 'Yes' : 'No';

if (AFFILIATE_PROGRAM == true) {
    $output_affiliate_code =
        '<tr>
            <td>Affiliate Code:</td>
            <td>' . h($affiliate_code) . '</td>
        </tr>';
}

$utm = '';

if ($utm_source) {

    $utm .=
        '<tr>
            <th colspan="2"><h2>UTM</h2></th>
        </tr>
        <tr>
            <td>Source:</td>
            <td>' . h($utm_source) . '</td>
        </tr>';

    if ($utm_medium) {
        $utm .=
            '<tr>
                <td>Medium:</td>
                <td>' . h($utm_medium) . '</td>
            </tr>';
    }

    if ($utm_campaign) {
        $utm .=
            '<tr>
                <td>Campaign:</td>
                <td>' . h($utm_campaign) . '</td>
            </tr>';
    }

    if ($utm_term) {
        $utm .=
            '<tr>
                <td>Term:</td>
                <td>' . h($utm_term) . '</td>
            </tr>';
    }

    if ($utm_content) {
        $utm .=
            '<tr>
                <td>Content:</td>
                <td>' . h($utm_content) . '</td>
            </tr>';
    }
}

$output_custom_form_submitted = ($custom_form_submitted) ? 'Yes' : 'No';
$output_order_created = ($order_created) ? 'Yes' : 'No';
$output_order_retrieved = ($order_retrieved) ? 'Yes' : 'No';
$output_order_checked_out = ($order_checked_out) ? 'Yes' : 'No';
$output_order_completed = ($order_completed) ? 'Yes' : 'No';

echo
    output_header() . '
    <div id="subnav">
        <h1>Visitor # ' . $id . '</h1>
        <div class="subheading">Visitor\'s IP Address: ' . $ip_address . '</div>
    </div>
    <div id="content">
        
        <a href="#" id="help_link">Help</a>
        <h1>Visit Details</h1>
        <div class="subheading">View this computer\'s visit to the website.</div>
        <table class="field">
            <tr>
                <th colspan="2"><h2>General Visit Information</h2></th>
            </tr>
            <tr>
                <td>Visitor Number:</td>
                <td>' . $id . '</td>
            </tr>
            <tr>
                <td>Start Time:</td>
                <td>' . get_absolute_time(array('timestamp' => $start_timestamp)) . '</td>
            </tr>
            <tr>
                <td>End Time:</td>
                <td>' . get_absolute_time(array('timestamp' => $stop_timestamp)) . '</td>
            </tr>
            <tr>
                <td>Site Search Terms:</td>
                <td>' . str_replace('|', ',<br />', h($site_search_terms)) . '</td>
            </tr>
            ' . $output_currency_row . '
            <tr>
                <th colspan="2"><h2>Referral Information</h2></th>
            </tr>
            <tr>
                <td>URL:</td>
                <td><a href="' . h(escape_url($http_referer)) . '" target="_blank">' . h($output_http_referer) . '</a></td>
            </tr>
            <tr>
                <td>Host Name:</td>
                <td><a href="http://' . h($referring_host_name) . '" target="_blank">' . h($output_referring_host_name) . '</a></td>
            </tr>
            <tr>
                <td>Search Engine:</td>
                <td>' . h($referring_search_engine) . '</td>
            </tr>
            <tr>
                <td>Search Terms:</td>
                <td>' . h($referring_search_terms) . '</td>
            </tr>
            <tr>
                <td>Pay Per Click / Organic:</td>
                <td>' . $output_pay_per_click_organic . '</td>
            </tr>
            <tr>
                <td>First Visit:</td>
                <td>' . $output_first_visit . '</td>
            </tr>
            <tr>
                <td>Landing Page:</td>
                <td><a href="' . OUTPUT_PATH . h($landing_page_name) . '" target="_blank">' . h($landing_page_name) . '</a></td>
            </tr>
            <tr>
                <td>Tracking Code:</td>
                <td>' . h($tracking_code) . '</td>
            </tr>
            ' . $output_affiliate_code . '
            ' . $utm . '
            <tr>
                <th colspan="2"><h2>Actions performed during Visit</h2></th>
            </tr>
            <tr>
                <td>Page Views:</td>
                <td>' . number_format($page_views) . '</td>
            </tr>
            <tr>
                <td>Custom Form Submitted:</td>
                <td>' . $output_custom_form_submitted . '</td>
            </tr>
            <tr>
                <td>Custom Form:</td>
                <td>' . h($custom_form_name) . '</td>
            </tr>
            <tr>
                <td>Order Created:</td>
                <td>' . $output_order_created . '</td>
            </tr>
            <tr>
                <td>Order Retrieved:</td>
                <td>' . $output_order_retrieved . '</td>
            </tr>
            <tr>
                <td>Order Checked Out:</td>
                <td>' . $output_order_checked_out . '</td>
            </tr>
            <tr>
                <td>Order Completed:</td>
                <td>' . $output_order_completed . '</td>
            </tr>
            <tr>
                <td>Order Total:</td>
                <td>' . prepare_amount($order_total / 100) . '</td>
            </tr>
            <tr>
                <th colspan="2"><h2>Visitor\'s Location Information</h2></th>
            </tr>
            <tr>
                <td>City:</td>
                <td>' . h($city) . '</td>
            </tr>
            <tr>
                <td>State:</td>
                <td>' . h($state) . '</td>
            </tr>
            <tr>
                <td>Zip Code:</td>
                <td>' . h($zip_code) . '</td>
            </tr>
            <tr>
                <td>Country:</td>
                <td>' . h($country) . '</td>
            </tr>
            <tr>
                <th colspan="2"><h2>Miscellaneous</h2></th>
            </tr>
            <tr>
                <td>IP Address:</td>
                <td>' . $ip_address . '</td>
            </tr>
        </table>
    </div>
    ' . output_footer();
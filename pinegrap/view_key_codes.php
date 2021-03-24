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

$liveform = new liveform('view_key_codes');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_key_codes'][$key] = trim($value);
    }
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_key_codes']['query'] = '';
}

switch ($_SESSION['software']['ecommerce']['view_key_codes']['sort']) {
    case 'Key Code':
        $sort_column = 'key_codes.code';
        break;

    case 'Offer Code':
        $sort_column = 'key_codes.offer_code';
        break;

    case 'Offer Message':
        $sort_column = 'offers.description';
        break;

    case 'Enabled':
        $sort_column = 'key_codes.enabled';
        break;

    case 'Expiration Date':
        $sort_column = 'key_codes.expiration_date';
        break;

    case 'Notes':
        $sort_column = 'key_codes.notes';
        break;

    case 'Single-Use':
        $sort_column = 'key_codes.single_use';
        break;

    case 'Report':
        $sort_column = 'key_codes.report';
        break;

    case 'Last Modified':
        $sort_column = 'key_codes.timestamp';
        break;

    default:
        $sort_column = 'key_codes.timestamp';
        $_SESSION['software']['ecommerce']['view_key_codes']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_key_codes']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_key_codes']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_key_codes']['order'] = 'asc';
}

$all_key_codes = db_value("SELECT COUNT(*) FROM key_codes");

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_key_codes']['query']) == true) && ($_SESSION['software']['ecommerce']['view_key_codes']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', key_codes.code, key_codes.offer_code, CAST(key_codes.expiration_date AS CHAR), key_codes.notes, last_modified_user.user_username)) LIKE '%" . e(mb_strtolower($_SESSION['software']['ecommerce']['view_key_codes']['query'])) . "%')";
}

// If user requested to export, then export them.
if ($_GET['submit_data'] == 'Export Key Codes') {

    header("Content-type: text/csv");
    header("Content-disposition: attachment; filename=key_codes.csv");

    // Output column headings for CSV data.
    echo
        '"key_code",' .
        '"offer_code",' .
        '"enabled",' .
        '"expiration_date",' .
        '"notes",' .
        '"single_use",' .
        '"report",' .
        '"last_modified",' .
        '"last_modified_username"' . "\n";

    // Get all key codes.
    $key_codes = db_items(
        "SELECT
            key_codes.code,
            key_codes.offer_code,
            key_codes.enabled,
            key_codes.expiration_date,
            key_codes.notes,
            key_codes.single_use,
            key_codes.report,
            key_codes.timestamp AS last_modified_timestamp,
            last_modified_user.user_username AS last_modified_username
        FROM key_codes
        LEFT JOIN user AS last_modified_user ON key_codes.user = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . e($_SESSION['software']['ecommerce']['view_key_codes']['order']));

    // If the date format is month and then day, then use that format.
    if (DATE_FORMAT == 'month_day') {
        $month_and_day_format = 'n/j';

    // Otherwise the date format is day and then month, so use that format.
    } else {
        $month_and_day_format = 'j/n';
    }

    // Loop through the key codes in order to output CSV data.
    foreach ($key_codes as $key_code) {

        $expiration_date = '';

        if ($key_code['expiration_date'] != '0000-00-00') {
            $expiration_date = $key_code['expiration_date'];
        }

        echo
            '"' . escape_csv($key_code['code']) . '",' .
            '"' . escape_csv($key_code['offer_code']) . '",' .
            '"' . $key_code['enabled'] . '",' .
            '"' . $expiration_date . '",' .
            '"' . escape_csv($key_code['notes']) . '",' .
            '"' . $key_code['single_use'] . '",' .
            '"' . $key_code['report'] . '",' .
            '"' . date($month_and_day_format . '/Y g:i:s A T', $key_code['last_modified_timestamp']) . '",' .
            '"' . escape_csv($key_code['last_modified_username']) . '"' . "\n";
    }

    exit;

// Otherwise the user did not select to export gift cards, so just list gift cards.
} else {

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

    // Define the maximum number of results per screen.
    $max = 100;

    // Prepare limit clause so we only get necessary results that appear on this screen.
    $start = $screen * $max - $max;
    $limit = "LIMIT $start, $max";

    // Get all key codes.
    $key_codes = db_items(
        "SELECT
            key_codes.id,
            key_codes.code,
            key_codes.offer_code,
            key_codes.enabled,
            key_codes.expiration_date,
            key_codes.notes,
            key_codes.single_use,
            key_codes.report,
            last_modified_user.user_username AS last_modified_username,
            key_codes.timestamp AS last_modified_timestamp,
            offers.id AS offer_id,
            offers.description AS offer_description,
            offers.status AS offer_status,
            offers.start_date AS offer_start_date,
            offers.end_date AS offer_end_date
        FROM key_codes
        LEFT JOIN offers ON key_codes.offer_code = offers.code
        LEFT JOIN user AS last_modified_user ON key_codes.user = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . e($_SESSION['software']['ecommerce']['view_key_codes']['order']) . "
        $limit");

    // Get the current date so that later we can figure out if key codes have expired.
    $current_date = date('Y-m-d');

    // Loop through the key codes in order to set the status for each one,
    // based on enabled and expiration date fields.
    foreach ($key_codes as $key => $key_code) {
        // If this key code is active, then store that, so a color can be used to indicate that.
        if (
            $key_code['enabled']
            &&
            (
                ($key_code['expiration_date'] == '0000-00-00')
                || ($key_code['expiration_date'] >= $current_date)
            )
            && ($key_code['offer_status'] == 'enabled')
            && ($key_code['offer_start_date'] <= $current_date)
            && ($key_code['offer_end_date'] >= $current_date)
        ) {
            $key_codes[$key]['status_enabled'] = true;
        }
    }

    $previous = $screen - 1;
    $next = $screen + 1;

    $number_of_results = db(
        "SELECT COUNT(*) FROM key_codes
        LEFT JOIN user AS last_modified_user ON key_codes.user = last_modified_user.user_id
        $where");

    $number_of_screens = ceil($number_of_results / $max);

    echo output_header();

    require('templates/view_key_codes.php');

    echo output_footer();

    $liveform->remove();
}
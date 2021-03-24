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

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_offers'][$key] = trim($value);
    }
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_offers']['query'] = '';
}

switch ($_SESSION['software']['ecommerce']['view_offers']['sort']) {
    case 'Offer Code':
        $sort_column = 'offers.code';
        break;

    case 'Message':
        $sort_column = 'offers.description';
        break;

    case 'Rule':
        $sort_column = 'offer_rules.name';
        break;

    case 'Status':
        $sort_column = 'offers.status';
        break;

    case 'Start Date':
        $sort_column = 'offers.start_date';
        break;

    case 'End Date':
        $sort_column = 'offers.end_date';
        break;

    case 'Require Code':
        $sort_column = 'offers.require_code';
        break;
        
    case 'Best':
        $sort_column = 'offers.only_apply_best_offer';
        break;

    case 'Last Modified':
        $sort_column = 'offers.timestamp';
        break;

    default:
        $sort_column = 'offers.timestamp';
        $_SESSION['software']['ecommerce']['view_offers']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_offers']['order'] = 'desc';
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_offers']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_offers']['order'] = 'asc';
}

$all_offers = db_value("SELECT COUNT(*) FROM offers");

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_offers']['query']) == true) && ($_SESSION['software']['ecommerce']['view_offers']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', offers.code, offers.description, offer_rules.name, offers.status, last_modified_user.user_username)) LIKE '%" . e(mb_strtolower($_SESSION['software']['ecommerce']['view_offers']['query'])) . "%')";
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

// Define the maximum number of results per screen.
$max = 100;

// Prepare limit clause so we only get necessary results that appear on this screen.
$start = $screen * $max - $max;
$limit = "LIMIT $start, $max";

$offers = db_items(
    "SELECT
        offers.id,
        offers.code,
        offers.description,
        offer_rules.id as offer_rule_id,
        offer_rules.name as offer_rule_name,
        offers.status,
        offers.start_date,
        offers.end_date,
        offers.require_code,
        offers.only_apply_best_offer,
        last_modified_user.user_username AS last_modified_username,
        offers.timestamp AS last_modified_timestamp
    FROM offers
    LEFT JOIN offer_rules ON offers.offer_rule_id = offer_rules.id
    LEFT JOIN user AS last_modified_user ON offers.user = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . e($_SESSION['software']['ecommerce']['view_offers']['order']) . "
    $limit");

// Get the current date so that later we can figure out if offers are active.
$current_date = date('Y-m-d');

// Loop through the offers in order to prepare them.
foreach ($offers as $key => $offer) {

    // If this offer is active, then store that, so a color can be used to indicate that.
    if (
        $offer['status'] == 'enabled'
        and $offer['start_date'] <= $current_date
        and $offer['end_date'] >= $current_date
    ) {
        $offer['status_enabled'] = true;
    }

    $offer['actions'] = db_items(
        "SELECT
            offer_actions.id,
            offer_actions.name
        FROM offers_offer_actions_xref
        LEFT JOIN offer_actions ON offers_offer_actions_xref.offer_action_id = offer_actions.id
        WHERE offers_offer_actions_xref.offer_id = '" . e($offer['id']) . "'
        ORDER BY offer_actions.name");

    $offers[$key] = $offer;
}

$previous = $screen - 1;
$next = $screen + 1;

$number_of_results = db(
    "SELECT COUNT(*) FROM offers
    LEFT JOIN offer_rules ON offers.offer_rule_id = offer_rules.id
    LEFT JOIN user AS last_modified_user ON offers.user = last_modified_user.user_id
    $where");

$number_of_screens = ceil($number_of_results / $max);

echo output_header();

require('templates/view_offers.php');

echo output_footer();
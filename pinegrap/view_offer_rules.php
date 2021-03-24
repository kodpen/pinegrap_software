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

$liveform = new liveform('view_offer_rules');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_offer_rules'][$key] = trim($value);
    }
}

switch ($_SESSION['software']['ecommerce']['view_offer_rules']['sort']) {

    case 'Name':
        $sort_column = 'offer_rules.name';
        break;

    case 'Required Subtotal':
        $sort_column = 'offer_rules.required_subtotal';
        break;

    case 'Required Quantity':
        $sort_column = 'offer_rules.required_quantity';
        break;

    case 'Last Modified':
        $sort_column = 'offer_rules.timestamp';
        break;

    default:
        $sort_column = 'offer_rules.timestamp';
        $_SESSION['software']['ecommerce']['view_offer_rules']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_offer_rules']['order'] = 'desc';
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_offer_rules']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_offer_rules']['order'] = 'asc';
}

$offer_rules = db_items(
    "SELECT
        offer_rules.id,
        offer_rules.name,
        offer_rules.required_subtotal / 100 AS required_subtotal,
        offer_rules.required_quantity,
        last_modified_user.user_username AS last_modified_username,
        offer_rules.timestamp as last_modified_timestamp
    FROM offer_rules
    LEFT JOIN user AS last_modified_user ON offer_rules.user = last_modified_user.user_id
    ORDER BY $sort_column " . e($_SESSION['software']['ecommerce']['view_offer_rules']['order']));

foreach ($offer_rules as $key => $offer_rule) {

    $offer_rule['products'] = db_items("
        SELECT
            products.id,
            products.name,
            products.short_description
        FROM offer_rules_products_xref
        LEFT JOIN products ON offer_rules_products_xref.product_id = products.id
        WHERE offer_rules_products_xref.offer_rule_id = '" . e($offer_rule['id']) . "'
        ORDER BY name, short_description");

    $offer_rules[$key] = $offer_rule;
}

$number_of_results = count($offer_rules);

echo output_header();

require('templates/view_offer_rules.php');

echo output_footer();

$liveform->remove();
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

$form = new liveform('view_containers');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_containers'][$key] = trim($value);
    }
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_containers']['query'] = '';
}

switch ($_SESSION['software']['ecommerce']['view_containers']['sort']) {
    case 'Name':
        $sort_column = 'containers.name';
        break;

    case 'Enabled':
        $sort_column = 'containers.enabled';
        break;

    case 'Length':
        $sort_column = 'containers.length';
        break;

    case 'Width':
        $sort_column = 'containers.width';
        break;

    case 'Height':
        $sort_column = 'containers.height';
        break;

    case 'Weight':
        $sort_column = 'containers.weight';
        break;

    case 'Cost':
        $sort_column = 'containers.cost';
        break;

    case 'Created':
        $sort_column = 'containers.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'containers.last_modified_timestamp';
        break;

    default:
        $sort_column = 'containers.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_containers']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_containers']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_containers']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_containers']['order'] = 'asc';
}

$all_containers = db_value("SELECT COUNT(*) FROM containers");

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_containers']['query']) == true) && ($_SESSION['software']['ecommerce']['view_containers']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', containers.name, containers.length, containers.width, containers.height, containers.weight, containers.cost/100, created_user.user_username, last_modified_user.user_username)) LIKE '%" . e(mb_strtolower($_SESSION['software']['ecommerce']['view_containers']['query'])) . "%')";
}

$containers = db_items(
    "SELECT
        containers.id,
        containers.name,
        containers.enabled,
        containers.length,
        containers.width,
        containers.height,
        containers.weight,
        containers.cost/100 AS cost,
        created_user.user_username AS created_username,
        containers.created_timestamp,
        last_modified_user.user_username AS last_modified_username,
        containers.last_modified_timestamp
    FROM containers
    LEFT JOIN user AS created_user ON containers.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON containers.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . e($_SESSION['software']['ecommerce']['view_containers']['order']));

echo output_header();

require('templates/view_containers.php');

echo output_footer();

$form->remove();
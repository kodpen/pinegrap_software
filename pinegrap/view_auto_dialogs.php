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
validate_area_access($user, 'manager');

$liveform = new liveform('view_auto_dialogs');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['view_auto_dialogs'][$key] = trim($value);
    }
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_auto_dialogs']['query'] = '';
}

switch ($_SESSION['software']['view_auto_dialogs']['sort']) {
    case 'Name':
        $sort_column = 'auto_dialogs.name';
        break;

    case 'Enabled':
        $sort_column = 'auto_dialogs.enabled';
        break;

    case 'URL':
        $sort_column = 'auto_dialogs.url';
        break;

    case 'Width':
        $sort_column = 'auto_dialogs.width';
        break;

    case 'Height':
        $sort_column = 'auto_dialogs.height';
        break;

    case 'Delay':
        $sort_column = 'auto_dialogs.delay';
        break;

    case 'Frequency':
        $sort_column = 'auto_dialogs.frequency';
        break;

    case 'Only on Page':
        $sort_column = 'auto_dialogs.page';
        break;

    case 'Created':
        $sort_column = 'auto_dialogs.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'auto_dialogs.last_modified_timestamp';
        break;

    default:
        $sort_column = 'auto_dialogs.last_modified_timestamp';
        $_SESSION['software']['view_auto_dialogs']['sort'] = 'Last Modified';
        $_SESSION['software']['view_auto_dialogs']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['view_auto_dialogs']['order']) == false) {
    $_SESSION['software']['view_auto_dialogs']['order'] = 'asc';
}

$all_auto_dialogs = db_value("SELECT COUNT(*) FROM auto_dialogs");

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['view_auto_dialogs']['query']) == true) && ($_SESSION['software']['view_auto_dialogs']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', auto_dialogs.name, auto_dialogs.url, auto_dialogs.width, auto_dialogs.height, auto_dialogs.delay, auto_dialogs.frequency, auto_dialogs.page, created_user.user_username, last_modified_user.user_username)) LIKE '%" . e(mb_strtolower($_SESSION['software']['view_auto_dialogs']['query'])) . "%')";
}

// Get all auto dialogs.
$auto_dialogs = db_items(
    "SELECT
        auto_dialogs.id,
        auto_dialogs.name,
        auto_dialogs.enabled,
        auto_dialogs.url,
        auto_dialogs.width,
        auto_dialogs.height,
        auto_dialogs.delay,
        auto_dialogs.frequency,
        auto_dialogs.page,
        created_user.user_username AS created_username,
        auto_dialogs.created_timestamp,
        last_modified_user.user_username AS last_modified_username,
        auto_dialogs.last_modified_timestamp
    FROM auto_dialogs
    LEFT JOIN user AS created_user ON auto_dialogs.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON auto_dialogs.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . e($_SESSION['software']['view_auto_dialogs']['order']));

echo output_header();

require('templates/view_auto_dialogs.php');

echo output_footer();

$liveform->remove_form();

?>
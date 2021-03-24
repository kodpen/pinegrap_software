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
validate_area_access($user, 'user');

// get menu name
$query = "SELECT name FROM menus WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

// Set Variables
$menu_name = $row['name'];
$output_rows = '';

// if user has a user role and if they do not have access to this menu, output error
if (($user['role'] == 3) && (in_array($_GET['id'], get_items_user_can_edit('menus', $user['id'])) == FALSE)) {
    log_activity("access denied because user does not have access to edit menu (" . $menu_name . ")", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

$number_of_results = 0;

if ($user['role'] < 2) {
    $output_subheading = '<div class="subheading">Page Style Body Tag: ' . h('<menu>' . $menu_name . '</menu>') . '</div>';
}

// if the user came from the pages tab, then prepare back button label
if ($_GET['from'] == 'pages') {
    $output_back_button_label = 'Back to Page';
    
// else if the user came from the welcome screen, then prepare different back button label
} else if ($_GET['from'] == 'welcome') {
    $output_back_button_label = 'Back to Welcome';
    
// else the user came from the design tab, so prepare different back button label
} else {
    $output_back_button_label = 'Back to Menus';
}

// function for getting all menu items
function get_menu_items($menu_id, $parent_id = 0, $level = 1)
{
    $menu_items = array();
    
    // get menu items
    $query = 
        "SELECT
            menu_items.id,
            menu_items.name,
            menu_items.link_page_id,
            menu_items.link_url,
            menu_items.security,
            menu_items.created_user_id,
            user.user_username as created_username,
            menu_items.created_timestamp,
            user2.user_username as last_modified_username,
            menu_items.last_modified_timestamp
         FROM menu_items
         LEFT JOIN user ON menu_items.created_user_id = user.user_id
         LEFT JOIN user as user2 ON menu_items.last_modified_user_id = user2.user_id
         WHERE
            (menu_id = '" . escape($menu_id) . "')
            AND (parent_id = '" . escape($parent_id) . "')
         ORDER BY menu_items.sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $current_menu_items = array();
    
    // loop through menu items in order to build array
    while ($row = mysqli_fetch_assoc($result)) {
        $current_menu_items[] = $row;
    }
    
    // loop through menu items in order to build array of menu items
    foreach ($current_menu_items as $menu_item) {
        $menu_item['level'] = $level;
        
        $menu_items[] = $menu_item;
        
        // determine if there is a sub menu
        $query = "SELECT COUNT(*) FROM menu_items WHERE parent_id = '" . $menu_item['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // if there is a sub menu, then get menu items for sub menu
        if ($row[0] > 0) {
            // get menu items for sub menu
            $sub_menu_items = get_menu_items($menu_id, $menu_item['id'], $level + 1);
            
            // add sub menu items to menu items array
            $menu_items = array_merge($menu_items, $sub_menu_items);
        }
    }
    
    return $menu_items;
}

// Get menu items
$menu_items = get_menu_items($_GET['id']);

// Loop through each menu item
foreach ($menu_items as $menu_item) {
    $indentation_padding = 20 * ($menu_item['level'] - 1);
    
    // Add link url for onclick event.
    $output_link_url = 'edit_menu_item.php?id=' . $menu_item['id'] . '&from=' . h(escape_javascript(urlencode($_GET['from']))) . '&send_to=' . h(escape_javascript(urlencode($_GET['send_to'])));
    
    $number_of_results++;
    
    $link_to_value = '';
    
    // if there is a link to page id, then get the page name and set it as the link to value
    if ($menu_item['link_page_id'] != 0) {
        $link_to_value = get_page_name($menu_item['link_page_id']);
        
    // else if there is a link URL, then set that to the link to value
    } elseif ($menu_item['link_url'] != '') {
        $link_to_value = $menu_item['link_url'];
    }
    
    // if the link to value is over 50 characters, then truncate the string
    if (mb_strlen($link_to_value) > 50) {
        $link_to_value = mb_substr($link_to_value, 0, 50) . '...';
    }

    $output_security_check_mark = '';

    // If security is enabled, then output check mark.
    if (
        ($menu_item['security'] == 1)
        && ($menu_item['link_page_id'] != 0)
    ) {
        $output_security_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
    }
    
    $output_rows .= 
        '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
            <td>&nbsp;</td>
            <td class="chart_label" style="padding-left: ' . $indentation_padding . 'px">' . h($menu_item['name']) . '</td>
            <td>' . h($link_to_value) . '</td>
            <td style="text-align: center">' . $output_security_check_mark . '</td>
            <td>' . get_relative_time(array('timestamp' => $menu_item['created_timestamp'])) . ' by ' . h($menu_item['created_username']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $menu_item['last_modified_timestamp'])) . ' by ' . h($menu_item['last_modified_username']) . '</td>
        </tr>';
}

include_once('liveform.class.php');
$liveform = new liveform('view_menu_items');

$edit_and_duplicate = '';

// If this user is an admin or designer, then show edit and duplicate buttons
if ($user['role'] < 2) {
    $edit_and_duplicate =
        ' <a href="edit_menu.php?id=' . h(urlencode($_GET['id'])) . '&from=' . h(urlencode($_GET['from'])) . '&send_to=' . h(urlencode($_GET['send_to'])) . '">Edit Menu Properties</a>
        <a href="duplicate_menu.php?id=' . h(urlencode($_GET['id'])) . '&from=' . h(urlencode($_GET['from'])) . '&send_to=' . h(urlencode($_GET['send_to'])) . get_token_query_string_field() . '">Duplicate</a>';
}

echo
    output_header() . '
    <div id="subnav">
        <h1>' . h($menu_name) . '</h1>
        ' . $output_subheading . '
    </div>
    <div id="button_bar">
        <a href="add_menu_item.php?menu_id=' . h(urlencode($_GET['id'])) . '&from=' . h(urlencode($_GET['from'])) . '&send_to=' . h(urlencode($_GET['send_to'])) . '">Create Menu Item</a>' . 
        $edit_and_duplicate . '
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>Edit Menu</h1>
        <div class="subheading">Update, add, or remove menu items.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
        </div>
        <input type="hidden" name="menu_id" value="' . h($_GET['id']) . '" />
        <table class="chart" style="margin-bottom: 1.5em">
            <tr>
                <th style="width: 35px;"></th>
                <th>Name</th>
                <th>Link</th>
                <th style="text-align: center">Security</th>
                <th style="width: 150px;">Created</th>
                <th style="width: 150px;">Last Modified</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="buttons">
            <a href="' . h(escape_url($_GET['send_to'])) . '" class="submit-secondary">' . $output_back_button_label . '</a>
        </div>
    </div>' .
    output_footer();

$liveform->unmark_errors();
$liveform->clear_notices();
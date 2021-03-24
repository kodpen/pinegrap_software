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
validate_area_access($user, 'designer');

validate_token_field();

$menu = db_item(
    "SELECT
        name,
        effect,
        first_level_popup_position,
        second_level_popup_position,
        class
    FROM menus
    WHERE id = '" . e($_GET['id']) . "'");

if (!$menu) {
    output_error('Sorry, that menu could not be found.');
}

$old_name = $menu['name'];

$menu['name'] = get_unique_name(array(
    'name' => $menu['name'],
    'type' => 'menu'));

db(
    "INSERT INTO menus (
        name,
        effect,
        first_level_popup_position,
        second_level_popup_position,
        class,
        created_user_id,
        created_timestamp,
        last_modified_user_id,
        last_modified_timestamp)
    VALUES (
        '" . e($menu['name']) . "',
        '" . e($menu['effect']) . "',
        '" . e($menu['first_level_popup_position']) . "',
        '" . e($menu['second_level_popup_position']) . "',
        '" . e($menu['class']) . "',
        '" . USER_ID . "',
        UNIX_TIMESTAMP(),
        '" . USER_ID . "',
        UNIX_TIMESTAMP())");

$menu['id'] = mysqli_insert_id(db::$con);

duplicate_menu_items(array(
    'new_menu_id' => $menu['id'],
    'old_menu_id' => $_GET['id']));

log_activity('menu was duplicated (' . $old_name . ' -> ' . $menu['name'] . ')');

$form = new liveform('edit_menu', $menu['id']);
$form->add_notice('The menu and all of its items have been duplicated. You are now editing the duplicate.');

go(PATH . SOFTWARE_DIRECTORY . '/edit_menu.php?id=' . $menu['id'] . '&from=' . urlencode($_GET['from']) . '&send_to=' . urlencode($_GET['send_to']));

// Use recursion to duplicate all menu items in a menu

function duplicate_menu_items($properties) {

    $new_menu_id = $properties['new_menu_id'];
    $old_menu_id = $properties['old_menu_id'];
    $new_parent_id = $properties['new_parent_id'];
    $old_parent_id = $properties['old_parent_id'];

    // Get the old menu items for this level of the menu
    $menu_items = db_items(
        "SELECT
            id,
            name,
            sort_order,
            link_page_id,
            link_url,
            link_target,
            security
        FROM menu_items
        WHERE
            menu_id = '" . e($old_menu_id) . "'
            AND parent_id = '" . e($old_parent_id) . "'
        ORDER BY sort_order");

    // Loop through the menu items at this level, in order to duplicate them
    foreach ($menu_items as $menu_item) {

        // Create new menu item that is a duplicate of the old one
        db(
            "INSERT INTO menu_items (
                menu_id,
                parent_id,
                name,
                sort_order,
                link_page_id,
                link_url,
                link_target,
                security,
                created_user_id,
                created_timestamp,
                last_modified_user_id,
                last_modified_timestamp)
            VALUES (
                '" . e($new_menu_id) . "',
                '" . e($new_parent_id) . "',
                '" . e($menu_item['name']) . "',
                '" . e($menu_item['sort_order']) . "',
                '" . e($menu_item['link_page_id']) . "',
                '" . e($menu_item['link_url']) . "',
                '" . e($menu_item['link_target']) . "',
                '" . e($menu_item['security']) . "',
                '" . USER_ID . "',
                UNIX_TIMESTAMP(),
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        // Use recursion to duplicate all the menu items under this parent item
        duplicate_menu_items(array(
            'new_menu_id' => $new_menu_id,
            'old_menu_id' => $old_menu_id,
            'new_parent_id' => mysqli_insert_id(db::$con),
            'old_parent_id' => $menu_item['id']));
    }
}
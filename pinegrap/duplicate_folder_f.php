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

// Duplicates a folder all pages directly inside.  Does not duplicate sub-folders.

function duplicate_folder($request) {

    $folder = db_item(
        "SELECT
            folder_id AS id,
            folder_name AS name,
            folder_parent AS parent,
            folder_level AS level,
            folder_style AS style,
            mobile_style_id AS mobile_style_id,
            folder_order,
            folder_access_control_type AS access_control_type,
            folder_archived AS archived
        FROM folder
        WHERE folder_id = '" . e($request['folder']['id']) . "'");

    if (!$folder) {
        return error_response('Sorry, the folder could not be found.');
    }

    if (!$folder['parent']) {
        return error_response('Sorry, you may not duplicate the root folder.');
    }

    $new_folder = $folder;

    $new_folder['name'] = $folder['name'];

    require_once(dirname(__FILE__) . '/find_replace.php');

    $new_folder['name'] = find_replace(array(
        'content' => $new_folder['name'],
        'keywords' => $request['find_replace_keywords']));

    if ($new_folder['name'] == '') {
        $new_folder['name'] = 'no name';
    }

    $new_folder['name'] = get_unique_name(array(
        'name' => $new_folder['name'],
        'type' => 'folder'));

    // Create new folder.
    db(
        "INSERT INTO folder (
            folder_name,
            folder_parent,
            folder_level,
            folder_style,
            mobile_style_id,
            folder_order,
            folder_access_control_type,
            folder_archived,
            folder_user,
            folder_timestamp)
        VALUES (
            '" . e($new_folder['name']) . "',
            '" . e($new_folder['parent']) . "',
            '" . e($new_folder['level']) . "',
            '" . e($new_folder['style']) . "',
            '" . e($new_folder['mobile_style_id']) . "',
            '" . e($new_folder['folder_order']) . "',
            '" . e($new_folder['access_control_type']) . "',
            '" . e($new_folder['archived']) . "',
            '" . e(USER_ID) . "',
            UNIX_TIMESTAMP())");

    $new_folder['id'] = mysqli_insert_id(db::$con);

    log_activity('Folder was duplicated (' . $folder['name'] . ' -> ' . $new_folder['name'] . ').');

    // Get all pages in old folder in order to duplicate them and place duplicates in new folder.
    $pages = db_items(
        "SELECT page_id AS id FROM page WHERE page_folder = '" . e($folder['id']) . "'");

    if ($pages) {

        require_once(dirname(__FILE__) . '/duplicate_page_f.php');

        foreach ($pages as $page) {
            $response = duplicate_page(array(
                'page' => $page,
                'folder' => $new_folder,
                'find_replace_keywords' => $request['find_replace_keywords']));
        }
    }

    return array(
        'status' => 'success',
        'folder' => $new_folder);
}
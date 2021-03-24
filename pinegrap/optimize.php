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

validate_token_field();

$file = db_item(
    "SELECT
        id,
        name,
        design,
        folder AS folder_id,
        description,
        type,
        size,
        optimized
    FROM files
    WHERE id = '" . e($_GET['id']) . "'");

if (!$file) {
    output_error('Sorry, we could not find that file.');
}

// If the user does not have edit rights to this file's folder, or this file is a design file and
// the user is not a designer or administrator, then log activity and output error.
if (!check_edit_access($file['folder_id']) or ($file['design'] and (USER_ROLE > 1))) {
    log_activity('access denied to optimize image because user does not have access to file');
    output_error('Access denied.');
}

require(dirname(__FILE__) . '/optimize_image.php');

$response = optimize_image($file['id']);

if ($response['status'] == 'error') {
    output_error(h($response['message']));
}

if ($_GET['send_to'] == PATH . SOFTWARE_DIRECTORY . '/view_design_files.php') {
    $form = new liveform('view_design_files');
    $form->add_notice(h($response['message']));
    go($_GET['send_to']);

} else {
    $form = new liveform('view_files');
    $form->add_notice(h($response['message']));
    go(PATH . SOFTWARE_DIRECTORY . '/view_files.php');
}
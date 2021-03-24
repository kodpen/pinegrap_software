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

$liveform = new liveform('edit_auto_dialog');

$auto_dialog = db_item(
    "SELECT
        id,
        name,
        enabled,
        url,
        width,
        height,
        delay,
        frequency,
        page
    FROM auto_dialogs
    WHERE id = '" . e($_REQUEST['id']) . "'");

// If the form has not been submitted, then output it.
if (!$_POST) {

    // If the form has not been submitted yet, then pre-populate fields with data.
    if (!$liveform->field_in_session('id')) {
        $liveform->assign_field_value('name', $auto_dialog['name']);
        $liveform->assign_field_value('enabled', $auto_dialog['enabled']);
        $liveform->assign_field_value('url', $auto_dialog['url']);

        // If there is a width then prefill it.
        // If the value is just zero, then we just want the field to be blank.
        if ($auto_dialog['width']) {
            $liveform->assign_field_value('width', $auto_dialog['width']);
        }

        // If there is a height then prefill it.
        // If the value is just zero, then we just want the field to be blank.
        if ($auto_dialog['height']) {
            $liveform->assign_field_value('height', $auto_dialog['height']);
        }

        // If there is a delay then prefill it.
        // If the value is just zero, then we just want the field to be blank.
        if ($auto_dialog['delay']) {
            $liveform->assign_field_value('delay', $auto_dialog['delay']);
        }

        // If there is a frequency then prefill it.
        // If the value is just zero, then we just want the field to be blank.
        if ($auto_dialog['frequency']) {
            $liveform->assign_field_value('frequency', $auto_dialog['frequency']);
        }

        $liveform->assign_field_value('page', $auto_dialog['page']);
    }

    $screen = 'edit';

    $home_page_name = db_value("SELECT page_name FROM page WHERE page_home = 'yes' ORDER BY page_timestamp DESC LIMIT 1");

    $preview_url = PATH . encode_url_path($home_page_name) . '?preview_auto_dialog=' . $auto_dialog['id'];

    echo output_header();

    require('templates/edit_auto_dialog.php');

    echo output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    $liveform->add_fields_to_session();

    // If the user selected to delete this auto dialog, then delete it.
    if ($liveform->field_in_session('delete')) {
        db("DELETE FROM auto_dialogs WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('auto dialog (' . $auto_dialog['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_auto_dialogs = new liveform('view_auto_dialogs');
        $liveform_view_auto_dialogs->add_notice('The auto dialog has been deleted.');

    // Otherwise the user selected to save the auto dialog, so save it.
    } else {
        $liveform->validate_required_field('name', 'Name is required.');
        $liveform->validate_required_field('url', 'URL is required.');

        // If there is not already an error for the name field,
        // and that name is already in use, then output error.
        if (
            (!$liveform->check_field_error('name'))
            && (db_value("SELECT COUNT(*) FROM auto_dialogs WHERE (name = '" . e($liveform->get_field_value('name')) . "') AND (id != '" . e($liveform->get_field_value('id')) . "')") != 0)
        ) {
            $liveform->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
        }
        
        // If there is an error, forward user back to previous screen.
        if ($liveform->check_form_errors()) {
            go($_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
        }

        $page = $liveform->get_field_value('page');

        // If the first character is a slash, then the user
        // has accidentally entered a URL instead of a page name,
        // so correct the mistake and remove the slash for the user.
        if (mb_substr($page, 0, 1) == '/') {
            $page = mb_substr($page, 1);
        }
        
        db(
            "UPDATE auto_dialogs
            SET
                name = '" . e($liveform->get_field_value('name')) . "',
                enabled = '" . e($liveform->get_field_value('enabled')) . "',
                url = '" . e($liveform->get_field_value('url')) . "',
                width = '" . e($liveform->get_field_value('width')) . "',
                height = '" . e($liveform->get_field_value('height')) . "',
                delay = '" . e($liveform->get_field_value('delay')) . "',
                frequency = '" . e($liveform->get_field_value('frequency')) . "',
                page = '" . e($page) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($liveform->get_field_value('id')) . "'");
        
        log_activity('auto dialog (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_auto_dialogs = new liveform('view_auto_dialogs');
        $liveform_view_auto_dialogs->add_notice('The auto dialog has been saved.');
    }

    $liveform->remove_form();

    go(PATH . SOFTWARE_DIRECTORY . '/view_auto_dialogs.php');
    
}
?>
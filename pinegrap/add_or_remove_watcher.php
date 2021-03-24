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

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('add_or_remove_watcher', $_POST['page_id']);
$liveform->add_fields_to_session();

// If the user is managing watchers instead of adding/removing him/herself, then set variable for that.
if ($liveform->get_field_value('management') == 'true') {
    $management = TRUE;

// Otherwise the user just adding/removing him/herself, so remember that.
} else {
    $management = FALSE;
}

// set values from form
$id = $liveform->get_field_value('id');
$send_to = $liveform->get_field_value('send_to');
$page_id = $liveform->get_field_value('page_id');
$item_id = $liveform->get_field_value('item_id');
$item_type = $liveform->get_field_value('item_type');
$action = $liveform->get_field_value('action');
$username_or_email_address = $liveform->get_field_value('username_or_email_address');

// get page properties
$query =
    "SELECT
        page_name,
        page_type,
        page_folder as folder_id,
        comments,
        comments_label,
        comments_watcher_email_page_id
    FROM page
    WHERE page_id = '" . escape($page_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$page_name = $row['page_name'];
$page_type = $row['page_type'];
$folder_id = $row['folder_id'];
$comments = $row['comments'];
$comments_label = $row['comments_label'];
$comments_watcher_email_page_id = $row['comments_watcher_email_page_id'];

// if comments are disabled for the page, log and output error
if ($comments == 0) {
    log_activity("access denied to add or remove watcher for page ($page_name) because comments are disabled", $_SESSION['sessionusername']);
    output_error('You do not have access to add or remove watchers because comments are disabled for the page. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if watching comments is disabled for the page, log and output error
if ($comments_watcher_email_page_id == 0) {
    log_activity("access denied to add or remove watcher for page ($page_name) because watching comments is disabled", $_SESSION['sessionusername']);
    output_error('You do not have access to add or remove watchers because watching comments is disabled for the page. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if the visitor is not logged in, log and output error
if (USER_LOGGED_IN == FALSE) {
    log_activity("access denied to add or remove watcher for page ($page_name) because visitor is not logged in", $_SESSION['sessionusername']);
    output_error('You do not have access to add or remove watchers because you are not logged in. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if user does not have access to view page then the user does not have access to add or remove him/herself as a watcher, so log and output error
if (check_view_access($folder_id) == false) {
    log_activity("access denied to add or remove watcher for page ($page_name) because user does not have access to view page", $_SESSION['sessionusername']);
    output_error('You do not have access to add or remove watchers because you do not have access to view the page. <a href="javascript:history.go(-1);">Go back</a>.');
}

$output_comment_label_lowercase = h(mb_strtolower(get_comment_label(array('label' => $comments_label))));

// If the user is attempting to manage watchers, then handle request in a certain way.
if ($management == TRUE) {
    // Assume that the visitor does not have access to manage watchers, until we find out otherwise.
    $watcher_management_access = FALSE;

    // If the visitor is logged in, then continue to check if user has access to manage watchers.
    if (USER_LOGGED_IN == TRUE) {
        // If the user has edit access to this page then user has access to manage watchers.
        if (check_edit_access($folder_id) == true) {
            $watcher_management_access = TRUE;

        // Otherwise, if this item is a submitted form, then check if user has access in a different way
        } else if ($item_type == 'submitted_form') {
            // get information for submitted form
            $query =
                "SELECT
                    form_editor_user_id,
                    user_id
                FROM forms
                WHERE id = '" . escape($item_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);

            $form_editor_user_id = $row['form_editor_user_id'];
            $form_submitter_user_id = $row['user_id'];

            // If this user is the form editor for this submitted form,
            // then the user has access to manage watchers.
            if (USER_ID == $form_editor_user_id) {
                $watcher_management_access = TRUE;

            // Otherwise this user is not the form editor, so determine if the submitter is allowed to manage watchers for this page.
            } else {
                $query = "SELECT comments_watchers_managed_by_submitter FROM page WHERE page_id = '" . escape($page_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $comments_watchers_managed_by_submitter = $row['comments_watchers_managed_by_submitter'];
                
                // If the form submitter is allowed to manage watchers for this page, then check if this user is the submitter.
                if ($comments_watchers_managed_by_submitter == 1) {
                    // If this user has the same user id as the user that submitted the form,
                    // then this user is allowed to manage watchers.
                    if (USER_ID == $form_submitter_user_id) {
                        $watcher_management_access = TRUE;

                    // Otherwise this user does not have the same id, so check if this user's e-mail address
                    // is the same as the connect-to-contact e-mail address field value for this submitted form.
                    } else {
                        $query =
                            "SELECT form_data.data
                            FROM form_data
                            LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                            WHERE
                                (form_data.form_id = '" . escape($item_id) . "')
                                AND (form_fields.contact_field = 'email_address')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        $submitter_email_address = $row['data'];

                        // If this user has the same e-mail address as the connect-to-contact e-mail address
                        // field value for this submitted form, then the user has access to manage watchers.
                        if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                            $watcher_management_access = TRUE;
                        }
                    }
                }
            }
        }
    }

    // If this user does not have access to manage watchers, then output error.
    if ($watcher_management_access == FALSE) {
        log_activity('access denied to manage watchers for page (' . $page_name . ')', $_SESSION['sessionusername']);
        output_error('You do not have access to manage watchers. <a href="javascript:history.go(-1);">Go back</a>.');
    }

    // If the manager requested to add a watcher, then do that
    if ($action == 'add') {
        // Try to find a user for the username or e-mail address that the manager entered.
        $query = 
            "SELECT user_id
            FROM user
            WHERE
                (user_username = '" . escape($username_or_email_address) . "')
                OR (user_email = '" . escape($username_or_email_address) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // If a user was found, then determine if we should add watcher in a certain way.
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $watcher_user_id = $row['user_id'];

            // Check if the user is already a watcher.
            $query = 
                "SELECT COUNT(*)
                FROM watchers
                WHERE
                    (user_id = '" . $watcher_user_id . "')
                    AND (page_id = '" . escape($page_id) . "')
                    AND (item_id = '" . escape($item_id) . "')
                    AND (item_type = '" . escape($item_type) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            
            // If the user is already a watcher, then output error.
            if ($row[0] > 0) {
                $liveform->mark_error('username_or_email_address', 'Sorry, that watcher has already been added.');
                header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
                exit();
            }

            // Add record in database for watcher.
            $query = 
                "INSERT INTO watchers (
                    user_id,
                    page_id,
                    item_id,
                    item_type)
                 VALUES (
                    '" . $watcher_user_id . "',
                    '" . escape($page_id) . "',
                    '" . escape($item_id) . "',
                    '" . escape($item_type) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Otherwise a user was not found, so check if value is a valid e-mail address.
        } else {
            // If the value that was entered is not a valid e-mail address then output error.
            if (validate_email_address($username_or_email_address) == FALSE) {
                $liveform->mark_error('username_or_email_address', 'Sorry, the username or e-mail address that you entered is not valid.');
                header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
                exit();
            }

            // Check if there is already a watcher with the entered e-mail address.
            $query = 
                "SELECT COUNT(*)
                FROM watchers
                WHERE
                    (page_id = '" . escape($page_id) . "')
                    AND (item_id = '" . escape($item_id) . "')
                    AND (item_type = '" . escape($item_type) . "')
                    AND (email_address = '" . escape($username_or_email_address) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            
            // If there is already a watcher for the entered e-mail address, then output error.
            if ($row[0] > 0) {
                $liveform->mark_error('username_or_email_address', 'Sorry, that watcher has already been added.');
                header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
                exit();
            }

            // Add record in database for watcher.
            $query = 
                "INSERT INTO watchers (
                    email_address,
                    page_id,
                    item_id,
                    item_type)
                 VALUES (
                    '" . escape($username_or_email_address) . "',
                    '" . escape($page_id) . "',
                    '" . escape($item_id) . "',
                    '" . escape($item_type) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // Remove form so that we get fresh values when we take the visitor back to the previous page.
        $liveform->remove_form();
        
        // Create liveform in order to add notice.
        $liveform = new liveform('add_or_remove_watcher', $_POST['page_id']);
        
        // Add notice to let the manager know that the watcher has been added.
        $liveform->add_notice('The watcher has been added and will be notified via e-mail when a ' . $output_comment_label_lowercase . ' is added in the future.');

        // send user back to previous page
        header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
        exit();

    // Otherwise the manager requested to remove a watcher, so do that.
    } else {
        // Check to make sure that the watcher exists.
        $query = 
            "SELECT COUNT(*)
            FROM watchers
            WHERE
                (id = '" . escape($id) . "')
                AND (page_id = '" . escape($page_id) . "')
                AND (item_id = '" . escape($item_id) . "')
                AND (item_type = '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // If the watcher does not exist, then output error.
        if ($row[0] == 0) {
            $liveform->mark_error('', 'Sorry, that watcher does not exist.');
            header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
            exit();
        }
        
        // Delete record for watcher in database.
        $query = "DELETE FROM watchers WHERE id = '" . escape($id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // Remove form so that we get fresh values when we take the visitor back to the previous page.
        $liveform->remove_form();
        
        // Create liveform in order to add notice.
        $liveform = new liveform('add_or_remove_watcher', $_POST['page_id']);
        
        // Add notice to let the manager know that the watcher has been removed.
        $liveform->add_notice('The watcher has been removed.');

        // send user back to previous page
        header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
        exit();
    }

// Otherwise the user is just attempting to add/remove him/herself, so handle request in a different way.
} else {
    // if the user selected to add him/herself as a watcher, then do that
    if ($action == 'add') {
        // If the page type or item type is not valid for a form item view/submitted form,
        // then log activity and output error.
        if (
            (($page_type == 'form item view') && ($item_type != 'submitted_form'))
            || (($page_type != 'form item view') && ($item_type == 'submitted_form'))
        ) {
            log_activity('access denied to add watcher to page (' . $page_name . ') because the item type was not valid for the page type', $_SESSION['sessionusername']);
            output_error('Sorry, we could not add you as a watcher, because the item type was not valid for the page type. <a href="javascript:history.go(-1);">Go back</a>.');
        }

        // If the item type is submitted form then check if visitor has view access to it.
        if ($item_type == 'submitted_form') {
            // Get various info for the submitted form.
            $submitted_form = db_item(
                "SELECT
                    forms.id,
                    forms.reference_code,
                    forms.form_editor_user_id,
                    forms.user_id AS submitter_user_id,
                    page.page_folder AS custom_form_folder_id
                FROM forms
                LEFT JOIN page ON forms.page_id = page.page_id
                WHERE forms.id = '" . escape($item_id) . "'");

            // If the submitted form was not found, then log and output error.
            if (!$submitted_form['id']) {
                log_activity('access denied to add watcher to page (' . $page_name . ') because the submitted form could not be found', $_SESSION['sessionusername']);
                output_error('Sorry, we could not add you as a watcher, because the submitted form could not be found. <a href="javascript:history.go(-1);">Go back</a>.');
            }

            // Get submitter security for form item view.
            $form_item_view = db_item(
                "SELECT submitter_security
                FROM form_item_view_pages
                WHERE
                    (page_id = '" . escape($page_id) . "')
                    AND (collection = 'a')");

            // If submitter security is enabled for the form item view,
            // then check if the viewer is authorized to view the submitted form.
            if ($form_item_view['submitter_security'] == 1) {
                // Assume that this visitor does not have access to view the submitted form
                // until we find out otherwise.
                $view_access = false;

                // If this visitor submitted this form during this browser session, then grant access.
                if (
                    (is_array($_SESSION['software']['submitted_form_reference_codes']) == true)
                    && (in_array($submitted_form['reference_code'], $_SESSION['software']['submitted_form_reference_codes']) == true)
                ) {
                    $view_access = true;

                // Otherwise, if this visitor is logged in, then continue to check if visitor has view access.
                } else if (USER_LOGGED_IN == TRUE) {
                    // If this user has edit access to the custom form,
                    // or is the form editor for this submitted form,
                    // or is the submitter by direct user id connection,
                    // then the user has view access.
                    if (
                        (check_edit_access($submitted_form['custom_form_folder_id']) == true)
                        || (USER_ID == $submitted_form['form_editor_user_id'])
                        || (USER_ID == $submitted_form['submitter_user_id'])
                    ) {
                        $view_access = true;

                    // Otherwise this user does not have access through those ways
                    // so check if user has access in other ways.
                    } else {
                        $submitter_email_address = db_value(
                            "SELECT form_data.data
                            FROM form_data
                            LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                            WHERE
                                (form_data.form_id = '" . $submitted_form['id'] . "')
                                AND (form_fields.contact_field = 'email_address')");

                        // If this user has the same e-mail address as the connect-to-contact e-mail address
                        // field value for this submitted form, then the user has view access.
                        if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                            $view_access = true;

                        // Otherwise this user is not the submitter, but if they are a watcher,
                        // then grant access.
                        } else if (
                            db_value(
                                "SELECT COUNT(*)
                                FROM watchers
                                WHERE
                                    (
                                        (user_id = '" . USER_ID . "')
                                        OR (email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                    )
                                    AND (page_id = '" . escape($page_id) . "')
                                    AND (item_id = '" . $submitted_form['id'] . "')
                                    AND (item_type = 'submitted_form')")
                            > 0
                        ) {
                            $view_access = true;
                        }
                    }
                }

                // If this visitor does not have view access to the submitted form.
                // then log and output error.
                if ($view_access == false) {
                    log_activity('access denied to add watcher to page (' . $page_name . ') because the visitor did not have view access to submitted form (' . $submitted_form['reference_code'] . ')', $_SESSION['sessionusername']);
                    output_error('Sorry, we could not add you as a watcher, because you do not have access to view that submitted form. <a href="javascript:history.go(-1);">Go back</a>.');
                }
            }
        }

        // check if the user is already a watcher
        $query = 
            "SELECT id
            FROM watchers
            WHERE
                (
                    (user_id = '" . USER_ID . "')
                    || (email_address = '" . USER_EMAIL_ADDRESS . "')
                )
                AND (page_id = '" . escape($page_id) . "')
                AND (item_id = '" . escape($item_id) . "')
                AND (item_type = '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the user is already a watcher, then output error
        if (mysqli_num_rows($result) > 0) {
            output_error('You may not add yourself as a watcher because you are already a watcher. <a href="javascript:history.go(-1);">Go back</a>.');
        }
        
        // add record in database for watcher
        $query = 
            "INSERT INTO watchers (
                user_id,
                page_id,
                item_id,
                item_type)
             VALUES (
                '" . USER_ID . "',
                '" . escape($page_id) . "',
                '" . escape($item_id) . "',
                '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // remove form so that we get fresh values when we take the visitor back to the previous page
        $liveform->remove_form();
        
        // create liveform in order to add notice
        $liveform = new liveform('add_or_remove_watcher', $_POST['page_id']);
        
        // add notice to let the user know that they have been added
        $liveform->add_notice('You will now be notified when a ' . $output_comment_label_lowercase . ' is added.');
        
    // else the user selected to remove him/herself from being a watcher, so do that
    } else {
        // check to make sure that the user is already a watcher
        $query = 
            "SELECT id
            FROM watchers
            WHERE
                (
                    (user_id = '" . USER_ID . "')
                    || (email_address = '" . USER_EMAIL_ADDRESS . "')
                )
                AND (page_id = '" . escape($page_id) . "')
                AND (item_id = '" . escape($item_id) . "')
                AND (item_type = '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the user is not already a watcher, then output error
        if (mysqli_num_rows($result) == 0) {
            output_error('You may not remove yourself as a watcher because you are not currently a watcher. <a href="javascript:history.go(-1);">Go back</a>.');
        }
        
        // delete record for watcher in database
        // The extra checks to make sure that the user id and email address are not blank
        // should not be necessary, howevever we are just doing them in case a bug ever happens
        // and many watchers are deleted incorrectly.
        $query = 
            "DELETE FROM watchers
            WHERE
                (
                    ((user_id = '" . USER_ID . "') && (user_id != ''))
                    || ((email_address = '" . USER_EMAIL_ADDRESS . "') && (email_address != ''))
                )
                AND (page_id = '" . escape($page_id) . "')
                AND (item_id = '" . escape($item_id) . "')
                AND (item_type = '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // remove form so that we get fresh values when we take the visitor back to the previous page
        $liveform->remove_form();
        
        // create liveform in order to add notice
        $liveform = new liveform('add_or_remove_watcher', $_POST['page_id']);
        
        // add notice to let the user know that they have been removed
        $liveform->add_notice('You will no longer be notified when a ' . $output_comment_label_lowercase . ' is added.');
    }

    // send user back to previous page
    header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#software_watcher');
    exit();
}
?>
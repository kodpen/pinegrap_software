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

check_banned_ip_addresses('add comment');

include_once('liveform.class.php');
$liveform = new liveform('add_comment', $_POST['page_id']);
$liveform->add_fields_to_session();

// get values from form
$send_to = $liveform->get_field_value('send_to');
$page_id = $liveform->get_field_value('page_id');
$item_id = $liveform->get_field_value('item_id');
$item_type = $liveform->get_field_value('item_type');
$name = $liveform->get_field_value('name');
$name_type = $liveform->get_field_value('name_type');
$message = $liveform->get_field_value('message');
$watcher = $liveform->get_field_value('watcher');

// get page properties
$query =
    "SELECT
        page_name,
        page_folder as folder_id,
        page_type,
        comments,
        comments_label,
        comments_allow_new_comments,
        comments_disallow_new_comment_message,
        comments_automatic_publish,
        comments_allow_user_to_select_name,
        comments_require_login_to_comment,
        comments_allow_file_attachments,
        comments_administrator_email_to_email_address,
        comments_administrator_email_subject,
        comments_administrator_email_conditional_administrators,
        comments_submitter_email_page_id,
        comments_watcher_email_page_id
    FROM page
    WHERE page_id = '" . escape($page_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$page_name = $row['page_name'];
$folder_id = $row['folder_id'];
$page_type = $row['page_type'];
$comments = $row['comments'];
$comments_label = $row['comments_label'];
$comments_allow_new_comments = $row['comments_allow_new_comments'];
$comments_disallow_new_comment_message = $row['comments_disallow_new_comment_message'];
$comments_automatic_publish = $row['comments_automatic_publish'];
$comments_allow_user_to_select_name = $row['comments_allow_user_to_select_name'];
$comments_require_login_to_comment = $row['comments_require_login_to_comment'];
$comments_allow_file_attachments = $row['comments_allow_file_attachments'];
$comments_administrator_email_to_email_address = $row['comments_administrator_email_to_email_address'];
$comments_administrator_email_subject = $row['comments_administrator_email_subject'];
$comments_administrator_email_conditional_administrators = $row['comments_administrator_email_conditional_administrators'];
$comments_submitter_email_page_id = $row['comments_submitter_email_page_id'];
$comments_watcher_email_page_id = $row['comments_watcher_email_page_id'];

$comment_label = get_comment_label(array('label' => $comments_label));
$output_comment_label = h($comment_label);
$comment_label_lowercase = mb_strtolower($comment_label);
$output_comment_label_lowercase = h($comment_label_lowercase);

// validate fields that need to be validated
$liveform->validate_required_field('message', 'A ' . $output_comment_label_lowercase . ' is required.');

// If the user selected the publish schedule option, then validate those fields.
if ($liveform->get_field_value('publish') == 'schedule') {
    $liveform->validate_required_field('publish_date_and_time', 'Please select the date &amp; time when you want the ' . $output_comment_label_lowercase . ' to be published.');

    // If there is not already an error for the date & time field,
    // and the value is not valid, then add error.
    if (
        ($liveform->check_field_error('publish_date_and_time') == false)
        && (validate_date_and_time($liveform->get_field_value('publish_date_and_time')) == false)
    ) {
        $liveform->mark_error('publish_date_and_time', 'Please enter a valid date &amp; time when you want the ' . $output_comment_label_lowercase . ' to be published.');
    }

    // If there is not already an error for the date & time field,
    // and the date & time is in the past, then add error.
    if (
        ($liveform->check_field_error('publish_date_and_time') == false)
        && (prepare_form_data_for_input($liveform->get_field_value('publish_date_and_time'), 'date and time') < date('Y-m-d H:i'))
    ) {
        $liveform->mark_error('publish_date_and_time', 'Sorry, the date &amp; time you entered is in the past. Please enter a future date &amp; time.');
    }
}

// if CAPTCHA is enabled then validate CAPTCHA
if (CAPTCHA == TRUE) {
    validate_captcha_answer($liveform);
}

// if an error exists, then send user back to previous screen
if ($liveform->check_form_errors() == true) {
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to') . '#software_add_comment');
    exit();
}

// if comments are disabled for the page, log and output error
if ($comments == 0) {
    log_activity("access denied to submit a comment to page ($page_name) because comments are disabled", $_SESSION['sessionusername']);
    output_error('You do not have access to submit a comment to that page because comments are disabled. <a href="javascript:history.go(-1);">Go back</a>.');
}

// assume that new comments are not allowed, until we find out otherwise
$allow_new_comments = FALSE;

// if the comment is for a specific item, then determine if new comments are allowed for the item
if ($item_id != 0) {
    $query = 
        "SELECT allow_new_comments
        FROM allow_new_comments_for_items
        WHERE
            (page_id = '" . escape($page_id) . "')
            AND (item_id = '" . escape($item_id) . "')
            AND (item_type = '" . escape($item_type) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if a result was found, then use the result
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // if new comments are are allowed then remember that
        if ($row['allow_new_comments'] == 1) {
            $allow_new_comments = TRUE;
        }
        
    // else a result was not found, so use value from page
    } else {
        // if new comments are allowed for the page, then remember that
        if ($comments_allow_new_comments == 1) {
            $allow_new_comments = TRUE;
        }
    }
    
// else the comment is just for the page, not for an item, so use value from page
} else {
    // if new comments are allowed for the page, then remember that
    if ($comments_allow_new_comments == 1) {
        $allow_new_comments = TRUE;
    }
}

// if new comments are not allowed, log and output error
if ($allow_new_comments == FALSE) {
    log_activity("access denied to submit a comment to page ($page_name) because new comments are not allowed", $_SESSION['sessionusername']);
    output_error(h($comments_disallow_new_comment_message) . ' <a href="javascript:history.go(-1);">Go back</a>.');
}

// if the user is not logged in, and if the user is required to login to comment, then output an error
if ((isset($_SESSION['sessionusername']) == FALSE) && ($comments_require_login_to_comment == 1)) {
    log_activity("access denied to submit a comment to page ($page_name) because visitor was not logged in", $_SESSION['sessionusername']);
    output_error('You must login before you can submit a ' . $output_comment_label_lowercase . '. <a href="javascript:history.go(-1);">Go back</a>.');
}

// get user information if user is logged in
$query =
    "SELECT
        user.user_id,
        user.user_role
    FROM user
    WHERE user.user_username = '" . escape($_SESSION['sessionusername']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];
$user_role = $row['user_role'];

// if user does not have access to view page, log and output error
if (check_view_access($folder_id) == false) {
    log_activity("access denied to submit a comment to page ($page_name) because visitor does not have view access to page", $_SESSION['sessionusername']);
    output_error('You do not have access to submit a ' . $output_comment_label_lowercase . ' to that page. <a href="javascript:history.go(-1);">Go back</a>.');
}

$user['id'] = $user_id;
$user['role'] = $user_role;

// If the user is a basic user then get the folders they have access to.
if ($user['role'] == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}

// If the page type or item type is not valid for a form item view/submitted form,
// then log activity and output error.
if (
    (($page_type == 'form item view') && ($item_type != 'submitted_form'))
    || (($page_type != 'form item view') && ($item_type == 'submitted_form'))
) {
    log_activity('access denied to submit a comment to page (' . $page_name . ') because the item type was not valid for the page type', $_SESSION['sessionusername']);
    output_error('Sorry, the ' . $output_comment_label_lowercase . ' was not added, because the item type was not valid for the page type. <a href="javascript:history.go(-1);">Go back</a>.');
}

// If the item type is submitted form then prepare for that type of comment.
if ($item_type == 'submitted_form') {
    // Get various info for the submitted form.
    $submitted_form = db_item(
        "SELECT
            forms.id,
            forms.page_id AS custom_form_page_id,
            forms.reference_code,
            forms.form_editor_user_id,
            forms.user_id AS submitter_user_id,
            page.page_folder AS custom_form_folder_id,
            user.user_email AS form_editor_email_address
        FROM forms
        LEFT JOIN page ON forms.page_id = page.page_id
        LEFT JOIN user ON forms.form_editor_user_id = user.user_id
        WHERE forms.id = '" . escape($item_id) . "'");

    // If the submitted form was not found, then log and output error.
    if (!$submitted_form['id']) {
        log_activity('access denied to submit a comment to page (' . $page_name . ') because the submitted form could not be found', $_SESSION['sessionusername']);
        output_error('Sorry, the ' . $output_comment_label_lowercase . ' was not added, because the submitted form could not be found. <a href="javascript:history.go(-1);">Go back</a>.');
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
            log_activity('access denied to submit a comment to page (' . $page_name . ') because the visitor did not have view access to submitted form (' . $submitted_form['reference_code'] . ')', $_SESSION['sessionusername']);
            output_error('Sorry, the ' . $output_comment_label_lowercase . ' was not added, because you do not have access to view that submitted form. <a href="javascript:history.go(-1);">Go back</a>.');
        }
    }
}

// if the user is not logged in,
// or if the user has a role greater than a user role,
// or if the user has edit rights to this page,
// or if the item is a submitted form and this user is the form editor
// then save the name they entered
if (
    (isset($_SESSION['sessionusername']) == FALSE)
    || ($user['role'] < 3)
    || (check_folder_access_in_array($folder_id, $folders_that_user_has_access_to) == TRUE)
    || (($item_type == 'submitted_form') && ($user['id'] == $submitted_form['form_editor_user_id']))
) {
    $name = $name;

// else if user is allowed to select name, then set the name that the user selected
} elseif ($comments_allow_user_to_select_name == 1) {
    // if a certain type of name was selected, then get name for user, because we will need it below.
    if (
        ($name_type == 'first_name')
        || ($name_type == 'first_name_last_initial')
        || ($name_type == 'first_initial_last_name')
        || ($name_type == 'first_initial_last_initial')
        || ($name_type == 'full_name')
    ) {
        // get name for the user because we might need it below
        $query =
            "SELECT
                contacts.first_name,
                contacts.last_name
            FROM user
            LEFT JOIN contacts ON user_contact = contacts.id
            WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        $contact = mysqli_fetch_assoc($result);
    }
    
    // prepare the name differently based on the type of name that was selected
    switch ($name_type) {
        case 'username':
            $name = $_SESSION['sessionusername'];
            break;
            
        case 'first_name':
            $name = $contact['first_name'];
            break;
            
        case 'first_name_last_initial':
            $name = $contact['first_name'] . ' ' . mb_substr($contact['last_name'], 0, 1) . '.';
            break;
            
        case 'first_initial_last_name':
            $name = mb_substr($contact['first_name'], 0, 1) . '. ' . $contact['last_name'];
            break;
            
        case 'first_initial_last_initial':
            $name = mb_substr($contact['first_name'], 0, 1) . '. ' . mb_substr($contact['last_name'], 0, 1) . '.';
            break;
            
        case 'full_name':
            $name = $contact['first_name'] . ' ' . $contact['last_name'];
            break;
            
        case 'anonymous':
            $name = 'Anonymous';
            break;
    }

// else save the user's username
} else {
    $name = $_SESSION['sessionusername'];
}

// if there is not a name to save, then save anonymous
if ($name == '') {
    $name = 'Anonymous';
}

$file_id = 0;

// If file attachments are allowed and a file was uploaded, then add file.
if (($comments_allow_file_attachments == 1) && ($_FILES['file']['name'])) {
    $file_name = prepare_file_name($_FILES['file']['name']);

    // Check if file name is already in use and change it if necessary.
    $file_name = get_unique_name(array(
        'name' => $file_name,
        'type' => 'file'));

    $file_size = $_FILES['file']['size'];
    $file_temp_name = $_FILES['file']['tmp_name'];
    
    // Get the position of the last period in order to get the extension.
    $position_of_last_period = mb_strrpos($file_name, '.');

    $file_extension = '';
    
    // If there is an extension then remember it.
    if ($position_of_last_period !== false) {
        $file_extension = mb_substr($file_name, $position_of_last_period + 1);
    }
    
    // create file
    copy($file_temp_name, FILE_DIRECTORY_PATH . '/' . $file_name);

    // create file record in database
    $query =
        "INSERT INTO files (
            name,
            folder,
            type,
            size,
            user,
            design,
            attachment,
            timestamp)
        VALUES (
            '" . escape($file_name) . "',
            '" . escape($folder_id) . "',
            '" . escape($file_extension) . "',
            '" . escape($file_size) . "',
            '$user_id',
            '0',
            '1',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $file_id = mysqli_insert_id(db::$con);
}

$published = '';
$publish_date_and_time = '';
$publish_cancel = '';

// If the user has access to publish comments for this page (i.e. edit access to page),
// or if this comment is for a submitted form and the user is the form editor,
// then allow user to set publish settings.
if (
    (check_edit_access($folder_id))
    ||
    (
        ($item_type == 'submitted_form')
        && (USER_LOGGED_IN)
        && (USER_ID == $submitted_form['form_editor_user_id'])
    )
) {
    switch ($liveform->get_field_value('publish')) {
        case 'now':
            $published = '1';
            break;
        
        case 'schedule':
            $publish_date_and_time = prepare_form_data_for_input($liveform->get_field_value('publish_date_and_time'), 'date and time');

            if ($liveform->get_field_value('publish_cancel')) {
                $publish_cancel = '1';
            }

            break;
    }
    
// else if automatic publish is enabled, then set the comment to be published
} else if ($comments_automatic_publish == 1) {
    $published = 1;
    
// else automatic publish is disabled, so set the comment to not be published
} else {
    $published = 0;
}

// add comment to database
$query = 
    "INSERT INTO comments (
        page_id,
        item_id,
        item_type,
        name,
        message,
        file_id,
        published,
        publish_date_and_time,
        publish_cancel,
        created_user_id,
        created_timestamp,
        last_modified_user_id,
        last_modified_timestamp)
     VALUES (
        '" . escape($page_id) . "',
        '" . escape($item_id) . "',
        '" . escape($item_type) . "',
        '" . escape($name) . "',
        '" . escape($message) . "',
        '$file_id',
        '" . escape($published) . "',
        '" . e($publish_date_and_time) . "',
        '" . e($publish_cancel) . "',
        '" . escape($user_id) . "',
        UNIX_TIMESTAMP(),
        '" . escape($user_id) . "',
        UNIX_TIMESTAMP())";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// get new comment's id
$comment_id = mysqli_insert_id(db::$con);

// If this comment was published then cancel any necessary scheduled comments,
// that are set to be cancelled if new comments are added.
if ($published == '1') {
    db(
        "UPDATE comments
        SET
            publish_date_and_time = '',
            publish_cancel = ''
        WHERE
            (publish_cancel = '1')
            AND (page_id = '" . e($page_id) . "')
            AND (item_id = '" . e($item_id) . "')
            AND (item_type = '" . e($item_type) . "')");
}

// if the added comments session array is not initialized, then initialize it
if (isset($_SESSION['software']['added_comments']) == FALSE) {
    $_SESSION['software']['added_comments'] = array();
}

// if there is a comment id then add comment id to added comments array
if ($comment_id != '') {
    $_SESSION['software']['added_comments'][] = $comment_id;
}

// if comment is published, and if this is a form item view, then update the submitted_form_info table so that form list views can show comment info
if (($published == '1') && ($page_type == 'form item view')) {
    // Get the number of views so we do not lose that data when we delete record below.
    $number_of_views = db_value("SELECT number_of_views FROM submitted_form_info WHERE (submitted_form_id = '" . escape($item_id) . "') AND (page_id = '" . escape($page_id) . "')");

    // get the number of published comments for this submitted form and page
    $query =
        "SELECT COUNT(*)
        FROM comments
        WHERE
            (page_id = '" . escape($page_id) . "')
            AND (item_id = '" . escape($item_id) . "')
            AND (item_type = '" . escape($item_type) . "')
            AND (published = '1')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_comments = $row[0];
    
    // delete the current record if one exists
    $query = "DELETE FROM submitted_form_info WHERE (submitted_form_id = '" . escape($item_id) . "') AND (page_id = '" . escape($page_id) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // insert a new record
    $query = 
        "INSERT INTO submitted_form_info (
            submitted_form_id,
            page_id,
            number_of_views,
            number_of_comments,
            newest_comment_id)
         VALUES (
            '" . escape($item_id) . "',
            '" . escape($page_id) . "',
            '$number_of_views',
            '$number_of_comments',
            '$comment_id')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

// prepare to e-mail addresses for the administrator e-mail
$administrator_email_addresses = array();

// if there is a to e-mail address, then add it to the array
if ($comments_administrator_email_to_email_address != '') {
    $administrator_email_addresses[] = $comments_administrator_email_to_email_address;
}

// if the page type is form item view then get additional administrator e-mail addresses
if ($page_type == 'form item view') {
    // if e-mailing conditional administrators is enabled, then continue to check if there are conditional administrators that should be e-mailed
    if ($comments_administrator_email_conditional_administrators == 1) {
        // get all form field options that have conditional administrator e-mail addresses where the data that was submitted matches the option value
        $query = 
            "SELECT form_field_options.email_address
            FROM form_field_options
            LEFT JOIN form_data ON form_field_options.form_field_id = form_data.form_field_id
            WHERE
                (form_field_options.page_id = '" . $submitted_form['custom_form_page_id'] . "')
                AND (form_data.form_id = '" . escape($item_id) . "')
                AND (form_field_options.email_address != '')
                AND (form_data.data = form_field_options.value)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through results and add them to array
        while($row = mysqli_fetch_assoc($result)) {
            // We support multiple conditional admin email addresses, separated by comma,
            // (e.g. ^^example1@example.com,example2@example.com^^), so deal with that.
            $conditional_email_addresses = explode(',', $row['email_address']);

            foreach ($conditional_email_addresses as $conditional_email_address) {
                $conditional_email_address = trim($conditional_email_address);

                // if this e-mail address has not already been added to the array, then add it
                if (in_array($conditional_email_address, $administrator_email_addresses) == false) {
                    $administrator_email_addresses[] = $conditional_email_address;
                }
            }
        }
    }
    
    // if a form editor e-mail address was found and it has not already been added, then add it
    if (($submitted_form['form_editor_email_address'] != '') && (in_array($submitted_form['form_editor_email_address'], $administrator_email_addresses) == FALSE)) {
        $administrator_email_addresses[] = $submitted_form['form_editor_email_address'];
    }
}

// if there is at least one administrator e-mail address to e-mail, then prepare to send an e-mail to the administrator
if (count($administrator_email_addresses) > 0) {

    $body = '';
    
    $output_submitter_name = '';
    
    // if the comment submitter is logged in, then prepare to include username next to the name
    if ((isset($_SESSION['sessionusername']) == true) && ($_SESSION['sessionusername'] != '')) {
        $submitter_name = '';
                
        // if the username is the same as the display name, then get the user's full name from their contact record
        if ($_SESSION['sessionusername'] == $name) {
            // get the user's contact info
            $query =
                "SELECT
                    contacts.first_name,
                    contacts.last_name
                FROM user
                LEFT JOIN contacts ON user.user_contact = contacts.id
                WHERE user.user_username = '" . escape($_SESSION['sessionusername']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            
            // if the first name is not blank, then add it to the submitter name
            if ($first_name != '') {
                $submitter_name = $first_name;
            }
            
            // if the last name is not blank, then add it to the submitter name
            if ($last_name != '') {
                if ($first_name != '') {
                    $submitter_name .= ' ';
                }
                
                $submitter_name .= $last_name;
            }
        }
        
        // if the submitter name is blank, then set the username as the submitter's name
        if ($submitter_name == '') {
            $submitter_name = $_SESSION['sessionusername'];
        }
        
        // output the submitter name
        $output_submitter_name = ' (' . $submitter_name . ')';
    }
    
    $attachment_line = '';
    
    // if there is an attachment, then add line to e-mail content with link to attachment
    if ($file_id != 0) {
        $attachment_line =
            "\n" .
            'Attachment: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path($file_name) . ' (' . convert_bytes_to_string($file_size) . ')' . "\n";
    }
    
    // if comment was published, then build a message that informs the administrator
    if ($published == '1') {
        $subject = '';
        
        // if the administrator e-mail subject is not blank, then prepare it for output and set it to the e-mail subject
        if ($comments_administrator_email_subject != '') {
            // if the item is a submitted form, then prepare to replace variables in subject with submitted form data
            if ($item_type == 'submitted_form') {
                $subject = get_variable_submitted_form_data_for_content($page_id, $item_id, $comments_administrator_email_subject, $prepare_for_html = FALSE);
                
            // else the item is not a submitted form, so just set the subject
            } else {
                $subject = $comments_administrator_email_subject;
            }
        
        // else use the default subject
        } else {
            $subject = 'A ' . $comment_label_lowercase . ' has been added and published.';
        }
        
        // set body message
        $body .=
            'The following ' . $comment_label_lowercase . ' has been added and published:' . "\n" .
            "\n" .
            'Display Name: ' . $name . $output_submitter_name . "\n" .
            "\n" .
            $comment_label . ': ' . $message . "\n" .
            $attachment_line;
            
    // else build a message that tells the administrator that they need to publish the comment
    } else {
        $subject = '';
        
        // if the administrator e-mail subject is not blank, then prepare it for output and set it to the e-mail subject
        if ($comments_administrator_email_subject != '') {
            // if the item is a submitted form, then prepare to replace variables in subject with submitted form data
            if ($item_type == 'submitted_form') {
                $subject = get_variable_submitted_form_data_for_content($page_id, $item_id, $comments_administrator_email_subject, $prepare_for_html = FALSE);
                
            // else the item is not a submitted form, so just set the subject
            } else {
                $subject = $comments_administrator_email_subject;
            }
        
        // else use the default subject
        } else {
            $subject = 'A ' . $comment_label_lowercase . ' has been added, but it has not been published yet.';
        }

        $output_dashes = '';
        $output_schedule = '';

        // If this is a scheduled comment, then output info about that.
        if ($publish_date_and_time) {
            // Output a line of dashes above and below the schedule comment intro,
            // so that a moderator can quickly understand that the comment is
            // scheduled.  We use 78 dashes because that should be wider
            // than the intro line in most cases, depending on the comment label.
            // Many email clients will wrap plain-text email lines after 78 chars,
            // so we don't want to use more than that.  It is probably better visually,
            // to use more than necessary, instead of less.  We could count
            // the total characters in the comment label and intro line,
            // but we are not going to bother with that for now.
            // Many email clients don't use monospace for plain-text emails,
            // so regardless of what we do, this will not line up properly anyway for many people.

            $output_dashes = '------------------------------------------------------------------------------' . "\n";

            $output_schedule =
                'It is scheduled to be published on ' .
                get_absolute_time(array(
                    'timestamp' => strtotime($publish_date_and_time),
                    'format' => 'plain_text',
                    'timezone_type' => 'site')) . '.' . "\n";

            // If the schedule is set to be cancelled if new comments are added,
            // then add more info about that.
            if ($publish_cancel) {
                $output_schedule .= 'However, it will not be published if a new ' . $comment_label_lowercase . ' is added first.' . "\n";
            }

            $output_action = 'edit';

        } else {
            $output_action = 'publish';
        }
        
        // set body message
        $body .=
            $output_dashes .
            'The following ' . $comment_label_lowercase . ' has been added, but it has not been published yet.' . "\n" .
            $output_schedule .
            $output_dashes .
            "\n" .
            'Display Name: ' . $name . $output_submitter_name . "\n" .
            "\n" .
            $comment_label . ': ' . $message . "\n" .
            $attachment_line .
            "\n" .
            'You may ' . $output_action . ' the ' . $comment_label_lowercase . ' by clicking on the link below.' . "\n" .
            "\n" .
            URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_comment.php?id=' . $comment_id . "\n";
    }

    $query_string = get_query_string_for_page_url($page_type, $item_id, $item_type);

    // If this is the first item that is being added to the query string, then add question mark.
    if (mb_strpos($query_string, '?') === false) {
        $query_string .= '?';
        
    // Otherwise this is not the first item that is being added to the query string, so add ampersand.
    } else {
        $query_string .= '&';
    }

    // Add comments parameter now.
    $query_string .= 'comments=all';
    
    // build the view comment link
    $body .=
        "\n" .
        'The ' . $comment_label_lowercase . ' appears at the link below.' . "\n" .
        "\n" .
        URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id) . $query_string . '#c-' . $comment_id;

    email(array(
        'to' => $administrator_email_addresses,
        'from_name' => ORGANIZATION_NAME,
        'from_email_address' => EMAIL_ADDRESS,
        'subject' => $subject,
        'body' => $body
    ));

}

// if comment is published, and if this is a form item view, and if there is a page to send
// then send e-mail to submitter letting them know a comment has been added
if (($published == '1') && ($page_type == 'form item view') && ($comments_submitter_email_page_id != '0')) {
    send_comment_email_to_custom_form_submitter($comment_id);
}

// if comment is published, and if there is a page to send
// then send e-mail to watchers letting them know a comment has been added
if (($published == '1') && ($comments_watcher_email_page_id != '0')) {
    send_comment_email_to_watchers($comment_id);
}

// if comment watching is enabled and the user is logged in and the user selected to be added as a watcher,
// then add user as a watcher if he/she is not already one
if (
    ($comments_watcher_email_page_id != 0)
    && (isset($_SESSION['sessionusername']) == TRUE)
    && ($watcher == 1)
) {
    // check if the user is already a watcher
    $query = 
        "SELECT id
        FROM watchers
        WHERE
            (user_id = '" . $user_id . "')
            AND (page_id = '" . escape($page_id) . "')
            AND (item_id = '" . escape($item_id) . "')
            AND (item_type = '" . escape($item_type) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if the user is not already a watcher, then add the user as a watcher
    if (mysqli_num_rows($result) == 0) {
        $query = 
            "INSERT INTO watchers (
                user_id,
                page_id,
                item_id,
                item_type)
             VALUES (
                '" . escape($user_id) . "',
                '" . escape($page_id) . "',
                '" . escape($item_id) . "',
                '" . escape($item_type) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
}

// Determine if there are featured comments in order to determine if we need to modify URL and add comments parameter.
$query =
    "SELECT COUNT(*)
    FROM comments
    WHERE
        (page_id = '" . escape($page_id) . "')
        AND (item_id = '" . escape($item_id) . "')
        AND (item_type = '" . escape($item_type) . "')
        AND (featured = '1')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);

// If there are featured comments, then add comments parameter to query string.
if ($row[0] > 0) {
    // Get current URL parts in order to prepare URL to send visitor to.
    $url_parts = parse_url($send_to);

    // Put query string parameters into an array in order to prepare new query string.
    parse_str($url_parts['query'], $query_string_parameters);

    $query_string = '';

    // Loop through the query string parameters in order to build query string.
    foreach ($query_string_parameters as $name => $value) {
        // If this is not the comments view query string parameter, then add it.
        if ($name != 'comments') {
            // If this is the first item that is being added to the query string, then add question mark.
            if ($query_string == '') {
                $query_string = '?';
                
            // Otherwise this is not the first item that is being added to the query string, so add ampersand.
            } else {
                $query_string .= '&';
            }
            
            $query_string .= urlencode($name) . '=' . urlencode($value);
        }
    }

    // If this is the first item that is being added to the query string, then add question mark.
    if ($query_string == '') {
        $query_string = '?';
        
    // Otherwise this is not the first item that is being added to the query string, so add ampersand.
    } else {
        $query_string .= '&';
    }

    // Add comments parameter now.
    $query_string .= 'comments=all';

    // Send user back to view comment on page.
    header('Location: ' . URL_SCHEME . HOSTNAME . $url_parts['path'] . $query_string . '#c-' . $comment_id);

} else  {
    // Send user back to view comment on page.
    header('Location: ' . URL_SCHEME . HOSTNAME . $send_to . '#c-' . $comment_id);
}

// remove liveform
$liveform->remove_form();
?>
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

// this script processes a submitted custom form
include('init.php');

validate_token_field();

check_banned_ip_addresses('submit custom form');

/* begin: check that user has access to submit this form */

// get page properties
$query =
    "SELECT
        page.page_folder,
        custom_form_pages.form_name,
        custom_form_pages.enabled,
        custom_form_pages.quiz,
        custom_form_pages.quiz_pass_percentage,
        custom_form_pages.watcher_page_id,
        custom_form_pages.save,
        custom_form_pages.auto_registration,
        custom_form_pages.hook_code,
        custom_form_pages.submitter_email,
        custom_form_pages.submitter_email_from_email_address,
        custom_form_pages.submitter_email_subject,
        custom_form_pages.submitter_email_format,
        custom_form_pages.submitter_email_body,
        custom_form_pages.submitter_email_page_id,
        custom_form_pages.administrator_email,
        custom_form_pages.administrator_email_to_email_address,
        custom_form_pages.administrator_email_bcc_email_address,
        custom_form_pages.administrator_email_subject,
        custom_form_pages.administrator_email_format,
        custom_form_pages.administrator_email_body,
        custom_form_pages.administrator_email_page_id,
        custom_form_pages.contact_group_id,
        custom_form_pages.membership,
        custom_form_pages.membership_days,
        custom_form_pages.membership_start_page_id,
        custom_form_pages.private,
        custom_form_pages.private_folder_id,
        custom_form_pages.private_days,
        custom_form_pages.private_start_page_id,
        custom_form_pages.offer,
        custom_form_pages.offer_id,
        custom_form_pages.offer_days,
        custom_form_pages.offer_eligibility,
        custom_form_pages.confirmation_type,
        custom_form_pages.confirmation_page_id,
        custom_form_pages.confirmation_alternative_page,
        custom_form_pages.confirmation_alternative_page_contact_group_id,
        custom_form_pages.confirmation_alternative_page_id
    FROM page
    LEFT JOIN custom_form_pages ON page.page_id = custom_form_pages.page_id
    WHERE page.page_id = '" . escape($_POST['page_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

if (mysqli_num_rows($result) == 0) {
    output_error('The page, for the form you submitted, can no longer be found. <a href="javascript:history.go(-1);">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

$folder_id = $row['page_folder'];
$form_name = $row['form_name'];
$enabled = $row['enabled'];
$quiz = $row['quiz'];
$quiz_pass_percentage = $row['quiz_pass_percentage'];
$watcher_page_id = $row['watcher_page_id'];
$save = $row['save'];
$auto_registration = $row['auto_registration'];
$hook_code = $row['hook_code'];
$submitter_email = $row['submitter_email'];
$submitter_email_from_email_address = $row['submitter_email_from_email_address'];
$submitter_email_subject = $row['submitter_email_subject'];
$submitter_email_format = $row['submitter_email_format'];
$submitter_email_body = $row['submitter_email_body'];
$submitter_email_page_id = $row['submitter_email_page_id'];
$administrator_email = $row['administrator_email'];
$administrator_email_to_email_address = $row['administrator_email_to_email_address'];
$administrator_email_bcc_email_address = $row['administrator_email_bcc_email_address'];
$administrator_email_subject = $row['administrator_email_subject'];
$administrator_email_format = $row['administrator_email_format'];
$administrator_email_body = $row['administrator_email_body'];
$administrator_email_page_id = $row['administrator_email_page_id'];
$contact_group_id = $row['contact_group_id'];
$membership = $row['membership'];
$membership_days = $row['membership_days'];
$membership_start_page_id = $row['membership_start_page_id'];
$private = $row['private'];
$private_folder_id = $row['private_folder_id'];
$private_days = $row['private_days'];
$private_start_page_id = $row['private_start_page_id'];
$offer = $row['offer'];
$offer_id = $row['offer_id'];
$offer_days = $row['offer_days'];
$offer_eligibility = $row['offer_eligibility'];
$confirmation_type = $row['confirmation_type'];
$confirmation_page_id = $row['confirmation_page_id'];
$confirmation_alternative_page = $row['confirmation_alternative_page'];
$confirmation_alternative_page_contact_group_id = $row['confirmation_alternative_page_contact_group_id'];
$confirmation_alternative_page_id = $row['confirmation_alternative_page_id'];

// If auto-registration is disabled, then check if we need to show an error
// about the user not being logged in, if this form grants membership or private access.
if ($auto_registration == 0) {
    // if custom form grants membership access and the user is not logged in, then output error
    if ((($membership == 1) && ($membership_days > 0)) && ((isset($_SESSION['sessionusername']) == false ) || (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false))) {
        log_activity("access denied to submit data to membership trial custom form ($form_name) because user is not logged in", $_SESSION['sessionusername']);
        output_error('You do not have access to submit data to this membership trial form because you are not logged in. <a href="javascript:history.go(-1);">Go back</a>.');
    }

    // If the user is not logged in and this form grants private access,
    // then log activity and output error.
    if (!USER_LOGGED_IN && $private && $private_folder_id) {
        log_activity('access denied to submit data to custom form (' . $form_name . ') that grants private access because user is not logged in', $_SESSION['sessionusername']);
        output_error('You do not have access to submit data to this form because you are not logged in. <a href="javascript:history.go(-1);">Go back</a>.');
    }
}

$user_id = '';
$contact_id = '';
$member_id = '';

// If the user is logged in, then get info for user.
if (USER_LOGGED_IN == true) {
    $query = "SELECT
                user.user_id,
                contacts.id as contact_id,
                contacts.member_id
             FROM user
             LEFT JOIN contacts ON user.user_contact = contacts.id
             WHERE user.user_username = '" . escape($_SESSION['sessionusername']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['user_id'];
    $contact_id = $row['contact_id'];
    $member_id = $row['member_id'];
}

// if custom form grants membership access and the user is a member or has been a member, then output error
if (($membership == 1) && ($membership_days > 0) && ($member_id != '')) {
    output_error('You may not submit a membership trial form, because you are a member or have been a member.');
}

// if user is logged into software, then get user info (we will use this in several places below)
if (isset($_SESSION['sessionusername']) == TRUE) {
    $user = validate_user();
}

// if user does not have access to form, log and output error
if (check_view_access($folder_id) == false) {
    log_activity("access denied to submit data to custom form ($form_name)", $_SESSION['sessionusername']);
    output_error('You do not have access to submit data to this form. <a href="javascript:history.go(-1);">Go back</a>.');
}

/* end: check that user has access to submit this form */

// if form is disabled, output error
if (!$enabled) {
    output_error('The form is disabled.');
}

include_once('liveform.class.php');
$liveform = new liveform($_POST['page_id']);

$liveform->add_fields_to_session();

// If save-for-later is enabled and the visitor clicked save button,
// then remember that action.
if ($save and $liveform->field_in_session('save_button')) {
    $action = 'save';

// Otherwise visitor submitted form.
} else {
    $action = 'submit';
}

$connect_to_contact = '';

// if there is a connect to contact passed in the URL string then prepare and save it. This will be used later on.
if (isset($_POST['connect_to_contact']) == TRUE) {
    $connect_to_contact = trim(mb_strtolower($_POST['connect_to_contact']));
}

// get all fields for the custom form (we will use this in various areas below)
$query =
    "SELECT
        id,
        name,
        rss_field,
        label,
        type,
        required,
        default_value,
        use_folder_name_for_default_value,
        wysiwyg,
        multiple,
        contact_field,
        office_use_only,
        upload_folder_id,
        quiz_question,
        quiz_answer
    FROM form_fields
    WHERE (page_id = '" . escape($_POST['page_id']) . "') AND (type != 'information')
    ORDER BY sort_order";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$fields = array();

while ($row = mysqli_fetch_assoc($result)) {
    $fields[] = $row;
}

// assume that we should not deal with office use only fields, until we find out otherwise
$office_use_only = FALSE;

// if the user has edit access to this page and if the user was in edit mode, then we need to deal with office use only fields, so remember that
if (
    (check_edit_access($folder_id) == true)
    && ($liveform->get_field_value('office_use_only') == 'true')
) {
    $office_use_only = TRUE;
}

$pretty_urls = check_if_pretty_urls_are_enabled($_POST['page_id']);

// loop through all fields in order to validate fields
foreach ($fields as $field) {
    // if the field is an office use only field and we need to deal with office use only fields
    // or if the field is not an office use only field,
    // then the field appeared on the form, so validate the field
    if (
        (($field['office_use_only'] == 1) && ($office_use_only == TRUE))
        || ($field['office_use_only'] == 0)
    ) {
        // if field is required and visitor clicked submit button,
        // then validate field
        if ($field['required'] and ($action == 'submit')) {
            // if field is a file upload type
            if ($field['type'] == 'file upload') {
                if (!$_FILES[$field['id']]['name']) {
                    $error_message = '';
                    
                    if ($field['label']) {
                        $error_message = $field['label'] . ' is required.';
                    }
                    
                    $liveform->mark_error($field['id'], $error_message);
                }
                
            // else field is not a file upload type
            } else {
                $error_message = '';
                
                if ($field['label']) {
                    $error_message = $field['label'] . ' is required.';
                }
                
                $liveform->validate_required_field($field['id'], $error_message);
            }
        }

        // if field has date type and there is not already an error for this field and user entered value for field and submitted date is invalid, prepare error
        if (($field['type'] == 'date') && ($liveform->check_field_error($field['id']) == false) && ($liveform->get_field_value($field['id']) != '') && (validate_date($liveform->get_field_value($field['id'])) == false)) {
            $liveform->mark_error($field['id'], 'Please enter a valid date for ' . $field['label']);
        }
        
        // if field has date & time type and there is not already an error for this field and user entered value for field and submitted date & time is invalid, prepare error
        if (($field['type'] == 'date and time') && ($liveform->check_field_error($field['id']) == false) && ($liveform->get_field_value($field['id']) != '') && (validate_date_and_time($liveform->get_field_value($field['id'])) == false)) {
            $liveform->mark_error($field['id'], 'Please enter a valid date &amp; time for ' . $field['label']);
        }
        
        // if field has email address type and there is not already an error for this field and user entered value for field and submitted e-mail address is invalid, prepare error
        if (($field['type'] == 'email address') && ($liveform->check_field_error($field['id']) == false) && ($liveform->get_field_value($field['id']) != '') && (validate_email_address($liveform->get_field_value($field['id'])) == false)) {
            $liveform->mark_error($field['id'], 'Please enter a valid e-mail address for ' . $field['label']);
        }
        
        // if field has time type and there is not already an error for this field and user entered value for field and submitted time is invalid, prepare error
        if (($field['type'] == 'time') && ($liveform->check_field_error($field['id']) == false) && ($liveform->get_field_value($field['id']) != '') && (validate_time($liveform->get_field_value($field['id'])) == false)) {
            $liveform->mark_error($field['id'], 'Please enter a valid time for ' . $field['label']);
        }

        // If this field is a title field and there is not already an error for this field,
        // and the visitor entered a value for this field, and pretty URLs are enabled,
        // then check if address name is already in use.
        if (
            ($field['rss_field'] == 'title')
            && ($liveform->check_field_error($field['id']) == false)
            && ($liveform->get_field_value($field['id']) != '')
            && ($pretty_urls == true)
        ) {
            $address_name = create_address_name($liveform->get_field_value($field['id']));

            // If that address name is already in use, then output error.
            if (db_value("SELECT COUNT(*) FROM forms WHERE (page_id = '" . escape($_POST['page_id']) . "') AND (address_name = '" . escape($address_name) . "')") > 0) {
                $liveform->mark_error($field['id'], 'Sorry, that ' . $field['label'] . ' is already in use. Can you please enter a different ' . $field['label'] . '?');
            }
        }
    }
}

// if CAPTCHA is enabled then validate CAPTCHA
if (CAPTCHA == TRUE) {
    validate_captcha_answer($liveform);
}

// If hooks are enabled and there is hook code, then run it.
if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && ($hook_code != '')) {
    eval(prepare_for_eval($hook_code));
}

// if an error does not exist
if ($liveform->check_form_errors() == false) {
    $reference_code = generate_form_reference_code();

    if ($action == 'save') {
        $complete = 0;
    } else {
        $complete = 1;
    }
    
    // create form
    $query = "INSERT INTO forms (
                page_id,
                complete,
                user_id,
                reference_code,
                tracking_code,
                affiliate_code,
                http_referer,
                ip_address,
                submitted_timestamp,
                last_modified_user_id,
                last_modified_timestamp)
             VALUES (
                '" . escape($_POST['page_id']) . "',
                '$complete',
                '" . $user_id . "',
                '$reference_code',
                '" . escape(get_tracking_code()) . "',
                '" . escape(get_affiliate_code()) . "',
                '" . escape($_SESSION['software']['http_referer']) . "',
                IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0),
                UNIX_TIMESTAMP(),
                '" . $user_id . "',
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $form_id = mysqli_insert_id(db::$con);
    
    // if this is a quiz custom form, then initialize variables for tracking quiz score
    if ($quiz == 1) {
        $number_of_quiz_questions = 0;
        $number_of_correct_answers = 0;
    }

    // Prepare to keep track of whether there are selected options that override the upload folder
    // for file upload fields.
    $upload_folder_id = '';

    // Prepare to keep track of uploaded files so that we can update the folder for all of the files,
    // if we find a selected option that overrides the upload folder.
    $files = array();
    
    $administrator_email_addresses = array();

    // Prepare to keep track of the number of email address fields in order to determine if we can use
    // email address field value for recipient's address.
    $number_of_email_address_fields = 0;

    // Prepare variable that we will use to store value for email address field in case we need to use it for recipient email.
    $email_address = '';

    // Prepare to store connect to contact email address value which we will use for submitter email address.
    $connect_to_contact_email_address = '';
    
    // loop through all fields
    foreach ($fields as $field) {
        // If the field is an office use only field and office use only fields did not appear on the form,
        // then set the value of the field to the default value
        if (($field['office_use_only'] == 1) && ($office_use_only == FALSE)) {
            // If field is set to use folder name for default value, then use it.
            if ($field['use_folder_name_for_default_value'] == 1) {
                $folder_name = db_value("SELECT folder_name FROM folder WHERE folder_id = '" . escape($liveform->get_field_value('folder_id')) . "'");
                $liveform->assign_field_value($field['id'], $folder_name);

            // Otherwise use default value from field.
            } else {
                $liveform->assign_field_value($field['id'], $field['default_value']);
            }
        }
        
        // assume that the form data type is standard until we find out otherwise
        $form_data_type = 'standard';
        
        // if the form field's type is date, date and time, or time, then set form data type to the form field type
        if (
            ($field['type'] == 'date')
            || ($field['type'] == 'date and time')
            || ($field['type'] == 'time')
        ) {
            $form_data_type = $field['type'];
            
        // else if the form field is a wysiwyg text area, then set type to html and prepare content for input
        } elseif (($field['type'] == 'text area') && ($field['wysiwyg'] == 1)) {
            $form_data_type = 'html';
            
            $liveform->assign_field_value($field['id'], prepare_rich_text_editor_content_for_input($liveform->get_field_value($field['id'])));
        }
        
        // if this is a quiz custom form and this is a quiz field, then increase number of quiz questions and initialize variable for correct answer
        if (($quiz == 1) && ($field['quiz_question'] == 1)) {
            $number_of_quiz_questions++;
            $correct_answer = false;
        }
        
        // if this field has multiple values (i.e. check box group or pick list)
        if (is_array($liveform->get_field_value($field['id'])) == true) {
            foreach ($liveform->get_field_value($field['id']) as $value) {
                // store form data
                $query = "INSERT INTO form_data (
                            form_id,
                            form_field_id,
                            data,
                            name,
                            type)
                         VALUES (
                            '" . $form_id . "',
                            '" . $field['id'] . "',
                            '" . escape(prepare_form_data_for_input($value, $field['type'])) . "',
                            '" . escape($field['name']) . "',
                            '$form_data_type')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if this is a quiz custom form and this is a quiz field and a correct answer has not already been found for this field and the answer is correct, then remember that
                if (($quiz == 1) && ($field['quiz_question'] == 1) && ($correct_answer == false) && (mb_strtolower(prepare_form_data_for_input($value, $field['type'])) == mb_strtolower($field['quiz_answer']))) {
                    $correct_answer = true;
                }
            }

        // else this field does not have multiple values
        } else {
            // if field has file upload type and file was uploaded for field, prepare uploaded file
            if (($field['type'] == 'file upload') && ($_FILES[$field['id']]['name'])) {
                $file_name = prepare_file_name($_FILES[$field['id']]['name']);

                // Check if file name is already in use and change it if necessary.
                $file_name = get_unique_name(array(
                    'name' => $file_name,
                    'type' => 'file'));

                $file_size = $_FILES[$field['id']]['size'];
                $file_temp_name = $_FILES[$field['id']]['tmp_name'];

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
                $query = "INSERT INTO files (
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
                            '" . escape($field['upload_folder_id']) . "',
                            '" . escape($file_extension) . "',
                            '" . escape($file_size) . "',
                            '$user_id',
                            '0',
                            '1',
                            UNIX_TIMESTAMP())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $file_id = mysqli_insert_id(db::$con);
                $data = '';

                // Add file to array, so later we can update the folder for the file,
                // if we find a selected option that overrides the upload folder.
                $files[] = array('id' => $file_id);
                
            // else field does not have file upload type
            } else {
                $file_id = '';
                $data = $liveform->get_field_value($field['id']);
            }
            
            // store form data
            $query = "INSERT INTO form_data (
                        form_id,
                        form_field_id,
                        file_id,
                        data,
                        name,
                        type)
                     VALUES (
                        '" . $form_id . "',
                        '" . $field['id'] . "',
                        '" . $file_id . "',
                        '" . escape(prepare_form_data_for_input($data, $field['type'])) . "',
                        '" . escape($field['name']) . "',
                        '$form_data_type')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if this field is connected to a contact field, and if connect to contact is on, prepare SQL for updating contact
            if (($field['contact_field']) && ($connect_to_contact != 'false')) {
                $sql_update_contact .= $field['contact_field'] . " = '" . escape($liveform->get_field_value($field['id'])) . "', ";
            }

            // If this field has an email address field type, then increment counter and store email address
            // in case we need to use email address for submitter email address.
            if ($field['type'] == 'email address') {
                $number_of_email_address_fields++;

                $email_address = $liveform->get_field_value($field['id']);
            }
            
            // if this field is connected to contact e-mail address, then remember this e-mail address because we will use it later
            if ($field['contact_field'] == 'email_address') {
                $connect_to_contact_email_address = $liveform->get_field_value($field['id']);
            }
            
            // if this is a quiz custom form and this is a quiz field and the answer is correct, then remember that
            if (($quiz == 1) && ($field['quiz_question'] == 1) && (mb_strtolower(prepare_form_data_for_input($data, $field['type'])) == mb_strtolower($field['quiz_answer']))) {
                $correct_answer = true;
            }
        }
        
        // if this is a quiz custom form and this is a quiz field and a correct answer was submitted, then increment number of correct answers
        if (($quiz == 1) && ($field['quiz_question'] == 1) && ($correct_answer == true)) {
            $number_of_correct_answers++;
        }

        // If this field is a pick list (without multi-selection) or radio button, then determine if there is an upload folder
        // set for the option that was selected, so that we can override the upload folder
        // for file upload fields if any exist.
        if (
            (
                ($field['type'] == 'pick list')
                && ($field['multiple'] == 0)
            )
            || ($field['type'] == 'radio button')
        ) {
            // We join the folder table in order to make sure that folder still exists for id.
            $test_upload_folder_id = db_value(
                "SELECT folder.folder_id
                FROM form_field_options
                LEFT JOIN folder ON form_field_options.upload_folder_id = folder.folder_id
                WHERE
                    (form_field_options.form_field_id = '" . escape($field['id']) . "')
                    AND (form_field_options.upload_folder_id != 0)
                    AND (form_field_options.value = '" . escape($liveform->get_field_value($field['id'])) . "')");

            // If an upload folder was found for the option that was selected,
            // then store upload folder.
            if ($test_upload_folder_id != '') {
                $upload_folder_id = $test_upload_folder_id;
            }
        }
        
        // if field is a pick list, checkbox or radio button, then add e-mail addressess for form field options to array if there are any, so that we can later send e-mails to those administrators
        if (($field['type'] == 'pick list') || ($field['type'] == 'radio button') || ($field['type'] == 'check box')) {
            $where = '';
            
            // if this field has multiple values (i.e. check box group or pick list), then loop through it and build sql where statement to include all values
            if (is_array($liveform->get_field_value($field['id'])) == true) {
                foreach ($liveform->get_field_value($field['id']) as $value) {
                    // if the where statement is not blank, then add OR
                    if ($where != '') {
                        $where .= ' OR ';
                    }
                    
                    $where .= "value = '" . escape($value) . "'";
                }

            // else this field does not have multiple values, so build sql where statement to include only this value
            } else {
                $where = "value = '" . escape($liveform->get_field_value($field['id'])) . "'";
            }
            
            // get e-mail address from form_field_options table for the field
            $query = "SELECT email_address FROM form_field_options WHERE (form_field_id = '" . escape($field['id']) . "') AND (email_address != '') AND ($where)";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // loop through results and add them to array
            while ($row = mysqli_fetch_assoc($result)) {
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
    }

    // If pretty URLs are enabled, then update address name.
    if ($pretty_urls == true) {
        update_submitted_form_address_name($form_id);
    }

    // If a selected option that overrides the upload folder was found
    // and at least one file was uploaded, then update the folder for all files that were uploaded.
    if (
        ($upload_folder_id != '')
        && (count($files) > 0)
    ) {
        foreach ($files as $file) {
            db("UPDATE files SET folder = '$upload_folder_id' WHERE id = '" . $file['id'] . "'");
        }
    }

    // If the visitor clicked save-for-later button, then send visitor back to
    // custom form page with a confirmation notice and don't complete actions below.
    if ($action == 'save') {

        // If there is a contact, then connect form to contact.
        // We have to do this here, because we are not going to run
        // the rest of the code in this script for save-for-later.
        if ($contact_id) {
            db(
                "UPDATE forms
                SET contact_id = '$contact_id'
                WHERE id = '$form_id'");
        }

        $url = build_url(array(
            'url' => $liveform->get('send_to'),
            'parameters' => array($_POST['page_id'] . '_save_confirmation' => 'true')));

        $liveform->remove();

        go($url);

    }

    // create array that will be used to store contact groups that contact should be added to
    $contact_groups = array();

    // Figure out the email address for the submitter, that we will use in several locations below.
    $submitter_email_address = '';

    // If a connect to contact email address field exists on this form,
    // then use the value for that field for the submitter email address.
    if ($connect_to_contact_email_address != '') {
        $submitter_email_address = $connect_to_contact_email_address;

    // Otherwise if the user is logged, then let's use the user's email address.
    } else if (USER_EMAIL_ADDRESS) {
        $submitter_email_address = USER_EMAIL_ADDRESS;

    // Otherwise if there was just one email address field,
    // then let's assume that is the submitter's email address.
    } else if ($number_of_email_address_fields == 1) {
        $submitter_email_address = $email_address;
    }

    // Assume that an existing auto-registration user is not found,
    // until we find out otherwise.  We will use this later to
    // determine where we should connect to the contact to the user or not.
    $auto_registration_existing_user_found = false;

    // If auto-registration is enabled and the user is not logged in,
    // or the user is logged in but connect to contact is disabled,
    // and there is a submitter email address, then deal with auto-registration.
    if (
        ($auto_registration == 1)
        &&
        (
            (!USER_LOGGED_IN)
            || ($connect_to_contact == 'false')
        )
        && ($submitter_email_address != '')
    ) {
        // Check if user exists for email address.
        $user_id = db_value("SELECT user_id FROM user WHERE user_email = '" . escape($submitter_email_address) . "'");

        // If a user does not exist, then create user.
        if (!$user_id) {
            // Create a username by using everything before "@" in the email address,
            // and, if necessary, add numbers to the end to make it unique.
            $username = strtok($submitter_email_address, '@');
            $username = get_unique_username($username);

            $random_password = get_random_string(array(
                'type' => 'lowercase_letters',
                'length' => 10));

            db(
                "INSERT INTO user (
                    user_username,
                    user_email,
                    user_password,
                    user_role,
                    user_timestamp)
                VALUES (
                    '" . escape($username) . "',
                    '" . escape($submitter_email_address) . "',
                    '" . md5($random_password) . "',
                    '3',
                    UNIX_TIMESTAMP())");

            // Get the new user id so we can connect the submitted form to this user.
            $user_id = mysqli_insert_id(db::$con);

            // Remember email and password in the session, so we can show it on confirmation page.
            $_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] = $submitter_email_address;
            $_SESSION['software']['custom_form_auto_registration'][$form_id]['password'] = $random_password;

            // If there is a registration contact group, then add the user to it.
            if (REGISTRATION_CONTACT_GROUP_ID) {
                $contact_groups[] = REGISTRATION_CONTACT_GROUP_ID;
            }

            // If the user is not already logged in, then auto-login user.
            // The user might already be logged in if connect-to-contact was disabled.
            if (!USER_LOGGED_IN) {
                $_SESSION['sessionusername'] = $username;
                $_SESSION['sessionpassword'] = md5($random_password);

                require_once(dirname(__FILE__) . '/connect_user_to_order.php');
                connect_user_to_order();

                log_activity('user was auto-logged in by custom form auto-registration', $_SESSION['sessionusername']);
            }

        // Otherwise a user was found, so remember that.
        } else {
            $auto_registration_existing_user_found = true;
        }

        // Update the submitter for the submitted form, so it matches
        // the user we just found or created.
        db(
            "UPDATE forms
            SET user_id = '$user_id'
            WHERE id = '$form_id'");
    }

    $key_code = '';

    // If this form grants an offer, then deal with that.
    if ($offer && $offer_id) {
        $offer_code = db_value("SELECT code FROM offers WHERE id = '$offer_id'");

        // If an offer was found, then continue.
        if ($offer_code != '') {
            // Assume the visitor is not eligible until we find out otherwise.
            $eligible = false;

            switch ($offer_eligibility) {
                case 'everyone':
                    $eligible = true;
                    break;

                case 'new_contacts':
                    if (
                        ($submitter_email_address != '')
                        && (!db_value(
                            "SELECT id
                            FROM contacts
                            WHERE email_address = '" . e($submitter_email_address) . "'
                            LIMIT 1"))
                    ) {
                        $eligible = true;
                    }

                    break;
                
                case 'existing_contacts':
                    if (
                        ($submitter_email_address != '')
                        && (db_value(
                            "SELECT id
                            FROM contacts
                            WHERE email_address = '" . e($submitter_email_address) . "'
                            LIMIT 1"))
                    ) {
                        $eligible = true;
                    }

                    break;
            }

            // If the visitor is eligible for the offer, then create key code.
            if ($eligible == true) {
                $key_code = $reference_code;

                $expiration_date = '';

                // If a number of validity days has been defined, then calculate expiration date.
                if ($offer_days) {
                    $expiration_date = date('Y-m-d', strtotime('+' . $offer_days . ' day'));
                }

                db(
                    "INSERT INTO key_codes (
                        code,
                        offer_code,
                        enabled,
                        expiration_date,
                        single_use,
                        report,
                        user,
                        timestamp)
                    VALUES (
                        '$key_code',
                        '" . e($offer_code) . "',
                        '1',
                        '" . $expiration_date . "',
                        '1',
                        'offer_code',
                        '" . USER_ID . "',
                        UNIX_TIMESTAMP())");

                log_activity('key code (' . $reference_code . ') was created because a custom form, that grants an offer, was submitted', $_SESSION['sessionusername']);
            }
        }
    }
    
    // If the custom form grants membership access, and if connect to contact is on, then update the contact's member id (i.e. reference code) and expiration date and set user's start page if necessary
    if (($membership == 1) && ($membership_days > 0) && ($connect_to_contact != 'false')) {
        // Create expiration date based on membership_days value
        $expiration_date = date('Y-m-d', time() + ($membership_days * 86400));
        
        // Add member_id and expiration_date to query.
        $sql_update_contact .= 
            "member_id = '" . $reference_code . "', 
            expiration_date = '" . $expiration_date . "', ";
        
        // if there is a membership start page selected for this custom form, then check if page still exists and set user's start page if necessary
        if ($membership_start_page_id != 0) {
            // check if membership start page still exists
            $query = "SELECT page_id FROM page WHERE page_id = '$membership_start_page_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the membership start page still exists, then continue to set user's start page
            if (mysqli_num_rows($result) > 0) {
                $query = "UPDATE user SET user_home = '$membership_start_page_id' WHERE user_id = '$user_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }
    
    // if contact needs to be updated, create contact if necessary and then update contact
    if ($sql_update_contact) {
        // if contact does not exist, create new contact
        if (!$contact_id) {
            $query = "INSERT INTO contacts (
                        user,
                        timestamp)
                     VALUES (
                        '$user_id',
                        UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $contact_id = mysqli_insert_id(db::$con);

            // If there is a user for this submitted form,
            // and it is not an existing user that was found via auto-registation
            // then connect contact record to user record.
            // We don't want to connect the contact to the user record
            // if the user was found via auto-registation,
            // because this visitor did not login as that user,
            // so we don't want the visitor to be allowed to destroy
            // and existing connection to some other contact.
            if (($user_id) && ($auto_registration_existing_user_found == false)) {
                $query = "UPDATE user SET user_contact = '$contact_id' WHERE user_id = '$user_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
        
        $query = "UPDATE contacts
                 SET
                    $sql_update_contact
                    user = '$user_id',
                    timestamp = UNIX_TIMESTAMP()
                 WHERE id = '$contact_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // if there is a contact, then add contact to contact groups and connect contact to submitted form
    if ($contact_id) {
        // if contact group is set for custom form, add it to array
        if ($contact_group_id != 0) {
            $contact_groups[] = $contact_group_id;
        }
        
        // if the custom form grants membership access,
        // and the membership contact group is set,
        // and the membership contact group has not already been added to the contact groups array
        // then add membership contact group to contact groups array
        if (($membership == 1) && ($membership_days > 0) && (MEMBERSHIP_CONTACT_GROUP_ID != 0) && (in_array(MEMBERSHIP_CONTACT_GROUP_ID, $contact_groups) == false)) {
            $contact_groups[] = MEMBERSHIP_CONTACT_GROUP_ID;
        }
        
        // loop through contact groups that contact should be added to
        foreach ($contact_groups as $contact_group_id) {
            // check if contact group exists
            $query = "SELECT id FROM contact_groups WHERE id = '$contact_group_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if contact group exists
            if (mysqli_num_rows($result) > 0) {
                // check if contact is already in contact group
                $query =
                    "SELECT contact_id
                    FROM contacts_contact_groups_xref
                    WHERE
                        (contact_id = '$contact_id')
                        AND (contact_group_id = '$contact_group_id')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if contact is not already in contact group, then add contact to contact group
                if (mysqli_num_rows($result) == 0) {
                    $query =
                        "INSERT INTO contacts_contact_groups_xref (
                            contact_id,
                            contact_group_id)
                        VALUES (
                            '$contact_id',
                            '$contact_group_id')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }
        
        // connect contact to submitted form
        $query =
            "UPDATE forms
            SET contact_id = '$contact_id'
            WHERE id = '$form_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // If this form grants private access and there is a user,
    // then give user access to private folder, and set start page, if necessary.
    // There should almost always be a user here, however there are very rare situations
    // where, for example, if auto-registration was enabled, but there is no email address field,
    // there might be no user at this point.  The admin should resolve that themselves.
    if ($private && $private_folder_id && $user_id) {
        // Get access control values for this user and folder.
        $row = db_item(
            "SELECT 
                aclfolder_rights AS rights,
                expiration_date
            FROM aclfolder
            WHERE
                (aclfolder_user = '" . $user_id . "')
                AND (aclfolder_folder = '$private_folder_id')");

        $rights = $row['rights'];
        $expiration_date = $row['expiration_date'];

        // If the user does not have edit rights and does not have infinite private rights already,
        // then continue to give user private access or extend it.
        if (
            ($rights != 2)
            && 
            (
                ($rights != 1)
                || ($expiration_date != '0000-00-00')
            )
        ) {
            // Delete existing access control record if one exists, so we can add a new one.
            db(
                "DELETE FROM aclfolder
                WHERE
                    (aclfolder_user = '" . $user_id . "')
                    AND (aclfolder_folder = '$private_folder_id')");

            $new_expiration_date = '';

            // If the private access is limited in length, then prepare new expiration date.
            if ($private_days) {
                // Get current date.
                $current_date = date('Y-m-d');

                // Calculate the expiration date based on the number of days from today.
                $calculated_expiration_date = date('Y-m-d', strtotime($current_date) + ($private_days * 86400));

                // If the calculated expiration is greater than the previous expiration date,
                // then use the calculated expiration date.
                if ($calculated_expiration_date > $expiration_date) {
                    $new_expiration_date = $calculated_expiration_date;

                // Otherwise just use the old expiration date (we don't want to reduce the expiration date).
                } else {
                    $new_expiration_date = $expiration_date;
                }
            }

            // Add access control record in order to give user private access.
            db(
                "INSERT INTO aclfolder (
                    aclfolder_user,
                    aclfolder_folder,
                    aclfolder_rights,
                    expiration_date)
                VALUES (
                    '" . $user_id . "',
                    '$private_folder_id',
                    '1',
                    '$new_expiration_date')");
        }

        // If a start page is set, then set start page for user.
        if ($private_start_page_id) {
            db("UPDATE user SET user_home = '$private_start_page_id' WHERE user_id = '" . $user_id . "'");
        }
    }
    
    // if this is a quiz custom form, then calculate score for quiz and update submitted form
    if ($quiz == 1) {
        // if there is at least one quiz question, the calculate quiz score
        if ($number_of_quiz_questions > 0) {
            $quiz_score = round($number_of_correct_answers / $number_of_quiz_questions * 100);
            
        // else there are no quiz questions, so score is 100
        } else {
            $quiz_score = 100;
        }
        
        // update submitted form with quiz score
        $query =
            "UPDATE forms
            SET quiz_score = '$quiz_score'
            WHERE id = '$form_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // Remember in session that visitor submitted this form, so we know that
    // this visitor is allowed to view the confirmation for this form on
    // custom form confirmation and form item view pages.
    $_SESSION['software']['submitted_form_reference_codes'][] = $reference_code;

    $add_watcher_user = array();

    // If the add watcher feature was used where a watcher can be passed via
    // the query string to the custom form, then get watcher info.
    if ($liveform->field_in_session('add_watcher') == true) {
        $add_watcher_user = db_item(
            "SELECT
                user_id AS id,
                user_email AS email_address
            FROM user
            WHERE
                (user_username = '" . escape($liveform->get_field_value('add_watcher')) . "')
                OR (user_email = '" . escape($liveform->get_field_value('add_watcher')) . "')");
    }

    // If the submitter email is enabled, then send it to the submitter and possibly a watcher.
    if ($submitter_email == 1) {
        // Prepare array to store email address for submitter and
        // any watcher that was added through the hidden add watcher feature.
        $submitter_and_watcher_email_addresses = array();

        // If the submitter email address is not blank, then add it to array.
        if ($submitter_email_address != '') {
            $submitter_and_watcher_email_addresses[] = $submitter_email_address;
        }

        // If the add watcher feature was used and a user was found for the watcher,
        // then add watcher's email address to array.
        if ($add_watcher_user['id'] != '') {
            $submitter_and_watcher_email_addresses[] = $add_watcher_user['email_address'];
        }

        // If at least one submitter or watcher was found,
        // then continue to send email.
        if (count($submitter_and_watcher_email_addresses) > 0) {

            // Loop through the submitter and watcher email addresses,
            // in order to send a separate email to each one.  We don't just
            // want to send one email with multiple tos because they can see eachother's
            // email address that way.  We don't need to re-prepare the subject
            // and body for every round of this loop, because they will be the same each time,
            // however we are just doing it like that for now to save development time.
            foreach ($submitter_and_watcher_email_addresses as $to_email_address) {

                if ($submitter_email_from_email_address != '') {
                    $from_email_address = $submitter_email_from_email_address;
                } else {
                    $from_email_address = EMAIL_ADDRESS;
                }
                
                // check the subject line for variable data and replace any variables with content from the submitted form
                $submitter_email_subject = get_variable_submitted_form_data_for_content($_POST['page_id'], $form_id, $submitter_email_subject, $prepare_for_html = FALSE);

                // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
                // with the key code that was created, or a blank value if no key code was created.
                if ($offer) {
                    $submitter_email_subject = replace_variables(array(
                        'content' => $submitter_email_subject,
                        'fields' => array(array(
                            'name' => 'key_code',
                            'data' => $key_code,
                            'type' => '')),
                        'format' => 'plain_text'));
                }

                // if the format of the e-mail should be plain text, then prepare that
                if ($submitter_email_format == 'plain_text') {

                    // check the body for variable data and replace any variables with content from the submitted form
                    $body = get_variable_submitted_form_data_for_content($_POST['page_id'], $form_id, $submitter_email_body, $prepare_for_html = FALSE);

                    // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
                    // with the key code that was created, or a blank value if no key code was created.
                    if ($offer) {
                        $body = replace_variables(array(
                            'content' => $body,
                            'fields' => array(array(
                                'name' => 'key_code',
                                'data' => $key_code,
                                'type' => '')),
                            'format' => 'plain_text'));
                    }

                    // If a user account was created via the auto-registration feature,
                    // and this is the email for the submitter (i.e. not the email for the watcher)
                    // then show user account info.
                    if (
                        ($_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] != '')
                        && ($to_email_address == $submitter_email_address)
                    ) {
                        $body .=
                            "\n" .
                            "\n" .
                            'New Account:' . "\n" .
                            '------------' . "\n" .
                            'We have created a new account for you on our site. You can find your login info below.' . "\n" .
                            "\n" .
                            'Email: ' . $_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] . "\n" .
                            'Password: ' . $_SESSION['software']['custom_form_auto_registration'][$form_id]['password'];
                    }

                // else the format of the e-mail should be HTML, so prepare that
                } else {

                    require_once(dirname(__FILE__) . '/get_page_content.php');

                    $body = get_page_content($submitter_email_page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $form_id));

                    // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
                    // with the key code that was created, or a blank value if no key code was created.
                    if ($offer) {
                        $body = replace_variables(array(
                            'content' => $body,
                            'fields' => array(array(
                                'name' => 'key_code',
                                'data' => $key_code,
                                'type' => '')),
                            'format' => 'html'));
                    }

                }
                
                email(array(
                    'to' => $to_email_address,
                    'from_name' => ORGANIZATION_NAME,
                    'from_email_address' => $from_email_address,
                    'subject' => $submitter_email_subject,
                    'format' => $submitter_email_format,
                    'body' => $body));

            }
        }
    }
    
    // if an administrator e-mail is enabled and there is an e-mail address to e-mail, then send e-mail
    if (($administrator_email == 1) && (($administrator_email_to_email_address != '') || ($administrator_email_bcc_email_address != '') || (count($administrator_email_addresses) > 0))) {
        
        // if there is a to e-mail address and it has not already been added to the array, then add it
        if (($administrator_email_to_email_address != '') && (in_array($administrator_email_to_email_address, $administrator_email_addresses) == false)) {
            $administrator_email_addresses[] = $administrator_email_to_email_address;
        }
        
        // check the subject line for variable data and replace any variables with content from the submitted form
        $administrator_email_subject = get_variable_submitted_form_data_for_content($_POST['page_id'], $form_id, $administrator_email_subject, $prepare_for_html = FALSE);

        // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
        // with the key code that was created, or a blank value if no key code was created.
        if ($offer) {
            $administrator_email_subject = replace_variables(array(
                'content' => $administrator_email_subject,
                'fields' => array(array(
                    'name' => 'key_code',
                    'data' => $key_code,
                    'type' => '')),
                'format' => 'plain_text'));
        }

        // if the format of the e-mail should be plain text, then prepare that
        if ($administrator_email_format == 'plain_text') {
            // check the body for variable data and replace any variables with content from the submitted form
            $body = get_variable_submitted_form_data_for_content($_POST['page_id'], $form_id, $administrator_email_body, $prepare_for_html = FALSE);

            // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
            // with the key code that was created, or a blank value if no key code was created.
            if ($offer) {
                $body = replace_variables(array(
                    'content' => $body,
                    'fields' => array(array(
                        'name' => 'key_code',
                        'data' => $key_code,
                        'type' => '')),
                    'format' => 'plain_text'));
            }

            // If a user account was created via the auto-registration feature,
            // then show user account info.
            if ($_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] != '') {
                $body .=
                    "\n" .
                    "\n" .
                    'New Account:' . "\n" .
                    '------------' . "\n" .
                    'A new account was created for this user. You can find the login info below.' . "\n" .
                    "\n" .
                    'Email: ' . $_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] . "\n" .
                    'Password: ' . $_SESSION['software']['custom_form_auto_registration'][$form_id]['password'];
            }

        // else the format of the e-mail should be HTML, so prepare that
        } else {

            require_once(dirname(__FILE__) . '/get_page_content.php');
            
            $body = get_page_content($administrator_email_page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $form_id));

            // If this form grants an offer, then replace ^^key_code^^ mail-merge field,
            // with the key code that was created, or a blank value if no key code was created.
            if ($offer) {
                $body = replace_variables(array(
                    'content' => $body,
                    'fields' => array(array(
                        'name' => 'key_code',
                        'data' => $key_code,
                        'type' => '')),
                    'format' => 'html'));
            }

        }

        // In the past we would set the from info to the submitter's address (if one existed),
        // however this caused issues with mail providers using DMARC (e.g. Yahoo, AOL),
        // so we now send it from this site's info and add a reply to for the submitter (if exists).
        
        email(array(
            'to' => $administrator_email_addresses,
            'bcc' => $administrator_email_bcc_email_address,
            'from_name' => ORGANIZATION_NAME,
            'from_email_address' => EMAIL_ADDRESS,
            'reply_to' => $submitter_email_address,
            'subject' => $administrator_email_subject,
            'format' => $administrator_email_format,
            'body' => $body));

    }
    
    // If the user is allowed to select to be a watcher
    // and there is a user for this submitted form
    // and the user selected that he/she wants to be a watcher,
    // and the watcher page still exists and is valid for watching,
    // and conditional admins are disabled
    // or the user is not a conditional admin (already notified),
    // then add user as a watcher.
    // There are certain things that we have already checked when
    // deciding whether to output the check box on the custom form page,
    // so we don't have to check for those again, because the check box should
    // not be checked in those cases anyway (e.g. user is the moderator).
    // We have to check about the conditional admin now, because we did not know
    // what data was going to be submitted, and therefore what conditional admins
    // are valid for this submitted form until it was submitted.
    if (
        ($watcher_page_id != 0)
        && ($user_id)
        && ($liveform->get_field_value('watcher') == 1)
        && ($watcher_page = db_item(
            "SELECT
                page_id AS id,
                comments_administrator_email_conditional_administrators
            FROM page
            WHERE
                (page_id = '$watcher_page_id')
                AND (comments = '1')
                AND (comments_watcher_email_page_id != '0')"))
        && (
            (!$watcher_page['comments_administrator_email_conditional_administrators'])
            ||
            (
                !db_value(
                    "SELECT COUNT(*)
                    FROM form_field_options
                    LEFT JOIN form_data ON form_field_options.form_field_id = form_data.form_field_id
                    WHERE
                        (form_field_options.page_id = '" . e($_POST['page_id']) . "')
                        AND (form_data.form_id = '$form_id')
                        AND (form_field_options.email_address LIKE '%" . e(escape_like(USER_EMAIL_ADDRESS)) . "%')
                        AND (form_data.data = form_field_options.value)")
            )
        )
    ) {
        db(
            "INSERT INTO watchers (
                user_id,
                page_id,
                item_id,
                item_type)
             VALUES (
                '$user_id',
                '$watcher_page_id',
                '$form_id',
                'submitted_form')");
    }

    // If the add watcher feature has been used, then determine if we should add the watcher.
    // The add watcher feature allows a watcher to be passed in the query string to the custom form.
    if (
        ($add_watcher_user['id'] != '')
        && ($liveform->get_field_value('add_watcher_page_id') != '')
    ) {
        // Get details for form item view page.
        // In the future the query below should probably make sure that form item view for page id
        // that was passed is connected to the custom form that was just submitted,
        // in order to prevent visitors from adding watchers to unrelated pages.
        $add_watcher_page = db_item(
            "SELECT
                page_id AS id,
                page_folder AS folder_id
            FROM page
            WHERE
                (page_id = '" . escape($liveform->get_field_value('add_watcher_page_id')) . "')
                AND (page_type = 'form item view')
                AND (comments = '1')
                AND (comments_watcher_email_page_id != '0')");

        // If a page was found and this visitor has view access to the page,
        // then continue to add watcher.  Checking view access just makes sure that
        // visitor can't add watcher to some other page that they don't have access to.
        if (
            ($add_watcher_page['id'] != '')
            && (check_view_access($add_watcher_page['folder_id'], true) == true)
        ) {
            db(
                "INSERT INTO watchers (
                    user_id,
                    page_id,
                    item_id,
                    item_type)
                 VALUES (
                    '" . $add_watcher_user['id'] . "',
                    '" . $add_watcher_page['id'] . "',
                    '$form_id',
                    'submitted_form')");
        }
    }
    
    // store send to before we remove form
    $send_to = $liveform->get_field_value('send_to');
    
    $liveform->remove_form();
    
    // if visitor tracking is on, update visitor record with custom form information, if visitor has not already submitted a custom form
    if (VISITOR_TRACKING == true) {
        $query = "UPDATE visitors
                 SET
                    custom_form_submitted = '1',
                    custom_form_name = '" . escape($form_name) . "',
                    stop_timestamp = UNIX_TIMESTAMP()
                 WHERE (id = '" . $_SESSION['software']['visitor_id'] . "') AND (custom_form_submitted = '0')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // Check if there are auto e-mail campaigns that should be created based on this custom form being submitted.
    create_auto_email_campaigns(array(
        'action' => 'custom_form_submitted',
        'action_item_id' => $_POST['page_id'],
        'contact_id' => $contact_id));
    
    // if this is a quiz custom form, then verify that user has passed quiz
    if ($quiz == 1) {
        // if quiz score is less than the quiz pass percentage, then prepare notice and send user back to custom form
        if ($quiz_score < $quiz_pass_percentage) {
            $liveform->add_notice('The quiz was submitted successfully, however you did not pass the quiz. Your score was ' . $quiz_score . '%.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . $send_to);
            exit();
        }
    }

    // If the confirmation type is message, then forward visitor to send to with confirmation parameter in query string, so that message can be outputted.
    if ($confirmation_type == 'message') {
        $url = build_url(array(
            'url' => $send_to,
            'scheme' => URL_SCHEME,
            'hostname' => HOSTNAME,
            'parameters' => array($_POST['page_id'] . '_confirmation' => 'true')));

        header('Location: ' . $url);

    // Otherwise the confirmation type is page, so forward visitor to page.
    } else {
        // If the visitor is logged in,
        // and an alternative confirmation page is enabled,
        // and the user is in the alternative page contact group,
        // then set confirmation page to alternative page.
        if (
            (USER_LOGGED_IN == true)
            && ($confirmation_alternative_page == 1)
            && ($confirmation_alternative_page_contact_group_id != 0)
            && ($confirmation_alternative_page_id != 0)
            && (db_value("SELECT COUNT(*) FROM contact_groups WHERE id = '$confirmation_alternative_page_contact_group_id'") > 0)
            && (db_value("SELECT COUNT(*) FROM page WHERE page_id = '$confirmation_alternative_page_id'") > 0)
            && (db_value("SELECT COUNT(*) FROM contacts_contact_groups_xref WHERE (contact_id = '" . USER_CONTACT_ID . "') AND (contact_group_id = '$confirmation_alternative_page_contact_group_id')") > 0)
        ) {
            $confirmation_page_id = $confirmation_alternative_page_id;
        }

        // get the confirmation page's page type to see if we need to pass a reference code to it
        $query = "SELECT page_type FROM page WHERE page_id = '" . escape($confirmation_page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $page_type = $row['page_type'];
        
        $submitted_form_reference_code = '';
        
        // If the page type is a custom form confirmation or form item view,
        // then pass the submitted form's reference code to the page.
        if (($page_type == 'custom form confirmation') || ($page_type == 'form item view')) {
            $submitted_form_reference_code = '?r=' . urlencode($reference_code);
        }
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($confirmation_page_id) . $submitted_form_reference_code);
    }
    
// else an error does exist
} else {
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
}
?>
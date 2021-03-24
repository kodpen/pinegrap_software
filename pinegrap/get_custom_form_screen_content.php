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

function get_custom_form_screen_content($properties) {

    $current_page_id = $properties['current_page_id'];
    $form_name = $properties['form_name'];
    $enabled = $properties['enabled'];
    $label_column_width = $properties['label_column_width'];
    $watcher_page_id = $properties['watcher_page_id'];
    $save = $properties['save'];
    $submit_button_label = $properties['submit_button_label'];
    $membership = $properties['membership'];
    $membership_days = $properties['membership_days'];
    $confirmation_type = $properties['confirmation_type'];
    $confirmation_message = $properties['confirmation_message'];
    $return_type = $properties['return_type'];
    $return_message = $properties['return_message'];
    $return_page_id = $properties['return_page_id'];
    $return_alternative_page = $properties['return_alternative_page'];
    $return_alternative_page_contact_group_id = $properties['return_alternative_page_contact_group_id'];
    $return_alternative_page_id = $properties['return_alternative_page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];
    $folder_id_for_default_value = $properties['folder_id_for_default_value'];

    $layout_type = get_layout_type($current_page_id);

    // If save-for-later confirmation should be shown, then show it.
    if (
        $save
        and ($_GET[$current_page_id . '_save_confirmation'] == 'true')
    ) {
        $output =
            '<div class="save_confirmation_message">
                Your form has been saved for later.
            </div>';

    // If the confirmation type is message and the message should be shown, then show it.
    } else if (
        ($confirmation_type == 'message')
        && ($_GET[$current_page_id . '_confirmation'] == 'true')
    ) {
        $output = '<div class="confirmation_message">' . $confirmation_message . '</div>';

    // Otherwise the confirmation message should not be shown, so determine what should be shown.
    } else {
        // If the return type is "message" and the visitor is logged in and the visitor has submitted this form in the past,
        // and the visitor did not come from the control panel, then show return message.
        if (
            ($return_type == 'message')
            && (USER_LOGGED_IN == true)
            && (db_value("SELECT COUNT(*) FROM forms WHERE (page_id = '$current_page_id') AND (user_id = '" . USER_ID . "')") > 0)
            && ($_GET['from'] != 'control_panel')
        ) {
            $output = '<div class="return_message">' . $return_message . '</div>';

        // Otherwise if the return type is "page" and the visitor is logged in and the visitor has submitted this form in the past,
        // and the visitor did not come from the control panel, then send user to return page.
        } else if (
            ($return_type == 'page')
            && (USER_LOGGED_IN == true)
            && (db_value("SELECT COUNT(*) FROM forms WHERE (page_id = '$current_page_id') AND (user_id = '" . USER_ID . "')") > 0)
            && ($_GET['from'] != 'control_panel')
        ) {
            // If an alternative return page is enabled,
            // and the user is in the alternative page contact group,
            // then set return page to alternative page.
            if (
                ($return_alternative_page == 1)
                && ($return_alternative_page_contact_group_id != 0)
                && ($return_alternative_page_id != 0)
                && (db_value("SELECT COUNT(*) FROM contact_groups WHERE id = '$return_alternative_page_contact_group_id'") > 0)
                && (db_value("SELECT COUNT(*) FROM page WHERE page_id = '$return_alternative_page_id'") > 0)
                && (db_value("SELECT COUNT(*) FROM contacts_contact_groups_xref WHERE (contact_id = '" . USER_CONTACT_ID . "') AND (contact_group_id = '$return_alternative_page_contact_group_id')") > 0)
            ) {
                $return_page_id = $return_alternative_page_id;
            }

            $page = db_item("SELECT page_name AS name, page_type AS type FROM page WHERE page_id = '$return_page_id'");
            
            $submitted_form_reference_code = '';
            
            // if the page type is a form item view, then pass the submitted form's reference code to the form item view
            if ($page['type'] == 'form item view') {
                $reference_code = db_value("SELECT reference_code FROM forms WHERE (page_id = '$current_page_id') AND (user_id = '" . USER_ID . "') ORDER BY submitted_timestamp DESC");

                $submitted_form_reference_code = '?r=' . urlencode($reference_code);
            }
            
            // Send user to return page.
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path($page['name']) . $submitted_form_reference_code);
            exit();

        // Otherwise the return type is "custom_form" or the user is not logged or the user has not submitted this form in the past,
        // or the visitor came from the control panel, so determine if custom form should be shown.
        } else {
            // if the custom form grants trial membership access,
            // and the user is logged in,
            // then check if user is allowed to submit membership trial form
            if (($membership == 1) && ($membership_days > 0) && (isset($_SESSION['sessionusername']) == true) && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)) {
                global $user;
                
                // get information about the user
                $user = validate_user();
                
                // get folder id that custom form is in, in order to check if user has edit access to custom form
                $query = "SELECT page_folder FROM page WHERE page_id = '$current_page_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $folder_id = $row['page_folder'];
                
                // if the user does not have edit rights to this folder,
                // then we need to check if the membership trial custom form should be displayed to the user
                if (check_edit_access($folder_id) == false) {
                    // get member id for user, if one exists
                    $query =
                        "SELECT contacts.member_id
                        FROM user
                        LEFT JOIN contacts ON user.user_contact = contacts.id
                        WHERE user.user_id = '" . $user['id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $member_id = $row['member_id'];
                    
                    // if the user has a member id, then don't allow the user to view the membership trial custom form, so output error
                    if ($member_id != '') {
                        output_error('You may not submit a membership trial form, because you are a member or have been a member.');
                    }
                }
            }
            
            
            $form = new liveform($current_page_id);
            
            // if form is enabled
            if ($enabled) {

                if ($layout_type == 'system') {

                    $output_connect_to_contact_field = '';
                    
                    // if there is a connect to contact value then pass it through the form, and output a notice if needed
                    if (isset($_GET['connect_to_contact']) == TRUE) {
                        $output_connect_to_contact_field = '<input type="hidden" name="connect_to_contact" value="' . h($_GET['connect_to_contact']) . '" />';
                        
                        // if connect to contact is off then output notice
                        if (trim(mb_strtolower($_GET['connect_to_contact'] == 'false'))) {
                            $form->add_notice('The connect to contact feature has been disabled for this visit to this form, so your contact information will not be prefilled and will not be updated.');
                        }
                    }

                    $output_hidden_watcher_fields = '';

                    // If an add watcher value was passed in the query string, then output hidden add watcher fields.
                    // Someone can pass a username or email address in the query string, which allows that
                    // watcher to be emailed, like the submitter is emailed, and also added as a watcher to a form item view page.
                    if (isset($_GET['add_watcher']) == true) {
                        $output_hidden_add_watcher_fields =
                            $form->output_field(array('type' => 'hidden', 'name' => 'add_watcher', 'value' => $_GET['add_watcher'])) . '
                            ' . $form->output_field(array('type' => 'hidden', 'name' => 'add_watcher_page_id', 'value' => $_GET['add_watcher_page_id']));
                    }
                    
                    // assume that we will not show office use only fields until we find out otherwise
                    $office_use_only = false;
                    $office_use_only_hidden_field_value = 'false';
                    
                    // if this page is editable, show office use only fields
                    if ($editable == true) {
                        $office_use_only = true;
                        $office_use_only_hidden_field_value = 'true';
                    }
                    
                    // get form info (form content, wysiwyg fields, file upload exists)
                    $form_info = get_form_info($current_page_id, 0, 0, 0, $label_column_width, $office_use_only, $form, 'frontend', $editable, $device_type, $folder_id_for_default_value);
                    
                    // assume that we don't need to output wywiwyg javascript until we find out otherwise
                    $output_wysiwyg_javascript = '';
                    
                    // if there is at least one wysiwyg field, prepare wysiwyg fields
                    if ($form_info['wysiwyg_fields']) {
                        $output_wysiwyg_javascript = get_wysiwyg_editor_code($form_info['wysiwyg_fields']);
                    }
                    
                    // assume that we don't need to set enctype for HTML form until we find out otherwise
                    $enctype = '';
                    
                    // if a file upload field exists in the form, then prepare to set enctype for HTML form
                    if ($form_info['file_upload_exists'] == true) {
                        $enctype = ' enctype="multipart/form-data"';
                    }
                    
                    $output_watcher_check_box = '';
                    
                    // If a watcher page was selected and the visitor is logged in,
                    // and the watcher page still exists and is valid for watching,
                    // and this user is not the moderator (the moderator is already notified)
                    // then output the watcher check box.
                    if (
                        ($watcher_page_id != 0)
                        && (isset($_SESSION['sessionusername']) == TRUE)
                        && ($watcher_page = db_item(
                            "SELECT
                                page_id AS id,
                                comments_label,
                                comments_administrator_email_to_email_address,
                                comments_submitter_email_page_id
                            FROM page
                            WHERE
                                (page_id = '$watcher_page_id')
                                AND (comments = '1')
                                AND (comments_watcher_email_page_id != '0')"))
                        && (mb_strpos(mb_strtolower($watcher_page['comments_administrator_email_to_email_address']), mb_strtolower(USER_EMAIL_ADDRESS)) === FALSE)
                        && (!$watcher_page['comments_submitter_email_page_id'])
                    ) {
                        // If the form has not been submitted yet,
                        // then check watcher check box by default.
                        if (!$form->field_in_session('page_id')) {
                            $form->assign_field_value('watcher', '1');
                        }

                        $output_watcher_check_box =
                            '<tr class="add_watcher_row">
                                <td>&nbsp;</td>
                                <td style="padding-top: 1em; padding-bottom: 1em">' .
                                    $form->output_field(array(
                                        'type' => 'checkbox',
                                        'name' => 'watcher',
                                        'id' => 'watcher',
                                        'value' => '1',
                                        'class' => 'software_input_checkbox')) .
                                    '<label for="watcher"> Notify me when a ' .
                                    h(mb_strtolower(get_comment_label(array('label' => $watcher_page['comments_label'])))) .
                                    ' is added.</label>
                                </td>
                            </tr>';
                    }
                    
                    $output_captcha_fields = '';
                    
                    // if CAPTCHA is enabled then prepare to output CAPTCHA fields
                    if (CAPTCHA == TRUE) {
                        // get captcha fields if there are any
                        $output_captcha_fields = get_captcha_fields($form);
                        
                        // if there are captcha fields to be displayed, then output them in a container
                        if ($output_captcha_fields != '') {
                            $output_captcha_fields =
                                '<tr class="captcha_row">
                                    <td colspan="2" style="padding-top: 1em;">' . $output_captcha_fields . '</td>
                                </tr>';
                        }
                    }

                    // If save-for-later is enabled, then output save button.
                    if ($save) {
                        $output_save_button = '<input type="submit" name="save_button" value="Save for Later" class="software_input_submit_secondary">&nbsp;&nbsp;';
                    }
                    
                    // if a submit button label was entered for the page, then use that
                    if ($submit_button_label) {
                        $output_submit_button_label = h($submit_button_label);
                    
                    // else a submit button label could not be found, so use a default label
                    } else {
                        $output_submit_button_label = 'Submit';
                    }

                    // create unique and valid css class for field
                    $output_form_class = 'f_' . get_class_name($form_name);
                    
                    $output =
                        $output_wysiwyg_javascript .
                        '<form class="' . $output_form_class . '" ' . $enctype . ' action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/custom_form.php" method="post" style="margin: 0px">
                            ' . get_token_field() . '
                            <input type="hidden" name="page_id" value="' . $current_page_id . '" />
                            ' . $form->output_field(array('type'=>'hidden', 'name'=>'send_to', 'value'=>get_request_uri())) . '
                            ' . $form->output_field(array('type'=>'hidden', 'name'=>'office_use_only', 'value'=>$office_use_only_hidden_field_value)) . '
                            ' . $form->output_field(array('type' => 'hidden', 'name' => 'folder_id', 'value' => $folder_id_for_default_value)) . '
                            ' . $output_connect_to_contact_field . '
                            ' . $output_hidden_add_watcher_fields . '
                            ' . $form->output_errors() . '
                            ' . $form->output_notices() . '
                            <table style="margin-bottom: 15px">
                                ' . $form_info['content'] . '
                                ' . $output_captcha_fields . '
                                ' . $output_watcher_check_box . '
                                <tr class="submit_button_row">
                                    <td colspan="2" style="padding-top: 1em">

                                        ' . $output_save_button . '

                                        <input type="submit" name="submit_button" value="' . $output_submit_button_label . '" class="software_input_submit_primary submit_button">

                                    </td>
                                </tr>
                            </table>
                        </form>';

                // Otherwise the layout is custom.
                } else {

                    // If this page is in edit mode, then remember that office use only
                    // fields should be shown.
                    if ($editable) {
                        $office_use_only = true;

                    } else {
                        $office_use_only = false;
                    }

                    $sql_where_office_use_only = "";

                    // If office use only fields are not going to be shown,
                    // then prepare SQL filter for non-office-use-only fields.
                    if (!$office_use_only) {
                        $sql_where_office_use_only = " AND (office_use_only = 0)";
                    }

                    $fields = db_items(
                        "SELECT
                            id,
                            name,
                            label,
                            type,
                            contact_field,
                            default_value,
                            use_folder_name_for_default_value,
                            multiple,
                            required,
                            size,
                            maxlength,
                            wysiwyg,
                            `rows`, # Backticks for reserved word.
                            cols,
                            information
                        FROM form_fields
                        WHERE
                            (page_id = '" . e($current_page_id) . "')
                            $sql_where_office_use_only
                        ORDER BY sort_order",
                        'id');

                    $file_upload = false;

                    // If the form has not been submitted yet, then remember to set default values.
                    if (!$form->field_in_session('page_id')) {
                        $set_default_values = true;
                    } else {
                        $set_default_values = false;
                    }

                    if (trim(mb_strtolower($_GET['connect_to_contact'])) == 'false') {
                        $connect_to_contact = false;
                    } else {
                        $connect_to_contact = true;
                    }

                    $contact = array();
                    
                    // If connect to contact is enabled, and user is logged in,
                    // then get contact info, because fields might need to be prefilled.
                    if ($connect_to_contact && USER_LOGGED_IN) {
                        $contact = db_item(
                            "SELECT contacts.*
                            FROM user
                            LEFT JOIN contacts ON user.user_contact = contacts.id
                            WHERE user.user_id = '" . e(USER_ID) . "'");
                    }

                    $office_use_only_target_options = array();

                    // If office use only fields will not appear on form, then get triggers for them,
                    // so we can limit the options that appear in target pick lists.
                    if ($office_use_only == false) {

                        $office_use_only_fields = db_items(
                            "SELECT
                                id,
                                contact_field,
                                default_value,
                                use_folder_name_for_default_value
                            FROM form_fields
                            WHERE
                                (page_id = '" . e($current_page_id) . "')
                                AND (type = 'pick list')
                                AND (office_use_only = '1')");

                        // Loop through the office use only fields in order to check if we need to apply triggers.
                        foreach ($office_use_only_fields as $office_use_only_field) {
                            // Get default value for office use only field so we can find if there is a trigger for this value.
                            $default_value = '';
                                
                            // If field is set to use folder name for default value, then use it.
                            if ($office_use_only_field['use_folder_name_for_default_value'] == 1) {
                                $default_value = db_value("SELECT folder_name FROM folder WHERE folder_id = '" . e($folder_id_for_default_value) . "'");

                            // Otherwise use default value from field.
                            } else {
                                $default_value = $office_use_only_field['default_value'];
                            }

                            // Check if there is an option for this default value that has a trigger.
                            $option = db_item(
                                "SELECT
                                    id,
                                    target_form_field_id
                                FROM form_field_options
                                WHERE
                                    (form_field_id = '" . $office_use_only_field['id'] . "')
                                    AND (value = '$default_value')
                                    AND (target_form_field_id != '0')");

                            // If an option with a trigger for the default value was found, then add target options to array.
                            if ($option != '') {
                                $target_options = db_items("SELECT value FROM target_options WHERE trigger_option_id = '" . $option['id'] . "'");

                                $office_use_only_target_options[$option['target_form_field_id']] = array();

                                // Loop through target options in order to add them to array.
                                foreach ($target_options as $target_option) {
                                    $office_use_only_target_options[$option['target_form_field_id']][] = mb_strtolower($target_option['value']);
                                }
                            }
                        }
                    }

                    $wysiwyg_fields = array();
                    $date_fields = array();
                    $date_and_time_fields = array();

                    foreach ($fields as $field) {
                        $attributes = $form->get_field($field['id']);

                        // If this is a check box group or pick list,
                        // then get options for the field, because we will need them below.
                        if (
                            ($field['type'] == 'check box')
                            || ($field['type'] == 'pick list')
                        ) {
                            $options = db_items(
                                "SELECT
                                    label,
                                    value,
                                    default_selected
                                FROM form_field_options
                                WHERE form_field_id = '" . $field['id'] . "'
                                ORDER BY sort_order");
                        }

                        if ($set_default_values) {
                            $value_from_query_string = trim($_GET['value_' . $field['id']]);

                            // If a default value was passed in the query string, then use that.
                            if ($value_from_query_string != '') {
                                $attributes['value'] = $value_from_query_string;

                            // Otherwise if edit mode is off and a contact was found for user
                            // and this field is connected to a contact field,
                            // then set the default value to the contact field's value.
                            } else if (!$editable && $contact && $field['contact_field']) {
                                $attributes['value'] = $contact[$field['contact_field']];
                                
                            // Otherwise, if field is set to use folder name for default value,
                            // then use it.
                            } else if ($field['use_folder_name_for_default_value']) {
                                $default_value = db_value(
                                    "SELECT folder_name
                                    FROM folder
                                    WHERE folder_id = '" . e($folder_id_for_default_value) . "'");

                                $attributes['value'] = $default_value;

                            // Otherwise if there is a default value, then use that.
                            } else if ($field['default_value'] != '') {
                                $attributes['value'] = $field['default_value'];

                            // Otherwise if this is a check box group or pick list,
                            // then use on/off values (default_selected) from choices.
                            } else if (
                                ($field['type'] == 'check box')
                                || ($field['type'] == 'pick list')
                            ) {
                                $values = array();

                                foreach ($options as $option) {
                                    if ($option['default_selected']) {
                                        $values[] = $option['value'];
                                    }
                                }

                                // If there is at least one default-selected value, then continue to set it.
                                if ($values) {
                                    // If there is only one value, then set it.
                                    if (count($values) == 1) {
                                        $attributes['value'] = $values[0];

                                    // Otherwise, there is more than one value, so set all of them.
                                    } else {
                                        $attributes['value'] = $values;
                                    }
                                }
                            }
                        }

                        switch ($field['type']) {

                            case 'date':
                                $date_fields[] = $field['id'];

                                break;

                            case 'date and time':
                                $date_and_time_fields[] = $field['id'];

                                break;

                            case 'file upload':
                                $file_upload = true;

                                break;

                            case 'pick list':
                                
                                $attributes['options'] = array();
                                
                                foreach ($options as $option) {
                                    // If there are not target options for this field,
                                    // or this option is a target option, then include this option.
                                    // Target options in this case are the only options that a different office use only field require to appear in pick list.
                                    if (
                                        (isset($office_use_only_target_options[$field['id']]) == false)
                                        || (in_array(mb_strtolower($option['value']), $office_use_only_target_options[$field['id']]) == true)
                                    ) {
                                        $attributes['options'][$option['label']] =
                                            array(
                                                'value' => $option['value'],
                                                'default_selected' => $option['default_selected']
                                            );
                                    }
                                }

                                break;

                            case 'text area':
                                // If field is a rich-text editor, then remember that,
                                // so we can prepare JS later.
                                if ($field['wysiwyg']) {
                                    $wysiwyg_fields[] = $field['id'];
                                }

                                if ($field['rows']) {
                                    $attributes['rows'] = $field['rows'];
                                }

                                if ($field['cols']) {
                                    $attributes['cols'] = $field['cols'];
                                }

                                break;

                        }

                        if (
                            $field['size']
                            and
                            (
                                ($field['type'] == 'text box')
                                or ($field['type'] == 'pick list')
                                or ($field['type'] == 'file upload')
                                or ($field['type'] == 'date')
                                or ($field['type'] == 'date and time')
                                or ($field['type'] == 'email address')
                                or ($field['type'] == 'time')
                            )
                        ) {
                            $attributes['size'] = $field['size'];
                        }

                        if ($field['type'] == 'date') {
                            $attributes['maxlength'] = 10;

                        } else if ($field['type'] == 'date and time') {
                            $attributes['maxlength'] = 22;

                        } else if ($field['type'] == 'time') {
                            $attributes['maxlength'] = 11;

                        } else if (
                            $field['maxlength']
                            and
                            (
                                ($field['type'] == 'text box')
                                or ($field['type'] == 'text area')
                                or ($field['type'] == 'file upload')
                                or ($field['type'] == 'email address')
                            )
                        ) {
                            $attributes['maxlength'] = $field['maxlength'];
                        }

                        // If this field is required, and it is not a check box,
                        // or it is a check box and there is just one check box option,
                        // then add required attribute.  We don't add the required attribute,
                        // when there are multiple check box options, because it would require
                        // that all of them be checked.
                        if (
                            $field['required']
                            &&
                            (
                                ($field['type'] != 'check box')
                                || (count($options) == 1)
                            )
                        ) {
                            $attributes['required'] = true;
                        }

                        // If there is at least one attribute to set, then set them.
                        if ($attributes) {
                            $form->set_field($field['id'], $attributes);
                        }
                    }

                    $attributes =
                        'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/custom_form.php" ' .
                        'method="post"';

                    if ($file_upload) {
                        $attributes .= ' enctype="multipart/form-data"';
                    }

                    $system =
                        get_token_field() . '

                        <input type="hidden" name="page_id" value="' . h($current_page_id) . '">
                        <input type="hidden" name="send_to" value="' . h(REQUEST_URL) . '">';

                    if (!$connect_to_contact) {
                        $system .= '<input type="hidden" name="connect_to_contact" value="false">';

                        $form->add_notice('The connect to contact feature has been disabled for this visit to this form, so your contact information will not be prefilled and will not be updated.');
                    }

                    // If an add watcher value was passed in the query string,
                    // then output hidden add watcher fields. Someone can pass
                    // a username or email address in the query string, which
                    // allows that watcher to be emailed, like the submitter
                    // is emailed, and also added as a watcher to a form item view page.
                    if (isset($_GET['add_watcher'])) {
                        $system .=
                            '<input type="hidden" name="add_watcher" value="' . h($_GET['add_watcher']) . '">
                            <input type="hidden" name="add_watcher_page_id" value="' . h($_GET['add_watcher_page_id']) . '">';
                    }
                    
                    // If office-use-only fields can appear on this form,
                    // then output hidden field for that.
                    if ($office_use_only) {
                        $system .= '<input type="hidden" name="office_use_only" value="true">';
                    }

                    // If a folder id was passed, then add hidden field,
                    // so once the form is submitted, we will know what the folder was,
                    // so that office use only fields that did not appear on the form
                    // can be set to the folder, if the fields are set to do that.
                    if ($folder_id_for_default_value) {
                        $system .= '<input type="hidden" name="folder_id" value="' . h($folder_id_for_default_value) . '">';
                    }

                    // If there is at least one rich-text editor field,
                    // then output JS for them.
                    if ($wysiwyg_fields) {
                        $system .= get_wysiwyg_editor_code($wysiwyg_fields);
                    }

                    // If there is a date or date and time field, then prepare system content,
                    // for the date/time picker.
                    if ($date_fields || $date_and_time_fields) {
                        $system .= get_date_picker_format();

                        foreach ($date_fields as $date_field) {
                            $system .=
                                '<script>
                                    software_$("#' . escape_javascript($date_field) . '").datepicker({
                                        dateFormat: date_picker_format
                                    });
                                </script>';
                        }

                        // If there is a date and time field, then prepare JS for those.
                        if ($date_and_time_fields) {
                            // Include JS file for timepicker.
                            $system .=
                                '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>';

                            foreach ($date_and_time_fields as $date_and_time_field) {
                                $system .=
                                    '<script>
                                        software_$("#' . escape_javascript($date_and_time_field) . '").datetimepicker({
                                            dateFormat: date_picker_format,
                                            timeFormat: "h:mm TT"
                                        });
                                    </script>';
                            }
                        }
                    }
                    
                    // If a watcher page was selected and the visitor is logged in,
                    // and the watcher page still exists and is valid for watching,
                    // and this user is not the moderator (the moderator is already notified)
                    // then output the watcher check box.
                    if (
                        ($watcher_page_id)
                        && (USER_LOGGED_IN)
                        && ($watcher_page = db_item(
                            "SELECT
                                page_id AS id,
                                comments_label,
                                comments_administrator_email_to_email_address,
                                comments_submitter_email_page_id
                            FROM page
                            WHERE
                                (page_id = '$watcher_page_id')
                                AND (comments = '1')
                                AND (comments_watcher_email_page_id != '0')"))
                        && (mb_strpos(mb_strtolower($watcher_page['comments_administrator_email_to_email_address']), mb_strtolower(USER_EMAIL_ADDRESS)) === FALSE)
                        && (!$watcher_page['comments_submitter_email_page_id'])
                    ) {
                        $watcher_option = true;

                        $comment_label = get_comment_label(array(
                            'label' => $watcher_page['comments_label']));

                    } else {
                        $watcher_option = false;
                        $comment_label = '';
                    }

                    $captcha_info = get_captcha_info($form);

                    $system .= $captcha_info['system'];

                    settype($save, 'bool');

                    // If a submit button label was not entered for the page, then set default label.
                    if ($submit_button_label == '') {
                        $submit_button_label = 'Submit';
                    }
                    
                    $output = render_layout(array(
                        'page_id' => $current_page_id,
                        'form' => $form,
                        'field' => $fields,
                        'attributes' => $attributes,
                        'system' => $system,
                        'messages' => $form->get_messages(),
                        'office_use_only' => $office_use_only,
                        'watcher_option' => $watcher_option,
                        'comment_label' => $comment_label,
                        'captcha_question' => $captcha_info['question'],
                        'save' => $save,
                        'submit_button_label' => $submit_button_label));

                    $output = $form->prepare($output);

                }
            
            // else form is disabled
            } else {
                $output = '&nbsp;';
            }
            
            if ($editable == true) {
                $output_title = 'Custom Form';
                
                // if the form name is not blank, then add it to the title
                if ($form_name != '') {
                    $output_title .= ': ' . h($form_name);
                }
                
                $output =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #805FA7; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $current_page_id . '&from=pages&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #805FA7; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $output_title . '">Edit</a>
                        ' . $output . '
                    </div>';
            }

            $form->remove_form();
        }
    }
    
    return $output;

}
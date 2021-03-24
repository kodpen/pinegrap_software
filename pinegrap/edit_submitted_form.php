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

// Get info about submitted form.
$query =
    "SELECT 
        page.page_folder as folder_id,
        forms.user_id,
        forms.page_id,
        forms.form_editor_user_id,
        custom_form_pages.form_name,
        forms.reference_code,
        forms.complete,
        forms.address_name,
        forms.contact_id
    FROM forms
    LEFT JOIN page on forms.page_id = page.page_id
    LEFT JOIN custom_form_pages ON forms.page_id = custom_form_pages.page_id
    WHERE forms.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$folder_id = $row['folder_id'];
$custom_form_page_id = $row['page_id'];
$submitter_id = $row['user_id'];
$form_editor_user_id = $row['form_editor_user_id'];
$form_name = $row['form_name'];
$reference_code = $row['reference_code'];
$complete = $row['complete'];
$old_address_name = $row['address_name'];
$contact_id = $row['contact_id'];

// If there is a form_item_view_page_id submitted in the post,
// then get properties for that page.  We will use these properties
// in several places further below.
if ($_POST['form_item_view_page_id']) {
    $query =
        "SELECT 
            custom_form_page_id,
            submitted_form_editable_by_registered_user,
            submitted_form_editable_by_submitter,
            hook_code
        FROM form_item_view_pages
        WHERE
            (page_id = '" . escape($_POST['form_item_view_page_id']) . "')
            AND (collection = 'a')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $form_item_view_custom_form_page_id = $row['custom_form_page_id'];
    $submitted_form_editable_by_registered_user = $row['submitted_form_editable_by_registered_user'];
    $submitted_form_editable_by_submitter = $row['submitted_form_editable_by_submitter'];
    $hook_code = $row['hook_code'];
}

// if user is a user level and does not have access to manage forms or the user does not have access to edit the folder the form is in
if (
    ($user['role'] > 2)
    && (($user['manage_forms'] == false) || (check_edit_access($folder_id) == false))
) {
    // remember that the user is not a submitted forms manager for this form (we will use this later)
    $submitted_forms_manager = FALSE;
    
    // If there is a form_item_view_page_id submitted in the post
    if ($_POST['form_item_view_page_id']) {
        // If the form_item_view page does not belong to the custom form being submitted, output error
        if ($form_item_view_custom_form_page_id != $custom_form_page_id) {
            log_activity("access denied to edit form submission", $_SESSION['sessionusername']);
            output_error('Access denied. The submitted form item view page does not match the custom form id. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // If the form is not editable by just any registered user
        if ($submitted_form_editable_by_registered_user == '0') {
            $edit_access = false;

            // If the user is the submitter and the form is incomplete,
            // or the user is the submitter and the page allows submitter to edit,
            // or if this user is the form editor, then the user has access.
            if (
                (($user['id'] == $submitter_id) and !$complete)
                or (($user['id'] == $submitter_id) and $submitted_form_editable_by_submitter)
                or ($user['id'] == $form_editor_user_id)
            ) {
                $edit_access = true;
            }

            if (!$edit_access) {
                log_activity("access denied to forms", $_SESSION['sessionusername']);
                output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
            }
        }
    // Else, there was not a form_item_view_page_id submitted in the post, so output error
    } else {
        log_activity("access denied to forms", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
// else remember that the user is a submitted forms manager (we will use this later)
} else {
    $submitted_forms_manager = TRUE;
}

$pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);

include_once('liveform.class.php');
$liveform = new liveform('edit_submitted_form', $_REQUEST['id']);

// if form has not been submitted yet
if (!$_POST) {
    // get form information
    $query =
        "SELECT
            custom_form_pages.form_name,
            custom_form_pages.quiz,
            forms.page_id,
            forms.quiz_score,
            forms.reference_code,
            forms.complete,
            forms.tracking_code,
            forms.affiliate_code,
            forms.http_referer,
            INET_NTOA(forms.ip_address) as ip_address,
            form_editor_user.user_username as form_editor_username,
            contacts.member_id,
            submitted_user.user_username as submitted_username,
            forms.submitted_timestamp,
            last_modified_user.user_username as last_modified_username,
            forms.last_modified_timestamp,
            custom_form_pages.label_column_width
        FROM forms
        LEFT JOIN custom_form_pages on forms.page_id = custom_form_pages.page_id
        LEFT JOIN contacts ON forms.contact_id = contacts.id
        LEFT JOIN user as form_editor_user ON forms.form_editor_user_id = form_editor_user.user_id
        LEFT JOIN user as submitted_user ON forms.user_id = submitted_user.user_id
        LEFT JOIN user as last_modified_user ON forms.last_modified_user_id = last_modified_user.user_id
        WHERE forms.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $form_name = $row['form_name'];
    $quiz = $row['quiz'];
    $page_id = $row['page_id'];
    $quiz_score = $row['quiz_score'];
    $reference_code = $row['reference_code'];
    $complete = $row['complete'];
    $tracking_code = $row['tracking_code'];
    $affiliate_code = $row['affiliate_code'];
    $http_referer = $row['http_referer'];
    $ip_address = $row['ip_address'];
    $form_editor_username = $row['form_editor_username'];
    $member_id = $row['member_id'];
    $submitted_username = $row['submitted_username'];
    $submitted_timestamp = $row['submitted_timestamp'];
    $last_modified_username = $row['last_modified_username'];
    $last_modified_timestamp = $row['last_modified_timestamp'];
    $label_column_width = $row['label_column_width'];
    
    $output_quiz_score_row = '';

    // if this is for a quiz custom form, prepare to output quiz score row
    if ($quiz == 1) {
        $output_quiz_score_row =
            '<tr>
                <td>Quiz Score:</td>
                <td>' . $quiz_score . '%</td>
            </tr>';
    }

    if ($complete) {
        $status = 'Complete';
    } else {
        $status = 'Incomplete';
    }

    $output_address_name = '';
    
    // If pretty URLs are enabled, then output address name.
    if ($pretty_urls == true) {
        $output_address_name = h($old_address_name);
    }
    
    if (AFFILIATE_PROGRAM == true) {
        $output_affiliate_code =
            '<tr>
                <td>Affiliate Code:</td>
                <td>' . h($affiliate_code) . '</td>
            </tr>';
    }
    
    // if http referer is greater than 25 characters, then shorten text version
    if (mb_strlen($http_referer) > 25) {
        $http_referer_text = mb_substr($http_referer, 0, 25) . '...';
    } else {
        $http_referer_text = $http_referer;
    }

    // If we don't know the IP address for the submitted form, then set it to empty string.
    if ($ip_address == '0.0.0.0') {
        $output_ip_address = '';

    // Otherwise, we do know the IP address, so output it.
    } else {
        $output_ip_address = $ip_address;
    }
    
    if (!$submitted_username) {
        $submitted_username = '[Unknown]';
    }

    if (!$last_modified_username) {
        $last_modified_username = '[Unknown]';
    }
    
    // if edit submitted form screen has not been submitted already, pre-populate fields with form data
    if (isset($_SESSION['software']['liveforms']['edit_submitted_form'][$_GET['id']]) == false) {
        $query = "SELECT
                    form_data.form_field_id,
                    form_data.data,
                    count(*) as number_of_values,
                    form_fields.type,
                    form_fields.wysiwyg
                 FROM form_data
                 LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                 WHERE
                    (form_data.form_id = '" . escape($_GET['id']) . "')
                    AND (form_fields.type != 'file upload')
                 GROUP BY form_data.form_field_id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $fields = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $fields[] = $row;
        }
        
        // loop through all field data in order to add it to liveform session
        foreach ($fields as $field) {
            // if there is more than one value, get all values
            if ($field['number_of_values'] > 1) {
                $query = "SELECT data
                         FROM form_data
                         WHERE (form_id = '" . escape($_GET['id']) . "') AND (form_field_id = '" . $field['form_field_id'] . "')
                         ORDER BY id";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $field['data'] = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $field['data'][] = $row['data'];
                }
            }
            
            // if this field is a rich-text editor field, then prepare content for output before we set field data
            if (
                ($field['type'] == 'text area')
                && ($field['wysiwyg'] == 1)
            ) {
                $field['data'] = prepare_rich_text_editor_content_for_output($field['data']);
            }
            
            $liveform->assign_field_value($field['form_field_id'], prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false));
        }
    }
    
    // get all fields for this form
    $query = "SELECT
                id,
                label,
                type,
                required,
                information,
                default_value,
                size,
                maxlength,
                wysiwyg,
                `rows`, # Backticks for reserved word.
                cols,
                multiple,
                spacing_above,
                spacing_below,
                contact_field,
                office_use_only
             FROM form_fields
             WHERE page_id = '$page_id'
             ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $fields = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $fields[] = $row;
    }
    
    $wysiwyg_fields = array();

    // Prepare to keep track if whether there are file upload fields or not,
    // in order to know later if we need to output enctype for form.
    $file_upload_exists = false;
    
    foreach ($fields as $field) {
        // If this is not an office use only field, or the user has access to edit office use only fields from this screen, then output the field
        if (
            ($field['office_use_only'] == 0)
            || ($user['role'] < 3)
            || ((check_edit_access($folder_id) == true) && ($user['manage_forms'] == TRUE))
        ) {
            // if field is for office use only, then prepare to apply office use only style to row
            if ($field['office_use_only'] == 1) {
                $row_class = ' class="office_use_only"';
                
            // else field is not for office use only, so don't prepare any special styling
            } else {
                $row_class = '';
            }
            
            if ($field['size'] == 0) {
                $field['size'] = '';
            }

            if ($field['maxlength'] == 0) {
                $field['maxlength'] = '';
            }

            if ($field['rows'] == 0) {
                $field['rows'] = '';
            }

            if ($field['cols'] == 0) {
                $field['cols'] = '';
            }
            
            if ($field['label'] && $field['required']) {
                $field['label'] .= '*';
            }
            
            // if field has options, get options
            if (($field['type'] == 'pick list') || ($field['type'] == 'radio button') || ($field['type'] == 'check box')) {
                $query = "SELECT
                            id,
                            label,
                            value,
                            default_selected
                         FROM form_field_options
                         WHERE form_field_id = '" . $field['id'] . "'
                         ORDER BY sort_order";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $options = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $options[] = $row;
                }
            }
            
            // if field should have spacing above, add spacing
            if ($field['spacing_above']) {
                $output_fields .=
                    '<tr' . $row_class . '>
                        <td colspan="2">&nbsp;</td>
                    </tr>';
            }
            
            switch ($field['type']) {
                case 'text box':
                case 'email address':
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'text', 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>$field['maxlength'])) . '</td>
                        </tr>';
                    break;
                    
                case 'text area':
                    $style = '';
                    
                    // if field is wysiwyg
                    if ($field['wysiwyg'] == 1) {
                        // add field to wysiwyg fields array, so that we can prepare JavaScript later
                        $wysiwyg_fields[] = $field['id'];
                        
                        // if rows was not set, then set default rows so that WYSIWYG editor appears correctly
                        if (!$field['rows']) {
                            $field['rows'] = 15;
                        }
                        
                        // if cols was not set, then set default width so that WYSIWYG editor appears correctly
                        if (!$field['cols']) {
                            $style = 'width: 95%';
                        }
                    }
                    
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'textarea', 'name'=>$field['id'], 'id'=>$field['id'], 'value'=>$field['default_value'], 'maxlength'=>$field['maxlength'], 'rows'=>$field['rows'], 'cols'=>$field['cols'], 'style'=>$style)) . '</td>
                        </tr>';
                    break;
                    
                case 'pick list':
                    if ($field['multiple'] == 1) {
                        $name = $field['id'] . '[]';
                        $multiple = 'multiple';
                    } else {
                        $name = $field['id'];
                        $multiple = '';
                    }
                    
                    $pick_list_options = array();
                    
                    foreach ($options as $option) {
                        $pick_list_options[$option['label']] =
                            array(
                                'value' => $option['value'],
                                'default_selected' => $option['default_selected']
                            );
                    }
                    
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'select', 'name'=>$name, 'value'=>$field['default_value'], 'options'=>$pick_list_options, 'size'=>$field['size'], 'multiple'=>$multiple)) . '</td>
                        </tr>';
                    
                    break;
                    
                case 'radio button':
                    $output_options = '';
                    
                    foreach ($options as $option) {
                        // if this radio button should be selected by default, prepare to select by default
                        if ($option['value'] == $field['default_value']) {
                            $checked = 'checked';
                        } else {
                            $checked = '';
                        }
                        
                        $output_options .= $liveform->output_field(array('type'=>'radio', 'name'=>$field['id'], 'id'=>'software_option_' . $option['id'], 'value'=>$option['value'], 'checked'=>$checked, 'class'=>'radio')) . '<label for="software_option_' . $option['id'] . '"> ' . h($option['label']) . '</label><br />';
                    }
                    
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">' . $output_options . '</td>
                        </tr>';
                    
                    break;
                    
                case 'check box':
                    $output_options = '';
                    
                    foreach ($options as $option) {
                        // if there is more than one option for this check box group
                        if (count($options) > 1) {
                            $name = $field['id'] . '[]';
                            
                        // else there is just one option for this check box group
                        } else {
                            $name = $field['id'];
                        }
                        
                        // if this checkbox should be selected by default, prepare to select by default
                        if (($option['default_selected'] == 1) || ($option['value'] == $field['default_value'])) {
                            $checked = 'checked';
                        } else {
                            $checked = '';
                        }
                        
                        $output_options .= $liveform->output_field(array('type'=>'checkbox', 'name'=>$name, 'id'=>'software_option_' . $option['id'], 'value'=>$option['value'], 'checked'=>$checked, 'class'=>'checkbox')) . '<label for="software_option_' . $option['id'] . '"> ' . h($option['label']) . '</label><br />';
                    }
                    
                    // the hidden field is outputted so even if a check box is not checked, a field will be included in the post data
                    // this is important because we use the fields in the post data to determine which fields should be updated
                    
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top"><input type="hidden" name="' . $field['id'] . '" />' . $output_options . '</td>
                        </tr>';
                    
                    break;

                case 'file upload':
                    // Get file name and size for file if a file exists.
                    $file = db_item(
                        "SELECT
                            files.name,
                            files.size
                        FROM form_data
                        LEFT JOIN files on form_data.file_id = files.id
                        WHERE
                            (form_data.form_id = '" . escape($_GET['id']) . "')
                            AND (form_data.form_field_id = '" . $field['id'] . "')");

                    // If there is an existing file for this field, then output info for that.
                    if ($file['name'] != '') {
                        $output_file_info = '<div style="margin-bottom: .7em"><a href="' . OUTPUT_PATH . h(encode_url_path($file['name'])) . '" target="_blank"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_attachment.png" width="16" height="16" alt="attachment" title="" border="0" style="padding-right: .5em; vertical-align: middle" /></a><a href="' . OUTPUT_PATH . h(encode_url_path($file['name'])) . '" target="_blank">' . h($file['name']) . '</a> (' . convert_bytes_to_string($file['size']) . ')</div>';
                        $output_upload_label = 'Replace File: ';

                        // If this field is optional, then output delete option.
                        if ($field['required'] == 0) {
                            $output_delete_option = '&nbsp; &nbsp;<label for="' . $field['id'] . '_delete_file">or Delete File: </label>' . $liveform->output_field(array('type'=>'checkbox', 'name'=> $field['id'] . '_delete_file', 'id'=> $field['id'] . '_delete_file', 'value'=>'1', 'class'=>'checkbox'));
                        } else {
                            $output_delete_option = '';
                        }

                    // Otherwise there is not an existing file for this field, so output info for that.
                    } else {
                        $output_file_info = '';
                        $output_upload_label = '';
                        $output_delete_option = '';
                    }

                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">
                                ' . $output_file_info . '
                                <div>' . $output_upload_label . $liveform->output_field(array('type'=>'file', 'name'=>$field['id'], 'size'=>$field['size'])) . $output_delete_option . '</div>
                            </td>
                        </tr>';

                    $file_upload_exists = true;
                    
                    break;
                    
                case 'date':
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">
                                ' . $liveform->output_field(array('type'=>'text', 'id' => $field['id'], 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'10')) . '
                                ' . get_date_picker_format() . '
                                <script>
                                    $("#' . $field['id'] . '").datepicker({
                                        dateFormat: date_picker_format
                                    });
                                </script>
                            </td>
                        </tr>';
                    break;
                    
                case 'date and time':
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">
                                ' . $liveform->output_field(array('type'=>'text', 'id' => $field['id'], 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'22')) . '
                                <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
                                ' . get_date_picker_format() . '
                                <script>
                                    $("#' . $field['id'] . '").datetimepicker({
                                        dateFormat: date_picker_format,
                                        timeFormat: "h:mm TT"
                                    });
                                </script>
                            </td>
                        </tr>';
                    break;
                    
                case 'information':
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td colspan="2">' . $field['information'] . '</td>
                        </tr>';
                    
                    break;
                    
                case 'time':
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'text', 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'11')) . ' (Format: h:mm AM/PM)</td>
                        </tr>';
                    break;
                    
                default:
                    $output_fields .=
                        '<tr' . $row_class . '>
                            <td style="vertical-align: top">' . $field['label'] . '</td>
                            <td style="vertical-align: top"></td>
                        </tr>';
                    
                    break;
            }
            
            // if field should have spacing below, add spacing
            if ($field['spacing_below']) {
                $output_fields .=
                    '<tr' . $row_class . '>
                        <td colspan="2">&nbsp;</td>
                    </tr>';
            }
        }
    }
    
    // if label column width is not blank, then prepare label column width output
    if ($label_column_width != '') {
        $output_label_column_width = ' style="width: ' . $label_column_width . '%"';
    } else {
        $output_label_column_width = '';
    }
    
    $output_wysiwyg_javascript = '';
    
    // if there is at least one wysiwyg field, prepare wysiwyg fields
    if ($wysiwyg_fields) {
        $output_wysiwyg_javascript = get_wysiwyg_editor_code($wysiwyg_fields);
    }

    // Assume that we don't need to set enctype for HTML form until we find out otherwise.
    $output_enctype = '';

    // If a file upload field exists in the form, then prepare to set enctype for HTML form
    if ($file_upload_exists == true) {
        $output_enctype = ' enctype="multipart/form-data"';
    }

    // If the form is incomplete then show certain buttons.
    if (!$complete) {
        $output_buttons =
            '<input type="submit" name="save_for_later_button" value="Save for Later" class="submit-secondary">&nbsp;&nbsp;

            <input type="submit" name="complete_button" value="Complete" class="submit-primary">&nbsp;&nbsp;';

    // Otherwise the form is complete, so show different buttons.
    } else {
        $output_buttons =
            '<input type="submit" name="save_button" value="Save" class="submit-primary">&nbsp;&nbsp;

            <input type="submit" name="incomplete_button" value="Incomplete" class="submit-secondary">&nbsp;&nbsp;';
    }
    
echo
    output_header() . '
    <div id="subnav">
        <h1>' . h($form_name) . '</h1>
        <div class="subheading">Reference Code: ' . $reference_code . '   |  Submitted ' . get_relative_time(array('timestamp' => $submitted_timestamp)) . ' by ' . h($submitted_username) . '</div>
    </div>
    <div id="content">
        
        <a href="#" id="help_link">Help</a>
        <h1>Edit Submitted Form</h1>
        <div class="subheading">View or update this submitted form. Office use only fields are also visible.</div>
        ' . $output_wysiwyg_javascript . '
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <form' . $output_enctype . ' action="edit_submitted_form.php" method="post" style="margin: 0px">
            ' . get_token_field() . '
            <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
            <table class="field">
                <tr>
                    <th colspan="2"><h2>Submitted Form Properties</h2></th>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0">
                        <table>
                            <tr>
                                <td style="padding: 0 5em 0 0; vertical-align: top">
                                    <table>
                                        <tr>
                                            <td>Custom Form:</td>
                                            <td>' . h($form_name) . '</td>
                                        </tr>
                                        ' . $output_quiz_score_row . '
                                        <tr>
                                            <td>Reference Code:</td>
                                            <td>' . $reference_code . '</td>
                                        </tr>
                                        <tr>
                                            <td>Status:</td>
                                            <td>' . $status . '</td>
                                        </tr>
                                        <tr>
                                            <td>Address Name:</td>
                                            <td>' . $output_address_name . '</td>
                                        </tr>
                                        <tr>
                                            <td>Tracking Code:</td>
                                            <td>' . h($tracking_code) . '</td>
                                        </tr>
                                        ' . $output_affiliate_code . '
                                        <tr>
                                            <td>Referring URL:</td>
                                            <td><a href="' . h(escape_url($http_referer)) . '" target="_blank">' . h($http_referer_text) . '</a></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="padding: 0; vertical-align: top">
                                    <table>
                                        <tr>
                                            <td>' . h(MEMBER_ID_LABEL) . ':</td>
                                            <td>' . h($member_id) . '</td>
                                        </tr>
                                        <tr>
                                            <td>IP Address:</td>
                                            <td>' . $output_ip_address . '</td>
                                        </tr>
                                        <tr>
                                            <td>Submitted Date:</td>
                                            <td>' . get_absolute_time(array('timestamp' => $submitted_timestamp)) . ' by ' . h($submitted_username) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Last Modified Date:</td>
                                            <td>' . get_absolute_time(array('timestamp' => $last_modified_timestamp)) . ' by ' . h($last_modified_username) . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form Editor:</td>
                                            <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'form_editor_username', 'value'=>$form_editor_username, 'size'=>'40', 'maxlength'=>'100')) . ' (enter username)</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Submitted Form Data</h2></th>
                </tr>
                ' . $output_fields . '
                <tr>
                    <td' . $output_label_column_width . '>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
            <div class="buttons">

                ' . $output_buttons . '

                <input type="button" name="cancel_button" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;

                <input type="submit" name="delete_button" value="Delete" class="delete" onclick="return confirm(\'WARNING: This submitted form will be permanently deleted.\')">

            </div>
        </form>
    </div>
    ' . output_footer();
    
    $liveform->remove_form();
    
// else form has been submitted
} else {
    validate_token_field();
    
    // if form was selected for deletion
    if (isset($_POST['delete_button'])) {
        // assume that the user does not have delete access until we find out otherwise
        $delete_access = FALSE;
        
        // if the user is greater than a user role,
        // or if user has access to manage forms and can edit the folder the form is in,
        // or if the user is deleting this from a form item view page and submitted forms are editable by a registered user from the form item view page,
        // or if the user is deleting this from a form item view page and the user is the submitter and the form item view page allows users to edit their submissions,
        // then the user has access to edit and delete submitted form
        // the only type of user that can edit but cannot delete are form editors
        if (
            ($user['role'] < 3)
            || ((check_edit_access($folder_id) == true) && ($user['manage_forms'] == true))
            || (($_POST['form_item_view_page_id']) && ($submitted_form_editable_by_registered_user == '1'))
            || (($_POST['form_item_view_page_id']) && ($user['id'] == $submitter_id) && ($submitted_form_editable_by_submitter == '1'))
        ) {
            $delete_access = TRUE;
        }
        
        // if the user does not have delete access (e.g. form editor), then log activity and output error
        if ($delete_access == FALSE) {
            log_activity("access denied to delete submitted form", $_SESSION['sessionusername']);
            output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // get uploaded files for this form, so they can be deleted
        $query = "SELECT
                    files.id,
                    files.name
                 FROM form_data
                 LEFT JOIN files ON form_data.file_id = files.id
                 WHERE (form_data.form_id = '" . escape($_POST['id']) . "') AND (form_data.file_id > 0)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $files = array();
        
        while($row = mysqli_fetch_assoc($result)) {
            $files[] = $row;
        }
        
        // loop through all files so they can be deleted
        foreach ($files as $file) {
            // if file still exists, delete file
            if ($file['id']) {
                // delete file record
                $query = "DELETE FROM files WHERE id = '" . $file['id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete file
                @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                log_activity("file (" . $file['name'] . ") was deleted because the submitted form (form name: $form_name, reference code: $reference_code) for the file was deleted", $_SESSION['sessionusername']);
            }
        }
        
        // delete form
        $query = "DELETE FROM forms WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete form data
        $query = "DELETE FROM form_data WHERE (form_id = '" . escape($_POST['id']) . "') AND (form_id != '0')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete views for this submitted form that the form view directory feature uses
        $query = "DELETE FROM submitted_form_views WHERE submitted_form_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity("submitted form (form name: $form_name, reference code: $reference_code) was deleted", $_SESSION['sessionusername']);
        
        // if the there is a form list view send to, then forward user there
        if ((isset($_POST['form_list_view_send_to']) == TRUE) && ($_POST['form_list_view_send_to'] != '')) {
            // Get the form list view page id for this form item view in order to setup liveform correctly.
            $query = "SELECT page_id FROM form_list_view_pages WHERE form_item_view_page_id = '" . escape($_POST['form_item_view_page_id'])  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $form_list_view_page_id = $row['page_id'];

            // If a form list view page was found, then add notice.
            if ($form_list_view_page_id != '') {
                $liveform_form_list_view = new liveform('form_list_view', $form_list_view_page_id);
                
                // add notice that submitted form has been deleted
                $liveform_form_list_view->add_notice('The submitted form has been deleted.');
            }
            
            header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['form_list_view_send_to']);
            
        // else forward user to view submitted forms in backend
        } else {
            $liveform_view_submitted_forms = new liveform('view_submitted_forms');
            
            // add notice that submitted form has been deleted
            $liveform_view_submitted_forms->add_notice('The submitted form has been deleted.');
            
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_submitted_forms.php');
        }
        
        $liveform->remove_form();
        
    // else form was not selected for deletion, so form is being edited
    } else {
        $liveform->add_fields_to_session();
        
        $sql_form_editor = "";
        
        // if the user is a submitted forms manager for this submitted form
        // (i.e. manager or above or has access to manage forms and has edit access to the folder for the custom form)
        // and the form editor field appeared on the form
        // then prepare SQL to update form editor
        if (
            ($submitted_forms_manager == TRUE)
            && ($liveform->field_in_session('form_editor_username') == TRUE)
        ) {
            // if a username was entered, then validate username
            if ($liveform->get_field_value('form_editor_username') != '') {
                // try to find a user with the username that was entered for the form editor
                $query = "SELECT user_id FROM user WHERE user_username = '" . escape($liveform->get_field_value('form_editor_username')) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if a user was found, then prepare SQL for updating form editor
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $sql_form_editor = "form_editor_user_id = '" . $row['user_id'] . "',";
                    
                // else a user was not found, so prepare error
                } else {
                    $liveform->mark_error('form_editor_username', 'Please enter a valid username for the form editor.');
                }
                
            // else a username was not entered, so prepare to set an empty form editor
            } else {
                $sql_form_editor = "form_editor_user_id = '0',";
            }
        }
        
        // get page id
        $query = "SELECT page_id
                 FROM forms
                 WHERE forms.id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);

        $page_id = $row['page_id'];
        
        // get all fields for this form
        $fields = db_items(
            "SELECT
                id,
                name,
                rss_field,
                label,
                type,
                wysiwyg,
                contact_field,
                required,
                office_use_only,
                upload_folder_id
             FROM form_fields
             WHERE
                (page_id = '$page_id')
                AND (type != 'information')
             ORDER BY sort_order");

        // Loop through fields in order to validate them.
        foreach ($fields as $field) {
            // If field is required and the visitor clicked complete button
            // (for incomplete form) or save button (for complete form)
            // then determine if field appeared on form and should be required.
            if (
                $field['required']
                and
                (
                    $liveform->field_in_session('complete_button')
                    or $liveform->field_in_session('save_button')
                )
            ) {
                // If field is a file upload type then determine if field should be required, in a certain way.
                if ($field['type'] == 'file upload') {
                    // If field appeared on form and a file was not uploaded as a replacement, then check if there is an existing file.
                    if (
                        (isset($_FILES[$field['id']]) == true)
                        && ($_FILES[$field['id']]['name'] == '')
                    ) {
                        $file_id = db_value(
                            "SELECT files.id
                            FROM form_data
                            LEFT JOIN files on form_data.file_id = files.id
                            WHERE
                                (form_data.form_id = '" . escape($_POST['id']) . "')
                                AND (form_data.form_field_id = '" . $field['id'] . "')");

                        // If there is not an existing file, then add error.
                        if ($file_id == '') {
                            $error_message = '';
                            
                            if ($field['label']) {
                                $error_message = $field['label'] . ' is required.';
                            }
                            
                            $liveform->mark_error($field['id'], $error_message);
                        }
                    }
                    
                // Otherwise field is not a file upload type, so determine if field should be required, in a different way.
                } else {
                    // If field appeared on form, then require field.
                    if (isset($_POST[$field['id']]) == true) {
                        $error_message = '';
                        
                        if ($field['label']) {
                            $error_message = $field['label'] . ' is required.';
                        }
                        
                        $liveform->validate_required_field($field['id'], $error_message);
                    }
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
                if (db_value("SELECT COUNT(*) FROM forms WHERE (page_id = '" . escape($page_id) . "') AND (id != '" . escape($_POST['id']) . "') AND (address_name = '" . escape($address_name) . "')") > 0) {
                    $liveform->mark_error($field['id'], 'Sorry, that ' . $field['label'] . ' is already in use. Can you please enter a different ' . $field['label'] . '?');
                }
            }
        }

        // If hooks are enabled and visitor is editing this form from a form item view page,
        // and there is hook code, then run it.
        if (
            (defined('PHP_REGIONS') and PHP_REGIONS === true)
            && ($_POST['form_item_view_page_id'])
            && ($hook_code != '')
        ) {
            eval(prepare_for_eval($hook_code));
        }
        
        // if an error does not exist
        if ($liveform->check_form_errors() == false) {


            // If an incomplete button was clicked, then mark form as incomplete.
            if (
                $liveform->field_in_session('save_for_later_button')
                or $liveform->field_in_session('incomplete_button')
            ) {
                $new_complete = 0;

            // Otherwise a complete button was clicked, so remember that.
            } else {
                $new_complete = 1;
            }

            // update form
            $query = "UPDATE forms
                     SET
                        complete = '$new_complete',
                        $sql_form_editor
                        last_modified_user_id = '" . $user['id'] . "',
                        last_modified_timestamp = UNIX_TIMESTAMP()
                     WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // Loop through all fields in order to save data for each one.
            foreach ($fields as $field) {
                // if this is not an office use only field or the user has access to edit office use only fields, then update field
                if (
                    ($field['office_use_only'] == 0)
                    || ($user['role'] < 3)
                    || ((check_edit_access($folder_id) == true) && ($user['manage_forms'] == TRUE))
                    || ($user['id'] == $form_editor_user_id)
                ) {
                    // If field is a file upload type then save data in a certain way.
                    if ($field['type'] == 'file upload') {
                        // If this field appeared on the form, then determine if we need to save data for it.
                        if (isset($_FILES[$field['id']]) == true) {
                            // If a new file was uploaded, then deal with that.
                            if ($_FILES[$field['id']]['name'] != '') {
                                // Check if there is an existing file.
                                $file = db_item(
                                    "SELECT
                                        files.id,
                                        files.name
                                    FROM form_data
                                    LEFT JOIN files on form_data.file_id = files.id
                                    WHERE
                                        (form_data.form_id = '" . escape($_POST['id']) . "')
                                        AND (form_data.form_field_id = '" . $field['id'] . "')");

                                // If there is an existing file, then delete it.
                                if ($file['id'] != '') {
                                    // Delete file record in database.
                                    db("DELETE FROM files WHERE id = '" . $file['id'] . "'");
                                    
                                    // Delete file on file system.
                                    @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                                    log_activity('file (' . $file['name'] . ') was deleted because a visitor uploaded a new file to replace it for the submitted form (form name: ' . $form_name . ', reference code: ' . $reference_code . ')', $_SESSION['sessionusername']);
                                }

                                // Delete existing form_data record if one exists.
                                db(
                                    "DELETE FROM form_data
                                    WHERE
                                        (form_id = '" . escape($_POST['id']) . "')
                                        AND (form_id != '0')
                                        AND (form_field_id = '" . $field['id'] . "')");

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

                                $user_id = '';

                                // If the user is logged in, then store user id.
                                if (USER_LOGGED_IN == true) {
                                    $user_id = USER_ID;
                                }

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
                                
                                db(
                                    "INSERT INTO form_data (
                                        form_id,
                                        form_field_id,
                                        file_id,
                                        name)
                                    VALUES (
                                        '" . escape($_POST['id']) . "',
                                        '" . $field['id'] . "',
                                        '" . $file_id . "',
                                        '" . escape($field['name']) . "')");

                            // Otherwise if this field is optional and the existing file was set to be deleted, then delete it.
                            } else if (
                                ($field['required'] == 0)
                                && ($liveform->get_field_value($field['id'] . '_delete_file') == 1)
                            ) {
                                // Check if there is an existing file.
                                $file = db_item(
                                    "SELECT
                                        files.id,
                                        files.name
                                    FROM form_data
                                    LEFT JOIN files on form_data.file_id = files.id
                                    WHERE
                                        (form_data.form_id = '" . escape($_POST['id']) . "')
                                        AND (form_data.form_field_id = '" . $field['id'] . "')");

                                // If there is an existing file, then delete it.
                                if ($file['id'] != '') {
                                    // Delete file record in database.
                                    db("DELETE FROM files WHERE id = '" . $file['id'] . "'");
                                    
                                    // Delete file on file system.
                                    @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                                    log_activity('file (' . $file['name'] . ') was deleted for the submitted form (form name: ' . $form_name . ', reference code: ' . $reference_code . ')', $_SESSION['sessionusername']);
                                }

                                // Delete existing form_data record if one exists.
                                db(
                                    "DELETE FROM form_data
                                    WHERE
                                        (form_id = '" . escape($_POST['id']) . "')
                                        AND (form_id != '0')
                                        AND (form_field_id = '" . $field['id'] . "')");

                                // Insert blank form_data record.  We are not sure if this is necessary,
                                // however, we appear to do this when a submitted form is originally created,
                                // when a file is not uploaded, so we have decided to also do it here.
                                db(
                                    "INSERT INTO form_data (
                                        form_id,
                                        form_field_id,
                                        name)
                                    VALUES (
                                        '" . escape($_POST['id']) . "',
                                        '" . $field['id'] . "',
                                        '" . escape($field['name']) . "')");
                            }
                        }

                    // Otherwise the field is not a file upload type so save data in a different way.
                    } else {
                        // if field appeared on form
                        if (isset($_POST[$field['id']]) == true) {
                            // delete existing values for this field
                            $query = "DELETE FROM form_data
                                     WHERE (form_id = '" . escape($_POST['id']) . "') AND (form_id != '0') AND (form_field_id = '" . $field['id'] . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // assume that the form data type is standard until we find out otherwise
                            $form_data_type = 'standard';
                            
                            // if the form field's type is date, date and time, or time, then set form data type to the form field type
                            if (
                                ($field['type'] == 'date')
                                || ($field['type'] == 'date and time')
                                || ($field['type'] == 'time')
                            ) {
                                $form_data_type = $field['type'];
                                
                            // else if the form field is a wysiwyg text area, then set type to html and prepare data for input
                            } elseif (($field['type'] == 'text area') && ($field['wysiwyg'] == 1)) {
                                $form_data_type = 'html';
                                
                                $liveform->assign_field_value($field['id'], prepare_rich_text_editor_content_for_input($liveform->get_field_value($field['id'])));
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
                                                '" . escape($_POST['id']) . "',
                                                '" . $field['id'] . "',
                                                '" . escape(prepare_form_data_for_input($value, $field['type'])) . "',
                                                '" . escape($field['name']) . "',
                                                '$form_data_type')";
                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                }

                            // else this field does not have multiple values
                            } else {
                                // store form data
                                $query = "INSERT INTO form_data (
                                            form_id,
                                            form_field_id,
                                            data,
                                            name,
                                            type)
                                         VALUES (
                                            '" . escape($_POST['id']) . "',
                                            '" . $field['id'] . "',
                                            '" . escape(prepare_form_data_for_input($liveform->get_field_value($field['id']), $field['type'])) . "',
                                            '" . escape($field['name']) . "',
                                            '$form_data_type')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                    }
                }
            }

            // If pretty URLs are enabled, then update address name.
            if ($pretty_urls == true) {
                $new_address_name = update_submitted_form_address_name($_POST['id']);
            }

            // If the form was incomplete before and has now been completed,
            // then check if custom form actions should be completed (e.g. email alerts).
            if (!$complete and $new_complete) {
                $custom_form = db_item(
                    "SELECT
                        page.page_id,
                        custom_form_pages.enabled,
                        custom_form_pages.save,
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
                        custom_form_pages.administrator_email_page_id
                    FROM page
                    LEFT JOIN custom_form_pages ON page.page_id = custom_form_pages.page_id
                    WHERE
                        (page.page_id = '$custom_form_page_id')
                        AND (page.page_type = 'custom form')");

                // If custom form is enabled and save-for-later is enabled,
                // then complete actions.
                if ($custom_form['enabled'] and $custom_form['save']) {
                    $form_id = $_POST['id'];

                    // If the submitter or admin email is enabled, then get
                    // submitter email address for later.
                    if ($custom_form['submitter_email'] or $custom_form['administrator_email']) {
                        $submitter_email_address = get_submitter_email_address($form_id);
                    }

                    // If submitter email is enabled, and a submitter email address
                    // was found, then determine if there is a recipient to send email to.
                    if ($custom_form['submitter_email'] and $submitter_email_address) {

                        if ($custom_form['submitter_email_from_email_address']) {
                            $from_email_address = $custom_form['submitter_email_from_email_address'];
                        } else {
                            $from_email_address = EMAIL_ADDRESS;
                        }

                        $subject = get_variable_submitted_form_data_for_content($custom_form['page_id'], $form_id, $custom_form['submitter_email_subject'], $prepare_for_html = false);

                        // If the format of the e-mail should be plain text,
                        // then prepare that.
                        if ($custom_form['submitter_email_format'] == 'plain_text') {

                            // Check the body for variable data and replace
                            // any variables with content from the submitted form.
                            $body = get_variable_submitted_form_data_for_content($custom_form['page_id'], $form_id, $custom_form['submitter_email_body'], $prepare_for_html = false);

                        // Otherwise the format of the e-mail should be HTML, so prepare that.
                        } else {

                            require_once(dirname(__FILE__) . '/get_page_content.php');

                            $body = get_page_content($custom_form['submitter_email_page_id'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $form_id));

                        }

                        email(array(
                            'to' => $submitter_email_address,
                            'from_name' => ORGANIZATION_NAME,
                            'from_email_address' => $from_email_address,
                            'subject' => $subject,
                            'format' => $custom_form['submitter_email_format'],
                            'body' => $body
                        ));

                    }

                    // If admin email is enabled, then determine if we should send it.
                    if ($custom_form['administrator_email']) {

                        $administrator_email_addresses = array();

                        if ($custom_form['administrator_email_to_email_address']) {
                            $administrator_email_addresses[] = $custom_form['administrator_email_to_email_address'];
                        }

                        // Get conditional administrators.
                        $conditional_administrators = db_values(
                            "SELECT form_field_options.email_address
                            FROM form_field_options
                            LEFT JOIN form_data ON form_field_options.form_field_id = form_data.form_field_id
                            WHERE
                                (form_field_options.page_id = '" . $custom_form['page_id'] . "')
                                AND (form_data.form_id = '" . e($form_id) . "')
                                AND (form_field_options.email_address != '')
                                AND (form_data.data = form_field_options.value)");

                        // Loop through the conditional administrators in order to add them.
                        foreach ($conditional_administrators as $conditional_administrator) {
                            // We support multiple conditional admin email addresses, separated by comma,
                            // (e.g. ^^example1@example.com,example2@example.com^^), so deal with that.
                            $conditional_email_addresses = explode(',', $conditional_administrator);

                            foreach ($conditional_email_addresses as $conditional_email_address) {
                                $conditional_email_address = trim($conditional_email_address);

                                // If this e-mail address has not already been added, then add it.
                                if (in_array($conditional_email_address, $administrator_email_addresses) == false) {
                                    $administrator_email_addresses[] = $conditional_email_address;
                                }
                            }
                        }

                        // If there is an admin to email, then continue to send email.
                        if (
                            $administrator_email_addresses
                            or $custom_form['administrator_email_bcc_email_address']
                        ) {

                            $subject = get_variable_submitted_form_data_for_content($custom_form['page_id'], $form_id, $custom_form['administrator_email_subject'], $prepare_for_html = false);

                            // If the format of the e-mail should be plain text,
                            // then prepare that.
                            if ($custom_form['administrator_email_format'] == 'plain_text') {

                                // Check the body for variable data and replace
                                // any variables with content from the submitted form.
                                $body = get_variable_submitted_form_data_for_content($custom_form['page_id'], $form_id, $custom_form['administrator_email_body'], $prepare_for_html = false);

                            // Otherwise the format of the e-mail should be HTML, so prepare that.
                            } else {

                                require_once(dirname(__FILE__) . '/get_page_content.php');

                                $body = get_page_content($custom_form['administrator_email_page_id'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true, array('form_id' => $form_id));
                                
                            }

                            email(array(
                                'to' => $administrator_email_addresses,
                                'bcc' => $custom_form['administrator_email_bcc_email_address'],
                                'from_name' => ORGANIZATION_NAME,
                                'from_email_address' => EMAIL_ADDRESS,
                                'reply_to' => $submitter_email_address,
                                'subject' => $subject,
                                'format' => $custom_form['administrator_email_format'],
                                'body' => $body
                            ));

                        }

                    }

                    // Check if there are auto e-mail campaigns that should be
                    // created based on this custom form being submitted.
                    create_auto_email_campaigns(array(
                        'action' => 'custom_form_submitted',
                        'action_item_id' => $custom_form['page_id'],
                        'contact_id' => $contact_id));

                }
            }
            
            log_activity("submitted form (form name: $form_name, reference code: $reference_code) was modified", $_SESSION['sessionusername']);

            // If the save-for-later button was clicked.
            if ($liveform->field_in_session('save_for_later_button')) {
                $message = 'The form has been saved for later.';

            // Otherwise if the complete button was clicked.
            } else if ($liveform->field_in_session('complete_button')) {
                $message = 'The form has been completed.';

            // Otherwise if the save button was clicked.
            } else if ($liveform->field_in_session('save_button')) {
                $message = 'The form has been saved.';

            // Otherwise if the incomplete button was clicked.
            } else if ($liveform->field_in_session('incomplete_button')) {
                $message = 'The form has been saved and marked as incomplete.';
            }
            
            // if there is a send to, then forward user to send to
            if ((isset($_POST['send_to']) == TRUE) && ($_POST['send_to'] != '')) {
                $liveform_form_item_view = new liveform('form_item_view');
                
                // add notice that submitted form has been saved
                $liveform_form_item_view->add_notice($message);

                $send_to = $_POST['send_to'];

                // If the visitor is not at an ugly URL and pretty URLs are enabled,
                // and the address name has changed, then replace old address name in send to,
                // with new address name, so that we don't forward the visitor to an invalid address.
                if (
                    (mb_strpos(mb_strtolower($send_to), 'r=') === false)
                    && ($pretty_urls == true)
                    && ($new_address_name != $old_address_name)
                ) {
                    $send_to_parts = parse_url($send_to);

                    $path_without_address_name = mb_substr($send_to_parts['path'], 0, mb_strrpos($send_to_parts['path'], '/') + 1);

                    $send_to = $path_without_address_name . $new_address_name;

                    if ($send_to_parts['query'] != '') {
                        $send_to .= '?' . $send_to_parts['query'];
                    }
                }
                
                // remove edit submitted form name and value from send to query string, because we don't want to send the user to edit the submitted form
                $send_to = str_replace('&edit_submitted_form=true', '', $send_to);
                $send_to = str_replace('?edit_submitted_form=true', '', $send_to);
                
                header('Location: ' . URL_SCHEME . HOSTNAME . $send_to);
                
            // else forward user to view submitted forms in backend
            } else {
                $liveform_view_submitted_forms = new liveform('view_submitted_forms');
                
                // add notice that submitted form has been saved
                $liveform_view_submitted_forms->add_notice($message);
                
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_submitted_forms.php');
            }
            
            $liveform->remove_form();
            
        // else an error does exist, so forward user back to edit submitted form
        } else {
            // if there is a send to, then forward user to send
            if ((isset($_POST['send_to']) == TRUE) && ($_POST['send_to'] != '')) {
                header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
                
            // else forward user to edit submitted form in backend
            } else {
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_submitted_form.php?id=' . $_POST['id']);
            }
        }
    }
}
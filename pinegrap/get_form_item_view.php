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

function get_form_item_view($properties) {

    $page_id = $properties['page_id'];
    $form_id = $properties['form_id'];
    $editable = $properties['editable'];

    $properties = get_page_type_properties($page_id, 'form item view');

    $custom_form_page_id = $properties['custom_form_page_id'];
    $layout = $properties['layout'];
    $submitter_security = $properties['submitter_security'];
    $submitted_form_editable_by_registered_user = $properties['submitted_form_editable_by_registered_user'];
    $submitted_form_editable_by_submitter = $properties['submitted_form_editable_by_submitter'];

    // If the style is set to collection b, then get page type properties for that collection.
    if (COLLECTION == 'b') {

        $properties = get_page_type_properties($page_id, 'form item view', 'b');

        // We only currently support collections for the layout field,
        // so that is why we only override that property for collection B.
        $layout = $properties['layout'];
        
    }

    $layout_type = get_layout_type($page_id);

    // If we don't know which submitted form to show and user has edit access to page, then show
    // notice.
    if (
        !$form_id and !$_GET['r']
        and check_edit_access(db("SELECT page_folder FROM page WHERE page_id = '" . e($page_id) . "'"))
    ) {

        $output =
            '<p class="software_notice">This page will only show data if a reference code is passed to it via the link to this page (typically from a Form List View page).</p>' .
            $layout;

    // else the user did not come from the control panel, so prepare output differently
    } else {

        $sql_field_selects = '';
        
        // get standard fields
        $standard_fields = get_standard_fields_for_view();
        
        // loop through all standard fields in order to prepare filters
        foreach ($standard_fields as $standard_field) {
            if ($sql_field_selects) {
                $separator = ",\n";
            } else {
                $separator = '';
            }
            
            $sql_field_selects .= $separator . $standard_field['sql_name'] . " as " . $standard_field['value'];
        }
        
        $where = '';
        
        // if there is a form id (e.g. the form item view is being used as a confirmation after a custom form has been submitted), then use that to get the submitted form
        if ($form_id != '') {
            $where = "forms.id = '" . e($form_id) . "'";
        
        // else get the submitted form by using the reference code
        } else {
            $where = "forms.reference_code = '" . e($_GET['r']) . "'";
        }
        
        // get submitted form
        $query =
        "SELECT
            forms.id,
            forms.user_id as submitter_id,
            forms.form_editor_user_id,
            submitter.user_badge AS submitter_badge,
            submitter.user_badge_label AS submitter_badge_label,
            last_modifier.user_badge AS last_modifier_badge,
            last_modifier.user_badge_label AS last_modifier_badge_label,
            newest_comment_submitter.user_badge AS newest_comment_submitter_badge,
            newest_comment_submitter.user_badge_label AS newest_comment_submitter_badge_label,
            $sql_field_selects
        FROM forms
        LEFT JOIN user as submitter ON forms.user_id = submitter.user_id
        LEFT JOIN user as last_modifier ON forms.last_modified_user_id = last_modifier.user_id
        LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '$page_id'))
        LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id
        LEFT JOIN user AS newest_comment_submitter ON newest_comment.created_user_id = newest_comment_submitter.user_id
        WHERE
            (forms.page_id = '" . escape($custom_form_page_id) . "')
            AND ($where)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if page is not being edited and a form was not found for reference code, then determine what to do.
        if (($editable == false) && (mysqli_num_rows($result) == 0)) {
            // If a form id was passed into this function, then that means a system is calling this like an API
            // (e.g. confirmation for a custom form, site search indexing), so just return an empty string.
            if ($form_id != '') {
                return '';

            // Otherwise if a pretty URL is being used to access this page, then output error for that.
            } else if (defined('PRETTY_URL_PATH') == true) {
                output_error('The submitted form could not be found. Please check that you have entered the correct address. The address for the submitted form might have changed, or the submitted form might have been deleted. <a href="javascript:history.go(-1)">Go back</a>.', 404);

            // Otherwise this page is being called through an ugly URL, so output error for that.
            } else {
                if ($_GET['r']) {
                    output_error('The submitted form could not be found. The reference code might be invalid or the submitted form might have been deleted. <a href="javascript:history.go(-1)">Go back</a>.', 404);
                } else {
                    output_error('There was no reference code passed to this page, so a submitted form could not be found. You may browse to a submitted form from a form list view page. <a href="javascript:history.go(-1)">Go back</a>.', 404);
                }
            }
        }
        
        $submitted_form = mysqli_fetch_assoc($result);
        
        global $user;
        
        // if the user is logged in, then get user information
        // we will use this in several places below
        if ((isset($_SESSION['sessionusername']) == true) && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)) {
            $user = validate_user();
        }

        // Get folder id that custom form is in, in order to check if user has edit access to custom form.
        // We will use this in several places below.
        $query = "SELECT page_folder FROM page WHERE page_id = '" . escape($custom_form_page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $folder_id = $row['page_folder'];

        // If submitter security is enabled, then determine if visitor has access to view this submitted form.
        if ($submitter_security == 1) {
            // Assume that this visitor does not have view access until we find out otherwise.
            $view_access = FALSE;

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
                    (check_edit_access($folder_id) == true)
                    || (USER_ID == $submitted_form['form_editor_user_id'])
                    || (USER_ID == $submitted_form['submitter_id'])
                ) {
                    $view_access = TRUE;

                // Otherwise this user does not have access through those ways
                // so check if user has access in other ways.
                } else {
                    // Check if this user's e-mail address is the same as the
                    // connect-to-contact e-mail address field value for this submitted form.
                    $query =
                        "SELECT form_data.data
                        FROM form_data
                        LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                        WHERE
                            (form_data.form_id = '" . escape($submitted_form['id']) . "')
                            AND (form_fields.contact_field = 'email_address')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $submitter_email_address = $row['data'];

                    // If this user has the same e-mail address as the connect-to-contact e-mail address
                    // field value for this submitted form, then the user has view access.
                    if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                        $view_access = TRUE;

                    // Otherwise this user is not the submitter, so check if user is a watcher.
                    } else {
                        $query = 
                            "SELECT COUNT(*)
                            FROM watchers
                            WHERE
                                (
                                    (user_id = '" . USER_ID . "')
                                    OR (email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                )
                                AND (page_id = '" . escape($page_id) . "')
                                AND (item_id = '" . escape($submitted_form['id']) . "')
                                AND (item_type = 'submitted_form')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // If the user is a watcher, then the user has view access.
                        if ($row[0] > 0) {
                            $view_access = TRUE;
                        }
                    }
                }
            }

            // If user does not have view access, then output error.
            if ($view_access == FALSE) {
                log_activity('access denied to view submitted form (' . $submitted_form['reference_code'] . ') on form item view because submitter security is enabled and visitor does not have access', $_SESSION['sessionusername']);
                output_error('Sorry, you do not have access to view this submitted form. <a href="javascript:history.go(-1)">Go back</a>.');
            }
        }
        
        // assume that the visitor does not have edit access until we find out otherwise
        $edit_access = false;
        
        // assume that the visitor does not have delete access until we find out otherwise
        $delete_access = false;
        
        // if submitted form is editable by registered user, the visitor has access to edit and delete submitted form
        if ($submitted_form_editable_by_registered_user == '1') {
            $edit_access = true;
            $delete_access = true;
            
        // else if user is logged in, then check if user has access to edit submitted form
        } elseif (isset($user['id']) == TRUE) {
            // if the user is greater than a user role,
            // or if the user is the submitter and the form is incomplete,
            // or if the user is the submitter and the form item view page allows users to edit their submissions,
            // or if the user is the form editor
            // or if user has access to manage forms and can edit the folder the form is in,
            // then the user has access to edit and delete submitted form
            if (
                ($user['role'] < 3)
                || (($user['id'] == $submitted_form['submitter_id']) and !$submitted_form['complete'])
                || (($user['id'] == $submitted_form['submitter_id']) && ($submitted_form_editable_by_submitter == '1'))
                || ((check_edit_access($folder_id) == true) && ($user['manage_forms'] == true))
            ) {
                $edit_access = true;
                $delete_access = true;
                
            // else if the user is the form editor, then the user has access to edit the submitted form, but not delete it
            } else if ($user['id'] == $submitted_form['form_editor_user_id']) {
                $edit_access = true;
            }
        }
        
        // if user has access to edit submitted form and chose to edit submitted form, then output form
        if (($edit_access == true) && ($_GET['edit_submitted_form'] == 'true')) {
            // assume that the visitor does not have access to edit office use only fields until we find out otherwise
            // we will use this below is several areas
            $office_use_only_access = FALSE;
            
            // if the visitor has office use only access, then remember that
            if (
                ($user['role'] < 3)
                || ((check_edit_access($folder_id) == true) && ($user['manage_forms'] == TRUE))
                || ($user['id'] == $submitted_form['form_editor_user_id'])
            ) {
                $office_use_only_access = TRUE;
            }
            
            // get label column width for custom form
            $query = "SELECT label_column_width FROM custom_form_pages WHERE page_id = '" . escape($custom_form_page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $label_column_width = $row['label_column_width'];
            
            // assume that we should not populate fields until we find out otherwise
            $populate_fields = FALSE;
            
            // if edit submitted form screen has not been submitted already, then get all submitted data in order to populate fields later
            if (isset($_SESSION['software']['liveforms']['edit_submitted_form'][$submitted_form['id']]) == FALSE) {
                $populate_fields = TRUE;
                
                $sql_office_use_only = "";
                
                // if the visitor does not have access to edit office use only fields, then prepare SQL so that we will not get those fields
                if ($office_use_only_access == FALSE) {
                    $sql_office_use_only = "AND (form_fields.office_use_only = '0')";
                }
                
                // get all submitted data for this form (other than possibly office use only fields)
                $query =
                    "SELECT
                        form_data.form_field_id,
                        form_data.data,
                        count(*) as number_of_values,
                        form_fields.office_use_only as office_use_only
                    FROM form_data
                    LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                    WHERE
                        (form_data.form_id = '" . escape($submitted_form['id']) . "')
                        $sql_office_use_only
                    GROUP BY form_data.form_field_id";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $fields = array();

                while ($row = mysqli_fetch_assoc($result)) {
                    $fields[] = $row;
                }
                
                // loop through all field data in order to add it to the array
                foreach ($fields as $field) {
                    // if there is more than one value, get all values
                    if ($field['number_of_values'] > 1) {
                        $query =
                            "SELECT data
                            FROM form_data
                            WHERE (form_id = '" . escape($submitted_form['id']) . "') AND (form_field_id = '" . $field['form_field_id'] . "')
                            ORDER BY id";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $field['data'] = array();
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $field['data'][] = $row['data'];
                        }
                    }
                    
                    $submitted_form['field_' . $field['form_field_id']] = $field['data'];
                }
            }

            $office_use_only_target_options = array();

            // If office use only fields will not appear on form, then get triggers for them,
            // so we can limit the options that appear in target pick lists.
            if ($office_use_only_access == false) {
                // Get the number of trigger options, so we can determine
                // if we need to deal with triggers.
                $number_of_trigger_options = db_value(
                    "SELECT COUNT(*)
                    FROM form_field_options
                    WHERE
                        (page_id = '" . escape($custom_form_page_id) . "')
                        AND (target_form_field_id != '0')");

                // If there is at least one trigger option, then apply triggers.
                if ($number_of_trigger_options > 0) {
                    // Get all answered values for fields that are pick lists and office use only
                    // so that we can determine if we need to apply triggers
                    $form_data_items = db_items(
                        "SELECT
                            form_data.form_field_id AS field_id,
                            form_data.data
                        FROM form_data
                        LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                        WHERE
                            (form_data.form_id = '" . escape($submitted_form['id']) . "')
                            AND (form_fields.type = 'pick list')
                            AND (form_fields.office_use_only = '1')");

                    // Loop through the form data items in order to check if we need to apply triggers.
                    foreach ($form_data_items as $form_data_item) {
                        // Check if there is an option that has a trigger for this selected value.
                        $option = db_item(
                            "SELECT
                                id,
                                target_form_field_id
                            FROM form_field_options
                            WHERE
                                (form_field_id = '" . $form_data_item['field_id'] . "')
                                AND (value = '" . escape($form_data_item['data']) . "')
                                AND (target_form_field_id != '0')");

                        // If an option with a trigger for the value was found, then add target options to array.
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
            }
            
            $sql_office_use_only = "";
            
            // if the visitor does not have access to edit office use only fields, then prepare SQL so that we will not get those fields
            if ($office_use_only_access == FALSE) {
                $sql_office_use_only = "AND (office_use_only = '0')";
            }
            
            // Get all fields for this form.
            $query =
                "SELECT
                    id,
                    name,
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
                    office_use_only
                FROM form_fields
                WHERE
                    (page_id = '" . escape($custom_form_page_id) . "')
                    $sql_office_use_only
                ORDER BY sort_order";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $fields = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            
            $label_column_width_added = FALSE;
            $wysiwyg_fields = array();

            // Prepare to keep track if whether there are file upload fields or not,
            // in order to know later if we need to output enctype for form.
            $file_upload_exists = false;
            
            $liveform = new liveform('edit_submitted_form', $submitted_form['id']);
            
            $output_fields = '';
            
            // loop through the fields in order to determine if we should output them
            foreach ($fields as $field) {
                // if this field appears in this form item view's layout, then output field so it can be edited
                if (preg_match('/\^\^' . escape_regex($field['name'])  . '\^\^/i', $layout) != 0) {
                    // if we should populate the field and this field is not a file upload type, then populate field.
                    if (
                        ($populate_fields == TRUE)
                        && ($field['type'] != 'file upload')
                    ) {
                        $liveform->assign_field_value($field['id'], prepare_form_data_for_output($submitted_form['field_' . $field['id']], $field['type'], $prepare_for_html = FALSE));
                    }
                    
                    $output_label_column_width = '';
                    
                    // if the label column width is not blank and it has not been added, then add it to this field
                    if (($label_column_width != '') && ($label_column_width_added == FALSE)) {
                        $output_label_column_width = '; width: ' . $label_column_width . '%';
                        $label_column_width_added = TRUE;
                    }
                    
                    // if field is for office use only, then prepare to apply office use only style to row
                    if ($field['office_use_only'] == 1) {
                        $row_class = ' class="software_office_use_only"';
                        
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
                        $query =
                            "SELECT
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
                            // If there are not target options for this field,
                            // or this option is a target option, then include this option.
                            // Target options in this case are the only options that a different office use only field require to appear in pick list.
                            if (
                                (isset($office_use_only_target_options[$field['id']]) == false)
                                || (in_array(mb_strtolower($row['value']), $office_use_only_target_options[$field['id']]) == true)
                            ) {
                                $options[] = $row;
                            }
                        }
                    }
                    
                    switch ($field['type']) {
                        case 'text box':
                        case 'email address':
                            
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'text', 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>$field['maxlength'], 'class'=>'software_input_text')) . '</td>
                                </tr>';
                            break;
                            
                        case 'text area':
                            // if field is wysiwyg, then prepare special values and output textarea so it takes up both columns
                            if ($field['wysiwyg'] == 1) {
                                // add field to wysiwyg fields array, so that we can prepare JavaScript later
                                $wysiwyg_fields[] = $field['id'];
                                
                                // if rows was not set, then set default rows so that WYSIWYG editor appears correctly
                                if (!$field['rows']) {
                                    $field['rows'] = 15;
                                }
                                
                                $style = '';
                                
                                // if cols was not set, then set default width so that WYSIWYG editor appears correctly
                                if (!$field['cols']) {
                                    $style = 'width: 95%';
                                }
                                
                                $output_fields .=
                                    '<tr' . $row_class . '>
                                        <td colspan="2">
                                            <div style="margin-bottom: .5em">' . $field['label'] . '</div>
                                            <div>' . $liveform->output_field(array('type'=>'textarea', 'name'=>$field['id'], 'id'=>$field['id'], 'value'=>$field['default_value'], 'maxlength'=>$field['maxlength'], 'rows'=>$field['rows'], 'cols'=>$field['cols'], 'class'=>'software_textarea', 'style'=>$style)) . '</div>
                                        </td>
                                    </tr>';
                                
                            // else the field is not wysiwyg, so output two columns like normal
                            } else {
                                $output_fields .=
                                    '<tr' . $row_class . '>
                                        <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                        <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'textarea', 'name'=>$field['id'], 'id'=>$field['id'], 'value'=>$field['default_value'], 'maxlength'=>$field['maxlength'], 'rows'=>$field['rows'], 'cols'=>$field['cols'], 'class'=>'software_textarea')) . '</td>
                                    </tr>';
                            }

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
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'select', 'name'=>$name, 'value'=>$field['default_value'], 'options'=>$pick_list_options, 'size'=>$field['size'], 'multiple'=>$multiple, 'class'=>'software_select')) . '</td>
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
                                
                                $output_options .= $liveform->output_field(array('type'=>'radio', 'name'=>$field['id'], 'id'=>'software_option_' . $option['id'], 'value'=>$option['value'], 'checked'=>$checked, 'class'=>'software_input_radio')) . '<label for="software_option_' . $option['id'] . '"> ' . h($option['label']) . '</label><br />';
                            }
                            
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
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
                                
                                $output_options .= $liveform->output_field(array('type'=>'checkbox', 'name'=>$name, 'id'=>'software_option_' . $option['id'], 'value'=>$option['value'], 'checked'=>$checked, 'class'=>'software_input_checkbox')) . '<label for="software_option_' . $option['id'] . '"> ' . h($option['label']) . '</label><br />';
                            }
                            
                            // the hidden field is outputted so even if a check box is not checked, a field will be included in the post data
                            // this is important because we use the fields in the post data to determine which fields should be updated
                            
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
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
                                    (form_data.form_id = '" . escape($submitted_form['id']) . "')
                                    AND (form_data.form_field_id = '" . $field['id'] . "')");

                            // If there is an existing file for this field, then output info for that.
                            if ($file['name'] != '') {
                                $output_file_info = '<div class="software_attachment" style="margin-bottom: .7em"><a href="' . OUTPUT_PATH . h(encode_url_path($file['name'])) . '" target="_blank" style="background: none; padding: 0"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_attachment.png" width="16" height="16" alt="attachment" title="" border="0" style="padding-right: .5em; vertical-align: middle" /></a><a href="' . OUTPUT_PATH . h(encode_url_path($file['name'])) . '" target="_blank">' . h($file['name']) . '</a> (' . convert_bytes_to_string($file['size']) . ')</div>';
                                $output_upload_label = 'Replace File: ';

                                // If this field is optional, then output delete option.
                                if ($field['required'] == 0) {
                                    $output_delete_option = '&nbsp; &nbsp;<label for="' . $field['id'] . '_delete_file">or Delete File: </label>' . $liveform->output_field(array('type'=>'checkbox', 'name'=> $field['id'] . '_delete_file', 'id'=> $field['id'] . '_delete_file', 'value'=>'1', 'class'=>'software_input_checkbox'));
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
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">
                                        ' . $output_file_info . '
                                        <div>' . $output_upload_label . $liveform->output_field(array('type'=>'file', 'name'=>$field['id'], 'size'=>$field['size'], 'class'=>'software_input_file')) . $output_delete_option . '</div>
                                    </td>
                                </tr>';

                            $file_upload_exists = true;
                            
                            break;
                            
                        case 'date':
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">
                                        ' . $liveform->output_field(array('type'=>'text', 'id' => $field['id'], 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'10', 'class'=>'software_input_text')) . '
                                        ' . get_date_picker_format() . '
                                        <script>
                                            software_$("#' . $field['id'] . '").datepicker({
                                                dateFormat: date_picker_format
                                            });
                                        </script>
                                    </td>
                                </tr>';
                            break;
                            
                        case 'date and time':
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">
                                        ' . $liveform->output_field(array('type'=>'text', 'id' => $field['id'], 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'22', 'class'=>'software_input_text')) . '
                                        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
                                        ' . get_date_picker_format() . '
                                        <script>
                                            software_$("#' . $field['id'] . '").datetimepicker({
                                                dateFormat: date_picker_format,
                                                timeFormat: "h:mm TT"
                                            });
                                        </script>
                                    </td>
                                </tr>';
                            break;

                        case 'information':

                            if ($editable) {
                                // add the edit button to images in content
                                $field['information'] = add_edit_button_for_images('form_field', $field['id'], $field['information']);
                            }
                            
                            $output_fields .=
                                '<tr' . $row_class. '>
                                    <td colspan="2" style="vertical-align: top">' . $field['information'] . '</td>
                                </tr>';

                            break;
                            
                        case 'time':
                            $output_fields .=
                                '<tr' . $row_class . '>
                                    <td style="vertical-align: top' . $output_label_column_width . '">' . $field['label'] . '</td>
                                    <td style="vertical-align: top">' . $liveform->output_field(array('type'=>'text', 'name'=>$field['id'], 'value'=>$field['default_value'], 'size'=>$field['size'], 'maxlength'=>'11', 'class'=>'software_input_text')) . ' (Format: h:mm AM/PM)</td>
                                </tr>';
                            break;
                    }
                }
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
            if (!$submitted_form['complete']) {
                $output_buttons =
                    '<input type="submit" name="save_for_later_button" value="Save for Later" class="software_input_submit_secondary">&nbsp;&nbsp;

                    <input type="submit" name="complete_button" value="Complete" class="software_input_submit_primary save_button">&nbsp;&nbsp;';

            // Otherwise the form is complete, so show different buttons.
            } else {
                $output_buttons =
                    '<input type="submit" name="save_button" value="Save" class="software_input_submit_primary save_button">&nbsp;&nbsp;

                    <input type="submit" name="incomplete_button" value="Incomplete" class="software_input_submit_secondary">&nbsp;&nbsp;';
            }
            
            // if the user has access to delete the submitted form, then output delete button
            if ($delete_access == TRUE) {
                $output_delete_button = '&nbsp;&nbsp;<input type="submit" name="delete_button" value="Delete" class="software_input_submit_secondary delete_button" onclick="return confirm(\'WARNING: This submitted form will be permanently deleted.\')">';
            }
            
            $output = 
                $output_wysiwyg_javascript . '
                ' . $liveform->output_errors() . '
                ' . $liveform->output_notices() . '
                <form' . $output_enctype . ' action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_submitted_form.php" method="post" style="margin: 0px">
                    ' . get_token_field() . '
                    <input type="hidden" name="id" value="' . $submitted_form['id'] . '" />
                    <input type="hidden" name="form_item_view_page_id" value="' . $page_id . '" />
                    <input type="hidden" name="form_list_view_send_to" value="' . h($_GET['send_to']) . '" />
                    <input type="hidden" name="send_to" value="' . h(get_request_uri()) . '" />
                    <table style="margin-bottom: 1em">
                        ' . $output_fields . '
                    </table>
                    <div>

                        ' . $output_buttons . '

                        <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="software_input_submit_secondary cancel_button">

                        ' . $output_delete_button . '

                    </div>
                </form>';
            
            $liveform->remove_form();
            
        // else user has not chosen to edit submitted form, so just output layout with values
        } else {

            // record the view in the database so that the form view directory feature will know about it
            $query =
                "INSERT INTO submitted_form_views (
                    submitted_form_id,
                    page_id,
                    timestamp)
                VALUES (
                    '" . $submitted_form['id'] . "',
                    '" . $page_id . "',
                    UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // Get a random number between 1 and 100 in order to determine if we should delete
            // old submitted form view records.  There is a 1 in 100 chance that we will delete
            // old records each time a form item view is viewed.
            $random_number = rand(1, 100);
            
            // if the random number is 1, then delete old log entries
            // all log entries before 6 months ago are deleted
            if ($random_number == 1) {
                $six_months_ago_timestamp = time() - 15552000;

                db("DELETE FROM submitted_form_views WHERE timestamp < $six_months_ago_timestamp");
            }

            // Also record the view in the submitted form info table which stores totals for performance reasons.

            // If a record does not already exist in the submitted form info table, then add one.
            if (db_value("SELECT COUNT(*) FROM submitted_form_info WHERE (submitted_form_id = '" . $submitted_form['id'] . "') AND (page_id = '" . $page_id . "')") == 0) {
                db(
                    "INSERT INTO submitted_form_info (
                        submitted_form_id,
                        page_id,
                        number_of_views)
                    VALUES (
                        '" . $submitted_form['id'] . "',
                        '" . $page_id . "',
                        '1')");

            // Otherwise a record already exists in the submitted form info table, so just update it.
            } else {
                db(
                    "UPDATE submitted_form_info
                    SET number_of_views = number_of_views + 1
                    WHERE
                        (submitted_form_id = '" . $submitted_form['id'] . "')
                        AND (page_id = '" . $page_id . "')");
            }
            
            // get form data for all custom fields
            $query =
                "SELECT
                    form_data.form_field_id,
                    form_data.data,
                    count(*) as number_of_values,
                    form_fields.name,
                    form_fields.type,
                    form_fields.office_use_only as office_use_only,
                    files.name as file_name
                FROM form_data
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                LEFT JOIN files on form_data.file_id = files.id
                WHERE form_data.form_id = '" . escape($submitted_form['id']) . "'
                GROUP BY form_data.form_field_id";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // initialize array to remember which fields have data, for use with conditionals later
            $custom_fields_with_data = array();

            $fields = array();

            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            
            // loop through all field data
            foreach ($fields as $field) {
                // if there is more than one value, get all values
                if ($field['number_of_values'] > 1) {
                    $query =
                        "SELECT data
                        FROM form_data
                        WHERE (form_id = '" . escape($submitted_form['id']) . "') AND (form_field_id = '" . $field['form_field_id'] . "')
                        ORDER BY id";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $field['data'] = array();
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $field['data'][] = $row['data'];
                    }
                }
                    
                $data = '';
                
                // if there are multiple data parts, prepare data string with commas for separation
                if (is_array($field['data']) == true) {
                    foreach ($field['data'] as $data_part) {
                        if ($data != '') {
                            $data .= ', ';
                        }
                        
                        $data .= $data_part;
                    }
                
                // else there are not multiple data parts
                } else {
                    // if there is a file name, use file name for data
                    if ($field['file_name']) {
                        $data = $field['file_name'];
                        
                    // else there is not a file name, so just use data
                    } else {
                        $data = $field['data'];
                    }
                }
                
                $submitted_form['field_' . $field['form_field_id']] = $data;

                // if there is data for this field, remember that for conditionals later
                if ($data != '') {
                    $custom_fields_with_data[$field['name']] = TRUE;
                }
            }

            // get custom fields
            $query =
                "SELECT
                    id,
                    name,
                    type,
                    wysiwyg
                FROM form_fields
                WHERE page_id = '" . escape($custom_form_page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $custom_fields = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $custom_fields[] = $row;
            }
            
            // start the output off with just the layout
            $output = $layout;

            // get all conditionals
            preg_match_all('/\[\[(.*?\^\^(.*?)\^\^.*?)(\|\|(.*?))?\]\]/si', $output, $conditionals, PREG_SET_ORDER);
            
            // loop through all conditionals
            foreach ($conditionals as $conditional) {
                $whole_string = $conditional[0];
                $positive_string = $conditional[1];
                $negative_string = $conditional[4];
                $field_name = $conditional[2];
                
                // if field name is reference code and there is another field, use the other field,
                // because we don't want to use reference code for conditional checking
                if (($field_name == 'reference_code') && (preg_match('/\[\[(.*?\^\^reference_code\^\^.*?\^\^(.*?)\^\^.*?)(\|\|(.*?))?\]\]/si', $whole_string, $conditional))) {
                    $field_name = $conditional[2];
                }
                
                // assume that the field name is not valid, until we find out otherwise
                // we don't want to replace the conditional if the field name is not valid, so that a ^^name^^ conditional will be left alone if used with an e-mail campaign
                $field_name_valid = FALSE;
                
                // loop through the standard fields in order to determine if the field name is a valid standard field name
                foreach ($standard_fields as $standard_field) {
                    // if this standard field value matches the field name, then the field name is valid, so remember that and break out of the loop
                    if ($standard_field['value'] == $field_name) {
                        $field_name_valid = TRUE;
                        break;
                    }
                }
                
                // if we don't know if the field name is valid yet, then loop through the custom fields in order to check if the field name is a valid custom field
                if ($field_name_valid == FALSE) {
                    foreach ($custom_fields as $custom_field) {
                        // if this custom field name matches the field name, then the field name is valid, so remember that and break out of the loop
                        if ($custom_field['name'] == $field_name) {
                            $field_name_valid = TRUE;
                            break;
                        }
                    }
                }
                
                // if the field name is valid, then replace conditional
                if ($field_name_valid == TRUE) {
                    // if there is data to output, use first part of conditional
                    if (($submitted_form[$field_name] != '') || ($custom_fields_with_data[$field_name] == TRUE)) {
                        $output = str_replace($whole_string, $positive_string, $output);
                        
                    // else there is no data to output, so use second part of conditional
                    } else {
                        $output = str_replace($whole_string, $negative_string, $output);
                    }
                }
            }

            // get all variables so they can be replaced with data
            preg_match_all('/\^\^(.*?)\^\^(%%(.*?)%%)?/i', $output, $variables, PREG_SET_ORDER);

            // loop through the variables in order to replace them with data
            foreach ($variables as $variable) {
                $whole_string = $variable[0];
                $field_name = $variable[1];

                $date_format = '';

                // if a date format was passed along with the variable, then store that
                // we have to use unhtmlspecialchars() because the date format was created
                // in the rich-text editor, so there might be HTML entities (e.g. &lt;)
                // and the date() function would convert those characters into date elements
                if (isset($variable[3]) == TRUE) {
                    $date_format = unhtmlspecialchars($variable[3]);
                }

                // assume that the field name is not valid, until we find out otherwise
                $field_name_valid = FALSE;

                $field_group = '';
                $field_type = '';
                $field_id = '';
                $field_wysiwyg = '';

                // loop through the standard fields in order to determine
                // if the field name is valid and to get field info
                foreach ($standard_fields as $standard_field) {
                    // if this standard field value matches the field name, then the field name is valid,
                    // so remember that, store field info, and break out of the loop
                    if ($standard_field['value'] == $field_name) {
                        $field_name_valid = TRUE;
                        $field_group = 'standard';
                        $field_type = $standard_field['type'];

                        break;
                    }
                }

                // if we don't know if the field name is valid yet, then loop through the custom fields
                // in order to check if the field name is a valid custom field and to get field info
                if ($field_name_valid == FALSE) {
                    foreach ($custom_fields as $custom_field) {
                        // if this custom field name matches the field name, then the field name is valid,
                        // so remember that, store field info, and break out of loop
                        if ($custom_field['name'] == $field_name) {
                            $field_name_valid = TRUE;
                            $field_group = 'custom';
                            $field_id = $custom_field['id'];
                            $field_type = $custom_field['type'];
                            $field_wysiwyg = $custom_field['wysiwyg'];

                            break;
                        }
                    }
                }

                // if the field name is valid, then continue to replace variable with data
                if ($field_name_valid == TRUE) {
                    $data = '';

                    // assume that we should prepare field data for HTML until we find out otherwise
                    $prepare_for_html = TRUE;

                    // get values differently based on the field group
                    switch ($field_group) {
                        case 'standard':
                            $data = $submitted_form[$field_name];
                            break;
                        
                        case 'custom':
                            $data = $submitted_form['field_' . $field_id];

                            // if this field is a WYSIWYG field, then do not prepare for HTML
                            if ($field_wysiwyg == 1) {
                                $prepare_for_html = FALSE;
                            }

                            break;
                    }

                    $data = prepare_form_data_for_output($data, $field_type, $prepare_for_html, $date_format);

                    // if this is a standard field, then do some extra things for standard fields
                    if ($field_group == 'standard') {
                        // If this is the number of views or number of comments field then do some things for the numeric value.
                        if (
                            ($field_name == 'number_of_views')
                            || ($field_name == 'number_of_comments')
                        ) {
                            // If the value is blank, then set it to 0.
                            if ($data == '') {
                                $data = 0;

                            // Otherwise the value is not blank, so format the number,
                            // so that it has commas in the thousands place.
                            } else {
                                $data = number_format($data);
                            }
                        }
                        
                        // if this is the newest comment field and the message is greater than 100 characters, then shorten message
                        if (($field_name == 'newest_comment') && (mb_strlen($data) > 100)) {
                            $data = mb_substr($data, 0, 100) . '...';
                        }
                        
                        // if this is the newest comment name field and the value is blank and there is a newest comment, then set name to "Anonymous"
                        if (($field_name == 'newest_comment_name') && ($data == '') && ($submitted_form['newest_comment_id'] != '')) {
                            $data = 'Anonymous';
                        }
                        
                        // If this is the submitter field and badge is enabled for the submitter,
                        // or if this is the last modifier field and badge is enabled for the last modifier,
                        // or if this is the newest comment name field and badge is enabled for the newest comment submitter,
                        // then add badge
                        if (
                            (($field_name == 'submitter') && ($submitted_form['submitter_badge'] == 1) && (($submitted_form['submitter_badge_label'] != '') || (BADGE_LABEL != '')))
                            || (($field_name == 'last_modifier') && ($submitted_form['last_modifier_badge'] == 1) && (($submitted_form['last_modifier_badge_label'] != '') || (BADGE_LABEL != '')))
                            || (($field_name == 'newest_comment_name') && ($submitted_form['newest_comment_submitter_badge'] == 1) && (($submitted_form['newest_comment_submitter_badge_label'] != '') || (BADGE_LABEL != '')))
                        ) {
                            $badge_label = '';

                            // Get the user's badge label differently based on the field.
                            switch ($field_name) {
                                case 'submitter':
                                    $badge_label = $submitted_form['submitter_badge_label'];
                                    break;

                                case 'last_modifier':
                                    $badge_label = $submitted_form['last_modifier_badge_label'];
                                    break;

                                case 'newest_comment_name':
                                    $badge_label = $submitted_form['newest_comment_submitter_badge_label'];
                                    break;
                            }

                            // If the user's badge label is blank, then use default label.
                            if ($badge_label == '') {
                                $badge_label = BADGE_LABEL;
                            }

                            $data .= ' <span class="software_badge ' . h(get_class_name($badge_label)) . '">' . h($badge_label) . '</span>';
                        }

                        // If this is the comment attachments field and the data is not blank,
                        // then output the comment attachments as links.
                        if (($field_name == 'comment_attachments') && ($data != '')) {
                            $comment_attachments = explode('||', $data);

                            $output_comment_attachments = '';

                            foreach ($comment_attachments as $comment_attachment) {
                                if ($output_comment_attachments != '') {
                                    $output_comment_attachments .= ', ';
                                }

                                $output_comment_attachments .= '<a href="' . OUTPUT_PATH . h(encode_url_path($comment_attachment)) . '" target="_blank">' . h($comment_attachment) . '</a>';
                            }

                            $data = $output_comment_attachments;
                        }
                    }

                    // replace the variable with the data
                    // we can't use str_replace() for this because we need to limit the number of replacements to 1
                    // in order to prevent bugs where it will replace variables further below that might have date formats
                    $output = preg_replace('/' . preg_quote($whole_string, '/') . '/', addcslashes($data, '\\$'), $output, 1);
                }
            }

            // If the layout type is system, then prepare system content.
            if ($layout_type == 'system') {

                $output_auto_registration = '';

                // If a user account was created via the auto-registration feature,
                // then show user account info.
                if ($_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['email_address'] != ''){
                    $output_auto_registration =
                        '<div class="account heading" style="margin-top: 1em">New Account</div>
                        <div class="account data">
                            <p>We have created a new account for you on our site. You can find your login info below.</p>
                            <p>
                                Email: ' . h($_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['email_address']) . '<br>
                                Password: ' . h($_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['password']) . '
                            </p>
                        </div>';
                }
                
                $output_buttons = '';
                
                // if a form id was not passed then output the back and edit buttons
                if ($form_id == '') {
                    $output_back_button = '';
                    $output_edit_button = '';
                    
                    // if there is a send to, then output the back button.
                    if ((isset($_GET['send_to']) == TRUE) && ($_GET['send_to'] != '')) {
                        $output_back_button = '<a href="' . h(escape_url($_GET['send_to'])) . '" class="software_button_primary back_button">Back</a>&nbsp;&nbsp;&nbsp;';
                    }
                    
                    // if visitor has access to edit submitted form, then output edit button
                    if ($edit_access == true) {
                        $edit_url = build_url(array(
                            'url' => get_request_uri(),
                            'parameters' => array('edit_submitted_form' => 'true')));

                        $output_edit_button = '<a href="' . h($edit_url) . '" class="software_button_primary">Edit</a>';
                    }
                    
                    $output_buttons = '<div>' . $output_back_button . $output_edit_button . '<br />&nbsp;</div>';
                }
                
                $liveform = new liveform('form_item_view');
                
                $output = 
                    $liveform->output_errors() . '
                    ' . $liveform->output_notices() . '
                    ' . $output . '
                    ' . $output_auto_registration . '
                    ' . $output_buttons;
                
                $liveform->remove_form();


            // Otherwise the layout type is custom, so prepare custom content.
            } else {

                $liveform = new liveform('form_item_view');

                $back_button_url = '';
                $edit_button_url = '';

                // If a form id was not passed then output the back and edit buttons.
                if (!$form_id) {
                    
                    // If there is a send to, then output the back button.
                    if ($_GET['send_to']) {
                        $back_button_url = escape_url($_GET['send_to']);
                    }
                    
                    // If visitor has access to edit submitted form, then output edit button.
                    if ($edit_access) {
                        $edit_button_url = build_url(array(
                            'url' => get_request_uri(),
                            'parameters' => array('edit_submitted_form' => 'true')));
                    }
                }

                $auto_registration = false;

                if ($_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['email_address'] != '') {
                    $auto_registration = true;
                    $auto_registration_email_address = $_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['email_address'];
                    $auto_registration_password = $_SESSION['software']['custom_form_auto_registration'][$submitted_form['id']]['password'];
                }

                $output = render_layout(array(
                    'page_id' => $page_id,
                    'messages' => $liveform->get_messages(),
                    'content' => $output,
                    'layout' => $layout,
                    'form' => $submitted_form,
                    'auto_registration' => $auto_registration,
                    'auto_registration_email_address' => $auto_registration_email_address,
                    'auto_registration_password' => $auto_registration_password,
                    'back_button_url' => $back_button_url,
                    'edit_button_url' => $edit_button_url));

                $liveform->remove_form();
            }
        }
    }
    
    if ($editable) {

        // if output is blank, give it one space in order to prevent region from collapsing
        if ($output == '') {
            $output = '&nbsp;';
        }
        
        $output =
            '<div class="edit_mode" style="position: relative; border: 1px dashed #805FA7; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_form_item_view.php?page_id=' . $page_id . '&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #805FA7; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Form Item View: '. h(get_page_name($page_id)) .'">Edit</a>
            ' . $output . '
            </div>';
    }

    return
        '<div class="software_form_item_view">
            ' . $output . '
        </div>';
}
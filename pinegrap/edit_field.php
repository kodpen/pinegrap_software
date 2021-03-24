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

// get page_id and product_id for field, in order to validate user's access
$query =
    "SELECT
        form_type,
        page_id,
        product_id,
        name as field_name,
        rss_field
    FROM form_fields
    WHERE form_fields.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if a form field could not be found, then output error
if (mysqli_num_rows($result) == 0) {
    output_error('The form field could not be found.');
}

$row = mysqli_fetch_assoc($result);

$field_form_type = $row['form_type'];
$page_id = $row['page_id'];
$product_id = $row['product_id'];
$field_name = $row['field_name'];

// Set the old rss field so later we can determine if field has been set as the title field,
// so address names need to be updated for submitted forms for pretty URL feature.
$old_rss_field = $row['rss_field'];

// if there is a page_id, this is a page form
if ($page_id != 0) {
    validate_area_access($user, 'user');
    
    // get page info
    $query =
        "SELECT
            page_type,
            page_folder,
            page_name
        FROM page
        WHERE page_id = '" . escape($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $page_type = $row['page_type'];
    $folder_id = $row['page_folder'];
    $page_name = $row['page_name'];

    $form_type = '';
    
    // get the form type by looking at the page type
    switch ($page_type) {
        case 'custom form':
            $form_type = 'custom';
            break;

        // Express order can have a shipping and/or billing form, so check field for the type
        // of form that we are dealing with
        case 'express order':

            if ($field_form_type == 'shipping') {
                $form_type = 'shipping';
            } else {
                $form_type = 'billing';
            }

            break;

        case 'shipping address and arrival':
            $form_type = 'shipping';
            break;

        case 'billing information':
            $form_type = 'billing';
            break;
    }

    // Get the form type name that we will output to user

    $form_type_name = '';

    switch ($form_type) {
        case 'custom':
            $form_type_name = 'custom form';
            break;

        case 'shipping':
            $form_type_name = 'custom shipping form';
            break;

        case 'billing':
            $form_type_name = 'custom billing form';
            break;
    }
    
    $form_type_identifier_id = 'page_id';

    // Prepare sql filter in order to get correct fields

    $form_type_filter =
        "form_fields." . $form_type_identifier_id . " = '" . e(${$form_type_identifier_id}) . "'";

    // If the page type is express order then we need to add an extra filter for the form type
    if ($page_type == 'express order') {
        $form_type_filter .=
            " AND form_fields.form_type = '" . e($form_type) . "'";
    } 
    
    // validate user's access
    if (check_edit_access($folder_id) == false) {
        log_activity('access denied to edit field on ' . $form_type_name . ' because user does not have access to modify folder that ' . $form_type_name . ' is in', $_SESSION['sessionusername']);
        output_error('Access denied.');
    }

    $form_name = '';
    $quiz = '';

    // If this is a page and form type that supports a form name, then get it
    if ($page_type != 'express order' or $form_type != 'shipping') {
    
        $sql_quiz = "";
        
        // if this is a custom form, then get quiz value
        if ($form_type == 'custom') {
            $sql_quiz = ", quiz";
        }
        
        // get form name and possibly quiz for page
        $query = "SELECT form_name" . $sql_quiz . " FROM " . str_replace(' ', '_', $page_type) . "_pages WHERE page_id = '" . escape($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $form_name = $row['form_name'];
        $quiz = $row['quiz'];
    }
    
    // if form name is blank, use page name for form name
    if (!$form_name) {
        $form_name = $page_name;
    }
    
    // setup form designer heading table, content heading and subheading.
    $output_form_designer_subnav_heading = h($field_name);
    $output_form_designer_subnav_subheading = ucwords($form_type_name) . ': ' . h($form_name);
    $output_form_designer_content_heading = 'Edit ' . ucwords($form_type_name) . ' Field';
    $output_form_designer_content_subheading = 'View or update this ' . $form_type_name . ' field.';
    
// else if there is a product_id, this is a product form
} elseif ($product_id != 0) {

    validate_ecommerce_access($user);
    
    $form_type = 'product';
    $form_type_name = 'product form';
    $form_type_identifier_id = 'product_id';
    $form_type_filter =
        "form_fields." . $form_type_identifier_id . " = '" . e(${$form_type_identifier_id}) . "'";
    
    // get product name, short description and form name to determine what we will use for the form name
    $query = "SELECT 
                 name,
                 short_description,
                 form_name
             FROM products
             WHERE id = '" . escape($product_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $product_name = $row['name'];
    $short_description = $row['short_description'];
    $form_name = $row['form_name'];
    
    // if form name is blank and short description is not, use short description for form name
    if (($form_name == '') && ($short_description != '')) {
        $form_name = $short_description;
        
    // else, if form name is blank and product name is not, use product name for form name
    } else if (($form_name == '') && ($product_name != '')) {
        $form_name = $product_name;
    }
    
    // setup form designer heading table, content heading and subheading
    $output_form_designer_subnav_heading = h($short_description); 
    $output_form_designer_subnav_subheading = ' Product ID: ' . h($product_name) . ' | Form Name: ' . h($form_name);
    $output_form_designer_content_heading = 'Edit Product Form Field';
    $output_form_designer_content_subheading = 'View or update this product form field.';
}

include_once('liveform.class.php');
$liveform = new liveform('edit_field');

if (!$_POST) {
    // get field data
    $query = "SELECT *
             FROM form_fields
             WHERE form_fields.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $id = $row['id'];
    $name = $row['name'];
    $rss_field = $row['rss_field'];
    $label = $row['label'];
    $type = $row['type'];
    $required = $row['required'];
    $default_value = $row['default_value'];
    $use_folder_name_for_default_value = $row['use_folder_name_for_default_value'];
    $sort_order = $row['sort_order'];
    $size = $row['size'];
    $maxlength = $row['maxlength'];
    $wysiwyg = $row['wysiwyg'];
    $rows = $row['rows'];
    $cols = $row['cols'];
    $multiple = $row['multiple'];
    $spacing_above = $row['spacing_above'];
    $spacing_below = $row['spacing_below'];
    $contact_field = $row['contact_field'];
    $office_use_only = $row['office_use_only'];
    $upload_folder_id = $row['upload_folder_id'];
    $quiz_question = $row['quiz_question'];
    $quiz_answer = $row['quiz_answer'];
    $information = $row['information'];

    // If this is a pick list, then get trigger info.
    if ($type == 'pick list') {
        $sql_trigger_select = ", form_fields.name AS target_form_field_name";
        $sql_trigger_join = "LEFT JOIN form_fields ON form_field_options.target_form_field_id = form_fields.id";
    }
    
    // get field options
    $options = db_items(
        "SELECT
            form_field_options.id,
            form_field_options.label,
            form_field_options.value,
            form_field_options.email_address,
            form_field_options.upload_folder_id,
            form_field_options.default_selected
            $sql_trigger_select
        FROM form_field_options
        $sql_trigger_join
        WHERE form_field_options.form_field_id = '" . escape($_GET['id']) . "'
        ORDER BY form_field_options.sort_order");

    $output_options = '';
    
    // loop through all field options in order to prepare list of options
    foreach ($options as $option) {
        $output_options .= "\n";
        
        $output_options .= h($option['label']);
        
        // If the value is not equal to the label or another parameter exists after this,
        // then add parameter.
        if (
            ($option['value'] != $option['label'])
            || ($option['default_selected'] == 1)
            || ($option['target_form_field_name'] != '')
            || ($option['upload_folder_id'] != 0)
        ) {
            $output_options .= '|' . h($option['value']);
        }
        
        // If option should be selected by default or another parameter exists after this one,
        // then add parameter.
        if (
            ($option['default_selected'] == 1)
            || ($option['target_form_field_name'] != '')
            || ($option['upload_folder_id'] != 0)
        ) {
            $output_options .= '|';

            // If option should be selected by default, then output value for that.
            if ($option['default_selected'] == 1) {
                $output_options .= 'on';
            }
        }

        // If there is a trigger or another parameter exists after this one,
        // then add parameter.
        if (
            ($option['target_form_field_name'] != '')
            || ($option['upload_folder_id'] != 0)
        ) {
            $output_options .= '|';

            // If there is a trigger, then output value for that.
            if ($option['target_form_field_name'] != '') {
                $output_options .= h($option['target_form_field_name']) . '=';

                // Get target options in order to output them all.
                $target_options = db_items("SELECT value FROM target_options WHERE trigger_option_id = '" . $option['id'] . "'");

                // Loop through target options in order to output them.
                foreach ($target_options as $key => $target_option) {
                    // If this is not the first option, then output comma for separation.
                    if ($key != 0) {
                        $output_options .= ',';
                    }

                    $output_options .= h($target_option['value']);
                }
            }
        }

        // If there is an upload folder id, then add it to option line.
        if ($option['upload_folder_id'] != 0) {
            $output_options .= '|' . $option['upload_folder_id'];
        }
        
        if ($option['email_address'] != '') {
            $output_options .= ' ^^' . h($option['email_address']) . '^^';
        }
    }
    
    if (!$size) {$size = '';}
    if (!$maxlength) {$maxlength = '';}
    if (!$rows) {$rows = '';}
    if (!$cols) {$cols = '';}
    
    // check if required should be checked
    if ($required == 1) {
        $required_checked = ' checked="checked"';
    } else {
        $required_checked = '';
    }
    
    // check if wysiwyg should be checked
    if ($wysiwyg == 1) {
        $wysiwyg_checked = ' checked="checked"';
    } else {
        $wysiwyg_checked = '';
    }
    
    // check if multiple should be checked
    if ($multiple == 1) {
        $multiple_checked = ' checked="checked"';
    } else {
        $multiple_checked = '';
    }
    
    // get field id for field directly above this field, because this will be the position value
    $query = "SELECT id
             FROM form_fields
             WHERE ($form_type_filter) AND (sort_order < $sort_order)
             ORDER BY sort_order DESC
             LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if no fields were found then this field is the first field
    if (mysqli_num_rows($result) == 0) {
        $position = 'top';
    
    // else this field is not the first field, so store field id in position value
    } else {
        $row = mysqli_fetch_assoc($result);
        $position = $row['id'];
    }
    
    // check if spacing above should be checked
    if ($spacing_above == 1) {
        $spacing_above_checked = ' checked="checked"';
    } else {
        $spacing_above_checked = '';
    }
    
    // check if spacing below should be checked
    if ($spacing_below == 1) {
        $spacing_below_checked = ' checked="checked"';
    } else {
        $spacing_below_checked = '';
    }

    if ($use_folder_name_for_default_value == 1) {
        $default_value_disabled = 'disabled="disabled"';
        $default_value_class = ' class="disabled"';
        $use_folder_name_for_default_value_checked = ' checked="checked"';
    } else {
        $default_value_disabled = '';
        $default_value_class = '';
        $use_folder_name_for_default_value_checked = '';
    }
    
    // hide all form type properties until we determine which need to be displayed
    $name_row_style = 'display: none';
    $rss_field_row_style = 'display: none';
    $rss_field_heading_style = 'display: none';
    $label_row_style = 'display: none';
    $required_row_style = 'display: none';
    $upload_folder_id_row_style = 'display: none';
    $default_value_row_style = 'display: none';
    $position_row_style = 'display: none';
    $size_row_style = 'display: none';
    $maxlength_row_style = 'display: none';
    $wysiwyg_row_style = 'display: none';
    $rows_row_style = 'display: none';
    $cols_row_style = 'display: none';
    $multiple_row_style = 'display: none';
    $spacing_row_style = 'display: none';
    
    // if this is a custom form
    if ($form_type == 'custom') {
        $contact_field_row_style = 'display: none';
        $office_use_only_row_style = 'display: none';
        
        // check if office use only should be checked
        if ($office_use_only == 1) {
            $office_use_only_checked = ' checked="checked"';
        } else {
            $office_use_only_checked = '';
        }
        
        // if quiz is enabled for custom form
        if ($quiz == 1) {
            // check if quiz question should be checked
            if ($quiz_question == 1) {
                $quiz_question_checked = ' checked="checked"';
            } else {
                $quiz_question_checked = '';
            }
            
            $quiz_question_row_style = 'display: none';
            $quiz_answer_row_style = 'display: none';
        }

        // If an upload folder has not been set for this field yet,
        // then set it to the custom form's folder as the default.
        if (!$upload_folder_id) {
            $upload_folder_id = $folder_id;
        }
    }
    
    $choices_row_style = 'display: none';
    $information_row_style = 'display: none';
    
    $activate_editors = false;
    
    switch($type) {
        case 'text box':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $maxlength_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $contact_field_row_style = '';
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'text area':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';            
            $required_row_style = '';
            $default_value_row_style = '';
            $wysiwyg_row_style = '';
            $rows_row_style = '';
            $cols_row_style = '';
            $maxlength_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $contact_field_row_style = '';
                $office_use_only_row_style = '';
            }
            break;
            
        case 'pick list':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $multiple_row_style = '';
            $spacing_row_style = '';
            $contact_field_row_style = '';
            $choices_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'radio button':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $spacing_row_style = '';
            $contact_field_row_style = '';
            $choices_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'check box':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $spacing_row_style = '';
            $contact_field_row_style = '';
            $choices_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'file upload':
            $name_row_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $upload_folder_id_row_style = '';
            $size_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
            }
            break;
            
        case 'date':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'date and time':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'email address':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $maxlength_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $contact_field_row_style = '';
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
            
        case 'information':
            $name_row_style = '';
            $information_row_style = '';
            $position_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
            }
            
            $activate_editors = true;
            
            break;
            
        case 'time':
            $name_row_style = '';
            $rss_field_row_style = '';
            $rss_field_heading_style = '';
            $label_row_style = '';
            $position_row_style = '';
            $required_row_style = '';
            $default_value_row_style = '';
            $size_row_style = '';
            $spacing_row_style = '';
            
            // if this is a custom form
            if ($form_type == 'custom') {
                $office_use_only_row_style = '';
                
                // if quiz is enabled for custom form, then show quiz fields
                if ($quiz == 1) {
                    $quiz_question_row_style = '';
                    
                    // if quiz question is enabled, then show quiz answer field
                    if ($quiz_question == 1) {
                        $quiz_answer_row_style = '';
                    }
                }
            }
            break;
    }

    $output_rss_field_heading = '';
    $output_rss_field_row = '';
    $output_contact_field_row = '';
    $output_office_use_only_row = '';
    $output_quiz_rows = '';
    $delete_data_warning = '';
    
    // if this is a custom form then output certain areas
    if ($form_type == 'custom') {

        $output_rss_field_heading = '<span id="rss_field_heading" style="' . $rss_field_heading_style . '"> &amp; RSS</span>';
        
        $output_rss_field_row = 
            '<tr id="rss_field_row" style="' . $rss_field_row_style . '">
                <td>RSS / Search Element:</td>
                <td><select name="rss_field">' . select_rss_field($rss_field) . '</select></td>
            </tr>';
        
        $output_contact_field_row =
            '
            <tr id="contact_field_row_header" style="' . $contact_field_row_style . '">
                <th colspan="2"><h2>Prefill & Update the Submitter\'s Contact Field</h2></th>
            </tr>
            <tr id="contact_field_row" style="' . $contact_field_row_style . '">
                <td>Connect to Contact:</td>
                <td><select name="contact_field"><option value="">-None-</option>' . select_contact_field($contact_field) . '</select></td>
            </tr>';
        
        $output_office_use_only_row =
            '<tr id="office_use_only_row_header" style="' . $office_use_only_row_style . '">
                <th colspan="2"><h2>Hide Field from Submitter</h2></th>
            </tr>
            <tr id="office_use_only_row" style="' . $office_use_only_row_style . '">
                <td>Office Use Only:</td>
                <td><input type="checkbox" name="office_use_only" value="1"' . $office_use_only_checked . ' class="checkbox" /></td>
            </tr>';
        
        // if quiz is enabled for custom form, then prepare to output quiz fields
        if ($quiz == 1) {
            $output_quiz_rows =
                '<tr id="quiz_question_row" style="' . $quiz_question_row_style . '">
                    <td>Quiz Question:</td>
                    <td><input type="checkbox" name="quiz_question" id="quiz_question" value="1"' . $quiz_question_checked . ' class="checkbox" onclick="show_or_hide_quiz_question()" /></td>
                </tr>
                <tr id="quiz_answer_row" style="' . $quiz_answer_row_style . '">
                    <td style="padding-left: 20px">Correct Answer:</td>
                    <td><input type="text" name="quiz_answer" value="' . h(prepare_form_data_for_output($quiz_answer, $type)) . '" size="30" maxlength="255" /></td>
                </tr>';
        }

        $delete_data_warning = ' and ALL SUBMITTED FORM DATA for this field';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>' . $output_form_designer_subnav_heading . '</h1>
            <div class="subheading">' . $output_form_designer_subnav_subheading . '</div>
        </div>
        <div id="content">
            
            ' . get_wysiwyg_editor_code(array('information'), $activate_editors) . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>' . $output_form_designer_content_heading . '</h1>
            <div class="subheading">' . $output_form_designer_content_subheading . '</div>
            <form action="edit_field.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <input type="hidden" id="' . h($form_type_identifier_id) . '" name="' . h($form_type_identifier_id) . '" value="' . h(${$form_type_identifier_id}) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Field Type</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 20%">Type:</td>
                        <td style="width: 80%"><select name="type" onchange="change_field_type(this.options[this.selectedIndex].value)"><option value="">-Select-</option>' .  select_field_type($type, $form_type) . '</select></td>
                    </tr>
                    <tr id="name_row_header" style="' . $name_row_style . '">
                        <th colspan="2"><h2>Field Name for Reports' . $output_rss_field_heading . '</h2></th>
                    </tr>
                    <tr id="name_row" style="' . $name_row_style . '">
                        <td>Name:</td>
                        <td><input type="text" name="name" value="' . h($name) . '" size="30" maxlength="100" /></td>
                    </tr>
                    ' . $output_rss_field_row . '
                    <tr id="label_row_header" style="' . $label_row_style . '">
                        <th colspan="2"><h2>Display Options on Custom Form Page</h2></th>
                    </tr>
                    <tr id="label_row" style="' . $label_row_style . '">
                        <td style="vertical-align: top">Label:</td>
                        <td><input type="text" name="label" value="' . h($label) . '" size="30" maxlength="255" /></td>
                    </tr>
                    <tr id="size_row" style="' . $size_row_style . '">
                        <td>Size:</td>
                        <td><input type="text" name="size" value="' . $size . '" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="maxlength_row" style="' . $maxlength_row_style . '">
                        <td>Maximum Characters:</td>
                        <td><input type="text" name="maxlength" value="' . $maxlength . '" size="3" maxlength="10" /></td>
                    <tr id="position_row" style="' . $position_row_style . '">
                        <td>Position:</td>
                        <td><select name="position">' . select_field_position($position, $_GET['id'], ${$form_type_identifier_id}, $page_type, $form_type) . '</td>
                    </tr>
                    <tr id="spacing_row" style="' . $spacing_row_style . '">
                        <td>Spacing:</td>
                        <td><input type="checkbox" name="spacing_above" id="spacing_above" value="1"' . $spacing_above_checked . ' class="checkbox" /><label for="spacing_above"> Above</label> <input type="checkbox" name="spacing_below" id="spacing_below" value="1"' . $spacing_below_checked . ' class="checkbox" /><label for="spacing_below"> Below</label></td>
                    </tr>
                    <tr id="default_value_row_header" style="' . $default_value_row_style . '">
                        <th colspan="2"><h2>Prefill Field with Value</h2></th>
                    </tr>
                    <tr id="default_value_row" style="' . $default_value_row_style . '">
                        <td>Default Value:</td>
                        <td><input type="text" id="default_value" name="default_value" value="' . h($default_value) . '" size="30" maxlength="255"' . $default_value_disabled . $default_value_class . ' /><label for="use_folder_name_for_default_value">&nbsp;&nbsp; or use Folder name: </label><input type="checkbox" name="use_folder_name_for_default_value" id="use_folder_name_for_default_value" value="1"' . $use_folder_name_for_default_value_checked . ' class="checkbox" onclick="toggle_use_folder_name_for_default_value()" /></td>
                    </tr>
                    ' . $output_contact_field_row . '
                    <tr id="required_row_header" style="' . $required_row_style . '">
                        <th colspan="2"><h2>Field is Required to Submit Form</h2></th>
                    </tr>
                    <tr id="required_row" style="' . $required_row_style . '">
                        <td>Required:</td>
                        <td><input type="checkbox" name="required" value="1"' . $required_checked . ' class="checkbox" /></td>
                    </tr>
                    ' . $output_office_use_only_row . '
                    <tr id="upload_folder_id_row_header" style="' . $upload_folder_id_row_style . '">
                        <th colspan="2"><h2>Upload from Form and put into Folder</h2></th>
                    </tr>
                    <tr id="upload_folder_id_row" style="' . $upload_folder_id_row_style . '">
                        <td>Upload to:</td>
                        <td><select name="upload_folder_id">' . select_folder($upload_folder_id) . '</select></td>
                    </tr>
                    <tr id="wysiwyg_row_header" style="' . $wysiwyg_row_style . '">
                        <th colspan="2"><h2>Enable Rich-text Editor</h2></th>
                    </tr>
                    <tr id="wysiwyg_row" style="' . $wysiwyg_row_style . '">
                        <td>Enable Rich-text Editor:</td>
                        <td><input type="checkbox" name="wysiwyg" value="1"' . $wysiwyg_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="rows_row_header" style="' . $rows_row_style . '">
                        <th colspan="2"><h2>Amount of Rows and Columns to Display</h2></th>
                    </tr>
                    <tr id="rows_row" style="' . $rows_row_style . '">
                        <td>Rows:</td>
                        <td><input type="text" name="rows" value="' . $rows . '" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="cols_row" style="' . $cols_row_style . '">
                        <td>Columns:</td>
                        <td><input type="text" name="cols" value="' . $cols . '" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="multiple_row_header" style="' . $multiple_row_style . '">
                        <th colspan="2"><h2>Allow Multiple Selection</h2></th>
                    </tr>
                    <tr id="multiple_row" style="' . $multiple_row_style . '">
                        <td>Allow Multiple Selection:</td>
                        <td><input type="checkbox" name="multiple" value="1"' . $multiple_checked . ' class="checkbox" /></td>
                    </tr>
                    ' . $output_quiz_rows . '
                    <tr id="choices_row" style="' . $choices_row_style . '">
                        <td colspan="2">
                            Choice(s):<br />
                            <table cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="vertical-align: top; padding-right: 15px">
                                        <textarea name="options" rows="8" cols="40" wrap="off">' . $output_options . '</textarea>
                                    </td>
                                    <td style="vertical-align: top; padding-right: 15px; white-space: nowrap">
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Format 1:</div>
                                        Choice 1<br />
                                        Choice 2<br />
                                        Choice 3<br />
                                        <br />
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Example 1:</div>
                                        Apple<br />
                                        Banana<br />
                                        Pear<br />
                                    </td>
                                    <td style="vertical-align: top; padding-right: 15px; white-space: nowrap">
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Format 2:</div>
                                        Label 1|Value 1<br />
                                        Label 2|Value 2<br />
                                        Label 3|Value 3<br />
                                        <br />
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Example 2:</div>
                                        Apple|apple<br />
                                        Banana|banana<br />
                                        Pear|pear<br />
                                    </td>
                                    <td style="vertical-align: top; white-space: nowrap">
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Format 3:</div>
                                        Label 1|Value 1|on/off<br />
                                        Label 2|Value 2|on/off<br />
                                        Label 3|Value 3|on/off<br />
                                        <br />
                                        <div style="border-bottom: 1px solid #666666; margin-bottom: 2px">Example 3:</div>
                                        Apple|apple|on<br />
                                        Banana|banana|off<br />
                                        Pear|pear|on<br />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr id="information_row" style="' . $information_row_style . '">
                        <td style="vertical-align: top">Information:</td>
                        <td><textarea id="information" name="information" rows="15" cols="80">' . h(prepare_rich_text_editor_content_for_output($information)) . '</textarea></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This field' . $delete_data_warning . ' will be permanently deleted.\')">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->unmark_errors();
    $liveform->clear_notices();

} else {
    validate_token_field();
    
    // if field was selected for deletion
    if ($_POST['submit_delete'] == 'Delete') {
        
        // delete field
        $query = "DELETE FROM form_fields WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // delete field options
        $query = "DELETE FROM form_field_options WHERE form_field_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Delete target options for this field.
        db("DELETE FROM target_options WHERE trigger_form_field_id = '" . escape($_POST['id']) . "'");

        db("DELETE FROM product_submit_form_fields WHERE form_field_id = '" . escape($_POST['id']) . "'");

        // If this is a custom form, then delete submitted form data for this,
        // field also, because for custom forms, we don't have a way to show
        // submitted form data without the custom form field.
        if ($form_type == 'custom') {

            // Get uploaded files for this field, in order to delete them.
            $files = db_items(
                "SELECT
                    files.id,
                    files.name
                FROM form_data
                LEFT JOIN files ON form_data.file_id = files.id
                WHERE
                    (form_data.form_field_id = '" . e($_POST['id']) . "')
                    AND (form_data.file_id != 0)
                    AND (files.id IS NOT NULL)");

            // Loop through files in order to delete record in DB and on file system.
            foreach ($files as $file) {
                db("DELETE FROM files WHERE id = '" . $file['id'] . "'");
                @unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);
                log_activity('file (' . $file['name'] . ') from a submitted form was deleted because a custom form field was deleted');
            }

            // Delete all submitted form data for this field.
            db("DELETE FROM form_data WHERE form_field_id = '" . e($_POST['id']) . "'");

        }
        
        // if this is a product form, then update last modified info for product
        if ($form_type == 'product') {
            $query = "UPDATE products
                     SET
                        user = '" . $user['id'] . "',
                        timestamp = UNIX_TIMESTAMP()
                     WHERE id = '" . escape($product_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
        // else this is a form for a page, so update last modified info for page
        } else {
            $query = "UPDATE page
                     SET
                        page_timestamp = UNIX_TIMESTAMP(),
                        page_user = '" . $user['id'] . "'
                     WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        $liveform_view_fields = new liveform('view_fields');

        // If this page type supports a custom layout, then check if this page
        // has a modified custom layout in order to determine if we should show a warning.
        if (check_if_page_type_supports_layout($page_type)) {
            $page = db_item(
                "SELECT
                    layout_type,
                    layout_modified
                FROM page
                WHERE page_id = '" . e($page_id) . "'");

            if (($page['layout_type'] == 'custom') && $page['layout_modified']) {
                $liveform_view_fields->add_warning('You might need to edit the custom layout now, because you have made changes to fields on the custom form.');
            }
        }
        
        log_activity($form_type_name . ' field (' . $_POST['name'] . ') on ' . $form_type_name . ' (' . $form_name . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_fields->add_notice('The field has been deleted.');

        $url_form_type = '';

        // If this is an express order page, then determine if we should forward to shipping
        // or billing form.
        if ($page_type == 'express order') {

            $url_form_type = '&form_type=';

            if ($form_type == 'shipping') {
                $url_form_type .= 'shipping';
            } else {
                $url_form_type .= 'billing';
            }
        }

        // forward user to view fields screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?' . $form_type_identifier_id . '=' . $_POST[$form_type_identifier_id] . $url_form_type . '&send_to=' . urlencode($_POST['send_to']));
        
    // else field was not selected for deletion
    } else {
        $name = trim($_POST['name']);
    
        // If the page name field is blank.
        if ($name == '') {
            $liveform->mark_error('name', 'The field must have a name. Please type in a name for the field.');
        }
        
        // If the name contains a special character, then output an error.
        // We do not allow most of these characters because they can create problems
        // when field variables are used on form list views and etc. (e.g. ^^example^^).
        if (
            (mb_strpos($name, '^') !== false)
            || (mb_strpos($name, '&') !== false)
            || (mb_strpos($name, '[') !== false)
            || (mb_strpos($name, ']') !== false)
            || (mb_strpos($name, '<') !== false)
            || (mb_strpos($name, '>') !== false)
            || (mb_strpos($name, '/') !== false)
        ) {
            $liveform->mark_error('name', 'The field name cannot contain the following special characters: ^ &amp; [ ] &lt; &gt; /');
        }
        
        // if there are errors in the liveform then send the user back to the add field screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_field.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        }

        $sql_upload_folder_id = "";
        
        // If this is a file upload field, then check access to selected folder,
        // and prepare to add folder info to SQL.
        if ($_POST['type'] == 'file upload') {
            // Get old upload folder, because we will always allow
            // the old upload folder to be set again, regardless of user's access to it.
            $old_upload_folder_id = db_value(
                "SELECT upload_folder_id
                FROM form_fields
                WHERE id = '" . e($_POST['id']) . "'");

            // If the upload folder that the user just selected is different
            // from the old upload folder, and the user does not have edit access
            // to the folder, then output error.
            if (
                ($_POST['upload_folder_id'] != $old_upload_folder_id)
                && (check_edit_access($_POST['upload_folder_id']) == false)
            ) {
                log_activity("access denied to set upload folder for custom form field because user does not have edit rights to folder", $_SESSION['sessionusername']);
                output_error('Access denied.');
            }

            $sql_upload_folder_id = "upload_folder_id = '" . e($_POST['upload_folder_id']) . "',";
        }
        
        // if field is an information field, then set field to be not required
        if ($_POST['type'] == 'information') {
            $required = 0;
        } else {
            $required = $_POST['required'];
        }
        
        // update field
        $query = "UPDATE form_fields
                 SET
                    name = '" . escape($name) . "',
                    rss_field = '" . escape($_POST['rss_field']) . "',
                    label = '" . escape($_POST['label']) . "',
                    type = '" . escape($_POST['type']) . "',
                    required = '" . escape($required) . "',
                    information = '" . escape(prepare_rich_text_editor_content_for_input($_POST['information'])) . "',
                    default_value = '" . escape($_POST['default_value']) . "',
                    use_folder_name_for_default_value = '" . escape($_POST['use_folder_name_for_default_value']) . "',
                    size = '" . escape($_POST['size']) . "',
                    maxlength = '" . escape($_POST['maxlength']) . "',
                    wysiwyg = '" . escape($_POST['wysiwyg']) . "',
                    `rows` = '" . escape($_POST['rows']) . "',  # Backticks for reserved word.
                    cols = '" . escape($_POST['cols']) . "',
                    multiple = '" . escape($_POST['multiple']) . "',
                    spacing_above = '" . escape($_POST['spacing_above']) . "',
                    spacing_below = '" . escape($_POST['spacing_below']) . "',
                    contact_field = '" . escape($_POST['contact_field']) . "',
                    office_use_only = '" . escape($_POST['office_use_only']) . "',
                    $sql_upload_folder_id
                    quiz_question = '" . escape($_POST['quiz_question']) . "',
                    quiz_answer = '" . escape(prepare_form_data_for_input($_POST['quiz_answer'], $_POST['type'])) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // assume that there are not any invalid e-mail addresses in options until we find out otherwise
        $invalid_email_address = FALSE;

        // Assume that there are not any invalid triggers until we find out otherwise.
        $invalid_trigger = false;

        // if field has options, deal with options
        if (($_POST['type'] == 'pick list') || ($_POST['type'] == 'radio button') || ($_POST['type'] == 'check box')) {
            // delete existing options
            $query = "DELETE FROM form_field_options WHERE form_field_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // Delete target options for this field.
            db("DELETE FROM target_options WHERE trigger_form_field_id = '" . escape($_POST['id']) . "'");
            
            $option_lines = array();
            $option_lines = explode("\n", $_POST['options']);
            
            $count = 1;
            
            foreach ($option_lines as $option_line) {
                $email_address_list = '';
                
                // if there is an e-mail address in this option line, then validate e-mail address
                if (preg_match('/\^\^(.*?)\^\^/', $option_line, $matches)) {
                    // We support multiple conditional admin email addresses, separated by comma,
                    // (e.g. ^^example1@example.com,example2@example.com^^), so deal with that.
                    $email_addresses = explode(',', $matches[1]);

                    foreach ($email_addresses as $email_address) {
                        $email_address = trim($email_address);

                        // If e-mail address is valid, then add it to the list that will be stored in db.
                        if (validate_email_address($email_address)) {
                            if ($email_address_list != '') {
                                $email_address_list .= ', ';
                            }

                            $email_address_list .= $email_address;
                        
                        // Otherwise the e-mail address is not valid,
                        // so remember that so we can tell the user.
                        } else {
                            $invalid_email_address = true;
                        }
                    }
                    
                    // remove e-mail address from option line
                    $option_line = str_replace($matches[0], '', $option_line);
                }
                
                $option_parts = array();
                $option_parts = explode('|', $option_line);
                $label = trim($option_parts[0]);

                // if a value was specifically entered for this option, use entered value for value
                if (isset($option_parts[1]) == true) {
                    $value = trim($option_parts[1]);
                    
                // else use label for value
                } else {
                    $value = $label;
                }
                
                if (mb_strtolower(trim($option_parts[2])) == 'on') {
                    $default_selected = 1;
                } else {
                    $default_selected = 0;
                }

                $target_field_id = '';
                $target_options = array();

                // If this field is a pick list and a trigger is defined, then deal with it.
                if (
                    ($_POST['type'] == 'pick list')
                    && (trim($option_parts[3]) != '')
                ) {
                    $trigger_parts = explode('=', $option_parts[3]);
                    $target_field_name = trim($trigger_parts[0]);

                    // If there is a field name, then get field id.
                    if ($target_field_name != '') {
                        $target_field_id = db_value(
                            "SELECT id
                            FROM form_fields
                            WHERE
                                ($form_type_filter)
                                AND (name = '" . e($target_field_name) . "')
                                AND (type = 'pick list')");

                        // If a target field was found for the name, then get target options.
                        if ($target_field_id != '') {
                            // Create array of target options by separating by comma.
                            $raw_target_options = explode(',', $trigger_parts[1]);

                            // Loop through the target options in order to remove white space and add options to array.
                            foreach ($raw_target_options as $target_option) {
                                $target_options[] = trim($target_option);
                            }

                            // If there are no target options, then remove trigger.
                            if (count($target_options) == 0) {
                                $target_field_id = '';
                                $invalid_trigger = true;
                            }

                        } else {
                            $target_field_id = '';
                            $invalid_trigger = true;
                        }

                    } else {
                        $invalid_trigger = true;
                    }
                }

                $upload_folder_id = '';

                // If this field is a pick list or a radio button, and an upload folder is defined
                // for this option, and a folder exists for the id, and this user has edit access to the folder,
                // then prepare to store upload folder for option.
                if (
                    (
                        ($_POST['type'] == 'pick list')
                        || ($_POST['type'] == 'radio button')
                    )
                    && (trim($option_parts[4]) != '')
                    && (db_value("SELECT COUNT(*) FROM folder WHERE folder_id = '" . escape(trim($option_parts[4])) . "'") > 0)
                    && (check_edit_access(trim($option_parts[4])) == true)
                ) {
                    $upload_folder_id = trim($option_parts[4]);
                }
                
                // create form field option
                $query = "INSERT INTO form_field_options (
                            page_id,
                            product_id,
                            form_field_id,
                            label,
                            value,
                            email_address,
                            default_selected,
                            sort_order,
                            target_form_field_id,
                            upload_folder_id)
                        VALUES (
                            '" . escape($page_id) . "',
                            '" . escape($product_id) . "',
                            '" . escape($_POST['id']) . "',
                            '" . escape($label) . "',
                            '" . escape($value) . "',
                            '" . escape($email_address_list) . "',
                            '$default_selected',
                            '$count',
                            '$target_field_id',
                            '" . escape($upload_folder_id) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $option_id = mysqli_insert_id(db::$con);

                // Loop through target options in order to add database records for them.
                foreach ($target_options as $target_option) {
                    db(
                        "INSERT INTO target_options (
                            $form_type_identifier_id,
                            trigger_form_field_id,
                            trigger_option_id,
                            value)
                        VALUES (
                            '" . e($_POST[$form_type_identifier_id]) . "',
                            '" . e($_POST['id']) . "',
                            '$option_id',
                            '$target_option')");
                }

                $count++;
            }
        }
        
        /* begin: update sort orders for fields */

        $fields = array();
        
        if ($_POST['position'] == 'top') {
            $fields[] = $_POST['id'];
        }
        
        // get all fields other than the field that is currently being edited
        $query = "SELECT id
                 FROM form_fields
                 WHERE ($form_type_filter) AND (id != '" . e($_POST['id'])  . "')
                 ORDER BY sort_order";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            // add this field to array
            $fields[] = $row['id'];
            
            // if this field is the position value, then we need to add the field that is being edited to the array
            if ($row['id'] == $_POST['position']) {
                $fields[] = $_POST['id'];
            }
        }
        
        $count = 1;
        
        // loop through all fields in order to update sort order
        foreach ($fields as $key => $field_id) {
            // update sort order for field
            $query = "UPDATE form_fields
                     SET sort_order = '$count'
                     WHERE id = '" . escape($field_id)  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $count++;
        }
        
        /* end: update sort orders for fields */

        // If this is a custom form, and RSS field was not set to title before this update,
        // and now it is set to title, and pretty URLs are enabled for custom form,
        // then update address names for submitted forms for pretty URL feature.
        if (
            ($form_type == 'custom')
            && ($old_rss_field != 'title')
            && ($_POST['rss_field'] == 'title')
            && (check_if_pretty_urls_are_enabled($page_id) == true)
        ) {
            update_multiple_submitted_form_address_names($page_id);
        }
        
        // if this is a product form, then update last modified info for product
        if ($form_type == 'product') {
            $query = "UPDATE products
                     SET
                        user = '" . $user['id'] . "',
                        timestamp = UNIX_TIMESTAMP()
                     WHERE id = '" . escape($product_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
        // else this is a form for a page, so update last modified info for page
        } else {
            $query = "UPDATE page
                     SET
                        page_timestamp = UNIX_TIMESTAMP(),
                        page_user = '" . $user['id'] . "'
                     WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        $liveform_view_fields = new liveform('view_fields');

        // If this page type supports a custom layout, then check if this page
        // has a modified custom layout in order to determine if we should show a warning.
        if (check_if_page_type_supports_layout($page_type)) {
            $page = db_item(
                "SELECT
                    layout_type,
                    layout_modified
                FROM page
                WHERE page_id = '" . e($page_id) . "'");

            if (($page['layout_type'] == 'custom') && $page['layout_modified']) {
                $liveform_view_fields->add_warning('You might need to edit the custom layout now, because you have made changes to fields on the custom form.');
            }
        }
        
        log_activity($form_type_name . ' field (' . $_POST['name'] . ') on ' . $form_type_name . ' (' . $form_name . ') was modified');
        
        $notice = 'The field has been saved.';
        
        // if there was an invalid e-mail address, then add error to the liveform
        if ($invalid_email_address == TRUE) {
            $notice .= ' However, there were one or more e-mail addresses entered for choices that were invalid, so they have been removed.';
        }

        // If there was an invalid trigger, then add error.
        if ($invalid_trigger == true) {
            // If there was also an invalid email address message then output message with certain wording.
            if ($invalid_email_address == true) {
                $notice .= ' Also, there were one or more triggers entered for choices that were invalid, so they have been removed.';
            } else {
                $notice .= ' However, there were one or more triggers entered for choices that were invalid, so they have been removed.';
            }
        }
        
        $liveform_view_fields->add_notice($notice);

        $url_form_type = '';

        // If this is an express order page, then determine if we should forward to shipping
        // or billing form.
        if ($page_type == 'express order') {

            $url_form_type = '&form_type=';

            if ($form_type == 'shipping') {
                $url_form_type .= 'shipping';
            } else {
                $url_form_type .= 'billing';
            }
        }
        
        // forward user to view fields screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?' . $form_type_identifier_id . '=' . $_POST[$form_type_identifier_id] . $url_form_type . '&send_to=' . urlencode($_POST['send_to']));
    }
}
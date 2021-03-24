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

$liveform = new liveform('add_field');

// if there is a page_id supplied in the query string, then this is a page form
if ((isset($_REQUEST['page_id'])) && ($_REQUEST['page_id'] != '')) {
    validate_area_access($user, 'user');
    
    // get page info
    $query =
        "SELECT
            page_type,
            page_folder,
            page_name
        FROM page
        WHERE page_id = '" . escape($_REQUEST['page_id']) . "'";
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

        // Express order can have a shipping and/or billing form, so check query string for the type
        // of form that we are dealing with
        case 'express order':

            if ($_REQUEST['form_type'] == 'shipping') {
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
        "form_fields." . $form_type_identifier_id . " = '" . e($_REQUEST[$form_type_identifier_id]) . "'";

    // If the page type is express order then we need to add an extra filter for the form type
    if ($page_type == 'express order') {
        $form_type_filter .=
            " AND form_fields.form_type = '" . e($form_type) . "'";
    } 
    
    // validate user's access
    if (check_edit_access($folder_id) == false) {
        log_activity('access denied to add field to ' . $form_type . ' because user does not have access to modify folder that ' . $form_type . ' is in', $_SESSION['sessionusername']);
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
        $query = "SELECT form_name" . $sql_quiz . " FROM " . str_replace(' ', '_', $page_type) . "_pages WHERE page_id = '" . escape($_REQUEST['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $form_name = $row['form_name'];
        $quiz = $row['quiz'];
    }
    
    // if form name is blank, use page name for form name
    if (!$form_name) {
        $form_name = $page_name;
    }
    
    $output_form_designer_content_heading = 'Create ' . ucwords($form_type_name) . ' Field';
    $output_form_designer_content_subheading = 'Create a new field on this ' . $form_type_name . '.';
    
// else if there is a product_id supplied in the query string, this is a product form
} elseif ((isset($_REQUEST['product_id'])) && ($_REQUEST['product_id'] != '')) {

    $form_type = 'product';
    $form_type_name = 'product form';
    $form_type_identifier_id = 'product_id';
    $form_type_filter =
        "form_fields." . $form_type_identifier_id . " = '" . e($_REQUEST[$form_type_identifier_id]) . "'";
    
    validate_ecommerce_access($user);
    
    // get product name, short description and form name to determine what we will use for the form name
    $query =
        "SELECT 
            name,
            short_description,
            form_name
        FROM products
        WHERE id = '" . escape($_REQUEST['product_id']) . "'";
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
    
    $output_form_designer_content_heading = 'Create Product Form Field';
    $output_form_designer_content_subheading = 'Create a new field on this product form.';
}

// if the form has not been submitted yet, then prepare to output form
if (!$_POST) {
    // intialize output variables
    $output_rss_field_row = '';
    $output_rss_field_heading = '';
    $output_quiz_rows = '';
    $output_upload_folder_id_row = '';
    $output_contact_field_row = '';
    $output_office_use_only_row = '';
    
    // if this is for a custom form
    if ($form_type == 'custom') {
        $output_rss_field_heading = '<span id="rss_field_heading" style="display: none"> &amp; RSS</span>';
        
        $output_rss_field_row = 
            '<tr id="rss_field_row" style="display: none">
                <td>RSS / Search Element:</td>
                <td><select name="rss_field">' . select_rss_field() . '</select></td>
            </tr>';
        
        // if quiz is enabled, then prepare to output quiz fields
        if ($quiz == 1) {
            $output_quiz_rows =
                '<tr id="quiz_question_row" style="display: none">
                    <td>Quiz Question:</td>
                    <td><input type="checkbox" name="quiz_question" id="quiz_question" value="1" class="checkbox" onclick="show_or_hide_quiz_question()" /></td>
                </tr>
                <tr id="quiz_answer_row" style="display: none">
                    <td style="padding-left: 20px">Correct Answer:</td>
                    <td><input type="text" name="quiz_answer" size="30" maxlength="255" /></td>
                </tr>';
        }
        
        $output_upload_folder_id_row = 
            '<tr id="upload_folder_id_row_header" style="display: none">
                <th colspan="2"><h2>Upload from Form and put into Folder</h2></th>
            </tr>
            <tr id="upload_folder_id_row" style="display: none">
                <td>Upload to:</td>
                <td><select name="upload_folder_id">' . select_folder($folder_id) . '</select></td>
            </tr>';
        
        $output_contact_field_row =
            '<tr id="contact_field_row_header" style="display: none">
                <th colspan="2"><h2>Prefill & Update the Submitter\'s Contact Field</h2></th>
            </tr>
            <tr id="contact_field_row" style="display: none">
                <td>Connect to Contact:</td>
                <td><select name="contact_field"><option value="">-None-</option>' . select_contact_field() . '</select></td>
            </tr>';
        
        $output_office_use_only_row = 
            '<tr id="office_use_only_row_header" style="display: none">
                <th colspan="2"><h2>Hide Field from Submitter</h2></th>
            </tr>
            <tr id="office_use_only_row" style="display: none">
                <td>Office Use Only:</td>
                <td><input type="checkbox" name="office_use_only" id="office_use_only" value="1" class="checkbox" /></td>
            </tr>';
    }
    
    // get field with largest sort order in this form, so we can prefill position field with an appropriate value
    $query = "SELECT id
             FROM form_fields
             WHERE $form_type_filter
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
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>[new field]</h1>
            <div class="subheading">' . ucwords($form_type_name) . ': ' . h($form_name) . '</div>
        </div>
        <div id="content">
            
            ' . get_wysiwyg_editor_code(array('information'), $activate_editors = false) . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>' . $output_form_designer_content_heading . '</h1>
            <div class="subheading">' . $output_form_designer_content_subheading . '</div>
            <form name="form" action="add_field.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
                <input type="hidden" id="' . $form_type_identifier_id . '" name="' . $form_type_identifier_id . '" value="' . h($_REQUEST[$form_type_identifier_id]) . '">
                <input type="hidden" name="form_type" value="' . $form_type . '">
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Field Type</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 20%">Type:</td>
                        <td style="width: 80%"><select name="type" onchange="change_field_type(this.options[this.selectedIndex].value)"><option value="">-Select-</option>' .  select_field_type('', $form_type) . '</select></td>
                    </tr>
                    <tr id="name_row_header" style="display: none">
                        <th colspan="2"><h2>New Field Name for Reports' . $output_rss_field_heading . '</h2></th>
                    </tr>
                    <tr id="name_row" style="display: none">
                        <td>Name:</td>
                        <td><input type="text" name="name" size="30" maxlength="100" /></td>
                    </tr>
                    ' . $output_rss_field_row . '
                    <tr id="label_row_header" style="display: none">
                        <th colspan="2"><h2>Display Options on Custom Form Page</h2></th>
                    </tr>
                    <tr id="label_row" style="display: none">
                        <td style="vertical-align: top">Label:</td>
                        <td><input type="text" name="label" size="30" maxlength="255" /></td>
                    </tr>
                    <tr id="size_row" style="display: none">
                        <td>Size:</td>
                        <td><input type="text" name="size" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="maxlength_row" style="display: none">
                        <td>Maximum Characters:</td>
                        <td><input type="text" name="maxlength" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="position_row" style="display: none">
                        <td>Position:</td>
                        <td><select name="position">' . select_field_position($position, '', $_REQUEST[$form_type_identifier_id], $page_type, $form_type) . '</td>
                    </tr>
                    <tr id="spacing_row" style="display: none">
                        <td>Spacing:</td>
                        <td><input type="checkbox" name="spacing_above" id="spacing_above" value="1" class="checkbox" /><label for="spacing_above"> Above</label> <input type="checkbox" name="spacing_below" id="spacing_below" value="1" class="checkbox" /><label for="spacing_below"> Below</label></td>
                    </tr>
                    <tr id="default_value_row_header" style="display: none">
                        <th colspan="2"><h2>Prefill Field with Value</h2></th>
                    </tr>
                    <tr id="default_value_row" style="display: none">
                        <td>Default Value:</td>
                        <td><input type="text" id="default_value" name="default_value" size="30" maxlength="255" /><label for="use_folder_name_for_default_value">&nbsp;&nbsp; or use Folder name: </label><input type="checkbox" name="use_folder_name_for_default_value" id="use_folder_name_for_default_value" value="1" class="checkbox" onclick="toggle_use_folder_name_for_default_value()" /></td>
                    </tr>
                    ' . $output_contact_field_row . '
                    <tr id="required_row_header" style="display: none">
                        <th colspan="2"><h2>Field is Required to Submit Form</h2></th>
                    </tr>
                    <tr id="required_row" style="display: none">
                        <td>Required:</td>
                        <td><input type="checkbox" name="required" value="1" class="checkbox" checked="checked" /></td>
                    </tr>
                    ' . $output_office_use_only_row . '
                    ' . $output_upload_folder_id_row . '
                    <tr id="wysiwyg_row_header" style="display: none">
                        <th colspan="2"><h2>Enable Rich-text Editor</h2></th>
                    </tr>
                    <tr id="wysiwyg_row" style="display: none">
                        <td>Enable Rich-text Editor:</td>
                        <td><input type="checkbox" name="wysiwyg" value="1" class="checkbox" /></td>
                    </tr>
                    <tr id="rows_row_header" style="display: none">
                        <th colspan="2"><h2>Amount of Rows and Columns to Display</h2></th>
                    </tr>
                    <tr id="rows_row" style="display: none">
                        <td>Rows:</td>
                        <td><input type="text" name="rows" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="cols_row" style="display: none">
                        <td>Columns:</td>
                        <td><input type="text" name="cols" size="3" maxlength="10" /></td>
                    </tr>
                    <tr id="multiple_row_header" style="display: none">
                        <th colspan="2"><h2>Allow Multiple Selection</h2></th>
                    </tr>
                    <tr id="multiple_row" style="display: none">
                        <td>Allow Multiple Selection:</td>
                        <td><input type="checkbox" name="multiple" value="1" class="checkbox" /></td>
                    </tr>
                    ' . $output_quiz_rows . '
                    <tr id="choices_row" style="display: none">
                        <td colspan="2">
                            Choices:<br />
                            <table cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="vertical-align: top; padding-right: 15px">
                                        <textarea name="options" rows="8" cols="40" wrap="off"></textarea>
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
                    <tr id="information_row" style="display: none">
                        <td style="vertical-align: top">Information:</td>
                        <td><textarea id="information" name="information" rows="15" cols="80"></textarea></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->unmark_errors();
    $liveform->clear_notices();
    
} else {
    validate_token_field();
    
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
    if ($liveform->check_form_errors()) {

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

        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_field.php?' . $form_type_identifier_id . '=' . urlencode($_POST[$form_type_identifier_id]) . $url_form_type . '&send_to=' . urlencode($_POST['send_to']));
        exit();
    }

    $sql_upload_folder_id_column = "";
    $sql_upload_folder_id_value = "";
    
    // if this is a custom form
    if ($form_type == 'custom') {
        // If this is a file upload field, then check access to selected folder,
        // and prepare to add folder info to SQL.
        if ($_POST['type'] == 'file upload') {
            // validate user's access to upload folder id
            if (check_edit_access($_POST['upload_folder_id']) == false) {
                log_activity("access denied to set upload folder for custom form field because user does not have edit rights to folder", $_SESSION['sessionusername']);
                output_error('Access denied.');
            }

            $sql_upload_folder_id_column = "upload_folder_id,";
            $sql_upload_folder_id_value = "'" . e($_POST['upload_folder_id']) . "',";
        }
    }
    
    // if field is an information field, then set field to be not required
    if ($_POST['type'] == 'information') {
        $required = 0;
    } else {
        $required = $_POST['required'];
    }
    
    // create form field
    $query = "INSERT INTO form_fields (
                form_type,
                " . $form_type_identifier_id . ",
                name,
                rss_field,
                label,
                type,
                required,
                information,
                default_value,
                use_folder_name_for_default_value,
                size,
                maxlength,
                wysiwyg,
                `rows`, # Backticks for reserved word.
                cols,
                multiple,
                spacing_above,
                spacing_below,
                contact_field,
                office_use_only,
                $sql_upload_folder_id_column
                quiz_question,
                quiz_answer,
                user,
                timestamp)
            VALUES (
                '" . e($form_type) . "',
                '" . escape($_POST[$form_type_identifier_id]) . "',
                '" . escape($name) . "',
                '" . escape($_POST['rss_field']) . "',
                '" . escape($_POST['label']) . "',
                '" . escape($_POST['type']) . "',
                '" . escape($required) . "',
                '" . escape(prepare_rich_text_editor_content_for_input($_POST['information'])) . "',
                '" . escape($_POST['default_value']) . "',
                '" . escape($_POST['use_folder_name_for_default_value']) . "',
                '" . escape($_POST['size']) . "',
                '" . escape($_POST['maxlength']) . "',
                '" . escape($_POST['wysiwyg']) . "',
                '" . escape($_POST['rows']) . "',
                '" . escape($_POST['cols']) . "',
                '" . escape($_POST['multiple']) . "',
                '" . escape($_POST['spacing_above']) . "',
                '" . escape($_POST['spacing_below']) . "',
                '" . escape($_POST['contact_field']) . "',
                '" . escape($_POST['office_use_only']) . "',
                $sql_upload_folder_id_value
                '" . escape($_POST['quiz_question']) . "',
                '" . escape(prepare_form_data_for_input($_POST['quiz_answer'], $_POST['type'])) . "',
                '" . $user['id'] . "',
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $form_field_id = mysqli_insert_id(db::$con);

    // assume that there are not any invalid e-mail addresses in options until we find out otherwise
    $invalid_email_address = FALSE;

    // Assume that there are not any invalid triggers until we find out otherwise.
    $invalid_trigger = false;

    // if field has options, deal with options
    if (($_POST['type'] == 'pick list') || ($_POST['type'] == 'radio button') || ($_POST['type'] == 'check box')) {
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
                        " . $form_type_identifier_id . ",
                        form_field_id,
                        label,
                        value,
                        email_address,
                        default_selected,
                        sort_order,
                        target_form_field_id,
                        upload_folder_id)
                    VALUES (
                        '" . escape($_POST[$form_type_identifier_id]) . "',
                        '$form_field_id',
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
                        '$form_field_id',
                        '$option_id',
                        '$target_option')");
            }
            
            $count++;
        }
    }

    /* begin: update sort orders for fields */

    $fields = array();
    
    if ($_POST['position'] == 'top') {
        $fields[] = $form_field_id;
    }
    
    // get all fields other than the field that is currently being edited
    $query = "SELECT id
             FROM form_fields
             WHERE ($form_type_filter) AND (id != '" . e($form_field_id)  . "')
             ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // add this field to array
        $fields[] = $row['id'];
        
        // if this field is the position value, then we need to add the field that is being edited to the array
        if ($row['id'] == $_POST['position']) {
            $fields[] = $form_field_id;
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
    
    // if this is a product form, then update last modified info for product
    if ($form_type == 'product form') {
        $query = "UPDATE products
                 SET
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                 WHERE id = '" . escape($_POST['product_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
    // else this is a form for a page, so update last modified info for page
    } else {
        $query = "UPDATE page
                 SET
                    page_timestamp = UNIX_TIMESTAMP(),
                    page_user = '" . $user['id'] . "'
                 WHERE page_id = '" . escape($_POST['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    $liveform = new liveform('view_fields');

    // If this page type supports a custom layout, then check if this page
    // has a modified custom layout in order to determine if we should show a warning.
    if (check_if_page_type_supports_layout($page_type)) {
        $page = db_item(
            "SELECT
                layout_type,
                layout_modified
            FROM page
            WHERE page_id = '" . e($_POST['page_id']) . "'");

        if (($page['layout_type'] == 'custom') && $page['layout_modified']) {
            $liveform->add_warning('You might need to edit the custom layout now, because you have made changes to fields on the custom form.');
        }
    }
    
    log_activity($form_type_name . ' field (' . $_POST['name'] . ') on ' . $form_type_name . ' (' . $form_name . ') was created', $_SESSION['sessionusername']);
    
    $notice = 'The field has been created.';
    
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
    
    $liveform->add_notice($notice);

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
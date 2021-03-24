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

// Get various properties for page that we will use in various places below.
$page = db_item(
    "SELECT
        page_id AS id,
        page_name AS name,
        page_folder AS folder_id,
        page_style AS style_id,
        mobile_style_id AS mobile_style_id
    FROM page
    WHERE page_id = '" . e($_REQUEST['page_id']) . "'");

if (!$page) {
    output_error('Sorry, the page could not be found.');
}

// validate user's access
if (check_edit_access($page['folder_id']) == false) {
    log_activity("access denied to edit form list view because user does not have access to modify folder that form list view is in", $_SESSION['sessionusername']);
    output_error('Access was denied, because you do not have access to modify the folder that the form list view is in.');
}

// get custom form page and folder, in order to validate user's access
$query = "SELECT
            form_list_view_pages.custom_form_page_id,
            page.page_folder,
            custom_form_pages.form_name
         FROM form_list_view_pages
         LEFT JOIN page ON form_list_view_pages.custom_form_page_id = page.page_id
         LEFT JOIN custom_form_pages ON form_list_view_pages.custom_form_page_id = custom_form_pages.page_id
         WHERE
            (form_list_view_pages.page_id = '" . e($_REQUEST['page_id']) . "')
            AND (form_list_view_pages.collection = 'a')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$custom_form_page_id = $row['custom_form_page_id'];
$custom_form_folder_id = $row['page_folder'];
$custom_form_name = $row['form_name'];

if (isset($custom_form_name) == true) {
    $output_custom_form_information = 'Displays submitted forms from: ' . $custom_form_name;
}

// validate user's access to custom form
if (check_edit_access($custom_form_folder_id) == false) {
    log_activity("access denied to edit form list view because user does not have access to modify folder that custom form is in", $_SESSION['sessionusername']);
    output_error('Access was denied, because you do not have access to modify the folder that the custom form is in.');
}

// Get the current style that is shown for this page for this user, so we can figure
// out the collection.  This might be the style that a designer is previewing
// or the activated style if the user is not previewing a style.
$preview_style = get_preview_style(array(
    'page_id' => $page['id'],
    'folder_id' => $page['folder_id'],
    'page_style_id' => $page['style_id'],
    'page_mobile_style_id' => $page['mobile_style_id'],
    'device_type' => $_SESSION['software']['device_type']));

// Get the collection for the style so we can show/save data for the
// right collection
$collection = db_value("SELECT collection FROM style WHERE style_id = '" . e($preview_style['id']) . "'");

// if form has not been submitted
if (!$_POST) {

    $form = new liveform('edit_form_list_view');

    // Get activated style in order to figure out if the user is editing fields
    // for a collection that is different from the activated collection.

    $activated_style = get_activated_style(array(
        'page_id' => $page['id'],
        'folder_id' => $page['folder_id'],
        'page_style_id' => $page['style_id'],
        'page_mobile_style_id' => $page['mobile_style_id'],
        'device_type' => $_SESSION['software']['device_type']));

    $activated_collection = db_value("SELECT collection FROM style WHERE style_id = '" . e($activated_style['id']) . "'");

    $collection_field_marker = '';

    // If the user is editing fields for a collection that is different from
    // the activated collection, then add warning, so user understands.
    if ($activated_collection != $collection) {

        $form->add_notice(
            'You are currently previewing a Page Style that has a different
            collection than the activated Page Style.  This means that updates to the
            collection fields marked below,
            will not affect the production Page.  Once the new Page Style is activated,
            then the updates will go live.  However, updates to any other fields will
            go live instantly.  You can find more info about collections under the Page Style help.');

        // Show marker next to collection fields, so user understands which
        // fields are collection fields.
        $collection_field_marker = ' &nbsp; <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_notice.png" alt="Notice" title=""> <span style="color: #428221">Collection Field</span>';
    }

    // Get collection A info for this form list view.  We get the collection A
    // info even if the style is set to collection B, because we only support
    // collection B for the 4 HTML/layout fields for now.
    $query =
        "SELECT
            custom_form_page_id,
            header,
            layout,
            footer,
            order_by_1_standard_field,
            order_by_1_form_field_id,
            order_by_1_type,
            order_by_2_standard_field,
            order_by_2_form_field_id,
            order_by_2_type,
            order_by_3_standard_field,
            order_by_3_form_field_id,
            order_by_3_type,
            maximum_number_of_results,
            maximum_number_of_results_per_page,
            search,
            search_label,
            search_advanced,
            search_advanced_show_by_default,
            search_advanced_layout,
            browse,
            browse_show_by_default_form_field_id,
            show_results_by_default
        FROM form_list_view_pages
        WHERE
            (page_id = '" . e($page['id']) . "')
            AND (collection = 'a')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if there is not a record for this form list view, then this is the first time it is being edited
    if (mysqli_num_rows($result) == 0) {
        $first_edit = true;
    } else {
        $first_edit = false;
    }

    $row = mysqli_fetch_assoc($result);

    $custom_form_page_id = $row['custom_form_page_id'];
    $header = $row['header'];
    $layout = $row['layout'];
    $footer = $row['footer'];
    $order_by_1_standard_field = $row['order_by_1_standard_field'];
    $order_by_1_form_field_id = $row['order_by_1_form_field_id'];
    $order_by_1_type = $row['order_by_1_type'];
    $order_by_2_standard_field = $row['order_by_2_standard_field'];
    $order_by_2_form_field_id = $row['order_by_2_form_field_id'];
    $order_by_2_type = $row['order_by_2_type'];
    $order_by_3_standard_field = $row['order_by_3_standard_field'];
    $order_by_3_form_field_id = $row['order_by_3_form_field_id'];
    $order_by_3_type = $row['order_by_3_type'];
    $maximum_number_of_results = $row['maximum_number_of_results'];
    $maximum_number_of_results_per_page = $row['maximum_number_of_results_per_page'];
    $search = $row['search'];
    $search_label = $row['search_label'];
    $search_advanced = $row['search_advanced'];
    $search_advanced_show_by_default = $row['search_advanced_show_by_default'];
    $search_advanced_layout = $row['search_advanced_layout'];
    $browse = $row['browse'];
    $browse_show_by_default_form_field_id = $row['browse_show_by_default_form_field_id'];
    $show_results_by_default = $row['show_results_by_default'];

    // If the style is set to collection b, then get page type properties for that collection.
    if ($collection == 'b') {

        $properties = get_page_type_properties($page['id'], 'form list view', 'b');

        // We only currently support collections for the 4 fields below,
        // so that is why we only override those properties for collection B.
        $header = $properties['header'];
        $layout = $properties['layout'];
        $footer = $properties['footer'];
        $search_advanced_layout = $properties['search_advanced_layout'];

    }

    // if order by is a standard field, use standard field value
    if ($order_by_1_standard_field) {
        $order_by_1 = $order_by_1_standard_field;

    // else order by is a custom field, use custom form field id
    } else {
        $order_by_1 = $order_by_1_form_field_id;
    }

    $output_order_by_1_type_style = '';

    // if order by is blank or random, then hide ascending/descending pick list
    if (($order_by_1 == '0') || ($order_by_1 == 'random')) {
        $output_order_by_1_type_style = ' style="display: none"';
    }

    // if order by is a standard field, use standard field value
    if ($order_by_2_standard_field) {
        $order_by_2 = $order_by_2_standard_field;

    // else order by is a custom field, use custom form field id
    } else {
        $order_by_2 = $order_by_2_form_field_id;
    }

    $output_order_by_2_type_style = '';

    // if order by is blank or random, then hide ascending/descending pick list
    if (($order_by_2 == '0') || ($order_by_2 == 'random')) {
        $output_order_by_2_type_style = ' style="display: none"';
    }

    // if order by is a standard field, use standard field value
    if ($order_by_3_standard_field) {
        $order_by_3 = $order_by_3_standard_field;

    // else order by is a custom field, use custom form field id
    } else {
        $order_by_3 = $order_by_3_form_field_id;
    }

    $output_order_by_3_type_style = '';

    // if order by is blank or random, then hide ascending/descending pick list
    if (($order_by_3 == '0') || ($order_by_3 == 'random')) {
        $output_order_by_3_type_style = ' style="display: none"';
    }

    // if maximum number of results is 0, then set to empty string
    if ($maximum_number_of_results == 0) {
        $maximum_number_of_results = '';
    }

    // if this is the first time that this form list view is being edited, then set default value for maximum number of results per page
    if ($first_edit == true) {
        $maximum_number_of_results_per_page = 100;
    }

    // if maximum number of results per page is 0, then set to empty string
    if ($maximum_number_of_results_per_page == 0) {
        $maximum_number_of_results_per_page = '';
    }

    // get standard fields
    $standard_fields = get_standard_fields_for_view();

    // get custom fields
    $query =
        "SELECT
            id,
            name,
            type,
            label,
            wysiwyg
        FROM form_fields
        WHERE
            (page_id = '$custom_form_page_id')
            AND (type != 'information')
            AND (name != '')
        ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $custom_fields = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $custom_fields[] = $row;
    }

    // get filters
    $query =
        "SELECT
            form_field_id,
            standard_field,
            operator,
            value,
            dynamic_value,
            dynamic_value_attribute
        FROM form_list_view_filters
        WHERE page_id = '" . escape($_GET['page_id']) . "'
        ORDER BY id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $filters = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $filters[] = $row;
    }

    $output_filters_for_javascript = '';
    $count = 0;

    // loop through filters in order to prepare output for javascript
    foreach ($filters as $filter) {
        // get field type
        $field_type = '';

        // if a standard field was selected for filter
        if ($filter['standard_field'] != '') {
            $field = $filter['standard_field'];

            // get field type by looping through standard fields, so that we know how to prepare data for output
            foreach ($standard_fields as $standard_field) {
                // if this is the standard field that was selected for this filter, then set field type and break out of loop
                if ($standard_field['value'] == $filter['standard_field']) {
                    $field_type = $standard_field['type'];
                    break;
                }
            }

        // else a custom field was selected for the filter
        } else {
            $field = $filter['form_field_id'];

            // get field type by looping through custom fields, so that we know how to prepare data for output
            foreach ($custom_fields as $custom_field) {
                // if this is the custom field that was selected for this filter, then set field type and break out of loop
                if ($custom_field['id'] == $filter['form_field_id']) {
                    $field_type = $custom_field['type'];
                    break;
                }
            }
        }

        // if dynamic value attribute is equal to 0, then set to empty string
        if ($filter['dynamic_value_attribute'] == 0) {
            $filter['dynamic_value_attribute'] = '';
        }

        $output_filters_for_javascript .=
            'filters[' . $count . '] = new Array();
            filters[' . $count . ']["field"] = "' . $field . '";
            filters[' . $count . ']["operator"] = "' . $filter['operator'] . '";
            filters[' . $count . ']["value"] = "' . escape_javascript(prepare_form_data_for_output($filter['value'], $field_type, $prepare_for_html = false)) . '";
            filters[' . $count . ']["dynamic_value"] = "' . $filter['dynamic_value'] . '";
            filters[' . $count . ']["dynamic_value_attribute"] = "' . $filter['dynamic_value_attribute'] . '";' . "\n";

        $count++;
    }

    $output_available_standard_fields = '';

    // initialize array for storing field options for filters
    $field_options = array();
    $field_options[] = array('name' => 'System Fields', 'value' => '<optgroup>');

    $output_advanced_search_available_standard_fields = '';

    // loop through all standard fields
    foreach ($standard_fields as $standard_field) {
        $output_available_standard_fields .= '^^' . h($standard_field['value']) . '^^<br />';
        $field_options[] = array('name' => $standard_field['name'], 'value' => $standard_field['value'], 'type' => $standard_field['type']);
        $output_advanced_search_available_standard_fields .= '{{name: \'' . h($standard_field['value']) . '\'}}<br />';
    }

    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    $field_options[] = array('name' => 'Form Fields', 'value' => '<optgroup>');

    $output_available_custom_fields = '';
    $output_advanced_search_available_custom_fields = '';

    // loop through all custom fields
    foreach ($custom_fields as $custom_field) {
        $output_available_custom_fields .= '^^' . h($custom_field['name']) . '^^<br />';
        $field_options[] = array('name' => $custom_field['name'], 'value' => $custom_field['id'], 'type' => $custom_field['type']);
        $output_advanced_search_available_custom_fields .= '{{name: \'' . h($custom_field['name']) . '\'}}<br />';
    }

    $field_options[] = array('name' => '', 'value' => '</optgroup>');

    $output_field_options_for_javascript = '';
    $count = 0;

    // loop through all field options in order to prepare javascript array
    foreach ($field_options as $field_option) {
        $output_field_options_for_javascript .=
            'field_options[' . $count . '] = new Array();
            field_options[' . $count . ']["name"] = "' . escape_javascript($field_option['name']) . '";
            field_options[' . $count . ']["value"] = "' . escape_javascript($field_option['value']) . '";
            field_options[' . $count . ']["type"] = "' . escape_javascript($field_option['type']) . '";' . "\n";

        // if there are value options, then add value options to javascript array
        if (isset($field_option['value_options']) == true) {
            $output_field_options_for_javascript .=
                'field_options[' . $count . ']["value_options"] = new Array();' . "\n";

            $count_2 = 0;

            // loop through value options in order to add options to javascript array
            foreach ($field_option['value_options'] as $value_option) {
                $output_field_options_for_javascript .=
                    'field_options[' . $count . ']["value_options"][' . $count_2 . '] = new Array();
                    field_options[' . $count . ']["value_options"][' . $count_2 . ']["name"] = "' . escape_javascript($value_option['name']) . '";
                    field_options[' . $count . ']["value_options"][' . $count_2 . ']["value"] = "' . escape_javascript($value_option['value']) . '";' . "\n";

                $count_2++;
            }
        }

        $count++;
    }

    $output_javascript =
        '<script>
            var last_filter_number = 0;

                var filters = new Array();

                ' . $output_filters_for_javascript . '

                var field_options = new Array();

                ' . $output_field_options_for_javascript . '

                window.onload = initialize_filters;
        </script>';

    // Put the javascript into the head of the document.
    $output_header = preg_replace('/(<\/head>)/i', $output_javascript .'$1', output_header());

    // Get MySQL version so we can know if advanced search and browse are supported.
    $query = "SELECT VERSION()";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $mysql_version = $row[0];

    $mysql_version_parts = explode('.', $mysql_version);
    $mysql_major_version = $mysql_version_parts[0];
    $mysql_minor_version = $mysql_version_parts[1];

    // Assume that MySQL version is old until we find out otherwise.
    $mysql_version_new = false;

    // If the MySQL version is at least 4.1 then remember that MySQL version is new.
    if (
        (
            ($mysql_major_version == 4)
            && ($mysql_minor_version >= 1)
        )
        || ($mysql_major_version >= 5)
    ) {
        $mysql_version_new = true;
    }

    // If the MySQL version is new then prepare hidden field so that JavaScript knows what to do.
    if ($mysql_version_new == true) {
        $output_mysql_version_new_hidden_field = '<input type="hidden" id="mysql_version_new" name="mysql_version_new" value="true" />';

    // Otherwise the MySQL version is old so prepare hidden field with value for that.
    } else {
        $output_mysql_version_new_hidden_field = '<input type="hidden" id="mysql_version_new" name="mysql_version_new" value="false" />';
    }

    $output_search_checked = '';

    // Assume that various rows of fields should be hidden until we find out otherwise.
    $output_search_label_row_style = ' style="display: none"';
    $output_search_advanced_row_style = ' style="display: none"';
    $output_search_advanced_show_by_default_row_style = ' style="display: none"';
    $output_search_advanced_layout_container_style = '; display: none';

    // If search is enabled, then check check box and determine if other rows should be shown.
    if ($search == 1) {
        $output_search_checked = ' checked="checked"';

        $output_search_label_row_style = '';

        // If the MySQL version is new then prepare advanced search.
        if ($mysql_version_new == true) {
            $output_search_advanced_row_style = '';

            // If advanced search is enabled, then show other rows.
            if ($search_advanced == 1) {
                $output_search_advanced_show_by_default_row_style = '';
                $output_search_advanced_layout_container_style = '';
            }
        }
    }

    $output_search_advanced_checked = '';

    // If advanced search is enabled, then check check box.
    if ($search_advanced == 1) {
        $output_search_advanced_checked = ' checked="checked"';
    }

    $output_search_advanced_show_by_default_checked = '';

    // If show by default is enabled, then check check box.
    if ($search_advanced_show_by_default == 1) {
        $output_search_advanced_show_by_default_checked = ' checked="checked"';
    }

    $output_browse_container_style = ' style="display: none"';

    // If the MySQL version is new then prepare browse.
    if ($mysql_version_new == true) {
        // Show browse container.
        $output_browse_container_style = '';

        $output_browse_checked = '';

        // Assume that browse rows should be hidden until we find out otherwise.
        $output_browse_show_by_default_form_field_id_row_style = ' style="display: none"';
        $output_browse_fields_row_style = ' style="display: none"';

        // If browse is enabled, then check check box and show browse rows.
        if ($browse == 1) {
            $output_browse_checked = ' checked="checked"';
            $output_browse_show_by_default_form_field_id_row_style = '';
            $output_browse_fields_row_style = '';
        }

        // Get current active browse fields so that we know which need to be checked.
        $query =
            "SELECT
                form_field_id,
                number_of_columns,
                sort_order,
                shortcut,
                date_format
            FROM form_list_view_browse_fields
            WHERE page_id = '" . escape($_GET['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $browse_fields = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $browse_fields[$row['form_field_id']] = $row;
        }

        $output_browse_show_by_default_form_field_id_options = '<option value=""></option>';
        $output_browse_field_rows = '';

        // Loop through the custom form fields in order to prepare options for show by default pick list
        // and to output an option in the browse fields for each one.
        foreach ($custom_fields as $field) {
            // If this field is not a text area or the rich-text editor is disabled for the text area,
            // then continue to output option for this field.
            if (($field['type'] != 'text area') || ($field['wysiwyg'] == 0)) {
                $output_label = $field['label'];

                // If the last character of the label if a colon, then remove the colon,
                // because the label will eventually appear in a pick list so a colon does not make sense.
                if (mb_substr($output_label, -1) == ':') {
                    $output_label = mb_substr($output_label, 0, -1);
                }

                // If the label is not blank, then continue to process this field.
                if ($output_label != '') {
                    $output_selected = '';

                    // If this field is the show by default field, then select it.
                    if ($field['id'] == $browse_show_by_default_form_field_id) {
                        $output_selected = ' selected="selected"';
                    }

                    // Add option for show by default pick list for this field.
                    $output_browse_show_by_default_form_field_id_options .= '<option value="' . $field['id'] . '"' . $output_selected . '>' . $output_label . '</option>';

                    $output_checked = '';
                    $output_shortcut_checked = '';
                    $output_number_of_columns_cell_style = '; display: none';
                    $output_sort_order_cell_style = '; display: none';
                    $output_sort_order_ascending_selected = ' selected = "selected"';
                    $output_sort_order_descending_selected = '';
                    $output_shortcut_cell_style = '; display: none';
                    $output_date_format_cell_style = 'display: none';
                    $output_date_format_field = '';

                    // If this browse field is date or date & time field then include date format field.
                    if (($field['type'] == 'date') || ($field['type'] == 'date and time')) {
                        $output_date_format_field = '<input type="text" id="browse_field_' . $field['id'] . '_date_format" name="browse_field_' . $field['id'] . '_date_format" value="' . h($browse_fields[$field['id']]['date_format']) . '" size="20" maxlength="255" />';
                    }

                    // If this field is an active browse field, then check check box.
                    if (isset($browse_fields[$field['id']]) == true) {
                        $output_checked = ' checked="checked"';

                        $output_number_of_columns_cell_style = '';
                        $output_sort_order_cell_style = '';

                        switch ($browse_fields[$field['id']]['sort_order']) {
                            case 'ascending':
                                $output_sort_order_ascending_selected = ' selected = "selected"';
                                break;

                            case 'descending':
                                $output_sort_order_descending_selected = ' selected = "selected"';
                                break;
                        }

                        $output_shortcut_cell_style = '';

                        // If shortcut is enabled, then check it.
                        if ($browse_fields[$field['id']]['shortcut'] == 1) {
                            $output_shortcut_checked = ' checked="checked"';
                        }

                        // If this browse field is date or date & time field
                        // then show date format cell.
                        if (($field['type'] == 'date') || ($field['type'] == 'date and time')) {
                            $output_date_format_cell_style = '';
                        }
                    }

                    $output_number_of_columns = $browse_fields[$field['id']]['number_of_columns'];

                    // If the number of columns is blank or 0, then set it to the default.
                    if (($output_number_of_columns == 0) || ($output_number_of_columns == '')) {
                        $output_number_of_columns = '3';
                    }

                    $output_browse_field_rows .=
                        '<tr>
                            <td style="padding-left: 0; padding-right: 2em"><input type="checkbox" name="browse_field_' . $field['id'] . '" id="browse_field_' . $field['id'] . '" value="1"' . $output_checked . ' class="checkbox"  onclick="show_or_hide_edit_form_list_view_browse_field(' . $field['id'] . ')" /><label for="browse_field_' . $field['id'] . '"> ' . $output_label . '</label></td>
                            <td id="browse_field_' . $field['id'] . '_number_of_columns_cell" style="padding-right: 2em' . $output_number_of_columns_cell_style . '">Columns: <input type="text" name="browse_field_' . $field['id'] . '_number_of_columns" value="' . $output_number_of_columns . '" size="2" maxlength="3" /></td>
                            <td id="browse_field_' . $field['id'] . '_sort_order_cell" style="padding-right: 2em' . $output_sort_order_cell_style . '">Order: <select name="browse_field_' . $field['id'] . '_sort_order"><option value="ascending"' . $output_sort_order_ascending_selected . '>Ascending</option><option value="descending"' . $output_sort_order_descending_selected . '>Descending</option></select></td>
                            <td id="browse_field_' . $field['id'] . '_shortcut_cell" style="padding-right: 2em' . $output_shortcut_cell_style . '"><label for="browse_field_' . $field['id'] . '_shortcut">Shortcut: </label><input type="checkbox" name="browse_field_' . $field['id'] . '_shortcut" id="browse_field_' . $field['id'] . '_shortcut" value="1"' . $output_shortcut_checked . ' class="checkbox" /></td>
                            <td id="browse_field_' . $field['id'] . '_date_format_cell" style="' . $output_date_format_cell_style . '">Format: ' . $output_date_format_field . '</td>
                        </tr>';
                }
            }
        }
    }

    $output_show_results_by_default_checked = '';

    // If show results by default is enabled, then check check box.
    if ($show_results_by_default == 1) {
        $output_show_results_by_default_checked = ' checked="checked"';
    }

    // We had to set the id for the header field to "header_content",
    // so CodeMirror would work, because there is already an id of "header"
    // in the control panel header.

    print $output_header . '
    <div id="subnav">
        <h1>' . h($page['name']) . '</h1>
        <div class="subheading">' . $output_custom_form_information . '</div>
    </div>
    <div id="content">
        
        ' . $form->get_messages() . '
        <a href="#" id="help_link">Help</a>
        <h1>Edit Form List View</h1>
        <div class="subheading">Update this page\'s display of data from multiple submitted forms.</div>
        <form action="edit_form_list_view.php" method="post" style="margin: 0px; padding: 0px">
            ' . get_codemirror_includes() . '
            ' . get_token_field() . '
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
            <input type="hidden" name="page_id" value="' . h($_GET['page_id']) . '" />
            <input type="hidden" id="last_filter_number" name="last_filter_number" value="0" />
            ' . $output_mysql_version_new_hidden_field . '
            <h2 style="margin-bottom: 1em">Header (code that is outputted before the list of Submitted Forms)' . $collection_field_marker . '</h2>
            <textarea id="header_content" name="header_content" style="width: 100%; height: 300px">' . h($header) . '</textarea>
            ' . get_codemirror_javascript(array('id' => 'header_content', 'code_type' => 'mixed')) . '
            <h2>All available submitted form fields for display within View</h2>
            <table class="field" style="margin-bottom: 0 !important">
                <tr>
                    <td style="vertical-align: top; padding-right: 20px">
                        <div><strong>System Fields</strong></div>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            ^^form_item_view^^<br />
                            ' . $output_available_standard_fields . '
                        </div>
                    </td>
                    <td style="vertical-align: top; padding-right: 20px">
                        <div><strong>Form Fields</strong></div>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            ' . $output_available_custom_fields . '
                        </div>
                    </td>
                    <td style="vertical-align: top">
                        <div><strong>Hints</strong></div>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            <ul style="margin-top: 0px; margin-left: 20px; padding: 0">
                                <li>Copy fields from the columns on the left and paste in the layout below.</li>
                                <li>
                                    To link to a Form Item View Page, create a link and set the link URL to ^^form_item_view^^ (e.g. &lt;a href="^^form_item_view^^"&gt;Example&lt;/a&gt;).  The system will then automatically create a URL.  If the link does not work then make sure that a Form Item View Page is set in the page properties for this Form List View.
                                </li>
                                <li>
                                    Use the following URL format to link to files and embed images:<br />
                                    {path}^^example^^
                                </li>
                                <li>
                                    Use the following format to output different content depending on whether there is a value or not:<br />
                                    [[There is a value: ^^example^^ || There is not a value]]
                                </li>
                                <li>
                                    Use the following format to customize the date format for date and date &amp; time fields.  The format can either be a <a href="http://php.net/manual/en/function.date.php" target="_blank">PHP date format</a> or "relative" for a relative time (e.g. "2 minutes ago", "2 minutes from now").<br />
                                    ^^submitted_date_and_time^^%%l, F j, Y \a\t g:i A%%<br />
                                    ^^submitted_date_and_time^^%%relative%%<br />
                                </li>
                                <li>
                                    Use the following URL format to link directly to the newest comment on a Form Item View page:<br />
                                    ^^form_item_view^^#c-^^newest_comment_id^^
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </table>
            <h2 style="margin-bottom: 1em">Display layout of submitted form data fields within View' . $collection_field_marker . '</h2>
            <textarea id="layout" name="layout" style="width: 100%; height: 300px">' . h($layout) . '</textarea>
            ' . get_codemirror_javascript(array('id' => 'layout', 'code_type' => 'mixed')) . '

            <h2 style="margin-bottom: 1em" style="margin-bottom: 1em">Footer (code that is outputted after the list of Submitted Forms)' . $collection_field_marker . '</h2>
            <textarea id="footer_content" name="footer_content" style="width: 100%; height: 300px">' . h($footer) . '</textarea>
            ' . get_codemirror_javascript(array('id' => 'footer_content', 'code_type' => 'mixed')) . '

            <h2 style="margin-bottom: .75em">Sort order of submitted form data within View </h2>
            <table class="field">
                <tr>
                    <td>Order by</td>
                    <td><select id="order_by_1" name="order_by_1" onchange="change_order_by(1)">' . select_order_by($order_by_1, $standard_fields, $custom_fields, TRUE) . '</select> <select id="order_by_1_type" name="order_by_1_type"' . $output_order_by_1_type_style . '>' . select_order_by_type($order_by_1_type) . '</select></td>
                </tr>
                <tr>
                    <td>and then by</td>
                    <td><select id="order_by_2" name="order_by_2" onchange="change_order_by(2)">' . select_order_by($order_by_2, $standard_fields, $custom_fields, FALSE) . '</select> <select id="order_by_2_type" name="order_by_2_type"' . $output_order_by_2_type_style . '>' . select_order_by_type($order_by_2_type) . '</select></td>
                </tr>
                <tr>
                    <td>and then by</td>
                    <td><select id="order_by_3" name="order_by_3" onchange="change_order_by(3)">' . select_order_by($order_by_3, $standard_fields, $custom_fields, FALSE) . '</select> <select id="order_by_3_type" name="order_by_3_type"' . $output_order_by_3_type_style . '>' . select_order_by_type($order_by_3_type) . '</select></td>
                </tr>
            </table>
            <h2 style="margin-bottom: .75em">Limit the amount of data within each View Page</h2>
            <table class="field">
                <tr>
                    <td>Maximum Number of Results:</td>
                    <td><input name="maximum_number_of_results" type="text" value="' . $maximum_number_of_results . '" size="4" maxlength="10" /> (leave blank for no limit)</td>
                </tr>
                <tr>
                    <td>Maximum Number of Results Per Page:</td>
                    <td><input name="maximum_number_of_results_per_page" type="text" value="' . $maximum_number_of_results_per_page . '" size="4" maxlength="10" /> (leave blank for no limit)</td>
                </tr>
            </table>
            <h2 style="margin-bottom: 1em">Define what submitted form data can be displayed by the View</h2>
            <div style="margin: 2em 0em"><a href="javascript:void(0)" onclick="create_filter()" class="button">Add Filter</a></div>
            <table id="filter_table" class="chart">
                <tr>
                    <th style="text-align: left">Field</th>
                    <th style="text-align: left">Operation</th>
                    <th style="text-align: left">Value</th>
                    <th style="text-align: left">Dynamic Value</th>
                    <th style="text-align: left">&nbsp;</th>
                </tr>
            </table>
            <h2 style="margin-bottom: .75em">Allow Visitor to search data</h2>
            <table class="field">
                <tr>
                    <td><label for="search">Enable Search:</label></td>
                    <td><input type="checkbox" name="search" id="search" value="1"' . $output_search_checked . ' class="checkbox" onclick="show_or_hide_edit_form_list_view_search()" /></td>
                </tr>
                <tr id="search_label_row"' . $output_search_label_row_style . '>
                    <td style="padding-left: 2em">Label:</td>
                    <td><input name="search_label" type="text" value="' . h($search_label) . '" size="30" maxlength="100" /></td>
                </tr>
                <tr id="search_advanced_row"' . $output_search_advanced_row_style . '>
                    <td style="padding-left: 2em"><label for="search_advanced">Enable Advanced Search:</label></td>
                    <td><input type="checkbox" name="search_advanced" id="search_advanced" value="1"' . $output_search_advanced_checked . ' class="checkbox" onclick="show_or_hide_edit_form_list_view_search_advanced()" /></td>
                </tr>
                <tr id="search_advanced_show_by_default_row"' . $output_search_advanced_show_by_default_row_style . '>
                    <td style="padding-left: 4em"><label for="search_advanced_show_by_default">Expand by Default:</label></td>
                    <td><input type="checkbox" name="search_advanced_show_by_default" id="search_advanced_show_by_default" value="1"' . $output_search_advanced_show_by_default_checked . ' class="checkbox" onclick="if (this.checked == true) {document.getElementById(\'browse_show_by_default_form_field_id\').selectedIndex = 0}" /></td>
                </tr>
            </table>
            <div id="search_advanced_layout_container" style="padding-left: 4em' . $output_search_advanced_layout_container_style . '">
                <h2>All available fields for advanced search</h2>
                <table class="field">
                    <tr>
                        <td style="vertical-align: top; padding-right: 20px; width: 25%">
                            <div><strong>System Fields</strong></div>
                            <div class="scrollable fields" style="height: 100px; padding: 5px; white-space: nowrap">
                                ' . $output_advanced_search_available_standard_fields . '
                            </div>
                        </td>
                        <td style="vertical-align: top; padding-right: 20px; white-space: nowrap; width: 25%">
                            <div><strong>Form Fields</strong></div>
                            <div class="scrollable fields" style="height: 100px; padding: 5px">
                                ' . $output_advanced_search_available_custom_fields . '
                            </div>
                        </td>
                        <td style="vertical-align: top; padding-right: 20px; white-space: nowrap; width: 25%">
                            <div><strong>Buttons</strong></div>
                            <div class="scrollable fields" style="height: 100px; padding: 5px">
                                {{name: \'submit_button\'}}<br />
                                {{name: \'clear_button\'}}<br />

                            </div>
                        </td>
                        <td style="vertical-align: top; width: 25%">
                            <div><strong>Hints:</strong></div>
                            <div class="scrollable" style="height: 100px; padding: 5px">
                                <ul style="margin-top: 0px; margin-left: 20px; padding: 0">
                                    <li>Copy fields and buttons from here and paste in the layout below.</li>
                                    <li>
                                        You may add additional properties to any field for further customization.  See help for available properties.
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </table>
                <h2 style="margin-bottom: 1em">Layout of fields in advanced search' . $collection_field_marker . '</h2>
                <textarea id="search_advanced_layout" name="search_advanced_layout" style="width: 100%; height: 300px">' . h($search_advanced_layout) . '</textarea>
                ' . get_codemirror_javascript(array('id' => 'search_advanced_layout', 'code_type' => 'mixed')) . '
            </div>
            <div' . $output_browse_container_style . '>
                <h2 style="margin-bottom: .75em">Allow Visitor to browse data</h2>
                <table class="field">
                    <tr>
                        <td><label for="browse">Enable Browse:</label></td>
                        <td><input type="checkbox" name="browse" id="browse" value="1"' . $output_browse_checked . ' class="checkbox" onclick="show_or_hide_edit_form_list_view_browse()" /></td>
                    </tr>
                    <tr id="browse_show_by_default_form_field_id_row"' . $output_browse_show_by_default_form_field_id_row_style . '>
                        <td style="padding-left: 2em">Expand by Default:</td>
                        <td><select id="browse_show_by_default_form_field_id" name="browse_show_by_default_form_field_id" onchange="if (this.selectedIndex != 0) {document.getElementById(\'search_advanced_show_by_default\').checked = false}">' . $output_browse_show_by_default_form_field_id_options . '</select></td>
                    </tr>
                    <tr id="browse_fields_row"' . $output_browse_fields_row_style . '>
                        <td style="padding-left: 2em">Select Fields:</td>
                        <td>
                            <table>
                                ' . $output_browse_field_rows . '
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <h2 style="margin-bottom: .75em">Show all results before Visitor filters results by browsing or searching</h2>
            <table class="field">
                <tr>
                    <td><label for="show_results_by_default">Show Results by Default:</label></td>
                    <td><input type="checkbox" name="show_results_by_default" id="show_results_by_default" value="1"' . $output_show_results_by_default_checked . ' class="checkbox" /></td>
                </tr>
            </table>
            <div class="buttons">
                <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="javascript:history.go(-1)" class="submit-secondary" />
            </div>
        </form>
    </div>' .
    output_footer();

    $form->remove();

// else form has been submitted
} else {
    validate_token_field();

    // if value of order by selection is numeric then a custom form field was selected (not a standard field)
    if (is_numeric($_POST['order_by_1']) == true) {
        $order_by_1_standard_field = '';
        $order_by_1_form_field_id = $_POST['order_by_1'];
    } else {
        $order_by_1_standard_field = $_POST['order_by_1'];
        $order_by_1_form_field_id = '';
    }

    // if value of order by selection is numeric then a custom form field was selected (not a standard field)
    if (is_numeric($_POST['order_by_2']) == true) {
        $order_by_2_standard_field = '';
        $order_by_2_form_field_id = $_POST['order_by_2'];
    } else {
        $order_by_2_standard_field = $_POST['order_by_2'];
        $order_by_2_form_field_id = '';
    }

    // if value of order by selection is numeric then a custom form field was selected (not a standard field)
    if (is_numeric($_POST['order_by_3']) == true) {
        $order_by_3_standard_field = '';
        $order_by_3_form_field_id = $_POST['order_by_3'];
    } else {
        $order_by_3_standard_field = $_POST['order_by_3'];
        $order_by_3_form_field_id = '';
    }

    // Update non-collection fields.  We stick them in collection A.
    db(
        "UPDATE form_list_view_pages
        SET
            $sql_collection_a_fields
            order_by_1_standard_field = '" . e($order_by_1_standard_field) . "',
            order_by_1_form_field_id = '" . e($order_by_1_form_field_id) . "',
            order_by_1_type = '" . e($_POST['order_by_1_type']) . "',
            order_by_2_standard_field = '" . e($order_by_2_standard_field) . "',
            order_by_2_form_field_id = '" . e($order_by_2_form_field_id) . "',
            order_by_2_type = '" . e($_POST['order_by_2_type']) . "',
            order_by_3_standard_field = '" . e($order_by_3_standard_field) . "',
            order_by_3_form_field_id = '" . e($order_by_3_form_field_id) . "',
            order_by_3_type = '" . e($_POST['order_by_3_type']) . "',
            maximum_number_of_results = '" . e($_POST['maximum_number_of_results']) . "',
            maximum_number_of_results_per_page = '" . e($_POST['maximum_number_of_results_per_page']) . "',
            search = '" . e($_POST['search']) . "',
            search_label = '" . e($_POST['search_label']) . "',
            search_advanced = '" . e($_POST['search_advanced']) . "',
            search_advanced_show_by_default = '" . e($_POST['search_advanced_show_by_default']) . "',
            browse = '" . e($_POST['browse']) . "',
            browse_show_by_default_form_field_id = '" . e($_POST['browse_show_by_default_form_field_id']) . "',
            show_results_by_default = '" . e($_POST['show_results_by_default']) . "'
        WHERE
            (page_id = '" . e($page['id']) . "')
            AND (collection = 'a')");

    // Update the collection fields for the collection that is set in the style.
    create_or_update_page_type_record('form list view', array(
        'page_id' => $page['id'],
        'collection' => $collection,
        'header' => $_POST['header_content'],
        'layout' => $_POST['layout'],
        'footer' => $_POST['footer_content'],
        'search_advanced_layout' => $_POST['search_advanced_layout']));

    // delete old filters
    $query = "DELETE FROM form_list_view_filters WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // get standard fields in order to determine field type
    $standard_fields = get_standard_fields_for_view();

    // get custom fields in order to determine field type
    $query =
        "SELECT
            id,
            type
        FROM form_fields
        WHERE page_id = '$custom_form_page_id'
        ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $custom_fields = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $custom_fields[] = $row;
    }

    // loop through all filters in order to insert filters into database
    for ($i = 1; $i <= $_POST['last_filter_number']; $i++) {
        // if filter exists and an operator was selected for this filter, then insert filter
        if ($_POST['filter_' . $i . '_operator'] != '') {
            // if field value is not numeric, then a standard field was selected
            if (is_numeric($_POST['filter_' . $i . '_field']) == false) {
                $standard_field_value = $_POST['filter_' . $i . '_field'];
                $form_field_id = 0;

                $field_type = '';

                // get field type by looping through standard fields, so that we know how to prepare data for the database
                foreach ($standard_fields as $standard_field) {
                    // if this is the standard field that was selected for this filter, then set field type and break out of loop
                    if ($standard_field['value'] == $_POST['filter_' . $i . '_field']) {
                        $field_type = $standard_field['type'];
                        break;
                    }
                }

            // else the field value is numberic, so it is a custom field
            } else {
                $standard_field_value = '';
                $form_field_id = $_POST['filter_' . $i . '_field'];

                $field_type = '';

                // get field type by looping through custom fields, so that we know how to prepare data for the database
                foreach ($custom_fields as $custom_field) {
                    // if this is the custom field that was selected for this filter, then set field type and break out of loop
                    if ($custom_field['id'] == $_POST['filter_' . $i . '_field']) {
                        $field_type = $custom_field['type'];
                        break;
                    }
                }
            }

            // if user entered a value, clear dynamic value, in order to prevent user from using two values
            if ($_POST['filter_' . $i . '_value'] != '') {
                $dynamic_value = '';
                $dynamic_value_attribute = '';
            } else {
                $dynamic_value = $_POST['filter_' . $i . '_dynamic_value'];

                // if days ago was selected for dynamic value, then set dynamic value attribute
                if ($dynamic_value == 'days ago') {
                    $dynamic_value_attribute = $_POST['filter_' . $i . '_dynamic_value_attribute'];
                } else {
                    $dynamic_value_attribute = '';
                }
            }

            // insert filter
            $query =
                "INSERT INTO form_list_view_filters (
                    page_id,
                    standard_field,
                    form_field_id,
                    operator,
                    value,
                    dynamic_value,
                    dynamic_value_attribute)
                VALUES (
                    '" . escape($_POST['page_id']) . "',
                    '" . escape($standard_field_value) . "',
                    '" . escape($form_field_id) . "',
                    '" . escape($_POST['filter_' . $i . '_operator']) . "',
                    '" . escape(prepare_form_data_for_input($_POST['filter_' . $i . '_value'], $field_type)) . "',
                    '" . escape($dynamic_value) . "',
                    '" . escape($dynamic_value_attribute) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // Delete old browse fields.
    $query = "DELETE FROM form_list_view_browse_fields WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // Loop through the custom fields in order to add active browse fields to database.
    foreach ($custom_fields as $field) {
        // If this field was checked, then add record in database.
        if ($_POST['browse_field_' . $field['id']] == 1) {
            $number_of_columns = $_POST['browse_field_' . $field['id'] . '_number_of_columns'];

            // If the number of columns is not greater or equal to 1, then set it to the default.
            // This prevents a division-by-zero error on the form list view page.
            if ((is_numeric($number_of_columns) == false) || ($number_of_columns < 1)) {
                $number_of_columns = 3;
            }

            $query =
                "INSERT INTO form_list_view_browse_fields (
                    page_id,
                    form_field_id,
                    number_of_columns,
                    sort_order,
                    shortcut,
                    date_format)
                VALUES (
                    '" . escape($_POST['page_id']) . "',
                    '" . $field['id'] . "',
                    '" . escape($number_of_columns) . "',
                    '" . escape($_POST['browse_field_' . $field['id'] . '_sort_order']) . "',
                    '" . escape($_POST['browse_field_' . $field['id'] . '_shortcut']) . "',
                    '" . escape($_POST['browse_field_' . $field['id'] . '_date_format']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // update last modified for page
    $query =
        "UPDATE page
        SET
            page_timestamp = UNIX_TIMESTAMP(),
            page_user = '" . $user['id'] . "'
        WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('page (' . $page['name'] . ') was modified', $_SESSION['sessionusername']);

    if ($_POST['send_to']) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['page_id']);
    }
}

function select_order_by($order_by = '', $standard_fields, $custom_fields, $include_random)
{
    $output_random_option = '';

    // if order by is random, then select option by default
    if ($order_by == 'random') {
        $random_option_selected = ' selected="selected"';
    } else {
        $random_option_selected = '';
    }

    $output_standard_fields = '';

    foreach ($standard_fields as $standard_field) {
        if ($standard_field['value'] == $order_by) {
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }

        $output_standard_fields .= '<option value="' . h($standard_field['value']) . '"' . $selected . '>' . h($standard_field['name']) . '</option>';
    }

    $output_custom_fields = '';

    foreach ($custom_fields as $custom_field) {
        if ($custom_field['id'] == $order_by) {
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }

        $output_custom_fields .= '<option value="' . h($custom_field['id']) . '"' . $selected . '>' . h($custom_field['name']) . '</option>';
    }
    
    // only include the random option for the first filter, otherwise it will cause problems with db queries
    if ($include_random) {
        $output_random_option = '<option value="random"' . $random_option_selected. '>Random</option>';
    }

    return
        '<option value=""></option>
        ' . $output_random_option . '
        <optgroup label="System Fields">
            ' . $output_standard_fields . '
        </optgroup>
        <optgroup label="Form Fields">
            ' . $output_custom_fields . '
        </optgroup>';
}

function select_order_by_type($type = '')
{
    $output_ascending_alphabetical_selected = '';
    $output_ascending_numerical_selected = '';
    $output_descending_alphabetical_selected = '';
    $output_descending_numercial_selected = '';

    switch ($type) {
        case 'ascending_alphabetical':
        default:
            $output_ascending_alphabetical_selected = ' selected="selected"';
            break;

        case 'ascending_numerical':
            $output_ascending_numerical_selected = ' selected="selected"';
            break;

        case 'descending_alphabetical':
            $output_descending_alphabetical_selected = ' selected="selected"';
            break;

        case 'descending_numercial':
            $output_descending_numercial_selected = ' selected="selected"';
            break;
    }

    return
        '<option value="ascending_alphabetical"' . $output_ascending_alphabetical_selected . '>Ascending (alphabetical)</option>
        <option value="descending_alphabetical"' . $output_descending_alphabetical_selected . '>Descending (alphabetical)</option>
        <option value="ascending_numerical"' . $output_ascending_numerical_selected . '>Ascending (numerical)</option>
        <option value="descending_numercial"' . $output_descending_numercial_selected . '>Descending (numerical)</option>';
}
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

function get_form_list_view($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $email = $properties['email'];

    $properties = get_page_type_properties($page_id, 'form list view');

    $custom_form_page_id = $properties['custom_form_page_id'];
    $layout = $properties['layout'];
    $order_by_1_standard_field = $properties['order_by_1_standard_field'];
    $order_by_1_form_field_id = $properties['order_by_1_form_field_id'];
    $order_by_1_type = $properties['order_by_1_type'];
    $order_by_2_standard_field = $properties['order_by_2_standard_field'];
    $order_by_2_form_field_id = $properties['order_by_2_form_field_id'];
    $order_by_2_type = $properties['order_by_2_type'];
    $order_by_3_standard_field = $properties['order_by_3_standard_field'];
    $order_by_3_form_field_id = $properties['order_by_3_form_field_id'];
    $order_by_3_type = $properties['order_by_3_type'];
    $maximum_number_of_results = $properties['maximum_number_of_results'];
    $maximum_number_of_results_per_page = $properties['maximum_number_of_results_per_page'];
    $search = $properties['search'];
    $search_label = $properties['search_label'];
    $search_advanced = $properties['search_advanced'];
    $search_advanced_show_by_default = $properties['search_advanced_show_by_default'];
    $search_advanced_layout = $properties['search_advanced_layout'];
    $browse = $properties['browse'];
    $browse_show_by_default_form_field_id = $properties['browse_show_by_default_form_field_id'];
    $show_results_by_default = $properties['show_results_by_default'];
    $form_item_view_page_id = $properties['form_item_view_page_id'];
    $viewer_filter = $properties['viewer_filter'];
    $viewer_filter_submitter = $properties['viewer_filter_submitter'];
    $viewer_filter_watcher = $properties['viewer_filter_watcher'];
    $viewer_filter_editor = $properties['viewer_filter_editor'];
    $header = $properties['header'];
    $footer = $properties['footer'];

    // If the style is set to collection b, then get page type properties for that collection.
    if (COLLECTION == 'b') {

        $properties = get_page_type_properties($page_id, 'form list view', 'b');

        // We only currently support collections for the 4 fields below,
        // so that is why we only override those properties for collection B.
        $layout = $properties['layout'];
        $search_advanced_layout = $properties['search_advanced_layout'];
        $header = $properties['header'];
        $footer = $properties['footer'];

    }

    // If search is disabled, then also mark advanced search as disabled.
    if (!$search) {
        $search_advanced = 0;
    }

    $layout_type = get_layout_type($page_id);

    $liveform = new liveform('form_list_view', $page_id);

    // get current page name (we will use this in a couple of places below)
    $current_page_name = get_page_name($page_id);

    // Get form item view page name (we use this in a couple of places below).
    $form_item_view_page_name = get_page_name($form_item_view_page_id);

    // Determine if pretty URLs are enabled for this custom form.
    $pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);
    
    // if the form list view is being prepared for an e-mail, then prepare send to in a certain way
    if ($email == TRUE) {
        $send_to = PATH . encode_url_path($current_page_name);
        
    // else the form list view is not being prepared for an e-mail, so use current request for send to
    } else {
        $send_to = REQUEST_URL;
    }
    
    // add send to value to link to form item view, if one exists, so that we can output a back button on the form item view page
    $layout = str_replace('r=^^reference_code^^', 'r=^^reference_code^^&amp;send_to=' . h(urlencode($send_to)), $layout);

    // get standard fields
    $standard_fields = get_standard_fields_for_view();

    // get all custom fields for custom form
    $query =
        "SELECT
            id,
            name,
            type,
            multiple,
            wysiwyg,
            size,
            maxlength
        FROM form_fields
        WHERE page_id = '" . escape($custom_form_page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $custom_fields = mysqli_fetch_items($result);

    $sql_viewer_filter = "";

    // If viewer filter is enabled, then prepare SQL to filter results.
    if ($viewer_filter == 1) {
        // If none of the 3 filters are enabled or viewer is not logged in,
        // then add an SQL filter that will guarantee that no results are returned.
        if (
            (
                ($viewer_filter_submitter == 0)
                && ($viewer_filter_watcher == 0)
                && ($viewer_filter_editor == 0)
            )
            || (USER_LOGGED_IN == false)
        ) {
            // Ideally, we should probably just not bother to run the query at all,
            // rather than setting a where condition that can never be true,
            // however updating all of the logic below to deal with that is too complicated for now.
            $sql_viewer_filter = "AND (TRUE = FALSE)";

        // Otherwise at least one of the 3 filters is enabled or viewer is logged in,
        // so prepare SQL filter.
        } else {
            // Assume that user does not have edit access until we find out otherwise.
            // It is important to find out of the viewer has edit access to the custom form
            // because we don't need to add filters if the editor filter is enabled and the viewer
            // has edit rights to the custom form, because then the viewer has access to all submitted forms
            // so we want to show them all.
            $edit_custom_form_access = false;

            // If editor filter is enabled, then determine if viewer has edit access to custom form.
            if ($viewer_filter_editor == 1) {
                // If this user is a manager or above, then user has edit access to custom form.
                if (USER_ROLE < 3) {
                    $edit_custom_form_access = true;

                // Otherwise this user has a user role, so check if user has edit access to custom form.
                } else {
                    // Get folder id that custom form is in, in order to check if user
                    // has edit access to custom form.
                    $custom_form_folder_id = db_value("SELECT page_folder FROM page WHERE page_id = '" . escape($custom_form_page_id) . "'");

                    // If user has edit access to custom form, then remember that.
                    if (check_edit_access($custom_form_folder_id) == true) {
                        $edit_custom_form_access = true;
                    }
                }
            }

            // If the editor filter is disabled, or if the viewer does not have edit access
            // to custom form, then it is necessary to filter the submitted forms based on who the viewer is.
            if (($viewer_filter_editor == 0) || ($edit_custom_form_access == false)) {
                $sql_viewer_filters = "";

                // If the submitter filter is enabled, then prepare SQL for forms.user_id column.
                if ($viewer_filter_submitter == 1) {
                    // We only do the forms.user_id column and not the forms.form_editor_user_id column yet,
                    // because the order of these filters are important for performance reasons.
                    $sql_viewer_filters .= "(forms.user_id = '" . USER_ID . "')";
                }

                // If the editor filter is enabled, then prepare SQL for that filter.
                if ($viewer_filter_editor == 1) {
                    // If this is not the first filter, then add an "OR" for separation.
                    if ($sql_viewer_filters != '') {
                        $sql_viewer_filters .= " OR ";
                    }

                    $sql_viewer_filters .= "(forms.form_editor_user_id = '" . USER_ID . "')";
                }

                // If the submitter filter is enabled, then determine if we need to add an SQL filter for
                // email address connect-to-contact field. This is important because we also consider that field's
                // value to be the submitter.
                if ($viewer_filter_submitter == 1) {
                    // Check if there is a connect-to-contact email address field for the custom form.
                    $submitter_field_id = db_value(
                        "SELECT id
                        FROM form_fields
                        WHERE
                            (page_id = '" . escape($custom_form_page_id) . "')
                            AND (contact_field = 'email_address')");

                    // If a submitter field was found for the custom form, then include SQL filter for it.
                    if ($submitter_field_id) {
                        // If this is not the first filter, then add an "OR" for separation.
                        if ($sql_viewer_filters != '') {
                            $sql_viewer_filters .= " OR ";
                        }

                        $sql_viewer_filters .= "((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $submitter_field_id . "') LIMIT 1) = '" . escape(USER_EMAIL_ADDRESS) . "')";
                    }
                }

                // If the watcher filter is enabled, then determine if we should prepare SQL for that filter.
                if ($viewer_filter_watcher == 1) {
                    // Determine if form item view page has comments and watchers enabled,
                    // so that we can determine if it is necessary to check if viewer is a watcher.
                    $watcher_page_id = db_value(
                        "SELECT page_id
                        FROM page
                        WHERE
                            (page_id = '" . escape($form_item_view_page_id) . "')
                            AND (page_type = 'form item view')
                            AND (comments = '1')
                            AND (comments_watcher_email_page_id != '0')");

                    // If this is not the first filter, then add an "OR" for separation.
                    if ($sql_viewer_filters != '') {
                        $sql_viewer_filters .= " OR ";
                    }

                    // If a watcher page was found, then include a check to see if viewer is a watcher.
                    // We are using EXISTS because supposedly it offers the best performance for this type of query.
                    if ($watcher_page_id) {
                        $sql_viewer_filters .=
                            "(
                                EXISTS (SELECT 1
                                FROM watchers
                                WHERE
                                    (watchers.page_id = '$watcher_page_id')
                                    AND (watchers.item_id = forms.id)
                                    AND (watchers.item_type = 'submitted_form')
                                    AND
                                    (
                                        (watchers.user_id = '" . USER_ID . "')
                                        OR (watchers.email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                    )
                                LIMIT 1)
                            )";

                    // Otherwise, a watcher page was not found, so we need to add a filter that computes to false
                    // in case this is the only filter.  We have to add this filter so that all submitted forms are not
                    // returned if this is the only filter.
                    } else {
                        $sql_viewer_filters .= "(TRUE = FALSE)";
                    }
                }

                $sql_viewer_filter = "AND ($sql_viewer_filters)";
            }
        }
    }

    $attributes = '';
    $browse_or_search_required = false;
    $browse_expanded = false;
    $browse_active = false;
    $browse_fields = array();
    $search_active = false;
    $search_advanced_content = '';
    $search_advanced_expanded = false;

    // Create array to store the advanced search fields that we will find.
    $advanced_search_fields = array();

    // Create array to store the buttons that we will find (e.g. submit & clear buttons).
    $advanced_search_buttons = array();

    // Create array that will store advanced search fields that are dynamic
    // (i.e where they should be pick list of dynamic data that filter
    // other dynamic fields when they are selected)
    $dynamic_fields = array();

    $system = '';
    $forms = array();

    // If browse or search is enabled, then output form for them.
    if (($browse == 1) || ($search == 1)) {

        $attributes = 'action="" method="get"';

        $liveform->add_fields_to_session();

        // get current URL parts in order to deal with query string parameters
        $url_parts = parse_url(REQUEST_URL);
        
        // put query string parameters into an array
        parse_str($url_parts['query'], $query_string_parameters);

        // Get the field prefix (e.g. the page id and underscore that appears before field names) and length
        // in order to determine in the loop below if a query string parameter is part of this form or not.       
        $field_prefix = $page_id . '_';
        $field_prefix_length = mb_strlen($field_prefix);

        // loop through the query string parameters in order to prepare hidden fields for each query string parameter,
        // so that we don't lose any when the form is submitted
        foreach ($query_string_parameters as $name => $value) {
            // If this parameter is not a field that appears on this form
            // and the value is not an array, then add hidden field for it
            if (
                (mb_substr($name, 0, $field_prefix_length) != $field_prefix)
                && (is_array($value) == FALSE)
            ) {
                $system .= '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '">';
            }
        }

        $search_query = '';

        // If search is enabled and the simple and advanced clear buttons have not been clicked,
        // then prepare search query. We do this in this separate logic area above the browse and search areas because
        // we need to know this information in the browse area below when determining if browse should be shown by default.
        // It will also be used in the search logic area further below.
        if (
            ($search == 1)
            && ($liveform->field_in_session($page_id . '_simple_clear') == false)
            && ($liveform->field_in_session($page_id . '_advanced_clear') == false)
        ) {
            // Set new query which has a new format for the name of the field in order to support multiple form list views on one page.
            $new_query = $liveform->get_field_value($page_id . '_query');

            // Set old query which has an old format (this is for backwards compatibility)
            $old_query = $liveform->get_field_value('query');

            // If there is a specific query for this page, then use it
            if ($new_query != '') {
                $search_query = $new_query;
                
            // else if there is an old query, then use it.
            } else if ($old_query != '') {
                $search_query = $old_query;
            }
        }

        $liveform->set($page_id . '_query', $search_query);

        // Assume that browse is disabled until we find out otherwise.
        // We will only consider browse fully enabled if
        // there is at least one browse field with at least one filter.
        // This is why we can't use $browse to simple check if browse is enabled later.
        $browse_enabled = false;

        // If browse is enabled, then continue to determine if we need to prepare browse feature.
        if ($browse) {

            // If browse should be shown by default,
            // and a browse field has not been selected by the visitor,
            // and there is not a simple search,
            // and there is not an advanced search,
            // then set default browse field so that it is shown by default.
            if (
                ($browse_show_by_default_form_field_id != 0)
                && ($liveform->field_in_session($page_id . '_browse_field_id') == false)
                && ($search_query == '')
                &&
                (
                    ($search == 0)
                    || ($search_advanced == 0)
                    || ($liveform->get_field_value($page_id . '_advanced') != 'true')
                )
            ) {
                $liveform->assign_field_value($page_id . '_browse_field_id', $browse_show_by_default_form_field_id);
            }

            // Get browse fields that have a label and are not information or rich-text editor fields.
            // Even though we weed those types of fields out when they add the browse fields on the edit form list view screen,
            // we have to do this check here, because the state of the fields might have changed since then.
            $browse_fields = db_items(
                "SELECT
                    form_list_view_browse_fields.form_field_id AS id,
                    form_list_view_browse_fields.number_of_columns,
                    form_list_view_browse_fields.sort_order,
                    form_list_view_browse_fields.shortcut,
                    form_list_view_browse_fields.date_format,
                    form_fields.label,
                    form_fields.type,
                    form_fields.multiple
                FROM form_list_view_browse_fields
                LEFT JOIN form_fields ON form_list_view_browse_fields.form_field_id = form_fields.id
                WHERE
                    (form_list_view_browse_fields.page_id = '" . escape($page_id) . "')
                    AND (form_fields.label != '')
                    AND (form_fields.type != 'information')
                    AND 
                    (
                        (form_fields.type != 'text area')
                        OR (form_fields.wysiwyg != '1')
                    )
                ORDER BY form_fields.sort_order");

        }

        // If advanced search is enabled, then get advanced search fields in
        // order to figure out dynamic fields that we need to get submitted
        // form data for.
        if ($search_advanced) {

            // Create array to store the count of each advanced search field
            // that we find so that we can create a unique HTML field name if
            // there are multiple advanced search fields for the same field.
            $field_count = array();

            // Start out by setting the advanced search content to the initial layout.
            $search_advanced_content = $search_advanced_layout;

            // Get all variables so they can be replaced with fields.
            preg_match_all('/{({.*?})}/', $search_advanced_content, $variables, PREG_SET_ORDER);

            // include JSON library in order to convert JSON string to PHP array
            include_once('JSON.php');
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

            // Loop through the variables in order to get advanced filter fields.
            foreach ($variables as $variable) {

                $advanced_search_field = array();

                $placeholder = $variable[0];

                // The JSON for the field was entered via the rich-text editor, so convert HTML entities to normal characters.
                $field_json = unhtmlspecialchars($variable[1]);

                // Create PHP array for field from JSON string.
                $field = $json->decode($field_json);

                // Convert field name to lower case for comparisons later.
                $field['name'] = mb_strtolower($field['name']);

                // If this field is a button, then add to button array.
                if (
                    ($field['name'] == 'submit_button')
                    || ($field['name'] == 'clear_button')
                ) {
                    $advanced_search_buttons[] = array(
                        'name' => $field['name'],
                        'placeholder' => $placeholder,
                        'label' => $field['label'],
                        'class' => $field['class']
                    );

                // Otherwise this field is a normal field, so add it to field array.
                } else {
                    // Assume that the variable is not valid, until we find out otherwise.
                    $variable_valid = FALSE;

                    // loop through the standard fields in order to determine
                    // if the field name is valid and to get field info
                    foreach ($standard_fields as $standard_field) {
                        // if this standard field value matches the field name, then the field name is valid,
                        // so remember that, store field info, and break out of the loop
                        if ($standard_field['value'] == $field['name']) {
                            $field_count[$field['name']]++;

                            $advanced_search_field = array(
                                'name' => $field['name'],
                                'group' => 'standard',
                                'type' => $standard_field['type'],
                                'sql_name' => $standard_field['sql_name'],
                                'placeholder' => $placeholder,
                                'operator' => $field['operator'],
                                'size' => $field['size'],
                                'class' => $field['class'],
                                'help' => $field['help'],
                                'dynamic' => $field['dynamic'],
                                'sort_order' => $field['sort_order']
                            );

                            $variable_valid = TRUE;

                            break;
                        }
                    }

                    // if we don't know if the field name is valid yet, then loop
                    // through the custom fields in order to check if the field name
                    // is a valid custom field and to get field info
                    if ($variable_valid == FALSE) {
                        foreach ($custom_fields as $custom_field) {
                            // if this custom field name matches the field name,
                            // and the field is not an information field, then the field name is valid,
                            // so remember that, store field info, and break out of loop
                            if (
                                (mb_strtolower($custom_field['name']) == $field['name'])
                                && ($custom_field['type'] != 'information')
                            ) {
                                $field_count[$field['name']]++;

                                $size = '';

                                // If a size was passed as a property in the JSON, then use it.
                                if ($field['size']) {
                                    $size = $field['size'];

                                // Otherwise a size was not passed in the JSON,
                                // so if this is a custom field that has a size property, and it is not 0, then use it.
                                } else if (
                                    (
                                        ($custom_field['type'] == 'text box')
                                        || ($custom_field['type'] == 'pick list')
                                        || ($custom_field['type'] == 'file upload')
                                        || ($custom_field['type'] == 'date')
                                        || ($custom_field['type'] == 'date and time')
                                        || ($custom_field['type'] == 'email address')
                                        || ($custom_field['type'] == 'time')
                                    )
                                    && ($custom_field['size'] != 0)
                                ) {
                                    $size = $custom_field['size'];
                                }

                                $advanced_search_field = array(
                                    'name' => $field['name'],
                                    'group' => 'custom',
                                    'type' => $custom_field['type'],
                                    'id' => $custom_field['id'],
                                    'placeholder' => $placeholder,
                                    'operator' => $field['operator'],
                                    'size' => $size,
                                    'maxlength' => $custom_field['maxlength'],
                                    'multiple' => $custom_field['multiple'],
                                    'class' => $field['class'],
                                    'help' => $field['help'],
                                    'dynamic' => $field['dynamic'],
                                    'sort_order' => $field['sort_order']
                                );

                                break;
                            }
                        }
                    }
                }

                // If an advanced search field was found, then add it to arrays.
                if ($advanced_search_field) {

                    $advanced_search_fields[] = $advanced_search_field;

                    if ($advanced_search_field['dynamic']) {
                        $dynamic_fields[$advanced_search_field['name']] = $advanced_search_field;
                    }

                }

            }

        }

        // If there is a browse field or dynamic advanced search field,
        // then get submitted form data in order to populate those features.
        // Dynamic advanced search fields are pick lists of submitted form data
        // that filter options in other dynamic fields.
        if ($browse_fields or $dynamic_fields) {

            // Assume that we don't need to join various tables until we find
            // out otherwise.
            $submitter_join_required = false;
            $last_modifier_join_required = false;
            $submitted_form_info_join_required = false;
            $newest_comment_join_required = false;
            $newest_comment_submitter_join_required = false;

            // Prepare all the fields that need to be selected when getting the
            // submitted form data.  This includes browse fields and dynamic fields.

            $select_fields = array();

            // We always select the id, reference code and address name, because we
            // normally need them, so add them first.

            $select_fields['id'] = array(
                'group' => 'standard',
                'name' => 'id',
                'sql_name' => 'forms.id',
                'type' => '');

            $select_fields['reference_code'] = array(
                'group' => 'standard',
                'name' => 'reference_code',
                'sql_name' => 'forms.reference_code',
                'type' => '');

            $select_fields['address_name'] = array(
                'group' => 'standard',
                'name' => 'address_name',
                'sql_name' => 'forms.address_name',
                'type' => '');

            // Loop through browse fields, in order to prepare selects.
            foreach ($browse_fields as $field) {

                // If this field has not already been added as a select field,
                // then add it.
                if (!isset($select_fields[$field['id']])) {
                    $select_fields[$field['id']] = array(
                        'group' => 'custom',
                        'id' => $field['id'],
                        'type' => $field['type'],
                        'multiple' => $field['multiple']);
                }

            }

            // Loop through dynamic fields, in order to prepare selects.
            foreach ($dynamic_fields as $field) {

                // If this is a standard field, then handle it in a certain way.
                if ($field['group'] == 'standard') {

                    // If this field has not already been added as a select field,
                    // then add it.
                    if (!isset($select_fields[$field['name']])) {
                        $select_fields[$field['name']] = array(
                            'group' => 'standard',
                            'name' => $field['name'],
                            'sql_name' => $field['sql_name'],
                            'type' => $field['type']);
                    }

                // Otherwise, this is a custom field, so handle it in a different way.
                } else {

                    // If this field has not already been added as a select field,
                    // then add it.
                    if (!isset($select_fields[$field['id']])) {
                        $select_fields[$field['id']] = array(
                            'group' => 'custom',
                            'id' => $field['id'],
                            'type' => $field['type'],
                            'multiple' => $field['multiple']);
                    }

                }

            }

            $sql_select = "";

            // Loop through select fields, in order to prepare selects.
            foreach ($select_fields as $field) {

                // If this is not the first select field, then add comma for separation.
                if ($sql_select != '') {
                    $sql_select .= ",";
                }

                // If this is a standard field, then handle it in a certain way.
                if ($field['group'] == 'standard') {

                    $sql_select .= $field['sql_name'] . " AS " . $field['name'];

                    // Figure out if certain joins are required for this field.
                    switch ($field['name']) {
                        case 'submitter':
                            $submitter_join_required = true;
                            break;

                        case 'last_modifier':
                            $last_modifier_join_required = true;
                            break;

                        case 'number_of_views':
                        case 'number_of_comments':
                        case 'comment_attachments':
                            $submitted_form_info_join_required = true;
                            break;

                        case 'newest_comment_name':
                            $submitted_form_info_join_required = true;
                            $newest_comment_join_required = true;
                            $newest_comment_submitter_join_required = true;
                            break;

                        case 'newest_comment':
                        case 'newest_comment_date_and_time':
                        case 'newest_comment_id':
                        case 'newest_activity_date_and_time':
                            $submitted_form_info_join_required = true;
                            $newest_comment_join_required = true;
                            break;
                    }

                // Otherwise, this is a custom field, so handle it in a different way.
                } else {

                    // If this field is a pick list and allow multiple selection is enabled,
                    // or if this field is a check box, then select field in a certain way,
                    // so that multiple values may be retrieved.  We don't just use this type of query
                    // for all types of fields because it is probably slightly slower.
                    if (
                        (
                            ($field['type'] == 'pick list')
                            && ($field['multiple'] == 1)
                        )
                        || ($field['type'] == 'check box')
                    ) {
                        $sql_select .= "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR '||') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "')) AS field_" . $field['id'];

                    // else if this field is a file upload field, then select file name
                    } else if ($field['type'] == 'file upload') {
                        $sql_select .= "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') LIMIT 1) AS field_" . $field['id'];

                    // else this field has any other type, so select field in the default way
                    } else {
                        $sql_select .= "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') LIMIT 1) AS field_" . $field['id'];
                    }

                }

            }

            $sql_where = "";

            // Get filters in order to prepare SQL for where clause.
            // We order by form_field_id ASC in order to get filters for standard
            // fields first, in order to improve performance.
            $filters = db_items(
                "SELECT
                    form_field_id,
                    standard_field,
                    operator,
                    value,
                    dynamic_value,
                    dynamic_value_attribute
                FROM form_list_view_filters
                WHERE page_id = '" . e($page_id) . "'
                ORDER BY form_field_id ASC");
            
            // loop through all filters in order to prepare SQL
            foreach ($filters as $filter) {
                // get operand 1
                $operand_1 = '';
                
                // if a standard field was selected for filter
                if ($filter['standard_field'] != '') {
                    // determine if a join is required based on the field
                    switch ($filter['standard_field']) {
                        case 'submitter':
                            $submitter_join_required = TRUE;
                            break;

                        case 'last_modifier':
                            $last_modifier_join_required = TRUE;
                            break;

                        case 'number_of_views':
                        case 'number_of_comments':
                        case 'comment_attachments':
                            $submitted_form_info_join_required = TRUE;
                            break;

                        case 'newest_comment_name':
                        case 'newest_comment':
                        case 'newest_comment_date_and_time':
                        case 'newest_comment_id':
                        case 'newest_activity_date_and_time':
                            $submitted_form_info_join_required = TRUE;
                            $newest_comment_join_required = TRUE;
                            break;
                    }

                    // get operand 1 which is sql name by looping through standard fields
                    foreach ($standard_fields as $standard_field) {
                        // if this is the standard field that was selected for this filter, then set operand 1 and break out of loop
                        if ($standard_field['value'] == $filter['standard_field']) {
                            $operand_1 = $standard_field['sql_name'];
                            break;
                        }
                    }
                    
                // else a custom field was selected for the filter
                } else {
                    // Assume that the custom field for this filter is not valid, until we find out otherwise.
                    // A custom field for a filter is valid if the custom field exists on the custom form for this form list view.
                    $custom_field_is_valid = FALSE;
                    
                    // get field type and determine if field is valid by looping through custom fields
                    foreach ($custom_fields as $custom_field) {
                        // if this is the custom field that was selected for this filter,
                        // then we know the custom field is valid, so break out of loop
                        if ($custom_field['id'] == $filter['form_field_id']) {
                            $field_type = $custom_field['type'];
                            $field_multiple = $custom_field['multiple'];
                            $custom_field_is_valid = TRUE;
                            break;
                        }
                    }
                    
                    // If the custom field for this filter is not valid, then do not prepare SQL filter and skip to the next filter.
                    // We have to do this in order to prevent a database error.
                    if ($custom_field_is_valid == FALSE) {
                        continue;
                    }

                    // If this custom field is a pick list and allow multiple selection is enabled,
                    // or if this custom field is a check box, then prepare operand in a certain way,
                    // because there might be multiple values.
                    if (
                        (
                            ($field_type == 'pick list')
                            && ($field_multiple == 1)
                        )
                        || ($field_type == 'check box')
                    ) {
                        // If the operator is "is equal to" or "is not equal to" then prepare special filter in a way
                        // so that it looks at individual values instead of a string of grouped values.
                        if (
                            ($filter['operator'] == 'is equal to')
                            || ($filter['operator'] == 'is not equal to')
                        ) {
                            $operand_1 = "(SELECT COUNT(*) FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') AND (form_data.data = '" . escape($filter['value']) . "'))";

                            // If the operator is "is equal to", then change operator to "is greater than",
                            // because of the special comparison we are using.
                            if ($filter['operator'] == 'is equal to') {
                                $filter['operator'] = 'is greater than';

                            // Otherwise the operator is "is not equal to", so change operator to "is equal to",
                            // because of the special comparison we are using.
                            } else {
                                $filter['operator'] = 'is equal to';
                            }

                            // Change the filter value to 0 in order for our special comparison to work.
                            $filter['value'] = '0';

                        // Otherwise the operator is a different operator, so prepare operand 1,
                        // so that multiple values are grouped into one concatenated string, separated by commas.
                        } else {
                            $operand_1 = "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "'))";
                        }

                    // else if this custom field is a file upload field, then prepare operand so it contains file name
                    } else if ($field_type == 'file upload') {
                        $operand_1 = "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";

                    // else this custom field has any other type, so prepare operand in the default way
                    } else {
                        $operand_1 = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";
                    }
                }
                
                // if a basic value was entered, use that value
                if ($filter['value'] != '') {
                    $operand_2 = $filter['value'];
                    
                // else a dynamic value was entered, so use dynamic value
                } else {
                    $operand_2 = get_dynamic_value($filter['dynamic_value'], $filter['dynamic_value_attribute']);
                }
                
                $sql_where .= " AND (" . prepare_sql_operation($filter['operator'], $operand_1, $operand_2) . ")";
            }

            $sql_join = "";

            // if a submitter join is required, then add join
            if ($submitter_join_required == TRUE) {
                $sql_join .= "LEFT JOIN user AS submitter ON forms.user_id = submitter.user_id\n";
            }

            // if a last modifier join is required, then add join
            if ($last_modifier_join_required == TRUE) {
                $sql_join .= "LEFT JOIN user AS last_modifier ON forms.last_modified_user_id = last_modifier.user_id\n";
            }

            // if a submitted form comment info join is required, then add join
            if ($submitted_form_info_join_required == TRUE) {
                $sql_join .= "LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_item_view_page_id . "'))\n";
            }

            // if a newest comment join is required, then add join
            if ($newest_comment_join_required == TRUE) {
                $sql_join .= "LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id\n";
            }

            // if a newest comment submitter join is required, then add join
            if ($newest_comment_submitter_join_required == TRUE) {
                $sql_join .= "LEFT JOIN user AS newest_comment_submitter ON newest_comment.created_user_id = newest_comment_submitter.user_id\n";
            }

            // get submitted forms
            $forms = db_items(
                "SELECT $sql_select
                FROM forms
                $sql_join
                WHERE
                    (forms.page_id = '" . e($custom_form_page_id) . "')
                    " . $sql_where .
                    $sql_viewer_filter);

            // Prepare to store filters for browse fields.
            foreach ($browse_fields as $key => $field) {
                $browse_fields[$key]['filters'] = array();
                $browse_fields[$key]['filters_for_sorting'] = array();
            }

            // Prepare to store filters for dynamic fields.
            foreach ($dynamic_fields as $key => $field) {
                $dynamic_fields[$key]['filters'] = array();
                $dynamic_fields[$key]['filters_for_sorting'] = array();
            }

            // Loop through submitted forms in order to prepare browse and dynamic filters.
            foreach ($forms as $form) {

                // Loop through browse fields, in order to prepare browse filters.
                foreach ($browse_fields as $key => $field) {

                    $browse_filter = $form['field_' . $field['id']];

                    // If the filter is not blank, then continue to check if it should be added to the array.
                    if ($browse_filter != '') {
                        // If this field is a pick list and allow multiple selection is enabled,
                        // or if this field is a check box, and a separator exists in the filter,
                        // then this filter contains multiple values, so process it in a certain way.
                        if (
                            (
                                (
                                    ($field['type'] == 'pick list')
                                    && ($field['multiple'] == 1)
                                )
                                || ($field['type'] == 'check box')
                            )
                            && (mb_strpos($browse_filter, '||') !== false)
                        ) {
                            $browse_filters = explode('||', $browse_filter);
                            
                            // Loop through all the filters for this field in order to add them to array.
                            foreach ($browse_filters as $browse_filter) {
                                $browse_filter_lowercase = mb_strtolower($browse_filter);

                                // If this filter has not already been added, then add it to arrays.
                                if (isset($browse_fields[$key]['filters'][$browse_filter_lowercase]) == false) {
                                    $browse_fields[$key]['filters'][$browse_filter_lowercase] = array(
                                        'name' => $browse_filter,
                                        'count' => 1,
                                        'reference_code' => $form['reference_code'],
                                        'address_name' => $form['address_name']);

                                    $browse_fields[$key]['filters_for_sorting'][] = $browse_filter_lowercase;

                                // Otherwise this filter has already been added, so increase count.
                                } else {
                                    $browse_fields[$key]['filters'][$browse_filter_lowercase]['count']++;
                                }
                            }

                        // Otherwise this filter has only one value so process it in a different way.
                        } else {
                            // Prepare a lowercase version of the browse filter that we will use for sorting.
                            $browse_filter_for_sorting = mb_strtolower($browse_filter);

                            // If this browse field is a date or date and time field,
                            // and this browse field has a date format, then update the browse filter
                            // so it matches the date format (e.g. "May 2020").  Notice how we leave
                            // $browse_filter_for_sorting set to the default date format (e.g. 2020-05-23)
                            // above so that we can still sort dates correctly.
                            if (
                                (
                                    ($field['type'] == 'date')
                                    || ($field['type'] == 'date and time')
                                )
                                && ($field['date_format'] != '')
                            ) {
                                $browse_filter = date($field['date_format'], strtotime($browse_filter));
                            }

                            $browse_filter_lowercase = mb_strtolower($browse_filter);

                            // If this filter has not already been added, then add it to arrays.
                            if (isset($browse_fields[$key]['filters'][$browse_filter_lowercase]) == false) {
                                $browse_fields[$key]['filters'][$browse_filter_lowercase] = array(
                                    'name' => $browse_filter,
                                    'count' => 1,
                                    'reference_code' => $form['reference_code'],
                                    'address_name' => $form['address_name']);

                                $browse_fields[$key]['filters_for_sorting'][] = $browse_filter_for_sorting;

                            // Otherwise this filter has already been added, so increase count.
                            } else {
                                $browse_fields[$key]['filters'][$browse_filter_lowercase]['count']++;
                            }
                        }
                    }

                }

                // Loop through dynamic fields, in order to prepare filters.
                foreach ($dynamic_fields as $key => $field) {

                    // If this is a standard field, then handle it in a certain way.
                    if ($field['group'] == 'standard') {

                        $filter = $form[$field['name']];

                    // Otherwise, this is a custom field, so handle it in a different way.
                    } else {

                        $filter = $form['field_' . $field['id']];

                    }

                    // If the filter is blank, then skip to the next dynamic field.
                    if ($filter == '') {
                        continue;
                    }

                    // If this is a custom field and it is a pick list and allow
                    // multiple selection is enabled, or if this field is a check box,
                    // and a separator exists in the filter, then this filter
                    // contains multiple values, so process it in a certain way.
                    if (
                        ($field['group'] == 'custom')
                        &&
                        (
                            (
                                ($field['type'] == 'pick list')
                                && ($field['multiple'] == 1)
                            )
                            || ($field['type'] == 'check box')
                        )
                        && (mb_strpos($filter, '||') !== false)
                    ) {
                        $dynamic_filters = explode('||', $filter);
                        
                        // Loop through all the filters for this field in order to add them to array.
                        foreach ($dynamic_filters as $filter) {

                            $filter_lowercase = mb_strtolower($filter);

                            // If this filter has not already been added, then add it to arrays.
                            if (isset($dynamic_fields[$key]['filters'][$filter_lowercase]) == false) {

                                $dynamic_fields[$key]['filters'][$filter_lowercase] = array(
                                    'name' => $filter,
                                    'count' => 1,
                                    'reference_code' => $form['reference_code'],
                                    'address_name' => $form['address_name']);

                                $dynamic_fields[$key]['filters_for_sorting'][] = $filter_lowercase;

                            // Otherwise this filter has already been added, so increase count.
                            } else {
                                $dynamic_fields[$key]['filters'][$filter_lowercase]['count']++;
                            }

                            // Remember that this filter is related to this form,
                            // so JS can dynamically update options in the pick list.
                            $dynamic_fields[$key]['filters'][$filter_lowercase]['forms'][] = $form['id'];

                        }

                    // Otherwise this filter has only one value so process it in a different way.
                    } else {

                        // Prepare a lowercase version of the browse filter that we will use for sorting.
                        $filter_for_sorting = mb_strtolower($filter);

                        // Convert filter to output format, so dates and etc. will appear
                        // as visitors expect them instead of db values.
                        $filter = prepare_form_data_for_output($filter, $field['type'], $prepare_for_html = false);

                        $filter_lowercase = mb_strtolower($filter);

                        // If this filter has not already been added, then add it to arrays.
                        if (isset($dynamic_fields[$key]['filters'][$filter_lowercase]) == false) {
                            $dynamic_fields[$key]['filters'][$filter_lowercase] = array(
                                'name' => $filter,
                                'count' => 1,
                                'reference_code' => $form['reference_code'],
                                'address_name' => $form['address_name']);

                            $dynamic_fields[$key]['filters_for_sorting'][] = $filter_for_sorting;

                        // Otherwise this filter has already been added, so increase count.
                        } else {
                            $dynamic_fields[$key]['filters'][$filter_lowercase]['count']++;
                        }

                        // Remember that this filter is related to this form,
                        // so JS can dynamically update options in the pick list.
                        $dynamic_fields[$key]['filters'][$filter_lowercase]['forms'][] = $form['id'];

                    }
                    
                }

            }

            // Loop through browse fields in order to remove fields that have
            // no filters and to sort filters array.
            foreach ($browse_fields as $key => $field) {

                // If this browse field has at least one filter, then sort them and remember this.
                if ($field['filters']) {

                    switch ($field['sort_order']) {
                        case 'ascending':
                            $sort_order = SORT_ASC;
                            break;

                        case 'descending':
                            $sort_order = SORT_DESC;
                            break;
                    }

                    // Use filters for sorting array in order to sort the filters array.
                    array_multisort($browse_fields[$key]['filters_for_sorting'], $sort_order, $browse_fields[$key]['filters']);

                    // Remove filters for sorting array, because it is no longer needed.
                    unset($browse_fields[$key]['filters_for_sorting']);

                    // Remember that browse is fully enabled, because now we know there is at least
                    // one field with at least one filter.  We will use this later.
                    $browse_enabled = true;

                // Otherwise this browse field does not have any filters, so remove field,
                // because there is no point in it appearing in the pick list.
                } else {
                    unset($browse_fields[$key]);
                }

            }

            // Loop through dynamic fields in order to sort them.
            foreach ($dynamic_fields as $key => $field) {

                // If this field does not have a filter, then skip to next field.
                if (!$field['filters']) {
                    continue;
                }

                // Prepare the type of ordering that we will do.
                switch ($field['sort_order']) {
                    default:
                    case 'ascending':
                        $sort_order = SORT_ASC;
                        break;

                    case 'descending':
                        $sort_order = SORT_DESC;
                        break;
                }

                // Use filters for sorting array in order to sort the filters array.
                // We order the filters here first, even for fields, like pick lists,
                // that will be ordered below based on the option order in the custom form,
                // because we want filters that no longer exist as options,
                // to be ordered at the bottom with some default alphabetical order.
                array_multisort($dynamic_fields[$key]['filters_for_sorting'], $sort_order, $dynamic_fields[$key]['filters']);

                // Remove filters for sorting array, because it is no longer needed.
                unset($dynamic_fields[$key]['filters_for_sorting']);

                // If this is a type of field that has options set in a specific
                // order in the custom form, then use that order of options
                // to order the filters.
                if (
                    ($field['group'] == 'custom')
                    and
                    (
                        ($field['type'] == 'pick list')
                        or ($field['type'] == 'radio button')
                        or ($field['type'] == 'check box')
                    )
                ) {

                    $ordered_filters = array();

                    // Get the options for the field in the order that they appear
                    // on the custom form.
                    $options = db_values(
                        "SELECT value
                        FROM form_field_options
                        WHERE form_field_id = '" . $field['id'] . "'
                        ORDER BY sort_order");

                    // Loop through the options to prepare ordered filters.
                    foreach ($options as $option) {

                        $option = mb_strtolower($option);
                        
                        // If a filter exists for this option, then add filter
                        // as an ordered filter.
                        if ($dynamic_fields[$key]['filters'][$option]) {
                            $ordered_filters[$option] = $dynamic_fields[$key]['filters'][$option];
                        }

                    }

                    // Loop through the filters in order to add filters to the
                    // ordered filters array, for filters that did not exist as an option.
                    // For example, this might happen if an option existed for
                    // a pick list on the custom form, forms were submitted with that option,
                    // and then the option was removed from the pick list.
                    foreach ($dynamic_fields[$key]['filters'] as $key_2 => $filter) {
                        // If this filter was not already added up above, then add it.
                        if (!$ordered_filters[$key_2]) {
                            $ordered_filters[$key_2] = $filter;
                        }
                    }

                    // Update the filters array to contain the ordered filters
                    // that we just prepared.
                    $dynamic_fields[$key]['filters'] = $ordered_filters;

                }

                // Convert the filters to a simple, index-based array,
                // so when it is converted to JSON below and passed to JS,
                // it can be handled as an array in JS instead of as an object.
                $dynamic_fields[$key]['filters'] = array_values($dynamic_fields[$key]['filters']);

            }

        }

        // If there is at least one browse filter, then browse is enabled,
        // so prepare feature.
        if ($browse_enabled) {

            $browse_field_id_options = array();
            $browse_field_id_options['Browse'] = '';

            // Assume that the selected browse field (if there is one) is invalid, until we find out otherwise.
            // We use this in order to determine if we need to clear the browse field value.
            $selected_browse_field_valid = false;

            $selected_browse_field_date_format = '';

            $browse_filter_query_string = '';

            // Loop through the query string parameters in order to build query string for filters.
            foreach ($query_string_parameters as $name => $value) {
                // If this parameter is not a field that appears on this form then add it to query string.
                if (mb_substr($name, 0, $field_prefix_length) != $field_prefix) {
                    // If this field can have multiple values (e.g. check box group), then loop through value array in order to add a name/value pair to the query string for every value.
                    if (is_array($value) == TRUE) {
                        foreach ($value as $subvalue) {
                            // if this is the first item that is being added to the query string, then add question mark
                            if ($browse_filter_query_string == '') {
                                $browse_filter_query_string = '?';
                                
                            // else this is not the first item that is being added to the query string, so add ampersand
                            } else {
                                $browse_filter_query_string .= '&';
                            }
                            
                            $browse_filter_query_string .= urlencode($name . '[]') . '=' . urlencode($subvalue);
                        }

                    // Otherwise this field has just one value, so output name/value pair for it.
                    } else {
                        // if this is the first item that is being added to the query string, then add question mark
                        if ($browse_filter_query_string == '') {
                            $browse_filter_query_string = '?';
                            
                        // else this is not the first item that is being added to the query string, so add ampersand
                        } else {
                            $browse_filter_query_string .= '&';
                        }
                        
                        $browse_filter_query_string .= urlencode($name) . '=' . urlencode($value);
                    }
                }
            }

            // Loop through browse fields in order to prepare browse pick list and filters.
            foreach ($browse_fields as $key => $browse_field) {

                $label = $browse_field['label'];

                // If the last character of the label if a colon, then remove the colon,
                // because the label will eventually appear in a pick list so a colon does not make sense.
                if (mb_substr($label, -1) == ':') {
                    $label = mb_substr($label, 0, -1);
                }

                $browse_field_id_options[$label] = $browse_field['id'];

                // If this browse field is the selected browse field, then the selected browse field is valid, so show container.
                if ($browse_field['id'] == $liveform->get_field_value($page_id . '_browse_field_id')) {

                    $browse_field['current'] = true;

                    $selected_browse_field_valid = true;

                    // If this browse field is a date or date and time field,
                    // and this browse field has a date format, then remember date format so we
                    // have it further below where we prepare the filter.
                    if (
                        (
                            ($browse_field['type'] == 'date')
                            || ($browse_field['type'] == 'date and time')
                        )
                        && ($browse_field['date_format'] != '')
                    ) {
                        $selected_browse_field_date_format = $browse_field['date_format'];
                    }
                }

                // Prepare a customized version of the browse filter query string for this particular field.
                $browse_filter_query_string_custom = $browse_filter_query_string;

                // If this is the first item that is being added to the query string, then add question mark.
                if ($browse_filter_query_string_custom == '') {
                    $browse_filter_query_string_custom = '?';
                    
                // Otherwise this is not the first item that is being added to the query string, so add ampersand.
                } else {
                    $browse_filter_query_string_custom .= '&';
                }
                
                $browse_filter_query_string_custom .= $page_id . '_browse_field_id=' . $browse_field['id'];

                $browse_field['number_of_filters'] = count($browse_field['filters']);

                // If the number of columns is 0 then set it to the default, in order to prevent a division-by-zero error.
                // This condition should never occur, however we add this check just in case.
                if ($browse_field['number_of_columns'] == 0) {
                    $browse_field['number_of_columns'] = 3;
                }

                $browse_field['number_of_filters_per_column'] = ceil($browse_field['number_of_filters'] / $browse_field['number_of_columns']);

                // Determine the actual number of columns.
                $browse_field['number_of_columns'] = ceil($browse_field['number_of_filters'] / $browse_field['number_of_filters_per_column']);

                // Loop through filters in order to prepare them.
                foreach ($browse_field['filters'] as $filter_key => $filter) {

                    $filter['current'] = false;

                    // If this field is the active browse field and this filter is the current filter,
                    // then remember that.
                    if (
                        ($browse_field['id'] == $liveform->get_field_value($page_id . '_browse_field_id'))
                        && ($filter['name'] == $liveform->get_field_value($page_id . '_browse_filter'))
                    ) {
                        $filter['current'] = true;
                    }

                    // Assume that we will not use a shortcut until we find out otherwise.
                    // A shorcut will link the filter directly to the one result on the form item view page,
                    // instead of back to this page.
                    $filter['shortcut'] = false;

                    // If a shortcut is enabled for this field and there is only one result for this filter,
                    // and there is a form item view page name, then remember that we need to use a shortcut.
                    if (
                        ($browse_field['shortcut'] == 1)
                        && ($filter['count'] == 1)
                        && ($form_item_view_page_name != '')
                    ) {
                        $filter['shortcut'] = true;
                    }

                    // If we should use a shortcut, then prepare link to form item view page.
                    if ($filter['shortcut']) {
                        // If pretty URLs are enabled for the custom form, and this submitted form has an address name,
                        // then output pretty URL.
                        if ($pretty_urls and ($filter['address_name'] != '')) {
                            $filter['url'] = PATH . encode_url_path($current_page_name) . '/' . $filter['address_name'] . '?send_to=' . urlencode($send_to);

                        // Otherwise, output non-pretty URL.
                        } else {
                            $filter['url'] = PATH . encode_url_path($form_item_view_page_name) . '?r=' . $filter['reference_code'] . '&send_to=' . urlencode($send_to);
                        }

                    // Otherwise we should not use a shortcut, so prepare link to this page.
                    } else {
                        $filter['url'] = $url_parts['path'] . $browse_filter_query_string_custom . '&' . $page_id . '_browse_filter=' . urlencode($filter['name']) . '#' . $page_id . '_system';
                    }

                    // Store the db data value of the name, before we
                    // possibly change it for output purposes
                    // (e.g. for date fields).
                    $filter['data'] = $filter['name'];

                    // If this browse field is a date or date and time field,
                    // and this browse field has a date format, then output the browse filter
                    // like it is (e.g. May 2020), instead of outputting it like a normal date/time.
                    if (
                        (
                            ($browse_field['type'] == 'date')
                            || ($browse_field['type'] == 'date and time')
                        )
                        && ($browse_field['date_format'] != '')
                    ) {
                        // don't do anything

                    // Otherwise this is a normal type of field, so prepare name for output.
                    } else {
                        $filter['name'] = prepare_form_data_for_output($filter['name'], $browse_field['type'], $prepare_for_html = false);
                    }

                    $browse_field['filters'][$filter_key] = $filter;

                }

                $browse_fields[$key] = $browse_field;
            }

            // If browse is selected but the selected browse field is not valid, then clear field,
            // so that we don't output browse toggle.
            if (
                ($liveform->get_field_value($page_id . '_browse_field_id') != '')
                && ($selected_browse_field_valid == false)
            ) {
                $liveform->assign_field_value($page_id . '_browse_field_id', '');
            }

            // If there is a selected browse field, then complete various tasks.
            if ($liveform->get_field_value($page_id . '_browse_field_id') != '') {

                $browse_expanded = true;

                // If search is enabled, then deactivate search so browse and search won't be active at the same time.
                if ($search == 1) {
                    // Clear old query value.
                    $liveform->assign_field_value('query', '');

                    // Clear new query value.
                    $liveform->assign_field_value($page_id . '_query', '');

                    // If advanced search is enabled, then set advanced search to be inactive.
                    if ($search_advanced) {
                        $liveform->assign_field_value($page_id . '_advanced', 'false');
                    }
                }
            }

            $liveform->set($page_id . '_browse_field_id', 'options', $browse_field_id_options);

        }
        
        // Create variable which we will use to remember if there is at least one filter set for an advanced field,
        // so later we know if we need to add advanced filters to query.
        $advanced_filter = false;

        // If advanced search is enabled then output toggle and layout.
        if ($search_advanced) {

            // If the visitor has enabled the advanced search, or if the visitor has not enabled/disabled the advanced search,
            // and the advanced search should be enabled by default.
            if (
                ($liveform->get_field_value($page_id . '_advanced') == 'true')
                ||
                (
                    ($liveform->field_in_session($page_id . '_advanced') == false)
                    && ($search_advanced_show_by_default == 1)
                )
            ) {

                $search_advanced_expanded = true;

                $liveform->assign_field_value($page_id . '_advanced', 'true');

            // Otherwise advanced search is collapsed, so update advanced field value.
            } else {
                $liveform->assign_field_value($page_id . '_advanced', 'false');
            }

            $system .= '<input type="hidden" id="' . $page_id . '_advanced" name="' . $page_id . '_advanced">';

            // Create array which we will use to keep track of the current number for fields that appear multiple times,
            // so that we can increment the number as we loop through the fields.
            $field_number = array();

            // Loop through the advanced search fields in order to replace variables with fields.
            foreach ($advanced_search_fields as $key => $field) {
                // If field is a custom field and it has options, then get options.
                if (
                    ($field['group'] == 'custom')
                    &&
                    (
                        ($field['type'] == 'pick list')
                        || ($field['type'] == 'radio button')
                        || ($field['type'] == 'check box')
                    )
                ) {
                    $query =
                        "SELECT
                            id,
                            label,
                            value
                        FROM form_field_options
                        WHERE form_field_id = '" . $field['id'] . "'
                        ORDER BY sort_order";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $options = mysqli_fetch_items($result);
                }

                // Create a simple name which will be used as part of the html field name.
                $simple_name = $field['name'];

                // If this field is a custom field, then prepare simple name.
                // We don't have to prepare the simple name for standard fields because their names are already valid.
                if ($field['group'] == 'custom') {
                    // Replace spaces with underscores.
                    $simple_name = str_replace(' ', '_', $simple_name);

                    // Remove special characters in case any of them cause problems.
                    $simple_name = preg_replace('/[^A-Za-z0-9-_]/', '', $simple_name);
                }

                // If this field appears multiple times, then set html field name with occurence number on the end.
                if ($field_count[$field['name']] > 1) {
                    // Increment field number so we have a new number for this occurence of this field.
                    $field_number[$field['name']]++;

                    // Set the html field name with the page id, simple name, and occurence number.
                    $field['html_field_name'] = $page_id . '_' . $simple_name . '_' . $field_number[$field['name']];

                // Otherwise this field only appears once, so set html field name without occurence number.
                } else {
                    // Set the html field name with the page id and simple name.
                    $field['html_field_name'] = $page_id . '_' . $simple_name;
                }

                // Store the html field name in the array, because we will
                // use it in different loops further below.
                $advanced_search_fields[$key]['html_field_name'] = $field['html_field_name'];

                // If this is a dynamic field then also update html field name in that array.
                if ($field['dynamic']) {
                    $dynamic_fields[$field['name']]['html_field_name'] = $field['html_field_name'];
                }

                // If the advanced clear button was clicked then clear field value.
                if ($liveform->field_in_session($page_id . '_advanced_clear') == true) {
                    $liveform->assign_field_value($field['html_field_name'], '');

                // Otherwise the advanced clear button was not clicked so determine if this field has a filter.
                } else {
                    // If the advanced search is expanded, then determine if a filter is set for this field.
                    // We use this later to determine if we need to process advanced filters and in the
                    // init_form_list_view() JavaScript function in order to determine if we need to refresh
                    // the page when the advanced search is collapsed in order to remove the filter(s).
                    if ($search_advanced_expanded) {
                        $field_value = $liveform->get_field_value($field['html_field_name']);

                        // If there is a filter for this field, then take note.
                        // The array check is necessary because of multi-selection pick lists and check box groups.
                        if (
                            (is_array($field_value) == TRUE)
                            || ($field_value != '')
                        ) {
                            $advanced_filter = true;
                        }

                    // Otherwise the advanced search is collapsed, so clear value for this advanced search field.
                    } else {
                        $liveform->assign_field_value($field['html_field_name'], '');
                    }
                }

                $output_field = '';

                // If this field is dynamic, then show a pick list of the filters
                // that were collected above.
                if ($field['dynamic']) {
                    
                    $pick_list_options = array();

                    $pick_list_options[''] = '';
                    
                    if ($dynamic_fields[$field['name']]['filters']) {
                        foreach ($dynamic_fields[$field['name']]['filters'] as $filter) {
                            $pick_list_options[$filter['name']] = $filter['name'];
                        }
                    }

                    $liveform->set($field['html_field_name'], 'options', $pick_list_options);

                    $class = $field['class'];

                    // If the class is blank, then set default class.
                    if ($class == '') {
                        $class = 'software_select';
                    }

                    $output_field =
                        '<select
                            id="' . h($field['html_field_name']) . '"
                            name="' . h($field['html_field_name']) . '"
                            class="dynamic ' . h($class) . '"
                        ></select>

                        <span class="clear ' . h($field['html_field_name']) . '_clear" style="display: none">
                            <a href="javascript:void(0)" class="software_button_small_secondary remove_button">X</a>
                        </span>';

                // Otherwise the field is not dynamic, so show a normal field.
                } else {

                    // Show a different type of field depending on the type.
                    switch ($field['type']) {
                        // Use a text box for the field for various types of fields (e.g. text box, text area, email address)
                        default:
                            $maxlength = '';
                            $output_date_time_picker = '';

                            // Prepare maxlength and date/time picker code based on the field type.
                            switch ($field['type']) {
                                case 'text box':
                                case 'email address':
                                    // If the maxlength is not 0 then use it.
                                    if ($field['maxlength'] != 0) {
                                        $maxlength = $field['maxlength'];
                                    }

                                    break;

                                case 'date':
                                    $maxlength = '10';

                                    $output_date_time_picker =
                                        get_date_picker_format() . '
                                        <script>
                                            software_$("#' . $field['html_field_name'] . '").datepicker({
                                                dateFormat: date_picker_format
                                            });
                                        </script>';

                                    break;

                                case 'date and time':
                                    $maxlength = '22';

                                    $output_date_time_picker =
                                        '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
                                        ' . get_date_picker_format() . '
                                        <script>
                                            software_$("#' . $field['html_field_name'] . '").datetimepicker({
                                                dateFormat: date_picker_format,
                                                timeFormat: "h:mm TT"
                                            });
                                        </script>';

                                    break;

                                case 'time':
                                    $maxlength = '11';
                                    break;
                            }

                            $class = $field['class'];

                            // If the class is blank, then set default class.
                            if ($class == '') {
                                $class = 'software_input_text';
                            }

                            $output_help = '';

                            // If the help has not been disabled, then show help for time fields
                            if (($field['help'] !== FALSE) && ($field['type'] == 'time')) {
                                $output_help = ' (Format: h:mm AM/PM)';
                            }

                            $output_field = $liveform->output_field(array(
                                'type' => 'text',
                                'id' => $field['html_field_name'],
                                'name' => $field['html_field_name'],
                                'size' => $field['size'],
                                'maxlength' => $maxlength,
                                'class' => $class)) .
                                $output_help .
                                $output_date_time_picker;

                            break;
                            
                        case 'pick list':
                            $html_field_name = $field['html_field_name'];
                            $multiple = '';
                            
                            // if the pick list supports multiple selection, then alter html field name and setup pick list to allow multiple selection
                            if ($field['multiple'] == 1) {
                                $html_field_name .= '[]';
                                $multiple = 'multiple';
                            }
                            
                            $pick_list_options = array();

                            // Create counter in order to determine which option is the first option.
                            $count = 0;
                            
                            foreach ($options as $option) {

                                $count++;

                                // If this is the first option and the value is not blank, then add a blank option,
                                // so it is possible for an option to not be selected.
                                if (
                                    ($count == 1)
                                    && ($option['value'] != '')
                                ) {
                                    $pick_list_options[''] = '';
                                }

                                $pick_list_options[$option['label']] = $option['value'];

                            }

                            $liveform->set($field['html_field_name'], 'options', $pick_list_options);

                            $class = $field['class'];

                            // If the class is blank, then set default class.
                            if ($class == '') {
                                $class = 'software_select';
                            }

                            $output_field = $liveform->output_field(array(
                                'type' => 'select',
                                'name' => $html_field_name,
                                'size' => $field['size'],
                                'multiple' => $multiple,
                                'class' => $class));
                            
                            break;
                            
                        case 'radio button':
                            $class = $field['class'];

                            // If the class is blank, then set default class.
                            if ($class == '') {
                                $class = 'software_input_radio';
                            }

                            // Add a default option so that one of the actual options does not always have to be selected.
                            $output_field .= $liveform->output_field(array(
                                'type' => 'radio',
                                'name' => $field['html_field_name'],
                                'id' => $field['html_field_name'] . '_1',
                                'value' => '',
                                'checked' => 'checked',
                                'class' => $class)) .
                                '<label for="' . $field['html_field_name'] . '_1"> [Any]</label><br />';

                            foreach ($options as $key => $option) {
                                $output_field .= $liveform->output_field(array(
                                    'type' => 'radio',
                                    'name' => $field['html_field_name'],
                                    'id' => $field['html_field_name'] . '_' . ($key + 2),
                                    'value' => $option['value'],
                                    'class' => $class)) .
                                    '<label for="' . $field['html_field_name'] . '_' . ($key + 2) . '"> ' . h($option['label']) . '</label><br />';
                            }
                            
                            break;
                            
                        case 'check box':
                            $html_field_name = $field['html_field_name'];

                            // if there is more than one option for this check box group, then prepare check boxes to support multiple check boxes
                            if (count($options) > 1) {
                                $html_field_name .= '[]';
                            }

                            $class = $field['class'];

                            // If the class is blank, then set default class.
                            if ($class == '') {
                                $class = 'software_input_checkbox';
                            }
                            
                            foreach ($options as $key => $option) {
                                $output_field .= $liveform->output_field(array(
                                    'type' => 'checkbox',
                                    'name' => $html_field_name,
                                    'id' => $field['html_field_name'] . '_' . ($key + 1),
                                    'value' => $option['value'],
                                    'class' => $class)) .
                                    '<label for="' . $field['html_field_name'] . '_' . ($key + 1) . '"> ' . h($option['label']) . '</label><br />';
                            }
                            
                            break;
                    }

                }

                // Replace the placeholder with the field. We can't use str_replace() for this
                // because we need to limit the number of replacements to 1 in order to prevent bugs where
                // it will incorrectly replace multiple placeholders for the same field
                // (e.g. when there are duplicate fields for mobile)
                $search_advanced_content = preg_replace('/' . preg_quote($field['placeholder'], '/') . '/', addcslashes($output_field, '\\$'), $search_advanced_content, 1);

            }

            // Loop through the advanced search buttons in order to replace variables with buttons.
            foreach ($advanced_search_buttons as $button) {
                $label = $button['label'];
                $class = $button['class'];

                // If this is a submit button, then prepare values for it.
                if ($button['name'] == 'submit_button') {
                    $name = $page_id . '_advanced_submit';

                    // If the label is blank, then set default label.
                    if ($label == '') {
                        $label = 'Search';
                    }

                    // If the class is blank, then set default class.
                    if ($class == '') {
                        $class = 'software_input_submit_primary submit';
                    }

                // Otherwise this is a clear button, so prepare values for it.
                } else {
                    $name = $page_id . '_advanced_clear';

                    // If the label is blank, then set default label.
                    if ($label == '') {
                        $label = 'Clear';
                    }

                    // If the class is blank, then set default class.
                    if ($class == '') {
                        $class = 'software_input_submit_secondary clear';
                    }
                }

                $output_button = $liveform->output_field(array(
                    'type' => 'submit',
                    'name' => $name,
                    'value' => $label,
                    'class' => $class));

                // Replace the placeholder with the button.
                $search_advanced_content = str_replace($button['placeholder'], $output_button, $search_advanced_content);
            }

        }

        $system .=
            '<script>
                software.init_form_list_view({
                    page_id: ' . $page_id . ',
                    dynamic_fields: ' . encode_json($dynamic_fields) . '})
            </script>';
    }

    // If browse and search are disabled, or show results by default is enabled,
    // or the visitor has browsed or searched, then continue to get results.
    if (
        (
            ($search == 0)
            && ($browse_enabled == false)
        )
        || ($show_results_by_default == 1)
        ||
        (
            ($liveform->get_field_value($page_id . '_browse_field_id') != '')
            && ($liveform->get_field_value($page_id . '_browse_filter') != '')
        )
        || ($search_query != '')
        || ($liveform->field_in_session($page_id . '_simple_submit') == true)
        || ($advanced_filter == true)
        || ($liveform->field_in_session($page_id . '_advanced_submit') == true)
    ) {

        // get all field names that are in the layout, so that we can determine
        // what needs to be selected from the database
        preg_match_all('/\^\^(.*?)\^\^/', $layout, $variables, PREG_SET_ORDER);

        $field_names_in_layout = array();

        // loop through the variables in order to add the field names to an array
        foreach ($variables as $variable) {
            $field_name = mb_strtolower($variable[1]);

            // if this field name has not already been added to the array, then add it
            if (in_array($field_name, $field_names_in_layout) == FALSE) {
                $field_names_in_layout[] = $field_name;
            }
        }

        // assume that we don't need to join various tables until we find out otherwise
        // "for count" are for joins that will be used by the count query
        // "for select" are for joins that will be used by the select query
        $submitter_join_required_for_count = FALSE;
        $submitter_join_required_for_select = FALSE;
        $last_modifier_join_required_for_count = FALSE;
        $last_modifier_join_required_for_select = FALSE;
        $submitted_form_info_join_required_for_count = FALSE;
        $submitted_form_info_join_required_for_select = FALSE;
        $newest_comment_join_required_for_count = FALSE;
        $newest_comment_join_required_for_select = FALSE;
        $newest_comment_submitter_join_required_for_select = FALSE;

        $sql_select =
            "forms.id,
            forms.reference_code,
            forms.address_name";

        // loop through the standard fields in order to prepare selects and determine which joins we need
        foreach ($standard_fields as $standard_field) {
            // If this standard field is in the layout, and it is not the reference code field,
            // and it is not the address name field, then select it.  We always select the reference code
            // and address name fields, so we don't want to add a duplicate selection here for those.
            if (
                (in_array($standard_field['value'], $field_names_in_layout) == TRUE)
                && ($standard_field['value'] != 'reference_code')
                && ($standard_field['value'] != 'address_name')
            ) {
                // if the sql select is not blank, then add a comma and new line for separation
                if ($sql_select != '') {
                    $sql_select .= ",\n";
                }

                $sql_select .= $standard_field['sql_name'] . " AS " . $standard_field['value'];

                // do additional things depending on the field
                switch ($standard_field['value']) {
                    case 'submitter':
                        $submitter_join_required_for_select = TRUE;

                        // if the sql select is not blank, then add a comma and new line for separation
                        if ($sql_select != '') {
                            $sql_select .= ",\n";
                        }

                        $sql_select .=
                            "submitter.user_badge AS submitter_badge,
                            submitter.user_badge_label AS submitter_badge_label";

                        break;

                    case 'last_modifier':
                        $last_modifier_join_required_for_select = TRUE;

                        // if the sql select is not blank, then add a comma and new line for separation
                        if ($sql_select != '') {
                            $sql_select .= ",\n";
                        }

                        $sql_select .=
                            "last_modifier.user_badge AS last_modifier_badge,
                            last_modifier.user_badge_label AS last_modifier_badge_label";

                        break;

                    case 'number_of_views':
                    case 'number_of_comments':
                    case 'comment_attachments':
                        $submitted_form_info_join_required_for_select = TRUE;
                        break;

                    case 'newest_comment_name':
                        $submitted_form_info_join_required_for_select = TRUE;
                        $newest_comment_join_required_for_select = TRUE;
                        $newest_comment_submitter_join_required_for_select = TRUE;

                        // if the sql select is not blank, then add a comma and new line for separation
                        if ($sql_select != '') {
                            $sql_select .= ",\n";
                        }

                        $sql_select .=
                            "newest_comment_submitter.user_badge AS newest_comment_submitter_badge,
                            newest_comment_submitter.user_badge_label AS newest_comment_submitter_badge_label";
                        
                        break;

                    case 'newest_comment':
                    case 'newest_comment_date_and_time':
                    case 'newest_comment_id':
                    case 'newest_activity_date_and_time':
                        $submitted_form_info_join_required_for_select = TRUE;
                        $newest_comment_join_required_for_select = TRUE;
                        break;
                }
            }
        }

        // loop through the custom fields in order to prepare selects
        foreach ($custom_fields as $custom_field) {
            // if this custom field is in the layout, then select it
            if (in_array(mb_strtolower($custom_field['name']), $field_names_in_layout) == TRUE) {
                // if the sql select is not blank, then add a comma and new line for separation
                if ($sql_select != '') {
                    $sql_select .= ",\n";
                }

                // If this custom field is a pick list and allow multiple selection is enabled,
                // or if this custom field is a check box, then select field in a certain way,
                // so that multiple values may be retrieved.  We don't just use this type of query
                // for all types of fields because it is probably slightly slower.
                if (
                    (
                        ($custom_field['type'] == 'pick list')
                        && ($custom_field['multiple'] == 1)
                    )
                    || ($custom_field['type'] == 'check box')
                ) {
                    $sql_select .= "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "')) AS field_" . $custom_field['id'];

                // else if this custom field is a file upload field, then select file name
                } else if ($custom_field['type'] == 'file upload') {
                    $sql_select .= "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "') LIMIT 1) AS field_" . $custom_field['id'];

                // else this custom field has any other type, so select field in the default way
                } else {
                    $sql_select .= "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "') LIMIT 1) AS field_" . $custom_field['id'];
                }
            }
        }

        $sql_where = "";

        // If filters have not already been retrieved (in browse code),
        // then get filters in order to prepare SQL for where clause.
        // We order by form_field_id ASC in order to get filters for standard
        // fields first, in order to improve performance.
        if (isset($filters) == false) {
            $query =
                "SELECT
                    form_field_id,
                    standard_field,
                    operator,
                    value,
                    dynamic_value,
                    dynamic_value_attribute
                FROM form_list_view_filters
                WHERE page_id = '" . escape($page_id) . "'
                ORDER BY form_field_id ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $filters = mysqli_fetch_items($result);
        }

        // If browse is enabled and there is an active browse field and an active browse filter,
        // then add a filter for the browse filter.
        if (
            ($browse_enabled == true)
            && ($liveform->get_field_value($page_id . '_browse_field_id') != '')
            && ($liveform->get_field_value($page_id . '_browse_filter') != '')
        ) {
            $filters[] = array(
                'form_field_id' => $liveform->get_field_value($page_id . '_browse_field_id'),
                'operator' => 'is equal to',
                'value' => $liveform->get_field_value($page_id . '_browse_filter'),
                'date_format' => $selected_browse_field_date_format
            );
        }
        
        // loop through all filters in order to prepare SQL
        foreach ($filters as $filter) {
            // get operand 1
            $operand_1 = '';
            
            // if a standard field was selected for filter
            if ($filter['standard_field'] != '') {
                // determine if a join is required based on the field
                switch ($filter['standard_field']) {
                    case 'submitter':
                        $submitter_join_required_for_count = TRUE;
                        $submitter_join_required_for_select = TRUE;
                        break;

                    case 'last_modifier':
                        $last_modifier_join_required_for_count = TRUE;
                        $last_modifier_join_required_for_select = TRUE;
                        break;

                    case 'number_of_views':
                    case 'number_of_comments':
                    case 'comment_attachments':
                        $submitted_form_info_join_required_for_count = TRUE;
                        $submitted_form_info_join_required_for_select = TRUE;
                        break;

                    case 'newest_comment_name':
                    case 'newest_comment':
                    case 'newest_comment_date_and_time':
                    case 'newest_comment_id':
                    case 'newest_activity_date_and_time':
                        $submitted_form_info_join_required_for_count = TRUE;
                        $submitted_form_info_join_required_for_select = TRUE;
                        $newest_comment_join_required_for_count = TRUE;
                        $newest_comment_join_required_for_select = TRUE;
                        break;
                }

                // get operand 1 which is sql name by looping through standard fields
                foreach ($standard_fields as $standard_field) {
                    // if this is the standard field that was selected for this filter, then set operand 1 and break out of loop
                    if ($standard_field['value'] == $filter['standard_field']) {
                        $operand_1 = $standard_field['sql_name'];
                        break;
                    }
                }
                
            // else a custom field was selected for the filter
            } else {
                // Assume that the custom field for this filter is not valid, until we find out otherwise.
                // A custom field for a filter is valid if the custom field exists on the custom form for this form list view.
                $custom_field_is_valid = FALSE;
                
                // get field type and determine if field is valid by looping through custom fields
                foreach ($custom_fields as $custom_field) {
                    // if this is the custom field that was selected for this filter,
                    // then we know the custom field is valid, so break out of loop
                    if ($custom_field['id'] == $filter['form_field_id']) {
                        $field_type = $custom_field['type'];
                        $field_multiple = $custom_field['multiple'];
                        $custom_field_is_valid = TRUE;
                        break;
                    }
                }
                
                // If the custom field for this filter is not valid, then do not prepare SQL filter and skip to the next filter.
                // We have to do this in order to prevent a database error.
                if ($custom_field_is_valid == FALSE) {
                    continue;
                }

                // If this custom field is a pick list and allow multiple selection is enabled,
                // or if this custom field is a check box, then prepare operand in a certain way,
                // because there might be multiple values.
                if (
                    (
                        ($field_type == 'pick list')
                        && ($field_multiple == 1)
                    )
                    || ($field_type == 'check box')
                ) {
                    // If the operator is "is equal to" or "is not equal to" then prepare special filter in a way
                    // so that it looks at individual values instead of a string of grouped values.
                    if (
                        ($filter['operator'] == 'is equal to')
                        || ($filter['operator'] == 'is not equal to')
                    ) {
                        $operand_1 = "(SELECT COUNT(*) FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') AND (form_data.data = '" . escape($filter['value']) . "'))";

                        // If the operator is "is equal to", then change operator to "is greater than",
                        // because of the special comparison we are using.
                        if ($filter['operator'] == 'is equal to') {
                            $filter['operator'] = 'is greater than';

                        // Otherwise the operator is "is not equal to", so change operator to "is equal to",
                        // because of the special comparison we are using.
                        } else {
                            $filter['operator'] = 'is equal to';
                        }

                        // Change the filter value to 0 in order for our special comparison to work.
                        $filter['value'] = '0';

                    // Otherwise the operator is a different operator, so prepare operand 1,
                    // so that multiple values are grouped into one concatenated string, separated by commas.
                    } else {
                        $operand_1 = "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "'))";
                    }

                // else if this custom field is a file upload field, then prepare operand so it contains file name
                } else if ($field_type == 'file upload') {
                    $operand_1 = "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";

                // Otherwise if there is a date format for this filter, then use MySQL to format operand 1.
                } else if ($filter['date_format'] != '') {
                    // Start MySQL date format off with the PHP date format.
                    $mysql_date_format = $filter['date_format'];

                    // Replace single % with two so MySQL will treat it like a literal percent character.
                    $mysql_date_format = str_replace('%', '%%', $mysql_date_format);

                    $replacements = array();

                    // Prepare array of characters that need to be converted from PHP to MySQL.
                    $replacements[] = array('php_character' => 'd', 'mysql_character' => 'd');
                    $replacements[] = array('php_character' => 'D', 'mysql_character' => 'a');
                    $replacements[] = array('php_character' => 'j', 'mysql_character' => 'e');
                    $replacements[] = array('php_character' => 'l', 'mysql_character' => 'W');
                    $replacements[] = array('php_character' => 'w', 'mysql_character' => 'w');
                    $replacements[] = array('php_character' => 'W', 'mysql_character' => 'v');
                    $replacements[] = array('php_character' => 'F', 'mysql_character' => 'M');
                    $replacements[] = array('php_character' => 'm', 'mysql_character' => 'm');
                    $replacements[] = array('php_character' => 'M', 'mysql_character' => 'b');
                    $replacements[] = array('php_character' => 'n', 'mysql_character' => 'c');
                    $replacements[] = array('php_character' => 'Y', 'mysql_character' => 'Y');
                    $replacements[] = array('php_character' => 'y', 'mysql_character' => 'y');
                    $replacements[] = array('php_character' => 'a', 'mysql_character' => 'p');
                    $replacements[] = array('php_character' => 'A', 'mysql_character' => 'p');
                    $replacements[] = array('php_character' => 'g', 'mysql_character' => 'l');
                    $replacements[] = array('php_character' => 'G', 'mysql_character' => 'k');
                    $replacements[] = array('php_character' => 'h', 'mysql_character' => 'h');
                    $replacements[] = array('php_character' => 'H', 'mysql_character' => 'H');
                    $replacements[] = array('php_character' => 'i', 'mysql_character' => 'i');
                    $replacements[] = array('php_character' => 's', 'mysql_character' => 's');

                    foreach ($replacements as $replacement) {
                        // Replace PHP character (as long as a backslash or a percent sign does not appear before it)
                        // with percent version of MySQL character.
                        $mysql_date_format = preg_replace('/(?:^|([^\\\%]))(' . $replacement['php_character'] . ')/', '$1%' . $replacement['mysql_character'], $mysql_date_format);
                    }

                    // Remove backslashes so that MySQL outputs literal character.
                    $mysql_date_format = str_replace('\\', '', $mysql_date_format);

                    $operand_1 = "date_format((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1), '" . escape($mysql_date_format) . "')";

                // else this custom field has any other type, so prepare operand in the default way
                } else {
                    $operand_1 = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";
                }
            }
            
            // if a basic value was entered, use that value
            if ($filter['value'] != '') {
                $operand_2 = $filter['value'];
                
            // else a dynamic value was entered, so use dynamic value
            } else {
                $operand_2 = get_dynamic_value($filter['dynamic_value'], $filter['dynamic_value_attribute']);
            }
            
            $sql_where .= " AND (" . prepare_sql_operation($filter['operator'], $operand_1, $operand_2) . ")";
        }

        // prepare SQL for search
        $sql_search = "";
        
        // if there is a search query, then prepare SQL for search
        if ($search_query != '') {
            $submitter_join_required_for_count = TRUE;
            $submitter_join_required_for_select = TRUE;
            $last_modifier_join_required_for_count = TRUE;
            $last_modifier_join_required_for_select = TRUE;
            $submitted_form_info_join_required_for_count = TRUE;
            $submitted_form_info_join_required_for_select = TRUE;
            $newest_comment_join_required_for_count = TRUE;
            $newest_comment_join_required_for_select = TRUE;

            // loop through all standard fields in order to prepare sql for search
            foreach ($standard_fields as $standard_field) {
                // If this is not the comment attachments field, then search it.
                // We don't search the comment attachments for performance reasons.
                if ($standard_field['value'] != 'comment_attachments') {
                    // if a field has already been added, then add an or
                    if ($sql_search != '') {
                        $sql_search .= "OR ";
                    }
                    
                    // add field to sql search
                    $sql_search .= "(" . $standard_field['sql_name'] . " LIKE '%" . escape(escape_like(prepare_form_data_for_input($search_query, $standard_field['type']))) . "%') ";
                }
            }
            
            // loop through all custom fields in order to prepare sql for search
            foreach ($custom_fields as $custom_field) {
                // If this custom field is a pick list and allow multiple selection is enabled,
                // or if this custom field is a check box, then prepare field in a certain way,
                // so that multiple values may be retrieved.  We don't just use this type of query
                // for all types of fields because it is probably slightly slower.
                if (
                    (
                        ($custom_field['type'] == 'pick list')
                        && ($custom_field['multiple'] == 1)
                    )
                    || ($custom_field['type'] == 'check box')
                ) {
                    $field = "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "'))";

                // else if this custom field is a file upload field, then select file name
                } else if ($custom_field['type'] == 'file upload') {
                    $field = "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "') LIMIT 1)";

                // else this custom field has any other type, so select field in the default way
                } else {
                    $field = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $custom_field['id'] . "') LIMIT 1)";
                }
                
                // add field to sql search
                $sql_search .= "OR (" . $field . " LIKE '%" . escape(escape_like(prepare_form_data_for_input($search_query, $custom_field['type']))) . "%') ";
            }
            
            // wrap sql search in an and clause
            $sql_search = "AND (" . $sql_search . ") ";
        }

        // If advanced search is enabled and there is at least one advanced filter
        // then loop through advanced search fields in order to determine which have filters and need to be added to query.
        if (
            ($search_advanced == 1)
            && ($advanced_filter == true)
        ) {
            // Loop through advanced search fields in order to determine if filters need to be added.
            foreach ($advanced_search_fields as $field) {

                $field_value = $liveform->get_field_value($field['html_field_name']);

                // If the visitor filled out this advanced search field, then add filter.
                // The array check is necessary because of multi-selection pick lists and check box groups.
                if (
                    (is_array($field_value) == TRUE)
                    || ($field_value != '')
                ) {
                    // If this is a standard field, then prepare filter in a certain way.
                    if ($field['group'] == 'standard') {
                        // Determine if a join is required based on the field.
                        switch ($field['name']) {
                            case 'submitter':
                                $submitter_join_required_for_count = TRUE;
                                $submitter_join_required_for_select = TRUE;
                                break;

                            case 'last_modifier':
                                $last_modifier_join_required_for_count = TRUE;
                                $last_modifier_join_required_for_select = TRUE;
                                break;

                            case 'number_of_views':
                            case 'number_of_comments':
                            case 'comment_attachments':
                                $submitted_form_info_join_required_for_count = TRUE;
                                $submitted_form_info_join_required_for_select = TRUE;
                                break;

                            case 'newest_comment_name':
                            case 'newest_comment':
                            case 'newest_comment_date_and_time':
                            case 'newest_comment_id':
                            case 'newest_activity_date_and_time':
                                $submitted_form_info_join_required_for_count = TRUE;
                                $submitted_form_info_join_required_for_select = TRUE;
                                $newest_comment_join_required_for_count = TRUE;
                                $newest_comment_join_required_for_select = TRUE;
                                break;
                        }

                        // Add filter for advanced search field.
                        $sql_search .= " AND (" . prepare_sql_operation($field['operator'], $field['sql_name'], prepare_form_data_for_input($field_value, $field['type'])) . ")";
                        
                    // Otherwise this is a custom field, so prepare filter in a different way.
                    } else {
                        // If multiple values were submitted for this field (e.g. multi-selection pick list or check box group),
                        // then add filter for all selected options.
                        if (is_array($field_value) == TRUE) {

                            // Loop through the selected options in order to add filters.
                            foreach ($field_value as $value) {
                                $operand_1 = "(SELECT COUNT(*) FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') AND (form_data.data = '" . escape($value) . "'))";

                                // Add filter for advanced search field.
                                // Fields with multiple options require a special comparison,
                                // so we use the "is greater than" operator regardless if which operator
                                // was passed in the layout, and we use "0" for the 2nd operand.
                                $sql_search .= " AND (" . prepare_sql_operation('is greater than', $operand_1, '0') . ")";
                            }

                        // Otherwise if this is a dynamic field and the field
                        // supports multiple values, then add filter in a certain way.
                        } else if (
                            $field['dynamic']
                            and
                            (
                                (
                                    ($field['type'] == 'pick list')
                                    and $field['multiple']
                                )
                                or ($field['type'] == 'check box')
                            )
                        ) {

                            $operand_1 = "(SELECT COUNT(*) FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') AND (form_data.data = '" . e($field_value) . "'))";
                            $sql_search .= " AND (" . prepare_sql_operation('is greater than', $operand_1, '0') . ")";

                        // Otherwise only one value was submitted, so prepare filter differently.
                        } else {
                            // if this custom field is a file upload field, then prepare operand so it contains file name.
                            if ($field['type'] == 'file upload') {
                                $operand_1 = "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') LIMIT 1)";

                            // else this custom field has any other type, so prepare operand in the default way
                            } else {
                                $operand_1 = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $field['id'] . "') LIMIT 1)";
                            }

                            $sql_search .= " AND (" . prepare_sql_operation($field['operator'], $operand_1, prepare_form_data_for_input($field_value, $field['type'])) . ")";
                        }
                    }
                }
            }
        }

        $order_by_1 = '';

        // if order by 1 has a standard field value, then use that value
        if ($order_by_1_standard_field != '') {
            $order_by_1 = $order_by_1_standard_field;
            
        // else if order by 1 has a custom field value, then determine if we should use it
        } else if ($order_by_1_form_field_id != 0) {
            // loop through custom form fields, in order to determine if field is valid
            foreach ($custom_fields as $custom_field) {
                // if field is equal to order by, then the field is valid, so set value
                if ($custom_field['id'] == $order_by_1_form_field_id) {
                    $order_by_1_field_type = $custom_field['type'];
                    $order_by_1_field_multiple = $custom_field['multiple'];
                    $order_by_1 = $order_by_1_form_field_id;
                    break;
                }
            }
        }

        $order_by_2 = '';

        // if order by 2 has a standard field value, then use that value
        if ($order_by_2_standard_field != '') {
            $order_by_2 = $order_by_2_standard_field;
            
        // else if order by 2 has a custom field value, then determine if we should use it
        } else if ($order_by_2_form_field_id != 0) {
            // loop through custom form fields, in order to determine if field is valid
            foreach ($custom_fields as $custom_field) {
                // if field is equal to order by, then the field is valid, so set value
                if ($custom_field['id'] == $order_by_2_form_field_id) {
                    $order_by_2_field_type = $custom_field['type'];
                    $order_by_2_field_multiple = $custom_field['multiple'];
                    $order_by_2 = $order_by_2_form_field_id;
                    break;
                }
            }
        }

        $order_by_3 = '';

        // if order by 3 has a standard field value, then use that value
        if ($order_by_3_standard_field != '') {
            $order_by_3 = $order_by_3_standard_field;
            
        // else if order by 3 has a custom field value, then determine if we should use it
        } else if ($order_by_3_form_field_id != 0) {
            // loop through custom form fields, in order to determine if field is valid
            foreach ($custom_fields as $custom_field) {
                // if field is equal to order by, then the field is valid, so set value
                if ($custom_field['id'] == $order_by_3_form_field_id) {
                    $order_by_3_field_type = $custom_field['type'];
                    $order_by_3_field_multiple = $custom_field['multiple'];
                    $order_by_3 = $order_by_3_form_field_id;
                    break;
                }
            }
        }
        
        $sql_order_by = '';
        
        // if there is at least one order by, then prepare order by statement
        if (
            ($order_by_1 != '')
            || ($order_by_2 != '')
            || ($order_by_3 != '')
        ) {
            $sql_order_by .= "ORDER BY ";

            // if there is an order by 1
            if ($order_by_1) {
                // If the order by is not random, then prepare the alphabetical or numerical and ascending or descending part of the order by.
                if ($order_by_1 != 'random') {
                    $sql_order_by_type = "";

                    // If the order by is numerical, then add "+ 0" so MySQL will order numbers properly (e.g. 1, 2, 10 instead of 1, 10, 2)
                    if (($order_by_1_type == 'ascending_numerical') || ($order_by_1_type == 'descending_numercial')) {
                        $sql_order_by_type .= "+ 0 ";
                    }

                    // Add the correct ascending or descending part based on the order by type.
                    switch ($order_by_1_type) {
                        case 'ascending_alphabetical':
                        case 'ascending_numerical':
                            $sql_order_by_type .= "ASC";
                            break;

                        case 'descending_alphabetical':
                        case 'descending_numercial':
                            $sql_order_by_type .= "DESC";
                            break;
                    }
                }

                // if order by is numeric, then order by is a custom form field
                if (is_numeric($order_by_1) == true) {
                    // If this custom field is a pick list and allow multiple selection is enabled,
                    // or if this custom field is a check box, then prepare field in a certain way,
                    // so that multiple values may be retrieved.  We don't just use this type of query
                    // for all types of fields because it is probably slightly slower.
                    if (
                        (
                            ($order_by_1_field_type == 'pick list')
                            && ($order_by_1_field_multiple == 1)
                        )
                        || ($order_by_1_field_type == 'check box')
                    ) {
                        $sql_order_by .= "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_1_form_field_id . "')) " . $sql_order_by_type;

                    // else if this custom field is a file upload field, then prepare operand so it contains file name
                    } else if ($order_by_1_field_type == 'file upload') {
                        $sql_order_by .= "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_1_form_field_id . "') LIMIT 1) " . $sql_order_by_type;

                    // else this custom field has any other type, so prepare operand in the default way
                    } else {
                        $sql_order_by .= "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_1_form_field_id . "') LIMIT 1) " . $sql_order_by_type;
                    }
                    
                // else order by is not numeric, so order by is a standard field
                } else {
                    // if order by is random
                    if ($order_by_1 == 'random') {
                        $sql_order_by .= "RAND()";
                    
                    // else order by is not random
                    } else {
                        // determine if a join is required based on the field
                        switch ($order_by_1) {
                            case 'submitter':
                                $submitter_join_required_for_select = TRUE;
                                break;

                            case 'last_modifier':
                                $last_modifier_join_required_for_select = TRUE;
                                break;

                            case 'number_of_views':
                            case 'number_of_comments':
                            case 'comment_attachments':
                                $submitted_form_info_join_required_for_select = TRUE;
                                break;

                            case 'newest_comment_name':
                            case 'newest_comment':
                            case 'newest_comment_date_and_time':
                            case 'newest_comment_id':
                            case 'newest_activity_date_and_time':
                                $submitted_form_info_join_required_for_select = TRUE;
                                $newest_comment_join_required_for_select = TRUE;
                                break;
                        }

                        $sql_name = '';

                        // loop through the standard fields in order to get the SQL name for this field
                        foreach ($standard_fields as $standard_field) {
                            // if this is the standard field for this order by, then set SQL name and break out of loop
                            if ($standard_field['value'] == $order_by_1) {
                                $sql_name = $standard_field['sql_name'];
                                break;
                            }
                        }

                        $sql_order_by .= $sql_name . " " . $sql_order_by_type;
                    }
                }
                
                // if there is at least one more order by, then prepare comma
                if ($order_by_2 || $order_by_3) {
                    $sql_order_by .= ", ";
                }
            }

            // if there is an order by 2
            if ($order_by_2) {
                // Prepare the alphabetical or numerical and ascending or descending part of the order by.
                $sql_order_by_type = "";

                // If the order by is numerical, then add "+ 0" so MySQL will order numbers properly (e.g. 1, 2, 10 instead of 1, 10, 2)
                if (($order_by_2_type == 'ascending_numerical') || ($order_by_2_type == 'descending_numercial')) {
                    $sql_order_by_type .= "+ 0 ";
                }

                // Add the correct ascending or descending part based on the order by type.
                switch ($order_by_2_type) {
                    case 'ascending_alphabetical':
                    case 'ascending_numerical':
                        $sql_order_by_type .= "ASC";
                        break;

                    case 'descending_alphabetical':
                    case 'descending_numercial':
                        $sql_order_by_type .= "DESC";
                        break;
                }

                // if order by is numeric, then order by is a custom form field
                if (is_numeric($order_by_2) == true) {
                    // If this custom field is a pick list and allow multiple selection is enabled,
                    // or if this custom field is a check box, then prepare field in a certain way,
                    // so that multiple values may be retrieved.  We don't just use this type of query
                    // for all types of fields because it is probably slightly slower.
                    if (
                        (
                            ($order_by_2_field_type == 'pick list')
                            && ($order_by_2_field_multiple == 1)
                        )
                        || ($order_by_2_field_type == 'check box')
                    ) {
                        $sql_order_by .= "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_2_form_field_id . "')) " . $sql_order_by_type;

                    // else if this custom field is a file upload field, then prepare operand so it contains file name
                    } else if ($order_by_2_field_type == 'file upload') {
                        $sql_order_by .= "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_2_form_field_id . "') LIMIT 1) " . $sql_order_by_type;

                    // else this custom field has any other type, so prepare operand in the default way
                    } else {
                        $sql_order_by .= "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_2_form_field_id . "') LIMIT 1) " . $sql_order_by_type;
                    }
                    
                // else order by is not numeric, so order by is a standard field
                } else {
                    // determine if a join is required based on the field
                    switch ($order_by_2) {
                        case 'submitter':
                            $submitter_join_required_for_select = TRUE;
                            break;

                        case 'last_modifier':
                            $last_modifier_join_required_for_select = TRUE;
                            break;

                        case 'number_of_views':
                        case 'number_of_comments':
                        case 'comment_attachments':
                            $submitted_form_info_join_required_for_select = TRUE;
                            break;

                        case 'newest_comment_name':
                        case 'newest_comment':
                        case 'newest_comment_date_and_time':
                        case 'newest_comment_id':
                        case 'newest_activity_date_and_time':
                            $submitted_form_info_join_required_for_select = TRUE;
                            $newest_comment_join_required_for_select = TRUE;
                            break;
                    }

                    $sql_name = '';

                    // loop through the standard fields in order to get the SQL name for this field
                    foreach ($standard_fields as $standard_field) {
                        // if this is the standard field for this order by, then set SQL name and break out of loop
                        if ($standard_field['value'] == $order_by_2) {
                            $sql_name = $standard_field['sql_name'];
                            break;
                        }
                    }
                    
                    $sql_order_by .= $sql_name . " " . $sql_order_by_type;
                }
                
                // if there is at least one more order by, then prepare comma
                if ($order_by_3) {
                    $sql_order_by .= ", ";
                }
            }

            // if there is an order by 3
            if ($order_by_3) {
                // Prepare the alphabetical or numerical and ascending or descending part of the order by.
                $sql_order_by_type = "";

                // If the order by is numerical, then add "+ 0" so MySQL will order numbers properly (e.g. 1, 2, 10 instead of 1, 10, 2)
                if (($order_by_3_type == 'ascending_numerical') || ($order_by_3_type == 'descending_numercial')) {
                    $sql_order_by_type .= "+ 0 ";
                }

                // Add the correct ascending or descending part based on the order by type.
                switch ($order_by_3_type) {
                    case 'ascending_alphabetical':
                    case 'ascending_numerical':
                        $sql_order_by_type .= "ASC";
                        break;

                    case 'descending_alphabetical':
                    case 'descending_numercial':
                        $sql_order_by_type .= "DESC";
                        break;
                }

                // if order by is numeric, then order by is a custom form field
                if (is_numeric($order_by_3) == true) {
                    // If this custom field is a pick list and allow multiple selection is enabled,
                    // or if this custom field is a check box, then prepare field in a certain way,
                    // so that multiple values may be retrieved.  We don't just use this type of query
                    // for all types of fields because it is probably slightly slower.
                    if (
                        (
                            ($order_by_3_field_type == 'pick list')
                            && ($order_by_3_field_multiple == 1)
                        )
                        || ($order_by_3_field_type == 'check box')
                    ) {
                        $sql_order_by .= "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_3_form_field_id . "')) " . $sql_order_by_type;

                    // else if this custom field is a file upload field, then prepare operand so it contains file name
                    } else if ($order_by_3_field_type == 'file upload') {
                        $sql_order_by .= "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_3_form_field_id . "') LIMIT 1) " . $sql_order_by_type;

                    // else this custom field has any other type, so prepare operand in the default way
                    } else {
                        $sql_order_by .= "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $order_by_3_form_field_id . "') LIMIT 1) " . $sql_order_by_type;
                    }
                    
                // else order by is not numeric, so order by is a standard field
                } else {
                    // determine if a join is required based on the field
                    switch ($order_by_3) {
                        case 'submitter':
                            $submitter_join_required_for_select = TRUE;
                            break;

                        case 'last_modifier':
                            $last_modifier_join_required_for_select = TRUE;
                            break;

                        case 'number_of_views':
                        case 'number_of_comments':
                        case 'comment_attachments':
                            $submitted_form_info_join_required_for_select = TRUE;
                            break;

                        case 'newest_comment_name':
                        case 'newest_comment':
                        case 'newest_comment_date_and_time':
                        case 'newest_comment_id':
                        case 'newest_activity_date_and_time':
                            $submitted_form_info_join_required_for_select = TRUE;
                            $newest_comment_join_required_for_select = TRUE;
                            break;
                    }

                    $sql_name = '';

                    // loop through the standard fields in order to get the SQL name for this field
                    foreach ($standard_fields as $standard_field) {
                        // if this is the standard field for this order by, then set SQL name and break out of loop
                        if ($standard_field['value'] == $order_by_3) {
                            $sql_name = $standard_field['sql_name'];
                            break;
                        }
                    }
                    
                    $sql_order_by .= $sql_name . " " . $sql_order_by_type;
                }
            }
        }

        $sql_join_for_select = "";

        // if a submitter join is required, then add join
        if ($submitter_join_required_for_select == TRUE) {
            $sql_join_for_select .= "LEFT JOIN user AS submitter ON forms.user_id = submitter.user_id\n";
        }

        // if a last modifier join is required, then add join
        if ($last_modifier_join_required_for_select == TRUE) {
            $sql_join_for_select .= "LEFT JOIN user AS last_modifier ON forms.last_modified_user_id = last_modifier.user_id\n";
        }

        // if a submitted form comment info join is required, then add join
        if ($submitted_form_info_join_required_for_select == TRUE) {
            $sql_join_for_select .= "LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_item_view_page_id . "'))\n";
        }

        // if a newest comment join is required, then add join
        if ($newest_comment_join_required_for_select == TRUE) {
            $sql_join_for_select .= "LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id\n";
        }

        // if a newest comment submitter join is required, then add join
        if ($newest_comment_submitter_join_required_for_select == TRUE) {
            $sql_join_for_select .= "LEFT JOIN user AS newest_comment_submitter ON newest_comment.created_user_id = newest_comment_submitter.user_id\n";
        }

        $sql_join_for_count = "";

        // if a submitter join is required, then add join
        if ($submitter_join_required_for_count == TRUE) {
            $sql_join_for_count .= "LEFT JOIN user AS submitter ON forms.user_id = submitter.user_id\n";
        }

        // if a last modifier join is required, then add join
        if ($last_modifier_join_required_for_count == TRUE) {
            $sql_join_for_count .= "LEFT JOIN user AS last_modifier ON forms.last_modified_user_id = last_modifier.user_id\n";
        }

        // if a submitted form comment info join is required, then add join
        if ($submitted_form_info_join_required_for_count == TRUE) {
            $sql_join_for_count .= "LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_item_view_page_id . "'))\n";
        }

        // if a newest comment join is required, then add join
        if ($newest_comment_join_required_for_count == TRUE) {
            $sql_join_for_count .= "LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id\n";
        }
        
        $page_number = '';
        
        // if there is a specific page number for this page, then use it
        if (isset($_GET[$page_id . '_page_number']) == TRUE) {
            $page_number = $_REQUEST[$page_id . '_page_number'];
            
        // else if there is a general page number, then use it (this is for backwards compatibility)
        } else if (isset($_REQUEST['page_number']) == TRUE) {
            $page_number = $_REQUEST['page_number'];
        }
        
        // convert page number to integer
        settype($page_number, 'integer');
        
        // if page number was not passed or was invalid, set page number to 1
        if ($page_number <= 0) {
            $page_number = 1;
        }
        
        // get total number of results for all pages
        $query =
            "SELECT COUNT(*)
            FROM forms
            " . $sql_join_for_count . "
            WHERE
                (forms.page_id = '" . escape($custom_form_page_id) . "')
                " . $sql_where . "
                " . $sql_search .
                $sql_viewer_filter;
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_results = $row[0];
        
        // if the number of results is greater than the maximum number of allowed results and the maxmium number of allowed results is greater than 0,
        // then set the number of results to the maximum number of allowed results
        if (($number_of_results > $maximum_number_of_results) && ($maximum_number_of_results > 0)) {
            $number_of_results = $maximum_number_of_results;
        }
        
        // if there is a limit for the maximum number of results per page, then calculate the number of pages
        if ($maximum_number_of_results_per_page > 0) {
            $number_of_pages = ceil($number_of_results / $maximum_number_of_results_per_page);
            
        // else there is no limit for the maximum number of results per page, so there is only one page
        } else {
            $number_of_pages = 1;
        }
        
        // if page number is greater than the number of pages and number of pages is greater than 0, then set page number to last page
        if (($page_number > $number_of_pages) && ($number_of_pages > 0)) {
            $page_number = $number_of_pages;
        }
        
        // determine where result set should start
        $start = ($page_number * $maximum_number_of_results_per_page) - $maximum_number_of_results_per_page;
        
        // determine the number of results for this page
        
        // if this page is not the last page, the number of results for this page is equal to the maximum number of results per page
        if ($page_number != $number_of_pages) {
            $number_of_results_for_this_page = $maximum_number_of_results_per_page;
            
        // else this page is the last page, so calculate the number of results for this page
        } else {
            $number_of_results_for_this_page = $number_of_results - (($page_number - 1) * $maximum_number_of_results_per_page);
        }
        
        // prepare the limit clause
        $sql_limit = "LIMIT " . $start . ", " . $number_of_results_for_this_page;
        
        // get submitted forms
        $forms = db_items(
            "SELECT
                " . $sql_select . "
            FROM forms
            " . $sql_join_for_select . "
            WHERE
                (forms.page_id = '" . escape($custom_form_page_id) . "')
                " . $sql_where . "
                " . $sql_search . "
                " . $sql_viewer_filter . "
            " . $sql_order_by . "
            " . $sql_limit);
        
        // loop through submitted forms, in order to prepare content.
        foreach ($forms as $key => $form) {

            $form['content'] = $layout;

            // If pretty URLs are enabled for the custom form, and this submitted form has an address name,
            // then output pretty URL.
            if (($pretty_urls == true) && ($form['address_name'] != '')) {
                $form['url'] = PATH . encode_url_path($current_page_name) . '/' . $form['address_name'] . '?send_to=' . urlencode($send_to);

            // Otherwise, output non-pretty URL.
            } else {
                $form['url'] = PATH . encode_url_path($form_item_view_page_name) . '?r=^^reference_code^^&send_to=' . urlencode($send_to);
            }

            $form['content'] = str_replace('^^form_item_view^^', h($form['url']), $form['content']);
            
            // get all conditionals
            preg_match_all('/\[\[(.*?\^\^(.*?)\^\^.*?)(\|\|(.*?))?\]\]/si', $form['content'], $conditionals, PREG_SET_ORDER);
            
            // loop through all conditionals
            foreach ($conditionals as $conditional) {
                $whole_string = $conditional[0];
                $positive_string = $conditional[1];
                $negative_string = $conditional[4];
                $field_name = mb_strtolower($conditional[2]);
                
                // if field name is reference code and there is another field, use the other field,
                // because we don't want to use reference code for conditional checking
                if (($field_name == 'reference_code') && (preg_match('/\[\[(.*?\^\^reference_code\^\^.*?\^\^(.*?)\^\^.*?)(\|\|(.*?))?\]\]/si', $whole_string, $conditional))) {
                    $field_name = mb_strtolower($conditional[2]);
                }
                
                // assume that the field name is not valid, until we find out otherwise
                // we don't want to replace the conditional if the field name is not valid,
                // so that a ^^name^^ conditional will be left alone if used with an e-mail campaign
                $field_name_valid = FALSE;

                $field_group = '';
                
                // loop through the standard fields in order to determine if the field name is a valid standard field name
                foreach ($standard_fields as $standard_field) {
                    // if this standard field value matches the field name,
                    // then the field name is valid, so remember that and break out of the loop
                    if ($standard_field['value'] == $field_name) {
                        $field_name_valid = TRUE;
                        $field_group = 'standard';
                        break;
                    }
                }
                
                $custom_field_id = '';

                // if we don't know if the field name is valid yet, then loop through the custom fields
                // in order to check if the field name is a valid custom field
                if ($field_name_valid == FALSE) {
                    foreach ($custom_fields as $custom_field) {
                        // if this custom field name matches the field name, then the field name is valid,
                        // so remember that and break out of the loop
                        if (mb_strtolower($custom_field['name']) == $field_name) {
                            $field_name_valid = TRUE;
                            $field_group = 'custom';
                            $field_id = $custom_field['id'];
                            break;
                        }
                    }
                }
                
                // if the field name is valid, then replace conditional
                if ($field_name_valid == TRUE) {
                    // if this field is a standard field, then set the select name in a certain way
                    if ($field_group == 'standard') {
                        $select_name = $field_name;

                    // else this field is a custom field, so set the select name in a different way
                    } else {
                        $select_name = 'field_' . $field_id;
                    }

                    // if there is data to output, use first part of conditional
                    if ($form[$select_name] != '') {
                        $form['content'] = str_replace($whole_string, $positive_string, $form['content']);
                        
                    // else there is no data to output, so use second part of conditional
                    } else {
                        $form['content'] = str_replace($whole_string, $negative_string, $form['content']);
                    }
                }
            }

            // get all variables so they can be replaced with data
            preg_match_all('/\^\^(.*?)\^\^(%%(.*?)%%)?/i', $form['content'], $variables, PREG_SET_ORDER);

            // loop through the variables in order to replace them with data
            foreach ($variables as $variable) {
                $whole_string = $variable[0];
                $field_name = mb_strtolower($variable[1]);

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
                        if (mb_strtolower($custom_field['name']) == $field_name) {
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
                            $data = $form[$field_name];
                            break;
                        
                        case 'custom':
                            $data = $form['field_' . $field_id];

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
                        if (($field_name == 'newest_comment_name') && ($data == '') && ($form['newest_comment_id'] != '')) {
                            $data = 'Anonymous';
                        }
                        
                        // If this is the submitter field and badge is enabled for the submitter,
                        // or if this is the last modifier field and badge is enabled for the last modifier,
                        // or if this is the newest comment name field and badge is enabled for the newest comment submitter,
                        // then add badge
                        if (
                            (($field_name == 'submitter') && ($form['submitter_badge'] == 1) && (($form['submitter_badge_label'] != '') || (BADGE_LABEL != '')))
                            || (($field_name == 'last_modifier') && ($form['last_modifier_badge'] == 1) && (($form['last_modifier_badge_label'] != '') || (BADGE_LABEL != '')))
                            || (($field_name == 'newest_comment_name') && ($form['newest_comment_submitter_badge'] == 1) && (($form['newest_comment_submitter_badge_label'] != '') || (BADGE_LABEL != '')))
                        ) {
                            $badge_label = '';

                            // Get the user's badge label differently based on the field.
                            switch ($field_name) {
                                case 'submitter':
                                    $badge_label = $form['submitter_badge_label'];
                                    break;

                                case 'last_modifier':
                                    $badge_label = $form['last_modifier_badge_label'];
                                    break;

                                case 'newest_comment_name':
                                    $badge_label = $form['newest_comment_submitter_badge_label'];
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
                    $form['content'] = preg_replace('/' . preg_quote($whole_string, '/') . '/', addcslashes($data, '\\$'), $form['content'], 1);
                }
            }

            // Update forms array so it contains updated content and etc.
            $forms[$key] = $form;

        }
        
        $output_pagination = '';
        $previous_page_url = '';
        $next_page_url = '';

        // If there is more than one page, then prepare pagination.
        if ($number_of_pages > 1) {

            if ($layout_type == 'system') {

                $output_pagination .= '<div style="clear: both;"></div><div class="software_pagination">';
                
                // if the current URL was not already parsed above for the search feature, then parse the current URL in order to prepare pagination links
                if (isset($url_parts) == FALSE) {
                    // get current URL parts in order to deal with query string parameters
                    $url_parts = parse_url(REQUEST_URL);
                    
                    // put query string parameters into an array
                    parse_str($url_parts['query'], $query_string_parameters);
                }
                
                $pagination_url = $url_parts['path'] . '?';
                
                $query_string = '';

                // loop through the query string parameters in order to build query string
                foreach ($query_string_parameters as $name => $value) {
                    // if this is not the specific page number parameter, then add it to the query string
                    if ($name != $page_id . '_page_number') {
                        // If this field can have multiple values (e.g. check box group), then loop through value array in order to add a name/value pair to the query string for every value.
                        if (is_array($value) == TRUE) {
                            foreach ($value as $subvalue) {
                                // if this is not the first item, then add an ampersand
                                if ($query_string != '') {
                                    $query_string .= '&';
                                }
                                
                                $query_string .= urlencode($name . '[]') . '=' . urlencode($subvalue);
                            }

                        // Otherwise this field has just one value, so output name/value pair for it.
                        } else {
                            // if this is not the first item, then add an ampersand
                            if ($query_string != '') {
                                $query_string .= '&';
                            }
                            
                            $query_string .= urlencode($name) . '=' . urlencode($value);
                        }
                    }
                }
                
                $pagination_url .= $query_string;
                
                // if the query string was not blank, then add an ampersand
                if ($query_string != '') {
                    $pagination_url .= '&';
                }
                
                $pagination_url .= $page_id . '_page_number=';
                
                $output_pagination_url = h($pagination_url);
                
                $previous_page_number = $page_number - 1;
                
                $output_previous_label = '&lt;';
                
                // if previous page number is greater than zero, output previous link
                if ($previous_page_number > 0) {
                    $output_pagination .= '<a href="' . $output_pagination_url . $previous_page_number . '" class="previous">' . $output_previous_label . '</a>';
                    
                // else previous page number is zero, so output span instead of link
                } else {
                    $output_pagination .= '<span class="previous">' . $output_previous_label . '</span>';
                }
                
                // create pagination page numbers array that will store the page numbers that will be outputted
                $pagination_pages_numbers = array();
                
                // store the first page number
                $pagination_page_numbers[] = 1;
                
                // loop in order to store up to 3 page numbers before current page number
                for ($i = ($page_number - 3); $i < $page_number; $i++) {
                    // if number is greater than 1, then store page number
                    if ($i > 1) {
                        $pagination_page_numbers[] = $i;
                    }
                }
                
                // if current page number is greater than 1 and less than number of pages, then store current page number
                if (($page_number > 1) && ($page_number < $number_of_pages)) {
                    $pagination_page_numbers[] = $page_number;
                }
                
                // loop in order to store up to 3 page numbers after current page number
                for ($i = ($page_number + 1); $i <= ($page_number + 3); $i++) {
                    // if number is less than number of pages, then store page number
                    if ($i < $number_of_pages) {
                        $pagination_page_numbers[] = $i;
                    }
                }
                
                // store the last page number
                $pagination_page_numbers[] = $number_of_pages;
                
                // loop through all pagination page numbers
                foreach ($pagination_page_numbers as $key => $pagination_page_number) {
                    // if last pagination page number is less than one less than this page number, then output ellipsis
                    if ($pagination_page_numbers[$key - 1] < ($pagination_page_number - 1)) {
                        $output_pagination .= '<span>...</span>';
                    }
                    
                    // if this number is the current page number, then output span
                    if ($pagination_page_number == $page_number) {
                        $output_pagination .= '<span class="current">' . $pagination_page_number . '</span>';
                        
                    // else this number is not the current page number, so output link
                    } else {
                        $output_pagination .= '<a href="' . $output_pagination_url . $pagination_page_number . '" class="number">' . $pagination_page_number . '</a>';
                    }
                }
                
                $next_page_number = $page_number + 1;
                
                $output_next_label = '&gt;';
                
                // if next page number is less than or equal to the total number of pages, output next link
                if ($next_page_number <= $number_of_pages) {
                    $output_pagination .= '<a href="' . $output_pagination_url . $next_page_number . '" class="next">' . $output_next_label . '</a>';
                    
                // else next page number is greater than total number of pages, so output span instead of link
                } else {
                    $output_pagination .= '<span class="next">' . $output_next_label . '</span>';
                }
                
                $output_pagination .= '</div>';

            // Otherwise, this is a custom layout.
            } else {

                if ($page_number > 1) {
                    $previous_page_url = build_url(array(
                        'url' => REQUEST_URL,
                        'parameters' => array($page_id . '_page_number' => $page_number - 1)));
                }

                if ($page_number < $number_of_pages) {
                    $next_page_url = build_url(array(
                        'url' => REQUEST_URL,
                        'parameters' => array($page_id . '_page_number' => $page_number + 1)));
                }

            }

        }

        if (
            $browse_enabled
            and
            (
                ($liveform->get($page_id . '_browse_field_id') != '')
                and ($liveform->get($page_id . '_browse_filter') != '')
            )
        ) {
            $browse_active = true;
        }

        if (
            $search
            and
            (
                ($search_query != '')
                or $liveform->field_in_session($page_id . '_simple_submit')
                or
                (
                    $search_advanced
                    and
                    (
                        $advanced_filter
                        or $liveform->field_in_session($page_id . '_advanced_submit')
                    )
                )
            )
        ) {
            $search_active = true;
        }

    // Otherwise it is required that the visitor browse or search first
    // and the visitor has not done that yet, so remember that.
    } else {
        $browse_or_search_required = true;
    }

    $content = render_layout(array(
        'page_id' => $page_id,
        'messages' => $liveform->get_messages(),
        'attributes' => $attributes,
        'browse' => $browse_enabled,
        'browse_expanded' => $browse_expanded,
        'browse_active' => $browse_active,
        'browse_fields' => $browse_fields,
        'search' => $search,
        'search_active' => $search_active,
        'search_label' => $search_label,
        'search_advanced' => $search_advanced,
        'search_advanced_content' => $search_advanced_content,
        'search_advanced_expanded' => $search_advanced_expanded,
        'system' => $system,
        'query' => $search_query,
        'browse_or_search_required' => $browse_or_search_required,
        'number_of_forms' => count($forms),
        'total_number_of_forms' => $number_of_results,
        'header' => $header,
        'forms' => $forms,
        'footer' => $footer,
        'standard_fields' => $standard_fields,
        'custom_fields' => $custom_fields,
        'output_pagination' => $output_pagination,
        'page_number' => $page_number,
        'number_of_pages' => $number_of_pages,
        'previous_page_url' => $previous_page_url,
        'next_page_url' => $next_page_url));

    $content = $liveform->prepare($content);

    if ($editable == true) {

        // if output is blank, give it one space in order to prevent region from collapsing
        if ($content == '') {
            $content = '&nbsp;';
        }
        
        $content =
            '<div class="edit_mode" style="position: relative; border: 1px dashed #805FA7; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_form_list_view.php?page_id=' . $page_id . '&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #805FA7; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Form List View: '. h($current_page_name) .'">Edit</a>
            ' . $content . '
            </div>';

        $content = add_edit_button_for_images('form_list_view', 0, $content);

    }

    $liveform->remove_form();

    return
        '<div id="' . $page_id . '_system" class="software_form_list_view">
            ' . $content . '
        </div>
        <div style="clear: both"></div>';

}
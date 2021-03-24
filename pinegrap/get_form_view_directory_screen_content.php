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

function get_form_view_directory_screen_content($properties) {

    // Setup properties
    $current_page_id = $properties['current_page_id'];
    $summary = $properties['summary'];
    $summary_days = $properties['summary_days'];
    $summary_maximum_number_of_results = $properties['summary_maximum_number_of_results'];
    $form_list_view_heading = $properties['form_list_view_heading'];
    $subject_heading = $properties['subject_heading'];
    $number_of_submitted_forms_heading = $properties['number_of_submitted_forms_heading'];
    
    // get all form list views that are selected for this form view directory
    $form_list_views = db_items(
        "SELECT
            form_view_directories_form_list_views_xref.form_list_view_page_id AS page_id,
            form_view_directories_form_list_views_xref.form_list_view_name AS name,
            form_list_view_page.page_name,
            form_list_view_page.page_folder AS folder_id,
            form_view_directories_form_list_views_xref.subject_form_field_id,
            form_list_view_pages.custom_form_page_id,
            form_list_view_pages.form_item_view_page_id,
            form_list_view_pages.viewer_filter,
            form_list_view_pages.viewer_filter_submitter,
            form_list_view_pages.viewer_filter_watcher,
            form_list_view_pages.viewer_filter_editor,
            form_item_view_page.page_name AS form_item_view_page_name
        FROM form_view_directories_form_list_views_xref
        LEFT JOIN page AS form_list_view_page ON form_view_directories_form_list_views_xref.form_list_view_page_id = form_list_view_page.page_id
        LEFT JOIN form_list_view_pages ON
            (form_list_view_page.page_id = form_list_view_pages.page_id)
            AND (form_list_view_pages.collection = 'a')
        LEFT JOIN page AS form_item_view_page ON form_list_view_pages.form_item_view_page_id = form_item_view_page.page_id
        WHERE form_view_directories_form_list_views_xref.form_view_directory_page_id = '$current_page_id'
        ORDER BY name ASC");

    foreach ($form_list_views as $key => $form_list_view) {
        $custom_form_page_id = $form_list_view['custom_form_page_id'];
        $form_item_view_page_id = $form_list_view['form_item_view_page_id'];
        $viewer_filter = $form_list_view['viewer_filter'];
        $viewer_filter_submitter = $form_list_view['viewer_filter_submitter'];
        $viewer_filter_watcher = $form_list_view['viewer_filter_watcher'];
        $viewer_filter_editor = $form_list_view['viewer_filter_editor'];

        // If the visitor is not logged in or the user just has a user role,
        // then we need to verify that this visitor has view access to the form list view.
        if ((USER_LOGGED_IN == false) || (USER_ROLE == 3)) {

            $access_control_type = get_access_control_type($form_list_view['folder_id']);

            // If the access control is private or membership,
            // then continue to check if user has access.
            if (($access_control_type == 'private') || ($access_control_type == 'membership')) {

                // If the visitor is not logged in, then send visitor to login/register.
                if (USER_LOGGED_IN == false) {
                    go(PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
                }

                // If the user does not have view access to the form list view,
                // then remove this form list view from the array, so no data is shown for it,
                // and move to next form list view.
                if (check_view_access($form_list_view['folder_id'], $always_grant_access_for_registration_and_guest = true) == false) {
                    unset($form_list_views[$key]);
                    continue;
                }
            }
        }

        $standard_fields = get_standard_fields_for_view();

        $custom_fields = db_items(
            "SELECT
                id,
                name,
                type,
                multiple,
                wysiwyg,
                size,
                maxlength
            FROM form_fields
            WHERE page_id = '" . $custom_form_page_id . "'");

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

        // Assume that we don't need to join various tables until we find out otherwise.
        $submitter_join_required = FALSE;
        $last_modifier_join_required = FALSE;

        $sql_where = "";

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
            WHERE page_id = '" . $form_list_view['page_id'] . "'
            ORDER BY form_field_id ASC");

        // Loop through all filters in order to prepare SQL.
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

        $form_list_views[$key]['sql_joins'] = $sql_join;

        $form_list_views[$key]['sql_filters'] =
            $sql_where .
            $sql_viewer_filter;
    }

    $output_search = '';
    $sql_search = "";

    // If the search is enabled, then output search field.
    if ($_GET[$current_page_id . '_search'] == 'true') {
        // get current URL parts in order to deal with query string parameters
        $url_parts = parse_url(get_request_uri());
        
        // put query string parameters into an array
        parse_str($url_parts['query'], $query_string_parameters);
        
        $output_hidden_fields = '';
        
        // loop through the query string parameters in order to prepare hidden fields for each query string parameter,
        // so that we don't lose any when the form is submitted
        foreach ($query_string_parameters as $name => $value) {
            // if this is not the specific query parameter (already going to be a field for that),
            // then add hidden field for it.
            if ($name != $current_page_id . '_query') {
                $output_hidden_fields .= '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '" />' . "\n";
            }
        }

        $output_search =
            '<div class="form_view_directory_search mobile_left mobile_width">
                <form action="" method="get" class="mobile_align_left" style="text-align: right; margin: 0em 0em 1em 0em">
                    ' . $output_hidden_fields . '
                    <span>Search by Submitter:</span>
                    <span class="search">
                        <span class="simple">
                            <input type="text" name="' . $current_page_id . '_query" value="' . h($_GET[$current_page_id . '_query']) . '" class="software_input_text mobile_fixed_width query" style="margin-bottom: 0 !important" placeholder="Enter Username" />
                            <input type="submit" title="Search by Submitter" value="" class="submit" />
                        </span>
                    </span>
                </form>
            </div>';

        // If the visitor has searched, then prepare SQL filter for the username.
        if ($_GET[$current_page_id . '_query'] != '') {
            $sql_search = "AND (SELECT user.user_username FROM user WHERE user.user_id = forms.user_id) LIKE '%" . escape(escape_like($_GET[$current_page_id . '_query'])) . "%'";
        }
    }
    
    $output_summary = '';
    
    // if summary is enabled, then prepare to output it
    if ($summary == 1) {
        // if summary days is equal to 1, then output a specific summary days message
        if ($summary_days == 1) {
            $output_summary_days = 'last day';
            
        // else summary days is not equal to 1, so output a different summary days message
        } else {
            $output_summary_days = 'last ' . $summary_days . ' days';
        }

        $summary_timestamp = time() - ($summary_days * 86400);

        $output_most_recent_link_style = '';
        $output_most_viewed_link_style = '';
        $output_most_active_link_style = '';
        $output_summary_table = '';

        // Create an array that we will use to remember if pretty URLs are enabled
        // for a custom form or not, so we don't have to check multiple times.
        $remember_pretty_urls = array();

        switch ($_GET['summary']) {
            case 'most_recent':
            default:
                $output_most_recent_link_style = ' style="font-weight: bold"';

                $most_recent_submitted_forms = array();
                
                // loop through the form list views in order to get the most recent submitted forms or the submitted forms with the most recent comments
                foreach ($form_list_views as $form_list_view) {
                    // Get the most recent submitted forms for this form list view.
                    $submitted_forms = db_items(
                        "SELECT
                            forms.id,
                            (SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $form_list_view['subject_form_field_id'] . "') LIMIT 1) AS subject,
                            forms.reference_code,
                            forms.address_name,
                            submitted_form_info.number_of_comments,
                            submitted_form_info.number_of_views,
                            GREATEST(forms.submitted_timestamp, IFNULL(newest_comment.created_timestamp, '')) AS newest_activity_timestamp
                        FROM forms
                        LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_list_view['form_item_view_page_id'] . "'))
                        LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id
                        " . $form_list_view['sql_joins'] . "
                        WHERE
                            (forms.page_id = '" . $form_list_view['custom_form_page_id'] . "')
                            AND (GREATEST(forms.submitted_timestamp, IFNULL(newest_comment.created_timestamp, '')) > $summary_timestamp)
                            $sql_search
                            " . $form_list_view['sql_filters'] . "
                        ORDER BY newest_activity_timestamp DESC
                        LIMIT $summary_maximum_number_of_results");

                    foreach ($submitted_forms as $submitted_form) {
                        $most_recent_submitted_forms[] = array(
                            'newest_activity_timestamp' => $submitted_form['newest_activity_timestamp'],
                            'form_list_view_name' => $form_list_view['name'],
                            'id' => $submitted_form['id'],
                            'subject' => $submitted_form['subject'],
                            'custom_form_page_id' => $form_list_view['custom_form_page_id'],
                            'form_list_view_page_name' => $form_list_view['page_name'],
                            'form_item_view_page_id' => $form_list_view['form_item_view_page_id'],
                            'form_item_view_page_name' => $form_list_view['form_item_view_page_name'],
                            'reference_code' => $submitted_form['reference_code'],
                            'address_name' => $submitted_form['address_name'],
                            'number_of_comments' => $submitted_form['number_of_comments'],
                            'number_of_views' => $submitted_form['number_of_views']
                        );
                    }
                }
                
                // sort the array so that the most recent submitted forms are listed first
                rsort($most_recent_submitted_forms);
                
                // update the array to only contain the maxmium number of results that the summary allows
                $most_recent_submitted_forms = array_slice($most_recent_submitted_forms, 0, $summary_maximum_number_of_results);
                
                $output_most_recent_submitted_form_rows = '';

                $row_count = 1;
                
                // loop through the most recent submitted forms, in order to prepare to output them
                foreach ($most_recent_submitted_forms as $most_recent_submitted_form) {
                    // if the subject is greater than 100 characters, then shorten the subject
                    if (mb_strlen($most_recent_submitted_form['subject']) > 100) {
                        $most_recent_submitted_form['subject'] = mb_substr($most_recent_submitted_form['subject'], 0, 100) . '...';
                    }
                    
                    $output_form_item_view_link_start = '';
                    $output_form_item_view_link_end = '';
                    
                    // if there is a form item view page, then prepare to link the subject to it
                    if ($most_recent_submitted_form['form_item_view_page_name'] != '') {
                        // If we have already found if pretty urls are enabled for
                        // this custom form, then use that.
                        if (isset($remember_pretty_urls[$most_recent_submitted_form['custom_form_page_id']]) == true) {
                            $pretty_urls = $remember_pretty_urls[$most_recent_submitted_form['custom_form_page_id']];

                        // Otherwise figure out if pretty URLs are enabled and remember that.
                        } else {
                            $pretty_urls = check_if_pretty_urls_are_enabled($most_recent_submitted_form['custom_form_page_id']);
                            $remember_pretty_urls[$most_recent_submitted_form['custom_form_page_id']] = $pretty_urls;
                        }

                        // If pretty URLs are enabled for the custom form, and this submitted form has
                        // an address name, then prepare pretty URL.
                        if (($pretty_urls == true) && ($most_recent_submitted_form['address_name'] != '')) {
                            $output_url = h(PATH . encode_url_path($most_recent_submitted_form['form_list_view_page_name']) . '/' . $most_recent_submitted_form['address_name']);

                        // Otherwise, prepare ugly URL.
                        } else {
                            $output_url = h(PATH . encode_url_path($most_recent_submitted_form['form_item_view_page_name']) . '?r=' . $most_recent_submitted_form['reference_code']);
                        }

                        $output_form_item_view_link_start = '<a href="' . $output_url . '">';
                        $output_form_item_view_link_end = '</a>';
                    }
                    
                    // if the number of comment is blank, then set it to to 0
                    if ($most_recent_submitted_form['number_of_comments'] == '') {
                        $most_recent_submitted_form['number_of_comments'] = 0;
                    }

                    // if the number of views is blank, then set it to to 0
                    if ($most_recent_submitted_form['number_of_views'] == '') {
                        $most_recent_submitted_form['number_of_views'] = 0;
                    }
                    
                    $output_most_recent_submitted_form_rows .=
                        '<tr class="data row_' . ($row_count % 2) . '">
                            <td style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top">' . $output_form_item_view_link_start . h($most_recent_submitted_form['subject']) . $output_form_item_view_link_end . ' <span style="font-size: 75%">(' . h($most_recent_submitted_form['form_list_view_name']) . ')</span></td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_recent_submitted_form['number_of_comments']) . '</td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_recent_submitted_form['number_of_views']) . '</td>
                            <td class="mobile_left mobile_align_left" style="text-align: left; vertical-align: top; white-space: nowrap">' . get_relative_time(array('timestamp' => $most_recent_submitted_form['newest_activity_timestamp'])) . '</td>
                        </tr>';

                    $row_count++;
                }

                $output_summary_table =
                    '<table id="software_form_view_directory_summary_table_most_recent">
                        <tr class="heading" style="border: none">
                            <th style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: bottom; white-space: nowrap">' . h($subject_heading) . '</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Comments</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Views</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top; white-space: nowrap">Last Post</th>
                        </tr>
                        ' . $output_most_recent_submitted_form_rows . '
                    </table>';

                break;

            case 'most_viewed':
                $output_most_viewed_link_style = ' style="font-weight: bold"';

                $most_viewed_submitted_forms = array();
                
                // loop through the form list views in order to get the most viewed submitted forms
                foreach ($form_list_views as $form_list_view) {
                    $submitted_forms = db_items(
                        "SELECT
                            forms.id,
                            (SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $form_list_view['subject_form_field_id'] . "') LIMIT 1) AS subject,
                            forms.reference_code,
                            forms.address_name,
                            submitted_form_info.number_of_comments,
                            submitted_form_info.number_of_views,
                            GREATEST(forms.submitted_timestamp, IFNULL(newest_comment.created_timestamp, '')) AS newest_activity_timestamp,
                            (
                                SELECT COUNT(*)
                                FROM submitted_form_views
                                WHERE
                                    (submitted_form_views.submitted_form_id = forms.id)
                                    AND (submitted_form_views.page_id = '" . $form_list_view['form_item_view_page_id'] . "')
                                    AND (submitted_form_views.timestamp > $summary_timestamp)
                            ) AS number_of_views_for_date_range
                        FROM forms
                        LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_list_view['form_item_view_page_id'] . "'))
                        LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id
                        " . $form_list_view['sql_joins'] . "
                        WHERE
                            (forms.page_id = '" . $form_list_view['custom_form_page_id'] . "')
                            AND (submitted_form_info.number_of_views > 0)
                            $sql_search
                            " . $form_list_view['sql_filters'] . "
                        HAVING number_of_views_for_date_range > 0
                        ORDER BY number_of_views_for_date_range DESC
                        LIMIT $summary_maximum_number_of_results");

                    foreach ($submitted_forms as $submitted_form) {
                        $most_viewed_submitted_forms[] = array(
                            'number_of_views_for_date_range' => $submitted_form['number_of_views_for_date_range'],
                            'id' => $submitted_form['id'],
                            'subject' => $submitted_form['subject'],
                            'custom_form_page_id' => $form_list_view['custom_form_page_id'],
                            'form_list_view_page_name' => $form_list_view['page_name'],
                            'form_list_view_name' => $form_list_view['name'],
                            'form_item_view_page_id' => $form_list_view['form_item_view_page_id'],
                            'form_item_view_page_name' => $form_list_view['form_item_view_page_name'],
                            'reference_code' => $submitted_form['reference_code'],
                            'address_name' => $submitted_form['address_name'],
                            'number_of_comments' => $submitted_form['number_of_comments'],
                            'number_of_views' => $submitted_form['number_of_views'],
                            'newest_activity_timestamp' => $submitted_form['newest_activity_timestamp']
                        );
                    }
                }
                
                // sort the array so that the most viewed submitted forms are listed first
                rsort($most_viewed_submitted_forms);
                
                // update the array to only contain the maxmium number of results that the summary allows
                $most_viewed_submitted_forms = array_slice($most_viewed_submitted_forms, 0, $summary_maximum_number_of_results);
                
                $output_most_viewed_submitted_form_rows = '';

                $row_count = 1;

                // loop through the most viewed submitted forms, in order to prepare to output them
                foreach ($most_viewed_submitted_forms as $most_viewed_submitted_form) {
                    // if the subject is greater than 100 characters, then shorten the subject
                    if (mb_strlen($most_viewed_submitted_form['subject']) > 100) {
                        $most_viewed_submitted_form['subject'] = mb_substr($most_viewed_submitted_form['subject'], 0, 100) . '...';
                    }
                    
                    $output_form_item_view_link_start = '';
                    $output_form_item_view_link_end = '';
                    
                    // if there is a form item view page, then prepare to link the subject to it
                    if ($most_viewed_submitted_form['form_item_view_page_name'] != '') {
                        // If we have already found if pretty urls are enabled for
                        // this custom form, then use that.
                        if (isset($remember_pretty_urls[$most_viewed_submitted_form['custom_form_page_id']]) == true) {
                            $pretty_urls = $remember_pretty_urls[$most_viewed_submitted_form['custom_form_page_id']];

                        // Otherwise figure out if pretty URLs are enabled and remember that.
                        } else {
                            $pretty_urls = check_if_pretty_urls_are_enabled($most_viewed_submitted_form['custom_form_page_id']);
                            $remember_pretty_urls[$most_viewed_submitted_form['custom_form_page_id']] = $pretty_urls;
                        }

                        // If pretty URLs are enabled for the custom form, and this submitted form has
                        // an address name, then prepare pretty URL.
                        if (($pretty_urls == true) && ($most_viewed_submitted_form['address_name'] != '')) {
                            $output_url = h(PATH . encode_url_path($most_viewed_submitted_form['form_list_view_page_name']) . '/' . $most_viewed_submitted_form['address_name']);

                        // Otherwise, prepare ugly URL.
                        } else {
                            $output_url = h(PATH . encode_url_path($most_viewed_submitted_form['form_item_view_page_name']) . '?r=' . $most_viewed_submitted_form['reference_code']);
                        }

                        $output_form_item_view_link_start = '<a href="' . $output_url . '">';
                        $output_form_item_view_link_end = '</a>';
                    }
                    
                    // if the number of comments is blank, then set it to to 0
                    if ($most_viewed_submitted_form['number_of_comments'] == '') {
                        $most_viewed_submitted_form['number_of_comments'] = 0;
                    }

                    // if the number of views is blank, then set it to to 0
                    if ($most_viewed_submitted_form['number_of_views'] == '') {
                        $most_viewed_submitted_form['number_of_views'] = 0;
                    }
                    
                    $output_most_viewed_submitted_form_rows .=
                        '<tr class="data row_' . ($row_count % 2) . '">
                            <td style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top">' . $output_form_item_view_link_start . h($most_viewed_submitted_form['subject']) . $output_form_item_view_link_end . ' <span style="font-size: 75%">(' . h($most_viewed_submitted_form['form_list_view_name']) . ')</span></td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_viewed_submitted_form['number_of_comments']) . '</td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_viewed_submitted_form['number_of_views']) . '</td>
                            <td class="mobile_left mobile_align_left" style="text-align: left; vertical-align: top; white-space: nowrap">' . get_relative_time(array('timestamp' => $most_viewed_submitted_form['newest_activity_timestamp'])) . '</td>
                        </tr>';
                        
                    $row_count++;
                }

                $output_summary_table =
                    '<table id="software_form_view_directory_summary_table_most_viewed">
                        <tr class="heading" style="border: none">
                            <th style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: bottom; white-space: nowrap">' . h($subject_heading) . '</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Comments</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Views</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top; white-space: nowrap">Last Post</th>
                        </tr>
                        ' . $output_most_viewed_submitted_form_rows . '
                    </table>';

                break;

            case 'most_active':
                $output_most_active_link_style = ' style="font-weight: bold"';

                $most_active_submitted_forms = array();

                // loop through the form list views in order to get the most active submitted forms
                foreach ($form_list_views as $form_list_view) {
                    $submitted_forms = db_items(
                        "SELECT
                            forms.id,
                            (SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $form_list_view['subject_form_field_id'] . "') LIMIT 1) AS subject,
                            forms.reference_code,
                            forms.address_name,
                            submitted_form_info.number_of_comments,
                            submitted_form_info.number_of_views,
                            GREATEST(forms.submitted_timestamp, IFNULL(newest_comment.created_timestamp, '')) AS newest_activity_timestamp,
                            (
                                SELECT COUNT(*)
                                FROM comments
                                WHERE
                                    (comments.page_id = '" . $form_list_view['form_item_view_page_id'] . "')
                                    AND (comments.item_id = forms.id)
                                    AND (comments.item_type = 'submitted_form')
                                    AND (comments.published = '1')
                                    AND (comments.created_timestamp > $summary_timestamp)
                            ) AS number_of_comments_for_date_range
                        FROM forms
                        LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_list_view['form_item_view_page_id'] . "'))
                        LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id
                        " . $form_list_view['sql_joins'] . "
                        WHERE
                            (forms.page_id = '" . $form_list_view['custom_form_page_id'] . "')
                            AND (newest_comment.created_timestamp > $summary_timestamp)
                            $sql_search
                            " . $form_list_view['sql_filters'] . "
                        HAVING number_of_comments_for_date_range > 0
                        ORDER BY number_of_comments_for_date_range DESC
                        LIMIT $summary_maximum_number_of_results");

                    foreach ($submitted_forms as $submitted_form) {
                        $most_active_submitted_forms[] = array(
                            'number_of_comments_for_date_range' => $submitted_form['number_of_comments_for_date_range'],
                            'id' => $submitted_form['id'],
                            'subject' => $submitted_form['subject'],
                            'custom_form_page_id' => $form_list_view['custom_form_page_id'],
                            'form_list_view_page_name' => $form_list_view['page_name'],
                            'form_list_view_name' => $form_list_view['name'],
                            'form_item_view_page_id' => $form_list_view['form_item_view_page_id'],
                            'form_item_view_page_name' => $form_list_view['form_item_view_page_name'],
                            'reference_code' => $submitted_form['reference_code'],
                            'address_name' => $submitted_form['address_name'],
                            'number_of_comments' => $submitted_form['number_of_comments'],
                            'number_of_views' => $submitted_form['number_of_views'],
                            'newest_activity_timestamp' => $submitted_form['newest_activity_timestamp']
                        );
                    }
                }
                
                // sort the array so that the most active submitted forms are listed first
                rsort($most_active_submitted_forms);
                
                // update the array to only contain the maxmium number of results that the summary allows
                $most_active_submitted_forms = array_slice($most_active_submitted_forms, 0, $summary_maximum_number_of_results);
                
                $output_most_active_submitted_form_rows = '';

                $row_count = 1;

                // loop through the most active submitted forms, in order to prepare to output them
                foreach ($most_active_submitted_forms as $most_active_submitted_form) {
                    // if the subject is greater than 100 characters, then shorten the subject
                    if (mb_strlen($most_active_submitted_form['subject']) > 100) {
                        $most_active_submitted_form['subject'] = mb_substr($most_active_submitted_form['subject'], 0, 100) . '...';
                    }
                    
                    $output_form_item_view_link_start = '';
                    $output_form_item_view_link_end = '';
                    
                    // if there is a form item view page, then prepare to link the subject to it
                    if ($most_active_submitted_form['form_item_view_page_name'] != '') {
                        // If we have already found if pretty urls are enabled for
                        // this custom form, then use that.
                        if (isset($remember_pretty_urls[$most_active_submitted_form['custom_form_page_id']]) == true) {
                            $pretty_urls = $remember_pretty_urls[$most_active_submitted_form['custom_form_page_id']];

                        // Otherwise figure out if pretty URLs are enabled and remember that.
                        } else {
                            $pretty_urls = check_if_pretty_urls_are_enabled($most_active_submitted_form['custom_form_page_id']);
                            $remember_pretty_urls[$most_active_submitted_form['custom_form_page_id']] = $pretty_urls;
                        }

                        // If pretty URLs are enabled for the custom form, and this submitted form has
                        // an address name, then prepare pretty URL.
                        if (($pretty_urls == true) && ($most_active_submitted_form['address_name'] != '')) {
                            $output_url = h(PATH . encode_url_path($most_active_submitted_form['form_list_view_page_name']) . '/' . $most_active_submitted_form['address_name']);

                        // Otherwise, prepare ugly URL.
                        } else {
                            $output_url = h(PATH . encode_url_path($most_active_submitted_form['form_item_view_page_name']) . '?r=' . $most_active_submitted_form['reference_code']);
                        }

                        $output_form_item_view_link_start = '<a href="' . $output_url . '">';
                        $output_form_item_view_link_end = '</a>';
                    }
                    
                    // if the number of comments is blank, then set it to to 0
                    if ($most_active_submitted_form['number_of_comments'] == '') {
                        $most_active_submitted_form['number_of_comments'] = 0;
                    }

                    // if the number of views is blank, then set it to to 0
                    if ($most_active_submitted_form['number_of_views'] == '') {
                        $most_active_submitted_form['number_of_views'] = 0;
                    }
                    
                    $output_most_active_submitted_form_rows .=
                        '<tr class="data row_' . ($row_count % 2) . '">
                            <td style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top">' . $output_form_item_view_link_start . h($most_active_submitted_form['subject']) . $output_form_item_view_link_end . ' <span style="font-size: 75%">(' . h($most_active_submitted_form['form_list_view_name']) . ')</span></td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_active_submitted_form['number_of_comments']) . '</td>
                            <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($most_active_submitted_form['number_of_views']) . '</td>
                            <td class="mobile_left mobile_align_left" style="text-align: left; vertical-align: top; white-space: nowrap">' . get_relative_time(array('timestamp' => $most_active_submitted_form['newest_activity_timestamp'])) . '</td>
                        </tr>';

                    $row_count++;
                }

                $output_summary_table =
                    '<table id="software_form_view_directory_summary_table_most_active">
                        <tr class="heading" style="border: none">
                            <th style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: bottom; white-space: nowrap">' . h($subject_heading) . '</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Comments</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Views</th>
                            <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top; white-space: nowrap">Last Post</th>
                        </tr>
                        ' . $output_most_active_submitted_form_rows . '
                    </table>';

                break;
        }

        $current_url = get_request_uri();

        $most_recent_url = build_url(array(
            'url' => $current_url,
            'parameters' => array('summary' => 'most_recent')));

        $most_viewed_url = build_url(array(
            'url' => $current_url,
            'parameters' => array('summary' => 'most_viewed')));

        $most_active_url = build_url(array(
            'url' => $current_url,
            'parameters' => array('summary' => 'most_active')));
        
        $output_summary =
            '<fieldset class="software_fieldset" style="margin-bottom: 1em">
                <legend class="software_legend">Summary (' . $output_summary_days . ')</legend>
                    <div class="filters" style="margin-bottom: .5em">
                        <a href="' . h($most_recent_url) . '" id="software_form_view_directory_summary_link_most_recent"' . $output_most_recent_link_style . '>Most Recent</a>&nbsp;&nbsp;
                        <a href="' . h($most_viewed_url) . '" id="software_form_view_directory_summary_link_most_viewed"' . $output_most_viewed_link_style . '>Most Viewed</a>&nbsp;&nbsp;
                        <a href="' . h($most_active_url) . '" id="software_form_view_directory_summary_link_most_active"' . $output_most_active_link_style . '>Most Active</a>
                    </div>
                    <div>
                        ' . $output_summary_table . '
                    </div>
            </fieldset>';
    }
    
    $output_form_list_view_rows = '';
    $row_count = 1;
    
    // loop through the form list views in order to prepare to output list
    foreach ($form_list_views as $form_list_view) {
        $info = db_item(
            "SELECT
                COUNT(*) AS number_of_submitted_forms,
                SUM(submitted_form_info.number_of_comments) AS number_of_comments,
                SUM(submitted_form_info.number_of_views) AS number_of_views,
                MAX(GREATEST(forms.submitted_timestamp, IFNULL(newest_comment.created_timestamp, ''))) AS newest_activity_timestamp
            FROM forms
            LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_list_view['form_item_view_page_id'] . "'))
            LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id
            " . $form_list_view['sql_joins'] . "
            WHERE
                (forms.page_id = '" . $form_list_view['custom_form_page_id'] . "')
                $sql_search
                " . $form_list_view['sql_filters']);

        $output_newest_activity = '';

        if ($info['newest_activity_timestamp']) {
            $output_newest_activity = get_relative_time(array('timestamp' => $info['newest_activity_timestamp']));
        }
        
        $output_form_list_view_rows .=
            '<tr class="data row_' . ($row_count % 2) . '">
                <td style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top"><a href="' . OUTPUT_PATH . h(encode_url_path($form_list_view['page_name'])) . '">' . h($form_list_view['name']) . '</a></td>
                <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($info['number_of_submitted_forms']) . '</td>
                <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($info['number_of_comments']) . '</td>
                <td class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . number_format($info['number_of_views']) . '</td>
                <td class="mobile_left mobile_align_left" style="text-align: left; vertical-align: top; white-space: nowrap">' . $output_newest_activity . '</td>
            </tr>';
        $row_count++;
    }
    
    return
        '<div style="margin-bottom: 1em">
            ' . $output_search . '
            ' . $output_summary . '
            <table class="summary">
                <tr class="heading" style="border: none">
                    <th style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top; white-space: nowrap">' . h($form_list_view_heading) . '</th>
                    <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">' . h($number_of_submitted_forms_heading) . '</th>
                    <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Comments</th>
                    <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: right; vertical-align: top; white-space: nowrap">Views</th>
                    <th class="mobile_left" style="padding: 0em 1em 0em 0em; text-align: left; vertical-align: top; white-space: nowrap">Last Post</th>
                </tr>
                ' . $output_form_list_view_rows . '
            </table>
        </div>';
}
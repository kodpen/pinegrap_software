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

// Various functions related to forms.


// Get a list of submitted forms for one custom form.

function get_forms($request) {

    if (!$request['custom_form'] and !$request['custom_form_page_id']) {
        return error_response('We don\'t know which custom form to return forms for. Please pass the custom form page name via "custom_form" or custom form page ID via "custom_form_page_id".');
    }

    // If a page name was passed, then check that way.
    if ($request['custom_form']) {

        $custom_form = db_item("
            SELECT
                page_id,
                page_folder AS folder_id
            FROM page
            WHERE
                page_name = '" . e($request['custom_form']) . "'
                AND page_type = 'custom form'");

        if (!$custom_form) {
            return error_response('The custom form ("' . $request['custom_form'] . '") could not be found.');
        }

    // Otherwise, a page id was passed, so check that way.
    } else {

        $custom_form = db_item("
            SELECT
                page_id,
                page_folder AS folder_id
            FROM page
            WHERE
                page_id = '" . e($request['custom_form_page_id']) . "'
                AND page_type = 'custom form'");

        if (!$custom_form) {
            return error_response('The custom form (ID: ' . $request['custom_form_page_id'] . ') could not be found.');
        }
    }

    // Check access to submitted forms.
    if (
        $request['check_access']
        and (
            !USER_LOGGED_IN
            or
            (
                USER_ROLE > 2
                and (!USER_MANAGE_FORMS or !check_edit_access($custom_form['folder_id']))
            )
        )
    ) {
        return error_response('Access denied.');
    }

    $standard_fields = array();

    $standard_fields['id'] = array(
        'name' => 'id',
        'group' => 'standard',
        'sql' => 'forms.id');

    $standard_fields['reference_code'] = array(
        'name' => 'reference_code',
        'group' => 'standard',
        'sql' => 'forms.reference_code');

    $standard_fields['complete'] = array(
        'name' => 'complete',
        'group' => 'standard',
        'sql' => 'forms.complete');

    $standard_fields['address_name'] = array(
        'name' => 'address_name',
        'group' => 'standard',
        'sql' => 'forms.address_name');

    $standard_fields['tracking_code'] = array(
        'name' => 'tracking_code',
        'group' => 'standard',
        'sql' => 'forms.tracking_code');

    $standard_fields['affiliate_code'] = array(
        'name' => 'affiliate_code',
        'group' => 'standard',
        'sql' => 'forms.affiliate_code');

    $standard_fields['referring_url'] = array(
        'name' => 'referring_url',
        'group' => 'standard',
        'sql' => 'forms.http_referer');

    $standard_fields['submitter'] = array(
        'name' => 'submitter',
        'group' => 'standard',
        'sql' => '(SELECT user.user_username FROM user WHERE user.user_id = forms.user_id)');

    $standard_fields['submitted'] = array(
        'name' => 'submitted',
        'group' => 'standard',
        'sql' => 'forms.submitted_timestamp');

    $standard_fields['modifier'] = array(
        'name' => 'modifier',
        'group' => 'standard',
        'sql' => '(SELECT user.user_username FROM user WHERE user.user_id = forms.last_modified_user_id)');

    $standard_fields['modified'] = array(
        'name' => 'modified',
        'group' => 'standard',
        'sql' => 'forms.last_modified_timestamp');

    $custom_fields = db_items("
        SELECT
            id,
            name,
            type,
            multiple
        FROM form_fields
        WHERE page_id = '" . e($custom_form['page_id']) . "'", 'name');

    // Combine the standard and custom fields into one array.
    $fields = array_merge($standard_fields, $custom_fields);

    $sql_select = "";
    $sql_search = "";
    $filters = array();

    // Loop through the fields to prepare various SQL info.
    foreach ($fields as $field) {

        // If no select fields were passed or this field should be selected, then select it.
        if (!$request['fields'] or in_array($field['name'], $request['fields'])) {

            // If the sql select is not blank, then add a comma and new line for separation.
            if ($sql_select != '') {
                $sql_select .= ",\n";
            }

            // Escape backticks if any exist in field name.
            $field_name_escaped = str_replace('`', '``', $field['name']);

            $sql_select .= get_field_sql($field) . " AS `" . $field_name_escaped . "`";
        }

        // If a search query was passed, then prepare SQL.
        if (isset($request['search']) and $request['search'] != '') {

            // If a field has already been added, then add an or for separation.
            if ($sql_search != '') {
                $sql_search .= " OR ";
            }
            
            $sql_search .= "(" . get_field_sql($field) . " LIKE '%" . e(escape_like($request['search'])) . "%')";
        }

        // If a simple filter was passed for this field, then add filter.
        if (isset($request[$field['name']])) {
            $filters[] = array(
                'field' => $field['name'],
                'operator' => 'is equal to',
                'value' => $request[$field['name']],
            );
        }
    }

    // If advanced filters were passed, then prepare them.
    if ($request['filters'] and is_array($request['filters'])) {

        foreach ($request['filters'] as $filter) {

            if (!isset($filter['field'])) {
                return error_response('A field is missing for a filter.');
            }

            if (!$fields[$filter['field']]) {
                return error_response('The field ("' . $filter['field'] . '") for a filter does not exist.');
            }

            if (!isset($filter['operator'])) {
                return error_response('An operator is missing for a filter.');
            }

            if (
                $filter['operator'] != 'contains'
                and $filter['operator'] != 'does not contain'
                and $filter['operator'] != 'is equal to'
                and $filter['operator'] != 'is not equal to'
                and $filter['operator'] != 'is less than'
                and $filter['operator'] != 'is less than or equal to'
                and $filter['operator'] != 'is greater than'
                and $filter['operator'] != 'is greater than or equal to'
            ) {
                return error_response('The operator ("' . $filter['operator'] . '") for a filter is not valid.');
            }

            if (!isset($filter['value'])) {
                return error_response('A value is missing for a filter.');
            }

            $filters[] = $filter;
        }
    }

    // Prepare SQL for filters.

    $sql_filters = "";

    foreach ($filters as $filter) {

        $sql_filters .=
            " AND (" .

            prepare_sql_operation(
                $filter['operator'],
                get_field_sql($fields[$filter['field']]),
                $filter['value']) .

            ")";
    }

    if ($sql_search) {
        $sql_search = "AND (" . $sql_search . ")";
    }

    // Prepare sort.

    $sql_sort = "";

    // If no sort was passed, then set default to modified descending.
    if (!$request['sort']) {
        $request['sort'] = 'modified';
        $request['sort_order'] = 'desc';
    }

    // If multiple sort fields were passed, then prepare multiple sorts.
    if (is_array($request['sort'])) {

        foreach ($request['sort'] as $sort_field) {

            if (!isset($sort_field['field'])) {
                return error_response('A field is missing for a sort.');
            }

            if (!$fields[$sort_field['field']]) {
                return error_response('The field ("' . $sort_field['field'] . '") for a sort does not exist.');
            }

            if (
                $sort_field['order']
                and $sort_field['order'] != 'asc'
                and $sort_field['order'] != 'asc_num'
                and $sort_field['order'] != 'desc'
                and $sort_field['order'] != 'desc_num'
            ) {
                return error_response('The order ("' . $sort_field['order'] . '") for a sort is not valid. Use "asc", "asc_num", "desc", or "desc_num".');
            }

            // If another sort has already been added, then add a comma and new line for separation.
            if ($sql_sort != '') {
                $sql_sort .= ",\n";
            }

            $sql_sort .= get_field_sql($fields[$sort_field['field']]);

            // If no order was passed, then use ascending as the default.
            if (!$sort_field['order']) {
                $sort_field['order'] = 'asc';
            }

            // If the sort is numerical, then add "+ 0" so MySQL will order numbers properly
            // (1, 2, 10 instead of 1, 10, 2).
            if ($sort_field['order'] == 'asc_num' or $sort_field['order'] == 'desc_num') {
                $sql_sort .= " + 0";
            }

            if ($sort_field['order'] == 'desc' or $sort_field['order'] == 'desc_num') {
                $sql_sort .= " DESC";
            }
        }

    // Otherwise there is a single sort field, so prepare it.
    } else {

        if (!$fields[$request['sort']]) {
            return error_response('The field ("' . $request['sort'] . '") for the sort does not exist.');
        }

        if (
            $request['sort_order']
            and $request['sort_order'] != 'asc'
            and $request['sort_order'] != 'asc_num'
            and $request['sort_order'] != 'desc'
            and $request['sort_order'] != 'desc_num'
        ) {
            return error_response('The order ("' . $request['sort_order'] . '") for the sort is not valid. Use "asc", "asc_num", "desc", or "desc_num".');
        }

        $sql_sort .= get_field_sql($fields[$request['sort']]);

        // If no order was passed, then use ascending as the default.
        if (!$request['sort_order']) {
            $request['sort_order'] = 'asc';
        }

        // If the sort is numerical, then add "+ 0" so MySQL will order numbers properly
        // (1, 2, 10 instead of 1, 10, 2).
        if ($request['sort_order'] == 'asc_num' or $request['sort_order'] == 'desc_num') {
            $sql_sort .= " + 0";
        }

        if ($request['sort_order'] == 'desc' or $request['sort_order'] == 'desc_num') {
            $sql_sort .= " DESC";
        }
    }

    // Prepare limit.

    $sql_limit = "";

    if ($request['limit']) {

        $sql_limit = "LIMIT";

        // If there is an offset, then prepare it.
        if ($request['offset']) {

            // If the offset is not a positive integer, then return error.
            if (
                !is_numeric($request['offset'])
                or $request['offset'] < 1
                or $request['offset'] != round($request['offset'])
            ) {
                return error_response('The offset ("' . $request['offset'] . '") is not valid. Please use a positive integer.');
            }

            $sql_limit .= " " . $request['offset'] . ",";
        }

        // If the limit is not a positive integer, then return error.
        if (
            !is_numeric($request['limit'])
            or $request['limit'] < 1
            or $request['limit'] != round($request['limit'])
        ) {
            return error_response('The limit ("' . $request['limit'] . '") is not valid. Please use a positive integer.');
        }

        $sql_limit .= " " . $request['limit'];
    }

    $forms = db_items("
        SELECT $sql_select
        FROM forms
        WHERE
            (forms.page_id = '" . e($custom_form['page_id']) . "')
            $sql_filters
            $sql_search
        ORDER BY $sql_sort
        $sql_limit");

    return array(
        'status' => 'success',
        'forms' => $forms);
}

function get_field_sql($field) {

    // If this is a standard field, then we have already prepared the SQL, so return it.
    if ($field['group'] == 'standard') {
        return $field['sql'];
    }

    // If this custom field is a pick list and allow multiple selection is enabled,
    // or if this custom field is a check box, then select field in a certain way,
    // so that multiple values may be retrieved.  We don't just use this type of query
    // for all types of fields because it is probably slightly slower.
    if (
        ($field['type'] == 'pick list' and $field['multiple']) or ($field['type'] == 'check box')
    ) {
        return "
            (SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ')
            FROM form_data
            WHERE
                (form_data.form_id = forms.id)
                AND (form_data.form_field_id = '" . $field['id'] . "'))";

    // Otherwise, if this custom field is a file upload field, then select file name.
    } else if ($field['type'] == 'file upload') {

        return "
            (SELECT files.name FROM form_data
            LEFT JOIN files ON form_data.file_id = files.id
            WHERE
                (form_data.form_id = forms.id)
                AND (form_data.form_field_id = '" . $field['id'] . "')
            LIMIT 1)";

    // Otherwise this custom field has any other type, so select field in the default way.
    } else {
        return "
            (SELECT form_data.data FROM form_data
            WHERE
                (form_data.form_id = forms.id)
                AND (form_data.form_field_id = '" . $field['id'] . "')
            LIMIT 1)";
    }
}

// Get info for a single submitted form.

function get_form($request) {

    // We are going to use the get_forms function, so update request to just get one form.
    $request['limit'] = 1;

    $response = get_forms($request);

    if ($response['status'] == 'error') {
        return $response;
    }

    if (!$response['forms']) {
        return error_response('Form could not be found.');
    }

    return array(
        'status' => 'success',
        'form' => $response['forms'][0]);
}
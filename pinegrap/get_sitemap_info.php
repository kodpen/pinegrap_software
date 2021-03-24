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

// create function that is responsible for generating sitemap content for the sitemap.xml file
function get_sitemap_info() {

    // get all non-archived folders in order to determine which are public
    $query = "SELECT folder_id AS id FROM folder WHERE folder_archived = '0'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $folders = array();

    // loop through folders in order to add them to array
    while ($row = mysqli_fetch_assoc($result)) {
        $folders[] = $row;
    }

    $public_folder_ids = array();

    // loop through folders in order to add public folder ID's to array
    foreach ($folders as $folder) {
        // get access control type for folder
        $access_control_type = get_access_control_type($folder['id']);
        
        // if this folder is public, then add it to the public folder ID's array
        if ($access_control_type == 'public') {
            $public_folder_ids[] = $folder['id'];
        }
    }

    // get pages that might need to appear in sitemap (sitemap is enabled and page type is valid)
    $query =
        "SELECT
            page_id as id,
            page_name as name,
            page_folder as folder_id,
            page_home as home,
            page_timestamp as last_modified_timestamp,
            page_type as type
        FROM page
        WHERE
            (sitemap = '1')
            AND
            (
                (page_type = 'standard')
                OR (page_type = 'folder view')
                OR (page_type = 'photo gallery')
                OR (page_type = 'custom form')
                OR (page_type = 'form list view')
                OR (page_type = 'form view directory')
                OR (page_type = 'calendar view')
                OR (page_type = 'catalog')
                OR (page_type = 'express order')
                OR (page_type = 'order form')
                OR (page_type = 'shopping cart')
                OR (page_type = 'search results')
            )
        ORDER BY page_timestamp DESC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $pages = array();

    // loop through pages in order to add them to array
    while ($row = mysqli_fetch_assoc($result)) {
        $pages[] = $row;
    }

    $output_sitemap_urls = '';
    $home_page_added = FALSE;
    $catalog_pages = array();
    $form_list_view_pages = array();
    $calendar_view_pages = array();

    // loop through pages in order to prepare sitemap
    foreach ($pages as $page) {
        // if this page is public, then continue to add it to the sitemap
        if (in_array($page['folder_id'], $public_folder_ids) == TRUE) {
            // if this a home page and a home page has not already been added then add URL with just scheme and hostname for home page
            if (($page['home'] == 'yes') && ($home_page_added == FALSE)) {
                $output_sitemap_urls .=
                    '    <url>' . "\n" .
                    '        <loc>' . URL_SCHEME . HOSTNAME_SETTING . '</loc>' . "\n" .
                    '        <lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $page['last_modified_timestamp']) . '</lastmod>' . "\n" .
                    '        <priority>1.0</priority>' . "\n" .
                    '    </url>' . "\n";
                
                $home_page_added = TRUE;
            }

            $output_path = h(PATH . encode_url_path($page['name']));
            
            $output_sitemap_urls .=
                '    <url>' . "\n" .
                '        <loc>' . URL_SCHEME . HOSTNAME_SETTING . $output_path . '</loc>' . "\n" .
                '        <lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $page['last_modified_timestamp']) . '</lastmod>' . "\n" .
                '    </url>' . "\n";
            
            // for certain types of pages add page to array so that we can later get items that appear through the page
            // we don't want to prepare URL's for all of the items yet, because we want to make sure we get all normal pages before we hit the 50,000 URL limit
            switch ($page['type']) {
                case 'catalog':
                    $catalog_pages[] = $page;
                    break;
                
                case 'form list view':
                    $form_list_view_pages[] = $page;
                    break;
                    
                case 'calendar view':
                    $calendar_view_pages[] = $page;
                    break;
            }
        }
    }
    
    // create an array that will hold all added URL's so that we don't add duplicates
    // different catalog pages might link to the same catalog detail with the same catalog items
    $added_urls = array();

    // loop through all catalog pages in order to prepare URL's to catalog items
    foreach ($catalog_pages as $catalog_page) {
        // get info about this catalog page and the catalog detail page
        $query =
            "SELECT
                catalog_pages.product_group_id,
                catalog_detail_page.page_folder as catalog_detail_folder_id,
                catalog_detail_page.page_name as catalog_detail_page_name,
                catalog_detail_page.sitemap as catalog_detail_sitemap
            FROM catalog_pages
            LEFT JOIN page AS catalog_detail_page ON catalog_pages.catalog_detail_page_id = catalog_detail_page.page_id
            WHERE catalog_pages.page_id = '" . $catalog_page['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $product_group_id = $row['product_group_id'];
        $catalog_detail_folder_id = $row['catalog_detail_folder_id'];
        $catalog_detail_page_name = $row['catalog_detail_page_name'];
        $catalog_detail_sitemap = $row['catalog_detail_sitemap'];
        
        // if there is no product group selected for this catalog page, then get top-level product group
        if ($product_group_id == 0) {
            $query = "SELECT id FROM product_groups WHERE parent_id = '0'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $product_group_id = $row['id'];
        }

        // If the product group is disabled, then skip this catalog page.
        if (!db_value("SELECT enabled FROM product_groups WHERE id = '" . e($product_group_id) . "'")) {
            continue;
        }
        
        $get_catalog_detail_items = FALSE;
        
        // if the catalog detail page is public and sitemap is enabled, then remember that we should add URL's for it to the sitemap
        if ((in_array($catalog_detail_folder_id, $public_folder_ids) == TRUE) && ($catalog_detail_sitemap == 1)) {
            $get_catalog_detail_items = TRUE;
        }
        
        $catalog_items = get_catalog_items_for_sitemap($product_group_id, $get_catalog_detail_items);
        
        // if there is at least one catalog item, then prepare sitemap URL(s)
        if (count($catalog_items) > 0) {
            $output_catalog_path = h(PATH . encode_url_path($catalog_page['name']) . '/');
            $output_catalog_detail_path = h(PATH . encode_url_path($catalog_detail_page_name) . '/');
            
            // loop through the catalog items in order to prepare sitemap URL's
            foreach ($catalog_items as $catalog_item) {
                // if this catalog item is a product group with a browse display type, then prepare path to catalog page
                if (($catalog_item['type'] == 'product_group') && ($catalog_item['display_type'] == 'browse')) {
                    $output_path = $output_catalog_path;
                    
                // else this catalog item is a product group with a select display type or it is a product, so prepare path to catalog detail page
                } else {
                    $output_path = $output_catalog_detail_path;
                }
                
                $output_url = URL_SCHEME . HOSTNAME_SETTING . $output_path . h(encode_url_path($catalog_item['address_name']));
                
                // if this URL has not already been added then add URL to sitemap
                if (in_array($output_url, $added_urls) == FALSE) {
                    $output_sitemap_urls .=
                        '    <url>' . "\n" .
                        '        <loc>' . $output_url . '</loc>' . "\n" .
                        '        <lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $catalog_item['last_modified_timestamp']) . '</lastmod>' . "\n" .
                        '    </url>' . "\n";
                    
                    // remember that we have added this URL
                    $added_urls[] = $output_url;
                }
            }
        }
    }

    // If there is at least one form list view, then prepare URLs for them.
    if (count($form_list_view_pages) > 0) {
        // create an array that will hold all added URL's so that we don't add duplicates
        // different form list views might link to the same form item view
        $added_urls = array();

        // get MySQL version so we can decide how we want to prepare the query
        // in order to optimize performance
        $query = "SELECT VERSION()";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $mysql_version = $row[0];

        $mysql_version_parts = explode('.', $mysql_version);
        $mysql_major_version = $mysql_version_parts[0];
        $mysql_minor_version = $mysql_version_parts[1];

        // if the MySQL version is at least 4.1 then get submitted forms
        // by using subqueries which are not available in previous version.
        // We are not going to get submitted forms if MySQL is older,
        // because performance is poor.
        if (
            (
                ($mysql_major_version == 4)
                && ($mysql_minor_version >= 1)
            )
            || ($mysql_major_version >= 5)
        ) {
            // loop through all form list view pages in order to prepare URL's to submitted forms on form item view pages
            foreach ($form_list_view_pages as $form_list_view_page) {
                // get info about custom form and form item view for this page
                $query =
                    "SELECT
                        form_list_view_pages.custom_form_page_id,
                        form_list_view_pages.viewer_filter,
                        form_item_view_page.page_id as form_item_view_page_id,
                        form_item_view_page.page_folder as form_item_view_folder_id,
                        form_item_view_page.page_name as form_item_view_page_name,
                        form_item_view_page.sitemap as form_item_view_sitemap
                    FROM form_list_view_pages
                    LEFT JOIN page AS form_item_view_page ON form_list_view_pages.form_item_view_page_id = form_item_view_page.page_id
                    WHERE
                        (form_list_view_pages.page_id = '" . $form_list_view_page['id'] . "')
                        AND (form_list_view_pages.collection = 'a')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                
                $custom_form_page_id = $row['custom_form_page_id'];
                $viewer_filter = $row['viewer_filter'];
                $form_item_view_page_id = $row['form_item_view_page_id'];
                $form_item_view_folder_id = $row['form_item_view_folder_id'];
                $form_item_view_page_name = $row['form_item_view_page_name'];
                $form_item_view_sitemap = $row['form_item_view_sitemap'];
                
                // If the viewer filter for the form list view is disabled,
                // and the form item view page is public and sitemap is enabled,
                // then continue to add URL's for it to the sitemap
                if (
                    ($viewer_filter == 0)
                    && (in_array($form_item_view_folder_id, $public_folder_ids) == TRUE)
                    && ($form_item_view_sitemap == 1)
                ) {
                    $standard_fields = get_standard_fields_for_view();

                    $custom_fields = db_items(
                        "SELECT
                            id,
                            name,
                            type,
                            multiple
                        FROM form_fields
                        WHERE page_id = '$custom_form_page_id'");

                    // Assume that we don't need to join various tables until we find out otherwise.
                    $submitter_join_required = FALSE;
                    $last_modifier_join_required = FALSE;
                    $submitted_form_info_join_required = FALSE;
                    $newest_comment_join_required = FALSE;

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
                        WHERE page_id = '" . $form_list_view_page['id'] . "'
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

                    // Get all submitted forms for this form list view.
                    $submitted_forms = db_items(
                        "SELECT
                            forms.id,
                            forms.reference_code,
                            forms.address_name,
                            forms.last_modified_timestamp
                        FROM forms
                        $sql_join
                        WHERE
                            (forms.page_id = '$custom_form_page_id')
                            $sql_where
                        ORDER BY forms.last_modified_timestamp DESC");
                    
                    // if there is at least one submitted form, then prepare sitemap URL(s)
                    if (count($submitted_forms) > 0) {
                        $pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);

                        // If pretty URLs are enabled, then prepare pretty path.
                        if ($pretty_urls == true) {
                            $output_pretty_path = h(PATH . encode_url_path($form_list_view_page['name']) . '/');
                        }

                        // We still need to prepare ugly path even if pretty URLs are enabled,
                        // in case a submitted form does not have an address name.

                        $output_ugly_path = h(PATH . encode_url_path($form_item_view_page_name));
                        
                        // loop through submitted forms in order to prepare sitemap URL's
                        foreach ($submitted_forms as $submitted_form) {
                            // If pretty URLs are enabled for the custom form, and this submitted form has
                            // an address name, then prepare pretty URL.
                            if (($pretty_urls == true) && ($submitted_form['address_name'] != '')) {
                                $output_url = URL_SCHEME . HOSTNAME_SETTING . $output_pretty_path . h($submitted_form['address_name']);

                            // Otherwise, prepare ugly URL.
                            } else {
                                $output_url = URL_SCHEME . HOSTNAME_SETTING . $output_ugly_path . '?r=' . $submitted_form['reference_code'];
                            }
                            
                            // if this URL has not already been added then add URL to sitemap
                            if (in_array($output_url, $added_urls) == FALSE) {
                                $output_sitemap_urls .=
                                    '    <url>' . "\n" .
                                    '        <loc>' . $output_url . '</loc>' . "\n" .
                                    '        <lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $submitted_form['last_modified_timestamp']) . '</lastmod>' . "\n" .
                                    '    </url>' . "\n";
                                
                                // remember that we have added this URL
                                $added_urls[] = $output_url;
                            }
                        }
                    }
                }
            }
        }
    }
    
    // create an array that will hold all added URL's so that we don't add duplicates
    // different calendar views might link to the same calendar event view
    $added_urls = array();

    // loop through all calendar view pages in order to prepare URL's to calendar events on calendar event view pages
    foreach ($calendar_view_pages as $calendar_view_page) {
        // get calendars that are allowed for this calendar view page
        $query = "SELECT calendar_id as id FROM calendar_views_calendars_xref WHERE page_id = '" . $calendar_view_page['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $calendar_ids = array();
        
        // loop through calendars in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $calendar_ids[] = $row['id'];
        }
        
        // if there is at least one calendar allowed for this calendar view page, then continue
        if (count($calendar_ids) > 0) {
            // get calendar event view page info for this calendar view page
            $query =
                "SELECT
                    calendar_event_view_page.page_id as calendar_event_view_page_id,
                    calendar_event_view_page.page_folder as calendar_event_view_folder_id,
                    calendar_event_view_page.page_name as calendar_event_view_page_name,
                    calendar_event_view_page.sitemap as calendar_event_view_sitemap
                FROM calendar_view_pages
                LEFT JOIN page AS calendar_event_view_page ON calendar_view_pages.calendar_event_view_page_id = calendar_event_view_page.page_id
                WHERE calendar_view_pages.page_id = '" . $calendar_view_page['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $calendar_event_view_page_id = $row['calendar_event_view_page_id'];
            $calendar_event_view_folder_id = $row['calendar_event_view_folder_id'];
            $calendar_event_view_page_name = $row['calendar_event_view_page_name'];
            $calendar_event_view_sitemap = $row['calendar_event_view_sitemap'];
            
            // if the calendar event view page is public and sitemap is enabled, then continue to add URL's for it to the sitemap
            if ((in_array($calendar_event_view_folder_id, $public_folder_ids) == TRUE) && ($calendar_event_view_sitemap == 1)) {
                $sql_calendar_condition = "";
                
                // loop through the calendar ID's in order to prepare SQL condition
                foreach ($calendar_ids as $calendar_id) {
                    // if the SQL condition is not blank, then add OR separator
                    if ($sql_calendar_condition != '') {
                        $sql_calendar_condition .= " OR ";
                    }
                    
                    $sql_calendar_condition .= "(calendar_events_calendars_xref.calendar_id = '" . $calendar_id . "')";
                }
                
                // get all calendar events for this calendar view page
                $query =
                    "SELECT
                        DISTINCT(calendar_events.id),
                        calendar_events.last_modified_timestamp
                    FROM calendar_events_calendars_xref
                    LEFT JOIN calendar_events ON calendar_events_calendars_xref.calendar_event_id = calendar_events.id
                    WHERE
                        (calendar_events.published = 1)
                        AND ($sql_calendar_condition)
                    ORDER BY calendar_events.last_modified_timestamp DESC";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');        
                
                $calendar_events = array();

                // loop through calendar events in order to add them to array
                while ($row = mysqli_fetch_assoc($result)) {
                    $calendar_events[] = $row;
                }
                
                // if there is at least one calendar event, then prepare sitemap URL(s)
                if (count($calendar_events) > 0) {
                    
                    $output_path = h(PATH . encode_url_path($calendar_event_view_page_name));
                    
                    // loop through calendar events in order to prepare sitemap URL's
                    foreach ($calendar_events as $calendar_event) {
                        $output_url = URL_SCHEME . HOSTNAME_SETTING . $output_path . '?id=' . $calendar_event['id'];
                        
                        // if this URL has not already been added then add URL to sitemap
                        if (in_array($output_url, $added_urls) == FALSE) {
                            $output_sitemap_urls .=
                                '    <url>' . "\n" .
                                '        <loc>' . $output_url . '</loc>' . "\n" .
                                '        <lastmod>' . gmdate('Y-m-d\TH:i:s\Z', $calendar_event['last_modified_timestamp']) . '</lastmod>' . "\n" .
                                '    </url>' . "\n";
                            
                            // remember that we have added this URL
                            $added_urls[] = $output_url;
                        }
                    }
                }
            }
        }
    }
    
    // get additional sitemap content
    $query = "SELECT additional_sitemap_content FROM config";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $additional_sitemap_content = $row['additional_sitemap_content'];
    
    // if additional sitemap content exists, then add it to the sitemap URL's
    if ($additional_sitemap_content != '') {
        $output_sitemap_urls .= $additional_sitemap_content . "\n";
    }
    
    $sitemap_info = array();
    
    // assume that URL's do not exist until we find out otherwise
    $sitemap_info['urls_exist'] = FALSE;
    
    // if URL's exist, then remember that, because we will return that info
    if ($output_sitemap_urls != '') {
        $sitemap_info['urls_exist'] = TRUE;
    }
    
    // prepare content for sitemap
    $sitemap_info['content'] =
        '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" .
        $output_sitemap_urls . 
        '</urlset>';
    
    return $sitemap_info;
}

// create function that will be used below to get catalog items for each catalog page for sitemap
function get_catalog_items_for_sitemap($product_group_id, $get_catalog_detail_items, $product_groups = array(), $products_product_groups_xrefs = array()) {

    // if the product groups array is empty, then this is the first time this function has run, so get data for arrays
    if (count($product_groups) == 0) {
        // get all product groups so we can determine which product groups appear in catalog
        $query =
            "SELECT
                id,
                parent_id,
                display_type,
                address_name,
                timestamp as last_modified_timestamp
            FROM product_groups
            WHERE enabled = '1'
            ORDER BY timestamp DESC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $product_groups = array();
        
        // add each product group to an array
        while ($row = mysqli_fetch_assoc($result)) {
            $product_groups[] = $row;
        }
        
        // if we should get catalog detail items, then get all relationships between products and product groups
        // so we can determine which products appear in catalog
        if ($get_catalog_detail_items == TRUE) {
            $query =
                "SELECT
                    products_groups_xref.product as product_id,
                    products_groups_xref.product_group as product_group_id,
                    products.address_name as product_address_name,
                    products.timestamp as product_last_modified_timestamp
                FROM products_groups_xref
                LEFT JOIN products ON products_groups_xref.product = products.id
                WHERE products.enabled = '1'
                ORDER BY products.timestamp DESC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $products_product_groups_xrefs = array();
            
            // add each relationship to an array
            while ($row = mysqli_fetch_assoc($result)) {
                $products_product_groups_xrefs[] = $row;
            }
        }
    }
    
    $child_product_groups = array();
    
    // loop through product groups array in order to get all product groups that are in parent product group
    foreach ($product_groups as $product_group) {
        // if the parent product group id for this product group is equal to the parent product group id, then this is a child product group, so add to array
        if ($product_group['parent_id'] == $product_group_id) {
            $child_product_groups[] = $product_group;
        }
    }
    
    $catalog_items = array();
    
    // loop through child product groups in order to get catalog items in each one
    foreach ($child_product_groups as $child_product_group) {
        // if this child product group is a browse product group, then add child product group to array and get items under this product group via recursion
        if ($child_product_group['display_type'] == 'browse') {
            $catalog_items[] = array(
                'type' => 'product_group',
                'display_type' => 'browse',
                'address_name' => $child_product_group['address_name'],
                'last_modified_timestamp' => $child_product_group['last_modified_timestamp']
            );
            
            $catalog_items = array_merge($catalog_items, get_catalog_items_for_sitemap($child_product_group['id'], $get_catalog_detail_items, $product_groups, $products_product_groups_xrefs));
            
        // else this child product group is a select product group that should appear on a catalog detail page,
        // so if we should get catalog detail items, then add child product group to array
        } else if ($get_catalog_detail_items == TRUE) {
            $catalog_items[] = array(
                'type' => 'product_group',
                'display_type' => 'select',
                'address_name' => $child_product_group['address_name'],
                'last_modified_timestamp' => $child_product_group['last_modified_timestamp']
            );
        }
    }
    
    // if we should get catalog detail items, then add products to array
    if ($get_catalog_detail_items == TRUE) {
        // loop through products_product_groups_xrefs array in order to get all products that are in the parent product group
        foreach ($products_product_groups_xrefs as $products_product_groups_xref) {
            // if this product is in the parent product group, then add it to the array
            if ($products_product_groups_xref['product_group_id'] == $product_group_id) {
                $catalog_items[] = array(
                    'type' => 'product',
                    'address_name' => $products_product_groups_xref['product_address_name'],
                    'last_modified_timestamp' => $products_product_groups_xref['product_last_modified_timestamp']
                );
            }
        }
    }
    
    return $catalog_items;
}
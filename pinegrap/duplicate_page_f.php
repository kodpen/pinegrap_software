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

function duplicate_page($request) {

    $user = validate_user();

    // if the user has a user role and has create pages turned off, then output error
    if (($user['role'] == '3') && ($user['create_pages'] == FALSE)) {
        log_activity("access denied because user does not have access to duplicate pages");
        output_error('Access denied.');
    }

    // get original page's info
    $result = mysqli_query(db::$con, "SELECT * FROM page WHERE page_id = '" . e($request['page']['id']) . "'") or output_error('Query failed');
    $row = mysqli_fetch_array($result);

    if (!$row) {
        return error_response('Sorry, the page could not be found.');
    }

    $page_id = $row['page_id'];
    $page_type = $row['page_type'];
    $layout_type = $row['layout_type'];
    $layout_modified = $row['layout_modified'];
    $page_folder = $row['page_folder'];
    $page_name = $row['page_name'];
    $page_style = $row['page_style'];
    $mobile_style_id = $row['mobile_style_id'];
    $page_title = $row['page_title'];
    $page_search = $row['page_search'];
    $page_meta_description = $row['page_meta_description'];
    $page_meta_keywords = $row['page_meta_keywords'];
    $sitemap = $row['sitemap'];
    $comments = $row['comments'];
    $comments_label = $row['comments_label'];
    $comments_message = $row['comments_message'];
    $comments_allow_new_comments = $row['comments_allow_new_comments'];
    $comments_automatic_publish = $row['comments_automatic_publish'];
    $comments_disallow_new_comment_message = $row['comments_disallow_new_comment_message'];
    $comments_allow_user_to_select_name = $row['comments_allow_user_to_select_name'];
    $comments_require_login_to_comment = $row['comments_require_login_to_comment'];
    $comments_allow_file_attachments = $row['comments_allow_file_attachments'];
    $comments_show_submitted_date_and_time = $row['comments_show_submitted_date_and_time'];
    $comments_administrator_email_to_email_address = $row['comments_administrator_email_to_email_address'];
    $comments_administrator_email_subject = $row['comments_administrator_email_subject'];
    $comments_administrator_email_conditional_administrators = $row['comments_administrator_email_conditional_administrators'];
    $comments_submitter_email_page_id = $row['comments_submitter_email_page_id'];
    $comments_submitter_email_subject = $row['comments_submitter_email_subject'];
    $comments_watcher_email_page_id = $row['comments_watcher_email_page_id'];
    $comments_watcher_email_subject = $row['comments_watcher_email_subject'];
    $comments_watchers_managed_by_submitter = $row['comments_watchers_managed_by_submitter'];
    $system_region_header = $row['system_region_header'];
    $system_region_footer = $row['system_region_footer'];

    if (check_edit_access($page_folder) == false) {
        log_activity("access denied because user does not have access to modify folder");
        output_error('Access denied.');
    }

    // if the user does not have access to create this type of page, then set the page type to standard
    if (
        ($user['role'] == 3)
        &&
        (
            ($page_type == 'change password')
            || ($page_type == 'set password')
            || (($page_type == 'email a friend') && ($user['set_page_type_email_a_friend'] == FALSE))
            || ($page_type == 'error')
            || (($page_type == 'folder view') && ($user['set_page_type_folder_view'] == FALSE))
            || ($page_type == 'forgot password')
            || ($page_type == 'login')
            || ($page_type == 'logout')
            || (($page_type == 'photo gallery') && ($user['set_page_type_photo_gallery'] == FALSE))
            || ($page_type == 'search results')
            || ($page_type == 'registration entrance')
            || ($page_type == 'registration confirmation')
            || ($page_type == 'membership entrance')
            || ($page_type == 'membership confirmation')
            || ($page_type == 'my account')
            || ($page_type == 'my account profile')
            || ($page_type == 'email preferences')
            || ($page_type == 'view order')
            || ($page_type == 'update address book')
            || (($page_type == 'custom form') && ($user['set_page_type_custom_form'] == FALSE))
            || (($page_type == 'custom form confirmation') && ($user['set_page_type_custom_form_confirmation'] == FALSE))
            || (($page_type == 'form list view') && ($user['set_page_type_form_list_view'] == FALSE))
            || (($page_type == 'form item view') && ($user['set_page_type_form_item_view'] == FALSE))
            || (($page_type == 'form view directory') && ($user['set_page_type_form_view_directory'] == FALSE))
            || (($page_type == 'calendar view') && (($user['manage_calendars'] == FALSE) || ($user['set_page_type_calendar_view'] == FALSE)))
            || (($page_type == 'calendar event view') && (($user['manage_calendars'] == FALSE) || ($user['set_page_type_calendar_event_view'] == FALSE)))
            || (($page_type == 'catalog') && ($user['set_page_type_catalog'] == FALSE))
            || (($page_type == 'catalog detail') && ($user['set_page_type_catalog_detail'] == FALSE))
            || (($page_type == 'express order') && ($user['set_page_type_express_order'] == FALSE))
            || (($page_type == 'order form') && ($user['set_page_type_order_form'] == FALSE))
            || (($page_type == 'shopping cart') && ($user['set_page_type_shopping_cart'] == FALSE))
            || (($page_type == 'shipping address and arrival') && ($user['set_page_type_shipping_address_and_arrival'] == FALSE))
            || (($page_type == 'shipping method') && ($user['set_page_type_shipping_method'] == FALSE))
            || (($page_type == 'billing information') && ($user['set_page_type_billing_information'] == FALSE))
            || (($page_type == 'order preview') && ($user['set_page_type_order_preview'] == FALSE))
            || (($page_type == 'order receipt') && ($user['set_page_type_order_receipt'] == FALSE))
            || ($page_type == 'affiliate sign up form')
            || ($page_type == 'affiliate sign up confirmation')
            || ($page_type == 'affiliate welcome')
        )
    ) {
        $page_type = 'standard';
    }

    $original_page_name = $page_name;

    if ($request['find_replace_keywords']) {

        require_once(dirname(__FILE__) . '/find_replace.php');

        $page_name = find_replace(array(
            'content' => $page_name,
            'keywords' => $request['find_replace_keywords']));

        if ($page_name == '') {
            $page_name = 'no-name';
        }

        $page_title = find_replace(array(
            'content' => $page_title,
            'keywords' => $request['find_replace_keywords']));

        $page_meta_description = find_replace(array(
            'content' => $page_meta_description,
            'keywords' => $request['find_replace_keywords']));

        $page_meta_keywords = find_replace(array(
            'content' => $page_meta_keywords,
            'keywords' => $request['find_replace_keywords']));
    }

    $page_name = get_unique_name(array(
        'name' => $page_name,
        'type' => 'page'));

    // If a new folder for the new page was passed in the request (e.g. for duplicate folder), then
    // use that folder.
    if ($request['folder']) {
        $folder = $request['folder'];

    // Otherwise, use the same folder as the page we are duplicating.
    } else {
        $folder['id'] = $page_folder;
    }

    // insert row into page table
    $query = "INSERT INTO page (
                page_name,
                page_folder,
                page_style,
                mobile_style_id,
                page_title,
                page_search,
                page_meta_description,
                page_meta_keywords,
                sitemap,
                page_type,
                layout_type,
                layout_modified,
                comments,
                comments_label,
                comments_message,
                comments_allow_new_comments,
                comments_disallow_new_comment_message,
                comments_automatic_publish,
                comments_allow_user_to_select_name,
                comments_require_login_to_comment,
                comments_allow_file_attachments,
                comments_show_submitted_date_and_time,
                comments_administrator_email_to_email_address,
                comments_administrator_email_subject,
                comments_administrator_email_conditional_administrators,
                comments_submitter_email_page_id,
                comments_submitter_email_subject,
                comments_watcher_email_page_id,
                comments_watcher_email_subject,
                comments_watchers_managed_by_submitter,
                system_region_header,
                system_region_footer,
                page_user,
                page_timestamp)
            VALUES (
                '" . e($page_name) . "',
                '" . e($folder['id']) . "',
                '" . escape($page_style) . "',
                '" . escape($mobile_style_id) . "',
                '" . escape($page_title) . "',
                '" . escape($page_search) . "',
                '" . escape($page_meta_description) . "',
                '" . escape($page_meta_keywords) . "',
                '" . escape($sitemap) . "',
                '" . escape($page_type) . "',
                '" . e($layout_type) . "',
                '" . e($layout_modified) . "',
                '" . escape($comments) . "',
                '" . e($comments_label) . "',
                '" . e($comments_message) . "',
                '" . escape($comments_allow_new_comments) . "',
                '" . escape($comments_disallow_new_comment_message) . "',
                '" . escape($comments_automatic_publish) . "',
                '" . escape($comments_allow_user_to_select_name) . "',
                '" . escape($comments_require_login_to_comment) . "',
                '" . escape($comments_allow_file_attachments) . "',
                '" . escape($comments_show_submitted_date_and_time) . "',
                '" . escape($comments_administrator_email_to_email_address) . "',
                '" . escape($comments_administrator_email_subject) . "',
                '" . escape($comments_administrator_email_conditional_administrators) . "',
                '" . escape($comments_submitter_email_page_id) . "',
                '" . escape($comments_submitter_email_subject) . "',
                '" . escape($comments_watcher_email_page_id) . "',
                '" . escape($comments_watcher_email_subject) . "',
                '" . escape($comments_watchers_managed_by_submitter) . "',
                '" . escape($system_region_header) . "',
                '" . escape($system_region_footer) . "',
                '" . $user['id'] . "',
                UNIX_TIMESTAMP())";
    $result=mysqli_query(db::$con, $query) or output_error('Query failed');

    $new_page['id'] = mysqli_insert_id(db::$con);
    $new_page['name'] = $page_name;

    // call the function that updates the tag cloud table
    update_tag_cloud_keywords_for_page($new_page['id'], $page_search, $page_meta_keywords, 0, '');

    // get region info
    $result=mysqli_query(db::$con, "SELECT pregion_content, pregion_order, collection FROM pregion WHERE pregion_page = '" . e($page_id) . "' ORDER BY pregion_order") or die ('Query failed');
    // create duplicate regions
    while($row=mysqli_fetch_array($result)) {

        $region_name = time() .'_'. $row[pregion_order];

        if ($request['find_replace_keywords']) {
            $row['pregion_content'] = find_replace(array(
                'content' => $row['pregion_content'],
                'keywords' => $request['find_replace_keywords']));
        }

        $result_insert=mysqli_query(db::$con, "INSERT INTO pregion (pregion_name, pregion_content, pregion_page, pregion_order, collection, pregion_user, pregion_timestamp) VALUES ('" . escape($region_name) . "', '" . e($row['pregion_content']) . "', '" . e($new_page['id']) . "', '" . escape($row['pregion_order']) . "', '" . escape($row['collection']) . "', '" . escape($user['id']) . "', UNIX_TIMESTAMP())") or output_error('Query failed');
    }

    // if page might have a form, then duplicate form fields and form field options
    if (
        ($page_type == 'custom form')
        || ($page_type == 'shipping address and arrival')
        || ($page_type == 'billing information')
        || ($page_type == 'express order')
    ) {
        $query = "SELECT * FROM form_fields WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $form_fields = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $form_fields[] = $row;
        }
        
        foreach ($form_fields as $form_field) {
            // create form field
            $query = "INSERT INTO form_fields (
                        form_type,
                        page_id,
                        name,
                        label,
                        type,
                        sort_order,
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
                        upload_folder_id,
                        user,
                        timestamp)
                     VALUES (
                        '" . e($form_field['form_type']) . "',
                        '" . e($new_page['id']) . "',
                        '" . escape($form_field['name']) . "',
                        '" . escape($form_field['label']) . "',
                        '" . escape($form_field['type']) . "',
                        '" . $form_field['sort_order'] . "',
                        '" . $form_field['required'] . "',
                        '" . escape($form_field['information']) . "',
                        '" . escape($form_field['default_value']) . "',
                        '" . escape($form_field['use_folder_name_for_default_value']) . "',
                        '" . $form_field['size'] . "',
                        '" . $form_field['maxlength'] . "',
                        '" . $form_field['wysiwyg'] . "',
                        '" . $form_field['rows'] . "',
                        '" . $form_field['cols'] . "',
                        '" . $form_field['multiple'] . "',
                        '" . $form_field['spacing_above'] . "',
                        '" . $form_field['spacing_below'] . "',
                        '" . escape($form_field['contact_field']) . "',
                        '" . $form_field['office_use_only'] . "',
                        '" . $form_field['upload_folder_id'] . "',
                        '" . $user['id'] . "',
                        UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $new_form_field_id = mysqli_insert_id(db::$con);
            
            // get form field options
            $query = "SELECT * FROM form_field_options WHERE form_field_id = '" . $form_field['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $form_field_options = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $form_field_options[] = $row;
            }
            
            foreach ($form_field_options as $form_field_option) {
                // create form field option
                $query = "INSERT INTO form_field_options (
                            page_id,
                            form_field_id,
                            label,
                            value,
                            default_selected,
                            sort_order,
                            upload_folder_id)
                         VALUES (
                            '" . e($new_page['id']) . "',
                            '" . $new_form_field_id . "',
                            '" . escape($form_field_option['label']) . "',
                            '" . escape($form_field_option['value']) . "',
                            '" . $form_field_option['default_selected'] . "',
                            '" . $form_field_option['sort_order'] . "',
                            '" . $form_field_option['upload_folder_id'] . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }

    // if page type is form list view, duplicate filters and browse fields
    if ($page_type == 'form list view') {
        $query = "SELECT * FROM form_list_view_filters WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $filters = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $filters[] = $row;
        }
        
        foreach ($filters as $filter) {
            // create filter
            $query = "INSERT INTO form_list_view_filters (
                        page_id,
                        standard_field,
                        form_field_id,
                        operator,
                        value,
                        dynamic_value,
                        dynamic_value_attribute)
                     VALUES (
                        '" . e($new_page['id']) . "',
                        '" . escape($filter['standard_field']) . "',
                        '" . escape($filter['form_field_id']) . "',
                        '" . escape($filter['operator']) . "',
                        '" . escape($filter['value']) . "',
                        '" . escape($filter['dynamic_value']) . "',
                        '" . escape($filter['dynamic_value_attribute']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        // Get browse fields.
        $query = "SELECT * FROM form_list_view_browse_fields WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $browse_fields = mysqli_fetch_items($result);
        
        // Loop through browse fields in order to add them to database.
        foreach ($browse_fields as $browse_field) {
            $query =
                "INSERT INTO form_list_view_browse_fields (
                    page_id,
                    form_field_id,
                    number_of_columns,
                    sort_order,
                    shortcut,
                    date_format)
                VALUES (
                    '" . e($new_page['id']) . "',
                    '" . $browse_field['form_field_id'] . "',
                    '" . $browse_field['number_of_columns'] . "',
                    '" . $browse_field['sort_order'] . "',
                    '" . $browse_field['shortcut'] . "',
                    '" . $browse_field['date_format'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // if page type is form view directory, duplicate form list views that are selected for the form view directory
    if ($page_type == 'form view directory') {
        $query =
            "SELECT
                form_list_view_page_id as page_id,
                form_list_view_name as name,
                subject_form_field_id
            FROM form_view_directories_form_list_views_xref
            WHERE form_view_directory_page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $form_list_views = array();
        
        // loop through the form list views in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $form_list_views[] = $row;
        }
        
        // loop through the form list views in order to add records to database
        foreach ($form_list_views as $form_list_view) {
            // create form list view connection to form view directory
            $query =
                "INSERT INTO form_view_directories_form_list_views_xref (
                    form_view_directory_page_id,
                    form_list_view_page_id,
                    form_list_view_name,
                    subject_form_field_id)
                VALUES (
                   '" . e($new_page['id']) . "',
                   '" . $form_list_view['page_id'] . "',
                   '" . escape($form_list_view['name']) . "',
                   '" . $form_list_view['subject_form_field_id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // if page type is calendar view, duplicate calendars for view
    if ($page_type == 'calendar view') {
        $query = "SELECT calendar_id FROM calendar_views_calendars_xref WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $calendars = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $calendars[] = $row['calendar_id'];
        }
        
        foreach ($calendars as $calendar_id) {
            // create calendar connection to view
            $query =
                "INSERT INTO calendar_views_calendars_xref (
                   page_id,
                   calendar_id)
                VALUES (
                   '" . e($new_page['id']) . "',
                   '$calendar_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // if page type is calendar event view, duplicate calendars for view
    if ($page_type == 'calendar event view') {
        $query = "SELECT calendar_id FROM calendar_event_views_calendars_xref WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $calendars = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $calendars[] = $row['calendar_id'];
        }
        
        foreach ($calendars as $calendar_id) {
            // create calendar connection to view
            $query =
                "INSERT INTO calendar_event_views_calendars_xref (
                   page_id,
                   calendar_id)
                VALUES (
                   '" . e($new_page['id']) . "',
                   '$calendar_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // if this is a search results page, then update the keywords for this page for the tag cloud
    if ($page_type == 'search results') {
        // get search catalog items and product group id for this page
        $query = "SELECT search_catalog_items, product_group_id FROM search_results_pages WHERE page_id = '" . e($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $new_search_catalog_items = $row['search_catalog_items'];
        $new_product_group_id = $row['product_group_id'];
        
        // call function that will add the keywords and xref records to the database
        update_tag_cloud_keywords_for_search_results_page_type($new_page['id'], $new_search_catalog_items, $new_product_group_id);
    }

    // if page type has a table for properties, get properties so we can add properties for new page
    if (check_for_page_type_properties($page_type) == true) {

        $properties = get_page_type_properties($page_id, $page_type);
        
        // remove id from properties because it is the id of the original page
        unset($properties['id']);
        
        // update the page id for the properties to be the new page id
        $properties['page_id'] = $new_page['id'];
        
        // if this page is a custom form page, then we need to create a unique form name
        if ($page_type == 'custom form') {
            $properties['form_name'] = $properties['form_name'] . '[' . get_duplicate_form_name_number($properties['form_name']) . ']';
        }
        
        create_or_update_page_type_record($page_type, $properties);

        // If this is a form list or item view then also duplicate collection B properties.
        if ($page_type == 'form list view' or $page_type == 'form item view') {

            $properties = get_page_type_properties($page_id, $page_type, 'b');

            // If collection B properties were found, then duplicate them.
            if ($properties) {

                // remove id from properties because it is the id of the original page
                unset($properties['id']);
                
                // update the page id for the properties to be the new page id
                $properties['page_id'] = $new_page['id'];
                
                create_or_update_page_type_record($page_type, $properties);
            }
        }
    }

    // duplicate comments that are connected to this page
    $query = "SELECT * FROM comments WHERE page_id = '" . e($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $comments = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }

    foreach ($comments as $comment) {
        // create comment
        $query =
            "INSERT INTO comments (
               page_id,
               item_id,
               item_type,
               name,
               message,
               file_id,
               published,
               publish_date_and_time,
               publish_cancel,
               featured,
               created_user_id,
               created_timestamp,
               last_modified_user_id,
               last_modified_timestamp)
            VALUES (
               '" . e($new_page['id']) . "',
               '" . escape($comment['item_id']) . "',
               '" . escape($comment['item_type']) . "',
               '" . escape($comment['name']) . "',
               '" . escape($comment['message']) . "',
               '" . escape($comment['file_id']) . "',
               '" . escape($comment['published']) . "',
               '" . e($comment['publish_date_and_time']) . "',
               '" . e($comment['publish_cancel']) . "',
               '" . escape($comment['featured']) . "',
               '" . escape($comment['created_user_id']) . "',
               '" . escape($comment['created_timestamp']) . "',
               '" . escape($comment['last_modified_user_id']) . "',
               '" . escape($comment['last_modified_timestamp']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    // Duplicate preview style records.

    $preview_styles = db_items(
        "SELECT
            theme_id,
            style_id,
            device_type
        FROM preview_styles
        WHERE page_id = '" . e($page_id) . "'");

    foreach ($preview_styles as $preview_style) {
        db(
            "INSERT INTO preview_styles (
                page_id,
                theme_id,
                style_id,
                device_type)
            VALUES (
                '" . e($new_page['id']) . "',
                '" . $preview_style['theme_id'] . "',
                '" . $preview_style['style_id'] . "',
                '" . $preview_style['device_type'] . "')");
    }

    // If the page type supports a layout and this page has a custom layout,
    // and there is a layout file on the file sytem, then duplicate the layout file.
    if (
        check_if_page_type_supports_layout($page_type)
        && file_exists(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php')
    ) {
        file_put_contents(LAYOUT_DIRECTORY_PATH . '/' . $new_page['id'] . '.php', file_get_contents(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php'));
    }

    log_activity('Page was duplicated (' . $original_page_name . ' -> ' . $new_page['name'] . ').');

    return array(
        'status' => 'success',
        'page' => $new_page);
}

function get_duplicate_form_name_number($form_name, $number = 1) {

    // check to see if there is already a form name for the one that we want to use
    $query = "SELECT form_name FROM custom_form_pages where form_name = '" . e($form_name) . "[" . $number . "]'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    // if there is a result, then there is already a page with that form name
    if (mysqli_num_rows($result) > 0) {
        return get_duplicate_form_name_number($form_name, $number + 1);
    // else there is not a page with that form name already, so use that number
    } else {
        return $number;
    }
}
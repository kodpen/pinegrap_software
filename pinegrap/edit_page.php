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

$liveform = new liveform('edit_page');

$user = validate_user();
validate_area_access($user, 'user');

// get page's folder in order to validate folder access
$result = mysqli_query(db::$con, "SELECT page_id, page_folder FROM page WHERE page_id = '" . escape($_REQUEST['id']) . "'") or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

if (!$row['page_id']) {
    output_error('Sorry, the page could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (check_edit_access($row['page_folder']) == false) {
    log_activity("access denied because user does not have access to modify folder", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (!$_POST) {
    $query =
        "SELECT
            page_id,
            page_name,
            page_folder,
            page_home,
            page_search,
            page_search_keywords,
            page_style,
            mobile_style_id,
            page_title,
            page_meta_description,
            page_meta_keywords,
            sitemap,
            page_type,
            layout_type,
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
            comments_watchers_managed_by_submitter
        FROM page
        WHERE page_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $page_id = $row['page_id'];
    $page_name = $row['page_name'];
    $page_folder = $row['page_folder'];
    $page_home = $row['page_home'];
    $page_search = $row['page_search'];
    $page_search_keywords = $row['page_search_keywords'];
    $page_style = $row['page_style'];
    $mobile_style_id = $row['mobile_style_id'];
    $page_title = $row['page_title'];
    $page_meta_description = $row['page_meta_description'];
    $page_meta_keywords = $row['page_meta_keywords'];
    $sitemap = $row['sitemap'];
    $page_type = $row['page_type'];
    $layout_type = $row['layout_type'];
    $comments = $row['comments'];
    $comments_label = $row['comments_label'];
    $comments_message = $row['comments_message'];
    $comments_allow_new_comments = $row['comments_allow_new_comments'];
    $comments_disallow_new_comment_message = $row['comments_disallow_new_comment_message'];
    $comments_automatic_publish = $row['comments_automatic_publish'];
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

    $output_wysiwyg_editor_code = '';
    
    $output_subnav_page_type  = '';
    
    // Get page type
    if ($page_type != '') {
        $output_subnav_page_type = ' | Page Type: ' . h(get_page_type_name($page_type));
    }
    
    $output_subnav_short_link = '';

    // Get most recent short link for this page if one exists.
    $query =
        "SELECT name
        FROM short_links
        WHERE
            (destination_type = 'page')
            AND (page_id = '" . escape($_GET['id']) . "')
        ORDER BY last_modified_timestamp DESC
        LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $short_link = mysqli_fetch_assoc($result);
    
    // if there is a short link, then prepare to output description in sub-navigation area
    if ($short_link['name'] != '') {
        $output_subnav_short_link = ' | Short Link: ' . h($short_link['name']);
    }
    
    $output_subnav_home = '';
    
    // if this is a home page, then prepare to output description in sub-navigation area
    if ($page_home == 'yes') {
        $output_subnav_home = '<img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY .'/images/icon_home_white_small.png" width="16" height="14" alt="" title="Home Page Icon" class="icon_home" />';
    }
    
    $output_subnav_search = '';
    $output_subnav_search_keywords = '';
    
    // if search is enabled, then prepare to output description in sub-navigation area
    if ($page_search == 1) {
        $output_subnav_search = ' | Searchable';
        
        // if there are search keywords, then prepare to output description in sub-navigation area
        if ($page_search_keywords != '') {
            $output_subnav_search_keywords = ' | Keyword: ' . h($page_search_keywords);
        }
    }
    
    // Check to see if the page has page type properties
    $check_for_page_type_properties = check_for_page_type_properties($page_type);
    // If the page has page type properties.
    if ($check_for_page_type_properties == true) {
        // Get the page type properties.
        $page_type_properties = get_page_type_properties($page_id, $page_type);
        
        // if there is a next page,
        // and the page type is not catalog detail,
        // or ordering is enabled,
        // then prepare to output next page
        if (
            (isset($page_type_properties['next_page_id']) == true)
            &&
            (
                ($page_type != 'catalog detail')
                || ($page_type_properties['allow_customer_to_add_product_to_order'] == 1)
            )
        ) {
            // Get the next page name and output the next page.
            $next_page_name = get_page_name($page_type_properties['next_page_id']);
            // Next page is blank then output none.
            if ($next_page_name == '') {
                $output_subnav_next_page = ' | Next Page: None';
            // else output name
            } else {
                $output_subnav_next_page = ' | Next Page: <a href="' . OUTPUT_PATH . $next_page_name . '">' . $next_page_name . '</a>';
            }
        } else if((isset($page_type_properties['add_button_next_page_id']) && ($page_type_properties['add_button_next_page_id'] != 0))) {
            // Get the next page name and output the next page. 
            $next_page_name = get_page_name($page_type_properties['add_button_next_page_id']);
            // Next page is blank then output none.
            if ($next_page_name == '') {
                $output_subnav_next_page = ' | Next Page: None';
            // else output name
            } else {
                $output_subnav_next_page = ' | Next Page: <a href="' . OUTPUT_PATH . $next_page_name . '">' . $next_page_name . '</a>';
            }
        } else {
            // If no next page set the variable to blank.
            $output_subnav_next_page = '';
        }
        
        if((isset($page_type_properties['skip_button_next_page_id']) && ($page_type_properties['skip_button_next_page_id'] != 0))) {
            // Get the next page name and output the next page.
            $skip_page_name = get_page_name($page_type_properties['skip_button_next_page_id']);
            $output_subnav_skip_page = ' | Skip Page: <a href="' . OUTPUT_PATH . $skip_page_name . '">' . $skip_page_name . '</a>';
        } else {
            // If no next page set the variable to blank.
            $output_subnav_skip_page = '';
        }
    }
    
    // if user is above a user role, then prepare to output style and mobile style pick list, because user has access to select style
    if ($user['role'] < 3) {
        $output_style = '<select name="style">' . select_style($page_style) . '</select>';
        $output_mobile_style = '<select name="mobile_style_id">' . get_mobile_style_options($mobile_style_id) . '</select>';
        
    // else user has a user role, so prepare to just output style and mobile style name
    } else {
        // if there is a style set for this page, then prepare to just output style name
        if ($page_style != 0) {
            // get style name
            $query = "SELECT style_name FROM style WHERE style_id='" . escape($page_style) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $style_name = $row['style_name'];
            
            $output_style = h($style_name);
            
        // else there is not a style set for this page, so output default
        } else {
            $output_style = 'Default (inherit)';
        }

        // if there is a mobile style set for this page, then prepare to just output mobile style name
        if ($mobile_style_id != 0) {
            // get mobile style name
            $query = "SELECT style_name FROM style WHERE style_id='" . escape($mobile_style_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $mobile_style_name = $row['style_name'];
            
            $output_mobile_style = h($mobile_style_name);
            
        // else there is not a mobile style set for this page, so output default
        } else {
            $output_mobile_style = 'Default (inherit)';
        }
    }
    
    // if user is above a user role or page type is accessible by this user, then prepare to output page type area
    if (
        ($user['role'] < 3)
        || ($page_type == 'standard')
        || (($page_type == 'email a friend') && ($user['set_page_type_email_a_friend'] == TRUE))
        || (($page_type == 'folder view') && ($user['set_page_type_folder_view'] == TRUE))
        || (($page_type == 'photo gallery') && ($user['set_page_type_photo_gallery'] == TRUE))
        || (($page_type == 'catalog') && ($user['set_page_type_catalog'] == TRUE))
        || (($page_type == 'catalog detail') && ($user['set_page_type_catalog_detail'] == TRUE))
        || (($page_type == 'express order') && ($user['set_page_type_express_order'] == TRUE))
        || (($page_type == 'order form') && ($user['set_page_type_order_form'] == TRUE))
        || (($page_type == 'shopping cart') && ($user['set_page_type_shopping_cart'] == TRUE))
        || (($page_type == 'shipping address and arrival') && ($user['set_page_type_shipping_address_and_arrival'] == TRUE))
        || (($page_type == 'shipping method') && ($user['set_page_type_shipping_method'] == TRUE))
        || (($page_type == 'billing information') && ($user['set_page_type_billing_information'] == TRUE))
        || (($page_type == 'order preview') && ($user['set_page_type_order_preview'] == TRUE))
        || (($page_type == 'order receipt') && ($user['set_page_type_order_receipt'] == TRUE))
        || (($page_type == 'custom form') && ($user['set_page_type_custom_form'] == TRUE))
        || (($page_type == 'custom form confirmation') && ($user['set_page_type_custom_form_confirmation'] == TRUE))
        || (($page_type == 'form list view') && ($user['set_page_type_form_list_view'] == TRUE))
        || (($page_type == 'form item view') && ($user['set_page_type_form_item_view'] == TRUE))
        || (($page_type == 'form view directory') && ($user['set_page_type_form_view_directory'] == TRUE))
        || (($page_type == 'calendar view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_view'] == TRUE))
        || (($page_type == 'calendar event view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_event_view'] == TRUE))
    ) {
        // hide all page type properties until we determine which need to be displayed

        $layout_type_row_style = 'display: none';

        $email_a_friend_submit_button_label_row_style = 'display: none';
        $email_a_friend_next_page_id_row_style = 'display: none';
        $folder_view_pages_row_style = 'display: none';
        $folder_view_files_row_style = 'display: none';
        $photo_gallery_number_of_columns_row_style = 'display: none';
        $photo_gallery_thumbnail_max_size_row_style = 'display: none';
        $search_results_search_folder_id_row_style = 'display: none';
        $search_results_search_catalog_items_row_style = 'display: none';
        $search_results_product_group_id_row_style = 'display: none';
        $search_results_catalog_detail_page_id_row_style = 'display: none';
        $update_address_book_address_type_row_style = 'display: none';
        $update_address_book_address_type_page_id_row_style = 'display: none';
        $custom_form_form_name_row_style = 'display: none';
        $custom_form_enabled_row_style = 'display: none';
        $custom_form_quiz_row_style = 'display: none';
        $custom_form_quiz_pass_percentage_row_style = 'display: none';
        $custom_form_label_column_width_row_style = 'display: none';
        $custom_form_watcher_page_id_row_style = 'display: none';
        $custom_form_save_row_style = 'display: none';
        $custom_form_submit_button_label_row_style = 'display: none';
        $custom_form_auto_registration_row_style = 'display: none';
        $custom_form_hook_code_row_style = 'display: none';
        $custom_form_submitter_email_row_style = 'display: none';
        $custom_form_submitter_email_from_email_address_row_style = 'display: none';
        $custom_form_submitter_email_subject_row_style = 'display: none';
        $custom_form_submitter_email_format_row_style = 'display: none';
        $custom_form_submitter_email_body_row_style = 'display: none';
        $custom_form_submitter_email_page_id_row_style = 'display: none';
        $custom_form_administrator_email_row_style = 'display: none';
        $custom_form_administrator_email_to_email_address_row_style = 'display: none';
        $custom_form_administrator_email_bcc_email_address_row_style = 'display: none';
        $custom_form_administrator_email_subject_row_style = 'display: none';
        $custom_form_administrator_email_format_row_style = 'display: none';
        $custom_form_administrator_email_body_row_style = 'display: none';
        $custom_form_administrator_email_page_id_row_style = 'display: none';
        $custom_form_contact_group_id_row_style = 'display: none';
        $custom_form_membership_row_style = 'display: none';
        $custom_form_membership_days_row_style = 'display: none';
        $custom_form_membership_start_page_id_row_style = 'display: none';
        $custom_form_private_row_style = 'display: none';
        $custom_form_private_folder_id_row_style = 'display: none';
        $custom_form_private_days_row_style = 'display: none';
        $custom_form_private_start_page_id_row_style = 'display: none';
        $custom_form_offer_row_style = 'display: none';
        $custom_form_offer_id_row_style = 'display: none';
        $custom_form_offer_days_row_style = 'display: none';
        $custom_form_offer_eligibility_row_style = 'display: none';
        $custom_form_confirmation_type_row_style = 'display: none';
        $custom_form_confirmation_message_row_style = 'display: none';
        $custom_form_confirmation_page_id_row_style = 'display: none';
        $custom_form_confirmation_alternative_page_row_style = 'display: none';
        $custom_form_confirmation_alternative_page_contact_group_id_row_style = 'display: none';
        $custom_form_confirmation_alternative_page_id_row_style = 'display: none';
        $custom_form_return_type_row_style = 'display: none';
        $custom_form_return_message_row_style = 'display: none';
        $custom_form_return_page_id_row_style = 'display: none';
        $custom_form_return_alternative_page_row_style = 'display: none';
        $custom_form_return_alternative_page_contact_group_id_row_style = 'display: none';
        $custom_form_return_alternative_page_id_row_style = 'display: none';
        $custom_form_pretty_urls_row_style = 'display: none';
        $custom_form_confirmation_continue_button_label_row_style = 'display: none';
        $custom_form_confirmation_next_page_id_row_style = 'display: none';
        $form_list_view_custom_form_page_id_row_style = 'display: none';
        $form_list_view_form_item_view_page_id_row_style = 'display: none';
        $form_list_view_viewer_filter_row_style = 'display: none';
        $form_list_view_viewer_filter_submitter_row_style = 'display: none';
        $form_list_view_viewer_filter_watcher_row_style = 'display: none';
        $form_list_view_viewer_filter_editor_row_style = 'display: none';
        $form_item_view_custom_form_page_id_row_style = 'display: none';
        $form_item_view_submitter_security_row_style = 'display: none';
        $form_item_view_submitted_form_editable_by_registered_user_row_style = 'display: none';
        $form_item_view_submitted_form_editable_by_submitter_row_style = 'display: none';
        $form_item_view_hook_code_row_style = 'display: none';
        $form_view_directory_form_list_views_row_style = 'display: none';
        $form_view_directory_summary_row_style = 'display: none';
        $form_view_directory_summary_days_row_style = 'display: none';
        $form_view_directory_summary_maximum_number_of_results_row_style = 'display: none';
        $form_view_directory_form_list_view_heading_row_style = 'display: none';
        $form_view_directory_subject_heading_row_style = 'display: none';
        $form_view_directory_number_of_submitted_forms_heading_row_style = 'display: none';
        $calendar_view_calendars_row_style = 'display: none';
        $calendar_view_default_view_row_style = 'display: none';
        $calendar_view_number_of_upcoming_events_row_style = 'display: none';
        $calendar_view_calendar_event_view_page_id_row_style = 'display: none';
        $calendar_event_view_calendars_row_style = 'display: none';
        $calendar_event_view_notes_row_style = 'display: none';
        $calendar_event_view_back_button_label_row_style = 'display: none';
        $catalog_product_group_id_row_style = 'display: none';
        $catalog_menu_row_style = 'display: none';
        $catalog_search_row_style = 'display: none';
        $catalog_number_of_featured_items_row_style = 'display: none';
        $catalog_number_of_new_items_row_style = 'display: none';
        $catalog_number_of_columns_row_style = 'display: none';
        $catalog_image_width_row_style = 'display: none';
        $catalog_image_height_row_style = 'display: none';
        $catalog_back_button_label_row_style = 'display: none';
        $catalog_catalog_detail_page_id_row_style = 'display: none';
        $catalog_detail_allow_customer_to_add_product_to_order_row_style = 'display: none';
        $catalog_detail_add_button_label_row_style = 'display: none';
        $catalog_detail_next_page_id_row_style = 'display: none';
        $catalog_detail_back_button_label_row_style = 'display: none';
        $express_order_shopping_cart_label_row_style = 'display: none';
        $express_order_quick_add_label_row_style = 'display: none';
        $express_order_quick_add_product_group_id_row_style = 'display: none';
        $express_order_product_description_type_row_style = 'display: none';
        $express_order_shipping_form_row_style = 'display: none';
        $express_order_special_offer_code_label_row_style = 'display: none';
        $express_order_special_offer_code_message_row_style = 'display: none';
        $express_order_custom_field_1_label_row_style = 'display: none';
        $express_order_custom_field_2_label_row_style = 'display: none';
        $express_order_po_number_row_style = 'display: none';
        $express_order_card_verification_number_page_id_row_style = 'display: none';
        $express_order_offline_payment_always_allowed_row_style = 'display: none';
        $express_order_offline_payment_label_row_style = 'display: none';
        $express_order_terms_page_id_row_style = 'display: none';
        $express_order_update_button_label_row_style = 'display: none';
        $express_order_purchase_now_button_label_row_style = 'display: none';
        $express_order_auto_registration_row_style = 'display: none';
        $express_order_form_row_style = 'display: none';
        $express_order_form_name_row_style = 'display: none';
        $express_order_form_label_column_width_row_style = 'display: none';
        $express_order_pre_save_hook_code_row_style = 'display: none';
        $express_order_post_save_hook_code_row_style = 'display: none';
        $express_order_order_receipt_email_row_style = 'display: none';
        $express_order_order_receipt_email_subject_row_style = 'display: none';
        $express_order_order_receipt_email_format_row_style = 'display: none';
        $express_order_order_receipt_email_header_row_style = 'display: none';
        $express_order_order_receipt_email_footer_row_style = 'display: none';
        $express_order_order_receipt_email_page_id_row_style = 'display: none';
        $express_order_next_page_id_row_style = 'display: none';
        $order_form_product_group_id_row_style = 'display: none';
        $order_form_product_layout_row_1_style = 'display: none';
        $order_form_product_layout_row_2_style = 'display: none';
        $order_form_add_button_label_row_style = 'display: none';
        $order_form_add_button_next_page_id_row_style = 'display: none';
        $order_form_skip_button_label_row_style = 'display: none';
        $order_form_skip_button_next_page_id_row_style = 'display: none';
        $shopping_cart_shopping_cart_label_row_style = 'display: none';
        $shopping_cart_quick_add_label_row_style = 'display: none';
        $shopping_cart_quick_add_product_group_id_row_style = 'display: none';
        $shopping_cart_product_description_type_row_style = 'display: none';
        $shopping_cart_special_offer_code_label_row_style = 'display: none';
        $shopping_cart_special_offer_code_message_row_style = 'display: none';
        $shopping_cart_update_button_label_row_style = 'display: none';
        $shopping_cart_checkout_button_label_row_style = 'display: none';
        $shopping_cart_hook_code_row_style = 'display: none';
        $shopping_cart_next_page_id_with_shipping_row_style = 'display: none';
        $shopping_cart_next_page_id_without_shipping_row_style = 'display: none';
        $shipping_address_and_arrival_address_type_row_style = 'display: none';
        $shipping_address_and_arrival_address_type_page_id_row_style = 'display: none';
        $shipping_address_and_arrival_form_row_style = 'display: none';
        $shipping_address_and_arrival_form_name_row_style = 'display: none';
        $shipping_address_and_arrival_form_label_column_width_row_style = 'display: none';
        $shipping_address_and_arrival_submit_button_label_row_style = 'display: none';
        $shipping_address_and_arrival_next_page_id_row_style = 'display: none';
        $shipping_method_product_description_type_row_style = 'display: none';
        $shipping_method_submit_button_label_row_style = 'display: none';
        $shipping_method_next_page_id_row_style = 'display: none';
        $billing_information_custom_field_1_label_row_style = 'display: none';
        $billing_information_custom_field_2_label_row_style = 'display: none';
        $billing_information_po_number_row_style = 'display: none';
        $billing_information_form_row_style = 'display: none';
        $billing_information_form_name_row_style = 'display: none';
        $billing_information_form_label_column_width_row_style = 'display: none';
        $billing_information_submit_button_label_row_style = 'display: none';
        $billing_information_next_page_id_row_style = 'display: none';
        $order_preview_product_description_type_row_style = 'display: none';
        $order_preview_card_verification_number_page_id_row_style = 'display: none';
        $order_preview_offline_payment_always_allowed_row_style = 'display: none';
        $order_preview_offline_payment_label_row_style = 'display: none';
        $order_preview_terms_page_id_row_style = 'display: none';
        $order_preview_submit_button_label_row_style = 'display: none';
        $order_preview_auto_registration_row_style = 'display: none';
        $order_preview_pre_save_hook_code_row_style = 'display: none';
        $order_preview_post_save_hook_code_row_style = 'display: none';
        $order_preview_order_receipt_email_row_style = 'display: none';
        $order_preview_order_receipt_email_subject_row_style = 'display: none';
        $order_preview_order_receipt_email_format_row_style = 'display: none';
        $order_preview_order_receipt_email_header_row_style = 'display: none';
        $order_preview_order_receipt_email_footer_row_style = 'display: none';
        $order_preview_order_receipt_email_page_id_row_style = 'display: none';
        $order_preview_next_page_id_row_style = 'display: none';
        $order_receipt_product_description_type_row_style = 'display: none';
        $affiliate_sign_up_form_terms_page_id_row_style = 'display: none';
        $affiliate_sign_up_form_submit_button_label_row_style = 'display: none';
        $affiliate_sign_up_form_next_page_id_row_style = 'display: none';
        
        $output_edit_custom_form = '';
        $output_layout_buttons = '';
        $output_edit_form_list_view = '';
        $output_edit_form_item_view = '';
        $output_edit_custom_shipping_form = '';
        $output_edit_custom_billing_form = '';

        if (check_if_page_type_supports_layout($page_type)) {
            // If this page has a custom layout, and user is an admin or designer,
            // then output layout buttons in the button bar.
            if (
                ($layout_type == 'custom')
                && (USER_ROLE < 2)
            ) {
                if (check_if_page_type_requires_from_control_panel($page_type)) {
                    $query_string_from = '?from=control_panel';
                } else {
                    $query_string_from = '';
                }

                $output_layout_buttons =
                    '<a href="page_designer.php?url=' . h(urlencode(PATH . encode_url_path($page_name) . $query_string_from)) . '&amp;type=layout&amp;id=' . $page_id . '" target="_top">Edit Layout</a>
                    <a href="generate_layout.php?page_id=' . $page_id . '">Generate Layout</a>' . "\n";
            }

            // Show the layout type row.
            $layout_type_row_style = '';
        }

        $activate_editors = false;
        
        switch($page_type) {
            case 'email a friend':
                $email_a_friend_properties = get_page_type_properties($page_id, $page_type);
                
                $email_a_friend_submit_button_label_row_style = '';
                $email_a_friend_next_page_id_row_style = '';
                
                break;

            case 'folder view':
                $folder_view_properties = get_page_type_properties($page_id, $page_type);
                
                $folder_view_pages_row_style = '';
                $folder_view_files_row_style = '';
                
                break;
                
            case 'photo gallery':
                $photo_gallery_properties = get_page_type_properties($page_id, $page_type);
                
                $photo_gallery_number_of_columns_row_style = '';
                $photo_gallery_thumbnail_max_size_row_style = '';
                
                break;
                
            case 'search results':
                $search_results_properties = get_page_type_properties($page_id, $page_type);
                $search_results_search_folder_id_row_style = '';
                $search_results_search_catalog_items_row_style = '';
                
                // if the product search is enabled, then display fields
                if ($search_results_properties['search_catalog_items'] == '1') {
                    $search_results_product_group_id_row_style = '';
                    $search_results_catalog_detail_page_id_row_style = '';
                }
                
                break;
                
            case 'update address book':
                $update_address_book_properties = get_page_type_properties($page_id, $page_type);
                
                $update_address_book_address_type_row_style = '';
                
                // if address type is enabled, then show address type page pick list
                if ($update_address_book_properties['address_type'] == '1') {
                    $update_address_book_address_type_page_id_row_style = '';
                }
                
                break;
            
            case 'custom form':
                $custom_form_properties = get_page_type_properties($page_id, $page_type);
                
                $custom_form_form_name_row_style = '';
                $custom_form_enabled_row_style = '';
                $custom_form_quiz_row_style = '';
                
                // if quiz is enabled, then prepare to show quiz pass percentage
                if ($custom_form_properties['quiz'] == 1) {
                    $custom_form_quiz_pass_percentage_row_style = '';
                }
                
                $custom_form_label_column_width_row_style = '';
                $custom_form_watcher_page_id_row_style = '';
                $custom_form_save_row_style = '';
                $custom_form_submit_button_label_row_style = '';
                $custom_form_auto_registration_row_style = '';
                $custom_form_hook_code_row_style = '';
                $custom_form_submitter_email_row_style = '';

                // if submitter e-mail is enabled, then show rows for it
                if ($custom_form_properties['submitter_email'] == 1) {
                    $custom_form_submitter_email_from_email_address_row_style = '';
                    $custom_form_submitter_email_subject_row_style = '';
                    $custom_form_submitter_email_format_row_style = '';

                    // if format is "plain text", then show body row
                    if ($custom_form_properties['submitter_email_format'] == 'plain_text') {
                        $custom_form_submitter_email_body_row_style = '';

                    // else format is "html", so show page row
                    } else {
                        $custom_form_submitter_email_page_id_row_style = '';
                    }
                }

                $custom_form_administrator_email_row_style = '';
                
                // if administrator e-mail is enabled, then show rows for it
                if ($custom_form_properties['administrator_email'] == 1) {
                    $custom_form_administrator_email_to_email_address_row_style = '';
                    $custom_form_administrator_email_bcc_email_address_row_style = '';
                    $custom_form_administrator_email_subject_row_style = '';
                    $custom_form_administrator_email_format_row_style = '';

                    // if format is "plain text", then show body row
                    if ($custom_form_properties['administrator_email_format'] == 'plain_text') {
                        $custom_form_administrator_email_body_row_style = '';

                    // else format is "html", so show page row
                    } else {
                        $custom_form_administrator_email_page_id_row_style = '';
                    }
                }
                
                $custom_form_contact_group_id_row_style = '';
                $custom_form_membership_row_style = '';
                
                // if membership is enabled, then prepare to show membership fields
                if ($custom_form_properties['membership'] == 1) {
                    $custom_form_membership_days_row_style = '';
                    $custom_form_membership_start_page_id_row_style = '';
                }

                $custom_form_private_row_style = '';
                
                // If granting private access is enabled, then show those fields.
                if ($custom_form_properties['private'] == 1) {
                    $custom_form_private_folder_id_row_style = '';
                    $custom_form_private_days_row_style = '';
                    $custom_form_private_start_page_id_row_style = '';
                }

                $custom_form_offer_row_style = '';
                
                // If granting offer is enabled, then show those fields.
                if ($custom_form_properties['offer'] == 1) {
                    $custom_form_offer_id_row_style = '';
                    $custom_form_offer_days_row_style = '';
                    $custom_form_offer_eligibility_row_style = '';
                }
                
                $custom_form_confirmation_type_row_style = '';
                
                // If confirmation type is message, then show rows for it.
                if ($custom_form_properties['confirmation_type'] == 'message') {
                    $custom_form_confirmation_message_row_style = '';

                    // Enable rich-text editor.
                    $activate_editors = true;

                // Otherwise the confirmation type is page, so show rows for it.
                } else {
                    $custom_form_confirmation_page_id_row_style = '';
                    $custom_form_confirmation_alternative_page_row_style = '';

                    // If confirmation alternative page is enabled, then show rows for it.
                    if ($custom_form_properties['confirmation_alternative_page'] == 1) {
                        $custom_form_confirmation_alternative_page_contact_group_id_row_style = '';
                        $custom_form_confirmation_alternative_page_id_row_style = '';
                    }
                }

                $custom_form_return_type_row_style = '';
                
                // If return type is message, then show rows for it.
                if ($custom_form_properties['return_type'] == 'message') {
                    $custom_form_return_message_row_style = '';

                    // Enable rich-text editor.
                    $activate_editors = true;

                // Otherwise if the return type is page, then show rows for it.
                } else if ($custom_form_properties['return_type'] == 'page') {
                    $custom_form_return_page_id_row_style = '';
                    $custom_form_return_alternative_page_row_style = '';

                    // If return alternative page is enabled, then show rows for it.
                    if ($custom_form_properties['return_alternative_page'] == 1) {
                        $custom_form_return_alternative_page_contact_group_id_row_style = '';
                        $custom_form_return_alternative_page_id_row_style = '';
                    }
                }

                $custom_form_pretty_urls_row_style = '';
                
                // output edit custom form button
                $output_edit_custom_form = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Custom Form</a>' . "\n";
                
                break;

            case 'custom form confirmation':
                $custom_form_confirmation_properties = get_page_type_properties($page_id, $page_type);
                
                $custom_form_confirmation_continue_button_label_row_style = '';
                $custom_form_confirmation_next_page_id_row_style = '';
                
                break;
                
            case 'form list view':
                $form_list_view_properties = get_page_type_properties($page_id, $page_type);
                
                $form_list_view_custom_form_page_id_row_style = '';
                $form_list_view_form_item_view_page_id_row_style = '';
                $form_list_view_viewer_filter_row_style = '';

                // If viewer filter is enabled then show related rows.
                if ($form_list_view_properties['viewer_filter'] == 1) {
                    $form_list_view_viewer_filter_submitter_row_style = '';
                    $form_list_view_viewer_filter_watcher_row_style = '';
                    $form_list_view_viewer_filter_editor_row_style = '';
                }
                
                // Output edit form list view
                $output_edit_form_list_view = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_form_list_view.php?page_id=' . $page_id . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Form List View</a>' . "\n";
                
                break;
                
            case 'form item view':
                $form_item_view_properties = get_page_type_properties($page_id, $page_type);
                
                $form_item_view_custom_form_page_id_row_style = '';
                $form_item_view_submitter_security_row_style = '';
                $form_item_view_submitted_form_editable_by_registered_user_row_style = '';
                $form_item_view_submitted_form_editable_by_submitter_row_style = '';
                $form_item_view_hook_code_row_style = '';
                
                // Output edit form list view
                $output_edit_form_item_view = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_form_item_view.php?page_id=' . $page_id . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Form Item View</a>' . "\n";
                
                break;
                
            case 'form view directory':
                $form_view_directory_properties = get_page_type_properties($page_id, $page_type);
                
                $form_view_directory_form_list_views_row_style = '';
                $form_view_directory_summary_row_style = '';
                
                // if summary is enabled, then prepare to show summary fields
                if ($form_view_directory_properties['summary'] == 1) {
                    $form_view_directory_summary_days_row_style = '';
                    $form_view_directory_summary_maximum_number_of_results_row_style = '';
                }
                
                $form_view_directory_form_list_view_heading_row_style = '';
                $form_view_directory_subject_heading_row_style = '';
                $form_view_directory_number_of_submitted_forms_heading_row_style = '';
                
                break;
                
            case 'calendar view':
                $calendar_view_properties = get_page_type_properties($page_id, $page_type);
                
                $calendar_view_calendars_row_style = '';
                $calendar_view_default_view_row_style = '';
                $calendar_view_calendar_event_view_page_id_row_style = '';
                
                // if the default view is set to upcoming, then display number_of_upcoming_events field
                if ($calendar_view_properties['default_view'] == 'upcoming') {
                    $calendar_view_number_of_upcoming_events_row_style = '';
                }
                
                break;
                
            case 'calendar event view':
                $calendar_event_view_properties = get_page_type_properties($page_id, $page_type);
                
                $calendar_event_view_calendars_row_style = '';
                $calendar_event_view_notes_row_style = '';
                $calendar_event_view_back_button_label_row_style = '';
                
                break;
                
            case 'catalog':
                $catalog_properties = get_page_type_properties($page_id, $page_type);
                
                $catalog_product_group_id_row_style = '';
                $catalog_menu_row_style = '';
                $catalog_search_row_style = '';
                $catalog_number_of_featured_items_row_style = '';
                $catalog_number_of_new_items_row_style = '';
                $catalog_number_of_columns_row_style = '';
                $catalog_image_width_row_style = '';
                $catalog_image_height_row_style = '';
                $catalog_back_button_label_row_style = '';
                $catalog_catalog_detail_page_id_row_style = '';
                
                break;
                
            case 'catalog detail':
                $catalog_detail_properties = get_page_type_properties($page_id, $page_type);

                $catalog_detail_allow_customer_to_add_product_to_order_row_style = '';
                
                // if allow customer to add product to order is enabled, then prepare to show related fields
                if ($catalog_detail_properties['allow_customer_to_add_product_to_order'] == 1) {
                    $catalog_detail_add_button_label_row_style = '';
                    $catalog_detail_next_page_id_row_style = '';
                }

                $catalog_detail_back_button_label_row_style = '';
                
                break;

            case 'express order':
                $express_order_properties = get_page_type_properties($page_id, $page_type);
                
                $express_order_shopping_cart_label_row_style = '';
                $express_order_quick_add_label_row_style = '';
                $express_order_quick_add_product_group_id_row_style = '';
                $express_order_product_description_type_row_style = '';

                $express_order_shipping_form_row_style = '';
                
                // If the shipping form is enabled, then output edit custom shipping form button
                if ($express_order_properties['shipping_form']) {
                    $output_edit_custom_shipping_form =
                        '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&amp;form_type=shipping&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Custom Shipping Form</a>' . "\n";
                }

                $express_order_special_offer_code_label_row_style = '';
                $express_order_special_offer_code_message_row_style = '';
                $express_order_custom_field_1_label_row_style = '';
                $express_order_custom_field_2_label_row_style = '';
                $express_order_po_number_row_style = '';

                $express_order_form_row_style = '';
                
                // If the form is enabled, then output edit custom billing form button and display fields.
                if ($express_order_properties['form'] == '1') {
                    $output_edit_custom_billing_form =
                        '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&amp;form_type=billing&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Custom Billing Form</a>' . "\n";
                    $express_order_form_name_row_style = '';
                    $express_order_form_label_column_width_row_style = '';
                }

                $express_order_card_verification_number_page_id_row_style = '';
                $express_order_offline_payment_always_allowed_row_style = '';
                $express_order_offline_payment_label_row_style = '';
                $express_order_terms_page_id_row_style = '';
                $express_order_update_button_label_row_style = '';
                $express_order_purchase_now_button_label_row_style = '';
                $express_order_auto_registration_row_style = '';
                $express_order_pre_save_hook_code_row_style = '';
                $express_order_post_save_hook_code_row_style = '';
                $express_order_order_receipt_email_row_style = '';

                // if order receipt e-mail is enabled, then show subject and format rows
                if ($express_order_properties['order_receipt_email'] == 1) {
                    $express_order_order_receipt_email_subject_row_style = '';
                    $express_order_order_receipt_email_format_row_style = '';

                    // if format is "plain text", then show header and footer rows
                    if ($express_order_properties['order_receipt_email_format'] == 'plain_text') {
                        $express_order_order_receipt_email_header_row_style = '';
                        $express_order_order_receipt_email_footer_row_style = '';

                    // else format is "html", so show page row
                    } else {
                        $express_order_order_receipt_email_page_id_row_style = '';
                    }
                }

                $express_order_next_page_id_row_style = '';
                
                break;

            case 'order form':
                $order_form_properties = get_page_type_properties($page_id, $page_type);
                
                $order_form_product_group_id_row_style = '';
                $order_form_product_layout_row_1_style = '';
                $order_form_product_layout_row_2_style = '';
                $order_form_add_button_label_row_style = '';
                $order_form_add_button_next_page_id_row_style = '';
                $order_form_skip_button_label_row_style = '';
                $order_form_skip_button_next_page_id_row_style = '';
                
                break;

            case 'shopping cart':
                $shopping_cart_properties = get_page_type_properties($page_id, $page_type);
                
                $shopping_cart_shopping_cart_label_row_style = '';
                $shopping_cart_quick_add_label_row_style = '';
                $shopping_cart_quick_add_product_group_id_row_style = '';
                $shopping_cart_product_description_type_row_style = '';
                $shopping_cart_special_offer_code_label_row_style = '';
                $shopping_cart_special_offer_code_message_row_style = '';
                $shopping_cart_update_button_label_row_style = '';
                $shopping_cart_checkout_button_label_row_style = '';
                $shopping_cart_hook_code_row_style = '';
                $shopping_cart_next_page_id_with_shipping_row_style = '';
                $shopping_cart_next_page_id_without_shipping_row_style = '';
                
                break;

            case 'shipping address and arrival':
                $shipping_address_and_arrival_properties = get_page_type_properties($page_id, $page_type);
                
                $shipping_address_and_arrival_address_type_row_style = '';
                
                // if address type is enabled, then show address type page pick list
                if ($shipping_address_and_arrival_properties['address_type'] == '1') {
                    $shipping_address_and_arrival_address_type_page_id_row_style = '';
                }
                
                $shipping_address_and_arrival_form_row_style = '';
                
                // if the form is enabled, then output edit custom shipping form button and display fields
                if ($shipping_address_and_arrival_properties['form'] == '1') {
                    $output_edit_custom_shipping_form = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Custom Shipping Form</a>' . "\n";
                    $shipping_address_and_arrival_form_name_row_style = '';
                    $shipping_address_and_arrival_form_label_column_width_row_style = '';
                }
                
                $shipping_address_and_arrival_submit_button_label_row_style = '';
                $shipping_address_and_arrival_next_page_id_row_style = '';
                
                break;

            case 'shipping method':
                $shipping_method_properties = get_page_type_properties($page_id, $page_type);
                
                $shipping_method_product_description_type_row_style = '';
                $shipping_method_submit_button_label_row_style = '';
                $shipping_method_next_page_id_row_style = '';
                
                break;
                
            case 'billing information':
                $billing_information_properties = get_page_type_properties($page_id, $page_type);
                
                $billing_information_custom_field_1_label_row_style = '';
                $billing_information_custom_field_2_label_row_style = '';
                $billing_information_po_number_row_style = '';

                $billing_information_form_row_style = '';
                
                // If the form is enabled, then output edit custom billing form button and display fields.
                if ($billing_information_properties['form'] == '1') {
                    $output_edit_custom_billing_form = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '">Edit Custom Billing Form</a>' . "\n";
                    $billing_information_form_name_row_style = '';
                    $billing_information_form_label_column_width_row_style = '';
                }

                $billing_information_submit_button_label_row_style = '';
                $billing_information_next_page_id_row_style = '';
                
                break;

            case 'order preview':
                $order_preview_properties = get_page_type_properties($page_id, $page_type);
                
                $order_preview_product_description_type_row_style = '';
                $order_preview_card_verification_number_page_id_row_style = '';
                $order_preview_offline_payment_always_allowed_row_style = '';
                $order_preview_offline_payment_label_row_style = '';
                $order_preview_terms_page_id_row_style = '';
                $order_preview_submit_button_label_row_style = '';
                $order_preview_auto_registration_row_style = '';
                $order_preview_pre_save_hook_code_row_style = '';
                $order_preview_post_save_hook_code_row_style = '';
                $order_preview_order_receipt_email_row_style = '';

                // if order receipt e-mail is enabled, then show subject and format rows
                if ($order_preview_properties['order_receipt_email'] == 1) {
                    $order_preview_order_receipt_email_subject_row_style = '';
                    $order_preview_order_receipt_email_format_row_style = '';

                    // if format is "plain text", then show header and footer rows
                    if ($order_preview_properties['order_receipt_email_format'] == 'plain_text') {
                        $order_preview_order_receipt_email_header_row_style = '';
                        $order_preview_order_receipt_email_footer_row_style = '';

                    // else format is "html", so show page row
                    } else {
                        $order_preview_order_receipt_email_page_id_row_style = '';
                    }
                }

                $order_preview_next_page_id_row_style = '';
                
                break;

            case 'order receipt':
                $order_receipt_properties = get_page_type_properties($page_id, $page_type);
                
                $order_receipt_product_description_type_row_style = '';
                
                break;
                
            case 'affiliate sign up form':
                $affiliate_sign_up_form_properties = get_page_type_properties($page_id, $page_type);
                
                $affiliate_sign_up_form_terms_page_id_row_style = '';
                $affiliate_sign_up_form_submit_button_label_row_style = '';
                $affiliate_sign_up_form_next_page_id_row_style = '';
                
                break;
        }
        
        // if there is a edit button to display output the button bar.
        if (
            ($output_edit_custom_form != '')
            || ($output_edit_form_list_view != '')
            || ($output_edit_form_item_view != '')
            || ($output_edit_custom_shipping_form != '')
            || ($output_edit_custom_billing_form != '')
            || ($output_layout_buttons != '')
        ) {
            $output_button_bar = '
                <div id="button_bar">' .
                    $output_edit_custom_form .
                    $output_edit_form_list_view .
                    $output_edit_form_item_view .
                    $output_edit_custom_shipping_form .
                    $output_edit_custom_billing_form .
                    $output_layout_buttons . '
                </div>';
        }

        if ($layout_type == 'system') {
            $layout_type_system_checked = ' checked="checked"';
            $layout_type_custom_checked = '';

            // If the user is not an admin or designer, then disable custom layout type option.
            if (USER_ROLE > 1) {
                $layout_type_custom_label_class = ' class="disabled"';
                $layout_type_custom_option_disabled = ' disabled="disabled"';
            }

        } else {
            $layout_type_system_checked = '';
            $layout_type_custom_checked = ' checked="checked"';
        }

        $folder_view_pages_checked = '';

        // If pages are enabled for folder view or if there are not any properties, then check the checkbox
        if (($folder_view_properties['pages'] == '1') || ($folder_view_properties['pages'] == '')) {
            $folder_view_pages_checked = ' checked="checked"';
        }

        $folder_view_files_checked = '';

        // If files are enabled for folder view or if there are not any properties, then check the checkbox
        if (($folder_view_properties['files'] == '1') || ($folder_view_properties['files'] == '')) {
            $folder_view_files_checked = ' checked="checked"';
        }

        // Setup the photo galleries default number_of_columns value
        if ($photo_gallery_properties['number_of_columns'] == '') {
            $photo_gallery_number_of_columns = '4';
        } else {
            $photo_gallery_number_of_columns = $photo_gallery_properties['number_of_columns'];
        }
        
        // Setup the photo galleries default thumbnail max size value
        if ($photo_gallery_properties['thumbnail_max_size'] == '') {
            $photo_gallery_thumbnail_max_size = '100';
        } else {
            $photo_gallery_thumbnail_max_size = $photo_gallery_properties['thumbnail_max_size'];
        }
        
        // if the product search is enabled or if there are not any properties, then check the checkbox
        if (($search_results_properties['search_catalog_items'] == '1') || ($search_results_properties['search_catalog_items'] == '')) {
            $search_results_search_catalog_items_checked = ' checked="checked"';
        
        // else leave the checkbox unchecked
        } else {
            $search_results_search_catalog_items_checked = '';
        }
        
        // if update address book address type is enabled prepare to check checkbox
        if ($update_address_book_properties['address_type'] == 1) {
            $update_address_book_address_type_checked = ' checked="checked"';
        } else {
            $update_address_book_address_type_checked = '';
        }
        
        // if custom form is enabled prepare to check checkbox
        if (($custom_form_properties['enabled'] == 1) || ($custom_form_properties['enabled'] == '')) {
            $custom_form_enabled_checked = ' checked="checked"';
        } else {
            $custom_form_enabled_checked = '';
        }
        
        // if custom form quiz is enabled prepare to check checkbox
        if ($custom_form_properties['quiz'] == 1) {
            $custom_form_quiz_checked = ' checked="checked"';
        } else {
            $custom_form_quiz_checked = '';
        }

        // If save is enabled, then check check box.
        if ($custom_form_properties['save'] == 1) {
            $custom_form_save_checked = ' checked="checked"';
        } else {
            $custom_form_save_checked = '';
        }

        // If auto-registration is enabled, then check check box.
        if ($custom_form_properties['auto_registration'] == 1) {
            $custom_form_auto_registration_checked = ' checked="checked"';
        } else {
            $custom_form_auto_registration_checked = '';
        }

        // if submitter e-mail is enabled prepare to check check box
        if ($custom_form_properties['submitter_email'] == 1) {
            $custom_form_submitter_email_checked = ' checked="checked"';
        } else {
            $custom_form_submitter_email_checked = '';
        }

        // if submitter e-mail format is set to plain text, then check the plain text radio button
        if ($custom_form_properties['submitter_email_format'] == 'plain_text') {
            $custom_form_submitter_email_format_plain_text_checked = ' checked="checked"';
            $custom_form_submitter_email_format_html_checked = '';

        // else submitter e-mail format is set to HTML, so check the html radio button
        } else {
            $custom_form_submitter_email_format_plain_text_checked = '';
            $custom_form_submitter_email_format_html_checked = ' checked="checked"';
        }

        // if administrator e-mail is enabled prepare to check check box
        if ($custom_form_properties['administrator_email'] == 1) {
            $custom_form_administrator_email_checked = ' checked="checked"';
        } else {
            $custom_form_administrator_email_checked = '';
        }

        // if administrator e-mail format is set to plain text, then check the plain text radio button
        if ($custom_form_properties['administrator_email_format'] == 'plain_text') {
            $custom_form_administrator_email_format_plain_text_checked = ' checked="checked"';
            $custom_form_administrator_email_format_html_checked = '';

        // else administrator e-mail format is set to HTML, so check the html radio button
        } else {
            $custom_form_administrator_email_format_plain_text_checked = '';
            $custom_form_administrator_email_format_html_checked = ' checked="checked"';
        }
        
        // if custom form membership is enabled prepare to check checkbox
        if ($custom_form_properties['membership'] == 1) {
            $custom_form_membership_checked = ' checked="checked"';
        } else {
            $custom_form_membership_checked = '';
        }

        if ($custom_form_properties['private'] == 1) {
            $custom_form_private_checked = ' checked="checked"';
        } else {
            $custom_form_private_checked = '';
        }

        // If private days is 0, then change value to an empty string,
        // so that a 0 does not appear in the field.
        if ($custom_form_properties['private_days'] == 0) {
            $custom_form_properties['private_days'] = '';
        }

        if ($custom_form_properties['offer'] == 1) {
            $custom_form_offer_checked = ' checked="checked"';
        } else {
            $custom_form_offer_checked = '';
        }

        // If offer days is 0, then change value to an empty string,
        // so that a 0 does not appear in the field.
        if ($custom_form_properties['offer_days'] == 0) {
            $custom_form_properties['offer_days'] = '';
        }

        $custom_form_offer_eligibility_everyone = '';
        $custom_form_offer_eligibility_new_contacts = '';
        $custom_form_offer_eligibility_existing_contacts = '';

        switch ($custom_form_properties['offer_eligibility']) {
            case 'everyone':
                $custom_form_offer_eligibility_everyone = ' selected="selected"';
                break;

            case 'new_contacts':
                $custom_form_offer_eligibility_new_contacts = ' selected="selected"';
                break;

            case 'existing_contacts':
                $custom_form_offer_eligibility_existing_contacts = ' selected="selected"';
                break;
        }

        // If confirmation type is set to message, then select the message radio button.
        if ($custom_form_properties['confirmation_type'] == 'message') {
            $custom_form_confirmation_type_message_checked = ' checked="checked"';
            $custom_form_confirmation_type_page_checked = '';

        // Otherwise confirmation type is set to page, so select the page radio button.
        } else {
            $custom_form_confirmation_type_message_checked = '';
            $custom_form_confirmation_type_page_checked = ' checked="checked"';
        }

        // If confirmation alternative page is enabled then check checkbox.
        if ($custom_form_properties['confirmation_alternative_page'] == 1) {
            $custom_form_confirmation_alternative_page_checked = ' checked="checked"';
        } else {
            $custom_form_confirmation_alternative_page_checked = '';
        }

        // Select the correct radio button for return type.
        switch ($custom_form_properties['return_type']) {
            case 'custom_form':
                $custom_form_return_type_custom_form_checked = ' checked="checked"';
                $custom_form_return_type_message_checked = '';
                $custom_form_return_type_page_checked = '';
                break;

            case 'message':
                $custom_form_return_type_custom_form_checked = '';
                $custom_form_return_type_message_checked = ' checked="checked"';
                $custom_form_return_type_page_checked = '';
                break;

            case 'page':
                $custom_form_return_type_custom_form_checked = '';
                $custom_form_return_type_message_checked = '';
                $custom_form_return_type_page_checked = ' checked="checked"';
                break;
        }

        // If return alternative page is enabled then check checkbox.
        if ($custom_form_properties['return_alternative_page'] == 1) {
            $custom_form_return_alternative_page_checked = ' checked="checked"';
        } else {
            $custom_form_return_alternative_page_checked = '';
        }

        // If pretty urls is enabled then check checkbox.
        if ($custom_form_properties['pretty_urls'] == 1) {
            $custom_form_pretty_urls_checked = ' checked="checked"';
        } else {
            $custom_form_pretty_urls_checked = '';
        }

        $form_list_view_viewer_filter_checked = '';

        if ($form_list_view_properties['viewer_filter'] == 1) {
            $form_list_view_viewer_filter_checked = ' checked="checked"';
        }

        $form_list_view_viewer_filter_submitter_checked = '';

        if (($form_list_view_properties['viewer_filter_submitter'] == 1) || ($form_list_view_properties['viewer_filter'] == 0)) {
            $form_list_view_viewer_filter_submitter_checked = ' checked="checked"';
        }

        $form_list_view_viewer_filter_watcher_checked = '';

        if (($form_list_view_properties['viewer_filter_watcher'] == 1) || ($form_list_view_properties['viewer_filter'] == 0)) {
            $form_list_view_viewer_filter_watcher_checked = ' checked="checked"';
        }

        $form_list_view_viewer_filter_editor_checked = '';

        if (($form_list_view_properties['viewer_filter_editor'] == 1) || ($form_list_view_properties['viewer_filter'] == 0)) {
            $form_list_view_viewer_filter_editor_checked = ' checked="checked"';
        }

        // if form item view submitter security is enabled then prepare to check checkbox
        if ($form_item_view_properties['submitter_security'] == 1) {
            $form_item_view_submitter_security_checked = ' checked="checked"';
        } else {
            $form_item_view_submitter_security_checked = '';
        }
        
        // if form item view allows registered users to edit form submissions, prepare to check checkbox
        if ($form_item_view_properties['submitted_form_editable_by_registered_user'] == 1) {
            $form_item_view_submitted_form_editable_by_registered_user_checked = ' checked="checked"';
            $form_item_view_submitted_form_editable_by_submitter_row_style = 'display: none';
        } else {
            $form_item_view_submitted_form_editable_by_registered_user_checked = '';
        }
        
        // if form item view allows users to edit their own submissions, prepare to check checkbox
        if ($form_item_view_properties['submitted_form_editable_by_submitter'] == 1) {
            $form_item_view_submitted_form_editable_by_submitter_checked = ' checked="checked"';
        } else {
            $form_item_view_submitted_form_editable_by_submitter_checked = '';
        }
        
        // if display is enabled for form view directory, prepare to check checkbox
        if ($form_view_directory_properties['summary'] == 1) {
            $form_view_directory_summary_checked = ' checked="checked"';
        } else {
            $form_view_directory_summary_checked = '';
        }
        
        // if form view directory's summary date range is blank, then set it to the default value
        if ($form_view_directory_properties['summary_days'] == '') {
            $form_view_directory_summary_days = '30';
            
        // else the form view directory's summary date range is not blank, so set it to the saved value
        } else {
            $form_view_directory_summary_days = $form_view_directory_properties['summary_days'];
        }
        
        // if form view directory's summary maximum number of results is blank, then set it to the default value
        if ($form_view_directory_properties['summary_maximum_number_of_results'] == '') {
            $form_view_directory_summary_maximum_number_of_results = '5';
            
        // else the form view directory's summary maximum number of results is not blank, so set it to the saved value
        } else {
            $form_view_directory_summary_maximum_number_of_results = $form_view_directory_properties['summary_maximum_number_of_results'];
        }
        
        // if form view directory's form list view heading is blank, then set it to the default value
        if ($form_view_directory_properties['form_list_view_heading'] == '') {
            $form_view_directory_form_list_view_heading = 'Forum';
            
        // else the form view directory's form list view heading is not blank, so set it to the saved value
        } else {
            $form_view_directory_form_list_view_heading = $form_view_directory_properties['form_list_view_heading'];
        }
        
        // if form view directory's subject heading is blank, then set it to the default value
        if ($form_view_directory_properties['subject_heading'] == '') {
            $form_view_directory_subject_heading = 'Subject';
            
        // else the form view directory's subject heading is not blank, so set it to the saved value
        } else {
            $form_view_directory_subject_heading = $form_view_directory_properties['subject_heading'];
        }
        
        // if form view directory's number of submitted forms heading is blank, then set it to the default value
        if ($form_view_directory_properties['number_of_submitted_forms_heading'] == '') {
            $form_view_directory_number_of_submitted_forms_heading = 'Forms';
            
        // else the form view directory's number of submitted forms heading is not blank, so set it to the saved value
        } else {
            $form_view_directory_number_of_submitted_forms_heading = $form_view_directory_properties['number_of_submitted_forms_heading'];
        }
        
        // if calendar view's default view is set to weekly, then prepare to select option
        if ($calendar_view_properties['default_view'] == 'weekly') {
            $calendar_view_default_view_monthly = '';
            $calendar_view_default_view_weekly = ' selected="selected"';
            $calendar_view_default_view_upcoming = '';

        // else if calendar view's default view is set to upcoming, then prepare to select option
        } elseif ($calendar_view_properties['default_view'] == 'upcoming') {
            $calendar_view_default_view_monthly = '';
            $calendar_view_default_view_weekly = '';
            $calendar_view_default_view_upcoming = ' selected="selected"';

        // else calendar view's default view is empty or is monthly, so prepare to select option
        } else {
            $calendar_view_default_view_monthly = ' selected="selected"';
            $calendar_view_default_view_weekly = '';
            $calendar_view_default_view_upcoming = '';
        }
        
        // Setup the calendar views default number_of_upcoming_events value
        if ($calendar_view_properties['number_of_upcoming_events'] == '') {
            $calendar_view_number_of_upcoming_events_value = '5';
        } else {
            $calendar_view_number_of_upcoming_events_value = $calendar_view_properties['number_of_upcoming_events'];
        }
        
        // If the catalog menu is on, or if the original page type was not a catalog page then check the menu checkbox
        if (($catalog_properties['menu'] == '1') || ($catalog_properties['menu'] == '')) {
            $catalog_menu_checked = ' checked="checked"';
            
        // else do not check the checkbox
        } else {
            $catalog_menu_checked = '';
        }
        
        // If the catalog search is on, or if the original page type was not a catalog page then check the search checkbox
        if (($catalog_properties['search'] == '1') || ($catalog_properties['search'] == '')) {
            $catalog_search_checked = ' checked="checked"';
            
        // else do not check the checkbox
        } else {
            $catalog_search_checked = '';
        }
        
        // set the default number_of_columns value for the catalog page type
        if ($catalog_properties['number_of_columns'] == '') {
            $catalog_number_of_columns = '4';
        } else {
            $catalog_number_of_columns = $catalog_properties['number_of_columns'];
        }
        
        // set the default image width value for the catalog page type
        if ($catalog_properties['image_width'] == '') {
            $catalog_image_width = '50';
        } else {
            $catalog_image_width = $catalog_properties['image_width'];
        }
        
        // set the default image height value for the catalog page type
        if ($catalog_properties['image_height'] == '') {
            $catalog_image_height = '50';
        } else {
            $catalog_image_height = $catalog_properties['image_height'];
        }
        
        // If allow_customer_to_add_product_to_order is on, or if the original page type was not a catalog detail page then check the allow_customer_to_add_product_to_order checkbox
        if (($catalog_detail_properties['allow_customer_to_add_product_to_order'] == '1') || ($catalog_detail_properties['allow_customer_to_add_product_to_order'] == '')) {
            $catalog_detail_allow_customer_to_add_product_to_order_checked = ' checked="checked"';
            
        // else do not check the checkbox
        } else {
            $catalog_detail_allow_customer_to_add_product_to_order_checked = '';
        }

        // if product description type is set to full description, then check that radio button
        if ($express_order_properties['product_description_type'] == 'full_description') {
            $express_order_product_description_type_full_description_checked = ' checked="checked"';
            $express_order_product_description_type_short_description_checked = '';

        // else product description type is set to short description, so check that radio button
        } else {
            $express_order_product_description_type_full_description_checked = '';
            $express_order_product_description_type_short_description_checked = ' checked="checked"';
        }

        $express_order_shipping_form_checked = '';

        if ($express_order_properties['shipping_form']) {
            $express_order_shipping_form_checked = ' checked="checked"';
        }
        
        // if update button label is empty, then prepare default value
        if (!$express_order_properties['update_button_label']) {
            // if a shopping cart label is found, then use that with "Update" in front of the label
            if ($express_order_properties['shopping_cart_label']) {
                $express_order_properties['update_button_label'] = 'Update ' . h($express_order_properties['shopping_cart_label']);
                
            // else a shopping cart label could not be found, so just use a default label
            } else {
                $express_order_properties['update_button_label'] = 'Update Cart';
            }
        }
        
        // if express order custom field 1 required is enabled prepare to check checkbox
        if ($express_order_properties['custom_field_1_required'] == 1) {
            $express_order_custom_field_1_required_checked = ' checked="checked"';
        } else {
            $express_order_custom_field_1_required_checked = '';
        }
        
        // if express order custom field 2 required is enabled prepare to check checkbox
        if ($express_order_properties['custom_field_2_required'] == 1) {
            $express_order_custom_field_2_required_checked = ' checked="checked"';
        } else {
            $express_order_custom_field_2_required_checked = '';
        }
        
        // if express order po number is enabled prepare to check checkbox
        if ($express_order_properties['po_number'] == 1) {
            $express_order_po_number_checked = ' checked="checked"';
        } else {
            $express_order_po_number_checked = '';
        }

        $express_order_form_checked = '';

        if ($express_order_properties['form'] == 1) {
            $express_order_form_checked = ' checked="checked"';
        }

        // If offline payment is always allowed then prepare to check check box.
        if ($express_order_properties['offline_payment_always_allowed'] == 1) {
            $express_order_offline_payment_always_allowed_checked = ' checked="checked"';
        } else {
            $express_order_offline_payment_always_allowed_checked = '';
        }

        // If auto-registration is enabled, then check check box.
        if ($express_order_properties['auto_registration'] == 1) {
            $express_order_auto_registration_checked = ' checked="checked"';
        } else {
            $express_order_auto_registration_checked = '';
        }
        
        // if order receipt e-mail is enabled prepare to check checkbox
        if ($express_order_properties['order_receipt_email'] == 1) {
            $express_order_order_receipt_email_checked = ' checked="checked"';
        } else {
            $express_order_order_receipt_email_checked = '';
        }

        // if order receipt format is set to "plain text", then check the plain text radio button
        if ($express_order_properties['order_receipt_email_format'] == 'plain_text') {
            $express_order_order_receipt_email_format_plain_text_checked = ' checked="checked"';
            $express_order_order_receipt_email_format_html_checked = '';

        // else order receipt format is set to "HTML", so check the html radio button
        } else {
            $express_order_order_receipt_email_format_plain_text_checked = '';
            $express_order_order_receipt_email_format_html_checked = ' checked="checked"';
        }

        // if product layout is set to drop-down selection, prepare checked value for radio buttons
        if ($order_form_properties['product_layout'] == 'drop-down selection') {
            $order_form_product_layout_list = '';
            $order_form_product_layout_drop_down_selection = ' checked="checked"';

        // else product layout is empty or is list, so prepare checked value for radio buttons
        } else {
            $order_form_product_layout_list = ' checked="checked"';
            $order_form_product_layout_drop_down_selection = '';
        }
        
        // if submit button label is empty, then prepare default value
        if (!$order_form_properties['add_button_label']) {
            $order_form_properties['add_button_label'] = 'Continue';
        }

        // if product description type is set to full description, then check that radio button
        if ($shopping_cart_properties['product_description_type'] == 'full_description') {
            $shopping_cart_product_description_type_full_description_checked = ' checked="checked"';
            $shopping_cart_product_description_type_short_description_checked = '';

        // else product description type is set to short description, so check that radio button
        } else {
            $shopping_cart_product_description_type_full_description_checked = '';
            $shopping_cart_product_description_type_short_description_checked = ' checked="checked"';
        }
        
        // if update button label is empty, then prepare default value
        if (!$shopping_cart_properties['update_button_label']) {
            // if a shopping cart label is found, then use that with "Update" in front of the label
            if ($shopping_cart_properties['shopping_cart_label']) {
                $shopping_cart_properties['update_button_label'] = 'Update ' . h($shopping_cart_properties['shopping_cart_label']);
                
            // else a shopping cart label could not be found, so just use a default label
            } else {
                $shopping_cart_properties['update_button_label'] = 'Update Cart';
            }
        }
        
        // if checkout button label is empty, then prepare default value
        if (!$shopping_cart_properties['checkout_button_label']) {
            $shopping_cart_properties['checkout_button_label'] = 'Checkout';
        }
        
        // if shipping address and arrival address type is enabled prepare to check checkbox
        if ($shipping_address_and_arrival_properties['address_type'] == 1) {
            $shipping_address_and_arrival_address_type_checked = ' checked="checked"';
        } else {
            $shipping_address_and_arrival_address_type_checked = '';
        }
        
        // if shipping address and arrival form is enabled prepare to check checkbox
        if ($shipping_address_and_arrival_properties['form'] == 1) {
            $shipping_address_and_arrival_form_checked = ' checked="checked"';
        } else {
            $shipping_address_and_arrival_form_checked = '';
        }

        // if product description type is set to full description, then check that radio button
        if ($shipping_method_properties['product_description_type'] == 'full_description') {
            $shipping_method_product_description_type_full_description_checked = ' checked="checked"';
            $shipping_method_product_description_type_short_description_checked = '';

        // else product description type is set to short description, so check that radio button
        } else {
            $shipping_method_product_description_type_full_description_checked = '';
            $shipping_method_product_description_type_short_description_checked = ' checked="checked"';
        }
        
        // if billing information custom field 1 required is enabled prepare to check checkbox
        if ($billing_information_properties['custom_field_1_required'] == 1) {
            $billing_information_custom_field_1_required_checked = ' checked="checked"';
        } else {
            $billing_information_custom_field_1_required_checked = '';
        }
        
        // if billing information custom field 2 required is enabled prepare to check checkbox
        if ($billing_information_properties['custom_field_2_required'] == 1) {
            $billing_information_custom_field_2_required_checked = ' checked="checked"';
        } else {
            $billing_information_custom_field_2_required_checked = '';
        }
        
        // if billing information po number is enabled prepare to check checkbox
        if ($billing_information_properties['po_number'] == 1) {
            $billing_information_po_number_checked = ' checked="checked"';
        } else {
            $billing_information_po_number_checked = '';
        }

        $billing_information_form_checked = '';

        if ($billing_information_properties['form'] == 1) {
            $billing_information_form_checked = ' checked="checked"';
        }

        // if product description type is set to full description, then check that radio button
        if ($order_preview_properties['product_description_type'] == 'full_description') {
            $order_preview_product_description_type_full_description_checked = ' checked="checked"';
            $order_preview_product_description_type_short_description_checked = '';

        // else product description type is set to short description, so check that radio button
        } else {
            $order_preview_product_description_type_full_description_checked = '';
            $order_preview_product_description_type_short_description_checked = ' checked="checked"';
        }

        // If offline payment is always allowed then prepare to check check box.
        if ($order_preview_properties['offline_payment_always_allowed'] == 1) {
            $order_preview_offline_payment_always_allowed_checked = ' checked="checked"';
        } else {
            $order_preview_offline_payment_always_allowed_checked = '';
        }

        // If auto-registration is enabled, then check check box.
        if ($order_preview_properties['auto_registration'] == 1) {
            $order_preview_auto_registration_checked = ' checked="checked"';
        } else {
            $order_preview_auto_registration_checked = '';
        }

        // if order receipt e-mail is enabled prepare to check checkbox
        if ($order_preview_properties['order_receipt_email'] == 1) {
            $order_preview_order_receipt_email_checked = ' checked="checked"';
        } else {
            $order_preview_order_receipt_email_checked = '';
        }

        // if order receipt e-mail format is set to "plain text", then check the plain text radio button
        if ($order_preview_properties['order_receipt_email_format'] == 'plain_text') {
            $order_preview_order_receipt_email_format_plain_text_checked = ' checked="checked"';
            $order_preview_order_receipt_email_format_html_checked = '';

        // else order receipt format is set to "HTML", so check the html radio button
        } else {
            $order_preview_order_receipt_email_format_plain_text_checked = '';
            $order_preview_order_receipt_email_format_html_checked = ' checked="checked"';
        }

        // if product description type is set to full description, then check that radio button
        if ($order_receipt_properties['product_description_type'] == 'full_description') {
            $order_receipt_product_description_type_full_description_checked = ' checked="checked"';
            $order_receipt_product_description_type_short_description_checked = '';

        // else product description type is set to short description, so check that radio button
        } else {
            $order_receipt_product_description_type_full_description_checked = '';
            $order_receipt_product_description_type_short_description_checked = ' checked="checked"';
        }
        
        // if submit button label is empty, then prepare default value
        if (!$affiliate_sign_up_form_properties['submit_button_label']) {
            $affiliate_sign_up_form_properties['submit_button_label'] = 'Sign Up';
        }
        
        $output_search_results_page_type_properties = '';

        // If advanced site search is enabled then output row for folder pick list
        // for search results properties.
        if (SEARCH_TYPE == 'advanced') {
            $output_search_results_page_type_properties .=
                '<tr id="search_results_search_folder_id_row" style="' . $search_results_search_folder_id_row_style . '">
                    <td>Search Folder:</td>
                    <td><select name="search_results_search_folder_id">' . select_folder($search_results_properties['search_folder_id']) . '</select></td>
                </tr>';
        }

        $output_ecommerce_page_type_properties = '';
        
        if (ECOMMERCE == true) {
            // if the user is an advanced user then prepare to output search results page type properties
            if ($user['role'] < 3) {
                $output_search_results_page_type_properties .=
                    '<tr id="search_results_search_catalog_items_row" style="' . $search_results_search_catalog_items_row_style . '">
                        <td>Search Products:</td>
                        <td><input type="checkbox" id="search_results_search_catalog_items" name="search_results_search_catalog_items" value="1"' . $search_results_search_catalog_items_checked . ' class="checkbox" onclick="show_or_hide_search_catalog_items()" /></td>
                    </tr>
                    <tr id="search_results_product_group_id_row" style="' . $search_results_product_group_id_row_style . '">
                        <td style="padding-left: 20px">In Product Group:</td>
                        <td><select name="search_results_product_group_id"><option value="">-Select-</option>' . get_product_group_options($search_results_properties['product_group_id'],  $parent_product_group_id = 0, $excluded_product_group_id = 0, $level = 0, $product_groups = array(), $include_select_product_groups = FALSE) . '</select> (leave unselected for all product groups)</td>
                    </tr>
                    <tr id="search_results_catalog_detail_page_id_row" style="' . $search_results_catalog_detail_page_id_row_style . '">
                        <td style="padding-left: 20px">Catalog Detail Page:</td>
                        <td><select name="search_results_catalog_detail_page_id"><option value="">-Select-</option>' . select_page($search_results_properties['catalog_detail_page_id'], 'catalog detail') . '</select></td>
                    </tr>';
            }

            $output_express_order_offline_payment_rows = '';
            $output_order_preview_offline_payment_rows = '';
            
            // if allow offline orders is on, then output offline payment label
            if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
                $output_express_order_offline_payment_rows = 
                    '<tr id="express_order_offline_payment_always_allowed_row" style="' . $express_order_offline_payment_always_allowed_row_style . '">
                        <td><label for="express_order_offline_payment_always_allowed">Always Allow Offline Payments:</label></td>
                        <td><input type="checkbox" id="express_order_offline_payment_always_allowed" name="express_order_offline_payment_always_allowed" value="1"' . $express_order_offline_payment_always_allowed_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="express_order_offline_payment_label_row" style="' . $express_order_offline_payment_label_row_style . '">
                        <td>Offline Payment Label:</td>
                        <td><input name="express_order_offline_payment_label" type="text" value="' . h($express_order_properties['offline_payment_label']) . '" maxlength="255" /></td>
                    </tr>';
                
                $output_order_preview_offline_payment_rows = 
                    '<tr id="order_preview_offline_payment_always_allowed_row" style="' . $order_preview_offline_payment_always_allowed_row_style . '">
                        <td><label for="order_preview_offline_payment_always_allowed">Always Allow Offline Payments:</label></td>
                        <td><input type="checkbox" id="order_preview_offline_payment_always_allowed" name="order_preview_offline_payment_always_allowed" value="1"' . $order_preview_offline_payment_always_allowed_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="order_preview_offline_payment_label_row" style="' . $order_preview_offline_payment_label_row_style . '">
                        <td>Offline Payment Label:</td>
                        <td><input name="order_preview_offline_payment_label" type="text" value="' . h($order_preview_properties['offline_payment_label']) . '" maxlength="255" /></td>
                    </tr>';
            }
            
            $output_ecommerce_page_type_properties =
                '<tr id="catalog_product_group_id_row" style="' . $catalog_product_group_id_row_style . '">
                    <td>Product Group:</td>
                    <td><select name="catalog_product_group_id"><option value="">-Select-</option>' . get_product_group_options($catalog_properties['product_group_id'], $parent_product_group_id = 0, $excluded_product_group_id = 0, $level = 0, $product_groups = array(), $include_select_product_groups = FALSE) . '</select> (leave unselected for all product groups)</td>
                </tr>
                <tr id="catalog_menu_row" style="' . $catalog_menu_row_style . '">
                    <td>Enable Menu:</td>
                    <td><input type="checkbox" id="catalog_menu" name="catalog_menu" value="1"' . $catalog_menu_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="catalog_search_row" style="' . $catalog_search_row_style . '">
                    <td>Enable Search:</td>
                    <td><input type="checkbox" id="catalog_search" name="catalog_search" value="1"' . $catalog_search_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="catalog_number_of_featured_items_row" style="' . $catalog_number_of_featured_items_row_style . '">
                    <td>Number of Featured Items:</td>
                    <td><input name="catalog_number_of_featured_items" type="text" value="' . $catalog_properties['number_of_featured_items'] . '" maxlength="2" size="3" /></td>
                </tr>
                <tr id="catalog_number_of_new_items_row" style="' . $catalog_number_of_new_items_row_style . '">
                    <td>Number of New Items:</td>
                    <td><input name="catalog_number_of_new_items" type="text" value="' . $catalog_properties['number_of_new_items'] . '" maxlength="2" size="3" /></td>
                </tr>
                <tr id="catalog_number_of_columns_row" style="' . $catalog_number_of_columns_row_style . '">
                    <td>Number of Columns:</td>
                    <td><input name="catalog_number_of_columns" type="text" value="' . $catalog_number_of_columns . '" maxlength="2" size="3" /></td>
                </tr>
                <tr id="catalog_image_width_row" style="' . $catalog_image_width_row_style . '">
                    <td>Image Width:</td>
                    <td><input name="catalog_image_width" type="text" value="' . $catalog_image_width . '" maxlength="4" size="3" /> pixels</td>
                </tr>
                <tr id="catalog_image_height_row" style="' . $catalog_image_height_row_style . '">
                    <td>Image Height:</td>
                    <td><input name="catalog_image_height" type="text" value="' . $catalog_image_height . '" maxlength="4" size="3" /> pixels</td>
                </tr>
                <tr id="catalog_back_button_label_row" style="' . $catalog_back_button_label_row_style . '">
                    <td>Back Button Label:</td>
                    <td><input name="catalog_back_button_label" type="text" value="' . $catalog_properties['back_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="catalog_catalog_detail_page_id_row" style="' . $catalog_catalog_detail_page_id_row_style . '">
                    <td>Catalog Detail Page:</td>
                    <td><select name="catalog_catalog_detail_page_id"><option value="">-Select-</option>' . select_page($catalog_properties['catalog_detail_page_id'], 'catalog detail') . '</select></td>
                </tr>
                <tr id="catalog_detail_allow_customer_to_add_product_to_order_row" style="' . $catalog_detail_allow_customer_to_add_product_to_order_row_style . '">
                    <td>Allow customer to add product to order:</td>
                    <td><input type="checkbox" id="catalog_detail_allow_customer_to_add_product_to_order" name="catalog_detail_allow_customer_to_add_product_to_order" value="1"' . $catalog_detail_allow_customer_to_add_product_to_order_checked . ' class="checkbox" onclick="show_or_hide_allow_customer_to_add_product_to_order()" /></td>
                </tr>
                <tr id="catalog_detail_add_button_label_row" style="' . $catalog_detail_add_button_label_row_style . '">
                    <td style="padding-left: 2em">Add Button Label:</td>
                    <td><input name="catalog_detail_add_button_label" type="text" value="' . $catalog_detail_properties['add_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="catalog_detail_next_page_id_row" style="' . $catalog_detail_next_page_id_row_style . '">
                    <td style="padding-left: 2em">Next Page:</td>
                    <td><select name="catalog_detail_next_page_id"><option value="">-Select-</option>' . select_page($catalog_detail_properties['next_page_id']) . '</select></td>
                </tr>
                <tr id="catalog_detail_back_button_label_row" style="' . $catalog_detail_back_button_label_row_style . '">
                    <td>Back Button Label:</td>
                    <td><input name="catalog_detail_back_button_label" type="text" value="' . $catalog_detail_properties['back_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="express_order_shopping_cart_label_row" style="' . $express_order_shopping_cart_label_row_style . '">
                    <td>Shopping Cart Label:</td>
                    <td><input name="express_order_shopping_cart_label" type="text" value="' . $express_order_properties['shopping_cart_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="express_order_quick_add_label_row" style="' . $express_order_quick_add_label_row_style . '">
                    <td>Quick Add Label:</td>
                    <td><input name="express_order_quick_add_label" type="text" value="' . $express_order_properties['quick_add_label'] . '" maxlength="255" /></td>
                </tr>
                <tr id="express_order_quick_add_product_group_id_row" style="' . $express_order_quick_add_product_group_id_row_style . '">
                    <td>Quick Add Product Group:</td>
                    <td><select name="express_order_quick_add_product_group_id"><option value="">-None-</option>' . get_product_group_options($express_order_properties['quick_add_product_group_id']) . '</select></td>
                </tr>
                <tr id="express_order_product_description_type_row" style="' . $express_order_product_description_type_row_style . '">
                    <td>Product Description Type:</td>
                    <td><input type="radio" id="express_order_product_description_type_full_description" name="express_order_product_description_type" value="full_description" class="radio"' . $express_order_product_description_type_full_description_checked . ' /><label for="express_order_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="express_order_product_description_type_short_description" name="express_order_product_description_type" value="short_description" class="radio"' . $express_order_product_description_type_short_description_checked . ' /><label for="express_order_product_description_type_short_description">Short Description</label></td>
                </tr>
                <tr id="express_order_shipping_form_row" style="' . $express_order_shipping_form_row_style . '">
                    <td><label for="express_order_shipping_form">Enable Custom Shipping Form:</label></td>
                    <td>
                        <input
                            id="express_order_shipping_form"
                            name="express_order_shipping_form"
                            type="checkbox"
                            value="1"
                            ' . $express_order_shipping_form_checked . '
                            class="checkbox"
                            onclick="toggle_express_order_custom_shipping_form()">
                        <script>var original_express_order_shipping_form = "' . $express_order_properties['shipping_form'] . '";</script>
                        <span id="express_order_shipping_form_notice" style="display: none; padding-left: 1em">
                            (when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Shipping Form.)
                        </span>
                    </td>
                </tr>
                <tr id="express_order_special_offer_code_label_row" style="' . $express_order_special_offer_code_label_row_style . '">
                    <td>Special Offer Code Label:</td>
                    <td><input name="express_order_special_offer_code_label" type="text" value="' . $express_order_properties['special_offer_code_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="express_order_special_offer_code_message_row" style="' . $express_order_special_offer_code_message_row_style . '">
                    <td>Special Offer Code Message:</td>
                    <td><input name="express_order_special_offer_code_message" type="text" value="' . $express_order_properties['special_offer_code_message'] . '" size="50" maxlength="255" /></td>
                </tr>
                <tr id="express_order_custom_field_1_label_row" style="' . $express_order_custom_field_1_label_row_style . '">
                    <td>Custom Field #1 Label:</td>
                    <td><input name="express_order_custom_field_1_label" type="text" value="' . $express_order_properties['custom_field_1_label'] . '" maxlength="255" /> <input type="checkbox" name="express_order_custom_field_1_required" id="express_order_custom_field_1_required" value="1"' . $express_order_custom_field_1_required_checked . ' class="checkbox" /><label for="express_order_custom_field_1_required"> Required</label></td>
                </tr>
                <tr id="express_order_custom_field_2_label_row" style="' . $express_order_custom_field_2_label_row_style . '">
                    <td>Custom Field #2 Label:</td>
                    <td><input name="express_order_custom_field_2_label" type="text" value="' . $express_order_properties['custom_field_2_label'] . '" maxlength="255" /> <input type="checkbox" name="express_order_custom_field_2_required" id="express_order_custom_field_2_required" value="1"' . $express_order_custom_field_2_required_checked . ' class="checkbox" /><label for="express_order_custom_field_2_required"> Required</label></td>
                </tr>
                <tr id="express_order_po_number_row" style="' . $express_order_po_number_row_style . '">
                    <td>Enable PO Number:</td>
                    <td><input type="checkbox" name="express_order_po_number" value="1"' . $express_order_po_number_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="express_order_form_row" style="' . $express_order_form_row_style . '">
                    <td><label for="express_order_form">Enable Custom Billing Form:</label></td>
                    <td><input id="express_order_form" name="express_order_form" type="checkbox" value="1"' . $express_order_form_checked . ' class="checkbox" onclick="show_or_hide_express_order_custom_billing_form()" /><script type="text/javascript">var original_express_order_form = "' . $express_order_properties['form'] . '";</script><span id="express_order_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Billing Form.)</span></td>
                </tr>
                <tr id="express_order_form_name_row" style="' . $express_order_form_name_row_style . '">
                    <td style="padding-left: 2em">Form Title for Display:</td>
                    <td><input id="express_order_form_name" name="express_order_form_name" type="text" value="' . h($express_order_properties['form_name']) . '" size="80" maxlength="100" /></td>
                </tr>
                <tr id="express_order_form_label_column_width_row" style="' . $express_order_form_label_column_width_row_style . '">
                    <td style="padding-left: 2em">Label Column Width:</td>
                    <td><input id="express_order_form_label_column_width" name="express_order_form_label_column_width" type="text" value="' . h($express_order_properties['form_label_column_width']) . '" size="3" maxlength="3" /> % (leave blank for auto)</td>
                </tr>
                <tr id="express_order_card_verification_number_page_id_row" style="' . $express_order_card_verification_number_page_id_row_style . '">
                    <td>Card Verification Number Page:</td>
                    <td><select name="express_order_card_verification_number_page_id"><option value="">-Select-</option>' . select_page($express_order_properties['card_verification_number_page_id']) . '</select></td>
                </tr>
                ' . $output_express_order_offline_payment_rows . '
                <tr id="express_order_terms_page_id_row" style="' . $express_order_terms_page_id_row_style . '">
                    <td>Terms Page:</td>
                    <td><select name="express_order_terms_page_id"><option value="">-Select-</option>' . select_page($express_order_properties['terms_page_id']) . '</select></td>
                </tr>
                <tr id="express_order_update_button_label_row" style="' . $express_order_update_button_label_row_style . '">
                    <td>Update Button Label:</td>
                    <td><input name="express_order_update_button_label" type="text" value="' . $express_order_properties['update_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="express_order_purchase_now_button_label_row" style="' . $express_order_purchase_now_button_label_row_style . '">
                    <td>Purchase Now Button Label:</td>
                    <td><input name="express_order_purchase_now_button_label" type="text" value="' . $express_order_properties['purchase_now_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="express_order_auto_registration_row" style="' . $express_order_auto_registration_row_style . '">
                    <td><label for="express_order_auto_registration">Enable Auto-Registration:</label></td>
                    <td><input type="checkbox" id="express_order_auto_registration" name="express_order_auto_registration" value="1"' . $express_order_auto_registration_checked . ' class="checkbox"></td>
                </tr>';

            // If hooks are enabled and the user is a designer or administrator then output hook rows for PHP code.
            if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                $output_ecommerce_page_type_properties .=
                    '<tr id="express_order_pre_save_hook_code_row" style="' . $express_order_pre_save_hook_code_row_style . '">
                        <td>Pre-Save Hook Code:</td>
                        <td><textarea name="express_order_pre_save_hook_code" rows="5" cols="70">' . h($express_order_properties['pre_save_hook_code']) . '</textarea></td>
                    </tr>
                    <tr id="express_order_post_save_hook_code_row" style="' . $express_order_post_save_hook_code_row_style . '">
                        <td>Post-Save Hook Code:</td>
                        <td><textarea name="express_order_post_save_hook_code" rows="5" cols="70">' . h($express_order_properties['post_save_hook_code']) . '</textarea></td>
                    </tr>';
            }

            $output_ecommerce_page_type_properties .=
                '<tr id="express_order_order_receipt_email_row" style="' . $express_order_order_receipt_email_row_style . '">
                    <td><label for="express_order_order_receipt_email">E-mail Order Receipt:</label></td>
                    <td><input type="checkbox" id="express_order_order_receipt_email" name="express_order_order_receipt_email" value="1"' . $express_order_order_receipt_email_checked . ' class="checkbox" onclick="show_or_hide_express_order_order_receipt_email()" /></td>
                </tr>
                <tr id="express_order_order_receipt_email_subject_row" style="' . $express_order_order_receipt_email_subject_row_style . '">
                    <td style="padding-left: 2em">Subject:</td>
                    <td><input name="express_order_order_receipt_email_subject" value="' . h($express_order_properties['order_receipt_email_subject']) . '" type="text" size="80" maxlength="255" /></td>
                </tr>
                <tr id="express_order_order_receipt_email_format_row" style="' . $express_order_order_receipt_email_format_row_style . '">
                    <td style="padding-left: 2em">Format:</td>
                    <td><input type="radio" id="express_order_order_receipt_email_format_plain_text" name="express_order_order_receipt_email_format" value="plain_text" class="radio"' . $express_order_order_receipt_email_format_plain_text_checked . ' onclick="show_or_hide_express_order_order_receipt_email_format()" /><label for="express_order_order_receipt_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="express_order_order_receipt_email_format_html" name="express_order_order_receipt_email_format" value="html" class="radio"' . $express_order_order_receipt_email_format_html_checked . ' onclick="show_or_hide_express_order_order_receipt_email_format()" /><label for="express_order_order_receipt_email_format_html">HTML</label></td>
                </tr>
                <tr id="express_order_order_receipt_email_header_row" style="' . $express_order_order_receipt_email_header_row_style . '">
                    <td style="padding-left: 2em">Header:</td>
                    <td><textarea name="express_order_order_receipt_email_header" rows="5" cols="70">' . h($express_order_properties['order_receipt_email_header']) . '</textarea></td>
                </tr>
                <tr id="express_order_order_receipt_email_footer_row" style="' . $express_order_order_receipt_email_footer_row_style . '">
                    <td style="padding-left: 2em">Footer:</td>
                    <td><textarea name="express_order_order_receipt_email_footer" rows="5" cols="70">' . h($express_order_properties['order_receipt_email_footer']) . '</textarea></td>
                </tr>
                <tr id="express_order_order_receipt_email_page_id_row" style="' . $express_order_order_receipt_email_page_id_row_style . '">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="express_order_order_receipt_email_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page($express_order_properties['order_receipt_email_page_id'], 'order receipt') . '</select></td>
                </tr>
                <tr id="express_order_next_page_id_row" style="' . $express_order_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="express_order_next_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page($express_order_properties['next_page_id'], 'order receipt') . '</select></td>
                </tr>
                <tr id="order_form_product_group_id_row" style="' . $order_form_product_group_id_row_style . '">
                    <td>Product Group:</td>
                    <td><select name="order_form_product_group_id"><option value="">-Select-</option>' . get_product_group_options($order_form_properties['product_group_id']) . '</select></td>
                </tr>
                <tr id="order_form_product_layout_row_1" style="' . $order_form_product_layout_row_1_style . '">
                    <td>Product Layout:</td>
                    <td><input type="radio" id="order_form_product_layout_list" name="order_form_product_layout" value="list" class="radio"' . $order_form_product_layout_list . ' /><label for="order_form_product_layout_list">List (full description)</label></td>
                </tr>
                <tr id="order_form_product_layout_row_2" style="' . $order_form_product_layout_row_2_style . '">
                    <td>&nbsp;</td>
                    <td><input type="radio" id="order_form_product_layout_drop_down_selection" name="order_form_product_layout" value="drop-down selection" class="radio"' . $order_form_product_layout_drop_down_selection . ' /><label for="order_form_product_layout_drop_down_selection">Drop-Down Selection (short description)</label></td>
                </tr>
                <tr id="order_form_add_button_label_row" style="' . $order_form_add_button_label_row_style . '">
                    <td>Add Button Label:</td>
                    <td><input name="order_form_add_button_label" type="text" value="' . $order_form_properties['add_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="order_form_add_button_next_page_id_row" style="' . $order_form_add_button_next_page_id_row_style . '">
                    <td style="padding-left: 20px">Next Page:</td>
                    <td><select name="order_form_add_button_next_page_id"><option value="">-Select-</option>' . select_page($order_form_properties['add_button_next_page_id']) . '</select></td>
                </tr>
                <tr id="order_form_skip_button_label_row" style="' . $order_form_skip_button_label_row_style . '">
                    <td>Skip Button Label:</td>
                    <td><input name="order_form_skip_button_label" type="text" value="' . $order_form_properties['skip_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="order_form_skip_button_next_page_id_row" style="' . $order_form_skip_button_next_page_id_row_style . '">
                    <td style="padding-left: 20px">Next Page:</td>
                    <td><select name="order_form_skip_button_next_page_id"><option value="">-Select-</option>' . select_page($order_form_properties['skip_button_next_page_id']) . '</select></td>
                </tr>
                <tr id="shopping_cart_shopping_cart_label_row" style="' . $shopping_cart_shopping_cart_label_row_style . '">
                    <td>Shopping Cart Label:</td>
                    <td><input name="shopping_cart_shopping_cart_label" type="text" value="' . $shopping_cart_properties['shopping_cart_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="shopping_cart_quick_add_label_row" style="' . $shopping_cart_quick_add_label_row_style . '">
                    <td>Quick Add Label:</td>
                    <td><input name="shopping_cart_quick_add_label" type="text" value="' . $shopping_cart_properties['quick_add_label'] . '" maxlength="255" /></td>
                </tr>
                <tr id="shopping_cart_quick_add_product_group_id_row" style="' . $shopping_cart_quick_add_product_group_id_row_style . '">
                    <td>Quick Add Product Group:</td>
                    <td><select name="shopping_cart_quick_add_product_group_id"><option value="">-None-</option>' . get_product_group_options($shopping_cart_properties['quick_add_product_group_id']) . '</select></td>
                </tr>
                <tr id="shopping_cart_product_description_type_row" style="' . $shopping_cart_product_description_type_row_style . '">
                    <td>Product Description Type:</td>
                    <td><input type="radio" id="shopping_cart_product_description_type_full_description" name="shopping_cart_product_description_type" value="full_description" class="radio"' . $shopping_cart_product_description_type_full_description_checked . ' /><label for="shopping_cart_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="shopping_cart_product_description_type_short_description" name="shopping_cart_product_description_type" value="short_description" class="radio"' . $shopping_cart_product_description_type_short_description_checked . ' /><label for="shopping_cart_product_description_type_short_description">Short Description</label></td>
                </tr>
                <tr id="shopping_cart_special_offer_code_label_row" style="' . $shopping_cart_special_offer_code_label_row_style . '">
                    <td>Special Offer Code Label:</td>
                    <td><input name="shopping_cart_special_offer_code_label" type="text" value="' . $shopping_cart_properties['special_offer_code_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="shopping_cart_special_offer_code_message_row" style="' . $shopping_cart_special_offer_code_message_row_style . '">
                    <td>Special Offer Code Message:</td>
                    <td><input name="shopping_cart_special_offer_code_message" type="text" value="' . $shopping_cart_properties['special_offer_code_message'] . '" size="50" maxlength="255" /></td>
                </tr>
                <tr id="shopping_cart_update_button_label_row" style="' . $shopping_cart_update_button_label_row_style . '">
                    <td>Update Button Label:</td>
                    <td><input name="shopping_cart_update_button_label" type="text" value="' . $shopping_cart_properties['update_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="shopping_cart_checkout_button_label_row" style="' . $shopping_cart_checkout_button_label_row_style . '">
                    <td>Checkout Button Label:</td>
                    <td><input name="shopping_cart_checkout_button_label" type="text" value="' . $shopping_cart_properties['checkout_button_label'] . '" maxlength="50" /></td>
                </tr>';

            // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
            if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                $output_ecommerce_page_type_properties .=
                    '<tr id="shopping_cart_hook_code_row" style="' . $shopping_cart_hook_code_row_style . '">
                        <td>Hook Code:</td>
                        <td><textarea name="shopping_cart_hook_code" rows="5" cols="70">' . h($shopping_cart_properties['hook_code']) . '</textarea></td>
                    </tr>';
            }

            $output_ecommerce_page_type_properties .=
                '<tr id="shopping_cart_next_page_id_with_shipping_row" style="' . $shopping_cart_next_page_id_with_shipping_row_style . '">
                    <td>Next Page (with shipping):</td>
                    <td><select name="shopping_cart_next_page_id_with_shipping"><option value="">-Select Shipping Address &amp; Arrival or Express Order Page-</option>' . select_page($shopping_cart_properties['next_page_id_with_shipping'], array('shipping address and arrival', 'express order')) . '</select></td>
                </tr>
                <tr id="shopping_cart_next_page_id_without_shipping_row" style="' . $shopping_cart_next_page_id_without_shipping_row_style . '">
                    <td>Next Page (without shipping):</td>
                    <td><select name="shopping_cart_next_page_id_without_shipping"><option value="">-Select Billing Information or Express Order Page-</option>' . select_page($shopping_cart_properties['next_page_id_without_shipping'], array('billing information', 'express order')) . '</select></td>
                </tr>
                <tr id="shipping_address_and_arrival_address_type_row" style="' . $shipping_address_and_arrival_address_type_row_style . '">
                    <td><label for="shipping_address_and_arrival_address_type">Enable Address Type:</label></td>
                    <td><input id="shipping_address_and_arrival_address_type" name="shipping_address_and_arrival_address_type" type="checkbox" value="1"' . $shipping_address_and_arrival_address_type_checked . ' class="checkbox" onclick="show_or_hide_shipping_address_and_arrival_address_type()" /></td>
                </tr>
                <tr id="shipping_address_and_arrival_address_type_page_id_row" style="' . $shipping_address_and_arrival_address_type_page_id_row_style . '">
                    <td style="padding-left: 2em">Address Type Page:</td>
                    <td><select name="shipping_address_and_arrival_address_type_page_id"><option value=""></option>' . select_page($shipping_address_and_arrival_properties['address_type_page_id']) . '</select></td>
                </tr>
                <tr id="shipping_address_and_arrival_form_row" style="' . $shipping_address_and_arrival_form_row_style . '">
                    <td><label for="shipping_address_and_arrival_form">Enable Custom Shipping Form:</label></td>
                    <td><input id="shipping_address_and_arrival_form" name="shipping_address_and_arrival_form" type="checkbox" value="1"' . $shipping_address_and_arrival_form_checked . ' class="checkbox" onclick="show_or_hide_custom_shipping_form()" /><script type="text/javascript">var original_shipping_address_and_arrival_form = "' . $shipping_address_and_arrival_properties['form'] . '";</script><span id="shipping_address_and_arrival_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Shipping Form.)</span></td>
                </tr>
                <tr id="shipping_address_and_arrival_form_name_row" style="' . $shipping_address_and_arrival_form_name_row_style . '">
                    <td style="padding-left: 2em">Form Title for Display:</td>
                    <td><input id="shipping_address_and_arrival_form_name" name="shipping_address_and_arrival_form_name" type="text" value="' . h($shipping_address_and_arrival_properties['form_name']) . '" size="80" maxlength="100" /></td>
                </tr>
                <tr id="shipping_address_and_arrival_form_label_column_width_row" style="' . $shipping_address_and_arrival_form_label_column_width_row_style . '">
                    <td style="padding-left: 2em">Label Column Width:</td>
                    <td><input id="shipping_address_and_arrival_form_label_column_width" name="shipping_address_and_arrival_form_label_column_width" type="text" value="' . h($shipping_address_and_arrival_properties['form_label_column_width']) . '" size="3" maxlength="3" /> % (leave blank for auto)</td>
                </tr>
                <tr id="shipping_address_and_arrival_submit_button_label_row" style="' . $shipping_address_and_arrival_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="shipping_address_and_arrival_submit_button_label" type="text" value="' . $shipping_address_and_arrival_properties['submit_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="shipping_address_and_arrival_next_page_id_row" style="' . $shipping_address_and_arrival_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="shipping_address_and_arrival_next_page_id"><option value="">-Select Shipping Method Page-</option>' . select_page($shipping_address_and_arrival_properties['next_page_id'], 'shipping method') . '</select></td>
                </tr>
                <tr id="shipping_method_product_description_type_row" style="' . $shipping_method_product_description_type_row_style . '">
                    <td>Product Description Type:</td>
                    <td><input type="radio" id="shipping_method_product_description_type_full_description" name="shipping_method_product_description_type" value="full_description" class="radio"' . $shipping_method_product_description_type_full_description_checked . ' /><label for="shipping_method_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="shipping_method_product_description_type_short_description" name="shipping_method_product_description_type" value="short_description" class="radio"' . $shipping_method_product_description_type_short_description_checked . ' /><label for="shipping_method_product_description_type_short_description">Short Description</label></td>
                </tr>
                <tr id="shipping_method_submit_button_label_row" style="' . $shipping_method_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="shipping_method_submit_button_label" type="text" value="' . $shipping_method_properties['submit_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="shipping_method_next_page_id_row" style="' . $shipping_method_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="shipping_method_next_page_id"><option value="">-Select-</option>' . select_page($shipping_method_properties['next_page_id']) . '</select></td>
                </tr>
                <tr id="billing_information_custom_field_1_label_row" style="' . $billing_information_custom_field_1_label_row_style . '">
                    <td>Custom Field #1 Label:</td>
                    <td><input name="billing_information_custom_field_1_label" type="text" value="' . $billing_information_properties['custom_field_1_label'] . '" maxlength="255" /> <input type="checkbox" name="billing_information_custom_field_1_required" id="billing_information_custom_field_1_required" value="1"' . $billing_information_custom_field_1_required_checked . ' class="checkbox" /><label for="billing_information_custom_field_1_required"> Required</label></td>
                </tr>
                <tr id="billing_information_custom_field_2_label_row" style="' . $billing_information_custom_field_2_label_row_style . '">
                    <td>Custom Field #2 Label:</td>
                    <td><input name="billing_information_custom_field_2_label" type="text" value="' . $billing_information_properties['custom_field_2_label'] . '" maxlength="255" /> <input type="checkbox" name="billing_information_custom_field_2_required" id="billing_information_custom_field_2_required" value="1"' . $billing_information_custom_field_2_required_checked . ' class="checkbox" /><label for="billing_information_custom_field_2_required"> Required</label></td>
                </tr>
                <tr id="billing_information_po_number_row" style="' . $billing_information_po_number_row_style . '">
                    <td>Enable PO Number:</td>
                    <td><input type="checkbox" name="billing_information_po_number" value="1"' . $billing_information_po_number_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="billing_information_form_row" style="' . $billing_information_form_row_style . '">
                    <td><label for="billing_information_form">Enable Custom Billing Form:</label></td>
                    <td><input id="billing_information_form" name="billing_information_form" type="checkbox" value="1"' . $billing_information_form_checked . ' class="checkbox" onclick="show_or_hide_billing_information_custom_billing_form()" /><script type="text/javascript">var original_billing_information_form = "' . $billing_information_properties['form'] . '";</script><span id="billing_information_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Billing Form.)</span></td>
                </tr>
                <tr id="billing_information_form_name_row" style="' . $billing_information_form_name_row_style . '">
                    <td style="padding-left: 2em">Form Title for Display:</td>
                    <td><input id="billing_information_form_name" name="billing_information_form_name" type="text" value="' . h($billing_information_properties['form_name']) . '" size="80" maxlength="100" /></td>
                </tr>
                <tr id="billing_information_form_label_column_width_row" style="' . $billing_information_form_label_column_width_row_style . '">
                    <td style="padding-left: 2em">Label Column Width:</td>
                    <td><input id="billing_information_form_label_column_width" name="billing_information_form_label_column_width" type="text" value="' . h($billing_information_properties['form_label_column_width']) . '" size="3" maxlength="3" /> % (leave blank for auto)</td>
                </tr>
                <tr id="billing_information_submit_button_label_row" style="' . $billing_information_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="billing_information_submit_button_label" type="text" value="' . $billing_information_properties['submit_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="billing_information_next_page_id_row" style="' . $billing_information_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="billing_information_next_page_id"><option value="">-Select Order Preview or Express Order Page-</option>' . select_page($billing_information_properties['next_page_id'], 'order preview') . select_page($billing_information_properties['next_page_id'], 'express order') . '</select></td>
                </tr>
                <tr id="order_preview_product_description_type_row" style="' . $order_preview_product_description_type_row_style . '">
                    <td>Product Description Type:</td>
                    <td><input type="radio" id="order_preview_product_description_type_full_description" name="order_preview_product_description_type" value="full_description" class="radio"' . $order_preview_product_description_type_full_description_checked . ' /><label for="order_preview_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="order_preview_product_description_type_short_description" name="order_preview_product_description_type" value="short_description" class="radio"' . $order_preview_product_description_type_short_description_checked . ' /><label for="order_preview_product_description_type_short_description">Short Description</label></td>
                </tr>
                <tr id="order_preview_card_verification_number_page_id_row" style="' . $order_preview_card_verification_number_page_id_row_style . '">
                    <td>Card Verification Number Page:</td>
                    <td><select name="order_preview_card_verification_number_page_id"><option value="">-Select-</option>' . select_page($order_preview_properties['card_verification_number_page_id']) . '</select></td>
                </tr>
                ' . $output_order_preview_offline_payment_rows . '
                <tr id="order_preview_terms_page_id_row" style="' . $order_preview_terms_page_id_row_style . '">
                    <td>Terms Page:</td>
                    <td><select name="order_preview_terms_page_id"><option value="">-Select-</option>' . select_page($order_preview_properties['terms_page_id']) . '</select></td>
                </tr>
                <tr id="order_preview_submit_button_label_row" style="' . $order_preview_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="order_preview_submit_button_label" type="text" value="' . $order_preview_properties['submit_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="order_preview_auto_registration_row" style="' . $order_preview_auto_registration_row_style . '">
                    <td><label for="order_preview_auto_registration">Enable Auto-Registration:</label></td>
                    <td><input type="checkbox" id="order_preview_auto_registration" name="order_preview_auto_registration" value="1"' . $order_preview_auto_registration_checked . ' class="checkbox"></td>
                </tr>';

            // If hooks are enabled and the user is a designer or administrator then output hook rows for PHP code.
            if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                $output_ecommerce_page_type_properties .=
                    '<tr id="order_preview_pre_save_hook_code_row" style="' . $order_preview_pre_save_hook_code_row_style . '">
                        <td>Pre-Save Hook Code:</td>
                        <td><textarea name="order_preview_pre_save_hook_code" rows="5" cols="70">' . h($order_preview_properties['pre_save_hook_code']) . '</textarea></td>
                    </tr>
                    <tr id="order_preview_post_save_hook_code_row" style="' . $order_preview_post_save_hook_code_row_style . '">
                        <td>Post-Save Hook Code:</td>
                        <td><textarea name="order_preview_post_save_hook_code" rows="5" cols="70">' . h($order_preview_properties['post_save_hook_code']) . '</textarea></td>
                    </tr>';
            }

            $output_ecommerce_page_type_properties .=
                '<tr id="order_preview_order_receipt_email_row" style="' . $order_preview_order_receipt_email_row_style . '">
                    <td><label for="order_preview_order_receipt_email">E-mail Order Receipt:</label></td>
                    <td><input type="checkbox" id="order_preview_order_receipt_email" name="order_preview_order_receipt_email" value="1"' . $order_preview_order_receipt_email_checked . ' class="checkbox" onclick="show_or_hide_order_preview_order_receipt_email()" /></td>
                </tr>
                <tr id="order_preview_order_receipt_email_subject_row" style="' . $order_preview_order_receipt_email_subject_row_style . '">
                    <td style="padding-left: 2em">Subject:</td>
                    <td><input name="order_preview_order_receipt_email_subject" value="' . h($order_preview_properties['order_receipt_email_subject']) . '" type="text" size="80" maxlength="255" /></td>
                </tr>
                <tr id="order_preview_order_receipt_email_format_row" style="' . $order_preview_order_receipt_email_format_row_style . '">
                    <td style="padding-left: 2em">Format:</td>
                    <td><input type="radio" id="order_preview_order_receipt_email_format_plain_text" name="order_preview_order_receipt_email_format" value="plain_text" class="radio"' . $order_preview_order_receipt_email_format_plain_text_checked . ' onclick="show_or_hide_order_preview_order_receipt_email_format()" /><label for="order_preview_order_receipt_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="order_preview_order_receipt_email_format_html" name="order_preview_order_receipt_email_format" value="html" class="radio"' . $order_preview_order_receipt_email_format_html_checked . ' onclick="show_or_hide_order_preview_order_receipt_email_format()" /><label for="order_preview_order_receipt_email_format_html">HTML</label></td>
                </tr>
                <tr id="order_preview_order_receipt_email_header_row" style="' . $order_preview_order_receipt_email_header_row_style . '">
                    <td style="padding-left: 2em">Header:</td>
                    <td><textarea name="order_preview_order_receipt_email_header" rows="5" cols="70">' . h($order_preview_properties['order_receipt_email_header']) . '</textarea></td>
                </tr>
                <tr id="order_preview_order_receipt_email_footer_row" style="' . $order_preview_order_receipt_email_footer_row_style . '">
                    <td style="padding-left: 2em">Footer:</td>
                    <td><textarea name="order_preview_order_receipt_email_footer" rows="5" cols="70">' . h($order_preview_properties['order_receipt_email_footer']) . '</textarea></td>
                </tr>
                <tr id="order_preview_order_receipt_email_page_id_row" style="' . $order_preview_order_receipt_email_page_id_row_style . '">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="order_preview_order_receipt_email_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page($order_preview_properties['order_receipt_email_page_id'], 'order receipt') . '</select></td>
                </tr>
                <tr id="order_preview_next_page_id_row" style="' . $order_preview_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="order_preview_next_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page($order_preview_properties['next_page_id'], 'order receipt') . '</select></td>
                </tr>
                <tr id="order_receipt_product_description_type_row" style="' . $order_receipt_product_description_type_row_style . '">
                    <td>Product Description Type:</td>
                    <td><input type="radio" id="order_receipt_product_description_type_full_description" name="order_receipt_product_description_type" value="full_description" class="radio"' . $order_receipt_product_description_type_full_description_checked . ' /><label for="order_receipt_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="order_receipt_product_description_type_short_description" name="order_receipt_product_description_type" value="short_description" class="radio"' . $order_receipt_product_description_type_short_description_checked . ' /><label for="order_receipt_product_description_type_short_description">Short Description</label></td>
                </tr>';
        }
        
        if (FORMS == true) {
            $output_wysiwyg_editor_code = get_wysiwyg_editor_code(array('custom_form_confirmation_message', 'custom_form_return_message'), $activate_editors);

            // prepare to get folders that user has access to, in order to determine which form list views should be available to be selected for the form view directory page type
            $folders_that_user_has_access_to = array();
            
            // if user is a basic user, then get folders that user has access to
            if ($user['role'] == 3) {
                $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
            }
            
            // get all unarchived form list views for form view directory page type
            $query =
                "SELECT
                    page.page_id,
                    page.page_name,
                    page.page_folder as folder_id,
                    form_list_view_pages.custom_form_page_id
                FROM page
                LEFT JOIN form_list_view_pages ON
                    (page.page_id = form_list_view_pages.page_id)
                    AND (form_list_view_pages.collection = 'a')
                LEFT JOIN folder ON page.page_folder = folder.folder_id
                WHERE
                    (page.page_type = 'form list view')
                    AND (folder.folder_archived = '0')
                ORDER BY page.page_name ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $form_list_views = array();
            
            // loop through the form list views in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $form_list_views[] = $row;
            }
            
            // get selected form list views for this page
            $query =
                "SELECT
                    form_list_view_page_id,
                    form_list_view_name,
                    subject_form_field_id
                FROM form_view_directories_form_list_views_xref
                WHERE form_view_directory_page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // create selected form list views array
            $selected_form_list_views = array();
            
            // loop through the selected form list views in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $selected_form_list_views[$row['form_list_view_page_id']] = $row;
            }
            
            // get all custom form fields for form view directory page type
            $query =
                "SELECT
                    id,
                    name,
                    page_id
                FROM form_fields
                ORDER BY page_id ASC, sort_order ASC";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // create custom forms array that will hold the form fields
            $custom_forms = array();
            
            // loop through the form fields in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $custom_forms[$row['page_id']]['form_fields'][] = $row;
            }
            
            $output_form_view_directory_form_list_view_rows = '';
            
            // loop through all of the form list views in order to prepare rows for each one
            foreach ($form_list_views as $form_list_view) {
                // if user has edit access to the form list view and there are form fields for the form list view's custom form, then prepare to output a row for it
                if ((check_folder_access_in_array($form_list_view['folder_id'], $folders_that_user_has_access_to) == TRUE) && (isset($custom_forms[$form_list_view['custom_form_page_id']]['form_fields']) == TRUE)) {
                    // assume that form list view should not be checked until we find out otherwise
                    $form_list_view_checked = '';
                    
                    // assume that we will not show name and subject fields until we find out otherwise
                    $name_container_style = 'display: none';
                    $subject_form_field_id_container_style = 'display: none';
                    
                    // assume that the form list view name is blank until we find out otherwise
                    $output_form_list_view_name = '';
                    
                    // if this form list view is selected, then prepare checkbox to be checked and prepare to show name and subject fields
                    if (isset($selected_form_list_views[$form_list_view['page_id']]) == TRUE) {
                        $form_list_view_checked = ' checked="checked"';
                        $name_container_style = '';
                        $subject_form_field_id_container_style = '';
                        $output_form_list_view_name = h($selected_form_list_views[$form_list_view['page_id']]['form_list_view_name']);
                    }
                    
                    $output_form_view_directory_subject_field_options = '';
                    
                    // loop through the fields for this form list view's custom form, in order to prepare to output subject field options
                    foreach ($custom_forms[$form_list_view['custom_form_page_id']]['form_fields'] as $form_field) {
                        // assume that this field should not be selected, until we find out otherwise
                        $selected = '';
                        
                        // if this form list view is selected and this field is selected, then prepare to select it
                        if ((isset($selected_form_list_views[$form_list_view['page_id']]) == TRUE) && ($form_field['id'] == $selected_form_list_views[$form_list_view['page_id']]['subject_form_field_id'])) {
                            $selected = ' selected="selected"';
                        }
                        
                        $output_form_view_directory_subject_field_options .= '<option value="' . $form_field['id'] . '"' . $selected . '>' . h($form_field['name']) . '</option>';
                    }
                    
                    $output_form_view_directory_form_list_view_rows .=
                        '<tr>
                            <td><input type="checkbox" name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '" id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '" value="1"' . $form_list_view_checked . ' class="checkbox" onclick="show_or_hide_form_view_directory_form_list_view(' . $form_list_view['page_id'] . ')" /><label for="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '"> ' . h($form_list_view['page_name']) . '</label></td>
                            <td><div id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name_container" style="' . $name_container_style . '">Name: <input name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name" value="' . $output_form_list_view_name . '" type="text" size="30" maxlength="100" /></div></td>
                            <td><div id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id_container" style="' . $subject_form_field_id_container_style . '">Subject Field: <select name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id"><option value=""></option>' . $output_form_view_directory_subject_field_options . '</select></div></td>
                        </tr>';
                }
            }
            
            $output_forms_page_type_properties =
                '<tr id="custom_form_form_name_row" style="' . $custom_form_form_name_row_style . '">
                    <td>Form Name:</td>
                    <td><input name="custom_form_form_name" type="text" value="' . h($custom_form_properties['form_name']) . '" size="30" maxlength="100" /></td>
                </tr>
                <tr id="custom_form_enabled_row" style="' . $custom_form_enabled_row_style . '">
                    <td>Enable Form:</td>
                    <td><input type="checkbox" name="custom_form_enabled" value="1"' . $custom_form_enabled_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="custom_form_quiz_row" style="' . $custom_form_quiz_row_style . '">
                    <td>Enable Quiz:</td>
                    <td><input type="checkbox" id="custom_form_quiz" name="custom_form_quiz" value="1"' . $custom_form_quiz_checked . ' class="checkbox" onclick="show_or_hide_quiz()" /></td>
                </tr>
                <tr id="custom_form_quiz_pass_percentage_row" style="' . $custom_form_quiz_pass_percentage_row_style . '">
                    <td style="padding-left: 2em">Quiz Pass Percentage:</td>
                    <td><input name="custom_form_quiz_pass_percentage" type="text" value="' . $custom_form_properties['quiz_pass_percentage'] . '" size="3" maxlength="3" /> %</td>
                </tr>
                <tr id="custom_form_label_column_width_row" style="' . $custom_form_label_column_width_row_style . '">
                    <td>Label Column Width:</td>
                    <td><input name="custom_form_label_column_width" type="text" value="' . h($custom_form_properties['label_column_width']) . '" size="3" maxlength="3" /> % (leave blank for auto)</td>
                </tr>
                <tr id="custom_form_watcher_page_id_row" style="' . $custom_form_watcher_page_id_row_style . '">
                    <td>Enable Watcher Option:</td>
                    <td><select name="custom_form_watcher_page_id"><option value="">-Select Form Item View Page-</option>' . select_page($custom_form_properties['watcher_page_id'], 'form item view') . '</select></td>
                </tr>
                <tr id="custom_form_save_row" style="' . $custom_form_save_row_style . '">
                    <td><label for="custom_form_save">Enable Save-for-Later:</label></td>
                    <td><input type="checkbox" id="custom_form_save" name="custom_form_save" value="1"' . $custom_form_save_checked . ' class="checkbox"></td>
                </tr>
                <tr id="custom_form_submit_button_label_row" style="' . $custom_form_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="custom_form_submit_button_label" type="text" value="' . $custom_form_properties['submit_button_label'] . '" size="30" maxlength="50" /></td>
                </tr>
                <tr id="custom_form_auto_registration_row" style="' . $custom_form_auto_registration_row_style . '">
                    <td><label for="custom_form_auto_registration">Enable Auto-Registration:</label></td>
                    <td><input type="checkbox" id="custom_form_auto_registration" name="custom_form_auto_registration" value="1"' . $custom_form_auto_registration_checked . ' class="checkbox"></td>
                </tr>';

            // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
            if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                $output_forms_page_type_properties .=
                    '<tr id="custom_form_hook_code_row" style="' . $custom_form_hook_code_row_style . '">
                        <td>Hook Code:</td>
                        <td><textarea name="custom_form_hook_code" rows="5" cols="70">' . h($custom_form_properties['hook_code']) . '</textarea></td>
                    </tr>';
            }

            // Get MySQL version so we know if viewer filter feature is supported.
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

            $output_viewer_filter_warning = '';

            // If mysql version is old then output warning about viewer filter feature not being supported.
            if ($mysql_version_new == false) {
                $output_viewer_filter_warning = ' <span style="color: red">Sorry, not supported with your MySQL version.</span>';
            }

            $output_forms_page_type_properties .=
                '<tr id="custom_form_submitter_email_row" style="' . $custom_form_submitter_email_row_style . '">
                    <td><label for="custom_form_submitter_email">E-mail Submitter:</label></td>
                    <td><input type="checkbox" id="custom_form_submitter_email" name="custom_form_submitter_email" value="1"' . $custom_form_submitter_email_checked . ' class="checkbox" onclick="show_or_hide_custom_form_submitter_email()" /></td>
                </tr>
                <tr id="custom_form_submitter_email_from_email_address_row" style="' . $custom_form_submitter_email_from_email_address_row_style . '">
                    <td style="padding-left: 2em">From E-mail Address:</td>
                    <td><input name="custom_form_submitter_email_from_email_address" type="text" value="' . $custom_form_properties['submitter_email_from_email_address'] . '" size="30" maxlength="100" /></td>
                </tr>
                <tr id="custom_form_submitter_email_subject_row" style="' . $custom_form_submitter_email_subject_row_style . '">
                    <td style="padding-left: 2em">Subject:</td>
                    <td><input name="custom_form_submitter_email_subject" type="text" value="' . h($custom_form_properties['submitter_email_subject']) . '" size="80" maxlength="255" /></td>
                </tr>
                <tr id="custom_form_submitter_email_format_row" style="' . $custom_form_submitter_email_format_row_style . '">
                    <td style="padding-left: 2em">Format:</td>
                    <td><input type="radio" id="custom_form_submitter_email_format_plain_text" name="custom_form_submitter_email_format" value="plain_text" class="radio"' . $custom_form_submitter_email_format_plain_text_checked . ' onclick="show_or_hide_custom_form_submitter_email_format()" /><label for="custom_form_submitter_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="custom_form_submitter_email_format_html" name="custom_form_submitter_email_format" value="html" class="radio"' . $custom_form_submitter_email_format_html_checked . ' onclick="show_or_hide_custom_form_submitter_email_format()" /><label for="custom_form_submitter_email_format_html">HTML</label></td>
                </tr>
                <tr id="custom_form_submitter_email_body_row" style="' . $custom_form_submitter_email_body_row_style . '">
                    <td style="padding-left: 2em">Body:</td>
                    <td><textarea name="custom_form_submitter_email_body" rows="10" cols="70">' . h($custom_form_properties['submitter_email_body']) . '</textarea></td>
                </tr>
                <tr id="custom_form_submitter_email_page_id_row" style="' . $custom_form_submitter_email_page_id_row_style . '">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="custom_form_submitter_email_page_id"><option value="">-Select-</option>' . select_page($custom_form_properties['submitter_email_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_administrator_email_row" style="' . $custom_form_administrator_email_row_style . '">
                    <td><label for="custom_form_administrator_email">E-mail Administrator:</label></td>
                    <td><input type="checkbox" id="custom_form_administrator_email" name="custom_form_administrator_email" value="1"' . $custom_form_administrator_email_checked . ' class="checkbox" onclick="show_or_hide_custom_form_administrator_email()" /></td>
                </tr>
                <tr id="custom_form_administrator_email_to_email_address_row" style="' . $custom_form_administrator_email_to_email_address_row_style . '">
                    <td style="padding-left: 2em">To E-mail Address:</td>
                    <td><input name="custom_form_administrator_email_to_email_address" type="text" value="' . $custom_form_properties['administrator_email_to_email_address'] . '" size="30" maxlength="100" /></td>
                </tr>
                <tr id="custom_form_administrator_email_bcc_email_address_row" style="' . $custom_form_administrator_email_bcc_email_address_row_style . '">
                    <td style="padding-left: 2em">BCC E-mail Address:</td>
                    <td><input name="custom_form_administrator_email_bcc_email_address" type="text" value="' . $custom_form_properties['administrator_email_bcc_email_address'] . '" size="30" maxlength="100" /></td>
                </tr>
                <tr id="custom_form_administrator_email_subject_row" style="' . $custom_form_administrator_email_subject_row_style . '">
                    <td style="padding-left: 2em">Subject:</td>
                    <td><input name="custom_form_administrator_email_subject" type="text" value="' . h($custom_form_properties['administrator_email_subject']) . '" size="80" maxlength="255" /></td>
                </tr>
                <tr id="custom_form_administrator_email_format_row" style="' . $custom_form_administrator_email_format_row_style . '">
                    <td style="padding-left: 2em">Format:</td>
                    <td><input type="radio" id="custom_form_administrator_email_format_plain_text" name="custom_form_administrator_email_format" value="plain_text" class="radio"' . $custom_form_administrator_email_format_plain_text_checked . ' onclick="show_or_hide_custom_form_administrator_email_format()" /><label for="custom_form_administrator_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="custom_form_administrator_email_format_html" name="custom_form_administrator_email_format" value="html" class="radio"' . $custom_form_administrator_email_format_html_checked . ' onclick="show_or_hide_custom_form_administrator_email_format()" /><label for="custom_form_administrator_email_format_html">HTML</label></td>
                </tr>
                <tr id="custom_form_administrator_email_body_row" style="' . $custom_form_administrator_email_body_row_style . '">
                    <td style="padding-left: 2em">Body:</td>
                    <td><textarea name="custom_form_administrator_email_body" rows="10" cols="70">' . h($custom_form_properties['administrator_email_body']) . '</textarea></td>
                </tr>
                <tr id="custom_form_administrator_email_page_id_row" style="' . $custom_form_administrator_email_page_id_row_style . '">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="custom_form_administrator_email_page_id"><option value="">-Select-</option>' . select_page($custom_form_properties['administrator_email_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_contact_group_id_row" style="' . $custom_form_contact_group_id_row_style . '">
                    <td>Add to Contact Group:</td>
                    <td><select name="custom_form_contact_group_id"><option value="">-None-</option>' . select_contact_group($custom_form_properties['contact_group_id'], $user) . '</select></td>
                </tr>
                <tr id="custom_form_membership_row" style="' . $custom_form_membership_row_style . '">
                    <td><label for="custom_form_membership">Grant Membership Trial:</label></td>
                    <td><input type="checkbox" id="custom_form_membership" name="custom_form_membership" value="1"' . $custom_form_membership_checked . ' class="checkbox" onclick="show_or_hide_custom_form_membership()" /></td>
                </tr>
                <tr id="custom_form_membership_days_row" style="' . $custom_form_membership_days_row_style . '">
                    <td style="padding-left: 2em">Trial Length:</td>
                    <td><input name="custom_form_membership_days" type="text" value="' . h($custom_form_properties['membership_days']) . '" size="3" maxlength="9" /> day(s)</td>
                </tr>
                <tr id="custom_form_membership_start_page_id_row" style="' . $custom_form_membership_start_page_id_row_style . '">
                    <td style="padding-left: 2em">Set Member\'s Start Page to:</td>
                    <td><select name="custom_form_membership_start_page_id"><option value=""></option>' . select_page($custom_form_properties['membership_start_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_private_row" style="' . $custom_form_private_row_style . '">
                    <td><label for="custom_form_private">Grant Private Access:</label></td>
                    <td><input type="checkbox" id="custom_form_private" name="custom_form_private" value="1"' . $custom_form_private_checked . ' class="checkbox" onclick="toggle_custom_form_private()" /></td>
                </tr>
                <tr id="custom_form_private_folder_id_row" style="' . $custom_form_private_folder_id_row_style . '">
                    <td style="padding-left: 2em">Set "View" Access to Folder:</td>
                    <td><select name="custom_form_private_folder_id"><option value=""></option>' . select_folder($custom_form_properties['private_folder_id'], 0, 0, 0, array(), array(), 'private') . '</select></td>
                </tr>
                <tr id="custom_form_private_days_row" style="' . $custom_form_private_days_row_style . '">
                    <td style="padding-left: 2em">Length:</td>
                    <td><input name="custom_form_private_days" type="text" value="' . h($custom_form_properties['private_days']) . '" size="3" maxlength="9" /> day(s) (leave blank for no expiration)</td>
                </tr>
                <tr id="custom_form_private_start_page_id_row" style="' . $custom_form_private_days_row_style . '">
                    <td style="padding-left: 2em">Set User\'s Start Page to:</td>
                    <td><select name="custom_form_private_start_page_id"><option value=""></option>' . select_page($custom_form_properties['private_start_page_id']) . '</select></td>
                </tr>';

            // If commerce is enabled, then output grant offer rows.
            if (ECOMMERCE) {
                $output_forms_page_type_properties .=
                    '<tr id="custom_form_offer_row" style="' . $custom_form_private_row_style . '">
                        <td><label for="custom_form_offer">Grant Offer:</label></td>
                        <td><input type="checkbox" id="custom_form_offer" name="custom_form_offer" value="1"' . $custom_form_offer_checked . ' class="checkbox" onclick="toggle_custom_form_offer()"></td>
                    </tr>
                    <tr id="custom_form_offer_id_row" style="' . $custom_form_offer_id_row_style . '">
                        <td style="padding-left: 2em"><label for="custom_form_offer_id">Offer:</label></td>
                        <td><select name="custom_form_offer_id"><option value=""></option>' . select_offer($custom_form_properties['offer_id']) . '</select></td>
                    </tr>
                    <tr id="custom_form_offer_days_row" style="' . $custom_form_offer_days_row_style . '">
                        <td style="padding-left: 2em"><label for="custom_form_offer_days">Validity Length:</label></td>
                        <td><input name="custom_form_offer_days" type="text" value="' . h($custom_form_properties['offer_days']) . '" size="3" maxlength="9" /> day(s) (leave blank for no expiration)</td>
                    </tr>
                    <tr id="custom_form_offer_eligibility_row" style="' . $custom_form_offer_eligibility_row_style . '">
                        <td style="padding-left: 2em"><label for="custom_form_offer_eligibility">Eligibility:</label></td>
                        <td><select name="custom_form_offer_eligibility"><option value="everyone"' . $custom_form_offer_eligibility_everyone . '>Everyone</option><option value="new_contacts"' . $custom_form_offer_eligibility_new_contacts . '>New Contacts</option><option value="existing_contacts"' . $custom_form_offer_eligibility_existing_contacts . '>Existing Contacts</option></select></td>
                    </tr>';
            }

            $output_forms_page_type_properties .=
                '<tr id="custom_form_confirmation_type_row" style="' . $custom_form_confirmation_type_row_style . '">
                    <td>Confirmation Type:</td>
                    <td><input type="radio" id="custom_form_confirmation_type_message" name="custom_form_confirmation_type" value="message" class="radio"' . $custom_form_confirmation_type_message_checked . ' onclick="show_or_hide_custom_form_confirmation_type()" /><label for="custom_form_confirmation_type_message">Message</label> &nbsp;<input type="radio" id="custom_form_confirmation_type_page" name="custom_form_confirmation_type" value="page" class="radio"' . $custom_form_confirmation_type_page_checked . ' onclick="show_or_hide_custom_form_confirmation_type()" /><label for="custom_form_confirmation_type_page">Next Page</label></td>
                </tr>
                <tr id="custom_form_confirmation_message_row" style="' . $custom_form_confirmation_message_row_style . '">
                    <td style="padding-left: 2em">Message:</td>
                    <td><textarea id="custom_form_confirmation_message" name="custom_form_confirmation_message" rows="15" cols="80">' . h(prepare_rich_text_editor_content_for_output($custom_form_properties['confirmation_message'])) . '</textarea></td>
                </tr>
                <tr id="custom_form_confirmation_page_id_row" style="' . $custom_form_confirmation_page_id_row_style . '">
                    <td style="padding-left: 2em">Next Page:</td>
                    <td><select name="custom_form_confirmation_page_id"><option value=""></option>' . select_page($custom_form_properties['confirmation_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_confirmation_alternative_page_row" style="' . $custom_form_confirmation_alternative_page_row_style . '">
                    <td style="padding-left: 2em"><label for="custom_form_confirmation_alternative_page">Alternative Next Page:</label></td>
                    <td><input type="checkbox" id="custom_form_confirmation_alternative_page" name="custom_form_confirmation_alternative_page" value="1"' . $custom_form_confirmation_alternative_page_checked . ' class="checkbox" onclick="show_or_hide_custom_form_confirmation_alternative_page()" /></td>
                </tr>
                <tr id="custom_form_confirmation_alternative_page_contact_group_id_row" style="' . $custom_form_confirmation_alternative_page_contact_group_id_row_style . '">
                    <td style="padding-left: 4em">If Contact Group:</td>
                    <td><select name="custom_form_confirmation_alternative_page_contact_group_id"><option value=""></option>' . select_contact_group($custom_form_properties['confirmation_alternative_page_contact_group_id'], $user) . '</select></td>
                </tr>
                <tr id="custom_form_confirmation_alternative_page_id_row" style="' . $custom_form_confirmation_alternative_page_id_row_style . '">
                    <td style="padding-left: 4em">Then Go to Page:</td>
                    <td><select name="custom_form_confirmation_alternative_page_id"><option value=""></option>' . select_page($custom_form_properties['confirmation_alternative_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_return_type_row" style="' . $custom_form_return_type_row_style . '">
                    <td>If User has already submitted<br />form in the past, then show:</td>
                    <td>
                        <input type="radio" id="custom_form_return_type_custom_form" name="custom_form_return_type" value="custom_form" class="radio"' . $custom_form_return_type_custom_form_checked . ' onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_custom_form">Custom Form</label><br />
                        <input type="radio" id="custom_form_return_type_message" name="custom_form_return_type" value="message" class="radio"' . $custom_form_return_type_message_checked . ' onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_message">Message</label><br />
                        <input type="radio" id="custom_form_return_type_page" name="custom_form_return_type" value="page" class="radio"' . $custom_form_return_type_page_checked . ' onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_page">Page</label>
                    </td>
                </tr>
                <tr id="custom_form_return_message_row" style="' . $custom_form_return_message_row_style . '">
                    <td style="padding-left: 2em">Message:</td>
                    <td><textarea id="custom_form_return_message" name="custom_form_return_message" rows="15" cols="80">' . h(prepare_rich_text_editor_content_for_output($custom_form_properties['return_message'])) . '</textarea></td>
                </tr>
                <tr id="custom_form_return_page_id_row" style="' . $custom_form_return_page_id_row_style . '">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="custom_form_return_page_id"><option value=""></option>' . select_page($custom_form_properties['return_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_return_alternative_page_row" style="' . $custom_form_return_alternative_page_row_style . '">
                    <td style="padding-left: 2em"><label for="custom_form_return_alternative_page">Alternative Page:</label></td>
                    <td><input type="checkbox" id="custom_form_return_alternative_page" name="custom_form_return_alternative_page" value="1"' . $custom_form_return_alternative_page_checked . ' class="checkbox" onclick="show_or_hide_custom_form_return_alternative_page()" /></td>
                </tr>
                <tr id="custom_form_return_alternative_page_contact_group_id_row" style="' . $custom_form_return_alternative_page_contact_group_id_row_style . '">
                    <td style="padding-left: 4em">If Contact Group:</td>
                    <td><select name="custom_form_return_alternative_page_contact_group_id"><option value=""></option>' . select_contact_group($custom_form_properties['return_alternative_page_contact_group_id'], $user) . '</select></td>
                </tr>
                <tr id="custom_form_return_alternative_page_id_row" style="' . $custom_form_return_alternative_page_id_row_style . '">
                    <td style="padding-left: 4em">Then Go to Page:</td>
                    <td><select name="custom_form_return_alternative_page_id"><option value=""></option>' . select_page($custom_form_properties['return_alternative_page_id']) . '</select></td>
                </tr>
                <tr id="custom_form_pretty_urls_row" style="' . $custom_form_pretty_urls_row_style . '">
                    <td><label for="custom_form_pretty_urls">Enable Pretty URLs:</label></td>
                    <td><input type="checkbox" id="custom_form_pretty_urls" name="custom_form_pretty_urls" value="1"' . $custom_form_pretty_urls_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="custom_form_confirmation_continue_button_label_row" style="' . $custom_form_confirmation_continue_button_label_row_style . '">
                    <td>Continue Button Label:</td>
                    <td><input name="custom_form_confirmation_continue_button_label" type="text" value="' . $custom_form_confirmation_properties['continue_button_label'] . '" size="30" maxlength="50" /></td>
                </tr>
                <tr id="custom_form_confirmation_next_page_id_row" style="' . $custom_form_confirmation_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="custom_form_confirmation_next_page_id"><option value="">-Select-</option>' . select_page($custom_form_confirmation_properties['next_page_id']) . '</select></td>
                </tr>
                <tr id="form_list_view_custom_form_page_id_row" style="' . $form_list_view_custom_form_page_id_row_style . '">
                    <td>Custom Form:</td>
                    <td><select name="form_list_view_custom_form_page_id"><option value=""></option>' . select_custom_form($form_list_view_properties['custom_form_page_id'], $user) . '</select></td>
                </tr>
                <tr id="form_list_view_form_item_view_page_id_row" style="' . $form_list_view_form_item_view_page_id_row_style . '">
                    <td>Form Item View:</td>
                    <td><select name="form_list_view_form_item_view_page_id"><option value=""></option>' . select_page($form_list_view_properties['form_item_view_page_id'], 'form item view') . '</select></td>
                </tr>
                <tr id="form_list_view_viewer_filter_row" style="' . $form_list_view_viewer_filter_row_style . '">
                    <td><label for="form_list_view_viewer_filter">Enable Viewer Filter:</label></td>
                    <td><input type="checkbox" id="form_list_view_viewer_filter" name="form_list_view_viewer_filter" value="1"' . $form_list_view_viewer_filter_checked . ' class="checkbox" onclick="show_or_hide_form_list_view_viewer_filter()" />' . $output_viewer_filter_warning . '</td>
                </tr>
                <tr id="form_list_view_viewer_filter_submitter_row" style="' . $form_list_view_viewer_filter_submitter_row_style . '">
                    <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_submitter">Include Forms from Submitter:</label></td>
                    <td><input type="checkbox" id="form_list_view_viewer_filter_submitter" name="form_list_view_viewer_filter_submitter" value="1"' . $form_list_view_viewer_filter_submitter_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="form_list_view_viewer_filter_watcher_row" style="' . $form_list_view_viewer_filter_watcher_row_style . '">
                    <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_watcher">Include Forms for Watchers:</label></td>
                    <td><input type="checkbox" id="form_list_view_viewer_filter_watcher" name="form_list_view_viewer_filter_watcher" value="1"' . $form_list_view_viewer_filter_watcher_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="form_list_view_viewer_filter_editor_row" style="' . $form_list_view_viewer_filter_editor_row_style . '">
                    <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_editor">Include Forms for Form Editors:</label></td>
                    <td><input type="checkbox" id="form_list_view_viewer_filter_editor" name="form_list_view_viewer_filter_editor" value="1"' . $form_list_view_viewer_filter_editor_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="form_item_view_custom_form_page_id_row" style="' . $form_item_view_custom_form_page_id_row_style . '">
                    <td>Custom Form:</td>
                    <td><select name="form_item_view_custom_form_page_id"><option value="">-Select-</option>' . select_custom_form($form_item_view_properties['custom_form_page_id'], $user) . '</select></td>
                </tr>
                <tr id="form_item_view_submitter_security_row" style="' . $form_item_view_submitter_security_row_style . '">
                    <td><label for="form_item_view_submitter_security">Allow only submitter and watchers<br />to view his/her submitted form(s):</label></td>
                    <td><input type="checkbox" id="form_item_view_submitter_security" name="form_item_view_submitter_security" value="1" ' . $form_item_view_submitter_security_checked . ' class="checkbox" /></td>
                </tr>
                <tr id="form_item_view_submitted_form_editable_by_registered_user_row" style="' . $form_item_view_submitted_form_editable_by_registered_user_row_style . '">
                    <td><label for="form_item_view_submitted_form_editable_by_registered_user">Allow any registered user<br />to edit submitted form(s):</label></td>
                    <td><input type="checkbox" id="form_item_view_submitted_form_editable_by_registered_user" name="form_item_view_submitted_form_editable_by_registered_user" value="1" ' . $form_item_view_submitted_form_editable_by_registered_user_checked . ' onclick="show_or_hide_form_item_view_editor()" class="checkbox" /></td>
                </tr>
                <tr id="form_item_view_submitted_form_editable_by_submitter_row" style="' . $form_item_view_submitted_form_editable_by_submitter_row_style . '">
                    <td><label for="form_item_view_submitted_form_editable_by_submitter">Allow submitter to edit<br />his/her submitted form(s):</label></td>
                    <td><input type="checkbox" id="form_item_view_submitted_form_editable_by_submitter" name="form_item_view_submitted_form_editable_by_submitter" value="1" ' . $form_item_view_submitted_form_editable_by_submitter_checked . ' class="checkbox" /></td>
                </tr>';

            // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
            if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                $output_forms_page_type_properties .=
                    '<tr id="form_item_view_hook_code_row" style="' . $form_item_view_hook_code_row_style . '">
                        <td>Hook Code:</td>
                        <td><textarea name="form_item_view_hook_code" rows="5" cols="70">' . h($form_item_view_properties['hook_code']) . '</textarea></td>
                    </tr>';
            }

            $output_forms_page_type_properties .=
                '<tr id="form_view_directory_form_list_views_row" style="' . $form_view_directory_form_list_views_row_style . '">
                    <td style="vertical-align: top">Form List Views:</td>
                    <td>
                        <table>
                            ' . $output_form_view_directory_form_list_view_rows . '
                        </table>
                    </td>
                </tr>
                <tr id="form_view_directory_summary_row" style="' . $form_view_directory_summary_row_style . '">
                    <td><label for="form_view_directory_summary">Display Summary:</label></td>
                    <td><input type="checkbox" id="form_view_directory_summary" name="form_view_directory_summary" value="1"' . $form_view_directory_summary_checked . ' class="checkbox" onclick="show_or_hide_form_view_directory_summary()" /></td>
                </tr>
                <tr id="form_view_directory_summary_days_row" style="' . $form_view_directory_summary_days_row_style . '">
                    <td style="padding-left: 2em">Date Range:</td>
                    <td>last <input name="form_view_directory_summary_days" type="text" value="' . $form_view_directory_summary_days . '" maxlength="4" size="3" /> day(s)</td>
                </tr>
                <tr id="form_view_directory_summary_maximum_number_of_results_row" style="' . $form_view_directory_summary_maximum_number_of_results_row_style . '">
                    <td style="padding-left: 2em">Maximum Number of Results:</td>
                    <td><input name="form_view_directory_summary_maximum_number_of_results" type="text" value="' . $form_view_directory_summary_maximum_number_of_results . '" maxlength="3" size="2" /></td>
                </tr>
                <tr id="form_view_directory_form_list_view_heading_row" style="' . $form_view_directory_form_list_view_heading_row_style . '">
                    <td>Form List View Heading:</td>
                    <td><input name="form_view_directory_form_list_view_heading" type="text" value="' . h($form_view_directory_form_list_view_heading) . '" maxlength="50" /></td>
                </tr>
                <tr id="form_view_directory_subject_heading_row" style="' . $form_view_directory_subject_heading_row_style . '">
                    <td>Subject Heading:</td>
                    <td><input name="form_view_directory_subject_heading" type="text" value="' . h($form_view_directory_subject_heading) . '" maxlength="50" /></td>
                </tr>
                <tr id="form_view_directory_number_of_submitted_forms_heading_row" style="' . $form_view_directory_number_of_submitted_forms_heading_row_style . '">
                    <td>Number of Submitted Forms Heading:</td>
                    <td><input name="form_view_directory_number_of_submitted_forms_heading" type="text" value="' . h($form_view_directory_number_of_submitted_forms_heading) . '" maxlength="50" /></td>
                </tr>';
        }
        
        if (CALENDARS == true) {
            // get calendars so user can select calendars for calendar view and calendar event view
            $query =
                "SELECT
                   id,
                   name
                FROM calendars
                ORDER BY name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $calendars = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $calendars[] = $row;
            }
            
            $calendar_view_calendar_check_boxes = '';
            $calendar_event_view_calendars_check_boxes = '';
            
            // loop through all calendars in order to check if user has access to calendar and if calendar should be checked
            foreach ($calendars as $calendar) {
                // if user has access to calendar, then continue
                if (validate_calendar_access($calendar['id']) == true) {
                    // check if calendar should be checked for calendar view
                    $query =
                        "SELECT calendar_id
                        FROM calendar_views_calendars_xref
                        WHERE
                            (calendar_id = '" . escape($calendar['id']) . "')
                            AND (page_id = '" . escape($page_id) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $checked = '';
                    
                    // if calendar should be checked, then prepare checkbox to be checked
                    if (mysqli_num_rows($result) > 0) {
                        $checked = ' checked="checked"';
                    }
                    
                    $calendar_view_calendar_check_boxes .= '<input type="checkbox" name="calendar_view_calendar_' . $calendar['id'] . '" id="calendar_view_calendar_' . $calendar['id'] . '" value="1"' . $checked . ' class="checkbox" /><label for="calendar_view_calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
                    
                    // check if calendar should be checked for calendar event view
                    $query =
                        "SELECT calendar_id
                        FROM calendar_event_views_calendars_xref
                        WHERE
                            (calendar_id = '" . escape($calendar['id']) . "')
                            AND (page_id = '" . escape($page_id) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $checked = '';
                    
                    // if calendar should be checked, then prepare checkbox to be checked
                    if (mysqli_num_rows($result) > 0) {
                        $checked = ' checked="checked"';
                    }
                    
                    $calendar_event_view_calendar_check_boxes .= '<input type="checkbox" name="calendar_event_view_calendar_' . $calendar['id'] . '" id="calendar_event_view_calendar_' . $calendar['id'] . '" value="1"' . $checked . ' class="checkbox" /><label for="calendar_event_view_calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
                }
            }
            
            // if notes is enabled for calendar event view prepare to check checkbox
            if ($calendar_event_view_properties['notes'] == 1) {
                $calendar_event_view_notes_checked = ' checked="checked"';
            } else {
                $calendar_event_view_notes_checked = '';
            }
            
            $output_calendars_page_type_properties =
                '<tr id="calendar_view_calendars_row" style="' . $calendar_view_calendars_row_style . '">
                    <td style="vertical-align: top">Calendars:</td>
                    <td>
                        ' . $calendar_view_calendar_check_boxes . '
                    </td>
                </tr>
                <tr id="calendar_view_default_view_row" style="' . $calendar_view_default_view_row_style . '">
                    <td>View:</td>
                    <td><select id="calendar_view_default_view" name="calendar_view_default_view" onchange="show_or_hide_calendar_view_number_of_upcoming_events()"><option value="monthly"' . $calendar_view_default_view_monthly . '>Monthly</option><option value="weekly"' . $calendar_view_default_view_weekly . '>Weekly</option><option value="upcoming"' . $calendar_view_default_view_upcoming . '>Upcoming</option></select></td>
                </tr>
                <tr id="calendar_view_number_of_upcoming_events_row" style="' . $calendar_view_number_of_upcoming_events_row_style . '">
                    <td style="padding-left: 2em">Number of Events:</td>
                    <td><input name="calendar_view_number_of_upcoming_events" type="text" value="' . h($calendar_view_number_of_upcoming_events_value) . '" maxlength="2" size="3" /></td>
                </tr>
                <tr id="calendar_view_calendar_event_view_page_id_row" style="' . $calendar_view_calendar_event_view_page_id_row_style . '">
                    <td>Calendar Event View:</td>
                    <td><select name="calendar_view_calendar_event_view_page_id"><option value="">-Select-</option>' . select_page($calendar_view_properties['calendar_event_view_page_id'], 'calendar event view') . '</select></td>
                </tr>
                <tr id="calendar_event_view_calendars_row" style="' . $calendar_event_view_calendars_row_style . '">
                    <td style="vertical-align: top">Calendars:</td>
                    <td>
                        ' . $calendar_event_view_calendar_check_boxes . '
                    </td>
                </tr>
                <tr id="calendar_event_view_notes_row" style="' . $calendar_event_view_notes_row_style . '">
                    <td style="vertical-align: top">Show Notes:</td>
                    <td>
                        <input type="checkbox" name="calendar_event_view_notes" id="calendar_event_view_notes" value="1"' . $calendar_event_view_notes_checked . ' class="checkbox" />
                    </td>
                </tr>
                <tr id="calendar_event_view_back_button_label_row" style="' . $calendar_event_view_back_button_label_row_style . '">
                    <td>Back Button Label:</td>
                    <td><input name="calendar_event_view_back_button_label" type="text" value="' . $calendar_event_view_properties['back_button_label'] . '" size="30" maxlength="50" /></td>
                </tr>';
        }
        
        if (AFFILIATE_PROGRAM == true) {
            $output_affiliate_page_type_properties =
                '<tr id="affiliate_sign_up_form_terms_page_id_row" style="' . $affiliate_sign_up_form_terms_page_id_row_style . '">
                    <td>Terms Page:</td>
                    <td><select name="affiliate_sign_up_form_terms_page_id"><option value="">-Select-</option>' . select_page($affiliate_sign_up_form_properties['terms_page_id']) . '</select></td>
                </tr>
                <tr id="affiliate_sign_up_form_submit_button_label_row" style="' . $affiliate_sign_up_form_submit_button_label_row_style . '">
                    <td>Submit Button Label:</td>
                    <td><input name="affiliate_sign_up_form_submit_button_label" type="text" value="' . $affiliate_sign_up_form_properties['submit_button_label'] . '" maxlength="50" /></td>
                </tr>
                <tr id="affiliate_sign_up_form_next_page_id_row" style="' . $affiliate_sign_up_form_next_page_id_row_style . '">
                    <td>Next Page:</td>
                    <td><select name="affiliate_sign_up_form_next_page_id"><option value="">-Select Affiliate Sign Up Confirmation Page-</option>' . select_page($affiliate_sign_up_form_properties['next_page_id'], 'affiliate sign up confirmation') . '</select></td>
                </tr>';
        }
    
        $output_page_type_properties =
            '<tr>
                <th colspan="2">
                    <a name="interactive_page_feature"></a>
                    <h2>Interactive Page Feature</h2>
                </th>
            </tr>
            <tr>
                <td>Page Type:</td>
                <td><select id="page_type" name="type" onchange="change_page_type(this.options[this.selectedIndex].value)">' . select_page_type($page_type, $user) . '</select><script type="text/javascript">var original_page_type = "' . $page_type . '";</script></td>
            </tr>
            <tr id="layout_type_row" style="' . $layout_type_row_style . '">
                <td>Layout Type:</td>
                <td>
                    <label>
                        <input type="radio" id="layout_type_system" name="layout_type" value="system" class="radio"' . $layout_type_system_checked . '>System
                    </label>&nbsp;
                    
                    <label' . $layout_type_custom_label_class . ' title="Administrators &amp; Designers are allowed to enable a custom layout type.">
                        <input type="radio" name="layout_type" value="custom" class="radio"' . $layout_type_custom_checked . $layout_type_custom_option_disabled . '>Custom
                    </label>
                </td>
            </tr>
            <tr id="email_a_friend_submit_button_label_row" style="' . $email_a_friend_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="email_a_friend_submit_button_label" type="text" value="' . $email_a_friend_properties['submit_button_label'] . '" size="30" maxlength="50" /></td>
            </tr>
            <tr id="email_a_friend_next_page_id_row" style="' . $email_a_friend_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="email_a_friend_next_page_id"><option value="">-Select-</option>' . select_page($email_a_friend_properties['next_page_id']) . '</select></td>
            </tr>
            <tr id="folder_view_pages_row" style="' . $folder_view_pages_row_style . '">
                <td><label for="folder_view_pages">Include Pages:</label></td>
                <td><input type="checkbox" id="folder_view_pages" name="folder_view_pages" value="1"' . $folder_view_pages_checked . ' class="checkbox" /></td>
            </tr>
            <tr id="folder_view_files_row" style="' . $folder_view_files_row_style . '">
                <td><label for="folder_view_files">Include Files:</label></td>
                <td><input type="checkbox" id="folder_view_files" name="folder_view_files" value="1"' . $folder_view_files_checked . ' class="checkbox" /></td>
            </tr>
            <tr id="photo_gallery_number_of_columns_row" style="' . $photo_gallery_number_of_columns_row_style . '">
                <td>Number of Columns:</td>
                <td><input name="photo_gallery_number_of_columns" type="text" value="' . $photo_gallery_number_of_columns . '" maxlength="2" size="3" /></td>
            </tr>
            <tr id="photo_gallery_thumbnail_max_size_row" style="' . $photo_gallery_thumbnail_max_size_row_style . '">
                <td>Thumbnail Max Size:</td>
                <td><input name="photo_gallery_thumbnail_max_size" type="text" value="' . $photo_gallery_thumbnail_max_size . '" maxlength="4" size="3" /> pixels</td>
            </tr>
            ' . $output_search_results_page_type_properties . '
            <tr id="update_address_book_address_type_row" style="' . $update_address_book_address_type_row_style . '">
                <td><label for="update_address_book_address_type">Enable Address Type:</label></td>
                <td><input id="update_address_book_address_type" name="update_address_book_address_type" type="checkbox" value="1"' . $update_address_book_address_type_checked . ' class="checkbox" onclick="show_or_hide_update_address_book_address_type()" /></td>
            </tr>
            <tr id="update_address_book_address_type_page_id_row" style="' . $update_address_book_address_type_page_id_row_style . '">
                <td style="padding-left: 2em">Address Type Page:</td>
                <td><select name="update_address_book_address_type_page_id"><option value=""></option>' . select_page($update_address_book_properties['address_type_page_id']) . '</select></td>
            </tr>
            ' . $output_ecommerce_page_type_properties . '
            ' . $output_forms_page_type_properties . '
            ' . $output_calendars_page_type_properties . '
            ' . $output_affiliate_page_type_properties;
    }
    
    // find search default
    if ($page_search == 1) {
        $search_checked = ' checked="checked"';
        $search_keywords_row_display = '';
    } else {
        $search_checked = '';
        $search_keywords_row_display = 'display: none;';
    }
    
    $output_home_page_rows = '';
    
    // if user is above a user role, then prepare to output home page rows, because user is allowed to set home page
    if ($user['role'] < 3) {
        $home_checked = '';
        
        // if this page is a home page, then prepare to check home page check box
        if ($page_home == 'yes') {
            $home_checked = ' checked="checked"';
        }
        
        $output_home_page_rows =
            '<tr>
                <th colspan="2"><h2>Home Page Feature</h2></th>
            </tr>
            <tr>
                <td>Home Page:</td>
                <td><input type="checkbox" name="home" value="yes"' . $home_checked . ' class="checkbox" /></td>
            </tr>';
    }
    
    $sitemap_row_style = 'display: none';
    
    // if the selected page type is a valid page type for the sitemap, then show sitemap row
    if (
        ($page_type == 'standard')
        || ($page_type == 'folder view')
        || ($page_type == 'photo gallery')
        || ($page_type == 'custom form')
        || ($page_type == 'form list view')
        || ($page_type == 'form item view')
        || ($page_type == 'form view directory')
        || ($page_type == 'calendar view')
        || ($page_type == 'calendar event view')
        || ($page_type == 'catalog')
        || ($page_type == 'catalog detail')
        || ($page_type == 'express order')
        || ($page_type == 'order form')
        || ($page_type == 'shopping cart')
        || ($page_type == 'search results')
    ) {
        $sitemap_row_style = '';
    }
    
    // if sitemap is enabled, then check the checkbox
    if ($sitemap == '1') {
        $sitemap_checked = ' checked="checked"';
    }
    
    $comments_checked = '';
    $comments_label_row_style = 'display: none';
    $comments_message_row_style = 'display: none';
    $comments_allow_new_comments_checked = '';
    $comments_allow_new_comments_row_style = 'display: none';
    $comments_disallow_new_comment_message_row_style = 'display: none';
    $comments_automatic_publish_checked = '';
    $comments_automatic_publish_row_style = 'display: none';
    $comments_allow_user_to_select_name_checked = '';
    $comments_allow_user_to_select_name_row_style = 'display: none';
    $comments_require_login_to_comment_checked = '';
    $comments_require_login_to_comment_row_style = 'display: none';
    $comments_allow_file_attachments_checked = '';
    $comments_allow_file_attachments_row_style = 'display: none';
    $comments_show_submitted_date_and_time_checked = '';
    $comments_show_submitted_date_and_time_row_style = 'display: none';
    $comments_administrator_email_row_style = 'display: none';
    $comments_administrator_email_to_email_address_row_style = 'display: none';
    $comments_administrator_email_subject_row_style = 'display: none';
    $comments_administrator_email_conditional_administrators_checked = '';
    $comments_administrator_email_conditional_administrators_row_style = 'display: none';
    $comments_submitter_email_row_style = 'display: none';
    $comments_submitter_email_page_id_row_style = 'display: none';
    $comments_submitter_email_subject_row_style = 'display: none';
    $comments_watcher_email_row_style = 'display: none';
    $comments_watcher_email_page_id_row_style = 'display: none';
    $comments_watcher_email_subject_row_style = 'display: none';
    $comments_watchers_managed_by_submitter_checked = '';
    $comments_watchers_managed_by_submitter_row_style = 'display: none';
    
    // if comments are on then prepare the fields to be outputted
    if ($comments == '1') {
        // check the comments checkbox
        $comments_checked = ' checked="checked"';
        
        $comments_label_row_style = '';
        $comments_message_row_style = '';
        $comments_allow_new_comments_row_style = '';
        $comments_disallow_new_comment_message_row_style = '';
        $comments_automatic_publish_row_style = '';
        $comments_allow_user_to_select_name_row_style = '';
        $comments_require_login_to_comment_row_style = '';
        $comments_allow_file_attachments_row_style = '';
        $comments_show_submitted_date_and_time_row_style = '';        
        $comments_administrator_email_row_style = '';
        $comments_administrator_email_to_email_address_row_style = '';
        $comments_administrator_email_subject_row_style = '';
        
        // if the page type is form item view then display the submitter specific rows
        if ($page_type == 'form item view') {
            $comments_administrator_email_conditional_administrators_row_style = '';
            $comments_submitter_email_row_style = '';
            $comments_submitter_email_page_id_row_style = '';
            $comments_submitter_email_subject_row_style = '';
        }

        $comments_watcher_email_row_style = '';
        $comments_watcher_email_page_id_row_style = '';
        $comments_watcher_email_subject_row_style = '';

        // If the page type is form item view then display the watches managed by submitter field
        if ($page_type == 'form item view') {
            $comments_watchers_managed_by_submitter_row_style = '';
        }
    }

    if ($comments_label == '') {
        $comments_label = 'Comment';
    }

    // if allow new comments is on or if comments are disabled, then check the checkbox
    if (($comments_allow_new_comments == '1') || ($comments == '0')) {
        $comments_allow_new_comments_checked = ' checked="checked"';
    }
    
    // if comments automatic publish is on or if comments are disabled, then check the checkbox
    if (($comments_automatic_publish == '1') || ($comments == '0')) {
        $comments_automatic_publish_checked = ' checked="checked"';
    }
    
    // if comments allow user to select name is on or if comments are disabled, then check the checkbox
    if (($comments_allow_user_to_select_name == '1') || ($comments == '0')) {
        $comments_allow_user_to_select_name_checked = ' checked="checked"';
    }
    
    // if comments require login to comment is on and comments are enabled, then check the checkbox
    if (($comments_require_login_to_comment == '1') && ($comments == '1')) {
        $comments_require_login_to_comment_checked = ' checked="checked"';
    }
    
    // if comments allow file attachments is on and comments are enabled, then check the checkbox
    if (($comments_allow_file_attachments == '1') && ($comments == '1')) {
        $comments_allow_file_attachments_checked = ' checked="checked"';
    }
    
    // if show submitted date and time is on or if comments are disabled, then check the checkbox
    if (($comments_show_submitted_date_and_time == '1') || ($comments == '0')) {
        $comments_show_submitted_date_and_time_checked = ' checked="checked"';
    }
    
    // if conditional administrators is enabled, then check the checkbox
    if ($comments_administrator_email_conditional_administrators == '1') {
        $comments_administrator_email_conditional_administrators_checked = ' checked="checked"';
    }

    // If watchers managed by submitter is enabled, then check the checkbox.
    if ($comments_watchers_managed_by_submitter == '1') {
        $comments_watchers_managed_by_submitter_checked = ' checked="checked"';
    }
    
    $output_delete_button = '';

    // if the user is at least a manager or has access to delete pages, then output the delete button
    if (($user['role'] < '3') || ($user['delete_pages'] == TRUE)) {
        $output_delete_button = '&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This page will be permanently deleted.\')">';
    }
    
    print
        output_header() . '
        <div id="subnav">
            ' . $output_subnav_home . '<h1>' . h($page_name) . '</h1>
            <div class="subheading">Access: ' . h(get_access_control_type_name(get_access_control_type($page_folder))) . $output_subnav_page_type . $output_subnav_short_link . $output_subnav_search . $output_subnav_search_keywords . $output_subnav_next_page . $output_subnav_skip_page . '</div>
        </div>
        ' . $output_button_bar . '
        <div id="content">
            
            ' . $output_wysiwyg_editor_code . '
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Page Properties</h1>
            <div class="subheading">View and update the page, move it to another folder, or change its built-in features.</div>
            <form action="edit_page.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Page Name</h2></th>
                    </tr>
                    <tr>
                        <td>Page Name:</td>
                        <td><span style="white-space: nowrap">' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . '<input name="name" type="text" value="' . h($page_name) . '" size="60" maxlength="100" /></span></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Page Access Control, Design &amp; Common Content</h2></th>
                    </tr>
                    <tr>
                        <td>Folder:</td>
                        <td><select name="folder">' . select_folder($page_folder) . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Override Folder\'s Default Page Styles</h2></th>
                    </tr>
                    <tr>
                        <td>Desktop Page Style:</td>
                        <td>' . $output_style . '</td>
                    </tr>
                    <tr>
                        <td>Mobile Page Style:</td>
                        <td>' . $output_mobile_style . '</td>
                    </tr>
                    ' . $output_page_type_properties . '
                    <tr>
                        <th colspan="2"><h2>Site Search Feature</h2></th>
                    </tr>
                    <tr>
                        <td>
                            <label for="search">Include in Site Search:<label>
                        </td>
                        <td>
                            <input type="checkbox" id="search" name="search" value="1"' . $search_checked . ' onclick="show_or_hide_search()" class="checkbox">
                        </td>
                    </tr>
                    <tr id="search_keywords_row" style="' . $search_keywords_row_display . '">
                        <td style="vertical-align: top">
                            <label for="search_keywords">Promote on Keyword:</label>
                        </td>
                        <td>
                            <textarea id="search_keywords" name="search_keywords" rows="3" style="width: 99%">'
                                . h($page_search_keywords) .
                            '</textarea>
                        </td>
                    </tr>
                    ' . $output_home_page_rows . '
                    <tr id="search_engine_optimization_heading_row">
                        <th colspan="2"><h2>Search Engine Optimization</h2></th>
                    </tr>
                    <tr>
                        <td>
                            <label for="title">Web Browser Title:</label>
                        </td>
                        <td>
                            <input id="title" name="title" type="text" value="' . h($page_title) . '" maxlength="255" style="width: 98%">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">
                            <label for="meta_description">Web Browser Description:</label>
                        </td>
                        <td>
                            <textarea id="meta_description" name="meta_description" maxlength="255" rows="3" style="width: 99%">'
                                . h($page_meta_description) .
                            '</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">
                            <label for="meta_keywords">Web Browser Keywords:</label>
                        </td>
                        <td>
                            <textarea id="meta_keywords" name="meta_keywords" rows="3" style="width: 99%">'
                                . h($page_meta_keywords) .
                            '</textarea>
                        </td>
                    </tr>
                    <tr id="sitemap_row" style="' . $sitemap_row_style . '">
                        <td><label for="sitemap">Include in Site Map:</label></td>
                        <td><input type="checkbox" id="sitemap" name="sitemap" value="1"' . $sitemap_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Comments Feature</h2></th>
                    </tr>
                    <tr>
                        <td><label for="comments">Enable Comments:</label></td>
                        <td><input type="checkbox" id="comments" name="comments" value="1"' . $comments_checked . ' onclick="show_or_hide_comments()" class="checkbox" /></td>
                    </tr>
                    <tr id="comments_label_row" style="' . $comments_label_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_label">Comment Label:</label></td>
                        <td><input id="comments_label" name="comments_label" type="text" value="' . h($comments_label) . '" maxlength="100"></td>
                    </tr>
                    <tr id="comments_message_row" style="' . $comments_message_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_message">Add Comment Message:</label></td>
                        <td><input id="comments_message" name="comments_message" type="text" value="' . h($comments_message) . '" size="80" maxlength="255"></td>
                    </tr>
                    <tr id="comments_allow_new_comments_row" style="' . $comments_allow_new_comments_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_allow_new_comments">Allow New Comments:</label></td>
                        <td><input type="checkbox" id="comments_allow_new_comments" name="comments_allow_new_comments" value="1"' . $comments_allow_new_comments_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_disallow_new_comment_message_row" style="' . $comments_disallow_new_comment_message_row_style . '">
                        <td style="padding-left: 2em">Do Not Allow New Comments Message:</td>
                        <td><input name="comments_disallow_new_comment_message" type="text" value="' . h($comments_disallow_new_comment_message) . '" size="80" maxlength="255" /></td>
                    </tr>
                    <tr id="comments_automatic_publish_row" style="' . $comments_automatic_publish_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_automatic_publish">Automatically Publish Comments:</label></td>
                        <td><input type="checkbox" id="comments_automatic_publish" name="comments_automatic_publish" value="1"' . $comments_automatic_publish_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_allow_user_to_select_name_row" style="' . $comments_allow_user_to_select_name_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_allow_user_to_select_name">Allow User to Select Name:</label></td>
                        <td><input type="checkbox" id="comments_allow_user_to_select_name" name="comments_allow_user_to_select_name" value="1"' . $comments_allow_user_to_select_name_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_require_login_to_comment_row" style="' . $comments_require_login_to_comment_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_require_login_to_comment">Require Login to Comment:</label></td>
                        <td><input type="checkbox" id="comments_require_login_to_comment" name="comments_require_login_to_comment" value="1"' . $comments_require_login_to_comment_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_allow_file_attachments_row" style="' . $comments_allow_file_attachments_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_allow_file_attachments">Allow File Attachments:</label></td>
                        <td><input type="checkbox" id="comments_allow_file_attachments" name="comments_allow_file_attachments" value="1"' . $comments_allow_file_attachments_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_show_submitted_date_and_time_row" style="' . $comments_show_submitted_date_and_time_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_show_submitted_date_and_time">Show Submitted Date &amp; Time:</label></td>
                        <td><input type="checkbox" id="comments_show_submitted_date_and_time" name="comments_show_submitted_date_and_time" value="1"' . $comments_show_submitted_date_and_time_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_administrator_email_row" style="' . $comments_administrator_email_row_style . '">
                        <td colspan="2" style="padding-left: 2em">E-mail moderator when a<br />comment is added:</td>
                    </tr>
                    <tr id="comments_administrator_email_to_email_address_row" style="' . $comments_administrator_email_to_email_address_row_style . '">
                        <td style="padding-left: 4em">To E-mail Address:</td>
                        <td><input name="comments_administrator_email_to_email_address" type="text" value="' . h($comments_administrator_email_to_email_address) . '" size="30" maxlength="100" /></td>
                    </tr>
                    <tr id="comments_administrator_email_subject_row" style="' . $comments_administrator_email_subject_row_style . '">
                        <td style="padding-left: 4em">Subject:</td>
                        <td><input name="comments_administrator_email_subject" type="text" value="' . h($comments_administrator_email_subject) . '" size="80" maxlength="255" /></td>
                    </tr>
                    <tr id="comments_administrator_email_conditional_administrators_row" style="' . $comments_administrator_email_conditional_administrators_row_style . '">
                        <td style="padding-left: 4em">Also send e-mail to custom form<br />conditional administrators:</td>
                        <td><input type="checkbox" name="comments_administrator_email_conditional_administrators" value="1"' . $comments_administrator_email_conditional_administrators_checked . ' class="checkbox" /></td>
                    </tr>
                    <tr id="comments_submitter_email_row" style="' . $comments_submitter_email_row_style . '">
                        <td colspan="2" style="padding-left: 2em">E-mail custom form submitter<br />when a comment is published:</td>
                    </tr>
                    <tr id="comments_submitter_email_page_id_row" style="' . $comments_submitter_email_page_id_row_style . '">
                        <td style="padding-left: 4em">Page:</td>
                        <td><select name="comments_submitter_email_page_id"><option value=""></option>' . select_page($comments_submitter_email_page_id) . '</select></td>
                    </tr>
                    <tr id="comments_submitter_email_subject_row" style="' . $comments_submitter_email_subject_row_style . '">
                        <td style="padding-left: 4em">Subject:</td>
                        <td><input name="comments_submitter_email_subject" type="text" value="' . h($comments_submitter_email_subject) . '" size="80" maxlength="255" /></td>
                    </tr>
                    <tr id="comments_watcher_email_row" style="' . $comments_watcher_email_row_style . '">
                        <td colspan="2" style="padding-left: 2em">E-mail watchers when a<br />comment is published:</td>
                    </tr>
                    <tr id="comments_watcher_email_page_id_row" style="' . $comments_watcher_email_page_id_row_style . '">
                        <td style="padding-left: 4em">Page:</td>
                        <td><select name="comments_watcher_email_page_id"><option value=""></option>' . select_page($comments_watcher_email_page_id) . '</select></td>
                    </tr>
                    <tr id="comments_watcher_email_subject_row" style="' . $comments_watcher_email_subject_row_style . '">
                        <td style="padding-left: 4em">Subject:</td>
                        <td><input name="comments_watcher_email_subject" type="text" value="' . h($comments_watcher_email_subject) . '" size="80" maxlength="255" /></td>
                    </tr>
                    <tr id="comments_watchers_managed_by_submitter_row" style="' . $comments_watchers_managed_by_submitter_row_style . '">
                        <td style="padding-left: 2em"><label for="comments_watchers_managed_by_submitter">Allow submitter to manage watchers:</label></td>
                        <td><input type="checkbox" id="comments_watchers_managed_by_submitter" name="comments_watchers_managed_by_submitter" value="1"' . $comments_watchers_managed_by_submitter_checked . ' class="checkbox" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" id="create_button" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">' . $output_delete_button . '
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form('edit_page');

// else -> process
} else {
    validate_token_field();
    
    // get current page data
    $query =
        "SELECT
            page_id,
            page_name,
            page_type,
            layout_type,
            page_title,
            page_meta_description,
            seo_analysis_current,
            page_search, 
            page_meta_keywords
        FROM page
        WHERE page_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $page_id = $row['page_id'];
	$current_page_name = $row['page_name'];
    $current_page_type = $row['page_type'];
    $current_layout_type = $row['layout_type'];
    $current_page_title = $row['page_title'];
    $current_page_meta_description = $row['page_meta_description'];
    $current_seo_analysis_current = $row['seo_analysis_current'];
    $current_page_search = $row['page_search'];
    $current_meta_keywords = $row['page_meta_keywords'];
    
    // if page was selected for delete, check if user has access and then delete page
    if ($_POST['submit_delete'] == 'Delete') {
        // if the user has a user role and the user does not have access to delete pages, then output error
        if (($user['role'] == '3') && ($user['delete_pages'] == FALSE)) {
            log_activity("access denied because user does not have access to delete pages", $_SESSION['sessionusername']);
            output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // if this page is a custom form, we need to check if there are submitted forms for this page,
        // because software will not allow this page to be deleted if there are submitted forms for this page
        if ($current_page_type == 'custom form') {
            $query = "SELECT id FROM forms WHERE page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if there are submitted forms for this page, do not delete page and notify user
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('', 'This page could not be deleted because there are submitted forms for this page.  All submitted forms for this page must be deleted before this page is allowed to be deleted.  You may disable the custom form on this page by unchecking the Enable Form check box below.  You may archive this page by moving it to a private folder.');
                
                // forward user to edit page screen
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
                exit();
            }
        }
        
        // if this page was a search results page type, then remove the keywords from the tag cloud
        if ($current_page_type == 'search results') {
            delete_tag_cloud_keywords_for_search_results_page($_POST['id']);
        }
        
        // delete page
        $query = "DELETE FROM page WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete regions
        $query = "DELETE FROM pregion WHERE pregion_page = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete form fields for page
        $query = "DELETE FROM form_fields WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete form field options for page
        $query = "DELETE FROM form_field_options WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Delete target options for page.
        db("DELETE FROM target_options WHERE page_id = '" . escape($_POST['id']) . "'");
        
        // if this page is a form list view, delete records from related tables.
        if ($current_page_type == 'form list view') {
            $query = "DELETE FROM form_list_view_filters WHERE page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $query = "DELETE FROM form_list_view_browse_fields WHERE page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_list_view_page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // if this page is a form view directory, delete form_view_directories_form_list_views_xref records
        if ($current_page_type == 'form view directory') {
            $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_view_directory_page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // delete views for this page that the form view directory feature uses
        $query = "DELETE FROM submitted_form_views WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete calendar_views_calendars_xref records
        $query = "DELETE FROM calendar_views_calendars_xref WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete calendar_event_views_calendars_xref records
        $query = "DELETE FROM calendar_event_views_calendars_xref WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if page type has a table for properties, delete page type record of properties
        if (check_for_page_type_properties($current_page_type) == true) {
            $page_type_table_name = str_replace(' ', '_', $current_page_type) . '_pages';
            
            $query = "DELETE FROM $page_type_table_name WHERE page_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        // get comment attachments for this page, so they can be deleted
        $query = 
            "SELECT
                comments.id as comment_id,
                files.id,
                files.name
            FROM comments
            LEFT JOIN files ON comments.file_id = files.id
            WHERE
                (comments.page_id = '" . escape($_POST['id']) . "')
                AND (files.id IS NOT NULL)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $attachments = array();
        
        // loop through the attachments in order to add them to array
        while ($row = mysqli_fetch_assoc($result)) {
            $attachments[] = $row;
        }
        
        // loop through the attachments so they can be deleted
        foreach ($attachments as $attachment) {
            // check if the file attachment is used by another comment (multiple comments can share the same file attachment when pages are duplicated)
            $query = "SELECT id FROM comments WHERE (file_id = '" . $attachment['id'] . "') AND (id != '" . $attachment['comment_id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the file attachment is not used by another comment, then delete the file
            if (mysqli_num_rows($result) == 0) {
                // delete file from database
                $query = "DELETE FROM files WHERE id = '" . $attachment['id'] . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete file on file system
                @unlink(FILE_DIRECTORY_PATH . '/' . $attachment['name']);
                
                // log that the file was deleted
                log_activity('file attachment (' . $attachment['name'] . ') for a comment was deleted because the page (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
            }
        }
        
        // delete comments for page
        $query = "DELETE FROM comments WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete submitted_form_info for page
        $query = "DELETE FROM submitted_form_info WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete allow new comments data for this page
        $query = "DELETE FROM allow_new_comments_for_items WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete watchers for this page
        $query = "DELETE FROM watchers WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // remove this page's keywords from the tag cloud
        update_tag_cloud_keywords_for_page($_POST['id'], 0, '', $current_page_search, $current_meta_keywords);

        // Check if this page has short links, in order to determine if we need to delete them.
        $query =
            "SELECT COUNT(*)
            FROM short_links
            WHERE
                (destination_type = 'page')
                AND (page_id = '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);

        // If a short link exists, then delete them.
        if ($row[0] != 0) {
            $query =
                "DELETE FROM short_links
                WHERE
                    (destination_type = 'page')
                    AND (page_id = '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        db("DELETE FROM preview_styles WHERE page_id = '" . escape($_POST['id']) . "'");

        // If a layout file exists, then delete it.
        if (file_exists(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php')) {
            unlink(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php');
        }
        
        log_activity("page ($_POST[name]) was deleted", $_SESSION['sessionusername']);
        
        // add notice
        $liveform_view_pages = new liveform('view_pages');
        $liveform_view_pages->add_notice('The page has been deleted.');
        
        // forward user to view pages
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_pages.php');
        exit();
        
    // else page was edited, not deleted
    } else {
        $name = trim($_POST['name']);
        
        // If the page name field is blank.
        if ($name == '') {
            $liveform->mark_error('name', 'The page must have a name. Please type in a name for the page.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        }
        
        // if the page type is catalog or catalog detail then check the name for slashes
        if (($_POST['type'] == 'catalog') || ($_POST['type'] == 'catalog detail')) {
            // if there is a slash in the page name, then output an error
            if (mb_strpos($name, '/') !== FALSE) {
                $liveform->mark_error('name', 'The page name for catalog and catalog detail pages cannot contain forward slashes. Please type in a new name for the page.');
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
                exit();
            }
        }
        
        $name = str_replace(" ", "_", $name);
        $name = str_replace("&", "_", $name);
        
        $search_keywords = trim($_POST['search_keywords']);

        if (check_name_availability(array('name' => $name, 'ignore_item_id' => $_POST['id'], 'ignore_item_type' => 'page')) == false) {
            $liveform->mark_error('name', 'The page name that you entered is already in use. Please enter a different page name.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        }
        
        // if page is a custom form, check to see if there is another page with this same form name
        if ($_POST['type'] == 'custom form') {
            $query = "SELECT id FROM custom_form_pages WHERE (form_name = '" . escape($_POST['custom_form_form_name']) . "') AND (page_id != '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if there is another page with this form name, output error
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('custom_form_form_name', 'The form name that you entered is already in use. Please enter a different form name.');
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['id'] . '&send_to=' . urlencode($_POST['send_to']));
                exit();
            }
        }
        
        // if user is above a user role, then deal with home page checkbox
        if ($user['role'] < 3) {
            $sql_home_page = " page_home = '" . escape($_POST['home']) . "',";
            
        // else this user does not have access to change the home page property
        } else {
            $sql_home_page = '';
        }

        // Assume that pretty URLs were disabled previously for this page, until we find out otherwise.
        // We use this in order to determine later if we need to update address names for submitted forms.
        $pretty_urls_old = false;

        // If this is a custom form, then get previous pretty URL status.
        if ($current_page_type == 'custom form') {
            $pretty_urls_old = check_if_pretty_urls_are_enabled($_POST['id']);
        }
        
        // if user is above a user role or current page type is accessible by this user, then prepare to save page type
        if (
            ($user['role'] < 3)
            || ($current_page_type == 'standard')
            || (($current_page_type == 'email a friend') && ($user['set_page_type_email_a_friend'] == TRUE))
            || (($current_page_type == 'folder view') && ($user['set_page_type_folder_view'] == TRUE))
            || (($current_page_type == 'photo gallery') && ($user['set_page_type_photo_gallery'] == TRUE))
            || (($current_page_type == 'catalog') && ($user['set_page_type_catalog'] == TRUE))
            || (($current_page_type == 'catalog detail') && ($user['set_page_type_catalog_detail'] == TRUE))
            || (($current_page_type == 'express order') && ($user['set_page_type_express_order'] == TRUE))
            || (($current_page_type == 'order form') && ($user['set_page_type_order_form'] == TRUE))
            || (($current_page_type == 'shopping cart') && ($user['set_page_type_shopping_cart'] == TRUE))
            || (($current_page_type == 'shipping address and arrival') && ($user['set_page_type_shipping_address_and_arrival'] == TRUE))
            || (($current_page_type == 'shipping method') && ($user['set_page_type_shipping_method'] == TRUE))
            || (($current_page_type == 'billing information') && ($user['set_page_type_billing_information'] == TRUE))
            || (($current_page_type == 'order preview') && ($user['set_page_type_order_preview'] == TRUE))
            || (($current_page_type == 'order receipt') && ($user['set_page_type_order_receipt'] == TRUE))
            || (($current_page_type == 'custom form') && ($user['set_page_type_custom_form'] == TRUE))
            || (($current_page_type == 'custom form confirmation') && ($user['set_page_type_custom_form_confirmation'] == TRUE))
            || (($current_page_type == 'form list view') && ($user['set_page_type_form_list_view'] == TRUE))
            || (($current_page_type == 'form item view') && ($user['set_page_type_form_item_view'] == TRUE))
            || (($current_page_type == 'form view directory') && ($user['set_page_type_form_view_directory'] == TRUE))
            || (($current_page_type == 'calendar view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_view'] == TRUE))
            || (($current_page_type == 'calendar event view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_event_view'] == TRUE))
        ) {
            // assume that we can update the page type until we find out otherwise
            $update_page_type = true;
            
            // if the submitted page type is different from the current page type and this page is a custom form,
            // we need to check if there are submitted forms for this page,
            // because software will not allow the page type for this page to be changed if there are submitted forms for this page
            if (($current_page_type != $_POST['type']) && ($current_page_type == 'custom form')) {
                $query = "SELECT id FROM forms WHERE page_id = '" . escape($_POST['id']) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                if (mysqli_num_rows($result) > 0) {
                    $update_page_type = false;
                    $liveform->mark_error('', 'The page type for this page could not be changed because there are submitted forms for this page.  All submitted forms for this page must be deleted before the page type for this page is allowed to be changed.');
                }
            }
            
            // if we can update the page type, then update page type and tag cloud table if needed
            if ($update_page_type == true) {
                // if the page type was changed and the original page type was a search results page type, then remove the keywords for the products and product groups if there are any to remove
                if (($current_page_type != $_POST['type']) && ($current_page_type == 'search results')) {
                    delete_tag_cloud_keywords_for_search_results_page($_POST['id']);
                }
                
                switch($_POST['type']) {
                    case 'email a friend':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'submit_button_label' => $_POST['email_a_friend_submit_button_label'],
                            'next_page_id' => $_POST['email_a_friend_next_page_id']
                        );
                        
                        break;

                    case 'folder view':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'pages' => $_POST['folder_view_pages'],
                            'files' => $_POST['folder_view_files']
                        );
                        
                        break;
                        
                    case 'photo gallery':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'number_of_columns' => $_POST['photo_gallery_number_of_columns'],
                            'thumbnail_max_size' => $_POST['photo_gallery_thumbnail_max_size']
                        );
                        
                        break;
                        
                    case 'search results':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'search_folder_id' => $_POST['search_results_search_folder_id'],
                            'search_catalog_items' => $_POST['search_results_search_catalog_items'],
                            'product_group_id' => $_POST['search_results_product_group_id'],
                            'catalog_detail_page_id' => $_POST['search_results_catalog_detail_page_id']
                        );
                        
                        // update the tag cloud tables
                        update_tag_cloud_keywords_for_search_results_page_type($_POST['id'], $_POST['search_results_search_catalog_items'], $_POST['search_results_product_group_id']);
                        
                        break;
                        
                    case 'update address book':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'address_type' => $_POST['update_address_book_address_type'],
                            'address_type_page_id' => $_POST['update_address_book_address_type_page_id']
                        );
                        
                        break;
                        
                    case 'custom form':
                        $new_contact_group_id = $_POST['custom_form_contact_group_id'];
                        
                        // if user has a user role, then verify that user has access to contact group that was selected
                        if ($user['role'] == 3) {
                            // get current contact group id
                            $query = "SELECT contact_group_id
                                     FROM custom_form_pages
                                     WHERE page_id = '" . escape($_POST['id']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $current_contact_group_id = $row['contact_group_id'];
                            
                            // if contact group is trying to be changed
                            // and a contact group was selected
                            // and user does not have access to contact group,
                            // then don't allow contact group to be changed
                            if (($new_contact_group_id != $current_contact_group_id) && ($new_contact_group_id) && (validate_contact_group_access($user, $new_contact_group_id) == false)) {
                                $new_contact_group_id = $current_contact_group_id;
                                $liveform->mark_error('', 'The contact group for the custom form could not be changed, because you do not have access to the contact group you selected.');
                                log_activity("access denied to change contact group for custom form because user did not have access to contact group", $_SESSION['sessionusername']);
                            }
                        }

                        $new_private_folder_id = $_POST['custom_form_private_folder_id'];
                        
                        // If user has a user role, then verify that user has access to the selected private folder.
                        if (USER_ROLE == 3) {
                            $old_private_folder_id = db_value(
                                "SELECT private_folder_id
                                FROM custom_form_pages
                                WHERE page_id = '" . escape($_POST['id']) . "'");
                            
                            // If the user is trying to change the private folder to a folder
                            // that he/she does not have edit access to,
                            // then don't allow folder to be set and log activity.
                            if (
                                ($new_private_folder_id != $old_private_folder_id)
                                && ($new_private_folder_id)
                                && (check_edit_access($new_private_folder_id) == false)
                            ) {
                                $new_private_folder_id = $old_private_folder_id;
                                $liveform->mark_error('', 'The private folder for the custom form could not be changed, because you do not have edit access to the folder you selected.');
                                log_activity('access denied to change private folder for custom form because user did not have edit access to folder', $_SESSION['sessionusername']);
                            }
                        }
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'form_name' => $_POST['custom_form_form_name'],
                            'enabled' => $_POST['custom_form_enabled'],
                            'quiz' => $_POST['custom_form_quiz'],
                            'quiz_pass_percentage' => $_POST['custom_form_quiz_pass_percentage'],
                            'label_column_width' => $_POST['custom_form_label_column_width'],
                            'watcher_page_id' => $_POST['custom_form_watcher_page_id'],
                            'save' => $_POST['custom_form_save'],
                            'submit_button_label' => $_POST['custom_form_submit_button_label'],
                            'auto_registration' => $_POST['custom_form_auto_registration'],
                            'submitter_email' => $_POST['custom_form_submitter_email'],
                            'submitter_email_from_email_address' => $_POST['custom_form_submitter_email_from_email_address'],
                            'submitter_email_subject' => $_POST['custom_form_submitter_email_subject'],
                            'submitter_email_format' => $_POST['custom_form_submitter_email_format'],
                            'submitter_email_body' => $_POST['custom_form_submitter_email_body'],
                            'submitter_email_page_id' => $_POST['custom_form_submitter_email_page_id'],
                            'administrator_email' => $_POST['custom_form_administrator_email'],
                            'administrator_email_to_email_address' => $_POST['custom_form_administrator_email_to_email_address'],
                            'administrator_email_bcc_email_address' => $_POST['custom_form_administrator_email_bcc_email_address'],
                            'administrator_email_subject' => $_POST['custom_form_administrator_email_subject'],
                            'administrator_email_format' => $_POST['custom_form_administrator_email_format'],
                            'administrator_email_body' => $_POST['custom_form_administrator_email_body'],
                            'administrator_email_page_id' => $_POST['custom_form_administrator_email_page_id'],
                            'contact_group_id' => $new_contact_group_id,
                            'membership' => $_POST['custom_form_membership'],
                            'membership_days' => $_POST['custom_form_membership_days'],
                            'membership_start_page_id' => $_POST['custom_form_membership_start_page_id'],
                            'private' => $_POST['custom_form_private'],
                            'private_folder_id' => $new_private_folder_id,
                            'private_days' => $_POST['custom_form_private_days'],
                            'private_start_page_id' => $_POST['custom_form_private_start_page_id'],
                            'confirmation_type' => $_POST['custom_form_confirmation_type'],
                            'confirmation_message' => prepare_rich_text_editor_content_for_input($_POST['custom_form_confirmation_message']),
                            'confirmation_page_id' => $_POST['custom_form_confirmation_page_id'],
                            'confirmation_alternative_page' => $_POST['custom_form_confirmation_alternative_page'],
                            'confirmation_alternative_page_contact_group_id' => $_POST['custom_form_confirmation_alternative_page_contact_group_id'],
                            'confirmation_alternative_page_id' => $_POST['custom_form_confirmation_alternative_page_id'],
                            'return_type' => $_POST['custom_form_return_type'],
                            'return_message' => prepare_rich_text_editor_content_for_input($_POST['custom_form_return_message']),
                            'return_page_id' => $_POST['custom_form_return_page_id'],
                            'return_alternative_page' => $_POST['custom_form_return_alternative_page'],
                            'return_alternative_page_contact_group_id' => $_POST['custom_form_return_alternative_page_contact_group_id'],
                            'return_alternative_page_id' => $_POST['custom_form_return_alternative_page_id'],
                            'pretty_urls' => $_POST['custom_form_pretty_urls']
                        );

                        // If hooks are enabled and the user is a designer or administrator then prepare property for PHP hook code.
                        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                            $properties['hook_code'] = $_POST['custom_form_hook_code'];
                        }

                        // If commerce is enabled then save offer properties.
                        if (ECOMMERCE) {
                            // If user does not have access to commerce, then get old values,
                            // because we might need to save them.
                            if (!USER_MANAGE_ECOMMERCE) {
                                $old_properties = db_item(
                                    "SELECT
                                        offer,
                                        offer_id
                                    FROM custom_form_pages
                                    WHERE page_id = '" . e($_POST['id']) . "'");
                            }

                            // If the user has access to commerce or the user just disabled grant offer,
                            // then save grant offer status.
                            if (USER_MANAGE_ECOMMERCE || !$_POST['custom_form_offer']) {
                                $properties['offer'] = $_POST['custom_form_offer'];

                            // Otherwise the user does not have access to commerce, so save old value.
                            } else {
                                $properties['offer'] = $old_properties['offer'];
                            }

                            // If the user has access to commerce or the user just selected the blank offer,
                            // then save the offer id from the user.
                            if (USER_MANAGE_ECOMMERCE || !$_POST['custom_form_offer_id']) {
                                $properties['offer_id'] = $_POST['custom_form_offer_id'];

                            // Otherwise the user does not have access to commerce, so save old value.
                            } else {
                                $properties['offer_id'] = $old_properties['offer_id'];
                            }

                            $properties['offer_days'] = $_POST['custom_form_offer_days'];
                            $properties['offer_eligibility'] = $_POST['custom_form_offer_eligibility'];
                        }
                        
                        break;
                    
                    case 'custom form confirmation':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'continue_button_label' => $_POST['custom_form_confirmation_continue_button_label'],
                            'next_page_id' => $_POST['custom_form_confirmation_next_page_id']
                        );
                        
                        break;
                    
                    case 'form list view':
                        $new_custom_form_page_id = $_POST['form_list_view_custom_form_page_id'];
                        
                        // if user has a user role, then verify that user has access to custom form that was selected
                        if ($user['role'] == 3) {
                            // get current custom form
                            $query = "SELECT custom_form_page_id
                                     FROM form_list_view_pages
                                     WHERE
                                        (page_id = '" . escape($_POST['id']) . "')
                                        AND (collection = 'a')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $current_custom_form_page_id = $row['custom_form_page_id'];
                            
                            // get folder of new custom_form
                            $query = "SELECT page_folder
                                     FROM page
                                     WHERE page_id = '" . escape($new_custom_form_page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $new_custom_form_folder_id = $row['page_folder'];
                            
                            // if custom form is trying to be changed and user does not have access to custom form, don't allow custom form to be changed
                            if (($new_custom_form_page_id != $current_custom_form_page_id) && (check_edit_access($new_custom_form_folder_id) == false)) {
                                $new_custom_form_page_id = $current_custom_form_page_id;
                                $liveform->mark_error('', 'The custom form for the form list view could not be changed, because you do not have access to the custom form you selected.');
                                log_activity("access denied to change custom form for form list view because user did not have access to modify folder that custom form was in", $_SESSION['sessionusername']);
                            }
                        }
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'custom_form_page_id' => $new_custom_form_page_id,
                            'form_item_view_page_id' => $_POST['form_list_view_form_item_view_page_id'],
                            'viewer_filter' => $_POST['form_list_view_viewer_filter'],
                            'viewer_filter_submitter' => $_POST['form_list_view_viewer_filter_submitter'],
                            'viewer_filter_watcher' => $_POST['form_list_view_viewer_filter_watcher'],
                            'viewer_filter_editor' => $_POST['form_list_view_viewer_filter_editor']
                        );
                        
                        // check if there is a record for this form list view
                        $query =
                            "SELECT COUNT(*)
                            FROM form_list_view_pages
                            WHERE page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);
                        
                        // if there is not a record for this form list view, then set default values
                        if ($row[0] == 0) {
                            $properties['search'] = 1;
                            $properties['show_results_by_default'] = 1;
                            $properties['maximum_number_of_results_per_page'] = 25;
                        }
                        
                        break;
                    
                    case 'form item view':
                        $new_custom_form_page_id = $_POST['form_item_view_custom_form_page_id'];
                        
                        // if user has a user role, then verify that user has access to custom form that was selected
                        if ($user['role'] == 3) {
                            // get current custom form
                            $query =
                                "SELECT custom_form_page_id
                                FROM form_item_view_pages
                                WHERE
                                    (page_id = '" . escape($_POST['id']) . "')
                                    AND (collection = 'a')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $current_custom_form_page_id = $row['custom_form_page_id'];
                            
                            // get folder of new custom_form
                            $query = "SELECT page_folder
                                     FROM page
                                     WHERE page_id = '" . escape($new_custom_form_page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            $new_custom_form_folder_id = $row['page_folder'];
                            
                            // if custom form is trying to be changed and user does not have access to custom form, don't allow custom form to be changed
                            if (($new_custom_form_page_id != $current_custom_form_page_id) && (check_edit_access($new_custom_form_folder_id) == false)) {
                                $new_custom_form_page_id = $current_custom_form_page_id;
                                $liveform->mark_error('', 'The custom form for the form item view could not be changed, because you do not have access to the custom form you selected.');
                                log_activity("access denied to change custom form for form item view because user did not have access to modify folder that custom form was in", $_SESSION['sessionusername']);
                            }
                        }
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'custom_form_page_id' => $new_custom_form_page_id,
                            'submitter_security' => $_POST['form_item_view_submitter_security'],
                            'submitted_form_editable_by_registered_user' => $_POST['form_item_view_submitted_form_editable_by_registered_user'],
                            'submitted_form_editable_by_submitter' => $_POST['form_item_view_submitted_form_editable_by_submitter']
                        );

                        // If hooks are enabled and the user is a designer or administrator then prepare property for PHP hook code.
                        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                            $properties['hook_code'] = $_POST['form_item_view_hook_code'];
                        }
                        
                        break;
                        
                    case 'form view directory':
                        $form_list_view_heading = $_POST['form_view_directory_form_list_view_heading'];
                        $subject_heading = $_POST['form_view_directory_subject_heading'];
                        $number_of_submitted_forms_heading = $_POST['form_view_directory_number_of_submitted_forms_heading'];
                        
                        // if the form list view heading is blank, then set it to the default value
                        if ($form_list_view_heading == '') {
                            $form_list_view_heading = 'Forum';
                        }
                        
                        // if the subject heading is blank, then set it to the default value
                        if ($subject_heading == '') {
                            $subject_heading = 'Subject';
                        }
                        
                        // if the number of submitted forms heading is blank, then set it to the default value
                        if ($number_of_submitted_forms_heading == '') {
                            $number_of_submitted_forms_heading = 'Forms';
                        }
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'summary' => $_POST['form_view_directory_summary'],
                            'summary_days' => $_POST['form_view_directory_summary_days'],
                            'summary_maximum_number_of_results' => $_POST['form_view_directory_summary_maximum_number_of_results'],
                            'form_list_view_heading' => $form_list_view_heading,
                            'subject_heading' => $subject_heading,
                            'number_of_submitted_forms_heading' => $number_of_submitted_forms_heading
                        );
                        
                        // delete old connections between form view directory and form list views
                        $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_view_directory_page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // prepare to get folders that user has access to, in order to determine which form list views should be available to be selected for the form view directory page type
                        $folders_that_user_has_access_to = array();
                        
                        // if user is a basic user, then get folders that user has access to
                        if ($user['role'] == 3) {
                            $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
                        }
                        
                        // get all unarchived form list views for form view directory page type
                        $query =
                            "SELECT
                                page.page_id,
                                page.page_name,
                                page.page_folder as folder_id,
                                form_list_view_pages.custom_form_page_id
                            FROM page
                            LEFT JOIN form_list_view_pages ON
                                (page.page_id = form_list_view_pages.page_id)
                                AND (form_list_view_pages.collection = 'a')
                            LEFT JOIN folder ON page.page_folder = folder.folder_id
                            WHERE
                                (page.page_type = 'form list view')
                                AND (folder.folder_archived = '0')
                            ORDER BY page.page_name ASC";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $form_list_views = array();
                        
                        // loop through the form list views in order to add them to array
                        while ($row = mysqli_fetch_assoc($result)) {
                            $form_list_views[] = $row;
                        }
                        
                        // loop through all of the form list views in order to add them to the database if necessary
                        foreach ($form_list_views as $form_list_view) {
                            // if the user has edit access to the form list view and the user selected it, then add it to the data
                            if ((check_folder_access_in_array($form_list_view['folder_id'], $folders_that_user_has_access_to) == TRUE) && ($_POST['form_view_directory_form_list_view_' . $form_list_view['page_id']] == 1)) {
                                $query =
                                    "INSERT INTO form_view_directories_form_list_views_xref (
                                        form_view_directory_page_id,
                                        form_list_view_page_id,
                                        form_list_view_name,
                                        subject_form_field_id)
                                    VALUES (
                                        '" . escape($_POST['id']) . "',
                                        '" . $form_list_view['page_id'] . "',
                                        '" . escape($_POST['form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name']) . "',
                                        '" . escape($_POST['form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id']) . "')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                        
                        break;
                        
                    case 'calendar view':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'default_view' => $_POST['calendar_view_default_view'],
                            'number_of_upcoming_events' => $_POST['calendar_view_number_of_upcoming_events'],
                            'calendar_event_view_page_id' => $_POST['calendar_view_calendar_event_view_page_id']
                        );
                        
                        // delete old connections between calendar view and calendars
                        $query = "DELETE FROM calendar_views_calendars_xref WHERE page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // get calendars in order to connect calendar view to calendars
                        $query =
                            "SELECT
                               id,
                               name
                            FROM calendars
                            ORDER BY name";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $calendars = array();
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $calendars[] = $row;
                        }
                        
                        // loop through calendars in order to connect calendar view to calendars
                        foreach ($calendars as $calendar) {
                            // if user has access to calendar and calendar was checked then add connection between calendar view and calendar
                            if ((validate_calendar_access($calendar['id']) == true) && ($_POST['calendar_view_calendar_' . $calendar['id']])) {
                                $query =
                                    "INSERT INTO calendar_views_calendars_xref (
                                       page_id,
                                       calendar_id)
                                    VALUES (
                                       '" . escape($_POST['id']) . "',
                                       '" . $calendar['id'] . "')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                        
                        // Get the calendar view pages default view property to see if it has been updated.
                        $query =
                            "SELECT
                               default_view
                            FROM calendar_view_pages
                            WHERE id = '" . e($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        
                        // If the default_view property was changed
                        if ($row['default_view'] != $_POST['calendar_view_default_view']) {
                            // Unset the session value.
                            unset($_SESSION['software']['calendar_views'][$_POST['id']]['view']);
                        }
                        
                        break;
                        
                    case 'calendar event view':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'notes' => $_POST['calendar_event_view_notes'],
                            'back_button_label' => $_POST['calendar_event_view_back_button_label']
                        );
                    
                        // delete old connections between calendar event view and calendars
                        $query = "DELETE FROM calendar_event_views_calendars_xref WHERE page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // get calendars in order to connect calendar event view to calendars
                        $query =
                            "SELECT
                               id,
                               name
                            FROM calendars
                            ORDER BY name";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $calendars = array();
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $calendars[] = $row;
                        }
                        
                        // loop through calendars in order to connect calendar event view to calendars
                        foreach ($calendars as $calendar) {
                            // if user has access to calendar and calendar was checked then add connection between calendar event view and calendar
                            if ((validate_calendar_access($calendar['id']) == true) && ($_POST['calendar_event_view_calendar_' . $calendar['id']])) {
                                $query =
                                    "INSERT INTO calendar_event_views_calendars_xref (
                                       page_id,
                                       calendar_id)
                                    VALUES (
                                       '" . escape($_POST['id']) . "',
                                       '" . $calendar['id'] . "')";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                        
                        break;
                        
                    case 'catalog':
                        // if number_of_columns is less than 1, then set number_of_columns to 1
                        if ($_POST['catalog_number_of_columns'] < 1) {
                            $catalog_number_of_columns = 1;
                            
                        // else number_of_columns is not less than 1, so set number_of_columns to what user entered
                        } else {
                            $catalog_number_of_columns = $_POST['catalog_number_of_columns'];
                        }
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'product_group_id' => $_POST['catalog_product_group_id'],
                            'menu' => $_POST['catalog_menu'],
                            'search' => $_POST['catalog_search'],
                            'number_of_featured_items' => $_POST['catalog_number_of_featured_items'],
                            'number_of_new_items' => $_POST['catalog_number_of_new_items'],
                            'number_of_columns' => $catalog_number_of_columns,
                            'image_width' => $_POST['catalog_image_width'],
                            'image_height' => $_POST['catalog_image_height'],
                            'back_button_label' => $_POST['catalog_back_button_label'],
                            'catalog_detail_page_id' => $_POST['catalog_catalog_detail_page_id']
                        );
                        
                        break;
                        
                    case 'catalog detail':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'allow_customer_to_add_product_to_order' => $_POST['catalog_detail_allow_customer_to_add_product_to_order'],
                            'add_button_label' => $_POST['catalog_detail_add_button_label'],
                            'next_page_id' => $_POST['catalog_detail_next_page_id'],
                            'back_button_label' => $_POST['catalog_detail_back_button_label']
                        );
                        
                        break;
                    
                    case 'express order':

                        // Get current form values before we update it, so that we know later
                        // if we should forward the user to the form designer or not.

                        $old_properties = db_item(
                            "SELECT shipping_form, form FROM express_order_pages
                            WHERE page_id = '" . e($_POST['id']) . "'");

                        $properties = array(
                            'page_id' => $_POST['id'],
                            'shopping_cart_label' => $_POST['express_order_shopping_cart_label'],
                            'quick_add_label' => $_POST['express_order_quick_add_label'],
                            'quick_add_product_group_id' => $_POST['express_order_quick_add_product_group_id'],
                            'product_description_type' => $_POST['express_order_product_description_type'],
                            'shipping_form' => $_POST['express_order_shipping_form'],
                            'special_offer_code_label' => $_POST['express_order_special_offer_code_label'],
                            'special_offer_code_message' => $_POST['express_order_special_offer_code_message'],
                            'custom_field_1_label' => $_POST['express_order_custom_field_1_label'],
                            'custom_field_1_required' => $_POST['express_order_custom_field_1_required'],
                            'custom_field_2_label' => $_POST['express_order_custom_field_2_label'],
                            'custom_field_2_required' => $_POST['express_order_custom_field_2_required'],
                            'po_number' => $_POST['express_order_po_number'],
                            'form' => $_POST['express_order_form'],
                            'form_name' => $_POST['express_order_form_name'],
                            'form_label_column_width' => $_POST['express_order_form_label_column_width'],
                            'card_verification_number_page_id' => $_POST['express_order_card_verification_number_page_id'],
                            'terms_page_id' => $_POST['express_order_terms_page_id'],
                            'update_button_label' => $_POST['express_order_update_button_label'],
                            'purchase_now_button_label' => $_POST['express_order_purchase_now_button_label'],
                            'auto_registration' => $_POST['express_order_auto_registration'],
                            'order_receipt_email' => $_POST['express_order_order_receipt_email'],
                            'order_receipt_email_subject' => $_POST['express_order_order_receipt_email_subject'],
                            'order_receipt_email_format' => $_POST['express_order_order_receipt_email_format'],
                            'order_receipt_email_header' => $_POST['express_order_order_receipt_email_header'],
                            'order_receipt_email_footer' => $_POST['express_order_order_receipt_email_footer'],
                            'order_receipt_email_page_id' => $_POST['express_order_order_receipt_email_page_id'],
                            'next_page_id' => $_POST['express_order_next_page_id']
                        );
                    
                        // if online payments is on, then update the offline payment properties.
                        if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
                            $properties['offline_payment_always_allowed'] = $_POST['express_order_offline_payment_always_allowed'];
                            $properties['offline_payment_label'] = $_POST['express_order_offline_payment_label'];
                        }

                        // If hooks are enabled and the user is a designer or administrator then prepare properties for PHP hook code.
                        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                            $properties['pre_save_hook_code'] = $_POST['express_order_pre_save_hook_code'];
                            $properties['post_save_hook_code'] = $_POST['express_order_post_save_hook_code'];
                        }
                        
                        break;
                    
                    case 'order form':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'product_group_id' => $_POST['order_form_product_group_id'],
                            'product_layout' => $_POST['order_form_product_layout'],
                            'add_button_label' => $_POST['order_form_add_button_label'],
                            'add_button_next_page_id' => $_POST['order_form_add_button_next_page_id'],
                            'skip_button_label' => $_POST['order_form_skip_button_label'],
                            'skip_button_next_page_id' => $_POST['order_form_skip_button_next_page_id']
                        );
                        
                        break;

                    case 'shopping cart':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'shopping_cart_label' => $_POST['shopping_cart_shopping_cart_label'],
                            'quick_add_label' => $_POST['shopping_cart_quick_add_label'],
                            'quick_add_product_group_id' => $_POST['shopping_cart_quick_add_product_group_id'],
                            'product_description_type' => $_POST['shopping_cart_product_description_type'],
                            'special_offer_code_label' => $_POST['shopping_cart_special_offer_code_label'],
                            'special_offer_code_message' => $_POST['shopping_cart_special_offer_code_message'],
                            'update_button_label' => $_POST['shopping_cart_update_button_label'],
                            'checkout_button_label' => $_POST['shopping_cart_checkout_button_label'],
                            'next_page_id_with_shipping' => $_POST['shopping_cart_next_page_id_with_shipping'],
                            'next_page_id_without_shipping' => $_POST['shopping_cart_next_page_id_without_shipping']
                        );
                        
                        // If hooks are enabled and the user is a designer or administrator then prepare property for PHP hook code.
                        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                            $properties['hook_code'] = $_POST['shopping_cart_hook_code'];
                        }

                        break;

                    case 'shipping address and arrival':
                        // get current form value before we update it, so that we know later if we should forward the user to the form designer or not
                        $query = "SELECT form FROM shipping_address_and_arrival_pages WHERE page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        $current_shipping_address_and_arrival_form = $row['form'];
                        
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'address_type' => $_POST['shipping_address_and_arrival_address_type'],
                            'address_type_page_id' => $_POST['shipping_address_and_arrival_address_type_page_id'],
                            'form' => $_POST['shipping_address_and_arrival_form'],
                            'form_name' => $_POST['shipping_address_and_arrival_form_name'],
                            'form_label_column_width' => $_POST['shipping_address_and_arrival_form_label_column_width'],
                            'submit_button_label' => $_POST['shipping_address_and_arrival_submit_button_label'],
                            'next_page_id' => $_POST['shipping_address_and_arrival_next_page_id']
                        );
                        
                        break;

                    case 'shipping method':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'product_description_type' => $_POST['shipping_method_product_description_type'],
                            'submit_button_label' => $_POST['shipping_method_submit_button_label'],
                            'next_page_id' => $_POST['shipping_method_next_page_id']
                        );
                        
                        break;

                    case 'billing information':
                        // Get current form value before we update it, so that we know later
                        // if we should forward the user to the form designer or not.
                        $current_billing_information_form = db_value("SELECT form FROM billing_information_pages WHERE page_id = '" . escape($_POST['id']) . "'");

                        $properties = array(
                            'page_id' => $_POST['id'],
                            'custom_field_1_label' => $_POST['billing_information_custom_field_1_label'],
                            'custom_field_1_required' => $_POST['billing_information_custom_field_1_required'],
                            'custom_field_2_label' => $_POST['billing_information_custom_field_2_label'],
                            'custom_field_2_required' => $_POST['billing_information_custom_field_2_required'],
                            'po_number' => $_POST['billing_information_po_number'],
                            'form' => $_POST['billing_information_form'],
                            'form_name' => $_POST['billing_information_form_name'],
                            'form_label_column_width' => $_POST['billing_information_form_label_column_width'],
                            'submit_button_label' => $_POST['billing_information_submit_button_label'],
                            'next_page_id' => $_POST['billing_information_next_page_id']
                        );
                        
                        break;

                    case 'order preview':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'product_description_type' => $_POST['order_preview_product_description_type'],
                            'card_verification_number_page_id' => $_POST['order_preview_card_verification_number_page_id'],
                            'terms_page_id' => $_POST['order_preview_terms_page_id'],
                            'submit_button_label' => $_POST['order_preview_submit_button_label'],
                            'auto_registration' => $_POST['order_preview_auto_registration'],
                            'order_receipt_email' => $_POST['order_preview_order_receipt_email'],
                            'order_receipt_email_subject' => $_POST['order_preview_order_receipt_email_subject'],
                            'order_receipt_email_format' => $_POST['order_preview_order_receipt_email_format'],
                            'order_receipt_email_header' => $_POST['order_preview_order_receipt_email_header'],
                            'order_receipt_email_footer' => $_POST['order_preview_order_receipt_email_footer'],
                            'order_receipt_email_page_id' => $_POST['order_preview_order_receipt_email_page_id'],
                            'next_page_id' => $_POST['order_preview_next_page_id']
                        );
                        
                        // if online payments is on, then update the offline payment properties.
                        if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
                            $properties['offline_payment_always_allowed'] = $_POST['order_preview_offline_payment_always_allowed'];
                            $properties['offline_payment_label'] = $_POST['order_preview_offline_payment_label'];
                        }

                        // If hooks are enabled and the user is a designer or administrator then prepare properties for PHP hook code.
                        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
                            $properties['pre_save_hook_code'] = $_POST['order_preview_pre_save_hook_code'];
                            $properties['post_save_hook_code'] = $_POST['order_preview_post_save_hook_code'];
                        }
                        
                        break;

                    case 'order receipt':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'product_description_type' => $_POST['order_receipt_product_description_type']
                        );
                        
                        break;
                        
                    case 'affiliate sign up form':
                        $properties = array(
                            'page_id' => $_POST['id'],
                            'terms_page_id' => $_POST['affiliate_sign_up_form_terms_page_id'],
                            'submit_button_label' => $_POST['affiliate_sign_up_form_submit_button_label'],
                            'next_page_id' => $_POST['affiliate_sign_up_form_next_page_id']
                        );
                        
                        break;
                }
                
                // if page type was changed then check if there are database records that we need to delete
                if ($current_page_type != $_POST['type']) {
                    // if current page type has a table for properties, delete page type record of properties
                    if (check_for_page_type_properties($current_page_type) == TRUE) {
                        $page_type_table_name = str_replace(' ', '_', $current_page_type) . '_pages';
                        
                        $query = "DELETE FROM $page_type_table_name WHERE page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                    
                    // if the old page type was form list view, delete form_view_directories_form_list_views_xref records
                    if ($current_page_type == 'form list view') {
                        $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_list_view_page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }

                    // if the old page type was form view directory, then delete form_view_directories_form_list_views_xref records
                    if ($current_page_type == 'form view directory') {
                        $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_view_directory_page_id = '" . escape($_POST['id']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
                
                // if new page type has a table for properties, create record in page type table
                if (check_for_page_type_properties($_POST['type']) == true) {
                    create_or_update_page_type_record($_POST['type'], $properties);
                }
                
                $sql_page_type = " page_type = '" . escape($_POST['type']) . "',";
                
            // else we cannot update the page type, so don't allow page type to be updated
            } else {
                $sql_page_type = '';
            }
            
        // else this user does not have access to change the page type property
        } else {
            $sql_page_type = '';
        }

        // If the user is allowed to change the page type property,
        // and the page type supports a layout,
        // and the user is an admin or a designer,
        // or the user just selected the system option,
        // then use the value that the user selected.
        if (
            $sql_page_type
            && check_if_page_type_supports_layout($_POST['type'])
            &&
            (
                (USER_ROLE < 2)
                || ($_POST['layout_type'] == 'system')
            )
        ) {
            $layout_type = $_POST['layout_type'];

        // Otherwise use old layout type value.
        } else {
            $layout_type = $current_layout_type;
        }
        
        // call the function that is responsible for updating the tag cloud table for pages
        update_tag_cloud_keywords_for_page($_POST['id'], $_POST['search'], $_POST['meta_keywords'], $current_page_search, $current_meta_keywords);
        
        $sql_style_fields = "";

        // if user role is Administrator, Designer, or Manager, then allow user to change desktop style and mobile style for page
        if ($user['role'] < 3) {
            $sql_style_fields =
                "page_style = '" . escape($_POST['style']) . "',
                mobile_style_id = '" . escape($_POST['mobile_style_id']) . "',";
        }
        
        // if sitemap was enabled and the selected page type is a valid page type for the sitemap,
        // then include this page in the sitemap
        if (
            ($_POST['sitemap'] == 1)
            &&
            (
                ($_POST['type'] == 'standard')
                || ($_POST['type'] == 'folder view')
                || ($_POST['type'] == 'photo gallery')
                || ($_POST['type'] == 'custom form')
                || ($_POST['type'] == 'form list view')
                || ($_POST['type'] == 'form item view')
                || ($_POST['type'] == 'form view directory')
                || ($_POST['type'] == 'calendar view')
                || ($_POST['type'] == 'calendar event view')
                || ($_POST['type'] == 'catalog')
                || ($_POST['type'] == 'catalog detail')
                || ($_POST['type'] == 'express order')
                || ($_POST['type'] == 'order form')
                || ($_POST['type'] == 'shopping cart')
                || ($_POST['type'] == 'search results')
            )
        ) {
            $sitemap = 1;
            
        // else sitemap was disabled or the selected page type is not a valid page type for the sitemap,
        // so do not include this page in the sitemap
        } else {
            $sitemap = 0;
        }
        
        $sql_seo_analysis_current = "";
        
        // if the seo analysis is current and the title or the meta description has changed, then prepare to clear current status
        if (($current_seo_analysis_current == 1) && (($current_page_title != $_POST['title']) || ($current_page_meta_description != $_POST['meta_description']))) {
            $sql_seo_analysis_current = "seo_analysis_current = '0',";
        }
        
        // update page
        $query =
            "UPDATE page
            SET
                page_name = '" . escape($name) . "',
                page_folder = '" . escape($_POST['folder']) . "',
                $sql_page_type
                layout_type = '" . e($layout_type) . "',
                $sql_home_page
                page_search = '" . escape($_POST['search']) . "',
                page_search_keywords = '" . escape($search_keywords) . "',
                page_timestamp = UNIX_TIMESTAMP(),
                page_user = '" . $user['id'] . "',
                $sql_style_fields
                page_title = '" . escape($_POST['title']) . "',
                page_meta_description = '" . escape($_POST['meta_description']) . "',
                page_meta_keywords = '" . escape($_POST['meta_keywords']) . "',
                sitemap = '" . $sitemap . "',
                $sql_seo_analysis_current
                comments = '" . escape($_POST['comments']) . "',
                comments_label = '" . e($_POST['comments_label']) . "',
                comments_message = '" . e($_POST['comments_message']) . "',
                comments_allow_new_comments = '" . escape($_POST['comments_allow_new_comments']) . "',
                comments_disallow_new_comment_message = '" . escape($_POST['comments_disallow_new_comment_message']) . "',
                comments_automatic_publish = '" . escape($_POST['comments_automatic_publish']) . "',
                comments_allow_user_to_select_name = '" . escape($_POST['comments_allow_user_to_select_name']) . "',
                comments_require_login_to_comment = '" . escape($_POST['comments_require_login_to_comment']) . "',
                comments_allow_file_attachments = '" . escape($_POST['comments_allow_file_attachments']) . "',
                comments_show_submitted_date_and_time = '" . escape($_POST['comments_show_submitted_date_and_time']) . "',
                comments_administrator_email_to_email_address = '" . escape($_POST['comments_administrator_email_to_email_address']) . "',
                comments_administrator_email_subject = '" . escape($_POST['comments_administrator_email_subject']) . "',
                comments_administrator_email_conditional_administrators = '" . escape($_POST['comments_administrator_email_conditional_administrators']) . "',
                comments_submitter_email_page_id = '" . escape($_POST['comments_submitter_email_page_id']) . "',
                comments_submitter_email_subject = '" . escape($_POST['comments_submitter_email_subject']) . "',
                comments_watcher_email_page_id = '" . escape($_POST['comments_watcher_email_page_id']) . "',
                comments_watcher_email_subject = '" . escape($_POST['comments_watcher_email_subject']) . "',
                comments_watchers_managed_by_submitter = '" . escape($_POST['comments_watchers_managed_by_submitter']) . "'
            WHERE page_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // If this page is a custom form, and pretty URLs were disabled before this update,
        // and now they are enabled, then update address names for submitted forms for pretty URLs feature.
        if (
            ($_POST['type'] == 'custom form')
            && ($pretty_urls_old == false)
            && (check_if_pretty_urls_are_enabled($_POST['id']) == true)
        ) {
            update_multiple_submitted_form_address_names($_POST['id']);
        }
        
        log_activity("page ($name) was modified", $_SESSION['sessionusername']);
        
        $send_to = $_POST['send_to'];
        
        // If there is not a send to value
        if ((isset($send_to) == FALSE) || ($send_to == '')) {
                $send_to = PATH . $name;
        }
        
        // If the page name has changed, or if the page type has been changed
        // from a page type that does not require from=control_panel to one that does
        // then update the send_to, so that it will work
        if (
            ($name != $current_page_name)
            ||
            (
                (check_if_page_type_requires_from_control_panel($current_page_type) == false)
                && (check_if_page_type_requires_from_control_panel($_POST['type']) == true)
            )
        ) {
            $query_string_from = '';

            if (check_if_page_type_requires_from_control_panel($_POST['type']) == true) {
                $query_string_from = '?from=control_panel';
            }
            
            $send_to = PATH . encode_url_path($name) . $query_string_from;
        }
        
        // If a custom form was enabled then forward user to form designer.
        if (
            (($_POST['type'] == 'custom form') && ($current_page_type != 'custom form'))
            || (($_POST['type'] == 'shipping address and arrival') && ($_POST['shipping_address_and_arrival_form'] == 1) && ($current_shipping_address_and_arrival_form != 1))
            || (($_POST['type'] == 'billing information') && ($_POST['billing_information_form'] == 1) && ($current_billing_information_form != 1))
            || (
                ($_POST['type'] == 'express order')
                and (
                    ($_POST['express_order_shipping_form'] and !$old_properties['shipping_form'])
                    or ($_POST['express_order_form'] and !$old_properties['form'])
                )
            )
        ) {

            $form_type = '';

            // If this is an express order page, then determine if we should forward to shipping
            // or billing form.
            if ($_POST['type'] == 'express order') {

                $form_type = '&form_type=';

                if ($_POST['express_order_shipping_form'] and !$old_properties['shipping_form']) {
                    $form_type .= 'shipping';
                } else {
                    $form_type .= 'billing';
                }
            }

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $_POST['id'] . $form_type . '&send_to=' . urlencode($send_to));
            
        // else we don't need to forward the user to the form designer so forward the user to the page
        } else {
            header('Location: ' . URL_SCHEME . HOSTNAME . $send_to);
        }
    }
}
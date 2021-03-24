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

$liveform_add_page = new liveform('add_page');

$user = validate_user();
validate_area_access($user, 'user');

// if the user has a user role and has create pages turned off, then output error
if (($user['role'] == '3') && ($user['create_pages'] == FALSE)) {
    log_activity("access denied because user does not have access to create pages", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (!$_POST) {
    $output_wysiwyg_editor_code = '';

    // if user role is Administrator, Designer, or Manager, then allow user to select style and mobile style for page
    if ($user['role'] < 3) {
        $output_style = '<select name="style">' . select_style() . '</select>';
        $output_mobile_style = '<select name="mobile_style_id">' . get_mobile_style_options() . '</select>';

    // else user has a user role
    } else {
        $output_style = 'Default (inherit)';
        $output_mobile_style = 'Default (inherit)';
    }

    // If the user is not an admin or designer, then disable custom layout type option.
    if (USER_ROLE > 1) {
        $layout_type_custom_label_class = ' class="disabled"';
        $layout_type_custom_option_disabled = ' disabled="disabled"';
    }
    
    // We do not know why we are setting variables below to hide rows.
    // All page type rows should be hidden by default for creating a page
    // because the page type is always "standard" when creating a page.
    // Remove these variables and put "display: none" inline when we have time.
    $email_a_friend_submit_button_label_row_style = 'display: none';
    $email_a_friend_next_page_id_row_style = 'display: none';
    $folder_view_pages_row_style = 'display: none';
    $folder_view_files_row_style = 'display: none';
    $photo_gallery_number_of_columns_row_style = 'display: none';
    $photo_gallery_thumbnail_max_size_row_style = 'display: none';
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
    $custom_form_submit_button_label_row_style = 'display: none';
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
    $custom_form_confirmation_continue_button_label_row_style = 'display: none';
    $custom_form_confirmation_next_page_id_row_style = 'display: none';
    $form_list_view_custom_form_page_id_row_style = 'display: none';
    $form_list_view_form_item_view_page_id_row_style = 'display: none';
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
    $express_order_form_row_style = 'display: none';
    $express_order_form_name_row_style = 'display: none';
    $express_order_form_label_column_width_row_style = 'display: none';
    $express_order_card_verification_number_page_id_row_style = 'display: none';
    $express_order_offline_payment_always_allowed_row_style = 'display: none';
    $express_order_offline_payment_label_row_style = 'display: none';
    $express_order_terms_page_id_row_style = 'display: none';
    $express_order_update_button_label_row_style = 'display: none';
    $express_order_purchase_now_button_label_row_style = 'display: none';
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
    
    $output_search_results_page_type_properties = '';

    // If advanced site search is enabled then output row for folder pick list
    // for search results properties.
    if (SEARCH_TYPE == 'advanced') {
        $output_search_results_page_type_properties .=
            '<tr id="search_results_search_folder_id_row" style="display: none">
                <td>Search Folder:</td>
                <td><select name="search_results_search_folder_id">' . select_folder() . '</select></td>
            </tr>';
    }

    $output_ecommerce_page_type_properties = '';
    
    if (ECOMMERCE == true) {
        // if the user is an advanced user then prepare to output search results page type properties
        if ($user['role'] < 3) {
            $output_search_results_page_type_properties .=
                '<tr id="search_results_search_catalog_items_row" style="' . $search_results_search_catalog_items_row_style . '">
                    <td>Search Products:</td>
                    <td><input type="checkbox" id="search_results_search_catalog_items" name="search_results_search_catalog_items" value="1" checked="checked" class="checkbox" onclick="show_or_hide_search_catalog_items()" /></td>
                </tr>
                <tr id="search_results_product_group_id_row" style="' . $search_results_product_group_id_row_style . '">
                    <td style="padding-left: 2em">In Product Group:</td>
                    <td><select name="search_results_product_group_id"><option value="">-Select-</option>' . get_product_group_options() . '</select> (leave unselected for all product groups)</td>
                </tr>
                <tr id="search_results_catalog_detail_page_id_row" style="' . $search_results_catalog_detail_page_id_row_style . '">
                    <td style="padding-left: 2em">Catalog Detail Page:</td>
                    <td><select name="search_results_catalog_detail_page_id"><option value="">-Select-</option>' . select_page(0, 'catalog detail') . '</select></td>
                </tr>';
        }
        
        $output_express_order_offline_payment_rows = '';
        $output_order_preview_offline_payment_rows = '';
        
        // if allow offline orders is on, then output offline payment rows.
        if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
            $output_express_order_offline_payment_rows = 
                '<tr id="express_order_offline_payment_always_allowed_row" style="' . $express_order_offline_payment_always_allowed_row_style . '">
                    <td><label for="express_order_offline_payment_always_allowed">Always Allow Offline Payments:</label></td>
                    <td><input type="checkbox" id="express_order_offline_payment_always_allowed" name="express_order_offline_payment_always_allowed" value="1" class="checkbox" /></td>
                </tr>
                <tr id="express_order_offline_payment_label_row" style="' . $express_order_offline_payment_label_row_style . '">
                    <td>Offline Payment Label:</td>
                    <td><input name="express_order_offline_payment_label" type="text" maxlength="255" /></td>
                </tr>';
            
            $output_order_preview_offline_payment_rows = 
                '<tr id="order_preview_offline_payment_always_allowed_row" style="' . $order_preview_offline_payment_always_allowed_row_style . '">
                    <td><label for="order_preview_offline_payment_always_allowed">Always Allow Offline Payments:</label></td>
                    <td><input type="checkbox" id="order_preview_offline_payment_always_allowed" name="order_preview_offline_payment_always_allowed" value="1" class="checkbox" /></td>
                </tr>
                <tr id="order_preview_offline_payment_label_row" style="' . $order_preview_offline_payment_label_row_style . '">
                    <td>Offline Payment Label:</td>
                    <td><input name="order_preview_offline_payment_label" type="text" maxlength="255" /></td>
                </tr>';
        }
        
        $output_ecommerce_page_type_properties =
            '<tr id="catalog_product_group_id_row" style="' . $catalog_product_group_id_row_style . '">
                <td>Product Group:</td>
                <td><select name="catalog_product_group_id"><option value="">-Select-</option>' . get_product_group_options($product_group_id = 0, $parent_product_group_id = 0, $excluded_product_group_id = 0, $level = 0, $product_groups = array(), $include_select_product_groups = FALSE) . '</select> (leave unselected for all product groups)</td>
            </tr>
            <tr id="catalog_menu_row" style="' . $catalog_menu_row_style . '">
                <td>Enable Menu:</td>
                <td><input type="checkbox" id="catalog_menu" name="catalog_menu" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="catalog_search_row" style="' . $catalog_search_row_style . '">
                <td>Enable Search:</td>
                <td><input type="checkbox" id="catalog_search" name="catalog_search" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="catalog_number_of_featured_items_row" style="' . $catalog_number_of_featured_items_row_style . '">
                <td>Number of Featured Items:</td>
                <td><input name="catalog_number_of_featured_items" type="text" value="0" maxlength="2" size="3" /></td>
            </tr>
            <tr id="catalog_number_of_new_items_row" style="' . $catalog_number_of_new_items_row_style . '">
                <td>Number of New Items:</td>
                <td><input name="catalog_number_of_new_items" type="text" value="0" maxlength="2" size="3" /></td>
            </tr>
            <tr id="catalog_number_of_columns_row" style="' . $catalog_number_of_columns_row_style . '">
                <td>Number of Columns:</td>
                <td><input name="catalog_number_of_columns" type="text" value="4" maxlength="2" size="3" /></td>
            </tr>
            <tr id="catalog_image_width_row" style="' . $catalog_image_width_row_style . '">
                <td>Image Width:</td>
                <td><input name="catalog_image_width" type="text" value="50" maxlength="4" size="3" /> pixels</td>
            </tr>
            <tr id="catalog_image_height_row" style="' . $catalog_image_height_row_style . '">
                <td>Image Height:</td>
                <td><input name="catalog_image_height" type="text" value="50" maxlength="4" size="3" /> pixels</td>
            </tr>
            <tr id="catalog_back_button_label_row" style="' . $catalog_back_button_label_row_style . '">
                <td>Back Button Label:</td>
                <td><input name="catalog_back_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="catalog_catalog_detail_page_id_row" style="' . $catalog_catalog_detail_page_id_row_style . '">
                <td>Catalog Detail Page:</td>
                <td><select name="catalog_catalog_detail_page_id"><option value="">-Select-</option>' . select_page(0, 'catalog detail') . '</select></td>
            </tr>
            <tr id="catalog_detail_allow_customer_to_add_product_to_order_row" style="' . $catalog_detail_allow_customer_to_add_product_to_order_row_style . '">
                <td>Allow customer to add product to order:</td>
                <td><input type="checkbox" id="catalog_detail_allow_customer_to_add_product_to_order" name="catalog_detail_allow_customer_to_add_product_to_order" value="1" checked="checked" class="checkbox" onclick="show_or_hide_allow_customer_to_add_product_to_order()" /></td>
            </tr>
            <tr id="catalog_detail_add_button_label_row" style="' . $catalog_detail_add_button_label_row_style . '">
                <td style="padding-left: 2em">Add Button Label:</td>
                <td><input name="catalog_detail_add_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="catalog_detail_next_page_id_row" style="' . $catalog_detail_next_page_id_row_style . '">
                <td style="padding-left: 2em">Next Page:</td>
                <td><select name="catalog_detail_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="catalog_detail_back_button_label_row" style="' . $catalog_detail_back_button_label_row_style . '">
                <td>Back Button Label:</td>
                <td><input name="catalog_detail_back_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="express_order_shopping_cart_label_row" style="' . $express_order_shopping_cart_label_row_style . '">
                <td>Shopping Cart Label:</td>
                <td><input name="express_order_shopping_cart_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="express_order_quick_add_label_row" style="' . $express_order_quick_add_label_row_style . '">
                <td>Quick Add Label:</td>
                <td><input name="express_order_quick_add_label" type="text" maxlength="255" /></td>
            </tr>
            <tr id="express_order_quick_add_product_group_id_row" style="' . $express_order_quick_add_product_group_id_row_style . '">
                <td>Quick Add Product Group:</td>
                <td><select name="express_order_quick_add_product_group_id"><option value="">-None-</option>' . get_product_group_options() . '</select></td>
            </tr>
            <tr id="express_order_product_description_type_row" style="' . $express_order_product_description_type_row_style . '">
                <td>Product Description Type:</td>
                <td><input type="radio" id="express_order_product_description_type_full_description" name="express_order_product_description_type" value="full_description" class="radio" checked="checked" /><label for="express_order_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="express_order_product_description_type_short_description" name="express_order_product_description_type" value="short_description" class="radio" /><label for="express_order_product_description_type_short_description">Short Description</label></td>
            </tr>
            <tr id="express_order_shipping_form_row" style="' . $express_order_shipping_form_row_style . '">
                <td><label for="express_order_shipping_form">Enable Custom Shipping Form:</label></td>
                <td>
                    <input
                        id="express_order_shipping_form"
                        name="express_order_shipping_form"
                        type="checkbox"
                        value="1"
                        class="checkbox"
                        onclick="toggle_express_order_custom_shipping_form()">
                    <script>var original_express_order_shipping_form = "0";</script>
                    <span id="express_order_shipping_form_notice" style="display: none; padding-left: 1em">
                        (when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Shipping Form.)
                    </span>
                </td>
            </tr>
            <tr id="express_order_special_offer_code_label_row" style="' . $express_order_special_offer_code_label_row_style . '">
                <td>Special Offer Code Label:</td>
                <td><input name="express_order_special_offer_code_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="express_order_special_offer_code_message_row" style="' . $express_order_special_offer_code_message_row_style . '">
                <td>Special Offer Code Message:</td>
                <td><input name="express_order_special_offer_code_message" type="text" size="50" maxlength="255" /></td>
            </tr>
            <tr id="express_order_custom_field_1_label_row" style="' . $express_order_custom_field_1_label_row_style . '">
                <td>Custom Field #1 Label:</td>
                <td><input name="express_order_custom_field_1_label" type="text" maxlength="255" /> <input type="checkbox" name="express_order_custom_field_1_required" id="express_order_custom_field_1_required" value="1" class="checkbox" /><label for="express_order_custom_field_1_required"> Required</label></td>
            </tr>
            <tr id="express_order_custom_field_2_label_row" style="' . $express_order_custom_field_2_label_row_style . '">
                <td>Custom Field #2 Label:</td>
                <td><input name="express_order_custom_field_2_label" type="text" maxlength="255" /> <input type="checkbox" name="express_order_custom_field_2_required" id="express_order_custom_field_2_required" value="1" class="checkbox" /><label for="express_order_custom_field_2_required"> Required</label></td>
            </tr>
            <tr id="express_order_po_number_row" style="' . $express_order_po_number_row_style . '">
                <td>Enable PO Number:</td>
                <td><input type="checkbox" name="express_order_po_number" value="1" class="checkbox" /></td>
            </tr>
            <tr id="express_order_form_row" style="' . $express_order_form_row_style . '">
                <td><label for="express_order_form">Enable Custom Billing Form:</label></td>
                <td><input id="express_order_form" name="express_order_form" type="checkbox" value="1" class="checkbox" onclick="show_or_hide_express_order_custom_billing_form()" /><script type="text/javascript">var original_express_order_form = "0";</script><span id="express_order_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Billing Form.)</span></td>
            </tr>
            <tr id="express_order_form_name_row" style="' . $express_order_form_name_row_style . '">
                <td style="padding-left: 2em">Form Title for Display:</td>
                <td><input id="express_order_form_name" name="express_order_form_name" type="text" size="80" maxlength="100" /></td>
            </tr>
            <tr id="express_order_form_label_column_width_row" style="' . $express_order_form_label_column_width_row_style . '">
                <td style="padding-left: 2em">Label Column Width:</td>
                <td><input id="express_order_form_label_column_width" name="express_order_form_label_column_width" type="text" size="3" maxlength="3" /> % (leave blank for auto)</td>
            </tr>
            <tr id="express_order_card_verification_number_page_id_row" style="' . $express_order_card_verification_number_page_id_row_style . '">
                <td>Card Verification Number Page:</td>
                <td><select name="express_order_card_verification_number_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            ' . $output_express_order_offline_payment_rows . '
            <tr id="express_order_terms_page_id_row" style="' . $express_order_terms_page_id_row_style . '">
                <td>Terms Page:</td>
                <td><select name="express_order_terms_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="express_order_update_button_label_row" style="' . $express_order_update_button_label_row_style . '">
                <td>Update Button Label:</td>
                <td><input name="express_order_update_button_label" type="text" value="Update Cart" maxlength="50" /></td>
            </tr>
            <tr id="express_order_purchase_now_button_label_row" style="' . $express_order_purchase_now_button_label_row_style . '">
                <td>Purchase Now Button Label:</td>
                <td><input name="express_order_purchase_now_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="express_order_auto_registration_row" style="display: none">
                <td><label for="express_order_auto_registration">Enable Auto-Registration:</label></td>
                <td><input type="checkbox" id="express_order_auto_registration" name="express_order_auto_registration" value="1" class="checkbox"></td>
            </tr>';

        // If hooks are enabled and the user is a designer or administrator then output hook rows for PHP code.
        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
            $output_ecommerce_page_type_properties .=
                '<tr id="express_order_pre_save_hook_code_row" style="' . $express_order_pre_save_hook_code_row_style . '">
                    <td>Pre-Save Hook Code:</td>
                    <td><textarea name="express_order_pre_save_hook_code" rows="5" cols="70"></textarea></td>
                </tr>
                <tr id="express_order_post_save_hook_code_row" style="' . $express_order_post_save_hook_code_row_style . '">
                    <td>Post-Save Hook Code:</td>
                    <td><textarea name="express_order_post_save_hook_code" rows="5" cols="70"></textarea></td>
                </tr>';
        }

        $output_ecommerce_page_type_properties .=
            '<tr id="express_order_order_receipt_email_row" style="' . $express_order_order_receipt_email_row_style . '">
                <td><label for="express_order_order_receipt_email">E-mail Order Receipt:</label></td>
                <td><input type="checkbox" id="express_order_order_receipt_email" name="express_order_order_receipt_email" value="1" checked="checked" class="checkbox" onclick="show_or_hide_express_order_order_receipt_email()" /></td>
            </tr>
            <tr id="express_order_order_receipt_email_subject_row" style="' . $express_order_order_receipt_email_subject_row_style . '">
                <td style="padding-left: 2em">Subject:</td>
                <td><input name="express_order_order_receipt_email_subject" value="Order Receipt #" type="text" size="80" maxlength="255" /></td>
            </tr>
            <tr id="express_order_order_receipt_email_format_row" style="' . $express_order_order_receipt_email_format_row_style . '">
                <td style="padding-left: 2em">Format:</td>
                <td><input type="radio" id="express_order_order_receipt_email_format_plain_text" name="express_order_order_receipt_email_format" value="plain_text" class="radio" checked="checked" onclick="show_or_hide_express_order_order_receipt_email_format()" /><label for="express_order_order_receipt_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="express_order_order_receipt_email_format_html" name="express_order_order_receipt_email_format" value="html" class="radio" onclick="show_or_hide_express_order_order_receipt_email_format()" /><label for="express_order_order_receipt_email_format_html">HTML</label></td>
            </tr>
            <tr id="express_order_order_receipt_email_header_row" style="' . $express_order_order_receipt_email_header_row_style . '">
                <td style="padding-left: 2em">Header:</td>
                <td><textarea name="express_order_order_receipt_email_header" rows="5" cols="70">Order Receipt</textarea></td>
            </tr>
            <tr id="express_order_order_receipt_email_footer_row" style="' . $express_order_order_receipt_email_footer_row_style . '">
                <td style="padding-left: 2em">Footer:</td>
                <td><textarea name="express_order_order_receipt_email_footer" rows="5" cols="70"></textarea></td>
            </tr>
            <tr id="express_order_order_receipt_email_page_id_row" style="' . $express_order_order_receipt_email_page_id_row_style . '">
                <td style="padding-left: 2em">Page:</td>
                <td><select name="express_order_order_receipt_email_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page(0, 'order receipt') . '</select></td>
            </tr>
            <tr id="express_order_next_page_id_row" style="' . $express_order_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="express_order_next_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page(0, 'order receipt') . '</select></td>
            </tr>
            <tr id="order_form_product_group_id_row" style="' . $order_form_product_group_id_row_style . '">
                <td>Product Group:</td>
                <td><select name="order_form_product_group_id"><option value="">-Select-</option>' . get_product_group_options() . '</select></td>
            </tr>
            <tr id="order_form_product_layout_row_1" style="' . $order_form_product_layout_row_1_style . '">
                <td>Product Layout:</td>
                <td><input type="radio" id="order_form_product_layout_list" name="order_form_product_layout" value="list" class="radio" checked="checked" /><label for="order_form_product_layout_list">List (full description)</label></td>
            </tr>
            <tr id="order_form_product_layout_row_2" style="' . $order_form_product_layout_row_2_style . '">
                <td>&nbsp;</td>
                <td><input type="radio" id="order_form_product_layout_drop_down_selection" name="order_form_product_layout" value="drop-down selection" class="radio" /><label for="order_form_product_layout_drop_down_selection">Drop-Down Selection (short description)</label></td>
            </tr>
            <tr id="order_form_add_button_label_row" style="' . $order_form_add_button_label_row_style . '">
                <td>Add Button Label:</td>
                <td><input name="order_form_add_button_label" type="text" value="Continue" maxlength="50" /></td>
            </tr>
            <tr id="order_form_add_button_next_page_id_row" style="' . $order_form_add_button_next_page_id_row_style . '">
                <td style="padding-left: 20px">Next Page:</td>
                <td><select name="order_form_add_button_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="order_form_skip_button_label_row" style="' . $order_form_skip_button_label_row_style . '">
                <td>Skip Button Label:</td>
                <td><input name="order_form_skip_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="order_form_skip_button_next_page_id_row" style="' . $order_form_skip_button_next_page_id_row_style . '">
                <td style="padding-left: 20px">Next Page:</td>
                <td><select name="order_form_skip_button_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="shopping_cart_shopping_cart_label_row" style="' . $shopping_cart_shopping_cart_label_row_style . '">
                <td>Shopping Cart Label:</td>
                <td><input name="shopping_cart_shopping_cart_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="shopping_cart_quick_add_label_row" style="' . $shopping_cart_quick_add_label_row_style . '">
                <td>Quick Add Label:</td>
                <td><input name="shopping_cart_quick_add_label" type="text" maxlength="255" /></td>
            </tr>
            <tr id="shopping_cart_quick_add_product_group_id_row" style="' . $shopping_cart_quick_add_product_group_id_row_style . '">
                <td>Quick Add Product Group:</td>
                <td><select name="shopping_cart_quick_add_product_group_id"><option value="">-None-</option>' . get_product_group_options() . '</select></td>
            </tr>
            <tr id="shopping_cart_product_description_type_row" style="' . $shopping_cart_product_description_type_row_style . '">
                <td>Product Description Type:</td>
                <td><input type="radio" id="shopping_cart_product_description_type_full_description" name="shopping_cart_product_description_type" value="full_description" class="radio" checked="checked" /><label for="shopping_cart_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="shopping_cart_product_description_type_short_description" name="shopping_cart_product_description_type" value="short_description" class="radio" /><label for="shopping_cart_product_description_type_short_description">Short Description</label></td>
            </tr>
            <tr id="shopping_cart_special_offer_code_label_row" style="' . $shopping_cart_special_offer_code_label_row_style . '">
                <td>Special Offer Code Label:</td>
                <td><input name="shopping_cart_special_offer_code_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="shopping_cart_special_offer_code_message_row" style="' . $shopping_cart_special_offer_code_message_row_style . '">
                <td>Special Offer Code Message:</td>
                <td><input name="shopping_cart_special_offer_code_message" type="text" size="50" maxlength="255" /></td>
            </tr>
            <tr id="shopping_cart_update_button_label_row" style="' . $shopping_cart_update_button_label_row_style . '">
                <td>Update Button Label:</td>
                <td><input name="shopping_cart_update_button_label" type="text" value="Update Cart" maxlength="50" /></td>
            </tr>
            <tr id="shopping_cart_checkout_button_label_row" style="' . $shopping_cart_checkout_button_label_row_style . '">
                <td>Checkout Button Label:</td>
                <td><input name="shopping_cart_checkout_button_label" type="text" value="Checkout" maxlength="50" /></td>
            </tr>';

        // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
            $output_ecommerce_page_type_properties .=
                '<tr id="shopping_cart_hook_code_row" style="' . $shopping_cart_hook_code_row_style . '">
                    <td>Hook Code:</td>
                    <td><textarea name="shopping_cart_hook_code" rows="5" cols="70"></textarea></td>
                </tr>';
        }


        $output_ecommerce_page_type_properties .=
            '<tr id="shopping_cart_next_page_id_with_shipping_row" style="' . $shopping_cart_next_page_id_with_shipping_row_style . '">
                <td>Next Page (with shipping):</td>
                <td><select name="shopping_cart_next_page_id_with_shipping"><option value="">-Select Shipping Address &amp; Arrival or Express Order Page-</option>' . select_page(0, array('shipping address and arrival', 'express order')) . '</select></td>
            </tr>
            <tr id="shopping_cart_next_page_id_without_shipping_row" style="' . $shopping_cart_next_page_id_without_shipping_row_style . '">
                <td>Next Page (without shipping):</td>
                <td><select name="shopping_cart_next_page_id_without_shipping"><option value="">-Select Billing Information or Express Order Page-</option>' . select_page(0, array('billing information', 'express order')) . '</select></td>
            </tr>
            <tr id="shipping_address_and_arrival_address_type_row" style="' . $shipping_address_and_arrival_address_type_row_style . '">
                <td><label for="shipping_address_and_arrival_address_type">Enable Address Type:</label></td>
                <td><input id="shipping_address_and_arrival_address_type" name="shipping_address_and_arrival_address_type" type="checkbox" value="1" class="checkbox" onclick="show_or_hide_shipping_address_and_arrival_address_type()" /></td>
            </tr>
            <tr id="shipping_address_and_arrival_address_type_page_id_row" style="' . $shipping_address_and_arrival_address_type_page_id_row_style . '">
                <td style="padding-left: 2em">Address Type Page:</td>
                <td><select name="shipping_address_and_arrival_address_type_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="shipping_address_and_arrival_form_row" style="' . $shipping_address_and_arrival_form_row_style . '">
                <td><label for="shipping_address_and_arrival_form">Enable Custom Shipping Form:</label></td>
                <td><input id="shipping_address_and_arrival_form" name="shipping_address_and_arrival_form" type="checkbox" value="1" class="checkbox" onclick="show_or_hide_custom_shipping_form()" /><script type="text/javascript">var original_shipping_address_and_arrival_form = "0";</script><span id="shipping_address_and_arrival_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Shipping Form.)</span></td>
            </tr>
            <tr id="shipping_address_and_arrival_form_name_row" style="' . $shipping_address_and_arrival_form_name_row_style . '">
                <td style="padding-left: 2em">Form Title for Display:</td>
                <td><input id="shipping_address_and_arrival_form_name" name="shipping_address_and_arrival_form_name" type="text" size="80" maxlength="100" /></td>
            </tr>
            <tr id="shipping_address_and_arrival_form_label_column_width_row" style="' . $shipping_address_and_arrival_form_label_column_width_row_style . '">
                <td style="padding-left: 2em">Label Column Width:</td>
                <td><input id="shipping_address_and_arrival_form_label_column_width" name="shipping_address_and_arrival_form_label_column_width" type="text" size="3" maxlength="3" /> % (leave blank for auto)</td>
            </tr>
            <tr id="shipping_address_and_arrival_submit_button_label_row" style="' . $shipping_address_and_arrival_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="shipping_address_and_arrival_submit_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="shipping_address_and_arrival_next_page_id_row" style="' . $shipping_address_and_arrival_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="shipping_address_and_arrival_next_page_id"><option value="">-Select Shipping Method Page-</option>' . select_page(0, 'shipping method') . '</select></td>
            </tr>
            <tr id="shipping_method_product_description_type_row" style="' . $shipping_method_product_description_type_row_style . '">
                <td>Product Description Type:</td>
                <td><input type="radio" id="shipping_method_product_description_type_full_description" name="shipping_method_product_description_type" value="full_description" class="radio" checked="checked" /><label for="shipping_method_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="shipping_method_product_description_type_short_description" name="shipping_method_product_description_type" value="short_description" class="radio" /><label for="shipping_method_product_description_type_short_description">Short Description</label></td>
            </tr>
            <tr id="shipping_method_submit_button_label_row" style="' . $shipping_method_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="shipping_method_submit_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="shipping_method_next_page_id_row" style="' . $shipping_method_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="shipping_method_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="billing_information_custom_field_1_label_row" style="' . $billing_information_custom_field_1_label_row_style . '">
                <td>Custom Field #1 Label:</td>
                <td><input name="billing_information_custom_field_1_label" type="text" maxlength="255" /> <input type="checkbox" name="billing_information_custom_field_1_required" id="billing_information_custom_field_1_required" value="1" class="checkbox" /><label for="billing_information_custom_field_1_required"> Required</label></td>
            </tr>
            <tr id="billing_information_custom_field_2_label_row" style="' . $billing_information_custom_field_2_label_row_style . '">
                <td>Custom Field #2 Label:</td>
                <td><input name="billing_information_custom_field_2_label" type="text" maxlength="255" /> <input type="checkbox" name="billing_information_custom_field_2_required" id="billing_information_custom_field_2_required" value="1" class="checkbox" /><label for="billing_information_custom_field_2_required"> Required</label></td>
            </tr>
            <tr id="billing_information_po_number_row" style="' . $billing_information_po_number_row_style . '">
                <td>Enable PO Number:</td>
                <td><input type="checkbox" name="billing_information_po_number" value="1" class="checkbox" /></td>
            </tr>
            <tr id="billing_information_form_row" style="' . $billing_information_form_row_style . '">
                <td><label for="billing_information_form">Enable Custom Billing Form:</label></td>
                <td><input id="billing_information_form" name="billing_information_form" type="checkbox" value="1" class="checkbox" onclick="show_or_hide_billing_information_custom_billing_form()" /><script type="text/javascript">var original_billing_information_form = "0";</script><span id="billing_information_form_notice" style="display: none; padding-left: 1em">(when ready, click "Save &amp; Continue" at the bottom of this screen to create the Custom Billing Form.)</span></td>
            </tr>
            <tr id="billing_information_form_name_row" style="' . $billing_information_form_name_row_style . '">
                <td style="padding-left: 2em">Form Title for Display:</td>
                <td><input id="billing_information_form_name" name="billing_information_form_name" type="text" size="80" maxlength="100" /></td>
            </tr>
            <tr id="billing_information_form_label_column_width_row" style="' . $billing_information_form_label_column_width_row_style . '">
                <td style="padding-left: 2em">Label Column Width:</td>
                <td><input id="billing_information_form_label_column_width" name="billing_information_form_label_column_width" type="text" size="3" maxlength="3" /> % (leave blank for auto)</td>
            </tr>
            <tr id="billing_information_submit_button_label_row" style="' . $billing_information_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="billing_information_submit_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="billing_information_next_page_id_row" style="' . $billing_information_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="billing_information_next_page_id"><option value="">-Select Order Preview or Express Order Page-</option>' . select_page(0, 'order preview') . select_page(0, 'express order') . '</select></td>
            </tr>
            <tr id="order_preview_product_description_type_row" style="' . $order_preview_product_description_type_row_style . '">
                <td>Product Description Type:</td>
                <td><input type="radio" id="order_preview_product_description_type_full_description" name="order_preview_product_description_type" value="full_description" class="radio" checked="checked" /><label for="order_preview_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="order_preview_product_description_type_short_description" name="order_preview_product_description_type" value="short_description" class="radio" /><label for="order_preview_product_description_type_short_description">Short Description</label></td>
            </tr>
            <tr id="order_preview_card_verification_number_page_id_row" style="' . $order_preview_card_verification_number_page_id_row_style . '">
                <td>Card Verification Number Page:</td>
                <td><select name="order_preview_card_verification_number_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            ' . $output_order_preview_offline_payment_rows . '
            <tr id="order_preview_terms_page_id_row" style="' . $order_preview_terms_page_id_row_style . '">
                <td>Terms Page:</td>
                <td><select name="order_preview_terms_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="order_preview_submit_button_label_row" style="' . $order_preview_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="order_preview_submit_button_label" type="text" maxlength="50" /></td>
            </tr>
            <tr id="order_preview_auto_registration_row" style="display: none">
                <td><label for="order_preview_auto_registration">Enable Auto-Registration:</label></td>
                <td><input type="checkbox" id="order_preview_auto_registration" name="order_preview_auto_registration" value="1" class="checkbox"></td>
            </tr>';

        // If hooks are enabled and the user is a designer or administrator then output hook rows for PHP code.
        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
            $output_ecommerce_page_type_properties .=
                '<tr id="order_preview_pre_save_hook_code_row" style="' . $order_preview_pre_save_hook_code_row_style . '">
                    <td>Pre-Save Hook Code:</td>
                    <td><textarea name="order_preview_pre_save_hook_code" rows="5" cols="70"></textarea></td>
                </tr>
                <tr id="order_preview_post_save_hook_code_row" style="' . $order_preview_post_save_hook_code_row_style . '">
                    <td>Post-Save Hook Code:</td>
                    <td><textarea name="order_preview_post_save_hook_code" rows="5" cols="70"></textarea></td>
                </tr>';
        }

        $output_ecommerce_page_type_properties .=
            '<tr id="order_preview_order_receipt_email_row" style="' . $order_preview_order_receipt_email_row_style . '">
                <td><label for="order_preview_order_receipt_email">E-mail Order Receipt:</label></td>
                <td><input type="checkbox" id="order_preview_order_receipt_email" name="order_preview_order_receipt_email" value="1" checked="checked" class="checkbox" onclick="show_or_hide_order_preview_order_receipt_email()" /></td>
            </tr>
            <tr id="order_preview_order_receipt_email_subject_row" style="' . $order_preview_order_receipt_email_subject_row_style . '">
                <td style="padding-left: 2em">Subject:</td>
                <td><input name="order_preview_order_receipt_email_subject" value="Order Receipt #" type="text" size="80" maxlength="255" /></td>
            </tr>
            <tr id="order_preview_order_receipt_email_format_row" style="' . $order_preview_order_receipt_email_format_row_style . '">
                <td style="padding-left: 2em">Format:</td>
                <td><input type="radio" id="order_preview_order_receipt_email_format_plain_text" name="order_preview_order_receipt_email_format" value="plain_text" class="radio" checked="checked" onclick="show_or_hide_order_preview_order_receipt_email_format()" /><label for="order_preview_order_receipt_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="order_preview_order_receipt_email_format_html" name="order_preview_order_receipt_email_format" value="html" class="radio" onclick="show_or_hide_order_preview_order_receipt_email_format()" /><label for="order_preview_order_receipt_email_format_html">HTML</label></td>
            </tr>
            <tr id="order_preview_order_receipt_email_header_row" style="' . $order_preview_order_receipt_email_header_row_style . '">
                <td style="padding-left: 2em">Header:</td>
                <td><textarea name="order_preview_order_receipt_email_header" rows="5" cols="70">Order Receipt</textarea></td>
            </tr>
            <tr id="order_preview_order_receipt_email_footer_row" style="' . $order_preview_order_receipt_email_footer_row_style . '">
                <td style="padding-left: 2em">Footer:</td>
                <td><textarea name="order_preview_order_receipt_email_footer" rows="5" cols="70"></textarea></td>
            </tr>
            <tr id="order_preview_order_receipt_email_page_id_row" style="' . $order_preview_order_receipt_email_page_id_row_style . '">
                <td style="padding-left: 2em">Page:</td>
                <td><select name="order_preview_order_receipt_email_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page(0, 'order receipt') . '</select></td>
            </tr>
            <tr id="order_preview_next_page_id_row" style="' . $order_preview_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="order_preview_next_page_id"><option value="">-Select Order Receipt Page-</option>' . select_page(0, 'order receipt') . '</select></td>
            </tr>
            <tr id="order_receipt_product_description_type_row" style="' . $order_receipt_product_description_type_row_style . '">
                <td>Product Description Type:</td>
                <td><input type="radio" id="order_receipt_product_description_type_full_description" name="order_receipt_product_description_type" value="full_description" class="radio" checked="checked" /><label for="order_receipt_product_description_type_full_description">Full Description</label> &nbsp;<input type="radio" id="order_receipt_product_description_type_short_description" name="order_receipt_product_description_type" value="short_description" class="radio" /><label for="order_receipt_product_description_type_short_description">Short Description</label></td>
            </tr>';
    }

    if (FORMS == true) {
        $output_wysiwyg_editor_code = get_wysiwyg_editor_code(array('custom_form_confirmation_message', 'custom_form_return_message'), $activate_editors = false);

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
                $output_form_view_directory_subject_field_options = '';
                
                // loop through the fields for this form list view's custom form, in order to prepare to output subject field options
                foreach ($custom_forms[$form_list_view['custom_form_page_id']]['form_fields'] as $form_field) {
                    $output_form_view_directory_subject_field_options .= '<option value="' . $form_field['id'] . '">' . h($form_field['name']) . '</option>';
                }
                
                $output_form_view_directory_form_list_view_rows .=
                    '<tr>
                        <td><input type="checkbox" name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '" id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '" value="1" class="checkbox" onclick="show_or_hide_form_view_directory_form_list_view(' . $form_list_view['page_id'] . ')" /><label for="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '"> ' . h($form_list_view['page_name']) . '</label></td>
                        <td><div id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name_container" style="display: none">Name: <input name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name" type="text" size="30" maxlength="100" /></div></td>
                        <td><div id="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id_container" style="display: none">Subject Field: <select name="form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id"><option value=""></option>' . $output_form_view_directory_subject_field_options . '</select></div></td>
                    </tr>';
            }
        }
        
        $output_forms_page_type_properties =
            '<tr id="custom_form_form_name_row" style="' . $custom_form_form_name_row_style . '">
                <td>Form Name:</td>
                <td><input name="custom_form_form_name" type="text" size="30" maxlength="100" /></td>
            </tr>
            <tr id="custom_form_enabled_row" style="' . $custom_form_enabled_row_style . '">
                <td>Enable Form:</td>
                <td><input type="checkbox" name="custom_form_enabled" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="custom_form_quiz_row" style="' . $custom_form_quiz_row_style . '">
                <td>Enable Quiz:</td>
                <td><input type="checkbox" id="custom_form_quiz" name="custom_form_quiz" value="1" class="checkbox" onclick="show_or_hide_quiz()" /></td>
            </tr>
            <tr id="custom_form_quiz_pass_percentage_row" style="' . $custom_form_quiz_pass_percentage_row_style . '">
                <td style="padding-left: 2em">Quiz Pass Percentage:</td>
                <td><input name="custom_form_quiz_pass_percentage" type="text" size="3" maxlength="3" /> %</td>
            </tr>
            <tr id="custom_form_label_column_width_row" style="' . $custom_form_label_column_width_row_style . '">
                <td>Label Column Width:</td>
                <td><input name="custom_form_label_column_width" type="text" size="3" maxlength="3" /> % (leave blank for auto)</td>
            </tr>
            <tr id="custom_form_watcher_page_id_row" style="' . $custom_form_watcher_page_id_row_style . '">
                <td>Enable Watcher Option:</td>
                <td><select name="custom_form_watcher_page_id"><option value="">-Select Form Item View Page-</option>' . select_page(0, 'form item view') . '</select></td>
            </tr>
            <tr id="custom_form_save_row" style="display: none">
                <td><label for="custom_form_save">Enable Save-for-Later:</label></td>
                <td><input type="checkbox" id="custom_form_save" name="custom_form_save" value="1" class="checkbox"></td>
            </tr>
            <tr id="custom_form_submit_button_label_row" style="' . $custom_form_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="custom_form_submit_button_label" type="text" size="30" maxlength="50" /></td>
            </tr>
            <tr id="custom_form_auto_registration_row" style="display: none">
                <td><label for="custom_form_auto_registration">Enable Auto-Registration:</label></td>
                <td><input type="checkbox" id="custom_form_auto_registration" name="custom_form_auto_registration" value="1" class="checkbox"></td>
            </tr>';

        // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
            $output_forms_page_type_properties .=
                '<tr id="custom_form_hook_code_row" style="' . $custom_form_hook_code_row_style . '">
                    <td>Hook Code:</td>
                    <td><textarea name="custom_form_hook_code" rows="5" cols="70"></textarea></td>
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
                <td><input type="checkbox" id="custom_form_submitter_email" name="custom_form_submitter_email" value="1" class="checkbox" onclick="show_or_hide_custom_form_submitter_email()" /></td>
            </tr>
            <tr id="custom_form_submitter_email_from_email_address_row" style="' . $custom_form_submitter_email_from_email_address_row_style . '">
                <td style="padding-left: 2em">From E-mail Address:</td>
                <td><input name="custom_form_submitter_email_from_email_address" type="text" size="30" maxlength="100" /></td>
            </tr>
            <tr id="custom_form_submitter_email_subject_row" style="' . $custom_form_submitter_email_subject_row_style . '">
                <td style="padding-left: 2em">Subject:</td>
                <td><input name="custom_form_submitter_email_subject" type="text" size="80" maxlength="255" /></td>
            </tr>
            <tr id="custom_form_submitter_email_format_row" style="' . $custom_form_submitter_email_format_row_style . '">
                <td style="padding-left: 2em">Format:</td>
                <td><input type="radio" id="custom_form_submitter_email_format_plain_text" name="custom_form_submitter_email_format" value="plain_text" class="radio" checked="checked" onclick="show_or_hide_custom_form_submitter_email_format()" /><label for="custom_form_submitter_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="custom_form_submitter_email_format_html" name="custom_form_submitter_email_format" value="html" class="radio" onclick="show_or_hide_custom_form_submitter_email_format()" /><label for="custom_form_submitter_email_format_html">HTML</label></td>
            </tr>
            <tr id="custom_form_submitter_email_body_row" style="' . $custom_form_submitter_email_body_row_style . '">
                <td style="padding-left: 2em">Body:</td>
                <td><textarea name="custom_form_submitter_email_body" rows="10" cols="70"></textarea></td>
            </tr>
            <tr id="custom_form_submitter_email_page_id_row" style="' . $custom_form_submitter_email_page_id_row_style . '">
                <td style="padding-left: 2em">Page:</td>
                <td><select name="custom_form_submitter_email_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_administrator_email_row" style="' . $custom_form_administrator_email_row_style . '">
                <td><label for="custom_form_administrator_email">E-mail Administrator:</label></td>
                <td><input type="checkbox" id="custom_form_administrator_email" name="custom_form_administrator_email" value="1" class="checkbox" onclick="show_or_hide_custom_form_administrator_email()" /></td>
            </tr>
            <tr id="custom_form_administrator_email_to_email_address_row" style="' . $custom_form_administrator_email_to_email_address_row_style . '">
                <td style="padding-left: 2em">To E-mail Address:</td>
                <td><input name="custom_form_administrator_email_to_email_address" type="text" size="30" maxlength="100" /></td>
            </tr>
            <tr id="custom_form_administrator_email_bcc_email_address_row" style="' . $custom_form_administrator_email_bcc_email_address_row_style . '">
                <td style="padding-left: 2em">BCC E-mail Address:</td>
                <td><input name="custom_form_administrator_email_bcc_email_address" type="text" size="30" maxlength="100" /></td>
            </tr>
            <tr id="custom_form_administrator_email_subject_row" style="' . $custom_form_administrator_email_subject_row_style . '">
                <td style="padding-left: 2em">Subject:</td>
                <td><input name="custom_form_administrator_email_subject" type="text" size="80" maxlength="255" /></td>
            </tr>
            <tr id="custom_form_administrator_email_format_row" style="' . $custom_form_administrator_email_format_row_style . '">
                <td style="padding-left: 2em">Format:</td>
                <td><input type="radio" id="custom_form_administrator_email_format_plain_text" name="custom_form_administrator_email_format" value="plain_text" class="radio" checked="checked" onclick="show_or_hide_custom_form_administrator_email_format()" /><label for="custom_form_administrator_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="custom_form_administrator_email_format_html" name="custom_form_administrator_email_format" value="html" class="radio" onclick="show_or_hide_custom_form_administrator_email_format()" /><label for="custom_form_administrator_email_format_html">HTML</label></td>
            </tr>
            <tr id="custom_form_administrator_email_body_row" style="' . $custom_form_administrator_email_body_row_style . '">
                <td style="padding-left: 2em">Body:</td>
                <td><textarea name="custom_form_administrator_email_body" rows="10" cols="70"></textarea></td>
            </tr>
            <tr id="custom_form_administrator_email_page_id_row" style="' . $custom_form_administrator_email_page_id_row_style . '">
                <td style="padding-left: 2em">Page:</td>
                <td><select name="custom_form_administrator_email_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_contact_group_id_row" style="' . $custom_form_contact_group_id_row_style . '">
                <td>Add to Contact Group:</td>
                <td><select name="custom_form_contact_group_id"><option value="">-None-</option>' . select_contact_group(0, $user) . '</select></td>
            </tr>
            <tr id="custom_form_membership_row" style="' . $custom_form_membership_row_style . '">
                <td><label for="custom_form_membership">Grant Membership Trial:</label></td>
                <td><input type="checkbox" id="custom_form_membership" name="custom_form_membership" value="1" class="checkbox" onclick="show_or_hide_custom_form_membership()" /></td>
            </tr>
            <tr id="custom_form_membership_days_row" style="' . $custom_form_membership_days_row_style . '">
                <td style="padding-left: 2em">Trial Length:</td>
                <td><input name="custom_form_membership_days" type="text" size="3" maxlength="9" /> day(s)</td>
            </tr>
            <tr id="custom_form_membership_start_page_id_row" style="' . $custom_form_membership_start_page_id_row_style . '">
                <td style="padding-left: 2em">Set Member\'s Start Page to:</td>
                <td><select name="custom_form_membership_start_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_private_row" style="display: none">
                <td><label for="custom_form_private">Grant Private Access:</label></td>
                <td><input type="checkbox" id="custom_form_private" name="custom_form_private" value="1" class="checkbox" onclick="toggle_custom_form_private()" /></td>
            </tr>
            <tr id="custom_form_private_folder_id_row" style="display: none">
                <td style="padding-left: 2em">Set "View" Access to Folder:</td>
                <td><select name="custom_form_private_folder_id"><option value=""></option>' . select_folder(0, 0, 0, 0, array(), array(), 'private') . '</select></td>
            </tr>
            <tr id="custom_form_private_days_row" style="display: none">
                <td style="padding-left: 2em">Length:</td>
                <td><input name="custom_form_private_days" type="text" size="3" maxlength="9" /> day(s) (leave blank for no expiration)</td>
            </tr>
            <tr id="custom_form_private_start_page_id_row" style="display: none">
                <td style="padding-left: 2em">Set User\'s Start Page to:</td>
                <td><select name="custom_form_private_start_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>';

        // If commerce is enabled and the user has access to commerce, then output grant offer rows.
        if ((ECOMMERCE) && (USER_MANAGE_ECOMMERCE)) {
            $output_forms_page_type_properties .=
                '<tr id="custom_form_offer_row" style="display: none">
                    <td><label for="custom_form_offer">Grant Offer:</label></td>
                    <td><input type="checkbox" id="custom_form_offer" name="custom_form_offer" value="1" class="checkbox" onclick="toggle_custom_form_offer()"></td>
                </tr>
                <tr id="custom_form_offer_id_row" style="display: none">
                    <td style="padding-left: 2em"><label for="custom_form_offer_id">Offer:</label></td>
                    <td><select name="custom_form_offer_id"><option value=""></option>' . select_offer() . '</select></td>
                </tr>
                <tr id="custom_form_offer_days_row" style="display: none">
                    <td style="padding-left: 2em"><label for="custom_form_offer_days">Validity Length:</label></td>
                    <td><input name="custom_form_offer_days" type="text" size="3" maxlength="9" /> day(s) (leave blank for no expiration)</td>
                </tr>
                <tr id="custom_form_offer_eligibility_row" style="display: none">
                    <td style="padding-left: 2em"><label for="custom_form_offer_eligibility">Eligibility:</label></td>
                    <td><select name="custom_form_offer_eligibility"><option value="everyone">Everyone</option><option value="new_contacts">New Contacts</option><option value="existing_contacts">Existing Contacts</option></select></td>
                </tr>';
        }

        $output_forms_page_type_properties .=
            '<tr id="custom_form_confirmation_type_row" style="display: none">
                <td>Confirmation Type:</td>
                <td><input type="radio" id="custom_form_confirmation_type_message" name="custom_form_confirmation_type" value="message" class="radio" checked="checked" onclick="show_or_hide_custom_form_confirmation_type()" /><label for="custom_form_confirmation_type_message">Message</label> &nbsp;<input type="radio" id="custom_form_confirmation_type_page" name="custom_form_confirmation_type" value="page" class="radio" onclick="show_or_hide_custom_form_confirmation_type()" /><label for="custom_form_confirmation_type_page">Next Page</label></td>
            </tr>
            <tr id="custom_form_confirmation_message_row" style="display: none">
                <td style="padding-left: 2em">Message:</td>
                <td><textarea id="custom_form_confirmation_message" name="custom_form_confirmation_message" rows="15" cols="80"></textarea></td>
            </tr>
            <tr id="custom_form_confirmation_page_id_row" style="display: none">
                <td style="padding-left: 2em">Next Page:</td>
                <td><select name="custom_form_confirmation_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_confirmation_alternative_page_row" style="display: none">
                <td style="padding-left: 2em"><label for="custom_form_confirmation_alternative_page">Alternative Next Page:</label></td>
                <td><input type="checkbox" id="custom_form_confirmation_alternative_page" name="custom_form_confirmation_alternative_page" value="1" class="checkbox" onclick="show_or_hide_custom_form_confirmation_alternative_page()" /></td>
            </tr>
            <tr id="custom_form_confirmation_alternative_page_contact_group_id_row" style="display: none">
                <td style="padding-left: 4em">If Contact Group:</td>
                <td><select name="custom_form_confirmation_alternative_page_contact_group_id"><option value=""></option>' . select_contact_group(0, $user) . '</select></td>
            </tr>
            <tr id="custom_form_confirmation_alternative_page_id_row" style="display: none">
                <td style="padding-left: 4em">Then Go to Page:</td>
                <td><select name="custom_form_confirmation_alternative_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_return_type_row" style="display: none">
                <td>If User has already submitted<br />form in the past, then show:</td>
                <td>
                    <input type="radio" id="custom_form_return_type_custom_form" name="custom_form_return_type" value="custom_form" class="radio" checked="checked" onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_custom_form">Custom Form</label><br />
                    <input type="radio" id="custom_form_return_type_message" name="custom_form_return_type" value="message" class="radio" onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_message">Message</label><br />
                    <input type="radio" id="custom_form_return_type_page" name="custom_form_return_type" value="page" class="radio" onclick="show_or_hide_custom_form_return_type()" /><label for="custom_form_return_type_page">Page</label>
                </td>
            </tr>
            <tr id="custom_form_return_message_row" style="display: none">
                <td style="padding-left: 2em">Message:</td>
                <td><textarea id="custom_form_return_message" name="custom_form_return_message" rows="15" cols="80"></textarea></td>
            </tr>
            <tr id="custom_form_return_page_id_row" style="display: none">
                <td style="padding-left: 2em">Page:</td>
                <td><select name="custom_form_return_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_return_alternative_page_row" style="display: none">
                <td style="padding-left: 2em"><label for="custom_form_return_alternative_page">Alternative Page:</label></td>
                <td><input type="checkbox" id="custom_form_return_alternative_page" name="custom_form_return_alternative_page" value="1" class="checkbox" onclick="show_or_hide_custom_form_return_alternative_page()" /></td>
            </tr>
            <tr id="custom_form_return_alternative_page_contact_group_id_row" style="display: none">
                <td style="padding-left: 4em">If Contact Group:</td>
                <td><select name="custom_form_return_alternative_page_contact_group_id"><option value=""></option>' . select_contact_group(0, $user) . '</select></td>
            </tr>
            <tr id="custom_form_return_alternative_page_id_row" style="display: none">
                <td style="padding-left: 4em">Then Go to Page:</td>
                <td><select name="custom_form_return_alternative_page_id"><option value=""></option>' . select_page() . '</select></td>
            </tr>
            <tr id="custom_form_pretty_urls_row" style="display: none">
                <td><label for="custom_form_pretty_urls">Enable Pretty URLs:</label></td>
                <td><input type="checkbox" id="custom_form_pretty_urls" name="custom_form_pretty_urls" value="1" class="checkbox" /></td>
            </tr>
            <tr id="custom_form_confirmation_continue_button_label_row" style="' . $custom_form_confirmation_continue_button_label_row_style . '">
                <td>Continue Button Label:</td>
                <td><input name="custom_form_confirmation_continue_button_label" type="text" size="30" maxlength="50" /></td>
            </tr>
            <tr id="custom_form_confirmation_next_page_id_row" style="' . $custom_form_confirmation_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="custom_form_confirmation_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="form_list_view_custom_form_page_id_row" style="' . $form_list_view_custom_form_page_id_row_style . '">
                <td>Custom Form:</td>
                <td><select name="form_list_view_custom_form_page_id"><option value=""></option>' . select_custom_form(0, $user) . '</select></td>
            </tr>
            <tr id="form_list_view_form_item_view_page_id_row" style="' . $form_list_view_form_item_view_page_id_row_style . '">
                <td>Form Item View:</td>
                <td><select name="form_list_view_form_item_view_page_id"><option value=""></option>' . select_page(0, 'form item view') . '</select></td>
            </tr>
            <tr id="form_list_view_viewer_filter_row" style="display: none">
                <td><label for="form_list_view_viewer_filter">Enable Viewer Filter:</label></td>
                <td><input type="checkbox" id="form_list_view_viewer_filter" name="form_list_view_viewer_filter" value="1" class="checkbox" onclick="show_or_hide_form_list_view_viewer_filter()" />' . $output_viewer_filter_warning . '</td>
            </tr>
            <tr id="form_list_view_viewer_filter_submitter_row" style="display: none">
                <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_submitter">Include Forms from Submitter:</label></td>
                <td><input type="checkbox" id="form_list_view_viewer_filter_submitter" name="form_list_view_viewer_filter_submitter" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="form_list_view_viewer_filter_watcher_row" style="display: none">
                <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_watcher">Include Forms for Watchers:</label></td>
                <td><input type="checkbox" id="form_list_view_viewer_filter_watcher" name="form_list_view_viewer_filter_watcher" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="form_list_view_viewer_filter_editor_row" style="display: none">
                <td style="padding-left: 2em"><label for="form_list_view_viewer_filter_editor">Include Forms for Form Editors:</label></td>
                <td><input type="checkbox" id="form_list_view_viewer_filter_editor" name="form_list_view_viewer_filter_editor" value="1" checked="checked" class="checkbox" /></td>
            </tr>
            <tr id="form_item_view_custom_form_page_id_row" style="' . $form_item_view_custom_form_page_id_row_style . '">
                <td>Custom Form:</td>
                <td><select name="form_item_view_custom_form_page_id"><option value="">-Select-</option>' . select_custom_form(0, $user) . '</select></td>
            </tr>
            <tr id="form_item_view_submitter_security_row" style="' . $form_item_view_submitter_security_row_style . '">
                <td><label for="form_item_view_submitter_security">Allow only submitter and watchers<br />to view his/her submitted form(s):</label></td>
                <td><input type="checkbox" id="form_item_view_submitter_security" name="form_item_view_submitter_security" value="1" class="checkbox" /></td>
            </tr>
            <tr id="form_item_view_submitted_form_editable_by_registered_user_row" style="' . $form_item_view_submitted_form_editable_by_registered_user_row_style . '">
                <td><label for="form_item_view_submitted_form_editable_by_registered_user">Allow any registered user<br />to edit submitted form(s):</label></td>
                <td><input type="checkbox" id="form_item_view_submitted_form_editable_by_registered_user" name="form_item_view_submitted_form_editable_by_registered_user" value="1" onclick="show_or_hide_form_item_view_editor()" class="checkbox" /></td>
            </tr>
            <tr id="form_item_view_submitted_form_editable_by_submitter_row" style="' . $form_item_view_submitted_form_editable_by_submitter_row_style . '">
                <td><label for="form_item_view_submitted_form_editable_by_submitter">Allow submitter to edit<br />his/her submitted form(s):</label></td>
                <td><input type="checkbox" id="form_item_view_submitted_form_editable_by_submitter" name="form_item_view_submitted_form_editable_by_submitter" value="1" class="checkbox" /></td>
            </tr>';

        // If hooks are enabled and the user is a designer or administrator then output hook row for PHP code.
        if ((defined('PHP_REGIONS') and PHP_REGIONS === true) && (USER_ROLE < 2)) {
            $output_forms_page_type_properties .=
                '<tr id="form_item_view_hook_code_row" style="' . $form_item_view_hook_code_row_style . '">
                    <td>Hook Code:</td>
                    <td><textarea name="form_item_view_hook_code" rows="5" cols="70"></textarea></td>
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
                <td><input type="checkbox" id="form_view_directory_summary" name="form_view_directory_summary" value="1" checked="checked" class="checkbox" onclick="show_or_hide_form_view_directory_summary()" /></td>
            </tr>
            <tr id="form_view_directory_summary_days_row" style="' . $form_view_directory_summary_days_row_style . '">
                <td style="padding-left: 2em">Date Range:</td>
                <td>last <input name="form_view_directory_summary_days" type="text" value="30" maxlength="4" size="3" /> day(s)</td>
            </tr>
            <tr id="form_view_directory_summary_maximum_number_of_results_row" style="' . $form_view_directory_summary_maximum_number_of_results_row_style . '">
                <td style="padding-left: 2em">Maximum Number of Results:</td>
                <td><input name="form_view_directory_summary_maximum_number_of_results" type="text" value="5" maxlength="3" size="2" /></td>
            </tr>
            <tr id="form_view_directory_form_list_view_heading_row" style="' . $form_view_directory_form_list_view_heading_row_style . '">
                <td>Form List View Heading:</td>
                <td><input name="form_view_directory_form_list_view_heading" type="text" value="Forum" maxlength="50" /></td>
            </tr>
            <tr id="form_view_directory_subject_heading_row" style="' . $form_view_directory_subject_heading_row_style . '">
                <td>Subject Heading:</td>
                <td><input name="form_view_directory_subject_heading" type="text" value="Subject" maxlength="50" /></td>
            </tr>
            <tr id="form_view_directory_number_of_submitted_forms_heading_row" style="' . $form_view_directory_number_of_submitted_forms_heading_row_style . '">
                <td>Number of Submitted Forms Heading:</td>
                <td><input name="form_view_directory_number_of_submitted_forms_heading" type="text" value="Forms" maxlength="50" /></td>
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
                $calendar_view_calendar_check_boxes .= '<input type="checkbox" name="calendar_view_calendar_' . $calendar['id'] . '" id="calendar_view_calendar_' . $calendar['id'] . '" value="1" class="checkbox" /><label for="calendar_view_calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
                $calendar_event_view_calendar_check_boxes .= '<input type="checkbox" name="calendar_event_view_calendar_' . $calendar['id'] . '" id="calendar_event_view_calendar_' . $calendar['id'] . '" value="1" class="checkbox" /><label for="calendar_event_view_calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
            }
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
                <td><select id="calendar_view_default_view" name="calendar_view_default_view" onchange="show_or_hide_calendar_view_number_of_upcoming_events()"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="upcoming">Upcoming</option></select></td>
            </tr>
            <tr id="calendar_view_number_of_upcoming_events_row" style="' . $calendar_view_number_of_upcoming_events_row_style . '">
                <td style="padding-left: 2em">Number of Events:</td>
                <td><input name="calendar_view_number_of_upcoming_events" type="text" value="5" maxlength="2" size="3" /></td>
            </tr>
            <tr id="calendar_view_calendar_event_view_page_id_row" style="' . $calendar_view_calendar_event_view_page_id_row_style . '">
                <td>Calendar Event View:</td>
                <td><select name="calendar_view_calendar_event_view_page_id"><option value="">-Select-</option>' . select_page(0, 'calendar event view') . '</select></td>
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
                    <input type="checkbox" name="calendar_event_view_notes" id="calendar_event_view_notes" value="1" class="checkbox" />
                </td>
            </tr>
            <tr id="calendar_event_view_back_button_label_row" style="' . $calendar_event_view_back_button_label_row_style . '">
                <td>Back Button Label:</td>
                <td><input name="calendar_event_view_back_button_label" type="text" size="30" maxlength="50" /></td>
            </tr>';
    }
    
    if (AFFILIATE_PROGRAM == true) {
        $output_affiliate_page_type_properties =
            '<tr id="affiliate_sign_up_form_terms_page_id_row" style="' . $affiliate_sign_up_form_terms_page_id_row_style . '">
                <td>Terms Page:</td>
                <td><select name="affiliate_sign_up_form_terms_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
            </tr>
            <tr id="affiliate_sign_up_form_submit_button_label_row" style="' . $affiliate_sign_up_form_submit_button_label_row_style . '">
                <td>Submit Button Label:</td>
                <td><input name="affiliate_sign_up_form_submit_button_label" type="text" value="Sign Up" maxlength="50" /></td>
            </tr>
            <tr id="affiliate_sign_up_form_next_page_id_row" style="' . $affiliate_sign_up_form_next_page_id_row_style . '">
                <td>Next Page:</td>
                <td><select name="affiliate_sign_up_form_next_page_id"><option value="">-Select Affiliate Sign Up Confirmation Page-</option>' . select_page(0, 'affiliate sign up confirmation') . '</select></td>
            </tr>';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>[new page]</h1>
        </div>
        <div id="content">
            
            ' . $output_wysiwyg_editor_code . '
            ' . $liveform_add_page->output_errors() . '
            ' . $liveform_add_page->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Page</h1>
            <div class="subheading">Create a new page, place it in a folder, and add any built-in features.</div>
            <form action="add_page.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Page Name</h2></th>
                    </tr>
                    <tr>
                        <td>Page Name:</td>
                        <td><span style="white-space: nowrap">' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . '<input name="name" type="text" size="60" maxlength="100" /></span></td>
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
                    <tr>
                        <th colspan="2"><h2>Interactive Page Features</h2></th>
                    </tr>
                    <tr>
                        <td>Page Type:</td>
                        <td><select id="page_type" name="type" onchange="change_page_type(this.options[this.selectedIndex].value)">' . select_page_type('', $user) . '</select><script type="text/javascript">var original_page_type = "standard";</script></td>
                    </tr>
                    <tr id="layout_type_row" style="display: none">
                        <td>Layout Type:</td>
                        <td>
                            <label>
                                <input type="radio" id="layout_type_system" name="layout_type" value="system" class="radio" checked="checked">System
                            </label>&nbsp;
                            
                            <label' . $layout_type_custom_label_class . ' title="Administrators &amp; Designers are allowed to enable a custom layout type.">
                                <input type="radio" name="layout_type" value="custom" class="radio"' . $layout_type_custom_option_disabled . '>Custom
                            </label>
                        </td>
                    </tr>
                    <tr id="email_a_friend_submit_button_label_row" style="' . $email_a_friend_submit_button_label_row_style . '">
                        <td>Submit Button Label:</td>
                        <td><input name="email_a_friend_submit_button_label" type="text" size="30" maxlength="50" /></td>
                    </tr>
                    <tr id="email_a_friend_next_page_id_row" style="' . $email_a_friend_next_page_id_row_style . '">
                        <td>Next Page:</td>
                        <td><select name="email_a_friend_next_page_id"><option value="">-Select-</option>' . select_page() . '</select></td>
                    </tr>
                    <tr id="folder_view_pages_row" style="' . $folder_view_pages_row_style . '">
                        <td><label for="folder_view_pages">Include Pages:</label></td>
                        <td><input type="checkbox" id="folder_view_pages" name="folder_view_pages" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                    <tr id="folder_view_files_row" style="' . $folder_view_files_row_style . '">
                        <td><label for="folder_view_files">Include Files:</label></td>
                        <td><input type="checkbox" id="folder_view_files" name="folder_view_files" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                    <tr id="photo_gallery_number_of_columns_row" style="' . $photo_gallery_number_of_columns_row_style . '">
                        <td>Number of Columns:</td>
                        <td><input name="photo_gallery_number_of_columns" type="text" value="4" maxlength="2" size="3" /></td>
                    </tr>
                    <tr id="photo_gallery_thumbnail_max_size_row" style="' . $photo_gallery_thumbnail_max_size_row_style . '">
                        <td>Thumbnail Max Size:</td>
                        <td><input name="photo_gallery_thumbnail_max_size" type="text" value="100" maxlength="4" size="3" /> pixels</td>
                    </tr>
                    ' . $output_search_results_page_type_properties . '
                    <tr id="update_address_book_address_type_row" style="' . $update_address_book_address_type_row_style . '">
                        <td><label for="update_address_book_address_type">Enable Address Type:</label></td>
                        <td><input id="update_address_book_address_type" name="update_address_book_address_type" type="checkbox" value="1" class="checkbox" onclick="show_or_hide_update_address_book_address_type()" /></td>
                    </tr>
                    <tr id="update_address_book_address_type_page_id_row" style="' . $update_address_book_address_type_page_id_row_style . '">
                        <td style="padding-left: 2em">Address Type Page:</td>
                        <td><select name="update_address_book_address_type_page_id"><option value=""></option>' . select_page() . '</select></td>
                    </tr>
                    ' . $output_ecommerce_page_type_properties . '
                    ' . $output_forms_page_type_properties . '
                    ' . $output_calendars_page_type_properties . '
                    ' . $output_affiliate_page_type_properties . '
                    <tr id="search_engine_optimization_heading_row">
                        <th colspan="2"><h2>Search Engine Optimization</h2></th>
                    </tr>
                    <tr id="sitemap_row">
                        <td><label for="sitemap">Include in Site Map:</label></td>
                        <td><input type="checkbox" id="sitemap" name="sitemap" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" id="create_button" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform_add_page->unmark_errors('add_page');
    $liveform_add_page->clear_notices('add_page');
    
} else {
    validate_token_field();
    
    // verify that user has access to create page in the requested folder
    if (check_edit_access($_POST['folder']) == false) {
        log_activity("access denied because user does not have access to create page in the requested folder", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $name = trim($_POST['name']);
    
    // If the page name field is blank.
    if ($name == '') {
        $liveform_add_page->mark_error('name', 'The page must have a name. Please type in a name for the page.');
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_page.php');
        exit();
    }
    
    // if the page type is catalog or catalog detail then check the name for slashes
    if (($_POST['type'] == 'catalog') || ($_POST['type'] == 'catalog detail')) {
        // if there is a slash in the page name, then output an error
        if (mb_strpos($name, '/') !== FALSE) {
            $liveform_add_page->mark_error('name', 'The page name for catalog and catalog detail pages cannot contain forward slashes. Please type in a new name for the page.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_page.php');
            exit();
        }
    }
    
    $name = str_replace(" ", "_", $name);
    $name = str_replace("&", "_", $name);

    if (check_name_availability(array('name' => $name)) == false) {
        $liveform_add_page->mark_error('name', 'The page name that you entered is already in use. Please enter a different page name.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_page.php');
        exit();
    }
    
    // if page is a custom form, check to see if there is another page with this same form name
    if ($_POST['type'] == 'custom form') {
        $query = "SELECT id FROM custom_form_pages WHERE form_name = '" . escape($_POST['custom_form_form_name']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if there is another page with this form name, output error
        if (mysqli_num_rows($result) > 0) {
            $liveform_add_page->mark_error('custom_form_form_name', 'The form name that you entered is already in use. Please enter a different form name.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_page.php');
            exit();
        }
    }

    $sql_style_fields = "";
    $sql_style_values = "";
    
    // if user role is Administrator, Designer, or Manager, then allow user to set style and mobile style for folder
    if ($user['role'] < 3) {
        $sql_style_fields =
            "page_style,
            mobile_style_id,";

        $sql_style_values =
            "'" . escape($_POST['style']) . "',
            '" . escape($_POST['mobile_style_id']) . "',";
    }
    
    // if the user has access to the selected page type, then set page type to selected page type
    if (
        ($user['role'] < 3)
        || ($_POST['type'] == 'standard')
        || (($_POST['type'] == 'email a friend') && ($user['set_page_type_email_a_friend'] == TRUE))
        || (($_POST['type'] == 'folder view') && ($user['set_page_type_folder_view'] == TRUE))
        || (($_POST['type'] == 'photo gallery') && ($user['set_page_type_photo_gallery'] == TRUE))
        || (($_POST['type'] == 'catalog') && ($user['set_page_type_catalog'] == TRUE))
        || (($_POST['type'] == 'catalog detail') && ($user['set_page_type_catalog_detail'] == TRUE))
        || (($_POST['type'] == 'express order') && ($user['set_page_type_express_order'] == TRUE))
        || (($_POST['type'] == 'order form') && ($user['set_page_type_order_form'] == TRUE))
        || (($_POST['type'] == 'shopping cart') && ($user['set_page_type_shopping_cart'] == TRUE))
        || (($_POST['type'] == 'shipping address and arrival') && ($user['set_page_type_shipping_address_and_arrival'] == TRUE))
        || (($_POST['type'] == 'shipping method') && ($user['set_page_type_shipping_method'] == TRUE))
        || (($_POST['type'] == 'billing information') && ($user['set_page_type_billing_information'] == TRUE))
        || (($_POST['type'] == 'order preview') && ($user['set_page_type_order_preview'] == TRUE))
        || (($_POST['type'] == 'order receipt') && ($user['set_page_type_order_receipt'] == TRUE))
        || (($_POST['type'] == 'custom form') && ($user['set_page_type_custom_form'] == TRUE))
        || (($_POST['type'] == 'custom form confirmation') && ($user['set_page_type_custom_form_confirmation'] == TRUE))
        || (($_POST['type'] == 'form list view') && ($user['set_page_type_form_list_view'] == TRUE))
        || (($_POST['type'] == 'form item view') && ($user['set_page_type_form_item_view'] == TRUE))
        || (($_POST['type'] == 'form view directory') && ($user['set_page_type_form_view_directory'] == TRUE))
        || (($_POST['type'] == 'calendar view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_view'] == TRUE))
        || (($_POST['type'] == 'calendar event view') && ($user['manage_calendars'] == TRUE) && ($user['set_page_type_calendar_event_view'] == TRUE))
    ) {
        $type = $_POST['type'];
        
    // else the user does not have access to the selected page type, so set type to standard
    } else {
        $type = 'standard';
    }

    // If the page type supports a layout, and if the user is an admin or designer,
    // then use layout type value that user selected.
    if (
        check_if_page_type_supports_layout($type)
        && (USER_ROLE < 2)
    ) {
        $layout_type = $_POST['layout_type'];

    // Otherwise force layout type to be system.
    } else {
        $layout_type = 'system';
    }
    
    // insert row into page table
    $query =
        "INSERT INTO page (
            page_name,
            page_folder,
            page_type,
            layout_type,
            page_home,
            page_search,
            page_search_keywords,
            page_timestamp,
            page_user,
            $sql_style_fields
            page_title,
            page_meta_description,
            page_meta_keywords,
            comments_disallow_new_comment_message,
            sitemap)
        VALUES (
            '" . escape($name) . "',
            '" . escape($_POST['folder']) . "',
            '" . escape($type) . "',
            '" . e($layout_type) . "',
            '0',
            '',
            '',
            UNIX_TIMESTAMP(),
            '$user[id]',
            $sql_style_values
            '',
            '',
            '',
            'We\'re sorry. New comments are no longer being accepted.',
            '" . escape($_POST['sitemap']) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $page_id = mysqli_insert_id(db::$con);
    
    // set page type properties, if necessary
    switch($type) {
        case 'email a friend':
            $properties = array(
                'page_id' => $page_id,
                'submit_button_label' => $_POST['email_a_friend_submit_button_label'],
                'next_page_id' => $_POST['email_a_friend_next_page_id']
            );
            
            break;

        case 'folder view':
            $properties = array(
                'page_id' => $page_id,
                'pages' => $_POST['folder_view_pages'],
                'files' => $_POST['folder_view_files']
            );
            
            break;
            
        case 'photo gallery':
            $properties = array(
                'page_id' => $page_id,
                'number_of_columns' => $_POST['photo_gallery_number_of_columns'],
                'thumbnail_max_size' => $_POST['photo_gallery_thumbnail_max_size']
            );
            
            break;
            
        case 'search results':
            $properties = array(
                'page_id' => $page_id,
                'search_folder_id' => $_POST['search_results_search_folder_id'],
                'search_catalog_items' => $_POST['search_results_search_catalog_items'],
                'product_group_id' => $_POST['search_results_product_group_id'],
                'catalog_detail_page_id' => $_POST['search_results_catalog_detail_page_id']
            );
            
            // update the tag cloud tables if needed
            update_tag_cloud_keywords_for_search_results_page_type($page_id, $_POST['search_results_search_catalog_items'], $_POST['search_results_product_group_id']);
            
            break;
            
        case 'update address book':
            $properties = array(
                'page_id' => $page_id,
                'address_type' => $_POST['update_address_book_address_type'],
                'address_type_page_id' => $_POST['update_address_book_address_type_page_id']
            );
            
            break;
            
        case 'custom form':

            $custom_form_contact_group_id = $_POST['custom_form_contact_group_id'];
            
            // if user has a user role,
            // and a contact group was selected
            // and user does not have access to contact group,
            // then don't allow contact group to be changed
            if (($user['role'] == 3) && ($custom_form_contact_group_id != 0) && (validate_contact_group_access($user, $custom_form_contact_group_id) == false)) {
                $custom_form_contact_group_id = 0;
                log_activity("access denied to set contact group for custom form because user did not have access to contact group", $_SESSION['sessionusername']);
            }

            $custom_form_private_folder_id = $_POST['custom_form_private_folder_id'];

            // If the user selected a private folder that he/she does not have edit access to,
            // then don't allow folder to be set and log activity.
            if ($custom_form_private_folder_id && (check_edit_access($custom_form_private_folder_id) == false)) {
                $custom_form_private_folder_id = 0;
                log_activity('access denied to set private folder for custom form because user did not have edit access to folder', $_SESSION['sessionusername']);
            }
            
            $properties = array(
                'page_id' => $page_id,
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
                'contact_group_id' => $custom_form_contact_group_id,
                'membership' => $_POST['custom_form_membership'],
                'membership_days' => $_POST['custom_form_membership_days'],
                'membership_start_page_id' => $_POST['custom_form_membership_start_page_id'],
                'private' => $_POST['custom_form_private'],
                'private_folder_id' => $custom_form_private_folder_id,
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

            // If commerce is enabled and the user has access to commerce, then save offer properties.
            if ((ECOMMERCE) && (USER_MANAGE_ECOMMERCE)) {
                $properties['offer'] = $_POST['custom_form_offer'];
                $properties['offer_id'] = $_POST['custom_form_offer_id'];
                $properties['offer_days'] = $_POST['custom_form_offer_days'];
                $properties['offer_eligibility'] = $_POST['custom_form_offer_eligibility'];
            }
            
            break;
        
        case 'custom form confirmation':
            $properties = array(
                'page_id' => $page_id,
                'continue_button_label' => $_POST['custom_form_confirmation_continue_button_label'],
                'next_page_id' => $_POST['custom_form_confirmation_next_page_id']
            );
            
            break;
        
        case 'form list view':
            $form_list_view_custom_form_page_id = $_POST['form_list_view_custom_form_page_id'];
            
            // if user has a user role, then verify that user has access to custom form that was selected
            if ($user['role'] == 3) {
                // get folder of custom form
                $query =
                    "SELECT page_folder
                    FROM page
                    WHERE page_id = '" . escape($form_list_view_custom_form_page_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                
                $form_list_view_custom_form_folder_id = $row['page_folder'];
                
                // if the user does not have access to custom form, don't allow custom form to be changed
                if (check_edit_access($form_list_view_custom_form_folder_id) == false) {
                    $form_list_view_custom_form_page_id = 0;
                    log_activity("access denied to set custom form for form list view because user did not have access to modify folder that custom form was in", $_SESSION['sessionusername']);
                }
            }
            
            $properties = array(
                'page_id' => $page_id,
                'custom_form_page_id' => $form_list_view_custom_form_page_id,
                'maximum_number_of_results_per_page' => 25,
                'search' => 1,
                'search_label' => 'Search',
                'show_results_by_default' => 1,
                'form_item_view_page_id' => $_POST['form_list_view_form_item_view_page_id'],
                'viewer_filter' => $_POST['form_list_view_viewer_filter'],
                'viewer_filter_submitter' => $_POST['form_list_view_viewer_filter_submitter'],
                'viewer_filter_watcher' => $_POST['form_list_view_viewer_filter_watcher'],
                'viewer_filter_editor' => $_POST['form_list_view_viewer_filter_editor']
            );
            
            break;
        
        case 'form item view':
            $form_item_view_custom_form_page_id = $_POST['form_item_view_custom_form_page_id'];
            
            // if user has a user role, then verify that user has access to custom form that was selected
            if ($user['role'] == 3) {
                // get folder of custom form
                $query = "SELECT page_folder
                         FROM page
                         WHERE page_id = '" . escape($form_item_view_custom_form_page_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                
                $form_item_view_custom_form_folder_id = $row['page_folder'];
                
                // if the user does not have access to custom form, don't allow custom form to be changed
                if (check_edit_access($form_item_view_custom_form_folder_id) == false) {
                    $form_item_view_custom_form_page_id = 0;
                    log_activity("access denied to set custom form for form item view because user did not have access to modify folder that custom form was in", $_SESSION['sessionusername']);
                }
            }
            
            $properties = array(
                'page_id' => $page_id,
                'custom_form_page_id' => $form_item_view_custom_form_page_id,
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
                'page_id' => $page_id,
                'summary' => $_POST['form_view_directory_summary'],
                'summary_days' => $_POST['form_view_directory_summary_days'],
                'summary_maximum_number_of_results' => $_POST['form_view_directory_summary_maximum_number_of_results'],
                'form_list_view_heading' => $form_list_view_heading,
                'subject_heading' => $subject_heading,
                'number_of_submitted_forms_heading' => $number_of_submitted_forms_heading
            );
            
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
                            '" . escape($page_id) . "',
                            '" . $form_list_view['page_id'] . "',
                            '" . escape($_POST['form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_name']) . "',
                            '" . escape($_POST['form_view_directory_form_list_view_' . $form_list_view['page_id'] . '_subject_form_field_id']) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
            
            break;
            
        case 'calendar view':
            $properties = array(
                'page_id' => $page_id,
                'default_view' => $_POST['calendar_view_default_view'],
                'number_of_upcoming_events' => $_POST['calendar_view_number_of_upcoming_events'],
                'calendar_event_view_page_id' => $_POST['calendar_view_calendar_event_view_page_id']
            );
            
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
                           '" . escape($page_id) . "',
                           '" . $calendar['id'] . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
            
            break;
            
        case 'calendar event view':
            $properties = array(
                'page_id' => $page_id,
                'notes' => $_POST['calendar_event_view_notes'],
                'back_button_label' => $_POST['calendar_event_view_back_button_label']
            );
            
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
                           '" . escape($page_id) . "',
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
                'page_id' => $page_id,
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
                'page_id' => $page_id,
                'allow_customer_to_add_product_to_order' => $_POST['catalog_detail_allow_customer_to_add_product_to_order'],
                'add_button_label' => $_POST['catalog_detail_add_button_label'],
                'next_page_id' => $_POST['catalog_detail_next_page_id'],
                'back_button_label' => $_POST['catalog_detail_back_button_label']
            );
            
            break;
        
        case 'express order':
            $properties = array(
                'page_id' => $page_id,
                'shopping_cart_label' => $_POST['express_order_shopping_cart_label'],
                'quick_add_label' => $_POST['express_order_quick_add_label'],
                'quick_add_product_group_id' => $_POST['express_order_quick_add_product_group_id'],
                'product_description_type' => $_POST['product_description_type'],
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
                'page_id' => $page_id,
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
                'page_id' => $page_id,
                'shopping_cart_label' => $_POST['shopping_cart_shopping_cart_label'],
                'quick_add_label' => $_POST['shopping_cart_quick_add_label'],
                'quick_add_product_group_id' => $_POST['shopping_cart_quick_add_product_group_id'],
                'product_description_type' => $_POST['product_description_type'],
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
            $properties = array(
                'page_id' => $page_id,
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
                'page_id' => $page_id,
                'product_description_type' => $_POST['product_description_type'],
                'submit_button_label' => $_POST['shipping_method_submit_button_label'],
                'next_page_id' => $_POST['shipping_method_next_page_id']
            );
            
            break;

        case 'billing information':
            $properties = array(
                'page_id' => $page_id,
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
                'page_id' => $page_id,
                'product_description_type' => $_POST['product_description_type'],
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
                'page_id' => $page_id,
                'product_description_type' => $_POST['order_receipt_product_description_type']
            );
            
            break;
            
        case 'affiliate sign up form':
            $properties = array(
                'page_id' => $page_id,
                'terms_page_id' => $_POST['affiliate_sign_up_form_terms_page_id'],
                'submit_button_label' => $_POST['affiliate_sign_up_form_submit_button_label'],
                'next_page_id' => $_POST['affiliate_sign_up_form_next_page_id']
            );
            
            break;
    }
    
    // if page type has a table for properties, create record in page type table
    if (check_for_page_type_properties($type) == true) {
        create_or_update_page_type_record($type, $properties);
    }

    // get style so that we can create regions
    // if default was selected for style
    if ($_POST['style'] == 0) {
        $style = get_style($_POST['folder']);
    // else default was not selected
    } else {
        $style = $_POST['style'];
    }

    // get style code
    $result = mysqli_query(db::$con, "SELECT style_code FROM style WHERE style_id = '" . escape($style) . "'") or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);
    $style_code = $row['style_code'];

    // create regions
    $pregion_count = 1;
    preg_match_all('/<pregion>.*?<\/pregion>/i', $style_code, $regions);
    foreach ($regions[0] as $region)
    {
        $region_name = time() .'_' . $pregion_count;
        $query = "INSERT INTO pregion (pregion_name, pregion_content, pregion_page, pregion_order, pregion_user, pregion_timestamp) VALUES ('$region_name', '', '$page_id', '$pregion_count', '$user[id]', UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        $pregion_count++;
    }

    log_activity("page ($name) was created", $_SESSION['sessionusername']);

    // If this page is a custom form, or a custom shipping or custom billing form was enabled,
    // then forward user to form designer.
    if (
        ($type == 'custom form')
        || (($type == 'shipping address and arrival') && ($_POST['shipping_address_and_arrival_form'] == 1))
        || (($type == 'billing information') && ($_POST['billing_information_form'] == 1))
        || (
            ($type == 'express order') and ($_POST['express_order_shipping_form'] or $_POST['express_order_form'])
        )
    ) {

        $form_type = '';

        // If this is an express order page, then determine if we should forward to shipping
        // or billing form.
        if ($type == 'express order') {

            $form_type = '&form_type=';

            if ($_POST['express_order_shipping_form']) {
                $form_type .= 'shipping';
            } else {
                $form_type .= 'billing';
            }
        }

        $query_string_from = '';
        
        // if the page is a shipping address and arrival page, then prepare from
        if ($type == 'shipping address and arrival') {
            $query_string_from = '?from=control_panel';
        }
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . $form_type . '&send_to=' . urlencode(PATH . $name . $query_string_from));
        
    // else we don't need to forward the user to the form designer so forward the user to the page
    } else {
        $query_string_from = '';
        
        // if page type is a certain page type, then prepare from
        switch ($type) {
            case 'view order':
            case 'custom form':
            case 'custom form confirmation':
            case 'calendar event view':
            case 'catalog detail':
            case 'shipping address and arrival':
            case 'shipping method':
            case 'logout':
                $query_string_from = '?from=control_panel';
                break;
        }
        
        // forward user to view page
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path($name) . $query_string_from);
    }
}
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

$(document).ready(function() {
    // Deal with changing buttons to fixed position at the bottom if they are not visible.

    var buttons = $('.buttons');

    // If buttons were found on the screen, and this is not the theme designer screen,
    // then continue to update buttons.
    if (
        (buttons.length)
        && (buttons.attr('id') != 'content_footer')
    ) {
        // We use a placeholder so that when the buttons div is changed to fixed position.
        // the placeholder can preserve the original space that the buttons div occupied,
        // so that the page content does not collapse and change the viewport, which creates
        // weird flickering issues when the user scrolls near the static buttons position.

        var buttons_placeholder = $('<div class="buttons_placeholder"></div>');

        buttons_placeholder.css({
            'display': 'none',
            'margin': '0',
            'padding': '0',
            'height': buttons.outerHeight() + 'px'
        });

        buttons.after(buttons_placeholder);

        // When the page is loaded, resized, or scrolled,
        // then update the positioning of the buttons.
        $(window).on('load resize scroll', function() {
            // Get the normal static position of the buttons,
            // so we can compare that to the scroll position and window height.
            if (buttons.css('position') == 'static') {
                var buttons_position = buttons.offset().top;
            } else {
                var buttons_position = buttons_placeholder.offset().top;
            }

            var scroll_position = $(window).scrollTop();
            var window_height = $(window).height();

            // If the normal static position of the buttons is in the current viewport,
            // then set the buttons so they are positioned in their normal static location,
            // if they are not already like that.
            if ((scroll_position + window_height) > (buttons_position + buttons.outerHeight())) {
                if (buttons.css('position') != 'static') {
                    buttons.css({
                        'margin': '0',
                        'position': 'static'
                    });

                    buttons_placeholder.css({
                        'display': 'none'
                    });
                }

            // Otherwise the normal static position of the buttons is not in the current
            // viewport, so change the position to fixed, if not already.
            } else {
                if (buttons.css('position') != 'fixed') {
                    buttons.css({
                        'position': 'fixed'
                    });

                    buttons_placeholder.css({
                        'display': 'block'
                    });

                    var margin_left = buttons.outerWidth() / 2;

                    buttons.css({
                        'bottom': '0',
                        'left': '50%',
                        'margin-left': '-' + margin_left + 'px',
                        'position': 'fixed'
                    });
                }
            }
        });
    }

    // Get the page designer button so that we can add a click event
    // if necessary.  We had to add the click event in order for the keyboard
    // shortcut (Ctrl+G) for the page designer to work.
    var page_designer_button = $('#button_bar .page_designer_button');

    // If a page designer button was found, and we are not already
    // in the page designer, then add click event.
    // We don't want to add a click event if we are already in the page
    // designer because the click event will conflict with the click event
    // that the page designer adds.
    if (
        (page_designer_button.length)
        &&
        (
            (check_iframe_access(parent.parent) == false)
            || ($(parent.parent.document).find('.page_designer').length == 0)
        )
    ) {
        page_designer_button.click(function(event) {
            event.preventDefault();
            window.parent.location = page_designer_button[0].href;
        });
    }

    // If there is a help URL, then set help button so it loads help popup when clicked.
    if (help_url) {
        $('#help_link, .green_help_button, .white_help_button').click(function() {
            window.open(help_url, 'help', 'location=1, status=1, scrollbars=1, resizable=1, directories=1, toolbar=1, titlebar=1').focus();
            return false;
        });

    // Otherwise, there is not a help URL, so hide help button.  This should only happen if site
    // is private labeled.
    } else {
        $('#help_link, .green_help_button, .white_help_button').hide();
    }
        
    // Search the document for all tables.
    var chart_table = document.getElementsByTagName("table");
    
    // Loop through the table results.
    for (var i=0; i<=chart_table.length; i++) {
        
        // If a table exists and the class is chart.
        if (
            (chart_table[i])
            && ((chart_table[i].className == "chart") || (chart_table[i].className == "chart shipping_report"))
            && (chart_table[i].id != 'submit_form_create_field_table')
            && (chart_table[i].id != 'submit_form_update_field_table')
        ) {
            
            // Get the rows of that table.
            var chart_table_row = chart_table[i].getElementsByTagName("tr");
            
            // Loop through the table row results.
            for (var x=0; x<=chart_table_row.length; x++) {
                
                // If a table row exists.
                if (chart_table_row[x]) {
                    
                    // Set background variable
                    var background = '';
                    
                    // Check to see if the row contains a table head.
                    if ((chart_table_row[x].firstChild.tagName != 'TH') && (chart_table_row[x].innerHTML.match("<th") == null)) {
                        
                        // Add jquery listener for mouseover.
                        $(chart_table_row[x]).mouseover( function () {
                            
                            // Save background color.
                            background = $(this).css("background-color");
                            // Change the background color.
                            this.style.background = "#FBEDA3";
                        });
                        
                        // Add jquery listener for mouseout.
                        $(chart_table_row[x]).mouseout( function () {
                            
                            // Replace the background color.
                            this.style.background = background;
                        });
                    }
                }
            }
        }
    }
    
    // Add a listener to the select button.
    $("#select_all").click( function () {
        
        // The cycle number is the amount of times we have looped through the results
        var cycle_number = "0";
        
        // Loop through the results
        for (var i=0; i<=chart_table.length; i++) {
            
            // If a table exists and the class is chart.
            if ((chart_table[i]) && (chart_table[i].className == "chart")) {
                
                // Search the document for table cells.
                var chart_table_cell = chart_table[i].getElementsByTagName("td");
                
                // Loop through the results.
                for (var x=0; x<=chart_table_cell.length; x++) {
                    
                    // If table cell exists, if it is not empty, and if the class is checkbox.
                    if ((chart_table_cell[x]) && (chart_table_cell[x].firstChild) && (chart_table_cell[x].firstChild.className == "checkbox")) {
                        
                        // Compaire the Select button to see if the class is Select.
                        if (this.className == "Select" && cycle_number == "0") {
                            
                            // Set the checked variable to true.
                            var checked = true;
                            // Change the Select button's class name to clear.
                            this.className = "Deselect";
                            // Changes the Select button's value to Clear
                            this.firstChild.nodeValue = "Deselect";
                            // sets the cycle number to 1
                            cycle_number = "1";
                        
                        // If the Select button's class is not Select check to see if the class is Clear.
                        } else if (this.className == "Deselect" && cycle_number == "0") {
                            // Set the checked variable to true.
                            var checked = false;
                            // Change the Select button's class name to Select.
                            this.className = "Select";
                            // Changes the Select button's value to Select
                            this.firstChild.nodeValue = "Select";
                            // sets the cycle number to 1
                            cycle_number = "1";
                        
                        // If neither assume that the button's class name is Select
                        } else if (!this.className && cycle_number == "0") {
                            // Set the checked variable to true.
                            var checked = true;
                            // Change the Select button's class name to clear.
                            this.className = "Deselect";
                            // Changes the Select button's value to Clear
                            this.firstChild.nodeValue = "Deselect";
                            cycle_number = "1";
                            // sets the cycle number to 1
                        }
                        
                        // If checked variable is false.
                        if (chart_table_cell[x].firstChild.disabled != true) {
                            if(checked == false) {
                                // Uncheck all checkboxes.
                                chart_table_cell[x].firstChild.checked = false;
                            // If checked variable is true.
                            } else {
                                // Check all checkboxes.
                                chart_table_cell[x].firstChild.checked = true;
                            }
                        }
                    }
                }
            }
        }
    });

    // Add keyboard shortcuts.
    $(window).bind('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) {
                // Add keyboard shortcut (Ctrl+D) for fullscreen toggle
                // for when focus is on the toolbar.
                case 'd':
                    var fullscreen_toggle = $(parent.document).find('#software_fullscreen_toggle');

                    if (fullscreen_toggle.length) {
                        event.preventDefault();
                        fullscreen_toggle.click();
                    }

                    break;

                // Add keyboard shortcut (Ctrl+E) for edit mode,
                // for when focus is on the toolbar.
                case 'e':
                    var grid_toggle = $(parent.document).find('#grid_toggle');

                    if (grid_toggle.length) {
                        event.preventDefault();

                        // Timeout resolves Firefox bug.
                        setTimeout (function () {
                            grid_toggle.click();
                        }, 0);
                    }

                    break;

                // Page designer shortcut (Ctrl+G).
                case 'g':
                    var page_designer_button = $('.page_designer_button');

                    if (page_designer_button.length) {
                        event.preventDefault();

                        // Timeout is necessary in order to workaround Firefox bug
                        // with Ctrl+G where it would still open find area,
                        // even though we run preventDefault above.
                        setTimeout (function () {
                            page_designer_button.click();
                        }, 0);
                    }

                    break;
                case 'y':
                    //This Function Development purphose. add class body, this class add outlines all elements on body css.This function for development draw line
                    show_draw_lines();
                    break;           

                case 's':
                    // A "disable_shortcut" class has been added to forms that purely delete
                    // items, because the shortcut is too dangerous in that case.
                    // We could still allow it for those types of forms, but just show
                    // the warning before the form is submitted, however we have not
                    // spent the time to do that, so we will just disable them for now.

                    // Find the form closest to the current focused element.
                    var form = $(document.activeElement).closest('form:not(.disable_shortcut)');

                    // If a form was found, then submit it.
                    if (form.length) {
                        event.preventDefault();
                        form.submit();

                    // Otherwise a form was not found that way,
                    // so find the first form that is not a disable shortcut form.
                    } else {
                        form = $('form:not(.disable_shortcut):first');

                        // If a form was found, then submit it.
                        if (form.length) {
                            event.preventDefault();
                            form.submit();
                        }
                    }

                    break;

            }
        }
    });
    
    // Add Ctrl+S keyboard shortcut info to first submit button of every form.

    // A "disable_shortcut" class has been added to forms that purely delete
    // items, because the shortcut is too dangerous in that case.
    // We could still allow it for those types of forms, but just show
    // the warning before the form is submitted, however we have not
    // spent the time to do that, so we will just disable them for now.
    
    $('form:not(.disable_shortcut)').each(function() {
        var button = $(this).find('input[type=submit]:first');

        if (button.length) {
            var title = button.prop('title');

            if (title != '') {
                button.prop('title', title + ' (Ctrl+S | \u2318+S)');

            } else {
                button.prop('title', 'Ctrl+S | \u2318+S');
            }
        }
    });
});

function check_all(field_name)
{
    for (var i = 0; i < document.forms.length; i++) {
        for (var j = 0; j < document.forms[i].length; j++) {
            if (document.forms[i].elements[j].name == field_name) {
                document.forms[i].elements[j].checked = true;
            }
        }
    }
}

function uncheck_all(field_name)
{
    for (var i = 0; i < document.forms.length; i++) {
        for (var j = 0; j < document.forms[i].length; j++) {
            if (document.forms[i].elements[j].name == field_name) {
                document.forms[i].elements[j].checked = false;
            }
        }
    }
}

function edit_pages(action)
{
    var result;

    switch (action) {
        case 'edit':
            document.form.action.value = 'edit';
            break;
            
        case 'delete':
            document.form.action.value = 'delete';
            result = confirm('WARNING: The selected page(s) will be permanently deleted.')
            break;
    }

    // if user select ok to confirmation, submit form
    if (result == true) {
        document.form.submit();
    }
}

function edit_files(action)
{
    var result;

    switch (action) {
        case 'edit':
            document.form.action.value = 'edit';
            break;
        
        case 'delete':
            document.form.action.value = 'delete';
            result = confirm('WARNING: The selected files(s) will be permanently deleted.')
            break;
    }

    // if user select ok to confirmation, submit form
    if (result == true) {
        document.form.submit();
    }
}

function edit_contacts(action)
{
    var result;

    switch (action) {
        case 'optin':
            document.form.action.value = 'optin';
            result = confirm('WARNING: The selected contact(s) will be opted-in.')
            break;

        case 'optout':
            document.form.action.value = 'optout';
            result = confirm('WARNING: The selected contact(s) will be opted-out.')
            break;

        case 'delete':
            document.form.action.value = 'delete';
            result = confirm('WARNING: The selected contact(s) will be permanently deleted.')
            break;
            
        case 'merge':
            document.form.action.value = 'merge';
            result = confirm('WARNING: The selected duplicate contact(s) will be merged together.')
            break;
    }

    // if user select ok to confirmation, submit form
    if (result == true) {
        document.form.submit();
    }
}

function edit_products(action)
{
    var result;

    switch (action) {
        case 'edit':
            document.form.action.value = 'edit';
            break;
        
        case 'delete':
            document.form.action.value = 'delete';
            result = confirm('WARNING: The selected product(s) will be permanently deleted.')
            break;
    }

    // if user select ok to confirmation, submit form
    if (result == true) {
        document.form.submit();
    }
}

function change_page_type(page_type) {
    
    if (check_if_page_type_supports_layout(page_type)) {
        $('#layout_type_row').fadeIn();
        
    } else {
        $('#layout_type_row').fadeOut();
    }

    // hide all objects
    document.getElementById('email_a_friend_submit_button_label_row').style.display = 'none';
    document.getElementById('email_a_friend_next_page_id_row').style.display = 'none';
    document.getElementById('folder_view_pages_row').style.display = 'none';
    document.getElementById('folder_view_files_row').style.display = 'none';
    document.getElementById('photo_gallery_number_of_columns_row').style.display = 'none';
    document.getElementById('photo_gallery_thumbnail_max_size_row').style.display = 'none';
    document.getElementById('update_address_book_address_type_row').style.display = 'none';
    document.getElementById('update_address_book_address_type_page_id_row').style.display = 'none';
    
    // if e-commerce is on
    if (document.getElementById('order_form_product_layout_row_1')) {
        document.getElementById('catalog_product_group_id_row').style.display = 'none';
        document.getElementById('catalog_menu_row').style.display = 'none';
        document.getElementById('catalog_search_row').style.display = 'none';
        document.getElementById('catalog_number_of_featured_items_row').style.display = 'none';
        document.getElementById('catalog_number_of_new_items_row').style.display = 'none';
        document.getElementById('catalog_number_of_columns_row').style.display = 'none';
        document.getElementById('catalog_image_width_row').style.display = 'none';
        document.getElementById('catalog_image_height_row').style.display = 'none';
        document.getElementById('catalog_back_button_label_row').style.display = 'none';
        document.getElementById('catalog_catalog_detail_page_id_row').style.display = 'none';
        document.getElementById('catalog_detail_allow_customer_to_add_product_to_order_row').style.display = 'none';
        document.getElementById('catalog_detail_add_button_label_row').style.display = 'none';
        document.getElementById('catalog_detail_back_button_label_row').style.display = 'none';
        document.getElementById('catalog_detail_next_page_id_row').style.display = 'none';
        document.getElementById('express_order_shopping_cart_label_row').style.display = 'none';
        document.getElementById('express_order_quick_add_label_row').style.display = 'none';
        document.getElementById('express_order_quick_add_product_group_id_row').style.display = 'none';
        document.getElementById('express_order_product_description_type_row').style.display = 'none';
        document.getElementById('express_order_shipping_form_row').style.display = 'none';
        document.getElementById('express_order_special_offer_code_label_row').style.display = 'none';
        document.getElementById('express_order_special_offer_code_message_row').style.display = 'none';
        document.getElementById('express_order_custom_field_1_label_row').style.display = 'none';
        document.getElementById('express_order_custom_field_2_label_row').style.display = 'none';
        document.getElementById('express_order_po_number_row').style.display = 'none';
        document.getElementById('express_order_form_row').style.display = 'none';
        document.getElementById('express_order_form_notice').style.display = 'none';
        document.getElementById('express_order_form_name_row').style.display = 'none';
        document.getElementById('express_order_form_label_column_width_row').style.display = 'none';
        document.getElementById('express_order_card_verification_number_page_id_row').style.display = 'none';
        
        if (document.getElementById('express_order_offline_payment_always_allowed_row')) {
            document.getElementById('express_order_offline_payment_always_allowed_row').style.display = 'none';
            document.getElementById('express_order_offline_payment_label_row').style.display = 'none';
        }
        
        document.getElementById('express_order_terms_page_id_row').style.display = 'none';
        document.getElementById('express_order_update_button_label_row').style.display = 'none';
        document.getElementById('express_order_purchase_now_button_label_row').style.display = 'none';
        document.getElementById('express_order_auto_registration_row').style.display = 'none';

        // If hook code rows exists (i.e. user is a designer or administrator and hooks are enabled),
        // then hide hook code rows by default.
        if (document.getElementById('express_order_pre_save_hook_code_row')) {
            document.getElementById('express_order_pre_save_hook_code_row').style.display = 'none';
            document.getElementById('express_order_post_save_hook_code_row').style.display = 'none';
        }

        document.getElementById('express_order_order_receipt_email_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_subject_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_format_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_header_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_footer_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_page_id_row').style.display = 'none';
        document.getElementById('express_order_next_page_id_row').style.display = 'none';
        document.getElementById('order_form_product_layout_row_1').style.display = 'none';
        document.getElementById('order_form_product_group_id_row').style.display = 'none';
        document.getElementById('order_form_product_layout_row_1').style.display = 'none';
        document.getElementById('order_form_product_layout_row_2').style.display = 'none';
        document.getElementById('order_form_add_button_label_row').style.display = 'none';
        document.getElementById('order_form_add_button_next_page_id_row').style.display = 'none';
        document.getElementById('order_form_skip_button_label_row').style.display = 'none';
        document.getElementById('order_form_skip_button_next_page_id_row').style.display = 'none';

        // If search folder row exists (i.e. advanced search is enabled), then hide it.
        if (document.getElementById('search_results_search_folder_id_row')) {
            document.getElementById('search_results_search_folder_id_row').style.display = 'none';
        }
        
        // if the ecommerce search results rows exist (i.e. if user has more than a user role), then hide them
        if (document.getElementById('search_results_search_catalog_items_row')) {
            document.getElementById('search_results_search_catalog_items_row').style.display = 'none';
            document.getElementById('search_results_product_group_id_row').style.display = 'none';
            document.getElementById('search_results_catalog_detail_page_id_row').style.display = 'none';
        }
        
        document.getElementById('shopping_cart_shopping_cart_label_row').style.display = 'none';
        document.getElementById('shopping_cart_quick_add_label_row').style.display = 'none';
        document.getElementById('shopping_cart_quick_add_product_group_id_row').style.display = 'none';
        document.getElementById('shopping_cart_product_description_type_row').style.display = 'none';
        document.getElementById('shopping_cart_special_offer_code_label_row').style.display = 'none';
        document.getElementById('shopping_cart_special_offer_code_message_row').style.display = 'none';
        document.getElementById('shopping_cart_update_button_label_row').style.display = 'none';
        document.getElementById('shopping_cart_checkout_button_label_row').style.display = 'none';

        // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
        // then hide hook code row by default.
        if (document.getElementById('shopping_cart_hook_code_row')) {
            document.getElementById('shopping_cart_hook_code_row').style.display = 'none';
        }

        document.getElementById('shopping_cart_next_page_id_with_shipping_row').style.display = 'none';
        document.getElementById('shopping_cart_next_page_id_without_shipping_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_address_type_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_address_type_page_id_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_form_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_form_notice').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_form_name_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_form_label_column_width_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_submit_button_label_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_next_page_id_row').style.display = 'none';
        document.getElementById('shipping_method_product_description_type_row').style.display = 'none';
        document.getElementById('shipping_method_submit_button_label_row').style.display = 'none';
        document.getElementById('shipping_method_next_page_id_row').style.display = 'none';
        document.getElementById('billing_information_custom_field_1_label_row').style.display = 'none';
        document.getElementById('billing_information_custom_field_2_label_row').style.display = 'none';
        document.getElementById('billing_information_po_number_row').style.display = 'none';
        document.getElementById('billing_information_form_row').style.display = 'none';
        document.getElementById('billing_information_form_notice').style.display = 'none';
        document.getElementById('billing_information_form_name_row').style.display = 'none';
        document.getElementById('billing_information_form_label_column_width_row').style.display = 'none';
        document.getElementById('billing_information_submit_button_label_row').style.display = 'none';
        document.getElementById('billing_information_next_page_id_row').style.display = 'none';
        document.getElementById('order_preview_product_description_type_row').style.display = 'none';
        document.getElementById('order_preview_card_verification_number_page_id_row').style.display = 'none';
        
        if (document.getElementById('order_preview_offline_payment_always_allowed_row')) {
            document.getElementById('order_preview_offline_payment_always_allowed_row').style.display = 'none';
            document.getElementById('order_preview_offline_payment_label_row').style.display = 'none';
        }
        
        document.getElementById('order_preview_terms_page_id_row').style.display = 'none';
        document.getElementById('order_preview_submit_button_label_row').style.display = 'none';
        document.getElementById('order_preview_auto_registration_row').style.display = 'none';

        // If hook code rows exists (i.e. user is a designer or administrator and hooks are enabled),
        // then hide hook code rows by default.
        if (document.getElementById('order_preview_pre_save_hook_code_row')) {
            document.getElementById('order_preview_pre_save_hook_code_row').style.display = 'none';
            document.getElementById('order_preview_post_save_hook_code_row').style.display = 'none';
        }

        document.getElementById('order_preview_order_receipt_email_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_subject_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_format_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_header_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_footer_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_page_id_row').style.display = 'none';
        document.getElementById('order_preview_next_page_id_row').style.display = 'none';
        document.getElementById('order_receipt_product_description_type_row').style.display = 'none';
    }
    
    // if forms is on
    if (document.getElementById('custom_form_form_name_row')) {
        document.getElementById('custom_form_form_name_row').style.display = 'none';
        document.getElementById('custom_form_enabled_row').style.display = 'none';
        document.getElementById('custom_form_quiz_row').style.display = 'none';
        document.getElementById('custom_form_quiz_pass_percentage_row').style.display = 'none';
        document.getElementById('custom_form_label_column_width_row').style.display = 'none';
        document.getElementById('custom_form_watcher_page_id_row').style.display = 'none';
        document.getElementById('custom_form_save_row').style.display = 'none';
        document.getElementById('custom_form_submit_button_label_row').style.display = 'none';
        document.getElementById('custom_form_auto_registration_row').style.display = 'none';

        // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
        // then hide hook code row by default.
        if (document.getElementById('custom_form_hook_code_row')) {
            document.getElementById('custom_form_hook_code_row').style.display = 'none';
        }

        document.getElementById('custom_form_submitter_email_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_from_email_address_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_subject_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_format_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_body_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_page_id_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_to_email_address_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_bcc_email_address_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_subject_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_format_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_body_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_page_id_row').style.display = 'none';
        document.getElementById('custom_form_contact_group_id_row').style.display = 'none';
        document.getElementById('custom_form_membership_row').style.display = 'none';
        document.getElementById('custom_form_membership_days_row').style.display = 'none';
        document.getElementById('custom_form_membership_start_page_id_row').style.display = 'none';
        document.getElementById('custom_form_private_row').style.display = 'none';
        document.getElementById('custom_form_private_folder_id_row').style.display = 'none';
        document.getElementById('custom_form_private_days_row').style.display = 'none';
        document.getElementById('custom_form_private_start_page_id_row').style.display = 'none';

        // If grant offer rows exist (i.e. commerce is enabled and user has access to commerce),
        // then hide grant offer rows.
        if (document.getElementById('custom_form_offer_row')) {
            document.getElementById('custom_form_offer_row').style.display = 'none';
            document.getElementById('custom_form_offer_id_row').style.display = 'none';
            document.getElementById('custom_form_offer_days_row').style.display = 'none';
            document.getElementById('custom_form_offer_eligibility_row').style.display = 'none';
        }

        document.getElementById('custom_form_confirmation_type_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_message_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_page_id_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_alternative_page_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_alternative_page_contact_group_id_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_alternative_page_id_row').style.display = 'none';
        document.getElementById('custom_form_return_type_row').style.display = 'none';
        document.getElementById('custom_form_return_message_row').style.display = 'none';
        document.getElementById('custom_form_return_page_id_row').style.display = 'none';
        document.getElementById('custom_form_return_alternative_page_row').style.display = 'none';
        document.getElementById('custom_form_return_alternative_page_contact_group_id_row').style.display = 'none';
        document.getElementById('custom_form_return_alternative_page_id_row').style.display = 'none';
        document.getElementById('custom_form_pretty_urls_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_continue_button_label_row').style.display = 'none';
        document.getElementById('custom_form_confirmation_next_page_id_row').style.display = 'none';
        document.getElementById('form_list_view_custom_form_page_id_row').style.display = 'none';
        document.getElementById('form_list_view_form_item_view_page_id_row').style.display = 'none';
        document.getElementById('form_list_view_viewer_filter_row').style.display = 'none';
        document.getElementById('form_list_view_viewer_filter_submitter_row').style.display = 'none';
        document.getElementById('form_list_view_viewer_filter_watcher_row').style.display = 'none';
        document.getElementById('form_list_view_viewer_filter_editor_row').style.display = 'none';
        document.getElementById('form_item_view_custom_form_page_id_row').style.display = 'none';
        document.getElementById('form_item_view_submitter_security_row').style.display = 'none';
        document.getElementById('form_item_view_submitted_form_editable_by_registered_user_row').style.display = 'none';
        document.getElementById('form_item_view_submitted_form_editable_by_submitter_row').style.display = 'none';

        // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
        // then hide hook code row by default.
        if (document.getElementById('form_item_view_hook_code_row')) {
            document.getElementById('form_item_view_hook_code_row').style.display = 'none';
        }

        document.getElementById('form_view_directory_form_list_views_row').style.display = 'none';
        document.getElementById('form_view_directory_summary_row').style.display = 'none';
        document.getElementById('form_view_directory_summary_days_row').style.display = 'none';
        document.getElementById('form_view_directory_summary_maximum_number_of_results_row').style.display = 'none';
        document.getElementById('form_view_directory_form_list_view_heading_row').style.display = 'none';
        document.getElementById('form_view_directory_subject_heading_row').style.display = 'none';
        document.getElementById('form_view_directory_number_of_submitted_forms_heading_row').style.display = 'none';
    }
    
    // if calendars is on
    if (document.getElementById('calendar_view_default_view_row')) {
        document.getElementById('calendar_view_calendars_row').style.display = 'none';
        document.getElementById('calendar_view_default_view_row').style.display = 'none';
        document.getElementById('calendar_view_calendar_event_view_page_id_row').style.display = 'none';
        document.getElementById('calendar_event_view_calendars_row').style.display = 'none';
        document.getElementById('calendar_view_number_of_upcoming_events_row').style.display = 'none';
        document.getElementById('calendar_event_view_notes_row').style.display = 'none';
        document.getElementById('calendar_event_view_back_button_label_row').style.display = 'none';
    }

    // if affiliate program is on
    if (document.getElementById('affiliate_sign_up_form_terms_page_id_row')) {
        document.getElementById('affiliate_sign_up_form_terms_page_id_row').style.display = 'none';
        document.getElementById('affiliate_sign_up_form_submit_button_label_row').style.display = 'none';
        document.getElementById('affiliate_sign_up_form_next_page_id_row').style.display = 'none';
    }

    // show needed objects
    switch (page_type) {
        case 'email a friend':
            document.getElementById('email_a_friend_submit_button_label_row').style.display = '';
            document.getElementById('email_a_friend_next_page_id_row').style.display = '';
            break;

        case 'folder view':
            document.getElementById('folder_view_pages_row').style.display = '';
            document.getElementById('folder_view_files_row').style.display = '';
            break;
            
        case 'photo gallery':
            document.getElementById('photo_gallery_number_of_columns_row').style.display = '';
            document.getElementById('photo_gallery_thumbnail_max_size_row').style.display = '';
            break;
            
        case 'update address book':
            document.getElementById('update_address_book_address_type_row').style.display = '';
            show_or_hide_update_address_book_address_type();
            break;
            
        case 'custom form':
            document.getElementById('custom_form_form_name_row').style.display = '';
            document.getElementById('custom_form_enabled_row').style.display = '';
            document.getElementById('custom_form_quiz_row').style.display = '';
            
            show_or_hide_quiz();
            
            document.getElementById('custom_form_label_column_width_row').style.display = '';
            document.getElementById('custom_form_watcher_page_id_row').style.display = '';
            document.getElementById('custom_form_save_row').style.display = '';
            document.getElementById('custom_form_submit_button_label_row').style.display = '';
            document.getElementById('custom_form_auto_registration_row').style.display = '';

            // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
            // then show row.
            if (document.getElementById('custom_form_hook_code_row')) {
                document.getElementById('custom_form_hook_code_row').style.display = '';
            }

            document.getElementById('custom_form_submitter_email_row').style.display = '';

            show_or_hide_custom_form_submitter_email();

            document.getElementById('custom_form_administrator_email_row').style.display = '';

            show_or_hide_custom_form_administrator_email();

            document.getElementById('custom_form_contact_group_id_row').style.display = '';
            document.getElementById('custom_form_membership_row').style.display = '';
            
            show_or_hide_custom_form_membership();

            document.getElementById('custom_form_private_row').style.display = '';

            toggle_custom_form_private();

            // If grant offer rows exist (i.e. commerce is enabled and user has access to commerce),
            // then show them.
            if (document.getElementById('custom_form_offer_row')) {
                document.getElementById('custom_form_offer_row').style.display = '';

                toggle_custom_form_offer();
            }

            document.getElementById('custom_form_confirmation_type_row').style.display = '';

            show_or_hide_custom_form_confirmation_type();

            document.getElementById('custom_form_return_type_row').style.display = '';

            show_or_hide_custom_form_return_type();

            document.getElementById('custom_form_pretty_urls_row').style.display = '';
            
            break;
        
        case 'custom form confirmation':
            document.getElementById('custom_form_confirmation_continue_button_label_row').style.display = '';
            document.getElementById('custom_form_confirmation_next_page_id_row').style.display = '';
            break;
            
        case 'form list view':
            document.getElementById('form_list_view_custom_form_page_id_row').style.display = '';
            document.getElementById('form_list_view_form_item_view_page_id_row').style.display = '';
            document.getElementById('form_list_view_viewer_filter_row').style.display = '';
            show_or_hide_form_list_view_viewer_filter();
            break;
            
        case 'form item view':
            document.getElementById('form_item_view_custom_form_page_id_row').style.display = '';
            document.getElementById('form_item_view_submitter_security_row').style.display = '';
            document.getElementById('form_item_view_submitted_form_editable_by_registered_user_row').style.display = '';
            show_or_hide_form_item_view_editor();

            // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
            // then show row.
            if (document.getElementById('form_item_view_hook_code_row')) {
                document.getElementById('form_item_view_hook_code_row').style.display = '';
            }

            break;
            
        case 'form view directory':
            document.getElementById('form_view_directory_form_list_views_row').style.display = '';
            document.getElementById('form_view_directory_summary_row').style.display = '';
            show_or_hide_form_view_directory_summary();
            document.getElementById('form_view_directory_form_list_view_heading_row').style.display = '';
            document.getElementById('form_view_directory_subject_heading_row').style.display = '';
            document.getElementById('form_view_directory_number_of_submitted_forms_heading_row').style.display = '';
            break;
            
        case 'calendar view':
            document.getElementById('calendar_view_calendars_row').style.display = '';
            document.getElementById('calendar_view_default_view_row').style.display = '';
            document.getElementById('calendar_view_calendar_event_view_page_id_row').style.display = '';
            
            show_or_hide_calendar_view_number_of_upcoming_events();
            break;
            
        case 'calendar event view':
            document.getElementById('calendar_event_view_calendars_row').style.display = '';
            document.getElementById('calendar_event_view_notes_row').style.display = '';
            document.getElementById('calendar_event_view_back_button_label_row').style.display = '';
            break;
            
        case 'catalog':
            document.getElementById('catalog_product_group_id_row').style.display = '';
            document.getElementById('catalog_menu_row').style.display = '';
            document.getElementById('catalog_search_row').style.display = '';
            document.getElementById('catalog_number_of_featured_items_row').style.display = '';
            document.getElementById('catalog_number_of_new_items_row').style.display = '';
            document.getElementById('catalog_number_of_columns_row').style.display = '';
            document.getElementById('catalog_image_width_row').style.display = '';
            document.getElementById('catalog_image_height_row').style.display = '';
            document.getElementById('catalog_back_button_label_row').style.display = '';
            document.getElementById('catalog_catalog_detail_page_id_row').style.display = '';
            break;
            
        case 'catalog detail':
            document.getElementById('catalog_detail_allow_customer_to_add_product_to_order_row').style.display = '';
            show_or_hide_allow_customer_to_add_product_to_order();
            document.getElementById('catalog_detail_back_button_label_row').style.display = '';
            break;

        case 'express order':
            document.getElementById('express_order_shopping_cart_label_row').style.display = '';
            document.getElementById('express_order_quick_add_label_row').style.display = '';
            document.getElementById('express_order_quick_add_product_group_id_row').style.display = '';
            document.getElementById('express_order_product_description_type_row').style.display = '';
            document.getElementById('express_order_shipping_form_row').style.display = '';
            document.getElementById('express_order_special_offer_code_label_row').style.display = '';
            document.getElementById('express_order_special_offer_code_message_row').style.display = '';
            document.getElementById('express_order_custom_field_1_label_row').style.display = '';
            document.getElementById('express_order_custom_field_2_label_row').style.display = '';
            document.getElementById('express_order_po_number_row').style.display = '';
            document.getElementById('express_order_form_row').style.display = '';
            show_or_hide_express_order_custom_billing_form();
            document.getElementById('express_order_card_verification_number_page_id_row').style.display = '';
            
            if (document.getElementById('express_order_offline_payment_always_allowed_row')) {
                document.getElementById('express_order_offline_payment_always_allowed_row').style.display = '';
                document.getElementById('express_order_offline_payment_label_row').style.display = '';
            }
            
            document.getElementById('express_order_terms_page_id_row').style.display = '';
            document.getElementById('express_order_update_button_label_row').style.display = '';
            document.getElementById('express_order_purchase_now_button_label_row').style.display = '';
            document.getElementById('express_order_auto_registration_row').style.display = '';

            // If hook code rows exists (i.e. user is a designer or administrator and hooks are enabled),
            // then show rows.
            if (document.getElementById('express_order_pre_save_hook_code_row')) {
                document.getElementById('express_order_pre_save_hook_code_row').style.display = '';
                document.getElementById('express_order_post_save_hook_code_row').style.display = '';
            }

            document.getElementById('express_order_order_receipt_email_row').style.display = '';
            show_or_hide_express_order_order_receipt_email();
            document.getElementById('express_order_next_page_id_row').style.display = '';
            break;
        
        case 'order form':
            document.getElementById('order_form_product_group_id_row').style.display = '';
            document.getElementById('order_form_product_layout_row_1').style.display = '';
            document.getElementById('order_form_product_layout_row_2').style.display = '';
            document.getElementById('order_form_add_button_label_row').style.display = '';
            document.getElementById('order_form_add_button_next_page_id_row').style.display = '';
            document.getElementById('order_form_skip_button_label_row').style.display = '';
            document.getElementById('order_form_skip_button_next_page_id_row').style.display = '';
            break;

        case 'search results':
            // If search folder row exists (i.e. advanced search is enabled), then show it.
            if (document.getElementById('search_results_search_folder_id_row')) {
                document.getElementById('search_results_search_folder_id_row').style.display = '';
            }

            // if e-commerce is on, then show e-commerce fields for search results
            if (document.getElementById('search_results_search_catalog_items_row')) {
                document.getElementById('search_results_search_catalog_items_row').style.display = '';
            
                show_or_hide_search_catalog_items();
            }
            break;

        case 'shopping cart':
            document.getElementById('shopping_cart_shopping_cart_label_row').style.display = '';
            document.getElementById('shopping_cart_quick_add_label_row').style.display = '';
            document.getElementById('shopping_cart_quick_add_product_group_id_row').style.display = '';
            document.getElementById('shopping_cart_product_description_type_row').style.display = '';
            document.getElementById('shopping_cart_special_offer_code_label_row').style.display = '';
            document.getElementById('shopping_cart_special_offer_code_message_row').style.display = '';
            document.getElementById('shopping_cart_update_button_label_row').style.display = '';
            document.getElementById('shopping_cart_checkout_button_label_row').style.display = '';

            // If hook code row exists (i.e. user is a designer or administrator and hooks are enabled),
            // then show row.
            if (document.getElementById('shopping_cart_hook_code_row')) {
                document.getElementById('shopping_cart_hook_code_row').style.display = '';
            }
            
            document.getElementById('shopping_cart_next_page_id_with_shipping_row').style.display = '';
            document.getElementById('shopping_cart_next_page_id_without_shipping_row').style.display = '';
            break;

        case 'shipping address and arrival':
            document.getElementById('shipping_address_and_arrival_address_type_row').style.display = '';
            show_or_hide_shipping_address_and_arrival_address_type();
            document.getElementById('shipping_address_and_arrival_form_row').style.display = '';
            show_or_hide_custom_shipping_form();
            document.getElementById('shipping_address_and_arrival_submit_button_label_row').style.display = '';
            document.getElementById('shipping_address_and_arrival_next_page_id_row').style.display = '';
            break;

        case 'shipping method':
            document.getElementById('shipping_method_product_description_type_row').style.display = '';
            document.getElementById('shipping_method_submit_button_label_row').style.display = '';
            document.getElementById('shipping_method_next_page_id_row').style.display = '';
            break;

        case 'billing information':
            document.getElementById('billing_information_custom_field_1_label_row').style.display = '';
            document.getElementById('billing_information_custom_field_2_label_row').style.display = '';
            document.getElementById('billing_information_po_number_row').style.display = '';
            document.getElementById('billing_information_form_row').style.display = '';
            show_or_hide_billing_information_custom_billing_form();
            document.getElementById('billing_information_submit_button_label_row').style.display = '';
            document.getElementById('billing_information_next_page_id_row').style.display = '';
            break;

        case 'order preview':
            document.getElementById('order_preview_product_description_type_row').style.display = '';
            document.getElementById('order_preview_card_verification_number_page_id_row').style.display = '';
            
            if (document.getElementById('order_preview_offline_payment_always_allowed_row')) {
                document.getElementById('order_preview_offline_payment_always_allowed_row').style.display = '';
                document.getElementById('order_preview_offline_payment_label_row').style.display = '';
            }
            
            document.getElementById('order_preview_terms_page_id_row').style.display = '';
            document.getElementById('order_preview_submit_button_label_row').style.display = '';
            document.getElementById('order_preview_auto_registration_row').style.display = '';

            // If hook code rows exists (i.e. user is a designer or administrator and hooks are enabled),
            // then show rows.
            if (document.getElementById('order_preview_pre_save_hook_code_row')) {
                document.getElementById('order_preview_pre_save_hook_code_row').style.display = '';
                document.getElementById('order_preview_post_save_hook_code_row').style.display = '';
            }

            document.getElementById('order_preview_order_receipt_email_row').style.display = '';
            show_or_hide_order_preview_order_receipt_email();
            document.getElementById('order_preview_next_page_id_row').style.display = '';
            break;

        case 'order receipt':
            document.getElementById('order_receipt_product_description_type_row').style.display = '';
            break;
            
        case 'affiliate sign up form':
            document.getElementById('affiliate_sign_up_form_terms_page_id_row').style.display = '';
            document.getElementById('affiliate_sign_up_form_submit_button_label_row').style.display = '';
            document.getElementById('affiliate_sign_up_form_next_page_id_row').style.display = '';
            break;
    }
    
    // if the selected page type is a valid page type for the sitemap, then show sitemap row
    if (
        (page_type == 'standard')
        || (page_type == 'folder view')
        || (page_type == 'photo gallery')
        || (page_type == 'custom form')
        || (page_type == 'form list view')
        || (page_type == 'form item view')
        || (page_type == 'form view directory')
        || (page_type == 'calendar view')
        || (page_type == 'calendar event view')
        || (page_type == 'catalog')
        || (page_type == 'catalog detail')
        || (page_type == 'express order')
        || (page_type == 'order form')
        || (page_type == 'shopping cart')
        || (page_type == 'search results')
    ) {
        document.getElementById('search_engine_optimization_heading_row').style.display = '';
        document.getElementById('sitemap_row').style.display = '';
        
    // else the selected page type is not a validate page type for the sitemap, so hide sitemap row
    } else {
        // if the title field does not exist (i.e. this is the create page screen),
        // then hide the search engine optimization heading row also, because there won't be any other fields for that area
        if (!document.getElementById('title')) {
            document.getElementById('search_engine_optimization_heading_row').style.display = 'none';
        }
        
        document.getElementById('sitemap_row').style.display = 'none';
    }
    
    // if the comment fields exist (e.g. edit page screen, not create page screen), then show or hide the form item view comment fields
    if (document.getElementById('comments')) {
        show_or_hide_form_item_view_comment_fields();
    }
    
    // If a custom form was just enabled,
    // then update the submit button to contain "Save & Continue"
    if (
        ((page_type == 'custom form') && (original_page_type != 'custom form'))
        || ((page_type == 'shipping address and arrival') && (document.getElementById('shipping_address_and_arrival_form').checked == true) && (original_shipping_address_and_arrival_form != 1))
        || ((page_type == 'billing information') && (document.getElementById('billing_information_form').checked == true) && (original_billing_information_form != 1))
        || (
            (page_type == 'express order')
            && (
                (document.getElementById('express_order_shipping_form').checked && original_express_order_shipping_form != 1)
                || (document.getElementById('express_order_form').checked && original_express_order_form != 1)
            )
        )
    ) {
        document.getElementById('create_button').value = 'Save & Continue';
        
    // else the submit button should contain the normal "Save"
    } else {
        document.getElementById('create_button').value = 'Save';
    }
}

function show_or_hide_recurring() {
    if (document.getElementById('recurring').checked == true) {
        document.getElementById('recurring_schedule_editable_by_customer_row').style.display = '';
        
        if (document.getElementById('start_row')) {
            document.getElementById('start_row').style.display = '';
        }
        
        document.getElementById('number_of_payments_row').style.display = '';
        document.getElementById('payment_period_row').style.display = '';
        
        if (document.getElementById('recurring_profile_disabled_perform_actions_row')) {
            document.getElementById('recurring_profile_disabled_perform_actions_row').style.display = '';
            show_or_hide_recurring_profile_disabled_perform_actions();
        }
        
        // if the Sage group ID field exists, then show it
        if (document.getElementById('sage_group_id_row')) {
            document.getElementById('sage_group_id_row').style.display = '';
        }
        
    } else {
        document.getElementById('recurring_schedule_editable_by_customer_row').style.display = 'none';
        
        if (document.getElementById('start_row')) {
            document.getElementById('start_row').style.display = 'none';
        }
        
        document.getElementById('number_of_payments_row').style.display = 'none';
        document.getElementById('payment_period_row').style.display = 'none';
        
        if (document.getElementById('recurring_profile_disabled_perform_actions_row')) {
            document.getElementById('recurring_profile_disabled_perform_actions_row').style.display = 'none';
            document.getElementById('recurring_profile_disabled_expire_membership_row').style.display = 'none';
            document.getElementById('recurring_profile_disabled_revoke_private_access_row').style.display = 'none';
            document.getElementById('recurring_profile_disabled_email_row').style.display = 'none';
            document.getElementById('recurring_profile_disabled_email_subject_row').style.display = 'none';
            document.getElementById('recurring_profile_disabled_email_page_id_row').style.display = 'none';
        }
        
        // if the Sage group ID field exists, then hide it
        if (document.getElementById('sage_group_id_row')) {
            document.getElementById('sage_group_id_row').style.display = 'none';
        }
    }
}

function show_or_hide_recurring_profile_disabled_perform_actions()
{
    if (document.getElementById('recurring_profile_disabled_perform_actions').checked == true) {
        document.getElementById('recurring_profile_disabled_expire_membership_row').style.display = '';
        document.getElementById('recurring_profile_disabled_revoke_private_access_row').style.display = '';
        document.getElementById('recurring_profile_disabled_email_row').style.display = '';
        show_or_hide_recurring_profile_disabled_email();
        
    } else {
        document.getElementById('recurring_profile_disabled_expire_membership_row').style.display = 'none';
        document.getElementById('recurring_profile_disabled_revoke_private_access_row').style.display = 'none';
        document.getElementById('recurring_profile_disabled_email_row').style.display = 'none';
        document.getElementById('recurring_profile_disabled_email_subject_row').style.display = 'none';
        document.getElementById('recurring_profile_disabled_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_recurring_profile_disabled_email()
{
    if (document.getElementById('recurring_profile_disabled_email').checked == true) {
        document.getElementById('recurring_profile_disabled_email_subject_row').style.display = '';
        document.getElementById('recurring_profile_disabled_email_page_id_row').style.display = '';
        
    } else {
        document.getElementById('recurring_profile_disabled_email_subject_row').style.display = 'none';
        document.getElementById('recurring_profile_disabled_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_grant_private_access() {
    if (document.getElementById('grant_private_access').checked == true) {
        document.getElementById('private_folder_row').style.display = '';
        document.getElementById('private_days_row').style.display = '';
        document.getElementById('send_to_page_row').style.display = '';
    } else {
        document.getElementById('private_folder_row').style.display = 'none';
        document.getElementById('private_days_row').style.display = 'none';
        document.getElementById('send_to_page_row').style.display = 'none';
    }
}

function show_or_hide_form() {
    // Put create button in variable to be used by function.
    var create_button = document.getElementById('create_button');
    var current_form_state = document.getElementById('current_form_state');

    if (document.getElementById('product_form').checked == true) {
        document.getElementById('form_name_row').style.display = '';
        document.getElementById('form_label_column_width_row').style.display = '';
        document.getElementById('form_quantity_type_row').style.display = '';
        
        // If the page was loaded with the form turned form off.
        if ((!document.getElementById('original_form_state')) || (document.getElementById('original_form_state').value == "0")) {
            
            // Show form notice
            document.getElementById('form_notice').style.display = '';
            
            // If the user is on the edit product page comeback
            if (document.URL.match("edit_product") == "edit_product") {
                // Change the submit button's value to Save and Continue.
                create_button.value = "Save & Continue";
            
            // Else user is on the add product page.
            } else {
                // Change the submit button's value to Create and Continue.
                create_button.value = "Create & Continue";
            }
            // Change value of form state hidden field.
            current_form_state.value = "1";
        }
        
    } else {
        document.getElementById('form_name_row').style.display = 'none';
        document.getElementById('form_label_column_width_row').style.display = 'none';
        document.getElementById('form_quantity_type_row').style.display = 'none';
        
        // Hide form notice
        if (document.getElementById('form_notice').style.display != 'none') {
            document.getElementById('form_notice').style.display = 'none';
        }
        
        // If the user is on the edit product page comeback
        if (document.URL.match("edit_product") == "edit_product") {
            // Change the submit button's value to Save and Continue.
            create_button.value = "Save";
            
        // Else user is on the add product page.
        } else {
            
            // Change the submit button's value to Create.
            create_button.value = "Create";
        }
        // Change value of form state hidden field.
        current_form_state.value = "0";
    }
}

function show_or_hide_social_networking()
{
    // If social networking is checked, then determine what rows should be shown.
    if (document.getElementById('social_networking').checked == true) {
        document.getElementById('social_networking_type_row').style.display = '';
        show_or_hide_social_networking_type();

    // Otherwise social networking is not checked, so hide rows.
    } else {
        document.getElementById('social_networking_type_row').style.display = 'none';
        document.getElementById('social_networking_services_row').style.display = 'none';
        document.getElementById('social_networking_code_row').style.display = 'none';
    }
}

function show_or_hide_social_networking_type()
{
    // If the "simple" option is selected, then show services row and hide code row.
    if (document.getElementById('social_networking_type_simple').checked == true) {
        document.getElementById('social_networking_services_row').style.display = '';
        document.getElementById('social_networking_code_row').style.display = 'none';
    
    // Otherwise the "advanced" option is selected, so hide services row and show code row.
    } else {
        document.getElementById('social_networking_services_row').style.display = 'none';
        document.getElementById('social_networking_code_row').style.display = '';
    }
}

function show_or_hide_membership_expiration_warning_email() {
    if (document.getElementById('membership_expiration_warning_email').checked == true) {
        document.getElementById('membership_expiration_warning_email_subject').style.display = '';
        document.getElementById('membership_expiration_warning_email_page_id').style.display = '';
        document.getElementById('membership_expiration_warning_email_days_before_expiration').style.display = '';
    } else {
        document.getElementById('membership_expiration_warning_email_subject').style.display = 'none';
        document.getElementById('membership_expiration_warning_email_page_id').style.display = 'none';
        document.getElementById('membership_expiration_warning_email_days_before_expiration').style.display = 'none';
    }
}

function show_or_hide_ecommerce() {
    if (document.getElementById('ecommerce').checked == true) {
        document.getElementById('ecommerce_multicurrency_row').style.display = '';
        document.getElementById('ecommerce_tax_row').style.display = '';
        show_or_hide_ecommerce_tax();
        document.getElementById('ecommerce_shipping_row').style.display = '';
        show_or_hide_ecommerce_shipping();
        document.getElementById('ecommerce_next_order_number_row').style.display = '';
        document.getElementById('ecommerce_email_address_row').style.display = '';
        document.getElementById('ecommerce_gift_card_row').style.display = '';
        show_or_hide_ecommerce_gift_card();
        document.getElementById('ecommerce_payment_methods_row').style.display = '';
        document.getElementById('ecommerce_credit_debit_card_row').style.display = '';
        show_or_hide_ecommerce_credit_debit_card();
        document.getElementById('ecommerce_paypal_express_checkout_row').style.display = '';
        show_or_hide_ecommerce_paypal_express_checkout();
        document.getElementById('ecommerce_offline_payment_row').style.display = '';
        show_or_hide_ecommerce_offline_payment();
        document.getElementById('ecommerce_private_folder_id_row').style.display = '';
        document.getElementById('ecommerce_retrieve_order_next_page_id_row').style.display = '';
        document.getElementById('ecommerce_reward_program_row').style.display = '';
        show_or_hide_ecommerce_reward_program();
        document.getElementById('ecommerce_custom_product_field_1_label_row').style.display = '';
        document.getElementById('ecommerce_custom_product_field_2_label_row').style.display = '';
        document.getElementById('ecommerce_custom_product_field_3_label_row').style.display = '';
        document.getElementById('ecommerce_custom_product_field_4_label_row').style.display = '';
        
    } else {
        document.getElementById('ecommerce_multicurrency_row').style.display = 'none';
        document.getElementById('ecommerce_tax_row').style.display = 'none';
        document.getElementById('ecommerce_tax_exempt_row').style.display = 'none';
        document.getElementById('ecommerce_tax_exempt_label_row').style.display = 'none';
        document.getElementById('ecommerce_shipping_row').style.display = 'none';
        document.getElementById('ecommerce_recipient_mode_row').style.display = 'none';
        document.getElementById('usps_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_address_verification_row').style.display = 'none';
        document.getElementById('ecommerce_address_verification_enforcement_type_row').style.display = 'none';
        document.getElementById('ups_row').style.display = 'none';
        document.getElementById('ups_key_row').style.display = 'none';
        document.getElementById('ups_user_id_row').style.display = 'none';
        document.getElementById('ups_password_row').style.display = 'none';
        document.getElementById('ups_account_row').style.display = 'none';
        document.getElementById('fedex_row').style.display = 'none';
        document.getElementById('fedex_key_row').style.display = 'none';
        document.getElementById('fedex_password_row').style.display = 'none';
        document.getElementById('fedex_account_row').style.display = 'none';
        document.getElementById('fedex_meter_row').style.display = 'none';
        document.getElementById('ecommerce_product_restriction_message_row').style.display = 'none';
        document.getElementById('ecommerce_no_shipping_methods_message_row').style.display = 'none';
        document.getElementById('ecommerce_end_of_day_time_row').style.display = 'none';
        document.getElementById('ecommerce_next_order_number_row').style.display = 'none';
        document.getElementById('ecommerce_email_address_row').style.display = 'none';
        document.getElementById('ecommerce_gift_card_row').style.display = 'none';
        document.getElementById('ecommerce_gift_card_validity_days_row').style.display = 'none';
        document.getElementById('ecommerce_givex_row').style.display = 'none';
        document.getElementById('ecommerce_givex_primary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_secondary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_givex_password_row').style.display = 'none';
        document.getElementById('ecommerce_payment_methods_row').style.display = 'none';
        document.getElementById('ecommerce_credit_debit_card_row').style.display = 'none';
        document.getElementById('ecommerce_accepted_cards_row').style.display = 'none';
        document.getElementById('ecommerce_payment_gateway_row').style.display = 'none';
        document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = 'none';
        document.getElementById('ecommerce_payment_gateway_mode_row').style.display = 'none';
        document.getElementById('ecommerce_authorizenet_api_login_id_row').style.display = 'none';
        document.getElementById('ecommerce_authorizenet_transaction_key_row').style.display = 'none';
        document.getElementById('ecommerce_clearcommerce_client_id_row').style.display = 'none';
        document.getElementById('ecommerce_clearcommerce_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_clearcommerce_password_row').style.display = 'none';
        document.getElementById('ecommerce_first_data_global_gateway_store_number_row').style.display = 'none';
        document.getElementById('ecommerce_first_data_global_gateway_pem_file_name_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payflow_pro_partner_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payflow_pro_merchant_login_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payflow_pro_user_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payflow_pro_password_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payments_pro_gateway_mode_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payments_pro_api_username_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payments_pro_api_password_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_payments_pro_api_signature_row').style.display = 'none';
        document.getElementById('ecommerce_sage_merchant_id_row').style.display = 'none';
        document.getElementById('ecommerce_sage_merchant_key_row').style.display = 'none';
        document.getElementById('ecommerce_stripe_api_key_row').style.display = 'none';
        document.getElementById('ecommerce_iyzipay_api_key_row').style.display = 'none';
        document.getElementById('ecommerce_iyzipay_secret_key_row').style.display = 'none';
        document.getElementById('ecommerce_iyzipay_installment_row').style.display = 'none';
        document.getElementById('ecommerce_iyzipay_threeds_row').style.display = 'none';
        document.getElementById('ecommerce_surcharge_percentage_row').style.display = 'none';
        document.getElementById('ecommerce_reset_encryption_key_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_transaction_type_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_mode_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_username_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_password_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_signature_row').style.display = 'none';
        document.getElementById('ecommerce_offline_payment_row').style.display = 'none';
        document.getElementById('ecommerce_offline_payment_only_specific_orders_row').style.display = 'none';
        document.getElementById('ecommerce_private_folder_id_row').style.display = 'none';
        document.getElementById('ecommerce_retrieve_order_next_page_id_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_points_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_membership_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_membership_days_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_bcc_email_address_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_subject_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_page_id_row').style.display = 'none';
        document.getElementById('ecommerce_custom_product_field_1_label_row').style.display = 'none';
        document.getElementById('ecommerce_custom_product_field_2_label_row').style.display = 'none';
        document.getElementById('ecommerce_custom_product_field_3_label_row').style.display = 'none';
        document.getElementById('ecommerce_custom_product_field_4_label_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_tax() {
    if (document.getElementById('ecommerce_tax').checked == true) {
        document.getElementById('ecommerce_tax_exempt_row').style.display = '';
        show_or_hide_ecommerce_tax_exempt();
    } else {
        document.getElementById('ecommerce_tax_exempt_row').style.display = 'none';
        document.getElementById('ecommerce_tax_exempt_label_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_tax_exempt() {
    if (document.getElementById('ecommerce_tax_exempt').checked == true) {
        document.getElementById('ecommerce_tax_exempt_label_row').style.display = '';
    } else {
        document.getElementById('ecommerce_tax_exempt_label_row').style.display = 'none';
    }
}

function show_or_hide_search_catalog_items()
{
    if (document.getElementById('search_results_search_catalog_items').checked == true) {
        document.getElementById('search_results_product_group_id_row').style.display = '';
        document.getElementById('search_results_catalog_detail_page_id_row').style.display = '';
        
    } else {
        document.getElementById('search_results_product_group_id_row').style.display = 'none';
        document.getElementById('search_results_catalog_detail_page_id_row').style.display = 'none';
    }
}

function show_or_hide_calendar_view_number_of_upcoming_events()
{
    if ((document.getElementById('calendar_view_default_view').options[document.getElementById('calendar_view_default_view').selectedIndex].value == 'upcoming')
        && (document.getElementById('calendar_view_number_of_upcoming_events_row').style.display == 'none')) {
        document.getElementById('calendar_view_number_of_upcoming_events_row').style.display = '';
        
    } else {
        document.getElementById('calendar_view_number_of_upcoming_events_row').style.display = 'none';
    }
}

function show_or_hide_update_address_book_address_type()
{
    if (document.getElementById('update_address_book_address_type').checked == true) {
        document.getElementById('update_address_book_address_type_page_id_row').style.display = '';
        
    } else {
        document.getElementById('update_address_book_address_type_page_id_row').style.display = 'none';
    }
}

function show_or_hide_shipping_address_and_arrival_address_type()
{
    if (document.getElementById('shipping_address_and_arrival_address_type').checked == true) {
        document.getElementById('shipping_address_and_arrival_address_type_page_id_row').style.display = '';
        
    } else {
        document.getElementById('shipping_address_and_arrival_address_type_page_id_row').style.display = 'none';
    }
}

function show_or_hide_custom_shipping_form()
{
    if (document.getElementById('shipping_address_and_arrival_form').checked == true) {
        document.getElementById('shipping_address_and_arrival_form_name_row').style.display = '';
        document.getElementById('shipping_address_and_arrival_form_label_column_width_row').style.display = '';
        
    } else {
        document.getElementById('shipping_address_and_arrival_form_name_row').style.display = 'none';
        document.getElementById('shipping_address_and_arrival_form_label_column_width_row').style.display = 'none';
    }
    
    // if the form is enabled and the form was not originally enabled, then show notice and update the submit button to contain "Save & Continue"
    if ((document.getElementById('shipping_address_and_arrival_form').checked == true) && (original_shipping_address_and_arrival_form != 1)) {
        document.getElementById('shipping_address_and_arrival_form_notice').style.display = '';
        document.getElementById('create_button').value = 'Save & Continue';
        
    // else the form is disabled or the form was already enabled, so do not show notice and update the submit button to contain "Save"
    } else {
        document.getElementById('shipping_address_and_arrival_form_notice').style.display = 'none';
        document.getElementById('create_button').value = 'Save';
    }
}

function toggle_express_order_custom_shipping_form() {
    
    // If the form is enabled and the form was not originally enabled, then show notice and update
    // the submit button to contain "Save & Continue"
    if (
        document.getElementById('express_order_shipping_form').checked
        && original_express_order_shipping_form != 1
    ) {
        document.getElementById('express_order_shipping_form_notice').style.display = '';
        document.getElementById('create_button').value = 'Save & Continue';
        
    // Otherwise the form is disabled or the form was already enabled, so do not show notice and
    // update the submit button to contain "Save"
    } else {
        document.getElementById('express_order_shipping_form_notice').style.display = 'none';
        document.getElementById('create_button').value = 'Save';
    }
}

function show_or_hide_express_order_custom_billing_form()
{
    if (document.getElementById('express_order_form').checked == true) {
        document.getElementById('express_order_form_name_row').style.display = '';
        document.getElementById('express_order_form_label_column_width_row').style.display = '';
        
    } else {
        document.getElementById('express_order_form_name_row').style.display = 'none';
        document.getElementById('express_order_form_label_column_width_row').style.display = 'none';
    }
    
    // if the form is enabled and the form was not originally enabled, then show notice and update the submit button to contain "Save & Continue"
    if ((document.getElementById('express_order_form').checked == true) && (original_express_order_form != 1)) {
        document.getElementById('express_order_form_notice').style.display = '';
        document.getElementById('create_button').value = 'Save & Continue';
        
    // else the form is disabled or the form was already enabled, so do not show notice and update the submit button to contain "Save"
    } else {
        document.getElementById('express_order_form_notice').style.display = 'none';
        document.getElementById('create_button').value = 'Save';
    }
}

function show_or_hide_billing_information_custom_billing_form()
{
    if (document.getElementById('billing_information_form').checked == true) {
        document.getElementById('billing_information_form_name_row').style.display = '';
        document.getElementById('billing_information_form_label_column_width_row').style.display = '';
        
    } else {
        document.getElementById('billing_information_form_name_row').style.display = 'none';
        document.getElementById('billing_information_form_label_column_width_row').style.display = 'none';
    }
    
    // if the form is enabled and the form was not originally enabled, then show notice and update the submit button to contain "Save & Continue"
    if ((document.getElementById('billing_information_form').checked == true) && (original_billing_information_form != 1)) {
        document.getElementById('billing_information_form_notice').style.display = '';
        document.getElementById('create_button').value = 'Save & Continue';
        
    // else the form is disabled or the form was already enabled, so do not show notice and update the submit button to contain "Save"
    } else {
        document.getElementById('billing_information_form_notice').style.display = 'none';
        document.getElementById('create_button').value = 'Save';
    }
}

function show_or_hide_ecommerce_shipping() {
    if (document.getElementById('ecommerce_shipping').checked == true) {
        document.getElementById('ecommerce_recipient_mode_row').style.display = '';
        document.getElementById('usps_user_id_row').style.display = '';
        document.getElementById('ecommerce_address_verification_row').style.display = '';
        show_or_hide_ecommerce_address_verification();
        document.getElementById('ups_row').style.display = '';
        toggle_ups();
        document.getElementById('fedex_row').style.display = '';
        toggle_fedex();
        document.getElementById('ecommerce_product_restriction_message_row').style.display = '';
        document.getElementById('ecommerce_no_shipping_methods_message_row').style.display = '';
        document.getElementById('ecommerce_end_of_day_time_row').style.display = '';
    } else {
        document.getElementById('ecommerce_recipient_mode_row').style.display = 'none';
        document.getElementById('usps_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_address_verification_row').style.display = 'none';
        document.getElementById('ecommerce_address_verification_enforcement_type_row').style.display = 'none';
        document.getElementById('ups_row').style.display = 'none';
        document.getElementById('ups_key_row').style.display = 'none';
        document.getElementById('ups_user_id_row').style.display = 'none';
        document.getElementById('ups_password_row').style.display = 'none';
        document.getElementById('ups_account_row').style.display = 'none';
        document.getElementById('fedex_row').style.display = 'none';
        document.getElementById('fedex_key_row').style.display = 'none';
        document.getElementById('fedex_password_row').style.display = 'none';
        document.getElementById('fedex_account_row').style.display = 'none';
        document.getElementById('fedex_meter_row').style.display = 'none';
        document.getElementById('ecommerce_product_restriction_message_row').style.display = 'none';
        document.getElementById('ecommerce_no_shipping_methods_message_row').style.display = 'none';
        document.getElementById('ecommerce_end_of_day_time_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_address_verification() {
    if (document.getElementById('ecommerce_address_verification').checked == true) {
        document.getElementById('ecommerce_address_verification_enforcement_type_row').style.display = '';
    } else {
        document.getElementById('ecommerce_address_verification_enforcement_type_row').style.display = 'none';
    }
}

function toggle_ups() {

    if ($('#ups').is(':checked')) {
        $('#ups_key_row').fadeIn();
        $('#ups_user_id_row').fadeIn();
        $('#ups_password_row').fadeIn();
        $('#ups_account_row').fadeIn();

    } else {
        $('#ups_key_row').fadeOut();
        $('#ups_user_id_row').fadeOut();
        $('#ups_password_row').fadeOut();
        $('#ups_account_row').fadeOut();
    }
}

function toggle_fedex() {

    if ($('#fedex').is(':checked')) {
        $('#fedex_key_row').fadeIn();
        $('#fedex_password_row').fadeIn();
        $('#fedex_account_row').fadeIn();
        $('#fedex_meter_row').fadeIn();

    } else {
        $('#fedex_key_row').fadeOut();
        $('#fedex_password_row').fadeOut();
        $('#fedex_account_row').fadeOut();
        $('#fedex_meter_row').fadeOut();
    }
}

function show_or_hide_ecommerce_gift_card()
{
    if (document.getElementById('ecommerce_gift_card').checked == true) {
        document.getElementById('ecommerce_gift_card_validity_days_row').style.display = '';
        document.getElementById('ecommerce_givex_row').style.display = '';
        show_or_hide_ecommerce_givex();
        
    } else {
        document.getElementById('ecommerce_gift_card_validity_days_row').style.display = 'none';
        document.getElementById('ecommerce_givex_row').style.display = 'none';
        document.getElementById('ecommerce_givex_primary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_secondary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_givex_password_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_givex()
{
    if (document.getElementById('ecommerce_givex').checked == true) {
        document.getElementById('ecommerce_givex_primary_hostname_row').style.display = '';
        document.getElementById('ecommerce_givex_secondary_hostname_row').style.display = '';
        document.getElementById('ecommerce_givex_user_id_row').style.display = '';
        document.getElementById('ecommerce_givex_password_row').style.display = '';
        
    } else {
        document.getElementById('ecommerce_givex_primary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_secondary_hostname_row').style.display = 'none';
        document.getElementById('ecommerce_givex_user_id_row').style.display = 'none';
        document.getElementById('ecommerce_givex_password_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_credit_debit_card()
{
    if (document.getElementById('ecommerce_credit_debit_card').checked == true) {
        document.getElementById('ecommerce_accepted_cards_row').style.display = '';
        document.getElementById('ecommerce_payment_gateway_row').style.display = '';
        document.getElementById('ecommerce_surcharge_percentage_row').style.display = '';
        document.getElementById('ecommerce_reset_encryption_key_row').style.display = '';
    } else {
        document.getElementById('ecommerce_accepted_cards_row').style.display = 'none';
        document.getElementById('ecommerce_payment_gateway_row').style.display = 'none';
        document.getElementById('ecommerce_surcharge_percentage_row').style.display = 'none';
        document.getElementById('ecommerce_reset_encryption_key_row').style.display = 'none';
    }
    
    show_or_hide_ecommerce_payment_gateway();
}

function show_or_hide_ecommerce_offline_payment()
{
    if (document.getElementById('ecommerce_offline_payment').checked == true) {
        document.getElementById('ecommerce_offline_payment_only_specific_orders_row').style.display = '';
    } else {
        document.getElementById('ecommerce_offline_payment_only_specific_orders_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_paypal_express_checkout()
{
    if (document.getElementById('ecommerce_paypal_express_checkout').checked == true) {
        document.getElementById('ecommerce_paypal_express_checkout_transaction_type_row').style.display = '';
        document.getElementById('ecommerce_paypal_express_checkout_mode_row').style.display = '';
        document.getElementById('ecommerce_paypal_express_checkout_api_username_row').style.display = '';
        document.getElementById('ecommerce_paypal_express_checkout_api_password_row').style.display = '';
        document.getElementById('ecommerce_paypal_express_checkout_api_signature_row').style.display = '';
    } else {
        document.getElementById('ecommerce_paypal_express_checkout_transaction_type_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_mode_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_username_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_password_row').style.display = 'none';
        document.getElementById('ecommerce_paypal_express_checkout_api_signature_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_payment_gateway() {
    // hide all payment gateway fields until we determine which should be displayed
    document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = 'none';
    document.getElementById('ecommerce_payment_gateway_mode_row').style.display = 'none';
    document.getElementById('ecommerce_authorizenet_api_login_id_row').style.display = 'none';
    document.getElementById('ecommerce_authorizenet_transaction_key_row').style.display = 'none';
    document.getElementById('ecommerce_clearcommerce_client_id_row').style.display = 'none';
    document.getElementById('ecommerce_clearcommerce_user_id_row').style.display = 'none';
    document.getElementById('ecommerce_clearcommerce_password_row').style.display = 'none';
    document.getElementById('ecommerce_first_data_global_gateway_store_number_row').style.display = 'none';
    document.getElementById('ecommerce_first_data_global_gateway_pem_file_name_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payflow_pro_partner_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payflow_pro_merchant_login_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payflow_pro_user_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payflow_pro_password_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payments_pro_gateway_mode_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payments_pro_api_username_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payments_pro_api_password_row').style.display = 'none';
    document.getElementById('ecommerce_paypal_payments_pro_api_signature_row').style.display = 'none';
    document.getElementById('ecommerce_sage_merchant_id_row').style.display = 'none';
    document.getElementById('ecommerce_sage_merchant_key_row').style.display = 'none';
    document.getElementById('ecommerce_stripe_api_key_row').style.display = 'none';
    document.getElementById('ecommerce_iyzipay_api_key_row').style.display = 'none';
    document.getElementById('ecommerce_iyzipay_secret_key_row').style.display = 'none';
    document.getElementById('ecommerce_iyzipay_installment_row').style.display = 'none';
    document.getElementById('ecommerce_iyzipay_threeds_row').style.display = 'none';


    // if credit/debit card is checked, the prepare to show fields
    if (document.getElementById('ecommerce_credit_debit_card').checked == true) {
        // show different fields depending on payment gateway choice
        switch (document.getElementById('ecommerce_payment_gateway').options[document.getElementById('ecommerce_payment_gateway').selectedIndex].value) {
            case 'Authorize.Net':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_payment_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_authorizenet_api_login_id_row').style.display = '';
                document.getElementById('ecommerce_authorizenet_transaction_key_row').style.display = '';
                break;
                
            case 'ClearCommerce':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_payment_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_clearcommerce_client_id_row').style.display = '';
                document.getElementById('ecommerce_clearcommerce_user_id_row').style.display = '';
                document.getElementById('ecommerce_clearcommerce_password_row').style.display = '';
                break;
                
            case 'First Data Global Gateway':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_payment_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_first_data_global_gateway_store_number_row').style.display = '';
                document.getElementById('ecommerce_first_data_global_gateway_pem_file_name_row').style.display = '';
                break;
            
            case 'PayPal Payflow Pro':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_payment_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_paypal_payflow_pro_partner_row').style.display = '';
                document.getElementById('ecommerce_paypal_payflow_pro_merchant_login_row').style.display = '';
                document.getElementById('ecommerce_paypal_payflow_pro_user_row').style.display = '';
                document.getElementById('ecommerce_paypal_payflow_pro_password_row').style.display = '';
                break;
                
            case 'PayPal Payments Pro':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_paypal_payments_pro_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_paypal_payments_pro_api_username_row').style.display = '';
                document.getElementById('ecommerce_paypal_payments_pro_api_password_row').style.display = '';
                document.getElementById('ecommerce_paypal_payments_pro_api_signature_row').style.display = '';
                break;
                
            case 'Sage':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_sage_merchant_id_row').style.display = '';
                document.getElementById('ecommerce_sage_merchant_key_row').style.display = '';
                break;

            case 'Stripe':
                document.getElementById('ecommerce_payment_gateway_transaction_type_row').style.display = '';
                document.getElementById('ecommerce_stripe_api_key_row').style.display = '';
                break;

            case 'Iyzipay':
                document.getElementById('ecommerce_payment_gateway_mode_row').style.display = '';
                document.getElementById('ecommerce_iyzipay_api_key_row').style.display = '';
                document.getElementById('ecommerce_iyzipay_secret_key_row').style.display = '';
                document.getElementById('ecommerce_iyzipay_installment_row').style.display = '';
                document.getElementById('ecommerce_iyzipay_threeds_row').style.display = '';
                break;
        }
    }
}

function show_or_hide_ecommerce_reward_program() {
    if (document.getElementById('ecommerce_reward_program').checked == true) {
        document.getElementById('ecommerce_reward_program_points_row').style.display = '';
        document.getElementById('ecommerce_reward_program_membership_row').style.display = '';
        show_or_hide_ecommerce_reward_program_membership();
        document.getElementById('ecommerce_reward_program_email_row').style.display = '';
        show_or_hide_ecommerce_reward_program_email();
        
    } else {
        document.getElementById('ecommerce_reward_program_points_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_membership_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_membership_days_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_bcc_email_address_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_subject_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_reward_program_membership() {
    if (document.getElementById('ecommerce_reward_program_membership').checked == true) {
        document.getElementById('ecommerce_reward_program_membership_days_row').style.display = '';
    } else {
        document.getElementById('ecommerce_reward_program_membership_days_row').style.display = 'none';
    }
}

function show_or_hide_ecommerce_reward_program_email() {
    if (document.getElementById('ecommerce_reward_program_email').checked == true) {
        document.getElementById('ecommerce_reward_program_email_bcc_email_address_row').style.display = '';
        document.getElementById('ecommerce_reward_program_email_subject_row').style.display = '';
        document.getElementById('ecommerce_reward_program_email_page_id_row').style.display = '';
    } else {
        document.getElementById('ecommerce_reward_program_email_bcc_email_address_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_subject_row').style.display = 'none';
        document.getElementById('ecommerce_reward_program_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_shippable() {
    if (document.getElementById('shippable').checked == true) {
        document.getElementById('weight_row').style.display = '';
        document.getElementById('primary_weight_points_row').style.display = '';
        document.getElementById('secondary_weight_points_row').style.display = '';
        document.getElementById('dimensions_row').style.display = '';
        document.getElementById('container_required_row').style.display = '';
        document.getElementById('preparation_time_row').style.display = '';
        document.getElementById('free_shipping_row').style.display = '';
        show_or_hide_free_shipping();
        document.getElementById('allowed_zones_row').style.display = '';
    } else {
        document.getElementById('weight_row').style.display = 'none';
        document.getElementById('primary_weight_points_row').style.display = 'none';
        document.getElementById('secondary_weight_points_row').style.display = 'none';
        document.getElementById('dimensions_row').style.display = 'none';
        document.getElementById('container_required_row').style.display = 'none';
        document.getElementById('preparation_time_row').style.display = 'none';
        document.getElementById('free_shipping_row').style.display = 'none';
        document.getElementById('extra_shipping_cost_row').style.display = 'none';
        document.getElementById('allowed_zones_row').style.display = 'none';
    }
}

function show_or_hide_free_shipping() {
    if (document.getElementById('free_shipping').checked == true) {
        document.getElementById('extra_shipping_cost_row').style.display = 'none';
    } else {
        document.getElementById('extra_shipping_cost_row').style.display = '';
    }
}

function show_or_hide_inventory() {
    if (document.getElementById('inventory').checked == true) {
        document.getElementById('inventory_quantity_row').style.display = '';
        document.getElementById('backorder_row').style.display = '';
        document.getElementById('out_of_stock_message_row').style.display = '';
    } else {
        document.getElementById('inventory_quantity_row').style.display = 'none';
        document.getElementById('backorder_row').style.display = 'none';
        document.getElementById('out_of_stock_message_row').style.display = 'none';
    }
}

function show_or_hide_commissionable() {
    if (document.getElementById('commissionable').checked == true) {
        document.getElementById('commission_rate_limit_row').style.display = '';
    } else {
        document.getElementById('commission_rate_limit_row').style.display = 'none';
    }
}

function show_or_hide_affiliate_program() {
    if (document.getElementById('affiliate_program').checked == true) {
        document.getElementById('affiliate_default_commission_rate_row').style.display = '';
        document.getElementById('affiliate_automatic_approval_row').style.display = '';
        document.getElementById('affiliate_contact_group_id_row').style.display = '';
        document.getElementById('affiliate_email_address_row').style.display = '';
        document.getElementById('affiliate_group_offer_id_row').style.display = '';
    } else {
        document.getElementById('affiliate_default_commission_rate_row').style.display = 'none';
        document.getElementById('affiliate_automatic_approval_row').style.display = 'none';
        document.getElementById('affiliate_contact_group_id_row').style.display = 'none';
        document.getElementById('affiliate_email_address_row').style.display = 'none';
        document.getElementById('affiliate_group_offer_id_row').style.display = 'none';
    }
}

function show_or_hide_google_analytics() {
    if (document.getElementById('google_analytics').checked == true) {
        document.getElementById('google_analytics_web_property_id_row').style.display = '';
    } else {
        document.getElementById('google_analytics_web_property_id_row').style.display = 'none';
    }
}

function show_or_hide_upsell() {
    if (document.getElementById('upsell').checked == true) {
        document.getElementById('upsell_message_row').style.display = '';
        document.getElementById('upsell_triggers_row').style.display = '';
        document.getElementById('upsell_trigger_subtotal_row').style.display = '';
        document.getElementById('upsell_and_or_row').style.display = '';
        document.getElementById('upsell_trigger_quantity_row').style.display = '';
        document.getElementById('upsell_action_button_label_row').style.display = '';
        document.getElementById('upsell_action_page_id_row').style.display = '';
    } else {
        document.getElementById('upsell_message_row').style.display = 'none';
        document.getElementById('upsell_triggers_row').style.display = 'none';
        document.getElementById('upsell_trigger_subtotal_row').style.display = 'none';
        document.getElementById('upsell_and_or_row').style.display = 'none';
        document.getElementById('upsell_trigger_quantity_row').style.display = 'none';
        document.getElementById('upsell_action_button_label_row').style.display = 'none';
        document.getElementById('upsell_action_page_id_row').style.display = 'none';
    }
}

function show_or_hide_multiple_recipients() {
    if (document.getElementById('order').checked == true) {
        document.getElementById('multiple_recipients_row').style.display = 'none';
    } else {
        document.getElementById('multiple_recipients_row').style.display = '';
    }
}

function show_or_hide_email_subscription() {
    if (document.getElementById('email_subscription').checked == true) {
        document.getElementById('email_subscription_type_row').style.display = '';
        document.getElementById('description_row').style.display = '';
        document.getElementById('description_heading_row').style.display = '';
    } else {
        document.getElementById('email_subscription_type_row').style.display = 'none';
        document.getElementById('description_row').style.display = 'none';
        document.getElementById('description_heading_row').style.display = 'none';
    }
}


function show_or_hide_contact_group_opt_in(contact_group_id) {
    if (document.getElementById('contact_group_' + contact_group_id).checked == true) {
        document.getElementById('contact_group_opt_in_cell_' + contact_group_id).style.display = '';
    } else {
        document.getElementById('contact_group_opt_in_cell_' + contact_group_id).style.display = 'none';
    }
}

function show_or_hide_reservations() {
    if (document.getElementById('reservations').checked == true) {
        // If recurrence is enabled then show separate reservations field.
        if (document.getElementById('recurrence').checked == true) {
            document.getElementById('separate_reservations_row').style.display = '';
        }
        
        document.getElementById('limit_reservations_row').style.display = '';
        show_or_hide_limit_reservations();
        document.getElementById('reserve_button_label_row').style.display = '';
        document.getElementById('product_id_row').style.display = '';
        document.getElementById('next_page_id_row').style.display = '';
    } else {
        document.getElementById('separate_reservations_row').style.display = 'none';
        document.getElementById('limit_reservations_row').style.display = 'none';
        document.getElementById('number_of_initial_spots_row').style.display = 'none';
        
        // if number of remaining spots exists, then hide it (exists on edit screen but not create screen)
        if (document.getElementById('number_of_remaining_spots_row')) {
            document.getElementById('number_of_remaining_spots_row').style.display = 'none';
        }
        
        document.getElementById('no_remaining_spots_message_row').style.display = 'none';
        document.getElementById('reserve_button_label_row').style.display = 'none';
        document.getElementById('product_id_row').style.display = 'none';
        document.getElementById('next_page_id_row').style.display = 'none';
    }
}

function show_or_hide_separate_reservations() {
    // if this is the create calendar event screen or this is a recurring event and separate reservations is enabled and limit reservations is enabled,
    // then show number of initial spots
    if (
        (!document.getElementById('number_of_remaining_spots_row'))
        ||
        (
            (document.getElementById('recurrence').checked == true)
            && (document.getElementById('separate_reservations').checked == true)
            && (document.getElementById('limit_reservations').checked == true)
        )
    ) {
        document.getElementById('number_of_initial_spots_row').style.display = '';
    
    // else do not show number of initial spots
    } else {
        document.getElementById('number_of_initial_spots_row').style.display = 'none';
    }
}

function show_or_hide_limit_reservations() {
    // if limit reservations is enabled then determine which sub-fields should be shown
    if (document.getElementById('limit_reservations').checked == true) {
        // if this is the create calendar event screen or this is a recurring event and separate reservations is enabled,
        // then show number of initial spots
        if (
            (!document.getElementById('number_of_remaining_spots_row'))
            ||
            (
                (document.getElementById('recurrence').checked == true)
                && (document.getElementById('separate_reservations').checked == true)
            )
        ) {
            document.getElementById('number_of_initial_spots_row').style.display = '';
        }
        
        // if number of remaining spots exists, then show it (exists on edit screen but not create screen)
        if (document.getElementById('number_of_remaining_spots_row')) {
            document.getElementById('number_of_remaining_spots_row').style.display = '';
        }
        
        document.getElementById('no_remaining_spots_message_row').style.display = '';
        
    // else limit reservations is not enabled, so hide sub-fields
    } else {
        document.getElementById('number_of_initial_spots_row').style.display = 'none';
        
        // if number of remaining spots exists, then hide it (exists on edit screen but not create screen)
        if (document.getElementById('number_of_remaining_spots_row')) {
            document.getElementById('number_of_remaining_spots_row').style.display = 'none';
        }
        
        document.getElementById('no_remaining_spots_message_row').style.display = 'none';
    }
}

function show_or_hide_calendar_access() {
    if (document.getElementById('manage_calendars').checked == true) {
        document.getElementById('calendar_access').style.display = '';
        document.getElementById('publish_calendar_events_container').style.display = '';
    } else {
        document.getElementById('calendar_access').style.display = 'none';
        document.getElementById('publish_calendar_events_container').style.display = 'none';
    }
}

function show_or_hide_contact_group_access() {
    if ((document.getElementById('manage_contacts').checked == true) || (document.getElementById('manage_emails').checked == true)) {
        document.getElementById('contact_group_access').style.display = '';
    } else {
        document.getElementById('contact_group_access').style.display = 'none';
    }
}

function show_or_hide_ecommerce_access() {
    if (document.getElementById('manage_ecommerce')) {
        if (document.getElementById('manage_ecommerce').checked == true) {
            document.getElementById('view_card_data_container').style.display = '';
        } else {
            document.getElementById('view_card_data_container').style.display = 'none';
        }
    }
}

function show_or_hide_view_expiration_date(folder_id) {
    if (document.getElementById('view_' + folder_id).checked == true) {
        document.getElementById('view_' + folder_id + '_expiration_date_container').style.display = '';

        $('#view_' + folder_id + '_expiration_date').datepicker({
            dateFormat: date_picker_format
        });

    } else {
        document.getElementById('view_' + folder_id + '_expiration_date_container').style.display = 'none';

        $('#view_' + folder_id + '_expiration_date').datepicker('destroy');
    }
}

function show_or_hide_quiz() {
    if (document.getElementById('custom_form_quiz').checked == true) {
        document.getElementById('custom_form_quiz_pass_percentage_row').style.display = '';
    } else {
        document.getElementById('custom_form_quiz_pass_percentage_row').style.display = 'none';
    }
}

function show_or_hide_custom_form_submitter_email() {
    if (document.getElementById('custom_form_submitter_email').checked == true) {
        document.getElementById('custom_form_submitter_email_from_email_address_row').style.display = '';
        document.getElementById('custom_form_submitter_email_subject_row').style.display = '';
        document.getElementById('custom_form_submitter_email_format_row').style.display = '';
        show_or_hide_custom_form_submitter_email_format();
        
    } else {
        document.getElementById('custom_form_submitter_email_from_email_address_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_subject_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_format_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_body_row').style.display = 'none';
        document.getElementById('custom_form_submitter_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_custom_form_submitter_email_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('custom_form_submitter_email_body_row').style.display = 'none';
    document.getElementById('custom_form_submitter_email_page_id_row').style.display = 'none';

    // if the "plain text" option is selected, then show the body row
    if (document.getElementById('custom_form_submitter_email_format_plain_text').checked == true) {
        document.getElementById('custom_form_submitter_email_body_row').style.display = '';
    
    // else the "html" option is selected, so show page row
    } else {
        document.getElementById('custom_form_submitter_email_page_id_row').style.display = '';
    }
}

function show_or_hide_custom_form_administrator_email() {
    if (document.getElementById('custom_form_administrator_email').checked == true) {
        document.getElementById('custom_form_administrator_email_to_email_address_row').style.display = '';
        document.getElementById('custom_form_administrator_email_bcc_email_address_row').style.display = '';
        document.getElementById('custom_form_administrator_email_subject_row').style.display = '';
        document.getElementById('custom_form_administrator_email_format_row').style.display = '';
        show_or_hide_custom_form_administrator_email_format();
        
    } else {
        document.getElementById('custom_form_administrator_email_to_email_address_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_bcc_email_address_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_subject_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_format_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_body_row').style.display = 'none';
        document.getElementById('custom_form_administrator_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_custom_form_administrator_email_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('custom_form_administrator_email_body_row').style.display = 'none';
    document.getElementById('custom_form_administrator_email_page_id_row').style.display = 'none';

    // if the "plain text" option is selected, then show the body row
    if (document.getElementById('custom_form_administrator_email_format_plain_text').checked == true) {
        document.getElementById('custom_form_administrator_email_body_row').style.display = '';
    
    // else the "html" option is selected, so show page row
    } else {
        document.getElementById('custom_form_administrator_email_page_id_row').style.display = '';
    }
}

function show_or_hide_custom_form_membership() {
    if (document.getElementById('custom_form_membership').checked == true) {
        document.getElementById('custom_form_membership_days_row').style.display = '';
        document.getElementById('custom_form_membership_start_page_id_row').style.display = '';
    } else {
        document.getElementById('custom_form_membership_days_row').style.display = 'none';
        document.getElementById('custom_form_membership_start_page_id_row').style.display = 'none';
    }
}

function toggle_custom_form_private()
{
    if (document.getElementById('custom_form_private').checked == true) {
        document.getElementById('custom_form_private_folder_id_row').style.display = '';
        document.getElementById('custom_form_private_days_row').style.display = '';
        document.getElementById('custom_form_private_start_page_id_row').style.display = '';
    } else {
        document.getElementById('custom_form_private_folder_id_row').style.display = 'none';
        document.getElementById('custom_form_private_days_row').style.display = 'none';
        document.getElementById('custom_form_private_start_page_id_row').style.display = 'none';
    }
}

function toggle_custom_form_offer()
{
    if ($('#custom_form_offer').is(':checked')) {
        $('#custom_form_offer_id_row').fadeIn();
        $('#custom_form_offer_days_row').fadeIn();
        $('#custom_form_offer_eligibility_row').fadeIn();

    } else {
        $('#custom_form_offer_id_row').fadeOut();
        $('#custom_form_offer_days_row').fadeOut();
        $('#custom_form_offer_eligibility_row').fadeOut();
    }
}

function show_or_hide_custom_form_confirmation_type()
{
    // Start off by hiding all rows under the confirmation type field until we determine which should be shown.
    document.getElementById('custom_form_confirmation_message_row').style.display = 'none';
    document.getElementById('custom_form_confirmation_page_id_row').style.display = 'none';
    document.getElementById('custom_form_confirmation_alternative_page_row').style.display = 'none';
    document.getElementById('custom_form_confirmation_alternative_page_contact_group_id_row').style.display = 'none';
    document.getElementById('custom_form_confirmation_alternative_page_id_row').style.display = 'none';

    // If the message option is selected, then show the message row
    if (document.getElementById('custom_form_confirmation_type_message').checked == true) {
        document.getElementById('custom_form_confirmation_message_row').style.display = '';

        // If the rich-text editor has not been loaded already for the message field, then load it.
        if ((typeof tinyMCE !== 'undefined') && (tinyMCE.getInstanceById('custom_form_confirmation_message') == null)) {
            tinyMCE.execCommand('mceAddControl', false, 'custom_form_confirmation_message');
        }
    
    // Otherwise the page option is selected, so show page rows.
    } else {
        document.getElementById('custom_form_confirmation_page_id_row').style.display = '';
        document.getElementById('custom_form_confirmation_alternative_page_row').style.display = '';

        show_or_hide_custom_form_confirmation_alternative_page();
    }
}

function show_or_hide_custom_form_confirmation_alternative_page()
{
    // Start off by hiding all rows under the alternative page field until we determine which should be shown.
    document.getElementById('custom_form_confirmation_alternative_page_contact_group_id_row').style.display = 'none';
    document.getElementById('custom_form_confirmation_alternative_page_id_row').style.display = 'none';

    // If the alternative page check box is checked, then show the alternative page rows.
    if (document.getElementById('custom_form_confirmation_alternative_page').checked == true) {
        document.getElementById('custom_form_confirmation_alternative_page_contact_group_id_row').style.display = '';
        document.getElementById('custom_form_confirmation_alternative_page_id_row').style.display = '';
    }
}

function show_or_hide_custom_form_return_type()
{
    // Start off by hiding all rows under the return type field until we determine which should be shown.
    document.getElementById('custom_form_return_message_row').style.display = 'none';
    document.getElementById('custom_form_return_page_id_row').style.display = 'none';
    document.getElementById('custom_form_return_alternative_page_row').style.display = 'none';
    document.getElementById('custom_form_return_alternative_page_contact_group_id_row').style.display = 'none';
    document.getElementById('custom_form_return_alternative_page_id_row').style.display = 'none';

    // If the message option is selected, then show the message row
    if (document.getElementById('custom_form_return_type_message').checked == true) {
        document.getElementById('custom_form_return_message_row').style.display = '';

        // If the rich-text editor has not been loaded already for the message field, then load it.
        if ((typeof tinyMCE !== 'undefined') && (tinyMCE.getInstanceById('custom_form_return_message') == null)) {
            tinyMCE.execCommand('mceAddControl', false, 'custom_form_return_message');
        }
    
    // Otherwise if the page option is selected, then show page rows.
    } else if (document.getElementById('custom_form_return_type_page').checked == true) {
        document.getElementById('custom_form_return_page_id_row').style.display = '';
        document.getElementById('custom_form_return_alternative_page_row').style.display = '';

        show_or_hide_custom_form_return_alternative_page();
    }
}

function show_or_hide_custom_form_return_alternative_page()
{
    // Start off by hiding all rows under the alternative page field until we determine which should be shown.
    document.getElementById('custom_form_return_alternative_page_contact_group_id_row').style.display = 'none';
    document.getElementById('custom_form_return_alternative_page_id_row').style.display = 'none';

    // If the alternative page check box is checked, then show the alternative page rows.
    if (document.getElementById('custom_form_return_alternative_page').checked == true) {
        document.getElementById('custom_form_return_alternative_page_contact_group_id_row').style.display = '';
        document.getElementById('custom_form_return_alternative_page_id_row').style.display = '';
    }
}

function show_or_hide_allow_customer_to_add_product_to_order() {
    if (document.getElementById('catalog_detail_allow_customer_to_add_product_to_order').checked == true) {
        document.getElementById('catalog_detail_add_button_label_row').style.display = '';
        document.getElementById('catalog_detail_next_page_id_row').style.display = '';
    } else {
        document.getElementById('catalog_detail_add_button_label_row').style.display = 'none';
        document.getElementById('catalog_detail_next_page_id_row').style.display = 'none';
    }
}

function show_or_hide_express_order_order_receipt_email() {
    if (document.getElementById('express_order_order_receipt_email').checked == true) {
        document.getElementById('express_order_order_receipt_email_subject_row').style.display = '';
        document.getElementById('express_order_order_receipt_email_format_row').style.display = '';
        show_or_hide_express_order_order_receipt_email_format();
        
    } else {
        document.getElementById('express_order_order_receipt_email_subject_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_format_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_header_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_footer_row').style.display = 'none';
        document.getElementById('express_order_order_receipt_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_express_order_order_receipt_email_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('express_order_order_receipt_email_header_row').style.display = 'none';
    document.getElementById('express_order_order_receipt_email_footer_row').style.display = 'none';
    document.getElementById('express_order_order_receipt_email_page_id_row').style.display = 'none';

    // if the "plain text" option is selected, then show the header and footer rows
    if (document.getElementById('express_order_order_receipt_email_format_plain_text').checked == true) {
        document.getElementById('express_order_order_receipt_email_header_row').style.display = '';
        document.getElementById('express_order_order_receipt_email_footer_row').style.display = '';
    
    // else the "html" option is selected, so show page row
    } else {
        document.getElementById('express_order_order_receipt_email_page_id_row').style.display = '';
    }
}

function show_or_hide_order_preview_order_receipt_email() {
    if (document.getElementById('order_preview_order_receipt_email').checked == true) {
        document.getElementById('order_preview_order_receipt_email_subject_row').style.display = '';
        document.getElementById('order_preview_order_receipt_email_format_row').style.display = '';
        show_or_hide_order_preview_order_receipt_email_format();
        
    } else {
        document.getElementById('order_preview_order_receipt_email_subject_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_format_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_header_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_footer_row').style.display = 'none';
        document.getElementById('order_preview_order_receipt_email_page_id_row').style.display = 'none';
    }
}

function show_or_hide_order_preview_order_receipt_email_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('order_preview_order_receipt_email_header_row').style.display = 'none';
    document.getElementById('order_preview_order_receipt_email_footer_row').style.display = 'none';
    document.getElementById('order_preview_order_receipt_email_page_id_row').style.display = 'none';

    // if the "plain text" option is selected, then show the header and footer rows
    if (document.getElementById('order_preview_order_receipt_email_format_plain_text').checked == true) {
        document.getElementById('order_preview_order_receipt_email_header_row').style.display = '';
        document.getElementById('order_preview_order_receipt_email_footer_row').style.display = '';
    
    // else the "html" option is selected, so show page row
    } else {
        document.getElementById('order_preview_order_receipt_email_page_id_row').style.display = '';
    }
}

function show_or_hide_quiz_question() {
    if (document.getElementById('quiz_question').checked == true) {
        document.getElementById('quiz_answer_row').style.display = '';
    } else {
        document.getElementById('quiz_answer_row').style.display = 'none';
    }
}

function show_or_hide_custom() {
    if (document.getElementById('custom').checked == true) {
        document.getElementById('custom_maximum_arrival_date_row').style.display = '';
        document.getElementById('shipping_cutoff_heading_row').style.display = 'none';
        document.getElementById('shipping_cutoff_row').style.display = 'none';
    } else {
        document.getElementById('custom_maximum_arrival_date_row').style.display = 'none';
        document.getElementById('shipping_cutoff_heading_row').style.display = '';
        document.getElementById('shipping_cutoff_row').style.display = '';
    }
}

function show_or_hide_comments()
{
    // if comments is checked then prepare to show rows
    if (document.getElementById('comments').checked == true) {
        document.getElementById('comments_label_row').style.display = '';
        document.getElementById('comments_message_row').style.display = '';
        document.getElementById('comments_allow_new_comments_row').style.display = '';
        document.getElementById('comments_disallow_new_comment_message_row').style.display = '';
        document.getElementById('comments_automatic_publish_row').style.display = '';
        document.getElementById('comments_allow_user_to_select_name_row').style.display = '';
        document.getElementById('comments_require_login_to_comment_row').style.display = '';
        document.getElementById('comments_allow_file_attachments_row').style.display = '';
        document.getElementById('comments_show_submitted_date_and_time_row').style.display = '';
        document.getElementById('comments_administrator_email_row').style.display = '';
        document.getElementById('comments_administrator_email_to_email_address_row').style.display = '';
        document.getElementById('comments_administrator_email_subject_row').style.display = '';
        
        show_or_hide_form_item_view_comment_fields();
        
        document.getElementById('comments_watcher_email_row').style.display = '';
        document.getElementById('comments_watcher_email_page_id_row').style.display = '';
        document.getElementById('comments_watcher_email_subject_row').style.display = '';
    
    // else hide all rows
    } else {
        document.getElementById('comments_label_row').style.display = 'none';
        document.getElementById('comments_message_row').style.display = 'none';
        document.getElementById('comments_allow_new_comments_row').style.display = 'none';
        document.getElementById('comments_disallow_new_comment_message_row').style.display = 'none';
        document.getElementById('comments_automatic_publish_row').style.display = 'none';
        document.getElementById('comments_allow_user_to_select_name_row').style.display = 'none';
        document.getElementById('comments_require_login_to_comment_row').style.display = 'none';
        document.getElementById('comments_allow_file_attachments_row').style.display = 'none';
        document.getElementById('comments_show_submitted_date_and_time_row').style.display = 'none';
        document.getElementById('comments_administrator_email_row').style.display = 'none';
        document.getElementById('comments_administrator_email_to_email_address_row').style.display = 'none';
        document.getElementById('comments_administrator_email_subject_row').style.display = 'none';
        document.getElementById('comments_administrator_email_conditional_administrators_row').style.display = 'none';
        document.getElementById('comments_submitter_email_row').style.display = 'none';
        document.getElementById('comments_submitter_email_page_id_row').style.display = 'none';
        document.getElementById('comments_submitter_email_subject_row').style.display = 'none';
        document.getElementById('comments_watcher_email_row').style.display = 'none';
        document.getElementById('comments_watcher_email_page_id_row').style.display = 'none';
        document.getElementById('comments_watcher_email_subject_row').style.display = 'none';
        document.getElementById('comments_watchers_managed_by_submitter_row').style.display = 'none';
    }
}

function show_or_hide_form_item_view_comment_fields()
{
    // get page type
    var page_type = document.getElementById('page_type').options[document.getElementById('page_type').selectedIndex].value;
    
    // if comments are enabled and the page type is form item view then show rows
    if ((document.getElementById('comments').checked == true) && (page_type == 'form item view')) {
        document.getElementById('comments_administrator_email_conditional_administrators_row').style.display = '';
        document.getElementById('comments_submitter_email_row').style.display = '';
        document.getElementById('comments_submitter_email_page_id_row').style.display = '';
        document.getElementById('comments_submitter_email_subject_row').style.display = '';
        document.getElementById('comments_watchers_managed_by_submitter_row').style.display = '';
        
    // else hide rows
    } else {
        document.getElementById('comments_administrator_email_conditional_administrators_row').style.display = 'none';
        document.getElementById('comments_submitter_email_row').style.display = 'none';
        document.getElementById('comments_submitter_email_page_id_row').style.display = 'none';
        document.getElementById('comments_submitter_email_subject_row').style.display = 'none';
        document.getElementById('comments_watchers_managed_by_submitter_row').style.display = 'none';
    }
}

function move_options(left_element_id, right_element_id, direction) {
    left = document.getElementById(left_element_id);
    right = document.getElementById(right_element_id);

    if (direction != 'left') {
        var tmp;
        tmp = left;
        left = right;
        right = tmp;
    }

    while(right.selectedIndex != -1) {
        left.options[left.options.length] = new Option(right.options[right.selectedIndex].text, right.options[right.selectedIndex].value);
        right.options[right.selectedIndex] = null;
    }
}

function prepare_selects(elements) {
    for (i = 0; i < elements.length; i++) {
        if (document.getElementById(elements[i])) {
            if (document.getElementById(elements[i] + "_hidden").value == '') {
                for (x = 0; x < document.getElementById(elements[i]).options.length; x++) {
                    document.getElementById(elements[i] + "_hidden").value += document.getElementById(elements[i]).options[x].value + ",";
                }
            }
        }
    }
    return true;
}

function change_offer_action_type($offer_action_type)
{
    // hide all objects
    document.getElementById('discount_order').style.display = 'none';
    document.getElementById('discount_product').style.display = 'none';
    document.getElementById('add_product').style.display = 'none';
    document.getElementById('discount_shipping').style.display = 'none';

    // show needed objects
    switch ($offer_action_type) {
        case 'discount order':
            document.getElementById('discount_order').style.display = '';
            break;

        case 'discount product':
            document.getElementById('discount_product').style.display = '';
            break;

        case 'add product':
            document.getElementById('add_product').style.display = '';
            break;
            
        case 'discount shipping':
            document.getElementById('discount_shipping').style.display = '';
            break;
    }
}

function change_field_type($field_type)
{
    // hide all objects
    document.getElementById('name_row').style.display = 'none';
    if (document.getElementById('rss_field_row')) {
        document.getElementById('rss_field_heading').style.display = 'none';
        document.getElementById('rss_field_row').style.display = 'none';
    }
    document.getElementById('name_row_header').style.display = 'none';
    document.getElementById('label_row').style.display = 'none';
    document.getElementById('label_row_header').style.display = 'none';
    document.getElementById('required_row').style.display = 'none';
    document.getElementById('required_row_header').style.display = 'none';
            
    // if upload_folder_id_row exists
    if (document.getElementById('upload_folder_id_row')) {
        document.getElementById('upload_folder_id_row').style.display = 'none';
        document.getElementById('upload_folder_id_row_header').style.display = 'none';
    }

    document.getElementById('default_value_row').style.display = 'none';
    document.getElementById('default_value_row_header').style.display = 'none';
    document.getElementById('position_row').style.display = 'none';
    document.getElementById('size_row').style.display = 'none';
    document.getElementById('maxlength_row').style.display = 'none';
    document.getElementById('wysiwyg_row').style.display = 'none';
    document.getElementById('wysiwyg_row_header').style.display = 'none';
    document.getElementById('rows_row').style.display = 'none';
    document.getElementById('rows_row_header').style.display = 'none';
    document.getElementById('cols_row').style.display = 'none';
    document.getElementById('multiple_row').style.display = 'none';
    document.getElementById('multiple_row_header').style.display = 'none';
    document.getElementById('spacing_row').style.display = 'none';
    
    // if contact_field_row exists
    if (document.getElementById('contact_field_row')) {
        document.getElementById('contact_field_row').style.display = 'none';
        document.getElementById('contact_field_row_header').style.display = 'none';
    }
    
    // if office_use_only_row exists
    if (document.getElementById('office_use_only_row')) {
        document.getElementById('office_use_only_row').style.display = 'none';
        document.getElementById('office_use_only_row_header').style.display = 'none';
    }
    
    // if quiz is enabled for this custom form
    if (document.getElementById('quiz_question_row')) {
        document.getElementById('quiz_question_row').style.display = 'none';
        document.getElementById('quiz_answer_row').style.display = 'none';
    }
    
    document.getElementById('choices_row').style.display = 'none';
    document.getElementById('information_row').style.display = 'none';

    // show needed objects
    switch ($field_type) {
        case 'text box':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('maxlength_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }
            
            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;
        
        case 'text area':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('wysiwyg_row').style.display = '';
            document.getElementById('wysiwyg_row_header').style.display = '';
            document.getElementById('rows_row').style.display = '';
            document.getElementById('rows_row_header').style.display = '';
            document.getElementById('cols_row').style.display = '';
            document.getElementById('maxlength_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }
            break;

        case 'pick list':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('multiple_row').style.display = '';
            document.getElementById('multiple_row_header').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            
            document.getElementById('choices_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }
            
            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;

        case 'radio button':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            
            document.getElementById('choices_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;

        case 'check box':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            document.getElementById('choices_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;
            
        case 'file upload':
            document.getElementById('name_row').style.display = '';
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('upload_folder_id_row').style.display = '';
            document.getElementById('upload_folder_id_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }
            break;
            
        case 'date':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;
            
        case 'date and time':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;
            
        case 'email address':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('maxlength_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if contact_field_row exists
            if (document.getElementById('contact_field_row')) {
                document.getElementById('contact_field_row').style.display = '';
                document.getElementById('contact_field_row_header').style.display = '';
            }
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;

        case 'information':
            document.getElementById('name_row').style.display = '';
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('information_row').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }
            
            if ((typeof tinyMCE !== 'undefined') && (tinyMCE.getInstanceById('information') == null)) {
                tinyMCE.execCommand('mceAddControl', false, 'information');
            }
            
            break;
            
        case 'time':
            document.getElementById('name_row').style.display = '';
            if (document.getElementById('rss_field_row')) {
                document.getElementById('rss_field_heading').style.display = '';
                document.getElementById('rss_field_row').style.display = '';
            }
            document.getElementById('name_row_header').style.display = '';
            document.getElementById('label_row').style.display = '';
            document.getElementById('label_row_header').style.display = '';
            document.getElementById('position_row').style.display = '';
            document.getElementById('required_row').style.display = '';
            document.getElementById('required_row_header').style.display = '';
            document.getElementById('default_value_row').style.display = '';
            document.getElementById('default_value_row_header').style.display = '';
            document.getElementById('size_row').style.display = '';
            document.getElementById('spacing_row').style.display = '';
            
            // if office_use_only_row exists
            if (document.getElementById('office_use_only_row')) {
                document.getElementById('office_use_only_row').style.display = '';
                document.getElementById('office_use_only_row_header').style.display = '';
            }

            // if quiz is enabled for this custom form
            if (document.getElementById('quiz_question_row')) {
                document.getElementById('quiz_question_row').style.display = '';
                show_or_hide_quiz_question();
            }
            
            break;
    }
}

// Create a function that will be used to set the start and end time fields
// for calendar events so they either accept both a date & time if "all day" is disabled
// or just a date if "all day" is enabled.
function toggle_calendar_event_all_day() {
    // If all day is checked, then prepare start and end date fields to just contain dates.
    if (document.getElementById('all_day').checked == true) {
        document.getElementById('start_time_label').style.display = 'none';
        document.getElementById('end_time_label').style.display = 'none';
        document.getElementById('show_start_time_container').style.display = 'none';
        document.getElementById('show_end_time_container').style.display = 'none';

        // Remove time picker by removing its parent date picker.
        $("#start_time").datepicker('destroy');
        $("#end_time").datepicker('destroy');

        // Add date picker to both fields.

        $("#start_time").datepicker({
            dateFormat: date_picker_format
        });
        
        $("#end_time").datepicker({
            dateFormat: date_picker_format
        });

        // Get just the date values in order to strip the times from the fields.
        var start_date = $.datepicker.formatDate(date_picker_format, $('#start_time').datepicker('getDate'));
        var end_date = $.datepicker.formatDate(date_picker_format, $('#end_time').datepicker('getDate'));

        // Update fields to only contain the date.
        $("#start_time").datepicker('setDate', start_date);
        $("#end_time").datepicker('setDate', end_date);

        // Update the size and maxlength of the fields to support just a date.
        $('#start_time').attr('size', 10);
        $('#start_time').attr('maxlength', 10);
        $('#end_time').attr('size', 10);
        $('#end_time').attr('maxlength', 10);

    // Otherwise all day is not checked, so prepare start and end date fields to contain both dates and times.
    } else {
        document.getElementById('start_time_label').style.display = '';
        document.getElementById('end_time_label').style.display = '';
        document.getElementById('show_start_time_container').style.display = '';
        document.getElementById('show_end_time_container').style.display = '';

        // Remove date picker in preparation for adding date/time picker.
        $("#start_time").datepicker('destroy');
        $("#end_time").datepicker('destroy');

        // Add date/time picker to both fields.

        $("#start_time").datetimepicker({
            dateFormat: date_picker_format,
            timeFormat: "h:mm TT"
        });

        $("#end_time").datetimepicker({
            dateFormat: date_picker_format,
            timeFormat: "h:mm TT"
        });

        // Update the size and maxlength of the fields to support both a date and time.
        $('#start_time').attr('size', 19);
        $('#start_time').attr('maxlength', 19);
        $('#end_time').attr('size', 19);
        $('#end_time').attr('maxlength', 19);
    }
}

function toggle_calendar_event_recurrence()
{
    // Assume that rows should be hidden until we find out otherwise.
    document.getElementById('recurrence_number_and_type_row').style.display = 'none';
    document.getElementById('recurrence_days_of_the_week_row').style.display = 'none';
    document.getElementById('recurrence_month_type_row').style.display = 'none';

    // If recurrence is checked, then determine which recurrence rows should be shown.
    if (document.getElementById('recurrence').checked == true) {
        document.getElementById('recurrence_number_and_type_row').style.display = '';
        change_calendar_event_recurrence_type();
    }

    // if this is a recurring event and reservations is enabled then show separate reservations field
    if (
        (document.getElementById('recurrence').checked == true)
        && (document.getElementById('reservations').checked == true)
    ) {
        document.getElementById('separate_reservations_row').style.display = '';
        
    // else separate reservations should not be shown, so hide it
    } else {
        document.getElementById('separate_reservations_row').style.display = 'none';
    }
    
    // if this is the edit calendar event screen, then determine if we should show or hide initial spots
    // the initial spots field is always displayed on the create calendar event screen, so that is why we don't have to deal with it
    if (document.getElementById('number_of_remaining_spots_row')) {
        // if this is a recurring event and reservations is enabled and separate reservations is enabled and limit reservations is enabled,
        // then show initial spots field
        if (
            (document.getElementById('recurrence').checked == true)
            && (document.getElementById('reservations').checked == true)
            && (document.getElementById('separate_reservations').checked == true)
            && (document.getElementById('limit_reservations').checked == true)
        ) {
            document.getElementById('number_of_initial_spots_row').style.display = '';
         
        // else hide initial spots field
        } else {
            document.getElementById('number_of_initial_spots_row').style.display = 'none';
        }
    }
}

function change_calendar_event_recurrence_type()
{
    // Hide various recurrence rows until we find out which should be shown.
    document.getElementById('recurrence_days_of_the_week_row').style.display = 'none';
    document.getElementById('recurrence_month_type_row').style.display = 'none';

    // Show different rows depending on the selected recurrence type.
    switch (document.getElementById('recurrence_type').options[document.getElementById('recurrence_type').selectedIndex].value) {
        case 'day':
            document.getElementById('recurrence_days_of_the_week_row').style.display = '';
            break;

        case 'month':
            document.getElementById('recurrence_month_type_row').style.display = '';
            break;
    }
}

function toggle_calendar_event_published() {
    if ($('#published').is(':checked')) {
        $('#unpublish_days_row').fadeIn();

    } else {
        $('#unpublish_days_row').fadeOut();
    }
}

function change_short_link_destination_type()
{
    // Hide all rows until we determine which need to be shown.
    document.getElementById('page_id_row').style.display = 'none';
    document.getElementById('catalog_page_id_row').style.display = 'none';
    document.getElementById('or_row').style.display = 'none';
    document.getElementById('catalog_detail_page_id_row').style.display = 'none';
    document.getElementById('product_group_id_row').style.display = 'none';
    document.getElementById('product_id_row').style.display = 'none';
    document.getElementById('url_row').style.display = 'none';
    document.getElementById('tracking_code_row').style.display = 'none';

    // Show certain rows based on which destination type was selected.
    switch (document.getElementById('destination_type').options[document.getElementById('destination_type').selectedIndex].value) {
        case 'page':
            document.getElementById('page_id_row').style.display = '';
            document.getElementById('tracking_code_row').style.display = '';
            break;

        case 'product_group':
            document.getElementById('catalog_page_id_row').style.display = '';
            document.getElementById('or_row').style.display = '';
            document.getElementById('catalog_detail_page_id_row').style.display = '';
            document.getElementById('product_group_id_row').style.display = '';
            document.getElementById('tracking_code_row').style.display = '';
            break;

        case 'product':
            document.getElementById('catalog_detail_page_id_row').style.display = '';
            document.getElementById('product_id_row').style.display = '';
            document.getElementById('tracking_code_row').style.display = '';
            break;

        case 'url':
            document.getElementById('url_row').style.display = '';
            break;
    }
}

function change_email_campaign_profile_action()
{
    // Hide all items until we determine which need to be shown.
    document.getElementById('calendar_event_id_row').style.display = 'none';
    document.getElementById('custom_form_page_id_row').style.display = 'none';
    document.getElementById('email_campaign_profile_id_row').style.display = 'none';

    // If product id row exists, then hide it
    // If commerce is disabled or user does not have access then row is not outputted.
    if (document.getElementById('product_id_row')) {
        document.getElementById('product_id_row').style.display = 'none';
    }
    
    document.getElementById('calendar_event_reserved_schedule_period_and_base').style.display = 'none';
    document.getElementById('standard_schedule_period_and_base').style.display = 'none';

    // Show certain rows based on which destination type was selected.
    switch (document.getElementById('action').options[document.getElementById('action').selectedIndex].value) {
        case 'calendar_event_reserved':
            document.getElementById('calendar_event_id_row').style.display = '';
            document.getElementById('calendar_event_reserved_schedule_period_and_base').style.display = '';
            break;

        case 'custom_form_submitted':
            document.getElementById('custom_form_page_id_row').style.display = '';
            document.getElementById('standard_schedule_period_and_base').style.display = '';
            break;

        case 'email_campaign_sent':
            document.getElementById('email_campaign_profile_id_row').style.display = '';
            document.getElementById('standard_schedule_period_and_base').style.display = '';
            break;

        case 'order_completed':
            document.getElementById('standard_schedule_period_and_base').style.display = '';
            break;

        case 'product_ordered':
            document.getElementById('product_id_row').style.display = '';
            document.getElementById('standard_schedule_period_and_base').style.display = '';
            break;

        default:
            document.getElementById('standard_schedule_period_and_base').style.display = '';
            break;
    }
}

function show_or_hide_email_campaign_profile_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('body_row').style.display = 'none';
    document.getElementById('page_id_row').style.display = 'none';
    document.getElementById('body_preview_row').style.display = 'none';

    // if the "plain text" option is selected, then show the body row
    if (document.getElementById('format_plain_text').checked == true) {
        document.getElementById('body_row').style.display = '';
    
    // else the "html" option is selected, so show page row and determine if body preview row should be shown
    } else {
        document.getElementById('page_id_row').style.display = '';

        change_email_campaign_profile_page_id();
    }
}

function change_email_campaign_profile_page_id()
{
    if (document.getElementById('page_id').options[document.getElementById('page_id').selectedIndex].firstChild) {
        document.getElementById('body_preview_iframe').src = path + document.getElementById('page_id').options[document.getElementById('page_id').selectedIndex].firstChild.nodeValue + '?edit=no&email=true';
        document.getElementById('body_preview_row').style.display = '';
    } else {
        document.getElementById('body_preview_row').style.display = 'none';
    }
}

function createXMLHttpRequest() {
    if (window.XMLHttpRequest) {
        try {
            return new XMLHttpRequest();
        } catch(error) {
            return false;
        }
    } else if (window.ActiveXObject) {
        try {
            return new ActiveXObject("Microsoft.XMLHTTP");
        } catch(error) {
            return false;
        }
    }
}

function check_upload(file_path)
{
    // get file name
    if (file_path.indexOf('/') > -1) {
        var file_name = file_path.substring(file_path.lastIndexOf('/') + 1);
    } else {
        var file_name = file_path.substring(file_path.lastIndexOf('\\') + 1);
    }
    
    // get file extension
    var file_name_parts = file_name.split('.');
    var file_extension = file_name_parts[file_name_parts.length - 1];
    
    // if this upload form allows for zip file extraction and file is a zip file, then ask user if user wants to extract zip file
    if (document.form.extract && (file_extension == 'zip')) {
        if (confirm('Click "OK" to extract and upload all files within the ZIP file. Click "Cancel" to just upload the ZIP file itself.\n\nNOTE: Files will be extracted from all folders in the ZIP file.\n\nIf an extracted file\'s name already exists, then it will be given a new, unique name.') == true) {
            document.form.extract.value = 'true';
        }
    }
    
    // if the file is not being extracted, then check if file exists
    if (!document.form.extract || (document.form.extract.value != 'true')) {
        var requester = createXMLHttpRequest();

        requester.onreadystatechange =
            function ()
            {
                // if XMLHttpRequest communication is complete
                if (requester.readyState == 4) {
                    var temp = requester.responseXML.getElementsByTagName("response");
                    var response = temp[0].firstChild.nodeValue;
                    
                    if (response == 'upload') {
                        document.form.submit();
                        
                    } else if (response == 'overwrite') {
                        if (confirm('There is already a file named "' + file_name + '".  Would you like to replace the existing file?') == true) {
                            document.form.overwrite.value = 'true';
                            document.form.submit();
                        }
                        
                    } else if (response == 'access denied') {
                        alert('There is already a file named "' + file_name + '".  You do not have access to replace the file. Please rename the file on your computer and try again.');
                    }
                }
            };
        
        // if path is not defined then that means this function was called from a rich-text editor plugin (insert link, insert image),
        // so use a relative path. The rich-text editor plugins use standard html files so we can't set the path and software directory with PHP
        if (typeof path === 'undefined') {
            var software_directory_path = '../../../';
            
        // else path is defined so use it to set absolute software directory path
        } else {
            var software_directory_path = path + software_directory + '/';
        }
        
        requester.open("GET", software_directory_path + "check_if_file_exists.php?file_name=" + encodeURIComponent(file_name));
        requester.send(null);
        
        return false;
        
    // else the file is being extracted
    } else {
        return true;
    }
}

function export_forms()
{
    if (document.getElementById('custom_form')) {
        if (document.getElementById('custom_form').value != '[All]') {
            return true;
        } else {
            alert('You may only export forms from one custom form at a time. Please select only one custom form in the filters and try again.');
            return false;
        }
        
    } else {
        var number_of_selected_custom_forms = 0;

        for (var i = 0; i < document.forms.length; i++) {
            for (var j = 0; j < document.forms[i].length; j++) {
                if ((document.forms[i].elements[j].name == 'custom_forms[]') && (document.forms[i].elements[j].checked == true)) {
                    number_of_selected_custom_forms++;
                }
            }
        }
        
        if (number_of_selected_custom_forms == 1) {
            return true;
            
        } else if (number_of_selected_custom_forms == 0) {
            alert('Please select a custom form in the advanced filters.');
            return false;
            
        } else {
            alert('You may only export forms from one custom form at a time. Please select only one custom form in the advanced filters and try again.');
            return false;
        }
    }
}

function change_user_role(user_role)
{
    // if user role was selected, then show certain access fields
    if (user_role == 3) {
        if (document.getElementById('manage_ecommerce_heading_row')) {
            document.getElementById('manage_ecommerce_heading_row').style.display = '';
            document.getElementById('manage_ecommerce_row').style.display = '';
        }
        
        if (document.getElementById('manage_calendars_heading_row')) {
            document.getElementById('manage_calendars_heading_row').style.display = '';
            document.getElementById('manage_calendars_row').style.display = '';
            show_or_hide_calendar_access();
        }
        
        document.getElementById('manage_visitors_heading_row').style.display = '';
        document.getElementById('manage_visitors_row').style.display = '';
        document.getElementById('edit_access_heading_row').style.display = '';
        document.getElementById('edit_access_row').style.display = '';
        document.getElementById('shared_content_access_rights_heading_row').style.display = '';
        document.getElementById('common_regions_access_row').style.display = '';
        document.getElementById('menus_access_row').style.display = '';
        document.getElementById('manage_contacts_and_manage_emails_heading_row').style.display = '';
        document.getElementById('manage_contacts_and_manage_emails_row').style.display = '';
        show_or_hide_contact_group_access();
        show_or_hide_ecommerce_access();

        if (document.getElementById('manage_ad_regions_heading_row')) {
            document.getElementById('manage_ad_regions_heading_row').style.display = '';
            document.getElementById('manage_ad_regions_row').style.display = '';
        }

        document.getElementById('view_access_heading_row').style.display = '';
        document.getElementById('view_access_row').style.display = '';
        
    // else administrator, designer, or manager role was selected, so hide certain access fields
    } else {
        if (document.getElementById('manage_ecommerce_heading_row')) {
            document.getElementById('manage_ecommerce_heading_row').style.display = 'none';
            document.getElementById('manage_ecommerce_row').style.display = 'none';
        }
        
        if (document.getElementById('manage_calendars_heading_row')) {
            document.getElementById('manage_calendars_heading_row').style.display = 'none';
            document.getElementById('manage_calendars_row').style.display = 'none';
        }

        document.getElementById('manage_visitors_heading_row').style.display = 'none';
        document.getElementById('manage_visitors_row').style.display = 'none';
        document.getElementById('edit_access_heading_row').style.display = 'none';
        document.getElementById('edit_access_row').style.display = 'none';
        document.getElementById('shared_content_access_rights_heading_row').style.display = 'none';
        document.getElementById('common_regions_access_row').style.display = 'none';
        document.getElementById('menus_access_row').style.display = 'none';
        document.getElementById('manage_contacts_and_manage_emails_heading_row').style.display = 'none';
        document.getElementById('manage_contacts_and_manage_emails_row').style.display = 'none';

        if (document.getElementById('manage_ad_regions_heading_row')) {
            document.getElementById('manage_ad_regions_heading_row').style.display = 'none';
            document.getElementById('manage_ad_regions_row').style.display = 'none';
        }

        document.getElementById('view_access_heading_row').style.display = 'none';
        document.getElementById('view_access_row').style.display = 'none';
    }
}



function getOffsetTop(element)
{
    el = document.getElementById(element);
    xPos = el.offsetTop;
    tempEl = el.offsetParent;
    while (tempEl != null) {
        xPos += tempEl.offsetTop;
        tempEl = tempEl.offsetParent;
    }
    return xPos;
}

function getOffsetLeft(element)
{
    el = document.getElementById(element);
    xPos = el.offsetLeft;
    tempEl = el.offsetParent;
    while (tempEl != null) {
        xPos += tempEl.offsetLeft;
        tempEl = tempEl.offsetParent;
    }
    return xPos;
}

function init_folder_tree()
{
    update_folder_tree(0);
}

function update_folder_tree(folder_id, expand_all)
{
    var expanded_folders_cookie = get_cookie_value('software[view_folders][expanded_folders]');
    expanded_folders = new Array();
    
    if (expanded_folders_cookie) {
        expanded_folders = expanded_folders_cookie.split(',');
    }
    
    // if folder is collapsed, expand folder
    if ((document.getElementById('ul_' + folder_id).style.display == 'none') || (expand_all == true)) {
        expanded_folders[expanded_folders.length] = folder_id;
        
        document.getElementById('ul_' + folder_id).innerHTML = '<li class="loading"><img src="images/loading.gif" width="16" height="16" border="0" alt="" />&nbsp;&nbsp;Loading...</li>';
        document.getElementById('ul_' + folder_id).style.display = 'block';
        
        var requester = createXMLHttpRequest();
        
        requester.onreadystatechange =
            function ()
            {
                // if XMLHttpRequest communication is complete
                if (requester.readyState == 4) {
                    var temp = requester.responseXML.getElementsByTagName("root");
                    var root = temp[0];
                    
                    document.getElementById('ul_' + folder_id).innerHTML = get_folder_content(root, expand_all);
                    
                    if (document.getElementById('image_' + folder_id)) {
                        document.getElementById('image_' + folder_id).src = 'images/icon_folder_expanded.png';
                    }
                    
                    save_expanded_folders_cookie();
                }
            };

        if (expand_all == true) {
            expand_all_value = 'true';
        } else {
            expand_all_value = 'false';
        }

        requester.open("GET", "get_folder_tree.php?folder_id=" + folder_id + "&expand_all=" + expand_all_value);
        requester.send(null);
    
    // else folder is not expanded, so collapse folder
    } else {
        // remove items in this folder
        document.getElementById('ul_' + folder_id).innerHTML = '';
        
        // collapse folder
        document.getElementById('ul_' + folder_id).style.display = 'none';
        
        // change status image to plus icon
        if (document.getElementById('image_' + folder_id)) {
            document.getElementById('image_' + folder_id).src = 'images/icon_folder_collapsed.png';
        }
        
        // loop through all expanded folders, so we can remove collapsed folder
        for (var i = 0; i < expanded_folders.length; i++) {
            // if this folder is the collapsed folder, remove folder from array
            if (expanded_folders[i] == folder_id) {
                expanded_folders.splice(i, 1);
            }
        }
        
        save_expanded_folders_cookie();
    }
    
    function get_folder_content(parent, expand_all)
    {
        var content = '';
        
        for (var i = 0; i < parent.childNodes.length; i++) {
            switch (parent.childNodes[i].tagName) {
                case 'folder':
                    var archived = '';
                    
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'style') {
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                style = " || Page Style: " + parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'access_control_type') {
                            access_control_type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                    }
                    
                    // Get user friendly access control type names
                    access_control_type_name = get_access_control_name (access_control_type)
                        
                    // if folder should be expanded
                    if ((expand_all == true) || (in_array(id, expanded_folders) == true) || (id == 1)) {
                        expanded_collapsed_icon = '<img id="image_' + id + '" src="images/icon_folder_expanded.png" width="25" height="25" border="0" class="icon_folder" alt="" />';
                        display = 'block';
                        expanded_folders[expanded_folders.length] = id;
                    } else {
                        expanded_collapsed_icon = '<img id="image_' + id + '" src="images/icon_folder_collapsed.png" width="25" height="25" border="0" class="icon_folder" alt="" />';
                        display = 'none';
                    }
                    
                    var last_class;
                    
                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }
                    
                    var archived_class = '';
                    
                    // if archived is true, then output the archived styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }
                    
                    content += '<li class="' + access_control_type + last_class + ' heading"><span onclick="update_folder_tree(' + id + ')" onmouseover="this.className=\'icon\'" onmouseout="this.className=\'\'">' + expanded_collapsed_icon + '</span><span onmouseover="this.className=\'highlight\'" onmouseout="this.className=\'\'"><span id="folder_' + id + '" class="object' + archived_class + '" onmouseover="document.getElementById(\'folder_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'folder_properties_' + id + '\').style.visibility = \'hidden\';"><span onclick="window.location.href=\'edit_folder.php?id=' + id + '\'"><span class="folder">' + prepare_content_for_html(name) + '</span><span id="folder_properties_' + id + '" style="visibility: hidden"> || Access Control: ' + access_control_type_name + style + '</span></span></span></span><ul id="ul_' + id + '" style="display: ' + display + '">';
                    content += get_folder_content(parent.childNodes[i], expand_all);
                    content += '</ul></li>';
                    break;
                    
                case 'page':
                    var archived = '';
                    
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'style') {
                            if (parent.childNodes[i].childNodes[j].firstChild && (parent.childNodes[i].childNodes[j].firstChild.nodeValue != '&nbsp;')) {
                                style = " || Page Style Override: " + parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            } else {
                                style = '';
                            }
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'home') {
                            home = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'type') {
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            } else {
                                type = '';
                            }
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                    }
                    
                    if (home == 'true') {
                        page_icon = '<img src="images/icon_home_page.gif" width="16" height="14" border="0" align="absbottom" class="icon_home_page" alt="" />';
                    } else {
                        page_icon = '<img src="images/icon_page.gif" width="12" height="14" border="0" align="absbottom" class="icon_page" alt="" />';
                    }
                    
                    var last_class;
                    
                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' class="last"';
                    } else {
                        last_class = '';
                    }
                    
                    var query_string_from = '';
                    
                    // if page type is a certain page type, then prepare from
                    switch(type) {
                        case 'view order':
                        case 'custom form':
                        case 'custom form confirmation':
                        case 'calendar event view':
                        case 'catalog detail':
                        case 'shipping address and arrival':
                        case 'shipping method':
                        case 'logout':
                            query_string_from = '?from=control_panel';
                            break;
                    }
                    
                    var archived_class = '';
                    
                    // if archived is true, then output the italic styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }
                    
                    content += '<li' + last_class + '><span onclick="window.location.href=\'' + prepare_content_for_html(path) + name + query_string_from + '\'" onmouseover="this.className=\'highlight\'" onmouseout="this.className=\'\'"><span id="page_' + id + '" class="object' + archived_class + '" onmouseover="document.getElementById(\'page_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'page_properties_' + id + '\').style.visibility = \'hidden\';">' + page_icon + '<span class="file">' + prepare_content_for_html(name) + '</span><span id="page_properties_' + id + '" style="visibility: hidden"> ' + style + '</span></span></span></li>';
                    break;
                    
                case 'file':
                    var design = '';
                    var access = '';
                    var archived = '';
                    
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'design') {
                            design = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'access') {
                            access = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                    }

                    var link = '';

                    // if the user has access to this file (i.e. not a design file or user is a designer or administrator),
                    // then output link
                    if (access == 'true') {
                        link = ' onclick="window.location.href=\'edit_file.php?id=' + id + '\'" onmouseover="this.className=\'highlight\'" onmouseout="this.className=\'\'"';
                    }
                    
                    var last_class;
                    
                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' class="last"';
                    } else {
                        last_class = '';
                    }

                    var design_class = '';
                    
                    // if file is a design file then output design class for lighter color
                    if (design == 'true') {
                        design_class = ' design';
                    }
                    
                    var archived_class = '';
                    
                    // if archived is true, then output the italic styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }
                    
                    content += '<li' + last_class + '><span' + link + '><span id="file_' + id + '" class="object' + design_class + archived_class + '"><img src="images/icon_file.gif" width="12" height="14" border="0" align="absbottom" class="icon_file" alt="" /><span class="no_style">' + prepare_content_for_html(name) + '</span></span></span></li>';
                    break;
            }
        }
        
        return content;
    }
}

function init_product_group_tree()
{
    update_product_group_tree(0);
}

function update_product_group_tree(product_group_id, expand_all) {

    var expanded_product_groups_cookie = get_cookie_value('software[product_group_tree][expanded_product_groups]');
    expanded_product_groups = new Array();
    
    if (expanded_product_groups_cookie) {
        expanded_product_groups = expanded_product_groups_cookie.split(',');
    }
    
    // if product_group is collapsed, expand product_group
    if ((document.getElementById('ul_' + product_group_id).style.display == 'none') || (expand_all == true)) {
        expanded_product_groups[expanded_product_groups.length] = product_group_id;
        
        document.getElementById('ul_' + product_group_id).innerHTML = '<li class="loading"><img src="images/loading.gif" width="16" height="16" border="0" alt="" />&nbsp;&nbsp;Loading...</li>';
        document.getElementById('ul_' + product_group_id).style.display = 'block';
        
        var requester = createXMLHttpRequest();
        
        requester.onreadystatechange = function() {
            // if XMLHttpRequest communication is complete
            if (requester.readyState == 4) {
                var temp = requester.responseXML.getElementsByTagName("root");
                var root = temp[0];
                
                document.getElementById('ul_' + product_group_id).innerHTML = get_product_group_content(root, expand_all);
                
                if (document.getElementById('image_' + product_group_id)) {
                    document.getElementById('image_' + product_group_id).src = 'images/icon_product_group_expanded.png';
                }

                $('#ul_' + product_group_id + ' .status_button').each(function() {

                    var status_button = $(this);

                    status_button.click(function() {

                        // Prepare the new status that should be set for this item.
                        if (status_button.hasClass('enable')) {
                            var status = 'enabled';
                        } else {
                            var status = 'disabled';
                        }

                        // If this item is a product group, then update status
                        // in a certain way.
                        if (status_button.hasClass('product_group_status_button')) {

                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify({
                                    action: 'update_product_group_status',
                                    token: software_token,
                                    id: status_button.data('id'),
                                    status: status
                                }),
                                type: 'POST',
                                success: function(response) {

                                    $.each(response.items, function(index, item) {

                                        if (item.type == 'product_group') {

                                            if (item.status == 'enabled') {
                                                $('.product_group_' + item.id).removeClass('status_disabled');
                                                $('.product_group_' + item.id).addClass('status_enabled');

                                                $('.product_group_' + item.id + '_status_button').removeClass('enable');
                                                $('.product_group_' + item.id + '_status_button').addClass('disable');
                                                $('.product_group_' + item.id + '_status_button').html('Disable');

                                            } else {
                                                $('.product_group_' + item.id).removeClass('status_enabled');
                                                $('.product_group_' + item.id).addClass('status_disabled');

                                                $('.product_group_' + item.id + '_status_button').removeClass('disable');
                                                $('.product_group_' + item.id + '_status_button').addClass('enable');
                                                $('.product_group_' + item.id + '_status_button').html('Enable');
                                            }

                                        // Otherwise the item is a product.
                                        } else {

                                            if (item.status == 'enabled') {
                                                $('.product_' + item.id).removeClass('status_disabled');
                                                $('.product_' + item.id).addClass('status_enabled');

                                                $('.product_' + item.id + '_status_button').removeClass('enable');
                                                $('.product_' + item.id + '_status_button').addClass('disable');
                                                $('.product_' + item.id + '_status_button').html('Disable');

                                            } else {
                                                $('.product_' + item.id).removeClass('status_enabled');
                                                $('.product_' + item.id).addClass('status_disabled');

                                                $('.product_' + item.id + '_status_button').removeClass('disable');
                                                $('.product_' + item.id + '_status_button').addClass('enable');
                                                $('.product_' + item.id + '_status_button').html('Enable');
                                            }

                                        }

                                    });

                                }
                            });

                        // Otherwise this item is a product, so update status,
                        // in a different way.
                        } else {

                            var id = status_button.data('id');

                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify({
                                    action: 'update_product_status',
                                    token: software_token,
                                    id: id,
                                    status: status
                                }),
                                type: 'POST',
                                success: function(response) {

                                    if (status == 'enabled') {
                                        $('.product_' + id).removeClass('status_disabled');
                                        $('.product_' + id).addClass('status_enabled');

                                        $('.product_' + id + '_status_button').removeClass('enable');
                                        $('.product_' + id + '_status_button').addClass('disable');
                                        $('.product_' + id + '_status_button').html('Disable');

                                    } else {
                                        $('.product_' + id).removeClass('status_enabled');
                                        $('.product_' + id).addClass('status_disabled');

                                        $('.product_' + id + '_status_button').removeClass('disable');
                                        $('.product_' + id + '_status_button').addClass('enable');
                                        $('.product_' + id + '_status_button').html('Enable');
                                    }

                                }
                            });

                        }

                    });

                });
                
                save_expanded_product_groups_cookie();
            }
        };

        if (expand_all == true) {
            expand_all_value = 'true';
        } else {
            expand_all_value = 'false';
        }

        requester.open("GET", "get_product_group_tree.php?product_group_id=" + product_group_id + "&expand_all=" + expand_all_value);
        requester.send(null);
    
    // else product_group is expanded, so collapse product_group
    } else {
        // remove items in this product_group
        document.getElementById('ul_' + product_group_id).innerHTML = '';
        
        // collapse product_group
        document.getElementById('ul_' + product_group_id).style.display = 'none';
        
        // change status image to plus icon
        if (document.getElementById('image_' + product_group_id)) {
            document.getElementById('image_' + product_group_id).src = 'images/icon_product_group_collapsed.png';
        }
        
        // loop through all expanded product_groups, so we can remove collapsed product_group
        for (var i = 0; i < expanded_product_groups.length; i++) {
            // if this product_group is the collapsed product_group, remove product_group from array
            if (expanded_product_groups[i] == product_group_id) {
                expanded_product_groups.splice(i, 1);
            }
        }
        
        save_expanded_product_groups_cookie();
    }
    
    function get_product_group_content(parent, expand_all)
    {
        var content = '';
        
        for (var i = 0; i < parent.childNodes.length; i++) {
            switch (parent.childNodes[i].tagName) {
                case 'product_group':
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = '';
                            
                            // if there is a name, then set name
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'enabled') {
                            var enabled = '';
                            
                            // If there is an enabled value, then set it.
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                enabled = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                    }
                    
                    // if product_group should be expanded
                    if ((expand_all == true) || (in_array(id, expanded_product_groups) == true) || (id == 1)) {
                        expanded_collapsed_icon = '<img id="image_' + id + '" src="images/icon_product_group_expanded.png" width="25" height="25" border="0" alt="" class="icon_product_group" />';
                        display = 'block';
                        expanded_product_groups[expanded_product_groups.length] = id;
                    } else {
                        expanded_collapsed_icon = '<img id="image_' + id + '" src="images/icon_product_group_collapsed.png" width="25" height="25" border="0" alt="" class="icon_product_group" />';
                        display = 'none';
                    }

                    if (enabled == '1') {
                        var status_class = ' status_enabled';
                        var status_button_class = 'disable';
                        var status_button_content = 'Disable';
                    } else {
                        var status_class = ' status_disabled';
                        var status_button_class = 'enable';
                        var status_button_content = 'Enable';
                    }
                    
                    var last_class;
                    
                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }
                    
                    content += '<li class="product_group_' + id + status_class + last_class + '"><span class="row"><span onclick="update_product_group_tree(' + id + ')" onmouseover="this.className=\'icon\'" onmouseout="this.className=\'\'">' + expanded_collapsed_icon +'</span><span onmouseover="this.className=\'highlight\'" onmouseout="this.className=\'\'"><span id="product_group_' + id + '" class="object" onmouseover="document.getElementById(\'product_group_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'product_group_properties_' + id + '\').style.visibility = \'hidden\';"><span onclick="window.location.href=\'edit_product_group.php?id=' + id + '\'"><span class="product_group">' + prepare_content_for_html(name) + '</span></span></span></span><span class="product_group_' + id + '_status_button product_group_status_button status_button ' + status_button_class + '" data-id="' + id + '">' + status_button_content + '</span></span><ul id="ul_' + id + '" style="display: ' + display + '">';
                    content += get_product_group_content(parent.childNodes[i], expand_all);
                    content += '</ul></li>';
                    break;
                    
                case 'product':
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'parent_id') {
                            parent_id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = '';
                            
                            // if there is a name, then set name
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'enabled') {
                            var enabled = '';
                            
                            // If there is an enabled value, then set it.
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                enabled = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                        
                        if (parent.childNodes[i].childNodes[j].tagName == 'short_description') {
                            short_description = '';
                            
                            // if there is a short description, then set short description
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                short_description = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                    }

                    if (enabled == '1') {
                        var status_class = ' status_enabled';
                        var status_button_class = 'disable';
                        var status_button_content = 'Disable';
                    } else {
                        var status_class = ' status_disabled';
                        var status_button_class = 'enable';
                        var status_button_content = 'Enable';
                    }
                    
                    var last_class;
                    
                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }
                    
                    content += '<li class="product_' + id + status_class + last_class + '"><span class="row"><span onclick="window.location.href=\'edit_product.php?id=' + id + '&send_to=' + encodeURIComponent(path + software_directory + '/') + 'view_product_groups.php\'" onmouseover="this.className=\'highlight\'" onmouseout="this.className=\'\'"><span id="product_' + id + '" class="object" onmouseover="document.getElementById(\'product_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'product_properties_' + id + '\').style.visibility = \'hidden\';"><img width="25" height="25" border="0" align="absbottom" alt="" class="icon_product" src="images/icon_product.png"/> ' + prepare_content_for_html(name) + ' - ' + prepare_content_for_html(short_description) + '</span></span><span class="product_' + id + '_status_button product_status_button status_button ' + status_button_class + ' product" data-id="' + id + '">' + status_button_content + '</span></span></li>';
                    break;
            }
        }
        
        return content;
    }
}

// Create user friendly access control names.
function get_access_control_name(access_control_type) {
    switch (access_control_type) {
        case 'public':
            return 'Public';
            break;
            
        case 'guest':
            return 'Guest';
            break;
        
        case 'private':
            return 'Private';
            break;
        
        case 'registration':
            return 'Registration';
            break;
        
        case 'membership':
            return 'Membership';
            break;
    }
}

function collapse_folder_tree()
{
    alluls = document.getElementsByTagName('UL');
    for (i = 0; i < alluls.length; i++) {
        ul = alluls[i];
        if (ul.parentNode.tagName == 'LI') {
            id = ul.id.substr(3);

            image_id = 'image_' + id;
            image = document.getElementById(image_id);

            ul.style.display = 'none';
            image.src = 'images/icon_folder_collapsed.png';
        }
    }

    expanded_folders = new Array();

    // set cookie to remember that this folder is collapsed
    document.cookie = "software[view_folders][expanded_folders]=0; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function collapse_product_group_tree()
{
    alluls = document.getElementsByTagName('UL');
    for (i = 0; i < alluls.length; i++) {
        ul = alluls[i];
        if (ul.parentNode.tagName == 'LI') {
            id = ul.id.substr(3);

            image_id = 'image_' + id;
            image = document.getElementById(image_id);

            ul.style.display = 'none';
            image.src = 'images/icon_product_group_collapsed.png';
        }
    }

    expanded_product_groups = new Array();

    // set cookie to remember that this product_group is collapsed
    document.cookie = "software[product_group_tree][expanded_product_groups]=0; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function get_cookie_value(name)
{
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function in_array(value, array) {
    for (var i = 0; i < array.length; i++) {
        if (array[i] == value) {
            return true;
        }
    }
    
    return false;
}

function save_expanded_folders_cookie()
{
    // sort expanded folders
    expanded_folders.sort();
    
    var expanded_folders_cookie = '';
    
    // loop through all expanded folders
    for (var i = 0; i < expanded_folders.length; i++) {
        // if this folder is not a duplicate then add to cookie value
        if (expanded_folders[i] != expanded_folders[i - 1]) {
            expanded_folders_cookie += expanded_folders[i] + ',';
        }
    }
        
    // remove last comma
    expanded_folders_cookie = expanded_folders_cookie.substring(0, expanded_folders_cookie.length - 1);
    
    // save cookie
    document.cookie = "software[view_folders][expanded_folders]=" + expanded_folders_cookie + "; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function save_expanded_product_groups_cookie()
{
    // sort expanded product_groups
    expanded_product_groups.sort();
    
    var expanded_product_groups_cookie = '';
    
    // loop through all expanded product_groups
    for (var i = 0; i < expanded_product_groups.length; i++) {
        // if this product_group is not a duplicate then add to cookie value
        if (expanded_product_groups[i] != expanded_product_groups[i - 1]) {
            expanded_product_groups_cookie += expanded_product_groups[i] + ',';
        }
    }
        
    // remove last comma
    expanded_product_groups_cookie = expanded_product_groups_cookie.substring(0, expanded_product_groups_cookie.length - 1);
    
    // save cookie
    document.cookie = "software[product_group_tree][expanded_product_groups]=" + expanded_product_groups_cookie + "; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function prepare_content_for_html(content)
{
    var chars = new Array ('&','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','\"','�','<',
                         '>','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�');

    var entities = new Array ('amp','agrave','aacute','acirc','atilde','auml','aring',
                            'aelig','ccedil','egrave','eacute','ecirc','euml','igrave',
                            'iacute','icirc','iuml','eth','ntilde','ograve','oacute',
                            'ocirc','otilde','ouml','oslash','ugrave','uacute','ucirc',
                            'uuml','yacute','thorn','yuml','Agrave','Aacute','Acirc',
                            'Atilde','Auml','Aring','AElig','Ccedil','Egrave','Eacute',
                            'Ecirc','Euml','Igrave','Iacute','Icirc','Iuml','ETH','Ntilde',
                            'Ograve','Oacute','Ocirc','Otilde','Ouml','Oslash','Ugrave',
                            'Uacute','Ucirc','Uuml','Yacute','THORN','euro','quot','szlig',
                            'lt','gt','cent','pound','curren','yen','brvbar','sect','uml',
                            'copy','ordf','laquo','not','shy','reg','macr','deg','plusmn',
                            'sup2','sup3','acute','micro','para','middot','cedil','sup1',
                            'ordm','raquo','frac14','frac12','frac34');

    for (var i = 0; i < chars.length; i++) {
        myRegExp = new RegExp();
        myRegExp.compile(chars[i],'g');
        content = content.replace (myRegExp, '&' + entities[i] + ';');
    }

    return content;
}

// Create a new HTML escaping function with a shorter name
// and that is probably faster than the one above.
function h(content)
{
    if (typeof content === 'undefined') {
        return '';
    }

    content = content.replace(/&/g, '&amp;');
    content = content.replace(/</g, '&lt;');
    content = content.replace(/>/g, '&gt;');
    content = content.replace(/"/g, '&quot;');

    return content;
}

function show_or_hide_search()
{
    if (document.getElementById('search').checked == true) {
        document.getElementById('search_keywords_row').style.display = '';
    } else {
        document.getElementById('search_keywords_row').style.display = 'none';
    }
}

function show_or_hide_form_list_view_viewer_filter()
{
    document.getElementById('form_list_view_viewer_filter_submitter_row').style.display = 'none';
    document.getElementById('form_list_view_viewer_filter_watcher_row').style.display = 'none';
    document.getElementById('form_list_view_viewer_filter_editor_row').style.display = 'none';

    if (document.getElementById('form_list_view_viewer_filter').checked == true) {
        document.getElementById('form_list_view_viewer_filter_submitter_row').style.display = '';
        document.getElementById('form_list_view_viewer_filter_watcher_row').style.display = '';
        document.getElementById('form_list_view_viewer_filter_editor_row').style.display = '';
    }
}

function show_or_hide_form_item_view_editor()
{
    if (document.getElementById('form_item_view_submitted_form_editable_by_registered_user').checked == true) {
        document.getElementById('form_item_view_submitted_form_editable_by_submitter_row').style.display = 'none';
    } else {
        document.getElementById('form_item_view_submitted_form_editable_by_submitter_row').style.display = '';
    }
}

function show_or_hide_form_view_directory_form_list_view(id)
{
    if (document.getElementById('form_view_directory_form_list_view_' + id).checked == true) {
        document.getElementById('form_view_directory_form_list_view_' + id + '_name_container').style.display = '';
        document.getElementById('form_view_directory_form_list_view_' + id + '_subject_form_field_id_container').style.display = '';
    } else {
        document.getElementById('form_view_directory_form_list_view_' + id + '_name_container').style.display = 'none';
        document.getElementById('form_view_directory_form_list_view_' + id + '_subject_form_field_id_container').style.display = 'none';
    }
}

function show_or_hide_form_view_directory_summary()
{
    if (document.getElementById('form_view_directory_summary').checked == true) {
        document.getElementById('form_view_directory_summary_days_row').style.display = '';
        document.getElementById('form_view_directory_summary_maximum_number_of_results_row').style.display = '';
    } else {
        document.getElementById('form_view_directory_summary_days_row').style.display = 'none';
        document.getElementById('form_view_directory_summary_maximum_number_of_results_row').style.display = 'none';
    }
}

function show_or_hide_effect(option)
{
    if (option == "Pop-up") {
        document.getElementById('popup_properties_heading_row').style.display = '';
        document.getElementById('first_level_popup_position_row').style.display = '';
        document.getElementById('second_level_popup_position_row').style.display = '';
    } else {
        document.getElementById('popup_properties_heading_row').style.display = 'none';
        document.getElementById('first_level_popup_position_row').style.display = 'none';
        document.getElementById('second_level_popup_position_row').style.display = 'none';
    }
}

function show_or_hide_display_type(option)
{
    // if the clicked option is static, then hide dynamic rows
    if (option == 'static') {
        document.getElementById('transition_type_row').style.display = 'none';
        document.getElementById('transition_duration_row').style.display = 'none';
        document.getElementById('slideshow_row').style.display = 'none';
        document.getElementById('slideshow_interval_row').style.display = 'none';
        document.getElementById('slideshow_continuous_row').style.display = 'none';
    
    // else if the option is dynamic, then show dynamic rows
    } else if (option == 'dynamic') {
        document.getElementById('transition_type_row').style.display = '';
        document.getElementById('transition_duration_row').style.display = '';
        document.getElementById('slideshow_row').style.display = '';
        show_or_hide_slideshow();
    }
}

function show_or_hide_slideshow()
{
    // if slideshow is checked, then show slideshow rows
    if (document.getElementById('slideshow').checked == true) {
        document.getElementById('slideshow_interval_row').style.display = '';
        document.getElementById('slideshow_continuous_row').style.display = '';
        
    // else slideshow is not checked, so hide slideshow rows
    } else {
        document.getElementById('slideshow_interval_row').style.display = 'none';
        document.getElementById('slideshow_continuous_row').style.display = 'none';
    }
}

function show_or_hide_product_group_display_type(option)
{
    // if the clicked option is browse, then hide select rows
    if (option == 'browse') {
        document.getElementById('details_row').style.display = 'none';
        document.getElementById('keywords_row').style.display = 'none';

        // If there are attribute rows, then deal with them.
        if (document.getElementById('attributes_heading_row')) {
            document.getElementById('attributes_heading_row').style.display = 'none';
            document.getElementById('enable_attributes_row').style.display = 'none';
            document.getElementById('attributes_row').style.display = 'none';
        }
    
    // else the option is select, so show select rows
    } else {
        document.getElementById('details_row').style.display = '';
        document.getElementById('keywords_row').style.display = '';

        // If there are attribute rows, then deal with them.
        if (document.getElementById('attributes_heading_row')) {
            document.getElementById('attributes_heading_row').style.display = '';
            document.getElementById('enable_attributes_row').style.display = '';

            if (document.getElementById('enable_attributes').checked == true) {
                document.getElementById('attributes_row').style.display = '';
            } else {
                document.getElementById('attributes_row').style.display = 'none';
            }
        }
    }
}

function change_order_by(number)
{
    var order_by = document.getElementById('order_by_' + number).options[document.getElementById('order_by_' + number).selectedIndex].value;
    
    // if order by is blank or random, then hide ascending/descending pick list
    if ((order_by == '') || (order_by == 'random')) {
        document.getElementById('order_by_' + number + '_type').style.display = 'none';
        
    // else order by is not blank or random, so show ascending/descending pick list
    } else {
        document.getElementById('order_by_' + number + '_type').style.display = 'inline';
    }
}

// loop through all filters in order to create rows for filters
function initialize_filters()
{
    for (var i = 0; i < filters.length; i++) {
        create_filter(filters[i]);
    }
}

// create row for filter
function create_filter(properties)
{
    // if no properties were passed, then set blank values
    if (!properties) {
        var properties = new Array();
        properties['field'] = '';
        properties['operator'] = '';
        properties['value'] = '';
        properties['dynamic_value'] = '';
        properties['dynamic_value_attribute'] = '';
    }
    
    // get filter number by adding one to the current number of filters
    var filter_number = last_filter_number + 1;
    
    var tbody = document.getElementById('filter_table').getElementsByTagName('tbody')[0]; 
    var tr = document.createElement('tr');
    
    // prepare content for field cell
    var field_cell_html =
        '<select id="filter_' + filter_number + '_field" name="filter_' + filter_number + '_field" onchange="update_value_cell(' + filter_number + '); update_dynamic_value(' + filter_number + ')">\n\
            <option value=""></option>';
    
    // loop through all field options in order to prepare field options for pick list
    for (var i = 0; i < field_options.length; i++) {
        // if the option is a starting optgroup, then prepare starting optgroup
        if (field_options[i]['value'] == '<optgroup>') {
            field_cell_html += '<optgroup label="' + prepare_content_for_html(field_options[i]['name']) + '">';
            
        // else if option is an ending optgroup, then prepare ending optgroup
        } else if (field_options[i]['value'] == '</optgroup>') {
            field_cell_html += '</optgroup>';
            
        // else option is a standard option, so prepare standard option
        } else {
            var status = '';
            
            // if this option should be selected by default, then select option by default
            if (properties['field'] == field_options[i]['value']) {
                status = ' selected="selected"';
            }
            
            field_cell_html += '<option value="' + field_options[i]['value'] + '"' + status + '>' + prepare_content_for_html(field_options[i]['name']) + '</option>';
        }
    }
    
    field_cell_html += '</select>';
    
    // insert content into field cell
    var td_1 = document.createElement('td');
    td_1.innerHTML = field_cell_html;
    
    // prepare content for operator cell
    var operator_cell_html = '<select name="filter_' + filter_number + '_operator">';
    
    // create array for operator options
    var operators = new Array(
        '',
        'contains',
        'does not contain',
        'is equal to',
        'is not equal to',
        'is less than',
        'is less than or equal to',
        'is greater than',
        'is greater than or equal to');
    
    // loop through all operators in order to prepare options
    for (var i = 0; i < operators.length; i++) {
        var status = '';
        
        // if this operator should be selected by default, then select operator by default
        if (properties['operator'] == operators[i]) {
            status = ' selected="selected"';
        }
        
        operator_cell_html += '<option value="' + operators[i] + '"' + status + '>' + operators[i] + '</option>';
    }
    
    operator_cell_html += '</select>';
    
    // insert content into operator cell
    var td_2 = document.createElement('td');
    td_2.innerHTML = operator_cell_html;
    
    var td_3 = document.createElement('td');
    td_3.id = 'filter_' + filter_number + '_value_cell';
    
    // prepare content for dynamic value cell
    var dynamic_value_cell_html =
        '<input id="filter_' + filter_number + '_dynamic_value_attribute" name="filter_' + filter_number + '_dynamic_value_attribute" type="text" value="' + prepare_content_for_html(properties['dynamic_value_attribute']) + '" size="2" maxlength="10" style="display: none" />\n\
        <select id="filter_' + filter_number + '_dynamic_value" name="filter_' + filter_number + '_dynamic_value" style="display: none" onchange="update_dynamic_value_attribute(' + filter_number + '); clear_value(' + filter_number + ')"></select>';
    
    // insert content into dynamic value cell
    var td_4 = document.createElement('td');
    td_4.innerHTML = dynamic_value_cell_html;
    
    // prepare content for delete cell
    var delete_cell_html = '<a href="javascript:void(0)" onclick="delete_filter(this.parentNode.parentNode)" class="button">Delete</a>';
    
    var td_5 = document.createElement('td');
    td_5.innerHTML = delete_cell_html;
    
    tr.appendChild(td_1);
    tr.appendChild(td_2);
    tr.appendChild(td_3);
    tr.appendChild(td_4);
    tr.appendChild(td_5);
    
    tbody.appendChild(tr);
    
    update_value_cell(filter_number, properties['value']); 
    update_dynamic_value(filter_number, properties['dynamic_value'], properties['dynamic_value_attribute']);
    
    // update number of filters
    last_filter_number++;
    document.getElementById('last_filter_number').value = last_filter_number;
    
}

function delete_filter(tr)
{
    tbody = tr.parentNode;
    tbody.removeChild(tr);
}

function update_value_cell(filter_number, value)
{
    // if value is not defined, then set value to empty string
    if (!value) {
        value = '';
    }
    
    // get field value for filter
    var field_value = document.getElementById('filter_' + filter_number + '_field').value;
    
    // loop through field options in order to determine if there are value options for field
    
    for (var i = 0; i < field_options.length; i++) {
        // if the option is the currently selected option, then prepare value cell HTML
        if (field_options[i]['value'] == field_value) {
            var value_cell_html = '';
            
            // if there are value options for the field, then create HTML for pick list of value options
            if (field_options[i]['value_options']) {
                value_cell_html =
                    '<select id="filter_' + filter_number + '_value" name="filter_' + filter_number + '_value">\n\
                        <option value=""></option>';
                
                // loop through all value options in order to prepare values options for pick list
                for (var j = 0; j < field_options[i]['value_options'].length; j++) {
                    var status = '';
                    
                    // if this option should be selected by default, then select option by default
                    if (value == field_options[i]['value_options'][j]['value']) {
                        status = ' selected="selected"';
                    }
                    
                    value_cell_html += '<option value="' + field_options[i]['value_options'][j]['value'] + '"' + status + '>' + prepare_content_for_html(field_options[i]['value_options'][j]['name']) + '</option>';
                }
                
                value_cell_html += '</select>';
                
            // else there are not value options for the field, so create HTML for value text box
            } else {
                value_cell_html = '<input id="filter_' + filter_number + '_value" name="filter_' + filter_number + '_value" type="text" value="' + prepare_content_for_html(value) + '" maxlength="255" />';
            }
            
            // update value cell with HTML
            document.getElementById('filter_' + filter_number + '_value_cell').innerHTML = value_cell_html;
            
            break;
        }
    }
}

function update_dynamic_value(filter_number, dynamic_value, dynamic_value_attribute)
{
    // get field value for filter
    field_value = document.getElementById('filter_' + filter_number + '_field').value;
    
    // get field type
    var field_type = '';
    
    // loop through all field options in order to find type
    for (var i = 0; i < field_options.length; i++) {
        // if this field option is the selected field option, then set type
        if (field_options[i]['value'] == field_value) {
            field_type = field_options[i]['type'];
            break;
        }
    }
    
    // create array for dynamic value options
    var dynamic_value_options = new Array();
    
    dynamic_value_options[0] = new Array();
    dynamic_value_options[0]['name'] = '';
    dynamic_value_options[0]['value'] = '';
    
    // if field type is date then add options for date
    if (field_type == 'date') {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Current Date';
        dynamic_value_options[index]['value'] = 'current date';
    }
    
    // if field type is date and time then add options for date and time
    if (field_type == 'date and time') {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Current Date & Time';
        dynamic_value_options[index]['value'] = 'current date and time';
    }
    
    // if field type is date and time then add options for date and time
    if ((field_type == 'date') || (field_type == 'date and time')) {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Day(s) Ago';
        dynamic_value_options[index]['value'] = 'days ago';
    }
    
    // if field type is time then add options for time
    if (field_type == 'time') {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Current Time';
        dynamic_value_options[index]['value'] = 'current time';
    }
    
    // if field type is username then add options for username
    if (field_type == 'username') {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Viewer';
        dynamic_value_options[index]['value'] = 'viewer';
    }
    
    // if field type is email address then add options for email address
    if (field_type == 'email address') {
        var index = dynamic_value_options.length;
        dynamic_value_options[index] = new Array();
        dynamic_value_options[index]['name'] = 'Viewer\'s E-mail Address';
        dynamic_value_options[index]['value'] = 'viewers email address';
    }
    
    // remove any existing options from dynamic value pick list
    document.getElementById('filter_' + filter_number + '_dynamic_value').options.length = 0;
    
    // loop through all dynamic value options in order to add options to dynamic value pick list
    for (var i = 0; i < dynamic_value_options.length; i++) {
        document.getElementById('filter_' + filter_number + '_dynamic_value').options[i] = new Option(dynamic_value_options[i]['name'], dynamic_value_options[i]['value']);
        
        // if this dynamic value option should be selected by default, then select dynamic value option by default
        if (dynamic_value_options[i]['value'] == dynamic_value) {
            document.getElementById('filter_' + filter_number + '_dynamic_value').selectedIndex = i;
        }
    }
    
    // if there is more than one dynamic value option, then show dynamic value pick list
    if (dynamic_value_options.length > 1) {
        document.getElementById('filter_' + filter_number + '_dynamic_value').style.display = 'inline';
        
    // else there is not at least one dynamic value option, so hide dynamic value pick list and attribute
    } else {
        document.getElementById('filter_' + filter_number + '_dynamic_value').style.display = 'none';
    }
    
    update_dynamic_value_attribute(filter_number, dynamic_value_attribute);
}

function update_dynamic_value_attribute(filter_number, dynamic_value_attribute)
{
    // get dynamic value for filter
    dynamic_value = document.getElementById('filter_' + filter_number + '_dynamic_value').value;
    
    // if the dynamic value is days ago, then show attribute
    if (dynamic_value == 'days ago') {
        document.getElementById('filter_' + filter_number + '_dynamic_value_attribute').style.display = 'inline';
    
    // else the dynamic value is not days ago, so hide attribute
    } else {
        document.getElementById('filter_' + filter_number + '_dynamic_value_attribute').style.display = 'none';
    }
}

function clear_value(filter_number)
{
    // if an option was selected for dynamic value pick list, then clear value
    if (document.getElementById('filter_' + filter_number + '_dynamic_value').options[document.getElementById('filter_' + filter_number + '_dynamic_value').selectedIndex].value != '') {
        document.getElementById('filter_' + filter_number + '_value').value = '';
    }
}

function submit_form(form_name) {
    document.getElementById(form_name).submit();
}

function submit_optimize_content()
{
    // if save and analyze button was clicked, then show analysis notice and determine if form should be submitted
    if (submit_button == 'save_and_analyze') {
        // show analysis notice
        document.getElementById('analysis_notice').style.display = '';
        
        // if analysis is allowed, then submit form
        if (allow_analysis == true) {
            return true;
            
        // else analysis is not allowed, so do not submit form
        } else {
            return false;
        }
        
    // else save and return button was clicked, so submit form
    } else {
        return true;
    }
}

function show_or_hide_cut_off_cell(checkbox) {
    // if the checkbox is checked, then show it's cut-off cell
    if (checkbox.checked == true) {
        document.getElementById(checkbox.id + '_cutoff_time_cell').style.display = '';
        
    // else hide it's cut-off cell
    } else {
        document.getElementById(checkbox.id + '_cutoff_time_cell').style.display = 'none';
    }
}

// create row for shipping cut-off
function create_shipping_cutoff(properties)
{
    // if no properties were passed, then set blank values
    if (!properties) {
        var properties = new Array();
        properties['shipping_method_id'] = '';
        properties['date_and_time'] = '';
    }
    
    // get shipping cut-off number by adding one to the current number of shipping cut-offs
    var shipping_cutoff_number = last_shipping_cutoff_number + 1;
    
    var tbody = document.getElementById('shipping_cutoff_table').getElementsByTagName('tbody')[0]; 
    var tr = document.createElement('tr');
    
    // prepare content for shipping method id cell
    var shipping_method_id_cell_html =
        '<select id="shipping_cutoff_' + shipping_cutoff_number + '_shipping_method_id" name="shipping_cutoff_' + shipping_cutoff_number + '_shipping_method_id">\n\
            <option value=""></option>';
    
    // loop through all shipping method id options in order to prepare options for pick list
    for (var i = 0; i < shipping_method_id_options.length; i++) {
        var status = '';
        
        // if this option should be selected by default, then select option by default
        if (properties['shipping_method_id'] == shipping_method_id_options[i]['value']) {
            status = ' selected="selected"';
        }
        
        shipping_method_id_cell_html += '<option value="' + shipping_method_id_options[i]['value'] + '"' + status + '>' + prepare_content_for_html(shipping_method_id_options[i]['name']) + '</option>';
    }
    
    shipping_method_id_cell_html += '</select>';
    
    // insert content into shipping method id cell
    var td_1 = document.createElement('td');
    td_1.innerHTML = shipping_method_id_cell_html;
    
    // prepare content for date and time cell
    var td_2 = document.createElement('td');
    td_2.innerHTML = '<input id="shipping_cutoff_' + shipping_cutoff_number + '_date_and_time" name="shipping_cutoff_' + shipping_cutoff_number + '_date_and_time" type="text" value="' + properties['date_and_time'] + '" size="20" maxlength="22" />';
    
    // prepare content for delete cell
    var td_3 = document.createElement('td');
    td_3.innerHTML = '<a href="javascript:void(0)" onclick="delete_shipping_cutoff(this.parentNode.parentNode)" class="button">Delete</a>';
    
    tr.appendChild(td_1);
    tr.appendChild(td_2);
    tr.appendChild(td_3);
    
    tbody.appendChild(tr);

    $('#shipping_cutoff_' + shipping_cutoff_number + '_date_and_time').datetimepicker({
        dateFormat: date_picker_format,
        timeFormat: "h:mm TT"
    });
    
    // show the shipping cut-off table in case it was hidden
    document.getElementById('shipping_cutoff_table').style.display = '';
    
    // update number of shipping cut-offs
    last_shipping_cutoff_number++;
    document.getElementById('last_shipping_cutoff_number').value = last_shipping_cutoff_number;
}

function delete_shipping_cutoff(tr)
{
    tbody = tr.parentNode;
    tbody.removeChild(tr);
    
    // if there is only one row in the table, then it is the heading row, so hide the whole table
    if (document.getElementById('shipping_cutoff_table').getElementsByTagName('tr').length == 1) {
        document.getElementById('shipping_cutoff_table').style.display = 'none';
    }
}

// this function updates the theme designer's control panel and preview window's width and height to match the browser
function resize_theme_designer() {
    // set width and height for the theme preview
    $('#theme_preview_iframe').css('width', document.documentElement.clientWidth - $('#theme_designer_toolbar').css('width').substr(0, $('#theme_designer_toolbar').css('width').lastIndexOf('p')) - 2);
    $('#theme_preview_iframe').css('height', document.documentElement.clientHeight);
    
    // set the toolbar's height
    $('#theme_designer_toolbar').css('height', document.documentElement.clientHeight);
    
    // calculate the height for the modules container by looking at the browser height and subtracting various other contains that appear above and below the modules container
    var theme_designer_toolbar_modules_height = parseInt($(window).height() - $('#header').height() - $('#subnav').height() - $('#button_bar').height() - $('#button_bar').height() - $('#content_header').height() - $('#content_footer').height() - 83);
    
    // update the modules container height
    $('#theme_designer_toolbar #modules').css('height', theme_designer_toolbar_modules_height);
}

// Create function to allow a module to be highlighted in the preview pane of the theme designer
// when a user hovers over the module
function highlight_module(selector)
{
    $('#theme_preview_iframe').contents().find(selector).css('position', 'relative');
    $('#theme_preview_iframe').contents().find(selector).append('<div id="module_highlight" style="background-color: rgba(143, 195, 117, .7); border: 1px solid #428221; width:100%; height:100%; position: absolute; top: 0; left: 0; z-index: 2147483647"></div>');
}

// Create function to allow a module to be unhighlighted in the preview pane of the theme designer
// when a user hovers off the module
function unhighlight_module(selector)
{
    $('#theme_preview_iframe').contents().find('#module_highlight').remove();
    $('#theme_preview_iframe').contents().find(selector).css('position', '');
}

var last_closed_module_id = 0;

// this function initializes the theme design's accordion
function init_theme_designer_accordion()
{
    // add listeners to all headers to listen for a click, and update the accordion when clicked
    $('.module .header').click(function () {
        // get the selected module id
        var selected_module_id = this.parentNode.id;
        
        var current_module_id = 0;
        
        // if there is a current module then save it's id
        if ($('.module .content.current')[0]) {
            current_module_id = $('.module .content.current')[0].parentNode.id;
        }
        
        // if there is a current module id, then call the function that will hide the currently opened module
        if (current_module_id) {
            hide_current_module(selected_module_id, current_module_id);
        }
        
        // if the selected module is not the current module, and if the selected module id is not equal to the last closed module id, then open the selected module
        if ((selected_module_id != current_module_id) && (selected_module_id != last_closed_module_id)) {
            // if there is not a module inside of the area that was clicked on, and if the area is blank, make an ajax call to get the area's content
            if ((!$('#' + selected_module_id + '.module .content').find('.module')[0]) && (!$('#' + selected_module_id + '.module .content').find('.field')[0])) {
                var file_id = document.getElementById('file_id').value;
                
                // send an AJAX GET in order to get the module content
                // async is set to false so that the request is sent before the browser window goes to the next page
                var html_output = $.ajax({
                    type: 'GET',
                    url: 'get_theme_designer_module.php',
                    data: 'file_id=' + file_id + '&module_id=' + selected_module_id,
                    async: false
                }).responseText;
                
                // if there is html output, then put the HTML into the area and init the color pickers
                if (html_output) {
                    // put html into area
                    $('#' + selected_module_id + '.module .content')[0].innerHTML = html_output;
                    
                    // initiate the color pickers
                    init_color_picker();
                }
            }
            
            // slide down the selected module
            $($('#' + selected_module_id + '.module .content')[0]).slideDown('fast');
            
            // add the current class to the module
            $($('#' + selected_module_id + '.module .content')[0]).addClass('current');
            
            // remove the closed class from the header arrow
            $($('#' + selected_module_id + '.module .header span.heading_arrow_image')[0]).removeClass('closed');
            
            // add the expanded class to the header arrow
            $($('#' + selected_module_id + '.module .header span.heading_arrow_image')[0]).addClass('expanded');
            
            // if there is an anchor for the current module, then send the user to it's anchor
            if ($('#' + selected_module_id + '.module').find('.anchor')[0].name) {
                setTimeout (function () {
                    window.location = '#' + $('#' + selected_module_id + '.module').find('.anchor')[0].name;
                }, 250);
            }
            
        // else if the selected module id equals the current module id, then set it's parent to the current module
        } else if (selected_module_id == current_module_id) {
            $($('#' + selected_module_id + '.module .content')[0].parentNode.parentNode).addClass('current');
        }
        
        // reset the last closed module id
        last_closed_module_id = 0;
    });
}

// this function hides the current opened module
function hide_current_module(selected_module_id, current_module_id)
{
    // if there is an current module id, and if the selected module is not inside of this module, then continue to close the module
    if ((current_module_id != 0) && (!$('#' + current_module_id + '.module .content').find('#' + selected_module_id + '.module')[0])) {
        // if the current module's parent module is not equal to the selected module's parent module, or the root module then call this function again, but pass in the current module's parent module as the current module
        // this is so that we can go up a level to find the parent module that we need to close
        if ((document.getElementById(current_module_id).parentNode.parentNode.id != document.getElementById(selected_module_id).parentNode.parentNode.id) && (document.getElementById(current_module_id).parentNode.id != 'modules')) {
            hide_current_module(selected_module_id, document.getElementById(current_module_id).parentNode.parentNode.id);
            
        // else close the current module
        } else {
            // slide up the module, and then hide all of it's children
            $($('#' + current_module_id + '.module .content')[0]).slideUp('fast', function () {
                $('#' + current_module_id + '.module .content').css('display', 'none');
            });
            
            // remove the expanded class from the header arrow
            $('#' + current_module_id + '.module .header span.heading_arrow_image').removeClass('expanded');
            
            // add the closed class to the header arrow
            $('#' + current_module_id + '.module .header span.heading_arrow_image').addClass('closed');
            
            // set the last closed module id to the current module id that was just closed
            last_closed_module_id = current_module_id;
        }
    }
    
    // remove the current class from the module that has it
    $('.module .content.current').removeClass('current');
}

// this function opens a module in the theme designer accordion
function open_theme_designer_accordion_module(module_id, modules_to_open)
{
    // if the modules to open array is not defined, then define it
    if (!modules_to_open) {
        var modules_to_open = new Array();
    }
    
    // if the parent of this module is the modules container, then follow the click path to open the accordion
    if ($('#' + module_id + '.module')[0].parentNode.id == 'modules') {
        // add the top most level to the modules to open array
        modules_to_open.push($('#' + module_id + '.module')[0].id);
        
        // reverse the array so that the modules are opened in the correct order
        modules_to_open = modules_to_open.reverse();
        
        // loop through the modules to open the accordion
        for(var i = 0; i < modules_to_open.length; i++) {
            $($('#' + modules_to_open[i] + '.module .header')[0]).trigger('click');
        }
        
    // else save the module and call this function again with the parent as the module id
    } else {
        // save this module to the modules to open array
        modules_to_open.push($('#' + module_id + '.module')[0].id);
        
        // call the function again for the parent
        open_theme_designer_accordion_module($('#' + module_id + '.module')[0].parentNode.parentNode.id, modules_to_open);
    }
}

// this function initializes the color picker for all color picker objects in the document
function init_color_picker() {
    // get all of the color picker toggles in the document
    var color_picker_toggles = $('.color_picker_toggle');
    
    // loop through each color picker toggle, and create a color picker object for it
    for (var i = 0; i <= color_picker_toggles.length; i++) {
        // if there is a color picker toggle, then continue
        if (color_picker_toggles[i]) {
            // convert the rgb color value to hex, and store the value to be used later on
            var background_color = rgb_to_hex(color_picker_toggles[i].style.backgroundColor);
            
            var toggle_id = 0;
            var input_id = 0;
            var placeholder_id = 0;
            
            // initiate the color picker
            $(color_picker_toggles[i]).ColorPicker({
                eventName: 'click',
                color: background_color,
                flat: false,
                onShow: function (colpkr) {
    				// fade in the color picker
    				$(colpkr).fadeIn(100);
                    
                    // set the input field's id that is in the same table cell as the color picker toggle to reflect the color picker object's id
                    $(this.parentNode).find('input')[0].id = colpkr.id + '_input';
                    
                    // remember this input's id so that we can use it during the onChange function
                    input_id = colpkr.id + '_input';
                    
                    // set the span's id that is in the same table cell as the color picker toggle to reflect the color picker object's id
                    $(this.parentNode).find('span')[1].id = colpkr.id + '_placeholder';
                    
                    // remember this spans's id so that we can use it during the onChange function
                    placeholder_id = colpkr.id + '_placeholder';
                    
                    // set the current color picker toggle to include the color picker object's id
                    this.id = colpkr.id + '_toggle';
                    
                    // remember this toggle's id so that we can use it during the onChange function
                    toggle_id = colpkr.id + '_toggle';
                    
                    // add a click event to the none button, and when clicked, clear out the color
                    $('.colorpicker_none_button').click(function() {
                        // update the color picker toggle to be white
        				$('#' + toggle_id).css('background-color', '#FFFFFF');
                        
                        // update the color picker toggle's input field with the new hex value
                        document.getElementById(input_id).value = '';
                        
                        // update the color picker's value placeholder
                        document.getElementById(placeholder_id).firstChild.nodeValue = 'Inherit';
                        
                        // fade out the color picker
        				$('.colorpicker').fadeOut(100);
                    });
                    
                    return false;
    			},
    			onHide: function (colpkr) {
    				// fade out the color picker
    				$(colpkr).fadeOut(500);
                    return false;
    			},
    			onSubmit: function (hsb, hex, rgb) {
    				// update the color picker toggle with the new color
    				$('#' + toggle_id).css('background-color', '#' + hex.toUpperCase());
                    
                    // remove the background image so that we can see the color
                    $('#' + toggle_id).css('background-image', 'none');
                    
                    // update the color picker toggle's input field with the new hex value
                    document.getElementById(input_id).value = hex.toUpperCase();
                    
                    // update the color picker's value placeholder
                    document.getElementById(placeholder_id).firstChild.nodeValue = '#' + hex.toUpperCase();
                    
                    // fade out the color picker
    				$('.colorpicker').fadeOut(100);
    			}
            });
        }
    }
}

// Setup edit comment publish pick list functionality and date/time picker.
function init_edit_comment_publish() {
    var publish = $('#publish');
    var publish_date_and_time = $('#publish_date_and_time');
    var publish_schedule = $('.publish_schedule');

    // If the schedule option is selected by default when the page first loaded,
    // then show fields for it and init date/time picker.
    if (publish.val() == 'schedule') {
        publish_schedule.fadeIn();

        publish_date_and_time.datetimepicker({
            dateFormat: date_picker_format,
            timeFormat: "h:mm TT"
        });
    } 

    // When the publish pick list is changed, then update fields.
    publish.change(function() {
        // If the schedule option is selected, then show fields for it.
        if (publish.val() == 'schedule') {
            publish_schedule.fadeIn();

            publish_date_and_time.datetimepicker({
                dateFormat: date_picker_format,
                timeFormat: "h:mm TT"
            });

            // Place the focus in the date & time field,
            // so that the date/time picker automatically appears.
            publish_date_and_time.focus();

        // Otherwise the schedule option is not selected, so hide its fields.
        } else {
            publish_schedule.fadeOut();
        }
    });
}

// this function takes an RGB value and converts it to a hex value
function rgb_to_hex(color) {
    // if the color has a pound sign, then return out of the function
    if (color.substr(0, 1) === '#') {
        return color;
    }
    
    // get the color digits from the string
    var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
    
    // break the digits into their specific color values
    var red = parseInt(digits[2]);
    var green = parseInt(digits[3]);
    var blue = parseInt(digits[4]);
    
    // convert each rgb color value to a hex value and then return the value as a hex color
    return '#' + color_to_hex(red) + color_to_hex(green) + color_to_hex(blue);

};

// this function takes a signle RGB value and converts it into a hex value
function color_to_hex(color) {
    // if the color is null, then return 00
    if (color == null){
        return "00";
    }
    
    // turn the color into only integers
    color = parseInt(color); 
    
    // if the color is 0 or not a number, then return 00
    if ((color==0) || (isNaN(color))){
        return "00";
    }
    
    // conver the colors to hex
    color = Math.max(0,color); 
    color = Math.min(color,255);
    color = Math.round(color);
    
    // return the hex value
    return "0123456789ABCDEF".charAt((color-color%16)/16) + "0123456789ABCDEF".charAt(color%16);
}

// this function shows and hides the background css rule options
function show_or_hide_background_options(option, options_container_path) {
    // if the option is solid color, then show the solid color options and hide the image options
    if (option == 'solid_color') {
        document.getElementById(options_container_path + '_background_color_row').style.display = '';
        document.getElementById(options_container_path + '_background_image_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_horizontal_position_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_vertical_position_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_repeat_row').style.display = 'none';
        
    // if the option is image, then show the image options and hide the solid color options
    } else if (option == 'image') {
        document.getElementById(options_container_path + '_background_color_row').style.display = '';
        document.getElementById(options_container_path + '_background_image_row').style.display = '';
        document.getElementById(options_container_path + '_background_horizontal_position_row').style.display = '';
        document.getElementById(options_container_path + '_background_vertical_position_row').style.display = '';
        document.getElementById(options_container_path + '_background_repeat_row').style.display = '';
        
    // else none was selected so hide both the image and solid color options
    } else {
        document.getElementById(options_container_path + '_background_color_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_image_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_horizontal_position_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_vertical_position_row').style.display = 'none';
        document.getElementById(options_container_path + '_background_repeat_row').style.display = 'none';
    }
}

// this function shows and hides the css rule options for the borders
function show_or_hide_css_rule_border_options(options_container_path, object) {
    if (document.getElementById(options_container_path + '_border_size_row').style.display == 'none') {
        document.getElementById(options_container_path + '_border_size_row').style.display = '';
        document.getElementById(options_container_path + '_border_color_row').style.display = '';
        document.getElementById(options_container_path + '_border_style_row').style.display = '';
        document.getElementById(options_container_path + '_border_position_row').style.display = '';
        
    } else {
        document.getElementById(options_container_path + '_border_size_row').style.display = 'none';
        document.getElementById(options_container_path + '_border_color_row').style.display = 'none';
        document.getElementById(options_container_path + '_border_style_row').style.display = 'none';
        document.getElementById(options_container_path + '_border_position_row').style.display = 'none';
    }
}

// this function shows and hides the css rule options for the rounded corners
function show_or_hide_css_rule_rounded_corner_options(options_container_path, object) {
    if (document.getElementById(options_container_path + '_rounded_corner_top_left_row').style.display == 'none') {
        document.getElementById(options_container_path + '_rounded_corner_top_left_row').style.display = '';
        document.getElementById(options_container_path + '_rounded_corner_top_right_row').style.display = '';
        document.getElementById(options_container_path + '_rounded_corner_bottom_left_row').style.display = '';
        document.getElementById(options_container_path + '_rounded_corner_bottom_right_row').style.display = '';
        
    } else {
        document.getElementById(options_container_path + '_rounded_corner_top_left_row').style.display = 'none';
        document.getElementById(options_container_path + '_rounded_corner_top_right_row').style.display = 'none';
        document.getElementById(options_container_path + '_rounded_corner_bottom_left_row').style.display = 'none';
        document.getElementById(options_container_path + '_rounded_corner_bottom_right_row').style.display = 'none';
    }
}

// this function shows and hides the css rule options for the shadows
function show_or_hide_css_rule_shadow_options(options_container_path, object) {
    if (document.getElementById(options_container_path + '_shadow_horizontal_offset_row').style.display == 'none') {
        document.getElementById(options_container_path + '_shadow_horizontal_offset_row').style.display = '';
        document.getElementById(options_container_path + '_shadow_vertical_offset_row').style.display = '';
        document.getElementById(options_container_path + '_shadow_blur_radius_row').style.display = '';
        document.getElementById(options_container_path + '_shadow_color_row').style.display = '';
        
    } else {
        document.getElementById(options_container_path + '_shadow_horizontal_offset_row').style.display = 'none';
        document.getElementById(options_container_path + '_shadow_vertical_offset_row').style.display = 'none';
        document.getElementById(options_container_path + '_shadow_blur_radius_row').style.display = 'none';
        document.getElementById(options_container_path + '_shadow_color_row').style.display = 'none';
    }
}

// This function shows and hides the css rule options for the previous & next buttons for ad regions.
function show_or_hide_css_rule_previous_and_next_buttons_options(options_container_path, object) {
    if (document.getElementById(options_container_path + '_previous_and_next_buttons_horizontal_offset_row').style.display == 'none') {
        document.getElementById(options_container_path + '_previous_and_next_buttons_horizontal_offset_row').style.display = '';
        document.getElementById(options_container_path + '_previous_and_next_buttons_vertical_offset_row').style.display = '';
        
    } else {
        document.getElementById(options_container_path + '_previous_and_next_buttons_horizontal_offset_row').style.display = 'none';
        document.getElementById(options_container_path + '_previous_and_next_buttons_vertical_offset_row').style.display = 'none';
    }
}

// this function shows or hides the style option
function show_or_hide_style_type(option)
{
    if (option == 'system') {
        document.getElementById('layout').style.display = '';
    } else {
        document.getElementById('layout').style.display = 'none';
    }
}

// this function shows or hides the theme type options
function show_or_hide_theme_type_options(option)
{
    // if the option is "system", then show the system theme options, hide the custom theme options and change the label of the submit button
    if (option == 'system') {
        // hide the custom theme option
        document.getElementById('custom_theme_type_option_heading_row').style.display = 'none';
        document.getElementById('custom_theme_type_option_row').style.display = 'none';
        
        // show the create system theme options
        document.getElementById('create_system_theme_options').style.display = '';
        
        // show the new system theme option heading and row
        document.getElementById('new_system_theme_type_option_heading_row').style.display = '';
        document.getElementById('new_system_theme_type_option_row').style.display = '';
        
        // set the hidden file upload field to custom file
        document.getElementById('file_upload_field').value = 'system_theme_csv_file';
        
        // change the submit button's value to say "Create & Continue"
        document.getElementById('submit_button').value = 'Create & Continue';
        
        // select the new theme option by default
        document.getElementById('new_theme').checked = 'checked';
    
    // else hide the system theme options, show the custom theme options and change the label of the submit button
    } else {
        // hide the create system theme options
        document.getElementById('create_system_theme_options').style.display = 'none';
        
        // hide the new system theme option heading and row
        document.getElementById('new_system_theme_type_option_heading_row').style.display = 'none';
        document.getElementById('new_system_theme_type_option_row').style.display = 'none';
        
        // hide the import system theme option heading and row
        document.getElementById('import_system_theme_type_option_heading_row').style.display = 'none';
        document.getElementById('import_system_theme_type_option_row').style.display = 'none';
        
        // show the custom theme option
        document.getElementById('custom_theme_type_option_heading_row').style.display = '';
        document.getElementById('custom_theme_type_option_row').style.display = '';
        
        // change the submit button's value to say "Create"
        document.getElementById('submit_button').value = 'Create';
        
        // set the hidden file load field to custom file
        document.getElementById('file_upload_field').value = 'custom_css_file';
    }
}

// this function shows or hides the theme type options
function show_or_hide_create_system_theme_options(option)
{
    // if the option is "import", then show the appropriate rows
    if (option == 'import') {
        // hide the new system theme option headign and row
        document.getElementById('new_system_theme_type_option_heading_row').style.display = 'none';
        document.getElementById('new_system_theme_type_option_row').style.display = 'none';
        
        // show the import system theme option headign and row
        document.getElementById('import_system_theme_type_option_heading_row').style.display = '';
        document.getElementById('import_system_theme_type_option_row').style.display = '';
        
        // change the submit button's value to say "Create"
        document.getElementById('submit_button').value = 'Create';
    
    // else set show new system theme rows
    } else {
        // hide the import system theme option headign and row
        document.getElementById('import_system_theme_type_option_heading_row').style.display = 'none';
        document.getElementById('import_system_theme_type_option_row').style.display = 'none';
        
        // show the new system theme option headign and row
        document.getElementById('new_system_theme_type_option_heading_row').style.display = '';
        document.getElementById('new_system_theme_type_option_row').style.display = '';
        
        // change the submit button's value to say "Create & Continue"
        document.getElementById('submit_button').value = 'Create & Continue';
    }
}

function show_or_hide_email_campaign_format()
{
    // start off by hiding all rows under the format field until we determine which should be shown
    document.getElementById('body_row').style.display = 'none';
    document.getElementById('page_id_row').style.display = 'none';
    document.getElementById('body_preview_row').style.display = 'none';

    // if the "plain text" option is selected, then show the body row
    if (document.getElementById('format_plain_text').checked == true) {
        document.getElementById('body_row').style.display = '';
    
    // else the "html" option is selected, so show page row and determine if body preview row should be shown
    } else {
        document.getElementById('page_id_row').style.display = '';

        if (document.getElementById('page_id').options[document.getElementById('page_id').selectedIndex].firstChild) {
            document.getElementById('body_preview_row').style.display = '';
        }
    }
}

function show_or_hide_edit_form_list_view_search()
{
    // Start off by hiding all rows under the search field until we determine which should be shown.
    document.getElementById('search_label_row').style.display = 'none';
    document.getElementById('search_advanced_row').style.display = 'none';
    document.getElementById('search_advanced_show_by_default_row').style.display = 'none';
    document.getElementById('search_advanced_layout_container').style.display = 'none';

    // If search is enabled, then determine which rows should be shown.
    if (document.getElementById('search').checked == true) {
        document.getElementById('search_label_row').style.display = '';
        
        // If MySQL version is new then show advanced fields.
        if (document.getElementById('mysql_version_new').value == 'true') {
            document.getElementById('search_advanced_row').style.display = '';

            show_or_hide_edit_form_list_view_search_advanced();
        }
    }
}

function show_or_hide_edit_form_list_view_search_advanced()
{
    // Start off by hiding all rows under the advanced search field until we determine which should be shown.
    document.getElementById('search_advanced_show_by_default_row').style.display = 'none';
    document.getElementById('search_advanced_layout_container').style.display = 'none';

    // If advanced search is enabled, then shows rows that are under it.
    if (document.getElementById('search_advanced').checked == true) {
        document.getElementById('search_advanced_show_by_default_row').style.display = '';
        document.getElementById('search_advanced_layout_container').style.display = '';
    }
}

function show_or_hide_edit_form_list_view_browse()
{
    // Start off by hiding all rows under the browse field until we determine which should be shown.
    document.getElementById('browse_show_by_default_form_field_id_row').style.display = 'none';
    document.getElementById('browse_fields_row').style.display = 'none';


    // If browse is enabled, then determine which rows should be shown.
    if (document.getElementById('browse').checked == true) {
        document.getElementById('browse_show_by_default_form_field_id_row').style.display = '';
        document.getElementById('browse_fields_row').style.display = '';
    }
}

function show_or_hide_edit_form_list_view_browse_field(field_id) {
    if (document.getElementById('browse_field_' + field_id).checked == true) {
        document.getElementById('browse_field_' + field_id + '_number_of_columns_cell').style.display = '';
        document.getElementById('browse_field_' + field_id + '_sort_order_cell').style.display = '';
        document.getElementById('browse_field_' + field_id + '_shortcut_cell').style.display = '';

        // If there is a date format field (i.e. field has a date or date and time type)
        // then show that cell also.
        if (document.getElementById('browse_field_' + field_id + '_date_format')) {
            document.getElementById('browse_field_' + field_id + '_date_format_cell').style.display = '';
        }

    } else {
        document.getElementById('browse_field_' + field_id + '_number_of_columns_cell').style.display = 'none';
        document.getElementById('browse_field_' + field_id + '_sort_order_cell').style.display = 'none';
        document.getElementById('browse_field_' + field_id + '_shortcut_cell').style.display = 'none';
        document.getElementById('browse_field_' + field_id + '_date_format_cell').style.display = 'none';
    }
}

function toggle_use_folder_name_for_default_value() {
    if (document.getElementById('use_folder_name_for_default_value').checked == true) {
        document.getElementById('default_value').disabled = true;
        $('#default_value').addClass('disabled');
    } else {
        document.getElementById('default_value').disabled = false;
        $('#default_value').removeClass('disabled');
    }
}

function toggle_badge()
{
    if (document.getElementById('badge').checked == true) {
        document.getElementById('badge_label_row').style.display = '';
    } else {
        document.getElementById('badge_label_row').style.display = 'none';
    }
}

function toggle_product_gift_card()
{
    document.getElementById('gift_card_email_subject_row').style.display = 'none';
    document.getElementById('gift_card_email_format_row').style.display = 'none';
    document.getElementById('gift_card_email_body_row').style.display = 'none';
    document.getElementById('gift_card_email_page_id_row').style.display = 'none';

    if (document.getElementById('gift_card').checked == true) {
        document.getElementById('gift_card_email_subject_row').style.display = '';
        document.getElementById('gift_card_email_format_row').style.display = '';
        toggle_product_gift_card_email_format();
    }
}

function toggle_product_gift_card_email_format()
{
    document.getElementById('gift_card_email_body_row').style.display = 'none';
    document.getElementById('gift_card_email_page_id_row').style.display = 'none';

    if (document.getElementById('gift_card_email_format_plain_text').checked == true) {
        document.getElementById('gift_card_email_body_row').style.display = '';
    
    } else {
        document.getElementById('gift_card_email_page_id_row').style.display = '';
    }
}

function toggle_product_submit_form()
{
    document.getElementById('submit_form_custom_form_page_id_row').style.display = 'none';
    document.getElementById('submit_form_create_row').style.display = 'none';
    document.getElementById('submit_form_create_fields_row').style.display = 'none';
    document.getElementById('submit_form_update_row').style.display = 'none';
    document.getElementById('submit_form_update_fields_row').style.display = 'none';

    if (document.getElementById('submit_form').checked == true) {
        document.getElementById('submit_form_custom_form_page_id_row').style.display = '';
        document.getElementById('submit_form_create_row').style.display = '';

        toggle_product_submit_form_create();

        document.getElementById('submit_form_update_row').style.display = '';

        toggle_product_submit_form_update();
    }
}

function toggle_product_submit_form_create()
{
    document.getElementById('submit_form_create_fields_row').style.display = 'none';

    if (document.getElementById('submit_form_create').checked == true) {
        document.getElementById('submit_form_create_fields_row').style.display = '';
    }
}

function toggle_product_submit_form_update()
{
    document.getElementById('submit_form_update_fields_row').style.display = 'none';

    if (document.getElementById('submit_form_update').checked == true) {
        document.getElementById('submit_form_update_fields_row').style.display = '';
    }
}

function toggle_product_add_comment()
{
    document.getElementById('add_comment_page_id_row').style.display = 'none';
    document.getElementById('add_comment_message_row').style.display = 'none';
    document.getElementById('add_comment_name_row').style.display = 'none';
    document.getElementById('add_comment_only_for_submit_form_update_row').style.display = 'none';

    if (document.getElementById('add_comment').checked == true) {
        document.getElementById('add_comment_page_id_row').style.display = '';
        document.getElementById('add_comment_message_row').style.display = '';
        document.getElementById('add_comment_name_row').style.display = '';
        document.getElementById('add_comment_only_for_submit_form_update_row').style.display = '';
    }
}

function init_shipping_method_service() {

    var service = $('#service');

    service.change(function() {

        var service_value = service.val();

        // If a service has been selected and we support real-time rates for that service, then
        // show real-time rate row.
        if (
            service_value
            && (service_value.substr(0, 4) == 'usps' || service_value.substr(0, 3) == 'ups')
        ) {

            $('#realtime_rate_row').fadeIn();

        // Otherwise hide the real-time rate row.
        } else {
            $('#realtime_rate_row').fadeOut();
        }
    });
    
    // Trigger change event for initial page load.
    service.trigger('change');
}

function toggle_variable_base_rate() {

    if ($('.variable_base_rate').is(':checked')) {
        $('.base_rate_2_row').fadeIn();
        $('.base_rate_3_row').fadeIn();
        $('.base_rate_4_row').fadeIn();
    } else {
        $('.base_rate_2_row').fadeOut();
        $('.base_rate_3_row').fadeOut();
        $('.base_rate_4_row').fadeOut();
    }
}

// initialize function for preparing layout cells
function initialize_style_designer()
{
    var selected_area = '';
    var selected_row_index = '';
    var selected_cell_index = '';
    
    // initialize function for deselecting a cell that should no longer be selected
    function deselect_cell(area, row_index, cell_index)
    {
        // add disable styling to add column before button
        $('#' + area + '_add_column_before').addClass('disabled');
        
        // remove onclick event for add column before button
        $('#' + area + '_add_column_before').unbind('click');

        // add disable styling to add column after button
        $('#' + area + '_add_column_after').addClass('disabled');
        
        // remove onclick event for add column after button
        $('#' + area + '_add_column_after').unbind('click');
        
        // add disable styling to edit cell properties button
        $('#' + area + '_edit_cell_properties').addClass('disabled');
        
        // remove onclick event for edit cell properties button
        $('#' + area + '_edit_cell_properties').unbind('click');
        
        // remove selected styling from cell
        $('#' + area + '_row_' + row_index + '_cell_' + cell_index).removeClass('selected');
        
        // clear values for selected variables
        selected_area = '';
        selected_row_index = '';
        selected_cell_index = '';
    }
    
    function select_cell(area, row_index, cell_index)
    {
        // if there is a selected cell, then deselect it
        if (selected_cell_index !== '') {
            deselect_cell(selected_area, selected_row_index, selected_cell_index);
        }

        // remove disable styling from add column before button
        $('#' + area + '_add_column_before').removeClass('disabled');
        
        // add onclick event for add column before button
        $('#' + area + '_add_column_before').bind('click', {area: area}, function(event) {
            var area = event.data.area;
            
            // set the new cell index
            var new_cell_index = selected_cell_index;
            
            // prepare the cell that will be added to a row
            var cell = {
                'region_type': '',
                'region_name': ''
            };
            
            // add the cell to the array
            areas[area]['rows'][selected_row_index]['cells'].splice(new_cell_index, 0, cell);
            
            // update the area so that the correct cells will be displayed for this area
            update_area(area);
            
            // select the cell that we just added
            select_cell(area, selected_row_index, new_cell_index);
        });
        
        // remove disable styling from add column after button
        $('#' + area + '_add_column_after').removeClass('disabled');
        
        // add onclick event for add column after button
        $('#' + area + '_add_column_after').bind('click', {area: area}, function(event) {
            var area = event.data.area;
            
            // set the new cell index to one more than the selected cell index
            var new_cell_index = selected_cell_index + 1;
            
            // prepare the cell that will be added to a row
            var cell = {
                'region_type': '',
                'region_name': ''
            };
            
            // add the cell to the array
            areas[area]['rows'][selected_row_index]['cells'].splice(new_cell_index, 0, cell);
            
            // update the area so that the correct cells will be displayed for this area
            update_area(area);
            
            // select the cell that we just added
            select_cell(area, selected_row_index, new_cell_index);
        });
        
        // remove disable styling from edit cell properties button
        $('#' + area + '_edit_cell_properties').removeClass('disabled');
        
        // add onclick event for edit cell properties button
        $('#' + area + '_edit_cell_properties').click(function() {
            $('#edit_cell_properties').dialog('open');
        });
        
        // update the selected variables so they store information for this cell
        selected_area = area;
        selected_row_index = row_index;
        selected_cell_index = cell_index;
        
        // add selected class to cell
        $('#' + area + '_row_' + row_index + '_cell_' + cell_index).addClass('selected');
    }
    
    // initialize function that will be responsible for updating the label that appears inside a cell
    function update_cell_label(area, row_index, cell_index)
    {
        var region_type = areas[area]['rows'][row_index]['cells'][cell_index]['region_type'];
        var region_name = prepare_content_for_html(areas[area]['rows'][row_index]['cells'][cell_index]['region_name']);
        var page_region_number = areas[area]['rows'][row_index]['cells'][cell_index]['page_region_number'];

        var row = row_index + 1;
        var col = cell_index + 1;
  
        var cell_label = '';
        
        // prepare cell label
        switch (region_type) {
            case '':
                cell_label = '&nbsp;<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .c' + col + '</span>';
                break;
                
            case 'ad':
                cell_label = 'Ad Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .ad_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'cart':
                cell_label = 'Cart Region<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .cart .c' + col + '</span>';
                break;
            
            case 'common':
                cell_label = 'Common Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .cregion_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'designer':
                cell_label = 'Designer Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .cregion_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'dynamic':
                cell_label = 'Dynamic Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .dregion_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'login':
                cell_label = 'Login Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .login_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'menu':
                cell_label = 'Menu Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .menu_' + region_name + ' .c' + col + '</span>';
                break;
                
            case 'menu_sequence':
                cell_label = 'Menu Sequence Region: ' + region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .menu_sequence_' + region_name + ' .c' + col + '</span>';
                break;

            case 'mobile_switch':
                cell_label = 'Mobile Switch<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .mobile_switch .c' + col + '</span>';
                break;
                
            case 'page':
                cell_label = 'Page Region #' + page_region_number + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .pregion .c' + col + '</span>';
                break;
                
            case 'pdf':
                cell_label = 'PDF Region <sup>beta</sup><br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + ' .pdf .c' + col + '</span>';
                break;
                
            case 'system':
                var region_name_for_label = '';
                var region_name_for_label_css = '';
                
                // if there is a region name, then output it next to the label
                if (region_name != '') {
                    region_name_for_label = region_name;
                    region_name_for_label_css = ' .system_' + region_name;
                
                // else just output the basic label
                } else {
                    region_name_for_label = 'Use Page';
                    region_name_for_label_css = ' .system'
                }
                
                cell_label = 'System Region: ' + region_name_for_label + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + region_name_for_label_css + ' .c' + col + '</span>';
                break;
                
            case 'tag_cloud':
                var output_region_name = '';
                var output_region_name_css = '';
                
                // if there is a region name for this tag cloud, then prepare to output it
                if (region_name != '') {
                    output_region_name = ': ' + region_name;
                    output_region_name_css = ' .tcloud_' + region_name;
                } else{
                    output_region_name_css = ' .tcloud';
                }
                
                cell_label = 'Tag Cloud Region' + output_region_name + '<br /><span class="theme_fold_css" style="padding: 0"> .r' + row + 'c' + col + output_region_name_css + ' .c' + col + '</span>';
                break;
        }
        
        // update the label
        $('#' + area + '_row_' + row_index + '_cell_' + cell_index + ' .cell_label')[0].innerHTML = cell_label;
    }

    // Create function that will be used to update all of the page region numbers
    // anytime an event happens that affects the sequence of numbers (e.g. page region cell added)
    function update_page_region_numbers()
    {
        var page_region_number = 0;

        // Loop through the areas.
        for (var area in areas) {
            // Loop through the rows.
            for (var row_index = 0; row_index < areas[area]['rows'].length; row_index++) {
                // Loop through the cells.
                for (var cell_index = 0; cell_index < areas[area]['rows'][row_index]['cells'].length; cell_index++) {
                    // If this cell is a page region, then update number
                    if (areas[area]['rows'][row_index]['cells'][cell_index]['region_type'] == 'page') {
                        // increment page region number for this page region
                        page_region_number += 1;

                        areas[area]['rows'][row_index]['cells'][cell_index]['page_region_number'] = page_region_number;

                        // update page region number in cell label
                        update_cell_label(area, row_index, cell_index);
                    }
                }
            }
        }
    }
    
    // initialize function that will be responsible for looking at the array for an area in order to output cells
    function update_area(area)
    {
        // remove all cells from the cells container, because we are going to recreate cells
        $('#' + area + ' .cells').empty();
        
        // loop through rows in this area in order to add cells
        for (var row_index = 0; row_index < areas[area]['rows'].length; row_index++) {
            var number_of_cells = areas[area]['rows'][row_index]['cells'].length;
            var total_margin = (number_of_cells - 1) * 12;
            var total_border = number_of_cells * 2;
            var total_padding = number_of_cells * 24;
            
            // get the width for the cells based on how many cells are in this row
            var width = ($('#' + area + ' .cells').width() - total_margin - total_border - total_padding) / number_of_cells;
            
            // round down the width to the nearest whole number and subtract some in order to prevent problems with cells not fitting in one row
            width = Math.floor(width) - 5;
            
            // loop through cells in this row
            for (var cell_index = 0; cell_index < areas[area]['rows'][row_index]['cells'].length; cell_index++) {
                // add a div for the cell
                $('#' + area + ' .cells').append('\
                    <div id ="' + area + '_row_' + row_index + '_cell_' + cell_index + '" class="cell">\
                        <div class="cell_label"></div>\
                        <div class="cell_remove">X</div>\
                        <div class="clear"></div>\
                    </div>');
                
                // update the label that appears inside the cell
                update_cell_label(area, row_index, cell_index);
                
                // if the cell is selected, then add a class to the container
                if ((area === selected_area) && (row_index === selected_row_index) && (cell_index === selected_cell_index)) {
                    $('#' + area + '_row_' + row_index + '_cell_' + cell_index).addClass('selected');
                }
                
                // if this cell is the last cell in the row, then add last class, so that extra margin on the right is not added
                if (cell_index == (areas[area]['rows'][row_index]['cells'].length - 1)) {
                    $('#' + area + '_row_' + row_index + '_cell_' + cell_index).addClass('last');
                }
                
                // set the width for the cell
                $('#' + area + '_row_' + row_index + '_cell_' + cell_index).width(width);
                
                // add click event so that cell can be selected when clicked
                $('#' + area + '_row_' + row_index + '_cell_' + cell_index).bind('click', {area: area, row_index: row_index, cell_index: cell_index}, function(event) {
                    var area = event.data.area;
                    var row_index = event.data.row_index;
                    var cell_index = event.data.cell_index;
                    
                    // if this cell is already selected, then open edit cell properties modal dialog
                    if ((area === selected_area) && (row_index === selected_row_index) && (cell_index === selected_cell_index)) {
                        $('#edit_cell_properties').dialog('open');
                        
                    // else this cell is not already selected, so select it
                    } else {
                        select_cell(area, row_index, cell_index);
                    }
                });
                
                // add click event to the remove button, so that the cell can be removed
                $('#' + area + '_row_' + row_index + '_cell_' + cell_index + ' .cell_remove').bind('click', {area: area, row_index: row_index, cell_index: cell_index}, function(event) {
                    var area = event.data.area;
                    var row_index = event.data.row_index;
                    var cell_index = event.data.cell_index;

                    // store the region type before we remove it, so we know further below if it was a page region
                    var region_type = areas[area]['rows'][row_index]['cells'][cell_index]['region_type'];
                    
                    // if this cell is selected, then deselect cell
                    if ((area === selected_area) && (row_index === selected_row_index) && (cell_index === selected_cell_index)) {
                        deselect_cell(area, row_index, cell_index);
                    }
                    
                    // if there is only one cell in the row, then remove the whole row
                    if (areas[area]['rows'][row_index]['cells'].length == 1) {
                        areas[area]['rows'].splice(row_index, 1);
                        
                    // else there is more than one cell in the row, so just remove the cell
                    } else {
                        areas[area]['rows'][row_index]['cells'].splice(cell_index, 1);
                    }
                    
                    update_area(area);

                    // if the cell that was removed was a page region, then update page region numbers
                    if (region_type == 'page') {
                        update_page_region_numbers();
                    }
                });
            }
            
            // add clear div
            $('#' + area + ' .cells').append('<div class="clear"></div>');
        }
    }
    
    // loop through the areas, in order to prepare them
    for (var area in areas) {
        // add event listener to add row before button
        $('#' + area + '_add_row_before').bind('click', {area: area}, function(event) {
            var area = event.data.area;
            
            // if there is a selected cell in this area, then prepare to add the row and cell above the selected cell
            if (area == selected_area) {
                var new_row_index = selected_row_index;
                
            // else there is not a selected cell in this area, so prepare to add the cell to the top of the area
            } else {
                var new_row_index = 0;
            }
            
            // prepare the row and cell that will be added to the array
            var row = {
                'cells': [
                    {
                        'region_type': '',
                        'region_name': ''
                    }
                ]
            };
            
            // add the row and cell to the array
            areas[area]['rows'].splice(new_row_index, 0, row);
            
            // update the area so that the correct cells will be displayed for this area
            update_area(area);
            
            // select the cell that we just added
            select_cell(area, new_row_index, 0);
        });

        // add event listener to add row after button
        $('#' + area + '_add_row_after').bind('click', {area: area}, function(event) {
            var area = event.data.area;
            
            // if there is a selected cell in this area, then prepare to add the row and cell below the selected cell
            if (area == selected_area) {
                var new_row_index = selected_row_index + 1;
                
            // else there is not a selected cell in this area, so prepare to add the cell to the bottom of the area
            } else {
                var new_row_index = areas[area]['rows'].length;
            }
            
            // prepare the row and cell that will be added to the array
            var row = {
                'cells': [
                    {
                        'region_type': '',
                        'region_name': ''
                    }
                ]
            };
            
            // add the row and cell to the array
            areas[area]['rows'].splice(new_row_index, 0, row);
            
            // update the area so that the correct cells will be displayed for this area
            update_area(area);
            
            // select the cell that we just added
            select_cell(area, new_row_index, 0);
        });
        
        update_area(area);
    }
    
    // initialize function that will be responsible for showing or hiding region name field based on what region type is selected
    function show_or_hide_region_type()
    {
        var region_type = document.getElementById('region_type').options[document.getElementById('region_type').selectedIndex].value;

        // if the selected region type supports a region name, then update the region name pick list with options and show pick list
        if (
            (region_type == 'ad')
            || (region_type == 'common')
            || (region_type == 'designer')
            || (region_type == 'dynamic')
            || (region_type == 'login')
            || (region_type == 'menu')
            || (region_type == 'menu_sequence')
            || (region_type == 'tag_cloud')
            || (region_type == 'system')
        ) {
            // remove existing options from region name pick list
            document.getElementById('region_name').length = 0;
            
            // initialize variable for storing region names
            var region_names = [];
            
            // initialize the picklist's first option to be blank
            var picklist_first_option = '';
            
            // get the region names in different ways for different region types
            switch (region_type) {
                case 'ad':
                    region_names = ad_regions;
                    break;
                
                case 'common':
                    region_names = common_regions;
                    break;
                
                case 'designer':
                    region_names = designer_regions;
                    break;
                
                case 'dynamic':
                    region_names = dynamic_regions;
                    break;
                
                case 'login':
                    region_names = login_regions;
                    break;
                
                case 'menu':
                    region_names = menu_regions;
                    break;
                    
                case 'menu_sequence':
                    region_names = menu_sequence_regions;
                    break;
                
                case 'tag_cloud':
                    region_names = tag_cloud_regions;
                    break;
                    
                case 'system':
                    region_names = system_region_pages;
                    
                    // set the picklists first option for the picklist
                    picklist_first_option = '-Use Page-';
                    break;
            }
            
            // initialize variable for storing options that will be added to the region name pick list
            document.getElementById('region_name').options.add(new Option(picklist_first_option, ''));
            
            // loop through all region names in order to prepare options for pick list
            for (var i = 0; i < region_names.length; i++) {
                document.getElementById('region_name').options.add(new Option(region_names[i], region_names[i]));
            }
            
            // update the region name pick list so that the correct option is selected based on the selected region name
            $("#region_name").val(areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_name']);
            
            // show the region name row
            document.getElementById('region_name_row').style.display = '';
            
        // else the selected region type does not require a region name, so hide region name row
        } else {
            document.getElementById('region_name_row').style.display = 'none';
        }
    }
    
    // initialize edit cell properties modal dialog
    $('#edit_cell_properties').dialog({
        autoOpen: false,
        modal: true,
        width: 500,
        height: 200,
        title: 'Edit Cell Properties',
        dialogClass: 'standard',
        open: function() {
            // if there is no region for the selected cell, then default the region type to page
            if (areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_type'] == '') {
                $("#region_type").val('page');
                
            // else there is a region for the selected cell, so update the region type pick list so that the correct option is selected based on the selected cell
            } else {
                $("#region_type").val(areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_type']);
            }
            
            show_or_hide_region_type();
        }
    });
    
    // add on change event to region type pick list
    $('#region_type').change(function() {
        show_or_hide_region_type();
    });
    
    // add click event to update cell properties button
    $('#update_cell_properties').click(function() {
        // prepare to update region type and name
        var region_type = '';
        var region_name = '';
        
        region_type = document.getElementById('region_type').options[document.getElementById('region_type').selectedIndex].value;
        
        // If the region type supports a region name, and there was at least one name for the user to select,
        // then get the name that was selected.
        if (
            (
                (region_type == 'ad')
                || (region_type == 'common')
                || (region_type == 'designer')
                || (region_type == 'dynamic')
                || (region_type == 'login')
                || (region_type == 'menu')
                || (region_type == 'menu_sequence')
                || (region_type == 'tag_cloud')
                || (region_type == 'system')
            )
            && (document.getElementById('region_name').options.length > 0)
        ) {
            region_name = document.getElementById('region_name').options[document.getElementById('region_name').selectedIndex].value;
        }
        
        // if the region type requires a region name and a region name was not selected, then alert the user
        if (
            (
                (region_type == 'ad')
                || (region_type == 'common')
                || (region_type == 'designer')
                || (region_type == 'dynamic')
                || (region_type == 'login')
                || (region_type == 'menu')
                || (region_type == 'menu_sequence')
            )
            && (region_name == '')
        ) {
            alert('Please select a region name.');
            return false;
        }
        
        // store original region type so further below we know if we need to update page region numbers
        var original_region_type = areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_type'];

        // update cell's properties in array
        areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_type'] = region_type;
        areas[selected_area]['rows'][selected_row_index]['cells'][selected_cell_index]['region_name'] = region_name;

        // if this cell was not a page region before and now it is, then update page region numbers
        if (
            (original_region_type != 'page')
            && (region_type == 'page')
        ) {
            update_page_region_numbers();

        // else if this cell was a page region before and now it is not,
        // then update cell label and page region numbers
        } else if (
            (original_region_type == 'page')
            && (region_type != 'page')
        ) {
            update_cell_label(selected_area, selected_row_index, selected_cell_index);

            update_page_region_numbers();

        // else this cell was not a page region before and it still is not one,
        // so just update cell label
        } else {
            update_cell_label(selected_area, selected_row_index, selected_cell_index);
        }
        
        // close the edit cell properties modal dialog
        $('#edit_cell_properties').dialog('close');
    });
    
    // add click event to cancel cell properties button
    $('#cancel_cell_properties').click(function() {
        // close the edit cell properties modal dialog
        $('#edit_cell_properties').dialog('close');
    });
    
    // add submit event for when the form is submitted
    $('#style_designer_form').submit(function() {
        // assume that a "Use Page" system region does not exist until we find out otherwise
        var use_page_system_region_exists = false;
        
        // loop through the areas in order to determine if there is a "Use Page" system region
        area_loop: for (var area in areas) {
            // loop through the rows
            for (var row_index = 0; row_index < areas[area]['rows'].length; row_index++) {
                // loop through the cells
                for (var cell_index = 0; cell_index < areas[area]['rows'][row_index]['cells'].length; cell_index++) {
                    // if this cell has a "Use Page" system region, then remember that and break out of loops
                    if (
                        (areas[area]['rows'][row_index]['cells'][cell_index]['region_type'] == 'system')
                        && (areas[area]['rows'][row_index]['cells'][cell_index]['region_name'] == '')
                    ) {
                        use_page_system_region_exists = true;
                        break area_loop;
                    }
                }
            }
        }
        
        // if a system region does not exist, then alert the user
        if (use_page_system_region_exists == false) {
            alert('Please add one "Use Page" system region before continuing.');
            return false;
        }
        
        document.getElementById('areas').value = JSON.stringify(areas);
        return true;
    });
}

// this function handles the confirm for the theme designer cancel button
function theme_designer_cancel_confirm(send_to)
{
    var result = confirm('WARNING: Any changes to this Theme that have not been saved will be lost.');

    // if user select ok to confirmation, then clear the theme designer session and go to the edit theme screen
    if (result == true) {
        // send an AJAX POST in order to clear the theme designer session
        // async is set to false so that the request is sent before the browser window goes to the next page
        $.ajax({
            type: 'GET',
            url: 'clear_theme_designer_session.php?file_id=' + document.getElementById('file_id').value,
            async: false
        });
        
        window.location = send_to;
    }
}

function open_advanced_styling()
{
    var advanced_styling = $('#advanced_styling');

    // initialize modal dialog for advanced styling
    advanced_styling.dialog({
        autoOpen: true,
        title: 'Advanced Styling',
        modal: true,
        dialogClass: 'standard',
        open: function() {
            // set the dialog box's default width and height
            var dialog_width = 400;
            var dialog_height = 500;

            // Get window width and height.
            var window_width = $(window).width();
            var window_height = $(window).height();
            
            // if the dialog's new width is greater than the default, then set the width
            if ((window_width * .6) >= dialog_width) {
                dialog_width = window_width * .6;
            }
            
            // if the dialog's new height is greater than the default, then set the width
            if ((window_height * .75) >= dialog_height) {
                dialog_height = window_height * .75;
            }

            // Update dialog width and height and position it in the center.
            advanced_styling.dialog('option', 'width', dialog_width);
            advanced_styling.dialog('option', 'height', dialog_height);

            var scrollbar_position = 0;
            
            // if there is a page y offset, then get the scrollbar pos
            if (window.pageYOffset) {
                scrollbar_position = window.pageYOffset;
                
            // else this is probally IE, so get the scrollbar position for IE
            } else {
                scrollbar_position = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
            }
            
            // set the left position, by subtracting the width of the window from the width of the dialog and dividing by 2
            var dialog_top = (window_height - dialog_height) / 2;
            
            // if the scrollbar is not at the very top of the page, then adjust the dialog box's top position
            if (scrollbar_position != 0) {
                dialog_top = dialog_top + scrollbar_position;
            }

            var ui_dialog = $('.standard.ui-dialog');
            
            // set the dialog box's position
            ui_dialog.css({
                top: dialog_top + 'px',
                left: '370px'
            });
            
            // set temp textarea to have the standard textarea's value
            document.getElementById('advanced_styling_textarea_temp').value = document.getElementById('advanced_styling_textarea').value;
            
            // show the advanced styling area
            document.getElementById('advanced_styling').style.display = 'block';
            
            // turn the advanced styling textarea into a CodeMirror editor
            advanced_styling_editor = CodeMirror.fromTextArea(document.getElementById('advanced_styling_textarea_temp'), {
                mode: 'css',
                lineNumbers: true,
                indentUnit: 4,
                theme: 'pastel-on-dark',
                lineWrapping: false,
                styleActiveLine: true,
                matchTags: { bothTags: true },
                autoCloseTags: true,
                lint: true,
                gutters: ["CodeMirror-lint-markers"],
                autoCloseBrackets: true
            });

            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
            
            // resize the theme designer so that preview will not jump way down when this dialog is opened
            resize_theme_designer();
        },
        close: function() {
            // copy the code from CodeMirror editor to the textarea that will be submitted with the form
            document.getElementById('advanced_styling_textarea').value = advanced_styling_editor.getValue();
            
            // remove the CodeMirror editor
            $('.CodeMirror').remove();
        },
        resize: function() {
            // resize CodeMirror editor because the dialog has been resized
            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
        }
    });
}

function open_advanced_styling_from_button_bar()
{
    // If the site wide fold is not open, then open it.
    // We have to do this, along with the check below,
    // so that the advanced styling field is in the dom
    // and ready for the dialog to open.
    if ($('#site_wide > .content').css('display') == 'none') {
        $('#site_wide > .header').trigger('click');
    }

    // If the site wide general fold is not open, then open it.
    if ($('#site_wide_general > .content').css('display') == 'none') {
        $('#site_wide_general > .header').trigger('click');
    }

    open_advanced_styling();
}

function open_pre_styling()
{
    // initialize modal dialog for pre styling
    $('#pre_styling').dialog({
        autoOpen: true,
        title: 'Pre Styling',
        modal: true,
        dialogClass: 'standard',
        open: function() {
            // set the dialog box's default width and height
            var dialog_width = 400;
            var dialog_height = 500;
            
            // if the dialog's new width is greater than the default, then set the width
            if (($(window).width() * .6) >= dialog_width) {
                dialog_width = $(window).width() * .6;
            }
            
            // if the dialog's new height is greater than the default, then set the width
            if (($(window).height() * .75) >= dialog_height) {
                dialog_height = $(window).height() * .75;
            }
            
            // set the width and height of the dialog based on the size of the window
            $('.standard.ui-dialog').css({
                width: dialog_width,
                height: dialog_height
            });
            
            var scrollbar_position = 0;
            
            // if there is a pag y offest, then get the scrollbar pos
            if (window.pageYOffset) {
                scrollbar_position = window.pageYOffset;
                
            // else this is probally IE, so get the scrollbar position for IE
            } else {
                scrollbar_position = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
            }
            
            // set the left position, by subtracting the width of the window from the width of the dialog and dividing by 2
            var dialog_top = ($(window).height() - $('.standard.ui-dialog').height()) / 2;
            
            // if the scrollbar is not at the very top of the page, then adjust the dialog box's top position
            if (scrollbar_position != 0) {
                dialog_top = dialog_top + scrollbar_position;
            }
            
            // set the dialog box's position
            $('.standard.ui-dialog').css({
                top: dialog_top + 'px',
                left: '370px'
            });
            
            // set temp textarea to have the standard textarea's value
            document.getElementById('pre_styling_textarea_temp').value = document.getElementById('pre_styling_textarea').value;
            
            // show the advanced styling area
            document.getElementById('pre_styling').style.display = 'block';
            
            // turn the pre styling textarea into a CodeMirror editor
            pre_styling_editor = CodeMirror.fromTextArea(document.getElementById('pre_styling_textarea_temp'), {
                mode: 'css',
                lineNumbers: true,
                indentUnit: 4,
                theme: 'pastel-on-dark',
                lineWrapping: false,
                styleActiveLine: true,
                matchTags: { bothTags: true },
                autoCloseTags: true,
                lint: true,
                gutters: ["CodeMirror-lint-markers"],
                autoCloseBrackets: true
            });

            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
            
            // resize the theme designer so that preview will not jump way down when this dialog is opened
            resize_theme_designer();
        },
        close: function() {
            // copy the code from CodeMirror editor to the textarea that will be submitted with the form
            document.getElementById('pre_styling_textarea').value = pre_styling_editor.getValue();
            
            // remove the CodeMirror editor
            $('.CodeMirror').remove();
        },
        resize: function() {
            // resize CodeMirror editor because the dialog has been resized
            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
        }
    });
}

function open_view_source()
{
    // initialize modal dialog for view source
    // we want view source to be non-modal so that the user can still click around into different modules in the background
    $('#view_source').dialog({
        autoOpen: true,
        title: 'View Source (read-only source from the last update or save)',
        modal: false,
        resizable: false,
        draggable: false,
        dialogClass: 'standard',
        open: function() {
            // set the dialog box's default width and height
            var dialog_width = 400;
            var dialog_height = 500;
            
            // if the dialog's new width is greater than the default, then set the width
            if (($(window).width() * .6) >= dialog_width) {
                dialog_width = $(window).width() * .6;
            }
            
            // if the dialog's new height is greater than the default, then set the width
            if (($(window).height() * .75) >= dialog_height) {
                dialog_height = $(window).height() * .75;
            }
            
            // set the width and height of the dialog based on the size of the window
            $('.standard.ui-dialog').css({
                width: dialog_width,
                height: dialog_height
            });
            
            var scrollbar_position = 0;
            
            // if there is a pag y offest, then get the scrollbar pos
            if (window.pageYOffset) {
                scrollbar_position = window.pageYOffset;
                
            // else this is probally IE, so get the scrollbar position for IE
            } else {
                scrollbar_position = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
            }
            
            // set the left position, by subtracting the width of the window from the width of the dialog and dividing by 2
            var dialog_top = ($(window).height() - $('.standard.ui-dialog').height()) / 2;
            
            // if the scrollbar is not at the very top of the page, then adjust the dialog box's top position
            if (scrollbar_position != 0) {
                dialog_top = dialog_top + scrollbar_position;
            }
            
            // set the dialog box's position
            $('.standard.ui-dialog').css({
                top: dialog_top + 'px',
                left: '370px'
            });
            
            // show the advanced styling area
            document.getElementById('view_source').style.display = 'block';
            
            // turn the advanced styling textarea into a CodeMirror editor
            view_source_editor = CodeMirror.fromTextArea(document.getElementById('view_source_textarea'), {
                mode: 'css',
                lineNumbers: true,
                indentUnit: 4,
                theme: 'pastel-on-dark',
                readOnly: true,
                lineWrapping: false,
                styleActiveLine: true,
                matchTags: { bothTags: true },
                autoCloseTags: true,
                lint: true,
                gutters: ["CodeMirror-lint-markers"],
                autoCloseBrackets: true
            });

            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
            
            // resize the theme designer so that preview will not jump way down when this dialog is opened
            resize_theme_designer();
        },
        close: function() {
            // remove the CodeMirror editor
            $('.CodeMirror').remove();
        },
        resize: function() {
            // resize CodeMirror editor because the dialog has been resized
            $('.CodeMirror').css({'width': '100%'});
            $('.CodeMirror').css({'height': parseInt($('.standard.ui-dialog').height() - 120)});
        }
    });
}

function product_submit_form_update_custom_form_fields()
{
    var custom_form_page_id = $('#submit_form_custom_form_page_id').val();

    submit_form_custom_form_fields = [];

    // If a custom form is selected, then get fields for that custom form,
    // so that we can create a pick list of fields.
    if (custom_form_page_id) {
        $.ajax({
            dataType: 'json',
            url: 'get_custom_form_fields.php',
            data: 'page_id=' + custom_form_page_id,
            async: false,
            success: function(data) {
                submit_form_custom_form_fields = data;
            }
        });
    };

    // Update where pick list.
    init_product_submit_form_update_where();
}

function product_submit_form_add_field(properties)
{
    var action = properties.action;

    if (properties.form_field_id) {
        var form_field_id = properties.form_field_id;
    } else {
        var form_field_id = '';
    }

    if (properties.value) {
        var value = properties.value;
    } else {
        var value = '';
    }

    // Get field number by adding one to the current number of fields.
    var field_number = last_submit_form_field_number[action] + 1;
    
    var tbody = document.getElementById('submit_form_' + action + '_field_table').getElementsByTagName('tbody')[0]; 
    var tr = document.createElement('tr');
    
    // Prepare content for form field id cell.
    var form_field_id_cell_html =
        'Set &nbsp;<select id="submit_form_' + action + '_field_' + field_number + '_form_field_id" name="submit_form_' + action + '_field_' + field_number + '_form_field_id">\n\
            <option value=""></option>';

    // Assume that the selected field type is "text box" until we find out otherwise.
    // We use this to determine if a text box or text area should be displayed for the value field.
    var field_type = 'text box';

    var length = submit_form_custom_form_fields.length;
    
    // Loop through all custom form fields in order to prepare options for pick list.
    for (var index = 0; index < length; index++) {
        var status = '';
        
        // If this option should be selected by default, then select it.
        if (form_field_id == submit_form_custom_form_fields[index]['id']) {
            status = ' selected="selected"';

            field_type = submit_form_custom_form_fields[index]['type'];
        }
        
        form_field_id_cell_html += '<option value="' + submit_form_custom_form_fields[index]['id'] + '"' + status + '>' + prepare_content_for_html(submit_form_custom_form_fields[index]['name']) + '</option>';
    }
    
    form_field_id_cell_html += '</select>';
    
    // Insert content into form field id cell.
    var td_1 = document.createElement('td');
    td_1.innerHTML = form_field_id_cell_html;
    
    // Prepare content for value cell.
    var td_2 = document.createElement('td');

    if (field_type != 'text area') {
        td_2.innerHTML = 'to &nbsp;<input id="submit_form_' + action + '_field_' + field_number + '_value" name="submit_form_' + action + '_field_' + field_number + '_value" type="text" value="' + prepare_content_for_html(value) + '" style="width: 300px" />';
    } else {
        td_2.innerHTML = 'to &nbsp;<textarea id="submit_form_' + action + '_field_' + field_number + '_value" name="submit_form_' + action + '_field_' + field_number + '_value" rows="3" style="vertical-align: top; width: 300px">' + prepare_content_for_html(value) + '</textarea>';
    }

    // Add an ID to the value cell so later we can update the value field when the field is changed
    // which might change the field type.
    td_2.id = 'submit_form_' + action + '_field_' + field_number + '_value_cell';
    
    // Prepare content for delete cell.
    var td_3 = document.createElement('td');
    td_3.innerHTML = '<a href="javascript:void(0)" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" class="button_small">x</a>';
    
    tr.appendChild(td_1);
    tr.appendChild(td_2);
    tr.appendChild(td_3);
    
    tbody.appendChild(tr);

    // Update value field based on the field type when the selected field is changed.
    $('#submit_form_' + action + '_field_' + field_number + '_form_field_id').change(function() {
        var selected_form_field_id = $(this).val();

        // Assume that the selected field type is "text box" until we find out otherwise.
        // We use this to determine if a text box or text area should be displayed for the value field.
        var field_type = 'text box';

        var length = submit_form_custom_form_fields.length;

        // Loop through the custom form fields in order to determine the type for the selected field.
        for (var index = 0; index < length; index++) {
            // If this field is the selected field, then remember field type and break out of loop.
            if (submit_form_custom_form_fields[index]['id'] == selected_form_field_id) {
                field_type = submit_form_custom_form_fields[index]['type'];
                break;
            }
        }

        // Store the value so we don't lose it when we might change the value field type below.
        var previous_value = $('#submit_form_' + action + '_field_' + field_number + '_value').val();

        if (field_type != 'text area') {
            $('#submit_form_' + action + '_field_' + field_number + '_value_cell').html('to &nbsp;<input id="submit_form_' + action + '_field_' + field_number + '_value" name="submit_form_' + action + '_field_' + field_number + '_value" type="text" value="' + prepare_content_for_html(previous_value) + '" style="width: 300px" />');
        } else {
            $('#submit_form_' + action + '_field_' + field_number + '_value_cell').html('to &nbsp;<textarea id="submit_form_' + action + '_field_' + field_number + '_value" name="submit_form_' + action + '_field_' + field_number + '_value" rows="3" style="vertical-align: top; width: 300px">' + prepare_content_for_html(previous_value) + '</textarea>');
        }
    });
    
    // Update number of fields.
    last_submit_form_field_number[action]++;
    document.getElementById('last_submit_form_' + action + '_field_number').value = last_submit_form_field_number[action];
}

function init_product_submit_form_update_where(field) {
    var reference_code_selected = '';

    if (field == 'reference_code') {
        reference_code_selected = ' selected="selected"';
    }    

    var options =
        '<option value=""></option>\
        <optgroup label="System Fields">\
            <option value="reference_code"' + reference_code_selected + '>Reference Code</option>\
        </optgroup>\
        <optgroup label="Form Fields">';

    var length = submit_form_custom_form_fields.length;
    
    // Loop through all custom form fields in order to prepare options for pick list.
    for (var index = 0; index < length; index++) {
        var selected = '';
        
        // If this option should be selected by default, then select it.
        if (submit_form_custom_form_fields[index]['id'] == field) {
            selected = ' selected="selected"';
        }
        
        options += '<option value="' + submit_form_custom_form_fields[index]['id'] + '"' + selected + '>' + h(submit_form_custom_form_fields[index]['name']) + '</option>';
    }

    options += '</optgroup>';

    $('#submit_form_update_where_field').html(options);
}

function init_page_designer(properties)
{
    var initial_panels = properties.initial_panels;
    var preview_panel_width = properties.preview_panel_width;
    var code_panel_width = properties.code_panel_width;
    var tool_panel_width = properties.tool_panel_width;
    var starting_item_type = properties.starting_item_type;
    var starting_item_id = properties.starting_item_id;
    var cursor_positions = properties.cursor_positions;
    var query = properties.query;

    var panels = [];

    var url = '';

    var style_id = '';

    var page;

    var code_type = '';
    var code_id = '';

    var content_changed = false;

    var editor;

    var min_panel_width = 100;

    // Figure out the total handle width as it is set in the CSS.
    // This includes the content width, padding, border, and etc.
    var handle = $('<div class="handle" style="display: none"></div>').appendTo('body');
    var handle_width = handle.outerWidth();
    handle.remove();

    var total_available_width = $('.panels').width();
    var total_available_panel_width = total_available_width - (handle_width * 2);

    // If this is the first time that the page designer is loading for this user's session,
    // then set default panel widths, and set the old window width and height
    // to the current width and height.
    if (!preview_panel_width) {
        tool_panel_width = Math.round(.1 * total_available_panel_width);
        code_panel_width = Math.round(.45 * total_available_panel_width);
        preview_panel_width = total_available_panel_width - code_panel_width - tool_panel_width;

        var old_window_width = $('.panels').width();
        var old_window_height = $('.panels').height();

    // Otherwise the user has loaded the page designer previously in the session,
    // so we just used the saved widths that were passed into this function
    // and set up above.  Also, we set the older window width to the total
    // of the saved values from the last use of the page designer.
    // We set the height to 0 just so it will be updated below.
    } else {
        var old_window_width = preview_panel_width + tool_panel_width + code_panel_width + (handle_width * 2);
        var old_window_height = 0;
    }

    var x_position = 0;

    // Add the initial panels.
    $.each(initial_panels, function(index, panel) {
        panel.x_position = x_position;

        switch (panel.type) {
            case 'preview':
                panel.width = preview_panel_width;
                break;
                
            case 'code':
                panel.width = code_panel_width;
                break;

            case 'tool':
                panel.width = tool_panel_width;
                break;
        }

        add_panel(panel);

        x_position += (panel.width + handle_width);
    });

    $('.panel_width').html(preview_panel_width + 'px');

    // When the user loads the page designer or resizes the window,
    // then update the panel sizes.  We run this function on load,
    // because there might be saved panels widths from a previous use
    // in the session, and the window might not be the same size
    // as the previous time, so we might need to update the panel sizes.
    $(window).on('load resize', function() {
        var new_window_width = $('.panels').width();

        // If the window width changed, then resize and move panels.
        if (new_window_width != old_window_width) {
            // If the window is bigger now, then expand center panel.
            if (new_window_width > old_window_width) {
                var left_panel_width = $('.left').width();
                var right_panel_width = $('.right').width();
                var center_panel_width = new_window_width - left_panel_width - right_panel_width - (handle_width * 2);

            // Otherwise the window is smaller now, so decrease right,
            // center, and then left, if necessary, in that order.
            } else {
                var decrease_width = old_window_width - new_window_width;

                var left_panel_width = $('.left').width();
                var center_panel_width = $('.center').width();
                var right_panel_width = $('.right').width();

                // If the right panel has not reached the minimum size, then decrease it.
                if (right_panel_width > min_panel_width) {
                    // If we can decrease the whole amount, then do that
                    if ((right_panel_width - decrease_width) >= min_panel_width) {
                        right_panel_width -= decrease_width;

                        // Remember that we don't need to decrease any other panels.
                        decrease_width = 0;

                    // Otherwise just decrease this panel to the minimum.
                    } else {
                        var decrease_amount = right_panel_width - min_panel_width;

                        right_panel_width -= decrease_amount;

                        // Update decrease width so we know how much more we will
                        // need to decrease from other panels.
                        decrease_width -= decrease_amount;
                    }
                }

                // If there is still width to decrease and the center panel
                // has not reached the minimum size, then decrease it.
                if ((decrease_width > 0) && (center_panel_width > min_panel_width)) {
                    // If we can decrease the whole amount, then do that
                    if ((center_panel_width - decrease_width) >= min_panel_width) {
                        center_panel_width -= decrease_width;

                        // Remember that we don't need to decrease any other panels.
                        decrease_width = 0;

                    // Otherwise just decrease this panel to the minimum.
                    } else {
                        var decrease_amount = center_panel_width - min_panel_width;

                        center_panel_width -= decrease_amount;

                        // Update decrease width so we know how much more we will
                        // need to decrease from other panels.
                        decrease_width -= decrease_amount;
                    }
                }
                
                // If there is still width to decrease and the left panel
                // has not reached the minimum size, then decrease it.
                if ((decrease_width > 0) && (left_panel_width > min_panel_width)) {
                    // If we can decrease the whole amount, then do that
                    if ((left_panel_width - decrease_width) >= min_panel_width) {
                        left_panel_width -= decrease_width;

                        // Remember that we don't need to decrease any other panels.
                        decrease_width = 0;

                    // Otherwise just decrease this panel to the minimum.
                    } else {
                        var decrease_amount = left_panel_width - min_panel_width;

                        left_panel_width -= decrease_amount;

                        // Update decrease width so we know how much more we will
                        // need to decrease from other panels.
                        decrease_width -= decrease_amount;
                    }

                    $('.panel_width').html(left_panel_width + 'px');
                }
            }

            var x_position = 0;

            $('.left').css({
                left: x_position + 'px',
                width: left_panel_width + 'px'
            });

            x_position += left_panel_width;

            $('#handle_0').css({
                left: x_position + 'px'
            });

            x_position += handle_width;

            $('.center').css({
                left: x_position + 'px',
                width: center_panel_width + 'px'
            });

            x_position += center_panel_width;

            $('#handle_1').css({
                left: x_position + 'px'
            });

            x_position += handle_width;

            $('.right').css({
                left: x_position + 'px',
                width: right_panel_width + 'px'
            });

            old_window_width = new_window_width;
        }

        var new_window_height = $('.panels').height();

        // If the window height changed, then resize scrollable areas in panels
        if (new_window_height != old_window_height) {
            var iframe_height = Math.round($('.left').height() - $('.left .header').outerHeight());

            $('.left iframe').css({
                height: iframe_height + 'px'
            });

            var editor_height = Math.round($('.center').height() - $('.center .header').outerHeight());

            $('.center .editor_container').css({
                height: editor_height + 'px'
            });

            old_window_height = new_window_height;
        }
    });

    function add_panel(properties) {
        var type = properties.type;
        var x_position = properties.x_position;
        var width = properties.width;

        panels.push(properties);

        var index = panels.length - 1;

        var extra_class = '';

        if (type == 'preview') {
            extra_class = ' preview';
        } else if (type == 'code') {
            extra_class = ' code';
        } else if (type == 'tool') {
            extra_class = ' tool';
        }

        switch (type) {
            case 'preview':
                var position_class = ' left';
                break;

            case 'code':
                var position_class = ' center';
                break;

            case 'tool':
                var position_class = ' right';
                break;
        }

        $('.panels').append('<div id="panel_' + index + '" class="panel' + extra_class + position_class + '"></div>');

        $('#panel_' + index).css({
            left: x_position + 'px',
            width: width + 'px'
        });

        x_position += width;

        switch (type) {
            case 'preview':
                $('#panel_' + index).html('\
                    <div class="header">\
                        <img src="images/device_desktop.png" class="device_desktop">\
                        <img src="images/device_tablet_landscape.png" class="device_tablet_landscape">\
                        <img src="images/device_tablet_portrait.png" class="device_tablet_portrait">\
                        <img src="images/device_phone_landscape.png" class="device_phone_landscape">\
                        <img src="images/device_phone_portrait.png" class="device_phone_portrait"><img src="images/device_phone_small.png" class="device_phone_small">\
                        <div class="panel_width">0</div>\
                    </div>\
                    <iframe id="preview" src="' + h(properties.url) + '" frameBorder="0"></iframe>');

                var iframe_height = Math.round($('.preview').height() - $('.preview .header').outerHeight());

                $('.preview iframe').css({
                    height: iframe_height + 'px'
                });

                $('.device_desktop').click(function() {
                    resize_preview_panel(1200);
                });

                $('.device_tablet_portrait').click(function() {
                    resize_preview_panel(768);
                });

                $('.device_tablet_landscape').click(function() {               
                    resize_preview_panel(1024);
                });

                $('.device_phone_portrait').click(function() {
                    resize_preview_panel(320);
                });

                $('.device_phone_landscape').click(function() {
                    resize_preview_panel(600);
                });

                $('.device_phone_small').click(function() {
                    resize_preview_panel(240);
                });

                var first_load = true;

                // When a url is loaded in the preview iframe, then do various things.
                $('#preview').load(function() {
                    // If the iframe contains a page at this site and not
                    // some other external site, then we have access to it,
                    // so continue.
                    if (check_iframe_access(this) == true) {
                        var preview_iframe = $('#preview');

                        page = preview_iframe[0].contentWindow.software_page;

                        var preview_path = preview_iframe[0].contentWindow.location.pathname;

                        // Get the length of software directory path (e.g. /livesite/)
                        // so that we can check if preview panel path is a backend path.
                        var backend_path_length = path.length + software_directory.length + 1;

                        // If page info could not be found in the source of the page,
                        // and a backend screen was not loaded in the preview panel,
                        // then try to figure out the page from the preview panel path.
                        if (
                            (page === undefined)
                            && (preview_path.substring(0, backend_path_length) != (path + software_directory + '/'))
                        ) {
                            // Get the item name without the path on the front,
                            // so we can look for a page with that name.
                            page = {name: preview_path.substring(path.length)};
                        }

                        var toolbar_iframe = preview_iframe.contents().find('#software_toolbar');

                        // If the toolbar iframe was found in the preview window,
                        // then that means the visitor is previewing a front-end page,
                        // so continue.
                        if (toolbar_iframe.length) {
                            // If this is the first page that is being loaded in the iframe,
                            // for the intial page designer load, and the toolbar is visible,
                            // then hide the toolbar.
                            if (first_load && toolbar_iframe.is(':visible')) {
                                preview_iframe.contents().find('#software_fullscreen_toggle').trigger('click');
                            }

                            var page_designer_button = toolbar_iframe.contents().find('.page_designer_button');

                            page_designer_button.removeClass('closed');
                            page_designer_button.addClass('open');
                            page_designer_button.attr('title', 'Close Page Designer (Ctrl+G | \u2318+G)');

                            page_designer_button.click(function(event) {
                                event.preventDefault();
                                close();
                            });
                        }

                        // If a front-end page appears in the preview panel,
                        // then get info about page and then update tools panel.
                        if (page) {
                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify({
                                    action: 'get_page',
                                    page: page,
                                }),
                                type: 'POST',
                                success: function(response) {
                                    page = response.page;

                                    var page_link = $('.tool .page');

                                    page_link.empty();

                                    if (page) {
                                        page_link.html('<a title="Click to reload this page." href="javascript:void(0)">' + h(page.name) + '</a>');

                                        var location = preview_iframe.contents().get(0).location;

                                        url = location.pathname + location.search;

                                        // Remove any previous click events.
                                        page_link.unbind('click');

                                        // Update preview iframe with url when page link is clicked.
                                        page_link.click(function() {
                                            preview_iframe.attr('src', url);
                                        });

                                    } else {
                                        page_link.html('<a style="color: #aaa" href="javascript:void(0)"><em>no page</em></a>');

                                        // Remove any previous click events.
                                        page_link.unbind('click');
                                    }

                                    var layout_link = $('.tool .layout');

                                    layout_link.empty();
                                    layout_link.unbind('click');

                                    var style = preview_iframe[0].contentWindow.software_style;

                                    var layout_type = '';

                                    if (style && style.layout_type) {
                                        layout_type = style.layout_type;
                                    } else if (page) {
                                        layout_type = page.layout_type;
                                    }

                                    // If this page supports a custom layout,
                                    // and it has a custom layout, then show layout link.
                                    if (
                                        page
                                        && check_if_page_type_supports_layout(page.type)
                                        && (layout_type == 'custom')
                                    ) {
                                        layout_link.html('<a class="layout" href="#" title="Click to edit the custom layout.">Layout</a>');

                                        // Store the page id so that if a different URL is loaded in the preview panel,
                                        // that might not have a page (e.g. backend screen), then it won't break layout click event.
                                        var page_id = page.id;

                                        // Open the layout in the code panel when the layout link is clicked.
                                        layout_link.click(function() {
                                            open_item({type: 'layout', id: page_id});
                                        });
                                    }

                                    if (style) {
                                        var new_style_id = style.id;
                                    }

                                    // If this is the first time that the page designer is loading,
                                    // and an item type was passed in the query string,
                                    // then load that item.
                                    if ((code_type == '') && (starting_item_type != '')) {
                                        open_item({type: starting_item_type, id: starting_item_id});

                                    // Otherwise if this is the first time the page designer is loading or there
                                    // is a style in the code panel and there are not unsaved changes,
                                    // and if the style for the page in the preview iframe is different
                                    // from the last time the preview iframe was loaded,
                                    // then open the style in the code panel.
                                    } else if (
                                        (
                                            (code_type == '')
                                            || (code_type == 'style')
                                        )
                                        && (content_changed == false)
                                        && (style)
                                        && (new_style_id != style_id)
                                    ) {
                                        open_item({type: 'style', id: style.id});

                                    // Otherwise if there is a layout in the code panel,
                                    // and there are no unsaved changes to that layout,
                                    // and the preview page has a custom layout,
                                    // and the new layout is different from the layout
                                    // that is already open, then open the layout in the code panel.
                                    } else if (
                                        (code_type == 'layout')
                                        && (content_changed == false)
                                        && page
                                        && check_if_page_type_supports_layout(page.type)
                                        && (layout_type == 'custom')
                                        && (page.id != code_id)
                                    ) {
                                        open_item({type: 'layout', id: page.id});

                                    // Otherwise, no item was opened, so we need to update the URL in the address bar,
                                    // so that if the user refreshes the page, then the correct URL will be opened,
                                    // because the open_item() function did not do it for us.
                                    } else {
                                        history.replaceState({}, null,
                                            path + software_directory + '/page_designer.php?url=' + encodeURIComponent(url) + '&type=' + code_type + '&id=' + code_id);
                                    }

                                    var style_link = $('.tool .style');

                                    style_link.empty();

                                    if (style) {
                                        style_link.html('<a title="Set in Page Properties." class="page_style" href="javascript:void(0)">' + h(style.name) + '</a>');

                                        // Remove any previous click events.
                                        style_link.unbind('click');

                                        // Open the style in the code panel when the style link is clicked.
                                        style_link.click(function() {
                                            open_item({type: 'style', id: style.id});
                                        });

                                    } else {
                                        style_link.html('<a style="color: #aaa" class="page_style" href="javascript:void(0)"><em>no page style</em></a>');

                                        // Remove any previous click events.
                                        style_link.unbind('click');
                                    }

                                    var theme_link = $('.tool .theme');

                                    theme_link.empty();

                                    var theme = preview_iframe[0].contentWindow.software_theme;

                                    // If a theme was outputted for <stylesheet></stylesheet> tags,
                                    // then add theme link to tool panel.
                                    if (theme) {
                                        theme_link.html('<a title="&lt;stylesheet&gt;&lt;/stylesheet&gt;" href="javascript:void(0)">' + h(theme.name) + '</a>');

                                        // Remove any previous click events.
                                        theme_link.unbind('click');

                                        // Open the theme in the code panel when the theme link is clicked.
                                        theme_link.click(function() {
                                            open_item({type: 'css', id: theme.id});
                                        });

                                    } else {
                                        theme_link.html('<a style="color: #aaa" title="Include &lt;stylesheet&gt;&lt;/stylesheet&gt; in the &lt;head&gt; area." href="#"><em>no theme</em></a>');

                                        // Remove any previous click events.
                                        theme_link.unbind('click');
                                    }

                                    $('.related_items').empty();

                                    if (style) {
                                        // Get related items by looking at the style content.
                                        $.ajax({
                                            contentType: 'application/json',
                                            url: 'api.php',
                                            data: JSON.stringify({
                                                action: 'get_items_in_style',
                                                style_id: style.id
                                            }),
                                            type: 'POST',
                                            success: function(response) {
                                                design_files = response.design_files;
                                                designer_regions = response.designer_regions;
                                                dynamic_regions = response.dynamic_regions;
                                                system_regions = response.system_regions;

                                                // If there is at least one result for designer regions, then output it/them.
                                                if (designer_regions.length > 0) {
                                                    //$('.related_items').append('<h3>Designer Regions</h3>');

                                                    $.each(designer_regions, function(index, designer_region) {
                                                        var related_item = $('<div><a class="designer_region" href="#" title="' + h('<cregion>' + designer_region.name +'</cregion>') + '">' + h(designer_region.name) + '</a></div>');

                                                        related_item.click(function() {
                                                            open_item({type: 'designer_region', id: designer_region.id});
                                                        });

                                                        $('.related_items').append(related_item);
                                                    });
                                                }

                                                // If there is at least one result for dynamic regions, then output it/them.
                                                if (dynamic_regions.length > 0) {
                                                    //$('.related_items').append('<h3>Dynamic Regions</h3>');

                                                    $.each(dynamic_regions, function(index, dynamic_region) {
                                                        var related_item = $('<div><a class="designer_region" href="#" title="' + h('<dregion>' + dynamic_region.name + '</dregion>') + '">' + h(dynamic_region.name) + '</a></div>');

                                                        related_item.click(function() {
                                                            open_item({type: 'dynamic_region', id: dynamic_region.id});
                                                        });

                                                        $('.related_items').append(related_item);
                                                    });
                                                }

                                                // If there is at least one result for system regions, then output it/them.
                                                if (system_regions.length > 0) {
                                                    //$('.related_items').append('<h3>System Regions</h3>');

                                                    $.each(system_regions, function(index, system_region) {
                                                        var related_item = $('<div><a class="system_region" href="javascript:void(0)" title="' + h('<system>' + system_region.name + '</system>') + '">' + h(system_region.name) + '</a></div>');

                                                        related_item.click(function() {
                                                            preview_iframe.attr('src', path + encodeURI(system_region.name));
                                                        });

                                                        $('.related_items').append(related_item);
                                                    });
                                                }

                                                // If there is at least one result for design files, then output it/them.
                                                if (design_files.length > 0) {
                                                    var number_of_items = 0;

                                                    $.each(design_files, function(index, design_file) {
                                                        // If this is not a theme, or it is a theme and it is a custom theme,
                                                        // then continue to output result.
                                                        if ((design_file.theme == 0) || (design_file.theme_type == 'custom')) {
                                                            number_of_items++;

                                                            // If this is the first item, then output heading first.
                                                            if (number_of_items == 1) {
                                                                //$('.related_items').append('<h3>Design Files</h3>');
                                                            }

                                                            //if (design_file.type == 'css') {
                                                            //    a_title = 'title="' + h('<link href="{path}' + encodeURI(design_file.name) + '" rel="stylesheet" type="text/css">') + '"';
                                                            //} else {
                                                            //    a_title = 'title="' + h('<script src="{path}' + encodeURI(design_file.name) + '" rel="javascript" type="text/javascript"></script>') + '"';
                                                            //}

                                                            var related_item = $('<div><a class="design_file" href="#">' + h(design_file.name) + '</a></div>');

                                                            related_item.click(function() {
                                                                open_item({type: design_file.type, id: design_file.id});
                                                            });

                                                            $('.related_items').append(related_item);
                                                        }
                                                    });
                                                } 
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }

                    first_load = false;
                });

                break;

            case 'tool':
                $('#panel_' + index).html('\
                    <div class="header">\
                        <div class="buttons">\
                            <div style="margin-top: 9px;">Page&nbsp;Designer&nbsp;&nbsp;<a href="javascript:void(0)" class="page_designer_button open" title="Close Page Designer (Ctrl+G | &#8984;+G)">&lt;/&gt;</a></div>\
                        </div>\
                    </div>\
                    <div class="content">\
                        <div class="page"></div>\
                        <div class="layout"></div>\
                        <div class="style"></div>\
                        <div class="theme"></div>\
                        <div class="related_items"></div>\
                        <form class="search_form">\
                            <div class="search"><span class="simple"><input type="text" id="query" class="query" name="query" value=""> <input type="submit" value="" class="submit_small_secondary submit"></span></div>\
                        </form>\
                    </div>\
                ');

                $('.page_designer_button.open').click(function(event) {
                    event.preventDefault();
                    close();
                });

                $('.search_form').submit(function(event) {
                    event.preventDefault();

                    new_query = $('.query').val();

                    if (new_query == '') {
                        return false;
                    }

                    query = new_query;

                    // If there is not already a clear button, then add one.
                    if (!$('.clear').length) {
                        var clear = $('<input type="button" value="" class="clear submit_small_secondary">');

                        clear.click(function(event) {
                            query = '';
                            $('.query').val('');
                            $('.query').focus();
                            $('.search_results').remove();
                            clear.remove();
                        });

                        $('.search_form span.simple').append(clear);
                    }

                    var styles;
                    var design_files;
                    var designer_regions;
                    var dynamic_regions;

                    // Send multiple AJAX requests in order to search multiple types of items.
                    $.when(
                        $.ajax({
                            contentType: 'application/json',
                            url: 'api.php',
                            data: JSON.stringify({
                                action: 'get_styles',
                                type: 'custom',
                                search: query
                            }),
                            type: 'POST',
                            success: function(response) {
                                styles = response.styles;
                            }
                        }),

                        $.ajax({
                            contentType: 'application/json',
                            url: 'api.php',
                            data: JSON.stringify({
                                action: 'get_design_files',
                                types: ['css', 'js'],
                                theme_type: 'custom',
                                search: query
                            }),
                            type: 'POST',
                            success: function(response) {
                                design_files = response.design_files;
                            }
                        }),

                        $.ajax({
                            contentType: 'application/json',
                            url: 'api.php',
                            data: JSON.stringify({
                                action: 'get_designer_regions',
                                search: query
                            }),
                            type: 'POST',
                            success: function(response) {
                                designer_regions = response.designer_regions;
                            }
                        }),

                        $.ajax({
                            contentType: 'application/json',
                            url: 'api.php',
                            data: JSON.stringify({
                                action: 'get_dynamic_regions',
                                search: query
                            }),
                            type: 'POST',
                            success: function(response) {
                                dynamic_regions = response.dynamic_regions;
                            }
                        })

                    // Once all of the AJAX requests are complete, then output search results.
                    ).then(function() {
                        $('.search_results').remove();
                        $('.search_form').after('<div class="search_results"></div>');

                        // If there is at least one result for styles, then output it/them.
                        if (styles.length > 0) {
                            $('.search_results').append('<h3>Page Styles</h3>');

                            $.each(styles, function(index, style) {
                                var search_result = $('<div><a class="page_style" href="#">' + h(style.name) + '</a></div>');

                                search_result.click(function() {
                                    open_item({type: 'style', id: style.id});
                                });

                                $('.search_results').append(search_result);
                            });
                        }

                        // If there is at least one result for designer regions, then output it/them.
                        if (designer_regions.length > 0) {
                            $('.search_results').append('<h3>Designer Regions</h3>');

                            $.each(designer_regions, function(index, designer_region) {
                                var search_result = $('<div><a class="designer_region" href="#" title="' + h('<cregion>' + designer_region.name + '</cregion>') + '">' + h(designer_region.name) + '</a></div>');

                                search_result.click(function() {
                                    open_item({type: 'designer_region', id: designer_region.id});
                                });

                                $('.search_results').append(search_result);
                            });
                        }

                        // If there is at least one result for dynamic regions, then output it/them.
                        if (dynamic_regions.length > 0) {
                            $('.search_results').append('<h3>Dynamic Regions</h3>');

                            $.each(dynamic_regions, function(index, dynamic_region) {
                                var search_result = $('<div><a class="designer_region" href="#" title="' + h('<dregion>' + dynamic_region.name + '</dregion>') + '">' + h(dynamic_region.name) + '</a></div>');

                                search_result.click(function() {
                                    open_item({type: 'dynamic_region', id: dynamic_region.id});
                                });

                                $('.search_results').append(search_result);
                            });
                        }

                        // If there is at least one result for design files, then output it/them.
                        if (design_files.length > 0) {
                            $('.search_results').append('<h3>Design Files</h3>');

                            $.each(design_files, function(index, design_file) {

                                if (design_file.type == 'css') {
                                    a_title = 'title="' + h('<link href="{path}' + encodeURI(design_file.name) + '" rel="stylesheet" type="text/css">') + '"';
                                } else {
                                    a_title = 'title="' + h('<script src="{path}' + encodeURI(design_file.name) + '" rel="javascript" type="text/javascript"></script>') + '"';
                                }

                                var search_result = $('<div><a class="design_file" href="#" ' + a_title + '>' + h(design_file.name) + '</a></div>');

                                search_result.click(function() {
                                    open_item({type: design_file.type, id: design_file.id});
                                });

                                $('.search_results').append(search_result);
                            });
                        }
                    });
                });

                // If there is a search query from previous page designer session,
                // then prefill query field and search for it.
                if (query != '') {
                    $('.query').val(query);
                    $('.search_form').trigger('submit');
                }

                break;
        }


        if (type != 'tool') {
            var handle = $('<div id="handle_' + index + '" class="handle"></div>');

            handle.css({
                left: x_position + 'px'
            });

            handle.mousedown(function(e) {
                e.preventDefault();

                // Add a cover to any iframes, so they do not interfeering with dragging panels.
                var iframe_cover = $('<div class="iframe_cover"></div>').css({
                    position: 'absolute',
                    top: '0',
                    left: '0',
                    right: '0',
                    bottom: '0',
                    'z-index': '2147483647'
                });

                $('iframe').after(iframe_cover);

                var handle = $(this);

                var left_panel = handle.prev();
                var right_panel = handle.next();

                var old_drag_position = e.pageX;
                
                $(document).mousemove(function(e) {
                    e.preventDefault();

                    var new_drag_position = e.pageX;

                    // If the user has dragged the handle to the left,
                    // then resize the panels for that direction.
                    if (new_drag_position < old_drag_position) {
                        var drag_distance = old_drag_position - new_drag_position;

                        var result = move_handle({
                            index: index,
                            direction: 'left',
                            distance: drag_distance
                        });

                    // Otherwise the user has draged the handle to the right,
                    // so resize the panels for that direction.
                    } else {
                        var drag_distance = new_drag_position - old_drag_position;

                        var result = move_handle({
                            index: index,
                            direction: 'right',
                            distance: drag_distance
                        });
                    }

                    if (result == true) {
                        old_drag_position = new_drag_position;
                    }
                })

                // Remove the mousemove event once the user releases the mouse button.
                $(document).one('mouseup', function() {
                    $(document).unbind('mousemove');

                    // Refresh CodeMirror, because it has a bug where the cursor position
                    // will be incorrect after the panel resize.
                    editor.refresh();

                    $('.iframe_cover').remove();
                });
            });

            $('.panels').append(handle);
        }
    }

    function move_handle(properties) {
        var index = properties.index;
        var direction = properties.direction;
        var distance = properties.distance;

        var left_panel = $('#panel_' + index);
        var handle = $('#handle_' + index);
        var right_panel = $('#panel_' + (index + 1));

        switch (direction) {
            case 'left':
                if (left_panel.width() > min_panel_width) {
                    if (distance > (left_panel.width() - min_panel_width)) {
                        distance = left_panel.width() - min_panel_width;
                    }

                    left_panel.css('width', '-=' + distance + 'px');

                    handle.css('left', '-=' + distance + 'px');

                    right_panel.css({
                        left: '-=' + distance + 'px',
                        width: '+=' + distance + 'px'
                    });

                    // If the user is dragging the left handle,
                    // then update the display of the width of the left panel.
                    if (index == 0) {
                        $('.panel_width').html(left_panel.width() + 'px');

                        // set attr in header so header images can be styled with css
                        switch (left_panel.width()) {
                            case 1200 : $('.preview div.header').attr('device','desktop'); break;
                            case 1024 : $('.preview div.header').attr('device','tablet_landscape'); break;
                            case 768  : $('.preview div.header').attr('device','tablet_portrait'); break;
                            case 600  : $('.preview div.header').attr('device','phone_landscape'); break;
                            case 320  : $('.preview div.header').attr('device','phone_portrait'); break;
                            case 240  : $('.preview div.header').attr('device','phone_small'); break;
                            default   : $('.preview div.header').attr('device','other');
                        }
                    }

                    return true;

                } else {
                    return false;
                }

                break;

            case 'right':
                if (right_panel.width() > min_panel_width) {
                    if (distance > (right_panel.width() - min_panel_width)) {
                        distance = right_panel.width() - min_panel_width;
                    }

                    left_panel.css('width', '+=' + distance + 'px');

                    handle.css('left', '+=' + distance + 'px');

                    right_panel.css({
                        left: '+=' + distance + 'px',
                        width: '-=' + distance + 'px'
                    });

                    // If the user is dragging the left handle,
                    // then update the display of the width of the left panel.
                    if (index == 0) {
                        $('.panel_width').html(left_panel.width() + 'px');

                        // set attr in header so header images can be styled with css
                        switch (left_panel.width()) {
                            case 1200 : $('.preview div.header').attr('device','desktop'); break;
                            case 1024 : $('.preview div.header').attr('device','tablet_landscape'); break;
                            case 768  : $('.preview div.header').attr('device','tablet_portrait'); break;
                            case 600  : $('.preview div.header').attr('device','phone_landscape'); break;
                            case 320  : $('.preview div.header').attr('device','phone_portrait'); break;
                            case 240  : $('.preview div.header').attr('device','phone_small'); break;
                            default   : $('.preview div.header').attr('device','other');
                        }
                    }

                    return true;

                } else {
                    return false;
                }

                break;
        }
    }

    function resize_preview_panel(width) {
        current_width = $('.preview').width();

        // If the preview panel does not happen to already
        // be that size, then resize it.
        if (current_width != width) {
            // If the preview panel width needs to be decreased,
            // then do that and increase size of the panel on the right.
            if (width < current_width) {
                var distance = current_width - width;

                move_handle({
                    index: 0,
                    direction: 'left',
                    distance: distance
                });

            // Otherwise increase the preview panel width.
            } else {
                var distance = width - current_width;

                move_handle({
                    index: 0,
                    direction: 'right',
                    distance: distance
                });
            }

            // Refresh CodeMirror, because it has a bug where the cursor position
            // will be incorrect after the panel resize.
            editor.refresh();
        }
    }

    // If there is an item open currently, then remember the cursor position
    // before we close the item, or browse away from the page designer.
    function save_cursor_position() {
        if (code_type) {
            var cursor_position = editor.getCursor();

            cursor_positions[code_type + '_' + code_id] = {
                line: cursor_position.line + 1,
                character: cursor_position.ch + 1,
                scroll: $('.editor_container').scrollTop()
            }
        }
    }

    function open_item(properties) {
        var type = properties.type;
        var id = properties.id;

        // If the content of the existing item has not been saved or cancelled yet,
        // then show confirmation to user and do not open new item.
        if (content_changed == true) {
            alert('Sorry, please save or cancel your changes first.');
            return false;
        }

        // Save the cursor position for the old item before we close it.
        save_cursor_position();

        code_type = type;
        code_id = id;

        // Update the URL in the address bar, so that if the user refreshes the browser
        // then the correct item will be opened.
        history.replaceState({}, null,
            path + software_directory + '/page_designer.php?url=' + encodeURIComponent(url) + '&type=' + code_type + '&id=' + code_id);

        // If a layout is being opened in the code panel, then add class to code panel,
        // so a different color header can be shown.
        if (type == 'layout') {
            $('.code').addClass('layout');
        } else {
            $('.code').removeClass('layout');
        }

        switch (type) {
            case 'style':
                var output_heading = 'Page Style';
                var mode = 'htmlmixed';
                style_id = id;
                break;

            case 'layout':
                var output_heading = 'Layout';
                var mode = 'php';
                break;

            case 'css':
                var output_heading = 'Design File';
                var mode = 'css';
                break;

            case 'js':
                var output_heading = 'Design File';
                var mode = 'javascript';
                break;

            case 'designer_region':
                var output_heading = 'Designer Region';
                var mode = 'htmlmixed';
                break;

            case 'dynamic_region':
                var output_heading = 'Dynamic Region';
                var mode = 'php';
                break;
        }

        $('.center').html('\
            <div class="header">\
                <div class="buttons">\
                    <input type="button" value="Save" class="save submit-primary disabled" title="Save (Ctrl+S | &#8984;+S)">&nbsp;&nbsp;\
                    <input type="button" value="Cancel" class="cancel submit-secondary disabled">\
                </div>\
                <span class="name" style="font-size: 125%;"></span>\
                <br /><span class="type" style="font-size: 80%;">' + output_heading + '</span>\
                <div style="clear: both"></div>\
            </div>\
            <div class="editor_container">\
                <textarea id="content" style="display: none"></textarea>\
            </div>');

        var editor_height = Math.round($('.center').height() - $('.center .header').outerHeight());

        $('.editor_container').css({
            overflow: 'auto',
            width: '100%',
            height: editor_height + 'px'
        });

        switch (type) {
            case 'style':
                var data = {
                    action: 'get_style',
                    style: {id: id}
                }

                break;

            case 'layout':
                var data = {
                    action: 'get_layout',
                    layout: {id: id}
                }

                break;

            case 'css':
            case 'js':
                var data = {
                    action: 'get_file',
                    file: {id: id}
                }

                break;

            case 'designer_region':
                var data = {
                    action: 'get_designer_region',
                    designer_region: {id: id}
                }

                break;

            case 'dynamic_region':
                var data = {
                    action: 'get_dynamic_region',
                    dynamic_region: {id: id}
                }

                break;
        }

        $.ajax({
            contentType: 'application/json',
            url: 'api.php',
            data: JSON.stringify(data),
            type: 'POST',
            success: function(response) {
                var readonly = false;

                switch (type) {
                    case 'style':
                        var name = response.style.name;
                        var content = response.style.code;

                        if (response.style.type == 'system') {
                            readonly = true;

                            $('.code .type').html('System Page Style &nbsp;<strong style="font-size: 120%">(read-only)</strong>');
                        }

                        break;

                    case 'layout':
                        var name = response.layout.name;
                        var content = response.layout.content;
                        break;

                    case 'css':
                    case 'js':
                        var name = response.file.name;
                        var content = response.file.content;

                        if (response.file.theme_type == 'system') {
                            readonly = true;

                            $('.code .type').html('System Theme &nbsp;<strong style="font-size: 120%">(read-only)</strong>');
                        }

                        break;

                    case 'designer_region':
                        var name = response.designer_region.name;
                        var content = response.designer_region.content;
                        break;

                    case 'dynamic_region':
                        var name = response.dynamic_region.name;
                        var content = response.dynamic_region.content;
                        break;
                }

                $('.code .name').html(h(name));

                $('#content').val(content);
                
                editor = CodeMirror.fromTextArea(document.getElementById('content'), {
                    mode: mode,
                    lineNumbers: true,
                    indentUnit: 4,
                    theme: 'pastel-on-dark',
                    lineWrapping: false,
                    styleActiveLine: true,
                    readOnly: readonly,
                    viewportMargin: Infinity,
                    matchTags: { bothTags: true },
                    autoCloseTags: true,
                    lint: true,
                    gutters: ["CodeMirror-lint-markers"],
                    autoCloseBrackets: true
                });

                editor.focus();

                // Get previous cursor position for this item.
                var cursor_position = cursor_positions[code_type + '_' + code_id];

                // If a cursor position was found, then set focus to that area.
                if (cursor_position) {
                    // Tell CodeMirror to set focus to previous line and character.
                    editor.setCursor({
                        line: cursor_position.line - 1,
                        ch: cursor_position.character - 1
                    });

                    // Wait a short period of time for CodeMirror to complete setCursor
                    // above and then scroll to the previous scroll position.
                    // This is necessary because CodeMirror does not scroll the focus
                    // line to a good position.  It leaves it at the bottom of the editor.
                    setTimeout (function () {
                        $('.editor_container').scrollTop(cursor_position.scroll);
                    }, 50);
                }

                editor.on('change', function () {
                    // If this is the first time the editor content has been changed,
                    // then enable save & cancel buttons and actions for buttons.
                    if (content_changed == false) {
                        content_changed = true;

                        // Enable the save & cancel buttons.
                        $('.save').removeClass('disabled');
                        $('.cancel').removeClass('disabled');

                        // When the save button is clicked then save the form.
                        $('.save').click(function() {
                            editor.focus();

                            var new_content = editor.getValue();

                            switch (type) {
                                case 'style':
                                    var data = {
                                        action: 'update_style',
                                        token: software_token,
                                        style: {
                                            id: id,
                                            code: new_content
                                        }
                                    }

                                    break;

                                case 'layout':
                                    var data = {
                                        action: 'update_layout',
                                        token: software_token,
                                        layout: {
                                            id: id,
                                            content: new_content
                                        }
                                    }

                                    break;

                                case 'css':
                                case 'js':
                                    var data = {
                                        action: 'update_file',
                                        token: software_token,
                                        file: {
                                            id: id,
                                            content: new_content
                                        }
                                    }

                                    break;

                                case 'designer_region':
                                    var data = {
                                        action: 'update_designer_region',
                                        token: software_token,
                                        designer_region: {
                                            id: id,
                                            content: new_content
                                        }
                                    }

                                    break;

                                case 'dynamic_region':
                                    var data = {
                                        action: 'update_dynamic_region',
                                        token: software_token,
                                        dynamic_region: {
                                            id: id,
                                            content: new_content
                                        }
                                    }

                                    break;
                            }

                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify(data),
                                type: 'POST',
                                success: function(response) {
                                    content_changed = false;

                                    // Store the new content, so if the cancel button is clicked
                                    // it will restore content from the last save, instead of
                                    // from when the editor was originally created.
                                    content = new_content;

                                    var preview_iframe = $('#preview');

                                    // If the preview iframe is currently on a front-end page
                                    // at this website, then reload the iframe.
                                    // The "|| page" is necessary, because there might be a PHP layout syntax error,
                                    // that prevents the software toolbar from appearing,
                                    // so that forces the page to be reloaded even if there is a PHP error on it.
                                    if (
                                        (check_iframe_access(preview_iframe[0]) == true)
                                        &&
                                        (
                                            (preview_iframe.contents().find('#software_toolbar').length)
                                            || page
                                        )
                                    ) {
                                        document.getElementById('preview').contentWindow.location.reload();
                                    }

                                    // Disable the save & cancel buttons.
                                    $('.save').unbind('click');
                                    $('.save').addClass('disabled');
                                    $('.cancel').unbind('click');
                                    $('.cancel').addClass('disabled');
                                }
                            });
                        });

                        // When the cancel button is clicked then restore original content
                        // and disable save & cancel buttons.
                        $('.cancel').click(function() {
                            // Update editor to contain original content.
                            editor.setValue(content);

                            content_changed = false;

                            // Disable the save & cancel buttons.
                            $('.save').unbind('click');
                            $('.save').addClass('disabled');
                            $('.cancel').unbind('click');
                            $('.cancel').addClass('disabled');                                    
                        });
                    }
                });
            }
        });
    }

    function close() {
        // If the iframe contains a page on this website,
        // (i.e. we have access to knowing what the URL is)
        // then get iframe location.
        if (check_iframe_access(document.getElementById('preview')) == true) {
            var location = $('#preview').contents().get(0).location.href;

        // Otherwise the iframe contains a page from a different website,
        // so we don't have access to knowing the location,
        // so use the last url at this site.
        } else {
            var location = url;
        }

        window.location.href = location;
    }

    // Add keyboard shortcuts.
    $(window).bind('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) {
                case 'd':
                    event.preventDefault();

                    var fullscreen_toggle = $('#preview').contents().find('#software_fullscreen_toggle');

                    if (fullscreen_toggle.length) {
                        fullscreen_toggle.trigger('click');
                    }

                    break;

                case 'e':
                    event.preventDefault();

                    var grid_toggle = $('#preview').contents().find('#grid_toggle');

                    if (grid_toggle.length) {
                        grid_toggle.trigger('click');
                    }

                    break;

                case 's':
                    event.preventDefault();
                    $('.save').trigger('click');
                    break;
            }
        }
    });

    $(window).on('beforeunload', function() {
        save_cursor_position();

        $.ajax({
            contentType: 'application/json',
            url: 'api.php',
            data: JSON.stringify({
                action: 'update_page_designer_properties',
                token: software_token,
                preview_panel_width: $('.preview').width(),
                code_panel_width: $('.code').width(),
                tool_panel_width: $('.tool').width(),
                cursor_positions: cursor_positions,
                query: query
            }),
            type: 'POST',
            async: false
        })

        if (content_changed == true) {
            return 'WARNING: If you leave this page, then your unsaved changes will be lost.';
        }
    });
}

function init_product_attribute_options(options) {
    var current_id = 0;

    $('.options').append(
        '<div class="option_list" style="margin-bottom: 2em"></div>\
        <div><a href="javascript:void(0)" class="add_option button">Add Option</a></div>');

    $('.add_option').click(add_option);

    $.each(options, function(index, option) {
        add_option(option);
    });

    function update_move_buttons() {
        $('.option').each(function() {
            var option = $(this);
            var move_up = $('.move_up', option);
            var move_down = $('.move_down', option);

            move_up.removeClass('disabled');
            move_down.removeClass('disabled');

            if (option.is(':only-child')) {
                move_up.addClass('disabled');
                move_down.addClass('disabled');
            } else if (option.is(':first-child')) {
                move_up.addClass('disabled');
            } else if (option.is(':last-child')) {
                move_down.addClass('disabled');
            }
        });
    }

    function add_option(option) {
        current_id++;

        var id = '';

        if (option.id) {
            id = option.id;
        }

        $('.option_list').append(
            '<div class="option option_' + current_id + '" style="margin-bottom: 1em">\
                <input type="hidden" name="option_id" value="' + id + '" class="option_id">\
                <input type="text" name="option_label" size="50" maxlength="255" class="option_label">\
                &nbsp; <label for="option_no_value_' + current_id + '">"No Thanks" Option: </label><input type="checkbox" id="option_no_value_' + current_id + '" name="option_no_value" value="1" class="checkbox option_no_value"> &nbsp;\
                <a href="javascript:void(0)" class="move move_up button_small" title="Move Up">&#9650;</a>\
                <a href="javascript:void(0)" class="move move_down button_small" title="Move Down">&#9660;</a>\
                <a href="javascript:void(0)" class="remove button_small" title="Remove">x</a>\
            </div>');

        if (typeof option.label !== 'undefined') {
            $('.option_' + current_id + ' .option_label').val(option.label);

            if (option.no_value == 1) {
                $('.option_' + current_id + ' .option_no_value').prop('checked', true);
            }
        }

        // If the add option button was clicked, then set focus to field that was just added.
        if (typeof option.label === 'undefined') {
            $('.option_' + current_id + ' .option_label').focus();
        }

        $('.option_' + current_id + ' .move_up').click(function() {
            var option = $(this).parent();

            option.after(option.prev());

            update_move_buttons();
        });

        $('.option_' + current_id + ' .move_down').click(function() {
            var option = $(this).parent();

            option.before(option.next());

            update_move_buttons();
        });

        $('.option_' + current_id + ' .remove').click(function() {
            $(this).parent().remove();

            update_move_buttons();
        });

        update_move_buttons();
    }

    // Prepare options when form is submitted.
    $('.product_attribute_form').submit(function() {
        // Create an array of options by looping through all of the option fields.

        var options = [];

        $('.option').each(function() {
            var option_id = $('.option_id', this).val();
            var label = $('.option_label', this).val();

            var no_value = '';

            if ($('.option_no_value', this).is(':checked')) {
                no_value = '1';
            }

            options.push({
                id: option_id,
                label: label,
                no_value: no_value});
        });

        // Add a hidden field to the form with a JSON value that contains the options.

        var hidden_field = $('<input type="hidden" name="options">');

        hidden_field.val(JSON.stringify(options));

        $('.options').append(hidden_field);

        return true;
    });
}

function init_product_attributes(properties) {
    var attributes = properties.attributes;
    var selected_attributes = properties.selected_attributes;

    var current_id = 0;

    $('.attributes').append(
        '<div class="attribute_list" style="margin-bottom: 2em"></div>\
        <div><a href="javascript:void(0)" class="add_attribute button">Add Attribute</a></div>');

    $('.add_attribute').click(add_attribute);

    if (selected_attributes) {
        $.each(selected_attributes, function(index, attribute) {
            add_attribute(attribute);
        });
    }

    function add_attribute(attribute) {
        current_id++;

        var id = current_id;

        var output_options = '<option value="">-Select Attribute-</option>';

        $.each(attributes, function(index, attribute) {
            output_options += '<option value="' + attribute.id + '">' + h(attribute.name) + '</option>';
        });

        $('.attribute_list').append(
            '<div class="attribute attribute_' + id + '" style="margin-bottom: 1em">\
                <select name="attribute_id" class="attribute_id">' + output_options + '</select>\
                <a href="javascript:void(0)" class="remove button_small">x</a>\
            </div>');

        // Once the user selects an attribute, then show option pick list.
        $('.attribute_' + id + ' .attribute_id').change(function() {
            $('.attribute_' + id + ' .option_id').remove();

            var selected_attribute_id = $('.attribute_' + id + ' .attribute_id').val();

            if (selected_attribute_id) {
                var output_options = '<option value="">-Select Option-</option>';

                var selected_attribute_index = 0;

                $.each(attributes, function(index, attribute) {
                    if (attribute.id == selected_attribute_id) {
                        selected_attribute_index = index;
                        return false;
                    }
                });

                $.each(attributes[selected_attribute_index].options, function(index, option) {
                    output_options += '<option value="' + option.id + '">' + h(option.label) + '</option>';
                });

                $('.attribute_' + id + ' .attribute_id').after(' <select name="option_id" class="option_id">' + output_options + '</select>');

                // If this attribute is an existing attribute for the product,
                // then select correct option in pick list.
                if (typeof attribute.option_id !== 'undefined') {
                    $('.attribute_' + id + ' .option_id').val(attribute.option_id);
                }
            }
        });

        // If this attribute is an existing attribute for the product,
        // then set value and trigger change event so that option pick list appears.
        if (typeof attribute.attribute_id !== 'undefined') {
            $('.attribute_' + id + ' .attribute_id').val(attribute.attribute_id).change();
        }

        $('.attribute_' + id + ' .remove').click(function() {
            $(this).parent().remove();
        });
    }

    // Once the create/edit product form is submitted,
    // put attributes into a JSON string in a hidden form field.
    $('.product_form').submit(function() {
        // Create an array of attributes by looping through all of the option fields.

        var attributes = [];

        $('.attribute').each(function() {
            var attribute_id = $('.attribute_id', this).val();
            var option_id = $('.option_id', this).val();

            // If an attribute and an option was selected, then add them to array.
            if (attribute_id && option_id) {
                attributes.push({
                    attribute_id: attribute_id,
                    option_id: option_id});
            }
        });

        // If there is at least one attribute, then add a hidden field to the form
        // with a JSON value that contains the attributes.
        if (attributes.length) {
            var hidden_field = $('<input type="hidden" name="attributes">');

            hidden_field.val(JSON.stringify(attributes));

            $('.attributes').append(hidden_field);
        }

        return true;
    });
}

function init_product_groups(properties) {
    var groups = properties.groups;
    var selected_groups = properties.selected_groups;

    var current_id = 0;

    $('.groups').append(
        '<div class="group_list" style="margin-bottom: 2em"></div>\
        <div><a href="javascript:void(0)" class="add_group button">Add Group</a></div>');

    $('.add_group').click(add_group);

    if (selected_groups) {
        $.each(selected_groups, function (index, group) {
            add_group(group);
        });
    }

    function add_group(group) {
        current_id++;

        var id = current_id;

        var output_options = '<option value="">-Select Group-</option>';

        $.each(groups, function (index, group) {
            output_options += '<option value="' + group.id + '">' + h(group.name) + '</option>';
        });

        $('.group_list').append(
            '<div class="group group_' + id + '" style="margin-bottom: 1em">\
                <select name="group_id" class="group_id">' + output_options + '</select>\
                <a href="javascript:void(0)" class="remove button_small">x</a>\
            </div>');



        // If this group is an existing group for the product,
        // then set value and trigger change event so that option pick list appears.
        if (typeof group.group_id !== 'undefined') {
            $('.group_' + id + ' .group_id').val(group.group_id).change();
        }

        $('.group_' + id + ' .remove').click(function () {
            $(this).parent().remove();
        });
    }

    // Once the create/edit product form is submitted,
    // put groups into a JSON string in a hidden form field.
    $('.product_form').submit(function () {
        // Create an array of groups by looping through all of the option fields.

        var groups = [];

        $('.group').each(function () {
            var group_id = $('.group_id', this).val();

            // If an group and an option was selected, then add them to array.
            if (group_id) {
                groups.push({
                    group_id: group_id
                });
            }
        });

        // If there is at least one group, then add a hidden field to the form
        // with a JSON value that contains the groups.
        if (groups.length) {
            var hidden_field = $('<input type="hidden" name="groups">');

            hidden_field.val(JSON.stringify(groups));

            $('.groups').append(hidden_field);
        }

        return true;
    });
}

function init_product_group_attributes(properties) {
    var attributes = properties.attributes;

    $.each(attributes, function(index, attribute) {
        var output_options = '';

        $.each(attribute.options, function(index, option) {
            var output_selected = '';

            if (option.id == attribute.default_option_id) {
                var output_selected = ' selected="selected"';
            }

            output_options += '<option value="' + option.id + '"' + output_selected + '>' + h(option.label) + '</option>';
        });

        $('.attributes tbody').append(
            '<tr class="attribute attribute_' + attribute.id + '" data-attribute-id="' + attribute.id + '">\
                <td>\
                    ' + h(attribute.name) + '\
                </td>\
                <td>\
                    <select class="default_option_id">\
                        <option value=""></option>\
                        ' + output_options + '\
                    </select>\
                </td>\
                <td>\
                    <a href="javascript:void(0)" class="move move_up button_small" title="Move Up">&#9650;</a>\
                    <a href="javascript:void(0)" class="move move_down button_small" title="Move Down">&#9660;</a>\
                </td>\
            </tr>');

        $('.attribute_' + attribute.id + ' .move_up').click(function() {
            var row = $(this).parents('tr:first');

            // If this is not the 2nd row, then move it up.
            // The first row is the heading row, so that is why we
            // never want to move the 2nd row up.
            if ((row.index() + 1) != 2) {
                row.insertBefore(row.prev());
            }

            update_move_buttons();
        });

        $('.attribute_' + attribute.id + ' .move_down').click(function() {
            var row = $(this).parents('tr:first');
            row.insertAfter(row.next());

            update_move_buttons();
        });
    });

    update_move_buttons();

    function update_move_buttons() {
        var number_of_attributes = $('.attribute').length;

        $('.attribute').each(function() {
            var attribute = $(this);
            var move_up = $('.move_up', attribute);
            var move_down = $('.move_down', attribute);

            // Let's remove the disabled classes until we find out if they need to be disabled.
            move_up.removeClass('disabled');
            move_down.removeClass('disabled');

            // If there is only 1 attribute, then disable both buttons.
            if (number_of_attributes == 1) {
                move_up.addClass('disabled');
                move_down.addClass('disabled');

            // Otherwise, if this is the first attribute, then disable the move up button.
            } else if (attribute.index() == 1) {
                move_up.addClass('disabled');

            // Otherwise, if this is the last attribute, then disable the move down button.
            } else if (attribute.is(':last-child')) {
                move_down.addClass('disabled');
            }
        });
    }

    // Prepare attributes when form is submitted.
    $('.product_group_form').submit(function() {
        // Create an array of attributes by looping through all of the attribute.

        var attributes = [];

        $('.attribute').each(function() {
            var attribute = $(this);

            attributes.push({
                id: attribute.attr('data-attribute-id'),
                default_option_id: $('.default_option_id', attribute).val()
            });
        });

        // Add a hidden field to the form with a JSON value that contains the attributes.

        var hidden_field = $('<input type="hidden" name="attributes">');

        hidden_field.val(JSON.stringify(attributes));

        $(this).append(hidden_field);

        return true;
    });
}



function init_product_groups(properties) {
    var groups = properties.groups;
    var selected_groups = properties.selected_groups;

    var current_id = 0;

    $('.groups').append(
        '<div class="group_list" style="margin-bottom: 2em"></div>\
        <div><a href="javascript:void(0)" class="add_group button">Add Group</a></div>');

    $('.add_group').click(add_group);

    if (selected_groups) {
        $.each(selected_groups, function (index, group) {
            add_group(group);
        });
    }

    function add_group(group) {
        current_id++;

        var id = current_id;

        var output_options = '<option value="">-Select Group-</option>';

        $.each(groups, function (index, group) {
            output_options += '<option value="' + group.id + '">' + h(group.name) + '</option>';
        });

        $('.group_list').append(
            '<div class="group group_' + id + '" style="margin-bottom: 1em">\
                <select name="group_id" class="group_id">' + output_options + '</select>\
                <a href="javascript:void(0)" class="remove button_small">x</a>\
            </div>');



        // If this group is an existing group for the product,
        // then set value and trigger change event so that option pick list appears.
        if (typeof group.group_id !== 'undefined') {
            $('.group_' + id + ' .group_id').val(group.group_id).change();
        }

        $('.group_' + id + ' .remove').click(function () {
            $(this).parent().remove();
        });
    }

    // Once the create/edit product form is submitted,
    // put groups into a JSON string in a hidden form field.
    $('.product_form').submit(function () {
        // Create an array of groups by looping through all of the option fields.

        var groups = [];

        $('.group').each(function () {
            var group_id = $('.group_id', this).val();

            // If an group and an option was selected, then add them to array.
            if (group_id) {
                groups.push({
                    group_id: group_id
                });
            }
        });

        // If there is at least one group, then add a hidden field to the form
        // with a JSON value that contains the groups.
        if (groups.length) {
            var hidden_field = $('<input type="hidden" name="groups">');

            hidden_field.val(JSON.stringify(groups));

            $('.groups').append(hidden_field);
        }

        return true;
    });
}




// Create a function that will open a jQuery dialog that contains an iframe.
function open_dialog(properties) {
    var modal = properties.modal;
    var title = properties.title;
    var url = properties.url;
    var width = properties.width;
    var height = properties.height;

    // If modal is not set, then enable it by default.
    if (modal === undefined) {
        modal = true;
    }

    // If the width is not set or it is larger than the window width,
    // then set width as a percentage of the browser width.
    if (
        (width === undefined)
        || (width == 0)
        || (width > $(window).width())
    ) {
        width = $(window).width() * .75;

    // Otherwise let's use the width that was passed,
    // but modify it to account for the dialog borders
    // so that the iframe size matches what was passed.
    } else {
        width = width + 20;
    }

    // If the height is not set or it is larger than the window height,
    // then set height as a percentage of the browser height.
    if (
        (height === undefined)
        || (height == 0)
        || (height > $(window).height())
    ) {
        height = $(window).height() * .75;

    // Otherwise the height is set and it is less than the window height,
    // so use height parameter and add 29px to make up for the jQuery dialog iframe height.
    } else {
        height = height + 29;
    }

    // Add iframe to the body.

    var iframe = $('<iframe src="' + h(url) + '" frameBorder="0" style="display: block; margin: 0" allowfullscreen></iframe>');

    $('body').append(iframe);

    // Open jQuery dialog.
    iframe.dialog({
        autoOpen: true,

        // Adjust the size of the iframe, so it does not appear behind the handles.
        open: function() {
            var iframe = $(this);
            var dialog = iframe.closest('.ui-dialog');
            var left_handle = $('.ui-resizable-w', dialog);
            var left_handle_width = left_handle.width();
            var width = dialog.width() - (left_handle_width * 2);
            var bottom_handle = $('.ui-resizable-s', dialog);
            var bottom_handle_height = bottom_handle.height();

            iframe.css({
                'width': width + 'px',
                'margin-left': left_handle_width + 'px',
                'margin-bottom': bottom_handle_height + 'px'
            });
        },

        // Add an overlay over the whole page while the user is dragging the dialog,
        // so that the drag will work correctly with an iframe.
        dragStart: function() {
            $('body').append('<div class="overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647"></div>');
        },

        dragStop: function() {
            $('.overlay').remove();
        },

        // Add an overlay over the whole page while the user is resizing the dialog,
        // so that the resize will work correctly with an iframe.
        resizeStart: function() {
            $('body').append('<div class="overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647"></div>');
        },

        resizeStop: function() {
            $('.overlay').remove();
        },

        close: function() {
            $(this).dialog('destroy').remove();
        },

        modal: modal,
        title: title,
        width: width,
        height: height,
        dialogClass: 'standard'
    });
}

function check_iframe_access(iframe) {
    var key = ( +new Date ) + "" + Math.random();

    try {
        var global = iframe.contentWindow;
        global[key] = "asd";
        return global[key] === "asd";
    }

    catch( e ) {
        return false;
    }
}

function check_if_page_type_supports_layout(page_type) {
    switch (page_type) {
        case 'billing information':
        case 'catalog':
        case 'catalog detail':
        case 'change password':
        case 'set password':
        case 'custom form':
        case 'email preferences':
        case 'express order':
        case 'forgot password':
        case 'form item view':
        case 'form list view':
        case 'login':
        case 'membership entrance':
        case 'my account':
        case 'my account profile':
        case 'order form':
        case 'order preview':
        case 'order receipt':
        case 'photo gallery':
        case 'registration entrance':
        case 'search results':
        case 'shipping address and arrival':
        case 'shipping method':
        case 'shopping cart':
        case 'update address book':
            return true;
            break;

        default:
            return false;
            break;
    }
}

// Show the list of contact groups when the opt-in check box is checked.
function init_email_preferences() {

    var contact_groups = $('.contact_groups');

    if (contact_groups.length) {

        var opt_in = $('input[name=opt_in]');

        opt_in.change(function() {

            if (opt_in.is(':checked')) {
                contact_groups.fadeIn();
            } else {
                contact_groups.fadeOut();
            }

        });

        // Trigger a change event so the fields will be updated during initial page load.
        opt_in.trigger('change');
    }

}

function preview_upload_image() {
    var total_file=document.getElementById("input_file_upload").files.length;
    for(var i=0;i<total_file;i++)
    {
        $('#image_preview').append("<div class='image_preview_item'><div class='image_prev_style_tag'>Upload</div><img src='"+URL.createObjectURL(event.target.files[i])+"'></div>");
    }
}

//Development purphose. add draw lines to all elements
function show_draw_lines(){
$('body').toggleClass('show-draw-lines');
}

function formatState (state) {
    if (!state.id) {
      return state.text;
    }
    var baseUrl = "/user/pages/images/flags";
    var $state = $(
      '<span><img src="' + path + state.element.value.toLowerCase() + '" class="img-flag small" /> ' + state.text + '</span>'
    );
    return $state;
  };


// the following code is a minified version of http://www.json.org/json2.js
// it is used by the style designer to prepare a JSON string to be submitted to a PHP script
// this is necessary because some older browsers don't support the JSON object
if(!this.JSON){this.JSON={};}
(function(){function f(n){return n<10?'0'+n:n;}
if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(key){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+
f(this.getUTCMonth()+1)+'-'+
f(this.getUTCDate())+'T'+
f(this.getUTCHours())+':'+
f(this.getUTCMinutes())+':'+
f(this.getUTCSeconds())+'Z':null;};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf();};}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}
function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key);}
if(typeof rep==='function'){value=rep.call(holder,key,value);}
switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}
gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null';}
v=partial.length===0?'[]':gap?'[\n'+gap+
partial.join(',\n'+gap)+'\n'+
mind+']':'['+partial.join(',')+']';gap=mind;return v;}
if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){k=rep[i];if(typeof k==='string'){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}else{for(k in value){if(Object.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}
v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+
mind+'}':'{'+partial.join(',')+'}';gap=mind;return v;}}
if(typeof JSON.stringify!=='function'){JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' ';}}else if(typeof space==='string'){indent=space;}
rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}
return str('',{'':value});};}
if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v;}else{delete value[k];}}}}
return reviver.call(holder,key,value);}
text=String(text);cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+
('0000'+a.charCodeAt(0).toString(16)).slice(-4);});}
if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j;}
throw new SyntaxError('JSON.parse');};}}());
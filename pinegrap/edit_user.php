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
include_once('liveform.class.php');
$liveform_view_users = new liveform('view_users');
$user = validate_user();
validate_area_access($user, 'manager');

// if editor is less than an administrator role, check to make sure that editor has access to edit user
if ($user['role'] > 0) {
    // get role of user that is being edited
    $query =
        "SELECT user_role
        FROM user
        WHERE user_id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $role = $row['user_role'];
    
    // if the editor's role is less than or equal to the user that the editor is trying to edit, then output error
    if ($user['role'] >= $role) {
        log_activity("access denied because user does not have access to edit user", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }
}

if (!$_POST) {
    $set_page_type_values = array();
    
    $query =
        "SELECT
            user_id,
            user_username,
            user_email,
            user_role,
            user_home,
            user_password_hint,
            user_badge,
            user_badge_label,
            user_reward_points,
            user_manage_contacts,
            user_manage_visitors,
            user_create_pages,
            user_delete_pages,
            user_manage_forms,
            user_manage_calendars,
            user_manage_emails,
            user_manage_ecommerce,
            user_view_card_data,
            manage_ecommerce_reports,
            user_set_offline_payment,
            user_publish_calendar_events,
            user_set_page_type_email_a_friend,
            user_set_page_type_folder_view,
            user_set_page_type_photo_gallery,
            user_set_page_type_custom_form,
            user_set_page_type_custom_form_confirmation,
            user_set_page_type_form_list_view,
            user_set_page_type_form_item_view,
            user_set_page_type_form_view_directory,
            user_set_page_type_calendar_view,
            user_set_page_type_calendar_event_view,
            user_set_page_type_catalog,
            user_set_page_type_catalog_detail,
            user_set_page_type_express_order,
            user_set_page_type_order_form,
            user_set_page_type_shopping_cart,
            user_set_page_type_shipping_address_and_arrival,
            user_set_page_type_shipping_method,
            user_set_page_type_billing_information,
            user_set_page_type_order_preview,
            user_set_page_type_order_receipt,
            contacts.id AS contact_id,
            contacts.first_name,
            contacts.last_name,
            contacts.email_address,
            contacts.member_id
        FROM user
        LEFT JOIN contacts ON user.user_contact = contacts.id
        WHERE user_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    $id = $row['user_id'];
    $username = $row['user_username'];
    $email = $row['user_email'];
    $role = $row['user_role'];
    $home = $row['user_home'];
    $password_hint = $row['user_password_hint'];
    $badge = $row['user_badge'];
    $badge_label = $row['user_badge_label'];
    $reward_points = $row['user_reward_points'];
    $manage_contacts = $row['user_manage_contacts'];
    $manage_visitors = $row['user_manage_visitors'];
    $create_pages = $row['user_create_pages'];
    $delete_pages = $row['user_delete_pages'];
    $manage_forms = $row['user_manage_forms'];
    $manage_calendars = $row['user_manage_calendars'];
    $manage_emails = $row['user_manage_emails'];
    $manage_ecommerce = $row['user_manage_ecommerce'];
    $view_card_data = $row['user_view_card_data'];
    $manage_ecommerce_reports = $row['manage_ecommerce_reports'];
    $set_offline_payment = $row['user_set_offline_payment'];
    $publish_calendar_events = $row['user_publish_calendar_events'];
    $set_page_type_values['set_page_type_email_a_friend'] = $row['user_set_page_type_email_a_friend'];
    $set_page_type_values['set_page_type_folder_view'] = $row['user_set_page_type_folder_view'];
    $set_page_type_values['set_page_type_photo_gallery'] = $row['user_set_page_type_photo_gallery'];
    $set_page_type_values['set_page_type_custom_form'] = $row['user_set_page_type_custom_form'];
    $set_page_type_values['set_page_type_custom_form_confirmation'] = $row['user_set_page_type_custom_form_confirmation'];
    $set_page_type_values['set_page_type_form_list_view'] = $row['user_set_page_type_form_list_view'];
    $set_page_type_values['set_page_type_form_item_view'] = $row['user_set_page_type_form_item_view'];
    $set_page_type_values['set_page_type_form_view_directory'] = $row['user_set_page_type_form_view_directory'];
    $set_page_type_values['set_page_type_calendar_view'] = $row['user_set_page_type_calendar_view'];
    $set_page_type_values['set_page_type_calendar_event_view'] = $row['user_set_page_type_calendar_event_view'];
    $set_page_type_values['set_page_type_catalog'] = $row['user_set_page_type_catalog'];
    $set_page_type_values['set_page_type_catalog_detail'] = $row['user_set_page_type_catalog_detail'];
    $set_page_type_values['set_page_type_express_order'] = $row['user_set_page_type_express_order'];
    $set_page_type_values['set_page_type_order_form'] = $row['user_set_page_type_order_form'];
    $set_page_type_values['set_page_type_shopping_cart'] = $row['user_set_page_type_shopping_cart'];
    $set_page_type_values['set_page_type_shipping_address_and_arrival'] = $row['user_set_page_type_shipping_address_and_arrival'];
    $set_page_type_values['set_page_type_shipping_method'] = $row['user_set_page_type_shipping_method'];
    $set_page_type_values['set_page_type_billing_information'] = $row['user_set_page_type_billing_information'];
    $set_page_type_values['set_page_type_order_preview'] = $row['user_set_page_type_order_preview'];
    $set_page_type_values['set_page_type_order_receipt'] = $row['user_set_page_type_order_receipt'];
    $contact_id = $row['contact_id'];
    $first_name = trim($row['first_name']);
    $last_name = trim($row['last_name']);
    $email_address = trim($row['email_address']);
    $member_id = trim($row['member_id']);

    $output_login_as_user_button = '';

    // If this user is not the editor, then output button to allow editor to login as user.
    // There is not an important reason why we don't allow a user to login as him/herself,
    // however we just decided to prevent this because there is really no reason to allow it and
    // it might cause confusing log messages eventually (e.g. "example_username -> example_username").
    // It might also cause confusion for the user about what happens if they do that.
    if ($id != USER_ID) {
        $output_login_as_user_button = '<a href="login_as_user.php?id=' . h($_GET['id']) . get_token_query_string_field() . '">Login as User</a>';
    }
    
    $output_contact_info = '';
    
    // if this user has a contact, then prepare contact info
    if ($contact_id != '') {
        $output_name = '';
        
        // if there is a first name or last name, then output name
        if (($first_name != '') || ($last_name != '')) {
            $name = '';
            
            // if there is a first name, then start name with that
            if ($first_name != '') {
                $name .= $first_name;
            }
            
            // if there is a last name, then add it to the name
            if ($last_name != '') {
                // if the name is not blank so far, then add a space for separation
                if ($name != '') {
                    $name .= ' ';
                }
                
                $name .= $last_name;
            }
            
            $output_name = '<div style="font-size: 120%; margin-bottom: .75em"><a href="edit_contact.php?id=' . $contact_id . '&send_to=' . h(urlencode(get_request_uri())) . '">' . h($name) . '</a></div>';
        }
        
        $output_email_address = '';
        
        // if there is an e-mail address then output it
        if ($email_address != '') {
            $output_link_start = '';
            $output_link_end = '';
            
            // if there is no name then add link around e-mail address
            if ($output_name == '') {
                $output_link_start = '<a href="edit_contact.php?id=' . $contact_id . '&send_to=' . h(urlencode(get_request_uri())) . '">';
                $output_link_end = '</a>';
            }
            
            $output_email_address = '<div style="margin-bottom: .75em">' . $output_link_start . h($email_address) . $output_link_end . '</div>';
        }
        
        // if there is a member ID then output it
        if ($member_id != '') {
            $output_member_id = '<div>Member ID: ' . h($member_id) . '</div>';
        }
        
        $output_contact_info =
            $output_name . '
            ' . $output_email_address . '
            ' . $output_member_id;
        
    // else the user does not have a contact, so output message and submit button
    } else {
        $output_contact_info =
            '<div style="margin-bottom: 1.5em" class="help">
                This User does not currently have a Contact connected to it. Connecting a Contact to this User is required to use the Membership features.
                A Contact will be created and connected to this User automatically when the User performs certain actions
                (e.g. updates his/her profile, submits a Custom Form, submits an Order, and etc.).
                However, if for any reason, you would like to do this now, you can.
            </div>
            <div><input type="submit" name="submit_create_contact" value="Create Contact" class="submit-secondary" /></div>';
    }
    
    $output_role = '';
    
    // assume that user may be deleted, until we find out otherwise
    $allow_delete = true;
    
    // if user is an administrator or designer, then prepare to output role picklist
    if ($user['role'] <= 1) {
        // assume that role is allowed to be changed, until we find out otherwise
        $allow_role_change = true;
        
        // if the editor is an administrator and the user that is being edited is an administrator, find out if role is allowed to be changed
        if (($user['role'] == 0) && ($role == 0)) {
            // check to see if there is another administrator user, other than this user that is being edited
            $query =
                "SELECT user_id
                FROM user
                WHERE
                    (user_role = '0')
                    AND (user_id != '$id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is no other administrator user, do not allow the role to be changed and do not allow the user to be deleted
            if (mysqli_num_rows($result) == 0) {
                $allow_role_change = false;
                $allow_delete = false;
            }
        }

        // if the role is allowed to be changed
        if ($allow_role_change == true) {
            $output_role =
                '<tr>
                    <th colspan="2"><h2>Set Default Privileges for User Account</h2></th>
                </tr>
                <tr>
                    <td>User Role:</td>
                    <td><select name="role" onchange="change_user_role(this.options[this.selectedIndex].value)">' . select_user_role($role, $user['role']) . '</select></td>
                </tr>';
        }
    }
    
    // if the user is allowed to be deleted, prepare delete button to be outputted
    if ($allow_delete == true) {
        $output_delete_button = '&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This user will be permanently deleted.\')" />';
        
    // else the user is not allowed to be deleted, so do not prepare delete button to be outputted
    } else {
        $output_delete_button = '';
    }

    $output_password_hint_field = "";

    // Find out if password hint is enabled in the settings
    if (PASSWORD_HINT == TRUE) {
        $output_password_hint_field = 
            '<tr>
                <td>User Password Hint:</td>
                <td><input name="password_hint" type="text" value="' . h($password_hint) . '" size="40" maxlength="100" /></td>
            </tr>';
    }

    $badge_checked = '';
    $manage_visitors_checked = '';
    $create_pages_checked = '';
    $delete_pages_checked = '';
    $manage_forms_checked = '';
    $manage_calendars_checked = '';
    $manage_ecommerce_checked = '';
    $view_card_data_checked = '';
    $manage_ecommerce_reports_checked = '';
    $set_offline_payment_checked = '';
    $manage_contacts_checked = '';
    $manage_emails_checked = '';
    $publish_calendar_events_checked = '';
    
    // If badge is enabled, then check check box and show badge label row.
    if ($badge == 1) {
        $badge_checked = ' checked="checked"';
        $output_badge_label_row_style = '';
    } else {
        $output_badge_label_row_style = ' style="display: none"';
    }
    
    // find out if manage visitors checkbox should be checked or not
    if ($manage_visitors == 'yes') {
        $manage_visitors_checked = ' checked="checked"';
    }
    
    // find out if create pages checkbox should be checked or not
    if ($create_pages == '1') {
        $create_pages_checked = ' checked="checked"';
    }
    
    // find out if delete pages checkbox should be checked or not
    if ($delete_pages == '1') {
        $delete_pages_checked = ' checked="checked"';
    }
    
    // find out if manage forms checkbox should be checked or not
    if ($manage_forms == 'yes') {
        $manage_forms_checked = ' checked="checked"';
    }
    
    // find out if manage calendars checkbox should be checked or not
    if ($manage_calendars == 'yes') {
        $manage_calendars_checked = ' checked="checked"';
        $calendar_access_style = '';
        $publish_calendar_events_style = '';
        
    } else {
        $calendar_access_style = '; display: none';
        $publish_calendar_events_style = '; display: none';
    }
    
    // find out if publish calendar events should be checked or not
    if ($publish_calendar_events == 'yes') {
        $publish_calendar_events_checked = ' checked="checked"';
    }
    
    // if manage e-commerce is enabled, then check check box and show view card data check box
    if ($manage_ecommerce == 'yes') {
        $manage_ecommerce_checked = ' checked="checked"';
        $view_card_data_style = '';
        
    // else manage e-commerce is disabled, so uncheck check box and hide view card data check box
    } else {
        $view_card_data_style = '; display: none';
    }

    // find out if view card data checkbox should be checked or not
    if ($manage_ecommerce_reports) {
        $manage_ecommerce_reports_checked = ' checked="checked"';
    }

    if ($view_card_data == 1) {
        $view_card_data_checked = ' checked="checked"';
    }
    
    // find out if set offline payment checkbox should be checked or not
    if ($set_offline_payment == 1) {
        $set_offline_payment_checked = ' checked="checked"';
    }
    
    // find out if manage contacts checkbox should be checked or not
    if ($manage_contacts == 'yes') {
        $manage_contacts_checked = ' checked="checked"';
    }
    
    // find out if manage e-mails checkbox should be checked or not
    if ($manage_emails == 'yes') {
        $manage_emails_checked = ' checked="checked"';
    }
    
    $contact_group_access_style = '';
    
    // if manage contacts and manage emails is off, then hide list of contact groups
    if (($manage_contacts != 'yes') && ($manage_emails != 'yes')) {
        $contact_group_access_style = '; display: none';
    }
    
    $manage_visitors_heading_row_style = '';
    $manage_visitors_row_style = '';
    $manage_ecommerce_heading_row_style = '';
    $manage_ecommerce_row_style = '';
    $manage_calendars_heading_row_style = '';
    $manage_calendars_row_style = '';
    $edit_access_heading_row_style = '';
    $edit_access_row_style = '';
    $shared_content_access_rights_heading_row_style = '';
    $common_regions_access_row_style = '';
    $menus_access_row_style = '';
    $manage_contacts_and_manage_emails_heading_row_style = '';
    $manage_contacts_and_manage_emails_row_style = '';
    $manage_ad_regions_heading_row_style = '';
    $manage_ad_regions_row_style = '';
    $view_access_heading_row_style = '';
    $view_access_row_style = '';
    
    // if user is an administrator, designer, or manager, then hide certain fields
    if ($role < 3) {
        $manage_visitors_heading_row_style = ' style="display: none"';        
        $manage_visitors_row_style = ' style="display: none"';
        $manage_ecommerce_heading_row_style = ' style="display: none"';
        $manage_ecommerce_row_style = ' style="display: none"';
        $manage_calendars_heading_row_style = ' style="display: none"';
        $manage_calendars_row_style = ' style="display: none"';
        $edit_access_heading_row_style = ' style="display: none"';
        $edit_access_row_style = ' style="display: none"';
        $shared_content_access_rights_heading_row_style = ' style="display: none"';
        $common_regions_access_row_style = ' style="display: none"';
        $menus_access_row_style = ' style="display: none"';
        $manage_contacts_and_manage_emails_heading_row_style = ' style="display: none"';
        $manage_contacts_and_manage_emails_row_style = ' style="display: none"';
        $manage_ad_regions_heading_row_style = ' style="display: none"';
        $manage_ad_regions_row_style = ' style="display: none"';
        $view_access_heading_row_style = ' style="display: none"';
        $view_access_row_style = ' style="display: none"';
    }
    
    $output_manage_forms = '';
    
    // if forms module is on, then output manage forms checkbox
    if (FORMS == true) {
        $output_manage_forms = '<div><input type="checkbox" name="manage_forms" id="manage_forms" value="yes" class="checkbox"' . $manage_forms_checked . ' /><label for="manage_forms"> Also allow User to access submitted form data for selected folders</label></div>';
    }
    
    // if calendars module is on, then output manage calendars checkbox
    if (CALENDARS == true) {
        // get all calendars
        $query =
            "SELECT
               id,
               name
            FROM calendars
            ORDER BY name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $calendars = array();
        
        // loop through all calendars so they can be added to array
        while ($row = mysqli_fetch_assoc($result)) {
            $calendars[] = $row;
        }

        $output_calendars = '';

        // loop through all calendars
        foreach ($calendars as $calendar) {
            // check to see if user has access to calendar
            $query =
                "SELECT user_id
                FROM users_calendars_xref
                WHERE
                    (user_id = '" . escape($_GET['id']) . "')
                    AND (calendar_id = '" . $calendar['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if user has access to this calendar, then prepare to check checkbox
            if (mysqli_num_rows($result) > 0) {
                $checked = ' checked="checked"';
                
            // else user does not have access to this calendar, so do not check checkbox
            } else {
                $checked = '';
            }
            
            $output_calendars .= '<input type="checkbox" name="calendar_' . $calendar['id'] . '" id="calendar_' . $calendar['id'] . '" value="1" class="checkbox"' . $checked . ' /><label for="calendar_' . $calendar['id'] . '"> ' . h($calendar['name']) . '</label><br />';
        }
        
        $output_manage_calendars =
            '<tr id="manage_calendars_heading_row" '. $manage_calendars_heading_row_style .'>
                <th colspan="2"><h2>Calendar Management Rights</h2></th>
            </tr>
            <tr id="manage_calendars_row"' . $manage_calendars_row_style . '>
                <td colspan="2">
                    <div><input type="checkbox" name="manage_calendars" id="manage_calendars" value="yes" class="checkbox"' . $manage_calendars_checked . ' onclick="show_or_hide_calendar_access()" /><label for="manage_calendars"> Allow User to add events to one or more calendars</label></div>
                    <div id="calendar_access" class="scrollable" style="max-height: 10em; margin: 1em 0em 1em 2em' . $calendar_access_style . '">
                        ' . $output_calendars . '
                    </div>
                    <div id="publish_calendar_events_container" style="margin-left: 2em' . $publish_calendar_events_style . '"><input type="checkbox" id="publish_calendar_events" name="publish_calendar_events" value="yes" class="checkbox"' . $publish_calendar_events_checked . ' /><label for="publish_calendar_events"> Also allow User to publish calendar events for selected calendars</label></div>
                </td>
            </tr>';
    }
    
    // if e-commerce module is on, then output manage e-commerce checkbox
    if (ECOMMERCE == true) {
        $output_set_offline_payment = '';
        
        // if offline payment is enabled, then prepare to output set offline payment
        if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
            $output_set_offline_payment = '<div><input type="checkbox" name="set_offline_payment" id="set_offline_payment" value="1" class="checkbox"' . $set_offline_payment_checked . ' /><label for="set_offline_payment"> Allow User to set offline payment option for orders</label></div>';
        }
        
        $output_manage_ecommerce =
            '<tr id="manage_ecommerce_heading_row" '. $manage_ecommerce_heading_row_style .'>
                <th colspan="2"><h2>Commerce Management Rights</h2></th>
            </tr>
            <tr id="manage_ecommerce_row"' . $manage_ecommerce_row_style . '>
                <td colspan="2">
                    <div><input type="checkbox" name="manage_ecommerce" id="manage_ecommerce" value="yes" class="checkbox"' . $manage_ecommerce_checked . ' onclick="show_or_hide_ecommerce_access()" /><label for="manage_ecommerce"> Allow User to manage all commerce (i.e. products, shipping, tax, and orders)</label></div>
                    <div id="view_card_data_container" style="margin: 1em 0em 1em 2em' . $view_card_data_style . '"><input type="checkbox" name="view_card_data" id="view_card_data" value="1" class="checkbox"' . $view_card_data_checked . ' /><label for="view_card_data"> Also allow User to view card data</label></div>
                    <div>
                        <input type="checkbox" name="manage_ecommerce_reports" id="manage_ecommerce_reports" value="1" class="checkbox"' . $manage_ecommerce_reports_checked . '><label for="manage_ecommerce_reports"> Allow User to manage all commerce reports (i.e. order reports &amp; shipping report)</label>
                    </div>
                    ' . $output_set_offline_payment . '
                </td>
            </tr>';
    }

    $output_manage_ads = '';

    // If ads is enabled, then output area for it.
    if (ADS === true) {
        $output_manage_ads =
            '<tr id="manage_ad_regions_heading_row" '. $manage_ad_regions_heading_row_style .'>
                <th colspan="2"><h2>Ads Management Rights</h2></th>
            </tr>
            <tr id="manage_ad_regions_row"' . $manage_ad_regions_row_style . '>
                <td colspan="2">
                    <div style="margin-bottom: 1em">Allow User to edit Ads within the selected Ad Regions</div>
                    <div style="margin-left: 2em">
                        <div class="scrollable" style="max-height: 10em">
                            ' . get_checkboxes_for_items_user_can_edit('ad_regions', get_items_user_can_edit('ad_regions', $_GET['id'])) . '
                        </div>
                    </div>
                </td>
            </tr>';
    }
    
    // get all contact groups
    $query =
        "SELECT
           id,
           name
        FROM contact_groups
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contact_groups = array();
    
    // loop through all contact groups so they can be added to array
    while ($row = mysqli_fetch_assoc($result)) {
        $contact_groups[] = $row;
    }

    $output_contact_groups = '';

    // loop through all contact groups
    foreach ($contact_groups as $contact_group) {
        // check to see if user has access to contact group
        $query =
            "SELECT user_id
            FROM users_contact_groups_xref
            WHERE
                (user_id = '" . escape($_GET['id']) . "')
                AND (contact_group_id = '" . $contact_group['id'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if user has access to this contact group, then prepare to check checkbox
        if (mysqli_num_rows($result) > 0) {
            $checked = ' checked="checked"';
            
        // else user does not have access to this contact group, so do not check checkbox
        } else {
            $checked = '';
        }
        
        $output_contact_groups .= '<input type="checkbox" name="contact_group_' . $contact_group['id'] . '" id="contact_group_' . $contact_group['id'] . '" value="1" class="checkbox"' . $checked . ' /><label for="contact_group_' . $contact_group['id'] . '"> ' . h($contact_group['name']) . '</label><br />';
    }
    
    $output_hidden_role = '';
    
    // if user that is logged in is a manager then output hidden field for role data
    if ($user['role'] == 2) {
        $output_hidden_role = '<input type="hidden" name="role" value="3" />';
    }
    
    // get folders that user has edit access to
    $folders_that_user_has_edit_access_to = array();
    
    $query =
        "SELECT aclfolder_folder as folder_id
        FROM aclfolder
        WHERE
            (aclfolder_user = '" . escape($_GET['id']) . "')
            AND (aclfolder_rights = '2')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
        $folders_that_user_has_edit_access_to[] = $row['folder_id'];
    }
    
    // get folders that user has view access to
    $folders_that_user_has_view_access_to = array();
    
    $query =
        "SELECT aclfolder_folder as folder_id
        FROM aclfolder
        WHERE
            (aclfolder_user = '" . escape($_GET['id']) . "')
            AND (aclfolder_rights = '1')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while ($row = mysqli_fetch_assoc($result)) {
        $folders_that_user_has_view_access_to[] = $row['folder_id'];
    }

    $output_badge_label_info = '';

    // If there is a default badge label in the site settings,
    // then output info about how field can be left blank for default.
    if (BADGE_LABEL != '') {
        $output_badge_label_info = ' (leave blank for default: "' . h(BADGE_LABEL) . '")';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($username) . '</h1>
            ' . h($email) . '
        </div>
        <div id="button_bar">
            ' . $output_login_as_user_button . '
            <a href="#" onclick="if (confirm(\'Are you sure you want to reset the user\\\'s password and email a new password to the user?\') == true) {document.reset_password_form.submit(); return false;} else {return false;}">Reset &amp; Send Password</a><form name="reset_password_form" action="reset_password.php" method="post">' . get_token_field() . '<input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" /><input type="hidden" name="user_id" value="' . h($_GET['id']) . '"></form>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit User</h1>
            <div class="subheading">Update this user\'s privileges, or email a new password.</div>
            <form name="form" action="edit_user.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
                ' . $output_hidden_role . '
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                <input type="hidden" name="current_url" value="' . h(get_request_uri()) . '" />
                <table>
                    <tr>
                        <td style="padding: 0em 6em 0em 0em">
                            <table class="field">
                                <tr>
                                    <th colspan="2"><h2>User Account ID</h2></th>
                                </tr>
                                <tr>
                                    <td>Username:</td>
                                    <td><input name="username" type="text" value="' . h($username) . '" size="40" maxlength="100" /></td>
                                </tr>
                                <tr>
                                    <th colspan="2"><h2>Send User to a Specific Page on Login</h2></th>
                                </tr>
                                <tr>
                                    <td>User Start Page:</td>
                                    <td><select name="home_page"><option value="0">-None-</option>' . select_page($home) . '</select></td>
                                </tr>
                                <tr>
                                    <th colspan="2"><h2>Email for Login &amp; Password Retrieval</h2></th>
                                </tr>
                                <tr>
                                    <td>User Email:</td>
                                    <td><input name="email" type="email" value="' . h($email) . '" size="40" maxlength="100" /></td>
                                </tr>
                                ' . $output_password_hint_field . '
                                ' . $output_role . '
                                <tr id="edit_access_heading_row" '. $edit_access_heading_row_style .'>
                                    <th colspan="2"><h2>Content Management &amp; Forms Management Rights</h2></th>
                                </tr>
                                <tr id="edit_access_row"' . $edit_access_row_style . '>
                                    <td colspan="2">
                                        <div style="margin-bottom: 1em">Allow User to view and edit pages, files, and custom forms within selected folders</div>
                                        <div style="margin-left: 2em; margin-bottom: 1em">
                                            <div class="scrollable" style="max-height: 10em; margin-bottom: 1em">
                                                ' . get_acl_folder_tree('edit', 0, 0, array(), $folders_that_user_has_edit_access_to) . '
                                            </div>
                                            <div><input type="checkbox" name="create_pages" id="create_pages" value="1" class="checkbox"' . $create_pages_checked . ' /><label for="create_pages"> Also allow User to create/duplicate pages in selected folders</label></div>
                                            <div><input type="checkbox" name="delete_pages" id="delete_pages" value="1" class="checkbox"' . $delete_pages_checked . ' /><label for="delete_pages"> Also allow User to delete pages in selected folders</label></div>
                                            ' . $output_manage_forms . '
                                        </div>
                                        <div style="margin-bottom: 1em">Allow User to set the following page types for pages</div>
                                        <div style="margin-left: 2em">
                                            <div class="scrollable" style="max-height: 10em">
                                                ' . get_page_type_checkboxes_and_labels($set_page_type_values) . '
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="shared_content_access_rights_heading_row" '. $shared_content_access_rights_heading_row_style .'>
                                    <th colspan="2"><h2>Shared Content Management Rights</h2></th>
                                </tr>
                                <tr id="common_regions_access_row"' . $common_regions_access_row_style . '>
                                    <td colspan="2">
                                        <div style="margin-bottom: 1em">Allow User to edit the content within the selected Common Regions</div>
                                        <div style="margin-left: 2em">
                                            <div class="scrollable" style="max-height: 10em">
                                                ' . get_checkboxes_for_items_user_can_edit('common_regions', get_items_user_can_edit('common_regions', $_GET['id'])) . '
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="menus_access_row"' . $menus_access_row_style . '>
                                    <td colspan="2">
                                        <div style="margin-bottom: 1em">Allow User to edit Menu Items within the selected Menus</div>
                                        <div style="margin-left: 2em">
                                            <div class="scrollable" style="max-height: 10em">
                                                ' . get_checkboxes_for_items_user_can_edit('menus', get_items_user_can_edit('menus', $_GET['id'])) . '
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                ' . $output_manage_calendars . '
                                <tr id="manage_visitors_heading_row" '. $manage_visitors_heading_row_style .'>
                                    <th colspan="2"><h2>Visitor Report Management Rights</h2></th>
                                </tr>
                                <tr id="manage_visitors_row" '. $manage_visitors_row_style .'>
                                    <td colspan="2"><input type="checkbox" name="manage_visitors" id="manage_visitors" value="yes" class="checkbox"' . $manage_visitors_checked . ' /><label for="manage_visitors"> Allow User to manage all visitor reports</label></td>
                                </tr>
                                <tr id="manage_contacts_and_manage_emails_heading_row"' . $manage_contacts_and_manage_emails_heading_row_style . '>
                                    <th colspan="2"><h2>Contact Management &amp; Campaign Management Rights</h2></th>
                                </tr>
                                <tr id="manage_contacts_and_manage_emails_row"' . $manage_contacts_and_manage_emails_row_style . '>
                                    <td colspan="2">
                                        <div><input type="checkbox" name="manage_contacts" id="manage_contacts" value="yes" class="checkbox"' . $manage_contacts_checked . ' onclick="show_or_hide_contact_group_access()" /><label for="manage_contacts"> Allow User to view, edit, import, and export all contacts within any selected contact groups</label></div>
                                        <div><input type="checkbox" name="manage_emails" id="manage_emails" value="yes" class="checkbox"' . $manage_emails_checked . ' onclick="show_or_hide_contact_group_access()" /><label for="manage_emails"> Allow User to send e-mail campaigns to any selected contact groups</label></div>
                                        <div id="contact_group_access" class="scrollable" style="max-height: 10em; margin-top: 1em; margin-left: 2em' . $contact_group_access_style . '">
                                            ' . $output_contact_groups . '
                                        </div>
                                    </td>
                                </tr>
                                ' . $output_manage_ecommerce . '
                                ' . $output_manage_ads . '
                                <tr id="view_access_heading_row" '. $view_access_heading_row_style .'>
                                    <th colspan="2"><h2>Private Content Access Rights</h2></th>
                                </tr>
                                <tr id="view_access_row"' . $view_access_row_style . '>
                                    <td colspan="2">
                                        <div style="margin-bottom: 1em">
                                            Allow User to view pages, files, and submit custom forms within selected private folders.<br />
                                            For selected folders, you can enter an optional expiration date (leave blank for no expiration).
                                        </div>
                                        <div style="margin-left: 2em">
                                            <div class="scrollable" style="max-height: 10em">
                                                ' . get_date_picker_format() . '
                                                ' . get_acl_folder_tree('view', 0, 0, array(), $folders_that_user_has_view_access_to, $_GET['id']) . '
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2"><h2>Badge</h2></th>
                                </tr>
                                <tr>
                                    <td colspan="2"><input type="checkbox" name="badge" id="badge" value="1" class="checkbox"' . $badge_checked . ' onclick="toggle_badge()" /><label for="badge"> Show badge next to username</label></td>
                                </tr>
                                <tr id="badge_label_row"' . $output_badge_label_row_style . '>
                                    <td style="padding-left: 3em">Badge Label:</td>
                                    <td><input type="text" name="badge_label" value="' . h($badge_label) . '" size="20" maxlength="100" />' . $output_badge_label_info . '</td>
                                </tr>
                                <tr>
                                    <th colspan="2"><h2>Reward Program</h2></th>
                                </tr>
                                <tr>
                                    <td>Reward Points:</td>
                                    <td><input type="text" name="reward_points" value="' . $reward_points . '" size="5" maxlength="9" /></td>
                                </tr>
                            </table>
                            <div class="buttons">
                                <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />' . $output_delete_button . '
                            </div>
                        </td>
                        <td style="padding:0; vertical-align: top;">
                            <div style="width: 25em">
                                <h2>User\'s Contact</h2>
                                ' . $output_contact_info . '
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>' .
        output_footer();
        
} else {
    validate_token_field();
    
    // if create contact was selected, then create contact
    if ($_POST['submit_create_contact'] == 'Create Contact') {
        // get e-mail address for this user
        $query =
            "SELECT user_email
            FROM user
            WHERE user_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $email_address = $row['user_email'];
        
        $query =
            "INSERT INTO contacts (
                email_address,
                user,
                timestamp)
            VALUES (
                '" . escape($email_address) . "',
                '" . $user['id'] . "',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $contact_id = mysqli_insert_id(db::$con);
        
        // connect contact to user
        $query =
            "UPDATE user
            SET
                user_contact = '" . $contact_id . "',
                user_user = '" . $user['id'] . "',
                user_timestamp = UNIX_TIMESTAMP()
            WHERE user_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // forward user to contact that was just created
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_contact.php?id=' . $contact_id . '&send_to=' . urlencode($_POST['current_url']));
        exit();
    
    // else if user was selected for delete
    } else if ($_POST['submit_delete'] == 'Delete') {
        // get role for user that is being deleted
        $query =
            "SELECT user_role
            FROM user
            WHERE user_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $role = $row['user_role'];
        
        // assume that user is allowed to be deleted until we find out otherwise
        $allow_delete = true;
        
        // if the editor is an administrator and the user that is being edited is an administrator, find out if user is allowed to be deleted
        if (($user['role'] == 0) && ($role == 0)) {
            // check to see if there is another administrator user, other than this user that is being edited
            $query =
                "SELECT user_id
                FROM user
                WHERE
                    (user_role = '0')
                    AND (user_id != '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is no other administrator user, do not allow user to be deleted
            if (mysqli_num_rows($result) == 0) {
                $allow_delete = false;
            }
        }

        // if the user is allowed to be deleted
        if ($allow_delete == true) {
            // delete user record
            $query = "DELETE FROM user WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete aclfolder records
            $query = "DELETE FROM aclfolder WHERE aclfolder_user = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete users_contact_groups_xref records
            $query = "DELETE FROM users_contact_groups_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete users_calendars_xref records
            $query = "DELETE FROM users_calendars_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete address book records
            $query = "DELETE FROM address_book WHERE user = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete ad region xref records
            $query = "DELETE FROM users_ad_regions_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete common region xref records
            $query = "DELETE FROM users_common_regions_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete menu xref records
            $query = "DELETE FROM users_menus_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            db("DELETE FROM users_messages_xref WHERE user_id = '" . e($_POST['id']) . "'");
            
            log_activity("user ($_POST[username]) was deleted", $_SESSION['sessionusername']);
            
            $liveform_view_users->add_notice('The user has been deleted.');
            
            // If there is a send to value then send user back to that screen
            if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
                header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
                
            // else send user to the default view
            } else {
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_users.php');
            }
            exit();
            
		// else user is not allowed to be deleted, so output error
        } else {
            $liveform_view_users->add_notice('The user may not be deleted because it is the only administrator.');
            
            // If there is a send to value then send user back to that screen
            if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
                header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
                
            // else send user to the default view
            } else {
                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_users.php');
            }
            exit();
        }

    // else the user is being edited
    } else {

        // if editor is not an administrator and the editor's role is less than or equal to the role that the editor is trying to set, then output error
        if (($user['role'] != 0) && ($user['role'] >= $_POST['role'])) {
            log_activity("access denied because user does not have access to set the requested role for a user", $_SESSION['sessionusername']);
            output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
        }

        $_POST['username'] = trim($_POST['username']);
        $_POST['email'] = trim($_POST['email']);

        // If the username is blank, then output error.
        if ($_POST['username'] == '') {
            output_error('Username is required. <a href="javascript:history.go(-1);">Go back</a>.');
        }

        // validate e-mail address
        if (validate_email_address($_POST['email']) == FALSE) {
            output_error('The email address is invalid. <a href="javascript:history.go(-1);">Go back</a>.');
        }

        // determine if username is already in use
        $result = mysqli_query(db::$con, "SELECT user_id FROM user WHERE ((user_username = '" . escape($_POST['username']) . "') OR (user_email = '" . escape($_POST['username']) . "')) AND (user_id != '" . escape($_POST['id']) . "')") or output_error('Query failed');
        if (mysqli_num_rows($result) > 0)
        {
            output_error('The username that you entered is already in use. <a href="javascript:history.go(-1);">Go back</a>.');
        }

        // determine if e-mail address is already in use
        $result = mysqli_query(db::$con, "SELECT user_id FROM user WHERE ((user_email = '" . escape($_POST['email']) . "') OR (user_username = '" . escape($_POST['email']) . "')) AND (user_id != '" . escape($_POST['id']) . "')") or output_error('Query failed');
        if (mysqli_num_rows($result) > 0) {
            output_error('The email address that you entered is already in use. <a href="javascript:history.go(-1);">Go back</a>.');
        }

        $username = $_POST['username'];
        
        // get role for user that is being edited
        $query =
            "SELECT user_role
            FROM user
            WHERE user_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $role = $row['user_role'];
        
        // assume that role is allowed to be changed, until we find out otherwise
        $allow_role_change = true;
        
        // if the editor is an administrator and the user that is being edited is an administrator, find out if role is allowed to be changed
        if (($user['role'] == 0) && ($role == 0)) {
            // check to see if there is another administrator user, other than this user that is being edited
            $query =
                "SELECT user_id
                FROM user
                WHERE
                    (user_role = '0')
                    AND (user_id != '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is no other administrator user, do not allow the role to be changed
            if (mysqli_num_rows($result) == 0) {
                $allow_role_change = false;
            }
        }

        // if the role is allowed to be changed
        if ($allow_role_change == true) {
            $sql_role = " user_role = '" . escape($_POST['role']) . "',";
        } else {
            $sql_role = "";
        }
        
        $sql_offline_payment = '';
        
        // if offline payment is enabled, then prepare to update set offline payment
        if (ECOMMERCE_OFFLINE_PAYMENT == TRUE) {
            $sql_offline_payment = "user_set_offline_payment = '" . escape($_POST['set_offline_payment']) . "',";
        }
        
        $sql_set_page_types = '';
        
        // if forms are enabled, then set the sql to update the set page type columns that are associated with this feature
        if (FORMS == true) {
            $sql_set_page_types .= 
                " user_set_page_type_custom_form = '" . escape($_POST['set_page_type_custom_form']) . "',
                user_set_page_type_custom_form_confirmation = '" . escape($_POST['set_page_type_custom_form_confirmation']) . "', 
                user_set_page_type_form_list_view = '" . escape($_POST['set_page_type_form_list_view']) . "',
                user_set_page_type_form_item_view = '" . escape($_POST['set_page_type_form_item_view']) . "',
                user_set_page_type_form_view_directory = '" . escape($_POST['set_page_type_form_view_directory']) . "',";
        }
        
        // if calendars are enabled, then set the sql to update the set page type columns that are associated with this feature
        if (CALENDARS == true) {
            $sql_set_page_types .= 
                " user_set_page_type_calendar_view = '" . escape($_POST['set_page_type_calendar_view']) . "',
                user_set_page_type_calendar_event_view = '" . escape($_POST['set_page_type_calendar_event_view']) . "',";
        }
        
        // if ecommerce is enabled, then set the sql to update the set page type columns that are associated with this feature
        if (ECOMMERCE == true) {
            $sql_set_page_types .= 
                " user_set_page_type_catalog = '" . escape($_POST['set_page_type_catalog']) . "',
                user_set_page_type_catalog_detail = '" . escape($_POST['set_page_type_catalog_detail']) . "',
                user_set_page_type_express_order = '" . escape($_POST['set_page_type_express_order']) . "',
                user_set_page_type_order_form = '" . escape($_POST['set_page_type_order_form']) . "',
                user_set_page_type_shopping_cart = '" . escape($_POST['set_page_type_shopping_cart']) . "',
                user_set_page_type_shipping_address_and_arrival = '" . escape($_POST['set_page_type_shipping_address_and_arrival']) . "',
                user_set_page_type_shipping_method = '" . escape($_POST['set_page_type_shipping_method']) . "',
                user_set_page_type_billing_information = '" . escape($_POST['set_page_type_billing_information']) . "',
                user_set_page_type_order_preview = '" . escape($_POST['set_page_type_order_preview']) . "',
                user_set_page_type_order_receipt = '" . escape($_POST['set_page_type_order_receipt']) . "',";
        }
        
        // update user
        $query =
            "UPDATE user
            SET
                user_username = '" . escape($username) . "',
                user_email = '" . escape($_POST['email']) . "',
                $sql_role
                user_home = '" . escape($_POST['home_page']) . "',
                user_password_hint = '" . escape($_POST['password_hint']) . "',
                user_badge = '" . escape($_POST['badge']) . "',
                user_badge_label = '" . escape($_POST['badge_label']) . "',
                user_reward_points = '" . escape($_POST['reward_points']) . "',
                user_manage_contacts = '" . escape($_POST['manage_contacts']) . "',
                user_manage_visitors = '" . escape($_POST['manage_visitors']) . "',
                user_create_pages = '" . escape($_POST['create_pages']) . "',
                user_delete_pages = '" . escape($_POST['delete_pages']) . "',
                user_manage_forms = '" . escape($_POST['manage_forms']) . "',
                user_manage_calendars = '" . escape($_POST['manage_calendars']) . "',
                user_manage_emails = '" . escape($_POST['manage_emails']) . "',
                user_manage_ecommerce = '" . escape($_POST['manage_ecommerce']) . "',
                user_view_card_data = '" . escape($_POST['view_card_data']) . "',
                manage_ecommerce_reports = '" . e($_POST['manage_ecommerce_reports']) . "',
                $sql_offline_payment
                user_publish_calendar_events = '" . escape($_POST['publish_calendar_events']) . "',
                user_set_page_type_email_a_friend = '" . escape($_POST['set_page_type_email_a_friend']) . "',
                user_set_page_type_folder_view = '" . escape($_POST['set_page_type_folder_view']) . "',
                user_set_page_type_photo_gallery = '" . escape($_POST['set_page_type_photo_gallery']) . "',
                $sql_set_page_types
                user_user = '" . $user['id'] . "',
                user_timestamp = UNIX_TIMESTAMP()
            WHERE user_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Delete current access control records in order to add new ones.
        $query = "DELETE FROM aclfolder WHERE aclfolder_user = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // find all folders to update access control list
        $result=mysqli_query(db::$con, "SELECT folder_id FROM folder") or output_error('Query failed');
        while ($row=mysqli_fetch_array($result)) {
            $folder_id = $row['folder_id'];
            $sql_expiration_date = "";
            
            // if the user was given edit rights to this folder, then set rights value to 2
            if ($_POST['edit_' . $folder_id] == 1) {
                $rights = 2;
                
            // else if the user was given view rights to this folder, then deal with that.
            } elseif ($_POST['view_' . $folder_id] == 1) {
                // Remove spaces from beginning and end of date.
                $expiration_date = trim($_POST['view_' . $folder_id . '_expiration_date']);

                // If an expiration date was entered, then validate it.
                if ($expiration_date != '') {
                    // Convert date to storage format.
                    $expiration_date = prepare_form_data_for_input($expiration_date, 'date');
                    
                    // Split date into parts.
                    $expiration_date_parts = explode('-', $expiration_date);
                    $year = $expiration_date_parts[0];
                    $month = $expiration_date_parts[1];
                    $day = $expiration_date_parts[2];
                    
                    // If the expiration date is valid, then give user access and set expiration date in SQL.
                    if ((is_numeric($month) == true) && (is_numeric($day) == true) && (is_numeric($year) == true) && (checkdate($month, $day, $year) == true)) {
                        $rights = 1;
                        $sql_expiration_date = $expiration_date;
                        
                    // Otherwise the expiration date is not valid, so do not give user access and do not set expiration date in SQL.
                    } else {
                        $rights = 0;
                    }

                // Otherwise an expiration date was not entered, so just give user access.
                } else {
                    $rights = 1;
                }
                
            // else the user was was given no rights to this folder, so set rights value to 0
            } else {
                $rights = 0;
            }

            $result2 = mysqli_query(db::$con, "INSERT INTO aclfolder (aclfolder_user, aclfolder_folder, aclfolder_rights, expiration_date) VALUES ('" . escape($_POST['id']) . "', '" . $folder_id . "', '" . $rights . "', '" . escape($sql_expiration_date) . "')") or output_error('Query failed.');
        }
        
        // if user that was created has a user role, then prepare to assign access to various items for user
        if ($_POST['role'] == 3) {
            // delete user and common region references in users_common_regions_xref
            $query = "DELETE FROM users_common_regions_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // get all common regions
            $query = "SELECT cregion_id FROM cregion";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $common_regions = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $common_regions[] = $row;
            }
            
            // loop through all common regions and input them if they were selected
            foreach ($common_regions as $common_region) {
                // if common region was selected for user to be given access to, give access to common region
                if ($_POST['common_region_' . $common_region['cregion_id']] == 1) {
                    $query =
                        "INSERT INTO users_common_regions_xref (
                            user_id,
                            common_region_id)
                        VALUES (
                            '" . escape($_POST['id']) . "',
                            '" . escape($common_region['cregion_id']) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
            
            // delete user and menu references in users_menus_xref
            $query = "DELETE FROM users_menus_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // get all menus
            $query = "SELECT id FROM menus";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $menus = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $menus[] = $row;
            }
            
            // loop through all menus and input them if they were selected
            foreach ($menus as $menu) {
                // if menu was selected for user to be given access to, give access to menu
                if ($_POST['menu_' . $menu['id']] == 1) {
                    $query =
                        "INSERT INTO users_menus_xref (
                            user_id,
                            menu_id)
                        VALUES (
                            '" . escape($_POST['id']) . "',
                            '" . escape($menu['id']) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
            
            // delete user and contact group references in users_contact_groups_xref
            $query = "DELETE FROM users_contact_groups_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if manage contacts or manage e-mails was checked, check to see which contact groups the user needs to be given access to
            if (($_POST['manage_contacts'] == 'yes') || ($_POST['manage_emails'] == 'yes')) {
                // get all contact groups
                $query = "SELECT id FROM contact_groups";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $contact_groups = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $contact_groups[] = $row;
                }
                
                // loop through all contact groups
                foreach ($contact_groups as $contact_group) {
                    // if contact group was selected for user to be given access to, give access to user to contact group
                    if ($_POST['contact_group_' . $contact_group['id']] == 1) {
                        $query =
                            "INSERT INTO users_contact_groups_xref (
                                user_id,
                                contact_group_id)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                '" . $contact_group['id'] . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
            }
            
            // delete user and calendar references in users_calendars_xref
            $query = "DELETE FROM users_calendars_xref WHERE user_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if manage calendars was checked, check to see which calendars the user needs to be given access to
            if ($_POST['manage_calendars'] == 'yes') {
                // get all calendars
                $query = "SELECT id FROM calendars";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $calendars = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $calendars[] = $row;
                }
                
                // loop through all calendars
                foreach ($calendars as $calendar) {
                    // if calendar was selected for user to be given access to, give access to user to calendar
                    if ($_POST['calendar_' . $calendar['id']] == 1) {
                        $query =
                            "INSERT INTO users_calendars_xref (
                                user_id,
                                calendar_id)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                '" . $calendar['id'] . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
            }

            // If ads are enabled, then store access.
            if (ADS === true) {
                // delete user and ad region references in users_ad_regions_xref
                $query = "DELETE FROM users_ad_regions_xref WHERE user_id = '" . escape($_POST['id']) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // get all ad regions
                $query = "SELECT id FROM ad_regions";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $ad_regions = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $ad_regions[] = $row;
                }
                
                // loop through all ad regions and input them if they were selected
                foreach ($ad_regions as $ad_region) {
                    // if ad region was selected for user to be given access to, give access to ad region
                    if ($_POST['ad_region_' . $ad_region['id']] == 1) {
                        $query =
                            "INSERT INTO users_ad_regions_xref (
                                user_id,
                                ad_region_id)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                '" . escape($ad_region['id']) . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
            }
        }
        
        log_activity("user ($username) was modified", $_SESSION['sessionusername']);
        $liveform_view_users->add_notice('The user has been saved.');
        
        // If there is a send to value then send user back to that screen
        if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
            
        // else send user to the default view
        } else {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_users.php');
        }
        
        exit();
    }
}
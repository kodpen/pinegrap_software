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
validate_contacts_access($user);

include_once('liveform.class.php');

// if user does not have access to contact, then output error
if (validate_contact_access($user, $_REQUEST['id']) == false) {
    log_activity("access denied to edit contact because user does not have access to a contact group that the contact is in", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

if (!$_POST) {
    // if ecommerce is on, then output orders button to show orders for contact
    if ((ECOMMERCE === true) && (($user['role'] < 3) || ($user['manage_ecommerce'] == true))) {
        $output_orders_button = '
            <div id="button_bar">
                <a href="view_orders_for_contact.php?id=' . h(urlencode($_REQUEST['id'])) . '">View Orders</a>
            </div>';
    }

    $query =
        "SELECT
            contacts.salutation,
            contacts.first_name,
            contacts.last_name,
            contacts.suffix,
            contacts.nickname,
            contacts.company,
            contacts.title,
            contacts.department,
            contacts.office_location,
            contacts.business_address_1,
            contacts.business_address_2,
            contacts.business_city,
            contacts.business_state,
            contacts.business_country,
            contacts.business_zip_code,
            contacts.business_phone,
            contacts.business_fax,
            contacts.home_address_1,
            contacts.home_address_2,
            contacts.home_city,
            contacts.home_state,
            contacts.home_country,
            contacts.home_zip_code,
            contacts.home_phone,
            contacts.home_fax,
            contacts.mobile_phone,
            contacts.email_address,
            contacts.website,
            contacts.lead_source,
            contacts.opt_in,
            contacts.description,
            contacts.member_id,
            contacts.expiration_date,
            contacts.affiliate_approved,
            contacts.affiliate_name,
            contacts.affiliate_code,
            contacts.affiliate_commission_rate,
            user.user_id,
            user.user_username AS username,
            user.user_role
        FROM contacts
        LEFT JOIN user ON contacts.id = user.user_contact
        WHERE contacts.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);

    $salutation =           h($row['salutation']);
    $first_name =           h($row['first_name']);
    $last_name =            h($row['last_name']);
    $suffix =               h($row['suffix']);
    $nickname =             h($row['nickname']);
    $company =              h($row['company']);
    $title =                h($row['title']);
    $department =           h($row['department']);
    $office_location =      h($row['office_location']);
    $business_address_1 =   h($row['business_address_1']);
    $business_address_2 =   h($row['business_address_2']);
    $business_city =        h($row['business_city']);
    $business_state =       h($row['business_state']);
    $business_country =     h($row['business_country']);
    $business_zip_code =    h($row['business_zip_code']);
    $business_phone =       h($row['business_phone']);
    $business_fax =         h($row['business_fax']);
    $home_address_1 =       h($row['home_address_1']);
    $home_address_2 =       h($row['home_address_2']);
    $home_city =            h($row['home_city']);
    $home_state =           h($row['home_state']);
    $home_country =         h($row['home_country']);
    $home_zip_code =        h($row['home_zip_code']);
    $home_phone =           h($row['home_phone']);
    $home_fax =             h($row['home_fax']);
    $mobile_phone =         h($row['mobile_phone']);
    $email_address =        h($row['email_address']);
    $website =              h($row['website']);
    $lead_source =          h($row['lead_source']);
    $opt_in =              $row['opt_in'];
    $description =          h($row['description']);
    $member_id =            h($row['member_id']);
    $expiration_date =      $row['expiration_date'];
    $affiliate_approved =   $row['affiliate_approved'];
    $affiliate_name =       h($row['affiliate_name']);
    $affiliate_code =       h($row['affiliate_code']);
    $affiliate_commission_rate = $row['affiliate_commission_rate'];
    $user_id = $row['user_id'];
    $username = $row['username'];
    $user_role = $row['user_role'];

    // If this contact has a user connected to it, then output username.
    if ($username != '') {
        // If the editor user is an administrator or the editor user has access to edit this user,
        // then prepare username with link.
        if ((USER_ROLE == 0) || (USER_ROLE < $user_role)) {
            $output_user_info = '<a href="edit_user.php?id=' . $user_id . '">' . h($username) . '</a>';

        // Otherwise the editor user does not have access to edit this user,
        // so prepare username without link.
        } else {
            $output_user_info = h($username);
        }

    // Otherwise this contact does not have a user connected to it, so output different content.
    } else {
        $output_user_info = '<div style="margin-top: .25em; margin-bottom: .85em">This Contact does not currently have a User connected to it.</div>';

        // If the user editing this contact has a manager role or above,
        // then add button to allow user to create a user for this contact.
        if (USER_ROLE < 3) {
            $output_user_info .= '<div><input type="button" value="Create User" onclick="window.location=\'' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY . '/add_user.php?contact_id=' . $_GET['id'])) . '\'" class="submit-secondary"></div>';
        }
    }

    if ($expiration_date == '0000-00-00') {
        $expiration_date = '';
    }

    $expiration_date = prepare_form_data_for_output($expiration_date, 'date');
    
    // if contact is opt-in
    if ($opt_in) {
        $opt_in_checked = ' checked="checked"';
    // else contact is not opt-in
    } else {
        $opt_in_checked = '';
    }
    
    // get all contact groups
    $query =
        "SELECT
           id,
           name,
           email_subscription
        FROM contact_groups
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contact_groups = array();

    // loop through all contact groups
    while ($row = mysqli_fetch_assoc($result)) {
        // if user has access to contact group, then include this contact group
        if (validate_contact_group_access($user, $row['id']) == true) {
            $contact_groups[] = $row;
        }
    }    
    
    $output_contact_groups = '';
    $contact_group_counter = 1;
    
    // determine how many contact groups need to be in each table cell
    $number_of_contact_groups_per_cell = ceil(count($contact_groups)/3);
    
    foreach ($contact_groups as $key => $contact_group) {
        //if the counter is zero, then output an opening table cell and opening table tags
        if ($contact_group_counter == 1) {
            $output_contact_groups .= 
                '<td style="width: 33%">
                    <table class="field">';
        }
        
        // check if contact is in this contact group
        $query =
            "SELECT contact_id
            FROM contacts_contact_groups_xref
            WHERE
                (contact_id = '" . escape($_GET['id']) . "')
                AND (contact_group_id = '" . $contact_group['id'] . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if contact is in this contact group, then prepare checkbox to be checked
        if (mysqli_num_rows($result) > 0) {
            $contact_group_checked = ' checked="checked"';
            $contact_group_opt_in_cell_style = '';
        } else {
            $contact_group_checked = '';
            $contact_group_opt_in_cell_style = '; display: none';
        }
        
        // if contact group has email subscription turned on, prepare onclick to show opt-in field and prepare opt-in field
        if ($contact_group['email_subscription'] == 1) {
            $contact_group_onclick = ' onclick="show_or_hide_contact_group_opt_in(' . $contact_group['id'] . ')"';
            
            // check if contact is opted-in to this contact group
            $query =
                "SELECT opt_in
                FROM opt_in
                WHERE
                    (contact_id = '" . escape($_GET['id']) . "')
                    AND (contact_group_id = '" . $contact_group['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            // if an opt-in record was not found or opt-in is 1, then contact is opted-in
            if ((mysqli_num_rows($result) == 0) || ($row['opt_in'] == 1)) {
                $contact_group_opt_in_checked = ' checked="checked"';
                
            // else contact is opted-out
            } else {
                $contact_group_opt_in_checked = '';
            }
            
            $output_opt_in = '<input type="checkbox" name="contact_group_opt_in_' . $contact_group['id'] . '" id="contact_group_opt_in_' . $contact_group['id'] . '" value="1"' . $contact_group_opt_in_checked . ' class="checkbox" /><label for="contact_group_opt_in_' . $contact_group['id'] . '"> Opt-In</label>';
        } else {
            $contact_group_onclick = '';
            $output_opt_in = '&nbsp;';
        }
        
        $output_contact_groups .=
            '<tr>
                <td><input type="checkbox" name="contact_group_' . $contact_group['id'] . '" id="contact_group_' . $contact_group['id'] . '" value="1" class="checkbox"' . $contact_group_checked . $contact_group_onclick . ' /><label for="contact_group_' . $contact_group['id'] . '"> ' . h($contact_group['name']) . '</label></td>
                <td id="contact_group_opt_in_cell_' . $contact_group['id'] . '" style="padding-left: 10px' . $contact_group_opt_in_cell_style . '">' . $output_opt_in . '</td>
            </tr>';
        
        // if the counter is equal to the number of contact groups per cell, or if this is the last contact group, then output closing table and table cell tags and set counter to zero
        if (($contact_group_counter == $number_of_contact_groups_per_cell) || (array_key_exists($key + 1, $contact_groups) == FALSE)) {
            $output_contact_groups .= 
                '
                    </table>
                </td>';
            
            $contact_group_counter = 1;
            
        // otherwise increment the counter
        } else {
            $contact_group_counter++;
        }
    }
    
    // if the affiliate program is enabled, prepare affiliate program output
    if (AFFILIATE_PROGRAM == true) {
        if ($affiliate_approved) {
            $affiliate_approved_checked = ' checked';
        } else {
            $affiliate_approved_checked = '';
        }
        
        // clear affiliate commission rate if it is 0
        if ($affiliate_commission_rate == 0) {
            $affiliate_commission_rate = '';
        }
        
        $output_affiliate =
            '<h2 style="margin-bottom: 0.35em">Affiliate Information</h2>
            <table>
                <tr>
                    <td>Approved:</td>
                    <td><input type="checkbox" name="affiliate_approved" value="1"' . $affiliate_approved_checked . ' class="checkbox" /></td>
                </tr>
                <tr>
                    <td>Affiliate Name:</td>
                    <td><input name="affiliate_name" type="text" value="' . $affiliate_name . '" size="30" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>Affiliate Code:</td>
                    <td><input name="affiliate_code" type="text" value="' . $affiliate_code . '" size="30" maxlength="100" /> (leave blank to automatically generate code)</td>
                </tr>
                <tr>
                    <td>Commission Rate:</td>
                    <td><input name="affiliate_commission_rate" type="text" value="' . $affiliate_commission_rate . '" size="6" maxlength="100" /> % (leave blank for default: ' . AFFILIATE_DEFAULT_COMMISSION_RATE . '%)</td>
                </tr>
            </table>';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . $first_name  . ' ' . $last_name  . '</h1>
        </div>
        ' . $output_orders_button . '
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Contact</h1>
            <div class="subheading">View or update this contact\'s information, subscriber status, member status, affiliate status, or contact groups.</div>
            <form name="form" action="edit_contact.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2" style="padding-right: 1em"><h2>Contact\'s Name</h2></th>
                        <th colspan="2"><h2>Contact\'s Subscriber Information</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Salutation:</td>
                        <td style="padding-right: 20px"><input name="salutation" type="text" value="' . $salutation . '" size="30" maxlength="50" tabindex="1" /></td>
                        <td style="padding-right: 5px">Email: </td>
                        <td><input name="email_address" type="email" value="' . $email_address . '" size="30" maxlength="100" tabindex="6"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">First Name:</td>
                        <td style="padding-right: 20px"><input name="first_name" type="text" value="' . $first_name . '" size="30" maxlength="50" tabindex="2"></td>
                        <td style="padding-right: 5px"><label for="opt_in">Opt-In:</label></td>
                        <td><input type="checkbox" id="opt_in" name="opt_in" value="1" class="checkbox" tabindex="7"' . $opt_in_checked . '></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Last Name:</td>
                        <td style="padding-right: 20px"><input name="last_name" type="text" value="' . $last_name . '" size="30" maxlength="50" tabindex="3"></td>
                        <th colspan="2"><h2 style="margin: 0">Contact\'s User</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Suffix:</td>
                        <td style="padding-right: 20px"><input name="suffix" type="text" value="' . $suffix . '" size="30" maxlength="50" tabindex="4" /></td>
                        <td colspan="2" rowspan="2">' . $output_user_info . '</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Nickname:</td>
                        <td style="padding-right: 20px"><input name="nickname" type="text" value="' . $nickname . '" size="30" maxlength="50" tabindex="5"></td>
                    </tr>
                    <tr>
                        <th colspan="2" style="padding-right: 1em"><h2>Contact\'s Work Information</h2></th>
                        <th colspan="2"><h2>Contact\'s Home Information</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Company:</td>
                        <td style="padding-right: 20px"><input name="company" type="text" value="' . $company . '" size="30" maxlength="50" tabindex="8"></td>
                        <td style="padding-right: 5px">Home Address 1: </td>
                        <td><input name="home_address_1" type="text" value="' . $home_address_1 . '" size="30" maxlength="50" tabindex="22"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Title:</td>
                        <td style="padding-right: 20px"><input name="title" type="text" value="' . $title . '" size="30" maxlength="50" tabindex="9"></td>
                        <td style="padding-right: 5px">Home Address 2: </td>
                        <td><input name="home_address_2" type="text" value="' . $home_address_2 . '" size="30" maxlength="50" tabindex="23"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Department:</td>
                        <td style="padding-right: 20px"><input name="department" type="text" value="' . $department . '" size="30" maxlength="50" tabindex="10"></td>
                        <td style="padding-right: 5px">Home City: </td>
                        <td><input name="home_city" type="text" value="' . $home_city . '" size="30" maxlength="50" tabindex="24"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Office Location:</td>
                        <td style="padding-right: 20px"><input name="office_location" type="text" value="' . $office_location . '" size="30" maxlength="50" tabindex="12"></td>
                        <td style="padding-right: 5px">Home State: </td>
                        <td><input name="home_state" type="text" value="' . $home_state . '" size="30" maxlength="50" tabindex="25"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Address 1: </td>
                        <td style="padding-right: 20px"><input name="business_address_1" type="text" value="' . $business_address_1 . '" size="30" maxlength="50" tabindex="13"></td>
                        <td style="padding-right: 5px">Home Country: </td>
                        <td><input name="home_country" type="text" value="' . $home_country . '" size="30" maxlength="50" tabindex="26"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Address 2: </td>
                        <td style="padding-right: 20px"><input name="business_address_2" type="text" value="' . $business_address_2 . '" size="30" maxlength="50" tabindex="14"></td>
                        <td style="padding-right: 5px">Home Zip Code: </td>
                        <td><input name="home_zip_code" type="text" value="' . $home_zip_code . '" size="30" maxlength="10" tabindex="27"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business City: </td>
                        <td style="padding-right: 20px"><input name="business_city" type="text" value="' . $business_city . '" size="30" maxlength="50" tabindex="15"></td>
                        <td style="padding-right: 5px">Home Phone: </td>
                        <td><input name="home_phone" type="text" value="' . $home_phone . '" size="30" maxlength="50" tabindex="28"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business State: </td>
                        <td style="padding-right: 20px"><input name="business_state" type="text" value="' . $business_state . '" size="30" maxlength="50" tabindex="16"></td>
                        <td style="padding-right: 5px">Mobile Phone: </td>
                        <td><input name="mobile_phone" type="text" value="' . $mobile_phone . '" size="30" maxlength="50" tabindex="29"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Country: </td>
                        <td style="padding-right: 20px"><input name="business_country" type="text" value="' . $business_country . '" size="30" maxlength="50" tabindex="17"></td>
                        <td style="padding-right: 5px">Home Fax: </td>
                        <td><input name="home_fax" type="text" value="' . $home_fax . '" size="30" maxlength="50" tabindex="30"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Zip Code: </td>
                        <td style="padding-right: 20px"><input name="business_zip_code" type="text" value="' . $business_zip_code . '" size="30" maxlength="10" tabindex="18"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Phone:</td>
                        <td><input name="business_phone" type="text" value="' . $business_phone . '" size="30" maxlength="50" tabindex="19"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Fax: </td>
                        <td><input name="business_fax" type="text" value="' . $business_fax . '" size="30" maxlength="50" tabindex="20"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Website: </td>
                        <td><input name="website" type="text" value="' . $website . '" size="30" maxlength="255" tabindex="21" /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th colspan="4"><h2>Additional Notes</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Lead Source: </td>
                        <td><input name="lead_source" type="text" value="' . $lead_source . '" size="30" maxlength="50" tabindex="31" /></td>
                    </tr>
                    <tr>
                        <td>Description: </td>
                        <td colspan="3"><textarea name="description" cols="50" rows="5" style="width: 96%" tabindex="32">' . $description . '</textarea></td>
                    </tr>
                </table>
                <h2 style="margin-bottom: 0.35em">Membership Access</h2>
                <table class="field">
                    <tr>
                        <td>' . h(MEMBER_ID_LABEL) . ':</td>
                        <td><input name="member_id" type="text" value="' . $member_id . '" size="30" />&nbsp;&nbsp;&nbsp;(leave blank for no membership)</td>
                    </tr>
                    <tr>
                        <td>Expiration Date:</td>
                        <td>
                            <input id="expiration_date" name="expiration_date" type="text" value="' . $expiration_date . '" size="10" />&nbsp;&nbsp;&nbsp;(leave blank for lifetime membership)
                            ' . get_date_picker_format() . '
                            <script>
                                $("#expiration_date").datepicker({
                                    dateFormat: date_picker_format
                                });
                            </script>
                        </td>
                    </tr>
                </table>
                ' . $output_affiliate . '
                <h2 style="margin-bottom: .75em">Contact Groups & Subscriptions</h2>
                <table class="field" style="width: 100%">
                    <tr>
                        ' . $output_contact_groups . '
                    </tr>
                </table>
                <div class="buttons" style="margin-top: 1em">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This contact will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

} else {
    validate_token_field();
    
    // if contact was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete contact
        $query =
            "DELETE FROM contacts
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete contact references in contacts_contact_groups_xref
        $query = "DELETE FROM contacts_contact_groups_xref WHERE contact_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete contact references in opt_in
        $query = "DELETE FROM opt_in WHERE contact_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity("contact ($_POST[first_name] $_POST[last_name]) was deleted", $_SESSION['sessionusername']);
        
        $liveform_view_contacts = new liveform('view_contacts');
        $liveform_view_contacts->add_notice('The contact has been deleted.');
        
    // else contact was not selected for delete
    } else {

        $_POST['email_address'] = trim($_POST['email_address']);
        
        // if an e-mail address was entered, validate e-mail address
        if ($_POST['email_address']) {
            if (validate_email_address($_POST['email_address']) == FALSE) {
                output_error('The email address is invalid. <a href="javascript:history.go(-1);">Go back</a>.');
            }
        }
        
        if (AFFILIATE_PROGRAM == true) {
            // determine if affiliate was approved already (we will use this later)
            $query = "SELECT affiliate_approved FROM contacts WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            $original_affiliate_approved = $row['affiliate_approved'];
            
            $affiliate_code = $_POST['affiliate_code'];
            
            // if an affiliate code was entered check to see if affiliate code is already in use
            if ($affiliate_code) {
                $query = "SELECT id FROM contacts WHERE affiliate_code = '" . escape($affiliate_code) . "' AND id != '" . escape($_POST['id']) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                if (mysqli_num_rows($result) > 0) {
                    output_error('That affiliate code is already in use. Please use a different code. <a href="javascript:history.go(-1);">Go back</a>.');
                }
            }
            
            // if affiliate is approved and affiliate code is blank, generate affiliate code
            if ($_POST['affiliate_approved'] && (!$affiliate_code)) {
                $affiliate_code = generate_affiliate_code();
            }
            
            $sql_affiliate =
                "affiliate_approved = '" . escape($_POST['affiliate_approved']) . "',
                affiliate_name = '" . escape($_POST['affiliate_name']) . "',
                affiliate_code = '" . escape($affiliate_code) . "',
                affiliate_commission_rate = '" . escape($_POST['affiliate_commission_rate']) . "',";
        }
        
        $query =
            "UPDATE contacts
            SET
                salutation = '" . escape($_POST['salutation']) . "',
                first_name = '" . escape($_POST['first_name']) . "',
                last_name = '" . escape($_POST['last_name']) . "',
                suffix = '" . escape($_POST['suffix']) . "',
                nickname = '" . escape($_POST['nickname']) . "',
                company = '" . escape($_POST['company']) . "',
                title = '" . escape($_POST['title']) . "',
                department = '" . escape($_POST['department']) . "',
                office_location = '" . escape($_POST['office_location']) . "',
                business_address_1 = '" . escape($_POST['business_address_1']) . "',
                business_address_2 = '" . escape($_POST['business_address_2']) . "',
                business_city = '" . escape($_POST['business_city']) . "',
                business_state = '" . escape($_POST['business_state']) . "',
                business_country = '" . escape($_POST['business_country']) . "',
                business_zip_code = '" . escape($_POST['business_zip_code']) . "',
                business_phone = '" . escape($_POST['business_phone']) . "',
                business_fax = '" . escape($_POST['business_fax']) . "',
                home_address_1 = '" . escape($_POST['home_address_1']) . "',
                home_address_2 = '" . escape($_POST['home_address_2']) . "',
                home_city = '" . escape($_POST['home_city']) . "',
                home_state = '" . escape($_POST['home_state']) . "',
                home_country = '" . escape($_POST['home_country']) . "',
                home_zip_code = '" . escape($_POST['home_zip_code']) . "',
                home_phone = '" . escape($_POST['home_phone']) . "',
                home_fax = '" . escape($_POST['home_fax']) . "',
                mobile_phone = '" . escape($_POST['mobile_phone']) . "',
                email_address = '" . escape($_POST['email_address']) . "',
                website = '" . escape($_POST['website']) . "',
                lead_source = '" . escape($_POST['lead_source']) . "',
                opt_in = '" . escape($_POST['opt_in']) . "',
                description = '" . escape($_POST['description']) . "',
                member_id = '" . escape($_POST['member_id']) . "',
                expiration_date = '" . escape(prepare_form_data_for_input($_POST['expiration_date'], 'date')) . "',
                $sql_affiliate
                user = '" . $user['id'] . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        // If this contact has an email address, then update opt-in status for other contacts
        // with this same email address, so the opt-in status is the same for all.
        if ($_POST['email_address']) {
            db(
                "UPDATE contacts SET opt_in = '" . e($_POST['opt_in']) . "'
                WHERE email_address = '" . e($_POST['email_address']) . "'");
        }
        
        // get all contact groups, so that contact can be added to selected contact groups
        $query =
            "SELECT
               id,
               email_subscription
            FROM contact_groups";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $contact_groups = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            $contact_groups[] = $row;
        }
        
        // loop through all contact groups
        foreach ($contact_groups as $contact_group) {
            // if user has access to contact group, then continue
            if (validate_contact_group_access($user, $contact_group['id']) == true) {
                // if contact group was selected for contact, add contact to contact group
                if ($_POST['contact_group_' . $contact_group['id']] == 1) {
                    // check to see if contact is already in contact group
                    $query =
                        "SELECT contact_id
                        FROM contacts_contact_groups_xref
                        WHERE
                            (contact_id = '" . escape($_POST['id']) . "')
                            AND (contact_group_id = '" . $contact_group['id'] . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // if contact is not already in contact group, then add contact to contact group
                    if (mysqli_num_rows($result) == 0) {
                        $query =
                            "INSERT INTO contacts_contact_groups_xref (
                                contact_id,
                                contact_group_id)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                '" . $contact_group['id'] . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                    
                    // if contact group has email subscription turned on, add opt-in selection
                    if ($contact_group['email_subscription'] == 1) {
                        // check to see if there is already an opt-in record
                        $query =
                            "SELECT contact_id
                            FROM opt_in
                            WHERE
                                (contact_id = '" . escape($_POST['id']) . "')
                                AND (contact_group_id = '" . $contact_group['id'] . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if there is not already an opt-in record, then create record
                        if (mysqli_num_rows($result) == 0) {
                            $query =
                                "INSERT INTO opt_in (
                                    contact_id,
                                    contact_group_id,
                                    opt_in)
                                VALUES (
                                    '" . escape($_POST['id']) . "',
                                    '" . $contact_group['id'] . "',
                                    '" . escape($_POST['contact_group_opt_in_' . $contact_group['id']]) . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                        // else an opt-in record already exists, so update record
                        } else {
                            $query =
                                "UPDATE opt_in
                                SET opt_in = '" . escape($_POST['contact_group_opt_in_' . $contact_group['id']]) . "'
                                WHERE
                                    (contact_id = '" . escape($_POST['id']) . "')
                                    AND (contact_group_id = '" . $contact_group['id'] . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                    
                // else contact group was not selected for contact, so remove contact from contact group
                } else {
                    $query =
                        "DELETE FROM contacts_contact_groups_xref
                        WHERE
                            (contact_id = '" . escape($_POST['id']) . "')
                            AND (contact_group_id = '" . $contact_group['id'] . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }
        
        // if affiliate program is enabled and there is a group offer and the affiliate has been approved and it was not approved before, then determine if we need to add a key code for group offer for this affiliate
        if ((AFFILIATE_PROGRAM == TRUE) && (AFFILIATE_GROUP_OFFER_ID != 0) && ($_POST['affiliate_approved'] == 1) && ($original_affiliate_approved == 0)) {
            // check if offer exists and get offer code
            $query = "SELECT code FROM offers WHERE id = '" . AFFILIATE_GROUP_OFFER_ID . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if an offer was found, then continue to check if a key code should be added for group offer
            if (mysqli_num_rows($result) > 0) {
                $offer = mysqli_fetch_assoc($result);
                
                // check if a key code already exists for this group offer and affiliate
                $query =
                    "SELECT id
                    FROM key_codes
                    WHERE
                        (code = '" . escape($affiliate_code) . "')
                        AND (offer_code = '" . escape($offer['code']) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if a key code does not already exist for this group offer and affiliate, then create key code
                if (mysqli_num_rows($result) == 0) {
                    $query =
                        "INSERT INTO key_codes (
                            code,
                            offer_code,
                            enabled,
                            user,
                            timestamp)
                        VALUES (
                            '" . escape($affiliate_code) . "',
                            '" . escape($offer['code']) . "',
                            '1',
                            '" . $user['id'] . "',
                            UNIX_TIMESTAMP())";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }

        log_activity("contact ($_POST[first_name] $_POST[last_name]) was modified", $_SESSION['sessionusername']);
        
        // if there is not a send to or the send to is the view contacts screen, then add notice
        if ((isset($_REQUEST['send_to']) == FALSE) || (mb_strpos($_REQUEST['send_to'], 'view_contacts.php') !== FALSE)) {
            $liveform_view_contacts = new liveform('view_contacts');
            $liveform_view_contacts->add_notice('The contact has been saved.');
        }
    }
    
    // If there is a send to value then send user back to that screen
    if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php');
    }
}
?>
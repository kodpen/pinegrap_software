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

if (!$_POST) {
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
    
    // loop through all contact groups and put in array if the user has access to it
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
    
    // loop through and output the contact groups
    foreach ($contact_groups as $key => $contact_group) {
        //if this is the first time the loop has ran then output an opening table cell and opening table tags
        if ($contact_group_counter == 1) {
            $output_contact_groups .= 
                '<td style="width: 33%">
                    <table class="field">';
        }
        
        // if contact group has email subscription turned on, prepare opt-in field
        if ($contact_group['email_subscription'] == 1) {
            $contact_group_onclick = ' onclick="show_or_hide_contact_group_opt_in(' . $contact_group['id'] . ')"';
            $output_opt_in = '<input type="checkbox" name="contact_group_opt_in_' . $contact_group['id'] . '" id="contact_group_opt_in_' . $contact_group['id'] . '" value="1" checked="checked" class="checkbox" /><label for="contact_group_opt_in_' . $contact_group['id'] . '"> Opt-In</label>';
        } else {
            $contact_group_onclick = '';
            $output_opt_in = '&nbsp;';
        }
        
        $output_contact_groups .=
            '<tr>
                <td><input type="checkbox" name="contact_group_' . $contact_group['id'] . '" id="contact_group_' . $contact_group['id'] . '" value="1" class="checkbox"' . $contact_group_onclick . ' /><label for="contact_group_' . $contact_group['id'] . '"> ' . h($contact_group['name']) . '</label></td>
                <td id="contact_group_opt_in_cell_' . $contact_group['id'] . '" style="padding-left: 10px; display: none">' . $output_opt_in . '</td>
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
        $output_affiliate =
            '<h2 style="margin-bottom: 0.35em">Affiliate Information</h2>
            <table class="field">
                <tr>
                    <td>Approved:</td>
                    <td><input type="checkbox" name="affiliate_approved" value="1" class="checkbox" /></td>
                </tr>
                <tr>
                    <td>Affiliate Name:</td>
                    <td><input name="affiliate_name" type="text" size="30" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>Affiliate Code:</td>
                    <td><input name="affiliate_code" type="text" size="30" maxlength="100" /> (leave blank to automatically generate code)</td>
                </tr>
                <tr>
                    <td>Commission Rate:</td>
                    <td><input name="affiliate_commission_rate" type="text" size="6" maxlength="100" /> % (leave blank for default: ' . AFFILIATE_DEFAULT_COMMISSION_RATE . '%)</td>
                </tr>
            </table>';
    }

    print
        output_header() . '
        <div id="subnav">
            <h1>[new contact]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Contact</h1>
            <div class="subheading">Create a new contact, subscriber, unregistered member, or unapproved affiliate, and add them to any of my contact groups.</div>
            <form name="form" action="add_contact.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2" style="padding-right: 1em"><h2>New Contact\'s Name</h2></th>
                        <th colspan="2"><h2>New Contact\'s Subscriber Information</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Salutation:</td>
                        <td style="padding-right: 20px"><input name="salutation" type="text" size="30" maxlength="50" tabindex="1" /></td>
                        <td style="padding-right: 5px">Email: </td>
                        <td><input name="email_address" type="email" size="30" maxlength="100" tabindex="6"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">First Name:</td>
                        <td style="padding-right: 20px"><input name="first_name" type="text" size="30" maxlength="50" tabindex="2"></td>
                        <td style="padding-right: 5px"><label for="opt_in">Opt-In:</label></td>
                        <td><input type="checkbox" id="opt_in" name="opt_in" value="1" class="checkbox" checked="checked" tabindex="7"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Last Name:</td>
                        <td style="padding-right: 20px"><input name="last_name" type="text" size="30" maxlength="50" tabindex="3"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Suffix:</td>
                        <td style="padding-right: 20px"><input name="suffix" type="text" size="30" maxlength="50" tabindex="4" /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Nickname:</td>
                        <td style="padding-right: 20px"><input name="nickname" type="text" size="30" maxlength="50" tabindex="5"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th colspan="2" style="padding-right: 1em"><h2>New Contact\'s Work Information</h2></th>
                        <th colspan="2"><h2>New Contact\'s Home Information</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Company:</td>
                        <td style="padding-right: 20px"><input name="company" type="text" size="30" maxlength="50" tabindex="8"></td>
                        <td style="padding-right: 5px">Home Address 1: </td>
                        <td><input name="home_address_1" type="text" size="30" maxlength="50" tabindex="21"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Title:</td>
                        <td style="padding-right: 20px"><input name="title" type="text" size="30" maxlength="50" tabindex="9"></td>
                        <td style="padding-right: 5px">Home Address 2: </td>
                        <td><input name="home_address_2" type="text" size="30" maxlength="50" tabindex="22"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Department:</td>
                        <td style="padding-right: 20px"><input name="department" type="text" size="30" maxlength="50" tabindex="10"></td>
                        <td style="padding-right: 5px">Home City: </td>
                        <td><input name="home_city" type="text" size="30" maxlength="50" tabindex="23"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Office Location:</td>
                        <td style="padding-right: 20px"><input name="office_location" type="text" size="30" maxlength="50" tabindex="11"></td>
                        <td style="padding-right: 5px">Home State: </td>
                        <td><input name="home_state" type="text" size="30" maxlength="50" tabindex="24"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Address 1: </td>
                        <td style="padding-right: 20px"><input name="business_address_1" type="text" size="30" maxlength="50" tabindex="12"></td>
                        <td style="padding-right: 5px">Home Country: </td>
                        <td><input name="home_country" type="text" size="30" maxlength="50" tabindex="25"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Address 2: </td>
                        <td style="padding-right: 20px"><input name="business_address_2" type="text" size="30" maxlength="50" tabindex="13"></td>
                        <td style="padding-right: 5px">Home Zip Code: </td>
                        <td><input name="home_zip_code" type="text" size="30" maxlength="10" tabindex="26"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business City: </td>
                        <td style="padding-right: 20px"><input name="business_city" type="text" size="30" maxlength="50" tabindex="14"></td>
                        <td style="padding-right: 5px">Home Phone: </td>
                        <td><input name="home_phone" type="text" size="30" maxlength="50" tabindex="27"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business State: </td>
                        <td style="padding-right: 20px"><input name="business_state" type="text" size="30" maxlength="50" tabindex="15"></td>
                        <td style="padding-right: 5px">Mobile Phone: </td>
                        <td><input name="mobile_phone" type="text" size="30" maxlength="50" tabindex="28"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Country: </td>
                        <td style="padding-right: 20px"><input name="business_country" type="text" size="30" maxlength="50" tabindex="16"></td>
                        <td style="padding-right: 5px">Home Fax: </td>
                        <td><input name="home_fax" type="text" size="30" maxlength="50" tabindex="29"></td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Zip Code: </td>
                        <td style="padding-right: 20px"><input name="business_zip_code" type="text" size="30" maxlength="10" tabindex="17"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Phone:</td>
                        <td><input name="business_phone" type="text" size="30" maxlength="50" tabindex="18"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Business Fax: </td>
                        <td><input name="business_fax" type="text" size="30" maxlength="50" tabindex="19"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Website: </td>
                        <td><input name="website" type="text" size="30" maxlength="255" tabindex="20" /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th colspan="4"><h2>Additional Notes</h2></th>
                    </tr>
                    <tr>
                        <td style="padding-right: 5px">Lead Source: </td>
                        <td><input name="lead_source" type="text" size="30" maxlength="50" tabindex="30" /></td>
                    </tr>
                    <tr>
                        <td>Description: </td>
                        <td colspan="3"><textarea name="description" cols="50" rows="5" style="width: 96%" tabindex="31"></textarea></td>
                    </tr>
                </table>
                <h2 style="margin-bottom: 0.35em">Membership Access</h2>
                <table class="field">
                    <tr>
                        <td>' . h(MEMBER_ID_LABEL) . ':</td>
                        <td><input name="member_id" type="text" size="30" />&nbsp;&nbsp; (leave blank for no membership)</td>
                    </tr>
                    <tr>
                        <td>Expiration Date:</td>
                        <td>
                            <input id="expiration_date" name="expiration_date" type="text" size="10" />&nbsp;&nbsp; (leave blank for lifetime membership)
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
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

} else {

    validate_token_field();
    
    $_POST['email_address'] = trim($_POST['email_address']);
    
    // if an e-mail address was entered
    if ($_POST['email_address']) {
        // validate e-mail address
        if (validate_email_address($_POST['email_address']) == FALSE) {
            output_error('The email address is invalid. <a href="javascript:history.go(-1);">Go back</a>.');
        }
    }
    
    if (AFFILIATE_PROGRAM == true) {
        $affiliate_code = $_POST['affiliate_code'];
        
        // if an affiliate code was entered check to see if affiliate code is already in use
        if ($affiliate_code) {
            $query = "SELECT id FROM contacts WHERE affiliate_code = '" . escape($affiliate_code) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            if (mysqli_num_rows($result) > 0) {
                output_error('That affiliate code is already in use. Please use a different code. <a href="javascript:history.go(-1);">Go back</a>.');
            }
        }
        
        // if affiliate is approved and affiliate code is blank, generate affiliate code
        if ($_POST['affiliate_approved'] && (!$affiliate_code)) {
            $affiliate_code = generate_affiliate_code();
        }
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
    
    // if user has user role, then check to make sure that at least one group was selected for contact
    if ($user['role'] == 3) {
        // assume no contact groups were selected, until we find out otherwise
        $contact_group_selected = false;
        
        // loop through all contact groups
        foreach ($contact_groups as $contact_group) {
            // if contact group was selected for contact to be added to and user has access to contact group, take note
            if (($_POST['contact_group_' . $contact_group['id']] == 1) && (validate_contact_group_access($user, $contact_group['id']) == true)) {
                $contact_group_selected = true;
                break;
            }
        }
        
        // if no contact groups were selected, then output error
        if ($contact_group_selected == false) {
            output_error('Please select at least one contact group for the contact. <a href="javascript:history.go(-1);">Go back</a>.');
        }
    }

    $query =
        "INSERT INTO contacts (
            salutation,
            first_name,
            last_name,
            suffix,
            nickname,
            company,
            title,
            department,
            office_location,
            business_address_1,
            business_address_2,
            business_city,
            business_state,
            business_country,
            business_zip_code,
            business_phone,
            business_fax,
            home_address_1,
            home_address_2,
            home_city,
            home_state,
            home_country,
            home_zip_code,
            home_phone,
            home_fax,
            mobile_phone,
            email_address,
            website,
            lead_source,
            opt_in,
            description,
            user,
            timestamp,
            member_id,
            expiration_date,
            affiliate_approved,
            affiliate_name,
            affiliate_code,
            affiliate_commission_rate)
        VALUES (
            '" . escape($_POST['salutation']) . "',
            '" . escape($_POST['first_name']) . "',
            '" . escape($_POST['last_name']) . "',
            '" . escape($_POST['suffix']) . "',
            '" . escape($_POST['nickname']) . "',
            '" . escape($_POST['company']) . "',
            '" . escape($_POST['title']) . "',
            '" . escape($_POST['department']) . "',
            '" . escape($_POST['office_location']) . "',
            '" . escape($_POST['business_address_1']) . "',
            '" . escape($_POST['business_address_2']) . "',
            '" . escape($_POST['business_city']) . "',
            '" . escape($_POST['business_state']) . "',
            '" . escape($_POST['business_country']) . "',
            '" . escape($_POST['business_zip_code']) . "',
            '" . escape($_POST['business_phone']) . "',
            '" . escape($_POST['business_fax']) . "',
            '" . escape($_POST['home_address_1']) . "',
            '" . escape($_POST['home_address_2']) . "',
            '" . escape($_POST['home_city']) . "',
            '" . escape($_POST['home_state']) . "',
            '" . escape($_POST['home_country']) . "',
            '" . escape($_POST['home_zip_code']) . "',
            '" . escape($_POST['home_phone']) . "',
            '" . escape($_POST['home_fax']) . "',
            '" . escape($_POST['mobile_phone']) . "',
            '" . escape($_POST['email_address']) . "',
            '" . escape($_POST['website']) . "',
            '" . escape($_POST['lead_source']) . "',
            '" . escape($_POST['opt_in']) . "',
            '" . escape($_POST['description']) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . escape($_POST['member_id']) . "',
            '" . escape(prepare_form_data_for_input($_POST['expiration_date'], 'date')) . "',
            '" . escape($_POST['affiliate_approved']) . "',
            '" . escape($_POST['affiliate_name']) . "',
            '" . escape($affiliate_code) . "',
            '" . escape($_POST['affiliate_commission_rate']) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    
    $contact_id = mysqli_insert_id(db::$con);

    // If this contact has an email address, then update opt-in status for other contacts
    // with this same email address, so the opt-in status is the same for all.
    if ($_POST['email_address']) {
        db(
            "UPDATE contacts SET opt_in = '" . e($_POST['opt_in']) . "'
            WHERE email_address = '" . e($_POST['email_address']) . "'");
    }
    
    // loop through all contact groups
    foreach ($contact_groups as $contact_group) {
        // if contact group was selected for contact to be added to and user has access to contact group, add contact to contact group
        if (($_POST['contact_group_' . $contact_group['id']] == 1) && (validate_contact_group_access($user, $contact_group['id']) == true)) {
            $query =
                "INSERT INTO contacts_contact_groups_xref (
                    contact_id,
                    contact_group_id)
                VALUES (
                    '" . $contact_id . "',
                    '" . $contact_group['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if contact group has email subscription turned on, add opt-in selection
            if ($contact_group['email_subscription'] == 1) {
                $query =
                    "INSERT INTO opt_in (
                        contact_id,
                        contact_group_id,
                        opt_in)
                    VALUES (
                        '" . $contact_id . "',
                        '" . $contact_group['id'] . "',
                        '" . escape($_POST['contact_group_opt_in_' . $contact_group['id']]) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }
    
    // if affiliate program is enabled and there is a group offer and the affiliate has been approved, then determine if we need to add a key code for group offer for this affiliate
    if ((AFFILIATE_PROGRAM == TRUE) && (AFFILIATE_GROUP_OFFER_ID != 0) && ($_POST['affiliate_approved'] == 1)) {
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

    log_activity("contact ($_POST[first_name] $_POST[last_name]) was created", $_SESSION['sessionusername']);

    $liveform_view_contacts = new liveform('view_contacts');
    $liveform_view_contacts->add_notice('The contact has been created.');

    // If there is a send to value then send user back to that screen
    if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php');
    }
}
?>
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
           name
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
    $number_of_contact_groups_per_cell = ceil(count($contact_groups)/3);
    
    // loop through and output the contact groups
    foreach ($contact_groups as $key => $contact_group) {
        //if this is the first time the loop has ran then output an opening table cell and opening table tags
        if ($contact_group_counter == 1) {
            $output_contact_groups .= 
                '<td style="width: 33%">
                    <table class="field">';
        }
        
        $output_contact_groups .= '<div style="padding: .5em"><input type="checkbox" name="contact_group_' . $contact_group['id'] . '" id="contact_group_' . $contact_group['id'] . '" value="1" class="checkbox" /><label for="contact_group_' . $contact_group['id'] . '"> ' . h($contact_group['name']) . '</label></div>';
        
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
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new contacts]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Import Contacts</h1>
            <div class="subheading">Import contacts into any of my contact groups.</div>
            <form enctype="multipart/form-data" action="import_contacts.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Select Formatted Text File to Upload</h2></th>
                    </tr>
                    <tr>
                        <td>CSV File:</td>
                        <td><input name="file" type="file" size="60" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Import Options</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Import Mode:</td>
                        <td>
                            <input type="radio" name="import_mode" id="import_all_contacts" value="import_all_contacts" checked="checked" class="radio" /><label for="import_all_contacts">Import all contacts</label><br />
                            <input type="radio" name="import_mode" id="only_import_unique_contacts" value="only_import_unique_contacts" class="radio" /><label for="only_import_unique_contacts">Only import contacts with unique email addresses</label>
                        </td>
                    </tr>
                </table>
                <h2 style="margin-bottom: .75em">Select Contact Groups to Import into</h2>
                <table class="field" style="width: 100%">
                    <tr>
                        ' . $output_contact_groups . '
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_import" value="Import" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

} else {
    validate_token_field();
    
    // if no file was uploaded
    if (!$_FILES['file']['name']) {
        output_error('Please select a file. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // Fix Mac line-ending issue.
    ini_set('auto_detect_line_endings', true);

    // get file handle for uploaded CSV file
    $handle = fopen($_FILES['file']['tmp_name'], "r");
    // get column names from first row of CSV file
    $columns = fgetcsv($handle, 100000, ",");

    // if file is empty
    if (!$columns) {
        output_error('The file was empty. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // create array with column field names
    foreach ($columns as $key => $value) {
        $column_names[] = convert_column_name($value);
    }

    // foreach column field name
    foreach ($column_names as $key => $value) {
        // if the column is invalid, remove from column list
        if ($value === FALSE) {
            unset($column_names[$key]);
        }

        // if column is email_address, then store key location for email_address
        if ($value == 'email_address') {
            $email_address_key = $key;
        }
    }

    // build list of column names for database query
    foreach ($column_names as $key => $value) {
        $column_list .= "$value, ";
    }
    $column_list .= 'user, timestamp';
    
    // get all contact groups
    $query = "SELECT id FROM contact_groups";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contact_groups = array();
    
    // loop through all contact groups
    while ($row = mysqli_fetch_assoc($result)) {
        // if contact group was checked and user has access to contact group, add contact group to array
        if (($_POST['contact_group_' . $row['id']] == 1) && (validate_contact_group_access($user, $row['id']) == true)) {
            $contact_groups[] = $row;
        }
    }
    
    // if user has a user role and there are no contact groups to import contact into, then output error
    if (($user['role'] == 3) && (!$contact_groups)) {
        output_error('Please select at least one contact group for the contacts to be imported into. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $imported_contacts = 0;

    // loops through all rows of data in CSV file
    while ($row = fgetcsv($handle, 100000, ",")) {
        // Assume that this line does not have a value,
        // until we find out otherwise.
        $line_has_value = false;

        // Loop through the columns for this line,
        // in order to determine if at least one has a value.
        foreach ($row as $value) {
            // If the value is not blank, then this line has at least one value,
            // so remember that and break out of loop.
            if (trim($value) != '') {
                $line_has_value = true;
                break;
            }
        }

        // If this line has at least one value, then continue to process it.
        if ($line_has_value == true) {
            $contact_id = 0;
            
            switch ($_POST['import_mode']) {
                case 'import_all_contacts':
                    // if e-mail address data was supplied in CSV file and e-mail address is invalid, clear e-mail address value
                    if ((isset($email_address_key) == true) && (validate_email_address(trim($row[$email_address_key])) == false)) {
                        $row[$email_address_key] = '';
                    }
                    
                    $data_list = '';
                    
                    // foreach field
                    foreach ($column_names as $key => $value) {
                        // create value list
                        $value = escape(trim($row[$key]));
                        $data_list .= "'$value', ";
                    }

                    $data_list .= "'$user[id]', UNIX_TIMESTAMP()";
                    
                    // insert row of data into database
                    $query = "INSERT INTO contacts ($column_list) VALUES ($data_list)";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $contact_id = mysqli_insert_id(db::$con);
                    
                    // if e-mail address exists
                    if (trim($row[$email_address_key]) != '') {
                        // check to see if contact should be opted out
                        $query = "SELECT id FROM contacts WHERE (opt_in = 0) AND (email_address = '" . escape(trim($row[$email_address_key])) . "') AND (id != '$contact_id')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if a contact was found
                        if (mysqli_num_rows($result) > 0) {
                            $query = "UPDATE contacts SET opt_in = 0 WHERE id = '$contact_id'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                    
                    $imported_contacts++;
                    
                    break;
                    
                case 'only_import_unique_contacts':
                    // if e-mail address data was supplied in CSV file
                    if (isset($email_address_key)) {
                        // if e-mail address is not valid, set e-mail address to empty
                        if (validate_email_address(trim($row[$email_address_key])) == false) {
                            $row[$email_address_key] = '';
                        }

                        // if e-mail address is not empty
                        if (trim($row[$email_address_key]) != '') {
                            // query database to determine if e-mail address is already in use
                            $query = "SELECT id FROM contacts WHERE email_address = '" . escape(trim($row[$email_address_key])) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // if a contact was not found
                            if (mysqli_num_rows($result) == 0) {
                                $unique = true;
                            } else {
                                $unique = false;
                            }
                            
                        // else e-mail address is empty
                        } else {
                            $unique = true;
                        }
                    }

                    // if contact is unique
                    if ($unique == true) {
                        $data_list = '';
                        
                        // foreach field
                        foreach ($column_names as $key => $value) {
                            // create value list
                            $value = escape(trim($row[$key]));
                            $data_list .= "'$value', ";
                        }

                        $data_list .= "'$user[id]', UNIX_TIMESTAMP()";
                        
                        // insert row of data into database
                        $query = "INSERT INTO contacts ($column_list) VALUES ($data_list)";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $contact_id = mysqli_insert_id(db::$con);

                        $imported_contacts++;
                    }
                    
                    break;
            }
            
            // if contact was created, assign contact to contact groups
            if ($contact_id) {
                // loop through all checked contact groups
                foreach ($contact_groups as $contact_group) {
                    // assign contact to this contact group
                    $query =
                        "INSERT INTO contacts_contact_groups_xref (
                            contact_id,
                            contact_group_id)
                        VALUES (
                            '" . $contact_id . "',
                            '" . $contact_group['id'] . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
        }
    }
    fclose($handle);
    
    log_activity(number_format($imported_contacts) . ' contacts were imported', $_SESSION['sessionusername']);
    
    $liveform_view_contacts = new liveform('view_contacts');
    $liveform_view_contacts->add_notice(number_format($imported_contacts) . ' contacts were imported.');
    
    // If there is a send to value then send user back to that screen
    if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php');
    }
}

function convert_column_name($column_name)
{
    // convert column name to lowercase
    $column_name = mb_strtolower($column_name);
    // remove spaces from column name
    $column_name = str_replace(' ', '', $column_name);
    // remove underscores from column name
    $column_name = str_replace('_', '', $column_name);
    // remove dashes from column name
    $column_name = str_replace('-', '', $column_name);

    switch ($column_name) {
        case 'salutation':
            return('salutation');
            break;

        case 'firstname':
        case 'first':
        case 'name':
            return('first_name');
            break;

        case 'lastname':
        case 'last':
            return('last_name');
            break;

        case 'suffix':
            return('suffix');
            break;

        case 'nickname':
        case 'alias':
            return('nickname');
            break;

        case 'company':
        case 'organization':
            return('company');
            break;

        case 'title':
        case 'jobtitle':
        case 'position':
            return('title');
            break;

        case 'department':
            return('department');
            break;

        case 'officelocation':
        case 'office':
        case 'location':
            return('office_location');
            break;

        case 'businessaddress1':
        case 'businessaddress':
        case 'businessstreet1':
        case 'businessstreet':
        case 'address1':
        case 'address':
        case 'street':
            return('business_address_1');
            break;

        case 'businessaddress2':
        case 'businessstreet2':
        case 'address2':
        case 'street2':
            return('business_address_2');
            break;

        case 'businesscity':
        case 'city':
            return('business_city');
            break;

        case 'businessstate':
        case 'state':
            return('business_state');
            break;

        case 'businesscountry':
        case 'businesscountry/region':
        case 'country':
            return('business_country');
            break;

        case 'businesszipcode':
        case 'businesspostalcode':
        case 'zipcode':
        case 'zip':
        case 'postalcode':
        case 'postal':
            return('business_zip_code');
            break;

        case 'businessphone':
        case 'businessphonenumber':
        case 'phone':
        case 'phonenumber':
            return('business_phone');
            break;

        case 'businessfax':
        case 'businessfaxnumber':
        case 'fax':
        case 'faxnumber':
            return('business_fax');
            break;

        case 'homeaddress1':
        case 'homeaddress':
        case 'homestreet1':
        case 'homestreet':
            return('home_address_1');
            break;

        case 'homeaddress2':
        case 'homestreet2':
            return('home_address_2');
            break;

        case 'homecity':
            return('home_city');
            break;

        case 'homestate':
            return('home_state');
            break;

        case 'homecountry':
        case 'homecountry/region':
            return('home_country');
            break;

        case 'homezipcode':
        case 'homezip':
        case 'homepostalcode':
        case 'homepostal':
            return('home_zip_code');
            break;

        case 'homephone':
        case 'homephonenumber':
            return('home_phone');
            break;

        case 'homefax':
        case 'homefaxnumber':
            return('home_fax');
            break;

        case 'mobilephone':
        case 'mobilephonenumber':
        case 'mobile':
        case 'cellphone':
        case 'cellphonenumber':
        case 'cell':
            return('mobile_phone');
            break;

        case 'emailaddress':
        case 'email':
            return('email_address');
            break;

        case 'website':
        case 'web':
        case 'site':
        case 'webpage':
        case 'url':
        case 'businesswebpage':
            return('website');
            break;

        case 'leadsource':
        case 'source':
            return('lead_source');
            break;

        case 'optin':
            return('opt_in');
            break;

        case 'description':
        case 'notes':
        case 'note':
        case 'comments':
        case 'comment':
            return('description');
            break;

        case 'memberid':
            return('member_id');
            break;

        case 'expirationdate':
            return('expiration_date');
            break;
            
        case 'affiliateapproved':
            return('affiliate_approved');
            break;
            
        case 'affiliatename':
            return('affiliate_name');
            break;

        case 'affiliatecode':
            return('affiliate_code');
            break;
            
        case 'affiliatecommissionrate':
            return('affiliate_commission_rate');
            break;
    }
    return FALSE;
}
?>
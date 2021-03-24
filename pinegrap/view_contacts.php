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



$liveform = new liveform('view_contacts');



// if user has a user role and if the all duplicate contacts filter is on, then user does not have access to this filter so output error

if (($user['role'] == 3) && ($_GET['filter'] == 'all_duplicate_contacts')) {

    log_activity("access denied to all duplicate contacts view", $_SESSION['sessionusername']);

    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');

}



// store all values collected in request to session

foreach ($_REQUEST as $key => $value) {

    // if the value is a string or this is the contact groups array then add it to the session

    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,

    // for certain php.ini settings

    if (

        (is_string($value) == TRUE)

        || ($key == 'contact_groups')

    ) {

        // if the value is a string, then trim it

        if (is_string($value) == TRUE) {

            $value = trim($value);

        }



        $_SESSION['software']['view_contacts'][$key] = $value;

    }

}



// if user has a user role, verify that user has access to contact groups that user has selected

if ($user['role'] == 3) {

    // if contact group has been selected and selected contact group is not [All]

    if (($_SESSION['software']['view_contacts']['contact_group']) && ($_SESSION['software']['view_contacts']['contact_group'] != '[All]')) {

        // if user does not have access to contact group, then unset contact group selection

        if (validate_contact_group_access($user, $_SESSION['software']['view_contacts']['contact_group']) == false) {

            unset($_SESSION['software']['view_contacts']['contact_group']);

        }

    }



    // if contact groups have been selected in advanced filters

    if ($_SESSION['software']['view_contacts']['contact_groups']) {

        // loop through all selected contact groups in order to check if user has access to contact groups

        foreach ($_SESSION['software']['view_contacts']['contact_groups'] as $key => $value) {

            // if user does not have access to contact group, then unset contact group selection

            if (validate_contact_group_access($user, $value) == false) {

                unset($_SESSION['software']['view_contacts']['contact_groups'][$key]);

            }

        }

    }

}



// If there is a filter in the query string, save it

if (isset($_GET['filter']) == true) {

    $filter = $_GET['filter'];



// else set the filter to default

} else {

    $filter = 'default';

}



$filter_for_links = '&filter=' . $filter;

$output_filter_for_links = h($filter_for_links);



// build filters array

$filters_in_array = 

    array(

        'all_my_contacts'=>' All My Contacts',

        'my_subscribers'=>'My Subscribers',

        'my_affiliates'=>'My Affiliates',

        'my_customers'=>'My Customers',

        'my_members'=>'My Members',

        'my_active_members'=>'My Active Members',

        'my_expired_members'=>'My Expired Members',

        'my_unregistered_members'=>'My Unregistered Members',

        'my_contacts_by_user'=>'My Contacts by User',

        'my_contacts_by_business_address'=>'My Contacts by Business Address',

        'my_contacts_by_home_address'=>'My Contacts by Home Address'

    );



// If user is a manager or above add the all duplicate contacts filter to the array

if ($user['role'] < 3) {

    $filters_in_array['all_duplicate_contacts'] = 'All Duplicate Contacts';

}



// If the user clicked on the clear button

if (isset($_GET['clear']) == true) {

    $_SESSION['software']['view_contacts']['query'] = '';

}



// if there is a search query, then prepare to output clear button

if ((isset($_SESSION['software']['view_contacts']['query']) == true) && ($_SESSION['software']['view_contacts']['query'] != '')) {

    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';

}



// if show contact groups is true then set the session to show the groups

if ($_GET['show_contact_groups'] == 'true') {

    $_SESSION['software']['view_contacts']['show_contact_groups'] = true;



// else if show contact groups is false, then update the session to hide the groups

} elseif (($_GET['show_contact_groups'] == 'false') && ($_GET['show_contact_groups'] != '')) {

    $_SESSION['software']['view_contacts']['show_contact_groups'] = false;

}



$output_organize_selected_button = '';



// if this is not the all duplicates view and if the user has selected to show contact groups, then output the organize selected button

if (($filter != 'all_duplicate_contacts') && ($_SESSION['software']['view_contacts']['show_contact_groups'] == true)) {

    $output_organize_selected_button = '<input type="button" value="Organize Selected" class="submit-secondary" onclick="window.open(\'organize_contacts.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=450,height=487\'); document.form.action.value = \'organize\';" />&nbsp;&nbsp;&nbsp;';

}



// Switch between the subnav filters

switch ($filter) {

    case 'my_subscribers':

        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';

        

        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }

        

        // Set the query filter.

        $where .= ' ((contacts.email_address != "") AND (contacts.opt_in = "1"))';

        

        // Change the heading and subheading.

        $heading = 'My Subscribers';

        $subheading = 'All my contacts that can be recipients of an email campaign.';

        break;

        

    case 'my_affiliates':

        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "(contacts.affiliate_approved = '1')";



        // Change the heading and subheading.

        $heading = 'My Affiliates';

        $subheading = 'All my contacts with approved affiliate status that can receive order commissions for referring visitors.';

        break;



    case 'my_customers':

        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Join table

        $join_table = 'LEFT JOIN orders ON orders.contact_id = contacts.id';



        // Set the query filter.

        $where .= "(orders.status != 'incomplete')";



        // Change the heading and subheading.

        $heading = 'My Customers';

        $subheading = 'All my contacts who have submitted orders.';

        break;



    case 'my_members':



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "(contacts.member_id != '')";



        // Set membership filter and label

        $membership_filter = true;

        $membership_status_label = 'Member';



        // Change the heading and subheading.

        $heading = 'My Members';

        $subheading = 'All my contacts with a member id.';

        break;



    case 'my_active_members':



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "((contacts.member_id != '') AND ((contacts.expiration_date >= CURRENT_DATE()) OR (contacts.expiration_date = '0000-00-00')))";



        // Set membership filter and label

        $membership_filter = true;

        $membership_status_label = 'Active Member';



        // Change the heading and subheading.

        $heading = 'My Active Members';

        $subheading = 'All my contacts with a member id and a future expiration date.';

        break;



    case 'my_expired_members':



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "((contacts.member_id != '') AND (contacts.expiration_date < CURRENT_DATE()) AND (contacts.expiration_date != '0000-00-00'))";



        // Set membership filter and label

        $membership_filter = true;

        $membership_status_label = 'Expired Member';



        // Change the heading and subheading.

        $heading = 'My Expired Members';

        $subheading = 'All my contacts with a member id and a lapsed expiration date.';

        break;



    case 'my_unregistered_members':



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "((contact_user.user_contact IS NULL) AND (contacts.member_id != '') AND ((contacts.expiration_date >= CURRENT_DATE()) OR (contacts.expiration_date = '0000-00-00')))";



        // Set membership filter and label

        $membership_filter = true;

        $membership_status_label = 'Unregistered Member';



        // Change the heading and subheading.

        $heading = 'My Unregistered Members';

        $subheading = 'All my contacts with a member id and valid expiration date, but cannot login due to a missing user account.';

        break;



    case 'my_contacts_by_user':



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }





        // Set the query filter.

        $where .= "(contacts.id = contact_user.user_contact)";



        // Change the heading and subheading.

        $heading = 'My Contacts by User';

        $subheading = 'All my contacts that have also registered through the website.';

        break;

        

    case 'my_contacts_by_business_address':

        

        $sql_columns = 

            'contacts.business_address_1 as address_1,

            contacts.business_address_2 as address_2,

            contacts.business_city as city,

            contacts.business_state as state,

            contacts.business_country as country,

            contacts.business_zip_code as zip_code,';

        

        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }



        // Set the query filter.

        $where .= "((((contacts.first_name != '') AND (contacts.last_name != '')) OR (contacts.company != '')) AND ((contacts.business_address_1 != '') AND (contacts.business_city != '') AND (contacts.business_state != '') AND (contacts.business_zip_code != '')))";



        // Change the heading and subheading.

        $heading = 'My Contacts by Business Address';

        $subheading = 'All my contacts that have a business address.';

        break;

        

    case 'my_contacts_by_home_address':



        $sql_columns = 

            'contacts.home_address_1 as address_1,

            contacts.home_address_2 as address_2,

            contacts.home_city as city,

            contacts.home_state as state,

            contacts.home_country as country,

            contacts.home_zip_code as zip_code,';



        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';



        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }





        // Set the query filter.

        $where .= "((((contacts.first_name != '') AND (contacts.last_name != '')) OR (contacts.company != '')) AND ((contacts.home_address_1 != '') AND (contacts.home_city != '') AND (contacts.home_state != '') AND (contacts.home_zip_code != '')))";



        // Change the heading and subheading.

        $heading = 'My Contacts by Home Address';

        $subheading = 'All my contacts that have a home address.';

        break;

        

    case 'all_duplicate_contacts':

        $sql_columns = 

            'contacts.business_address_1,

            contacts.business_address_2,

            contacts.business_city,

            contacts.business_state,

            contacts.business_country,

            contacts.business_zip_code,

            contacts.home_address_1,

            contacts.home_address_2,

            contacts.home_city,

            contacts.home_state,

            contacts.home_country,

            contacts.home_zip_code,';

        

        $all_email_addresses = array();

        

        // get all e-mail addresses from database

        $query = "SELECT email_address FROM contacts WHERE email_address != '' ORDER BY email_address ASC";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        

        // loop through the results in order to add them to array (we convert e-mail address to lowercase in order to prevent case comparison issues later)

        while ($row = mysqli_fetch_assoc($result)) {

            $all_email_addresses[] = mb_strtolower($row['email_address']);

        }

        

        $checked_email_addresses = array();

        $duplicate_email_addresses = array();

        

        // loop through all the email addresses to see if there are duplicates

        foreach ($all_email_addresses as $email_address) {

            // if the email address is in the checked array then it is a duplicate so add it to the duplicate email addresses array

            if (in_array($email_address, $checked_email_addresses) == TRUE) {

                $duplicate_email_addresses[] = $email_address;

            }

            

            // add this email to the checked email addresses array

            $checked_email_addresses[] = $email_address;

        }

        

        // remove duplicate email addresses from array so that we can build an sql where statement

        $duplicate_email_addresses = array_unique($duplicate_email_addresses);

        

        $duplicate_email_addresses_where_statement = '';

        

        // loop through the duplicate email addresses and build sql where statement so that we get only contacts that are duplicates

        foreach ($duplicate_email_addresses as $email_address) {

            if ($duplicate_email_addresses_where_statement != '') {

                $duplicate_email_addresses_where_statement .= ' OR ';

            }

            

            $duplicate_email_addresses_where_statement .= "(contacts.email_address = '" . escape($email_address) . "')";

        }

        

        // If where is blank

        if ($where == '') {

            $where .= ' WHERE ';

        

        // else where is not blank, so add and

        } else {

            $where .= ' AND ';

        }

        

        // if there are duplicate e-mail addresses to get then set the where statement to get them

        if ($duplicate_email_addresses_where_statement != '') {

            $where .= "(" . $duplicate_email_addresses_where_statement . ")";

        

        // else there are not any duplicate e-mail addresses so set the where statement to get a blank id, that way no contacts will be found for this screen

        } else {

            $where .= "(contacts.id = '')";

        }

        

        // Change the heading and subheading.

        $heading = 'All Duplicate Contacts';

        $subheading = 'All contacts that have a duplicate email address.';

        break;



    case'all_my_contacts':

    default:

        // Change the heading and subheading.

        $heading = 'All My Contacts';

        $subheading = 'All contacts, subscribers, members, and affiliates that I can edit.';

        break;

}



$my_contacts = 0;

$all_contacts = 0;



// Get contacts based on filter.

$query =

    "SELECT

        COUNT(DISTINCT contacts.id)

    FROM contacts

    LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

    $join_table

    $where";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$row = mysqli_fetch_row($result);

$all_contacts = $row[0];



// get all contact groups (the array will be used in multiple places in this script)

$query =

    "SELECT

        id,

        name

    FROM contact_groups

    ORDER BY name";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');



$contact_groups = array();



// create contact group selection list

while ($row = mysqli_fetch_assoc($result)) {

    // if user has access to this contact group, then add contact group to array

    if (validate_contact_group_access($user, $row['id']) == true) {

        $contact_groups[] = $row;

    }

}



// If the user is a basic user

if ($user['role'] == 3) {

    // If where is blank

    if ($where == '') {

        $my_contacts_where = ' WHERE ';



    // else where is not blank, so add and

    } else {

        $my_contacts_where = $where . ' AND ';

    }



    $my_contacts_where .= '(';



    // Set loop counter to zero.

    $loop_count = 0;



    // Loop through the contact groups the user has access to.

    foreach($contact_groups as $contact_group) {



        // If the loop has ran at least once add OR.

        if ($loop_count > 0) {

            $my_contacts_where    .= ' OR ';

        }



        // Add the condition to the sql statement.

        $my_contacts_where .= '(contact_group_id = ' . $contact_group['id'] . ')';



        // Increment the counter. After once loop this doesn't matter any more.

        $loop_count++;

    }



    $my_contacts_where .= ')';



    // Get number of contacts that user has access to.

    $query = "

              SELECT

                 COUNT(DISTINCT contacts_contact_groups_xref.contact_id)

              FROM contacts

              LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

              LEFT JOIN contacts_contact_groups_xref ON contacts_contact_groups_xref.contact_id = contacts.id

              $join_table

              $my_contacts_where";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $row = mysqli_fetch_row($result);

    $my_contacts = $row[0];



// If user is manager or above.

} else {

    $my_contacts = $all_contacts;

}



// if advanced filters value was passed in the query string

if (isset($_REQUEST['advanced_filters']) == true) {

    // if advanced filters should be turned on

    if ($_REQUEST['advanced_filters'] == 'true') {

        $_SESSION['software']['view_contacts']['advanced_filters'] = true;



    // else advanced filters should be turned off

    } else {

        $_SESSION['software']['view_contacts']['advanced_filters'] = false;

    }

}



// if contact group is not set yet, set default to [All]

if (isset($_SESSION['software']['view_contacts']['contact_group']) == false) {

    $_SESSION['software']['view_contacts']['contact_group'] = '[All]';

}



// if filter is set to all dupliate contacts then set the advanced filter varable to false so that none of the logic is ran for the filters,

// and set the contact groups filter to all so that all contact groups are used

if ($filter == 'all_duplicate_contacts') {

    $advanced_filters = FALSE;

    $contact_groups_filter = '[All]';

    

// else use the session values

} else {

    $advanced_filters = $_SESSION['software']['view_contacts']['advanced_filters'];

    $contact_groups_filter = $_SESSION['software']['view_contacts']['contact_group'];

}



// if advanced filters are on and contact groups have not already been set in session, set default for contact groups in advanced filters

if (($advanced_filters == true) && (isset($_SESSION['software']['view_contacts']['contact_groups']) == false)) {

    // if contact group is set to all, prepare contact groups so all will be checked

    if ($contact_groups_filter == '[All]') {

        foreach ($contact_groups as $contact_group) {

            $_SESSION['software']['view_contacts']['contact_groups'][] = $contact_group['id'];

        }



    // else contact group is not set to all

    } else {

        $_SESSION['software']['view_contacts']['contact_groups'][] = $contact_groups_filter;

    }

}



$decrease_year = array();

$current_year = array();

$increase_year = array();

$decrease_month = array();

$current_month = array();

$increase_month = array();

$decrease_week = array();

$current_week = array();

$increase_week = array();

$decrease_day = array();

$current_day = array();

$increase_day = array();



$output_date_range_time = '';

$show_hide_date_range = '';



// if the filter is not set to all duplicate contacts, then run logic to get date range

if ($filter != 'all_duplicate_contacts') {

    // find the oldest timestamp (this will be used later in a couple of places)

    $query = "SELECT MIN(timestamp) FROM contacts";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $row = mysqli_fetch_row($result);

    $oldest_timestamp = $row[0];



    // if date has not been set in the session yet, populate start and stop days with default,

    // which is the oldest contact timestamp date to today's date

    if (isset($_SESSION['software']['view_contacts']['start_month']) == false) {

        $_SESSION['software']['view_contacts']['start_month'] = date('m', $oldest_timestamp);

        $_SESSION['software']['view_contacts']['start_day'] = date('d', $oldest_timestamp);

        $_SESSION['software']['view_contacts']['start_year'] = date('Y', $oldest_timestamp);



        $_SESSION['software']['view_contacts']['stop_month'] = date('m');

        $_SESSION['software']['view_contacts']['stop_day'] = date('d');

        $_SESSION['software']['view_contacts']['stop_year'] = date('Y');

    }



    $decrease_year['start_month'] = '01';

    $decrease_year['start_day'] = '01';

    $decrease_year['start_year'] = $_SESSION['software']['view_contacts']['start_year'] - 1;

    $decrease_year['stop_month'] = '12';

    $decrease_year['stop_day'] = '31';

    $decrease_year['stop_year'] = $_SESSION['software']['view_contacts']['start_year'] - 1;



    $current_year['start_month'] = '01';

    $current_year['start_day'] = '01';

    $current_year['start_year'] = date('Y');

    $current_year['stop_month'] = '12';

    $current_year['stop_day'] = '31';

    $current_year['stop_year'] = date('Y');



    $increase_year['start_month'] = '01';

    $increase_year['start_day'] = '01';

    $increase_year['start_year'] = $_SESSION['software']['view_contacts']['start_year'] + 1;

    $increase_year['stop_month'] = '12';

    $increase_year['stop_day'] = '31';

    $increase_year['stop_year'] = $_SESSION['software']['view_contacts']['start_year'] + 1;



    $decrease_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'] - 1, 1, $_SESSION['software']['view_contacts']['start_year']);

    $decrease_month['new_month'] = date('m', $decrease_month['new_time']);

    $decrease_month['new_year'] = date('Y', $decrease_month['new_time']);

    $decrease_month['start_month'] = $decrease_month['new_month'];

    $decrease_month['start_day'] = '01';

    $decrease_month['start_year'] = $decrease_month['new_year'];

    $decrease_month['stop_month'] = $decrease_month['new_month'];

    $decrease_month['stop_day'] = date('t', $decrease_month['new_time']);

    $decrease_month['stop_year'] = $decrease_month['new_year'];



    $current_month['new_month'] = date('m');

    $current_month['new_year'] = date('Y');

    $current_month['start_month'] = $current_month['new_month'];

    $current_month['start_day'] = '01';

    $current_month['start_year'] = $current_month['new_year'];

    $current_month['stop_month'] = $current_month['new_month'];

    $current_month['stop_day'] = date('t');

    $current_month['stop_year'] = $current_month['new_year'];



    $increase_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'] + 1, 1, $_SESSION['software']['view_contacts']['start_year']);

    $increase_month['new_month'] = date('m', $increase_month['new_time']);

    $increase_month['new_year'] = date('Y', $increase_month['new_time']);

    $increase_month['start_month'] = $increase_month['new_month'];

    $increase_month['start_day'] = '01';

    $increase_month['start_year'] = $increase_month['new_year'];

    $increase_month['stop_month'] = $increase_month['new_month'];

    $increase_month['stop_day'] = date('t', $increase_month['new_time']);

    $increase_month['stop_year'] = $increase_month['new_year'];



    $decrease_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'], $_SESSION['software']['view_contacts']['start_day'], $_SESSION['software']['view_contacts']['start_year']);

    // if start date is a Sunday, use last Sunday (add 12:00:00 to prevent a bug that results in Saturday being returned)

    if (date('l', $decrease_week['start_date_timestamp']) == 'Sunday') {

        $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['start_date_timestamp']);



    // else start date is not a Sunday, so we need to do last sunday twice (add 12:00:00 to prevent a bug that results in Saturday being returned)

    } else {

        $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['start_date_timestamp']);

        $decrease_week['new_time_start'] = strtotime('last sunday 12:00:00', $decrease_week['new_time_start']);

    }

    $decrease_week['new_time_stop'] = strtotime('Saturday', $decrease_week['new_time_start']);

    $decrease_week['start_month'] = date('m', $decrease_week['new_time_start']);

    $decrease_week['start_day'] = date('d', $decrease_week['new_time_start']);

    $decrease_week['start_year'] = date('Y', $decrease_week['new_time_start']);

    $decrease_week['stop_month'] = date('m', $decrease_week['new_time_stop']);

    $decrease_week['stop_day'] = date('d', $decrease_week['new_time_stop']);

    $decrease_week['stop_year'] = date('Y', $decrease_week['new_time_stop']);



    // if today is Sunday

    if (date('l') == 'Sunday') {

        $current_week['new_time_start'] = strtotime('Sunday');

    } else {

        $current_week['new_time_start'] = strtotime('last Sunday');

    }

    $current_week['new_time_stop'] = strtotime('Saturday', $current_week['new_time_start']);

    $current_week['start_month'] = date('m', $current_week['new_time_start']);

    $current_week['start_day'] = date('d', $current_week['new_time_start']);

    $current_week['start_year'] = date('Y', $current_week['new_time_start']);

    $current_week['stop_month'] = date('m', $current_week['new_time_stop']);

    $current_week['stop_day'] = date('d', $current_week['new_time_stop']);

    $current_week['stop_year'] = date('Y', $current_week['new_time_stop']);



    $increase_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'], $_SESSION['software']['view_contacts']['start_day'], $_SESSION['software']['view_contacts']['start_year']);

    // if start date is a Sunday

    if (date('l', $increase_week['start_date_timestamp']) == 'Sunday') {

        $increase_week['new_time_start'] = strtotime('2 Sunday', $increase_week['start_date_timestamp']);

    } else {

        $increase_week['new_time_start'] = strtotime('Sunday', $increase_week['start_date_timestamp']);

    }

    $increase_week['new_time_stop'] = strtotime('Saturday', $increase_week['new_time_start']);

    $increase_week['start_month'] = date('m', $increase_week['new_time_start']);

    $increase_week['start_day'] = date('d', $increase_week['new_time_start']);

    $increase_week['start_year'] = date('Y', $increase_week['new_time_start']);

    $increase_week['stop_month'] = date('m', $increase_week['new_time_stop']);

    $increase_week['stop_day'] = date('d', $increase_week['new_time_stop']);

    $increase_week['stop_year'] = date('Y', $increase_week['new_time_stop']);



    $decrease_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'], $_SESSION['software']['view_contacts']['start_day'] - 1, $_SESSION['software']['view_contacts']['start_year']);

    $decrease_day['new_month'] = date('m', $decrease_day['new_time']);

    $decrease_day['new_day'] = date('d', $decrease_day['new_time']);

    $decrease_day['new_year'] = date('Y', $decrease_day['new_time']);

    $decrease_day['start_month'] = $decrease_day['new_month'];

    $decrease_day['start_day'] = $decrease_day['new_day'];

    $decrease_day['start_year'] = $decrease_day['new_year'];

    $decrease_day['stop_month'] = $decrease_day['new_month'];

    $decrease_day['stop_day'] = $decrease_day['new_day'];

    $decrease_day['stop_year'] = $decrease_day['new_year'];



    $current_day['new_month'] = date('m');

    $current_day['new_day'] = date('d');

    $current_day['new_year'] = date('Y');

    $current_day['start_month'] = $current_day['new_month'];

    $current_day['start_day'] = $current_day['new_day'];

    $current_day['start_year'] = $current_day['new_year'];

    $current_day['stop_month'] = $current_day['new_month'];

    $current_day['stop_day'] = $current_day['new_day'];

    $current_day['stop_year'] = $current_day['new_year'];



    $increase_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'], $_SESSION['software']['view_contacts']['start_day'] + 1, $_SESSION['software']['view_contacts']['start_year']);

    $increase_day['new_month'] = date('m', $increase_day['new_time']);

    $increase_day['new_day'] = date('d', $increase_day['new_time']);

    $increase_day['new_year'] = date('Y', $increase_day['new_time']);

    $increase_day['start_month'] = $increase_day['new_month'];

    $increase_day['start_day'] = $increase_day['new_day'];

    $increase_day['start_year'] = $increase_day['new_year'];

    $increase_day['stop_month'] = $increase_day['new_month'];

    $increase_day['stop_day'] = $increase_day['new_day'];

    $increase_day['stop_year'] = $increase_day['new_year'];



    // get timestamps for start and stop dates

    $start_timestamp = mktime(0, 0, 0, $_SESSION['software']['view_contacts']['start_month'], $_SESSION['software']['view_contacts']['start_day'], $_SESSION['software']['view_contacts']['start_year']);

    $stop_timestamp = mktime(23, 59, 59, $_SESSION['software']['view_contacts']['stop_month'], $_SESSION['software']['view_contacts']['stop_day'], $_SESSION['software']['view_contacts']['stop_year']);



    // If where is blank

    if ($where == '') {

        $where .= ' WHERE ';



    // else where is not blank, so add and

    } else {

        $where .= ' AND ';

    }



    $where .= "(contacts.timestamp >= $start_timestamp) AND (contacts.timestamp <= $stop_timestamp)";

    

    // Output start date range time

    $output_date_range_time = h(get_month_name_from_number($_SESSION['software']['view_contacts']['start_month']) . ' ' . $_SESSION['software']['view_contacts']['start_day'] . ', ' . $_SESSION['software']['view_contacts']['start_year']);

    $output_date_range_time .= ' - ';



    // Output end date range time

    $output_date_range_time .= h(get_month_name_from_number($_SESSION['software']['view_contacts']['stop_month']) . ' ' . $_SESSION['software']['view_contacts']['stop_day'] . ', ' . $_SESSION['software']['view_contacts']['stop_year']);



// else this is the all duplicate contacts filter so hide the date range

} else {

    $show_hide_date_range = ' display: none;';

}



// if advanced filters are on, prepare SQL for checked contact groups

if ($advanced_filters == true) {

    // if at least one contact group is checked

    if (is_array($_SESSION['software']['view_contacts']['contact_groups']) == true) {

        $where_contact_groups = '';



        foreach ($_SESSION['software']['view_contacts']['contact_groups'] as $contact_group) {

            // if this is not the first contact group, then add an OR before SQL

            if ($where_contact_groups) {

                $where_contact_groups .= " OR";

            }



            // if contact group is [None]

            if ($contact_group == '[None]') {

                $where_contact_groups .= " (contacts_contact_groups_xref.contact_group_id IS NULL)";



            // else contact group is not [None]

            } else {

                $where_contact_groups .= " (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_group) . "')";

            }

        }



        if ($where_contact_groups) {

            $where .= " AND ($where_contact_groups)";

        } else {

            $where .= " AND (0 = 1)";

        }



    // else no contact groups are checked, so use SQL that will result in no contacts being found

    } else {

        $where .= " AND (0 = 1)";

    }



// else advanced filters are off, so use contact group picklist

} else {

    // if user has selected [All] and user is greater than user role, then do not add any where clause for contact group, because all contact groups are valid

    if (($contact_groups_filter == '[All]') && ($user['role'] < 3)) {

        // do nothing



    // else if user has selected [All] and user has a user role, then prepare where clause for all contact groups that user has access to

    } elseif (($contact_groups_filter == '[All]') && ($user['role'] == 3)) {

        $where_contact_groups = '';



        foreach ($contact_groups as $contact_group) {

            // if this is not the first contact group, then add an OR before SQL

            if ($where_contact_groups) {

                $where_contact_groups .= " OR";

            }



            $where_contact_groups .= " (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_group['id']) . "')";

        }



        if ($where_contact_groups) {

            $where .= " AND ($where_contact_groups)";

        } else {

            $where .= " AND (0 = 1)";

        }



    // else if user selected [None]

    } elseif ($contact_groups_filter == '[None]') {

        $where .= " AND (contacts_contact_groups_xref.contact_group_id IS NULL)";



    // else user selected a contact group

    } else {

        $where .= " AND (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_groups_filter) . "')";

    }

}



if ($_SESSION['software']['view_contacts']['query']) {

    $view_contacts_query = escape($_SESSION['software']['view_contacts']['query']);

    $where .=

        " AND (

        (contacts.first_name LIKE '%" . $view_contacts_query . "%')

        OR (contacts.last_name LIKE '%" . $view_contacts_query . "%')

        OR (contacts.nickname LIKE '%" . $view_contacts_query . "%')

        OR (contacts.company LIKE '%" . $view_contacts_query . "%')

        OR (contacts.title LIKE '%" . $view_contacts_query . "%')

        OR (contacts.department LIKE '%" . $view_contacts_query . "%')

        OR (contacts.office_location LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_address_1 LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_address_2 LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_city LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_state LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_country LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_zip_code LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_phone LIKE '%" . $view_contacts_query . "%')

        OR (contacts.business_fax LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_address_1 LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_address_2 LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_city LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_state LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_country LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_zip_code LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_phone LIKE '%" . $view_contacts_query . "%')

        OR (contacts.home_fax LIKE '%" . $view_contacts_query . "%')

        OR (contacts.mobile_phone LIKE '%" . $view_contacts_query . "%')

        OR (contacts.email_address LIKE '%" . $view_contacts_query . "%')

        OR (contacts.website LIKE '%" . $view_contacts_query . "%')

        OR (contacts.lead_source LIKE '%" . $view_contacts_query . "%')

        OR (contacts.affiliate_code LIKE '%" . $view_contacts_query . "%'))";

}



// if advanced filters are on, prepare SQL

if ($advanced_filters == true) {

    if ($_SESSION['software']['view_contacts']['salutation']) {$where .= " AND (contacts.salutation LIKE '%" . escape($_SESSION['software']['view_contacts']['salutation']) . "%')";}

    if ($_SESSION['software']['view_contacts']['first_name']) {$where .= " AND (contacts.first_name LIKE '%" . escape($_SESSION['software']['view_contacts']['first_name']) . "%')";}

    if ($_SESSION['software']['view_contacts']['last_name']) {$where .= " AND (contacts.last_name LIKE '%" . escape($_SESSION['software']['view_contacts']['last_name']) . "%')";}

    if ($_SESSION['software']['view_contacts']['suffix']) {$where .= " AND (contacts.suffix LIKE '%" . escape($_SESSION['software']['view_contacts']['suffix']) . "%')";}

    if ($_SESSION['software']['view_contacts']['nickname']) {$where .= " AND (contacts.nickname LIKE '%" . escape($_SESSION['software']['view_contacts']['nickname']) . "%')";}

    if ($_SESSION['software']['view_contacts']['company']) {$where .= " AND (contacts.company LIKE '%" . escape($_SESSION['software']['view_contacts']['company']) . "%')";}

    if ($_SESSION['software']['view_contacts']['title']) {$where .= " AND (contacts.title LIKE '%" . escape($_SESSION['software']['view_contacts']['title']) . "%')";}

    if ($_SESSION['software']['view_contacts']['department']) {$where .= " AND (contacts.department LIKE '%" . escape($_SESSION['software']['view_contacts']['department']) . "%')";}

    if ($_SESSION['software']['view_contacts']['office_location']) {$where .= " AND (contacts.office_location LIKE '%" . escape($_SESSION['software']['view_contacts']['office_location']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_address_1']) {$where .= " AND (contacts.business_address_1 LIKE '%" . escape($_SESSION['software']['view_contacts']['business_address_1']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_address_2']) {$where .= " AND (contacts.business_address_2 LIKE '%" . escape($_SESSION['software']['view_contacts']['business_address_2']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_city']) {$where .= " AND (contacts.business_city LIKE '%" . escape($_SESSION['software']['view_contacts']['business_city']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_state']) {$where .= " AND (contacts.business_state LIKE '%" . escape($_SESSION['software']['view_contacts']['business_state']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_country']) {$where .= " AND (contacts.business_country LIKE '%" . escape($_SESSION['software']['view_contacts']['business_country']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_zip_code']) {$where .= " AND (contacts.business_zip_code LIKE '%" . escape($_SESSION['software']['view_contacts']['business_zip_code']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_phone']) {$where .= " AND (contacts.business_phone LIKE '%" . escape($_SESSION['software']['view_contacts']['business_phone']) . "%')";}

    if ($_SESSION['software']['view_contacts']['business_fax']) {$where .= " AND (contacts.business_fax LIKE '%" . escape($_SESSION['software']['view_contacts']['business_fax']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_address_1']) {$where .= " AND (contacts.home_address_1 LIKE '%" . escape($_SESSION['software']['view_contacts']['home_address_1']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_address_2']) {$where .= " AND (contacts.home_address_2 LIKE '%" . escape($_SESSION['software']['view_contacts']['home_address_2']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_city']) {$where .= " AND (contacts.home_city LIKE '%" . escape($_SESSION['software']['view_contacts']['home_city']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_state']) {$where .= " AND (contacts.home_state LIKE '%" . escape($_SESSION['software']['view_contacts']['home_state']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_country']) {$where .= " AND (contacts.home_country LIKE '%" . escape($_SESSION['software']['view_contacts']['home_country']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_zip_code']) {$where .= " AND (contacts.home_zip_code LIKE '%" . escape($_SESSION['software']['view_contacts']['home_zip_code']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_phone']) {$where .= " AND (contacts.home_phone LIKE '%" . escape($_SESSION['software']['view_contacts']['home_phone']) . "%')";}

    if ($_SESSION['software']['view_contacts']['home_fax']) {$where .= " AND (contacts.home_fax LIKE '%" . escape($_SESSION['software']['view_contacts']['home_fax']) . "%')";}

    if ($_SESSION['software']['view_contacts']['mobile_phone']) {$where .= " AND (contacts.mobile_phone LIKE '%" . escape($_SESSION['software']['view_contacts']['mobile_phone']) . "%')";}

    if ($_SESSION['software']['view_contacts']['email_address']) {$where .= " AND (contacts.email_address LIKE '%" . escape($_SESSION['software']['view_contacts']['email_address']) . "%')";}

    if ($_SESSION['software']['view_contacts']['website']) {$where .= " AND (contacts.website LIKE '%" . escape($_SESSION['software']['view_contacts']['website']) . "%')";}

    if ($_SESSION['software']['view_contacts']['lead_source']) {$where .= " AND (contacts.lead_source LIKE '%" . escape($_SESSION['software']['view_contacts']['lead_source']) . "%')";}



    // If the subscribers filter is not on.

        if ($filter != 'my_subscribers') {

            if ($_SESSION['software']['view_contacts']['opt_in_status'] == 'opt_in') {

                $where .= " AND (contacts.opt_in = '1')";

            } else if ($_SESSION['software']['view_contacts']['opt_in_status'] == 'opt_out') {

                $where .= " AND (contacts.opt_in = '0')";

            }



            if ($_SESSION['software']['view_contacts']['description']) {$where .= " AND (contacts.description LIKE '%" . escape($_SESSION['software']['view_contacts']['description']) . "%')";}



            if ($_SESSION['software']['view_contacts']['opt_in_status'] == 'opt_in') {

                $where .= " AND (contacts.opt_in = '1')";

            } else if ($_SESSION['software']['view_contacts']['opt_in_status'] == 'opt_out') {

                $where .= " AND (contacts.opt_in = '0')";

            }

        }

     // If any of the Membership filters are not on.

        if (isset($membership_filter) == false) {

            // prepare SQL for membership status

            switch ($_SESSION['software']['view_contacts']['membership_status']) {



                case 'member':

                    $where .=

                        " AND (contacts.member_id != '')

                        AND (contacts.member_id IS NOT NULL)";

                    break;



                case 'active_member':

                    $where .=

                        " AND (contacts.member_id != '')

                        AND (contacts.member_id IS NOT NULL)

                        AND

                        (

                            (contacts.expiration_date >= CURRENT_DATE())

                            OR (contacts.expiration_date = '0000-00-00')

                            OR (contacts.expiration_date IS NULL)

                        )";



                    break;



                case 'expired_member':

                    $where .=

                        " AND (contacts.member_id != '')

                        AND (contacts.member_id IS NOT NULL)

                        AND (contacts.expiration_date < CURRENT_DATE())

                        AND (contacts.expiration_date != '0000-00-00')

                        AND (contacts.expiration_date IS NOT NULL)";

                    break;



                case 'unregistered_member':

                    $where .=

                        " AND (contact_user.user_contact IS NULL)

                        AND (contacts.member_id != '')

                        AND ((contacts.expiration_date >= CURRENT_DATE())

                        OR (contacts.expiration_date = '0000-00-00'))";

                    break;



                case 'non_member':

                    $where .=

                        " AND

                        (

                            (contacts.member_id = '')

                            OR (contacts.member_id IS NULL)

                        )";

                    break;

            }

        }

    if ($_SESSION['software']['view_contacts']['member_id']) {$where .= " AND (contacts.member_id LIKE '%" . escape($_SESSION['software']['view_contacts']['member_id']) . "%')";}

    if ($_SESSION['software']['view_contacts']['expiration_date']) {$where .= " AND (contacts.expiration_date = '" . escape(prepare_form_data_for_input($_SESSION['software']['view_contacts']['expiration_date'], 'date')) . "')";}



    if (AFFILIATE_PROGRAM == true) {

        if ($_SESSION['software']['view_contacts']['affiliate_name']) {$where .= " AND (contacts.affiliate_name LIKE '%" . escape($_SESSION['software']['view_contacts']['affiliate_name']) . "%')";}

        if ($_SESSION['software']['view_contacts']['affiliate_code']) {$where .= " AND (contacts.affiliate_code LIKE '%" . escape($_SESSION['software']['view_contacts']['affiliate_code']) . "%')";}

    }

}



// if user requested to export contacts, export contacts

if ($_GET['submit_data'] == 'Export Contacts') {

    // force download dialog

    header("Content-type: text/csv");

    header("Content-disposition: attachment; filename=contacts.csv");



    if (AFFILIATE_PROGRAM == true) {

        $output_affiliate_headings = ',affiliate_approved,affiliate_name,affiliate_code,affiliate_commission_rate';

    }



    print 'first_name,last_name,nickname,company,title,department,office_location,business_address_1,business_address_2,business_city,business_state,business_country,business_zip_code,business_phone,business_fax,home_address_1,home_address_2,home_city,home_state,home_country,home_zip_code,home_phone,home_fax,mobile_phone,email_address,website,lead_source,opt_in,description,member_id,expiration_date' . $output_affiliate_headings . "\n";



    $number_of_contacts = 0;



    $query = "SELECT

                contacts.first_name,

                contacts.last_name,

                contacts.nickname,

                contacts.company,title,

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

                contacts.affiliate_commission_rate

             FROM contacts

             LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

             LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id

             $join_table

             $where

             GROUP BY contacts.id

             ORDER BY contacts.last_name, contacts.first_name";



    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while($row = mysqli_fetch_assoc($result)) {

        // for each value in the row

        foreach ($row as $key => $value) {

           // replace quotation mark with two quotation marks

           $value = str_replace('"', '""', $value);

           // add quotation marks around value

           $value = '"' . $value . '"';

           // set new value

           $row[$key] = $value;

        }



        if (AFFILIATE_PROGRAM == true) {

            $output_affiliate_values = ',' . $row['affiliate_approved'] . ',' . $row['affiliate_name'] . ',' . $row['affiliate_code'] . ',' . $row['affiliate_commission_rate'];

        }



        print $row['first_name'] . ',' . $row['last_name'] . ',' . $row['nickname'] . ',' . $row['company'] . ',' . $row['title'] . ',' . $row['department'] . ',' . $row['office_location'] . ',' . $row['business_address_1'] . ',' . $row['business_address_2'] . ',' . $row['business_city'] . ',' . $row['business_state'] . ',' . $row['business_country'] . ',' . $row['business_zip_code'] . ',' . $row['business_phone'] . ',' . $row['business_fax'] . ',' . $row['home_address_1'] . ',' . $row['home_address_2'] . ',' . $row['home_city'] . ',' . $row['home_state'] . ',' . $row['home_country'] . ',' . $row['home_zip_code'] . ',' . $row['home_phone'] . ',' . $row['home_fax'] . ',' . $row['mobile_phone'] . ',' . $row['email_address'] . ',' . $row['website'] . ',' . $row['lead_source'] . ',' . $row['opt_in'] . ',' . $row['description'] . ',' . $row['member_id'] . ',' . $row['expiration_date'] . $output_affiliate_values . "\n";



        $number_of_contacts++;

    }



    log_activity("$number_of_contacts contacts were exported", $_SESSION['sessionusername']);



// if mass deletion is allowed and user requested to delete contacts, delete contacts

} elseif ((MASS_DELETION == true) && ($_GET['submit_data'] == 'Delete Contacts')) {

    // get all contacts that need to be deleted

    $query =

        "SELECT contacts.id

        FROM contacts

        LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

        LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id

        $join_table

        $where

        GROUP BY contacts.id";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



    $number_of_contacts = mysqli_num_rows($result);



    $contacts = array();



    // loop through all contacts that need to be deleted, so they can be added to array

    while ($row = mysqli_fetch_assoc($result)) {

        $contacts[] = $row;

    }



    // loop through all contacts that need to be deleted, so they can be deleted

    foreach ($contacts as $contact) {

        // delete contact

        $query = "DELETE FROM contacts WHERE id = '" . $contact['id'] . "'";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



        // delete contact references in contacts_contact_groups_xref

        $query = "DELETE FROM contacts_contact_groups_xref WHERE contact_id = '" . $contact['id'] . "'";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



        // delete contact references in opt_in

        $query = "DELETE FROM opt_in WHERE contact_id = '" . $contact['id'] . "'";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    }



    // prepare list of contact groups for log



    $contact_group_list = '';



    // if advanced filters are on

    if ($advanced_filters == true) {

        if (is_array($_SESSION['software']['view_contacts']['contact_groups']) == true) {

            foreach ($_SESSION['software']['view_contacts']['contact_groups'] as $contact_group) {

                if ($contact_group_list) {

                    $contact_group_list .= ', ';

                }



                // if this contact group is the [None] contact group

                if ($contact_group == '[None]') {

                    $contact_group_list .= '[None]';



                // else this contact group is not the [None] contact group, so get contact group name

                } else {

                    $query = "SELECT name FROM contact_groups WHERE id = '" . escape($contact_group) . "'";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $row = mysqli_fetch_assoc($result);



                    $contact_group_list .= $row['name'];

                }

            }

        }



    // else advanced filters are off

    } else {

        // if the [All] contact group was selected

        if ($contact_groups_filter == '[All]') {

            $contact_group_list = '[All]';



        // else if the [None] contact group was selected

        } elseif ($contact_groups_filter == '[None]') {

            $contact_group_list = '[None]';



        // else get group name

        } else {

            // get contact group name

            $query = "SELECT name FROM contact_groups WHERE id = '" . escape($contact_groups_filter) . "'";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            $row = mysqli_fetch_assoc($result);



            $contact_group_list = $row['name'];

        }

    }



    // if at least one contact was deleted

    if ($number_of_contacts > 0) {

        log_activity(number_format($number_of_contacts) . " contact(s) from contact group(s) ($contact_group_list) were deleted", $_SESSION['sessionusername']);



        $liveform->add_notice(number_format($number_of_contacts) . " contact(s) from contact group(s) ($contact_group_list) were deleted");

    } else {

        $liveform->add_notice("No contacts were deleted");

    }



    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php');



// else, if the user selected to merge contacts, then merge them

} elseif ($_GET['submit_data'] == 'Merge Contacts') {

    $contacts_to_merge = array();

    

    // get contacts to be merged information

    $query = 

        "SELECT

            contacts.id,

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

            contacts.warning_expiration_date,

            contacts.affiliate_approved,

            contacts.affiliate_name,

            contacts.affiliate_code,

            contacts.affiliate_commission_rate,

            contacts.user,

            contact_user.user_id as user_id,

            contacts.timestamp

        FROM contacts

        LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

        LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id

        $join_table

        $where

        ORDER BY contacts.email_address ASC";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    while($row = mysqli_fetch_assoc($result)) {

        $contacts_to_merge[] = $row;

    }



    // merge the contacts, then refresh the screen with a notice

    $number_of_merged_contacts = merge_contacts($contacts_to_merge);

    

    $notice = '';

    

    // if contacts were merged then output a notice informing the user how many were merged

    if ($number_of_merged_contacts > 0) {

        $notice = number_format($number_of_merged_contacts) . " contact(s) have been merged successfully.";

        log_activity(number_format($number_of_merged_contacts) . " contact(s) were merged successfully", $_SESSION['sessionusername']);

    

    // else output a notice informing the user that no contacts where merged.

    } else {

        $notice = "No contacts were merged. Contacts tied to User accounts cannot be merged.";

    }

    

    $liveform->add_notice($notice);

    

    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_contacts.php?filter=all_duplicate_contacts');



// else user did not request to export contacts or to merge contacts, so view contacts

} else {

    // get minimum year from oldest timestamp

    $oldest_year = date('Y', $oldest_timestamp);

    if ($_SESSION['software']['view_contacts']['start_year'] < $oldest_year) {

        $oldest_year = $_SESSION['software']['view_contacts']['start_year'];

    }



    $this_year = date('Y');

    if ($_SESSION['software']['view_contacts']['stop_year'] > $this_year) {

        $this_year = $_SESSION['software']['view_contacts']['stop_year'];

    }



    $years = array();



    // create html for year options

    for ($i = $oldest_year; $i <= $this_year; $i++) {

        $years[] = $i;

    }



    // if sort was set, update session

    if (isset($_REQUEST['sort'])) {

        // store sort in session

        $_SESSION['software']['view_contacts']['sort'] = $_REQUEST['sort'];



        // clear order

        $_SESSION['software']['view_contacts']['order'] = '';

    }



    // if order was set, update session

    if (isset($_REQUEST['order'])) {

        $_SESSION['software']['view_contacts']['order'] = $_REQUEST['order'];

    }



    // If a screen was passed and it is a positive integer, then use it.

    // These checks are necessary in order to avoid SQL errors below for a bogus screen value.

    if (

        $_REQUEST['screen']

        and is_numeric($_REQUEST['screen'])

        and $_REQUEST['screen'] > 0

        and $_REQUEST['screen'] == round($_REQUEST['screen'])

    ) {

        $screen = (int) $_REQUEST['screen'];



    // Otherwise, use the default, which is the first screen.

    } else {

        $screen = 1;

    }



    // if the advanced filters are not on, then prepare contact group picklist

    if ($advanced_filters == false) {

        $output_contact_group_options = '';



        // create contact group selection list

        foreach ($contact_groups as $contact_group) {

            // if the contact group is equal to selected contact group

            if ($contact_group['id'] == $contact_groups_filter) {

                $selected = ' selected="selected"';

            } else {

                $selected = '';

            }



            // get number of contacts in contact group

            $number_of_contacts = get_number_of_contacts($contact_group['id'], $require_email = false);



            $output_contact_group_options .= '<option value="' . $contact_group['id'] . '"' . $selected . '>' . h($contact_group['name']) . ' (' . number_format($number_of_contacts) . ')</option>';

        }



        // if user has a role that is greater than user role, then prepare to output [None] option

        if ($user['role'] < 3) {

            // if none contact group is selected

            if ($contact_groups_filter == '[None]') {

                $selected = ' selected="selected"';

            } else {

                $selected = '';

            }



            $number_of_contacts = get_number_of_contacts('[None]', $require_email = false);



            $output_contact_group_options ='<option value="[None]"' . $selected . '>[None] (' . number_format($number_of_contacts) . ')</option>' . $output_contact_group_options;

        }



        // if all contact group is selected

        if ($contact_groups_filter == '[All]') {

            $selected = ' selected="selected"';

        } else {

            $selected = '';

        }



        $output_contact_group_options = '<option value="[All]"' . $selected . '>[All]</option>' . $output_contact_group_options;

    }

    

    $sort_order = '';

    

    // if the filter is set to all duplicate contacts then hard set the sql statement to sort by the email addresses in alphabetical order,

    if ($filter == 'all_duplicate_contacts') {

        $sort_column = "email_address";

        $sort_order = 'asc';

        

    // else sort the view based on what the user selected

    } else {

        switch ($_SESSION['software']['view_contacts']['sort']) {

            case 'First Name':

                $sort_column = 'first_name';

                break;



            case 'Last Name':

                $sort_column = 'last_name';

                break;



            case 'Company':

                $sort_column = 'company';

                break;



            case 'Email':

                $sort_column = 'email_address';

                break;



            case 'User':

                $sort_column = 'contact_user.user_username';

                break;



            case 'Opt-In':

                $sort_column = 'opt_in';

                break;

                

            case 'City':

                // if the my contacts by business address filter is on, then set sort column to business city

                if ($filter == 'my_contacts_by_business_address') {

                    $sort_column = 'business_city';

                    

                // else if the filter is set to my contacts by home address, then set the sort column to home city

                } elseif ($filter == 'my_contacts_by_home_address') {

                    $sort_column = 'home_city';

                

                // else set sort column to the default

                } else {

                    $sort_column = 'timestamp';

                    $_SESSION['software']['view_contacts']['sort'] = 'Last Modified';

                }

                

                break;

                

            case 'State':

                // if the my contacts by business address filter is on, then set sort column to business state

                if ($filter == 'my_contacts_by_business_address') {

                    $sort_column = 'business_state';

                    

                // else if the filter is set to my contacts by home address, then set the sort column to home state

                } elseif ($filter == 'my_contacts_by_home_address') {

                    $sort_column = 'home_state';

                

                // else set sort column to the default

                } else {

                    $sort_column = 'timestamp';

                    $_SESSION['software']['view_contacts']['sort'] = 'Last Modified';

                }

                

                break;

                

            case 'Zip Code':

                // if the my contacts by business address filter is on, then set sort column to business zip code

                if ($filter == 'my_contacts_by_business_address') {

                    $sort_column = 'business_zip_code';

                    

                // else if the filter is set to my contacts by home address, then set the sort column to home zipe code

                } elseif ($filter == 'my_contacts_by_home_address') {

                    $sort_column = 'home_zip_code';

                

                // else set sort column to the default

                } else {

                    $sort_column = 'timestamp';

                    $_SESSION['software']['view_contacts']['sort'] = 'Last Modified';

                }

                

                break;

                

            case 'Country':

                // if the my contacts by business address filter is on, then set sort column to business country

                if ($filter == 'my_contacts_by_business_address') {

                    $sort_column = 'business_country';

                    

                // else if the filter is set to my contacts by home address, then set the sort column to home country

                } elseif ($filter == 'my_contacts_by_home_address') {

                    $sort_column = 'home_zip_code';

                

                // else set sort column to the default

                } else {

                    $sort_column = 'timestamp';

                    $_SESSION['software']['view_contacts']['sort'] = 'Last Modified';

                }

                

                break;



            case 'Last Modified':

                $sort_column = 'timestamp';

                break;



            default:

                $sort_column = 'timestamp';

                $_SESSION['software']['view_contacts']['sort'] = 'Last Modified';

                $_SESSION['software']['view_contacts']['order'] = 'desc';

                break;

        }



        if (!$_SESSION['software']['view_contacts']['order']) {

            $_SESSION['software']['view_contacts']['order'] = 'asc';

        }

        

        // if the sort order is blank then set it to the order in the session

        if ($sort_order == '') {

            $sort_order = $_SESSION['software']['view_contacts']['order'];

        }

    }

    

    /* define range depending on screen value by using a limit clause in the SQL statement */

    // define the maximum number of results

    $max = 100;

    // determine where result set should start

    $start = $screen * $max - $max;

    $limit = "LIMIT $start, $max";



    // get total number of results for all screens, so that we can output links to different screens

    $query =

        "SELECT contacts.id

        FROM contacts

        LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

        LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id

        $join_table

        $where

        GROUP BY contacts.id";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $number_of_results = mysqli_num_rows($result);



    // get number of screens

    $number_of_screens = ceil($number_of_results / $max);



    // build Previous button if necessary

    $previous = $screen - 1;

    // if previous screen is greater than zero, output previous link

    if ($previous > 0) {

        $output_screen_links .= '<a class="submit-secondary" href="view_contacts.php?filter=' . h($filter) . '&screen=' . $previous . $output_filter_for_links . '">&lt;</a>&nbsp;&nbsp;';

    }



    // if there are more than one screen

    if ($number_of_screens > 1) {

        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_contacts.php?screen=\' + this.options[this.selectedIndex].value + \'' . h(escape_javascript($filter_for_links)) . '\')">';



        // build HTML output for links to screens

        for ($i = 1; $i <= $number_of_screens; $i++) {

            // if this number is the current screen, then select option

            if ($i == $screen) {

                $selected = ' selected="selected"';

            // else this number is not the current screen, so do not select option

            } else {

                $selected = '';

            }



            $output_screen_links .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';

        }



        $output_screen_links .= '</select>';

    }



    // build Next button if necessary

    $next = $screen + 1;

    // if next screen is less than or equal to the total number of screens, output next link

    if ($next <= $number_of_screens) {

        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_contacts.php?filter=' . h($filter) . '&screen=' . $next . $output_filter_for_links . '">&gt;</a>';

    }

    

    // get results for just this screen

    $query =

        "SELECT

            contacts.id,

            contacts.first_name,

            contacts.last_name,

            contacts.company,

            contacts.business_phone,

            contacts.home_phone,

            contacts.mobile_phone,

            contacts.email_address,

            contacts.opt_in,

            $sql_columns

            last_modified_user.user_username as last_modified_username,

            contact_user.user_id as user_id,

            contact_user.user_username as user_username,

            contact_user.user_role as user_role,

            contacts.timestamp

        FROM contacts

        LEFT JOIN user AS contact_user ON contacts.id = contact_user.user_contact

        LEFT JOIN user AS last_modified_user ON contacts.user = last_modified_user.user_id

        LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id

        $join_table

        $where

        GROUP BY contacts.id

        ORDER BY $sort_column " . escape($sort_order) . "

        $limit";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



    $contacts = array();



    // loop through all contacts, so they can be added to array

    while ($row = mysqli_fetch_assoc($result)) {

        $contacts[] = $row;

    }

    

    // if the filter is set to all duplicate contacts then organize the contacts to be sorted by e-mail address, and then by child orphan relationship

    if ($filter == 'all_duplicate_contacts') {

        $child_contacts = array();

        $organized_contacts = array();

        

        // loop through the contacts to put children contacts in their own array

        foreach ($contacts as $key => $contact) {

            // if the contact has a user id then it is a child so add it to the child contacts array

            if ($contact['user_id'] != '') {

                $child_contacts[$key] = $contact;

            }

        }

        

        // loop through all contacts to organize them

        foreach ($contacts as $contact) {

            // loop through all child contacts to see if there are any children for this contact, and add any matches to the organzied contacts array

            foreach ($child_contacts as $key => $child_contact) {

                // if the child's email address is the same as the contact's email address, then add it to the organized contacts array, 

                // and remove it from the other arrays so that it isn't found again

                if ($child_contact['email_address'] == $contact['email_address']) {

                    $organized_contacts[] = $child_contact;

                    

                    // remove contact from arrays so that this child contact is not found again

                    unset($contacts[$key]);

                    unset($child_contacts[$key]);

                }

            }

            

            // if this contact is not a child then add it to the organized contacts array

            if ($contact['user_id'] == '') {

                $organized_contacts[] = $contact;

            }

        }

        

        // if there are organized contacts, then unset the contacts array and set it to the organized contacts array

        if (count($organized_contacts) > 0) {

            unset($contacts);

            $contacts = $organized_contacts;

        }

    }

    

    // loop through all contacts that need to be outputted, so they can be outputted

    foreach ($contacts as $contact) {

        // Set link url

        $output_link_url = 'edit_contact.php?id=' . $contact['id'] . '&send_to=' . h(escape_javascript(REQUEST_URL));



        $output_opt_in_row = '';

        $output_phone_row = '';

        $output_email_address_row = '';

        

        // if the filter is not set to either my contacts by business address or my contacts by home address then output my contacts by user, opt in, phone and e-mail address rows

        if (($filter != 'my_contacts_by_business_address') && ($filter != 'my_contacts_by_home_address')) {

            $user_display = '';

            

            // if there is a user for this contact

            if ($contact['user_id']) {

                // if the editor user is an administrator or the editor user has access to edit this user, then prepare username with link

                if (($user['role'] == 0) || ($user['role'] < $contact['user_role'])) {

                    $user_display = '<a href="edit_user.php?id=' . $contact['user_id'] . '">' . h($contact['user_username']) . '</a>';



                // else the editor user does not have access to edit this user, so prepare username without link

                } else {

                    $user_display = h($contact['user_username']);

                }

            }



            // Show the user column column.

            $output_user_row = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $user_display . '</td>';



            // If the contact is opted in then prepare the checkmark.

            if ($contact['opt_in']) {

                $opt_in_display = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';

            } else {

                $opt_in_display = '';

            }

            

            // output opt in row

            $output_opt_in_row = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $opt_in_display . '</td>';

            

            $output_phone_numbers = '';

            

            // build phone numbers to be outputted in the Phone row

            if ($contact['business_phone'] != '') {

                $output_phone_numbers .= 'B: ' . h($contact['business_phone']);

            }

            

            if ($contact['home_phone'] != '') {

                if ($output_phone_numbers != '') {

                    $output_phone_numbers .= '<br />';

                }

                $output_phone_numbers .= 'H: ' . h($contact['home_phone']);

            }

            

            if ($contact['mobile_phone'] != '') {

                if ($output_phone_numbers != '') {

                    $output_phone_numbers .= '<br />';

                }

                $output_phone_numbers .= 'M: ' . h($contact['mobile_phone']);

            }

            

            // output phone row

            $output_phone_row  = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_phone_numbers . '</td>';

            

            // output email address row

            $output_email_address_row  = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'"><a href="mailto:' . h($contact['email_address']) . '">' . h($contact['email_address']) . '</a></td>';

        }

        

        $output_contact_groups = '';

        

        // If show groups is on, or the filter is set to all duplicate contacts then get contact groups.

        if (($_SESSION['software']['view_contacts']['show_contact_groups'] == true) || ($filter == 'all_duplicate_contacts')) {

            // get contact groups that this contact is in

            $query = "SELECT contact_group_id FROM contacts_contact_groups_xref WHERE contact_id = '" . $contact['id'] . "'";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



            $contact_groups_for_contact = array();



            // loop through all contacts, so they can be added to array

            while ($row = mysqli_fetch_assoc($result)) {

                $contact_groups_for_contact[] = $row['contact_group_id'];

            }



            // loop through all contact groups that this user has access to in order to prepare list of contact groups to output for this contact

            foreach ($contact_groups as $contact_group) {

                // if contact is in this contact group, then prepare to output contact group

                if (in_array($contact_group['id'], $contact_groups_for_contact) == true) {

                    if ($output_contact_groups) {

                        $output_contact_groups .= ',<br />';

                    }



                    $output_contact_groups .= h($contact_group['name']);

                }

            }

        }

        

        $output_address_rows = '';

        

        // If the filter is set to contacts by business address or contacts by home address then output address rows

        if (($filter == 'my_contacts_by_business_address') || ($filter == 'my_contacts_by_home_address')) {

            // set the street address line 1

            $output_street_address = h($contact['address_1']);

            

            // if there is a second street address, then combine address line 1 with address line 2 to get the full street address

            if ($contact['address_2'] != '') {

                $output_street_address .= ',<br />' . h($contact['address_2']);

            }

            

            // output address rows

            $output_address_rows = 

                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_street_address . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['city']) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['state']) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['zip_code']) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['country']) . '</td>';

        }

        

        // if the all duplicate contacts filter is on then build address row and output the appropriate table layout

        if ($filter == 'all_duplicate_contacts') {

            // dynamically build a business address string to be outputted

            $business_address = '';

            

            if ($contact['business_address_1'] != '') {

                $business_address .= $contact['business_address_1'];

            }

            

            if ($contact['business_address_2'] != '') {

                if ($business_address != '') {

                    $business_address .= ' ';

                }

                

                $business_address .= $contact['business_address_2'];

            }

            

            if ($contact['business_city'] != '') {

                if ($business_address != '') {

                    $business_address .= ' ';

                }

                

                $business_address .= $contact['business_city'];

            }

            

            if ($contact['business_state'] != '') {

                if ($business_address != '') {

                    $business_address .= ', ';

                }

                

                $business_address .= $contact['business_state'];

            }

            

            if ($contact['business_zip_code'] != '') {

                if ($business_address != '') {

                    $business_address .= ' ';

                }

                

                $business_address .= $contact['business_zip_code'];

            }

            

            if ($contact['business_country'] != '') {

                if ($business_address != '') {

                    $business_address .= ' ';

                }

                

                $business_address .= $contact['business_country'];

            }

            

            if ($business_address != '') {

                $business_address = 'B: ' . $business_address;

            }

            

            // remove any double spaces that may have been entered or created

            $business_address = str_replace("  ", " ", $business_address);

            

            // dynamically build a home address string to be outputted

            $home_address = '';

            

            if ($contact['home_address_1'] != '') {

                $home_address .= $contact['home_address_1'];

            }

            

            if ($contact['home_address_2'] != '') {

                if ($home_address != '') {

                    $home_address .= ' ';

                }

                

                $home_address .= $contact['home_address_2'];

            }

            

            if ($contact['home_city'] != '') {

                if ($home_address != '') {

                    $home_address .= ' ';

                }

                

                $home_address .= $contact['home_city'];

            }

            

            if ($contact['home_state'] != '') {

                if ($home_address != '') {

                    $home_address .= ', ';

                }

                

                $home_address .= $contact['home_state'];

            }

            

            if ($contact['home_zip_code'] != '') {

                if ($home_address != '') {

                    $home_address .= ' ';

                }

                

                $home_address .= $contact['home_zip_code'];

            }

            

            if ($contact['home_country'] != '') {

                if ($home_address != '') {

                    $home_address .= ' ';

                }

                

                $home_address .= $contact['home_country'];

            }

            

            if ($home_address != '') {

                $home_address = 'H: ' . $home_address;

            }

            

            // remove any double spaces that may have been entered or created

            $home_address = str_replace("  ", " ", $home_address);

            

            $output_address_row = '';

            

            // if there is a business address then output it

            if ($business_address != '') {

                $output_address_row .= h($business_address);

            }

            

            // if there is a home address then output it

            if ($home_address != '') {

                // if there is a business address then add a break tag to get the home address on it's own line

                if ($business_address != '') {

                    $output_address_row .= '<br />';

                }

                

                $output_address_row .= h($home_address);

            }

            

            $output_address_row = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_address_row . '</td>';

            

            $firstChar = strtoupper (mb_substr($contact['first_name'], 0, 1, "UTF-8")) . strtoupper (mb_substr($contact['last_name'], 0, 1, "UTF-8"));

            $output_rows .=

                '<tr id="' . $contact['id'] . '">

                    <td class="selectall"><input type="checkbox" name="contacts[]" value="' . $contact['id'] . '" class="checkbox" /></td>

                    <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">

                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 37 37" style="height:37px;width:37px;">

                            <g>

                              <circle style="fill:#303f9f;" cx="18.5" cy="18.5" r="18.5"></circle>

                              <text style="fill:#f5f5f6;font-size:12.5" x="18.5" y="18.5" text-anchor="middle" dy=".3em">' . $firstChar  . '</text>

                            </g>

                        </svg>

                    </td>

                    ' . $output_email_address_row . '

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['first_name']) . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['last_name']) . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['company']) . '</td>

                    ' . $output_address_row . '

                    ' . $output_phone_row . '

                    ' . $output_user_row . '

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_contact_groups . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $contact['timestamp'])) . '<br />' . h($contact['last_modified_username']) . '</td>

                </tr>';

            

        // else output the standard table layout

        } else {

            $firstChar = strtoupper (mb_substr($contact['first_name'], 0, 1, "UTF-8")) . strtoupper (mb_substr($contact['last_name'], 0, 1, "UTF-8"));

            $output_rows .=

                '<tr id="' . $contact['id'] . '">

                    <td class="selectall"><input type="checkbox" name="contacts[]" value="' . $contact['id'] . '" class="checkbox" /></td>

                    <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">

                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 37 37" style="height:37px;width:37px;">

                            <g>

                              <circle style="fill:#303f9f;" cx="18.5" cy="18.5" r="18.5"></circle>

                              <text style="fill:#f5f5f6;font-size:12.5" x="18.5" y="18.5" text-anchor="middle" dy=".3em">' . $firstChar  . '</text>

                            </g>

                        </svg>

                    </td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['first_name']) . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['last_name']) . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($contact['company']) . '</td>

                    ' . $output_phone_row . '

                    ' . $output_email_address_row . '

                    ' . $output_user_row . '

                    ' . $output_opt_in_row . '

                    ' . $output_address_rows . '

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . $output_contact_groups . '</td>

                    <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $contact['timestamp'])) . '<br />' . h($contact['last_modified_username']) . '</td>

                </tr>';

        }

    }



    // if the advanced filters are off

    if ($advanced_filters == false) {

        $output_contact_group_selection = '<select name="contact_group" onchange="submit_form(\'advanced_filters_form\')">' . $output_contact_group_options . '</select>';

        $output_advanced_filters_value = 'true';

        $output_advanced_filters_label = 'Add Advanced Filters';

        $output_advanced_filters = '';

        $advanced_filters_icon = 'off';

        

        // if the all duplicate contacts filter is on then hide the contact group select list and the advanced filters button

        if ($filter == 'all_duplicate_contacts') {

            $show_hide_contact_group_select = 'display: none;';

            $show_hide_advanced_filters_button = 'display: none;';

            

        // else show the contact groups pick list and advanced filters button

        } else {

            $show_hide_contact_group_select = '';

            $show_hide_advanced_filters_button = '';

        }



    // else the advanced filters are on

    } else {

        $output_contact_group_selection = '[select below]';

        $output_advanced_filters_value = 'false';

        $output_advanced_filters_label = 'Remove Advanced Filters';

        $show_hide_contact_group_select = 'display: none;';

        $advanced_filters_icon = 'on';



        $output_contact_groups = '';



        // if user has a role that is greater than user role, then prepare to output [None] option

        if ($user['role'] < 3) {

            // if none contact group is selected

            if ((is_array($_SESSION['software']['view_contacts']['contact_groups']) == true) && (in_array('[None]', $_SESSION['software']['view_contacts']['contact_groups']) == true)) {

                $checked = ' checked="checked"';

            } else {

                $checked = '';

            }



            $output_contact_groups .= '<input type="checkbox" name="contact_groups[]" id="contact_group_[None]" value="[None]"' . $checked . ' class="checkbox" /><label for="contact_group_[None]"> [None] (' . get_number_of_contacts('[None]', $require_email = false) . ')</label><br />';

        }



        foreach ($contact_groups as $contact_group) {

            // get number of contacts in contact group

            $number_of_contacts = get_number_of_contacts($contact_group['id'], $require_email = false);



            // if this contact group should be checked

            if ((is_array($_SESSION['software']['view_contacts']['contact_groups']) == true) && (in_array($contact_group['id'], $_SESSION['software']['view_contacts']['contact_groups']) == true)) {

                $checked = ' checked="checked"';

            } else {

                $checked = '';

            }



            $output_contact_groups .= '<input type="checkbox" name="contact_groups[]" id="contact_group_' . $contact_group['id'] . '" value="' . $contact_group['id'] . '"' . $checked . ' class="checkbox" /><label for="contact_group_' . $contact_group['id'] . '"> ' . h($contact_group['name']) . ' (' . $number_of_contacts . ')</label><br />';

        }



        // If the Opt in status is not on display the opt in select box.

        if ($filter != 'my_subscribers') {



            switch ($_SESSION['software']['view_contacts']['opt_in_status']) {

                case 'any':

                        $opt_in_status_any_selected = ' selected="selected"';

                    break;

                case 'opt_in':

                        $opt_in_status_opt_in_selected = ' selected="selected"';

                    break;

                case 'opt_out':

                        $opt_in_status_opt_out_selected = ' selected="selected"';

                    break;

            }



            // Output select box.

            $output_opt_status = '

                <select name="opt_in_status"><option value="any"' . $opt_in_status_any_selected . '>Any</option><option value="opt_in"' . $opt_in_status_opt_in_selected . '>Opt-In</option><option value="opt_out"' . $opt_in_status_opt_out_selected . '>Opt-Out</option></select>';

        } else {

            $output_opt_status = 'Opt-In';

        }



        // If any of the Membership filters are not on.

        if (isset($membership_filter) == false) {

            // prepare selection for membership status pick list

            switch ($_SESSION['software']['view_contacts']['membership_status']) {

                case 'any':

                    $membership_status_any_selected = ' selected="selected"';

                    break;



                case 'member':

                    $membership_status_member_or_expired_member_selected = ' selected="selected"';

                    break;



                case 'active_member':

                    $membership_status_member_selected = ' selected="selected"';

                    break;



                case 'expired_member':

                    $membership_status_expired_member_selected = ' selected="selected"';

                    break;



                case 'unregistered_member':

                    $membership_status_unregistered_member_selected = ' selected="selected"';

                    break;



                case 'non_member':

                    $membership_status_non_member_selected = ' selected="selected"';

                    break;

            }



            $output_membership_status = '

                <tr>

                    <td>Status:</td>

                    <td>

                        <select name="membership_status">

                            <option value="any"' . $membership_status_any_selected . '>Any</option>

                            <option value="member"' . $membership_status_member_or_expired_member_selected . '>Member</option>

                            <option value="active_member"' . $membership_status_member_selected . '> Active Member</option>

                            <option value="expired_member"' . $membership_status_expired_member_selected . '>Expired Member</option>

                            <option value="unregistered_member"' . $membership_status_unregistered_member_selected . '>Unregistered Member</option>

                            <option value="non_member"' . $membership_status_non_member_selected . '>Non-Member</option>

                        </select>

                    </td>

                </tr>';

        } else {

            $output_membership_status = '

                <tr>

                    <td>Status:</td>

                    <td>

                        ' . $membership_status_label . '

                    </td>

                </tr>';

        }



        if (AFFILIATE_PROGRAM == true) {

            $output_affiliate =

                '<fieldset style="padding: 0px 10px 10px 10px">

                    <legend><strong>Affiliate</strong></legend>

                    <table>

                        <tr>

                            <td>Affiliate Name:</td>

                            <td><input type="text" name="affiliate_name" value="' . h($_SESSION['software']['view_contacts']['affiliate_name']) . '" /></td>

                        </tr>

                        <tr>

                            <td>Affiliate Code:</td>

                            <td><input type="text" name="affiliate_code" value="' . h($_SESSION['software']['view_contacts']['affiliate_code']) . '" /></td>

                        </tr>

                    </table>

                </fieldset>';

        }



        $output_advanced_filters =

            '<div class="advanced_filters">

            <div style="margin: 0em 0em 1em 0em;"><h2>Advanced Filters</h2>

                <table style="width: 100%; margin-bottom: 5px">

                    <tr>

                        <td style="vertical-align: top; padding-right: 10px">

                            <fieldset style="padding: 0px 10px 10px 10px">

                                <legend><strong>Contact Groups</strong></legend>

                                <div style="white-space: nowrap; padding: .5em 0 .5em;"><a href="javascript:check_all(\'contact_groups[]\')" class="button_3d_secondary">All</a> <a href="javascript:uncheck_all(\'contact_groups[]\')" class="button_3d_secondary">None</a></div>

                                <div class="scrollable" style="height: 400px">

                                    <input type="hidden" name="contact_groups" value="" />

                                    ' . $output_contact_groups . '

                                </div>

                            </fieldset>

                        </td>

                        <td style="vertical-align: top; padding-right: 10px">

                            <fieldset style="padding: 0px 10px 10px 10px">

                                <legend><strong>General</strong></legend>

                                <table>

                                    <tr>

                                        <td>Salutation:</td>

                                        <td><input type="text" name="salutation" value="' . h($_SESSION['software']['view_contacts']['salutation']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>First Name:</td>

                                        <td><input type="text" name="first_name" value="' . h($_SESSION['software']['view_contacts']['first_name']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Last Name:</td>

                                        <td><input type="text" name="last_name" value="' . h($_SESSION['software']['view_contacts']['last_name']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Suffix:</td>

                                        <td><input type="text" name="suffix" value="' . h($_SESSION['software']['view_contacts']['suffix']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Nickname:</td>

                                        <td><input type="text" name="nickname" value="' . h($_SESSION['software']['view_contacts']['nickname']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Company:</td>

                                        <td><input type="text" name="company" value="' . h($_SESSION['software']['view_contacts']['company']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Title:</td>

                                        <td><input type="text" name="title" value="' . h($_SESSION['software']['view_contacts']['title']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Department:</td>

                                        <td><input type="text" name="department" value="' . h($_SESSION['software']['view_contacts']['department']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Office Location:</td>

                                        <td><input type="text" name="office_location" value="' . h($_SESSION['software']['view_contacts']['office_location']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Business Phone:</td>

                                        <td><input type="text" name="business_phone" value="' . h($_SESSION['software']['view_contacts']['business_phone']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Home Phone:</td>

                                        <td><input type="text" name="home_phone" value="' . h($_SESSION['software']['view_contacts']['home_phone']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Mobile Phone:</td>

                                        <td><input type="text" name="mobile_phone" value="' . h($_SESSION['software']['view_contacts']['mobile_phone']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Business Fax:</td>

                                        <td><input type="text" name="business_fax" value="' . h($_SESSION['software']['view_contacts']['business_fax']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Home Fax:</td>

                                        <td><input type="text" name="home_fax" value="' . h($_SESSION['software']['view_contacts']['home_fax']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Email:</td>

                                        <td><input type="text" name="email_address" value="' . h($_SESSION['software']['view_contacts']['email_address']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Opt-In Status:</td>

                                        <td>' . $output_opt_status . '</td>

                                    </tr>

                                    <tr>

                                        <td>Website:</td>

                                        <td><input type="text" name="website" value="' . h($_SESSION['software']['view_contacts']['website']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Lead Source:</td>

                                        <td><input type="text" name="lead_source" value="' . h($_SESSION['software']['view_contacts']['lead_source']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Description:</td>

                                        <td><input type="text" name="description" value="' . h($_SESSION['software']['view_contacts']['description']) . '" /></td>

                                    </tr>

                                </table>

                            </fieldset>

                        </td>

                        <td style="vertical-align: top">

                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">

                                <legend><strong>Business</strong></legend>

                                <table>

                                    <tr>

                                        <td>Address 1:</td>

                                        <td><input type="text" name="business_address_1" value="' . h($_SESSION['software']['view_contacts']['business_address_1']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Address 2:</td>

                                        <td><input type="text" name="business_address_2" value="' . h($_SESSION['software']['view_contacts']['business_address_2']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>City:</td>

                                        <td><input type="text" name="business_city" value="' . h($_SESSION['software']['view_contacts']['business_city']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>State:</td>

                                        <td><input type="text" name="business_state" value="' . h($_SESSION['software']['view_contacts']['business_state']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Zip Code:</td>

                                        <td><input type="text" name="business_zip_code" value="' . h($_SESSION['software']['view_contacts']['business_zip_code']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Country:</td>

                                        <td><input type="text" name="business_country" value="' . h($_SESSION['software']['view_contacts']['business_country']) . '" /></td>

                                    </tr>

                                </table>

                            </fieldset>

                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">

                                <legend><strong>Home</strong></legend>

                                <table>

                                    <tr>

                                        <td>Address 1:</td>

                                        <td><input type="text" name="home_address_1" value="' . h($_SESSION['software']['view_contacts']['home_address_1']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Address 2:</td>

                                        <td><input type="text" name="home_address_2" value="' . h($_SESSION['software']['view_contacts']['home_address_2']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>City:</td>

                                        <td><input type="text" name="home_city" value="' . h($_SESSION['software']['view_contacts']['home_city']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>State:</td>

                                        <td><input type="text" name="home_state" value="' . h($_SESSION['software']['view_contacts']['home_state']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Zip Code:</td>

                                        <td><input type="text" name="home_zip_code" value="' . h($_SESSION['software']['view_contacts']['home_zip_code']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Country:</td>

                                        <td><input type="text" name="home_country" value="' . h($_SESSION['software']['view_contacts']['home_country']) . '" /></td>

                                    </tr>

                                </table>

                            </fieldset>

                            <fieldset style="padding: 0px 10px 10px 10px; margin-bottom: 10px">

                                <legend><strong>Membership</strong></legend>

                                <table>

                                    ' . $output_membership_status . '

                                    <tr>

                                        <td>' . h(MEMBER_ID_LABEL) . ':</td>

                                        <td><input type="text" name="member_id" value="' . h($_SESSION['software']['view_contacts']['member_id']) . '" /></td>

                                    </tr>

                                    <tr>

                                        <td>Expiration Date:</td>

                                        <td>

                                            <input type="text" id="expiration_date" name="expiration_date" value="' . h($_SESSION['software']['view_contacts']['expiration_date']) . '" />

                                            ' . get_date_picker_format() . '

                                            <script>

                                                $("#expiration_date").datepicker({

                                                    dateFormat: date_picker_format

                                                });

                                            </script>

                                        </td>

                                    </tr>

                                </table>

                            </fieldset>

                            ' . $output_affiliate . '

                        </td>

                    </tr>

                    <tr>

                        <td><input type="submit" name="submit_data" value="Update" class="submit_small_secondary" /></td>

                    </tr>

                </table>

            </div>

            <div style="margin: 0em 0em 1em 0em;">

                <fieldset style="padding: 10px 10px 18px 10px">

                    <legend><strong>Date Range:</strong></legend>

                    From:&nbsp;<select name="start_month">' . select_month($_SESSION['software']['view_contacts']['start_month']) . '</select><select name="start_day">' . select_day($_SESSION['software']['view_contacts']['start_day']) . '</select><select name="start_year">' . select_year($years, $_SESSION['software']['view_contacts']['start_year']) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;<select name="stop_month">' . select_month($_SESSION['software']['view_contacts']['stop_month']) . '</select><select name="stop_day">' . select_day($_SESSION['software']['view_contacts']['stop_day']) . '</select><select name="stop_year">' . select_year($years, $_SESSION['software']['view_contacts']['stop_year']) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_data" value="Update" class="submit_small_secondary" />

                </fieldset>

            </div>

            </div>';

    }



    $output_delete_contacts_button = '';



    // if mass deletion is allowed, then prepare to output delete contacts button

    if (MASS_DELETION == true) {

        $output_delete_contacts_button = ' <input type="submit" name="submit_data" value="Delete Contacts" class="delete_small" onclick="return confirm(\'WARNING: All contacts that match the filters will be permanently deleted.  This includes contacts from all result pages that might exist.  Please make sure that you perform an update to the filters before you attempt to delete.  An update will allow you to see which contacts will be deleted before you actually delete them.  If you would like to continue with the deletion, please click OK.  Otherwise, please click Cancel.\')" />';

    }

    

    $output_user_label = '';

    $output_opt_in_label = '';

    $output_phone_label = '';

    $output_email_address_label = '';

    $output_address_labels = '';

    

    // If the contacts by user filter, my_contacts by business address, and my contacts by home address is not on, then output the user label

    if (($filter != 'my_contacts_by_business_address') && ($filter != 'my_contacts_by_home_address')) {

        $output_user_label = '<th>' . get_column_heading('User', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';



    }



    // if the filter is not set to either my contacts by business address or my contacts by home address then output the op_in, phone and email address labels

    if (($filter != 'my_contacts_by_business_address') && ($filter != 'my_contacts_by_home_address')) {

        $output_opt_in_label = '<th style="text-align: center;">' . get_column_heading('Opt-In', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

        $output_phone_label = '<th>Phone</th>';

        $output_email_address_label = '<th>' . get_column_heading('Email', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

    

    // else one of the above filters has been selected so output the address table headings

    } else {

        $output_address_labels = '<th>Address</th>';

        $output_address_labels .= '<th>' . get_column_heading('City', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

        $output_address_labels .= '<th>' . get_column_heading('State', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

        $output_address_labels .= '<th>' . get_column_heading('Zip Code', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

        $output_address_labels .= '<th>' . get_column_heading('Country', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

    }

    

    $output_table_headings = '';

    $output_modify_selected_buttons = '';

    

    $output_contact_groups_toggle = '';



    // if this is not the all duplicates view, then output the contact groups toggle

    if ($filter != 'all_duplicate_contacts') {

        // if the user has selected to show contact groups, then prepare toggle to hide contact groups

        if ($_SESSION['software']['view_contacts']['show_contact_groups'] == true) {

            $output_contact_groups_toggle = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contacts.php?filter=' . h($filter) . '&show_contact_groups=false" title="Hide Contact Groups">Hide Groups</a>';

        

        // else set the show groups toggle link's value and label

        } else {

            $output_contact_groups_toggle = '<a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contacts.php?filter=' . h($filter) . '&show_contact_groups=true" title="Show Contact Groups">Show Groups</a>';

        }

    }

    

    // if the all duplicate contacts filter is on then output the appropriate table headings and merge contacts buttons

    if ($filter == 'all_duplicate_contacts') {

        $output_table_headings = 

            '<th style="text-align: center;" id="select_all">Select</th>

            <th>Email</th>

            <th>First Name</th>

            <th>Last Name</th>

            <th>Company</th>

            <th>Address</th>

            ' . $output_phone_label . '

            <th>User</th>

            <th>Contact Groups ' . $output_contact_groups_toggle . '</th>

            <th>Last Modified</th>';

        

        $output_merge_contacts_button = ' <input type="submit" name="submit_data" value="Merge Contacts" class="submit_small_secondary" onclick="return confirm(\'WARNING: All contacts that match the filters will be permanently merged together.  This includes contacts from all result pages that might exist.  Please make sure that you perform an update to the filters before you attempt to merge.  An update will allow you to see which contacts will be merged before you actually merge them.  If you would like to continue with the merge, please click OK.  Otherwise, please click Cancel.\')" />';

        

        // output the merge selected button

        $output_modify_selected_buttons = '<input type="button" value="Merge Selected" class="submit-secondary" onclick="edit_contacts(\'merge\')" />&nbsp;&nbsp;&nbsp;';

    

    // else output the default table headings

    } else {

        $output_table_headings = 

            '<th style="text-align: center;" id="select_all">Select</th>

            <th></th>

            <th>' . get_column_heading('First Name', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>

            <th>' . get_column_heading('Last Name', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>

            <th>' . get_column_heading('Company', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>

            ' . $output_phone_label . '

            ' . $output_email_address_label . '

            ' . $output_user_label . '

            ' . $output_opt_in_label . '

            ' . $output_address_labels . '

            <th>' . $output_contact_groups_toggle . '</th>

            <th>' . get_column_heading('Last Modified', $_SESSION['software']['view_contacts']['sort'], $sort_order, $output_filter_for_links) . '</th>';

        

        // output the opt in and opt out buttons

        $output_modify_selected_buttons = '<input type="button" value="Opt-In Selected" class="submit-secondary" onclick="edit_contacts(\'optin\')" />&nbsp;&nbsp;&nbsp;<input type="button" value="Opt-Out Selected" class="submit-secondary" onclick="edit_contacts(\'optout\')" />&nbsp;&nbsp;&nbsp;';

    

    }

    

    $output .=

    output_header() . '

    <div id="subnav">

        <ul>
            <li><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contacts.php">All My Contacts</a></li>
            <li><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contact_groups.php">All Contact Groups</a></li>

        </ul>

    </div>

    <div id="button_bar">

        <a href="add_contact.php?send_to=' . h(REQUEST_URL) . '">Create Contact</a>

        <a href="import_contacts.php?send_to=' . h(REQUEST_URL) . '">Import Contacts</a>

    </div>

    <div id="content">

        ' . $liveform->output_errors() . '

        ' . $liveform->output_notices() . '

        <a href="#" id="help_link">Help</a>

        <h1>' . $heading . '</h1>

        <div class="subheading">' . $subheading . '</div>

        <form id="advanced_filters_form" action="view_contacts.php" method="get" style="margin-top: 0px">

            <input type="hidden" name="filter" value="' . h($filter) . '">

            <div style="margin: 1em 0em 0em 0em; padding: 0em">

                <table class="field" style="width: 100%;">

                    <tr>

                        <td style="padding:0; width: 160px;"><a href="view_contacts.php?filter=' . h($filter) . '&advanced_filters=' . $output_advanced_filters_value . '" style="' . $show_hide_advanced_filters_button .'white-space: nowrap;" class="button_small">' . $output_advanced_filters_label . ' <img style="vertical-align: top; padding-left: 3px ; margin-top: 2px" src="'. OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/advanced_filters_'. $advanced_filters_icon . '.png"></a></td>

                        <td style="text-align: right;"><span style="'. $show_hide_contact_group_select .'">Group:&nbsp;' . $output_contact_group_selection . '</span></td>

                        <td style="text-align: left;">Show:&nbsp;<select name="filter" onchange="submit_form(\'advanced_filters_form\')">' . get_filter_options($filters_in_array, $filter) . '</select></td>

                        <td style="text-align: right; padding:0; width: 30%"><input type="text" name="query" value="' . h($_SESSION['software']['view_contacts']['query']) . '" /> <input type="submit" name="submit_data" value="Search" class="submit_small_secondary" />' . $output_clear_button . '</td>

                    </tr>

                </table>

            </div>

            ' . $output_advanced_filters . '

            <table style="width: 100%; margin-bottom: .5em; padding: 0em; border-collapse: collapse">

            <tr>

                <td style="vertical-align: bottom; padding: 0;">

                    <span style="font-size: 150%; font-weight: bold;">    ' . $output_date_range_time . '</span>

                    <div style="margin-top: 5px; margin-bottom: 5px;' . $show_hide_date_range . '"><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $decrease_year['start_month'] . '&start_day=' . $decrease_year['start_day'] . '&start_year=' . $decrease_year['start_year'] . '&stop_month=' . $decrease_year['stop_month'] . '&stop_day=' . $decrease_year['stop_day'] . '&stop_year=' . $decrease_year['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $current_year['start_month'] . '&start_day=' . $current_year['start_day'] . '&start_year=' . $current_year['start_year'] . '&stop_month=' . $current_year['stop_month'] . '&stop_day=' . $current_year['stop_day'] . '&stop_year=' . $current_year['stop_year'] . '" class="button_3d_secondary">&nbsp;Year&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $increase_year['start_month'] . '&start_day=' . $increase_year['start_day'] . '&start_year=' . $increase_year['start_year'] . '&stop_month=' . $increase_year['stop_month'] . '&stop_day=' . $increase_year['stop_day'] . '&stop_year=' . $increase_year['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $decrease_month['start_month'] . '&start_day=' . $decrease_month['start_day'] . '&start_year=' . $decrease_month['start_year'] . '&stop_month=' . $decrease_month['stop_month'] . '&stop_day=' . $decrease_month['stop_day'] . '&stop_year=' . $decrease_month['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $current_month['start_month'] . '&start_day=' . $current_month['start_day'] . '&start_year=' . $current_month['start_year'] . '&stop_month=' . $current_month['stop_month'] . '&stop_day=' . $current_month['stop_day'] . '&stop_year=' . $current_month['stop_year'] . '" class="button_3d_secondary">&nbsp;Month&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $increase_month['start_month'] . '&start_day=' . $increase_month['start_day'] . '&start_year=' . $increase_month['start_year'] . '&stop_month=' . $increase_month['stop_month'] . '&stop_day=' . $increase_month['stop_day'] . '&stop_year=' . $increase_month['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $decrease_week['start_month'] . '&start_day=' . $decrease_week['start_day'] . '&start_year=' . $decrease_week['start_year'] . '&stop_month=' . $decrease_week['stop_month'] . '&stop_day=' . $decrease_week['stop_day'] . '&stop_year=' . $decrease_week['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $current_week['start_month'] . '&start_day=' . $current_week['start_day'] . '&start_year=' . $current_week['start_year'] . '&stop_month=' . $current_week['stop_month'] . '&stop_day=' . $current_week['stop_day'] . '&stop_year=' . $current_week['stop_year'] . '" class="button_3d_secondary">&nbsp;Week&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $increase_week['start_month'] . '&start_day=' . $increase_week['start_day'] . '&start_year=' . $increase_week['start_year'] . '&stop_month=' . $increase_week['stop_month'] . '&stop_day=' . $increase_week['stop_day'] . '&stop_year=' . $increase_week['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $decrease_day['start_month'] . '&start_day=' . $decrease_day['start_day'] . '&start_year=' . $decrease_day['start_year'] . '&stop_month=' . $decrease_day['stop_month'] . '&stop_day=' . $decrease_day['stop_day'] . '&stop_year=' . $decrease_day['stop_year'] . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $current_day['start_month'] . '&start_day=' . $current_day['start_day'] . '&start_year=' . $current_day['start_year'] . '&stop_month=' . $current_day['stop_month'] . '&stop_day=' . $current_day['stop_day'] . '&stop_year=' . $current_day['stop_year'] . '" class="button_3d_secondary">&nbsp;Day&nbsp;</a><a href="view_contacts.php?filter=' . h($filter) . '&start_month=' . $increase_day['start_month'] . '&start_day=' . $increase_day['start_day'] . '&start_year=' . $increase_day['start_year'] . '&stop_month=' . $increase_day['stop_month'] . '&stop_day=' . $increase_day['stop_day'] . '&stop_year=' . $increase_day['stop_year'] . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a></div>

                </td>

                <td style="vertical-align: bottom; padding: 0;">

                    <div class="view_summary">

                        Viewing '. number_format($number_of_results) .' of ' . number_format($my_contacts) . ' I can access. ' . number_format($all_contacts) . ' Total&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_data" value="Export Contacts" class="submit_small_secondary" />' . $output_merge_contacts_button . $output_delete_contacts_button . '

                    </div>

                </td>

            </tr>

        </table>

        </form>

        <form name="form" action="edit_contacts.php" method="post" style="margin: 0">

            ' . get_token_field() . '

            <input type="hidden" name="action" />

            <input type="hidden" name="add_to_contact_groups" />

            <input type="hidden" name="remove_from_contact_groups" />

            <input type="hidden" name="send_to" value="' . h(REQUEST_URL) . '" />

            <table class="chart" style="margin-bottom: 5px">

                <tr>

                    ' . $output_table_headings . '

                </tr>

                ' . $output_rows . '

            </table>

            <div class="pagination">

                ' . $output_screen_links . '

            </div>

            <div class="buttons">

                ' . $output_organize_selected_button . $output_modify_selected_buttons . '<input type="button" value="Delete Selected" class="delete" onclick="edit_contacts(\'delete\')" />

            </div>

        </form>

    </div>

    ' . output_footer();



    echo $output;



    $liveform->remove_form('view_contacts');

}
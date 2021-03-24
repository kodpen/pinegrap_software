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

$liveform = new liveform('view_users');

$user = validate_user();
validate_area_access($user, 'manager');

$output_clear_button = '';

// If there is a filter set.
if (isset($_GET['filter']) == true) {
    // Send the filter to the search form.
    $filter = $_GET['filter'];
} else {
    $filter = 'default';
}

$filter_for_links = '&filter=' . $filter;
$output_filter_for_links = h($filter_for_links);

// build filters array
$filters_in_array = 
    array(
        'all_my_users'=>'All My Users',
        'my_registered_users'=>'My Registered Users',
        'my_private_users'=>'My Private Users',
        'my_member_users'=>'My Member Users',
        'my_content_managers'=>'My Content Managers',
        'my_calendar_managers'=>'My Calendar Managers',
        'my_submitted_forms_managers'=>'My Submitted Forms Managers',
        'my_visitor_report_managers'=>'My Visitor Report Managers',
        'my_contact_managers'=>'My Contact Managers',
        'my_campaign_managers'=>'My Campaign Managers',
        'my_commerce_managers'=>'My Commerce Managers'
    );

// if user is site administrator then output the site administrator filter.
if ($user['role'] < 1) {
    $filters_in_array['all_site_designers'] = 'All Site Designers';
    $filters_in_array['all_site_administrators'] = 'All Site Administrators';
}

// if user is site designer then output the site designer filter.
if ($user['role'] < 2) {
    $filters_in_array['all_site_managers'] = 'All Site Managers';
}

// If there is a query then store it in a session
if (isset($_REQUEST['query']) == true) {
    $_SESSION['software']['view_users']['query'] = $_REQUEST['query'];
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['view_users']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['view_users']['query']) == true) && ($_SESSION['software']['view_users']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?filter=' . h(escape_javascript($filter)) . '&clear=true' . '\'" class="submit_small_secondary" />';
}

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['view_users']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['view_users']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    $_SESSION['software']['view_users']['order'] = $_REQUEST['order'];
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

// If the filter is not default or all my users view.
if ($filter == 'default') {
    
    // If sort value is one of the following set sort value to default.
    switch ($_SESSION['software']['view_users']['sort']) {
        case 'E-mail Address':
        case 'Start Page':
        case 'Last Modified':
            $_SESSION['software']['view_users']['sort'] = 'Last Modified';
            $_SESSION['software']['view_users']['order'] = 'desc';
            break;
    }
    
    // Output column headers.
    $output_user_role_column_heading = '<th>' . get_column_heading('Role', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    $output_private_folder_access_column_heading = '<th style="text-align: center">Private User</th>';
    $output_member_user_column_heading = '<th style="text-align: center">' . get_column_heading('Member User', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    $output_manage_content_column_heading = '<th style="text-align: center">Content Manager</th>';
    $output_manage_calendars_column_heading = '<th style="text-align: center">Calendar Manager</th>';
   
    // if forms module is on
    if (FORMS === true) {
        $output_manage_forms_column_header = '<th style="text-align: center">Forms Manager</th>';
    }
    
    $output_view_visitors_column_header = '<th style="text-align: center">Visitor Manager</th>';
    $output_manage_contacts_column_header = '<th style="text-align: center">Contact Manager</th>';
    $output_manage_email_column_header = '<th style="text-align: center">Campaign Manager</th>';
    
    // If ecommerce module is on
    if (ECOMMERCE === true) {
        $output_manage_ecommerce_column_header = '<th style="text-align: center">Commerce Manager</th>';
    }
    
    $output_manage_users_column_header = '<th style="text-align: center">' . get_column_heading('Site Manager', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    $output_edit_design_column_header = '<th style="text-align: center">' . get_column_heading('Site Designer', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    
// Else if the user is not on the all users view.
} else {
    
    // If sort value is one of the following set sort value to default.
    switch ($_SESSION['software']['view_users']['sort']) {
        case 'Role':
        case 'Member User':
        case 'Site Manager':
        case 'Site Designer':
            $_SESSION['software']['view_users']['sort'] = 'Last Modified';
            $_SESSION['software']['view_users']['order'] = 'desc';
            break;
    }
    
    // Select table column
    $sql_select_column .= ',
        user.user_home';
    
    // Output column headers
    $output_email_address_column_header = '<th>' . get_column_heading('E-mail Address', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    $output_user_start_page_column_heading = '<th>' . get_column_heading('Start Page', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
    $output_last_modified_column_header = '<th>' . get_column_heading('Last Modified', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>';
}

// If the user is not viewing the my manager, administrators, designers or content managers
if (($filter != 'default') && ($filter != 'all_site_administrators') && ($filter != 'all_site_designers') && ($filter != 'all_site_managers')) {
    
    // Select table column
    $sql_select_column .= ',
        contacts.id as contact_id,
        contacts.salutation as contact_salutation,
        contacts.first_name as contact_first_name,
        contacts.last_name as contact_last_name,
        contacts.nickname as contact_nickname,
        contacts.suffix as contact_suffix';
    
    // Output column heading
    $output_user_contact_column_header = '<th>User\'s Contact</th>';
}

switch ($filter) {
    case 'my_private_users':
        // Output column heading
        $output_private_folder_access_column_heading = '<th>Private Folders</th>';
        break;
        
    case 'my_content_managers':
        // Output content column heading
        $output_manage_content_column_heading = '<th>Folders</th>';
        break;
        
    case 'my_calendar_managers':
        // Output content column
        $output_manage_calendars_column_heading = '<th>Calendars</th>';
        break;
        
    case 'my_submitted_forms_managers':
        // if forms module is on, then output manage forms header
        if (FORMS === true) {
            
            $all_custom_form_pages = array();
            
            // Get all custom form pages
            $query = "
                SELECT
                    custom_form_pages.form_name,
                    page.page_folder
                FROM page
                LEFT JOIN custom_form_pages ON custom_form_pages.page_id = page.page_id";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            while ($row=mysqli_fetch_assoc($result)) {
                
                // Fill array with all custom forms
                $all_custom_form_pages[] = $row;
            }
            
            // Output column header
            $output_manage_forms_column_header = '<th>Custom Forms</th>';
        }
        break;
        
    case 'my_contact_managers':
        // Output column headings
        $output_manage_contacts_column_header = '<th>Contact Groups</th>';
        $output_manage_email_column_header = '<th style="text-align: center">Send Campaigns</th>';
        break;
    
    case 'my_campaign_managers':
        // Output column heading
        $output_manage_contacts_column_header = '<th>Contact Groups</th>';
        $output_manage_email_column_header = '<th style="text-align: center">Send Campaigns Only</th>';
        break;
}

switch ($_SESSION['software']['view_users']['sort']) {
    case 'Username':
        $sort_column = 'user.user_username';
        break;

    case 'E-mail Address':
        $sort_column = 'user.user_email';
        break;

    case 'Role':
        $sort_column = 'user.user_role';
        break;
        
    case 'Member User':
        $sort_column = 'user_member_id';
        break;
        
    case 'Start Page':
        $sort_column = 'user.user_home';
        break;
        
    case 'Site Manager':
    case 'Site Designer':
        $sort_column = 'user.user_role';
        break;

    case 'Last Modified':
        $sort_column = 'user.user_timestamp';
        break;

    default:
        $sort_column = 'user.user_timestamp';
        $_SESSION['software']['view_users']['sort'] = 'Last Modified';
        $_SESSION['software']['view_users']['order'] = 'desc';
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['view_users']['order']) == false) {
    $_SESSION['software']['view_users']['order'] = 'asc';
}

// assume that the id does not need to be distinct, until we find out otherwise
$sql_select_id = "user.user_id";

// assume that the from table is the user table until we find out otherwise
$sql_from_table = "user";

// assume that we don't need to join the user table, until we find out otherwise
$sql_join_user_table = "";

// Switch between the filters.
switch($filter) {
    case 'my_registered_users':
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }
        
        // Set the query filter.
        $where .= "(user.user_contact IS NOT NULL)";
        
        // Change the heading and subheading.
        $heading = 'My Registered Users';
        $subheading = 'All my users created through website registration.';
        break;

    case 'my_private_users':
        
        // set select id to be distinct, because we will select from aclfolder
        $sql_select_id = "DISTINCT user.user_id";

        // set the from table
        $sql_from_table = "aclfolder";

        // join user table because we are selecting from aclfolder for this filter
        $sql_join_user_table = "LEFT JOIN user ON aclfolder.aclfolder_user = user.user_id";
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < '3') OR (aclfolder.aclfolder_rights = '1'))";

        // Change the heading and subheading.
        $heading = 'My Private Users';
        $subheading = 'All my users that have access to view one or more private folders.';
        
        break;

    case 'my_member_users':
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }
        
        // Set the query filter.
        $where .= "(contacts.member_id != '')";
        // Change the heading and subheading.
        $heading = 'My Member Users';
        $subheading = 'All my users that are associated with a contact that contains a member id.';
        break;

    case 'my_content_managers':
        // set select id to be distinct, because we will select from aclfolder
        $sql_select_id = "DISTINCT user.user_id";

        // set the from table
        $sql_from_table = "aclfolder";

        // join user table because we are selecting from aclfolder for this filter
        $sql_join_user_table = "LEFT JOIN user ON aclfolder.aclfolder_user = user.user_id";
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < '3') OR (aclfolder.aclfolder_rights = '2'))";

        // Change the heading and subheading.
        $heading = 'My Content Managers';
        $subheading = 'All my users that have edit access to at least one folder.';

        break;

    case 'my_calendar_managers':
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_calendars = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Calendar Managers';
        $subheading = 'All my users that can add events to at least one calendar.';
        break;

    case 'my_submitted_forms_managers':
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_forms = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Submitted Forms Managers';
        $subheading = 'All my users that can view and edit data collected by at least one custom form.';
        break;

    case 'my_visitor_report_managers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_visitors = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Visitor Report Managers';
        $subheading = 'All my users that can view and edit all visitor reports.';
        break;

    case 'my_contact_managers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_contacts = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Contact Managers';
        $subheading = 'All my users that can view and edit all contacts in at least one contact group.';
        break;

    case 'my_campaign_managers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_emails = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Campaign Managers';
        $subheading = 'All my users that can send e-mail campaigns to one or more contact groups.';
        break;

    case 'my_commerce_managers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "((user.user_role < 3) OR (user.user_manage_ecommerce = 'yes'))";

        // Change the heading and subheading.
        $heading = 'My Commerce Managers';
        $subheading = 'All my users that can view and edit all products, product groups, offers, and orders.';
        break;

    case 'all_site_managers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "(user.user_role < 3)";

        // Change the heading and subheading.
        $heading = 'All Site Managers';
        $subheading = 'All users that can update site settings, and create or import other users and grant them privileges.';
        break;

    case 'all_site_designers':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "(user.user_role < 2)";

        // Change the heading and subheading.
        $heading = 'All Site Designers';
        $subheading = 'All users that can update site settings, site designs, and create or import site managers.';
        break;

    case 'all_site_administrators':
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";

        // else where is not blank, so add and
        } else {
            $where .= " AND ";
        }

        // Set the query filter.
        $where .= "(user.user_role < 1)";

        // Change the heading and subheading.
        $heading = 'All Site Administrators';
        $subheading = 'All site administrator user accounts.';
        
        break;

    default:

        // Select columns
        $sql_select_column .= ',
            contacts.member_id AS user_member_id';

        // Change the heading and subheading.
        $heading = 'All My Users';
        $subheading = 'All users that I have access too.';
        
        break;
}

// If the user is not viewing all site administrators, designers or managers
if (($filter != 'all_site_administrators') || ($filter != 'all_site_designers') || ($filter != 'all_site_managers')) {
    
    // Join contacts table
    $sql_join_contacts_table = "LEFT JOIN contacts ON user.user_contact = contacts.id";
} else {
    $sql_join_contacts_table = "";
}
        
$my_users = 0;
$all_users = 0;

// Get number of all users for filter.
$query = "SELECT
            COUNT($sql_select_id)
         FROM $sql_from_table
         $sql_join_user_table
         $sql_join_contacts_table
         $where";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_users = $row[0];

// if user is an administrator, then select all users
if ($user['role'] == 0) {
    // if where is blank, then add the start of the where clause
    if ($where == '') {
        $where .= "WHERE ";

    // else where is not blank, so add and
    } else {
        $where .= " AND ";
    }

    $where .= '(user.user_role >= ' . $user['role'] . ')';

// else user is not an administrator, so only show users that have less power than current user
} else {
    // if where is blank, then add the start of the where clause
    if ($where == '') {
        $where .= "WHERE ";

    // else where is not blank, so add and
    } else {
        $where .= " AND ";
    }

    $where .= '(user.user_role > ' . $user['role'] . ')';
}

// Get number of user that user has access to manage.
$query = "SELECT
            COUNT($sql_select_id)
         FROM $sql_from_table
         $sql_join_user_table
         $sql_join_contacts_table
         $where";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$my_users = $row[0];

$search_query = mb_strtolower($_SESSION['software']['view_users']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', user.user_username, user.user_email)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['view_users']['query'])) {
    // If where is blank
    if ($where == '') {
        $where .= ' WHERE ';

    // else where is not blank, so add and
    } else {
        $where .= ' AND ';
    }

    $where .= "($sql_search) ";
}

/* define range depending on screen value by using a limit clause in the SQL statement */
// define the maximum number of results
$max = 100;
// determine where result set should start
$start = $screen * $max - $max;
$limit = "LIMIT $start, $max";

// get total number of results for all screens, so that we can output links to different screens
$query = "SELECT COUNT($sql_select_id)
         FROM $sql_from_table
         $sql_join_user_table
         $sql_join_contacts_table
         $where";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$number_of_results = $row[0];

// get number of screens
$number_of_screens = ceil($number_of_results / $max);

// build Previous button if necessary
$previous = $screen - 1;
// if previous screen is greater than zero, output previous link
if ($previous > 0) {
    $output_screen_links .= '<a class="submit-secondary" href="view_users.php?screen=' . $previous . $output_filter_for_links . '">&lt;</a>&nbsp;&nbsp;';
}

// if there are more than one screen
if ($number_of_screens > 1) {
    $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_users.php?screen=\' + this.options[this.selectedIndex].value +\'' .  h(escape_javascript($filter_for_links)) . '\')">';

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
    $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_users.php?screen=' . $next .  $output_filter_for_links . '">&gt;</a>';
}

// if user is an administrator, get number of administrators in order to prevent the last administrator from being deleted
if ($user['role'] == 0) {
    $query = "SELECT COUNT(user_id) FROM user WHERE user_role = '0'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_administrators = $row[0];
}

// Set checkmark in variable
$output_checkmark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';

/* get results for just this screen*/
$query =
    "SELECT
        $sql_select_id as id,
        user.user_username as username,
        user.user_email as email,
        user.user_role as role,
        user.user_manage_contacts as manage_contacts,
        user.user_manage_visitors as manage_visitors,
        user.user_manage_ecommerce as manage_ecommerce,
        user.user_manage_forms as manage_forms,
        user.user_manage_calendars as manage_calendars,
        user.user_manage_emails as manage_emails,
        user.user_contact as user_contact,
        user_2.user_username as user,
        user.user_timestamp as timestamp" .
        $sql_select_column . "
    FROM $sql_from_table
    $sql_join_user_table
    LEFT JOIN user as user_2 ON user.user_user = user_2.user_id
    $sql_join_contacts_table
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['view_users']['order']) . "
    $limit";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $username = $row['username'];
    $email = $row['email'];
    $role = $row['role'];
    $manage_contacts = $row['manage_contacts'];
    $manage_visitors = $row['manage_visitors'];
    $manage_ecommerce = $row['manage_ecommerce'];
    $manage_forms = $row['manage_forms'];
    $manage_emails = $row['manage_emails'];
    $manage_calendars = $row['manage_calendars'];
    $last_modified_username = $row['user'];
    $user_contact = $row['user_contact'];
    
    // If the filter is on default or all my users view.
    if ($filter == 'default') {
        
        // Set database variables
        $user_member_id = $row['user_member_id'];
        $aclfolder_rights = $row['aclfolder_rights'];
    
    // else the user in not on the all my users view.
    } else {
        
        // Set database variables
        $user_home = $row['user_home'];
        $timestamp = $row['timestamp'];
    }
    
    // If the user is not viewing the my manager, administrators, designers or all my users
    if (($filter != 'default') || ($filter != 'all_site_administrators') || ($filter != 'all_site_designers') || ($filter != 'all_site_managers')) {
        
        // Set database variables
        $contact_id = $row['contact_id'];
        $contact_salutation = $row['contact_salutation'];
        $contact_first_name = $row['contact_first_name'];
        $contact_last_name = $row['contact_last_name'];
        $contact_nickname = $row['contact_nickname'];
        $contact_suffix = $row['contact_suffix'];
    }
    
    // If the user is viewing the my registered users or my member users view
    if (($filter == 'my_registered_users') || ($filter == 'my_member_users')) {
        
        // Set database variables
        $contact_timestamp = $row['contact_timestamp'];
    }
    
    // get current date
    $current_date = date('Y-m-d');

    // if this user is not an administrator or the number of administrators is greater than one, then we can allow this user to be deleted, so prepare checkbox
    if (($role != 0) || ($number_of_administrators > 1)) {
        $output_checkbox = '<input type="checkbox" name="users[]" value="' . $id . '" class="checkbox" />';

    } else {
        $output_checkbox = '<input type="checkbox" name="users[]" value="' . $id . '" class="checkbox" disabled="disabled" style="display: none;" />';
    }

    // if user is an administrator, designer, or manager, then user has access to contacts, e-commerce, forms, and e-mails automatically
    if ($role < 3) {
        $manage_contacts = 'yes';
        $manage_visitors = 'yes';
        $manage_ecommerce = 'yes';
        $manage_forms = 'yes';
        $manage_emails = 'yes';
        $manage_calendars = 'yes';
    }

    $output_link_url = 'edit_user.php?id=' . $id . '&send_to=' . h(escape_javascript(urlencode(REQUEST_URL)));

    // If the filter is not default or all my users view.
    if ($filter == 'default') {
        // output table columns
        $output_user_role_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_role_name($role) . '</td>';
        
        // if user has a memeber id
        if ($user_member_id != '') {
            
            // output checkmark
            $output_member_user_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_checkmark . '</td>';
            
        // Else output a blank cell
        } else {
            $output_member_user_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">&nbsp;</td>';
        }
        
        // Get folders user has access to
        $query2 = "SELECT DISTINCT aclfolder_user FROM aclfolder WHERE ((aclfolder_user = '" . $id . "') AND (aclfolder_rights = 1))";
        $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
        $row2 = mysqli_fetch_assoc($result2);
        
        // if user is a manager or above, or if the basic user has a aclfolder_user value
        if (($role < 3) || ($row2['aclfolder_user'] != '')) {
            $output_private_folder_access_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_checkmark . '</td>';
        
        // Else output the checkmark
        } else {
            $output_private_folder_access_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">&nbsp;</td>';
        }
        
        // If user can edit content
        if (($role < 3) || (no_acl_check($id) == TRUE)) {
            // Output checkmark
            $output_manage_content_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_checkmark . '</td>';
        
        // Else output the a blank cell
        } else {
            $output_manage_content_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">&nbsp;</td>';
        }
        
        // if user has access to manage calendars
        if ($manage_calendars == 'yes') {
            $manage_calendars = $output_checkmark;

        // else user does not have access to manage calendars
        } else {
            $manage_calendars = '';
        }
        
        // If forms module is on
        if (FORMS === true) {
            // if user has access to manage forms
            if ($manage_forms == 'yes') {
                $manage_forms = $output_checkmark;

            // else user does not have access to manage forms
            } else {
                $manage_forms = '';
            }
            
            // output column checkmark
            $output_manage_forms_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_forms . '</td>';
        }
        
        // output column
        $output_manage_calendars_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_calendars . '</td>';
        
        // if user has access to manage visitors
        if ($manage_visitors == 'yes') {
            $manage_visitors = $output_checkmark;

        // else user does not have access to manage contacts
        } else {
            $manage_visitors = '';
        }
        
        // output column
        $output_view_visitors_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_visitors . '</td>';
        
        // if user has access to manage contacts
        if ($manage_contacts == 'yes') {
            $manage_contacts = $output_checkmark;

        // else user does not have access to manage contacts
        } else {
            $manage_contacts = '';
        }
        
        // output column
        $output_manage_contacts_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_contacts . '</td>';
        
        if (ECOMMERCE === true) {
            // if user has access to manage e-commerce
            if ($manage_ecommerce == 'yes') {
                $manage_ecommerce = $output_checkmark;

            // else user does not have access to manage e-commerce
            } else {
                $manage_ecommerce = '';
            }
            
            // output column
            $output_manage_ecommerce_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_ecommerce . '</td>';
        }
        
        // If user is manager or above
        if ($role < 3) {
            
            // output checkmark
            $output_manage_users_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_checkmark . '</td>';
        } else {
            
            // output blank cell
            $output_manage_users_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">&nbsp;</td>';
        }
        // If user is a designer or administrator
        if ($role <= 1) {
            // output checkmark
            $output_edit_design_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $output_checkmark . '</td>';
        } else {
            
            // output blank cell
            $output_edit_design_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">&nbsp;</td>';
        }
    
    // Else if the user is not on the all my users view.
    } else {    
        // If user home is not set to none
        if ($user_home != 0) {
            // get start page name
            $user_start_page = get_page_name($user_home);
           
        // Else user has no start page
        } else {
            $user_start_page = '';
        }
        
        // output columns
        $output_email_address_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($email) . '</td>';
        $output_user_start_page_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($user_start_page) . '</td>';
        $output_last_modified_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $timestamp)) . ' by ' . h($last_modified_username) . '</td>';
    }
    
    // If the user is not viewing the my manager, administrators, designers or all my users
    if (($filter != 'default') && ($filter != 'all_site_administrators') && ($filter != 'all_site_designers') && ($filter != 'all_site_managers')) {
        $output_contact_name = '';
        
        // Output the contact name
        // If there is a nickname
        if ($contact_nickname != '') {
            
            // output nickname
            $output_contact_name = h($contact_nickname); 
        
        // else if there is no nickname
        } else {
            
            // If there is a salutation
            if ($contact_salutation != '') {
                
                // output the salutation
                $output_contact_name = h($contact_salutation) . ' ';
            }
            
            // If there is a first name
            if ($contact_first_name != '') {
                
                // output first name
                $output_contact_name .= h($contact_first_name) . ' ';
            }
            
            // If there is a last name
            if ($contact_last_name != '') {
                
                // output last name
                $output_contact_name .= h($contact_last_name);
            }
            
            // If there is a last name
            if ($contact_suffix != '') {
                
                // output last name
                $output_contact_name .= ' ' . h($contact_suffix);
            }
        }
        
        // If there is a contact name
        if ($output_contact_name != '') {
            
            // Output table cell with a link to the edit contacts screen
            $output_user_contact_column = '<td><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/edit_contact.php?id=' . $contact_id . '">' . $output_contact_name . '</a></td>';
            
        // Else output a blank cell
        } else {
            $output_user_contact_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">&nbsp;</td>';
        }
    }
    
    switch ($filter) {
        case 'my_private_users':
            
            // If user is a manager or above
            if ($role < 3) {
                // Output all
                $output_private_folder_access_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">All</td>';
            } else {
                // Else find the folders user has access to
                // Initialize variables
                $folder_names_that_user_has_access_to = '';
                
                // Get all folders user has access to
                $query2 = "
                    SELECT
                        folder.folder_name
                    FROM folder 
                    LEFT JOIN aclfolder ON aclfolder.aclfolder_folder = folder.folder_id
                    WHERE 
                        ((aclfolder_user = '" . $id . "')
                        AND (aclfolder_rights = '1'))
                    ORDER BY folder_name ASC";
                $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
                
                // Loop through all folders that user has access to.
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    if ($folder_names_that_user_has_access_to) {
                        $folder_names_that_user_has_access_to .= ',<br />';
                    }
                    
                    $folder_names_that_user_has_access_to .= h($row2['folder_name']);
                }
                
                // Output content column
                $output_private_folder_access_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                        <ul style="margin: 0em; padding: 0em; list-style-type: none">
                            '. $folder_names_that_user_has_access_to . '
                        </ul>
                    </td>';
            }
            
            break;
            
        case 'my_content_managers':
            // If user is a manager or above
            if ($role < 3) {
                // Output all
                $output_manage_content_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">All Pages and Files</td>';
            } else {
                // Initialize variables
                $folder_names_that_user_has_access_to = '';
                
                // Get all folders user has access to
                $query2 = "
                    SELECT
                        folder.folder_name,
                        folder.folder_parent
                    FROM folder 
                    LEFT JOIN aclfolder ON aclfolder.aclfolder_folder = folder.folder_id
                    WHERE 
                        ((aclfolder_user = '" . $id . "')
                        AND (aclfolder_rights = '2'))
                    ORDER BY folder_name ASC";
                $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
                
                // Loop through all folders that user has access to.
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    
                    if ($folder_names_that_user_has_access_to) {
                        $folder_names_that_user_has_access_to .= ',<br />';
                    }
                    
                    // If the folder is the top level folder
                    if ($row2['folder_parent'] == 0) {
                        
                        // Output folder name and then break out of loop.
                        $folder_names_that_user_has_access_to .= h($row2['folder_name']);
                        break;
                        
                    // Else if the top most folder was not found continue loop
                    } else {
                        // output folder names
                        $folder_names_that_user_has_access_to .= h($row2['folder_name']);
                    }
                }
                
                // Output content column
                $output_manage_content_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                        <ul style="margin: 0em; padding: 0em; list-style-type: none">
                            '. $folder_names_that_user_has_access_to . '
                        </ul>
                    </td>';
            }
            
            break;
            
        case 'my_calendar_managers':
            
            // If user is a manager or above
            if ($role < 3) {
                // Output all
                $output_manage_calendars_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">All</td>';
            } else {
                // Initialize variables
                $calendars_that_user_has_access_to = '';
                    
                // Get all calendars that user has access to manage
                $query2 = "
                    SELECT
                        calendars.name
                    FROM users_calendars_xref
                    LEFT JOIN calendars ON calendars.id = users_calendars_xref.calendar_id
                    WHERE 
                        users_calendars_xref.user_id = '" . $id . "'";
                $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    if ($calendars_that_user_has_access_to) {
                        $calendars_that_user_has_access_to .= ',<br />';
                    }
                    
                    $calendars_that_user_has_access_to .= h($row2['name']);
                }
                
                // Output content column
                $output_manage_calendars_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                        <ul style="margin: 0em; padding: 0em; list-style-type: none">
                            '. $calendars_that_user_has_access_to . '
                        </ul>
                    </td>';
            }
            
            break;
            
        case 'my_submitted_forms_managers':
            if (FORMS === true) {
                
                // If user is a manager or above
                if ($role < 3) {
                    // Output all
                    $output_manage_forms_column =
                        '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">All</td>';
                } else {
                    $submitted_forms_that_user_has_access_to = '';
                
                    // Get folders that user has access to
                    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($id);
                    
                    foreach ($all_custom_form_pages as $custom_form_page) {
                        // if user has access to folder that custom form is in, then output custom form
                        if (in_array($custom_form_page['page_folder'], $folders_that_user_has_access_to) == true) {
                            if ($custom_form_page['form_name'] != '') {
                                
                                if ($submitted_forms_that_user_has_access_to) {
                                    $submitted_forms_that_user_has_access_to .= ',<br />';
                                }
                                
                                $submitted_forms_that_user_has_access_to .= h($custom_form_page['form_name']);
                            }
                        }
                    }
                    
                    // Output content column
                    $output_manage_forms_column =
                        '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                            <ul style="margin: 0em; padding: 0em; list-style-type: none">
                                '. $submitted_forms_that_user_has_access_to . '
                            </ul>
                        </td>';
                }
            }
            break;
            
        case 'my_campaign_managers':
            // if user has access to manage campaigns
            if (($role == 3) && ($manage_emails == 'yes') && ($manage_contacts != 'yes')) {
                $manage_emails = $output_checkmark;

            // else user does not have access to manage campaigns
            } else {
                $manage_emails = '';
            }

            // Output content column
            $output_manage_email_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_emails . '</td>';
            break;
    }
    
    // If contact or campaigns managers filters are selected
    if (($filter == 'my_contact_managers') || ($filter == 'my_campaign_managers')) {
        
        // If user is a manager or above
            if ($role < 3) {
                // Output all
                $output_manage_contacts_column =
                    '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">All</td>';
            } else {
                
            $contact_group_that_user_has_access_to = '';
                
            // Get contact groups user can edit
            $query2 = "
                SELECT
                    contact_groups.name as contact_group_name
                FROM users_contact_groups_xref
                LEFT JOIN contact_groups ON contact_groups.id = users_contact_groups_xref.contact_group_id
                WHERE users_contact_groups_xref.user_id = '" . $id . "'";
            $result2 = mysqli_query(db::$con, $query2) or output_error('Query failed.');
            while ($row2 = mysqli_fetch_assoc($result2)) {
                // If there is a contact group then prepare to output the contact group
                if ($row2['contact_group_name'] != '') {
                    if ($contact_group_that_user_has_access_to) {
                        $contact_group_that_user_has_access_to .= ',<br />';
                    }
                    
                    $contact_group_that_user_has_access_to .= h($row2['contact_group_name']);
                }
            }
            
            // Output content column
            $output_manage_contacts_column =
                '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <ul style="margin: 0em; padding: 0em; list-style-type: none">
                        '. $contact_group_that_user_has_access_to . '
                    </ul>
                </td>';
        }
    }
    
    // If user is on the all my uses view or on the site administrator, designer, manager, my contact managers or my contact managers views.
    if (($filter == 'default') || ($filter == 'my_contact_managers')) {
        
        // if user has access to manage e-mails
        if ($manage_emails == 'yes') {
            $manage_emails = $output_checkmark;

        // else user does not have access to manage e-mails
        } else {
            $manage_emails = '';
        }
    
        // output column
        $output_manage_email_column = '<td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center">' . $manage_emails . '</td>';
    }

    $firstChar = strtoupper (mb_substr($username, 0, 1, "UTF-8"));
    $output_rows .=
        '<tr>
            <td class="selectall">' . $output_checkbox . '</td>
            <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 37 37" style="height:37px;width:37px;">
                    <g>
                      <circle style="fill:#512da8;" cx="18.5" cy="18.5" r="18.5"></circle>
                      <text style="fill:#f5f5f6;font-size:18.5" x="18.5" y="18.5" text-anchor="middle" dy=".3em">' . $firstChar  . '</text>
                    </g>
                </svg>
            </td>
            <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">' . h($username) . '</td>
            ' . $output_email_address_column . '
            ' . $output_user_role_column . '
            ' . $output_user_start_page_column . '
            ' . $output_private_folder_access_column . '
            ' . $output_member_user_column . '
            ' . $output_manage_content_column . '
            ' . $output_manage_calendars_column . '
            ' . $output_manage_forms_column . '
            ' . $output_view_visitors_column . '
            ' . $output_manage_contacts_column . '
            ' . $output_manage_email_column . '
            ' . $output_manage_ecommerce_column . '
            ' . $output_manage_users_column . '
            ' . $output_edit_design_column . '
            ' . $output_user_contact_column . '
            ' . $output_last_modified_column . '
        </tr>';
}

echo
    output_header(). '
    <div id="subnav"></div>
    <div id="button_bar">
        <a href="add_user.php?send_to=' . h(REQUEST_URL) . '">Create User</a>
        <a href="import_users.php?send_to=' . h(REQUEST_URL) . '">Import Users</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>' . $heading . '</h1>
        <div class="subheading">' . $subheading . '</div>
        <form id="search_form" action="view_users.php" method="get" class="search_form">
            <input type="hidden" name="filter" value="' . h($filter) . '">
            Show: <select name="filter" onchange="submit_form(\'search_form\')">' . get_filter_options($filters_in_array, $filter) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['view_users']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($my_users) . ' I can access. ' . number_format($all_users) . ' Total
        </div>
        <form name="form" action="delete_users.php" method="post" style="margin: 0" class="disable_shortcut">
            ' . get_token_field() . '
            <input type="hidden" name="send_to" value="' . h(REQUEST_URL) . '" />
            <table class="chart">
                <tr>
                    <th style="text-align: center;" id="select_all">Select</th>
                    <th></th>
                    <th>' . get_column_heading('Username', $_SESSION['software']['view_users']['sort'], $_SESSION['software']['view_users']['order'], $output_filter_for_links) . '</th>
                    ' . $output_email_address_column_header . '
                    ' . $output_user_role_column_heading . '
                    ' . $output_user_start_page_column_heading . '
                    ' . $output_private_folder_access_column_heading . '
                    ' . $output_member_user_column_heading . '
                    ' . $output_manage_content_column_heading . '
                    ' . $output_manage_calendars_column_heading . '
                    ' . $output_manage_forms_column_header . '
                    ' . $output_view_visitors_column_header . '
                    ' . $output_manage_contacts_column_header . '
                    ' . $output_manage_email_column_header . '
                    ' . $output_manage_ecommerce_column_header . '
                    ' . $output_manage_users_column_header . '
                    ' . $output_edit_design_column_header . '
                    ' . $output_user_contact_column_header . '
                    ' . $output_last_modified_column_header . '
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <div class="buttons">
                <input type="submit" value="Delete Selected" class="delete" onclick="return confirm(\'WARNING: The selected user(s) will be permanently deleted.\')" />
            </div>
        </form>
    </div>' .
    output_footer();

$liveform->remove_form('view_users');

function get_role_name($role_id)
{
    switch ($role_id)
    {
        case 0:
            return('Administrator');
            break;

        case 1:
            return('Designer');
            break;

        case 2:
            return('Manager');
            break;

        case 3:
            return('User');
            break;
    }
}
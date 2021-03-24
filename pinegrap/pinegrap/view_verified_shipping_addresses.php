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
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('view_verified_shipping_addresses');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_verified_shipping_addresses'][$key] = trim($value);
    }
}

// if state is not set yet, set default to [All]
if (isset($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id']) == false) {
    $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id'] = '[All]';
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

// check if foreign states exist
// foreign states are states that belong to a country that is not the default country
$query =
    "SELECT COUNT(*)
    FROM states
    LEFT JOIN countries ON states.country_id = countries.id
    WHERE countries.default_selected = '0'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);

$foreign_states = FALSE;

// if foreign states exist, then remember that
if ($row[0] > 0) {
    $foreign_states = TRUE;
}

// get all states in order to prepare options for state pick list
$query = 
    "SELECT
        states.id,
        states.name,
        countries.name as country_name
    FROM states
    LEFT JOIN countries ON states.country_id = countries.id
    ORDER BY
        countries.name ASC,
        states.name ASC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$states = array();

// loop through all states in order to add states to array
while ($row = mysqli_fetch_assoc($result)){
    $states[] = $row;
}

$output_state_options = '';

// loop through all states in order to prepare options for state pick list
foreach ($states as $state) {
    // if this state is equal to the selected state, then select it
    if ($state['id'] == $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id']) {
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }
    
    // if foreign states exist, then output state label with country name prefix in order to prevent confusion
    if ($foreign_states == TRUE) {
        $output_state_label = h($state['country_name']) . ': ' . h($state['name']);
        
    // else foreign states do not exist, so just use state name for label
    } else {
        $output_state_label = h($state['name']);
    }
    
    // get the number of verified shipping addresses that are assigned to this state
    $query = "SELECT COUNT(*) FROM verified_shipping_addresses WHERE state_id = '" . $state['id'] . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $number_of_verified_shipping_addresses = $row[0];
    
    $output_state_options .= '<option value="' . $state['id'] . '"' . $selected . '>' . $output_state_label . ' (' . number_format($number_of_verified_shipping_addresses) . ')</option>';
}

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query']) == true) && ($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort']) {
    case 'Company':
        $sort_column = 'verified_shipping_addresses.company';
        break;
        
    case 'Address 1':
        $sort_column = 'verified_shipping_addresses.address_1';
        break;
        
    case 'Address 2':
        $sort_column = 'verified_shipping_addresses.address_2';
        break;
        
    case 'City':
        $sort_column = 'verified_shipping_addresses.city';
        break;
        
    case 'State':
        $sort_column = 'states.name';
        break;
        
    case 'Zip Code':
        $sort_column = 'verified_shipping_addresses.zip_code';
        break;
        
    case 'Country':
        $sort_column = 'countries.name';
        break;

    case 'Created':
        $sort_column = 'verified_shipping_addresses.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'verified_shipping_addresses.last_modified_timestamp';
        break;

    default:
        $sort_column = 'verified_shipping_addresses.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order'] = 'asc';
}

$all_verified_shipping_addresses = 0;

// get the total number of verified shipping addresses
$query = "SELECT COUNT(*) FROM verified_shipping_addresses";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_verified_shipping_addresses = $row[0];

$where = "";

// if user has selected a state, then prepare where clause
if ($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id'] != '[All]') {
    $where = "WHERE (verified_shipping_addresses.state_id = '" . escape($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id']) . "') ";
}

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query']) == true) && ($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query'] != '')) {
    // if this is the first where clause, then add where first
    if ($where == '') {
        $where .= "WHERE ";
        
    // else this is not the first where clause, so add and
    } else {
        $where .= "AND ";
    }
    
    $where .= "(LOWER(CONCAT_WS(',', verified_shipping_addresses.company, verified_shipping_addresses.address_1, verified_shipping_addresses.address_2, verified_shipping_addresses.city, states.name, verified_shipping_addresses.zip_code, countries.name, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query'])) . "%')";
}

// get all verified shipping addresses
$query =
    "SELECT
        verified_shipping_addresses.id,
        verified_shipping_addresses.company,
        verified_shipping_addresses.address_1,
        verified_shipping_addresses.address_2,
        verified_shipping_addresses.city,
        states.name as state_name,
        verified_shipping_addresses.zip_code,
        countries.name as country_name,
        created_user.user_username as created_username,
        verified_shipping_addresses.created_timestamp,
        last_modified_user.user_username as last_modified_username,
        verified_shipping_addresses.last_modified_timestamp
    FROM verified_shipping_addresses
    LEFT JOIN states ON verified_shipping_addresses.state_id = states.id
    LEFT JOIN countries ON states.country_id = countries.id
    LEFT JOIN user AS created_user ON verified_shipping_addresses.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON verified_shipping_addresses.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$verified_shipping_addresses = array();

while ($row = mysqli_fetch_assoc($result)) {
    $verified_shipping_addresses[] = $row;
}

$output_rows = '';

// if there is at least one result to display
if ($verified_shipping_addresses) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($verified_shipping_addresses);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_verified_shipping_addresses.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_verified_shipping_addresses.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_verified_shipping_addresses.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($verified_shipping_addresses) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $created_username = '';
        
        if ($verified_shipping_addresses[$key]['created_username']) {
            $created_username = ' by ' . $verified_shipping_addresses[$key]['created_username'];
        }
        
        $last_modified_username = '';
        
        if ($verified_shipping_addresses[$key]['last_modified_username']) {
            $last_modified_username = ' by ' . $verified_shipping_addresses[$key]['last_modified_username'];
        }
        
        $output_label = '';
        $output_sort_order = '';

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'edit_verified_shipping_address.php?id=' . $verified_shipping_addresses[$key]['id'] . '\'">
                <td class="chart_label">' . h($verified_shipping_addresses[$key]['company']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['address_1']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['address_2']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['city']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['state_name']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['zip_code']) . '</td>
                <td>' . h($verified_shipping_addresses[$key]['country_name']) . '</td>
                <td>' . get_relative_time(array('timestamp' => $verified_shipping_addresses[$key]['created_timestamp'])) . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $verified_shipping_addresses[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_verified_shipping_address.php?state_id=' . escape($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['state_id']) . '">Create Verified Shipping Address</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Verified Shipping Addresses</h1>
        <div class="subheading">All optional shipping addresses that can be selected on Shipping Address &amp; Arrival Pages.</div>
        <form id="search" action="view_verified_shipping_addresses.php" method="get" class="search_form">
            State: <select name="state_id" onchange="submit_form(\'search\')"><option value="[All]">[All] (' . number_format($all_verified_shipping_addresses) . ')</option>' . $output_state_options . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_verified_shipping_addresses) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Company', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Address 1', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Address 2', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('City', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('State', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Zip Code', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Country', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['sort'], $_SESSION['software']['ecommerce']['view_verified_shipping_addresses']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
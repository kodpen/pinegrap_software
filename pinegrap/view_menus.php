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
validate_area_access($user, 'designer');

$liveform = new liveform('view_menus');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['design']['view_menus'][$key] = trim($value);
    }
}

// If the user clicked on the clear button
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['design']['view_menus']['query'] = '';
}

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['design']['view_menus']['query']) == true) && ($_SESSION['software']['design']['view_menus']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
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

switch ($_SESSION['software']['design']['view_menus']['sort']) {
    case 'Name':
        $sort_column = 'menus.name';
        break;
        
    case 'Effect':
        $sort_column = 'menus.effect';
        break;

    case 'Created':
        $sort_column = 'menus.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'menus.last_modified_timestamp';
        break;

    default:
        $sort_column = 'menus.last_modified_timestamp';
        $_SESSION['software']['design']['view_menus']['sort'] = 'Last Modified';
        $_SESSION['software']['design']['view_menus']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['design']['view_menus']['order']) == false) {
    $_SESSION['software']['design']['view_menus']['order'] = 'asc';
}

// get total number of menus
$query = "SELECT COUNT(id) FROM menus";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_menus = $row[0];

$search_query = mb_strtolower($_SESSION['software']['design']['view_menus']['query']);

// create where clause for sql
$sql_search = "(LOWER(CONCAT_WS(',', menus.name, menus.effect, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape($search_query) . "%')";

if (isset($_SESSION['software']['design']['view_menus']['query'])) {
    // Get only the results the user wanted in the search.
    $where .= "WHERE $sql_search";
}

// get all menus
$query =
    "SELECT
        menus.id,
        menus.name,
        menus.effect,
        menus.created_timestamp,
        menus.last_modified_timestamp,
        menus.last_modified_user_id,
        created_user.user_username as created_username,
        last_modified_user.user_username as last_modified_username
    FROM menus
    LEFT JOIN user as created_user ON menus.created_user_id = created_user.user_id
    LEFT JOIN user as last_modified_user ON menus.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['design']['view_menus']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$menus = array();

while ($row = mysqli_fetch_assoc($result)) {
    $menus[] = $row;
}

$output_rows = '';

// if there is at least one result to display
if ($menus) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($menus);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_menus.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_menus.php?screen=\' + this.options[this.selectedIndex].value) + \'' . '\'">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_menus.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($menus) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        if ($menus[$key]['created_username']) {
            $created_username = $menus[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($menus[$key]['last_modified_username']) {
            $last_modified_username = $menus[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }

        $output_link_url = 'view_menu_items.php?id=' . $menus[$key]['id'] . '&from=design&send_to=' . h(escape_javascript(urlencode(REQUEST_URL)));
        
        $output_effect = h($menus[$key]['effect']);
        
        // if the effect is set to none, then update value to be "None"
        if ($output_effect == '') {
            $output_effect = 'None';
        }
        
        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <td class="chart_label" style="width: 25%">' . h($menus[$key]['name']) . '</td>
                <td style="width: 25%">' . $output_effect . '</td>
                <td style="width: 25%">' . get_relative_time(array('timestamp' => $menus[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td style="width: 25%">' . get_relative_time(array('timestamp' => $menus[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

echo
    output_header() . '
    <div id="subnav">
        ' . get_design_subnav() . '
    </div>
    <div id="button_bar">
        <a href="add_menu.php">Create Menu</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Menus</h1>
        <div class="subheading">All shared menus that can be added to any page style and managed by any site manager.</div>
        <form action="view_menus.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['design']['view_menus']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_menus) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['design']['view_menus']['sort'], $_SESSION['software']['design']['view_menus']['order']) . '</th>
                <th>' . get_column_heading('Effect', $_SESSION['software']['design']['view_menus']['sort'], $_SESSION['software']['design']['view_menus']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['design']['view_menus']['sort'], $_SESSION['software']['design']['view_menus']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['design']['view_menus']['sort'], $_SESSION['software']['design']['view_menus']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
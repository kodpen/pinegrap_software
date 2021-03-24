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
$liveform = new liveform('view_ship_date_adjustments');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_ship_date_adjustments'][$key] = trim($value);
    }
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

// if the user clicked on the clear button, then clear the search
if (isset($_GET['clear']) == true) {
    $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query']) == true) && ($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort']) {
    case 'Zip Code Prefix':
        $sort_column = 'ship_date_adjustments.zip_code_prefix';
        break;

    case 'Shipping Method':
        $sort_column = 'shipping_methods.name';
        break;
        
    case 'Adjustment':
        $sort_column = 'ship_date_adjustments.adjustment';
        break;

    case 'Created':
        $sort_column = 'ship_date_adjustments.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'ship_date_adjustments.last_modified_timestamp';
        break;

    default:
        $sort_column = 'ship_date_adjustments.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order'] = 'asc';
}

$all_ship_date_adjustments = 0;

// get the total number of ship date adjustments
$query = "SELECT COUNT(*) FROM ship_date_adjustments";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_ship_date_adjustments = $row[0];

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query']) == true) && ($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query'] != '')) {
    $where .= "WHERE (LOWER(CONCAT_WS(',', ship_date_adjustments.zip_code_prefix, shipping_methods.name, shipping_methods.code, ship_date_adjustments.adjustment, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query'])) . "%')";
}

// Get all ship date adjustments.
$query =
    "SELECT
        ship_date_adjustments.id,
        ship_date_adjustments.zip_code_prefix,
        shipping_methods.name AS shipping_method_name,
        shipping_methods.code AS shipping_method_code,
        ship_date_adjustments.adjustment,
        created_user.user_username AS created_username,
        ship_date_adjustments.created_timestamp,
        last_modified_user.user_username AS last_modified_username,
        ship_date_adjustments.last_modified_timestamp
    FROM ship_date_adjustments
    LEFT JOIN shipping_methods ON ship_date_adjustments.shipping_method_id = shipping_methods.id
    LEFT JOIN user AS created_user ON ship_date_adjustments.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON ship_date_adjustments.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']);
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$ship_date_adjustments = mysqli_fetch_items($result);

$output_rows = '';

// if there is at least one result to display
if ($ship_date_adjustments) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($ship_date_adjustments);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_ship_date_adjustments.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_ship_date_adjustments.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_ship_date_adjustments.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($ship_date_adjustments) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_shipping_method = h($ship_date_adjustments[$key]['shipping_method_name']);
        
        if ($ship_date_adjustments[$key]['shipping_method_code'] != '') {
            $output_shipping_method .= ' (' . h($ship_date_adjustments[$key]['shipping_method_code']) . ')';
        }

        if ($ship_date_adjustments[$key]['adjustment'] < 0) {
            $adjustment = -$ship_date_adjustments[$key]['adjustment'];

            $plural_suffix = '';

            if ($adjustment > 1) {
                $plural_suffix = 's';
            }

            $output_adjustment = $adjustment . ' day' . $plural_suffix . ' earlier';

        } else {
            $adjustment = $ship_date_adjustments[$key]['adjustment'];

            $plural_suffix = '';

            if ($adjustment > 1) {
                $plural_suffix = 's';
            }

            $output_adjustment = $adjustment . ' day' . $plural_suffix . ' later';
        }

        $created_username = '';
        
        if ($ship_date_adjustments[$key]['created_username'] != '') {
            $created_username = ' by ' . $ship_date_adjustments[$key]['created_username'];
        }
        
        $last_modified_username = '';
        
        if ($ship_date_adjustments[$key]['last_modified_username'] != '') {
            $last_modified_username = ' by ' . $ship_date_adjustments[$key]['last_modified_username'];
        }

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'edit_ship_date_adjustment.php?id=' . $ship_date_adjustments[$key]['id'] . '\'">
                <td class="chart_label">' . h($ship_date_adjustments[$key]['zip_code_prefix']) . '</td>
                <td>' . $output_shipping_method . '</td>
                <td>' . $output_adjustment . '</td>
                <td>' . get_relative_time(array('timestamp' => $ship_date_adjustments[$key]['created_timestamp'])) . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $ship_date_adjustments[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
            </tr>';
    }
}

echo
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_ship_date_adjustment.php">Create Ship Date Adjustment</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Ship Date Adjustments</h1>
        <div class="subheading">Allow a ship date for a recipient to be adjusted for a particular zip code prefix and shipping method.</div>
        <form id="search" action="view_ship_date_adjustments.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_ship_date_adjustments']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_ship_date_adjustments) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Zip Code Prefix', $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'], $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) . '</th>
                <th>' . get_column_heading('Shipping Method', $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'], $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) . '</th>
                <th>' . get_column_heading('Adjustment', $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'], $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'], $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['sort'], $_SESSION['software']['ecommerce']['view_ship_date_adjustments']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
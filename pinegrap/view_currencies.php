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
$liveform = new liveform('view_currencies');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_currencies'][$key] = trim($value);
    }
}

$keys_and_values = '';

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

switch ($_SESSION['software']['ecommerce']['view_currencies']['sort']) {
    case 'Name':
        $sort_column = 'name';
        break;

    case 'Base':
        $sort_column = 'base';
        break;

    case 'Code':
        $sort_column = 'code';
        break;

    case 'Symbol':
        $sort_column = 'symbol';
        break;

    case 'Exchange Rate':
        $sort_column = 'exchange_rate';
        break;

    case 'Created':
        $sort_column = 'created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'last_modified_timestamp';
        break;

    default:
        $sort_column = 'last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_currencies']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_currencies']['order'] = 'desc';
        break;
}

// if order is not set, default to ascending
if (isset($_SESSION['software']['ecommerce']['view_currencies']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_currencies']['order'] = 'asc';
}

// get all of the currency information. Join user_id with username
$query =
    "SELECT
        currencies.id,
        currencies.name,
        currencies.base,
        currencies.code,
        currencies.symbol,
        currencies.exchange_rate,
        currencies.created_user_id,
        currencies.created_timestamp,
        currencies.last_modified_user_id,
        currencies.last_modified_timestamp,
        created_user.user_username as created_username,
        last_modified_user.user_username as last_modified_username
    FROM currencies
    LEFT JOIN user as created_user ON currencies.created_user_id = created_user.user_id
    LEFT JOIN user as last_modified_user ON currencies.last_modified_user_id = last_modified_user.user_id
    ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_currencies']['order']);

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$currencies = array();

while ($row = mysqli_fetch_assoc($result)) {
    $currencies[] = $row;
}

$output_rows = '';

// if there is at least one result to display
if ($currencies) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($currencies);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_currencies.php?screen=' . $previous . $keys_and_values . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_currencies.php?screen=\' + this.options[this.selectedIndex].value) + \'' . $keys_and_values . '\'">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a hclass="submit-secondary" ref="view_currencies.php?screen=' . $next . $keys_and_values . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($currencies) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $output_base_check_mark = '';

        if ($currencies[$key]['base'] == 1) {
            $output_base_check_mark = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';
        }

        if ($currencies[$key]['created_username']) {
            $created_username = $currencies[$key]['created_username'];
        } else {
            $created_username = '[Unknown]';
        }

        if ($currencies[$key]['last_modified_username']) {
            $last_modified_username = $currencies[$key]['last_modified_username'];
        } else {
            $last_modified_username = '[Unknown]';
        }

        $output_link_url = 'edit_currency.php?id=' . $currencies[$key]['id'];

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <td class="' . $row_style . '">' . h($currencies[$key]['name']) . '</td>
                <td class="' . $row_style . '" style="text-align: center">' . $output_base_check_mark . '</td>
                <td class="' . $row_style . '">' . h($currencies[$key]['code']) . '</td>
                <td class="' . $row_style . '">' . $currencies[$key]['symbol'] . '</td>
                <td style="text-align: right;" class="' . $row_style . '">' . h($currencies[$key]['exchange_rate']) . '</td>
                <td class="' . $row_style . '">' . get_relative_time(array('timestamp' => $currencies[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>
                <td class="' . $row_style . '">' . get_relative_time(array('timestamp' => $currencies[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>
            </tr>';
    }
}

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_currency.php">Add Currency</a>
        <a href="update_exchange_rates.php?send_to=' . h(get_request_uri()) . '">Update Exchange Rates</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Currencies</h1>
        <div class="subheading">All currencies available for customers to select.</div>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($number_of_results) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th style="text-align: center">' . get_column_heading('Base', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th>' . get_column_heading('Code', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th>' . get_column_heading('Symbol', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th style="text-align: right;">' . get_column_heading('Exchange Rate', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_currencies']['sort'], $_SESSION['software']['ecommerce']['view_currencies']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();
$liveform->clear_notices();
$liveform->remove_form();
?>
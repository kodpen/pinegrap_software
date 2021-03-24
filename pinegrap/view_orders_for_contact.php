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

// if user does not have access to contact, then output error
if (validate_contact_access($user, $_GET['id']) == false) {
    log_activity("access denied to view orders for contact because user does not have access to a contact group that the contact is in", $_SESSION['sessionusername']);
    output_error('Access denied.');
}

// if id was set, update session
if (isset($_GET['id'])) {
    // store id in session
    $_SESSION['software']['ecommerce']['view_orders_for_contact']['id'] = $_GET['id'];
}

$id = $_SESSION['software']['ecommerce']['view_orders_for_contact']['id'];

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['ecommerce']['view_orders_for_contact']['sort'] = $_REQUEST['sort'];
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    // store sort in session
    $_SESSION['software']['ecommerce']['view_orders_for_contact']['order'] = $_REQUEST['order'];
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

// Get contact name and last name
$query = "SELECT first_name, last_name FROM contacts WHERE id = '" . escape($id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$first_name = $row['first_name'];
$last_name = $row['last_name'];

switch ($_SESSION['software']['ecommerce']['view_orders_for_contact']['sort']) {
    case 'Order Number':
        $sort_column = 'order_number';
        break;
    case 'Total':
        $sort_column = 'total';
        break;
    case 'Order Date':
        $sort_column = 'order_date';
        break;
    default:
        $sort_column = 'order_date';
}
if ($_SESSION['software']['ecommerce']['view_orders_for_contact']['order']) {
    $asc_desc = $_SESSION['software']['ecommerce']['view_orders_for_contact']['order'];
} else {
    $asc_desc = 'desc';
}

// set where clause
$where = "WHERE contact_id = '" . escape($id) . "' ";

/* define range depending on screen value by using a limit clause in the SQL statement */
// define the maximum number of results
$max = 100;
// determine where result set should start
$start = $screen * $max - $max;
$limit = "LIMIT $start, $max";

// get total number of results for all screens, so that we can output links to different screens
$query = "SELECT count(id) " .
         "FROM orders " .
         $where;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$number_of_results = $row[0];

// get number of screens
$number_of_screens = ceil($number_of_results / $max);

// build Previous button if necessary
$previous = $screen - 1;
// if previous screen is greater than zero, output previous link
if ($previous > 0) {
    $output_screen_links .= '<a class="submit-secondary" href="view_orders_for_contact.php?id=' . h($id) . '&screen=' . h($previous) . '">&lt;</a>&nbsp;&nbsp;';
}

// if there are more than one screen
if ($number_of_screens > 1) {
    $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_orders_for_contact.php?screen=\' + this.options[this.selectedIndex].value)">';

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
    $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_orders_for_contact.php?id=' . h($id) . '&screen=' . h($next) . '">&gt;</a>';
}

/* get results for just this screen*/
$query = "SELECT id, order_number, total, order_date ".
         "FROM orders ".
         $where.
         "ORDER BY $sort_column $asc_desc ".
         $limit;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
while ($row = mysqli_fetch_assoc($result)) {
    $row['total'] =                 sprintf("%01.2lf", $row['total'] / 100);
    $row['order_date'] = get_relative_time(array('timestamp' => $row['order_date']));

    $output_link_url = 'view_order.php?id=' . $row['id'];

    $output_rows .=
'<tr class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
    <td class="' . $row_style .'">' . h($row['order_number']) . '</td>
    <td class="' .$row_style. '" nowrap>&nbsp;</td>
    <td class="' . $row_style .'" nowrap>' . h($row['total']) . '</td>
    <td class="' .$row_style. '" nowrap>&nbsp;</td>
    <td class="' . $row_style .'" nowrap>' . $row['order_date'] . '</td>
</tr>
';
}

echo
    output_header() . '
    <div id="subnav">
        <h1>' . h($first_name)  . ' ' . h($last_name)  . '</h1>
    </div>
    <div id="content">
        
        <a href="#" id="help_link">Help</a>
        <h1>All Orders for Contact</h1>
        <div class="subheading" style="margin-bottom: 1em">All orders for this contact.</div>
        <div class="view_summary">
            Viewing '. h(number_format($number_of_results)) .' of ' . h(number_format($number_of_results)) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th nowrap>' . asc_or_desc('Order Number','view_orders_for_contact', $keys_and_values) . '</td>
                <th nowrap>&nbsp;</td>
                <th nowrap>' . asc_or_desc('Total','view_orders_for_contact', $keys_and_values) . '</td>
                <th nowrap>&nbsp;</td>
                <th nowrap>' . asc_or_desc('Order Date','view_orders_for_contact', $keys_and_values) . '</td>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();
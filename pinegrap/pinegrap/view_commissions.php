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
$liveform = new liveform('view_commissions');

// if necessary create commission instances from recurring profiles
update_recurring_commissions();

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_commissions'][$key] = trim($value);
    }
}

// if the form has not been submitted yet, then set default values for fields
if (isset($_SESSION['software']['ecommerce']['view_commissions']['start_month']) == FALSE) {
    // set the start date to a month ago
    $_SESSION['software']['ecommerce']['view_commissions']['start_month'] = date('m', time() - 2678400);
    $_SESSION['software']['ecommerce']['view_commissions']['start_day'] = date('d', time() - 2678400);
    $_SESSION['software']['ecommerce']['view_commissions']['start_year'] = date('Y', time() - 2678400);
    
    // set the stop date to today
    $_SESSION['software']['ecommerce']['view_commissions']['stop_month'] = date('m');
    $_SESSION['software']['ecommerce']['view_commissions']['stop_day'] = date('d');
    $_SESSION['software']['ecommerce']['view_commissions']['stop_year'] = date('Y');
    
    // set status to [All]
    $_SESSION['software']['ecommerce']['view_commissions']['status'] = '[All]';
}

// if the user clicked the clear button, then clear the query
if (isset($_GET['clear']) == TRUE) {
    $_SESSION['software']['ecommerce']['view_commissions']['query'] = '';
}

$sql_status = "";

// if a status is set, then prepare SQL where condition
if ($_SESSION['software']['ecommerce']['view_commissions']['status'] != '[All]') {
    $sql_status = "AND (commissions.status = '" . escape($_SESSION['software']['ecommerce']['view_commissions']['status']) . "')";
}

$output_clear_button = '';
$sql_search = "";

// if there is a search query, then prepare to output clear button and SQL where condition
if ((isset($_SESSION['software']['ecommerce']['view_commissions']['query']) == TRUE) && ($_SESSION['software']['ecommerce']['view_commissions']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
    // we had to remove the order number from the concat below because it caused the keyword search to no longer work
    $sql_search = "AND (LOWER(CONCAT_WS(',', commissions.affiliate_code, contacts.affiliate_name, commissions.reference_code)) LIKE '%" . escape(escape_like($_SESSION['software']['ecommerce']['view_commissions']['query'])) . "%')";
}

// get timestamps for start and stop dates
$start_timestamp = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_commissions']['start_month'], $_SESSION['software']['ecommerce']['view_commissions']['start_day'], $_SESSION['software']['ecommerce']['view_commissions']['start_year']);
$stop_timestamp = mktime(23, 59, 59, $_SESSION['software']['ecommerce']['view_commissions']['stop_month'], $_SESSION['software']['ecommerce']['view_commissions']['stop_day'], $_SESSION['software']['ecommerce']['view_commissions']['stop_year']);

// get oldest timestamp
$query = "SELECT MIN(created_timestamp) FROM commissions";
$result = mysqli_query(db::$con, $query) or output_error("Query failed.");
$row = mysqli_fetch_row($result);
$oldest_timestamp = $row[0];

// get minimum year from oldest timestamp
$oldest_year = date('Y', $oldest_timestamp);
$current_year = date('Y');

$years = array();

// create html for year options
for ($i = $oldest_year; $i <= $current_year; $i++) {
    $years[] = $i;
}

// prepare statuses for pick list
$statuses =
    array(
        'pending',
        'payable',
        'ineligible',
        'paid'
    );

$output_status_options = '';

// loop through the statuses in order to prepare pick list options
foreach ($statuses as $status) {
    $selected = '';
    
    // if this is the selected status, then select it
    if ($status == $_SESSION['software']['ecommerce']['view_commissions']['status']) {
        $selected = ' selected="selected"';
    }
    
    $output_status_options .= '<option value="' . $status . '"' . $selected . '>' . ucwords($status) . '</option>';
}


// get total number of results for all screens
$query =
    "SELECT COUNT(commissions.id) as number_of_results
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    WHERE
        (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_status . "
        " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_results = $row['number_of_results'];

// get total number of commissions
$query = "SELECT COUNT(id) as all_commissions FROM commissions";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$all_commissions = $row['all_commissions'];

// define the maximum number of results
$max = 100;

// get number of screens
$number_of_screens = ceil($number_of_results / $max);

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

// build Previous button if necessary
$previous = $screen - 1;
// if previous screen is greater than zero, output previous link
if ($previous > 0) {
    $output_screen_links .= '<a class="submit-secondary" href="view_commissions.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
}

// if there are more than one screen
if ($number_of_screens > 1) {
    $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_commissions.php?screen=\' + this.options[this.selectedIndex].value)">';

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
    $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_commissions.php?screen=' . $next . '">&gt;</a>';
}

switch ($_SESSION['software']['ecommerce']['view_commissions']['sort']) {
    case 'Affiliate Name':
        $sort_column = 'contacts.affiliate_name';
        break;
        
    case 'Affiliate Code':
        $sort_column = 'commissions.affiliate_code';
        break;
        
    case 'Reference Code':
        $sort_column = 'commissions.reference_code';
        break;
        
    case 'Status':
        $sort_column = 'commissions.status';
        break;
    
    case 'Amount':
        $sort_column = 'commissions.amount';
        break;
        
    case 'Frequency':
        $sort_column = 'recurring_commission_profiles.period';
        break;
        
    case 'Product':
        $sort_column = 'recurring_commission_profiles.product_name';
        break;

    case 'Created':
        $sort_column = 'commissions.created_timestamp';
        break;
        
    case 'Last Modified':
        $sort_column = 'commissions.last_modified_timestamp';
        break;

    default:
        $sort_column = 'commissions.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_commissions']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_commissions']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_commissions']['order']) == FALSE) {
    $_SESSION['software']['ecommerce']['view_commissions']['order'] = 'asc';
}

// prepare limit clause so we only get necessary results that appear on this screen
$start = $screen * $max - $max;
$limit = "LIMIT $start, $max";

// get results for just this screen
$query =
    "SELECT
        commissions.id,
        commissions.affiliate_code,
        commissions.reference_code,
        commissions.status,
        commissions.amount,
        commissions.created_timestamp,
        commissions.last_modified_timestamp,
        contacts.affiliate_name,
        orders.order_number,
        recurring_commission_profiles.period,
        recurring_commission_profiles.product_name,
        recurring_commission_profiles.product_short_description,
        created_user.user_username as created_username,
        last_modified_user.user_username as last_modified_username
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    LEFT JOIN recurring_commission_profiles ON commissions.recurring_commission_profile_id = recurring_commission_profiles.id
    LEFT JOIN user as created_user ON commissions.created_user_id = created_user.user_id
    LEFT JOIN user as last_modified_user ON commissions.last_modified_user_id = last_modified_user.user_id
    WHERE
        (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_status . "
        " . $sql_search . "
    ORDER BY " . $sort_column . " " . escape($_SESSION['software']['ecommerce']['view_commissions']['order']) . "
    " . $limit;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$commissions = array();

// loop through results in order to add them to array
while ($row = mysqli_fetch_assoc($result)) {
    $commissions[] = $row;
}

$output_rows = '';

// loop through all commissions in order to output them
foreach ($commissions as $commission) {
    // if the created username is known, then prepend " by " so it appears correctly after the timestamp
    if ($commission['created_username'] != '') {
        $commission['created_username'] = ' by ' . $commission['created_username'];
    }
    
    // if the last modified username is known, then prepend " by " so it appears correctly after the timestamp
    if ($commission['last_modified_username'] != '') {
        $commission['last_modified_username'] = ' by ' . $commission['last_modified_username'];
    }
    
    // if the frequency is blank, then this is not a recurring commission, so set frequency to "one-time"
    if ($commission['period'] == '') {
        $commission['period'] = 'One-Time';
    }
    
    // if there is a short description for the product, then prepend " - " so it appears correctly after the product name
    if ($commission['product_short_description'] != '') {
        $commission['product_short_description'] = ' - ' . $commission['product_short_description'];
    }
    
    $output_rows .=
        '<tr class="pointer" onclick="window.location.href=\'edit_commission.php?id=' . $commission['id'] . '\'">
            <td class="chart_label">' . h($commission['affiliate_name']) . '</td>
            <td>' . h($commission['affiliate_code']) . '</td>
            <td>' . $commission['reference_code'] . '</td>
            <td>' . ucwords($commission['status']) . '</td>
            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($commission['amount'] / 100, 2, '.', ',') . '</td>
            <td>' . ucwords($commission['period']) . '</td>
            <td>' . $commission['order_number'] . '</td>
            <td>' . h($commission['product_name']) . h($commission['product_short_description']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $commission['created_timestamp'])) . h($commission['created_username']) . '</td>
            <td>' . get_relative_time(array('timestamp' => $commission['last_modified_timestamp'])) . h($commission['last_modified_username']) . '</td>
        </tr>';
}

// get pending total
$query =
    "SELECT SUM(commissions.amount) as pending_total
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    WHERE
        (commissions.status = 'pending')
        AND (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$pending_total = $row['pending_total'];

// get payable total
$query =
    "SELECT SUM(commissions.amount) as payable_total
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    WHERE
        (commissions.status = 'payable')
        AND (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$payable_total = $row['payable_total'];

// get ineligible total
$query =
    "SELECT SUM(commissions.amount) as ineligible_total
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    WHERE
        (commissions.status = 'ineligible')
        AND (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$ineligible_total = $row['ineligible_total'];

// get paid total
$query =
    "SELECT SUM(commissions.amount) as paid_total
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    LEFT JOIN orders ON commissions.order_id = orders.id
    WHERE
        (commissions.status = 'paid')
        AND (commissions.created_timestamp >= '" . $start_timestamp . "')
        AND (commissions.created_timestamp <= '" . $stop_timestamp . "')
        " . $sql_search;
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$paid_total = $row['paid_total'];

print
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Commissions</h1>
        <div class="subheading" style="margin-bottom: 1em">View all order commissions.</div>
        <form id="search" action="view_commissions.php" method="get" class="search_form">
            <table style="border-collapse: collapse; width: 100%">
                <tr>
                    <td style="padding: 0em; text-align: left">
                        From: <select name="start_month">' . select_month($_SESSION['software']['ecommerce']['view_commissions']['start_month']) . '</select> <select name="start_day">' . select_day($_SESSION['software']['ecommerce']['view_commissions']['start_day']) . '</select> <select name="start_year">' . select_year($years, $_SESSION['software']['ecommerce']['view_commissions']['start_year']) . '</select>&nbsp;&nbsp;&nbsp;To: <select name="stop_month">' . select_month($_SESSION['software']['ecommerce']['view_commissions']['stop_month']) . '</select> <select name="stop_day">' . select_day($_SESSION['software']['ecommerce']['view_commissions']['stop_day']) . '</select> <select name="stop_year">' . select_year($years, $_SESSION['software']['ecommerce']['view_commissions']['stop_year']) . '</select>
                    </td>
                    <td style="padding: 0em; text-align: right">
                        Status: <select name="status" onchange="submit_form(\'search\')"><option value="[All]">[All]</option>' . $output_status_options . '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_commissions']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '                    
                    </td>
                </tr>
            </table>
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_commissions) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Affiliate Name', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Affiliate Code', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Reference Code', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Status', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th style="text-align: right">' . get_column_heading('Amount', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Frequency', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Order', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Product', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_commissions']['sort'], $_SESSION['software']['ecommerce']['view_commissions']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
        <div style="text-align: right">
            <div>Pending Total: ' . BASE_CURRENCY_SYMBOL . number_format($pending_total / 100, 2, '.', ',') . '</div>
            <div>Payable Total: ' . BASE_CURRENCY_SYMBOL . number_format($payable_total / 100, 2, '.', ',') . '</div>
            <div>Ineligible Total: ' . BASE_CURRENCY_SYMBOL . number_format($ineligible_total / 100, 2, '.', ',') . '</div>
            <div>Paid Total: ' . BASE_CURRENCY_SYMBOL . number_format($paid_total / 100, 2, '.', ',') . '</div>
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
?>
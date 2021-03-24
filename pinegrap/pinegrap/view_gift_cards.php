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
$liveform = new liveform('view_gift_cards');

$current_date = date('Y-m-d');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_gift_cards'][$key] = trim($value);
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
    $_SESSION['software']['ecommerce']['view_gift_cards']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ecommerce']['view_gift_cards']['query']) == true) && ($_SESSION['software']['ecommerce']['view_gift_cards']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['ecommerce']['view_gift_cards']['sort']) {
    case 'Code':
        $sort_column = 'gift_cards.code';
        break;

    case 'Balance':
        $sort_column = 'gift_cards.balance';
        break;
        
    case 'Original Amt':
        $sort_column = 'gift_cards.amount';
        break;

    case 'Expiration Date':
        $sort_column = 'gift_cards.expiration_date';
        break;

    case 'Notes':
        $sort_column = 'gift_cards.notes';
        break;

    case 'From':
        $sort_column = 'gift_cards.from_name';
        break;
        
    case 'Recipient':
        $sort_column = 'gift_cards.recipient_email_address';
        break;

    case 'Delivery Date':
        $sort_column = 'gift_cards.delivery_date';
        break;

    case 'Created':
        $sort_column = 'gift_cards.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'gift_cards.last_modified_timestamp';
        break;

    default:
        $sort_column = 'gift_cards.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_gift_cards']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_gift_cards']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_gift_cards']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_gift_cards']['order'] = 'asc';
}

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_gift_cards']['query']) == true) && ($_SESSION['software']['ecommerce']['view_gift_cards']['query'] != '')) {

    // We do an extra comparison with dashes removed from the search query
    // so if someone enters a gift card with dashes it will still match
    // codes that do not contain dashes in the database.

    // We had to start using CAST(orders.order_number AS CHAR) in order to avoid an issue
    // where the lower function would not work in some version of MySQL.

    $where .=
        "WHERE
            (
                (LOWER(CONCAT_WS(',', gift_cards.code, gift_cards.amount/100, gift_cards.balance/100, CAST(gift_cards.expiration_date AS CHAR), gift_cards.notes, CAST(orders.order_number AS CHAR), gift_cards.from_name, gift_cards.recipient_email_address, gift_cards.message, CAST(gift_cards.delivery_date AS CHAR), created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ecommerce']['view_gift_cards']['query'])) . "%')
                OR (gift_cards.code LIKE '%" . escape(str_replace('-', '', $_SESSION['software']['ecommerce']['view_gift_cards']['query'])) . "%')
            )";
}

// If user requested to export gift cards, then export them.
if ($_GET['submit_data'] == 'Export Gift Cards') {
    header("Content-type: text/csv");
    header("Content-disposition: attachment; filename=gift_cards.csv");

    // Output column headings for CSV data.
    echo
        '"code",' .
        '"balance",' .
        '"amount",' .
        '"expiration_date",' .
        '"notes",' .
        '"order_number",' .
        '"from_name",' .
        '"recipient_email_address",' .
        '"message",' .
        '"delivery_date",' .
        '"created",' .
        '"created_username",' .
        '"last_modified",' .
        '"last_modified_username"' . "\n";

    // Get all gift cards.
    $gift_cards = db_items(
        "SELECT
            gift_cards.code,
            gift_cards.balance,
            gift_cards.amount,
            gift_cards.expiration_date,
            gift_cards.notes,
            orders.order_number,
            gift_cards.from_name,
            gift_cards.recipient_email_address,
            gift_cards.message,
            gift_cards.delivery_date,
            gift_cards.created_timestamp,
            created_user.user_username AS created_username,
            gift_cards.last_modified_timestamp,
            last_modified_user.user_username AS last_modified_username
        FROM gift_cards
        LEFT JOIN orders ON gift_cards.order_id = orders.id
        LEFT JOIN user AS created_user ON gift_cards.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON gift_cards.last_modified_user_id = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_gift_cards']['order']));

    // If the date format is month and then day, then use that format.
    if (DATE_FORMAT == 'month_day') {
        $month_and_day_format = 'n/j';

    // Otherwise the date format is day and then month, so use that format.
    } else {
        $month_and_day_format = 'j/n';
    }

    // Loop through the gift cards in order to output CSV data.
    foreach ($gift_cards as $gift_card) {
        $expiration_date = '';

        if ($gift_card['expiration_date'] != '0000-00-00') {
            $expiration_date = $gift_card['expiration_date'];
        }

        if (($gift_card['recipient_email_address'] != '') && ($gift_card['from_name'] == '')) {
            $gift_card['from_name'] = 'Anonymous';
        }

        $delivery_date = '';

        // If there is a recipient, then output the delivery date.
        if ($gift_card['recipient_email_address'] != '') {
            if ($gift_card['delivery_date'] == '0000-00-00') {
                $delivery_date = 'Immediate';

            } else {
                $delivery_date = $gift_card['delivery_date'];
            }
        }

        echo
            '"' . output_gift_card_code($gift_card['code']) . '",' .
            '"' . sprintf('%01.2lf', $gift_card['balance'] / 100) . '",' .
            '"' . sprintf('%01.2lf', $gift_card['amount'] / 100) . '",' .
            '"' . $expiration_date . '",' .
            '"' . escape_csv($gift_card['notes']) . '",' .
            '"' . $gift_card['order_number'] . '",' .
            '"' . escape_csv($gift_card['from_name']) . '",' .
            '"' . escape_csv($gift_card['recipient_email_address']) . '",' .
            '"' . escape_csv($gift_card['message']) . '",' .
            '"' . $delivery_date . '",' .
            '"' . date($month_and_day_format . '/Y g:i:s A T', $gift_card['created_timestamp']) . '",' .
            '"' . escape_csv($gift_card['created_username']) . '",' .
            '"' . date($month_and_day_format . '/Y g:i:s A T', $gift_card['last_modified_timestamp']) . '",' .
            '"' . escape_csv($gift_card['last_modified_username']) . '"' . "\n";
    }

    exit;

// Otherwise the user did not select to export gift cards, so just list gift cards.
} else {
    $all_gift_cards = 0;

    // get the total number of gift cards
    $query = "SELECT COUNT(*) FROM gift_cards";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $all_gift_cards = $row[0];

    // Get all gift cards.
    $query =
        "SELECT
            gift_cards.id,
            gift_cards.code,
            gift_cards.amount,
            gift_cards.expiration_date,
            gift_cards.balance,
            gift_cards.notes,
            gift_cards.from_name,
            gift_cards.recipient_email_address,
            gift_cards.delivery_date,
            created_user.user_username AS created_username,
            gift_cards.created_timestamp,
            last_modified_user.user_username AS last_modified_username,
            gift_cards.last_modified_timestamp
        FROM gift_cards
        LEFT JOIN orders ON gift_cards.order_id = orders.id
        LEFT JOIN user AS created_user ON gift_cards.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON gift_cards.last_modified_user_id = last_modified_user.user_id
        $where
        ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_gift_cards']['order']);
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $gift_cards = mysqli_fetch_items($result);

    $output_rows = '';

    // if there is at least one result to display
    if ($gift_cards) {
        // define the maximum number of results to display on one screen
        $max = 100;

        $number_of_results = count($gift_cards);

        // get number of screens
        $number_of_screens = ceil($number_of_results / $max);

        // build Previous button if necessary
        $previous = $screen - 1;
        // if previous screen is greater than zero, output previous link
        if ($previous > 0) {
            $output_screen_links .= '<a class="submit-secondary" href="view_gift_cards.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
        }

        // if there are more than one screen
        if ($number_of_screens > 1) {
            $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_gift_cards.php?screen=\' + this.options[this.selectedIndex].value)">';

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
            $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_gift_cards.php?screen=' . $next . '">&gt;</a>';
        }

        // determine where result set should start
        $start = $screen * $max - $max;

        // determine where result set should end
        $end = $start + $max - 1;

        // get the value of the last index of the array
        $last_index = count($gift_cards) - 1;

        // if the end if past the last index of the array, set the end to the last index of the array
        if ($end > $last_index) {
            $end = $last_index;
        }

        for ($key = $start; $key <= $end; $key++) {
            // If this gift card has a balance and has not expired,
            // then use class that shows green color.
            if (
                ($gift_cards[$key]['balance'])
                &&
                (
                    ($gift_cards[$key]['expiration_date'] == '0000-00-00')
                    || ($gift_cards[$key]['expiration_date'] >= $current_date)
                )
            ) {
                $output_status_class = 'status_enabled';
            
            // Otherwise this gift card has expired, so use class that shows red color.
            } else {
                $output_status_class = 'status_disabled';
            }

            $output_expiration_date = '';

            if ($gift_cards[$key]['expiration_date'] != '0000-00-00') {
                $output_expiration_date = get_absolute_time(array('timestamp' => strtotime($gift_cards[$key]['expiration_date']), 'type' => 'date'));
            }

            $output_from_name = '';
            $output_delivery_date = '';

            // If there is a recipient, then output order info.
            if ($gift_cards[$key]['recipient_email_address'] != '') {
                if ($gift_cards[$key]['from_name'] != '') {
                    $output_from_name = h($gift_cards[$key]['from_name']);

                } else {
                    $output_from_name = 'Anonymous';
                }
                
                if ($gift_cards[$key]['delivery_date'] == '0000-00-00') {
                    $output_delivery_date = 'Immediate';

                } else {
                    $output_delivery_date = get_absolute_time(array('timestamp' => strtotime($gift_cards[$key]['delivery_date']), 'type' => 'date'));
                }
            }

            $created_username = '';
            
            if ($gift_cards[$key]['created_username'] != '') {
                $created_username = ' by ' . $gift_cards[$key]['created_username'];
            }
            
            $last_modified_username = '';
            
            if ($gift_cards[$key]['last_modified_username'] != '') {
                $last_modified_username = ' by ' . $gift_cards[$key]['last_modified_username'];
            }

            $output_rows .=
                '<tr class="pointer" onclick="window.location.href=\'edit_gift_card.php?id=' . $gift_cards[$key]['id'] . '\'">
                    <td class="chart_label ' . $output_status_class . '">' . output_gift_card_code($gift_cards[$key]['code']) . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($gift_cards[$key]['balance'] / 100, 2) . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($gift_cards[$key]['amount'] / 100, 2) . '</td>
                    <td>' . $output_expiration_date . '</td>
                    <td>' . nl2br(h($gift_cards[$key]['notes'])) . '</td>
                    <td>' . $output_from_name . '</td>
                    <td>' . h($gift_cards[$key]['recipient_email_address']) . '</td>
                    <td>' . $output_delivery_date . '</td>
                    <td>' . get_relative_time(array('timestamp' => $gift_cards[$key]['created_timestamp'])) . h($created_username) . '</td>
                    <td>' . get_relative_time(array('timestamp' => $gift_cards[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
                </tr>';
        }
    }

    // Get active balance.

    if ($where == '')  {
        $sql_active_filter = "WHERE ";
    } else {
        $sql_active_filter = "AND ";
    }

    $sql_active_filter .= "((gift_cards.expiration_date = '0000-00-00') OR (gift_cards.expiration_date >= '" . $current_date . "'))";

    $active_balance = db_value(
        "SELECT SUM(gift_cards.balance)
        FROM gift_cards
        LEFT JOIN orders ON gift_cards.order_id = orders.id
        LEFT JOIN user AS created_user ON gift_cards.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON gift_cards.last_modified_user_id = last_modified_user.user_id
        $where
        $sql_active_filter");

    // Get expired balance.

    if ($where == '')  {
        $sql_expired_filter = "WHERE ";
    } else {
        $sql_expired_filter = "AND ";
    }

    $sql_expired_filter .= "((gift_cards.expiration_date != '0000-00-00') AND (gift_cards.expiration_date < '" . $current_date . "'))";

    $expired_balance = db_value(
        "SELECT SUM(gift_cards.balance)
        FROM gift_cards
        LEFT JOIN orders ON gift_cards.order_id = orders.id
        LEFT JOIN user AS created_user ON gift_cards.created_user_id = created_user.user_id
        LEFT JOIN user AS last_modified_user ON gift_cards.last_modified_user_id = last_modified_user.user_id
        $where
        $sql_expired_filter");

    // Get total balance.
    $total_balance = $active_balance + $expired_balance;

    echo
        output_header() . '
        <div id="subnav">
            ' . render(array('template' => 'commerce_subnav.php')) . '
        </div>
        <div id="button_bar">
            <a href="add_gift_card.php">Create Gift Card</a>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>All Gift Cards</h1>
            <div class="subheading">Gift cards are automatically created when a Gift Card Product is ordered.  You can also manually create Gift Cards.</div>
            <form id="search" action="view_gift_cards.php" method="get" class="search_form" style="margin-bottom: 1em">
                <input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_gift_cards']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
            </form>
            <div class="view_summary">
                Viewing '. number_format($number_of_results) .' of ' . number_format($all_gift_cards) . ' Total&nbsp;&nbsp;&nbsp;&nbsp;<form method="get" style="margin: 0; display: inline"><input type="submit" name="submit_data" value="Export Gift Cards" class="submit_small_secondary"></form>
            </div>
            <table class="chart">
                <tr>
                    <th>' . get_column_heading('Code', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th style="text-align: right">' . get_column_heading('Balance', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th style="text-align: right">' . get_column_heading('Original Amt', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Expiration Date', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Notes', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('From', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Recipient', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Delivery Date', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                    <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_gift_cards']['sort'], $_SESSION['software']['ecommerce']['view_gift_cards']['order']) . '</th>
                </tr>
                ' . $output_rows . '
            </table>
            <div class="pagination">
                ' . $output_screen_links . '
            </div>
            <table style="float: right">
                <tr>
                    <td style="padding: .25em; text-align: right">Active Balance:</td>
                    <td style="padding: .25em; text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($active_balance / 100, 2) . '</td>
                </tr>
                <tr>
                    <td style="padding: .25em; text-align: right">Expired Balance:</td>
                    <td style="padding: .25em; text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($expired_balance / 100, 2) . '</td>
                </tr>
                <tr>
                    <td style="padding: .25em; text-align: right">Total Balance:</td>
                    <td style="padding: .25em; text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($total_balance / 100, 2) . '</td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>' .
        output_footer();

    $liveform->remove_form();
}
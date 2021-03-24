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
$liveform = new liveform('view_product_attributes');

// store all values collected in request to session
foreach ($_REQUEST as $key => $value) {
    // if the value is a string then add it to the session
    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,
    // for certain php.ini settings
    if (is_string($value) == TRUE) {
        $_SESSION['software']['ecommerce']['view_product_attributes'][$key] = trim($value);
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
    $_SESSION['software']['ecommerce']['view_product_attributes']['query'] = '';
}

$output_clear_button = '';

// if there is a search query, then prepare to output clear button
if ((isset($_SESSION['software']['ecommerce']['view_product_attributes']['query']) == true) && ($_SESSION['software']['ecommerce']['view_product_attributes']['query'] != '')) {
    $output_clear_button = ' <input type="button" value="Clear" onclick="document.location.href = \'' . h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true' . '\'" class="submit_small_secondary" />';
}

switch ($_SESSION['software']['ecommerce']['view_product_attributes']['sort']) {
    case 'Name':
        $sort_column = 'product_attributes.name';
        break;

    case 'Label & Options':
        $sort_column = 'product_attributes.label';
        break;

    case 'Created':
        $sort_column = 'product_attributes.created_timestamp';
        break;

    case 'Last Modified':
        $sort_column = 'product_attributes.last_modified_timestamp';
        break;

    default:
        $sort_column = 'product_attributes.last_modified_timestamp';
        $_SESSION['software']['ecommerce']['view_product_attributes']['sort'] = 'Last Modified';
        $_SESSION['software']['ecommerce']['view_product_attributes']['order'] = 'desc';
        break;
}

// if order is not set, set to ascending
if (isset($_SESSION['software']['ecommerce']['view_product_attributes']['order']) == false) {
    $_SESSION['software']['ecommerce']['view_product_attributes']['order'] = 'asc';
}

$all_product_attributes = 0;

// get the total number of product attributes
$query = "SELECT COUNT(*) FROM product_attributes";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_row($result);
$all_product_attributes = $row[0];

$where = "";

// if there is a search query and it is not blank, then prepare SQL
if ((isset($_SESSION['software']['ecommerce']['view_product_attributes']['query']) == true) && ($_SESSION['software']['ecommerce']['view_product_attributes']['query'] != '')) {
    // We do an extra comparison with dashes removed from the search query
    // so if someone enters a product attribute with dashes it will still match
    // codes that do not contain dashes in the database.
    $where .= "WHERE (LOWER(CONCAT_WS(',', product_attributes.name, product_attributes.label, created_user.user_username, last_modified_user.user_username)) LIKE '%" . escape(mb_strtolower($_SESSION['software']['ecommerce']['view_product_attributes']['query'])) . "%')";
}

// Get all product attributes.
$query =
    "SELECT
        product_attributes.id,
        product_attributes.name,
        product_attributes.label,
        created_user.user_username AS created_username,
        product_attributes.created_timestamp,
        last_modified_user.user_username AS last_modified_username,
        product_attributes.last_modified_timestamp
    FROM product_attributes
    LEFT JOIN user AS created_user ON product_attributes.created_user_id = created_user.user_id
    LEFT JOIN user AS last_modified_user ON product_attributes.last_modified_user_id = last_modified_user.user_id
    $where
    ORDER BY $sort_column " . escape($_SESSION['software']['ecommerce']['view_product_attributes']['order']);
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$product_attributes = mysqli_fetch_items($result);

$output_rows = '';

// if there is at least one result to display
if ($product_attributes) {
    // define the maximum number of results to display on one screen
    $max = 100;

    $number_of_results = count($product_attributes);

    // get number of screens
    $number_of_screens = ceil($number_of_results / $max);

    // build Previous button if necessary
    $previous = $screen - 1;
    // if previous screen is greater than zero, output previous link
    if ($previous > 0) {
        $output_screen_links .= '<a class="submit-secondary" href="view_product_attributes.php?screen=' . $previous . '">&lt;</a>&nbsp;&nbsp;';
    }

    // if there are more than one screen
    if ($number_of_screens > 1) {
        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_product_attributes.php?screen=\' + this.options[this.selectedIndex].value)">';

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
        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_product_attributes.php?screen=' . $next . '">&gt;</a>';
    }

    // determine where result set should start
    $start = $screen * $max - $max;

    // determine where result set should end
    $end = $start + $max - 1;

    // get the value of the last index of the array
    $last_index = count($product_attributes) - 1;

    // if the end if past the last index of the array, set the end to the last index of the array
    if ($end > $last_index) {
        $end = $last_index;
    }

    for ($key = $start; $key <= $end; $key++) {
        $options = db_items(
            "SELECT
                id,
                label,
                no_value
            FROM product_attribute_options
            WHERE product_attribute_id = '" . $product_attributes[$key]['id'] . "'
            ORDER BY sort_order");

        $output_options = '<div style="padding-left: 2em">';

        foreach ($options as $option) {
            $output_no_value = '';

            if ($option['no_value']) {
                $output_no_value = ' ["No Thanks" Option]';
            }

            $output_options .= h($option['label']) . $output_no_value . '<br>';
        }

        $output_options .= '</div>';

        $created_username = '';
        
        if ($product_attributes[$key]['created_username'] != '') {
            $created_username = ' by ' . $product_attributes[$key]['created_username'];
        }
        
        $last_modified_username = '';
        
        if ($product_attributes[$key]['last_modified_username'] != '') {
            $last_modified_username = ' by ' . $product_attributes[$key]['last_modified_username'];
        }

        $output_rows .=
            '<tr class="pointer" onclick="window.location.href=\'edit_product_attribute.php?id=' . $product_attributes[$key]['id'] . '\'">
                <td class="chart_label">' . h($product_attributes[$key]['name']) . '</td>
                <td>
                    ' . h($product_attributes[$key]['label']) . '<br>
                    ' . $output_options . '
                </td>
                <td>' . get_relative_time(array('timestamp' => $product_attributes[$key]['created_timestamp'])) . h($created_username) . '</td>
                <td>' . get_relative_time(array('timestamp' => $product_attributes[$key]['last_modified_timestamp'])) . h($last_modified_username) . '</td>
            </tr>';
    }
}

echo
    output_header() . '
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_product_attribute.php">Create Product Attribute</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Product Attributes</h1>
        <div class="subheading">All attributes that can be assigned to products.</div>
        <form id="search" action="view_product_attributes.php" method="get" class="search_form">
            <input type="text" name="query" value="' . h($_SESSION['software']['ecommerce']['view_product_attributes']['query']) . '" /> <input type="submit" value="Search" class="submit_small_secondary" />' . $output_clear_button . '
        </form>
        <div class="view_summary">
            Viewing '. number_format($number_of_results) .' of ' . number_format($all_product_attributes) . ' Total
        </div>
        <table class="chart">
            <tr>
                <th>' . get_column_heading('Name', $_SESSION['software']['ecommerce']['view_product_attributes']['sort'], $_SESSION['software']['ecommerce']['view_product_attributes']['order']) . '</th>
                <th>' . get_column_heading('Label & Options', $_SESSION['software']['ecommerce']['view_product_attributes']['sort'], $_SESSION['software']['ecommerce']['view_product_attributes']['order']) . '</th>
                <th>' . get_column_heading('Created', $_SESSION['software']['ecommerce']['view_product_attributes']['sort'], $_SESSION['software']['ecommerce']['view_product_attributes']['order']) . '</th>
                <th>' . get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_product_attributes']['sort'], $_SESSION['software']['ecommerce']['view_product_attributes']['order']) . '</th>
            </tr>
            ' . $output_rows . '
        </table>
        <div class="pagination">
            ' . $output_screen_links . '
        </div>
    </div>' .
    output_footer();

$liveform->remove_form();
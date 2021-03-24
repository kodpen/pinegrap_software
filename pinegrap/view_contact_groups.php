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



include_once('liveform.class.php');

$liveform = new liveform('view_contact_groups');



// store all values collected in request to session

foreach ($_REQUEST as $key => $value) {

    // if the value is a string then add it to the session

    // we have to do this check because cookie arrays are sometimes included in the $_REQUEST array,

    // for certain php.ini settings

    if (is_string($value) == TRUE) {

        $_SESSION['software']['view_contacts']['view_contact_groups'][$key] = trim($value);

    }

}



// if user has access to manage contact groups, then prepare to output add contact group button

if ($user['role'] < 3) {

    $output_add_contact_group_button = '

    <div id="button_bar">

        <a href="add_contact_group.php">Create Contact Group</a>

    </div>';



// else user does not have access to manage contact groups, so prepare to not output buttons for contact groups

} else {

    $output_add_contact_group_button = '';

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



switch ($_SESSION['software']['view_contacts']['view_contact_groups']['sort']) {

    case 'Name':

        $sort_column = 'contact_groups.name';

        break;



    case 'Description':

        $sort_column = 'contact_groups.description';

        break;



    case 'E-mail Subscription':

        $sort_column = 'contact_groups.email_subscription';

        break;



    case 'E-mail Subscription Type':

        $sort_column = 'contact_groups.email_subscription_type';

        break;



    case 'Created':

        $sort_column = 'contact_groups.created_timestamp';

        break;



    case 'Last Modified':

        $sort_column = 'contact_groups.last_modified_timestamp';

        break;



    default:

        $sort_column = 'contact_groups.last_modified_timestamp';

        $_SESSION['software']['view_contacts']['view_contact_groups']['sort'] = 'Last Modified';

        $_SESSION['software']['view_contacts']['view_contact_groups']['order'] = 'desc';

        break;

}



// if order is not set, set to ascending

if (isset($_SESSION['software']['view_contacts']['view_contact_groups']['order']) == false) {

    $_SESSION['software']['view_contacts']['view_contact_groups']['order'] = 'asc';

}



// get all contact groups

$query =

    "SELECT

        contact_groups.id,

        contact_groups.name,

        contact_groups.description,

        contact_groups.email_subscription,

        contact_groups.email_subscription_type,

        created_user.user_username as created_username,

        contact_groups.created_timestamp,

        last_modified_user.user_username as last_modified_username,

        contact_groups.last_modified_timestamp

    FROM contact_groups

    LEFT JOIN user AS created_user ON contact_groups.created_user_id = created_user.user_id

    LEFT JOIN user AS last_modified_user ON contact_groups.last_modified_user_id = last_modified_user.user_id

    ORDER BY $sort_column " . escape($_SESSION['software']['view_contacts']['view_contact_groups']['order']);



$result = mysqli_query(db::$con, $query) or output_error('Query failed.');



$contact_groups = array();



while ($row = mysqli_fetch_assoc($result)) {



    // Add one to all contact groups.

    $all_contact_groups++;



    // if user has access to contact group then add contact group to contact groups array

    if (validate_contact_group_access($user, $row['id']) == true) {

        $contact_groups[] = $row;



        // Add one to all contact groups.

        $my_contact_groups++;

    }

}



$output_rows = '';



// if there is at least one result to display

if ($contact_groups) {

    // define the maximum number of results to display on one screen

    $max = 100;



    $number_of_results = count($contact_groups);



    // get number of screens

    $number_of_screens = ceil($number_of_results / $max);



    // build Previous button if necessary

    $previous = $screen - 1;

    // if previous screen is greater than zero, output previous link

    if ($previous > 0) {

        $output_screen_links .= '<a class="submit-secondary" href="view_contact_groups.php?screen=' . $previous . $keys_and_values . '">&lt;</a>&nbsp;&nbsp;';

    }



    // if there are more than one screen

    if ($number_of_screens > 1) {

        $output_screen_links .= '<select name="screens" onchange="window.location.href=(\'view_contact_groups.php?screen=\' + this.options[this.selectedIndex].value) + \'' . $keys_and_values . '\'">';



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

        $output_screen_links .= '&nbsp;&nbsp;<a class="submit-secondary" href="view_contact_groups.php?screen=' . $next . $keys_and_values . '">&gt;</a>';

    }



    // determine where result set should start

    $start = $screen * $max - $max;



    // determine where result set should end

    $end = $start + $max - 1;



    // get the value of the last index of the array

    $last_index = count($contact_groups) - 1;



    // if the end if past the last index of the array, set the end to the last index of the array

    if ($end > $last_index) {

        $end = $last_index;

    }



    for ($key = $start; $key <= $end; $key++) {

        if ($contact_groups[$key]['email_subscription']) {

            $email_subscription = '<img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="" />';



            if ($contact_groups[$key]['email_subscription_type'] == 'open') {

                $email_subscription_type = 'Open';

            } else {

                $email_subscription_type = 'Closed';

            }



            $description = $contact_groups[$key]['description'];



        } else {

            $email_subscription = '';

            $email_subscription_type = '';

            $description = '';

        }



        if ($contact_groups[$key]['created_username']) {

            $created_username = $contact_groups[$key]['created_username'];

        } else {

            $created_username = '[Unknown]';

        }



        if ($contact_groups[$key]['last_modified_username']) {

            $last_modified_username = $contact_groups[$key]['last_modified_username'];

        } else {

            $last_modified_username = '[Unknown]';

        }



        $output_link_url = 'edit_contact_group.php?id=' . $contact_groups[$key]['id'];



        $output_rows .=

            '<tr>

                <td style="white-space: nowrap"></td>

                <td onclick="window.location.href=\'' . $output_link_url . '\'" class="chart_label pointer">' . h($contact_groups[$key]['name']) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" style="text-align: center;">' . $email_subscription . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'" >' . $email_subscription_type . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . h($description) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $contact_groups[$key]['created_timestamp'])) . ' by ' . h($created_username) . '</td>

                <td class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">' . get_relative_time(array('timestamp' => $contact_groups[$key]['last_modified_timestamp'])) . ' by ' . h($last_modified_username) . '</td>

            </tr>';

    }

}



print

    output_header(). '

    <div id="subnav">

        <ul>
            <li><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contacts.php">All My Contacts</a></li>
            <li><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_contact_groups.php">All Contact Groups</a></li>

        </ul>

    </div>

    ' . $output_add_contact_group_button . '

    <div id="content">

        

        ' . $liveform->output_errors() . '

        ' . $liveform->output_notices() . '

        <a href="#" id="help_link">Help</a>

        <h1>All Contact Groups</h1>

        <div class="subheading">All contact groups used to collect and organize contacts and subscribers</div>

        <div class="view_summary">

            Viewing '. number_format($number_of_results) .' of ' . number_format($my_contact_groups) . ' I can access. ' . number_format($all_contact_groups) . ' Total

        </div>

        <table class="chart">

            <tr>

                <th>&nbsp;</th>

                <th>' . get_column_heading('Name', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

                <th style="text-align: center;">' . get_column_heading('Subscription', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

                <th>' . get_column_heading('Type', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

                <th>' . get_column_heading('Description', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

                <th>' . get_column_heading('Created', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

                <th>' . get_column_heading('Last Modified', $_SESSION['software']['view_contacts']['view_contact_groups']['sort'], $_SESSION['software']['view_contacts']['view_contact_groups']['order']) . '</th>

            </tr>

            ' . $output_rows . '

        </table>

        <div class="pagination">

            ' . $output_screen_links . '

        </div>

    </div>' .

    output_footer();



$liveform->remove_form();

?>
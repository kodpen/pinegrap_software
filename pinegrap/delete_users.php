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
include_once('liveform.class.php');
$liveform_view_users = new liveform('view_users');
$user = validate_user();
validate_area_access($user, 'manager');

validate_token_field();

// if at least one user was selected
if ($_POST['users']) {
    // if user is an administrator, get number of administrators in order to prevent the last administrator from being deleted
    if ($user['role'] == 0) {
        $query = "SELECT COUNT(user_id) FROM user WHERE user_role = '0'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_administrators = $row[0];
    }
    
    $number_of_users = 0;

    foreach ($_POST['users'] as $user_id) {
        // get user's role so that we can determine if this logged-in user has access to delete this user
        $query = "SELECT user_role FROM user WHERE user_id = '" . escape($user_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $user_role = $row['user_role'];
        
        // if logged-in user's role is administrator or if logged-in user's role is above this user's role,
        // then logged-in user has access to delete this user, so continue to delete user
        if (($user['role'] == 0) || ($user['role'] < $user_role)) {
            // if this user is not an administrator or the number of administrators is greater than one, then we can allow this user to be deleted, so prepare checkbox
            if (($user_role != 0) || ($number_of_administrators > 1)) {
                // delete user record
                $query = "DELETE FROM user WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete aclfolder records
                $query = "DELETE FROM aclfolder WHERE aclfolder_user = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete users_contact_groups_xref records
                $query = "DELETE FROM users_contact_groups_xref WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete users_calendars_xref records
                $query = "DELETE FROM users_calendars_xref WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete address book records
                $query = "DELETE FROM address_book WHERE user = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete ad region xref records
                $query = "DELETE FROM users_ad_regions_xref WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete common region xref records
                $query = "DELETE FROM users_common_regions_xref WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete menu xref records
                $query = "DELETE FROM users_menus_xref WHERE user_id = '" . escape($user_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                db("DELETE FROM users_messages_xref WHERE user_id = '" . e($user_id) . "'");

                $number_of_users++;
                
                // if user that was deleted was an administrator, then decrement number of administrators
                if ($user_role == 0) {
                    $number_of_administrators--;
                }
                
            // else add notice to notify user that administrator user could not be deleted
            } else {
                if (($user['role'] == 0) && ($number_of_administrators == 1)) {
                    $liveform_view_users->mark_error('', 'A user could not be deleted because it was the only administrator.');
                }
            }
        }
    }
    
    // if more than one user was deleted, then log activity and add notice
    if ($number_of_users > 0) {
        log_activity(number_format($number_of_users) . ' users were deleted', $_SESSION['sessionusername']);
        $liveform_view_users->add_notice(number_format($number_of_users) . ' user(s) were deleted.');
    }
}

// If there is a send to value then send user back to that screen
if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
    
// else send user to the default view
} else {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_users.php');
}
?>
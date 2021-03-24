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

// get user information
$query =
    "SELECT
        user_username,
        user_email,
        user_role,
        user_home,
        user_manage_contacts,
        user_manage_emails,
        user_manage_ecommerce,
        manage_ecommerce_reports,
        user_manage_forms,
        user_manage_calendars,
        user_manage_visitors
    FROM user
    WHERE user_id = '" . escape($_POST['user_id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if a user could not be found, then output error
if (mysqli_num_rows($result) == 0) {
    output_error('The user could not be found. <a href="javascript:history.go(-1)">Go back</a>.');
}

$row = mysqli_fetch_assoc($result);

$user_username = $row['user_username'];
$user_email = $row['user_email'];
$user_role = $row['user_role'];
$user_home = $row['user_home'];
$user_manage_contacts = $row['user_manage_contacts'];
$user_manage_emails = $row['user_manage_emails'];
$user_manage_ecommerce = $row['user_manage_ecommerce'];
$manage_ecommerce_reports = $row['manage_ecommerce_reports'];
$user_manage_forms = $row['user_manage_forms'];
$user_manage_calendars = $row['user_manage_calendars'];
$user_manage_visitors = $row['user_manage_visitors'];

// if editor is less than an administrator role
// and the editor's role is less than or equal to the user that the editor is trying to reset the password for,
// output error because user does not have access to do this
if (($user['role'] > 0) && ($user['role'] >= $user_role)) {
    log_activity("access denied because user does not have access to reset password for user", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

$random_password = get_random_string(array(
    'type' => 'lowercase_letters',
    'length' => 10));

// insert new random password into database
$query =
    "UPDATE user 
    SET 
        user_password = '" . md5($random_password) . "', 
        user_password_hint = '' 
    WHERE user_id = '" . escape($_POST['user_id']) . "'";

$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

$login = '';
    
// if user's role is administrator, designer, or manager
// or user has edit rights
// or user has access to control panel
// then set link to PATH/SOFTWARE_DIRECTORY/
if (
    ($user_role < 3)
    || (no_acl_check($_POST['user_id']) == true)
    || ($user_manage_calendars == 'yes')
    || ($user_manage_forms == 'yes')
    || ($user_manage_visitors == 'yes')
    || ($user_manage_contacts == 'yes')
    || ($user_manage_emails == 'yes')
    || ($user_manage_ecommerce == 'yes')
    || $manage_ecommerce_reports
    || (count(get_items_user_can_edit('ad_regions', $_POST['user_id'])) > 0)
) {
    $login = 
        'Login:' . "\n" .
        URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/' . "\n";

// else if there was a send to page selected for this user        
} elseif ($user_home) {
    $login =
        'Login:' . "\n" .
        URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . encode_url_path(get_page_name($user_home)) . "\n";
}

email(array(
    'to' => $user_email,
    'from_name' => ORGANIZATION_NAME,
    'from_email_address' => EMAIL_ADDRESS,
    'subject' => 'Password Reset',
    'body' =>
"Your password was reset by an administrator.  You can find your new password below.

Email: $user_email
Password: $random_password

$login
"));
    
log_activity("password was reset for user ($user_username)", $_SESSION['sessionusername']);
$liveform_view_users->add_notice('The user\'s password has been reset, and a new password, &quot;' . h($random_password) . '&quot;, has been e-mailed to the user.');

// If there is a send to value then send user back to that screen
if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
    
// else send user to the default view
} else {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_users.php');
}
exit();
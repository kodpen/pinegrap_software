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

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('email_a_friend');

$liveform->add_fields_to_session();

// Check current page to make sure it is an email a friend page, in order to make sure that a visitor is allowed to use the e-mail a friend feature
$query = "SELECT page_folder FROM page WHERE (page_id = '" . escape($liveform->get_field_value('current_page_id')) . "') AND (page_type = 'email a friend')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if the page does not exist or it is not an e-mail a friend page, then log activity and output error
if (mysqli_num_rows($result) == 0) {
    log_activity('access denied to e-mail a link to a friend because the page that the visitor came from no longer existed or was not an e-mail a friend page', $_SESSION['sessionusername']);
    output_error('The e-mail could not be sent because the page you came from no longer exists or it is not an e-mail a friend page. <a href="javascript:history.go(-1);">Go back</a>.');
}

// get folder id for later when we check if user has edit rights to folder
$row = mysqli_fetch_assoc($result);
$folder_id = $row['page_folder'];

$liveform->validate_required_field('from_email_address', 'Your E-mail Address is required.');
$liveform->validate_required_field('recipients_email_address', 'Recipient\'s E-mail Address is required.');
$liveform->validate_required_field('subject', 'Subject is required.');

// if there is not already an error for the from e-mail address field, validate e-mail address
if ($liveform->check_field_error('from_email_address') == false) {
    if (validate_email_address($liveform->get_field_value('from_email_address')) == false) {
        $liveform->mark_error('from_email_address', 'From E-mail Address is invalid.');
    }
}

// if there is not already an error for the recipient e-mail address field, validate e-mail address
if ($liveform->check_field_error('recipients_email_address') == false) {
    if (validate_email_address($liveform->get_field_value('recipients_email_address')) == false) {
        $liveform->mark_error('recipients_email_address', 'Recipient\'s E-mail Address is invalid.');
    }
}

// if CAPTCHA is enabled then validate CAPTCHA
if (CAPTCHA == TRUE) {
    validate_captcha_answer($liveform);
}

// if there are no errors, then check to see if user has reached the usage limit for this feature
if ($liveform->check_form_errors() == false) {
    // assume that the visitor does not have edit rights to the page's folder, until we find out otherwise
    $edit_rights = false;
    
    // if user is logged into software, then get user info and check to see if user has edit rights
    if (isset($_SESSION['sessionusername']) == true) {
        $user = validate_user();
        
        // if the user has edit rights to the page's folder, then remember that
        if (check_edit_access($folder_id) == true) {
            $edit_rights = true;
        }
    }
    
    // if the visitor does not have edit rights to the page's folder, then check to see if the user has reached the usage limit for this feature
    if ($edit_rights == false) {
        // Check log table to see how many times this visitor has used this feature in the last day
        $query = "SELECT COUNT(log_id) FROM log WHERE (log_description LIKE '%e-mailed a link%') AND (log_timestamp > (UNIX_TIMESTAMP() - 86400)) AND (log_ip = '" . escape($_SERVER['REMOTE_ADDR']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // if the user has reached the limit of ten e-mails in a day, then do not allow user to send email and output error message.
        if ($row[0] >= 10) {
            $liveform->mark_error('', 'You have sent the maximum number of e-mails that you are allowed to send in one day. Please try again later.');
        }
    }
}

// if an error exists, then send user back to previous screen
if ($liveform->check_form_errors() == true) {
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($liveform->get_field_value('current_page_id')));
    exit();
}

// setup variables
$link_url = $liveform->get_field_value('link_url');
$from_email_address = $liveform->get_field_value('from_email_address');
$recipients_email_address = $liveform->get_field_value('recipients_email_address');
$subject = $liveform->get_field_value('subject');
$message = $liveform->get_field_value('message');
$send_me_a_copy = $liveform->get_field_value('send_me_a_copy');

// parse the link url in order to get host
$link_url_parsed_url = parse_url($link_url);

// if the http referer does not have a hostname or the hostname is different from the website's hostname, then set the link URL to the home page
if ((isset($link_url_parsed_url['host']) == false) || ($link_url_parsed_url['host'] != HOSTNAME)) {
    $link_url = URL_SCHEME . HOSTNAME . PATH;
}

$to = array();

$to[] = $recipients_email_address;

// if the user checked to send a copy to him/herself
if ($send_me_a_copy == 1) {
    $to[] = $from_email_address;
}

$body = '';

// if the message is not empty, then add it to the body along with a line for spacing
if ($message != '') {
    $body =
        $message . "\n" .
        "\n";
}

$body .=
    $from_email_address . ' would like to share the following link with you:' . "\n" .
    "\n" .
    $link_url . "\n" .
    "\n" .
    'This e-mail was sent to you by ' . $from_email_address . ' via the website for ' . ORGANIZATION_NAME . ' (' . URL_SCHEME . HOSTNAME . '). For your privacy, your e-mail address has not been stored.';

// In the past we would set the from info to the submitter's address
// however this caused issues with mail providers using DMARC (e.g. Yahoo, AOL),
// so we now send it from this site's info and add a reply to for the submitter.

email(array(
    'to' => $to,
    'from_name' => ORGANIZATION_NAME,
    'from_email_address' => EMAIL_ADDRESS,
    'reply_to' => $from_email_address,
    'subject' => $subject,
    'body' => $body));

// log that the user e-mailed a page to a friend
// IMPORTANT!!!: The content "e-mailed a link" below is used by the spam protection logic above, so don't change this content without updating the spam protection logic
log_activity($from_email_address . ' e-mailed a link (' . $link_url . ') to a friend', $_SESSION['sessionusername']);

// get next page id
$query =
    "SELECT next_page_id
    FROM email_a_friend_pages
    WHERE page_id = '" . escape($liveform->get_field_value('current_page_id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$next_page_id = $row['next_page_id'];

// send user to next page
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . get_page_name($next_page_id));

// remove liveform
$liveform->remove_form();
?>
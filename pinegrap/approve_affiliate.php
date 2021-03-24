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

// try to find contact for supplied id
$query = "SELECT id, email_address, affiliate_code FROM contacts WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if contact could not be found output error
if (mysqli_num_rows($result) == 0) {
    output_error('The contact for the affiliate could not be found.');
}

$row = mysqli_fetch_assoc($result);

$email_address = $row['email_address'];
$existing_affiliate_code = $row['affiliate_code'];

// if there is an existing affiliate code
if ($existing_affiliate_code) {
    $affiliate_code = $existing_affiliate_code;
    
// else there is not an existing affiliate code, so generate code
} else {
    $affiliate_code = generate_affiliate_code();
}

// update contact to be approved and update affiliate code
$query = "UPDATE contacts
         SET
            affiliate_approved = '1',
            affiliate_code = '" . escape($affiliate_code) . "',
            user = '" . $user['id'] . "',
            timestamp = UNIX_TIMESTAMP()
         WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if there is a group offer, then determine if we need to add a key code for group offer for this affiliate
if (AFFILIATE_GROUP_OFFER_ID != 0) {
    // check if offer exists and get offer code
    $query = "SELECT code FROM offers WHERE id = '" . AFFILIATE_GROUP_OFFER_ID . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if an offer was found, then continue to check if a key code should be added for group offer
    if (mysqli_num_rows($result) > 0) {
        $offer = mysqli_fetch_assoc($result);
        
        // check if a key code already exists for this group offer and affiliate
        $query =
            "SELECT id
            FROM key_codes
            WHERE
                (code = '" . escape($affiliate_code) . "')
                AND (offer_code = '" . escape($offer['code']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if a key code does not already exist for this group offer and affiliate, then create key code
        if (mysqli_num_rows($result) == 0) {
            $query =
                "INSERT INTO key_codes (
                    code,
                    offer_code,
                    enabled,
                    user,
                    timestamp)
                VALUES (
                    '" . escape($affiliate_code) . "',
                    '" . escape($offer['code']) . "',
                    '1',
                    '" . $user['id'] . "',
                    UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
}

// store values in session, so they are accessible on affiliate welcome screen
$_SESSION['software']['affiliate_welcome']['affiliate_code'] = $affiliate_code;

email(array(
    'to' => $email_address,
    'bcc' => AFFILIATE_EMAIL_ADDRESS,
    'from_name' => ORGANIZATION_NAME,
    'from_email_address' => EMAIL_ADDRESS,
    'subject' => 'Welcome to the Affiliate Program',
    'format' => 'html',
    'body' => get_affiliate_welcome_screen()));

// add notice to view contact screen
include_once('liveform.class.php');
$liveform = new liveform('view_contact');
$liveform->add_notice('The affiliate has been approved.');

header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_contact.php?id=' . $_GET['id']);
?>

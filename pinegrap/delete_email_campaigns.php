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
validate_email_access($user);

validate_token_field();

include_once('liveform.class.php');

if (mb_strpos($_POST['send_to'], 'view_email_campaign_history.php') !== false) {
    $liveform = new liveform('view_email_campaign_history');
} else {
    $liveform = new liveform('view_email_campaigns');
}

// if at least one e-mail campaign was selected
if ($_POST['email_campaigns']) {
    $number_of_email_campaigns = 0;
    
    // Loop through all email campaigns that where selected.
    foreach ($_POST['email_campaigns'] as $email_campaign_id) {
        // assume that the user has access to this e-mail campaign until we find out otherwise
        $access = true;
        
        // if user has a user role
        if ($user['role'] == 3) {
            // get user that created this e-mail campaign, in order to check if user has access to this e-mail campaign
            $query =
                "SELECT created_user_id
                FROM email_campaigns
                WHERE id = '" . escape($email_campaign_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $created_user_id = $row['created_user_id'];
            
            // if user did not create this e-mail campaign, then the user does not have access to it
            if ($created_user_id != $user['id']) {
                $access = false;
            }
        }
        
        // if the user has access to this e-mail campaign, then delete it
        if ($access == true) {
            // delete the e-mail campaign from the database.
            $query = "DELETE FROM email_campaigns WHERE id = '" . escape($email_campaign_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete the records of contact groups for this e-mail campaign
            $query = "DELETE FROM contact_groups_email_campaigns_xref WHERE email_campaign_id  = '" . escape($email_campaign_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $number_of_email_campaigns++;
        }
    }
    
    // if 1 or more e-mail campaigns were deleted then log this information and output a notice
    if ($number_of_email_campaigns > 0) {
        log_activity("$number_of_email_campaigns campaign(s) were deleted", $_SESSION['sessionusername']);
        $liveform->add_notice("$number_of_email_campaigns campaign(s) were deleted.");
    }
}

// If there is a send to set, then forward user to send to.
if ($_POST['send_to'] != '') {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
    
// Otherwise there is not a send to set, so forward user to view e-mail campaigns screen.
} else {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_email_campaigns.php');
}
?>
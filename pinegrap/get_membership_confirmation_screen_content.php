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

function get_membership_confirmation_screen_content()
{
    $email_address = '';
    $username = '';
    
    // if the user is logged in, then get user's e-mail address and username
    if (isset($_SESSION['sessionusername']) == true) {
        // get e-mail address
        $query = "SELECT user_email FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $email_address = $row['user_email'];
        
        // set username
        $username = $_SESSION['sessionusername'];
    }
    
    return
        'Email: ' . h($email_address) . '<br />
        Username: ' . h($username) . '<br />
        <br />
        <a href="' . URL_SCHEME . HOSTNAME . h($_REQUEST['send_to']) . '" class="software_button_primary">Continue</a><br />';
}
?>
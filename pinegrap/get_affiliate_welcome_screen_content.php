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

function get_affiliate_welcome_screen_content() {

    // get commission rate for affiliate
    $query = "SELECT affiliate_commission_rate FROM contacts WHERE affiliate_code = '" . escape($_SESSION['software']['affiliate_welcome']['affiliate_code']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    
    // if affiliate has a commission rate specifically defined, then use that rate
    if ($row['affiliate_commission_rate'] > 0) {
        $affiliate_commission_rate = $row['affiliate_commission_rate'];
        
    // else use default rate
    } else {
        $affiliate_commission_rate = AFFILIATE_DEFAULT_COMMISSION_RATE;
    }
    
    $output =
        '<div>
            Affiliate Code: ' . h($_SESSION['software']['affiliate_welcome']['affiliate_code']) . '<br />
            Affiliate Link: <a href="' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . '/?a=' . h(urlencode($_SESSION['software']['affiliate_welcome']['affiliate_code'])) . '">' . URL_SCHEME . h($_SERVER['HTTP_HOST']) . '/?a=' . h(urlencode($_SESSION['software']['affiliate_welcome']['affiliate_code'])) . '</a><br>
            Affiliate Commission Rate: ' . $affiliate_commission_rate . '%
        </div>';

    return $output;
}
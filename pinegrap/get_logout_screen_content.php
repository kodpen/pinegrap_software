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

function get_logout_screen_content()
{
    $output = 'You have logged out successfully. <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/">Login</a>.<br />';

    return $output;
}
?>
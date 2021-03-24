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

function get_affiliate_sign_up_confirmation_screen_content()
{
    $output =
        '<table>
            <tr>
                <td>First Name:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['first_name']) . '</td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['last_name']) . '</td>
            </tr>
            <tr>
                <td>Address 1:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['address_1']). '</td>
            </tr>
            <tr>
                <td>Address 2:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['address_2']) . '</td>
            </tr>
            <tr>
                <td>City:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['city']) . '</td>
            </tr>
            <tr>
                <td>State / Province:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['state']) . '</td>
            </tr>
            <tr>
                <td>Zip / Postal Code:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['zip_code']) . '</td>
            </tr>
            <tr>
                <td>Country:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['country']) . '</td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['phone_number']) . '</td>
            </tr>
            <tr>
                <td>Fax:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['fax_number']) . '</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['email_address']) .  '</td>
            </tr>
            <tr>
                <td>Affiliate Code:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['affiliate_code']) . '</td>
            </tr>
            <tr>
                <td>Affiliate / Company Name:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['affiliate_name']) . '</td>
            </tr>
            <tr>
                <td>Affiliate Website:</td>
                <td>' . h($_SESSION['software']['affiliate_sign_up_confirmation']['affiliate_website']) . '</td>
            </tr>
        </table>';

    return $output;
}
?>
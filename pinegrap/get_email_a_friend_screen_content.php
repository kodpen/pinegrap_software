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

function get_email_a_friend_screen_content($properties)
{
    // Setup properties
    $current_page_id = $properties['current_page_id'];
    $submit_button_label = $properties['submit_button_label'];
    
    include_once('liveform.class.php');
    $liveform = new liveform('email_a_friend');
    
    // if the form has not been submitted yet, then prefill values
    if ($liveform->field_in_session('from_email_address') == false) {
        // parse the http referer url in order to get hostname
        $http_referer_parsed_url = parse_url($_SERVER['HTTP_REFERER']);
        
        // if there is a hostname in the http referer and the hostname is this website's hostname
        if ((isset($http_referer_parsed_url['host']) == true) && ($http_referer_parsed_url['host'] == HOSTNAME)) {
            // set the link URL to the http referer until we find out if we need to modify the link URL
            $liveform->assign_field_value('link_url', $_SERVER['HTTP_REFERER']);
            
            // get the position of the page name in case it exists in the URL
            $page_name_position = mb_strlen(PATH);
            
            // if the http_referer has a path
            // and the first certain number of characters are the path
            // and there is not a query string or the reference code is not already in the URL
            // and there is an active order
            // then determine if a reference code should be added to the link URL
            if (
                (isset($http_referer_parsed_url['path']) == true)
                && (mb_substr($http_referer_parsed_url['path'], 0, $page_name_position) == PATH)
                &&
                (
                    (isset($http_referer_parsed_url['query']) == false)
                    || (mb_strpos($http_referer_parsed_url['query'], 'r=') === false)
                )
                && (isset($_SESSION['ecommerce']['order_id']) == true)
            ) {
                // get page name
                $page_name = mb_substr($http_referer_parsed_url['path'], $page_name_position);
                
                // get page type of the refering page to see if it has a shopping cart or express order page type
                $query = "SELECT page_type FROM page WHERE page_name = '" . escape($page_name) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $page_type = $row['page_type'];
                
                // if the page type is shopping cart or express order
                if (($page_type == 'shopping cart') || ($page_type == 'express order')) {
                    // get order reference code that we will use to build a query string
                    $query = "SELECT reference_code FROM orders WHERE id = '" . $_SESSION['ecommerce']['order_id'] . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $reference_code = $row['reference_code'];
                    
                    // if there is a query string in the http referer
                    if ((isset($http_referer_parsed_url['query']) == true) && ($http_referer_parsed_url['query'] != '')) {
                        $query_string = '?' . $http_referer_parsed_url['query'] . '&r=' . $reference_code;
                    } else {
                        $query_string = '?r=' . $reference_code;
                    }
                    
                    // if there is an anchor on the http referer
                    if ((isset($http_referer_parsed_url['fragment']) == true) && ($http_referer_parsed_url['fragment'] != '')) {
                        $anchor = '#' . $http_referer_parsed_url['fragment'];
                    } else {
                        $anchor = '';
                    }
                    
                    // create a new link url
                    $liveform->assign_field_value('link_url', $http_referer_parsed_url['scheme'] . '://' . $http_referer_parsed_url['host'] . PATH . $page_name . $query_string . $anchor);
                }
            }
            
        // else the hostname in the http referer is not allowed, so set the link URL to the home page and add a notice
        } else {
            $liveform->assign_field_value('link_url', URL_SCHEME . HOSTNAME . PATH);
            $liveform->add_notice('We could not determine which link you wanted to e-mail, so we will include a link to the home page. If you prefer a different link, please browse to the desired page and click to e-mail that link.');
        }
        
        // if the user is logged in, then prefill e-mail address
        if (isset($_SESSION['sessionusername']) == true) {
            // get email address for user
            $query = "SELECT user_email FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            $row = mysqli_fetch_assoc($result);
            
            // prefill field
            $liveform->assign_field_value('from_email_address', $row['user_email']);
        }
    }
    
    $output_captcha_fields = '';
    
    // if CAPTCHA is enabled then prepare to output CAPTCHA fields
    if (CAPTCHA == TRUE) {
        // get captcha fields if there are any
        $output_captcha_fields = get_captcha_fields($liveform);
        
        // if there are captcha fields to be displayed, then output them in a container
        if ($output_captcha_fields != '') {
            $output_captcha_fields = '<div style="margin-bottom: 1em">' . $output_captcha_fields . '</div>';
        }
    }
    
    // if the submit button label is not blank, then use it
    if ($submit_button_label != '') {
        $output_submit_button_label = h($submit_button_label);
        
    // else the submit button label is blank, so set default value
    } else {
        $output_submit_button_label = 'Submit';
    }
    
    $output = 
        $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/email_a_friend.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="current_page_id" value="' . $current_page_id . '" />
            ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'link_url')) . '
            <table style="margin-bottom: 0.5em">
                <tr>
                    <td>Your E-mail Address*:</td>
                    <td>' . $liveform->output_field(array('type'=>'email', 'name'=>'from_email_address', 'size'=>'40', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Recipient\'s E-mail Address*:</td>
                    <td>' . $liveform->output_field(array('type'=>'email', 'name'=>'recipients_email_address', 'size'=>'40', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Subject*:</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'subject', 'size'=>'40', 'maxlength'=>'255', 'class'=>'software_input_text')) . '</td>
                </tr>
            </table>
            <div>Message:</div>
            <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'name'=>'message', 'rows'=>'3', 'cols'=>'46', 'class'=>'software_textarea', 'style'=>'width: 98%')) . '</div>
            <div>The following link will be included in the e-mail:</div>
            <div style="margin-bottom: 1em"><a href="' . h(escape_url($liveform->get_field_value('link_url'))) . '" target="_blank">' . h($liveform->get_field_value('link_url')) . '</a></div>
            <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'send_me_a_copy', 'id'=>'send_me_a_copy', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="send_me_a_copy"> Send me a copy.</label></div>
            ' . $output_captcha_fields . '
            <input type="submit" name="submit" value="' . $output_submit_button_label . '" class="software_input_submit_primary submit_button" />
        </form>';
    
    $liveform->remove_form();
    
    return $output;
}
?>
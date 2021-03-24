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

// set memory limit to unlimited
ini_set('memory_limit', '-1');
include('init.php');
$user = validate_user();
validate_email_access($user);

// if form has not been submitted yet
if (!$_POST) {
    // if a page id was passed in the query string, then check the HTML radio button and show and hide rows
    if (isset($_GET['page_id']) == TRUE) {
        $output_format_plain_text_checked = '';
        $output_format_html_checked = ' checked="checked"';
        $output_body_row_style = ' style="display: none"';
        $output_page_id_row_style = '';

        $query = "SELECT page_name, page_folder FROM page WHERE page_id = '" . escape($_GET['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $page_name = $row['page_name'];
        $folder_id = $row['page_folder'];
        
        // if the user has edit access to the page, then prepare to show body preview row and set iframe source
        if (check_edit_access($folder_id) == true) {
            $output_body_preview_row_style = '';
            $output_body_preview_iframe_source = OUTPUT_PATH . h(encode_url_path($page_name)) . '?edit=no&email=true';

        // else the user does not have edit access to the page, so hide body preview row and set iframe source to blank
        } else {
            $output_body_preview_row_style = ' style="display: none"';
            $output_body_preview_iframe_source = '';
        }

    // else a page id was not passed in the query string, so check the plain text radio button and show and hide rows
    } else {
        $output_format_plain_text_checked = ' checked="checked"';
        $output_format_html_checked = '';
        $output_body_row_style = '';
        $output_page_id_row_style = ' style="display: none"';
        $output_body_preview_row_style = ' style="display: none"';
        $output_body_preview_iframe_source = '';
    }

    $output_body = '';

    $plain_text_email_campaign_footer = db_value("SELECT plain_text_email_campaign_footer FROM config");

    if ($plain_text_email_campaign_footer != '') {
        $output_body =
            "\n" .
            "\n" .
            "\n" .
            h($plain_text_email_campaign_footer);
    }

    // get all pages
    $query =
        "SELECT
            page.page_id as id,
            page.page_name as name,
            page.page_folder as folder_id,
            folder.folder_archived
        FROM page
        LEFT JOIN folder ON page.page_folder = folder.folder_id
        WHERE folder.folder_archived = '0'
        ORDER BY page.page_name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $pages = array();
    
    // loop through all pages so they can be added to array
    while ($row = mysqli_fetch_assoc($result)) {
        $pages[] = $row;
    }
    
    $output_page_options = '';
    
    // loop through all pages in order to prepare options for pick list
    foreach ($pages as $page) {
        // if the user has access to view this page, then prepare option for this page
        if (check_view_access($page['folder_id']) == true) {
            // assume that this page should not be selected by default, until we find out otherwise
            $selected = '';
            
            // if this page should be selected, then prepare to select it
            if ($page['id'] == $_GET['page_id']) {
                $selected = ' selected="selected"';
            }
            
            // prepare option
            $output_page_options .= '<option value="' . $page['id'] . '"' . $selected . '>' . h($page['name']) . '</option>';
        }
    }
    
    // get all contact groups
    $query =
        "SELECT
           id,
           name
        FROM contact_groups
        ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $output_contact_group_rows = '';
    
    // loop through all contact groups
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $name = $row['name'];
        
        // if user has access to contact group, then include this contact group
        if (validate_contact_group_access($user, $id) == true) {
            // get number of contacts in contact group
            $number_of_contacts = get_number_of_contacts($id, $require_email = true);
            
            // if contact group has at least one contact
            if ($number_of_contacts > 0) {
                $output_contact_group_rows .=
                    '<tr>
                        <td>' . h($name) . ' (' . number_format($number_of_contacts) . ')</td>
                        <td style="padding-right: 2em; text-align: center"><input type="radio" name="contact_group_' . $id . '" value="ignored" class="radio" checked="checked" /></td>
                        <td style="padding-right: 2em; text-align: center"><input type="radio" name="contact_group_' . $id . '" value="included" class="radio" /></td>
                        <td style="text-align: center"><input type="radio" name="contact_group_' . $id . '" value="excluded" class="radio" /></td>
                    </tr>';
            }
        } 
    }
    
    // if there is at least one contact group to show, then prepare to output contact groups
    if ($output_contact_group_rows != '') {
        $output_contact_groups =
            '<div style="margin-bottom: 1em">Send message to all Subscribers in my selected Contact Groups:</div>
            <div style="margin-bottom: 1.5em">
                <table>
                    <tr>
                        <th>&nbsp;</th>
                        <th style="padding-right: 2em; text-align: center">Ignore</th>
                        <th style="padding-right: 2em; text-align: center">Include</th>
                        <th style="text-align: center">Exclude</th>
                    </tr>
                    ' . $output_contact_group_rows . '
                </table>
            </div>';
    } else {
        $output_contact_groups = '<div style="margin-bottom: 1.5em">[You do not have access to any contact groups with subscribers.]</div>';
    }
    
    // if an e-mail campaign job is setup on the server, then allow e-mail campaign to be scheduled
    if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB === true) {
        $output_start_time =
            '<tr>
                <th colspan="2"><h2>E-Mail Message Delivery Schedule</h2></th>
            </tr>
            <tr>
                <td>Send at this Date &amp; Time:</td>
                <td>
                    <input type="text" id="start_time" name="start_time" size="20" maxlength="19" value="" /> (Leave blank to send as soon as possible.)
                    ' . get_date_picker_format() . '
                    <script>
                        $("#start_time").datetimepicker({
                            dateFormat: date_picker_format,
                            timeFormat: "h:mm TT"
                        });
                    </script>
                </td>
            </tr>';
    }
    
    print
        output_header() . '
        <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
        <div id="subnav">
            <h1>[new campaign]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Campaign</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new e-mail campaign to send to all subscribers in selected contact groups.</div>
            <form name="form" action="add_email_campaign.php" method="post" style="margin: 0em; padding: 0em">
                ' . get_token_field() . '
                <table class="field" style="width: 100%">
                    <tr>
                        <th colspan="2"><h2>New E-Mail Message</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 20%">Subject:</td>
                        <td><input type="text" name="subject" size="80" maxlength="255" /></td>
                    </tr>
                    <tr>
                        <td>Format:</td>
                        <td><input type="radio" id="format_plain_text" name="format" value="plain_text" class="radio"' . $output_format_plain_text_checked . ' onclick="show_or_hide_email_campaign_format()" /><label for="format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="format_html" name="format" value="html" class="radio"' . $output_format_html_checked . ' onclick="show_or_hide_email_campaign_format()" /><label for="format_html">HTML</label></td>
                    </tr>
                    <tr id="body_row"' . $output_body_row_style . '>
                        <td colspan="2">
                            <div style="margin-bottom: 1em">Body:</div>
                            <textarea name="body" style="width: 99%; height: 300px">' . $output_body . '</textarea>
                        </td>
                    </tr>
                    <tr id="page_id_row"' . $output_page_id_row_style . '>
                        <td>My Page to Send:</td>
                        <td><select id="page_id" name="page_id" onchange="if (this.options[this.selectedIndex].firstChild) {document.getElementById(\'body_preview_iframe\').src = \'' . OUTPUT_PATH . '\' + this.options[this.selectedIndex].firstChild.nodeValue + \'?edit=no&email=true\'; document.getElementById(\'body_preview_row\').style.display = \'\';} else {document.getElementById(\'body_preview_row\').style.display = \'none\';}"><option value=""></option>' . $output_page_options . '</select></td>
                    </tr>
                    <tr id="body_preview_row"' . $output_body_preview_row_style . '>
                        <td colspan="2">
                            <div style="margin-bottom: 1em">Body Preview:</div>
                            <iframe id="body_preview_iframe" src="' . $output_body_preview_iframe_source . '" style="width: 99%; height: 300px"></iframe>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>E-Mail Message To My Contact Groups</h2></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            ' . $output_contact_groups . '
                            <div>
                                <div>Also send message to the following e-mail address: <input type="text" name="entered_email_address" size="40" /></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>E-Mail Message From</h2></th>
                    </tr>
                    <tr>
                        <td>From Name:</td>
                        <td><input type="text" name="from_name" size="40" value="' . h(ORGANIZATION_NAME) . '" maxlength="100" /></td>
                    </tr>
                    <tr>
                        <td>From E-mail Address:</td>
                        <td><input type="text" name="from_email_address" size="40" value="' . h($user['email_address']) .'" maxlength="100" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>E-Mail Message Reply To</h2></th>
                    </tr>
                    <tr>
                        <td>Reply to E-mail Address:</td>
                        <td><input type="text" name="reply_email_address" size="40" value="' . h($user['email_address']) .'" maxlength="100" /></td>
                    </tr>
                    ' . $output_start_time . '
                    <tr>
                        <th colspan="2"><h2>Purpose (as defined by the CAN-SPAM Act)</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Purpose:</td>
                        <td>
                            <div>
                                <input
                                    type="radio"
                                    id="purpose_commercial"
                                    name="purpose"
                                    value="commercial"
                                    class="radio"
                                    checked="checked"><label for="purpose_commercial"> Commercial &nbsp;(send email to opted-in contacts only. Example: "We have an offer for you")</label>
                            </div>

                            <div>
                                <input
                                    type="radio"
                                    id="purpose_transactional"
                                    name="purpose"
                                    value="transactional"
                                    class="radio"><label for="purpose_transactional"> Transactional &nbsp;(send email, regardless of opt-in. Example: "Your order has been shipped")</label>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
// else form has been submitted
} else {
    validate_token_field();
    
    // if the user selected plain text, then clear the page id, so we don't store it with the e-mail campaign
    if ($_POST['format'] == 'plain_text') {
        $_POST['page_id'] = '';

    // else the user selected HTML for the format, so do some checks
    } else {
        // if the user did not select a page, then output error
        if ($_POST['page_id'] == '') {
            output_error('Please select a page for the body of the campaign. <a href="javascript:history.go(-1)">Go back</a>.');
        }

        // get folder id for selected page in order to check if user has access to view content in folder
        $query = "SELECT page_folder FROM page WHERE page_id = '" . escape($_POST['page_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $folder_id = $row['page_folder'];
        
        // if the user does not have view access to the selected page's folder, then log activity and output error
        if (check_view_access($folder_id) == false) {
            log_activity("access denied for user to send a page for a campaign, because user does not have view access to the page", $_SESSION['sessionusername']);
            output_error('You are not authorized to send that page for the body of the campaign. <a href="javascript:history.go(-1)">Go back</a>.');
        }
    }
    
    $recipients = array();
    $recipients_contact_id_xref = array();
    
    // get all contact groups
    $query = "SELECT id FROM contact_groups";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contact_groups = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $contact_groups[] = $row['id'];
    }
    
    $included_contact_groups = array();
    
    // loop through all contact groups in order to get included e-mail addresses
    foreach ($contact_groups as $contact_group_id) {
        // if this contact group was included, then get e-mail addresses for this contact group
        if ($_POST['contact_group_' . $contact_group_id] == 'included') {
            // if user has access to contact group, then continue
            if (validate_contact_group_access($user, $contact_group_id) == true) {
                // store this included contact group in array, so later we can store which contact groups were included
                $included_contact_groups[] = $contact_group_id;
                
                // get e-mail subscription information about contact group
                $query =
                    "SELECT email_subscription
                    FROM contact_groups
                    WHERE id = '" . escape($contact_group_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $email_subscription = $row['email_subscription'];
                
                // if this is a subscription contact group
                if ($email_subscription == 1) {
                    // get all contacts in this contact group that are global opted-in and opted-in to this contact group
                    $query =
                        "SELECT
                            contacts.id,
                            contacts.email_address,
                            opt_in.opt_in
                        FROM contacts_contact_groups_xref
                        LEFT JOIN contacts ON contacts_contact_groups_xref.contact_id = contacts.id
                        LEFT JOIN opt_in ON (contacts_contact_groups_xref.contact_id = opt_in.contact_id) AND (contacts_contact_groups_xref.contact_group_id = opt_in.contact_group_id)
                        WHERE
                            (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_group_id) . "')
                            AND (contacts.opt_in = 1)
                            AND (
                                (opt_in.opt_in = 1)
                                OR (opt_in.opt_in IS NULL)
                            )
                            AND (contacts.email_address != '')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                // else this is not a subscription contact group
                } else {
                    // get all contacts in this contact group that are global opted-in
                    $query =
                        "SELECT
                            contacts.id,
                            contacts.email_address
                        FROM contacts_contact_groups_xref
                        LEFT JOIN contacts ON contacts_contact_groups_xref.contact_id = contacts.id
                        WHERE
                            (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_group_id) . "')
                            AND (contacts.opt_in = 1)
                            AND (contacts.email_address != '')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
                
                // loop through all contact e-mail addresses
                while ($row = mysqli_fetch_assoc($result)) {
                    $email_address = mb_strtolower($row['email_address']);
                    $recipients[] = $email_address;
                    
                    // prevent duplicate entries from overwriting the first contact id found for this e-mail address
                    if (!array_key_exists($email_address, $recipients_contact_id_xref)) {
                        $recipients_contact_id_xref[$email_address] = $row['id'];
                    }
                }
            }
        }
    }
    
    // remove duplicate e-mail addresses before we exclude e-mail addresses
    $recipients = array_unique($recipients);
    
    $excluded_contact_groups = array();
    
    // loop through all contact groups in order to get excluded e-mail addresses
    foreach ($contact_groups as $contact_group_id) {
        // if this contact group was excluded, then get e-mail addresses for this contact group
        if ($_POST['contact_group_' . $contact_group_id] == 'excluded') {
            // if user has access to contact group, then continue
            if (validate_contact_group_access($user, $contact_group_id) == true) {
                // store this excluded contact group in array, so later we can store which contact groups were excluded
                $excluded_contact_groups[] = $contact_group_id;
                
                // get all contacts in this contact group
                $query =
                    "SELECT contacts.email_address
                    FROM contacts_contact_groups_xref
                    LEFT JOIN contacts ON contacts_contact_groups_xref.contact_id = contacts.id
                    WHERE
                        (contacts_contact_groups_xref.contact_group_id = '" . escape($contact_group_id) . "')
                        AND (contacts.email_address != '')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // loop through all contact e-mail addresses
                while ($row = mysqli_fetch_assoc($result)) {
                    // check if this e-mail address has been included
                    $key = array_search(mb_strtolower($row['email_address']), $recipients);
                    
                    // if this e-mail address has been included, then exclude this e-mail address
                    if ($key !== false) {
                        unset($recipients[$key]);
                    }
                }
            }
        }
    }
    
    // if entered e-mail address was entered
    if ($_POST['entered_email_address']) {
        // if e-mail address is valid
        if (validate_email_address($_POST['entered_email_address']) == true) {
            $recipients[] = mb_strtolower($_POST['entered_email_address']);
            
            // if user has a role that is greater than user role, then possibly create contact for entered e-mail address
            if ($user['role'] < 3) {
                // determine if entered e-mail address is already in use by a contact
                $query = "SELECT id
                         FROM contacts
                         WHERE email_address = '" . escape($_POST['entered_email_address']) . "'";
                $result_contacts = mysqli_query(db::$con, $query) or output_error('Query failed.');

                // determine if entered e-mail address is already in use by a user
                $query = "SELECT user_id
                         FROM user
                         WHERE user_email = '" . escape($_POST['entered_email_address']) . "'";
                $result_users = mysqli_query(db::$con, $query) or output_error('Query failed.');

                // if entered e-mail address is not in use by a contact or a user, create contact for entered e-mail address
                if ((mysqli_num_rows($result_contacts) == 0) && (mysqli_num_rows($result_users) == 0)) {
                    $query = "INSERT INTO contacts
                             (email_address, user, timestamp)
                             VALUES ('" . escape($_POST['entered_email_address']) . "', '" . $user['id'] . "', UNIX_TIMESTAMP())";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }

        // else e-mail address is not valid, so generate error
        } else {
            output_error('The e-mail address you entered under Enter Recipient is invalid. <a href="javascript:history.go(-1)">Go back</a>.');
        }
    }

    // if there are no recipients, output error
    if (!$recipients) {
        output_error('Please select at least one recipient. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // remove duplicate e-mail addresses
    $recipients = array_unique($recipients);
    
    // get subject
    $subject = $_POST['subject'];

    // if plain text was selected for the format, then store body in variable
    if ($_POST['format'] == 'plain_text') {
        $body = $_POST['body'];

    // else HTML was selected for the format, so prepare body and store in variable
    } else {
        require_once(dirname(__FILE__) . '/get_page_content.php');

        // get html for page
        $body = get_page_content($_POST['page_id'], $system_content = '', $extra_system_content = '', $mode = 'preview', $email = true);
        
        // find if there is a base tag in the HTML
        $base_in_html = preg_match('/<\s*base\s+[^>]*href\s*=\s*["\'](?:http:\/\/|https:\/\/|ftp:\/\/).*?["\']/is', $body);

        // if there is not a base tag in the HTML, add base tag and convert relative links to absolute links
        if (!$base_in_html) {
            $base = '<head>' . "\n" . '<base href="' . URL_SCHEME . HOSTNAME_SETTING . '/" />';
            $body = preg_replace('/<head>/i', $base, $body);

            // change relative URLs to absolute URLs for links
            $body = preg_replace('/(<\s*a\s+[^>]*href\s*=\s*["\'])(?!ftp:\/\/|https:\/\/|mailto:|http:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'].*?>)/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

            // change relative URLs to absolute URLs for images
            $body = preg_replace('/(<\s*img\s+[^>]*src\s*=\s*["\'])(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'].*?>)/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

            // change relative URLs to absolute URLs for CSS background images
            $body = preg_replace('/(background-image\s*:\s*url\s*\(\s*(?:"|\'|))(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?(?:"|\'|).*?\))/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);

            // change relative URLs to absolute URLs for HTML background images
            $body = preg_replace('/(background\s*=\s*["\'])(?!http:\/\/|https:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'])/is', "$1" . URL_SCHEME . HOSTNAME_SETTING . "/$2", $body);
        }
        
        // get all links in order to add tracking codes to links
        preg_match_all('/(<\s*a\s+[^>]*href\s*=\s*["\']\s*)(.*?)(\s*["\'].*?>)/is', $body, $links);

        // If the date format is month and then day, then use that format.
        if (DATE_FORMAT == 'month_day') {
            $month_and_day_format = 'm/d';

        // Otherwise the date format is day and then month, so use that format.
        } else {
            $month_and_day_format = 'd/m';
        }

        // set tracking code to contain the page name and date and time
        $tracking_code = get_page_name($_POST['page_id']) . '_' . date($month_and_day_format . '/Y_h:i_A');

        // loop through all links in order to add tracking codes to links
        foreach ($links[0] as $key => $link) {
            // set the URL that was found in the link
            $url = $links[2][$key];
            
            // remove new lines from the link
            $url = str_replace("\r\n", '', $url);
            $url = str_replace("\n", '', $url);
            
            $url_parts = @parse_url($url);
            
            // if the URL is valid
            // and if there is not a scheme or the scheme is http or https
            // and if there is not a hostname or the hostname is this site's hostname
            // and if there is not already a tracking code in the URL
            // then continue with adding tracking code to URL
            if (
                ($url_parts != false)
                && ((isset($url_parts['scheme']) == false) || ($url_parts['scheme'] == '') || (mb_strtolower($url_parts['scheme']) == 'http') || (mb_strtolower($url_parts['scheme']) == 'https'))
                && ((isset($url_parts['host']) == false) || ($url_parts['host'] == '') || (mb_strtolower(str_replace('www.', '', $url_parts['host'])) == mb_strtolower(str_replace('www.', '', HOSTNAME_SETTING))))
                && ((isset($url_parts['query']) == false) || ($url_parts['query'] == '') || mb_strpos($url_parts['query'], 't=') === false)
            ) {
                $new_url = '';
                
                // if there is a scheme, then add scheme to new URL
                if ((isset($url_parts['scheme']) == true) && ($url_parts['scheme'] != '')) {
                    $new_url .= $url_parts['scheme'] . '://';
                }
                
                // if there is a hostname, then add hostname to new URL
                if ((isset($url_parts['host']) == true) && ($url_parts['host'] != '')) {
                    $new_url .= $url_parts['host'];
                }
                
                // if there is a path, then add path to new URL
                if ((isset($url_parts['path']) == true) && ($url_parts['path'] != '')) {
                    $new_url .= $url_parts['path'];
                }
                
                $new_url .= '?';
                
                // if there is a query string, then add query string and ampersand to new URL
                if ((isset($url_parts['query']) == true) && ($url_parts['query'] != '')) {
                    $new_url .= $url_parts['query'] . '&amp;';
                }
                
                $new_url .= 't=' . h(urlencode($tracking_code));
                
                // if there is a bookmark, then add bookmark to the new URL
                if ((isset($url_parts['fragment']) == true) && ($url_parts['fragment'] != '')) {
                    $new_url .= '#' . $url_parts['fragment'];
                }
                
                $entire_link = $links[0][$key];
                $link_start = $links[1][$key];
                $link_end = $links[3][$key];        
                
                // replace the link with the new link
                $body = str_replace($entire_link, $link_start . $new_url . $link_end, $body);
            }
        }
        
        // get URL for page
        $page_url = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_email_campaign.php?r=<reference_code></reference_code>';

        $email_preferences_url = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/email_preferences.php?id=<email_address_id></email_address_id>';
        

		if(language_ruler()==='en'){
			 $footer =
			     '<div class="software_email_footer" style="font-family: arial; font-size: 11px; color: #666666; text-align: center; background-color: #ffffff; padding: 5px; margin-top: 15px">
			         <a href="' . $page_url . '" style="color: #666666">View this email at our site</a><br>
			         ' . h(ORGANIZATION_NAME) . '
			         ' . h(ORGANIZATION_ADDRESS_1) . '
			         ' . h(ORGANIZATION_ADDRESS_2) . '
			         ' . h(ORGANIZATION_CITY) . ' ' . h(ORGANIZATION_STATE) . ' ' . h(ORGANIZATION_ZIP_CODE) . ' ' . h(ORGANIZATION_COUNTRY) . '<br>
			         <a href="' . $email_preferences_url . '" style="color: #666666">Update email preferences</a> or <a href="' . $email_preferences_url . '" style="color: #666666">unsubscribe</a><br>
			     </div>
			     </body>';
		} else if(language_ruler()==='tr'){
			 $footer =
			     '<div class="software_email_footer" style="font-family: arial; font-size: 11px; color: #666666; text-align: center; background-color: #ffffff; padding: 5px; margin-top: 15px">
			         <a href="' . $page_url . '" style="color: #666666">Bu e-postayi sitemizde goruntuleyin</a><br>
			         ' . h(ORGANIZATION_NAME) . '
			         ' . h(ORGANIZATION_ADDRESS_1) . '
			         ' . h(ORGANIZATION_ADDRESS_2) . '
			         ' . h(ORGANIZATION_CITY) . ' ' . h(ORGANIZATION_STATE) . ' ' . h(ORGANIZATION_ZIP_CODE) . ' ' . h(ORGANIZATION_COUNTRY) . '<br>
			         <a href="' . $email_preferences_url . '" style="color: #666666">E-posta tercihlerini guncelle</a> or <a href="' . $email_preferences_url . '" style="color: #666666">Abonelikten cik</a><br>
			     </div>
			     </body>';
		
		}




        $body = preg_replace('/<\/body>/i', $footer, $body);
    }

    // wrap long lines (RFC 821)
    $body = wordwrap($body, 900, "\n", 1);
    
    // create e-mail campaign
    $query =
        "INSERT INTO email_campaigns (
            from_name,
            from_email_address,
            reply_email_address,
            subject,
            format,
            body,
            page_id,
            start_time,
            purpose,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($_POST['from_name']) . "',
            '" . escape($_POST['from_email_address']) . "',
            '" . escape($_POST['reply_email_address']) . "',
            '" . escape($_POST['subject']) . "',
            '" . escape($_POST['format']) . "',
            '" . escape($body) . "',
            '" . escape($_POST['page_id']) . "',
            '" . escape(prepare_form_data_for_input($_POST['start_time'], 'date and time')) . "',
            '" . e($_POST['purpose']) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $email_campaign_id = mysqli_insert_id(db::$con);
    
    // loop through all recipients in order to create record in database for each recipient
    foreach ($recipients as $email_address) {
        if (array_key_exists($email_address, $recipients_contact_id_xref)) {
            $contact_id = $recipients_contact_id_xref[$email_address];
        } else {
            $contact_id = 0;
        }
        
        // create e-mail recipients
        $query =
            "INSERT INTO email_recipients (
                email_campaign_id,
                email_address,
                contact_id)
            VALUES (
                '$email_campaign_id',
                '" . escape($email_address) . "',
                '" . $contact_id . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // loop through included contact groups in order to store contacts groups that were included in this e-mail campaign
    foreach ($included_contact_groups as $contact_group_id) {
        $query =
            "INSERT INTO contact_groups_email_campaigns_xref (
                contact_group_id,
                email_campaign_id,
                type)
            VALUES (
                '" . escape($contact_group_id) . "',
                '$email_campaign_id',
                'included')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // loop through excluded contact groups in order to store contacts groups that were excluded in this e-mail campaign
    foreach ($excluded_contact_groups as $contact_group_id) {
        $query =
            "INSERT INTO contact_groups_email_campaigns_xref (
                contact_group_id,
                email_campaign_id,
                type)
            VALUES (
                '" . escape($contact_group_id) . "',
                '$email_campaign_id',
                'excluded')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    $log_page = '';

    // if HTML was selected for the format, then add page info to log message
    if ($_POST['format'] == 'html') {
        $log_page = ', page: ' . get_page_name($_POST['page_id']);
    }
    
    log_activity('campaign (subject: ' . $_POST['subject'] . $log_page . ') was created', $_SESSION['sessionusername']);

    include_once('liveform.class.php');
    $liveform = new liveform('view_email_campaigns');
    
    // if email campaign job is active
    if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB === true) {
        $liveform->add_notice('The campaign has been created, and it will be sent at the scheduled time.');
        
    // else email campaign job is not active
    } else {
        $liveform->add_notice('The campaign has been created, and you may <a href="send_email_campaign.php?id=' . $email_campaign_id . get_token_query_string_field() . '" onclick="window.open(\'send_email_campaign.php?id=' . $email_campaign_id . get_token_query_string_field() . '\', \'\', \'width=450, height=350, resizable=1, scrollbars=0\'); return false;">send it now</a>.');
    }
    
    // forward user to view e-mail campaigns screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_email_campaigns.php');
}
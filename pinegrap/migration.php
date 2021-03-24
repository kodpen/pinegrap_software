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

if ((defined('MIG') == false) || (MIG != true)) {
    output_error('This feature is not currently available. <a href="javascript:history.go(-1)">Go back</a>.');
}

$user = validate_user();
validate_area_access($user, 'designer');

include_once('liveform.class.php');
$liveform = new liveform('migration');

// If the form was not just submitted, then show form.
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            ' . get_design_subnav() . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Migration</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Copy items from a source site to this site.</div>
            <form action="migration.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Source Site Login Info</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 20%">API URL:</td>
                        <td>
                            <div style="margin-bottom: .5em">' . $liveform->output_field(array('type'=>'text', 'name'=>'url', 'value' => 'https://', 'size'=>'60')) . '</div>
                            <div style="margin-bottom: .5em">Example: https://www.example.com/software_directory/api.php</div>
                            <div>If source site supports it, make sure to use "https" URL so login info is sent securely.</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Email:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'username', 'size'=>'30')) . '</td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Password:</td>
                        <td>' . $liveform->output_field(array('type'=>'password', 'name'=>'password', 'size'=>'30')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Items to Migrate</h2></th>
                    </tr>
                    <tr>
                        <td><label for="styles">Page Styles:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'styles', 'name'=>'styles', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr>
                        <td><label for="common_regions">Common Regions:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'common_regions', 'name'=>'common_regions', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr>
                        <td><label for="designer_regions">Designer Regions:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'designer_regions', 'name'=>'designer_regions', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr>
                        <td><label for="folders_and_pages">Folders &amp; Pages:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'folders_and_pages', 'name'=>'folders_and_pages', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_migrate" value="Migrate" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    $liveform->remove_form();

// Otherwise the form was just submitted, so process form.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('url', 'API URL is required.');
    $liveform->validate_required_field('username', 'Email is required.');
    $liveform->validate_required_field('password', 'Password is required.');

    // If no items have been selected to be migrated, then add error.
    if (
        ($liveform->get_field_value('styles') == 0)
        && ($liveform->get_field_value('common_regions') == 0)
        && ($liveform->get_field_value('designer_regions') == 0)
        && ($liveform->get_field_value('folders_and_pages') == 0)
    ) {
        $liveform->mark_error('', 'Please select at least one type of item to migrate.');
    }

    // If there is not already an error, then send test API request
    // in order to determine if URL and login info works.
    if ($liveform->check_form_errors() == false) {    
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'test')));

        // If the response is blank, then there was a communication error,
        // so output error about that.
        if ($response['status'] == '') {
            $liveform->mark_error('url', 'Sorry, there was a communication error with the source site. Please check to make sure the API URL is correct.');
        } else if ($response['status'] == 'error') {
            $liveform->mark_error('username', h($response['message']));
            $liveform->mark_error('password', '');
        }
    }

    // If there is not already an error and styles were selected,
    // then try to get them from source site.
    if (
        ($liveform->check_form_errors() == false)
        && ($liveform->get_field_value('styles') == 1)
    ) {
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'get_styles',
                'code' => true)));

        // If the request was successful, then store styles for later.
        if ($response['status'] == 'success') {
            $styles = array();

            if (is_array($response['styles']) == true) {
                $styles = $response['styles'];
            }

        // Otherwise the request failed, so add error.
        } else {
            $output_error_message = '';

            if ($response['message'] != '') {
                $output_error_message = ' Error: ' . h($response['message']);
            }

            $liveform->mark_error('styles', 'Page Styles could not be retrieved from the source site, so the migration was aborted.' . $output_error_message);
        }
    }

    // If there is not already an error and common regions were selected,
    // then try to get them from source site.
    if (
        ($liveform->check_form_errors() == false)
        && ($liveform->get_field_value('common_regions') == 1)
    ) {
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'get_common_regions')));

        // If the request was successful, then store common regions for later.
        if ($response['status'] == 'success') {
            $common_regions = array();

            if (is_array($response['common_regions']) == true) {
                $common_regions = $response['common_regions'];
            }

        // Otherwise the request failed, so add error.
        } else {
            $output_error_message = '';

            if ($response['message'] != '') {
                $output_error_message = ' Error: ' . h($response['message']);
            }

            $liveform->mark_error('common_regions', 'Common Regions could not be retrieved from the source site, so the migration was aborted.' . $output_error_message);
        }
    }

    // If there is not already an error and designer regions were selected,
    // then try to get them from source site.
    if (
        ($liveform->check_form_errors() == false)
        && ($liveform->get_field_value('designer_regions') == 1)
    ) {
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'get_designer_regions',
                'content' => true)));

        // If the request was successful, then store designer regions for later.
        if ($response['status'] == 'success') {
            $designer_regions = array();

            if (is_array($response['designer_regions']) == true) {
                $designer_regions = $response['designer_regions'];
            }

        // Otherwise the request failed, so add error.
        } else {
            $output_error_message = '';

            if ($response['message'] != '') {
                $output_error_message = ' Error: ' . h($response['message']);
            }

            $liveform->mark_error('designer_regions', 'Designer Regions could not be retrieved from the source site, so the migration was aborted.' . $output_error_message);
        }
    }

    // If there is not already an error and folders & pages were selected,
    // then try to get folders from source site.
    if (
        ($liveform->check_form_errors() == false)
        && ($liveform->get_field_value('folders_and_pages') == 1)
    ) {
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'get_folders')));

        // If the request was successful, then store folders for later.
        if ($response['status'] == 'success') {
            $folders = array();

            if (is_array($response['folders']) == true) {
                $folders = $response['folders'];
            }

        // Otherwise the request failed, so add error.
        } else {
            $output_error_message = '';

            if ($response['message'] != '') {
                $output_error_message = ' Error: ' . h($response['message']);
            }

            $liveform->mark_error('folders_and_pages', 'Folders could not be retrieved from the source site, so the migration was aborted.' . $output_error_message);
        }
    }

    // If there is not already an error and folders & pages were selected,
    // then try to get pages from source site.
    if (
        ($liveform->check_form_errors() == false)
        && ($liveform->get_field_value('folders_and_pages') == 1)
    ) {
        $response = send_api_request(array(
            'url' => $liveform->get_field_value('url'),
            'request' => array(
                'username' => $liveform->get_field_value('username'),
                'password' => $liveform->get_field_value('password'),
                'action' => 'get_pages')));

        // If the request was successful, then store pages for later.
        if ($response['status'] == 'success') {
            $pages = array();

            if (is_array($response['pages']) == true) {
                $pages = $response['pages'];
            }

        // Otherwise the request failed, so add error.
        } else {
            $output_error_message = '';

            if ($response['message'] != '') {
                $output_error_message = ' Error: ' . h($response['message']);
            }

            $liveform->mark_error('folders_and_pages', 'Pages could not be retrieved from the source site, so the migration was aborted.' . $output_error_message);
        }
    }

    // If there is an error, forward user back to previous screen.
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/migration.php');
        exit();
    }

    $message_detail = '';

    // Create array that will be responsible for mainting a relationship
    // between old ids (at the source site) and new ids (at this site) for styles.
    // The key will be the old id and the value will be the new id.
    // This allows us to assign migrated folders and pages to the correct new style ids.
    $style_ids = array();

    // If styles were selected to be migrated and there is at least one style, then create them.
    if (
        ($liveform->get_field_value('styles') == 1)
        && (count($styles) > 0)
    ) {
        // Loop through the styles in order to add them.
        foreach ($styles as $style) {
            $original_style_name = $style['name'];

            $style['name'] = get_unique_name(array(
                'name' => $style['name'],
                'type' => 'style'));

            // If style name was updated in order to be unique,
            // and this is a system style, then update style name in body class.
            if (
                ($style['name'] != $original_style_name)
                && ($style['type'] == 'system')
            ) {
                $style['code'] = str_replace(get_class_name($original_style_name), get_class_name($style['name']), $style['code']);
            }

            // Add style to database.
            db(
                "INSERT INTO style (
                    style_name,
                    style_type,
                    style_layout,
                    style_empty_cell_width_percentage,
                    style_code,
                    style_head,
                    social_networking_position,
                    additional_body_classes,
                    collection,
                    layout_type,
                    style_user,
                    style_timestamp)
                VALUES (
                    '" . escape($style['name']) . "',
                    '" . escape($style['type']) . "',
                    '" . escape($style['layout']) . "',
                    '" . escape($style['empty_cell_width_percentage']) . "',
                    '" . escape($style['code']) . "',
                    '" . escape($style['head']) . "',
                    '" . escape($style['social_networking_position']) . "',
                    '" . escape($style['additional_body_classes']) . "',
                    '" . escape($style['collection']) . "',
                    '" . e($style['layout_type']) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $new_style_id = mysqli_insert_id(db::$con);
            
            // If the style is a system style, then add cells to database.
            if ($style['type'] == 'system') {
                foreach ($style['cells'] as $cell) {
                    db(
                        "INSERT INTO system_style_cells (
                            style_id,
                            area,
                            `row`, # Backticks for reserved word.
                            col,
                            region_type,
                            region_name)
                        VALUES (
                            '$new_style_id',
                            '" . escape($cell['area']) . "',
                            '" . escape($cell['row']) . "',
                            '" . escape($cell['col']) . "',
                            '" . escape($cell['region_type']) . "',
                            '" . escape($cell['region_name']) . "')");
                }
            }

            // Update array so that we can remember the old and new ids for this style,
            // so that we can set the style correctly for migrated folders and pages below.
            $style_ids[$style['id']] = $new_style_id;
        }

        $number_of_styles = count($styles);

        if ($number_of_styles > 1) {
            $plural_suffix = 's';
            $verb = 'were';

        } else {
            $plural_suffix = '';
            $verb = 'was';
        }

        if ($message_detail != '') {
            $message_detail .= ', ';
        }

        $message_detail .= number_format($number_of_styles) . ' Page Style' . $plural_suffix;
    }

    // If common regions were selected to be migrated and there is at least one common regions, then create them.
    if (
        ($liveform->get_field_value('common_regions') == 1)
        && (count($common_regions) > 0)
    ) {
        // Loop through the common regions in order to add them.
        foreach ($common_regions as $common_region) {
            $common_region['name'] = get_unique_name(array(
                'name' => $common_region['name'],
                'type' => 'common_region'));

            db(
                "INSERT INTO cregion (
                    cregion_name,
                    cregion_content,
                    cregion_designer_type,
                    cregion_user,
                    cregion_timestamp)
                VALUES (
                    '" . escape($common_region['name']) . "',
                    '" . escape($common_region['content']) . "',
                    'no',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");
        }

        $number_of_common_regions = count($common_regions);

        if ($number_of_common_regions > 1) {
            $plural_suffix = 's';
            $verb = 'were';

        } else {
            $plural_suffix = '';
            $verb = 'was';
        }

        if ($message_detail != '') {
            $message_detail .= ', ';
        }

        $message_detail .= number_format($number_of_common_regions) . ' Common Region' . $plural_suffix;
    }

    // If designer regions were selected to be migrated and there is at least one designer regions, then create them.
    if (
        ($liveform->get_field_value('designer_regions') == 1)
        && (count($designer_regions) > 0)
    ) {
        // Loop through the designer regions in order to add them.
        foreach ($designer_regions as $designer_region) {
            $designer_region['name'] = get_unique_name(array(
                'name' => $designer_region['name'],
                'type' => 'designer_region'));

            db(
                "INSERT INTO cregion (
                    cregion_name,
                    cregion_content,
                    cregion_designer_type,
                    cregion_user,
                    cregion_timestamp)
                VALUES (
                    '" . escape($designer_region['name']) . "',
                    '" . escape($designer_region['content']) . "',
                    'yes',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");
        }

        $number_of_designer_regions = count($designer_regions);

        if ($number_of_designer_regions > 1) {
            $plural_suffix = 's';
            $verb = 'were';

        } else {
            $plural_suffix = '';
            $verb = 'was';
        }

        if ($message_detail != '') {
            $message_detail .= ', ';
        }

        $message_detail .= number_format($number_of_designer_regions) . ' Designer Region' . $plural_suffix;
    }

    // If folders & pages were selected to be migrated and there is at least one folder, then create them.
    if (
        ($liveform->get_field_value('folders_and_pages') == 1)
        && (count($folders) > 0)
    ) {
        // Prepare name for migration folder where we will put all imported folders.
        // If a "Migration" folder already exists, then we will use "Migration[1]" or etc.
        $migration_folder_name = get_unique_name(array(
            'name' => 'Migration',
            'type' => 'folder'));

        $root_folder_id = db_value("SELECT folder_id FROM folder WHERE folder_parent = '0'");

        // Create a parent migration folder in order to have a place to put all imported folders.
        db(
            "INSERT INTO folder (
                folder_name,
                folder_parent,
                folder_level,
                folder_order,
                folder_user,
                folder_timestamp)
            VALUES (
                '$migration_folder_name',
                '$root_folder_id',
                '1',
                '0',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        $migration_folder_id = mysqli_insert_id(db::$con);

        // Create array that will be responsible for mainting a relationship
        // between old ids (at the source site) and new ids (at this site).
        // The key will be the old id and the value will be the new id.
        // This allows us to assign child folders to the new correct id for
        // their parent folders.
        $folder_ids = array();

        // Loop through the folders in order to add them.
        foreach ($folders as $folder) {
            // If this folder is a root folder, then set the parent folder id
            // to the migration folder that we created.
            if ($folder['parent_folder_id'] == 0) {
                $folder['parent_folder_id'] = $migration_folder_id;

            // Otherwise this folder is not a root folder, so figure out new parent folder id,
            // because its parent has already been created in this site and it has a new id.
            } else {
                $folder['parent_folder_id'] = $folder_ids[$folder['parent_folder_id']];
            }

            // Update the level (1 more than parent), because it might now be different in this site.
            $parent_folder_level = db_value("SELECT folder_level FROM folder WHERE folder_id = '" . escape($folder['parent_folder_id']) . "'");
            $folder['level'] = $parent_folder_level + 1;

            // Add folder to database.
            db(
                "INSERT INTO folder (
                    folder_name,
                    folder_parent,
                    folder_level,
                    folder_style,
                    mobile_style_id,
                    folder_order,
                    folder_access_control_type,
                    folder_archived,
                    folder_user,
                    folder_timestamp)
                VALUES (
                    '" . escape($folder['name']) . "',
                    '" . escape($folder['parent_folder_id']) . "',
                    '" . escape($folder['level']) . "',
                    '" . escape($style_ids[$folder['style_id']]) . "',
                    '" . escape($style_ids[$folder['mobile_style_id']]) . "',
                    '" . escape($folder['sort_order']) . "',
                    '" . escape($folder['access_control_type']) . "',
                    '" . escape($folder['archived']) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $new_folder_id = mysqli_insert_id(db::$con);
            
            // Update array so that we can remember the old and new ids for this folder.
            $folder_ids[$folder['id']] = $new_folder_id;
        }

        $number_of_folders = count($folders);

        if ($number_of_folders > 1) {
            $plural_suffix = 's';
            $verb = 'were';

        } else {
            $plural_suffix = '';
            $verb = 'was';
        }

        if ($message_detail != '') {
            $message_detail .= ', ';
        }

        $message_detail .= number_format($number_of_folders) . ' Folder' . $plural_suffix;

        // Create array that will be responsible for mainting a relationship
        // between old ids (at the source site) and new ids (at this site) for pages.
        // The key will be the old id and the value will be the new id.
        // This allows us to set page type properties and etc. to the new page ids.
        $page_ids = array();

        // Loop through the pages in order to add them.
        foreach ($pages as $page) {
            // Get a new unique page name if page name is already in use.
            $page['name'] = get_unique_name(array(
                'name' => $page['name'],
                'type' => 'page'));

            // Add page to database.  We disable home pages that are migrated.
            db(
                "INSERT INTO page (
                    page_name,
                    page_folder,
                    page_style,
                    mobile_style_id,
                    page_home,
                    page_title,
                    page_search,
                    page_search_keywords,
                    page_meta_description,
                    page_meta_keywords,
                    page_type,
                    layout_type,
                    layout_modified,
                    comments,
                    comments_label,
                    comments_message,
                    comments_allow_new_comments,
                    comments_disallow_new_comment_message,
                    comments_automatic_publish,
                    comments_allow_user_to_select_name,
                    comments_require_login_to_comment,
                    comments_allow_file_attachments,
                    comments_show_submitted_date_and_time,
                    comments_administrator_email_to_email_address,
                    comments_administrator_email_subject,
                    comments_administrator_email_conditional_administrators,
                    comments_submitter_email_subject,
                    comments_watcher_email_subject,
                    comments_watchers_managed_by_submitter,
                    seo_score,
                    seo_analysis,
                    seo_analysis_current,
                    sitemap,
                    system_region_header,
                    system_region_footer,
                    page_user,
                    page_timestamp)
                VALUES (
                    '" . escape($page['name']) . "',
                    '" . escape($folder_ids[$page['folder_id']]) . "',
                    '" . escape($style_ids[$page['style_id']]) . "',
                    '" . escape($style_ids[$page['mobile_style_id']]) . "',
                    'no',
                    '" . escape($page['title']) . "',
                    '" . escape($page['search']) . "',
                    '" . escape($page['search_keywords']) . "',
                    '" . escape($page['meta_description']) . "',
                    '" . escape($page['meta_keywords']) . "',
                    '" . escape($page['type']) . "',
                    '" . e($page['layout_type']) . "',
                    '" . e($page['layout_modified']) . "',
                    '" . escape($page['comments']) . "',
                    '" . e($page['comments_label']) . "',
                    '" . e($page['comments_message']) . "',
                    '" . escape($page['comments_allow_new_comments']) . "',
                    '" . escape($page['comments_disallow_new_comment_message']) . "',
                    '" . escape($page['comments_automatic_publish']) . "',
                    '" . escape($page['comments_allow_user_to_select_name']) . "',
                    '" . escape($page['comments_require_login_to_comment']) . "',
                    '" . escape($page['comments_allow_file_attachments']) . "',
                    '" . escape($page['comments_show_submitted_date_and_time']) . "',
                    '" . escape($page['comments_administrator_email_to_email_address']) . "',
                    '" . escape($page['comments_administrator_email_subject']) . "',
                    '" . escape($page['comments_administrator_email_conditional_administrators']) . "',
                    '" . escape($page['comments_submitter_email_subject']) . "',
                    '" . escape($page['comments_watcher_email_subject']) . "',
                    '" . escape($page['comments_watchers_managed_by_submitter']) . "',
                    '" . escape($page['seo_score']) . "',
                    '" . escape($page['seo_analysis']) . "',
                    '" . escape($page['seo_analysis_current']) . "',
                    '" . escape($page['sitemap']) . "',
                    '" . escape($page['system_region_header']) . "',
                    '" . escape($page['system_region_footer']) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $new_page_id = mysqli_insert_id(db::$con);
            
            // Loop through page regions in order to add them to database.
            foreach ($page['page_regions'] as $page_region) {
                db(
                    "INSERT INTO pregion (
                        pregion_name,
                        pregion_content,
                        pregion_page,
                        pregion_order,
                        collection,
                        pregion_user,
                        pregion_timestamp)
                    VALUES (
                        '" . escape($page_region['name']) . "',
                        '" . escape($page_region['content']) . "',
                        '$new_page_id',
                        '" . escape($page_region['sort_order']) . "',
                        '" . escape($page_region['collection']) . "',
                        '" . USER_ID . "',
                        UNIX_TIMESTAMP())");
            }

            // Update array so that we can remember the old and new ids for this page.
            $page_ids[$page['id']] = $new_page_id;
        }

        // Loop through the pages again in order to add page type properties to database.
        // We do this in a separate loop so that we have a chance to add all of the pages first
        // and know their new ids so we can update the various page ids for page type property fields.
        // We have not actually added support for that yet but plan to in the future.
        foreach ($pages as $page) {
            // If this page's type has page type properties, then add them to database.
            if (
                (check_for_page_type_properties($page['type']) == true)
                && (is_array($page['page_type_properties']) == true)
            ) {
                $page_type_properties = $page['page_type_properties'];

                // Remove id because we don't want to use that when we create the database record.
                unset($page_type_properties['id']);

                // Update the page id for the properties to be the new page id.
                $page_type_properties['page_id'] = $page_ids[$page['id']];

                create_or_update_page_type_record($page['type'], $page_type_properties);
            }
        }

        $number_of_pages = count($pages);

        if ($number_of_pages > 1) {
            $plural_suffix = 's';
            $verb = 'were';

        } else {
            $plural_suffix = '';
            $verb = 'was';
        }

        if ($message_detail != '') {
            $message_detail .= ', ';
        }

        $message_detail .= number_format($number_of_pages) . ' Page' . $plural_suffix;
    }

    log_activity('migration was completed (' . $message_detail . ')', USER_USERNAME);

    $liveform->add_notice('The items that you requested have been migrated (' . $message_detail . ').');

    // Forward user back to migration screen.
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/migration.php');
}

// Create function that will be responsible for using cURL to send API request.
// Properties:
// url: The API URL
// request: An array of values that this function will convert to JSON.
function send_api_request($properties)
{
    $url = $properties['url'];
    $request = $properties['request'];

    $data = encode_json($request);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)));

    // if there is a proxy address, then send cURL request through proxy
    if (PROXY_ADDRESS != '') {
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
    }

    $response = curl_exec($ch);

    return decode_json($response);
}
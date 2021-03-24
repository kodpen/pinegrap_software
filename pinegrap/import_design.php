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

// This script can be run from the command line, in order to avoid client & server timeout issues
// for large imports.
// First param is URL.  Second param is tag.  Third param is follow depth.  All are required.
// Example: /usr/bin/php /path/to/livesite/import_design.php http://example.com tag 10
// Example with no tag: /usr/bin/php /path/to/livesite/import_design.php http://example.com '' 10

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

include('init.php');
require('url_to_absolute.php');

// If the script was run from the command line, then remember that.
if (!isset($_SERVER['HTTP_HOST'])) {

    $command_line = true;

} else {

    $user = validate_user();

    validate_area_access($user, 'designer');
}

include_once('liveform.class.php');
$liveform = new liveform('import_design');

$action = '';

if ($command_line) {

    $action = 'import';

    $url = $argv[1];
    $import_name = $argv[2];
    $max_link_level = $argv[3];

    $allowed_hostname = str_replace('www.', '', mb_strtolower(parse_url($url, PHP_URL_HOST)));

    // Create an array that will keep track of which URLs have already been scanned.
    $scanned_urls = array();

    $scanned_items = array();

    if (is_numeric($max_link_level) == false) {
        $max_link_level = 0;
    }

    $max_imported_items = 999999;

    $max_scan_level = 20;
    $number_of_items = 0;

    $result = scan(array('url' => $url));

    $_SESSION['software']['design']['import_design']['scanned_items'] = $scanned_items;
}

if ($_REQUEST['submit_button']) {
    $liveform->add_fields_to_session();
    $action = mb_strtolower($liveform->get_field_value('submit_button'));
}

if ($action and !$command_line) {

    $liveform->validate_required_field('url', 'URL is required.');
    $liveform->validate_required_field('max_link_level', 'Follow Depth is required.');

    $url = $liveform->get_field_value('url');
    $import_name = $liveform->get_field_value('import_name');

    // If the URL does not start with "http://" or "https://" then add
    // http:// to the front of it, in order to avoid an error.
    if ((mb_substr($url, 0, 7) != 'http://') && (mb_substr($url, 0, 8) != 'https://')) {
        $url = 'http://' . $url;
    }

    // If the action is scan and there is not already an error for the URL field,
    // then check if that URL is online.
    if (
        ($action == 'scan')
        && ($liveform->check_field_error('url') == false)
    ) {
        if (check_if_url_exists($url) == false) {
            $liveform->mark_error('url', 'Sorry, we could not find a web page to start scanning at that URL.  Please try a different URL.');
        }
    }

    if ($liveform->check_form_errors() == true) {
        go(PATH . SOFTWARE_DIRECTORY . '/import_design.php');
    }

    $allowed_hostname = str_replace('www.', '', mb_strtolower(parse_url($url, PHP_URL_HOST)));
}

// If the user did not click the import button yet, then show intro or preview screen.
if ($action != 'import') {
    switch ($action) {
        case '':
            $output_method = 'get';

            $output_fields =
                '<table class="field" style="margin-bottom: 3em !important">
                    <tr>
                        <td>URL:</td>
                        <td>' . $liveform->output_field(array('type' => 'text', 'name' => 'url', 'placeholder' => 'http://www.mysite.com', 'size' => '60')) . '</td>
                    </tr>
                    <tr>
                        <td>Tag:</td>
                        <td>' . $liveform->output_field(array('type' => 'text', 'name' => 'import_name', 'size' => '10')) . ' &nbsp;(appended to imported items and filenames for easy reference, recommended, e.g. "mysite")</td>
                    </tr>
                    <tr>
                        <td>Follow Depth:</td>
                        <td>' . $liveform->output_field(array('type' => 'text', 'name' => 'max_link_level', 'value' => '1', 'size' => '2', 'maxlength' => '2')) . ' &nbsp;(0 = first page only, 1 = first page and pages linked from first page, and so on)</td>
                    </tr>
                </table>';

            $output_buttons = '<input type="submit" name="submit_button" value="Scan" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'view_styles.php\'" class="submit-secondary">';

            break;

        case 'scan':
            $output_method = 'post';

            $output_fields =
                get_token_field() . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'url')) . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'import_name')) . '
                ' . $liveform->output_field(array('type' => 'hidden', 'name' => 'max_link_level'));

            break;
    }

    echo
        output_header() . '
        <div id="subnav">
            ' . get_design_subnav() . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Import My Site</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Import web pages and files from your existing website.<br /></div>
            <form action="' . h($_SERVER['PHP_SELF']) . '" method="' . $output_method .'">
                ' . $output_fields;

    if ($action == 'scan') {
        // Force the web server to send output after every echo call
        // so the user can see the scan progress.
        ob_end_flush();

        echo
            '<h2>Scan Results</h2>
            <table class="import" style="margin-bottom: 3em !important"><thead><tr><th>&nbsp;</th><th class="folders">Folder</th><th class="pages">Page</th><th class="files">File</th><th class="design">Page Style</th><th class="design">Design File</th></tr></thead>';

        // set folder name to URL
        $folder_name = $url;
        $folder_name = str_replace('https://', '', $folder_name);
        $folder_name = str_replace('http://', '', $folder_name);

        // Add timestamp to folder name
        $folder_name .= ' ' . get_absolute_time(array('timestamp' => time(), 'format' => 'plain_text', 'timezone_type' => 'site'));

        // If the user supplied an import name, then add it to the folder name
        if ($import_name != '') {
            $folder_name .= '-' . $import_name;
        }

        $folder_name = get_unique_name(array(
            'name' => $folder_name,
            'type' => 'folder'));

        //echo '<div>Folder: Import My Site</div>';
        //echo '<div>Folder: ' . h($folder_name) . '</div>';

        echo '<tr><td>&nbsp;</td><td>Import My Site</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        echo '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . h($folder_name) . '</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        flush();

        // Create an array that will keep track of which URLs have already been scanned.
        $scanned_urls = array();

        $scanned_items = array();

        $max_link_level = $liveform->get_field_value('max_link_level');

        if (is_numeric($max_link_level) == false) {
            $max_link_level = 0;
        }

        $max_imported_items = 2500;

        $max_scan_level = 20;
        $number_of_items = 0;

        $result = scan(array('url' => $url));

        // Store the scanned items in the session, so that we can import them
        // if the user decides to import.
        $_SESSION['software']['design']['import_design']['scanned_items'] = $scanned_items;

        echo '<tr><td colspan="6">';

        if ($result === false) {
            echo '<div class="software_error" style="padding: 1em; margin: 1em;"><strong>We\'re sorry, but the scan could not be completed successfully at this time.</strong> Please try again later.</div>';

            $output_buttons = '<input type="button" name="restart" value="Restart" onclick="window.location.href=\'import_design.php\'" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'view_styles.php\'" class="submit-secondary">';
        } else {
            $output_max_imported_items_warning = '';

            if ($number_of_items >= $max_imported_items) {
                $output_max_imported_items_warning = '<br><strong>Warning: The scan limit of ' . number_format($max_imported_items) . ' items has been reached.</strong>';
            }

            echo '<div class="software_notice" style="padding: 1em; margin: 1em;"><strong>Scan completed successfully.</strong> Click \'Import\' to transfer and create the items within your site. Press \'Restart\' to adjust the settings and scan again.' . $output_max_imported_items_warning . '</div>';

            $output_buttons = '<input type="submit" name="submit_button" value="Import" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="restart" value="Restart" onclick="window.location.href=\'import_design.php\'" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'view_styles.php\'" class="submit-secondary">';
        }
    }

    echo '</td></tr></table>';

    echo
        '        <div class="buttons">' . $output_buttons . '</div>
            </form>
        </div>' .
        output_footer();

    $liveform->unmark_errors();
    $liveform->clear_notices();

// Otherwise, the user clicked import, so import items.
} else {

    if (!$command_line) {
        validate_token_field();
    }

    $scanned_items = $_SESSION['software']['design']['import_design']['scanned_items'];

    unset($_SESSION['software']['design']['import_design']['scanned_items']);

    // If there are no scanned items to import then add error
    // and forward user back to original screen.
    if (!$scanned_items) {
        $message = 'Sorry, we could not find any items to import.  Please try scanning again.';

        if ($command_line) {
            exit($message);
        } else {
            $liveform->mark_error('', $message);
            go(PATH . SOFTWARE_DIRECTORY . '/import_design.php');
        }
    }

    // Try to find an existing "Import My Site" folder.
    $import_my_site_folder_id = db_value("SELECT folder_id FROM folder WHERE folder_name = 'Import My Site'");

    // If an "Import My Site" folder was not found, then create it.
    if (!$import_my_site_folder_id) {
        $root_folder_id = db_value("SELECT folder_id FROM folder WHERE folder_parent = '0'");

        db(
            "INSERT INTO folder (
                folder_name,
                folder_parent,
                folder_level,
                folder_order,
                folder_user,
                folder_timestamp)
            VALUES (
                'Import My Site',
                '$root_folder_id',
                '1',
                '0',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        $import_my_site_folder_id = mysqli_insert_id(db::$con);
    }

    $import_my_site_folder_level = db_value("SELECT folder_level FROM folder WHERE folder_id = '$import_my_site_folder_id'");

    $folder_level = $import_my_site_folder_level + 1;

    // set folder name to URL
    $folder_name = $url;
    $folder_name = str_replace('https://', '', $folder_name);
    $folder_name = str_replace('http://', '', $folder_name);

    // Add timestamp to folder name
    $folder_name .= ' ' . get_absolute_time(array('timestamp' => time(), 'format' => 'plain_text', 'timezone_type' => 'site'));

    // If the user supplied an import name, then add it to the folder name
    if ($import_name != '') {
        $folder_name .= '-' . $import_name;
    }

    $folder_name = get_unique_name(array(
        'name' => $folder_name,
        'type' => 'folder'));
    
    // Create folder for this import.
    db(
        "INSERT INTO folder (
            folder_name,
            folder_parent,
            folder_level,
            folder_order,
            folder_user,
            folder_timestamp)
        VALUES (
            '" . escape($folder_name) . "',
            '$import_my_site_folder_id',
            '$folder_level',
            '0',
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");

    $folder_id = mysqli_insert_id(db::$con);

    // Create an array of imported items that we will use
    // to keep track of which scanned items were actually created.
    $imported_items = array();

    // Loop through the scanned items in order to create them.
    foreach ($scanned_items as $item) {

        $url = $item['url'];
        $base_url = $item['base_url'];
        $type = $item['type'];
        $name = $item['name'];
        $extension = $item['extension'];
        $design = $item['design'];

        $content = @file_get_contents($url);

        // If the download failed, then skip to the next item.
        if ($content === false) {
            continue;
        }

        if ($item['type'] == 'html') {

            // If this is a liveSite page that we are scanning, then remove
            // dynamic liveSite code that we don't want to import or scan.
            // This avoids the frontend JS, jquery files, and etc. from being imported.
            // We don't want to import them because they are dynamically outputted.
            $content = preg_replace('/' . preg_quote('<!-- Start liveSite dynamic code -->') . '.*?' . preg_quote('<!-- End liveSite dynamic code -->') . '/s', '', $content);
            
            // If there is a base tag in the content, then get base URL from the tag.
            // We will need this in order to prepare URLs for images and etc.
            // Also we remove the base tag from the content, because we are going to change links.
            if (preg_match('/<\s*base\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $match)) {
                $base_url = trim($match[1]);

                // Convert the base url to an absolute URL, in case it is relative.
                $base_url = url_to_absolute($url, $base_url);

                $base_hostname = str_replace('www.', '', mb_strtolower(parse_url($base_url, PHP_URL_HOST)));

                // If the base hostname is the allowed hostname,
                // then remove base tag because we don't want to use that in this system.
                // We leave the base tag if the hostname is an external site,
                // because we are not going to change/crawl URLs for items in that case,
                // and we want the references to those items to still work after
                // this HTML is imported into this system.
                if ($base_hostname == $allowed_hostname) {
                    $content = preg_replace('/<\s*base\s+[^>]*href\s*=\s*["\'].*?["\'].*?>/is', '', $content);
                }
            }

            // Remove all meta charset tags from the HTML,
            // because we are going to add our own utf-8 charset tag,
            // so it is compatible with this system.
            $content = preg_replace('/<meta\s+[^>]*charset.*?>/i', '', $content);

            // Get the title for the page so we can update the page property later.
            preg_match('/<title\s*>(.*?)<\/title>/i', $content, $matches);

            $title = '';

            // If a title tag was found, then remember title and remove title tags.
            if ($matches[0] != '') {
                $title = unhtmlspecialchars(trim($matches[1]));

                // Remove all title tags from the HTML.
                $content = preg_replace('/<title>.*?<\/title>/i', '', $content);
            }

            // Get the meta description for the page so we can update the page property later.
            preg_match('/<meta\s+[^>]*name\s*=\s*["\']\s*description\s*["\'].*?>/i', $content, $matches);

            $meta_description = '';

            // If a meta description was found, then remember it and remove meta description tags.
            if ($matches[0] != '') {
                preg_match('/content\s*=\s*["\'](.*?)["\']/i', $matches[0], $matches);

                $meta_description = unhtmlspecialchars(trim($matches[1]));

                // Remove all meta description tags from the HTML.
                $content = preg_replace('/<meta\s+[^>]*name\s*=\s*["\']\s*description\s*["\'].*?>/i', '', $content);
            }

            // Get the meta keywords for the page so we can update the page property later.
            preg_match('/<meta\s+[^>]*name\s*=\s*["\']\s*keywords\s*["\'].*?>/i', $content, $matches);

            $meta_keywords = '';

            // If a meta keywords was found, then remember it and remove meta keywords tags.
            if ($matches[0] != '') {
                preg_match('/content\s*=\s*["\'](.*?)["\']/i', $matches[0], $matches);

                $meta_keywords = unhtmlspecialchars(trim($matches[1]));

                // Remove all meta keywords tags from the HTML.
                $content = preg_replace('/<meta\s+[^>]*name\s*=\s*["\']\s*keywords\s*["\'].*?>/i', '', $content);
            }

            // Now that we have removed all necessary title and meta tags,
            // let's add our own head content.
            $head_content =
                '    <meta charset="utf-8">' . "\n" .
                '    <title></title>' . "\n" .
                '    <meta_tags></meta_tags>';

            // If there is a head tag, then add the head content after that.
            if (preg_match('/<\bhead\b.*?>/i', $content) != 0) {
                $content = preg_replace('/(<\bhead\b.*?>)/i', '$1' . "\n" . $head_content, $content);
                
            // Otherwise, if there is an html tag, then add the head content after that.
            } else if (preg_match('/<html.*?>/i', $content) != 0) {
                $content = preg_replace('/(<html.*?>)/i', '$1' . "\n" . $head_content, $content);

            // Otherwise, if there is a doctype, then add the head content after that.
            } else if (preg_match('/<!doctype html.*?>/i', $content) != 0) {
                $content = preg_replace('/(<!doctype html.*?>)/i', '$1' . "\n" . $head_content, $content);
            }

            $page_name = get_unique_name(array(
                'name' => $name,
                'type' => 'page'));

            $name = $page_name;

            $style_name = get_unique_name(array(
                'name' => $name,
                'type' => 'style'));

            // Create style.
            db(
                "INSERT INTO style (
                    style_name,
                    style_type,
                    style_code,
                    style_user,
                    style_timestamp)
                VALUES (
                    '" . escape($style_name) . "',
                    'custom',
                    '" . escape($content) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $style_id = mysqli_insert_id(db::$con);

            // Create the page.
            db(
                "INSERT INTO page (
                    page_name,
                    page_folder,
                    page_style,
                    page_title,
                    page_meta_description,
                    page_meta_keywords,
                    page_user,
                    page_timestamp,
                    comments_disallow_new_comment_message)
                VALUES (
                    '" . escape($page_name) . "',
                    '$folder_id',
                    '$style_id',
                    '" . escape($title) . "',
                    '" . escape($meta_description) . "',
                    '" . escape($meta_keywords) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP(),
                    'We\'re sorry. New comments are no longer being accepted.')");

            $page_id = mysqli_insert_id(db::$con);

            $imported_items[$url] = array(
                'url' => $url,
                'base_url' => $base_url,
                'name' => $name,
                'type' => $type,
                'page_id' => $page_id,
                'style_id' => $style_id);

            if ($command_line) {
                echo 'Imported Item:' . "\n";
                print_r($imported_items[$url]);
                echo 'Imported Item Count: ' . count($imported_items) . "\n";

                // Add a 1 second delay in order to avoid firewalls/Cloudflare/WordFence detecting
                // and blocking this import.
                sleep(1);
            }

        // Otherwise this is a regular file, so create file.
        } else {

            $old_file_backed_up = false;

            $sql_folder_id = $folder_id;
            $sql_description = '';

            if ($design == true) {
                $sql_design = '1';
            } else {
                $sql_design = '0';
            }

            // If the user supplied a tag, then check to see if there is an existing
            // file with the name, because if there is, then we are going to backup
            // the old file and let this new file have the name.
            if ($import_name != '') {

                $old_file = db_item(
                    "SELECT
                        id,
                        folder AS folder_id,
                        description,
                        design
                    FROM files WHERE name = '" . e($name) . "'");

                // If an old file was found, then rename it.
                if ($old_file) {

                    // If there is an extension, then prepare backup name in a certain way.
                    if (mb_strpos($name, '.') !== false) {
                        $name_without_extension = mb_substr($name, 0, mb_strrpos($name, '.'));
                        $old_extension = mb_substr($name, mb_strrpos($name, '.') + 1);
                        $backup_name = $name_without_extension . '-backup.' . $old_extension;

                    // Otherwise there is not an extension, so prepare backup name differently.
                    } else {
                        $backup_name = $name . '-backup';
                    }

                    // Get a unique version of the backup name.
                    $backup_name = get_unique_name(array(
                        'name' => $backup_name,
                        'type' => 'file'));

                    // Update old file to have backup name in db.
                    db(
                        "UPDATE files 
                        SET 
                            name = '" . e($backup_name) . "',
                            user = '" . USER_ID . "',
                            timestamp = UNIX_TIMESTAMP()
                        WHERE id = '" . $old_file['id'] . "'");

                    // Update old file to have backup name on file system.
                    @rename(FILE_DIRECTORY_PATH . '/' . $name, FILE_DIRECTORY_PATH . '/' . $backup_name);

                    log_activity('file was renamed during import my site (' . $name . ' -> ' . $backup_name . ')');

                    $old_file_backed_up = true;

                    // Set the properties for the new file to the old file properties.
                    $sql_folder_id = $old_file['folder_id'];
                    $sql_description = $old_file['description'];
                    $sql_design = $old_file['design'];

                }

            }

            // If an old file was not backed up, then that means we need to a create
            // a new, unique name for this file.
            if (!$old_file_backed_up) {
                $name = get_unique_name(array(
                    'name' => $name,
                    'type' => 'file'));
            }

            @file_put_contents(FILE_DIRECTORY_PATH . '/' . $name, $content);

            db(
                "INSERT INTO files (
                    name,
                    folder,
                    description,
                    type,
                    size,
                    design,
                    user,
                    timestamp)
                VALUES (
                    '" . escape($name) . "',
                    '" . e($sql_folder_id) . "',
                    '" . e($sql_description) . "',
                    '" . escape($extension) . "',
                    '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $name)) . "',
                    '" . e($sql_design) . "',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $file_id = mysqli_insert_id(db::$con);

            $imported_items[$url] = array(
                'url' => $url,
                'base_url' => $base_url,
                'name' => $name,
                'type' => $type,
                'file_id' => $file_id);

            if ($command_line) {
                echo 'Imported Item:' . "\n";
                print_r($imported_items[$url]);
                echo 'Imported Item Count: ' . count($imported_items) . "\n";

                // Add a 1 second delay in order to avoid firewalls/Cloudflare/WordFence detecting
                // and blocking this import.
                sleep(1);
            }
        }
    }

    require('update_imported_items.php');
    update_imported_items($imported_items);

    if ($command_line) {
        exit('Complete');
    }

    $liveform->remove_form();

    $liveform_view_pages = new liveform('view_pages');
    $liveform_view_pages->add_notice('All scanned items have been imported successfully.');

    go(PATH . SOFTWARE_DIRECTORY . '/view_pages.php?query=' . urlencode($import_name));
}

function scan($properties) {

    global $command_line;
    global $import_name;
    global $scanned_urls;
    global $scanned_items;
    global $allowed_hostname;
    global $max_scan_level;
    global $max_link_level;
    global $max_imported_items;
    global $number_of_items;

    $url = trim($properties['url']);
    $base_url = trim($properties['base_url']);
    $scan_level = $properties['scan_level'];
    $link_level = $properties['link_level'];

    if (is_numeric($scan_level) == false) {
        $scan_level = 0;
    }

    if (is_numeric($link_level) == false) {
        $link_level = 0;
    }

    if ($number_of_items >= $max_imported_items) {
        return false;
    }

    // If the URL does not start with "http://" or "https://" then return false.
    // We do this for security reasons, because we call file_get_contents()
    // below and we don't want someone to be able to crawl a local file.
    if ((mb_substr($url, 0, 7) != 'http://') && (mb_substr($url, 0, 8) != 'https://')) {
        return false;
    }

    $hostname = str_replace('www.', '', mb_strtolower(parse_url($url, PHP_URL_HOST)));

    if ($hostname != $allowed_hostname) {
        return false;
    }

    // Remove bookmark from the URL, if one exists,
    // because we do not care about that, and it might cause issues
    // later when we try to find things by the URL.
    $url = strtok($url, '#');

    // Get the URL parts in order to just get the path and query string
    // in order to determine if the url has already been crawled.
    $url_parts = parse_url($url);

    $scanned_url = $url_parts['path'];

    if ($url_parts['query'] != '') {
        $scanned_url .= '?' . $url_parts['query'];
    }

    // If this URL has already been crawled, then don't crawl it again.
    if ($scanned_urls[$scanned_url] == true) {
        return;
    }

    // Remember that this URL has now been crawled.
    $scanned_urls[$scanned_url] = true;

    // If this is not the first URL being crawled, and the URL is for the root of the site,
    // then don't crawl it.  We found that template sites often link back to the root
    // of their site and we don't normally want to crawl that root URL.
    if (
        ($scan_level > 0)
        &&
        (
            ($scanned_url == '')
            || ($scanned_url == '/')
        )
    ) {
        return false;
    }

    $content_type = '';

    $headers = check_if_url_exists($url);

    // If we cannot access the URL, then return false.
    if ($headers == false) {
        return false;
    }

    if (isset($headers['Content-Type']) == true) {
        // If the content type value is an array, then use the last value in the array.
        // get_headers() sets content type to an array when there is a redirection.
        if (is_array($headers['Content-Type']) == true) {
            $content_type = end($headers['Content-Type']);

        } else {
            $content_type = $headers['Content-Type'];
        }

        $charset_position = mb_strpos($content_type, ';');

        // If there is a charset on the end of the content type, then remove
        // the charset from the content type, so that when we check for an html,
        // css, or etc. content type further below, the check will work.
        if ($charset_position !== false) {
            $content_type = mb_substr($content_type, 0, $charset_position);
        }

        // Convert it to lowercase so when we check for certain
        // content types later the checks will work regardless of the case.
        $content_type = mb_strtolower($content_type);
    }

    $extension = pathinfo($url_parts['path'], PATHINFO_EXTENSION);
    $extension_lowercase = mb_strtolower($extension);

    if (
        ($content_type == 'text/html')
        || ($content_type == 'application/xhtml+xml')
        || ($extension_lowercase == 'html')
        || ($extension_lowercase == 'htm')
        || ($extension_lowercase == 'xhtml')
    ) {
        $type = 'html';

    } else if (
        ($content_type == 'text/css')
        || ($extension_lowercase == 'css')
    ) {
        $type = 'css';

    } else if (
        ($content_type == 'application/javascript')
        || ($content_type == 'application/x-javascript')
        || ($content_type == 'text/javascript')
        || ($extension_lowercase == 'js')
    ) {
        $type = 'js';

    } else {
        $type = 'general';
    }

    // If the type is HTML but the extension is for some other type of file,
    // then that means that something is probably wrong, so let's not import this URL.
    // This might happen if, for example, a CSS file references a PNG file,
    // but that PNG file does not exist and the server serves an HTML error page
    // without a correct 404 response code.
    if (
        ($type == 'html')
        &&
        (
            ($extension_lowercase == 'css')
            || ($extension_lowercase == 'js')
            || ($extension_lowercase == 'gif')
            || ($extension_lowercase == 'jpg')
            || ($extension_lowercase == 'jpeg')
            || ($extension_lowercase == 'png')
        )
    ) {
        return false;
    }

    // If this is an HTML, CSS, or JS item, then get the content of it,
    // because we will need to crawl it.
    if (
        ($type == 'html')
        || ($type == 'css')
        || ($type == 'js')
    ) {
        $content = @file_get_contents($url);

        if ($content === false) {
            return false;
        }
    }

    $number_of_items++;

    // Prepare name for item as it will appear in our system.

    $name = basename($url_parts['path']);

    $name = rawurldecode($name);

    // If this is an HTML item then remove extension if one exists,
    // because we don't want the page or style name to have an extension.
    // We preserve all content before the first period, so this means
    // that multiple extensions will be removed (e.g. example.min.html -> example)
    if ($type == 'html') {
        $name = strtok($name, '.');
        $extension = '';
    }

    if ($name == '') {
        $name = 'home';
    }

    if ($url_parts['query'] != '') {
        // If there is an extension in the name,
        // then place the query string before the extension.
        if ($extension != '') {
            $extension_position = mb_strpos($name, '.');

            $name = mb_substr($name, 0, $extension_position) . '-' . $url_parts['query'] . mb_substr($name, $extension_position);

        // Otherwise there is not an extension,
        // so just place the query string after the name.
        } else {
            $name .= '-' . $url_parts['query'];
        }
    }

    // If the user entered an import name, then add the import name to the item name.
    if ($import_name != '') {
        // If there is an extension in the name,
        // then place the import name before the extension.
        if ($extension != '') {
            $extension_position = mb_strpos($name, '.');

            $name = mb_substr($name, 0, $extension_position) . '-' . $import_name . mb_substr($name, $extension_position);

        // Otherwise there is not an extension,
        // so just place the import name after the name.
        } else {
            $name .= '-' . $import_name;
        }
    }

    $name = str_replace(' ', '-', $name);
    $name = str_replace('&', '-', $name);

    // If this is an HTML file then deal with that.
    if ($type == 'html') {

        // If this is a liveSite page that we are scanning, then remove
        // dynamic liveSite code that we don't want to import or scan.
        // This avoids the frontend JS, jquery files, and etc. from being imported.
        // We don't want to import them because they are dynamically outputted.
        $content = preg_replace('/' . preg_quote('<!-- Start liveSite dynamic code -->') . '.*?' . preg_quote('<!-- End liveSite dynamic code -->') . '/s', '', $content);

        $base_url = '';

        // If there is a base tag in the content, then get base URL from the tag.
        // We will need this in order to prepare URLs for images and etc.
        // Also we remove the base tag from the content, because we are going to change links.
        if (preg_match('/<\s*base\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $match)) {
            $base_url = trim($match[1]);

            // Convert the base url to an absolute URL, in case it is relative.
            $base_url = url_to_absolute($url, $base_url);
        }

        // If a URL from a base tag was not found,
        // then set base URL to the page URL.
        if ($base_url == '') {
            $base_url = $url;
        }

        // Store a new name, before we create a unique version of the name,
        // so we can store the new name in the array and won't have to generate
        // it again during import.
        $new_name = $name;

        $page_name = get_unique_name(array(
            'name' => $name,
            'type' => 'page'));

        $name = $page_name;

        $style_name = get_unique_name(array(
            'name' => $name,
            'type' => 'style'));

        echo '<tr><td class="items">'. $number_of_items . '</td><td>&nbsp;</td><td>' . h($page_name) . '</td><td>&nbsp;</td><td>' . h($style_name) . '</td><td>&nbsp;</td></tr>';
        flush();

        //echo '<div>Page:' . h($page_name) . '</div>';
        //echo '<div>Page Style:' . h($style_name) . '</div>';

        // Remember that we have now scanned this item.
        $scanned_items[$url] = array(
            'url' => $url,
            'base_url' => $base_url,
            'type' => $type,
            'name' => $new_name);

        if ($command_line) {
            echo 'Scanned Item:' . "\n";
            print_r($scanned_items[$url]);
            echo 'Scanned Item Count: ' . count($scanned_items) . "\n";

            // Add a 1 second delay in order to avoid firewalls/Cloudflare/WordFence detecting
            // and blocking this import.
            sleep(1);
        }

    // Otherwise this is some other type of file, so deal with that.
    } else {
        // If this is a CSS file, then set the base URL to the URL for the CSS file.
        if ($type == 'css') {
            $base_url = $url;

        // Otherwise if this is a JS file, then deal with base url differently.
        } else if ($type == 'js') {
            // Generally a base url is passed into this function from the parent HTML file
            // that included the js file, however if that did not happen,
            // then set the base url to the current URL of this JS file.
            if ($base_url == '') {
                $base_url = $url;
            }
        }

        $name = prepare_file_name($name);

        // Store a new name, before we create a unique version of the name,
        // so we can store the new name in the array and won't have to generate
        // it again during import.
        $new_name = $name;

        // If the user did not enter a tag, then get a unique name,
        // because for that situation we create a new unique name for files.
        if ($import_name == '') {
            $name = get_unique_name(array(
                'name' => $name,
                'type' => 'file'));
        }

        // If this is a design file then remember that for when we import the item,
        // and output the file name in the appropriate column.
        if (
            ($extension_lowercase == 'css')
            || ($extension_lowercase == 'js')
            || ($extension_lowercase == 'svg')
            || ($extension_lowercase == 'ttf')
            || ($extension_lowercase == 'otf')
            || ($extension_lowercase == 'eot')
            || ($extension_lowercase == 'woff')
            || ($extension_lowercase == 'woff2')
            || ($extension_lowercase == 'ico')
        ) {
            $design = true;

            echo '<tr><td class="items">'. $number_of_items . '</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>' . h($name) . '</td></tr>';
            flush();

        } else {
            $design = false;

            echo '<tr><td class="items">'. $number_of_items . '</td><td>&nbsp;</td><td>&nbsp;</td><td>' . h($name) . '</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
            flush();
        }

        //echo '<div>File:' . h($name) . '</div>';

        // Remember that we have now scanned this item.
        $scanned_items[$url] = array(
            'url' => $url,
            'base_url' => $base_url,
            'type' => $type,
            'name' => $new_name,
            'extension' => $extension,
            'design' => $design);

        if ($command_line) {
            echo 'Scanned Item:' . "\n";
            print_r($scanned_items[$url]);
            echo 'Scanned Item Count: ' . count($scanned_items) . "\n";

            // Add a 1 second delay in order to avoid firewalls/Cloudflare/WordFence detecting
            // and blocking this import.
            sleep(1);
        }
    }

    // If we have not reached the max imported items yet,
    // and we have not reached the max crawl level yet,
    // and this is an HTML, CSS, or JS file, then crawl child items.
    if (
        ($number_of_items < $max_imported_items)
        && ($scan_level < $max_scan_level)
        &&
        (
            ($type == 'html')
            || ($type == 'css')
            || ($type == 'js')
        )
    ) {
        $new_scan_level = $scan_level + 1;

        // If the type is html, then crawl various items in the HTML.
        if ($type == 'html') {
            preg_match_all('/<\s*img\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                    'scan_level' => $new_scan_level));
            }

            preg_match_all('/<\s*link\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                preg_match('/rel\s*=\s*["\'](.*?)["\']/is', $match[0], $match_rel);

                $rel = mb_strtolower(trim($match_rel[1]));

                // If the link tag is for a stylesheet or favicon,
                // then continue to crawl it.
                if (
                    ($rel == 'stylesheet')
                    || ($rel == 'alternate stylesheet')
                    || ($rel == 'icon')
                    || ($rel == 'shortcut icon')
                ) {
                    scan(array(
                        'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                        'scan_level' => $new_scan_level));
                }
            }

            preg_match_all('/<\s*script\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                    'base_url' => $url,
                    'scan_level' => $new_scan_level));
            }

            preg_match_all('/<\s*source\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                    'scan_level' => $new_scan_level));
            }

            preg_match_all('/<\s*embed\s+[^>]*src\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                    'scan_level' => $new_scan_level));
            }

            preg_match_all('/<\s*object\s+[^>]*data\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, unhtmlspecialchars(trim($match[1]))),
                    'scan_level' => $new_scan_level));
            }

            if ($link_level < $max_link_level) {
                $new_link_level = $link_level + 1;

                preg_match_all('/<\s*a\s+[^>]*href\s*=\s*["\'](.*?)["\'].*?>/is', $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $item_url = unhtmlspecialchars(trim($match[1]));

                    // If the anchor only contains a bookmark (e.g. #example),
                    // then don't scan this anchor.
                    if (mb_substr($item_url, 0, 1) == '#') {
                        continue;
                    }

                    $item_url = url_to_absolute($base_url, $item_url);

                    scan(array(
                        'url' => $item_url,
                        'scan_level' => $new_scan_level,
                        'link_level' => $new_link_level));
                }
            }
        }

        // If the type is HTML or CSS then scan for CSS items.
        if (($type == 'html') || ($type == 'css')) {
            // Crawl all URL references first.  This includes import statements
            // that use "url" syntax, background images, and etc.

            preg_match_all('/url\(\s*["\']?(.*?)["\']?\s*\)/i', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, trim($match[1])),
                    'scan_level' => $new_scan_level));
            }

            // Crawl import statements that use @import "example.css" syntax without "url()".

            preg_match_all('/@import\s*["\'](.*?)["\']/i', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                scan(array(
                    'url' => url_to_absolute($base_url, trim($match[1])),
                    'scan_level' => $new_scan_level));
            }
        }

        // If the type is HTML or JS then scan resources in the JS.
        // This means that we look for files in <script> tags, inline JS (e.g. onmouseover="example")
        // and in the JS files themselves.
        if (($type == 'html') || ($type == 'js')) {
            // We just look for any resource enclosed in quotes.
            // We have added support for an optional query string part (e.g. ?name=value)
            // that can appear after the file extension.
            preg_match_all('/["\']\s*([^"\']*\.(css|js|gif|jpg|jpeg|png)(\?.*?)?)\s*["\']/i', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $pass_base_url = '';

                // If this is a JS file, then pass the base URL
                // when we scan it, because paths in JS files are relative to the parent HTML.
                if (mb_strtolower($match[2]) == 'js') {
                    $pass_base_url = $base_url;
                }

                scan(array(
                    'url' => url_to_absolute($base_url, trim($match[1])),
                    'base_url' => $pass_base_url,
                    'scan_level' => $new_scan_level));
            }
        }
    }
}

function get_protocol_relative_url($url)
{
    if (mb_substr($url, 0, 7) == 'http://') {
        return mb_substr($url, 5);

    } else if (mb_substr($url, 0, 8) == 'https://') {
        return mb_substr($url, 6);

    } else {
        return $url;
    }
}

function check_if_url_exists($url)
{
    $headers = @get_headers($url, 1);

    if ($headers == false) {
        return false;
    }

    $response = trim($headers[0]);
    $response_parts = explode(' ', $headers[0]);
    $response_code = $response_parts[1];
    $first_character_of_response_code = mb_substr($response_code, 0, 1);

    if (($first_character_of_response_code == '2') || ($first_character_of_response_code == '3')) {
        return $headers;

    } else {
        return false;
    }
}
?>
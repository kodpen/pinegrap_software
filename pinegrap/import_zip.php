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

ini_set('max_execution_time', '9999');

include('init.php');
require('url_to_absolute.php');

$user = validate_user();
validate_area_access($user, 'designer');

include_once('liveform.class.php');
$liveform = new liveform('import_zip');

// If the form has not been submitted, then output it.
if (!$_POST) {
    echo
        output_header() . '
        <div id="subnav">
            ' . get_design_subnav() . '
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Import ZIP File</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Import web pages and files from a ZIP file.<br /></div>
            <form enctype="multipart/form-data" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <td>ZIP File:</td>
                        <td>' . $liveform->output_field(array('type' => 'file', 'name' => 'file', 'size' => '60')) . '</td>
                    </tr>
                    <tr>
                        <td>Tag:</td>
                        <td>' . $liveform->output_field(array('type' => 'text', 'name' => 'import_name', 'size' => '10')) . ' &nbsp;(appended to imported items and filenames for easy reference, recommended, e.g. "mysite")</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_import" value="Import" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'view_styles.php\'" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form has been submitted so process it.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();

    $import_name = $liveform->get_field_value('import_name');

    $zip_file_name = $_FILES['file']['name'];
    
    require('pclzip.lib.php');
    
    // load zip file
    $archive = new PclZip($_FILES['file']['tmp_name']);
    
    // get all items in archive
    $archive_items = $archive->listContent();
    
    // if zip file is invalid, output error
    if (!$archive_items) {
        output_error('The zip file cannot be extracted, because it is not a valid zip file. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $folder_id = 0;

    // Create an array of imported items that we will use
    // to keep track of which items were actually created.
    $imported_items = array();
    
    // loop through all archive items
    foreach ($archive_items as $archive_item) {
        // If this is a directory, then skip to next item.
        if (($archive_item['filename']{mb_strlen($archive_item['filename']) - 1}) == '/') {
            continue;
        }

        // If we have not already prepared the folder, then do that.
        if (!$folder_id) {
            // Try to find an existing "Import ZIP File" folder.
            $import_zip_file_folder_id = db_value("SELECT folder_id FROM folder WHERE folder_name = 'Import ZIP File'");

            // If an "Import ZIP File" folder was not found, then create it.
            if (!$import_zip_file_folder_id) {
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
                        'Import ZIP File',
                        '$root_folder_id',
                        '1',
                        '0',
                        '" . USER_ID . "',
                        UNIX_TIMESTAMP())");

                $import_zip_file_folder_id = mysqli_insert_id(db::$con);
            }

            $import_zip_file_folder_level = db_value("SELECT folder_level FROM folder WHERE folder_id = '$import_zip_file_folder_id'");

            $folder_level = $import_zip_file_folder_level + 1;

            // Set folder name to ZIP file name.
            $folder_name = $zip_file_name;

            // Add timestamp to folder name
            $folder_name .= ' ' . get_absolute_time(array('timestamp' => time(), 'format' => 'plain_text', 'timezone_type' => 'site'));

            // If the user supplied an import name, then add it to the folder name.
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
                    '$import_zip_file_folder_id',
                    '$folder_level',
                    '0',
                    '" . USER_ID . "',
                    UNIX_TIMESTAMP())");

            $folder_id = mysqli_insert_id(db::$con);
        }

        $url = 'http://example.com/' . $archive_item['filename'];
        $base_url = $url;

        $name = basename($archive_item['filename']);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $extension_lowercase = mb_strtolower($extension);

        if (
            ($extension_lowercase == 'html')
            || ($extension_lowercase == 'htm')
            || ($extension_lowercase == 'xhtml')
        ) {
            $type = 'html';

        } else if ($extension_lowercase == 'css') {
            $type = 'css';

        } else if ($extension_lowercase == 'js') {
            $type = 'js';

        } else {
            $type = 'general';
        }

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

        $extracted_file_contents = $archive->extract(PCLZIP_OPT_BY_NAME, $archive_item['filename'], PCLZIP_OPT_EXTRACT_AS_STRING);
        $content = $extracted_file_contents[0]['content'];

        if ($type == 'html') {

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
                if ($base_hostname == 'example.com') {
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

        // Otherwise this is a regular file, so create file.
        } else {

            $name = prepare_file_name($name);

            $sql_folder_id = $folder_id;
            $sql_description = '';

            // If this is a certain type of file, then prepare to mark file as design file.
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
                $sql_design = '1';

            } else {
                $sql_design = '0';
            }

            $old_file_backed_up = false;

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
        }
    }

    require('update_imported_items.php');
    update_imported_items($imported_items);

    $liveform->remove_form();

    $liveform_view_pages = new liveform('view_pages');
    $liveform_view_pages->add_notice('Items in ZIP file have been imported. ');

    go(PATH . SOFTWARE_DIRECTORY . '/view_pages.php?query=' . urlencode($import_name));
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
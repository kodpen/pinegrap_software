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

function get_folder_view_screen_content($properties)
{   
    $current_page_id = $properties['current_page_id'];
    $include_pages = $properties['pages'];
    $include_files = $properties['files'];

    include_once('liveform.class.php');
    $liveform = new liveform('folder_view');

    $output_my_start_page = '';

    // If the user has a start page and it is not this folder view page, then continue to check if we should output start page.
    if (
        (USER_START_PAGE_ID != 0)
        && (USER_START_PAGE_ID != $current_page_id)
    ) {
        // Get start page name.
        $start_page_name = get_page_name(USER_START_PAGE_ID);
        
        // If a start page name was found, then the page still exists, so prepare to output start page link.
        if ($start_page_name != '') {
            $output_my_start_page = '<div class="my_start_page" style="margin-bottom: 1em"><a class="software_button_tiny_secondary" href="' . OUTPUT_PATH . h(encode_url_path($start_page_name)) .'">My Start Page</a></div>';
        }
    }

    $output_folder_view_tree = '';

    // If neither pages nor files is selected to be included, then output error, because no content can be displayed.
    if (
        ($include_pages == 0)
        && ($include_files == 0)
    ) {
        $liveform->mark_error('', 'Sorry, neither Pages nor Files are selected to be included in this Folder View, so there is nothing to display.');

    // Otherwise pages and/or files are selected to be included, so get folder view tree
    } else {
        $folder_id = db_value("SELECT page_folder AS folder_id FROM page WHERE page_id = '$current_page_id'");
        $output_folder_view_tree = get_folder_view_tree($folder_id, $include_pages, $include_files, $current_page_id);

        // If there is no content for folder view tree, then output message.
        if ($output_folder_view_tree == '') {
            // If both pages and files are included then prepare message for that.
            if (
                ($include_pages == 1)
                && ($include_files == 1)
            ) {
                $output_items = 'pages or files';

            // Otherwise if pages are included, then prepare message for that.
            } else if ($include_pages == 1) {
                $output_items = 'pages';

            // Otherwise files are only included, so prepare message for that.
            } else {
                $output_items = 'files';
            }

            $output_folder_view_tree = '<p class="no_items_message">Sorry, we could not find any ' . $output_items . ' that you have access to, for this view.</p>';
        }
    }

    $output =
        '<div class="software_folder_view">
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            ' . $output_my_start_page . '
            <div class="folder_view_tree">
                ' . $output_folder_view_tree . '
            </div>
        </div>';

    $liveform->remove_form('folder_view');

    return $output;
}

// Create function that is responsible for building the content of the folder view tree.
function get_folder_view_tree($folder_id, $include_pages, $include_files, $excluded_page_id)
{
    $output = '';

    // If this folder is not archived, then continue to get folder contents.
    if (db_value("SELECT folder_archived FROM folder WHERE folder_id = '$folder_id'") == 0) {
        // Get folders in this folder.
        $folders = db_items(
            "SELECT
                folder_id AS id,
                folder_name AS name
            FROM folder
            WHERE folder_parent = '$folder_id'
            ORDER BY
                folder_order ASC,
                folder_name ASC");

        // Loop through folders in order to get their contents.
        foreach ($folders as $folder) {
            $folder_output = get_folder_view_tree($folder['id'], $include_pages, $include_files, $current_page_id);

            // If this folder has content to display, then output folder name and contents.
            // We output a folder even if a visitor does not have view access to it, assuming it has content,
            // because the visitor has view access to something inside of it and we need to preserve
            // the heirarchy/indentation.
            if ($folder_output != '') {
                $output .=
                    '<li class="folder ' . get_access_control_type($folder['id']) . '">
                        <div class="name">' . h($folder['name']) . '</div>
                        ' . $folder_output . '
                    </li>';
            }
        }

        // If the visitor has view access to this folder, then continue to get pages and files.
        if (check_view_access($folder_id, $always_grant_access_for_registration_and_guest = true) == true) {
            // If pages are selected to be included, then get them.
            if ($include_pages == 1) {
                $pages = db_items(
                    "SELECT
                        page_id AS id,
                        page_name AS name,
                        page_title AS title,
                        page_meta_description AS meta_description
                    FROM page
                    WHERE
                        (page_folder = '$folder_id')
                        AND (page_search = '1')
                        AND (page_type != 'registration confirmation')
                        AND (page_type != 'membership confirmation')
                        AND (page_type != 'view order')
                        AND (page_type != 'custom form confirmation')
                        AND (page_type != 'form item view')
                        AND (page_type != 'calendar event view')
                        AND (page_type != 'catalog detail')
                        AND (page_type != 'shipping address and arrival')
                        AND (page_type != 'shipping method')
                        AND (page_type != 'billing information')
                        AND (page_type != 'order preview')
                        AND (page_type != 'order receipt')
                        AND (page_type != 'affiliate sign up confirmation')
                        AND (page_type != 'affiliate welcome')
                    ORDER BY name ASC");

                // Loop through pages in order to output them.
                foreach ($pages as $page) {
                    // If this is not the excluded page, then output it.
                    if ($page['id'] != $excluded_page_id) {
                        // If there is a title, then prepare link with title.
                        if ($page['title'] != '') {
                            $output_name = '<div class="name"><a href="' . OUTPUT_PATH . h(encode_url_path($page['name'])) . '">' . h($page['title']) . '</a></div>';
                            
                        // Otherwise there is not a title, so prepare link with page name.
                        } else {
                            $output_name = '<div class="name"><a href="' . OUTPUT_PATH . h(encode_url_path($page['name'])) . '">' . h($page['name']) . '</a></div>';
                        }

                        $output_description = '';
                        
                        // If there is a meta description for this page, then output it.
                        if ($page['meta_description'] != '') {
                            $output_description = '<div class="description">' . h($page['meta_description']) . '</div>';
                        }

                        $output .=
                            '<li class="page">
                                ' . $output_name . '
                                ' . $output_description . '
                            </li>';
                    }
                }
            }

            // If files are selected to be included, then get them.
            if ($include_files == 1) {
                $files = db_items(
                    "SELECT
                        name,
                        description,
                        design
                    FROM files
                    WHERE folder = '$folder_id'
                    ORDER BY name ASC");

                // Loop through files in order to output them.
                foreach ($files as $file) {
                    $output_design_class = '';

                    // If this file is a design file, then output design class.
                    if ($file['design'] == 1) {
                        $output_design_class = ' design';
                    }

                    $output_description = '';
                    
                    // If there is a description for this file, then output it.
                    if ($file['description'] != '') {
                        $output_description = '<div class="description">' . h($file['description']) . '</div>';
                    }

                    $output .=
                        '<li class="file' . $output_design_class . '">
                            <div class="name"><a href="' . OUTPUT_PATH . h(encode_url_path($file['name'])) . '">' . h($file['name']) . '</a></div>
                            ' . $output_description . '
                        </li>';
                }
            }
        }

        // If content was found for this folder, then wrap content in ul tags.
        if ($output != '') {
            $output = '<ul>' . $output . '</ul>';
        }
    }

    return $output;
}
?>
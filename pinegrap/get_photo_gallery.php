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

function get_photo_gallery($properties) {

    global $user;

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];

    $properties = get_page_type_properties($page_id, 'photo gallery');

    $number_of_columns = $properties['number_of_columns'];
    $thumbnail_max_size = $properties['thumbnail_max_size'];

    $layout_type = get_layout_type($page_id);

    $page_name = get_page_name($page_id);

    if ($layout_type == 'system') {
        
        // If the number of thumbnails is greater than 0, then prepare and display the photo gallery
        if ($number_of_columns > 0) {
            // get folder id
            $query = 
                "SELECT 
                    page.page_folder,
                    folder.folder_name
                FROM page 
                LEFT JOIN folder on folder.folder_id = page.page_folder
                WHERE page_id = '" . escape($page_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $folder_id = $row['page_folder'];
            $folder_name = $row['folder_name'];
            
            $output_back_button = '';
            
            // if there is a folder id in the query bar, and if the current page's folder id is not equal to the folder id in the query bar, 
            // then check the folder access, set the folder id in the query bar as the current folder id and output the back button
            if (($_GET['folder_id'] != '') && ($folder_id != $_GET['folder_id'])) {
                /** Check to verify that this folder is inside the scope of the photo gallery **/
                
                // get all folders
                $query =
                    "SELECT
                        folder_id as id,
                        folder_parent as parent_folder_id
                    FROM folder";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $all_child_folders = array();
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $folders[] = $row;
                }
                
                // get all child folders that are within the scope of this photo gallery
                $all_child_folders = get_child_folders($folder_id, $folders);
                
                // if this folder is not in the array, then output an error
                if (in_array($_GET['folder_id'], $all_child_folders) == FALSE) {
                    log_activity("access denied because folder is not in the scope of the photo gallery page ($page_name)", $_SESSION['sessionusername']);
                    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
                }
                
                /** If we get to this point then the folder is within the scope of this photo gallery, so continue **/
                
                // replace the page's parent folder id with the new id passed in the query bar
                $folder_id = $_GET['folder_id'];
                
                // get the new folder's name
                $query = "SELECT folder_name, folder_parent FROM folder WHERE folder_id = '" . escape($folder_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $folder_name = $row['folder_name'];
                $folder_parent = $row['folder_parent'];
                
                /** Check access control to verify that the user has access to view this folder **/
                
                // get the access control type for the folder
                $access_control_type = get_access_control_type($folder_id);
                
                // if this is folder is not public, and if the user is not logged in or if they are a basic user, then check access control
                if (
                    ($access_control_type != 'public')
                    && (
                        (isset($user['role']) == FALSE) 
                        || ($user['role'] == 3)
                    )
                ) {
                    // do different things depending on access control type
                    switch ($access_control_type) {
                        case 'private':
                            // if user is not logged in or has an invalid login, send user to login screen
                            if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/index.php?send_to=' . urlencode(get_request_uri()));
                                exit();
                                
                            // else user is logged in
                            } else {
                                $access_check = check_private_access($folder_id);

                                // If the visitor does not have private access to this folder, log activity and output error.
                                if ($access_check['access'] == false) {
                                    log_activity("access denied to private folder ($folder_name)", $_SESSION['sessionusername']);
                                    output_error('Access denied. <a href="javascript:history.go(-1);">Go back</a>.');
                                }
                            }

                            break;

                        case 'guest':
                            // if user is not logged in or has an invalid login, and if the user is not browsing the site as a Guest,
                            // then forward user to Registration Entrance screen
                            if ((validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) 
                                && ($_SESSION['software']['guest'] !== true)) {
                                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?allow_guest=true&send_to=' . urlencode(get_request_uri()));
                                exit();
                            }
                            break;

                        case 'registration':
                            // if user is not logged in or has an invalid login, then forward user to Registration Entrance page
                            if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
                                exit();
                            }
                            break;

                        case 'membership':
                            // if user is not logged in or has an invalid login, then forward user to Membership Entrance page
                            if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php?send_to=' . urlencode(get_request_uri()));
                                exit();
                            }
                            
                            // if user does not have edit rights to this folder, then we need to validate membership
                            if (check_edit_access($folder_id) == false) {
                                // get user information so we can find contact for user
                                $query = "SELECT user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $row = mysqli_fetch_assoc($result);
                                $contact_id = $row['user_contact'];
                                
                                // get contact information
                                $query = "SELECT member_id, expiration_date FROM contacts WHERE id = '" . $contact_id . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $row = mysqli_fetch_assoc($result);
                                $member_id = $row['member_id'];
                                $expiration_date = $row['expiration_date'];

                                // if member id cannot be found and user has a user role, then output error
                                if (!$member_id) {
                                    output_error('Access denied. The album that you requested requires membership. Your ' . h(MEMBER_ID_LABEL) . ' could not be found. You can view your membership status in your account area. Please contact us for more information.');
                                }

                                // if expiration date is blank, then make expiration date high for lifetime membership
                                if (!$expiration_date || ($expiration_date == '0000-00-00')) {
                                    $expiration_date = '9999-99-99';
                                }

                                // if membership has expired and user has a user role, then output error
                                if ($expiration_date < date('Y-m-d')) {
                                    output_error('Access denied. The album that you requested requires membership. Your membership could not be verified.  You can view your membership status in your account area. Please contact us for more information.');
                                }
                            }
                            break;
                    }
                }
                
                /** If we get to this point then the user has access to view this folder, so continue **/
                
                $folder_parent_id_for_url = '';
                
                // if there is a parent folder, then output the id in the url
                if ($folder_parent != '') {
                    $folder_parent_id_for_url = '?folder_id=' . h(urlencode($folder_parent));
                }
                
                // output the back button
                $output_back_button = '<div style="margin-top: 1em; margin-bottom: 5px"><a href="' . OUTPUT_PATH . h(encode_url_path($page_name)) . $folder_parent_id_for_url . '" class="software_button_secondary back_button">Back</a></div>';
            }
            
            // get child folders for current folder, they will be used in several places below
            $query =
                "SELECT
                    folder_id as id,
                    folder_name as name
                FROM folder
                WHERE 
                    folder_parent = '" . escape($folder_id) . "'
                ORDER BY
                    folder_order,
                    folder_name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $child_folders = array();
            
            // Add each child folder to the array
            while ($row = mysqli_fetch_assoc($result)) {
                $child_folders[] = $row;
            }
            
            // loop through the child folders and validate the user's access to view them
            foreach($child_folders as $key => $child_folder) {
                // get the access control type for the folder
                $access_control_type = get_access_control_type($child_folder['id']);
                
                // assume the user has access until proven otherwise
                $has_access = TRUE;
                
                // if this folder is not public, and if the user is not logged in or if they are a basic user, then check access control
                if (
                    ($access_control_type != 'public')
                    && (
                        (isset($user['role']) == FALSE) 
                        || ($user['role'] == 3)
                    )
                ) {
                    switch ($access_control_type) {
                        case 'private':
                            $access_check = check_private_access($child_folder['id']);

                            // if user does not have access to this folder then the user does not have access
                            if ($access_check['access'] == false) {
                                $has_access = FALSE;
                            }
                            break;
                            
                        case 'guest':
                            // if user is not logged in or has an invalid login, and if the user is not browsing the site as a Guest, 
                            // then the user does not have access
                            if (
                                (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) 
                                && ($_SESSION['software']['guest'] !== true)
                            ) {
                                $has_access = FALSE;
                            }
                            break;
                            
                        case 'registration':
                            // if user is not logged in or has an invalid login, then the user does not have access
                            if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                                $has_access = FALSE;
                            }
                            break;

                        case 'membership':
                            // if user is not logged in or has an invalid login, then the user does not have access
                            if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                                $has_access = FALSE;
                            
                            // else if user does not have edit rights to this folder, then we need to validate membership
                            } elseif (check_edit_access($folder_id) == false) {
                                // get user information so we can find contact for user
                                $query = "SELECT user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $row = mysqli_fetch_assoc($result);
                                $contact_id = $row['user_contact'];
                                
                                // get contact information
                                $query = "SELECT member_id, expiration_date FROM contacts WHERE id = '" . $contact_id . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                                $row = mysqli_fetch_assoc($result);
                                $member_id = $row['member_id'];
                                $expiration_date = $row['expiration_date'];

                                // if member id cannot be found, then the user does not have access
                                if (!$member_id) {
                                    $has_access = FALSE;
                                }

                                // if expiration date is blank, then make expiration date high for lifetime membership
                                if (!$expiration_date || ($expiration_date == '0000-00-00')) {
                                    $expiration_date = '9999-99-99';
                                }

                                // if membership has expired and user has a user role, then the user does not have access
                                if ($expiration_date < date('Y-m-d')) {
                                    $has_access = FALSE;
                                }
                            }
                            break;
                    }
                }
                
                // if the user does not have access to this folder, then remove it from the array
                if ($has_access == FALSE) {
                    unset($child_folders[$key]);
                }
            }
            
            // get all images from database, they will be used in several places below
            $query =
                "SELECT
                    id,
                    name,
                    description,
                    folder
                FROM files
                WHERE 
                    (
                        (type = 'gif')
                        || (type = 'jpg')
                        || (type = 'jpeg')
                        || (type = 'png')
                        || (type = 'bmp')
                        || (type = 'tif')
                        || (type = 'tiff')
                    )
                    AND (design = 0)
                ORDER BY name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $all_images = array();
            
            // Add each image to the array
            while ($row = mysqli_fetch_assoc($result)) {
                $all_images[] = $row;
            }
            
            // prepare cell dimensions for mobile albums (an approxmation) to line up any wrapping cells
            $output_max_cell = '';

            if ($device_type == 'mobile') {
                $height_intval = 10 + intval($thumbnail_max_size);
                $output_max_cell = '; width: ' . strval($height_intval) . 'px; height: ' . strval($height_intval + 50) . 'px';
            }

            $albums = array();
            
            // loop through each child folder to determine which ones are albums
            foreach($child_folders as $child_folder) {
                $photos_in_album = array();
                
                // loop through all images and add the ones that are in this folder to the photos in album array so we can use them later
                foreach($all_images as $image) {
                    // if this image's folder is equal to the current folder, then add the image to the array
                    if ($image['folder'] == $child_folder['id']) {
                        $photos_in_album[] = $image;
                    }
                }
                
                // get the number of photos in the child folder
                $number_of_photos = count($photos_in_album);
                
                // if there are photos in the child folder, then this folder is an album, so add it to the albums array
                if ($number_of_photos > 0) {
                    $albums[] = 
                        array(
                            'id' => $child_folder['id'], 
                            'name' => $child_folder['name'], 
                            'number_of_photos' => $number_of_photos, 
                            'thumbnail_name' => $photos_in_album[0]['name']
                        );
                }
            }
            
            $output_albums = '';
            
            // if there are albums then prepare and output them
            if (empty($albums) == FALSE) {
                $cell_counter = 0;
                $closed_last_row = FALSE;
                $output_album_thumbnail_table_cells = '';
                
                // loop through each album to prepare and output them
                foreach($albums as $album) {
                    // If there is a album name, then continue
                    if ($album['name'] != '') {
                        // if the counter is zero, then output a starting tr tag
                        if ($cell_counter == 0) {
                            $output_album_thumbnail_table_cells .= '<tr>';
                            $closed_last_row = FALSE;
                        }
                        
                        // Get the dimensions of the image
                        $image_size = getimagesize(FILE_DIRECTORY_PATH . '/' . $album['thumbnail_name']);
                        $image_width = $image_size[0];
                        $image_height = $image_size[1];
                        
                        // get scaled dimensions of the image
                        $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $thumbnail_max_size);
                        
                        $output_thumbnail_width = '';
                        
                        // if the thumbnail width setting is greater than 0, then prepare to constrain thumbnail width
                        if ($thumbnail_dimensions['width'] > 0) {
                            $output_thumbnail_width = ' width="' . $thumbnail_dimensions['width'] . '"';
                        }
                        
                        $output_thumbnail_height = '';
                        
                        // if the thumbnail height setting is greater than 0, then prepare to constrain thumbnail height
                        if ($thumbnail_dimensions['height'] > 0) {
                            $output_thumbnail_height = ' height="' . $thumbnail_dimensions['height'] . '"';
                        }
                        
                        // output the image tag
                        $output_album_thumbnail_image_tag = '<img id="album_' . $album['id'] . '" class="image" src="' . OUTPUT_PATH . h(encode_url_path($album['thumbnail_name'])) . '"' . $output_thumbnail_width . $output_thumbnail_height . ' title="" alt="' . h($album['name']) . '" border="0" />';
                        
                        // if mode is edit then add the edit button to the image
                        if ($editable == TRUE) {
                            $output_album_thumbnail_image_tag = add_edit_button_for_images('photo_gallery', 0, $output_album_thumbnail_image_tag);
                        }
                        
                        // start to output the number of photos
                        $output_number_of_photos = '(' . $album['number_of_photos'] . ' Photo';
                        
                        // if there is more than one image, then add "s"
                        if ($album['number_of_photos'] > 1) {
                            $output_number_of_photos .= 's';
                        }
                        
                        // close the parenthesis
                        $output_number_of_photos .= ')';
                        
                        // output the cell
                        $output_album_thumbnail_table_cells .= 
                            '<td class="album mobile_left" style="vertical-align: top' . $output_max_cell .'">
                                <div class="thumbnail" style="position: relative;">
                                    ' . $output_album_thumbnail_image_tag . '
                                    <div id="album_frame_1" class="album_frame"></div>
                                    <div id="album_frame_2" class="album_frame"></div>
                                </div>
                                <div class="name">' . h($album['name']) . '</div>
                                <div class="number_of_photos">' . h($output_number_of_photos) . '</div>
                            </td>';
                        
                        // if the cell counter is smaller than the number of thumbnails, then increment the counter, 
                        // and set closed last row to false so that we know we will need to close the last row
                        if ($cell_counter < ($number_of_columns - 1)) {
                            $cell_counter++;
                            
                        // else if the counter is not zero, then reset the counter, output a closing tr tag, 
                        // and set closed last row to true so that we do not close the row again
                        // we ignore adding new rows to the table if mobile (since we will float all photos)
                        } elseif ($cell_counter != 0 && $device_type != 'mobile') {
                            $cell_counter = 0;
                            $output_album_thumbnail_table_cells .= '</tr>';
                            $closed_last_row = TRUE;
                        }
                    }
                }
                
                // if the last row wasn't closed, then close it
                if ($closed_last_row == FALSE) {
                    $output_album_thumbnail_table_cells .= '</tr>';
                }
                
                // output the albumns
                $output_albums = 
                    '<table>
                        ' . $output_album_thumbnail_table_cells . '
                    </table>';
            }
            
            $photos = array();
            
            // loop through all images and add the ones that are in the current folder to the photos in current folder array so that we can output them later
            foreach($all_images as $image) {
                // if this image's folder is equal to the current folder, then add the image to the array
                if ($image['folder'] == $folder_id) {
                    $photos[] = $image;
                }
            }

            $output_photos = '';
            
            // if there are photos, then prepare and output them
            if (empty($photos) == FALSE) {
                $output_photo_thumbnail_table_cells = '';
                $cell_counter = 0;
                $closed_last_row = FALSE;
                
                // loop through photos and prepare and output them
                foreach($photos as $photo) {
                    // If there is a photo name, then continue
                    if ($photo['name'] != '') {
                        // if the counter is zero, then output a starting tr tag
                        if ($cell_counter == 0) {
                            $output_photo_thumbnail_table_cells .= '<tr>';
                            $closed_last_row = FALSE;
                        }
                        
                        $output_thumbnail_width = '';
                        
                        // Get the dimensions of the image
                        $image_size = getimagesize(FILE_DIRECTORY_PATH . '/' . $photo['name']);
                        $image_width = $image_size[0];
                        $image_height = $image_size[1];
                        
                        // get scaled dimensions of the image
                        $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $thumbnail_max_size);
                        
                        $output_thumbnail_width = '';
                        
                        // if the thumbnail width setting is greater than 0, then prepare to constrain thumbnail width
                        if ($thumbnail_dimensions['width'] > 0) {
                            $output_thumbnail_width = ' width="' . $thumbnail_dimensions['width'] . '"';
                        }
                        
                        $output_thumbnail_height = '';
                        
                        // if the thumbnail height setting is greater than 0, then prepare to constrain thumbnail height
                        if ($thumbnail_dimensions['height'] > 0) {
                            $output_thumbnail_height = ' height="' . $thumbnail_dimensions['height'] . '"';
                        }
                        
                        // output the image tag
                        $output_photo_thumbnail_image_tag = '<img id="photo_' . $photo['id'] . '" class="image" src="' . OUTPUT_PATH . h(encode_url_path($photo['name'])) . '"' . $output_thumbnail_width . $output_thumbnail_height . ' alt="' . h($photo['name']) . '" border="0" />';
                        
                        $output_starting_container_tag = '';
                        $output_ending_container_tag = '';
                        
                        // if mode is edit then add the edit button to the image,
                        // and add a containg div tag with a relative position so that the edit button doesn't incorrectly become relative to the table cell
                        // the div tag is also set to inline block so that it's width matches the image's width
                        if ($editable == TRUE) {
                            $output_photo_thumbnail_image_tag = add_edit_button_for_images('photo_gallery', 0, $output_photo_thumbnail_image_tag);
                            $output_starting_container_tag = '<div style="position: relative; display: inline-block">';
                            $output_ending_container_tag = '</div>';
                        }

                        // prepare cell dimensions for mobile photos (an approximation) to line up any wrapping cells
                        $output_max_cell = '';

                        if ($device_type == 'mobile') {
                            $height_intval = 25 + intval($thumbnail_max_size);
                            $output_max_cell = '; width: ' . strval($height_intval) . 'px; height: ' . strval($height_intval) . 'px';
                        }

                        // output the cell
                        $output_photo_thumbnail_table_cells .= '<td class="photo mobile_left" style="vertical-align: top' . $output_max_cell . '">' . $output_starting_container_tag . '<a class="link" href="' . OUTPUT_PATH . h(encode_url_path($photo['name'])) . '" title="' . h($photo['description']) . '" alt="' . h($photo['name']) . '">' . $output_photo_thumbnail_image_tag . '</a>' . $output_ending_container_tag . '</td>';
                        
                        // if the cell counter is smaller than the number of thumbnails, then increment the counter and set close last row to false so that we know we will need to close the last row
                        if ($cell_counter < ($number_of_columns - 1)) {
                            $cell_counter++;
                            
                        // else if the counter is not zero, then reset the counter, output a closing tr tag, and set closed last row to true so that we do not close the row again
                        // we ignore adding new rows to the table if mobile (since we will float all photos)
                        } elseif ($cell_counter != 0 && $device_type != 'mobile') {
                            $output_photo_thumbnail_table_cells .= '</tr>';
                            $closed_last_row = TRUE;
                            $cell_counter = 0;
                        }
                    }
                }
                
                // if the last row wasn't closed, then close it
                if ($closed_last_row == FALSE) {
                    $output_photo_thumbnail_table_cells .= '</tr>';
                }
                
                // output the photos
                $output_photos = 
                    '<table>
                        ' . $output_photo_thumbnail_table_cells . '
                    </table>';
            }
            
            $output_javascript = '';
            
            // if there are not any albums or photos then output a notice to the user
            if ((empty($albums) == TRUE) && (empty($photos) == TRUE)) {
               return 
                    '<div class="software_photo_gallery">
                        <div class="software_photo_gallery_album">
                            <div class="heading" style="border: none"><h4>' . h($folder_name) . '</h4></div>
                            <p>There are no albums or photos in this photo gallery.</p>
                        </div>
                    </div>';
               
            // else prepare the javascript for the photo gallery and output the photo galley
            } else {
                // output the javascript required for the photo gallery
                $output_javascript =
                    '<script language="JavaScript">
                        var software_page_name = "' . escape_javascript($page_name) . '";
                        var software_photo_gallery_photos = new Array(
                        // object id' . "\r\n";
                
                $counter = 0;
                
                // loop through each album and add it to the array
                foreach ($albums as $album) {
                    $output_javascript .= '["album_' . $album['id'] . '"]';
                    
                    // if this is not the last album, or if there are photos to output, then output a comma to separate the array
                    if (($counter < (count($albums) - 1)) || (empty($photos) == FALSE)) {
                        $output_javascript .= ',' . "\r\n";
                    }
                    
                    $counter++;
                }
                
                $counter = 0;
                
                // loop through each photo and add it to the array
                foreach ($photos as $photo) {
                    $output_javascript .= '["photo_' . $photo['id'] . '"]';
                    
                    // if this is not the last photo, then output a comma to separate the array
                    if ($counter < (count($photos) - 1)) {
                        $output_javascript .= ',' . "\r\n";
                    }
                    
                    $counter++;
                }
                
                // close the array
                $output_javascript .= "\r\n" . ' );' . "\r\n";
                
                $output_variables_for_image_edit_button = '';
                
                // if mode is edit then output the variables for image editor
                if ($editable == TRUE) {
                    $output_variables_for_image_edit_button = 'var software_send_to = "' . escape_javascript(get_request_uri()) . '";';
                }
                
                // output the image edit button variables
                $output_javascript .= $output_variables_for_image_edit_button . "\r\n";
                
                // output the function that inits the photo gallery and the closing script tag
                $output_javascript .= 'software_init_photo_gallery(software_photo_gallery_photos);' . "\r\n" . '</script>';
                
                // output the photo gallery
                $output =
                    '<div class="software_photo_gallery_album">
                        <div class="heading" style="border: none"><h4>' . h($folder_name) . '</h4></div>
                        ' . $output_albums . '
                        ' . $output_photos . '
                        ' . $output_back_button . '
                        ' . $output_javascript . '
                    </div>';
            }
        }
        
        return
            '<div class="software_photo_gallery">
                ' . $output . '
            </div>';

    // Otherwise the layout is custom.
    } else {

        // get folder id
        $query = 
            "SELECT 
                page.page_folder,
                folder.folder_name
            FROM page 
            LEFT JOIN folder on folder.folder_id = page.page_folder
            WHERE page_id = '" . escape($page_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $folder_id = $row['page_folder'];
        $folder_name = $row['folder_name'];
        
        $back_button_url = '';
        
        // if there is a folder id in the query bar, and if the current page's folder id is not equal to the folder id in the query bar, 
        // then check the folder access, set the folder id in the query bar as the current folder id and output the back button
        if (($_GET['folder_id'] != '') && ($folder_id != $_GET['folder_id'])) {
            /** Check to verify that this folder is inside the scope of the photo gallery **/
            
            // get all folders
            $query =
                "SELECT
                    folder_id as id,
                    folder_parent as parent_folder_id
                FROM folder";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $all_child_folders = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $folders[] = $row;
            }
            
            // get all child folders that are within the scope of this photo gallery
            $all_child_folders = get_child_folders($folder_id, $folders);
            
            // if this folder is not in the array, then output an error
            if (in_array($_GET['folder_id'], $all_child_folders) == FALSE) {
                log_activity("access denied because folder is not in the scope of the photo gallery page ($page_name)", $_SESSION['sessionusername']);
                output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
            }
            
            /** If we get to this point then the folder is within the scope of this photo gallery, so continue **/
            
            // replace the page's parent folder id with the new id passed in the query bar
            $folder_id = $_GET['folder_id'];
            
            // get the new folder's name
            $query = "SELECT folder_name, folder_parent FROM folder WHERE folder_id = '" . escape($folder_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $folder_name = $row['folder_name'];
            $folder_parent = $row['folder_parent'];
            
            /** Check access control to verify that the user has access to view this folder **/
            
            // get the access control type for the folder
            $access_control_type = get_access_control_type($folder_id);
            
            // if this is folder is not public, and if the user is not logged in or if they are a basic user, then check access control
            if (
                ($access_control_type != 'public')
                && (
                    (isset($user['role']) == FALSE) 
                    || ($user['role'] == 3)
                )
            ) {
                // do different things depending on access control type
                switch ($access_control_type) {
                    case 'private':
                        // if user is not logged in or has an invalid login, send user to login screen
                        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/index.php?send_to=' . urlencode(get_request_uri()));
                            exit();
                            
                        // else user is logged in
                        } else {
                            $access_check = check_private_access($folder_id);

                            // If the visitor does not have private access to this folder, log activity and output error.
                            if ($access_check['access'] == false) {
                                log_activity("access denied to private folder ($folder_name)", $_SESSION['sessionusername']);
                                output_error('Access denied. <a href="javascript:history.go(-1);">Go back</a>.');
                            }
                        }

                        break;

                    case 'guest':
                        // if user is not logged in or has an invalid login, and if the user is not browsing the site as a Guest,
                        // then forward user to Registration Entrance screen
                        if ((validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) 
                            && ($_SESSION['software']['guest'] !== true)) {
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?allow_guest=true&send_to=' . urlencode(get_request_uri()));
                            exit();
                        }
                        break;

                    case 'registration':
                        // if user is not logged in or has an invalid login, then forward user to Registration Entrance page
                        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
                            exit();
                        }
                        break;

                    case 'membership':
                        // if user is not logged in or has an invalid login, then forward user to Membership Entrance page
                        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php?send_to=' . urlencode(get_request_uri()));
                            exit();
                        }
                        
                        // if user does not have edit rights to this folder, then we need to validate membership
                        if (check_edit_access($folder_id) == false) {
                            // get user information so we can find contact for user
                            $query = "SELECT user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                            $row = mysqli_fetch_assoc($result);
                            $contact_id = $row['user_contact'];
                            
                            // get contact information
                            $query = "SELECT member_id, expiration_date FROM contacts WHERE id = '" . $contact_id . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                            $row = mysqli_fetch_assoc($result);
                            $member_id = $row['member_id'];
                            $expiration_date = $row['expiration_date'];

                            // if member id cannot be found and user has a user role, then output error
                            if (!$member_id) {
                                output_error('Access denied. The album that you requested requires membership. Your ' . h(MEMBER_ID_LABEL) . ' could not be found. You can view your membership status in your account area. Please contact us for more information.');
                            }

                            // if expiration date is blank, then make expiration date high for lifetime membership
                            if (!$expiration_date || ($expiration_date == '0000-00-00')) {
                                $expiration_date = '9999-99-99';
                            }

                            // if membership has expired and user has a user role, then output error
                            if ($expiration_date < date('Y-m-d')) {
                                output_error('Access denied. The album that you requested requires membership. Your membership could not be verified.  You can view your membership status in your account area. Please contact us for more information.');
                            }
                        }
                        break;
                }
            }
            
            /** If we get to this point then the user has access to view this folder, so continue **/
            
            $folder_parent_id_for_url = '';
            
            // if there is a parent folder, then output the id in the url
            if ($folder_parent != '') {
                $folder_parent_id_for_url = '?folder_id=' . urlencode($folder_parent);
            }
            
            $back_button_url = PATH . encode_url_path($page_name) . $folder_parent_id_for_url;
        }

        // get child folders for current folder, they will be used in several places below
        $query =
            "SELECT
                folder_id as id,
                folder_name as name
            FROM folder
            WHERE 
                folder_parent = '" . escape($folder_id) . "'
            ORDER BY
                folder_order,
                folder_name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $child_folders = array();
        
        // Add each child folder to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $child_folders[] = $row;
        }
        
        // loop through the child folders and validate the user's access to view them
        foreach($child_folders as $key => $child_folder) {
            // get the access control type for the folder
            $access_control_type = get_access_control_type($child_folder['id']);
            
            // assume the user has access until proven otherwise
            $has_access = TRUE;
            
            // if this folder is not public, and if the user is not logged in or if they are a basic user, then check access control
            if (
                ($access_control_type != 'public')
                && (
                    (isset($user['role']) == FALSE) 
                    || ($user['role'] == 3)
                )
            ) {
                switch ($access_control_type) {
                    case 'private':
                        $access_check = check_private_access($child_folder['id']);

                        // if user does not have access to this folder then the user does not have access
                        if ($access_check['access'] == false) {
                            $has_access = FALSE;
                        }
                        break;
                        
                    case 'guest':
                        // if user is not logged in or has an invalid login, and if the user is not browsing the site as a Guest, 
                        // then the user does not have access
                        if (
                            (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) 
                            && ($_SESSION['software']['guest'] !== true)
                        ) {
                            $has_access = FALSE;
                        }
                        break;
                        
                    case 'registration':
                        // if user is not logged in or has an invalid login, then the user does not have access
                        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                            $has_access = FALSE;
                        }
                        break;

                    case 'membership':
                        // if user is not logged in or has an invalid login, then the user does not have access
                        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == FALSE) {
                            $has_access = FALSE;
                        
                        // else if user does not have edit rights to this folder, then we need to validate membership
                        } elseif (check_edit_access($folder_id) == false) {
                            // get user information so we can find contact for user
                            $query = "SELECT user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                            $row = mysqli_fetch_assoc($result);
                            $contact_id = $row['user_contact'];
                            
                            // get contact information
                            $query = "SELECT member_id, expiration_date FROM contacts WHERE id = '" . $contact_id . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
                            $row = mysqli_fetch_assoc($result);
                            $member_id = $row['member_id'];
                            $expiration_date = $row['expiration_date'];

                            // if member id cannot be found, then the user does not have access
                            if (!$member_id) {
                                $has_access = FALSE;
                            }

                            // if expiration date is blank, then make expiration date high for lifetime membership
                            if (!$expiration_date || ($expiration_date == '0000-00-00')) {
                                $expiration_date = '9999-99-99';
                            }

                            // if membership has expired and user has a user role, then the user does not have access
                            if ($expiration_date < date('Y-m-d')) {
                                $has_access = FALSE;
                            }
                        }
                        break;
                }
            }
            
            // if the user does not have access to this folder, then remove it from the array
            if ($has_access == FALSE) {
                unset($child_folders[$key]);
            }
        }
        
        // get all images from database, they will be used in several places below
        $query =
            "SELECT
                id,
                name,
                description,
                folder
            FROM files
            WHERE 
                (
                    (type = 'gif')
                    || (type = 'jpg')
                    || (type = 'jpeg')
                    || (type = 'png')
                    || (type = 'bmp')
                    || (type = 'tif')
                    || (type = 'tiff')
                )
                AND (design = 0)
            ORDER BY name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $all_images = array();
        
        // Add each image to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $all_images[] = $row;
        }

        $albums = array();
        
        // loop through each child folder to determine which ones are albums
        foreach($child_folders as $child_folder) {
            $photos_in_album = array();
            
            // loop through all images and add the ones that are in this folder to the photos in album array so we can use them later
            foreach($all_images as $image) {
                // if this image's folder is equal to the current folder, then add the image to the array
                if ($image['folder'] == $child_folder['id']) {
                    $photos_in_album[] = $image;
                }
            }
            
            // get the number of photos in the child folder
            $number_of_photos = count($photos_in_album);
            
            // if there are photos in the child folder, then this folder is an album, so add it to the albums array
            if ($number_of_photos > 0) {
                $albums[] =  array(
                    'id' => $child_folder['id'],
                    'name' => $child_folder['name'],
                    'url' => PATH . encode_url_path($page_name) . '?folder_id=' . $child_folder['id'],
                    'number_of_photos' => $number_of_photos,
                    'image_name' => $photos_in_album[0]['name'],
                    'image_url' => PATH . encode_url_path($photos_in_album[0]['name']));
            }
        }
        
        $photos = array();
        
        // loop through all images and add the ones that are in the current folder to the photos in current folder array so that we can output them later
        foreach($all_images as $image) {
            // if this image's folder is equal to the current folder, then add the image to the array
            if ($image['folder'] == $folder_id) {
                $image['url'] = PATH . encode_url_path($image['name']);

                $photos[] = $image;
            }
        }

        $content = render_layout(array(
            'page_id' => $page_id,
            'album_name' => $folder_name,
            'albums' => $albums,
            'photos' => $photos,
            'back_button_url' => $back_button_url));

        return
            '<div class="software_photo_gallery">
                ' . $content . '
            </div>';
    }

}
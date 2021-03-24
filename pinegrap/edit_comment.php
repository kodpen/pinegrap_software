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
$user = validate_user();
validate_area_access($user, 'user');

// get comment information
$query = 
    "SELECT
        comments.page_id,
        comments.item_id,
        comments.item_type,
        comments.name,
        comments.message,
        files.id as file_id,
        files.name as file_name,
        files.size as file_size,
        comments.published,
        comments.publish_date_and_time,
        comments.publish_cancel,
        comments.featured,
        page.page_type,
        page.page_folder,
        page.comments_submitter_email_page_id,
        page.comments_watcher_email_page_id,
        user.user_username as created_username,
        comments.created_timestamp
    FROM comments
    LEFT JOIN files ON comments.file_id = files.id
    LEFT JOIN page ON page.page_id = comments.page_id
    LEFT JOIN user ON comments.created_user_id = user.user_id
    WHERE comments.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$page_id = $row['page_id'];
$item_id = $row['item_id'];
$item_type = $row['item_type'];
$name = $row['name'];
$message = $row['message'];
$file_id = $row['file_id'];
$file_name = $row['file_name'];
$file_size = $row['file_size'];
$published = $row['published'];
$publish_date_and_time = $row['publish_date_and_time'];
$publish_cancel = $row['publish_cancel'];
$featured = $row['featured'];
$page_type = $row['page_type'];
$folder_id = $row['page_folder'];
$comments_submitter_email_page_id = $row['comments_submitter_email_page_id'];
$comments_watcher_email_page_id = $row['comments_watcher_email_page_id'];
$created_username = $row['created_username'];
$created_timestamp = $row['created_timestamp'];

// if user does not have access then output error
if (check_edit_access($folder_id) == false) {
    log_activity("access denied to edit comment because user does not have access to modify folder that the page is in", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

include_once('liveform.class.php');
$liveform = new liveform('edit_comment', $_REQUEST['id']);

// if the form has not been submitted
if (!$_POST) {
    // if the created username is not known, then set to [Unknown]
    if ($created_username == '') {
        $created_username = '[Unknown]';
    }
    
    // if edit comment screen has not been submitted already, pre-populate fields with data
    if (isset($_SESSION['software']['liveforms']['edit_comment'][$_GET['id']]) == false) {
        $liveform->assign_field_value('send_to', $_GET['send_to']);
        $liveform->assign_field_value('id', $_GET['id']);
        $liveform->assign_field_value('name', $name);
        $liveform->assign_field_value('message', $message);

        if ($published) {
            $liveform->assign_field_value('publish', 'published');

        } else if ($publish_date_and_time != '0000-00-00 00:00:00') {
            $liveform->assign_field_value('publish', 'schedule');

        } else {
            $liveform->assign_field_value('publish', 'not_published');
        }

        // If the comment is a scheduled comment, then set values for schedule fields.
        if ($publish_date_and_time != '0000-00-00 00:00:00') {
            $liveform->assign_field_value('publish_date_and_time', prepare_form_data_for_output($publish_date_and_time, 'date and time'));
            $liveform->assign_field_value('publish_cancel', $publish_cancel);

        // Otherwise the comment is not a scheduled comment,
        // so set values for scheduled fields to default values.
        } else {
            // If the date format is month and then day, then use that format.
            if (DATE_FORMAT == 'month_day') {
                $month_and_day_format = 'n/j';

            // Otherwise the date format is day and then month, so use that format.
            } else {
                $month_and_day_format = 'j/n';
            }

            $liveform->assign_field_value('publish_date_and_time', date($month_and_day_format . '/Y g:i A', time() + 3600));

            $liveform->assign_field_value('publish_cancel', '1');
        }

        $liveform->assign_field_value('featured', $featured);
    }
    
    $output_file_attachment = '';
    
    // if there is a file attachment, then output it
    if ($file_name != '') {
        // we are using a separate link for the image and the file name because we don't want an underline on the image and we don't want to have to update all themes with new CSS
        $output_file_attachment =
            '<tr>
                <td>&nbsp;</td>
                <td colspan="2"><a href="' . OUTPUT_PATH . h(encode_url_path($file_name)) . '" target="_blank"><img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_attachment.png" width="16" height="16" alt="attachment" title="" border="0" style="padding-right: .5em; vertical-align: middle" /></a><a href="' . OUTPUT_PATH . h(encode_url_path($file_name)) . '" target="_blank">' . h($file_name) . '</a> (' . convert_bytes_to_string($file_size) . ') &nbsp; ' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'delete_file_attachment', 'id'=>'delete_file_attachment', 'value'=>'1', 'class'=>'checkbox')) . '<label for="delete_file_attachment"> Delete Attachment</label></td>
            </tr>';
    }

    $publish_options = array();
    $publish_options['Published'] = 'published';
    $publish_options['At a Scheduled Time'] = 'schedule';
    $publish_options['Not Published'] = 'not_published';
    
    print
        output_header() . '
        <div id="subnav">
            <h1>Comment</h1>
            <div class="subheading">Added ' . get_relative_time(array('timestamp' => $created_timestamp)) . ' by ' . h($created_username) . '</div>
            <div class="subheading">Page: ' . h(get_page_name($page_id)) . '</div>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Comment</h1>
            <div class="subheading">Edit and publish this comment.</div>
            <br />
            <form name="form" action="edit_comment.php" method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to')) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id')) . '
                <table class="field">
                     <tr>
                        <th colspan="2"><h2>Contributor</h2></th>
                    </tr>
                    <tr>
                        <td>Display Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Comment</h2></th>
                    </tr>
                    <tr>
                        <td>Comment:</td>
                        <td>' . $liveform->output_field(array('type'=>'textarea', 'name'=>'message', 'style'=>'width: 600px; height: 300px')) . '</td>
                    </tr>
                    ' . $output_file_attachment . '
                    <tr>
                        <th colspan="2"><h2>Publish Comment</h2></th>
                    </tr>
                    <tr>
                        <td><label for="published">Publish:</label></td>
                        <td>' .
                            $liveform->output_field(array(
                                'type' => 'select',
                                'id' => 'publish',
                                'name' => 'publish',
                                'options' => $publish_options)) . '

                            <span class="publish_schedule" style="display: none"> ' .
                                $liveform->output_field(array(
                                    'type' => 'text',
                                    'id' => 'publish_date_and_time',
                                    'name' => 'publish_date_and_time',
                                    'maxlength' => '19',
                                    'size' => '20')) . ' &nbsp; ' .

                                $liveform->output_field(array(
                                    'type' => 'checkbox',
                                    'name' => 'publish_cancel',
                                    'id' => 'publish_cancel',
                                    'value' => '1',
                                    'class' => 'checkbox')) .
                                '<label for="publish_cancel"> Cancel if a new comment is added first.</label>
                            </span>

                            <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>
                            ' . get_date_picker_format() . '
                            <script>init_edit_comment_publish()</script>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Highlight Featured Comment</h2></th>
                    </tr>
                    <tr>
                        <td><label for="featured">Featured:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'featured', 'id'=>'featured', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This comment will be permanently deleted.\')" />
                </div>
               
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $bookmark = '';
    
    // if comment was selected for deletion, then delete the comment
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        // if there is a file attachment, then check if we should delete it
        if ($file_name != '') {
            // check if the file attachment is used by another comment (multiple comments can share the same file attachment when pages are duplicated)
            $query = "SELECT id FROM comments WHERE (file_id = '$file_id') AND (id != '" . escape($liveform->get_field_value('id')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the file attachment is not used by another comment, then delete the file
            if (mysqli_num_rows($result) == 0) {
                // delete file from database
                $query = "DELETE FROM files WHERE id = '$file_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete file on file system
                @unlink(FILE_DIRECTORY_PATH . '/' . $file_name);
                
                // log that the file was deleted
                log_activity('file attachment (' . $file_name . ') was deleted because a comment on page (' . get_page_name($page_id) . ') was deleted', $_SESSION['sessionusername']);
            }
        }
        
        // delete comment
        $query = "DELETE FROM comments WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('comment on page (' . get_page_name($page_id) . ') was deleted', $_SESSION['sessionusername']);
        
    // else the comment was edited, so update the comment
    } else {
        // validate fields that need to be validated
        $liveform->validate_required_field('message', 'A comment is required.');

        // If the user selected the publish schedule option, then validate those fields.
        if ($liveform->get_field_value('publish') == 'schedule') {
            $liveform->validate_required_field('publish_date_and_time', 'Please select the date &amp; time when you want the comment to be published.');

            // If there is not already an error for the date & time field,
            // and the value is not valid, then add error.
            if (
                ($liveform->check_field_error('publish_date_and_time') == false)
                && (validate_date_and_time($liveform->get_field_value('publish_date_and_time')) == false)
            ) {
                $liveform->mark_error('publish_date_and_time', 'Please enter a valid date &amp; time when you want the comment to be published.');
            }

            // If there is not already an error for the date & time field,
            // and the date & time is in the past, then add error.
            if (
                ($liveform->check_field_error('publish_date_and_time') == false)
                && (prepare_form_data_for_input($liveform->get_field_value('publish_date_and_time'), 'date and time') < date('Y-m-d H:i'))
            ) {
                $liveform->mark_error('publish_date_and_time', 'Sorry, the date &amp; time you entered is in the past. Please enter a future date &amp; time.');
            }
        }
        
        // if there is an error, forward user back to edit comment screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_comment.php?id=' . $liveform->get_field_value('id'));
            exit();
        }
        
        $sql_file_id = "";
        
        // if the file attachment was selected to be deleted and the file still exists, then check if we should delete it
        if (($liveform->get_field_value('delete_file_attachment') == 1) && ($file_name != '')) {
            // prepare SQL to clear file id for comment
            $sql_file_id = "file_id = '0',";
            
            // check if the file attachment is used by another comment (multiple comments can share the same file attachment when pages are duplicated)
            $query = "SELECT id FROM comments WHERE (file_id = '$file_id') AND (id != '" . escape($liveform->get_field_value('id')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the file attachment is not used by another comment, then delete the file
            if (mysqli_num_rows($result) == 0) {
                // delete file from database
                $query = "DELETE FROM files WHERE id = '$file_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // delete file on file system
                @unlink(FILE_DIRECTORY_PATH . '/' . $file_name);
                
                // log that the file was deleted
                log_activity('file attachment (' . $file_name . ') for a comment on page (' . get_page_name($page_id) . ') was deleted', $_SESSION['sessionusername']);
            }
        }

        $new_published = '';
        $new_publish_date_and_time = '';
        $new_publish_cancel = '';

        switch ($liveform->get_field_value('publish')) {
            case 'published':
                $new_published = '1';
                break;
            
            case 'schedule':
                $new_publish_date_and_time = prepare_form_data_for_input($liveform->get_field_value('publish_date_and_time'), 'date and time');

                if ($liveform->get_field_value('publish_cancel')) {
                    $new_publish_cancel = '1';
                }

                break;
        }
        
        // update comment
        $query =
            "UPDATE comments
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                message = '" . escape($liveform->get_field_value('message')) . "',
                $sql_file_id
                published = '" . $new_published . "',
                publish_date_and_time = '" . e($new_publish_date_and_time) . "',
                publish_cancel = '" . $new_publish_cancel . "',
                featured = '" . escape($liveform->get_field_value('featured')) . "',
                last_modified_user_id = '" . escape($user['id']) . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('comment on page (' . get_page_name($page_id) . ') was modified', $_SESSION['sessionusername']);
        
        // if the comment was just published,
        // and if it was not published before,
        // and if the page is a form item view,
        // and if there is a page selected to send
        // then send e-mail to custom form submitter letting him/her know a comment has been added
        if (($new_published == 1) && ($published == 0) && ($page_type == 'form item view') && ($comments_submitter_email_page_id != 0)) {
            send_comment_email_to_custom_form_submitter($liveform->get_field_value('id'));
        }
        
        // if the comment was just published,
        // and if it was not published before,
        // and if there is a page selected to send
        // then send e-mail to watchers letting them know a comment has been added
        if (($new_published == 1) && ($published == 0) && ($comments_watcher_email_page_id != 0)) {
            send_comment_email_to_watchers($liveform->get_field_value('id'));
        }
        
        // create bookmark for header
        $bookmark = '#c-' . $liveform->get_field_value('id');
    }
    
    // if the page type is form item view, then update the submitted_form_info table so that form list views can show comment info
    if ($page_type == 'form item view') {
        // Get the number of views so we do not lose that data when we delete record below.
        $number_of_views = db_value("SELECT number_of_views FROM submitted_form_info WHERE (submitted_form_id = '" . escape($item_id) . "') AND (page_id = '" . escape($page_id) . "')");

        // get the number of published comments for the submitted form and page
        $query =
            "SELECT COUNT(*)
            FROM comments
            WHERE
                (page_id = '" . escape($page_id) . "')
                AND (item_id = '" . escape($item_id) . "')
                AND (item_type = '" . escape($item_type) . "')
                AND (published = '1')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_comments = $row[0];

        $newest_comment_id = '';
        
        // if there is at least one comment, then get the newest comment id
        if ($number_of_comments > 0) {
            $query =
                "SELECT id
                FROM comments
                WHERE
                    (page_id = '" . escape($page_id) . "')
                    AND (item_id = '" . escape($item_id) . "')
                    AND (item_type = '" . escape($item_type) . "')
                    AND (published = '1')
                ORDER BY created_timestamp DESC
                LIMIT 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $newest_comment_id = $row['id'];
        }
        
        // delete the current record if one exists
        $query = "DELETE FROM submitted_form_info WHERE (submitted_form_id = '" . escape($item_id) . "') AND (page_id = '" . escape($page_id) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $query = 
            "INSERT INTO submitted_form_info (
                submitted_form_id,
                page_id,
                number_of_views,
                number_of_comments,
                newest_comment_id)
             VALUES (
                '" . escape($item_id) . "',
                '" . escape($page_id) . "',
                '$number_of_views',
                '$number_of_comments',
                '$newest_comment_id')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
    
    // if there is a send to, then forward user to send to
    if ($liveform->get_field_value('send_to') != '') {
        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to') . $bookmark);
        
    // else there is not a send to, so build the return URL
    } else {
        $query_string = get_query_string_for_page_url($page_type, $item_id, $item_type);

        // If this is the first item that is being added to the query string, then add question mark.
        if (mb_strpos($query_string, '?') === false) {
            $query_string .= '?';
            
        // Otherwise this is not the first item that is being added to the query string, so add ampersand.
        } else {
            $query_string .= '&';
        }

        // Add comments parameter now.
        $query_string .= 'comments=all';
        // if there is a send to set, then forward user to send to
        if ($_POST['send_to'] != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . get_page_name($page_id) . $query_string . $bookmark);
            
        // else there is not a send to set, so forward user to view products screen.
        } else {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_comments.php');
        }
       
    }
    
    $liveform->remove_form();
}
?>
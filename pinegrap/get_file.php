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

// We do not include init.php in this script, because we want this script to be as fast as possible.
// We were originally including init.php which includes the large functions.php file,
// which caused multi-second delays when there were a lot of embedded images

// Get config settings.
$query =
    "SELECT
        url_scheme,
        hostname,
        timezone
    FROM config";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
define('URL_SCHEME', $row['url_scheme']);
define('HOSTNAME_SETTING', $row['hostname']);
define('TIMEZONE', $row['timezone']);

// If this request was made over the web instead of a cron job,
// then deal with forcing secure mode and starting session.
if ($_SERVER['HTTP_HOST'] != '') {
    // Determine if request is secure or not.  We will use this in a couple of places below.
    $secure_request = check_if_request_is_secure();

    // If secure mode is enabled, and the visitor is not in secure mode,
    // and REQUIRE_SECURE_MODE has not been disabled in the config.php file,
    // then don't complete this request for the visitor.  REQUIRE_SECURE_MODE
    // is a constant that we added support for during the v8.6 release, so if
    // forcing secure mode like we are now in v8.6+, creates problems, then we have an easy way
    // for people to disable it without having to release a patch.
    if (
        (URL_SCHEME == 'https://')
        && ($secure_request == false)
        && 
        (
            (defined('REQUIRE_SECURE_MODE') == false)
            || (REQUIRE_SECURE_MODE !== false)
        )
        && ($_GET['secure_mode'] != 'false')
    ) {
        // If the visitor sent a post request (i.e. submitted a form),
        // then output error because we don't want to allow people
        // to do that insecurely.  With post requests, we don't have any way
        // of redirecting the request to a secure request, plus we don't want
        // to encourage this anyway.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            output_error('Sorry, this website does not allow insecure requests. Please submit the form to a secure address (i.e. "https" instead of "http").', 403);

        // Otherwise the visitor sent a get request, so redirect visitor to secure URL.
        // We use a 301 redirect so that search engines and etc. will use the secure URL.
        } else {
            initialize_url_constants();
            header('Location: https://' . HOSTNAME_SETTING . get_request_uri(), true, 301);
            exit();
        }

    // Otherwise if the request is secure, and the secure_mode query string value is set to false,
    // then redirect visitor to non-secure URL.
    } else if (
        ($secure_request == true)
        && ($_GET['secure_mode'] == 'false')
    ) {
        header('Location: http://' . HOSTNAME_SETTING . get_request_uri(), true, 301);
        exit();
    }

    // If secure mode is enabled, then setup the sesson cookie
    // so that it is only sent in secure mode.  We do this so that if a visitor
    // accidentally requests an insecure URL, then their session id is not sent in clear text
    // which would allow their session to be hijacked.
    if (URL_SCHEME == 'https://') {
        ini_set('session.cookie_secure', true);
    }

    // If PHP version is greater or equal to 5.2.0 then
    // set the session cookie so that it is not available through JavaScript.
    // This prevents various hacking methods.
    if (version_compare(PHP_VERSION, '5.2.0', '>=') == TRUE) {
        ini_set('session.cookie_httponly', true);
    }

    // We purposely start the session down here below the secure mode requirement
    // so that a session is not started if a visitor accidentally requests an insecure URL.
    // This prevents session hijacking.
    session_start();
}

$file = array();
$file['name'] = $_GET['name'];

// Check to see if file exists in database and get file info.
// We get the official name of the file, even though we already have the name
// so that we have the name with the proper case so further below
// when we check if the file exists on the file system, that check will be successful
// on case-sensitive operating systems (e.g. Unix).  This is necessary,
// because the visitor might not request the file with the correct case.
$query =
    "SELECT
        id,
        name,
        folder AS folder_id,
        attachment
    FROM files
    WHERE name = '" . escape($file['name']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if the file does not exist, then output error
if (mysqli_num_rows($result) == 0) {
    output_error('Sorry, the file that you requested does not exist. It might have recently been deleted or the address might be incorrect.', 404);
}

$row = mysqli_fetch_assoc($result);

$file['id'] = $row['id'];
$file['name'] = $row['name'];
$file['folder_id'] = $row['folder_id'];
$file['attachment'] = $row['attachment'];

// If a file directory path is not set, then set it to the default which is a path
// inside the software directory. A custom file directory path is used when
// an adminstrator wants the file directory to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('FILE_DIRECTORY_PATH') == false) {
    define('FILE_DIRECTORY_PATH', dirname(__FILE__) . '/files');
}

// if the file does not exist on the file system, then output error
if (file_exists(FILE_DIRECTORY_PATH . '/' . $file['name']) == FALSE) {
    output_error('Sorry, a record of the file exists in the database, but the actual file does not exist on the file system.  The administrator should restore the file on the file system or delete the file in the control panel and re-upload the file.', 404);
}

$access_control_type = get_access_control_type($file['folder_id']);

// if the access control type is not public, then do access control checks
if (
    ($access_control_type !== 'public')
    && ($access_control_type !== '')
) {
    initialize_user();

    // do different things depending on access control type of file
    switch ($access_control_type) {
        case 'private':
            // if the user is logged in, then check if the user has access to this private file
            if (USER_LOGGED_IN == TRUE) {
                $access_check = check_private_access($file['folder_id']);

                // If the user's private access to this folder has expired, then log activity and output error.
                if ($access_check['expired'] == true) {
                    log_activity('access denied to private file (' . $file['name'] . ') because user\'s access had expired', $_SESSION['sessionusername']);

                    output_error('Sorry, you do not have access to view this file because your access has expired.', 403);

                // Otherwise if the user just doesn't have private access to this folder, then log activity and output error.
                } else if ($access_check['access'] == false) {
                    log_activity('access denied to private file (' . $file['name'] . ')', $_SESSION['sessionusername']);

                    output_error('Sorry, you do not have access to view this file.', 403);
                }

            // else the user is not logged in, so forward user to login screen
            } else {
                initialize_url_constants();

                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/index.php?send_to=' . urlencode(get_request_uri()));
                exit();
            }

            break;

        case 'guest':
            // if the user is not logged in and the user has not selected to be a guest,
            // then forward user to registration entrance screen with guest option
            if (
                (USER_LOGGED_IN == FALSE)
                && ($_SESSION['software']['guest'] !== TRUE)
            ) {
                initialize_url_constants();

                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?allow_guest=true&send_to=' . urlencode(get_request_uri()));
                exit();
            }

            break;

        case 'registration':
            // if the user is not logged in, then forward user to registration entrance screen
            if (USER_LOGGED_IN == FALSE) {
                initialize_url_constants();

                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
                exit();
            }

            break;

        case 'membership':
            // if the user is not logged in, the forward user to membership entrance screen
            if (USER_LOGGED_IN == FALSE) {
                initialize_url_constants();

                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php?send_to=' . urlencode(get_request_uri()));
                exit();
            }

            // if the user does not have edit rights to this file's folder,
            // then validate membership
            if (check_edit_access($file['folder_id']) == false) {
                // if the user is not a member, then output error
                if (USER_MEMBER_ID == '') {
                    // get member ID label for error message
                    $query = "SELECT member_id_label FROM config";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $member_id_label = $row['member_id_label'];

                    output_error('Sorry, the file that you requested requires membership. Your ' . $member_id_label . ' could not be found. You can view your membership status in your account area. Please contact us for more information.', 403);
                }

                $expiration_date = USER_EXPIRATION_DATE;

                // if expiration date is blank, then set the expiration date to the highest possible value for lifetime membership
                if (
                    ($expiration_date == '')
                    || ($expiration_date == '0000-00-00')
                ) {
                    $expiration_date = '9999-99-99';
                }

                initialize_timezone();

                // if the user's membership has expired, then output error
                if ($expiration_date < date('Y-m-d')) {
                    output_error('Sorry, the file that you requested requires membership. Your membership could not be verified.  You can view your membership status in your account area. Please contact us for more information.', 403);
                }
            }
            
            break;
    }
}

// If the file is a comment or submitted form attachment
// then check if visitor has access to attachment.
if ($file['attachment'] == 1) {
    // Assume that visitor does not have access to attachment
    // until we find out otherwise.
    $attachment_access = false;

    // If the user has not already been initialized in access control check above, then init user.
    if (defined('USER_LOGGED_IN') == false) {
        initialize_user();
    }

    // If the visitor has edit access to the attachment's folder, then grant access.
    if (check_edit_access($file['folder_id']) == true) {
        $attachment_access = true;

    // Otherwise we need to check if visitor has access to
    // the item that the attachment is attached to.
    } else {
        // If the file is a comment attachment, then check access in a certain way.
        // An attachment can belong to multiple comments if a page is duplicated.
        if (
            $comments = db_items(
                "SELECT
                    comments.page_id,
                    comments.item_id,
                    comments.item_type,
                    comments.published,
                    page.page_folder AS folder_id,
                    page.page_type AS page_type,
                    page.comments AS page_comments
                FROM comments
                LEFT JOIN page ON comments.page_id = page.page_id
                WHERE comments.file_id = '" . $file['id'] . "'")
        ) {
            // Loop through the comments to see if visitor has access to any of them.
            // We only require that visitor have access to view one of the comments
            // for this attachment.  Visitor does not have to have access to view
            // all comments on various pages for this attachment.
            foreach ($comments as $comment) {
                // If the visitor has edit access to the comment, then grant access,
                // and break out of loop because we now know the visitor has access,
                // so we don't need to check the other comments.
                if (check_edit_access($comment['folder_id']) == true) {
                    $attachment_access = true;
                    break;

                // Otherwise the visitor does not have edit access to the comment,
                // so check if visitor has view access.
                } else {
                    // If comments are still enabled for the page,
                    // and the comment is published,
                    // and the user has view access to this page,
                    // then continue to check if user has access.
                    if (
                        ($comment['page_comments'] == 1)
                        && ($comment['published'] == 1)
                        && (check_view_access($comment['folder_id']) == true)
                    ) {
                        // If the page is a form item view,
                        // and the comment is for a specific submitted form,
                        // then determine if we need to check submitter security,
                        // in order to determine if visitor has view access.
                        if (
                            ($comment['page_type'] == 'form item view')
                            && ($comment['item_type'] == 'submitted_form')
                        ) {
                            // Get various info for form item view.
                            $form_item_view = db_item(
                                "SELECT
                                    custom_form_page_id,
                                    submitter_security
                                FROM form_item_view_pages
                                WHERE
                                    (page_id = '" . $comment['page_id'] . "')
                                    AND (collection = 'a')");

                            // If submitter security is enabled for the form item view,
                            // then check if the viewer is authorized to view the
                            // submitted form that this comment belongs to.
                            if ($form_item_view['submitter_security'] == 1) {
                                // If this visitor is logged in, then continue to check if visitor has view access.
                                if (USER_LOGGED_IN == true) {
                                    // Get various info for the submitted form.
                                    $submitted_form = db_item(
                                        "SELECT
                                            forms.id,
                                            forms.form_editor_user_id,
                                            forms.user_id AS submitter_user_id,
                                            page.page_folder AS custom_form_folder_id
                                        FROM forms
                                        LEFT JOIN page ON forms.page_id = page.page_id
                                        WHERE forms.id = '" . $comment['item_id'] . "'");

                                    // If this user has edit access to the custom form,
                                    // or is the form editor for this submitted form,
                                    // or is the submitter by direct user id connection,
                                    // then the user has view access.
                                    if (
                                        (check_edit_access($submitted_form['custom_form_folder_id']) == true)
                                        || (USER_ID == $submitted_form['form_editor_user_id'])
                                        || (USER_ID == $submitted_form['submitter_user_id'])
                                    ) {
                                        $attachment_access = true;
                                        break;

                                    // Otherwise this user does not have access through those ways
                                    // so check if user has access in other ways.
                                    } else {
                                        $submitter_email_address = db_value(
                                            "SELECT form_data.data
                                            FROM form_data
                                            LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                                            WHERE
                                                (form_data.form_id = '" . $submitted_form['id'] . "')
                                                AND (form_fields.contact_field = 'email_address')");

                                        // If this user has the same e-mail address as the connect-to-contact e-mail address
                                        // field value for this submitted form, then the user has view access.
                                        if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                                            $attachment_access = true;
                                            break;

                                        // Otherwise this user is not the submitter, but if they are a watcher,
                                        // then grant access and break out of loop.
                                        } else if (
                                            db_value(
                                                "SELECT COUNT(*)
                                                FROM watchers
                                                WHERE
                                                    (
                                                        (user_id = '" . USER_ID . "')
                                                        OR (email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                                    )
                                                    AND (page_id = '" . $comment['page_id'] . "')
                                                    AND (item_id = '" . $submitted_form['id'] . "')
                                                    AND (item_type = 'submitted_form')")
                                            > 0
                                        ) {
                                            $attachment_access = true;
                                            break;
                                        }
                                    }
                                }

                            // Otherwise submitter security is disabled,
                            // so that means the visitor has access,
                            // so break out of loop through comments.
                            } else {
                                $attachment_access = true;
                                break;
                            }

                        // Otherwise the page is not a form item view,
                        // so we know the visitor has view access, so grant access,
                        // and break out of loop through comments.
                        } else {
                            $attachment_access = true;
                            break;
                        }
                    }
                }
            }

        // Otherwise if the file is a submitted form attachment
        // (which it should always be if it is not a comment attachment),
        // then check access in a different way.  Unlike comment attachments,
        // submitted form attachments only belong to one submitted form.
        } else if (
            $submitted_form = db_item(
                "SELECT
                    page.page_id AS custom_form_page_id,
                    page.page_folder AS custom_form_folder_id,
                    form_fields.name AS attachment_field_name,
                    forms.id,
                    forms.user_id AS submitter_user_id,
                    forms.form_editor_user_id
                FROM form_data
                LEFT JOIN forms ON form_data.form_id = forms.id
                LEFT JOIN page ON forms.page_id = page.page_id
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                WHERE form_data.file_id = '" . $file['id'] . "'")
        ) {
            // If the user has access to edit the submitted form, via the forms tab,
            // then grant access to visitor to view attachment.
            if (
                (USER_LOGGED_IN == true)
                && (USER_MANAGE_FORMS == true)
                && (check_edit_access($submitted_form['custom_form_folder_id']) == true)
            ) {
                $attachment_access = true;

            // Otherwise the user does not have access to edit submitted form,
            // so check if the user has view access to a form list view or form item view,
            // that shows the attachment.
            } else {
                // Get all form list views that might show the attachment.
                $form_list_views = db_items(
                    "SELECT
                        form_list_view_pages.layout,
                        form_list_view_pages.form_item_view_page_id,
                        form_list_view_pages.viewer_filter,
                        form_list_view_pages.viewer_filter_submitter,
                        form_list_view_pages.viewer_filter_watcher,
                        form_list_view_pages.viewer_filter_editor,
                        page.page_folder AS folder_id
                    FROM page
                    LEFT JOIN form_list_view_pages ON page.page_id = form_list_view_pages.page_id
                    WHERE
                        (page.page_type = 'form list view')
                        AND (form_list_view_pages.custom_form_page_id = '" . $submitted_form['custom_form_page_id'] . "')");

                // Loop through the form list views in order to determine
                // if they show the attachment.
                foreach ($form_list_views as $form_list_view) {
                    // If the layout contains a variable for the attachment field,
                    // and the visitor has view access to the form list view,
                    // then continue to check if the visitor should have access to
                    // view attachment.
                    if (
                        (preg_match('/\^\^' . preg_quote($submitted_form['attachment_field_name'], '/') . '\^\^/i', $form_list_view['layout']) != 0)
                        && (check_view_access($form_list_view['folder_id']) == true)
                    ) {
                        // If there is a viewer filter for the form list view
                        // then determine if visitor has access to the submitted form
                        // that the attachment belongs to.
                        if ($form_list_view['viewer_filter'] == 1) {
                            // If the visitor is logged in, then continue to check if he/she has access.
                            if (USER_LOGGED_IN == true) {
                                // If the editor filter is enabled, and this visitor is the form editor,
                                // then grant access and break out of loop.
                                if (
                                    ($form_list_view['viewer_filter_editor'] == 1)
                                    && (USER_ID == $submitted_form['form_editor_user_id'])
                                ) {
                                    $attachment_access = true;
                                    break;
                                }

                                // If the submitter filter is enabled, then check if visitor is the submitter.
                                if ($form_list_view['viewer_filter_submitter'] == 1) {
                                    // If the visitor is the direct submitter of the form, then grant access,
                                    // and break out of loop.
                                    if (USER_ID == $submitted_form['submitter_user_id']) {
                                        $attachment_access = true;
                                        break;
                                    }

                                    $submitter_email_address = db_value(
                                        "SELECT form_data.data
                                        FROM form_data
                                        LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                                        WHERE
                                            (form_data.form_id = '" . $submitted_form['id'] . "')
                                            AND (form_fields.contact_field = 'email_address')");

                                    // If this user has the same e-mail address as the connect-to-contact e-mail address
                                    // field value for this submitted form, then the user has view access.
                                    if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                                        $attachment_access = true;
                                        break;
                                    }
                                }

                                // If the watcher filter is enabled, then check if visitor is a watcher.
                                if ($form_list_view['viewer_filter_watcher'] == 1) {
                                    if (
                                        db_value(
                                            "SELECT COUNT(*)
                                            FROM watchers
                                            WHERE
                                                (
                                                    (user_id = '" . USER_ID . "')
                                                    OR (email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                                )
                                                AND (page_id = '" . $form_list_view['form_item_view_page_id'] . "')
                                                AND (item_id = '" . $submitted_form['id'] . "')
                                                AND (item_type = 'submitted_form')")
                                        > 0
                                    ) {
                                        $attachment_access = true;
                                        break;
                                    }
                                }
                            }

                        // Otherwise there is no viewer filter, so that means the visitor has access
                        // to the attachment, so remember that and break out of loop.
                        } else {
                            $attachment_access = true;
                            break;
                        }

                    // Otherwise the layout of the form list view does not contain
                    // a variable for the attachment, or the visitor does not have access
                    // to view the form list view, so if there is a form item view,
                    // then if the user would have access to the attachment via that page.
                    } else if ($form_list_view['form_item_view_page_id']) {
                        // Get various info for form item view.
                        $form_item_view = db_item(
                            "SELECT
                                form_item_view_pages.page_id,
                                form_item_view_pages.layout,
                                form_item_view_pages.submitter_security,
                                page.page_folder AS folder_id
                            FROM form_item_view_pages
                            LEFT JOIN page ON form_item_view_pages.page_id = page.page_id
                            WHERE
                                (form_item_view_pages.page_id = '" . $form_list_view['form_item_view_page_id'] . "')
                                AND (form_item_view_pages.collection = 'a')");

                        // If the layout contains a variable for the attachment field,
                        // and the visitor has view access to the form item view,
                        // then continue to check if the visitor should have access to
                        // view attachment.
                        if (
                            (preg_match('/\^\^' . preg_quote($submitted_form['attachment_field_name'], '/') . '\^\^/i', $form_item_view['layout']) != 0)
                            && (check_view_access($form_item_view['folder_id']) == true)
                        ) {
                            // If submitter security is enabled for the form item view,
                            // then check if the viewer is authorized to view the
                            // submitted form for the attachment.
                            if ($form_item_view['submitter_security'] == 1) {
                                // If this visitor is logged in, then continue to check if visitor has view access.
                                if (USER_LOGGED_IN == true) {
                                    // If the user is the form editor for this submitted form,
                                    // or is the submitter by direct user id connection,
                                    // then the user has view access.
                                    if (
                                        (USER_ID == $submitted_form['form_editor_user_id'])
                                        || (USER_ID == $submitted_form['submitter_user_id'])
                                    ) {
                                        $attachment_access = true;
                                        break;

                                    // Otherwise this user does not have access through those ways
                                    // so check if user has access in other ways.
                                    } else {
                                        $submitter_email_address = db_value(
                                            "SELECT form_data.data
                                            FROM form_data
                                            LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                                            WHERE
                                                (form_data.form_id = '" . $submitted_form['id'] . "')
                                                AND (form_fields.contact_field = 'email_address')");

                                        // If this user has the same e-mail address as the connect-to-contact e-mail address
                                        // field value for this submitted form, then the user has view access.
                                        if (mb_strtolower(USER_EMAIL_ADDRESS) == mb_strtolower($submitter_email_address)) {
                                            $attachment_access = true;
                                            break;

                                        // Otherwise this user is not the submitter, but if they are a watcher,
                                        // then grant access and break out of loop.
                                        } else if (
                                            db_value(
                                                "SELECT COUNT(*)
                                                FROM watchers
                                                WHERE
                                                    (
                                                        (user_id = '" . USER_ID . "')
                                                        OR (email_address = '" . escape(USER_EMAIL_ADDRESS) . "')
                                                    )
                                                    AND (page_id = '" . $form_item_view['page_id'] . "')
                                                    AND (item_id = '" . $submitted_form['id'] . "')
                                                    AND (item_type = 'submitted_form')")
                                            > 0
                                        ) {
                                            $attachment_access = true;
                                            break;
                                        }
                                    }
                                }

                            // Otherwise submitter security is disabled,
                            // so that means the visitor has access,
                            // so break out of loop through form list views.
                            } else {
                                $attachment_access = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // If the visitor does not have access to the attachment, then deal with that.
    if ($attachment_access == false) {
        // If the visitor is not logged in, then forward visitor to login/register screen.
        if (USER_LOGGED_IN == false) {
            initialize_url_constants();

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
            exit();

        // Otherwise the visitor is logged in, so we know the visitor
        // should not have access, so log activity and output error.
        } else {
            log_activity('access denied to file attachment (' . $file['name'] . ')', $_SESSION['sessionusername']);

            output_error('Sorry, you do not have access to view this file attachment.', 403);
        }
    }
}

// If this is a private file, then log access so admins can keep track of users accessing private
// content.  We purposely log down below the file attachment access check above, in case the user
// did not have access to the attachment and so did not actually view the file.

if ($access_control_type == 'private') {
    log_activity(
        'User viewed private file.' . "\n" .
        "\n" .
        URL_SCHEME . HOSTNAME_SETTING . get_request_uri(),
        $_SESSION['sessionusername']);
}

// We are using strrchr instead of mb_strrchr because mb_strrchr requires PHP 5.2,
// and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).

$extension = mb_substr(strrchr($file['name'], '.'), 1);
$extension = mb_strtolower($extension);

$mimetype = array(
    'ez'=> 'application/andrew-inset',
    'hqx'=> 'application/mac-binhex40',
    'cpt'=> 'application/mac-compactpro',
    'doc'=> 'application/msword',
    'bin'=> 'application/octet-stream',
    'dms'=> 'application/octet-stream',
    'lha'=> 'application/octet-stream',
    'lzh'=> 'application/octet-stream',
    'exe'=> 'application/octet-stream',
    'class'=> 'application/octet-stream',
    'so'=> 'application/octet-stream',
    'dll'=> 'application/octet-stream',
    'oda'=> 'application/oda',
    'pdf'=> 'application/pdf',
    'ai'=> 'application/postscript',
    'eps'=> 'application/postscript',
    'ps'=> 'application/postscript',
    'smi'=> 'application/smil',
    'smil'=> 'application/smil',
    'mif'=> 'application/vnd.mif',
    'xls'=> 'application/vnd.ms-excel',
    'ppt'=> 'application/vnd.ms-powerpoint',
    'wbxml'=> 'application/vnd.wap.wbxml',
    'wmlc'=> 'application/vnd.wap.wmlc',
    'wmlsc'=> 'application/vnd.wap.wmlscriptc',
    'bcpio'=> 'application/x-bcpio',
    'vcd'=> 'application/x-cdlink',
    'pgn'=> 'application/x-chess-pgn',
    'cpio'=> 'application/x-cpio',
    'csh'=> 'application/x-csh',
    'dcr'=> 'application/x-director',
    'dir'=> 'application/x-director',
    'dxr'=> 'application/x-director',
    'dvi'=> 'application/x-dvi',
    'spl'=> 'application/x-futuresplash',
    'gtar'=> 'application/x-gtar',
    'hdf'=> 'application/x-hdf',
    'js'=> 'application/x-javascript',
    'skp'=> 'application/x-koan',
    'skd'=> 'application/x-koan',
    'skt'=> 'application/x-koan',
    'skm'=> 'application/x-koan',
    'latex'=> 'application/x-latex',
    'nc'=> 'application/x-netcdf',
    'cdf'=> 'application/x-netcdf',
    'sh'=> 'application/x-sh',
    'shar'=> 'application/x-shar',
    'swf'=> 'application/x-shockwave-flash',
    'sit'=> 'application/x-stuffit',
    'sv4cpio'=> 'application/x-sv4cpio',
    'sv4crc'=> 'application/x-sv4crc',
    'tar'=> 'application/x-tar',
    'tcl'=> 'application/x-tcl',
    'tex'=> 'application/x-tex',
    'texinfo'=> 'application/x-texinfo',
    'texi'=> 'application/x-texinfo',
    't'=> 'application/x-troff',
    'tr'=> 'application/x-troff',
    'roff'=> 'application/x-troff',
    'man'=> 'application/x-troff-man',
    'me'=> 'application/x-troff-me',
    'ms'=> 'application/x-troff-ms',
    'ustar'=> 'application/x-ustar',
    'src'=> 'application/x-wais-source',
    'xhtml'=> 'application/xhtml+xml',
    'xht'=> 'application/xhtml+xml',
    'zip'=> 'application/zip',
    'au'=> 'audio/basic',
    'snd'=> 'audio/basic',
    'mid'=> 'audio/midi',
    'midi'=> 'audio/midi',
    'kar'=> 'audio/midi',
    'mpga'=> 'audio/mpeg',
    'mp2'=> 'audio/mpeg',
    'mp3'=> 'audio/mpeg',
    'aif'=> 'audio/x-aiff',
    'aiff'=> 'audio/x-aiff',
    'aifc'=> 'audio/x-aiff',
    'm3u'=> 'audio/x-mpegurl',
    'ram'=> 'audio/x-pn-realaudio',
    'rm'=> 'audio/x-pn-realaudio',
    'rpm'=> 'audio/x-pn-realaudio-plugin',
    'ra'=> 'audio/x-realaudio',
    'wav'=> 'audio/x-wav',
    'mp4a'=> 'audio/mp4',
    'm4a'=> 'audio/x-m4a',
    'pdb'=> 'chemical/x-pdb',
    'xyz'=> 'chemical/x-xyz',
    'bmp'=> 'image/bmp',
    'gif'=> 'image/gif',
    'ief'=> 'image/ief',
    'jpeg'=> 'image/jpeg',
    'jpg'=> 'image/jpeg',
    'jpe'=> 'image/jpeg',
    'png'=> 'image/png',
    'tiff'=> 'image/tiff',
    'tif'=> 'image/tiff',
    'djvu'=> 'image/vnd.djvu',
    'djv'=> 'image/vnd.djvu',
    'wbmp'=> 'image/vnd.wap.wbmp',
    'ico'=> 'image/x-icon',
    'ras'=> 'image/x-cmu-raster',
    'pnm'=> 'image/x-portable-anymap',
    'pbm'=> 'image/x-portable-bitmap',
    'pgm'=> 'image/x-portable-graymap',
    'ppm'=> 'image/x-portable-pixmap',
    'rgb'=> 'image/x-rgb',
    'xbm'=> 'image/x-xbitmap',
    'xpm'=> 'image/x-xpixmap',
    'xwd'=> 'image/x-xwindowdump',
    'igs'=> 'model/iges',
    'iges'=> 'model/iges',
    'msh'=> 'model/mesh',
    'mesh'=> 'model/mesh',
    'silo'=> 'model/mesh',
    'wrl'=> 'model/vrml',
    'vrml'=> 'model/vrml',
    'css'=> 'text/css',
    'html'=> 'text/html',
    'htm'=> 'text/html',
    'asc'=> 'text/plain',
    'txt'=> 'text/plain',
    'rtx'=> 'text/richtext',
    'rtf'=> 'text/rtf',
    'sgml'=> 'text/sgml',
    'sgm'=> 'text/sgml',
    'tsv'=> 'text/tab-separated-values',
    'wml'=> 'text/vnd.wap.wml',
    'wmls'=> 'text/vnd.wap.wmlscript',
    'etx'=> 'text/x-setext',
    'xsl'=> 'text/xml',
    'xml'=> 'text/xml',
    'mpeg'=> 'video/mpeg',
    'mpg'=> 'video/mpeg',
    'mpe'=> 'video/mpeg',
    'qt'=> 'video/quicktime',
    'mov'=> 'video/quicktime',
    'mxu'=> 'video/vnd.mpegurl',
    'avi'=> 'video/x-msvideo',
    'movie'=> 'video/x-sgi-movie',
    'asf'=> 'video/x-ms-asf',
    'asx'=> 'video/x-ms-asf',
    'wmv'=> 'video/x-ms-wmv',
    'mp4'=> 'video/mp4',
    'mp4v'=> 'video/mp4',
    'mpg4'=> 'video/mp4',
    'm4v'=> 'video/x-m4v',
    'ice'=> 'x-conference/x-cooltalk'
);

$file['path'] = FILE_DIRECTORY_PATH . '/' . $file['name'];

$if_modified_since = preg_replace( '/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
$last_modified = gmdate('D, d M Y H:i:s', filemtime($file['path'])) . ' GMT';

// if user's cache is current, then output 304 not modified header
if ($if_modified_since == $last_modified) {
    header('HTTP/1.1 304 Not Modified');

// else user's cache is not current, so output last modified, content-type, and content-disposition
} else {
    header('Last-Modified: ' . $last_modified);

    // if file can be displayed in browser, display file in browser
    if (isset($mimetype[$extension]) && ($mimetype[$extension] != 'application/octet-stream')) {
        header('Content-type: ' . $mimetype[$extension]);
        header('Content-disposition: filename=' . $file['name']);
        
    // else file cannot be displayed in browser, so force download dialog
    } else {
        header('Content-type: application/octet-stream');
        header('Content-disposition: attachment; filename=' . $file['name']);
    }
}

// Set expires header so that the file will be cached for 1 week.
// We previously used 1 day but Google PageSpeed Insights prefers 1 week+.
// This is the old header for caching a file.
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT');

$cache_control_type = '';

// get the cache control type differently based on the access control type
switch ($access_control_type) {
    // if the file is public then set the cache control type to public,
    // which means that proxies and browsers should cache it
    case 'public':
    case '':
        $cache_control_type = 'public';
        break;

    // if the file is any other access control type,
    // then set the cache control type to private,
    // which means that proxies should not cache it, but browsers can
    default:
        $cache_control_type = 'private';
        break;
}

// Set the cache-control header so that the file will be cached for 1 week.
// We previously used 1 day but Google PageSpeed Insights prefers 1 week+.
// This is the new header for caching a file.
header('Cache-Control: ' . $cache_control_type . ', max-age=604800');

// Set the pragma cache.  Technically, this header should not be necessary,
// however we use it to make sure that this works in all browsers.
header('Pragma: public');

// if the user's cache is current, we don't need to output the file, so just exit
if ($if_modified_since == $last_modified) {
    exit();

// else if the file is a CSS or JS file, then replace placeholders (e.g. {path}) and then output file
// We added support for this with JS files in v9 so that halcyonic and verti themes would
// work in a sub-directory. halcyonic-config.js and verti-init.js require a path to be set.
} else if (
    ($extension == 'css')
    || ($extension == 'js')
) {
    // get content of the file
    $content = file_get_contents($file['path']);

    // set PATH constant
    initialize_url_constants();
    
    // replace path placeholders
    $content = preg_replace('/{path}/i', PATH, $content);

    // Replace software directory placeholders.
    $content = preg_replace('/{software_directory}/i', SOFTWARE_DIRECTORY, $content);
    
    // We are purposely using strlen instead of mb_strlen,
    // because we want to know the number of bytes and not the number of characters.
    header('Content-length: ' . strlen($content));
    
    echo $content;
    exit();

// else if x-sendfile is enabled then output file through Apache instead of PHP for performance reasons
// We can't use x-sendfile for CSS files because they might contain paths that need to be replaced.
} else if (defined('XSENDFILE') and XSENDFILE) {
    // we have to use an absolute path because x-sendfile assumes relative paths
    // are relative to the virtual files directory
    header('X-Sendfile: ' . FILE_DIRECTORY_PATH . '/' . $file['name']);
    exit();
    
// else just output the file through PHP
} else {
    header('Content-length: ' . filesize($file['path']));
    readfile($file['path']);
    exit();
}

function output_error($error_message, $response_code = 0) {

    // If there is a response code, then output header for that.
    if ($response_code) {

        // If this is PHP 5.4+ which has a function to do response codes, then use that.
        if (function_exists('http_response_code')) {
            http_response_code($response_code);

        // Otherwise create our own code.
        } else {
            switch ($response_code) {
                case 403:
                    header('HTTP/1.1 403 Forbidden');
                    break;

                case 404:
                    header('HTTP/1.1 404 Not Found');
                    break;

                case 410:
                    header('HTTP/1.1 410 Gone');
                    break;
            }
        }
    }

    $output_error_message = $error_message;

    $mysql_error = '';

    if (db::$con) {
        $mysql_error = mysqli_error(db::$con);
    }

    // if there is a MySQL error, then add that to the error message
    if ($mysql_error !== '') {
        $output_error_message .= ' ' . h($mysql_error);
    }

    echo
        '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Error</title>
            </head>
            <body>
                <div style="color: red">Error: ' . $output_error_message . '</div>
            </body>
        </html>';

    exit();
}

// Create function that can be used for general database queries that update or insert.
// It does not return anything.
function db($query) {
    mysqli_query(db::$con, $query) or output_error('Query failed.');
}

// Create function that can be used to query for a single value (e.g. SELECT COUNT(*) FROM page).
function db_value($query) {
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    return $row[0];
}

// Create function that can be used to get an array of values for a single item (e.g. the properties for a single page)
function db_item($query) {
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    return mysqli_fetch_assoc($result);
}

// Create function that can be used to get an array of multiple items (e.g. the properties of many pages)
function db_items($query) {

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $items = array();
    
    while ($item = mysqli_fetch_assoc($result)) {
        $items[] = $item;
    }
    
    return $items;
}

// create function to escape information in database queries
function escape($string) {
    return mysqli_real_escape_string(db::$con, $string);
}

function get_access_control_type($folder_id) {
    $query = "SELECT folder_parent, folder_access_control_type FROM folder WHERE folder_id = '" . escape($folder_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    if ($row['folder_access_control_type']) {
        return $row['folder_access_control_type'];
    }

    // if this folder is the root folder, return public
    if ($row['folder_parent'] == 0) {
        return 'public';
    // else this folder is not the root folder, so use recursion to find access control of folder parent
    } else {
        return get_access_control_type($row['folder_parent']);
    }
}

function log_activity($description, $user)
{
    $query = "INSERT INTO log (log_id, log_description, log_ip, log_user, log_timestamp) "
            ."VALUES ('', '" . escape($description) . "', '" . escape($_SERVER['REMOTE_ADDR']) . "', '" . escape($user) . "', UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // get a random number between 1 and 100 in order to determine if we should delete old log entries
    // there is a 1 in 100 chance that we will delete old log entries each time a log entry is added
    $random_number = rand(1, 100);
    
    // if the random number is 1, then delete old log entries
    // all log entries before 6 months ago are deleted
    if ($random_number == 1) {
        $query = "DELETE FROM log WHERE log_timestamp < (UNIX_TIMESTAMP() - 15552000)";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }
}

// create function to set user information if user is logged in
function initialize_user()
{
    // check if remember me is on
    $query = "SELECT remember_me FROM config";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $remember_me = $row['remember_me'];

    // if remember me is on and there is cookie login information,
    // and there is not session login information,
    // then add cookie login information to session if login info is valid
    if (
        ($remember_me == 1)
        && (isset($_COOKIE['software']['username']) == TRUE)
        && (isset($_COOKIE['software']['password']) == TRUE)
        && (isset($_SESSION['sessionusername']) == FALSE)
    ) {
        // check to see if the login information is valid by trying to find a user
        $query =
            "SELECT
                user.user_id AS id,
                user.user_email AS email_address,
                user.user_role AS role,
                user.user_manage_forms AS manage_forms,
                contacts.member_id AS member_id,
                contacts.expiration_date AS expiration_date
            FROM user
            LEFT JOIN contacts ON user.user_contact = contacts.id
            WHERE
                (user.user_username = '" . escape($_COOKIE['software']['username']) . "')
                AND (user.user_password = '" . escape($_COOKIE['software']['password']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the login information in the cookie is valid,
        // then add login information to session, log activity, and store user info
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['sessionusername'] = $_COOKIE['software']['username'];
            $_SESSION['sessionpassword'] = $_COOKIE['software']['password'];
            
            // create log entry to note that user logged in
            log_activity('user logged in', $_SESSION['sessionusername']);

            $user = mysqli_fetch_assoc($result);
            
        // else the login information in the cookie is not valid, so delete cookie
        } else {
            $current_timestamp = time();

            setcookie('software[username]', '', $current_timestamp - 1000, '/');
            setcookie('software[password]', '', $current_timestamp - 1000, '/');
        }

    // else if there is login info in the session,
    // then check if login info is valid by trying to find a user
    } else if (isset($_SESSION['sessionusername']) == TRUE) {
        $query =
            "SELECT
                user.user_id AS id,
                user.user_email AS email_address,
                user.user_role AS role,
                user.user_manage_forms AS manage_forms,
                contacts.member_id AS member_id,
                contacts.expiration_date AS expiration_date
            FROM user
            LEFT JOIN contacts ON user.user_contact = contacts.id
            WHERE
                (user.user_username = '" . escape($_SESSION['sessionusername']) . "')
                AND (user.user_password = '" . escape($_SESSION['sessionpassword']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if the login is valid, then store user info
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
        }
    }

    // if the user is logged in, then store user info in global constants to be used later
    if (isset($user) == TRUE) {
        define('USER_LOGGED_IN', TRUE);
        define('USER_ID', $user['id']);
        define('USER_EMAIL_ADDRESS', $user['email_address']);
        define('USER_ROLE', $user['role']);
        define('USER_MEMBER_ID', $user['member_id']);
        define('USER_EXPIRATION_DATE', $user['expiration_date']);

        if ((USER_ROLE < 3) || ($user['manage_forms'] == 'yes')) {
            define('USER_MANAGE_FORMS', true);
        } else {
            define('USER_MANAGE_FORMS', false);
        }

    // else the user is not logged in, so store that
    } else {
        define('USER_LOGGED_IN', FALSE);
        define('USER_ID', '');
    }
}

// Create function that will check if a visitor has view access to a folder.
// This does not just check private access.  It checks for all types of access control.
// Since any visitor can get access to registration or guest content by registering or choosing to be a guest,
// you can set $always_grant_access_for_registration_and_guest to true which will grant access
// regardless of whether the visitor is logged in or not.
function check_view_access($folder_id, $always_grant_access_for_registration_and_guest = false)
{
    // If the user is logged in and the user is an administrator, designer, or manager,
    // then they have view access to all folders, so just grant access.
    if (USER_LOGGED_IN && (USER_ROLE < 3)) {
        return true;
    }

    // Assume that visitor does not have access until we find out otherwise.
    $access = false;

    // Check if visitor has access differently based on the access control type of the folder.
    switch (get_access_control_type($folder_id)) {
        case 'public':
            $access = true;
            break;
        
        case 'private':
            $access_check = check_private_access($folder_id);

            // If the visitor has private access to this folder, then visitor has access.
            if ($access_check['access'] == true) {
                $access = true;
            }
            
            break;
            
        case 'guest':
            // If the visitor should always be granted access for guest access control,
            // or if the visitor has logged in, or if the visitor has selected to be a guest,
            // then the visitor has access.
            if (
                ($always_grant_access_for_registration_and_guest == true)
                || (USER_LOGGED_IN == true)
                || ($_SESSION['software']['guest'] == true)
            ) {
                $access = true;
            }
            
            break;

        case 'registration':
            // If the visitor should always be granted access for registration access control,
            // or if the visitor has logged in, then the visitor has access.
            if (
                ($always_grant_access_for_registration_and_guest == true)
                || (USER_LOGGED_IN == true)
            ) {
                $access = true;
            }
            
            break;

        case 'membership':
            // If the visitor is logged in and is a member
            // or has edit access, then the visitor has access.
            if (
                (USER_LOGGED_IN == true)
                &&
                (
                    (USER_MEMBER == true)
                    || (check_edit_access($folder_id) == true)
                )
            ) {
                $access = true;
            }
            
            break;
    }

    return $access;
}

// Create function in order to check if a visitor has edit access to a folder.
// It returns true or false.
function check_edit_access($folder_id)
{
    // Assume that the visitor does not have edit access until we find out otherwise.
    $access = false;

    // If the visitor is logged in, then continue to check if the visitor has edit access.
    if (USER_LOGGED_IN == true) {
        // If the user is a manager or above, then the user has edit access.
        if (USER_ROLE < 3) {
            $access = true;

        // Otherwise the user has a user role, so continue to check if user has edit access.
        } else {
            // Determine what type of access user has to folder.
            $row = db_item(
                "SELECT
                    aclfolder.aclfolder_rights AS rights,
                    folder.folder_parent AS parent_folder_id
                FROM aclfolder
                LEFT JOIN folder ON aclfolder.aclfolder_folder = folder.folder_id
                WHERE
                    (aclfolder.aclfolder_user = '" . USER_ID . "')
                    AND (aclfolder.aclfolder_folder = '" . escape($folder_id) . "')");

            $rights = $row['rights'];
            $parent_folder_id = $row['parent_folder_id'];

            // If this user has edit rights to this folder, then remember that.
            if ($rights == 2) {
                $access = true;

            // Otherwise we do not know if access has been granted, so if this is not the root folder
            // then use recursion to check for access in parent folder.
            } else {
                // If the parent folder has not been found yet, then get it.
                if ($parent_folder_id == '') {
                    $parent_folder_id = db_value("SELECT folder_parent AS parent_folder_id FROM folder WHERE folder_id = '" . escape($folder_id) . "'");
                }

                // If this is not the root folder, then use recursion to check parent folder for access.
                if ($parent_folder_id != 0) {
                    $access = check_edit_access($parent_folder_id);
                }
            }
        }
    }

    return $access;
}

// Create function in order to check if a visitor has access to a private folder.
// This function returns an array with two properties: "access" (set to true
// if the user has access and false if user does not have access) and "expired"
// (set to true if the access has expired and false otherwise).
// Visitors with edit rights to a folder also have private access to that folder.
function check_private_access($folder_id)
{
    $result = array();

    // Assume that the visitor does not have access and access has not expired until we find out otherwise.
    $result['access'] = false;
    $result['expired'] = false;

    // If the visitor is logged in, then continue to check if the visitor has private access.
    if (USER_LOGGED_IN == true) {
        // If the user is a manager or above, then the user has private access.
        if (USER_ROLE < 3) {
            $result['access'] = true;

        // Otherwise the user has a user role, so continue to check if user has private access.
        } else {
            // Determine what type of access user has to folder.
            $row = db_item(
                "SELECT
                    aclfolder.aclfolder_rights AS rights,
                    aclfolder.expiration_date,
                    folder.folder_parent AS parent_folder_id
                FROM aclfolder
                LEFT JOIN folder ON aclfolder.aclfolder_folder = folder.folder_id
                WHERE
                    (aclfolder.aclfolder_user = '" . USER_ID . "')
                    AND (aclfolder.aclfolder_folder = '" . escape($folder_id) . "')");

            $rights = $row['rights'];
            $expiration_date = $row['expiration_date'];
            $parent_folder_id = $row['parent_folder_id'];

            // If this user has edit rights to this folder, then they also have private access.
            if ($rights == 2) {
                $result['access'] = true;

            // Otherwise if this user has private access, then determine if access has expired.
            } else if ($rights == 1) {
                initialize_timezone();

                // If there is an expiration date and it has expired, then remember that.
                if (
                    ($expiration_date != '0000-00-00')
                    && ($expiration_date < date('Y-m-d'))
                ) {
                    $result['expired'] = true;

                // Otherwise the private access has not expired, so user has access.
                } else {
                    $result['access'] = true;
                }

            // Otherwise we do not know if access has been granted, so if this is not the root folder
            // then use recursion to check for access in parent folder.
            } else {
                // If the parent folder has not been found yet, then get it.
                if ($parent_folder_id == '') {
                    $parent_folder_id = db_value("SELECT folder_parent AS parent_folder_id FROM folder WHERE folder_id = '" . escape($folder_id) . "'");
                }

                // If this is not the root folder, then use recursion to check parent folder for access.
                if ($parent_folder_id != 0) {
                    $result = check_private_access($parent_folder_id);
                }
            }
        }
    }

    return $result;
}

// create a function to get the hostname, path, and software directory
function initialize_url_constants()
{
    define('HOSTNAME', $_SERVER['HTTP_HOST']);

    // get software directory

    // if this server is on Windows, then path delimiter is a backslash
    if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) == 'WIN') {
        $delimiter = '\\';
        
    // else this server is not on Windows, so path delimiter is a forward slash
    } else {
        $delimiter = '/';
    }

    $path_parts = explode($delimiter, dirname(__FILE__));
    define('SOFTWARE_DIRECTORY', $path_parts[count($path_parts) - 1]);

    // if this is at least PHP 5.0.0, then use strrpos to get the position of the last occurrence of the software directory in the path
    // PHP 4 does not support multicharacter needle for strrpos
    if (version_compare(PHP_VERSION, '5.0.0', '>=') == TRUE) {
        $position = mb_strrpos($_SERVER['SCRIPT_NAME'], '/' . SOFTWARE_DIRECTORY);
        
    // else this is PHP 4, so use mb_strrpos, which supports a multicharacter needle
    } else {
        $position = mb_strrpos($_SERVER['SCRIPT_NAME'], '/' . SOFTWARE_DIRECTORY);
    }
    
    // if the software directory could not be found in the path (should never happen), then set the path to /
    if ($position === FALSE) {
        $url_path = '/';
        
    // else the software directory was found, so set the path to everything up to the software directory
    } else {
        $url_path = mb_substr($_SERVER['SCRIPT_NAME'], 0, $position);
    }

    // if the path is not the root, then add a slash on the end
    if ($url_path != '/') {
        $url_path .= '/';
    }

    define('PATH', $url_path);
}

// create function to get current URL
function get_request_uri()
{
    // the order of the conditionals below is important, because newer versions of IIS supply a REQUEST_URI value,
    // but the value does not contain the original pretty URL and might also have other problems
    
    // if HTTP_X_REWRITE_URL is set (i.e. ISAPI_Rewrite is being used on IIS)
    if ($_SERVER['HTTP_X_REWRITE_URL']) {
        return $_SERVER['HTTP_X_REWRITE_URL'];
    
    // else if REQUEST_URI is set (i.e. non IIS web server)
    } elseif ($_SERVER['REQUEST_URI']) {
        return $_SERVER['REQUEST_URI'];
        
    // else no REQUEST_URI or HTTP_X_REWRITE_URL can be found (i.e. IIS web server without ISAPI_Rewrite)
    } else {
        // if QUERY_STRING is set then return PHP_SELF and QUERY_STRING
        if ($_SERVER['QUERY_STRING']) {
            return $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            
        // else QUERY_STRING is not set, so return just PHP_SELF
        } else {
            return $_SERVER['PHP_SELF'];
        }
    }
}

function check_if_request_is_secure()
{
    // if request is secure, then return TRUE
    if (
        ((isset($_SERVER['HTTPS']) == TRUE) && ($_SERVER['HTTPS'] == 'on'))
        || ((isset($_SERVER['SERVER_PORT']) == TRUE) && ($_SERVER['SERVER_PORT'] == '443'))
    ) {
        return TRUE;
        
    // else request is not secure, so return FALSE
    } else {
        return FALSE;
    }
}

// Create a function that we can use to set the timezone before we need to use date functions
// in PHP.  We create a function and selectively run this code in order to increase performance.
// We are not updating MySQL unlike other areas of the system in order to increase performance here.
function initialize_timezone()
{
    // If PHP is at least v5.1.3, then update timezone in PHP.
    if (version_compare(PHP_VERSION, '5.1.3', '>=') == true) {
        // If there is a timezone set in the site settings, then update PHP to use that.
        if (TIMEZONE != '') {
            date_default_timezone_set(TIMEZONE);

        // Otherwise there is not a timezone set, so update PHP to use server's timezone.
        } else {
            date_default_timezone_set(@date_default_timezone_get());
        }
    }
}
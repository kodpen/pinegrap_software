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
validate_area_access($user, 'designer');

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('view_styles');

// If a style was not selected, then output error.
if (!$_POST['styles']) {
    $liveform->mark_error('', 'You must select at least one page style to delete.');
    go(PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
}

$number_of_deleted_styles = 0;
$number_of_preserved_styles = 0;

// Loop through all styles that where selected.
foreach ($_POST['styles'] as $style_id) {
    // If this style is being used by a folder or page,
    // then don't delete it.
    if (
        (
            db_value(
                "SELECT COUNT(*)
                FROM folder
                WHERE
                    (folder_style = '" . escape($style_id) . "')
                    OR (mobile_style_id = '" . escape($style_id) . "')")
        )
        ||
        (
            db_value(
                "SELECT COUNT(*)
                FROM page
                WHERE
                    (page_style = '" . escape($style_id) . "')
                    OR (mobile_style_id = '" . escape($style_id) . "')")
        )
    ) {
        $number_of_preserved_styles++;

    // Otherwise this style is not being used, so delete it.
    } else {
        db("DELETE FROM style WHERE style_id = '" . escape($style_id) . "'");
        db("DELETE FROM system_style_cells WHERE style_id = '" . escape($style_id) . "'");
        db("DELETE FROM preview_styles WHERE style_id = '" . escape($style_id) . "'");

        $number_of_deleted_styles++;
    }
}

// If at least one style was deleted, then prepare message for that and log activity.
if ($number_of_deleted_styles > 0) {
    if ($number_of_deleted_styles == 1) {
        $message = '1 page style was deleted';
    } else {
        $message = number_format($number_of_deleted_styles) . ' page styles were deleted';
    }

    log_activity($message, $_SESSION['sessionusername']);

    $message .= '.';

    $liveform->add_notice($message);
}

// If at least one style was preserved, then add extra message for that 
if ($number_of_preserved_styles > 0) {
    if ($number_of_preserved_styles == 1) {
        $message = '1 page style was not deleted because it is being used by a folder or page.';

    } else {
        $message = number_format($number_of_preserved_styles) . ' page styles were not deleted because they are being used by a folder or page.';
    }

    $liveform->mark_error('', $message);
}

go(PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
?>
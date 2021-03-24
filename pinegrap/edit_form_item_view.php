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

// Get various properties for page that we will use in various places below.
$page = db_item(
    "SELECT
        page_id AS id,
        page_name AS name,
        page_folder AS folder_id,
        page_style AS style_id,
        mobile_style_id AS mobile_style_id
    FROM page
    WHERE page_id = '" . e($_REQUEST['page_id']) . "'");

if (!$page) {
    output_error('Sorry, the page could not be found.');
}

// validate user's access
if (check_edit_access($page['folder_id']) == false) {
    log_activity("access denied to edit form item view because user does not have access to modify folder that form item view is in", $_SESSION['sessionusername']);
    output_error('Access was denied, because you do not have access to modify the folder that the form item view is in.');
}

// get custom form folder, in order to validate user's access
$query = "SELECT
         page.page_folder,
         custom_form_pages.form_name
         FROM form_item_view_pages
         LEFT JOIN page ON form_item_view_pages.custom_form_page_id = page.page_id
         LEFT JOIN custom_form_pages ON custom_form_pages.page_id = form_item_view_pages.custom_form_page_id
         WHERE
            (form_item_view_pages.page_id = '" . e($_REQUEST['page_id']) . "')
            AND (form_item_view_pages.collection = 'a')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$custom_form_folder_id = $row['page_folder'];
$custom_form_name = $row['form_name'];

if ((isset($custom_form_name) == true) && ($custom_form_name != '')) {
    $output_custom_form_information = 'Displays submitted forms from: ' . $custom_form_name;
} else {
    $output_custom_form_information = 'Displays submitted forms from: None';
}

// validate user's access to custom form
if (check_edit_access($custom_form_folder_id) == false) {
    log_activity("access denied to edit form item view because user does not have access to modify folder that custom form is in", $_SESSION['sessionusername']);
    output_error('Access was denied, because you do not have access to modify the folder that the custom form is in.');
}

// Get the current style that is shown for this page for this user, so we can figure
// out the collection.  This might be the style that a designer is previewing
// or the activated style if the user is not previewing a style.
$preview_style = get_preview_style(array(
    'page_id' => $page['id'],
    'folder_id' => $page['folder_id'],
    'page_style_id' => $page['style_id'],
    'page_mobile_style_id' => $page['mobile_style_id'],
    'device_type' => $_SESSION['software']['device_type']));

// Get the collection for the style so we can show/save data for the
// right collection
$collection = db_value("SELECT collection FROM style WHERE style_id = '" . e($preview_style['id']) . "'");

// if form has not been submitted
if (!$_POST) {

    $form = new liveform('edit_form_item_view');

    // Get activated style in order to figure out if the user is editing fields
    // for a collection that is different from the activated collection.

    $activated_style = get_activated_style(array(
        'page_id' => $page['id'],
        'folder_id' => $page['folder_id'],
        'page_style_id' => $page['style_id'],
        'page_mobile_style_id' => $page['mobile_style_id'],
        'device_type' => $_SESSION['software']['device_type']));

    $activated_collection = db_value("SELECT collection FROM style WHERE style_id = '" . e($activated_style['id']) . "'");

    $collection_field_marker = '';

    // If the user is editing fields for a collection that is different from
    // the activated collection, then add warning, so user understands.
    if ($activated_collection != $collection) {

        $form->add_notice(
            'You are currently previewing a Page Style that has a different
            collection than the activated Page Style.  This means that updates to the
            collection field marked below, will not affect the production Page.
            Once the new Page Style is activated, then the updates will go live.
            You can find more info about collections under the Page Style help.');

        // Show marker next to collection fields, so user understands which
        // fields are collection fields.
        $collection_field_marker = ' &nbsp; <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_notice.png" alt="Notice" title=""> <span style="color: #428221">Collection Field</span>';
    }

    // Get collection A info for this form item view.  We get the collection A
    // info even if the style is set to collection B, because we only support
    // collection B for the layout field for now.
    $query =
        "SELECT
            custom_form_page_id,
            layout
        FROM form_item_view_pages
        WHERE
            (page_id = '" . e($page['id']) . "')
            AND (collection = 'a')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $custom_form_page_id = $row['custom_form_page_id'];
    $layout = $row['layout'];

    // If the style is set to collection b, then get page type properties for
    // that collection.
    if ($collection == 'b') {

        $properties = get_page_type_properties($page['id'], 'form item view', 'b');

        // We only currently support collection for the layout field,
        // so that is why we only override that property.
        $layout = $properties['layout'];

    }

    // get standard fields
    $standard_fields = get_standard_fields_for_view();

    $output_available_standard_fields = '';

    // loop through all standard fields
    foreach ($standard_fields as $standard_field) {
        $output_available_standard_fields .= '^^' . h($standard_field['value']) . '^^<br />';
    }

    // get custom fields
    $query = "SELECT
                id,
                name
             FROM form_fields
             WHERE
                (page_id = '$custom_form_page_id')
                AND (type != 'information')
                AND (name != '')
             ORDER BY sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $custom_fields = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $custom_fields[] = $row;
    }

    $output_available_custom_fields = '';

    // loop through all custom fields
    foreach ($custom_fields as $custom_field) {
        $output_available_custom_fields .= '^^' . h($custom_field['name']) . '^^<br />';
    }

    $output_javascript =
        '<script>
            window.onload = initialize_filters;

            var last_filter_number = 0;

            var custom_fields = new Array();

            ' . $output_custom_fields_for_javascript . '

            var filters = new Array();

            ' . $output_filters_for_javascript . '
        </script>';

    // Put the javascript into the head of the document.
    $output_header = preg_replace('/(<\/head>)/i', $output_javascript .'$1', output_header());

    print $output_header . '
    <div id="subnav">
        <h1>' . h($page['name']) . '</h1>
        <div class="subheading">' . $output_custom_form_information . '</div>
    </div>
    <div id="content">
        
        ' . $form->get_messages() . '
        <a href="#" id="help_link">Help</a>
        <h1>Edit Form Item View</h1>
        <div class="subheading">Update this page\'s display of a single submitted form, linked to by a reference code.</div>
        <form action="edit_form_item_view.php" method="post" style="margin: 0px; padding: 0px">
            ' . get_codemirror_includes() . '
            ' . get_token_field() . '
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
            <input type="hidden" name="page_id" value="' . h($_GET['page_id']) . '" />
            <h2>All available submitted form fields for display within View</h2>
            <table class="field" style="margin-bottom: 0 !important">
                <tr>
                    <td style="width: 30%; vertical-align: top; padding-right: 20px">
                        <strong>System Fields</strong>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            ' . $output_available_standard_fields . '
                        </div>
                    </td>
                    <td style="vertical-align: top; padding-right: 20px">
                         <strong>Form Fields</strong>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            ' . $output_available_custom_fields . '
                        </div>
                    </td>
                    <td style="vertical-align: top">
                        <strong>Hints</strong>
                        <div class="scrollable fields" style="height: 100px; padding: 5px">
                            <ul style="margin-top: 0px; margin-left: 20px">
                                <li>Copy fields from here and paste in the layout below.</li>
                                <li>
                                    Use the following URL format to link to files and embed images:<br />
                                    {path}^^example^^
                                </li>
                                <li>
                                    Use the following format to output different content depending on whether there is a value or not:<br />
                                    [[There is a value: ^^example^^ || There is not a value]]
                                </li>
                                <li>
                                    Use the following format to customize the date format for date and date &amp; time fields. The format can either be a <a href="http://php.net/manual/en/function.date.php" target="_blank">PHP date format</a> or "relative" for a relative time (e.g. "2 minutes ago", "2 minutes from now").<br />
                                    ^^submitted_date_and_time^^%%l, F j, Y \a\t g:i A%%<br />
                                    ^^submitted_date_and_time^^%%relative%%<br />
                                </li>
                                <li>
                                    Use the following URL format to link directly to the newest comment:<br />
                                    #c-^^newest_comment_id^^
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="margin-bottom: .35em">
                <h2 style="margin-bottom: 1em">Display layout of submitted form data fields within View' . $collection_field_marker . '</h2>
                <textarea id="layout" name="layout" style="width: 100%; height: 300px">' . h($layout) . '</textarea>
                ' . get_codemirror_javascript(array('id' => 'layout', 'code_type' => 'mixed')) . '
            </div>
            <div class="buttons">
                <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="javascript:history.go(-1)" class="submit-secondary" />
            </div>
        </form>
    </div>' .
    output_footer();

    $form->remove();

// else form has been submitted
} else {

    validate_token_field();

    // Update the layout for the collection that is set in the style.
    create_or_update_page_type_record('form item view', array(
        'page_id' => $page['id'],
        'collection' => $collection,
        'layout' => $_POST['layout']));

    // update last modified for page
    $query = "UPDATE page
             SET
                page_timestamp = UNIX_TIMESTAMP(),
                page_user = '" . $user['id'] . "'
             WHERE page_id = '" . escape($_POST['page_id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('page (' . $page['name'] . ') was modified', $_SESSION['sessionusername']);

    if ($_POST['send_to']) {
        // send user to send to
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
    } else {
        // send user to send to
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_page.php?id=' . $_POST['page_id']);
    }

}
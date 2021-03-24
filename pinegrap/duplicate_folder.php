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

$form = new liveform('duplicate_folder');

$folder = db_item(
    "SELECT
        folder_id AS id,
        folder_name AS name,
        folder_parent AS parent
    FROM folder
    WHERE folder_id = '" . e($_REQUEST['id']) . "'");

if (!$folder) {
    output_error('Sorry, the folder could not be found.');
}

// If the user does not have edit access to the parent folder or does not have access to
// create/duplicate pages, then log and show error.
if (!check_edit_access($folder['parent']) or (USER_ROLE == 3 and !$user['create_pages'])) {
    log_activity('Access denied to duplicate folder (' . $folder['name'] . ') because user does not have edit access to parent folder.');
    output_error('Access denied.');
}

// If the form has not been submitted, then output it.
if (!$_POST) {

    // If the form has not been submitted yet then autofill fields.
    if (!$form->field_in_session('find_replace')) {

        if ($_SESSION['software']['duplicate_folder']['find_replace']) {
            $form->set('find_replace', $_SESSION['software']['duplicate_folder']['find_replace']);
        } else {
            $form->set('find_replace', 'false');
        }

        $find_replace_keywords = $_SESSION['software']['duplicate_folder']['find_replace_keywords'];

        if ($find_replace_keywords) {
            foreach ($find_replace_keywords as $key => $item) {
                $form->set('find_' . ($key + 1), $item['find']);
                $form->set('replace_' . ($key + 1), $item['replace']);
            }
        }
    }

    echo output_header();

    $content = render(array(
        'template' => 'duplicate_folder.php',
        'form' => $form,
        'folder' => $folder));

    echo $form->prepare($content);

    echo output_footer();
    
    $form->remove();

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    $form->add_fields_to_session();

    // Remember if find and replace feature was used, so we can auto-open if user uses duplicate
    // folder feature again in this session.
    $_SESSION['software']['duplicate_folder']['find_replace'] = $form->get('find_replace');

    $find_replace_keywords = array();

    if ($form->get('find_replace') == 'true') {
        for ($number = 1; $number <= 10; $number++) {
            if ($form->get('find_' . $number) != '') {
                $find_replace_keywords[] = array(
                    'find' => $form->get('find_' . $number),
                    'replace' => $form->get('replace_' . $number));
            }
        }

        // Store find and replace keywords in session, so we can autofill the same values if the user
        // duplicates a folder again in this session.
        $_SESSION['software']['duplicate_folder']['find_replace_keywords'] = $find_replace_keywords;
    }

    require_once(dirname(__FILE__) . '/duplicate_folder_f.php');

    $response = duplicate_folder(array(
        'folder' => $folder,
        'find_replace_keywords' => $find_replace_keywords));

    if ($response['status'] == 'error') {
        output_error(h($response['message']));
    }

    $new_folder = $response['folder'];
    
    $form_edit_folder = new liveform('edit_folder');
    $form_edit_folder->add_notice('The folder has been duplicated, and you are now editing the duplicate.');

    $form->remove();

    go(PATH . SOFTWARE_DIRECTORY . '/edit_folder.php?id=' . $new_folder['id']);
}
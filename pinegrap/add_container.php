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
validate_ecommerce_access($user);

$form = new liveform('add_container');

// If the form has not been submitted, then output it.
if (!$_POST) {

    // If the form has not been submitted yet, then pre-populate fields with data.
    if ($form->is_empty()) {
        $form->set('enabled', 1);
    }

    echo output_header();

    $content = render(array(
        'template' => 'edit_container.php',
        'form' => $form,
        'screen' => 'create'));

    echo $form->prepare($content);

    echo output_footer();
    
    $form->remove();

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    $form->add_fields_to_session();
    
    $form->validate_required_field('name', 'Name is required.');
    $form->validate_required_field('length', 'Length is required.');
    $form->validate_required_field('width', 'Width is required.');
    $form->validate_required_field('height', 'Height is required.');

    // If there is not already an error for the name field,
    // and that name is already in use, then output error.
    if (
        (!$form->check_field_error('name'))
        and (db_value("SELECT COUNT(*) FROM containers WHERE name = '" . e($form->get('name')) . "'") != 0)
    ) {
        $form->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
    }

    if (!$form->check_field_error('length') and ($form->get('length') == 0)) {
        $form->mark_error('length', 'Sorry, the length must be greater than zero.');
    }

    if (!$form->check_field_error('width') and ($form->get('width') == 0)) {
        $form->mark_error('width', 'Sorry, the width must be greater than zero.');
    }

    if (!$form->check_field_error('height') and ($form->get('height') == 0)) {
        $form->mark_error('height', 'Sorry, the height must be greater than zero.');
    }
    
    if ($form->check_form_errors()) {
        go($_SERVER['PHP_SELF']);
    }

    // Convert cost to cents
    $cost = $form->get('cost') * 100;
    
    db(
        "INSERT INTO containers (
            name,
            enabled,
            length,
            width,
            height,
            weight,
            cost,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . e($form->get('name')) . "',
            '" . e($form->get('enabled')) . "',
            '" . e($form->get('length')) . "',
            '" . e($form->get('width')) . "',
            '" . e($form->get('height')) . "',
            '" . e($form->get('weight')) . "',
            '" . e($cost) . "',
            '" . USER_ID . "',
            UNIX_TIMESTAMP(),
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");

    $container_id = mysqli_insert_id(db::$con);
    
    log_activity('container (' . $form->get('name') . ') was created');
    
    $form_view_containers = new liveform('view_containers');
    $form_view_containers->add_notice('The container has been created.');

    $form->remove();

    go(PATH . SOFTWARE_DIRECTORY . '/view_containers.php');
    
}
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

$form = new liveform('edit_container');

$container = db_item(
    "SELECT
        id,
        name,
        enabled,
        length,
        width,
        height,
        weight,
        cost
    FROM containers
    WHERE id = '" . e($_REQUEST['id']) . "'");

// If the form has not been submitted, then output it.
if (!$_POST) {

    // If the form has not been submitted yet, then pre-populate fields with data.
    if ($form->is_empty()) {

        $form->set('name', $container['name']);
        $form->set('enabled', $container['enabled']);

        // Remove extra zeros in decimal.
        $form->set('length', $container['length']+0);
        $form->set('width', $container['width']+0);
        $form->set('height', $container['height']+0);

        // If there is a weight, then set it with extra decimal zeros removed.  Otherwise, if there
        // is no weight, then we just want the field to be blank.
        if ($container['weight'] > 0) {
            $form->set('weight', $container['weight']+0);
        }

        // If there is a cost, then convert to dollars and show it.  Otherwise, show blank field.
        if ($container['cost']) {
            $form->set('cost', $container['cost'] / 100);
        }
    }

    echo output_header();

    $content = render(array(
        'template' => 'edit_container.php',
        'form' => $form,
        'screen' => 'edit',
        'container' => $container));

    echo $form->prepare($content);

    echo output_footer();
    
    $form->remove();

// Otherwise the form has been submitted so process it.
} else {

    validate_token_field();
    
    $form->add_fields_to_session();

    // If the user selected to delete this container, then delete it.
    if ($form->field_in_session('delete')) {

        db("DELETE FROM containers WHERE id = '" . e($form->get('id')) . "'");
        
        log_activity('container (' . $container['name'] . ') was deleted');
        
        $form_view_containers = new liveform('view_containers');
        $form_view_containers->add_notice('The container has been deleted.');

    // Otherwise the user selected to save the container, so save it.
    } else {

        $form->validate_required_field('name', 'Name is required.');
        $form->validate_required_field('length', 'Length is required.');
        $form->validate_required_field('width', 'Width is required.');
        $form->validate_required_field('height', 'Height is required.');

        // If there is not already an error for the name field,
        // and that name is already in use, then output error.
        if (
            (!$form->check_field_error('name'))
            and (db_value("SELECT COUNT(*) FROM containers WHERE (name = '" . e($form->get('name')) . "') AND (id != '" . e($form->get('id')) . "')") != 0)
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
        
        // If there is an error, forward user back to previous screen.
        if ($form->check_form_errors()) {
            go($_SERVER['PHP_SELF'] . '?id=' . $form->get('id'));
        }

        // Convert cost to cents
        $cost = $form->get('cost') * 100;
        
        db(
            "UPDATE containers
            SET
                name = '" . e($form->get('name')) . "',
                enabled = '" . e($form->get('enabled')) . "',
                length = '" . e($form->get('length')) . "',
                width = '" . e($form->get('width')) . "',
                height = '" . e($form->get('height')) . "',
                weight = '" . e($form->get('weight')) . "',
                cost = '" . e($cost) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($form->get('id')) . "'");
        
        log_activity('container (' . $form->get('name') . ') was modified');
        
        $form_view_containers = new liveform('view_containers');
        $form_view_containers->add_notice('The container has been saved.');
    }

    $form->remove();

    go(PATH . SOFTWARE_DIRECTORY . '/view_containers.php');
}
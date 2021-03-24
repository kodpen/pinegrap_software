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

$form = new liveform('add_offer_rule');

// If the form was not just submitted, then output it.
if (!$_POST) {

    $form->set('required_products', 'options', get_product_options($include_blank_option = false));

    echo output_header();

    $content = render(array(
        'template' => 'edit_offer_rule.php',
        'form' => $form,
        'screen' => 'create'));

    echo $form->prepare($content);

    echo output_footer();
    
    $form->remove();

// Otherwise the form was just submitted so process it.
} else {

    validate_token_field();

    $form->add_fields_to_session();

    $form->validate_required_field('name', 'Name is required.');

    // If there is not already an error for the name field, and that name is already in use,
    // then show error.
    if (
        (!$form->check_field_error('name'))
        and (db_value("
            SELECT COUNT(*) FROM offer_rules WHERE name = '" . e($form->get('name')) . "'") != 0)
    ) {
        $form->mark_error('name', 'Sorry, the name that you entered is already in use, so please enter a different name.');
    }

    // If the user did not enter a subtotal or select required products, then show error.
    if (!$form->get('required_subtotal') and !$form->get('required_products')) {
        $form->mark_error('', 'Sorry, you must enter a required subtotal or select a required product.');
    }

    // If there are required products, but no quantity was entered, then show error.
    if ($form->get('required_products') and !$form->get('required_quantity')) {
        $form->mark_error('required_quantity', 'You have selected a required product, so please select a required quantity.');
    }

    // If there is an error, forward user back to previous screen.
    if ($form->check_form_errors()) {
        go($_SERVER['PHP_SELF']);
    }

    // Convert subtotal to cents.
    $required_subtotal = $form->get('required_subtotal') * 100;

    db("
        INSERT INTO offer_rules (
            name,
            required_subtotal,
            required_quantity,
            user,
            timestamp)
        VALUES (
            '" . e($form->get('name')) . "',
            '" . e($required_subtotal) . "',
            '" . e($form->get('required_quantity')) . "',
            '" . e(USER_ID) . "',
            UNIX_TIMESTAMP())");

    $offer_rule_id = mysqli_insert_id(db::$con);

    // Add required products to db.
    foreach ($form->get('required_products') as $product_id) {
        db("INSERT INTO offer_rules_products_xref (offer_rule_id, product_id)
            VALUES ('" . e($offer_rule_id) . "', '" . e($product_id) . "')");
    }

    log_activity('Offer rule (' . $form->get('name') . ') was created.');

    $form_view_offer_rules = new liveform('view_offer_rules');
    $form_view_offer_rules->add_notice('The offer rule has been created.');

    $form->remove();

    go(PATH . SOFTWARE_DIRECTORY . '/view_offer_rules.php');
}
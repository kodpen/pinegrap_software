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

function get_change_password($properties) {

    $page_id = $properties['page_id'];

    $form = new liveform('change_password');

    $attributes =
        'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/change_password.php" ' .
        'method="post"';

    // If the form is empty, then set default values.
    if ($form->is_empty()) {
        if (USER_LOGGED_IN) {
            $form->set('email_address', USER_EMAIL_ADDRESS);
        }
    }

    $form->set('email_address', 'required', true);
    $form->set('email_address', 'maxlength', 100);

    $form->set('current_password', 'required', true);

    // If strong password is enabled, then display password requirements.
    if (STRONG_PASSWORD) {
        $strong_password_help = get_strong_password_requirements();
    } else {
        $strong_password_help = '';
    }

    $form->set('new_password', 'required', true);
    $form->set('new_password_verify', 'required', true);

    if (PASSWORD_HINT) {
        $form->set('password_hint', 'maxlength', 100);
    }

    $my_account_url = '';

    // If the user is logged in then get my account URL, for cancel button.
    if (USER_LOGGED_IN) {
        $my_account_url = get_page_type_url('my account');
    }

    $system = get_token_field();

    $output = render_layout(array(
        'page_id' => $page_id,
        'messages' => $form->get_messages(),
        'form' => $form,
        'attributes' => $attributes,
        'strong_password_help' => $strong_password_help,
        'my_account_url' => $my_account_url,
        'system' => $system));

    $output = $form->prepare($output);

    $form->remove();

    return
        '<div class="software_change_password">
            ' . $output . '
        </div>';

}
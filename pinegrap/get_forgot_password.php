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

function get_forgot_password($properties = array()) {

    $page_id = $properties['page_id'];

    $form = new liveform('forgot_password');

    if ($form->get('screen') == 'confirm') {

        $output = $form->get_messages();

    } else {

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/forgot_password.php" ' .
            'method="post"';

        $form->set('email', 'required', true);
        $form->set('email', 'autofocus', true);

        $screen = $form->get_field_value('screen');

        $password_hint = '';

        if (PASSWORD_HINT && ($screen == 'password_hint')) {
            $email = $form->get_field_value('email');

            $password_hint = db_value(
                "SELECT user_password_hint
                FROM user
                WHERE user_email = '" . e($email) . "'");
        }

        // If a send to was passed in the query string,
        // then set send to.
        if ($_GET['send_to'] != '') {
            $form->assign_field_value('send_to', $_GET['send_to']);

        // Otherwise if there is no send to set in the session,
        // then set send to to home page as a default.
        } else if ($form->get_field_value('send_to') == '') {
            $form->assign_field_value('send_to', PATH);
        }

        // Escape the send to URL for security reasons,
        // because the send to is placed in an anchor href.
        $send_to = escape_url($form->get_field_value('send_to'));    

        $form->assign_field_value('send_to', $send_to);

        $system =
            get_token_field() . '
            <input type="hidden" name="send_to">
            <input type="hidden" name="screen">';

        if ($password_hint != '') {
            $system .= '<input type="hidden" name="email">';
        }

        $output = render_layout(array(
            'page_id' => $page_id,
            'page_type' => 'forgot password',
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'screen' => $screen,
            'password_hint' => $password_hint,
            'send_to' => $send_to,
            'system' => $system));

        $output = $form->prepare($output);

    }

    $form->remove();

    return
        '<div class="software_forgot_password">
            ' . $output . '
        </div>';

    return $output;

}
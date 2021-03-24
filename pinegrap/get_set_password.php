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

function get_set_password($properties) {

    $page_id = $properties['page_id'];
    $folder_id = $properties['folder_id'];

    $form = new liveform('set_password');

    if ($form->get('screen') == 'confirm') {

        $output = $form->get_messages();

    } else {

        $token = $_GET['k'];

        // If there is a token in the query string, then validate it.
        if ($token) {

            // Create a hash of the token, because that is what we store in db.
            $token_hash = hash('sha256', $token);

            // Check database if token is valid.
            $user = db_item(
                "SELECT
                    user_id AS id,
                    user_username AS username,
                    user_email AS email,
                    token_timestamp
                FROM user
                WHERE token = '" . e($token_hash) . "'");

            // If query to database return no results for token
            if (!$user['id']) {

                log_activity('Set Password: invalid token');

                output_error('Sorry, the token is not valid, so we can\'t allow you to set a password. Please check if your email client possibly broke the link. If so, then you might try fixing the link. Otherwise, the token might be old, so please <a href="' . h(get_page_type_url('forgot password')) . '">request a new email</a>.');
            }

            $token_timestamp = $user['token_timestamp'];
            $time_24hrs = (24*60*60);

            // Check to see if token has expired after 24 hours
            if (($token_timestamp + $time_24hrs) < time()) {
                
                log_activity('Set Password: expired token');

                output_error('Sorry, the token has expired. Please <a href="' . h(get_page_type_url('forgot password')) . '">request a new email</a>.');
            }   


        // Otherwise, there is not a token, so if visitor does not have edit access to page, then
        // output error.  We don't output an error for users with edit access, because they need
        // to be able to view page/form and edit it.
        } else if (!check_edit_access($folder_id)) {

            log_activity('Set Password: missing token');

            output_error('Sorry, the token is missing from the address, so we can\'t allow you to set a password.');
        }

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/set_password.php" ' .
            'method="post"';

        // If the form is empty, then set default values.
        if ($form->is_empty()) {
            $form->set('send_to', $_GET['send_to']);
            $form->set('k', $token);
        }

        // If strong password is enabled, then display password requirements.
        if (STRONG_PASSWORD) {
            $strong_password_help = get_strong_password_requirements();
        } else {
            $strong_password_help = '';
        }

        $form->set('new_password', 'required', true);

        if (PASSWORD_HINT) {
            $form->set('password_hint', 'maxlength', 100);
        }

        $system =
            get_token_field() . '
            <input type="hidden" name="send_to">' . '
            <input type="hidden" name="k">';

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'strong_password_help' => $strong_password_help,
            'email' => $user['email'],
            'system' => $system));

        $output = $form->prepare($output);
        
    }

    $form->remove();

    return
        '<div class="software_set_password">
            ' . $output . '
        </div>';
}
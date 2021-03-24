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

// If the user has not submitted the form, then show form.
if (!$_POST) {
    echo get_forgot_password_screen();
    exit();
}

validate_token_field();

$page_name = db_value(
    "SELECT page_name
    FROM page
    WHERE page_type = 'forgot password'
    LIMIT 1");

if ($page_name != '') {
    $url = PATH . encode_url_path($page_name);
} else {
    $url = PATH . SOFTWARE_DIRECTORY . '/forgot_password.php';
}

$form = new liveform('forgot_password');

$form->add_fields_to_session();

$email = $form->get_field_value('email');
$screen = $form->get_field_value('screen');
$send_to = $form->get_field_value('send_to');

$form->validate_required_field('email', 'Email is required.');

// If there is not an error then get user info.
if (!$form->check_form_errors()) {
    $user = db_item(
        "SELECT
            user_id AS id,
            user_username AS username,
            user_password_hint AS password_hint
        FROM user
        WHERE user_email = '" . e($email) . "'");

    if (!$user['id']) {
        $form->mark_error('email', 'Sorry, we could not find an account for the email address you entered.');
    }
}

// If there is an error, forward user back to previous screen.
if ($form->check_form_errors()) {
    go($url);
}

// If password hint is enabled and the user has a password hint,
// and the user has not already told us that the password hint did not help,
// then show the password hint to the user.
if (
    PASSWORD_HINT
    && ($user['password_hint'] != '')
    && ($screen != 'password_hint')
) {
    $form->assign_field_value('screen', 'password_hint');

    go($url);
}

// Create a function to get token for email link, because, if the token that we generate is already
// in use, then we will need to use recursion to generate a different token.

function get_token() {

    // Create a random token with lower and uppercase characters and numbers.  We use a length of 16
    // because it will result in a strong token, but it is also short enough so that the link in the
    // email will not break.

    $token['token'] = get_random_string(array(
        'type' => 'letters_and_numbers',
        'length' => 16));

    // Get the hash of the token, because that is how we store it in db.  We store a hash of the
    // token because the token is basically a password that is stored in the db.  If there is a
    // vulnerability that allows someone read access to db, this will prevent attacker from getting
    // token.  Hashing the token is not as important as hashing a password, because the token is
    // not a user's personal password that might be used on multiple sites and token is also
    // time-limited, however we might as well hash it.  We are using sha256 instead of bcrypt,
    // because bcrypt would require us to include user id in set password link in email, which
    // would complicate URL and make it longer, which might result in email client breaking link.

    $token['hash'] = hash('sha256', $token['token']);
    
    // If the token already exists, then use recursion to generate new token.
    if (db("SELECT user_id FROM user WHERE token = '" . $token['hash'] . "' LIMIT 1")) {
        return get_token();
        
    // Otherwise the token is not already in use, so return token.
    } else {
        return $token;
    }
}

$token = get_token();

// Insert token into database
db(
    "UPDATE user 
    SET 
        token = '" . $token['hash'] . "',
        token_timestamp = UNIX_TIMESTAMP()
    WHERE user_id = '" . $user['id'] . "'");

// Send reset password email to user. We use a short query string parameter ("k") for the token, to
// prevent the link from from being too long and breaking in email clients.  We use "k" instead of
// "t" for the token, because "t" is already used for tracking codes.

email(array(
    'to' => $email,
    'from_name' => ORGANIZATION_NAME,
    'from_email_address' => EMAIL_ADDRESS,
    'subject' => 'Reset Password',
    'body' =>
        'We received a request to reset your password. You can reset your password by clicking the link below.' . "\n" .
        "\n" .
        URL_SCHEME . HOSTNAME . get_page_type_url('set password') . '?k=' . $token['token'] . "\n" .
        "\n" .
        'If you did not make this request, then you may safely ignore this email, and your password will remain the same.'));

log_activity('User requested reset password email.', $user['username']);

$form->remove();

$form->assign_field_value('screen', 'confirm');

$form->add_notice('We have sent an email to you. Please follow the instructions in the email. If the email is hiding from you, then please look for it in your spam folder.');

go($url);
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

require_once(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/functions.php');

// Autoload the liveform class so it is only loaded if it is used,
// and so that we don't have to manually load it when necessary.
// It is not used for many front-end pages, so we don't just want to always load it.
spl_autoload_register('autoload_liveform');

// If we have not already updated the PHP settings in the router.php script,
// then update them now.
if (!defined('PHP_SETTINGS_UPDATED')) {

    // If an admin has not specifically requested that error reporting not be set by liveSite, then
    // set error_reporting to what is generally best for liveSite. Don't show PHP notices, strict,
    // and deprecated messages. E_DEPRECATED is only available in newer PHP versions.  We allow
    // an admin to disable this by setting SET_ERROR_REPORTING to false in config.php, because in
    // PHP 7.2+, PHP is showing more warnings, so an admin might not want liveSite to control this.

    if (!defined('SET_ERROR_REPORTING') or SET_ERROR_REPORTING) {
        if (defined('E_DEPRECATED')) {
            ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        } else {
            ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT);
        }
    }

    ini_set('default_charset', 'utf-8');
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
}
	
// get software directory

// if this server is on Windows, then path delimiter is a backslash
if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) == 'WIN') {
    $delimiter = '\\';
    
// else this server is not on Windows, so path delimiter is a forward slash
} else {
    $delimiter = '/';
}

$path_parts = explode($delimiter, dirname(__FILE__));
define('SOFTWARE_DIRECTORY', $path_parts[count($path_parts) - 1]);

// prepare escaped version of software directory because we will use this a lot
define('OUTPUT_SOFTWARE_DIRECTORY', h(SOFTWARE_DIRECTORY));

// if this request was made over the web (i.e. not a cron job),
// then get the path (e.g. /~example) to the software root (i.e. the level above the software directory)
// For cron jobs, we will set the path later from the value that we get from the database
if ($_SERVER['HTTP_HOST'] != '') {
    // get the url path parts in order to get the file name
    $url_path_parts = explode('/', $_SERVER['SCRIPT_NAME']);
    $file_name = $url_path_parts[count($url_path_parts) - 1];

    // if the index.php in the software root was requested, then get the path in a certain way
    if (
        ($file_name == 'index.php')
        && (defined('NON_ROOT_INDEX') == FALSE)
    ) {
        // get the path without the file name on the end
        $url_path = dirname($_SERVER['SCRIPT_NAME']);
        
        // convert backslashes to forward slashes
        // backslashes seem to only appear on Windows when only the root is left (e.g. \).
        $url_path = str_replace('\\', '/', $url_path);

    // else the software root index.php file was not requested, so get the path in a different way
    } else {
        // if this is at least PHP 5.0.0, then use strrpos to get the position of the last occurrence of the software directory in the path
        // PHP 4 does not support multicharacter needle for strrpos
        if (version_compare(PHP_VERSION, '5.0.0', '>=') == TRUE) {
            $position = mb_strrpos($_SERVER['SCRIPT_NAME'], '/' . SOFTWARE_DIRECTORY);
            
        // else this is PHP 4, so use mb_strrpos, which supports a multicharacter needle
        } else {
            $position = mb_strrpos($_SERVER['SCRIPT_NAME'], '/' . SOFTWARE_DIRECTORY);
        }
        
        // if the software directory could not be found in the path (should never happen), then set the path to /
        if ($position === FALSE) {
            $url_path = '/';
            
        // else the software directory was found, so set the path to everything up to the software directory
        } else {
            $url_path = mb_substr($_SERVER['SCRIPT_NAME'], 0, $position);
        }
    }

    // if the path is not the root, then add a slash on the end
    if ($url_path != '/') {
        $url_path .= '/';
    }

    define('PATH', $url_path);

    // prepare escaped version of path because we will use this a lot
    define('OUTPUT_PATH', h(PATH));

    // If the REQUEST_URL has not been defined by the router already,
    // then set it (e.g. for control panel screens).
    if (!defined('REQUEST_URL')) {
        define('REQUEST_URL', get_request_uri());
    }
}

// If a config file path is not set, then set it to the default which is a path
// inside the software directory. A custom config file path is used when
// an adminstrator wants the config file to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('CONFIG_FILE_PATH') == false) {
    define('CONFIG_FILE_PATH', dirname(__FILE__) . '/config/config.php');
}

// If a file directory path is not set, then set it to the default which is a path
// inside the software directory. A custom file directory path is used when
// an adminstrator wants the file directory to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('FILE_DIRECTORY_PATH') == false) {
    define('FILE_DIRECTORY_PATH', dirname(__FILE__) . '/files');
}

// If a layout directory path is not set, then set it to the default which is a path
// inside the software directory. A custom layout directory path is used when
// an adminstrator wants the layout directory to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (!defined('LAYOUT_DIRECTORY_PATH')) {
    define('LAYOUT_DIRECTORY_PATH', dirname(__FILE__) . '/layouts');
}

// If an htaccess file path is not set, then set it to the default which is a path
// in the web root. A custom htaccess file path is used when the .htaccess file is
// is located in a separate location from the software.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('HTACCESS_FILE_PATH') == false) {
    // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,
    // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).
    
    // If the web server is IIS then set the htaccess file info to the httpd.ini location.
    if (stristr($_SERVER['SERVER_SOFTWARE'], 'iis')) {
        define('HTACCESS_FILE_PATH', dirname(__FILE__) . '/../httpd.ini');
        define('HTACCESS_FILE_NAME', 'httpd.ini');

    // Otherwise the web server is Apache, so set the htaccess file info to the .htaccess location.
    } else {
        define('HTACCESS_FILE_PATH', dirname(__FILE__) . '/../.htaccess');
        define('HTACCESS_FILE_NAME', '.htaccess');
    }
}

// if DB_HOST is not defined, then config.php is not setup properly,
// so installation has probably not been completed, so output error
if (defined('DB_HOST') == false) {
    print 'config.php is not configured properly. This probably means that the software has not been installed. Please install the software, or configure config.php properly.';
    exit();
}

// If the ENVIRONMENT constant is set to "development", then set the ENVIRONMENT_SUFFIX to "src".
// This allows us to use source files instead of minified files during development.
if (defined('ENVIRONMENT') and ENVIRONMENT == 'development') {
    define('ENVIRONMENT_SUFFIX', 'src');

// Otherwise the ENVIRONMENT constant is not defined or set to something else, so set the ENVIRONMENT_SUFFIX to "min".
} else {
    define('ENVIRONMENT_SUFFIX', 'min');
}

// get current timestamp (we will use this in several places below)
$current_timestamp = time();

// If we have not already connected to the database in the router.php script, then connect to it.
if (!defined('DB_CONNECTED') or DB_CONNECTED !== true) {
    db_connect();
}

// get configuration constants from database
$query =
    "SELECT
        version,
        private_label,
        last_software_update_check_timestamp,
        software_update_available,
        url_scheme,
        hostname,
        path,
        email_address,
        title,
        meta_description,
        meta_keywords,
        forgot_password_link,
        mobile,
        search_type,
        social_networking,
        social_networking_type,
        social_networking_facebook,
        social_networking_twitter,
        social_networking_addthis,
        social_networking_plusone,
        social_networking_linkedin,
        captcha,
        auto_dialogs,
        mass_deletion,
        strong_password,
        password_hint,
        remember_me,
        proxy_address,
        badge_label,
        timezone,
        date_format,
		time_format,
        organization_name,
        organization_address_1,
        organization_address_2,
        organization_city,
        organization_state,
        organization_zip_code,
        organization_country,
        opt_in_label,
        visitor_tracking,
        pay_per_click_flag,
        stats_url,
        google_analytics,
        google_analytics_web_property_id,
        registration_contact_group_id,
        registration_email_address,
        member_id_label,
        membership_contact_group_id,
        membership_email_address,
        membership_expiration_warning_email,
        membership_expiration_warning_email_subject,
        membership_expiration_warning_email_page_id,
        membership_expiration_warning_email_days_before_expiration,
        ecommerce,
        ecommerce_multicurrency,
        ecommerce_tax,
        ecommerce_tax_exempt,
        ecommerce_tax_exempt_label,
        ecommerce_shipping,
        ecommerce_recipient_mode,
        usps_user_id,
        ecommerce_address_verification,
        ecommerce_address_verification_enforcement_type,
        ups,
        fedex,
        ecommerce_product_restriction_message,
        ecommerce_no_shipping_methods_message,
        ecommerce_end_of_day_time,
        ecommerce_email_address,
        ecommerce_gift_card,
        ecommerce_gift_card_validity_days,
        ecommerce_givex,
        ecommerce_givex_primary_hostname,
        ecommerce_givex_secondary_hostname,
        ecommerce_givex_user_id,
        ecommerce_givex_password,
        ecommerce_credit_debit_card,
        ecommerce_american_express,
        ecommerce_diners_club,
        ecommerce_discover_card,
        ecommerce_mastercard,
        ecommerce_visa,
        ecommerce_payment_gateway,
        ecommerce_payment_gateway_transaction_type,
        ecommerce_payment_gateway_mode,
        ecommerce_authorizenet_api_login_id,
        ecommerce_authorizenet_transaction_key,
        ecommerce_clearcommerce_client_id,
        ecommerce_clearcommerce_user_id,
        ecommerce_clearcommerce_password,
        ecommerce_first_data_global_gateway_store_number,
        ecommerce_first_data_global_gateway_pem_file_name,
        ecommerce_paypal_payflow_pro_partner,
        ecommerce_paypal_payflow_pro_merchant_login,
        ecommerce_paypal_payflow_pro_user,
        ecommerce_paypal_payflow_pro_password,
        ecommerce_paypal_payments_pro_api_username,
        ecommerce_paypal_payments_pro_api_password,
        ecommerce_paypal_payments_pro_api_signature,
        ecommerce_sage_merchant_id,
        ecommerce_sage_merchant_key,
        ecommerce_stripe_api_key,
        ecommerce_surcharge_percentage,
        ecommerce_paypal_express_checkout,
        ecommerce_paypal_express_checkout_transaction_type,
        ecommerce_paypal_express_checkout_mode,
        ecommerce_paypal_express_checkout_api_username,
        ecommerce_paypal_express_checkout_api_password,
        ecommerce_paypal_express_checkout_api_signature,
        ecommerce_offline_payment,
        ecommerce_offline_payment_only_specific_orders,
        ecommerce_private_folder_id,
        ecommerce_retrieve_order_next_page_id,
        ecommerce_reward_program,
        ecommerce_reward_program_points,
        ecommerce_reward_program_membership,
        ecommerce_reward_program_membership_days,
        ecommerce_reward_program_email,
        ecommerce_reward_program_email_bcc_email_address,
        ecommerce_reward_program_email_subject,
        ecommerce_reward_program_email_page_id,
        ecommerce_custom_product_field_1_label,
        ecommerce_custom_product_field_2_label,
        ecommerce_custom_product_field_3_label,
        ecommerce_custom_product_field_4_label,
		ecommerce_iyzipay_api_key,
		ecommerce_iyzipay_secret_key,
		ecommerce_iyzipay_installment,
		ecommerce_iyzipay_threeds,
        forms,
        calendars,
        ads,
        affiliate_program,
        affiliate_default_commission_rate,
        affiliate_automatic_approval,
        affiliate_contact_group_id,
        affiliate_email_address,
        affiliate_group_offer_id,
        mailchimp,
        debug,
        number_of_queries,
        number_of_email_recipients,
        special_user_id,
        last_sitemap_check_timestamp,
        last_sitemap_check_hash,
        last_recurring_commission_check_timestamp,
        installer,
		subscription_key,
        software_language
    FROM config";
$result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));
$row = mysqli_fetch_assoc($result);

// change type of values that should be boolean
settype($row['private_label'], 'boolean');
settype($row['software_update_available'], 'boolean');
settype($row['forgot_password_link'], 'boolean');
settype($row['mobile'], 'boolean');
settype($row['social_networking'], 'boolean');
settype($row['social_networking_facebook'], 'boolean');
settype($row['social_networking_twitter'], 'boolean');
settype($row['social_networking_addthis'], 'boolean');
settype($row['social_networking_plusone'], 'boolean');
settype($row['social_networking_linkedin'], 'boolean');
settype($row['captcha'], 'boolean');
settype($row['auto_dialogs'], 'boolean');
settype($row['mass_deletion'], 'boolean');
settype($row['strong_password'], 'boolean');
settype($row['password_hint'], 'boolean');
settype($row['remember_me'], 'boolean');
settype($row['membership_expiration_warning_email'], 'boolean');
settype($row['visitor_tracking'], 'boolean');
settype($row['google_analytics'], 'boolean');
settype($row['ecommerce'], 'boolean');
settype($row['ecommerce_multicurrency'], 'boolean');
settype($row['ecommerce_tax'], 'boolean');
settype($row['ecommerce_tax_exempt'], 'boolean');
settype($row['ecommerce_shipping'], 'boolean');
settype($row['ecommerce_address_verification'], 'boolean');
settype($row['ups'], 'boolean');
settype($row['fedex'], 'boolean');
settype($row['ecommerce_gift_card'], 'boolean');
settype($row['ecommerce_givex'], 'boolean');
settype($row['ecommerce_credit_debit_card'], 'boolean');
settype($row['ecommerce_american_express'], 'boolean');
settype($row['ecommerce_diners_club'], 'boolean');
settype($row['ecommerce_discover_card'], 'boolean');
settype($row['ecommerce_mastercard'], 'boolean');
settype($row['ecommerce_visa'], 'boolean');
settype($row['ecommerce_paypal_express_checkout'], 'boolean');
settype($row['ecommerce_offline_payment'], 'boolean');
settype($row['ecommerce_iyzipay_threeds'], 'boolean');
settype($row['ecommerce_offline_payment_only_specific_orders'], 'boolean');
settype($row['ecommerce_reward_program'], 'boolean');
settype($row['ecommerce_reward_program_membership'], 'boolean');
settype($row['ecommerce_reward_program_email'], 'boolean');
settype($row['forms'], 'boolean');
settype($row['calendars'], 'boolean');
settype($row['ads'], 'boolean');
settype($row['affiliate_program'], 'boolean');
settype($row['affiliate_automatic_approval'], 'boolean');
settype($row['mailchimp'], 'boolean');
settype($row['debug'], 'boolean');

  
// define all constants 
  
define('VERSION', $row['version']);
if (!defined('EDITION')){
    define('EDITION', 'PRO');
}

$private_label = $row['private_label'];
define('LAST_SOFTWARE_UPDATE_CHECK_TIMESTAMP', $row['last_software_update_check_timestamp']);

// don't define constant yet for SOFTWARE_UPDATE_AVAILABLE, because we might check further below if a software update is available
// just set value in variable
$original_software_update_available = $row['software_update_available'];

define('URL_SCHEME', $row['url_scheme']);

// if script was requested from a browser, then define $_SERVER['HTTP_HOST'] for hostname
if (isset($_SERVER['HTTP_HOST']) == true) {
    define('HOSTNAME', $_SERVER['HTTP_HOST']);
    
// else if script was not requested from a browser (e.g. scheduled task), then define hostname setting for hostname
} else {
    define('HOSTNAME', $row['hostname']);
}

// also define the hostname setting because in some areas we need to know the exact setting
define('HOSTNAME_SETTING', $row['hostname']);

define('EMAIL_ADDRESS', $row['email_address']);
define('TITLE', $row['title']);
define('META_DESCRIPTION', $row['meta_description']);
define('META_KEYWORDS', $row['meta_keywords']);
define('REMEMBER_ME', $row['remember_me']);
define('FORGOT_PASSWORD_LINK', $row['forgot_password_link']);
define('MOBILE', $row['mobile']);
define('SEARCH_TYPE', $row['search_type']);
define('SOCIAL_NETWORKING', $row['social_networking']);
define('SOCIAL_NETWORKING_TYPE', $row['social_networking_type']);
define('SOCIAL_NETWORKING_FACEBOOK', $row['social_networking_facebook']);
define('SOCIAL_NETWORKING_TWITTER', $row['social_networking_twitter']);
define('SOCIAL_NETWORKING_ADDTHIS', $row['social_networking_addthis']);
define('SOCIAL_NETWORKING_PLUSONE', $row['social_networking_plusone']);
define('SOCIAL_NETWORKING_LINKEDIN', $row['social_networking_linkedin']);
define('CAPTCHA', $row['captcha']);
define('AUTO_DIALOGS', $row['auto_dialogs']);
define('MASS_DELETION', $row['mass_deletion']);
define('STRONG_PASSWORD', $row['strong_password']);
define('PASSWORD_HINT', $row['password_hint']);
define('PROXY_ADDRESS', $row['proxy_address']);
define('BADGE_LABEL', $row['badge_label']);
define('TIMEZONE', $row['timezone']);
define('DATE_FORMAT', $row['date_format']);
define('TIME_FORMAT', $row['time_format']);
define('ORGANIZATION_NAME', $row['organization_name']);
define('ORGANIZATION_ADDRESS_1', $row['organization_address_1']);
define('ORGANIZATION_ADDRESS_2', $row['organization_address_2']);
define('ORGANIZATION_CITY', $row['organization_city']);
define('ORGANIZATION_STATE', $row['organization_state']);
define('ORGANIZATION_ZIP_CODE', $row['organization_zip_code']);
define('ORGANIZATION_COUNTRY', $row['organization_country']);
define('OPT_IN_LABEL', $row['opt_in_label']);
define('VISITOR_TRACKING', $row['visitor_tracking']);
define('PAY_PER_CLICK_FLAG', $row['pay_per_click_flag']);
define('STATS_URL', $row['stats_url']);
define('GOOGLE_ANALYTICS', $row['google_analytics']);
define('GOOGLE_ANALYTICS_WEB_PROPERTY_ID', $row['google_analytics_web_property_id']);
define('REGISTRATION_CONTACT_GROUP_ID', $row['registration_contact_group_id']);
define('REGISTRATION_EMAIL_ADDRESS', $row['registration_email_address']);
define('MEMBER_ID_LABEL', $row['member_id_label']);
define('MEMBERSHIP_CONTACT_GROUP_ID', $row['membership_contact_group_id']);
define('MEMBERSHIP_EMAIL_ADDRESS', $row['membership_email_address']);
define('MEMBERSHIP_EXPIRATION_WARNING_EMAIL', $row['membership_expiration_warning_email']);
define('MEMBERSHIP_EXPIRATION_WARNING_EMAIL_SUBJECT', $row['membership_expiration_warning_email_subject']);
define('MEMBERSHIP_EXPIRATION_WARNING_EMAIL_PAGE_ID', $row['membership_expiration_warning_email_page_id']);
define('MEMBERSHIP_EXPIRATION_WARNING_EMAIL_DAYS_BEFORE_EXPIRATION', $row['membership_expiration_warning_email_days_before_expiration']);
define('ECOMMERCE', $row['ecommerce']);
define('ECOMMERCE_MULTICURRENCY', $row['ecommerce_multicurrency']);
define('ECOMMERCE_TAX', $row['ecommerce_tax']);
define('ECOMMERCE_TAX_EXEMPT', $row['ecommerce_tax_exempt']);
define('ECOMMERCE_TAX_EXEMPT_LABEL', $row['ecommerce_tax_exempt_label']);
define('ECOMMERCE_SHIPPING', $row['ecommerce_shipping']);
define('ECOMMERCE_RECIPIENT_MODE', $row['ecommerce_recipient_mode']);
define('USPS_USER_ID', $row['usps_user_id']);
define('ECOMMERCE_ADDRESS_VERIFICATION', $row['ecommerce_address_verification']);
define('ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE', $row['ecommerce_address_verification_enforcement_type']);
define('UPS', $row['ups']);
define('FEDEX', $row['fedex']);
define('ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE', $row['ecommerce_product_restriction_message']);
define('ECOMMERCE_NO_SHIPPING_METHODS_MESSAGE', $row['ecommerce_no_shipping_methods_message']);
define('ECOMMERCE_END_OF_DAY_TIME', mb_substr($row['ecommerce_end_of_day_time'], 0, 5));
define('ECOMMERCE_EMAIL_ADDRESS', $row['ecommerce_email_address']);
define('ECOMMERCE_GIFT_CARD', $row['ecommerce_gift_card']);
define('ECOMMERCE_GIFT_CARD_VALIDITY_DAYS', $row['ecommerce_gift_card_validity_days']);
define('ECOMMERCE_GIVEX', $row['ecommerce_givex']);
define('ECOMMERCE_GIVEX_PRIMARY_HOSTNAME', $row['ecommerce_givex_primary_hostname']);
define('ECOMMERCE_GIVEX_SECONDARY_HOSTNAME', $row['ecommerce_givex_secondary_hostname']);
define('ECOMMERCE_GIVEX_USER_ID', $row['ecommerce_givex_user_id']);
define('ECOMMERCE_GIVEX_PASSWORD', $row['ecommerce_givex_password']);
define('ECOMMERCE_CREDIT_DEBIT_CARD', $row['ecommerce_credit_debit_card']);
define('ECOMMERCE_AMERICAN_EXPRESS', $row['ecommerce_american_express']);
define('ECOMMERCE_DINERS_CLUB', $row['ecommerce_diners_club']);
define('ECOMMERCE_DISCOVER_CARD', $row['ecommerce_discover_card']);
define('ECOMMERCE_MASTERCARD', $row['ecommerce_mastercard']);
define('ECOMMERCE_VISA', $row['ecommerce_visa']);
define('ECOMMERCE_PAYMENT_GATEWAY', $row['ecommerce_payment_gateway']);
define('ECOMMERCE_PAYMENT_GATEWAY_TRANSACTION_TYPE', $row['ecommerce_payment_gateway_transaction_type']);
define('ECOMMERCE_PAYMENT_GATEWAY_MODE', $row['ecommerce_payment_gateway_mode']);
define('ECOMMERCE_AUTHORIZENET_API_LOGIN_ID', $row['ecommerce_authorizenet_api_login_id']);
define('ECOMMERCE_AUTHORIZENET_TRANSACTION_KEY', $row['ecommerce_authorizenet_transaction_key']);
define('ECOMMERCE_CLEARCOMMERCE_CLIENT_ID', $row['ecommerce_clearcommerce_client_id']);
define('ECOMMERCE_CLEARCOMMERCE_USER_ID', $row['ecommerce_clearcommerce_user_id']);
define('ECOMMERCE_CLEARCOMMERCE_PASSWORD', $row['ecommerce_clearcommerce_password']);
define('ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_STORE_NUMBER', $row['ecommerce_first_data_global_gateway_store_number']);
define('ECOMMERCE_FIRST_DATA_GLOBAL_GATEWAY_PEM_FILE_NAME', $row['ecommerce_first_data_global_gateway_pem_file_name']);
define('ECOMMERCE_PAYPAL_PAYFLOW_PRO_PARTNER', $row['ecommerce_paypal_payflow_pro_partner']);
define('ECOMMERCE_PAYPAL_PAYFLOW_PRO_MERCHANT_LOGIN', $row['ecommerce_paypal_payflow_pro_merchant_login']);
define('ECOMMERCE_PAYPAL_PAYFLOW_PRO_USER', $row['ecommerce_paypal_payflow_pro_user']);
define('ECOMMERCE_PAYPAL_PAYFLOW_PRO_PASSWORD', $row['ecommerce_paypal_payflow_pro_password']);
define('ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_USERNAME', $row['ecommerce_paypal_payments_pro_api_username']);
define('ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_PASSWORD', $row['ecommerce_paypal_payments_pro_api_password']);
define('ECOMMERCE_PAYPAL_PAYMENTS_PRO_API_SIGNATURE', $row['ecommerce_paypal_payments_pro_api_signature']);
define('ECOMMERCE_SAGE_MERCHANT_ID', $row['ecommerce_sage_merchant_id']);
define('ECOMMERCE_SAGE_MERCHANT_KEY', $row['ecommerce_sage_merchant_key']);
define('ECOMMERCE_STRIPE_API_KEY', $row['ecommerce_stripe_api_key']);
define('ECOMMERCE_SURCHARGE_PERCENTAGE', $row['ecommerce_surcharge_percentage']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT', $row['ecommerce_paypal_express_checkout']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_TYPE', $row['ecommerce_paypal_express_checkout_transaction_type']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_MODE', $row['ecommerce_paypal_express_checkout_mode']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_USERNAME', $row['ecommerce_paypal_express_checkout_api_username']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_PASSWORD', $row['ecommerce_paypal_express_checkout_api_password']);
define('ECOMMERCE_PAYPAL_EXPRESS_CHECKOUT_API_SIGNATURE', $row['ecommerce_paypal_express_checkout_api_signature']);
define('ECOMMERCE_OFFLINE_PAYMENT', $row['ecommerce_offline_payment']);
define('ECOMMERCE_OFFLINE_PAYMENT_ONLY_SPECIFIC_ORDERS', $row['ecommerce_offline_payment_only_specific_orders']);
define('ECOMMERCE_PRIVATE_FOLDER_ID', $row['ecommerce_private_folder_id']);
define('ECOMMERCE_RETRIEVE_ORDER_NEXT_PAGE_ID', $row['ecommerce_retrieve_order_next_page_id']);
define('ECOMMERCE_REWARD_PROGRAM', $row['ecommerce_reward_program']);
define('ECOMMERCE_REWARD_PROGRAM_POINTS', $row['ecommerce_reward_program_points']);
define('ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP', $row['ecommerce_reward_program_membership']);
define('ECOMMERCE_REWARD_PROGRAM_MEMBERSHIP_DAYS', $row['ecommerce_reward_program_membership_days']);
define('ECOMMERCE_REWARD_PROGRAM_EMAIL', $row['ecommerce_reward_program_email']);
define('ECOMMERCE_REWARD_PROGRAM_EMAIL_BCC_EMAIL_ADDRESS', $row['ecommerce_reward_program_email_bcc_email_address']);
define('ECOMMERCE_REWARD_PROGRAM_EMAIL_SUBJECT', $row['ecommerce_reward_program_email_subject']);
define('ECOMMERCE_REWARD_PROGRAM_EMAIL_PAGE_ID', $row['ecommerce_reward_program_email_page_id']);
define('ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL', $row['ecommerce_custom_product_field_1_label']);
define('ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL', $row['ecommerce_custom_product_field_2_label']);
define('ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL', $row['ecommerce_custom_product_field_3_label']);
define('ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL', $row['ecommerce_custom_product_field_4_label']);		
define('ECOMMERCE_IYZIPAY_API_KEY', $row['ecommerce_iyzipay_api_key']);		
define('ECOMMERCE_IYZIPAY_SECRET_KEY', $row['ecommerce_iyzipay_secret_key']);		
define('ECOMMERCE_IYZIPAY_INSTALLMENT', $row['ecommerce_iyzipay_installment']);		
define('ECOMMERCE_IYZIPAY_THREEDS', $row['ecommerce_iyzipay_threeds']);
define('FORMS', $row['forms']);
define('CALENDARS', $row['calendars']);
define('ADS', $row['ads']);
define('AFFILIATE_PROGRAM', $row['affiliate_program']);
define('AFFILIATE_DEFAULT_COMMISSION_RATE', $row['affiliate_default_commission_rate']);
define('AFFILIATE_AUTOMATIC_APPROVAL', $row['affiliate_automatic_approval']);
define('AFFILIATE_CONTACT_GROUP_ID', $row['affiliate_contact_group_id']);
define('AFFILIATE_EMAIL_ADDRESS', $row['affiliate_email_address']);
define('AFFILIATE_GROUP_OFFER_ID', $row['affiliate_group_offer_id']);
define('MAILCHIMP', $row['mailchimp']);
define('DEBUG', $row['debug']);
define('SEO_ANALYSES', $row['number_of_queries']);
define('NUMBER_OF_EMAIL_RECIPIENTS', $row['number_of_email_recipients']);
define('SPECIAL_USER_ID', $row['special_user_id']);
define('LAST_SITEMAP_CHECK_TIMESTAMP', $row['last_sitemap_check_timestamp']);
define('LAST_SITEMAP_CHECK_HASH', $row['last_sitemap_check_hash']);
define('LAST_RECURRING_COMMISSION_CHECK_TIMESTAMP', $row['last_recurring_commission_check_timestamp']);
define('INSTALLER', $row['installer']);
define('SUBSCRIPTION_KEY', $row['subscription_key']);
define('SOFTWARE_LANGUAGE', $row['software_language']);


// If PHP is at least v5.1.3, then update timezone in PHP and MySQL.
// The timezone functions were added in v5.1.0, however the date('P') feature that we are using
// below to update MySQL was not added until v5.1.3, so that is why we are choosing that version.
if (version_compare(PHP_VERSION, '5.1.3', '>=') == true) {
    // Define the server timezone before we update the default timezone, so that we can know the server's default
    // timezone for the default option in the site settings.
    define('SERVER_TIMEZONE', @date_default_timezone_get());

    // If there is a timezone set in the site settings, then update PHP to use that.
    if (TIMEZONE != '') {
        date_default_timezone_set(TIMEZONE);

    // Otherwise there is not a timezone set, so update PHP to use server's timezone.
    } else {
        date_default_timezone_set(SERVER_TIMEZONE);
    }

    // Update timezone in MySQL to match PHP timezone.
    db("SET time_zone='" . date('P') . "'");

// Otherwise this is a site with an old PHP version, so just set the server timezone to empty string.
} else {
    define('SERVER_TIMEZONE', '');
}

// if the path is not defined yet, then that means this request was made from a cron job,
// so set path to value from the database
if (defined('PATH') == FALSE) {
    define('PATH', $row['path']);

    // prepare escaped version of path because we will use this a lot
    define('OUTPUT_PATH', h(PATH));

// else the path is defined, so if the defined path is different from the path in the database,
// then update the path in the database, so that the next time the e-mail campaign job runs,
// it will have the correct path
} else if (PATH != $row['path']) {
    $query = "UPDATE config SET path = '" . escape(PATH) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

// Check if we need to set a default value for logo_url
if (!defined('LOGO_URL')) {
    define('LOGO_URL', PATH . SOFTWARE_DIRECTORY . '/images/logo.png?v=' . @filemtime(dirname(__FILE__) . '/images/logo.png'));
}

$query = "SELECT * FROM config";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$software_theme = $row['software_theme'];
// if software theme set config from sql than use new theme
if($software_theme != NULL){
    // Check if we need to set a default value for control_panel_stylesheet_url
    if (!defined('CONTROL_PANEL_STYLESHEET_URL')) {
        define('CONTROL_PANEL_STYLESHEET_URL', PATH . SOFTWARE_DIRECTORY . '/backend.'. ENVIRONMENT_SUFFIX . '.css?v=' . @filemtime(dirname(__FILE__) . '/backend.' . ENVIRONMENT_SUFFIX . '.css'));
    }
}else{
    if (!defined('CONTROL_PANEL_STYLESHEET_URL')) {
            define('CONTROL_PANEL_STYLESHEET_URL', PATH . SOFTWARE_DIRECTORY . '/old_backend.min.css?v=' . @filemtime(dirname(__FILE__) . '/old_backend.min.css'));
    }
}

// if magic quotes is on, remove slashes from data so our data is clean
if (get_magic_quotes_gpc()) {
    $_REQUEST = array_stripslashes($_REQUEST);
    $_GET = array_stripslashes($_GET);
    $_POST = array_stripslashes($_POST);
    $_COOKIE = array_stripslashes($_COOKIE);
}

// If this request was made over the web instead of a cron job,
// then deal with forcing secure mode and starting session.
if ($_SERVER['HTTP_HOST'] != '') {
    // Determine if request is secure or not.  We will use this in a couple of places below.
    $secure_request = check_if_request_is_secure();

    // If secure mode is enabled, and the visitor is not in secure mode,
    // and REQUIRE_SECURE_MODE has not been disabled in the config.php file,
    // then don't complete this request for the visitor.  REQUIRE_SECURE_MODE
    // is a constant that we added support for during the v8.6 release, so if
    // forcing secure mode like we are now in v8.6+, creates problems, then we have an easy way
    // for people to disable it without having to release a patch.
    if (
        (URL_SCHEME == 'https://')
        && ($secure_request == false)
        && 
        (
            (defined('REQUIRE_SECURE_MODE') == false)
            || (REQUIRE_SECURE_MODE !== false)
        )
        && ($_GET['secure_mode'] != 'false')
    ) {
        // If the visitor sent a post request (i.e. submitted a form),
        // then output error because we don't want to allow people
        // to do that insecurely.  With post requests, we don't have any way
        // of redirecting the request to a secure request, plus we don't want
        // to encourage this anyway.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            output_error('Sorry, this website does not allow insecure requests. Please submit the form to a secure address (i.e. "https" instead of "http").', 403);

        // Otherwise the visitor sent a get request, so redirect visitor to secure URL.
        // We use a 301 redirect so that search engines and etc. will use the secure URL.
        } else {
            header('Location: https://' . HOSTNAME_SETTING . REQUEST_URL, true, 301);
            exit();
        }

    // Otherwise if the request is secure, and the secure_mode query string value is set to false,
    // then redirect visitor to non-secure URL.
    } else if (
        ($secure_request == true)
        && ($_GET['secure_mode'] == 'false')
    ) {
        header('Location: http://' . HOSTNAME_SETTING . REQUEST_URL, true, 301);
        exit();
    }

    // If secure mode is enabled, then setup the sesson cookie
    // so that it is only sent in secure mode.  We do this so that if a visitor
    // accidentally requests an insecure URL, then their session id is not sent in clear text
    // which would allow their session to be hijacked.
    if (URL_SCHEME == 'https://') {
        ini_set('session.cookie_secure', true);
    }

    // If PHP version is greater or equal to 5.2.0 then
    // set the session cookie so that it is not available through JavaScript.
    // This prevents various hacking methods.
    if (version_compare(PHP_VERSION, '5.2.0', '>=') == TRUE) {
        ini_set('session.cookie_httponly', true);
    }

    // We purposely start the session down here below the secure mode requirement
    // so that a session is not started if a visitor accidentally requests an insecure URL.
    // This prevents session hijacking.
    session_start();
}

define('PRIVATE_LABEL', $private_label);

// Initialize user in order to handle remember me and set user information if user is logged in.
initialize_user();

// If the software update check/messages update is not disabled in the config.php file,
// and it is currently at least 24 hours after the last software update check, then complete check.
// CHECK would likely only be disabled for a site on a server without Internet access.
// It gives us a way to disable the check so that there is not a timeout delay for sites like that.
if (
    (
        (defined('CHECK') == false)
        or (CHECK == true)
    )
    and ($current_timestamp >= (LAST_SOFTWARE_UPDATE_CHECK_TIMESTAMP + 86400))
) {

    require(dirname(__FILE__) . '/software_update_check.php');

    $software_update_available = software_update_check();
}

// if a software update check was just completed, then set constant to that value
if (isset($software_update_available)) {

    if ($software_update_available) {
        define('SOFTWARE_UPDATE_AVAILABLE', TRUE);
    } else {
        define('SOFTWARE_UPDATE_AVAILABLE', FALSE);
    }
    
// else a software update check was not just completed, so set constant to value from database
} else {
    define('SOFTWARE_UPDATE_AVAILABLE', $original_software_update_available);
}

// if it is currently at least 24 hours after the last sitemap check,
// then check if sitemap has changed in order to determine if we should alert search engines about new sitemap
if ($current_timestamp >= (LAST_SITEMAP_CHECK_TIMESTAMP + 86400)) {
    // update the config table to remember that the check has been completed today
    $query = "UPDATE config SET last_sitemap_check_timestamp = UNIX_TIMESTAMP()";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    require_once(dirname(__FILE__) . '/get_sitemap_info.php');
    
    // determine if URL's exist and get sitemap content
    $sitemap_info = get_sitemap_info();
    
    // if there is at least one URL in the sitemap, then determine if we need to alert search engines
    if ($sitemap_info['urls_exist'] == TRUE) {
        // generate hash of new sitemap content
        $sitemap_check_hash = md5($sitemap_info['content']);
        
        // if the hash has changed, then that means the sitemap content has changed, so alert search engines
        if ($sitemap_check_hash != LAST_SITEMAP_CHECK_HASH) {
            $sitemap_url = URL_SCHEME . HOSTNAME_SETTING . PATH . 'sitemap.xml';
            
            // alert Google
            $response = @file_get_contents('http://www.google.com/webmasters/tools/ping?sitemap=' . urlencode($sitemap_url));
            
            // if there was a communication problem with Google then log activity
            if ($response === FALSE) {
                log_activity('daily sitemap.xml check could not alert Google because of a communication error. Make sure allow_url_fopen is enabled in PHP.', '');
            }
            
            // alert Bing
            $response = @file_get_contents('http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode($sitemap_url));
            
            // if there was a communication problem with Bing then log activity
            if ($response === FALSE) {
                log_activity('daily sitemap.xml check could not alert Bing because of a communication error. Make sure allow_url_fopen is enabled in PHP.', '');
            }
            
            // log that we attempted to alert search engines about new sitemap
            log_activity('daily sitemap.xml check alerted search engines about updated sitemap.xml', '');
            
            // update hash value in config table
            $query = "UPDATE config SET last_sitemap_check_hash = '" . $sitemap_check_hash . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }
}

// detect and set device type (i.e. desktop or mobile) in session and cookie if it has not already been done
initialize_device_type();

// Generate token and add it to session for visitor if it has not already been done in order to prevent CSRF attacks.
// We use this token in so many places (for post and get requests) so that is why we do this here.
initialize_token();

// If ecommerce is enabled, then check order and prepare currency values.
if (ECOMMERCE == true) {

    // If there is an order in this visitor's session, then check if order is still an incomplete
    // order that is allowed to be active for visitor.  This is important, because there might
    // be multiple users accessing the same order, at the same time.  So, if one user completes
    // the order, then we need to remove the order from the session for the other user.  This
    // will prevent the cart region, cart, and etc. from showing the completed order for the
    // other user.

    if ($_SESSION['ecommerce']['order_id']) {

        $order = db_item(
            "SELECT status FROM orders WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'");

        // If order was not found (e.g. deleted), or has already been completed, then this order
        // is not allowed to be active for the visitor, so remove it from session.
        if (!$order or $order['status'] != 'incomplete') {
            unset($_SESSION['ecommerce']['order_id']);
        }
    }

    // Get base currency.
    $base_currency = db_item(
        "SELECT
            id,
            code,
            symbol,
            exchange_rate
        FROM currencies
        WHERE base = '1'");

    // If a base currency was found, then set constants for that currency.
    if ($base_currency['id'] != '') {
        define('BASE_CURRENCY_ID', $base_currency['id']);
        define('BASE_CURRENCY_CODE', $base_currency['code']);
        define('BASE_CURRENCY_SYMBOL', $base_currency['symbol']);

    // Otherwise a base currency was not found, so use USD.
    } else {
        define('BASE_CURRENCY_ID', 0);
        define('BASE_CURRENCY_CODE', 'USD');
        define('BASE_CURRENCY_SYMBOL', '$');
    }

    define('BASE_CURRENCY_EXCHANGE_RATE', 1);

    $visitor_currency = array();

    // If multi-currency is enabled in the site settings,
    // and the visitor has updated the currency,
    // and the updated currency is different from the base currency,
    // then get info about the visitor's currency.
    if (
        (ECOMMERCE_MULTICURRENCY == true)
        && (isset($_SESSION['ecommerce']['currency_id']) == true)
        && ($_SESSION['ecommerce']['currency_id'] != BASE_CURRENCY_ID)
    ) {
        $visitor_currency = db_item(
            "SELECT
                id,
                code,
                symbol,
                exchange_rate
            FROM currencies
            WHERE id = '" . escape($_SESSION['ecommerce']['currency_id']) . "'");
    }

    // If a visitor currency was found, then prepare constants.
    if ($visitor_currency['id'] != '') {
        define('VISITOR_CURRENCY_ID', $visitor_currency['id']);
        define('VISITOR_CURRENCY_CODE', $visitor_currency['code']);
        define('VISITOR_CURRENCY_CODE_FOR_OUTPUT', ' ' . $visitor_currency['code']);
        define('VISITOR_CURRENCY_SYMBOL', $visitor_currency['symbol']);
        define('VISITOR_CURRENCY_EXCHANGE_RATE', $visitor_currency['exchange_rate']);

    // Otherwise a visitor currency was not found, so just use base currency for visitor currency.
    } else {
        define('VISITOR_CURRENCY_ID', BASE_CURRENCY_ID);
        define('VISITOR_CURRENCY_CODE', BASE_CURRENCY_CODE);
        define('VISITOR_CURRENCY_CODE_FOR_OUTPUT', '');
        define('VISITOR_CURRENCY_SYMBOL', BASE_CURRENCY_SYMBOL);
        define('VISITOR_CURRENCY_EXCHANGE_RATE', BASE_CURRENCY_EXCHANGE_RATE);
    }
}
//initialize the developer page security page redirect for pin locked pages.
initialize_developer_security();

//check who is online for users, every 50 sec. after last check.
// we check user if last check status greater than 50 sec. because of performance.
// if you know what do you do, can increase or decrease it.
who_is_online(50);

//encode_types for language needs
//default - english
$utf_encode ='UTF-8';
//turkish
$tr_encode ='ISO-8859-9';
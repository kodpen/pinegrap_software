<?php

/*
  This file is just an example of a config.php file.
  You should not have to edit this file if you install Pinegrap correctly.
  This file can be used if the [software directory]/config/config.php file is accidently deleted after installation is completed.
  In order to use this file, please uncomment the lines below, enter values for the settings, and upload this file to [software directory]/config/ with config.php name.
*/

//Database connection configuration.
//This is create with software installation.
define('DB_HOST', ''); //Host Name, adress or ip. eg. localhost or host.example.com
define('DB_USERNAME', ''); //Username which has access datebase to connect.
define('DB_PASSWORD', ''); //Password of Username which has access datebase to connect.
define('DB_DATABASE', ''); // datebase Name

// If this site has enabled the legacy MySQL connection in the config.php and it is using a version
// of PHP that supports that (i.e. before PHP 7), then create a legacy DB connection.  Sites might
// enable this feature because they have old custom code in hook code, styles, custom layouts, or etc.
// that uses the old mysql extension and relies on Pinegrap to start that connection.
// If a site enables this feature, then a second db connection will be opened for every request,
// which might have a negative performance effect.
defined('DB_LEGACY', ''); //true value will enable it

//Secret Encryption key.
//This is create with sowtware installation and do not have to modify.
//DO NOT MODIFY OR SHARE
define('ENCRYPTION_KEY', ''); 

//Dynamic Region let to use dynamic php codes in page with regions.
//Before using read the documentations.
define('DYNAMIC_REGIONS', true); //false value or remove define will disable it.

//PHP Region is let to use php codes in pages from Page Designer and in regions.
//Before using read the documentations.
define('PHP_REGIONS', true); //false value or remove define will disable it.

//System Smtp Mail Setup. 
//This setup can add or modify from site settings > Smtp Settings Tab with inputs.
define('SYSTEM_SMTP_HOSTNAME', ''); 
define('SYSTEM_SMTP_USERNAME', ''); 
define('SYSTEM_SMTP_PASSWORD', ''); 
define('SYSTEM_SMTP_PORT', ''); //default is 587

//Email Campaign Smtp Mail Setup.
//This setup can add or modify from site settings > Smtp Settings Tab with inputs.
//Remember that; Email Campaign Job is required set cron job from server.
define('EMAIL_CAMPAIGN_JOB', true);
define('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '25'); //25 is default
define('CAMPAIGN_SMTP_HOSTNAME', ''); 
define('CAMPAIGN_SMTP_USERNAME', ''); 
define('CAMPAIGN_SMTP_PASSWORD', ''); 
define('CAMPAIGN_SMTP_PORT', ''); //default is 587

//If the ENVIRONMENT constant is set to "development", then set the ENVIRONMENT_SUFFIX to "src".
//This allows us to use source files instead of minified files during development.
define('ENVIRONMENT','');

//Edition is changes nothing. its just shown on backend foooters.
define('EDITION',''); //default PRO

//We found the new page locking feature necessary after updating the SMTP settings online via the software. 
//It can be used to prevent site administrators from seeing the information entered here except for developers.
//Developers define to config.php to page name and an unlock pin to lock pages.
//If page name define like below, users redirect to developer_lock.php. 
//Software ask's pin code to unlock and access to page.
//If user enter pin  correct, software redirect user to page try to access and no redirect developer_lock.php until Pin code is change. 
//IMPORTANT: if you set not 4 digit DEVELOPER_PIN, locked pages unaccessable for nobody.
//defines e.g.:
define('DEVELOPER_PIN', '');//4 digit eg. '1234'
define('smtp_settings.php', true);//just for example
define('welcome.php', true);//just for example

//Software Backend Logo url.
define('LOGO_URL',''); //default logo is from software_directory/images/logo.png

//Control Panel Stylesheet url is control of css codes of backend look.
define('CONTROL_PANEL_STYLESHEET_URL',''); //default Control Panel Stylesheet url is from software_directory/backend.min.css

// If an admin has not specifically requested that error reporting not be set by Pinegrap, then
// set error_reporting to what is generally best for Pinegrap. Don't show PHP notices, strict,
// and deprecated messages. E_DEPRECATED is only available in newer PHP versions.  We allow
// an admin to disable this by setting SET_ERROR_REPORTING to false in config.php, because in
// PHP 7.2+, PHP is showing more warnings, so an admin might not want Pinegrap to control this.
define('SET_ERROR_REPORTING','');
define('E_DEPRECATED','');
?>
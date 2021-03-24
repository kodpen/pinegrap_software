<?php

/**
 *
 * PineGrap - Enterprise Website Platform
 *
 * @author      Kodpen
 * @link        https://kodpen.com
 * @copyright   2007-2020 Kodpen
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

// If an admin has not specifically requested that error reporting not be set by PineGrap, then
// set error_reporting to what is generally best for PineGrap. Don't show PHP notices, strict,
// and deprecated messages. E_DEPRECATED is only available in newer PHP versions.  We allow
// an admin to disable this by setting SET_ERROR_REPORTING to false in config.php, because in
// PHP 7.2+, PHP is showing more warnings, so an admin might not want PineGrap to control this.


if (!defined('SET_ERROR_REPORTING') or SET_ERROR_REPORTING)
  {

    if (defined('E_DEPRECATED'))
      {

        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

      }
    else
      {

        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT);

      }

  }

ini_set('max_execution_time', '9999');

ini_set('default_charset', 'utf-8');

mb_internal_encoding('UTF-8');

mb_http_output('UTF-8');

// Define a constant so that if an error occurs the error handling function
// will know to output the database error, regardless of what the debug setting is set to,
// because we always want to output the database error if an error happens in this script.
define('INSTALL_OR_UPDATE', true);

$automated_upgrade = false;

// if this is being run from an automated upgrade process, then remember that
if (

($argv[1] == 'automated_upgrade')
 || ($_REQUEST['automated_upgrade'] == 'true')
)
  {

    $automated_upgrade = true;

  }

require (dirname(__FILE__) . '/../config/config.php');

require (dirname(__FILE__) . '/../functions.php');

//Backup Files Directory
function backup_list($directory)
  {

    $directory_path = 'backups/';

    foreach (array_diff(scandir($directory_path) , array(
        '..',
        '.'
    )) as $backup_folder) if (is_dir($directory_path . '/' . $backup_folder)) $l[] = $backup_folder;

    return $l;

  }

$directory = backup_list(getcwd());

if ($directory)
  {

    $backup_value .= '<option value="." selected disabled>Select</option>';

    foreach ($directory as $backup_folder)
      {

        $backup_value .= '<option value="' . $backup_folder . '">' . $backup_folder . '</option>';

      }

    $backup_values = array(
        $backup_value
    );

  }

// if this script is not being called from an automated upgrade script, then start session
if ($automated_upgrade == false)
  {

    // If this is a secure request then prepare to start a secure session.
    // We do this so that if a visitor accidentally requests an insecure URL,
    // then their session id is not sent in clear text
    // which would allow their session to be hijacked.
    if (check_if_request_is_secure() == true)
      {

        ini_set('session.cookie_secure', true);

      }

    // If PHP version is greater or equal to 5.2.0 then
    // set the session cookie so that it is not available through JavaScript.
    // This prevents various hacking methods.
    if (version_compare(PHP_VERSION, '5.2.0', '>=') == true)
      {

        ini_set('session.cookie_httponly', true);

      }

    session_start();

  }

// certain versions of PHP 5.3+ will display a warning if a timezone is not set
// (e.g. date.timezone not being set in the php.ini file),
// so we are going to force a timezone to be set
if (

(ini_get('date.timezone') == false)
 && (function_exists('date_default_timezone_set') == true)
)
  {

    date_default_timezone_set(@date_default_timezone_get());

  }

// if magic quotes is on, remove slashes from data so our data is clean
if (get_magic_quotes_gpc())
  {

    $_GET = array_stripslashes($_GET);

    $_POST = array_stripslashes($_POST);

    $_COOKIE = array_stripslashes($_COOKIE);

  }

// if this server is on Windows, then path delimiter is a backslash
if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) == 'WIN')
  {

    $delimiter = '\\';

    // else this server is not on Windows, so path delimiter is a forward slash
    
  }
else
  {

    $delimiter = '/';

  }

$path_parts = explode($delimiter, dirname(__FILE__));

define('SOFTWARE_DIRECTORY', $path_parts[count($path_parts) - 2]);

// prepare escaped version of software directory
define('OUTPUT_SOFTWARE_DIRECTORY', h(SOFTWARE_DIRECTORY));

// get the path by going 3 levels up from the current script request
$url_path = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));

// convert backslashes to forward slashes
// backslashes seem to only appear on Windows when only the root is left (e.g. \).
$url_path = str_replace('\\', '/', $url_path);

// if the path is not the root, then add a slash on the end
if ($url_path != '/')
  {

    $url_path .= '/';

  }

define('PATH', $url_path);

// prepare escaped version of path
define('OUTPUT_PATH', h(PATH));

// If a config file path is not set, then set it to the default which is a path
// inside the software directory. A custom config file path is used when
// an adminstrator wants the config file to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('CONFIG_FILE_PATH') == false)
  {

    define('CONFIG_FILE_PATH', dirname(__FILE__) . '/../config/config.php');

  }

// If a file directory path is not set, then set it to the default which is a path
// inside the software directory. A custom file directory path is used when
// an adminstrator wants the file directory to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('FILE_DIRECTORY_PATH') == false)
  {

    define('FILE_DIRECTORY_PATH', dirname(__FILE__) . '/../files');

  }

// If a layout directory path is not set, then set it to the default which is a path
// inside the software directory. A custom layout directory path is used when
// an adminstrator wants the layout directory to be located in a different area.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (!defined('LAYOUT_DIRECTORY_PATH'))
  {

    define('LAYOUT_DIRECTORY_PATH', dirname(__FILE__) . '/../layouts');

  }

// If an htaccess file path is not set, then set it to the default which is a path
// in the web root. A custom htaccess file path is used when the .htaccess file is
// is located in a separate location from the software.
// For example, this is required under a multitenant architecture where multiple sites
// are using the same software directory.
if (defined('HTACCESS_FILE_PATH') == false)
  {

    // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,
    // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).
    

    // If the web server is IIS then set the htaccess file info to the httpd.ini location.
    if (stristr($_SERVER['SERVER_SOFTWARE'], 'iis'))
      {

        define('HTACCESS_FILE_PATH', dirname(__FILE__) . '/../../httpd.ini');

        define('HTACCESS_FILE_NAME', 'httpd.ini');

        // Otherwise the web server is Apache, so set the htaccess file info to the .htaccess location.
        
      }
    else
      {

        define('HTACCESS_FILE_PATH', dirname(__FILE__) . '/../../.htaccess');

        define('HTACCESS_FILE_NAME', '.htaccess');

      }

  }

// If the ENVIRONMENT constant is set to "development", then set the ENVIRONMENT_SUFFIX to "src".
// This allows us to use source files instead of minified files during development.
if (defined('ENVIRONMENT') and ENVIRONMENT == 'development')
  {

    define('ENVIRONMENT_SUFFIX', 'src');

    // Otherwise the ENVIRONMENT constant is not defined or set to something else, so set the ENVIRONMENT_SUFFIX to "min".
    
  }
else
  {

    define('ENVIRONMENT_SUFFIX', 'min');

  }

// if this script is not being called from an automated upgrade script,
// then generate token and add it to session for visitor if it has not already been done in order to prevent CSRF attacks.
if ($automated_upgrade == false)
  {

    initialize_token();

  }

// create liveform object for form handling
include_once (dirname(__FILE__) . '/../liveform.class.php');

$liveform = new liveform('install');

$versions = array(

    array(
        'number' => '2017.2'
    ) ,

    array(
        'number' => '2017.2.1'
    ) ,

    array(
        'number' => '2017.2.2'
    ) ,

    array(
        'number' => '2017.2.3'
    ) ,

    array(
        'number' => '2017.2.4'
    ) ,

    array(
        'number' => '2017.2.5'
    ) ,

    array(
        'number' => '2017.2.6'
    ) ,

    array(
        'number' => '2017.2.7'
    ) ,

    array(
        'number' => '2017.2.8'
    ) ,

    array(
        'number' => '2017.2.9'
    ) ,

    array(
        'number' => '2017.2.10'
    ) ,

    array(
        'number' => '2017.2.11'
    ) ,

    array(
        'number' => '2017.2.12'
    ) ,

    array(
        'number' => '2017.2.13'
    ) ,

    array(
        'number' => '2019.1'
    ) ,

    array(
        'number' => '2019.1.1'
    ) ,

    array(
        'number' => '2019.1.2'
    ) ,

    array(
        'number' => '2019.1.3'
    ) ,

    array(
        'number' => '2019.1.4'
    ) ,

    array(
        'number' => '2019.1.5'
    ) ,

    array(
        'number' => '2019.1.6'
    ) ,

    array(
        'number' => '2019.1.7'
    ) ,

    array(
        'number' => '2019.1.8'
    ) ,

    array(
        'number' => '2019.1.9'
    ) ,

    array(
        'number' => '2019.1.10'
    ) ,

    array(
        'number' => '2019.2'
    ) ,

    array(
        'number' => '2019.2.1'
    ) ,

    array(
        'number' => '2019.2.2'
    ) ,

    array(
        'number' => '2019.2.3'
    ) ,

    array(
        'number' => '2019.2.4'
    ) ,

    array(
        'number' => '2019.2.5'
    ) ,

    array(
        'number' => '2019.2.6'
    ) ,

    array(
        'number' => '2019.2.7'
    ) ,

    array(
        'number' => '2019.2.8'
    ) ,

    array(
        'number' => '2019.2.9'
    ) ,

    array(
        'number' => '2020.1'
    ) ,

    array(
        'number' => '2020.1.1'
    ) ,

    array(
        'number' => '2020.1.2'
    ) ,

    array(
        'number' => '2020.1.3'
    ) ,

    array(
        'number' => '2020.1.4'
    ) ,

    array(
        'number' => '2020.1.5'
    ) ,

    array(
        'number' => '2020.1.6'
    ) ,

    array(
        'number' => '2020.1.7'
    ) ,

    array(
        'number' => '2020.1.8'
    ) ,

    array(
        'number' => '2020.2'
    ) ,

    array(
        'number' => '2020.2.1'
    ) ,

    array(
        'number' => '2020.2.2'
    ) ,

    array(
        'number' => '2020.2.3'
    ) ,

    array(
        'number' => '2020.2.4'
    ) ,

    array(
        'number' => '2020.2.5'
    ) ,

    array(
        'number' => '2020.3'
    ) ,

    array(
        'number' => '2020.3.1'
    ) ,

    array(
        'number' => '2020.3.2'
    ) ,

    array(
        'number' => '2020.3.3'
    ) ,

    array(
        'number' => '2020.3.4'
    ) ,

    array(
        'number' => '2020.4'
    ) ,

    array(
        'number' => '2020.4.1'
    ) ,

    array(
        'number' => '2020.4.2'
    ) ,

    array(
        'number' => '2021.1'
    ) ,

    array(
        'number' => '2021.1.1'
    ) ,

    array(
        'number' => '2021.1.2'
    ) ,

    array(
        'number' => '2021.1.3'
    ) ,

    array(
        'number' => '2021.1.4'
    ) ,

    array(
        'number' => '2021.1.5'
    ) ,

    array(
        'number' => '2021.1.6'
    ) ,

    array(
        'number' => '2021.1.7'
    ) ,

    array(
        'number' => '2021.1.8'
    ) ,

    array(
        'number' => '2021.1.9'
    ) ,

    array(
        'number' => '2021.1.10'
    ) ,

    array(
        'number' => '2021.1.11'
    ) ,

    array(
        'number' => '2021.1.12'
    ) ,

    array(
        'number' => '2021.1.13'
    ) ,

    array(
        'number' => '2021.1.14'
    ) ,

    array(
        'number' => '2021.2'
    ) ,

    array(
        'number' => '2021.2.1'
    ) ,

);

$software_version = $versions[count($versions) - 1]['number'];

$software_version_key = count($versions) - 1;

class db
  {

    public static $con;

  }

// if there are database constants in config.php and we can't connect to the database or select the database, then output error
// this is done in order to make sure that someone does not install over an existing site while a database is just having connection issues
if (

(defined('DB_HOST') == true)
 and (DB_HOST != '')
 and (!(db::$con = @mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE)))
)
  {

    exit('It appears that a site may already be installed here.  If you want to reinstall please remove all content from the config.php file and then refresh this page.');

  }

// if user has not yet completed install form and this is not being run as part of an automated upgrade, output form
if ((!isset($_POST['submit'])) && ($automated_upgrade == false))
  {

    // initialize variables
    $upgrade_option = false;

    $output_upgrade_message = '';

    $output_submit_button_label = 'Install';

    $output_upgrade = '';

    // if DB_HOST is defined,
    // and a connection can be made to the database,
    // and the database can be selected,
    // then check if software is already installed
    if (

    (defined('DB_HOST') == true)
 and (db::$con = @mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE))
)
      {

        init_mysql_charset();

        // Disable MySQL strict mode, because later versions of MySQL enable strict mode by default,
        // and PineGrap is not compatible with strict mode.  This will also remove all other sql modes,
        // however that should be fine.
        mysqli_query(db::$con, "SET SESSION sql_mode = ''");

        // initialize variable
        $software_installed = false;

        // get all tables in database in order to determine if software is already installed
        $query = "SHOW TABLES";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        while ($row = mysqli_fetch_row($result))
          {

            if (($row[0] == 'config') || ($row[0] == 'page') || ($row[0] == 'user'))
              {

                $software_installed = true;

                break;

              }

          }

        // if software is already installed, then check if upgrade option should be given
        if ($software_installed == true)
          {

            $database_version = get_database_version();

            // if the database version cannot be found, then it is probably before 4.5.0, so prepare notice
            if ($database_version == false)
              {

                $output_upgrade_message = 'It appears that the software is already installed, however the version is less than 4.5.0. The upgrade feature only supports version 4.5.0 and greater. Alternatively, if you want to replace the existing site with a new site, you may complete the install form below. ';

                // else the database version can be found, so continue
                
              }
            else
              {

                $database_version_key = get_version_key($database_version, $versions);

                // if database version key is less than software version key, then offer upgrade option
                if ($database_version_key < $software_version_key)
                  {

                    $upgrade_option = true;

                    $output_upgrade_message = 'Please select whether you want to upgrade the existing site or replace the existing site with a new site. ';

                    // if an install type has not already been selected, then select upgrade by default
                    if ($liveform->get_field_value('install_type') != 'install')
                      {

                        $liveform->assign_field_value('install_type', 'upgrade');

                        $output_submit_button_label = 'Upgrade';

                      }

                    // if upgrade is selected for install type then show upgrade fields
                    if ($liveform->get_field_value('install_type') == 'upgrade')
                      {

                        $output_upgrade_fields_style = '';

                      }
                    else
                      {

                        $output_upgrade_fields_style = '; display: none';

                      }

                    // initialize variables
                    $output_upgrade_authentication = '';

                    // if user is not logged in or is not an administrator, then display authentication rows
                    if (check_if_administrator_is_logged_in() == false)
                      {

                        $output_upgrade_authentication =

                        '<fieldset style="margin-bottom: 1em">

								<legend><strong>Authentication</strong></legend>

								<div style="padding: 1em">

									<div style="margin-bottom: 1em">Please enter the email address and password for an administrator for the existing site. If you cannot remember your login information you can use the <a href="../forgot_password.php" target="_blank">forgot password</a> feature.</div>

									<table>

										<tr>

											<td>Email:</td>

											<td>' . $liveform->output_field(array(
                            'type' => 'text',
                            'name' => 'upgrade_authentication_username',
                            'size' => '30'
                        )) . '</td>

										</tr>

										<tr>

											<td>Password:</td>

											<td>' . $liveform->output_field(array(
                            'type' => 'password',
                            'name' => 'upgrade_authentication_password',
                            'size' => '30'
                        )) . '</td>

										</tr>

									</table>

								</div>

							</fieldset>';

                      }

                    $output_upgrade =

                    '<div style="margin-bottom: 1em">

							<div style="margin-bottom: 1em">' . $liveform->output_field(array(
                        'type' => 'radio',
                        'name' => 'install_type',
                        'id' => 'upgrade',
                        'value' => 'upgrade',
                        'class' => 'radio',
                        'onclick' => 'show_or_hide_upgrade_install()'
                    )) . '<label for="upgrade"> Upgrade from version ' . $database_version . ' to ' . $software_version . '</label></div>

							<div id="upgrade_fields" style="margin-left: 2em' . $output_upgrade_fields_style . '">

								<fieldset style="margin-bottom: 1em">

									<legend><strong>Instructions</strong></legend>

									<div style="padding: 1em">

										<div style="margin-bottom: 1em">You must create a backup of the database and the software before upgrading. If custom changes have been made to your software or database, then you should consult with the software provider before upgrading.</div>

									</div>

								</fieldset>

								' . $output_upgrade_authentication . '

							</div>

						</div>';

                  }

              }

            $output_install_authentication = '';

            // if user is not logged in or is not an administrator, then display authentication rows
            if (check_if_administrator_is_logged_in() == false)
              {

                $output_install_authentication =

                '<fieldset style="margin-bottom: 1em">

						<legend><strong>Authentication</strong></legend>

						<div style="padding: 1em">

							<div style="margin-bottom: 1em">Please enter the email address and password for an administrator for the existing site. If you cannot remember your login information you can use the <a href="../forgot_password.php" target="_blank">forgot password</a> feature, or you can delete and recreate the MySQL database and try again.</div>

							<table>

								<tr>

									<td>Email:</td>

									<td>' . $liveform->output_field(array(
                    'type' => 'text',
                    'name' => 'install_authentication_username',
                    'size' => '30'
                )) . '</td>

								</tr>

								<tr>

									<td>Password:</td>

									<td>' . $liveform->output_field(array(
                    'type' => 'password',
                    'name' => 'install_authentication_password',
                    'size' => '30'
                )) . '</td>

								</tr>

							</table>

						</div>

					</fieldset>';

              }

          }

      }

    // initialize variable
    $output_install_fields_style = '';

    // if there is an upgrade option, then prepare to output install option as a radio button
    if ($upgrade_option == true)
      {

        $output_install_option = '<div style="margin-bottom: 1em">' . $liveform->output_field(array(
            'type' => 'radio',
            'name' => 'install_type',
            'id' => 'install',
            'value' => 'install',
            'class' => 'radio',
            'onclick' => 'show_or_hide_upgrade_install()'
        )) . '<label for="install"> Install version ' . $software_version . ' and replace existing site</label></div>';

        $output_install_fields_style .= 'margin-left: 2em';

        // else there is no upgrade option, so prepare to output install option as a hidden field
        
      }
    else
      {

        $output_install_option = $liveform->output_field(array(
            'type' => 'hidden',
            'name' => 'install_type',
            'value' => 'install'
        ));

      }

    // if upgrade option is selected for install type then hide install fields
    if ($liveform->get_field_value('install_type') == 'upgrade')
      {

        // if there is already install fields style code, then add a separator
        if ($output_install_fields_style != '')
          {

            $output_install_fields_style .= '; ';

          }

        $output_install_fields_style .= 'display: none';

      }

    if ($_SESSION['software']['install']['reinstall'] == true)
      {

        $reinstallation_verification =

        '<fieldset style="margin-bottom: 1em; padding: 10px">

				<legend><strong>Reinstallation Verification</strong></legend>

				<div style="margin-top: 7px; margin-bottom: 7px">Existing data has been found in the database. Please verify that you want to reinstall the software. <span style="font-weight: bold; color: red">All site data will be permanently deleted.</span> This includes all updates you have made since the last installation (i.e. styles, pages, files, products, and etc.). If you wish to continue, please check to verify reinstallation.</div>

				<table>

					<tr>

						<td>Reinstall:</td>

						<td>' . $liveform->output_field(array(
            'type' => 'hidden',
            'name' => 'reinstall_software'
        )) . $liveform->output_field(array(
            'type' => 'checkbox',
            'name' => 'reinstall_software',
            'value' => '1',
            'class' => 'checkbox'
        )) . ' (<span style="font-weight: bold; color: red">all data will be permanently deleted</span>)</td>

					</tr>

				</table>

			</fieldset>';

      }

    print

    get_header() . '

		

		<script type="text/javascript" language="JavaScript 1.2">

			function show_or_hide_upgrade_install() {

				if (document.getElementById("upgrade").checked == true) {

					if (document.getElementById("upgrade_fields")) {

						document.getElementById("upgrade_fields").style.display = "";

					}

					

					document.getElementById("install_fields").style.display = "none";

					

					document.getElementById("submit").value = "Upgrade";

					

				} else {

					if (document.getElementById("upgrade_fields")) {

						document.getElementById("upgrade_fields").style.display = "none";

					}

					

					document.getElementById("install_fields").style.display = "";

					

					document.getElementById("submit").value = "Install";

				}

			}

		</script>

		

		<h1 style="margin-bottom: .75em">Installation</h1>

		

		' . $liveform->output_errors() . '

		' . $liveform->output_notices() . '

		<form method="post" style="margin: 0px">

			' . get_token_field() . '

			' . $output_upgrade . '

			<div style="margin-bottom: 1em">

				' . $output_install_option . '

				<div id="install_fields" style="' . $output_install_fields_style . '">

					' . $reinstallation_verification . '

					' . $output_install_authentication . '

					<fieldset style="margin-bottom: 1em; padding: 10px">

						<legend><strong>Installation Folder</strong></legend>

						<tr>

							<td>Install From Folder:</td>

								' . $liveform->output_field(array(
        'type' => 'select',
        'id' => 'install_from_folder',
        'name' => 'install_from_folder',
        'options' => $backup_values
    )) . '



							</tr>

					</fieldset>

					<fieldset style="margin-bottom: 1em; padding: 10px">

						<legend><strong>MySQL Database</strong></legend>

						<table>

							<tr>

								<td>Database Hostname:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'db_host',
        'value' => 'localhost',
        'size' => '30'
    )) . ' (e.g. localhost, mysql.example.com, or 192.168.0.1)</td>

							</tr>

							<tr>

								<td>Database Username:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'db_username',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>Database Password:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'password',
        'name' => 'db_password',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>Database Name:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'db_database',
        'size' => '30'
    )) . '</td>

							</tr>

						</table>

					</fieldset>

					<fieldset style="margin-bottom: 1em; padding: 10px">

						<legend><strong>Administrator User</strong></legend>

						<div style="margin-top: 7px; margin-bottom: 7px">Please enter information for your new administrator user account.</div>

						<table>

							<tr>

								<td>Username:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'admin_username',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>E-mail Address:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'admin_email_address',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>Confirm E-mail Address:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'text',
        'name' => 'admin_confirm_email_address',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>Password:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'password',
        'name' => 'admin_password',
        'size' => '30'
    )) . '</td>

							</tr>

							<tr>

								<td>Confirm Password:</td>

								<td>' . $liveform->output_field(array(
        'type' => 'password',
        'name' => 'admin_confirm_password',
        'size' => '30'
    )) . '</td>

							</tr>

						</table>

					</fieldset>

				</div>

			</div>

			<input type="submit" name="submit" id="submit" value="' . $output_submit_button_label . '" class="submit-primary" />

		</form>' .

    get_footer();

    $liveform->remove_form('install');

    $_SESSION['software']['install']['reinstall'] = false;

    // else user has completed install form or this is being run as part of an automated upgrade, so process form
    
  }
else
  {

    $liveform->add_fields_to_session();

    // if software should be upgraded, then upgrade site
    if (($liveform->get_field_value('install_type') == 'upgrade') || ($automated_upgrade == true))
      {

        db::$con = @mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        init_mysql_charset();

        // Disable MySQL strict mode, because later versions of MySQL enable strict mode by default,
        // and PineGrap is not compatible with strict mode.  This will also remove all other sql modes,
        // however that should be fine.
        mysqli_query(db::$con, "SET SESSION sql_mode = ''");

        // if this is not running from an automated upgrade and an administrator is not logged in, then validate username and password fields
        if (($automated_upgrade == false) && (check_if_administrator_is_logged_in() == false))
          {

            $liveform->validate_required_field('upgrade_authentication_username', 'Email is required.');

            $liveform->validate_required_field('upgrade_authentication_password', 'Password is required.');

            // if there is not already an error
            if ($liveform->check_form_errors() == false)
              {

                // try to find user from username that was entered
                $query =

                "SELECT user_id

					FROM user

					WHERE

						(user_role = 0)

						AND

						(

							(user_username = '" . escape($liveform->get_field_value('upgrade_authentication_username')) . "')

							OR (user_email = '" . escape($liveform->get_field_value('upgrade_authentication_username')) . "')

						)

					LIMIT 1";

                $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                // if a user was not found, prepare error
                if (mysqli_num_rows($result) == 0)
                  {

                    $liveform->mark_error('upgrade_authentication_username', 'An administrator user could not be found for the email address or username that you supplied.');

                    // else a user was found, so check password
                    
                  }
                else
                  {

                    // try to find user from username and password that were entered
                    $query =

                    "SELECT user_id

						FROM user

						WHERE

							(user_role = 0)

							AND

							(

								(user_username = '" . escape($liveform->get_field_value('upgrade_authentication_username')) . "')

								OR (user_email = '" . escape($liveform->get_field_value('upgrade_authentication_username')) . "')

							)

							AND (user_password = '" . md5($liveform->get_field_value('upgrade_authentication_password')) . "')

						LIMIT 1";

                    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                    // if a user was not found, prepare error
                    if (mysqli_num_rows($result) == 0)
                      {

                        $liveform->mark_error('upgrade_authentication_password', 'The password you entered was incorrect. Please remember that passwords are case sensitive.');

                        $liveform->assign_field_value('upgrade_authentication_password', '');

                      }

                  }

              }

          }

        // if an error exists, then return to form
        if ($liveform->check_form_errors() == true)
          {

            return_to_form();

          }

        // Get MySQL version in order to determine if we should set the engine
        // for new tables that we create.  The engine property is not supported
        // in old MySQL version so we need to make sure that we don't add it
        // in order to avoid a query error during udpdate.
        $mysql_version = db_value("SELECT VERSION()");

        $mysql_version_parts = explode('.', $mysql_version);

        $mysql_major_version = $mysql_version_parts[0];

        $mysql_minor_version = $mysql_version_parts[1];

        // If the MySQL version is at least 4.1 then prepare engine value.
        // Engine support was actually added in MySQL 4.0.18, however we
        // don't want to deal with checking the maintenance version, so we are just
        // going to require 4.1 and higher.  No one but us is using earlier versions anyway.
        // We define it as a constant so that we can have access to it
        // in all update functions below.
        if (

        (

        ($mysql_major_version == 4)
 && ($mysql_minor_version >= 1)
)
 || ($mysql_major_version >= 5)
)
          {

            define('ENGINE', ' ENGINE=MyISAM');

            // Otherwise MySQL version is before 4.1, so do not include engine property
            
          }
        else
          {

            define('ENGINE', '');

          }

        $database_version = get_database_version();

        $database_version_key = get_version_key($database_version, $versions);

        // get 5.5.0 version key, so we can determine if we should update version in database
        $version_key_5_5_0 = get_version_key('5.5.0', $versions);

        // loop through all versions
        foreach ($versions as $version_key => $version)
          {

            // if version is greater than database version, then run upgrade for this version
            if ($version_key > $database_version_key)
              {

                $function_name = 'upgrade_to_' . str_replace('.', '_', $version['number']);

                // If there is a function for this version, then run function.
                // Some versions do not need any db updates, so there might not be a function.
                if (function_exists($function_name))
                  {

                    $function_name();

                  }

                // if version is 5.5.0 or later, then update version in database
                if ($version_key >= $version_key_5_5_0)
                  {

                    $query = "UPDATE config SET version = '" . $version['number'] . "'";

                    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                  }

              }

          }

        // reset flag so that the site does not indicate that there is a software update available anymore
        $query = "UPDATE config SET software_update_available = '0'";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        // Reset software update check so that it will check again, so site will get new messages
        // for new version if there are any.
        db("UPDATE config SET last_software_update_check_timestamp = ''");

        // if this is being run from an automated upgrade, then display non-HTML message
        if ($automated_upgrade == true)
          {

            header('Location: ../welcome.php');

            print 'complete';

            // else this is not being run from an automated upgrade, so display full confirmation HTML
            
          }
        else
          {

            print

            get_header() . '

				<h1 style="margin-bottom: .75em">Installation</h1>

				<div style="margin-bottom: 1em">Congratulations, the software has been upgraded successfully from version ' . $database_version . ' to ' . $software_version . '.</div>

				<div style="margin-bottom: 1em"><a href="../" class="button_primary">Continue</a></div>

				' . get_footer();

          }

        $liveform->remove_form('install');

        // else if software should be installed, then install software
        
      }
    elseif (($liveform->get_field_value('install_type') == 'install') || ($liveform->get_field_value('install_type') == ''))
      {

        // if there are existing database contents in config.php file, then determine if there is an existing site and if we need to authenticate user
        if (defined('DB_HOST') == true)
          {

            db::$con = @mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

            init_mysql_charset();

            // Disable MySQL strict mode, because later versions of MySQL enable strict mode by default,
            // and PineGrap is not compatible with strict mode.  This will also remove all other sql modes,
            // however that should be fine.
            mysqli_query(db::$con, "SET SESSION sql_mode = ''");

            // If the token does not exist in the session,
            // or the passed token does not match the token from the session,
            // then this might be a CSRF attack so log activity and exit with error.
            // We only care about the token for this area of the code (i.e. installs where it appears there is an existing site),
            // because it is the only dangerous area.  We don't want an attacker to be able to use CSRF
            // to overwrite an existing site without the admin's permission.  We don't add CSRF protection to other areas
            // of this script, because we need systems to be able to do fresh installs from a remote location (i.e. our trial system).
            if (

            ($_SESSION['software']['token'] == '')
 ||

            (

            ($_POST['token'] != $_SESSION['software']['token'])
 && ($_GET['token'] != $_SESSION['software']['token'])
)
)
              {

                log_activity('access denied to submit installation form because visitor\'s session expired or because request might have come from an unauthorized location', $_SESSION['sessionusername']);

                exit('Sorry, we could not accept your request because it appears that your session expired.');

              }

            // initialize variable
            $software_installed = false;

            // get all tables in database in order to determine if software is already installed
            $query = "SHOW TABLES";

            $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

            while ($row = mysqli_fetch_row($result))
              {

                if (($row[0] == 'config') || ($row[0] == 'page') || ($row[0] == 'user'))
                  {

                    $software_installed = true;

                    break;

                  }

              }

            // if software is already installed and this user is not already logged in as an administrator, then validate authentication fields
            if (($software_installed == true) && (check_if_administrator_is_logged_in() == false))
              {

                $liveform->validate_required_field('install_authentication_username', 'Email is required.');

                $liveform->validate_required_field('install_authentication_password', 'Password is required.');

                // if there is not already an error
                if ($liveform->check_form_errors() == false)
                  {

                    // try to find user from username that was entered
                    $query =

                    "SELECT user_id

						FROM user

						WHERE

							(user_role = 0)

							AND

							(

								(user_username = '" . escape($liveform->get_field_value('install_authentication_username')) . "')

								OR (user_email = '" . escape($liveform->get_field_value('install_authentication_username')) . "')

							)

						LIMIT 1";

                    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                    // if a user was not found, prepare error
                    if (mysqli_num_rows($result) == 0)
                      {

                        $liveform->mark_error('install_authentication_username', 'An administrator user could not be found for the email address or username that you supplied.');

                        // else a user was found, so check password
                        
                      }
                    else
                      {

                        // try to find user from username and password that were entered
                        $query =

                        "SELECT user_id

							FROM user

							WHERE

								(user_role = 0)

								AND

								(

									(user_username = '" . escape($liveform->get_field_value('install_authentication_username')) . "')

									OR (user_email = '" . escape($liveform->get_field_value('install_authentication_username')) . "')

								)

								AND (user_password = '" . md5($liveform->get_field_value('install_authentication_password')) . "')

							LIMIT 1";

                        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                        // if a user was not found, prepare error
                        if (mysqli_num_rows($result) == 0)
                          {

                            $liveform->mark_error('install_authentication_password', 'The password you entered was incorrect. Please remember that passwords are case sensitive.');

                            $liveform->assign_field_value('install_authentication_password', '');

                          }

                      }

                  }

              }

          }

        $liveform->validate_required_field('install_from_folder', 'Install From Folder is required.');

        $liveform->validate_required_field('db_host', 'Database Hostname is required.');

        $liveform->validate_required_field('db_username', 'Database Username is required.');

        $liveform->validate_required_field('db_database', 'Database Name is required.');

        $liveform->validate_required_field('admin_username', 'Username is required.');

        $liveform->validate_required_field('admin_email_address', 'E-mail Address is required.');

        $liveform->validate_required_field('admin_confirm_email_address', 'Confirm E-mail Address is required.');

        $liveform->validate_required_field('admin_password', 'Password is required.');

        $liveform->validate_required_field('admin_confirm_password', 'Confirm Password is required.');

        if ($liveform->get_field_value('install_from_folder') != '')
          {

            $install_directory_path = $liveform->get_field_value('install_from_folder');

          }
        else
          {

            $liveform->mark_error('install_from_folder', 'Something went wrong.');

          }

        // if an error does not exist for the database fields
        if (($liveform->check_field_error('db_host') == false) && ($liveform->check_field_error('db_username') == false) && ($liveform->check_field_error('db_password') == false) && ($liveform->check_field_error('db_database') == false))
          {

            db::$con = @mysqli_connect($liveform->get_field_value('db_host') , $liveform->get_field_value('db_username') , $liveform->get_field_value('db_password'));

            // if connection is made to database with login information that was supplied
            if (db::$con)
              {

                // if database cannot be selected
                if (@mysqli_select_db(db::$con, $liveform->get_field_value('db_database')) == false)
                  {

                    $liveform->mark_error('db_database', 'A connection to the MySQL server was successful, however the database name that you entered could not be selected. Please correct the database name. If the database name is correct, then the user might not have correct permissions to access the database. MySQL error: ' . mysqli_error(db::$con));

                    // else the database can be selected, so check to see if there is an existing site
                    
                  }
                else
                  {

                    init_mysql_charset();

                    // Disable MySQL strict mode, because later versions of MySQL enable strict mode by default,
                    // and PineGrap is not compatible with strict mode.  This will also remove all other sql modes,
                    // however that should be fine.
                    mysqli_query(db::$con, "SET SESSION sql_mode = ''");

                    // initialize variable
                    $software_installed = false;

                    // get all tables in database in order to determine if software is already installed in new database
                    $query = "SHOW TABLES";

                    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                    while ($row = mysqli_fetch_row($result))
                      {

                        if (($row[0] == 'config') || ($row[0] == 'page') || ($row[0] == 'user'))
                          {

                            $software_installed = true;

                          }

                      }

                    // if software is already installed in new database
                    if ($software_installed == true)
                      {

                        $_SESSION['software']['install']['reinstall'] = true;

                        // if the reinstall check box did not appear on the form, then add notice
                        if ($liveform->field_in_session('reinstall_software') == false)
                          {

                            $liveform->add_notice('A site is already installed in the database that you entered. If you wish to reinstall please check to verify reinstallation.');

                            // else the reinstall check box appeared on the form, so require it
                            
                          }
                        else
                          {

                            $liveform->validate_required_field('reinstall_software', 'Please verify that you want to reinstall.');

                          }

                        // else software is not already installed
                        
                      }
                    else
                      {

                        $_SESSION['software']['install']['reinstall'] = false;

                      }

                  }

                // else a connection was not made to database
                
              }
            else
              {

                $liveform->mark_error('', 'A connection to the MySQL server failed. Please correct the hostname, username, and/or password.  MySQL error: ' . mysqli_connect_error());

              }

          }

        // if there is not already an error for the admin password fields, check to see if admin password and confirm password do not match
        if (($liveform->check_field_error('admin_password') == false) && ($liveform->check_field_error('admin_confirm_password') == false))
          {

            if ($liveform->get_field_value('admin_password') != $liveform->get_field_value('admin_confirm_password'))
              {

                $liveform->mark_error('admin_password', 'The two administrator passwords you entered did not match.');

                $liveform->mark_error('admin_confirm_password');

                $liveform->assign_field_value('admin_password', '');

                $liveform->assign_field_value('admin_confirm_password', '');

              }

          }

        // if there is not already an error for the admin e-mail address fields, check to see if admin e-mail address and confirm e-mail adress do not match
        if (($liveform->check_field_error('admin_email_address') == false) && ($liveform->check_field_error('admin_confirm_email_address') == false))
          {

            if ($liveform->get_field_value('admin_email_address') != $liveform->get_field_value('admin_confirm_email_address'))
              {

                $liveform->mark_error('admin_email_address', 'The two administrator e-mail addresses you entered did not match.');

                $liveform->mark_error('admin_confirm_email_address');

              }

          }

        // determine if config.php can be written to
        $handle = @fopen(CONFIG_FILE_PATH, 'a');

        // if config.php can be written to, close handle
        if ($handle == true)
          {

            fclose($handle);

            // else config.php cannot be written to, so mark error
            
          }
        else
          {

            $liveform->mark_error('config_access', 'The system could not write to the config.php file (' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/config.php). Please configure the config.php file so it can be written to.  For Unix, set the permissions for the file to 777.  For Windows, give the anonymous web user rights to write to and delete the file.');

          }

        // create test file for writing in order to determine if files directory can be written to
        $handle = @fopen(FILE_DIRECTORY_PATH . '/test.txt', 'w');

        // if files directory can be written to, then delete test file
        if ($handle == true)
          {

            fclose($handle);

            unlink(FILE_DIRECTORY_PATH . '/test.txt');

            // else files directory cannot be written to, so mark error
            
          }
        else
          {

            $liveform->mark_error('files_access', 'The system could not write to the files directory (' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/files). Please configure the files directory so it can be written to.  For Unix, set the permissions for the directory to 777.  For Windows, give the anonymous web user rights to write, delete, and "Delete subfolders and files".');

          }

        // if there are errors or notices, then send user to previous screen
        if (($liveform->check_form_errors() == true) || ($liveform->check_form_notices() == true))
          {

            return_to_form();

          }

        // If the database has tables in it, then get all system tables and then drop
        // them all before we install new fresh tables.
        // Even though the sql.sql already contains "DROP TABLE IF EXISTS" commands, we
        // do this anyway, because there are some situations where this is still necessary.  For
        // example if an admin is working with a new version that has new tables that the starter
        // template does not contain yet, and the admin tries to re-install, then we need to delete
        // the new existing tables before we install, in order to avoid SQL errors.  This is
        // necessary because this install script will run an update, if necessary, after the install
        // and try to create new tables.
        

        $current_tables = db_values("SHOW TABLES");

        if ($current_tables)
          {

            $system_tables = get_tables();

            foreach ($system_tables as $table)
              {

                db("DROP TABLE IF EXISTS `" . $table . "`");

              }

          }

        // Get the MySQL version so we know whether to use utf8mb4 or utf8.
        $mysql_version = preg_replace('#[^0-9\.]#', '', mysqli_get_server_info(db::$con));

        if (version_compare($mysql_version, '5.5.3', '>=') == true)
          {

            $character_set = 'utf8mb4';

          }
        else
          {

            $character_set = 'utf8';

          }

        // Update the charset for the db so that when future tables are created,
        // they will have the correct charset.
        db(

        "ALTER DATABASE `" . e($liveform->get_field_value('db_database')) . "`

			CHARACTER SET = " . $character_set . "

			COLLATE = " . $character_set . "_unicode_ci");

        // Prepare template sql file
        $database_file = 'backups/' . $install_directory_path . '/sql.sql';

        // run all queries from MySQL dump file for template
        if (parse_mysql_dump($database_file) == false)
          {

            $liveform->mark_error('', 'There was an error while the database was being initialized. Please contact the software provider and include the error that appears next. MySQL error: ' . mysqli_error(db::$con));

            return_to_form();

          }

        // If the MySQL server supports utf8mb4, then convert all tables that were
        // created above from utf8 to utf8mb4.  The sql.sql file has utf8 set by default
        // so that is why this is necessary.
        

        if ($character_set == 'utf8mb4')
          {

            $tables = db_values("SHOW TABLES");

            foreach ($tables as $table)
              {

                db(

                "ALTER TABLE `" . $table . "`

					CONVERT TO CHARACTER SET " . $character_set . "

					COLLATE " . $character_set . "_unicode_ci");

                // We were having an issue with MariaDB (and maybe MySQL)
                // where the default character set was not set for tables
                // with only number type columns after the command above was run,
                // so we have to run the following similar command also.
                // We don't know why.
                db(

                "ALTER TABLE `" . $table . "`

					CHARACTER SET " . $character_set . "

					COLLATE " . $character_set . "_unicode_ci");

              }

          }

        // Update config settings.
        $query =

        "UPDATE config

			SET

				hostname = '" . escape($_SERVER['HTTP_HOST']) . "',

				subscription_id = '" . escape($liveform->get_field_value('subscription_id')) . "'";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        // if a settings e-mail address was passed, then use it
        if ($liveform->get_field_value('settings_email_address') != '')
          {

            $settings_email_address = $liveform->get_field_value('settings_email_address');

            // else a settings e-mail address was not passed, so use admin e-mail address
            
          }
        else
          {

            $settings_email_address = $liveform->get_field_value('admin_email_address');

          }

        // set e-mail address in various places
        

        $query = "UPDATE config SET email_address = '" . escape($settings_email_address) . "' WHERE email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE config SET registration_email_address = '" . escape($settings_email_address) . "' WHERE registration_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE config SET membership_email_address = '" . escape($settings_email_address) . "' WHERE membership_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE config SET ecommerce_email_address = '" . escape($settings_email_address) . "' WHERE ecommerce_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE config SET affiliate_email_address = '" . escape($settings_email_address) . "' WHERE affiliate_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE custom_form_pages SET submitter_email_from_email_address = '" . escape($settings_email_address) . "' WHERE submitter_email_from_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE custom_form_pages SET administrator_email_to_email_address = '" . escape($settings_email_address) . "' WHERE administrator_email_to_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE custom_form_pages SET administrator_email_bcc_email_address = '" . escape($settings_email_address) . "' WHERE administrator_email_bcc_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE page SET comments_administrator_email_to_email_address = '" . escape($settings_email_address) . "' WHERE comments_administrator_email_to_email_address != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE products SET email_bcc = '" . escape($settings_email_address) . "' WHERE email_bcc != ''";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        db("UPDATE email_campaign_profiles SET from_email_address = '" . escape($settings_email_address) . "' WHERE from_email_address != ''");

        db("UPDATE email_campaign_profiles SET reply_email_address = '" . escape($settings_email_address) . "' WHERE reply_email_address != ''");

        db("UPDATE email_campaign_profiles SET bcc_email_address = '" . escape($settings_email_address) . "' WHERE bcc_email_address != ''");

        // add administrator user
        // We are setting the user's start page to the 294 page which is "staff-home".
        $query =

        "INSERT INTO user (

				user_username,

				user_email,

				user_password,

				user_role,

				user_home,

				user_timestamp)

			VALUES (

				'" . escape($liveform->get_field_value('admin_username')) . "',

				'" . escape($liveform->get_field_value('admin_email_address')) . "',

				'" . md5($liveform->get_field_value('admin_password')) . "',

				'0',

				'294',

				UNIX_TIMESTAMP())";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $user_id = mysqli_insert_id(db::$con);

        // Update last modified site settings info to contain info for this admin user, so that
        // software update check will immediately start sending admin email info to us.
        

        db(

        "UPDATE config

			SET

				last_modified_user_id = '$user_id',

				last_modified_timestamp = UNIX_TIMESTAMP()");

        // set all timestamps to the current timestamp
        

        $query = "UPDATE ad_regions SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE ad_regions SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE ads SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE ads SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE arrival_dates SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        db(

        "UPDATE auto_dialogs

			SET

				created_timestamp = UNIX_TIMESTAMP(),

				last_modified_timestamp = UNIX_TIMESTAMP()");

        $query = "UPDATE calendar_event_locations SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE calendar_event_locations SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE calendars SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE calendars SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE contact_groups SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE contact_groups SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE countries SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE cregion SET cregion_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE currencies SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE currencies SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE dregion SET dregion_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        db("UPDATE email_campaign_profiles SET created_timestamp = UNIX_TIMESTAMP()");

        db("UPDATE email_campaign_profiles SET last_modified_timestamp = UNIX_TIMESTAMP()");

        $query = "UPDATE files SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE folder SET folder_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE form_fields SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        db(

        "UPDATE forms

			SET

				submitted_timestamp = UNIX_TIMESTAMP(),

				last_modified_timestamp = UNIX_TIMESTAMP()");

        $query = "UPDATE key_codes SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE login_regions SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE login_regions SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE menus SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE menus SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE menu_items SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE menu_items SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE offers SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE offer_rules SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE offer_actions SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE order_reports SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE order_reports SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE page SET page_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE pregion SET pregion_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        db("UPDATE product_attributes SET created_timestamp = UNIX_TIMESTAMP()");

        db("UPDATE product_attributes SET last_modified_timestamp = UNIX_TIMESTAMP()");

        $query = "UPDATE products SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE product_groups SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE referral_sources SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE shipping_methods SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE short_links SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE short_links SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE states SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE style SET style_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE tax_zones SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE verified_shipping_addresses SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE verified_shipping_addresses SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE visitor_reports SET created_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE visitor_reports SET last_modified_timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $query = "UPDATE zones SET timestamp = UNIX_TIMESTAMP()";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        // delete all files from files directory
        $handle = opendir(FILE_DIRECTORY_PATH . '/');

        while (false !== ($file = readdir($handle)))
          {

            if (($file != '.') && ($file != '..'))
              {

                unlink(FILE_DIRECTORY_PATH . '/' . $file);

              }

          }

        closedir($handle);

        // prepare path to template files
        $template_files_path = 'backups/' . $install_directory_path . '/files/';

        $handle = opendir($template_files_path);

        // copy template files to files directory
        while (false !== ($file = readdir($handle)))
          {

            if (($file != '.') && ($file != '..'))
              {

                copy($template_files_path . $file, FILE_DIRECTORY_PATH . '/' . $file);

              }

          }

        closedir($handle);

        // Deal with layouts now.
        

        // delete all files from layouts directory
        $handle = opendir(LAYOUT_DIRECTORY_PATH . '/');

        while (false !== ($file = readdir($handle)))
          {

            if (($file != '.') && ($file != '..'))
              {

                unlink(LAYOUT_DIRECTORY_PATH . '/' . $file);

              }

          }

        closedir($handle);

        // prepare path to template layouts
        $template_layouts_path = 'backups/' . $install_directory_path . '/layouts/';

        $handle = opendir($template_layouts_path);

        // copy template layouts to layouts directory
        while (false !== ($file = readdir($handle)))
          {

            if (($file != '.') && ($file != '..'))
              {

                copy($template_layouts_path . $file, LAYOUT_DIRECTORY_PATH . '/' . $file);

              }

          }

        closedir($handle);

        // create config.php file
        

        // if a logo URL was supplied, then use it
        if ($liveform->get_field_value('logo_url') != '')
          {

            $logo_url = "\r\n" . 'define(\'LOGO_URL\', \'' . $liveform->get_field_value('logo_url') . '\');';

            // else a logo URL was not supplied, so we won't add the logo_url line
            
          }
        else
          {

            $logo_url = '';

          }

        // if a footer logo URL was supplied, then use it
        if ($liveform->get_field_value('footer_logo_url') != '')
          {

            $footer_logo_url = "\r\n" . 'define(\'FOOTER_LOGO_URL\', \'' . $liveform->get_field_value('footer_logo_url') . '\');';

            // else a footer logo URL was not supplied, so we won't add the footer_logo_url line
            
          }
        else
          {

            $footer_logo_url = '';

          }

        // if a software update check value was supplied, then use it
        if ($liveform->get_field_value('software_update_check') != '')
          {

            // if true value was passed, then prepare value for config file
            if ($liveform->get_field_value('software_update_check') == 'true')
              {

                $software_update_check_value = 'TRUE';

                // else a true value was not passed, so prepare different value for config file
                
              }
            else
              {

                $software_update_check_value = 'FALSE';

              }

            $software_update_check = "\r\n" . 'define(\'SOFTWARE_UPDATE_CHECK\', ' . $software_update_check_value . ');';

            // else a software update check value was not supplied, so we won't add a line for it
            
          }
        else
          {

            $software_update_check = '';

          }

        if ($liveform->get_field_value('email_campaign_job') == 'true')
          {

            $email_campaign_job =

            "\n" .

            'define(\'EMAIL_CAMPAIGN_JOB\', true);';

          }

        // prepare data for config.php file
        $config_data =

        '<?php

define(\'DB_HOST\', \'' . $liveform->get_field_value('db_host') . '\');

define(\'DB_USERNAME\', \'' . $liveform->get_field_value('db_username') . '\');

define(\'DB_PASSWORD\', \'' . $liveform->get_field_value('db_password') . '\');

define(\'DB_DATABASE\', \'' . $liveform->get_field_value('db_database') . '\');

define(\'ENCRYPTION_KEY\', \'' . generate_encryption_key() . '\'); // DO NOT MODIFY OR SHARE

define(\'DYNAMIC_REGIONS\', true);

define(\'PHP_REGIONS\', true);' .

        $logo_url .

        $footer_logo_url .

        $software_update_check .

        $email_campaign_job . '

?>';

        $handle = fopen(CONFIG_FILE_PATH, 'w');

        fwrite($handle, $config_data);

        fclose($handle);

        // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,
        // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).
        

        // if the software is going to be accessed at a sub-directory and Apache is being used (not IIS), then update RewriteBase in .htaccess file
        if (

        (PATH != '/')
 && (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') === false)
)
          {

            // get contents of .htaccess file in order to update RewriteBase
            $htaccess_content = @file_get_contents(HTACCESS_FILE_PATH);

            // if we could get the contents of the .htaccess file, then continue to update it
            if ($htaccess_content !== false)
              {

                // open the .htaccess file in order to update it
                $handle = @fopen(HTACCESS_FILE_PATH, 'w');

                // if the .htaccess file could be opened for writing, then continue to update RewriteBase
                if ($handle == true)
                  {

                    $htaccess_content = str_replace('#RewriteBase /~example/', 'RewriteBase ' . PATH, $htaccess_content);

                    // update the .htaccess file with the new content
                    @fwrite($handle, $htaccess_content);

                    // close the .htaccess file
                    @fclose($handle);

                  }

              }

          }

        // If the installed version is not the most recent version, then update also.
        // This might happen during development when you install a new site, but it installs
        // a starter template for an old version, because the starter template has not been
        // updated yet.
        

        $installed_version = db("SELECT version FROM config");

        if ($installed_version != $software_version)
          {

            // Get MySQL version in order to determine if we should set the engine
            // for new tables that we create.  The engine property is not supported
            // in old MySQL version so we need to make sure that we don't add it
            // in order to avoid a query error during udpdate.
            $mysql_version = db_value("SELECT VERSION()");

            $mysql_version_parts = explode('.', $mysql_version);

            $mysql_major_version = $mysql_version_parts[0];

            $mysql_minor_version = $mysql_version_parts[1];

            // If the MySQL version is at least 4.1 then prepare engine value.
            // Engine support was actually added in MySQL 4.0.18, however we
            // don't want to deal with checking the maintenance version, so we are just
            // going to require 4.1 and higher.  No one but us is using earlier versions anyway.
            // We define it as a constant so that we can have access to it
            // in all update functions below.
            if (

            (

            ($mysql_major_version == 4)
 && ($mysql_minor_version >= 1)
)
 || ($mysql_major_version >= 5)
)
              {

                define('ENGINE', ' ENGINE=MyISAM');

                // Otherwise MySQL version is before 4.1, so do not include engine property
                
              }
            else
              {

                define('ENGINE', '');

              }

            $installed_version_key = get_version_key($installed_version, $versions);

            // Loop through all the versions in order to determine which we need to update to.
            foreach ($versions as $version_key => $version)
              {

                // If this version is greater than the installed version, then run update for this
                // version.
                if ($version_key > $installed_version_key)
                  {

                    $function_name = 'upgrade_to_' . str_replace('.', '_', $version['number']);

                    // If there is a function for this version, then run function.
                    // Some versions do not need any db updates, so there might not be a function.
                    if (function_exists($function_name))
                      {

                        $function_name();

                      }

                    db("UPDATE config SET version = '" . $version['number'] . "'");

                  }

              }

          }

        log_activity("The software was installed", $liveform->get_field_value('admin_username'));

        // if an e-mail should be sent to the administrator, then send e-mail
        if ($liveform->get_field_value('send_email') != 'false')
          {

            // prepare confirmation e-mail to administrator
            $to = $liveform->get_field_value('admin_email_address');

            $subject = 'PineGrap: Installation Complete for ' . $_SERVER['HTTP_HOST'];

            // prepare hidden password
            for ($i = 1;$i <= mb_strlen($liveform->get_field_value('admin_password'));$i++)
              {

                $hidden_password .= '*';

              }

            $body =

            'Congratulations, your PineGrap installation is complete!  You may find your login information below:



Email: ' . $liveform->get_field_value('admin_email_address') . '

Password: ' . $hidden_password . '



Login:

http://' . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/';

            $headers .= 'From: donotreply@kodpen.com' . "\r\n";

            // send e-mail to administrator
            @mb_send_mail($to, $subject, $body, $headers);

          }

        // output confirmation
        print

        get_header() .

        '<h1 style="margin-bottom: .75em">Installation</h1>

			<p>Congratulations, the installation is complete!</p>

			<p>Your database login information has been stored in the config.php file in the software directory. If you need to change the database login information in the future, you will need to update the config.php file.</p>

			<p>A confirmation e-mail has been sent to your e-mail address.  If you do not receive the confirmation e-mail, then e-mail is probably not configured correctly for your website.  There are features that rely on e-mail (i.e. e-mailing pages, creating users, and etc.), so it is important that you configure e-mail to work.</p>

			<div style="margin-bottom: 7px"><a href="../" class="button_primary">Continue</a></div>' .

        get_footer();

        $liveform->remove_form('install');

        $_SESSION['software']['install']['reinstall'] = false;

      }

  }

function language_select_output()
  {

    //this function must developing just show
    if (defined('DB_CONNECTED'))
      {

        $query = "SELECT * FROM config";

        $result = mysqli_query(db::$con, $query);

        $row = mysqli_fetch_assoc($result);

        $software_language = $row['software_language'];

        $select = '';

        if ($software_language != NULL)
          {

            if ($software_language == 'en')
              {

                $selected_en = 'selected="selected"';

              }

            if ($software_language == 'tr')
              {

                $selected_tr = 'selected="selected"';

              }

            $select_software_language_options = '<option value="en" ' . $selected_en . '>English</option><option value="tr" ' . $selected_tr . '>Türkçe</option>';

            $select = '<select id="software_language" disabled name="software_language" style="position:absolute;right:0px;border-bottom:0px !important">' . $select_software_language_options . '</select>';

          }

        return $select;

      }
    else
      {

        return false;

      }

  }

function get_header()

  {

    $help_url = '';

    return

    '<!DOCTYPE html>

		<html lang="' . language_ruler() . '">

			<head>

				<meta charset="utf-8">

				<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0" />

				<title>Installation</title>

				' . get_generator_meta_tag() . '

				<link rel="stylesheet" type="text/css" href="../jquery/theme/standard.' . ENVIRONMENT_SUFFIX . '.css">

				<link rel="stylesheet" type="text/css" href="../backend.' . ENVIRONMENT_SUFFIX . '.css?v=' . @filemtime(dirname(__FILE__) . '/../backend.' . ENVIRONMENT_SUFFIX . '.css') . '">

				<script type="text/javascript">

					var path = "' . escape_javascript(PATH) . '";

					var software_directory = "' . escape_javascript(SOFTWARE_DIRECTORY) . '";

					var software_token = "' . $_SESSION['software']['token'] . '";

					var help_url = "' . $help_url . '";

				</script>

				<script type="text/javascript" src="../jquery/jquery-1.7.2.min.js"></script>

				<script type="text/javascript" src="../jquery/jquery-ui-1.12.1.min.js"></script>

			   

				<script type="text/javascript" src="../backend.' . ENVIRONMENT_SUFFIX . '.js?v=' . @filemtime(dirname(__FILE__) . '/../backend.' . ENVIRONMENT_SUFFIX . '.js') . '"></script>

				' . language_pack_init() . '

			</head>

			<body class="installation welcome coloron" style="overflow-y:auto !important;">

				<div id="content">

				' . language_select_output() . $output_settings_btn;

  }

function get_footer()

  {

    global $software_version;

    return

    '       </div>

				<div id="footer" style="text-align: right; padding: 1em; color: #ffffff">

					v' . $software_version . '&nbsp;&nbsp;&copy;&nbsp;' . date('Y', time()) . '

				</div>

			   

			</body>

		</html>';

  }

function return_to_form()

  {

    header('Location: http://' . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/install/');

    exit();

  }

function get_version_key($number, $versions)

  {

    // loop through all versions in order to find the key
    foreach ($versions as $key => $version)
      {

        // if this is the version, then return the key
        if ($version['number'] == $number)
          {

            return $key;

          }

      }

    // if we have gotten here, then a key was not found, so return false
    return false;

  }

function check_if_administrator_is_logged_in()

  {

    // if a username and password are set in the session, then continue to check if username and password are valid and if user is an administrator
    if ((isset($_SESSION['sessionusername']) == true) && (isset($_SESSION['sessionpassword']) == true))
      {

        $query =

        "SELECT user_id

			FROM user

			WHERE

				(user_role = '0')

				AND (user_username = '" . escape($_SESSION['sessionusername']) . "')

				AND (user_password = '" . escape($_SESSION['sessionpassword']) . "')";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        // if a user was found, then return true
        if (mysqli_num_rows($result) > 0)
          {

            return true;

          }

      }

    // if we got here then an administrator is not logged in, so return false
    return false;

  }

function parse_mysql_dump($url)

  {

    $file_content = file($url);

    $query = '';

    foreach ($file_content as $sql_line)
      {

        $tsl = trim($sql_line);

        if (($tsl != '') && (mb_substr($tsl, 0, 2) != '--') && (mb_substr($tsl, 0, 1) != '#'))
          {

            $query .= $sql_line;

            if (preg_match("/;\s*$/", $sql_line))
              {

                $result = mysqli_query(db::$con, trim($query));

                if (!$result)
                  {

                    return false;

                  }

                $query = '';

              }

          }

      }

    return true;

  }

function get_database_version()

  {

    // Check database if version column exists.
    $query = "SHOW COLUMNS FROM config LIKE 'version'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If the column exists, then we can get the version from the database.
    if (mysqli_num_rows($result) != 0)
      {

        // Query the database for versions value.
        $query = "SELECT version FROM config";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $row = mysqli_fetch_assoc($result);

        // Return the value from the database.
        return $row['version'];

      }

    // if the email_recipients table does not exist, then the version is too old to detect
    $query = "SHOW TABLES LIKE 'email_recipients'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this version is too old to detect.
    if (mysqli_num_rows($result) == 0)
      {

        return false;

      }

    // query the database to see if the visitors table exists
    $query = "SHOW TABLES LIKE 'visitors'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // if the table does not exist, then the version is 4.5.0
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.0';

      }

    // query the database to see if the po_number column exists in the billing_information_pages table
    $query = "SHOW COLUMNS FROM billing_information_pages LIKE 'po_number'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // if the column does not exist, then the version is 4.5.1
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.1';

      }

    // query the database to see if the upsell column exists in the offers table
    $query = "SHOW COLUMNS FROM offers LIKE 'upsell'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // if the column does not exist, then the version is 4.5.2
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.2';

      }

    // query the database to see if the contact_groups table exists
    $query = "SHOW TABLES LIKE 'contact_groups'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // if the table does not exist, then the version is 4.5.3
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.3';

      }

    // Check if the version is 4.5.5
    // Query the database to see if the custom_field_1 column exists in the orders table.
    $query = "SHOW COLUMNS FROM orders LIKE 'custom_field_1'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If the column does not exist, then the version is 4.5.4
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.4';

      }

    // Check if the version is 5.0.0
    // If the calendars table exists, then this is atleast version 5.0.0
    $query = "SHOW TABLES LIKE 'calendars'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 4.5.5
    if (mysqli_num_rows($result) == 0)
      {

        return '4.5.5';

      }

    // Check if the version is 5.0.2
    // Query the database to see if the url_host column exists in the config table.
    $query = "SHOW COLUMNS FROM config LIKE 'url_host'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are results, then this is version 5.0.0 or 5.0.1
    if (mysqli_num_rows($result) != 0)
      {

        return '5.0.1';

      }

    // Check if the version is 5.0.3
    // Query the database to see if the type column exists in the contact_groups_email_campaigns_xref table.
    $query = "SHOW COLUMNS FROM contact_groups_email_campaigns_xref LIKE 'type'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.2
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.2';

      }

    // Check if the version is 5.0.4
    // Query the database to see if the quiz column exists in the custom_form_pages table.
    $query = "SHOW COLUMNS FROM custom_form_pages LIKE 'quiz'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.3
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.3';

      }

    // Check if the version is 5.0.5
    // Query the database to see if the ecommerce_authorizenet_api_login_id column exists in the config table.
    $query = "SHOW COLUMNS FROM config LIKE 'ecommerce_authorizenet_api_login_id'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.4
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.4';

      }

    // Check if the version is 5.0.6
    // Query the database to see if the table calendar_event_exceptions exists.
    $query = "SHOW TABLES LIKE 'calendar_event_exceptions'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.5
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.5';

      }

    // Check if the version is 5.0.7
    // Query the database to see if the formats table exists.
    $query = "SHOW TABLES LIKE 'formats'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are results, then this is version 5.0.6
    if (mysqli_num_rows($result) != 0)
      {

        return '5.0.6';

      }

    // Check if the version is 5.0.8
    // Query the database to see if the currencies table exists.
    $query = "SHOW TABLES LIKE 'currencies'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.7
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.7';

      }

    // Check if the version is 5.0.9
    // Query the database to see if the express_order_pages table exists.
    $query = "SHOW TABLES LIKE 'express_order_pages'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.8
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.8';

      }

    // Check if the version is 5.5.0
    // Query the database to see if the menus table exists.
    $query = "SHOW TABLES LIKE 'menus'";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    // If there are no results, then this is version 5.0.9
    if (mysqli_num_rows($result) == 0)
      {

        return '5.0.9';

      }

    // If we made it this far without returning anything, something is wrong and we are unable to detect the version.
    return false;

  }

function add_content_to_stylesheets($content)

  {

    // get all design stylesheets so that the CSS can be added to them
    $query = "SELECT name FROM files WHERE (design = '1') AND (type = 'css') ORDER BY name ASC";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    $stylesheets = array();

    while ($row = mysqli_fetch_assoc($result))
      {

        $stylesheets[] = $row;

      }

    // loop through all stylesheets in order to add content to them
    foreach ($stylesheets as $stylesheet)
      {

        $stylesheet_path = FILE_DIRECTORY_PATH . '/' . $stylesheet['name'];

        // open stylesheet for appending
        $handle = @fopen($stylesheet_path, 'a');

        // if stylesheet could be opened, then write content
        if ($handle)
          {

            fwrite($handle, $content);

            fclose($handle);

          }

      }

  }

// Create a function that will create a backup of all system themes,
// so if there is a problem from updating system themes, a site can use the backup.
// We create a backup as a custom theme. $version is used in order to give a backup
// a unique name associated with the version that we are updating to.  It is also
// included in the description of the backup file that is created.
function backup_system_themes($version)

  {

    // Get all themes so we can back them up.
    $query =

    "SELECT

			id,

			name,

			folder AS folder_id

		FROM files

		WHERE

			(design = '1')

			AND (type = 'css')";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    $themes = mysqli_fetch_items($result);

    foreach ($themes as $theme)
      {

        // Check to see if this is a system or custom theme.
        $query = "SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $theme['id'] . "'";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $row = mysqli_fetch_row($result);

        // If this is a system theme then back it up.
        if ($row[0] > 0)
          {

            // Get theme name with and without file extension. We will use this in order to create a backup.
            $theme_name_without_extension = mb_substr($theme['name'], 0, mb_strrpos($theme['name'], '.'));

            $theme_extension = mb_substr($theme['name'], mb_strrpos($theme['name'], '.') + 1);

            // Prepare the backup theme name.
            $backup_theme_name = $theme_name_without_extension . '-backup-pre-v' . $version . '.' . $theme_extension;

            // Check if the backup theme name already exists (unlikely)
            $query = "SELECT COUNT(*) FROM files WHERE name = '" . escape($backup_theme_name) . "'";

            $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

            $row = mysqli_fetch_row($result);

            // If the backup theme name does not exist, then create backup theme.
            if ($row[0] == 0)
              {

                $query =

                "INSERT INTO files (

						name,

						folder,

						description,

						type,

						size,

						design,

						timestamp)

					VALUES (

						'" . escape($backup_theme_name) . "',

						'" . escape($theme['folder_id']) . "',

						'" . escape('This backup Theme was automatically created during the v' . $version . ' update.') . "',

						'" . escape($theme_extension) . "',

						'" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $theme['name'])) . "',

						'1',

						UNIX_TIMESTAMP())";

                $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

                $backup_theme_id = mysqli_insert_id(db::$con);

                // Create file handle in order to create file for backup theme.
                $handle = @fopen(FILE_DIRECTORY_PATH . '/' . $backup_theme_name, 'w');

                // If the backup theme could be opened for writing, then continue to update it.
                if ($handle == true)
                  {

                    // Get content of original theme in order to use it for backup theme.
                    $theme_content = @file_get_contents(FILE_DIRECTORY_PATH . '/' . $theme['name']);

                    // Update backup theme with the content.
                    @fwrite($handle, $theme_content);

                    // Close the backup theme.
                    @fclose($handle);

                  }

              }

          }

      }

  }

// Create function that will regenerate CSS for system themes.
// We use this sometimes during an update so themes have CSS for new features.
function update_system_themes()

  {

    // Get all themes so we can update them.
    $query =

    "SELECT

			id,

			name

		FROM files

		WHERE

			(design = '1')

			AND (type = 'css')";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    $themes = mysqli_fetch_items($result);

    // Loop through the themes in order to update them.
    foreach ($themes as $theme)
      {

        // Check to see if this is a system theme.
        $query = "SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $theme['id'] . "'";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $row = mysqli_fetch_row($result);

        // If this is a system theme then update it.
        if ($row[0] > 0)
          {

            // Get the properties from the database.
            $query =

            "SELECT

					area,

					`row`, # Backticks for reserved word.

					col,

					module,

					property,

					value,

					region_type,

					region_name

				FROM system_theme_css_rules 

				WHERE file_id = '" . $theme['id'] . "'";

            $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

            $rules = mysqli_fetch_items($result);

            $properties = array();

            // Loop through the system theme css rules in order to prepare css properties.
            foreach ($rules as $rule)
              {

                // If this is an ad region, then set the properties in the ad regions area of the array.
                if ($rule['region_type'] == 'ad')
                  {

                    $properties['ad_region'][$rule['region_name']][$rule['module']][$rule['property']] = $rule['value'];

                    // Otherwise if this is a menu region, then set the properties in the menu regions area of the array.
                    
                  }
                else if ($rule['region_type'] == 'menu')
                  {

                    $properties['menu_region'][$rule['region_name']][$rule['module']][$rule['property']] = $rule['value'];

                    // Otherwise, this is not an ad or menu region, so process rule differently.
                    
                  }
                else
                  {

                    // If there is a row then output the object.
                    if ($rule['row'] != 0)
                      {

                        $object = 'r' . $rule['row'] . 'c' . $rule['col'];

                        // Otherwise there is not a row so set the object as the base object.
                        
                      }
                    else
                      {

                        $object = 'base_object';

                      }

                    // If the module is not blank, then set the module.
                    if ($rule['module'] != '')
                      {

                        $module = $rule['module'];

                        // Otherwise the module is blank, so set the module to the base module
                        
                      }
                    else
                      {

                        $module = 'base_module';

                      }

                    $properties[$rule['area']][$object][$module][$rule['property']] = $rule['value'];

                  }

              }

            require_once (dirname(__FILE__) . '/../generate_system_theme_css.php');

            $system_theme_css = generate_system_theme_css($properties);

            $theme_path = FILE_DIRECTORY_PATH . '/' . $theme['name'];

            // Open theme in order to update CSS.
            $handle = @fopen($theme_path, 'w');

            // If theme could be opened, then write content.
            if ($handle)
              {

                fwrite($handle, $system_theme_css);

                fclose($handle);

              }

          }

      }

  }

// Create function that will allow us to add content to the end of custom themes.
// We use this sometimes during an update so custom themes have CSS for new features.
function update_custom_themes($content)

  {

    // Get all themes so we can update them.
    $query =

    "SELECT

			id,

			name

		FROM files

		WHERE

			(design = '1')

			AND (type = 'css')";

    $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

    $themes = mysqli_fetch_items($result);

    // Loop through the themes in order to update them.
    foreach ($themes as $theme)
      {

        // Check to see if this is a custom theme.
        $query = "SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $theme['id'] . "'";

        $result = mysqli_query(db::$con, $query) or exit(mysqli_error(db::$con));

        $row = mysqli_fetch_row($result);

        // If this is a custom theme then update it.
        if ($row[0] == 0)
          {

            $theme_path = FILE_DIRECTORY_PATH . '/' . $theme['name'];

            // Open the theme for writing so it can be updated.
            $handle = @fopen($theme_path, 'a');

            // If the theme could be opened for writing, then continue to update it.
            if ($handle == true)
              {

                @fwrite($handle, $content);

                @fclose($handle);

              }

          }

      }

  }

// Returns an array of all PineGrap tables that we use to know which tables in the database we can
// delete when doing a fresh install, so that we don't delete any custom tables.


function get_tables()
  {

    return array(

        'aclfolder',

        'ad_regions',

        'address_book',

        'ads',

        'affiliate_sign_up_form_pages',

        'allow_new_comments_for_items',

        'applied_gift_cards',

        'arrival_dates',

        'auto_dialogs',

        'banned_ip_addresses',

        'billing_information_pages',

        'calendar_event_exceptions',

        'calendar_event_locations',

        'calendar_event_view_pages',

        'calendar_event_views_calendars_xref',

        'calendar_events',

        'calendar_events_calendar_event_locations_xref',

        'calendar_events_calendars_xref',

        'calendar_view_pages',

        'calendar_views_calendars_xref',

        'calendars',

        'catalog_detail_pages',

        'catalog_pages',

        'comments',

        'commissions',

        'config',

        'contact_groups',

        'contact_groups_email_campaigns_xref',

        'contacts',

        'contacts_contact_groups_xref',

        'containers',

        'cookies',

        'countries',

        'cregion',

        'currencies',

        'custom_form_confirmation_pages',

        'custom_form_pages',

        'dregion',

        'email_a_friend_pages',

        'email_campaign_profiles',

        'email_campaigns',

        'email_recipients',

        'excluded_transit_dates',

        'express_order_pages',

        'files',

        'folder',

        'folder_view_pages',

        'form_data',

        'form_field_options',

        'form_fields',

        'form_item_view_pages',

        'form_list_view_browse_fields',

        'form_list_view_filters',

        'form_list_view_pages',

        'form_view_directories_form_list_views_xref',

        'form_view_directory_pages',

        'forms',

        'gift_cards',

        'key_codes',

        'log',

        'login_regions',

        'menu_items',

        'menus',

        'messages',

        'next_order_number',

        'offer_actions',

        'offer_actions_shipping_methods_xref',

        'offer_rules',

        'offer_rules_products_xref',

        'offers',

        'offers_offer_actions_xref',

        'opt_in',

        'order_form_pages',

        'order_item_gift_cards',

        'order_items',

        'order_preview_pages',

        'order_receipt_pages',

        'order_report_filters',

        'order_reports',

        'orders',

        'page',

        'photo_gallery_pages',

        'pregion',

        'preview_styles',

        'product_attribute_options',

        'product_attributes',

        'product_groups',

        'product_groups_attributes_xref',

        'products_images_xref',

        'product_groups_images_xref',

        'product_submit_form_fields',

        'products',

        'products_attributes_xref',

        'products_groups_xref',

        'products_zones_xref',

        'recurring_commission_profiles',

        'referral_sources',

        'remaining_reservation_spots',

        'search_items',

        'search_results_pages',

        'ship_date_adjustments',

        'ship_tos',

        'shipping_address_and_arrival_pages',

        'shipping_cutoffs',

        'shipping_delivery_dates',

        'shipping_method_pages',

        'shipping_methods',

        'shipping_methods_zones_xref',

        'shipping_rates',

        'shipping_tracking_numbers',

        'shopping_cart_pages',

        'short_links',

        'states',

        'style',

        'submitted_form_info',

        'submitted_form_views',

        'system_style_cells',

        'system_theme_css_rules',

        'tag_cloud_keywords',

        'tag_cloud_keywords_xref',

        'target_options',

        'tax_zones',

        'tax_zones_countries_xref',

        'tax_zones_states_xref',

        'talks',

        'update_address_book_pages',

        'user',

        'users_ad_regions_xref',

        'users_calendars_xref',

        'users_common_regions_xref',

        'users_contact_groups_xref',

        'users_menus_xref',

        'users_messages_xref',

        'verified_shipping_addresses',

        'visitor_report_filters',

        'visitor_reports',

        'visitors',

        'watchers',

        'zones',

        'zones_countries_xref',

        'zones_states_xref',

        'dashboard',

    );

  }

// Add custom shipping form support to express order


function upgrade_to_2017_2_1()
  {

    db("ALTER TABLE express_order_pages ADD shipping_form TINYINT UNSIGNED NOT NULL DEFAULT 0");

    db("ALTER TABLE express_order_pages DROP shipping_address_and_arrival_page_id");

    // Add new form type property to fields because we will need to distiguish between shipping
    // and billing fields for express order
    db("ALTER TABLE form_fields ADD form_type ENUM('', 'custom', 'product', 'shipping', 'billing') NOT NULL DEFAULT ''");

    db("ALTER TABLE form_fields ADD INDEX form_type (form_type)");

    // Get all fields in order to set form type
    

    $fields = db_items(

    "SELECT

			form_fields.id,

			form_fields.product_id,

			page.page_type

		FROM form_fields

		LEFT JOIN page ON form_fields.page_id = page.page_id

		ORDER BY form_fields.id");

    foreach ($fields as $field)
      {

        $field['form_type'] = '';

        if ($field['page_type'] == 'custom form')
          {

            $field['form_type'] = 'custom';

          }
        else if ($field['page_type'] == 'shipping address and arrival')
          {

            $field['form_type'] = 'shipping';

          }
        else if (

        $field['page_type'] == 'billing information'
 or $field['page_type'] == 'express order'
)
          {

            $field['form_type'] = 'billing';

          }
        else if ($field['product_id'])
          {

            $field['form_type'] = 'product';

          }

        db(

        "UPDATE form_fields

			SET form_type = '" . $field['form_type'] . "'

			WHERE id = '" . $field['id'] . "'");

      }

  }

// Update forgot password feature to send token link in email instead of temp password.


function upgrade_to_2017_2_2()
  {

    // Try to find a change random password page, so we can update field names
    $page_id = db("SELECT page_id FROM page WHERE page_type = 'change random password' LIMIT 1");

    // Change page_type enum from change random password to set password for in page table
    db("ALTER TABLE page CHANGE page_type page_type ENUM( 'standard', 'change password', 

		'set password', 'email a friend', 'error', 'folder view', 'forgot password', 'login', 

		'logout', 'photo gallery', 'membership confirmation', 'membership entrance', 'my account', 

		'my account profile', 'email preferences', 'view order', 'update address book', 'custom form', 

		'custom form confirmation', 'form list view', 'form item view', 'form view directory', 

		'calendar view', 'calendar event view', 'catalog', 'catalog detail', 'express order', 

		'order form', 'shopping cart', 'shipping address and arrival', 'shipping method', 

		'billing information', 'order preview', 'order receipt', 'registration confirmation', 

		'registration entrance', 'search results', 'affiliate sign up form', 

		'affiliate sign up confirmation', 'affiliate welcome') NOT NULL DEFAULT 'standard'");

    // Find root folder to assign to set-pass page
    $root_folder = db("SELECT folder_id FROM folder WHERE folder_parent = '0' LIMIT 1");

    // If page id was found, rename change random password to set-pass
    // And change folder to root
    if ($page_id != '')
      {

        db(

        "UPDATE page SET 

				page_type = 'set password', 

				page_name = 'set-pass', 

				page_folder = '" . $root_folder . "' 

			WHERE page_id = '" . $page_id . "' LIMIT 1");

      }

    // If a change random password page was found and there is a custom layout, then update custom layout
    if ($page_id and file_exists(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php'))
      {

        // Get the custom layout content
        $content = file_get_contents(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php');

        // If a custom layout file was found, then continue to update it
        if ($content)
          {

            // Backup old file
            copy(

            LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php',

            LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.bak.php');

            // Remove password verify
            $content = str_replace('<input type="password" name="new_password_verify" id="new_password_verify" placeholder="Confirm New Password*">', '', $content);

            // Update custom layout
            file_put_contents(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php', $content);

          }

      }

    // Try to find a forgot password page, so we can update button to send email
    $page_id = db("SELECT page_id FROM page WHERE page_type = 'forgot password' LIMIT 1");

    // If a forgot password page was found and there is a custom layout, then update custom layout
    if ($page_id and file_exists(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php'))
      {

        // Get the custom layout content
        $content = file_get_contents(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php');

        // If a custom layout file was found, then continue to update it
        if ($content)
          {

            // Backup old file
            copy(

            LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php',

            LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.bak.php');

            $new_label = 'Send Email';

            // Search for two common old labels and replace with new label.
            $content = str_replace('Email Temporary Password', $new_label, $content);

            $content = str_replace('Send Password', $new_label, $content);

            // Update custom layout
            file_put_contents(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php', $content);

          }

      }

    // Alter table to handle token to be emailed for reset password. We purposely allow NULL for
    // the token column, so that we can use a UNIQUE index. Most of the users won't have a token
    // at any given time, but MySQL allows a UNIQUE index for multiple NULL records (does not allow
    // that for empty string).
    db(

    "ALTER TABLE user

		DROP user_random_password,

		ADD token VARCHAR(64),

		ADD token_timestamp INT UNSIGNED NOT NULL DEFAULT 0,

		ADD UNIQUE (token)");

  }

// Add feature to allow the commerce manager to set whether the key code or offer code should be
// reported on, for each key code.


function upgrade_to_2017_2_3()
  {

    db("ALTER TABLE key_codes ADD report ENUM('key_code', 'offer_code') NOT NULL DEFAULT 'key_code'");

    // Update existing key codes so that report is set to offer code for single-use key codes,
    // because that was the previous way that we used to determine if an offer code should be
    // reported on.
    db("UPDATE key_codes SET report = 'offer_code' WHERE single_use = '1'");

  }

// Add real-time delivery date feature.


function upgrade_to_2017_2_4()
  {

    // Add new real-time rate column because the service column is now going to be used for both
    // real-time rates and delivery dates.
    db("ALTER TABLE shipping_methods ADD realtime_rate TINYINT UNSIGNED NOT NULL DEFAULT 0");

    db("UPDATE shipping_methods SET realtime_rate = '1' WHERE service != ''");

    db("ALTER TABLE shipping_methods CHANGE service service ENUM('', 'usps_priority', 'usps_express', 'usps_ground', 'ups_next_day_air', 'ups_next_day_air_early', 'ups_next_day_air_saver', 'ups_2nd_day_air', 'ups_2nd_day_air_am', 'ups_3_day_select', 'ups_ground', 'fedex_first_overnight', 'fedex_priority_overnight', 'fedex_standard_overnight', 'fedex_2_day_am', 'fedex_2_day', 'fedex_express_saver', 'fedex_ground') NOT NULL DEFAULT ''");

    db("ALTER TABLE shipping_rates CHANGE service service ENUM('usps_priority', 'usps_express', 'usps_ground', 'ups_next_day_air', 'ups_next_day_air_early', 'ups_next_day_air_saver', 'ups_2nd_day_air', 'ups_2nd_day_air_am', 'ups_3_day_select', 'ups_ground', 'fedex_first_overnight', 'fedex_priority_overnight', 'fedex_standard_overnight', 'fedex_2_day_am', 'fedex_2_day', 'fedex_express_saver', 'fedex_ground') NOT NULL DEFAULT 'usps_priority'");

    db(

    "ALTER TABLE config

		ADD ups TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD fedex TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD fedex_key VARCHAR(100) NOT NULL DEFAULT '',

		ADD fedex_password VARCHAR(100) NOT NULL DEFAULT '',

		ADD fedex_account VARCHAR(100) NOT NULL DEFAULT '',

		ADD fedex_meter VARCHAR(100) NOT NULL DEFAULT ''");

    // Enable new ups check box if site was using UPS.
    db("UPDATE config SET ups = '1' WHERE ups_key != ''");

    // Create delivery date cache table in order to minimize requests to carriers.
    db(

    "CREATE TABLE shipping_delivery_dates (

			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

			service ENUM('usps_priority', 'usps_express', 'usps_ground', 'ups_next_day_air', 'ups_next_day_air_early', 'ups_next_day_air_saver', 'ups_2nd_day_air', 'ups_2nd_day_air_am', 'ups_3_day_select', 'ups_ground', 'fedex_first_overnight', 'fedex_priority_overnight', 'fedex_standard_overnight', 'fedex_2_day_am', 'fedex_2_day', 'fedex_express_saver', 'fedex_ground') NOT NULL DEFAULT 'usps_priority',

			zip_code VARCHAR(50) NOT NULL DEFAULT '',

			ship_date DATE NOT NULL DEFAULT '0000-00-00',

			delivery_date DATE NOT NULL DEFAULT '0000-00-00',

			timestamp INT UNSIGNED NOT NULL DEFAULT 0,

			INDEX combination (service, zip_code, ship_date),

			INDEX timestamp (timestamp)

		)" . ENGINE);

  }

// Add handling features in order to determine, more precisely, when a shipment is shipped out.


function upgrade_to_2017_2_5()
  {

    db(

    "ALTER TABLE shipping_methods

		ADD handle_days SMALLINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_mon TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_tue TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_wed TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_thu TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_fri TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_sat TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD handle_sun TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_mon TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_tue TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_wed TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_thu TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_fri TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_sat TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD ship_sun TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD end_of_day TIME NOT NULL DEFAULT '00:00:00'");

    // Update existing shipping methods so weekdays are enabled for both handling and shipping.
    db(

    "UPDATE shipping_methods SET

			handle_mon = '1',

			handle_tue = '1',

			handle_wed = '1',

			handle_thu = '1',

			handle_fri = '1',

			ship_mon = '1',

			ship_tue = '1',

			ship_wed = '1',

			ship_thu = '1',

			ship_fri = '1'");

  }

// Add feature to allow only certain countries to require zip code.  Also, adding indexes to increase
// performance.


function upgrade_to_2017_2_6()
  {

    db(

    "ALTER TABLE countries

		ADD zip_code_required TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD INDEX code (code),

		ADD INDEX default_selected (default_selected)");

    // Update certain countries so that zip code is required.
    // Source: https://www.ups.com/worldshiphelp/WS16/ENU/AppHelp/Codes/Countries_Territories_Requiring_Postal_Codes.htm
    

    $zip_code_required_countries = array(
        'DZ',
        'AR',
        'AM',
        'AU',
        'AT',
        'AZ',
        'A2',
        'BD',
        'BY',
        'BE',
        'BA',
        'BR',
        'BN',
        'BG',
        'CA',
        'IC',
        'CN',
        'HR',
        'CY',
        'CZ',
        'DK',
        'EN',
        'EE',
        'FO',
        'FI',
        'FR',
        'GE',
        'DE',
        'GR',
        'GL',
        'GU',
        'GG',
        'HO',
        'HU',
        'IN',
        'ID',
        'IL',
        'IT',
        'JP',
        'JE',
        'KZ',
        'KR',
        'KO',
        'KG',
        'LV',
        'LI',
        'LT',
        'LU',
        'MK',
        'MG',
        'M3',
        'MY',
        'MH',
        'MQ',
        'YT',
        'MX',
        'MN',
        'ME',
        'NL',
        'NZ',
        'NB',
        'NO',
        'PK',
        'PH',
        'PL',
        'PO',
        'PT',
        'PR',
        'RE',
        'RU',
        'SA',
        'SF',
        'CS',
        'SG',
        'SK',
        'SI',
        'ZA',
        'ES',
        'LK',
        'NT',
        'SX',
        'UV',
        'VL',
        'SE',
        'CH',
        'TW',
        'TJ',
        'TH',
        'TU',
        'TN',
        'TR',
        'TM',
        'VI',
        'UA',
        'GB',
        'US',
        'UY',
        'UZ',
        'VA',
        'VN',
        'WL',
        'YA'
    );

    foreach ($zip_code_required_countries as $country)
      {

        db("UPDATE countries SET zip_code_required = '1' WHERE code = '" . e($country) . "'");

      }

  }

// Add index for order reference code in order to improve performance.  When an order is created
// a lookup is done in order to check that the new reference code is unique.  Previously, when a
// site had millions of orders, that lookup could become slow.  That might have caused the customer
// to experience a 1-2 second delay after adding an item to the cart.


function upgrade_to_2017_2_7()
  {

    // Get orders that do not have a reference code, in order to add a reference code, so that we
    // can add a unique index further below.  We noticed that one site had a bunch of orders
    // with no reference code.  We are not sure why.
    $orders = db_items("SELECT id FROM orders WHERE reference_code = ''");

    if ($orders)
      {

        // Add a regular index temporarily for performance reasons.
        db("ALTER TABLE orders ADD INDEX reference_code (reference_code)");

        // Loop through the orders in order to insert a reference code.
        foreach ($orders as $order)
          {

            db(

            "UPDATE orders SET reference_code = '" . e(generate_order_reference_code()) . "'

				WHERE id = '" . e($order['id']) . "'");

          }

        // Remove the regular index, because we don't need it anymore and we want to add a unique
        // index below.
        db("ALTER TABLE orders DROP INDEX reference_code");

      }

    // Now, add the unique index that we want.
    db("ALTER TABLE orders ADD UNIQUE reference_code (reference_code)");

  }

// Add order shipped auto campaign feature


function upgrade_to_2017_2_8()
  {

    db(

    "ALTER TABLE email_campaign_profiles

		ADD purpose ENUM('commercial', 'transactional') NOT NULL DEFAULT 'commercial',

		CHANGE action action ENUM('calendar_event_reserved', 'custom_form_submitted', 'email_campaign_sent', 'order_abandoned', 'order_completed', 'order_shipped', 'product_ordered') NOT NULL DEFAULT 'calendar_event_reserved'");

    db(

    "ALTER TABLE email_campaigns

		ADD purpose ENUM('commercial', 'transactional') NOT NULL DEFAULT 'commercial',

		CHANGE action action ENUM('', 'calendar_event_reserved', 'custom_form_submitted', 'email_campaign_sent', 'gift_card_ordered', 'order_abandoned', 'order_completed', 'order_shipped', 'product_ordered') NOT NULL DEFAULT ''");

    db("UPDATE email_campaigns SET purpose = 'transactional' WHERE action = 'gift_card_ordered'");

  }

// Add feature to allow ul class for menu to be set.


function upgrade_to_2017_2_9()
  {

    db("ALTER TABLE menus ADD class VARCHAR(255) NOT NULL DEFAULT ''");

  }

// Add notes feature to key codes.


function upgrade_to_2017_2_10()
  {

    db("ALTER TABLE key_codes ADD notes MEDIUMTEXT NOT NULL DEFAULT ''");

  }

// Add MailChimp feature to sync products and orders.


function upgrade_to_2017_2_11()
  {

    db("ALTER TABLE config

		ADD mailchimp TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD mailchimp_key VARCHAR(100) NOT NULL DEFAULT '',

		ADD mailchimp_list_id VARCHAR(100) NOT NULL DEFAULT '',

		ADD mailchimp_store_id VARCHAR(100) NOT NULL DEFAULT '',

		ADD mailchimp_sync_running TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD mailchimp_sync_days SMALLINT UNSIGNED NOT NULL DEFAULT 0,

		ADD mailchimp_sync_limit INT UNSIGNED NOT NULL DEFAULT 0,

		ADD mailchimp_automation TINYINT UNSIGNED NOT NULL DEFAULT 0");

    // Set defaults so job will sync the past 3 years of orders, and will sync a max of 200 orders
    // each time it runs.
    db("UPDATE config SET mailchimp_sync_days = '1095', mailchimp_sync_limit = '200'");

    db("ALTER TABLE orders

		ADD mailchimp_sync_timestamp INT UNSIGNED NOT NULL DEFAULT 0,

		ADD mailchimp_sync_error TINYINT UNSIGNED NOT NULL DEFAULT 0,

		ADD INDEX mailchimp_sync_timestamp (mailchimp_sync_timestamp)");

    // If there is not already an index for billing_email_address, then add one.  We noticed that
    // a few sites had customizations where there was already an index for that column.
    // The index for the billing email address is necessary because we need to look up the total
    // number of orders and total revenue for a customer, in order to send it to MailChimp.
    if (!db("SHOW INDEX FROM orders WHERE Column_name = 'billing_email_address'"))
      {

        db("ALTER TABLE orders ADD INDEX billing_email_address (billing_email_address)");

      }

    db("ALTER TABLE product_groups

		ADD mailchimp_sync_timestamp INT UNSIGNED NOT NULL DEFAULT 0,

		ADD INDEX timestamp (timestamp),

		ADD INDEX mailchimp_sync_timestamp (mailchimp_sync_timestamp)");

    db("ALTER TABLE products

		ADD mailchimp_sync_timestamp INT UNSIGNED NOT NULL DEFAULT 0,

		ADD INDEX mailchimp_sync_timestamp (mailchimp_sync_timestamp)");

  }

// Add feature to allow multiple products for offer rules.


function upgrade_to_2017_2_12()
  {

    db("

		CREATE TABLE offer_rules_products_xref

		(

			offer_rule_id INT UNSIGNED NOT NULL DEFAULT 0,

			product_id INT UNSIGNED NOT NULL DEFAULT 0,

			INDEX offer_rule_id (offer_rule_id),

			INDEX product_id (product_id)

		)" . ENGINE);

    // Get existing offer rules that have a required product in order to move info to new table.
    $offer_rules = db_items("

		SELECT id, required_product_id FROM offer_rules WHERE required_product_id != '0'");

    // If there are offer rules, the move info to new table.
    if ($offer_rules)
      {

        foreach ($offer_rules as $offer_rule)
          {

            db("

				INSERT INTO offer_rules_products_xref (

					offer_rule_id,

					product_id)

				VALUES (

					'" . e($offer_rule['id']) . "',

					'" . e($offer_rule['required_product_id']) . "')");

          }

      }

    // Remove the old product column that is no longer necessary.
    db("ALTER TABLE offer_rules DROP required_product_id");

  }

// Add custom layout support for form item views.


function upgrade_to_2017_2_13()
  {

    // We have to run the query below because even though we did not support custom layout for form
    // item views in the past, a form item view might have been set to custom in the DB, if it was
    // duplicated from another page.  After the update, we want all form item views to have a
    // system layout, like before.
    

    db("UPDATE page SET layout_type = 'system', layout_modified = '0'

		WHERE page_type = 'form item view'");

  }

//subsription key
// Add language option and default theme option for software.
function upgrade_to_2019_1_1()
  {

    db("ALTER TABLE config

	ADD subscription_key VARCHAR(256) NOT NULL DEFAULT '',

	ADD software_language ENUM('en','tr') NOT NULL DEFAULT 'en',

	ADD software_theme ENUM('coloron','darkon','lighton') NOT NULL DEFAULT 'coloron'");

  }

// Add developer pass pin to All Users (default pin:0000)
//Developers define to config.php to page name and an unlock pin to lock pages.
//If page name define like below, users redirect to developer_lock.php.
//Software ask's pin code to unlock and access to page.
//If user enter pin  correct, software redirect user to page try to access and no redirect developer_lock.php until Pin code is change.
function upgrade_to_2019_1_2()
  {

    db("ALTER TABLE user ADD user_devpasspin VARCHAR(4) NOT NULL DEFAULT '0000'");

  }

// Here we integrate payment gateway iyzipay api key and secret key
function upgrade_to_2019_1_3()
  {

    db("ALTER TABLE config 

		ADD ecommerce_iyzipay_api_key VARCHAR(255) NOT NULL DEFAULT '',

		ADD ecommerce_iyzipay_secret_key VARCHAR(255) NOT NULL DEFAULT ''");

  }

// Than update gateway Select and include iyzipay option
function upgrade_to_2019_1_4()
  {

    db("ALTER TABLE config CHANGE ecommerce_payment_gateway ecommerce_payment_gateway ENUM('', 'Authorize.Net', 'ClearCommerce', 'First Data Global Gateway', 'PayPal Payflow Pro', 'PayPal Payments Pro', 'Sage', 'Stripe','Iyzipay') NOT NULL DEFAULT ''");

  }

function upgrade_to_2019_1_5()
  {

    db("ALTER TABLE config ADD ecommerce_iyzipay_installment ENUM('1', '2', '3', '6', '9', '12') NOT NULL DEFAULT '1'");

  }

function upgrade_to_2019_1_6()
  {

    db("ALTER TABLE config ADD ecommerce_iyzipay_threeds TINYINT UNSIGNED NOT NULL DEFAULT 0");

  }

function upgrade_to_2019_2_3()
  {

    db("ALTER TABLE config ADD time_format ENUM('twelve_hours', 'twenty_four_hours') NOT NULL DEFAULT 'twelve_hours'");

  }

//The Future lets you upload/select multiple images and use it in your product detail page both product and product group.
function upgrade_to_2020_1_1()
  {

    db("

	   CREATE TABLE products_images_xref

	   (

		   product INT UNSIGNED NOT NULL DEFAULT 0,

		   file_name VARCHAR(255) DEFAULT ''

	   )" . ENGINE);

    db("

	   CREATE TABLE product_groups_images_xref

	   (

		   product_group INT UNSIGNED NOT NULL DEFAULT 0,

		   file_name VARCHAR(255) DEFAULT ''

	   )" . ENGINE);

  }

function upgrade_to_2020_1_5()
  {

    //this update contains some updates installment options for submit order,order checkout and view order pages to show instalment prices and amounts
    //payment installment is if there is installment and how many installments
    db("ALTER TABLE orders ADD payment_installment INT UNSIGNED NOT NULL DEFAULT 1");

    //and installment charge is increase of installments charges.
    db("ALTER TABLE orders ADD installment_charges INT UNSIGNED NOT NULL DEFAULT 0");

    //it is not out of stock message, it is for new out of stock products fallow method. if this is return to 1 from submit_order page than this product, displayed on out of stock page and welcome page
    db("ALTER TABLE products

	ADD out_of_stock INT UNSIGNED NOT NULL DEFAULT 0,

	ADD out_of_stock_timestamp INT UNSIGNED NOT NULL DEFAULT 0");

  }

// who is online.
function upgrade_to_2020_1_6()
  {

    db("ALTER TABLE user ADD user_online_timestamp INT UNSIGNED NOT NULL DEFAULT 0");

  }

//default product code
//this is product image code template for Add Product pages.
//after product image code loop developed and products support multiple image selection this example may help users to add faster products
function upgrade_to_2020_1_7()
  {

    db("ALTER TABLE config ADD product_image_code_template TEXT NOT NULL DEFAULT ''");

  }

function upgrade_to_2020_2_3()
  {

    db("ALTER TABLE config MODIFY subscription_key VARCHAR(256)");

  }

//The Future lets you options for new welcome widget screen.
function upgrade_to_2021_1_3()
  {

    db("

		CREATE TABLE dashboard

		(

			main_weather_location VARCHAR(255) DEFAULT ''

			

		)" . ENGINE);

    db("INSERT INTO dashboard (main_weather_location)VALUES ('london')");

    db("ALTER TABLE dashboard ADD bg_image VARCHAR(256) NOT NULL DEFAULT 'bg_purple_and_blue'");

    db("ALTER TABLE dashboard ADD widget_themes ENUM('blur_one', 'blur_two','blur_three') NOT NULL DEFAULT 'blur_one'");

  }

//The Future lets you widget activate/deactivate or order them.
function upgrade_to_2021_1_8()
  {

    //insert into dashboard order_widget default all widgets active and default order.
    db("ALTER TABLE dashboard ADD order_widgets VARCHAR(256) NOT NULL DEFAULT '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16'");

  }

// Yahoo weather api integration
function upgrade_to_2021_1_9()
  {
    db("ALTER TABLE dashboard ADD weather_app_id VARCHAR(256) DEFAULT ''");
    db("ALTER TABLE dashboard ADD weather_key VARCHAR(256) DEFAULT ''");
    db("ALTER TABLE dashboard ADD weather_secret VARCHAR(256) DEFAULT ''");
  }

// Note Widget
function upgrade_to_2021_1_11()
  {
    db("ALTER TABLE dashboard ADD notes_widget_data TEXT");
    $query = "UPDATE dashboard
            SET
            notes_widget_data = 'PGgxPkNvbW1vbiBub3RlIGFyZWEgZm9yIHNpdGUgPHN0cm9uZyBzdHlsZT0iY29sb3I6IHJnYigxMDIsIDE4NSwgMTAyKTsiPmFkbWluaXN0cmF0b3JzPC9zdHJvbmc+PC9oMT48cD48YnI+PC9wPjxwPjxzdHJvbmc+YWRkPC9zdHJvbmc+IG9yIDxzdHJvbmc+ZWRpdDwvc3Ryb25nPiBub3RlcyBoZXJlLjwvcD48cD5vciBsaXN0cyBsaWtlOjwvcD48b2w+PGxpPkxpc3QgY29udGVudDwvbGk+PGxpPkFub3RoZXIgbGlzdCBjb250ZW50PC9saT48L29sPjx1bD48bGk+U3ViIGxpc3QgY29udGVudDwvbGk+PGxpPkFub3RoZXIgc3ViIGxpc3QgY29udGVudDwvbGk+PC91bD4='
        ";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
  }
function upgrade_to_2021_1_12()
  {
    db("ALTER TABLE dashboard CHANGE widget_themes widget_themes ENUM('blur_one', 'blur_two','blur_three','classic')");
  }
function upgrade_to_2021_1_14()
  {
    db("ALTER TABLE config ADD custom_css TEXT NOT NULL DEFAULT '/* Custom Stylesheet that can overwrite backend CSS files */'");
  }


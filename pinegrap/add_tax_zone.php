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

if (!$_POST) {
    // get all countries for countries selection
    $query = "SELECT id, name FROM countries ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $output_disallowed_countries .= '<option value="' . $row['id'] . '">' . h($row['name']) . '</option>';
    }

    // get all states for states selection
    $query = "SELECT states.id, states.name, countries.name as country_name
             FROM states
             LEFT JOIN countries ON countries.id = states.country_id
             ORDER BY countries.name, states.name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $output_disallowed_states .= '<option value="' . $row['id'] . '">' . h($row['country_name']) . ': ' . h($row['name']) . '</option>';
    }

    $output =
        output_header() . '
        <div id="subnav">
            <h1>[new tax zone]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Tax Zone</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new tax zone that will be used to calculate tax during checkout based on the products and billing address.</div>
            <form name="form" action="add_tax_zone.php" method="post" onsubmit="prepare_selects(new Array(\'allowed_countries\', \'allowed_states\'))">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Tax Zone Name</h2></th>
                    </tr>
                    <tr>
                        <td>Tax Zone Name:</td>
                        <td><input type="text" name="name" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <td>Tax Rate (%):</td>
                        <td><input type="text" name="tax_rate" size="5" /></td>
                    </tr>
                </table>
                <table class="field" style="width: 50%">
                    <tr>
                        <th colspan="3"><h2>Tax Zone Countries</h2></th>
                    </tr>   
                    <tr>
                        <td style="width: 50%">
                            <div style="margin-bottom: 3px">Allowed Countries</div>
                            <input type="hidden" id="allowed_countries_hidden" name="allowed_countries_hidden" value="">
                            <select id="allowed_countries" multiple="multiple" size="10" style="width: 95%; background-image: none;"></select>
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding-left: 15px; padding-right: 15px">
                            <input type="button" value="&gt;&gt;" onclick="move_options(\'allowed_countries\', \'disallowed_countries\', \'right\');" /><br />
                            <br />
                            <input type="button" value="&lt;&lt;" onclick="move_options(\'allowed_countries\', \'disallowed_countries\', \'left\');" /><br />
                        </td>
                        <td style="width: 50%">
                            <div style="margin-bottom: 3px">Disallowed Countries</div>
                            <select id="disallowed_countries" multiple="multiple" size="10" style="background-image: none; width: 95%">' . $output_disallowed_countries . '</select>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="3"><h2>Tax Zone States/Provinces</h2></th>
                    </tr>
                    <tr>
                        <td style="width: 50%">
                            <div style="margin-bottom: 3px">Allowed States</div>
                            <input type="hidden" id="allowed_states_hidden" name="allowed_states_hidden" value="">
                            <select id="allowed_states" multiple="multiple" size="10" style="background-image: none; width: 95%"></select>
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding-left: 15px; padding-right: 15px">
                            <input type="button" value="&gt;&gt;" onclick="move_options(\'allowed_states\', \'disallowed_states\', \'right\');" /><br />
                            <br />
                            <input type="button" value="&lt;&lt;" onclick="move_options(\'allowed_states\', \'disallowed_states\', \'left\');" /><br />
                        </td>
                        <td style="width: 50%">
                            <div style="margin-bottom: 3px">Disallowed States</div>
                            <select id="disallowed_states" multiple="multiple" size="10" style="background-image: none; width: 95%">' . $output_disallowed_states . '</select>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();
    
    // create tax zone
    $query = "INSERT INTO tax_zones (
                name,
                tax_rate,
                user,
                timestamp)
            VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['tax_rate']) . "',
                " . $user['id'] . ",
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $tax_zone_id = mysqli_insert_id(db::$con);

    // load all allowed countries in array by exploding string that has allowed country ids separated by commas
    $allowed_countries = explode(',', $_POST['allowed_countries_hidden']);

    // foreach allowed country insert row in tax_zones_countries_xref table
    foreach ($allowed_countries as $country_id) {
        // if country id is not blank, insert row
        if ($country_id) {
            $query = "INSERT INTO tax_zones_countries_xref (tax_zone_id, country_id) VALUES ($tax_zone_id, '" . escape($country_id) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // load all allowed states in array by exploding string that has allowed state ids separated by commas
    $allowed_states = explode(',', $_POST['allowed_states_hidden']);

    // foreach allowed state insert row in tax_zones_states_xref table
    foreach ($allowed_states as $state_id) {
        // if state id is not blank, insert row
        if ($state_id) {
            $query = "INSERT INTO tax_zones_states_xref (tax_zone_id, state_id) VALUES ($tax_zone_id, '" . escape($state_id) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    log_activity('tax zone (' . $_POST['name'] . ') was created', $_SESSION['sessionusername']);

    // forward user to view tax zones screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_tax_zones.php');
}
?>
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
    // get tax zone data
    $query = "SELECT * FROM tax_zones WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = $row['name'];
    $tax_rate = $row['tax_rate'];

    // get all countries for countries selection
    $query = "SELECT id, name FROM countries ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $countries[] = array('id'=>$row['id'], 'name'=>$row['name']);
    }

    // if there is at least one country
    if ($countries) {
        // foreach country, check if country is allowed or disallowed for this tax zone
        foreach ($countries as $key => $value) {
            $query = "SELECT country_id FROM tax_zones_countries_xref WHERE tax_zone_id = '" . escape($_GET['id']) . "' AND country_id = '" . $countries[$key]['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if tax zone and country were found
            if (mysqli_num_rows($result)) {
                $allowed_countries[] = $countries[$key];
            } else {
                $disallowed_countries[] = $countries[$key];
            }
        }

        // if there is at least one allowed country
        if ($allowed_countries) {
            // foreach allowed country prepare option
            foreach ($allowed_countries as $key => $value) {
                $output_allowed_countries .= '<option value="' . $allowed_countries[$key]['id'] . '">' . h($allowed_countries[$key]['name']) . '</option>';
            }
        }

        // if there is at least one disallowed country
        if ($disallowed_countries) {
            // foreach disallowed country prepare option
            foreach ($disallowed_countries as $key => $value) {
                $output_disallowed_countries .= '<option value="' . $disallowed_countries[$key]['id'] . '">' . h($disallowed_countries[$key]['name']) . '</option>';
            }
        }
    }

    // get all states for states selection
    $query = "SELECT states.id, states.name, countries.name as country_name
             FROM states
             LEFT JOIN countries ON countries.id = states.country_id
             ORDER BY countries.name, states.name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $states[] = array('id'=>$row['id'], 'name'=>$row['name'], 'country_name'=>$row['country_name']);
    }

    // if there is at least one state
    if ($states) {
        // foreach state, check if state is allowed or disallowed for this tax zone
        foreach ($states as $key => $value) {
            $query = "SELECT state_id FROM tax_zones_states_xref WHERE tax_zone_id = '" . escape($_GET['id']) . "' AND state_id = '" . $states[$key]['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if tax zone and state were found
            if (mysqli_num_rows($result)) {
                $allowed_states[] = $states[$key];
            } else {
                $disallowed_states[] = $states[$key];
            }
        }

        // if there is at least one allowed state
        if ($allowed_states) {
            // foreach allowed state prepare option
            foreach ($allowed_states as $key => $value) {
                $output_allowed_states .= '<option value="' . $allowed_states[$key]['id'] . '">' . h($allowed_states[$key]['country_name']) . ': ' . h($allowed_states[$key]['name']) . '</option>';
            }
        }

        // if there is at least one disallowed state
        if ($disallowed_states) {
            // foreach disallowed state prepare option
            foreach ($disallowed_states as $key => $value) {
                $output_disallowed_states .= '<option value="' . $disallowed_states[$key]['id'] . '">' . h($disallowed_states[$key]['country_name']) . ': ' . h($disallowed_states[$key]['name']) . '</option>';
            }
        }
    }

    $output =
        output_header() . '
        <div id="subnav">
            <h1>' . h($name) . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Tax Zone</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a tax zone that will be used to calculate tax during checkout based on the products and billing address.</div>
            <form name="form" action="edit_tax_zone.php" method="post" onsubmit="prepare_selects(new Array(\'allowed_countries\', \'allowed_states\'))">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Tax Zone Name</h2></th>
                    </tr>
                    <tr>
                        <td>Tax Zone Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . h($name) . '" /></td>
                    </tr>
                    <tr>
                        <td>Tax Rate (%):</td>
                        <td><input type="text" name="tax_rate" size="5" value="' . $tax_rate . '" /></td>
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
                            <select id="allowed_countries" multiple="multiple" size="10" style="background-image: none; width: 95%">' . $output_allowed_countries . '</select>
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
                            <select id="allowed_states" multiple="multiple" size="10" style="background-image: none; width: 95%">' . $output_allowed_states . '</select>
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This tax zone will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();
    
    // delete tax zone references in tax_zones_countries_xref (we do this reguardless of whether we are deleting the tax zone or updating the tax zone)
    $query = "DELETE FROM tax_zones_countries_xref ".
             "WHERE tax_zone_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // delete tax zone references in tax_zones_states_xref (we do this reguardless of whether we are deleting the tax zone or updating the tax zone)
    $query = "DELETE FROM tax_zones_states_xref ".
             "WHERE tax_zone_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if tax zone was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete tax zone
        $query = "DELETE FROM tax_zones WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('tax zone (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
        
    // else tax zone was not selected for delete
    } else {
        // update tax zone
        $query = "UPDATE tax_zones SET
                    name = '" . escape($_POST['name']) . "',
                    tax_rate = '" . escape($_POST['tax_rate']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // load all allowed countries in array by exploding string that has allowed country ids separated by commas
        $allowed_countries = explode(',', $_POST['allowed_countries_hidden']);

        // foreach allowed country insert row in tax_zones_countries_xref table
        foreach ($allowed_countries as $country_id) {
            // if country id is not blank, insert row
            if ($country_id) {
                $query = "INSERT INTO tax_zones_countries_xref (tax_zone_id, country_id) VALUES ('" . escape($_POST['id']) . "', '" . escape($country_id) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        // load all allowed states in array by exploding string that has allowed state ids separated by commas
        $allowed_states = explode(',', $_POST['allowed_states_hidden']);

        // foreach allowed state insert row in tax_zones_states_xref table
        foreach ($allowed_states as $state_id) {
            // if state id is not blank, insert row
            if ($state_id) {
                $query = "INSERT INTO tax_zones_states_xref (tax_zone_id, state_id) VALUES ('" . escape($_POST['id']) . "', '" . escape($state_id) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        log_activity('tax zone (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view tax zones screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_tax_zones.php');
}
?>
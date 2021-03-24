
<div id="subnav">
    <table>
        <tbody>
            <tr>     
                <td>    
                    <ul>
                        <li><a href="settings.php">Site Settings</a></li>
                    </ul>
                </td> 
                <td>    
                    <ul>
                        <li><a href="mailchimp_settings.php">MailChimp Settings</a></li>
                    </ul>
                </td>
                <td>    
                    <ul>
                        <li><a href="smtp_settings.php">SMTP Settings</a></li>
                    </ul>
                </td>

				<td>    

					<ul>

						<li><a href="backups.php">Backup Manager</a></li>

					</ul>

				</td>

				<td>    

					<ul>

						<li><a href="view_log.php" >Site Log</a></li>

					</ul>

				</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="content">

    <?=$form->get_messages()?>

    <a href="#" id="help_link">Help</a>

    <h1>MailChimp Settings</h1>

    <div class="subheading" style="margin-bottom: 1.5em">
        Auto-export customers, orders, &amp; products to MailChimp regularly. Requires cron job (job.php).
    </div>

    <form method="post">

        <?=get_token_field()?>

        <table class="field">

            <tr id="mailchimp_row">
                <td>
                    <label for="mailchimp">MailChimp Sync:</label>
                </td>
                <td>
                    <input
                        type="checkbox"
                        id="mailchimp"
                        name="mailchimp"
                        value="1"
                        class="checkbox">
                </td>
            </tr>

            <tr id="mailchimp_key_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_key">API Key:</label>
                </td>
                <td>
                    <input
                        type="text"
                        id="mailchimp_key"
                        name="mailchimp_key"
                        size="40"
                        maxlength="100">
                </td>
            </tr>

            <tr id="mailchimp_list_id_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_list_id">List ID:</label>
                </td>
                <td>
                    <input
                        type="text"
                        id="mailchimp_list_id"
                        name="mailchimp_list_id"
                        size="20"
                        maxlength="100">
                </td>
            </tr>

            <tr id="mailchimp_store_id_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_store_id">Store ID:</label>
                </td>
                <td>
                    <input
                        type="text"
                        id="mailchimp_store_id"
                        name="mailchimp_store_id"
                        size="20"
                        maxlength="100">
                </td>
            </tr>

            <tr id="mailchimp_sync_days_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_sync_days">Historical Sync:</label>
                </td>
                <td>
                    <input
                        type="text"
                        id="mailchimp_sync_days"
                        name="mailchimp_sync_days"
                        size="6"
                        maxlength="100">&nbsp;

                    days in the past&nbsp;
                    (Set how far in the past to sync orders. Leave blank to sync all historical orders.)
                </td>
            </tr>

            <tr id="mailchimp_sync_limit_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_sync_limit">Limit Sync:</label>
                </td>
                <td>
                    <input
                        type="text"
                        id="mailchimp_sync_limit"
                        name="mailchimp_sync_limit"
                        size="6"
                        maxlength="100">&nbsp;

                    (max number of orders to sync each time cron job runs)
                </td>
            </tr>

            <tr id="mailchimp_automation_row" style="display: none">
                <td style="padding-left: 2em">
                    <label for="mailchimp_automation">Automation:</label>
                </td>
                <td>
                    <input
                        type="checkbox"
                        id="mailchimp_automation"
                        name="mailchimp_automation"
                        value="1"
                        class="checkbox">&nbsp;

                    (only enable after all historical orders have been synced, to start sending MailChimp automation campaigns)
                </td>
            </tr>
        </table>

        <div class="buttons">
            <input type="submit" name="submit_button" value="Save" class="submit-primary">&nbsp;&nbsp;
            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
        </div>
    </form>
</div>

<script>
    (function () {

        function toggle_mailchimp() {

            if ($('#mailchimp').is(':checked')) {
                $('#mailchimp_key_row').fadeIn();
                $('#mailchimp_list_id_row').fadeIn();
                $('#mailchimp_store_id_row').fadeIn();
                $('#mailchimp_sync_days_row').fadeIn();
                $('#mailchimp_sync_limit_row').fadeIn();
                $('#mailchimp_automation_row').fadeIn();

            } else {
                $('#mailchimp_key_row').fadeOut();
                $('#mailchimp_list_id_row').fadeOut();
                $('#mailchimp_store_id_row').fadeOut();
                $('#mailchimp_sync_days_row').fadeOut();
                $('#mailchimp_sync_limit_row').fadeOut();
                $('#mailchimp_automation_row').fadeOut();
            }
        }

        toggle_mailchimp();

        $('#mailchimp').change(function() {
            toggle_mailchimp();
        });
    })();
</script>
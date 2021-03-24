
<?=$messages?>

<form <?=$attributes?>>

    <table style="margin-bottom: 1em">

        <tr>
            <td><label for="email_address">Email*:</label></td>
            <td><input type="email" name="email_address" id="email_address" class="software_input_text"></td>
        </tr>

        <tr>
            <td><label for="current_password">Current Password*:</label></td>
            <td><input type="password" name="current_password" id="current_password" class="software_input_password"></td>
        </tr>

        <?php if ($strong_password_help): ?>
            <tr>
                <td colspan="2">
                    <?=$strong_password_help?>
                </td>
            </tr>
        <?php endif ?>

        <tr>
            <td><label for="new_password">New Password*:</label></td>
            <td><input type="password" name="new_password" id="new_password" class="software_input_password"></td>
        </tr>

        <tr>
            <td><label for="new_password_verify">Confirm New Password*:</label></td>
            <td><input type="password" name="new_password_verify" id="new_password_verify" class="software_input_password"></td>
        </tr>

        <?php if (PASSWORD_HINT): ?>
            <tr>
                <td><label for="password_hint">Password Hint:</label></td>
                <td><input type="text" name="password_hint" id="password_hint" class="software_input_text"></td>
            </tr>
        <?php endif ?>

    </table>

    <div>

        <input type="submit" name="submit" value="Change Password" class="software_input_submit_primary submit_button">

        <?php if ($my_account_url): ?>
            &nbsp; <a href="<?=h($my_account_url)?>" class="software_button_secondary cancel_button">Cancel</a>
        <?php endif ?>

    </div>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

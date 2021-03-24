
<?=$messages?>

<form <?=$attributes?>>

    <p><?=h($email)?></p>

    <table style="margin-bottom: 1em">

        <?php if ($strong_password_help): ?>
            <tr>
                <td colspan="2">
                    <?=$strong_password_help?>
                </td>
            </tr>
        <?php endif ?>

        <tr>
            <td><label for="new_password">Password*:</label></td>
            <td><input type="password" name="new_password" id="new_password" class="software_input_password"></td>
        </tr>

        <?php if (PASSWORD_HINT): ?>
            <tr>
                <td><label for="password_hint">Password Hint:</label></td>
                <td><input type="text" name="password_hint" id="password_hint" class="software_input_text"></td>
            </tr>
        <?php endif ?>

    </table>

    <div>
        <input type="submit" name="submit" value="Set Password" class="software_input_submit_primary submit_button">
    </div>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

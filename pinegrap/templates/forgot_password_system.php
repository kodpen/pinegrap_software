
<?=$messages?>

<form <?=$attributes?>>

    <?php if (!$screen): ?>

        <table>

            <tr>
                <td><label for="email">Email:</label></td>
                <td><input name="email" id="email" type="email" size="30" class="software_input_text"></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" name="submit" value="Send Email" class="software_input_submit_primary submit_button" style="margin-top: 1em">
                </td>
            </tr>
            
        </table>

    <?php elseif ($screen == 'password_hint'): ?>

        <p>Your Password Hint is: <strong><?=h($password_hint)?></strong></p>

        <p>Do you remember your password now?</p>

        <div>

            <a href="<?=h($send_to)?>" class="software_button_primary">Yes, I Remember</a>

            &nbsp;

            <input type="submit" value="No, I Forget" class="software_input_submit_primary">

        </div>

    <?php endif ?>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

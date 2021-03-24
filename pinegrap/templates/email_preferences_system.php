
<?=$messages?>

<form <?=$attributes?>>

    <div style="margin-bottom: 15px">
        <label for="email_address">Email:</label>
        <input type="email" name="email_address" id="email_address" class="software_input_text" size="40">
    </div>

    <div style="margin-bottom: 15px">
        <label>
            <input type="checkbox" name="opt_in" value="1" class="software_input_checkbox">
            <?=h(OPT_IN_LABEL)?>
        </label>
    </div>

    <?php if ($contact_groups): ?>

        <div class="contact_groups" style="display: none; padding-left: 25px; margin-bottom: 15px">

            <div style="margin-bottom: 10px">Opt me in or out of the following lists:</div>

            <div style="padding-left: 25px">

                <?php foreach($contact_groups as $contact_group): ?>

                    <div>
                        <label>
                            <input type="checkbox" name="contact_group_<?=$contact_group['id']?>" value="1" class="software_input_checkbox">
                            <?=h($contact_group['name'])?>

                            <?php if ($contact_group['description']): ?>
                                - <?=h($contact_group['description'])?>
                            <?php endif ?>
                        </label>
                    </div>

                <?php endforeach ?>

            </div>

        </div>

    <?php endif ?>

    <div>

        <input type="submit" name="submit" value="Update" class="software_input_submit_primary update_button">

        <?php if ($my_account_url): ?>
            &nbsp; <a href="<?=h($my_account_url)?>" class="software_button_secondary cancel_button">Cancel</a>
        <?php endif ?>

    </div>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
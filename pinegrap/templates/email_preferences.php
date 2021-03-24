
<?=$messages?>

<form <?=$attributes?>>

    <div class="form-group">
        <label for="email_address">Email</label>
        <input type="email" name="email_address" id="email_address" class="form-control">
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" name="opt_in" value="1">
            <?=h(OPT_IN_LABEL)?>
        </label>
    </div>

    <?php if ($contact_groups): ?>

        <div class="contact_groups" style="display: none">

            <h2>Opt me in or out of the following lists:</h2>

            <?php foreach($contact_groups as $contact_group): ?>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="contact_group_<?=$contact_group['id']?>" value="1">
                        <?=h($contact_group['name'])?>

                        <?php if ($contact_group['description']): ?>
                            - <?=h($contact_group['description'])?>
                        <?php endif ?>
                    </label>
                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>

    <button type="submit" class="btn btn-primary">Update</button>

    <?php if ($my_account_url): ?>
        <a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">Cancel</a>
    <?php endif ?>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
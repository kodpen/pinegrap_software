
<?=$messages?>

<form <?=$attributes?>>

<?=eval('?>' . generate_form_layout_content(array('page_id' => $page_id)))?>
    <?php if ($watcher_option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Notify me when a <?=h(mb_strtolower($comment_label))?> is added.
            </label>
        </div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <h5>To prevent spam, please tell us:</h5>

        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
        </div>

    <?php endif ?>

    <?php
        // The name attribute on the buttons below is important for the system
        // to know which button was pressed.
    ?>

    <?php
        // If save-for-later is enabled for custom form then show button.
        if ($save):
    ?>
        <button type="submit" name="save_button" class="btn btn-default btn-secondary">
            Save for Later
        </button>
    <?php endif ?>

    <button type="submit" name="submit_button" class="btn btn-primary">
        <?=h($submit_button_label)?>
    </button>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>

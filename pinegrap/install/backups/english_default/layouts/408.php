
<?=$messages?>

<form <?=$attributes?>>

    <!-- subject -->
    <div class="form-group">
        <label for="174"><?=$field['174']['label']?></label>
        <input type="text" name="174" id="174">
    </div>
    
    <!-- detail -->
    <div class="form-group">
        <label for="176"><?=$field['176']['label']?></label>
        <textarea name="176" id="176"></textarea>
    </div>
    

    <?php if ($watcher_option): ?>
        <div class="checkbox mb24">
            <label class="check-box">
                <input type="checkbox" name="watcher" value="1" checked>
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span>Notify me when a <?=h(mb_strtolower($comment_label))?> is added.</span>
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

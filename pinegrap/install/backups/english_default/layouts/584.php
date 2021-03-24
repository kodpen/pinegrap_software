
<div class="row">
    
<?=$messages?>

<form <?=$attributes?>>

    <div class="col-sm-6">
    <!-- first_name -->
    <div class="form-group">
        <label for="270"><?=$field['270']['label']?></label>
        <input type="text" name="270" id="270" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-6">
    <!-- last_name -->
    <div class="form-group">
        <label for="271"><?=$field['271']['label']?></label>
        <input type="text" name="271" id="271" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-6">
    <!-- email -->
    <div class="form-group">
        <label for="272"><?=$field['272']['label']?></label>
        <input type="email" name="272" id="272" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-6">
    <!-- phone -->
    <div class="form-group">
        <label for="273"><?=$field['273']['label']?></label>
        <input type="text" name="273" id="273" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-9">
    <!-- event -->
    <div class="form-group">
        <label for="275"><?=$field['275']['label']?></label>
        <input type="text" name="275" id="275" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-3">
    <!-- tickets -->
    <div class="form-group">
        <label for="274"><?=$field['274']['label']?></label>
        <input type="text" name="274" id="274" class="form-control">
    </div>
    </div>

    <?php if ($watcher_option): ?>
        <div class="col-sm-12">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Notify me when a <?=h(mb_strtolower($comment_label))?> is added.
            </label>
        </div>
    	</div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>
    
        <div class="col-sm-12">
        <h5>To prevent spam, please tell us:</h5>

        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
        </div>
		</div>

    <?php endif ?>

    <?php
        // The name attribute on the buttons below is important for the system
        // to know which button was pressed.
    ?>
    
    <div class="col-sm-12">

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
        
    </div>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
    
</div>

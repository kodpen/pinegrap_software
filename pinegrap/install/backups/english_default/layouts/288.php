
<?=$messages?>

<form <?=$attributes?>>

    <!-- Organization Heading -->
    <?=$field['117']['information']?>
    
    <!-- organization -->
    <div class="form-group">
        <label for="111"><?=$field['111']['label']?></label>
        <input type="text" name="111" id="111" class="form-control">
    </div>
    
    <!-- services -->
    <div><?=$field['118']['label']?></div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Planning">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Planning</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Designing">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Designing</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Building">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Building</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Retrofiting">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Retrofiting</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Customizing">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Customizing</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Finishing">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Finishing</span>
        </label>
    </div>
    
    <div class="checkbox">
        <label class="check-box">
            <input type="checkbox" name="118[]" value="Maintainence">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
            <span>Maintainence</span>
        </label>
    </div>
    
    <!-- description -->
    <div class="form-group">
        <label for="112"><?=$field['112']['label']?></label>
        <textarea name="112" id="112" class="form-control"></textarea>
    </div>
    
    <!-- Contact Heading -->
    <?=$field['116']['information']?>
    
    <!-- salutation -->
    <div class="form-group">
       	<label for="114"><?=$field['114']['label']?></label>
        <div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="114" id="114"></select>
        </div>
    </div>
    
    <!-- first_name -->
    <div class="form-group">
        <label for="113"><?=$field['113']['label']?></label>
        <input type="text" name="113" id="113" class="form-control">
    </div>
    
    <!-- last_name -->
    <div class="form-group">
        <label for="115"><?=$field['115']['label']?></label>
        <input type="text" name="115" id="115" class="form-control">
    </div>
    
    <!-- phone -->
    <div class="form-group">
        <label for="109"><?=$field['109']['label']?></label>
        <input type="text" name="109" id="109" class="form-control">
    </div>
    
    <!-- email -->
    <div class="form-group">
        <label for="110"><?=$field['110']['label']?></label>
        <input type="text" name="110" id="110" class="form-control">
    </div>
    

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

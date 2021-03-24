
<?=$messages?>

<form <?=$attributes?>>

    <!-- category -->
    <div class="form-group">
        <label for="130"><?=$field['130']['label']?></label>
       	<div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="130" id="130"></select>
        </div>
    </div>
    
    <!-- item -->
    <div class="form-group">
        <label for="131"><?=$field['131']['label']?></label>
        <input type="text" name="131" id="131" class="form-control">
    </div>
    
    <!-- description -->
    <div class="form-group">
        <label for="132"><?=$field['132']['label']?></label>
        <textarea name="132" id="132" class="form-control"></textarea>
    </div>
    
    <!-- price -->
    <div class="form-group mb48">
        <label for="133"><?=$field['133']['label']?></label>
        <input type="text" name="133" id="133" class="form-control">
        <!-- info -->
    	<?=$field['219']['information']?>
    </div>
    
    <!-- seller -->
    <div class="form-group mb48">
        <label for="134"><?=$field['134']['label']?></label>
        <textarea name="134" id="134" class="form-control"></textarea>
    </div>
    
    <!-- photo -->
    <div class="form-group mb48">
        <label for="268"><?=$field['268']['label']?></label>
        <input type="file" name="268" id="268" class="form-control">
        <!-- photo info -->
    	<?=$field['269']['information']?>
    </div>
    
    <?php if ($office_use_only): ?>
        <!-- status -->
        <div><?=$field['136']['label']?></div>
        
        <div class="radio">
            <label>
                <input type="radio" name="136" value="">
                Still Available
            </label>
        </div>
        
        <div class="radio">
            <label>
                <input type="radio" name="136" value="Sold">
                Sold
            </label>
        </div>
    <?php endif ?>
    

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

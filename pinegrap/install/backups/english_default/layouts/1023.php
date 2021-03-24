
<div class="row">
    
	<div class="col-sm-12">
		<?=$messages?>
    </div>

<form <?=$attributes?>>

    <div class="col-sm-12">
    <!-- subject -->
    <div class="form-group">
        <input type="text" name="314" id="314" class="form-control" placeholder="Subject">
    </div>
    </div>
    
    <div class="col-sm-12">
    <!-- details -->
    <div class="form-group">
        <textarea name="315" id="315" placeholder="How can we help you?"></textarea>
    </div>
    </div>
    
    <div class="col-sm-12">
    <!-- information_01 -->
    <?=$field['316']['information']?>
    </div>
    
    <div class="col-sm-4">
    <!-- first_name -->
    <div class="form-group">
        <label for="317"><?=$field['317']['label']?></label>
        <input type="text" name="317" id="317" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <!-- last_name -->
    <div class="form-group">
        <label for="318"><?=$field['318']['label']?></label>
        <input type="text" name="318" id="318" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <!-- e-mail -->
    <div class="form-group">
        <label for="319"><?=$field['319']['label']?></label>
        <input type="email" name="319" id="319" class="form-control">
    </div>
    </div>
    
    <?php if ($office_use_only): ?>
        <div class="col-sm-12">
        <!-- credits -->
        <div class="form-group">
            <label for="320"><?=$field['320']['label']?></label>
            <input type="text" name="320" id="320" class="form-control">
        </div>
        </div>
    <?php endif ?>
    

    <?php if ($watcher_option): ?>
        <div class="col-sm-12">
        <div class="checkbox">
            <label class="check-box">
                <input type="checkbox" name="watcher" value="1" checked>
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span>Notify me when a <?=h(mb_strtolower($comment_label))?> is added.</span>
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

    <div class="col-sm-12">
    <button type="submit" class="btn btn-primary"><?=h($submit_button_label)?></button>
    </div>
            
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
    
</div>

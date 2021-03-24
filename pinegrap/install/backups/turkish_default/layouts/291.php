
<?=$messages?>

<form <?=$attributes?>>

    <!-- salutation -->
    <div class="form-group">
        <label for="119"><?=$field['119']['label']?></label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="119" id="119"></select>
        </div>
    </div>
    
    <!-- first_name -->
    <div class="form-group">
        <label for="120"><?=$field['120']['label']?></label>
        <input type="text" name="120" id="120" class="form-control">
    </div>
    
    <!-- last_name -->
    <div class="form-group">
        <label for="121"><?=$field['121']['label']?></label>
        <input type="text" name="121" id="121" class="form-control">
    </div>
    
    <!-- email -->
    <div class="form-group">
        <label for="122"><?=$field['122']['label']?></label>
        <input type="email" name="122" id="122" class="form-control">
    </div>
    
    <!-- test info -->
    <?=$field['125']['information']?>
    
    <!-- test_game_date -->
    <div class="form-group">
        <label for="123"><?=$field['123']['label']?></label>
        <input type="text" name="123" id="123" class="form-control">
    </div>
    
    <!-- test_location -->
    <div class="form-group">
        <label for="127"><?=$field['127']['label']?></label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="127" id="127"></select>
        </div>
    </div>
    
    <!-- burnt_orange -->
    <div><?=$field['137']['label']?></div>
   
    <div style="margin: 1em 0">
    	<div>
    		<div class="radio-option">
        		<div class="inner"></div>
       				<input type="radio" name="137" value="True">
                    <span>Doğru</span>
        		</div>

    	</div>
    	<div>
            
		<div class="radio-option">
    		<div class="inner"></div>
				<input type="radio" name="137" value="False">
                <span>Yanlış</span>
        	</div>
   		</div>
    </div>
    
    <!-- carolina -->
    <div><?=$field['138']['label']?></div>
    
    <div style="margin: 1em 0">
    	<div>
    		<div class="radio-option">
        		<div class="inner"></div>
       				<input type="radio" name="138" value="True">
                    <span>Doğru</span>
        		</div>

    	</div>
    	<div>
            
		<div class="radio-option">
    		<div class="inner"></div>
				<input type="radio" name="138" value="False">
                <span>Yanlış</span>
        	</div>
   		</div>
    </div>
    
    <!-- pasadena -->
    <div><?=$field['139']['label']?></div>
    
    <div style="margin: 1em 0">
    	<div>
    		<div class="radio-option">
        		<div class="inner"></div>
       				<input type="radio" name="139" value="True">
                    <span>Doğru</span>
        		</div>

    	</div>
    	<div>
            
		<div class="radio-option">
    		<div class="inner"></div>
				<input type="radio" name="139" value="False">
                <span>Yanlış</span>
        	</div>
   		</div>
    </div>
    
    <!-- nfl -->
    <div><?=$field['140']['label']?></div>
    
    <div style="margin: 1em 0">
    	<div>
    		<div class="radio-option">
        		<div class="inner"></div>
       				<input type="radio" name="140" value="True">
                    <span>Doğru</span>
        		</div>

    	</div>
    	<div>
            
		<div class="radio-option">
    		<div class="inner"></div>
				<input type="radio" name="140" value="False">
                <span>Yanlış</span>
        	</div>
   		</div>
    </div>
    
    <!-- test_winner -->
    <div><?=$field['124']['label']?></div>
    
    <div style="margin: 1em 0">
    	<div>
    		<div class="radio-option">
        		<div class="inner"></div>
       				<input type="radio" name="124" value="Texas Longhorns">
                    <span>Texas Longhorns</span>
        		</div>

    	</div>
    	<div>
            
		<div class="radio-option">
    		<div class="inner"></div>
				<input type="radio" name="124" value="USC Trojans">
                <span>USC Trojans</span>
        	</div>
   		</div>
    </div> 
    
    
    <!-- test_mvp -->
    <div class="form-group">
        <label for="129"><?=$field['129']['label']?></label>
        <input type="text" name="129" id="129" class="form-control">
    </div>
    

    <?php if ($watcher_option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Bir <?=h(mb_strtolower($comment_label))?> eklendiğinde bana bildir.
            </label>
        </div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <h5>Spam'ı önlemek için lütfen yanıtlayın:</h5>

        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
        </div>

    <?php endif ?>

    <button type="submit" class="btn btn-primary"><?=h($submit_button_label)?></button>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>

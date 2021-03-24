
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-lg-8">
    <div class="form-group" style="margin-bottom:-15px">
        <label for="email_address">Contact Email</label>
        <input type="email" name="email_address" id="email_address" placeholder="(Can be different from Username Email if preferred)">
    </div>
        
    <div class="form-group">
    	<label class="check-box">
        	<input type="checkbox" name="opt_in" value="1">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
        	<span>Yes, you may send me promotional emails.</span>
    	</label>
    </div>  
    </div>

    <?php if ($contact_groups): ?>

        <div class="col-sm-12">
        <div class="contact_groups form-group" style="display: none">

            <h5>Opt me in or out of the following lists:</h5>

            <?php foreach($contact_groups as $contact_group): ?>

            <div>
    		<label class="check-box">
        		<input type="checkbox" name="contact_group_<?=$contact_group['id']?>" value="1">
            	<span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
        		<span>
                    <?=h($contact_group['name'])?>
                   	<?php if ($contact_group['description']): ?>
                    - <?=h($contact_group['description'])?>
                    <?php endif ?>
                </span>
    		</label>
            </div>

            <?php endforeach ?>

        </div>
        </div>
    <?php endif ?>

    <div class="col-sm-12 mt24">
    	<button type="submit" class="btn btn-primary">Update</button>

    	<?php if ($my_account_url): ?>
        	<a href="<?=h($my_account_url)?>" class="btn btn-secondary">Back</a>
    	<?php endif ?>
    </div>
    
    </div>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
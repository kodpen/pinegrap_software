
<?=$messages?>

<div class="clearfix">
    
<form <?=$attributes?> class="text-left">

	<input type="text" name="email" id="email_address" placeholder="Email or Username*" autocomplete="email" spellcheck="false">
   	<input type="password" name="password" id="password" placeholder="Password*" autocomplete="current-password" spellcheck="false">
    <input type="submit" class="btn" value="Login" />
    
    <?php if (REMEMBER_ME): ?>
	<div class="pull-left">
        
    	<label class="check-box">
        	<input type="checkbox" name="remember_me" value="1">
            <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
        	<span>Remember</span>
    	</label>
        
	</div>
    <?php endif ?>
        
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

<?php if ($forgot_password_url): ?>
    <div class="pull-right">
        <p class="mb0"><a style="text-decoration: underline" href="<?=h($forgot_password_url)?>">Password?</a><p>
	</div>
<?php endif ?>

</div>
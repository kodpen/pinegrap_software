
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-sm-6">    
    <div class="form-group">
        <input type="email" name="email_address" id="email_address" placeholder="Email*">
    </div>
    </div>

    <div class="col-sm-6">
    <div class="form-group">
        <input type="password" name="current_password" id="current_password" placeholder="Current Password*">
    </div>

    <?php if ($strong_password_help): ?>
        <?=$strong_password_help?>
    <?php endif ?>
    </div>
        
    <div class="col-sm-6">
    <div class="form-group">
        <input type="password" name="new_password" id="new_password" placeholder="New Password*">
    </div>
    </div>

    <div class="col-sm-6">
    <div class="form-group">
        <input type="password" name="new_password_verify" id="new_password_verify" placeholder="Confirm New Password*">
    </div>
    </div>

    <?php if (PASSWORD_HINT): ?>
        <div class="col-sm-12">
        <div class="form-group">
            <input type="text" name="password_hint" id="password_hint" placeholder="Enter something to remind you">
        </div>
        </div>
    <?php endif ?>

    <div class="col-sm-12">
    <button type="submit" class="btn btn-primary">Change Password</button>
        
    <?php if ($my_account_url): ?>
        <a href="<?=h($my_account_url)?>" class="btn btn-secondary">Cancel</a>
    <?php endif ?>
    </div>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
    </div>
        
</form>

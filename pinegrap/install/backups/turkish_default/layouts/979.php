
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-sm-6">    
    <div class="form-group">
        <label for="email_address">E-posta*</label>
        <input type="email" name="email_address" id="email_address">
    </div>
    </div>

    <div class="col-sm-6">
    <div class="form-group">
        <label for="current_password">Mevcut &#350;ifre*</label>
        <input type="password" name="current_password" id="current_password">
    </div>

    <?php if ($strong_password_help): ?>
        <?=$strong_password_help?>
    <?php endif ?>
    </div>
        
    <div class="col-sm-6">
    <div class="form-group">
        <label for="new_password">Yeni &#350;ifre*</label>
        <input type="password" name="new_password" id="new_password">
    </div>
    </div>

    <div class="col-sm-6">
    <div class="form-group">
        <label for="new_password_verify">Yeni &#350;ifreyi Onayla*</label>
        <input type="password" name="new_password_verify" id="new_password_verify">
    </div>
    </div>

    <?php if (PASSWORD_HINT): ?>
        <div class="col-sm-12">
        <div class="form-group">
            <label for="password_hint">&#350;ifre ipucu (iste&#287;e ba&#287;l&#305;d&#305;r)</label>
            <input type="text" name="password_hint" id="password_hint" placeholder="Size hat&#305;rlatacak bir &#351;ey girin">
        </div>
        </div>
    <?php endif ?>

    <div class="col-sm-12">
    <button type="submit" class="btn btn-primary">&#350;ifre de&#287;i&#351;tir</button>
        
    <?php if ($my_account_url): ?>
        <a href="<?=h($my_account_url)?>" class="btn btn-secondary">&#304;ptal</a>
    <?php endif ?>
    </div>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
    </div>
        
</form>

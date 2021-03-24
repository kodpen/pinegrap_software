
<?=$messages?>

<form <?=$attributes?>>

    <?php if ($strong_password_help): ?>
        <?=$strong_password_help?>
    <?php endif ?>

    <input type="password" name="new_password" id="new_password" placeholder="Yeni Şifre*">
    <input type="password" name="new_password_verify" id="new_password_verify" placeholder="Yeni Şifreyi Onayla*">

    <?php if (PASSWORD_HINT): ?>
    	<input type="text" name="password_hint" id="password_hint" placeholder="Şifre İpucu (isteğe bağlı)">
    <?php endif ?>

    <button type="submit" class="btn btn-primary">Şifreyi Ayarla</button>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

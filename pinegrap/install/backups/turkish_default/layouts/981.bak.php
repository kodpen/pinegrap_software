
<?=$messages?>

<form <?=$attributes?>>

    <?php if (!$screen): ?>

      	<input type="email" name="email" id="email" placeholder="Kullanıcı E-Posta Adresi">
      	<button type="submit" class="btn btn-primary">E-posta Geçici Şifre</button>
    
    <?php elseif ($screen == 'password_hint'): ?>

        <p>Şifre İpucunuz: <strong><?=h($password_hint)?></strong></p>

        <p>Şifrenizi şimdi hatırlıyor musunuz?</p>

        <a href="<?=h($send_to)?>" class="btn btn-primary">Evet, Hatırlıyorum</a>

        <button type="submit" class="btn btn-primary">Hayır, Unuttum</button>

    <?php endif ?>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

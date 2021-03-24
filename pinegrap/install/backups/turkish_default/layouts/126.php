<div class="row" style="height: auto">
      <?=$messages?>
      <div class="tabbed-content button-tabs">
         <ul class="tabs">
            <li class="active">
               <div class="tab-title">
                  <span>
                     <h4 class="uppercase mb0">Giriş</h4>
                  </span>
               </div>
               <div class="tab-content">
                  <div class="col-sm-6 col-sm-offset-3">
                     <form <?=$login_attributes?>>
                        <input type="text" name="email" id="login_email_address" class="form-control" placeholder="E-posta*" autocomplete="email" spellcheck="false">
                        <input type="password" name="password" id="login_password" class="form-control" placeholder="Şifre*" autocomplete="current-password" spellcheck="false">
                        <button type="submit" class="btn">Giriş Yap</button>
                        <?php if (REMEMBER_ME): ?>
                        <div class="pull-left">
                           <label class="check-box">
                           <input type="checkbox" name="login_remember_me" value="1">
                           <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                           <span>Beni Hatırla</span>
                           </label>
                        </div>
                        <?php endif ?>
                        <!-- Required hidden fields (do not remove) -->
                        <?=$login_system?>
                     </form>
                     <?php if ($forgot_password_url): ?>
                     <div class="pull-right">
                        <p class="mb0"><a style="text-decoration: underline" href="<?=h($forgot_password_url)?>">Şifremi Unuttum</a>
                        <p>
                     </div>
                     <?php endif ?>    
                  </div>
               </div>
            </li>
            <li>
               <div class="tab-title">
                  <span>
                     <h4 class="uppercase mb0">Kayıt</h4>
                  </span>
               </div>
               <div class="tab-content">
                  <form <?=$register_attributes?>>
                     <div class="col-sm-6">
                        <input type="text" name="first_name" id="first_name" placeholder="İsim*" autocomplete="given-name" spellcheck="false">
                        <input type="text" name="last_name" id="last_name" placeholder="Soyisim*" autocomplete="family-name" spellcheck="false">
                        <input type="text" name="username" id="username" placeholder="Kullanıcı Adı*" autocomplete="username" spellcheck="false">
                        <input type="email" name="email" id="register_email_address" placeholder="E-posta*" autocomplete="email" spellcheck="false">
                        <input type="email" name="email_verify" id="email_address_verify" placeholder="E-postayı Onayla*" autocomplete="email" spellcheck="false">
                        <?php if ($strong_password_help): ?>
                        <?=$strong_password_help?>
                        <?php endif ?>
                     </div>
                     <div class="col-sm-6">
                        <input type="password" name="password" id="register_password" class="form-control" placeholder="Şifre*" autocomplete="new-password" spellcheck="false">
                        <input type="password" name="password_verify" id="password_verify" class="form-control" placeholder="Şifreyi Onayla*" autocomplete="new-password" spellcheck="false">
                        <?php if (PASSWORD_HINT): ?>
                        <input type="text" name="password_hint" id="password_hint" placeholder="Şifre İpucu">
                        <?php endif ?>
                        <?php if ($captcha_question): ?>
                        <input type="text" name="captcha_submitted_answer" id="captcha_submitted_answer" placeholder="<?=h($captcha_question)?>*">
                        <?php endif ?>
                        <button type="submit" class="btn">Kayıt Ol</button>
                        <?php if (REMEMBER_ME): ?>
                            <div class="pull-left">
                               <label class="check-box">
                               <input type="checkbox" name="register_remember_me" value="1">
                               <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                               <span>Beni Hatırla</span>
                               </label>
                            </div>
                        <?php endif ?>
                         <div class="clearfix"></div>
                         <div class="pull-left">
                             <label class="check-box">
                                 <input type="checkbox" name="opt_in" value="1">
                                 <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                                 <span><?=h($opt_in_label)?></span>
                             </label>
                         </div>
                        <div class="clearfix"></div>
                     </div>
                     <!-- Required hidden fields (do not remove) -->
                     <?=$register_system?>
                  </form>
                  <?php if ($guest): ?>
                  <div class="col-sm-4"></div>
                  <div class="col-sm-4">
                     <form <?=$guest_attributes?> class="text-left">
                        <input type="submit" style="background: none" value="Continue as Guest" />
                        <!-- Required hidden fields (do not remove) -->
                        <?=$guest_system?>
                     </form>
                  </div>
                  <div class="col-sm-4"></div>
                  <?php endif ?>
               </div>
            </li>
         </ul>
      </div>
</div>
<div class="row" style="height: auto">
    <?=$messages?>
      <div class="tabbed-content button-tabs">
         <ul class="tabs">
            <li class="active">
               <div class="tab-title">
                  <span>
                     <h4 class="uppercase mb0">Login</h4>
                  </span>
               </div>
               <div class="tab-content">
                  <div class="col-sm-6 col-sm-offset-3">
                     <form <?=$attributes?>>
                        <input type="text" name="u" id="login_email_address" class="form-control" placeholder="Email*">
                        <input type="password" name="p" id="login_password" class="form-control" placeholder="Password*">
                        <button type="submit" class="btn">Login</button>
                        <?php if (REMEMBER_ME): ?>
                        <div class="pull-left">
                           <label class="check-box">
                           <input type="checkbox" name="login_remember_me" value="1">
                           <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                           <span>Remember</span>
                           </label>
                        </div>
                        <?php endif ?>
                        <!-- Required hidden fields (do not remove) -->
                        <?=$login_system?>
                     </form>
                     <?php if ($forgot_password_url): ?>
                     <div class="pull-right">
                        <p class="mb0"><a style="text-decoration: underline" href="<?=h($forgot_password_url)?>">Password?</a>
                        <p>
                     </div>
                     <?php endif ?>    
                  </div>
               </div>
            </li>
            <li>
               <div class="tab-title">
                  <span>
                     <h4 class="uppercase mb0">Register</h4>
                  </span>
               </div>
               <div class="tab-content">
                  <form <?=$attributes?>>
                     <div class="col-sm-6">
                         
                        <div role="alert" class="alert alert-success mb16">
                			<span class="mb8" style="color: #60b963;">Contact us if you don't know your <?=h(MEMBER_ID_LABEL)?>.</span>
                			<input style="margin:16px 0" type="text" name="member_id" id="member_id" placeholder="<?=h(MEMBER_ID_LABEL)?>*">
            			</div>
                         
                        <input type="text" name="first_name" id="first_name" placeholder="First Name*">
						<input type="text" name="last_name" id="last_name" placeholder="Last Name*">
            			<input type="email" name="email_address" id="register_email_address" placeholder="Email*">
            			<input type="email" name="email_address_verify" id="email_address_verify" placeholder="Confirm Email*">
                        
                        <?php if ($strong_password_help): ?>
                        <?=$strong_password_help?>
                        <?php endif ?>
                     </div>
                     <div class="col-sm-6">
                         
            			<input type="text" name="username" id="username" placeholder="Username*">
            			<input type="password" name="password" id="register_password" class="form-control" placeholder="Password*">
						<input type="password" name="password_verify" id="password_verify" class="form-control" placeholder="Confirm Password*">
                        <?php if (PASSWORD_HINT): ?>
                        <input type="text" name="password_hint" id="password_hint" placeholder="Password Hint">
                        <?php endif ?>
                        <?php if ($captcha_question): ?>
                        <input type="text" name="captcha_submitted_answer" id="captcha_submitted_answer" placeholder="<?=h($captcha_question)?>*">
                        <?php endif ?>
                        <button type="submit" class="btn">Register as Member</button>
                        <?php if (REMEMBER_ME): ?>
                            <div class="pull-left">
                               <label class="check-box">
                               <input type="checkbox" name="register_remember_me" value="1">
                               <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                               <span>Remember</span>
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
               </div>
            </li>
         </ul>
      </div>
</div>
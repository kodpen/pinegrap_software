<?=$messages?>
<div class="row">
   <div class="col-sm-9">
      <div class="col-sm-12 p0">
         <i class="ti-user pull-left" style="font-size:64px;margin-right:16px;"></i>
         <h3 class="mt0 mb0"><?=h(USER_USERNAME)?></h3>
         <h5 class="mb24"><?=h(USER_EMAIL_ADDRESS)?></h5>
      </div>
      <div class="col-sm-12 p0">
         <div class="col-sm-6 p0">
            <?php if (
               $first_name or $last_name or $title or $company or $business_address_1
               or $business_address_2 or $business_city or $business_state or $business_zip_code
               or $business_country
               ):?>
            <?php if ($first_name or $last_name): ?>
            <strong>İsim Soyisim:</strong>&nbsp;<?=h($first_name)?> <?=h($last_name)?><br>
            <?php endif ?>
            <?php if ($title): ?>
            <?=h($title)?><br>
            <?php endif ?>
            <?php if ($company): ?>
            <strong>Şirket:</strong>&nbsp;<?=h($company)?><br>
            <?php endif ?>
            <?php if ($business_address_1): ?>
            <strong>İş Adresi:</strong>&nbsp;<?=h($business_address_1)?><br>
            <?php endif ?>
            <?php if ($business_address_2): ?>
            <?=h($business_address_2)?><br>
            <?php endif ?>
            <?php if ($business_city): ?>
            <?=h($business_city)?><?php if ($business_state or $business_zip_code): ?>, <?php endif ?>
            <?php endif ?>
            <?php if ($business_state): ?>
            <?=h($business_state)?>
            <?php endif ?>
            <?php if ($business_zip_code): ?>
            <?=h($business_zip_code)?>
            <?php endif ?>
            <?php if (
               $business_city
               or $business_state
               or $business_zip_code
               ):?>
            <br>
            <?php endif ?>
            <?php if ($business_country): ?>
            <?=h($business_country)?><br>
            <?php endif ?>
            <?php endif ?>
            <?php if ($business_phone): ?>
            <strong>İş Telefonu:</strong>&nbsp;<?=h($business_phone)?><br>
            <?php endif ?>
            <?php if ($mobile_phone): ?>
            <strong>Cep Telefonu:</strong>&nbsp;<?=h($mobile_phone)?><br>
            <?php endif ?>
            <?php if ($home_phone): ?>
            <strong>Ev Telefonu:</strong>&nbsp;<?=h($home_phone)?><br>
            <?php endif ?>
            <?php if ($business_fax): ?>
            <strong>Faks:</strong>&nbsp;<?=h($business_fax)?><br>
            <?php endif ?>
            <span class="mb16"></span>
         </div>
         <div class="col-sm-6 p0">
            <?php if (USER_MEMBER_ID): ?>
            <p class="mb16"><?=h(MEMBER_ID_LABEL)?>: <?=h(USER_MEMBER_ID)?><br>
               <strong>Son Kullanma Tarihi:</strong> 
               <?php if (USER_EXPIRATION_DATE == '0000-00-00'): ?>
               <i>Ayarlanmamış</i>
               <?php else: ?>
               <?=get_absolute_time(array(
                  'timestamp' => strtotime(USER_EXPIRATION_DATE),
                  'type' => 'date'))?>
               <?php endif ?>
            </p>
            <?php endif ?>
            <?php if ($reward_points): ?>
            <p class="mb16"><strong>Ödül Puanları</strong><br><?=h(number_format($reward_points))?></p>
            <?php endif ?>
            <?php if ($timezone): ?>
            <strong>Zaman Dilimi:</strong><br><?=h($timezone)?><span class="mb16"></span><br>
            <?php endif ?>
         </div>
      </div>
   </div>
   <div class="col-sm-3 mt32">
       <div class="widget">
           <h6 class="title">HESAP LİNKLERİ</h6>
           <hr>
           <ul class="link-list">
               <li>
               		<?php if ($start_page_url): ?>
      	   			<a href="<?=h($start_page_url)?>">Başlangıç Sayfam</a>
      				<?php endif ?>
               </li>
               <li>
                    <?php if ($my_account_profile_url): ?>
      				<a href="<?=h($my_account_profile_url)?>">Hesap Profili</a>
      				<?php endif ?>
               </li>
               <li>
                    <?php if ($email_preferences_url): ?>
      				<a href="<?=h($email_preferences_url)?>">E-posta Tercihleri</a>
      				<?php endif ?>
               </li>
               <li>
               		<?php if ($change_password_url): ?>
      				<a href="<?=h($change_password_url)?>">Parola Değiştirme</a>
      				<?php endif ?>
               </li>
               <li>
               		<?php if ($logout_url): ?>
      				<a href="<?=h($logout_url)?>">Kullanıcı Çıkışı Yap</a>
      				<?php endif ?>        
               </li>
           </ul>
           
       		<div class="widget">
           		<h6 class="title">DİĞER LİNKLER</h6>
           		<hr>
				<ul class="link-list">
                    <li><a href="{path}my-conversations">Sohbetlerim</a></li>
                    <li><a href="{path}my-support-tickets">Destek Biletlerim</a></li>
                    <li><a href="{path}my-services-projects">Hizmet Projelerim</a></li>
                    
				<?php // If user has view access to the "Customer" Private Folder (folder id=284) 
    				  // then show links for paying customers
    				if (check_view_access(284)):?>
                    <li><a href="{path}survey">Müşteri Anketi</a></li>
                <?php endif ?>
                    
				<?php // if user has access to "Software Product" or "eBook Product" Folders
    				  // then show links to the my content page
    				if (check_view_access(134) or check_view_access(135)):?>
                    <li><a href="{path}my-account-content">İçeriğim</a></li>
                <?php endif ?>

                <?php // if user has access to "Pay for Exam" Folder
    				  // then show link to the exam page
    				if (check_view_access(104)):?>
                    <li><a href="{path}exam">Sınav</a></li>
                <?php endif ?>
                    
				</ul>
            </div>
           
       </div>
   </div>
</div>
<?php if ($affiliate): ?>
<div class="row">
   <div class="col-sm-12">
      <h4 class="mt48">Ortaklık</h4>
      <div class="col-sm-6 p0">
         <p class="mb8">Ortaklık Adı: <?=h($affiliate_name)?></p>
         <p class="mb8">Ortaklık Kodu: <?=h($affiliate_code)?></p>
         <p class="mb8">Ortaklık Linki: 
            <a href="<?=h($affiliate_url)?>" target="_blank"><?=h($affiliate_url)?></a>
         </p>
         <p class="mb8">Komisyon Oranı: <?=h($affiliate_commission_rate)?>%</p>
      </div>
      <div class="col-sm-6 p0">
         <p class="mb8">Bekleyen Toplam:
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_pending_total, 2)?>
         </p>
         <p class="mb8">Ödenecek Toplam: 
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_payable_total, 2)?>
         </p>
         <p class="mb8">Ödenen Toplam:
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_paid_total, 2)?>
         </p>
      </div>
   </div>
   <?php if ($commissions): ?>
   <h4>Komisyonlar</h4>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Referans Kodu</th>
            <th>Tarih &amp; Zaman</th>
            <th>Durum</th>
            <th class="text-right">Miktar</th>
         </tr>
         <?php foreach($commissions as $commission): ?>
         <tr>
            <td><?=h($commission['reference_code'])?></td>
            <td>
               <?=get_relative_time(array(
                  'timestamp' => $commission['created_timestamp']))?>
            </td>
            <td><?=h($commission['status_label'])?></td>
            <td class="text-right">
               <?=BASE_CURRENCY_SYMBOL . number_format($commission['amount'], 2)?>
            </td>
         </tr>
         <?php endforeach ?>
      </table>
   </div>
   <?php endif ?>
</div>
<?php endif ?>

<?php if ($complete_orders or $incomplete_orders): ?>
<div class="row">
<div class="col-sm-12">
   <h4 class="mt48">Sipariş Geçmişi</h4>
   <?php if ($complete_orders): ?>
   <h5>Tamamlanan Siparişler</h5>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Sipariş Numarası</th>
            <th>Tarih &amp; Zaman</th>
            <th class="text-right">Toplam</th>
            <th></th>
         </tr>
         <?php foreach($complete_orders as $order): ?>
         <tr>
            <td><?=h($order['order_number'])?></td>
            <td>
               <?=get_relative_time(array(
                  'timestamp' => $order['order_date']))?>
            </td>
            <td class="text-right">
               <?=$order['total_info']?>
            </td>
            <td class="text-right button-col">
               <?php if ($order['view_url']): ?>
               <a href="<?=h($order['view_url'])?>" class="btn btn-secondary btn-xs">
               Görüntüle
               </a>
               <?php endif ?>
               <a href="<?=h($order['reorder_url'])?>" class="btn btn-secondary btn-xs">
               Siparişi Yinele
               </a>
            </td>
         </tr>
         <?php endforeach ?>
      </table>
   </div>
   <?php endif ?>
   <?php if ($incomplete_orders): ?>
   <h5>Tamamlanmamış Siparişler</h5>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Referans Kodu</th>
            <th>Tarih &amp; Zaman</th>
            <th></th>
         </tr>
         <?php foreach($incomplete_orders as $order): ?>
         <tr>
            <td><?=h($order['reference_code'])?></td>
            <td>
               <?=get_relative_time(array(
                  'timestamp' => $order['order_date']))?>
            </td>
            <td class="text-right button-col">
               <?php if ($order['view_url']): ?>
               <a href="<?=h($order['view_url'])?>" class="btn btn-secondary btn-xs">
               Görüntüle
               </a>
               <?php endif ?>
               <?php if ($order['active']): ?>
               <?php if ($order['order_url']): ?>
               <a href="<?=h($order['order_url'])?>" class="btn btn-primary btn-xs">
               Tamamla
               </a>
               <?php else: ?>
               <a href="#" class="btn btn-primary btn-xs disabled">
               Aktif
               </a>
               <?php endif ?>
               <?php else: ?>
               <a href="<?=h($order['retrieve_url'])?>" class="btn btn-secondary btn-xs">
               Kurtar
               </a>
               <?php endif ?>
               <a href="<?=h($order['delete_url'])?>" class="btn btn-secondary btn-xs delete_button" onclick="return confirm('Sipariş silinecek.')">
               Sil
               </a>
            </td>
         </tr>
         <?php endforeach ?>
      </table>
   </div>
   <?php endif ?>
</div>
</div>
<?php endif ?>
    
<?php if (($address_book) and ($recipients)): ?>
<div class="row">
<div class="col-sm-12">
<h4 class="mt64">Nakliye Adresleri</h4>
<div class="table-responsive">
   <table class="table table-striped">
      <tr>
         <th>Alıcı İsmi</th>
         <th>Tam İsim</th>
         <th>Teslimat Adresi</th>
         <th></th>
      </tr>
      <?php foreach($recipients as $recipient): ?>
      <tr>
         <td><?=h($recipient['ship_to_name'])?></td>
         <td>
            <?=h($recipient['salutation'])?>
            <?=h($recipient['first_name'])?>
            <?=h($recipient['last_name'])?>
         </td>
         <td>
            <?php if ($recipient['company']): ?>
            <?=h($recipient['company'])?><br>
            <?php endif ?>
            <?=h($recipient['address_1'])?><br>
            <?php if ($recipient['address_2']): ?>
            <?=h($recipient['address_2'])?><br>
            <?php endif ?>
            <?=h($recipient['city'])?>,
            <?=h($recipient['state'])?>
            <?=h($recipient['zip_code'])?><br>
            <?=h($recipient['country'])?><br>
         </td>
         <td class="text-right">
            <?php if ($recipient['update_url']): ?>
            <a href="<?=h($recipient['update_url'])?>" class="btn btn-secondary btn-xs">
            Güncelle
            </a>
            <?php endif ?>
            <a href="<?=h($recipient['remove_url'])?>" class="btn btn-secondary btn-xs delete_button" onclick="return confirm('<?=h(escape_javascript($recipient['ship_to_name']))?> silinecek.')">
            Sil
            </a>
         </td>
      </tr>
      <?php endforeach ?>
   </table>
</div>
<?php if ($update_address_book_url): ?>
<a href="<?=h($update_address_book_url)?>" class="btn btn-secondary">
Yeni Nakliye Adresi
</a>
<?php endif ?>
</div>
</div>
<?php endif ?>
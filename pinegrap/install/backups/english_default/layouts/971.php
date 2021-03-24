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
            <?=h($first_name)?> <?=h($last_name)?><br>
            <?php endif ?>
            <?php if ($title): ?>
            <?=h($title)?><br>
            <?php endif ?>
            <?php if ($company): ?>
            <?=h($company)?><br>
            <?php endif ?>
            <?php if ($business_address_1): ?>
            <?=h($business_address_1)?><br>
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
            Main:&nbsp;<?=h($business_phone)?><br>
            <?php endif ?>
            <?php if ($mobile_phone): ?>
            Mobile:&nbsp;<?=h($mobile_phone)?><br>
            <?php endif ?>
            <?php if ($home_phone): ?>
            Home:&nbsp;<?=h($home_phone)?><br>
            <?php endif ?>
            <?php if ($business_fax): ?>
            Fax:&nbsp;<?=h($business_fax)?><br>
            <?php endif ?>
            <span class="mb16"></span>
         </div>
         <div class="col-sm-6 p0">
            <?php if (USER_MEMBER_ID): ?>
            <p class="mb16"><?=h(MEMBER_ID_LABEL)?>: <?=h(USER_MEMBER_ID)?><br>
               Expiration Date: 
               <?php if (USER_EXPIRATION_DATE == '0000-00-00'): ?>
               <i>Not Set</i>
               <?php else: ?>
               <?=get_absolute_time(array(
                  'timestamp' => strtotime(USER_EXPIRATION_DATE),
                  'type' => 'date'))?>
               <?php endif ?>
            </p>
            <?php endif ?>
            <?php if ($reward_points): ?>
            <p class="mb16">Reward Points<br><?=h(number_format($reward_points))?></p>
            <?php endif ?>
            <?php if ($timezone): ?>
            Timezone:<br><?=h($timezone)?><span class="mb16"></span><br>
            <?php endif ?>
         </div>
      </div>
   </div>
   <div class="col-sm-3">
       <div class="widget">
           <h6 class="title">My Account Links</h6>
           <hr>
           <ul class="link-list">
               <li>
               		<?php if ($start_page_url): ?>
      	   			<a href="<?=h($start_page_url)?>">My Start Page</a>
      				<?php endif ?>
               </li>
               <li>
                    <?php if ($my_account_profile_url): ?>
      				<a href="<?=h($my_account_profile_url)?>">Account Profile</a>
      				<?php endif ?>
               </li>
               <li>
                    <?php if ($email_preferences_url): ?>
      				<a href="<?=h($email_preferences_url)?>">Email Preferences</a>
      				<?php endif ?>
               </li>
               <li>
               		<?php if ($change_password_url): ?>
      				<a href="<?=h($change_password_url)?>">Change Password</a>
      				<?php endif ?>
               </li>
               <li>
               		<?php if ($logout_url): ?>
      				<a href="<?=h($logout_url)?>">Logout</a>
      				<?php endif ?>        
               </li>
           </ul>
           
       		<div class="widget">
           		<h6 class="title">More Links</h6>
           		<hr>
				<ul class="link-list">
                    <li><a href="{path}my-conversations">My Conversations</a></li>
                    <li><a href="{path}my-support-tickets">My Support Tickets</a></li>
                    <li><a href="{path}my-services-projects">My Services Projects</a></li>
                    
				<?php // If user has view access to the "Customer" Private Folder (folder id=284) 
    				  // then show links for paying customers
    				if (check_view_access(284)):?>
                    <li><a href="{path}survey">Customer Survey</a></li>
                <?php endif ?>
                    
				<?php // if user has access to "Software Product" or "eBook Product" Folders
    				  // then show links to the my content page
    				if (check_view_access(134) or check_view_access(135)):?>
                    <li><a href="{path}my-account-content">My Content</a></li>
                <?php endif ?>

                <?php // if user has access to "Pay for Exam" Folder
    				  // then show link to the exam page
    				if (check_view_access(104)):?>
                    <li><a href="{path}exam">Exam</a></li>
                <?php endif ?>
                    
				</ul>
            </div>
           
       </div>
   </div>
</div>
<?php if ($affiliate): ?>
<div class="row">
   <div class="col-sm-12">
      <h4 class="mt48">Affiliate</h4>
      <div class="col-sm-6 p0">
         <p class="mb8">Affiliate Name: <?=h($affiliate_name)?></p>
         <p class="mb8">Affiliate Code: <?=h($affiliate_code)?></p>
         <p class="mb8">Affiliate Link: 
            <a href="<?=h($affiliate_url)?>" target="_blank"><?=h($affiliate_url)?></a>
         </p>
         <p class="mb8">Commission Rate: <?=h($affiliate_commission_rate)?>%</p>
      </div>
      <div class="col-sm-6 p0">
         <p class="mb8">Pending Total:
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_pending_total, 2)?>
         </p>
         <p class="mb8">Payable Total: 
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_payable_total, 2)?>
         </p>
         <p class="mb8">Paid Total:
            <?=BASE_CURRENCY_SYMBOL . number_format($affiliate_paid_total, 2)?>
         </p>
      </div>
   </div>
   <?php if ($commissions): ?>
   <h4>Commissions</h4>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Reference Code</th>
            <th>Date &amp; Time</th>
            <th>Status</th>
            <th class="text-right">Amount</th>
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
   <h4 class="mt48">Order History</h4>
   <?php if ($complete_orders): ?>
   <h5>Complete Orders</h5>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Order Number</th>
            <th>Date &amp; Time</th>
            <th class="text-right">Total</th>
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
               View
               </a>
               <?php endif ?>
               <a href="<?=h($order['reorder_url'])?>" class="btn btn-secondary btn-xs">
               Reorder
               </a>
            </td>
         </tr>
         <?php endforeach ?>
      </table>
   </div>
   <?php endif ?>
   <?php if ($incomplete_orders): ?>
   <h5>Incomplete Orders</h5>
   <div class="table-responsive">
      <table class="table table-striped">
         <tr>
            <th>Reference Code</th>
            <th>Date &amp; Time</th>
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
               View
               </a>
               <?php endif ?>
               <?php if ($order['active']): ?>
               <?php if ($order['order_url']): ?>
               <a href="<?=h($order['order_url'])?>" class="btn btn-primary btn-xs">
               Order
               </a>
               <?php else: ?>
               <a href="#" class="btn btn-primary btn-xs disabled">
               Active
               </a>
               <?php endif ?>
               <?php else: ?>
               <a href="<?=h($order['retrieve_url'])?>" class="btn btn-secondary btn-xs">
               Retrieve
               </a>
               <?php endif ?>
               <a href="<?=h($order['delete_url'])?>" class="btn btn-secondary btn-xs delete_button" onclick="return confirm('The order will be deleted.')">
               Delete
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
<h4 class="mt64">Shipping Addresses</h4>
<div class="table-responsive">
   <table class="table table-striped">
      <tr>
         <th>Ship to Name</th>
         <th>Full Name</th>
         <th>Delivery Address</th>
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
            Update
            </a>
            <?php endif ?>
            <a href="<?=h($recipient['remove_url'])?>" class="btn btn-secondary btn-xs delete_button" onclick="return confirm('<?=h(escape_javascript($recipient['ship_to_name']))?> will be deleted.')">
            Delete
            </a>
         </td>
      </tr>
      <?php endforeach ?>
   </table>
</div>
<?php if ($update_address_book_url): ?>
<a href="<?=h($update_address_book_url)?>" class="btn btn-secondary">
New Shipping Address
</a>
<?php endif ?>
</div>
</div>
<?php endif ?>
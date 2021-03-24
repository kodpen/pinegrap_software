<?php

/**
 *
 * liveSite - Enterprise Website Platform
 * 
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

function get_help_url() {
	$software_language_code = '';
	if(language_ruler() == 'tr'){
		$software_language_code = 'tr';
	}else{
		$software_language_code = 'en';
	}

    // Get script name for current screen.
    $php_self_parts = explode('/', $_SERVER['PHP_SELF']);
    $script_name = $php_self_parts[count($php_self_parts) - 1];
    
    // Get screen by removing extension from script name.
    $screen = mb_substr($script_name, 0, mb_strrpos($script_name, '.'));

    $url = '';

    switch ($screen) {

        case 'welcome': $url = 'pinegrap-welcome-'.$software_language_code.'#welcome'; break;

        case 'view_log': $url = 'pinegrap-settings-'.$software_language_code.'#site-log'; break;
		case 'settings': $url = 'pinegrap-settings-'.$software_language_code; break;
		case 'smtp_settings': $url = 'pinegrap-settings-'.$software_language_code . '#smtp-settings'; break;
		case 'mailchimp_settings': $url = 'pinegrap-settings-'.$software_language_code . '#mailchimp-settings'; break;
		case 'backups':  $url = 'pinegrap-settings-'.$software_language_code . '#backups'; break;
       

        case 'view_folders': $url = 'pinegrap-folders-'.$software_language_code; break;

        case 'add_folder':
        case 'edit_folder':
            $url = 'pinegrap-folders-'.$software_language_code.'#create-edit-folder';
            break;

        case 'view_pages':

            switch($_GET['filter']) {

                case 'all_my_pages': $url = 'pinegrap-pages-' . $software_language_code; break;
                case 'all_my_archived_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#archived-pages'; break;
                case 'my_home_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#home-pages'; break;
                case 'my_searchable_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#searchable-pages'; break;
                case 'my_unsearchable_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#unsearchable-pages'; break;
                case 'my_sitemap_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#site-map-pages'; break;
                case 'my_rss_enabled_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#rss-enabled-pages'; break;
                case 'my_standard_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#standard-pages'; break;
                case 'my_photo_gallery_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#photo-gallery-pages'; break;
                case 'my_calendar_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#calendar-pages'; break;
                case 'my_custom_form_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#custom-form-pages'; break;
                case 'my_form_view_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#form-view-pages'; break;
                case 'my_affiliate_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#affiliate-pages'; break;
                case 'my_commerce_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#commerce-pages'; break;
                case 'my_account_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#account-pages'; break;
                case 'my_login_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#login-pages'; break;
                case 'my_miscellaneous_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#miscellaneous-pages'; break;
                case 'my_public_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#public-access-pages'; break;
                case 'my_guest_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#guest-access-pages'; break;
                case 'my_registration_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#registration-access-pages'; break;
                case 'my_membership_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#membership-access-pages'; break;
                case 'my_private_pages': $url = 'pinegrap-pages-cont-' . $software_language_code .'#private-access-pages'; break;

				
                default: $url = 'pinegrap-pages-' . $software_language_code; break;

            }

            break;

        case 'toolbar': $url = 'pinegrap-pages-'.$software_language_code.'#editing-page-content'; break;
		case 'view_comments':
        case 'edit_comment': $url = 'pinegrap-pages-'.$software_language_code.'#edit-comment'; break;

        case 'add_page':
        case 'edit_page':
		
            $url = 'pinegrap-pages-'.$software_language_code.'#edit-page-properties';
            break;

        case 'view_fields': $url = 'pinegrap-pages-'.$software_language_code.'#edit-custom-form'; break;

        case 'add_field':
        case 'edit_field':
            $url = 'pinegrap-pages-'.$software_language_code.'#edit-custom-form-field';
            break;

        case 'edit_form_list_view': $url = 'pinegrap-pages-'.$software_language_code.'#edit-form-list-view'; break;
        case 'edit_form_item_view': $url = 'pinegrap-pages-'.$software_language_code.'#edit-form-item-view'; break;
        case 'view_short_links': $url = 'pinegrap-pages-cont-' . $software_language_code .'#short-links'; break;

        case 'add_short_link':
        case 'edit_short_link':
		
            $url = 'pinegrap-pages-cont-' . $software_language_code .'#create-edit-short-link';
            break;

        case 'view_auto_dialogs': $url = 'pinegrap-pages-cont-' . $software_language_code .'#auto-dialogs'; break;

        case 'add_auto_dialog':
        case 'edit_auto_dialog':
            $url = 'pinegrap-pages-cont-' . $software_language_code .'#create-edit-auto-dialog';
            break;

        case 'view_files':

            switch($_GET['filter']) {

                case 'all_my_files': $url = 'pinegrap-files-' . $software_language_code; break;
                case 'all_my_archived_files': $url = 'pinegrap-files-' . $software_language_code . '#archived-files'; break;
                case 'my_documents': $url = 'pinegrap-files-' . $software_language_code . '#documents'; break;
                case 'my_photos': $url = 'pinegrap-files-' . $software_language_code . '#photos'; break;
                case 'my_media': $url = 'pinegrap-files-' . $software_language_code . '#media'; break;
                case 'my_attachments': $url = 'pinegrap-files-' . $software_language_code . '#attachments'; break;
                case 'my_public_files': $url = 'pinegrap-files-' . $software_language_code . '#public-access-files'; break;
                case 'my_guest_files': $url = 'pinegrap-files-' . $software_language_code . '#guest-access-files'; break;
                case 'my_registration_files': $url = 'pinegrap-files-' . $software_language_code . '#registration-access-files'; break;
                case 'my_member_files': $url = 'pinegrap-files-' . $software_language_code . '#membership-access-files'; break;
                case 'my_private_files': $url = 'pinegrap-files-' . $software_language_code . '#private-access-files'; break;

                default: $url = 'pinegrap-files-' . $software_language_code; break;
                
            }

            break;

        case 'add_file': $url = 'pinegrap-files-' . $software_language_code . '#upload-files'; break;
        case 'edit_file': $url = 'pinegrap-files-' . $software_language_code . '#edit-file'; break;

        case 'view_calendars': $url = 'pinegrap-calendars-' . $software_language_code; break;
        case 'calendars': $url = 'pinegrap-calendars-' . $software_language_code . '#create-edit-calendar'; break;

        case 'add_calendar':
        case 'edit_calendar':
            $url = 'pinegrap-calendars-' . $software_language_code . '#create-edit-calendar-properties';
            break;

        case 'add_calendar_event':
        case 'edit_calendar_event':
            $url = 'pinegrap-calendars-' . $software_language_code . '#create-edit-calendar-event';
            break;

        case 'view_calendar_event_locations': $url = 'pinegrap-calendars-' . $software_language_code . '#event-locations'; break;

        case 'add_calendar_event_location':
        case 'edit_calendar_event_location':
            $url = 'pinegrap-calendars-' . $software_language_code . '#create-edit-calendar-event-location';
            break;

        case 'view_submitted_forms': $url = 'pinegrap-forms-' . $software_language_code; break;
        case 'edit_submitted_form': $url = 'pinegrap-forms-' . $software_language_code . '#edit-submitted-form'; break;
        case 'import_submitted_forms': $url = 'pinegrap-forms-' . $software_language_code . '#import-submitted-forms'; break;

        case 'view_visitor_reports': $url = 'pinegrap-visitors-' . $software_language_code; break;
        case 'view_visitor_report': $url = 'pinegrap-visitors-' . $software_language_code . '#create-edit-visitor-report'; break;
        case 'view_visitor': $url = 'pinegrap-visitors-' . $software_language_code . '#visit-details'; break;

        case 'view_contacts':

            switch($_GET['filter']) {

                case 'all_my_contacts': $url = 'pinegrap-contacts-' . $software_language_code; break;
                case 'my_subscribers': $url = 'pinegrap-contacts-' . $software_language_code . '#subscribers'; break;
                case 'my_affiliates': $url = 'pinegrap-contacts-' . $software_language_code . '#affiliates'; break;
                case 'my_customers': $url = 'pinegrap-contacts-' . $software_language_code . '#customers'; break;
                case 'my_members': $url = 'pinegrap-contacts-' . $software_language_code . '#members'; break;
                case 'my_active_members': $url = 'pinegrap-contacts-' . $software_language_code . '#active-members'; break;
                case 'my_expired_members': $url = 'pinegrap-contacts-' . $software_language_code . '#expired-members'; break;
                case 'my_unregistered_members': $url = 'pinegrap-contacts-' . $software_language_code . '#unregistered-members'; break;
                case 'my_contacts_by_user': $url = 'pinegrap-contacts-' . $software_language_code . '#contacts-by-user'; break;
                case 'my_contacts_by_business_address': $url = 'pinegrap-contacts-' . $software_language_code . '#contacts-by-business-address'; break;
                case 'my_contacts_by_home_address': $url = 'pinegrap-contacts-' . $software_language_code . '#contacts-by-home-address'; break;
                case 'all_duplicate_contacts': $url = 'pinegrap-contacts-' . $software_language_code . '#duplicate-contacts'; break;

                default: $url = 'pinegrap-contacts-' . $software_language_code; break;
                
            }

            break;

        case 'add_contact':
        case 'edit_contact':
            $url = 'pinegrap-contacts-' . $software_language_code . '#create-edit-contact';
            break;

        case 'import_contacts': $url = 'pinegrap-contacts-' . $software_language_code . '#import-contacts'; break;
        case 'view_contact_groups': $url = 'pinegrap-contacts-' . $software_language_code . '#contact-groups'; break;
        case 'view_contact_groups': $url = 'pinegrap-contacts-' . $software_language_code . '#contact-groups'; break;

        case 'add_contact_group':
        case 'edit_contact_group':
            $url = 'pinegrap-contacts-' . $software_language_code . '#create-edit-contact-group';
            break;

        case 'view_users':

            switch($_GET['filter']) {

                case 'all_my_users': $url = 'pinegrap-users-' . $software_language_code; break;
                case 'my_registered_users': $url = 'pinegrap-users-' . $software_language_code . '#registered-users'; break;
                case 'my_private_users': $url = 'pinegrap-users-' . $software_language_code . '#private-users'; break;
                case 'my_member_users': $url = 'pinegrap-users-' . $software_language_code . '#member-users'; break;
                case 'my_content_managers': $url = 'pinegrap-users-' . $software_language_code . '#content-managers'; break;
                case 'my_calendar_managers': $url = 'pinegrap-users-' . $software_language_code . '#calendar-managers'; break;
                case 'my_submitted_forms_managers': $url = 'pinegrap-users-' . $software_language_code . '#submitted-forms-managers'; break;
                case 'my_visitor_report_managers': $url = 'pinegrap-users-' . $software_language_code . '#visitor-report-managers'; break;
                case 'my_contact_managers': $url = 'pinegrap-users-' . $software_language_code . '#contact-managers'; break;
                case 'my_campaign_managers': $url = 'pinegrap-users-' . $software_language_code . '#campaign-managers'; break;
                case 'my_commerce_managers': $url = 'pinegrap-users-' . $software_language_code . '#commerce-managers'; break;
                case 'all_site_designers': $url = 'pinegrap-users-' . $software_language_code . '#site-designers'; break;
                case 'all_site_administrators': $url = 'pinegrap-users-' . $software_language_code . '#site-administrators'; break;
                case 'all_site_managers': $url = 'pinegrap-users-' . $software_language_code . '#site-managers'; break;
                
                default: $url = 'pinegrap-users-' . $software_language_code . ''; break;
                
            }

            break;

        case 'add_user':
        case 'edit_user':
            $url = 'pinegrap-users-' . $software_language_code . '#create-edit-user';
            break;

        case 'import_users': $url = 'pinegrap-users-' . $software_language_code . '#import-users'; break;

        case 'view_email_campaigns': $url = 'pinegrap-campaigns-' . $software_language_code; break;

        case 'add_email_campaign':
        case 'edit_email_campaign':
            $url = 'pinegrap-campaigns-' . $software_language_code . '#create-edit-campaign';
            break;

        case 'view_email_campaign_history': $url = 'pinegrap-campaigns-' . $software_language_code . '#campaign-history'; break;
        case 'view_email_campaign_profiles': $url = 'pinegrap-campaigns-' . $software_language_code . '#campaign-profiles'; break;

        case 'add_email_campaign_profile':
        case 'edit_email_campaign_profile':
            $url = 'pinegrap-campaigns-' . $software_language_code . '#create-edit-campaign-profile';
            break;

        case 'import_email_campaign_profiles': $url = 'pinegrap-campaigns-' . $software_language_code . '#import-campaign-profiles'; break;

        case 'view_orders': $url = 'pinegrap-commerce-' . $software_language_code; break;
        case 'view_order': $url = 'pinegrap-commerce-' . $software_language_code . '#view-order'; break;
        case 'view_order_reports': $url = 'pinegrap-commerce-' . $software_language_code . '#order-reports'; break;
        case 'view_order_report': $url = 'pinegrap-commerce-' . $software_language_code . '#create-edit-order-report'; break;
        case 'view_commissions': $url = 'pinegrap-commerce-' . $software_language_code . '#commissions'; break;
        case 'edit_commission': $url = 'pinegrap-commerce-' . $software_language_code . '#edit-commission'; break;
        case 'view_recurring_commission_profiles': $url = 'pinegrap-commerce-' . $software_language_code . '#recurring-commission-profiles'; break;
        case 'edit_recurring_commission_profile': $url = 'pinegrap-commerce-' . $software_language_code . '#edit-recurring-commission-profile'; break;
        case 'view_products': $url = 'pinegrap-commerce-' . $software_language_code . '#products'; break;

        case 'add_product':
        case 'edit_product':
            $url = 'pinegrap-commerce-' . $software_language_code . '#create-edit-product';
            break;

        case 'import_products': $url = 'pinegrap-commerce-' . $software_language_code . '#import-products'; break;
        case 'edit_featured_and_new_items': $url = 'pinegrap-commerce-' . $software_language_code . '#edit-featured-and-new-items'; break;
        case 'edit_featured_and_new_items': $url = 'pinegrap-commerce-' . $software_language_code . '#edit-featured-and-new-items'; break;
        case 'view_product_groups': $url = 'pinegrap-commerce-' . $software_language_code . '#product-groups'; break;

        case 'add_product_group':
        case 'edit_product_group':
            $url = 'pinegrap-commerce-' . $software_language_code . '#create-edit-product-group';
            break;

        case 'duplicate_product_group': $url = 'pinegrap-commerce-' . $software_language_code . '#duplicate-product-group'; break;
        case 'view_product_attributes': $url = 'pinegrap-commerce-' . $software_language_code . '#product-attributes'; break;

        case 'add_product_attribute':
        case 'edit_product_attribute':
            $url = 'pinegrap-commerce-' . $software_language_code . '#create-edit-product-attribute';
            break;

        case 'view_gift_cards': $url = 'pinegrap-commerce-' . $software_language_code . '#gift-cards'; break;
        case 'add_gift_card': $url = 'pinegrap-commerce-' . $software_language_code . '#create-gift-card'; break;
        case 'edit_gift_card': $url = 'pinegrap-commerce-' . $software_language_code . '#edit-gift-card'; break;
        case 'view_offers': $url = 'pinegrap-commerce-' . $software_language_code . '-cont#offers'; break;

        case 'add_offer':
        case 'edit_offer':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-offer';
            break;

        case 'view_offer_rules': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#offer-rules'; break;

        case 'add_offer_rule':
        case 'edit_offer_rule':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-offer-rule';
            break;

        case 'view_offer_actions': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#offer-actions'; break;

        case 'add_offer_action':
        case 'edit_offer_action':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-offer-action';
            break;

        case 'view_key_codes': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#key-codes'; break;

        case 'add_key_code':
        case 'edit_key_code':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-key-code';
            break;

        case 'import_key_codes': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#import-key-codes'; break;

        case 'view_zones': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#zones'; break;

        case 'add_zone':
        case 'edit_zone':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-zone';
            break;

        case 'view_shipping_methods': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#shipping-methods'; break;

        case 'add_shipping_method':
        case 'edit_shipping_method':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-shipping-method';
            break;

        case 'view_arrival_dates': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#arrival-dates'; break;

        case 'add_arrival_date':
        case 'edit_arrival_date':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-arrival-date';
            break;

        case 'view_verified_shipping_addresses': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#verified-shipping-addresses'; break;

        case 'add_verified_shipping_address':
        case 'edit_verified_shipping_address':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-verified-shipping-address';
            break;

        case 'view_containers': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#containers'; break;

        case 'add_container':
        case 'edit_container':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-container';
            break;

        case 'view_shipping_report': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#shipping-report'; break;
        case 'view_ship_date_adjustments': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#ship-date-adjustments'; break;

        case 'add_ship_date_adjustment':
        case 'edit_ship_date_adjustment':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-ship-date-adjustment';
            break;

        case 'view_tax_zones': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#tax-zones'; break;

        case 'add_tax_zone':
        case 'edit_tax_zone':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-tax-zone';
            break;

        case 'view_referral_sources': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#referral-sources'; break;

        case 'add_referral_source':
        case 'edit_referral_source':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-referral-source';
            break;

        case 'view_currencies': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#currencies'; break;

        case 'add_currency':
        case 'edit_currency':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-currency';
            break;

        case 'view_countries': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#countries'; break;

        case 'add_country':
        case 'edit_country':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-country';
            break;

        case 'view_states': $url = 'pinegrap-commerce-cont-' . $software_language_code . '#states'; break;

        case 'add_state':
        case 'edit_state':
            $url = 'pinegrap-commerce-cont-' . $software_language_code . '#create-edit-state';
            break;

        case 'view_ads': $url = 'pinegrap-ads-' . $software_language_code; break;

        case 'add_ad':
        case 'edit_ad':
            $url = 'pinegrap-ads-' . $software_language_code . '#create-edit-ad';
            break;

        case 'view_styles': $url = 'pinegrap-design-' . $software_language_code; break;
        case 'add_style': $url = 'pinegrap-design-' . $software_language_code . '#create-style'; break;

        case 'add_system_style':
        case 'edit_system_style':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-system-style';
            break;

        case 'view_system_style_source': $url = 'pinegrap-design-' . $software_language_code . '#view-system-style-source'; break;

        case 'add_custom_style':
        case 'edit_custom_style':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-custom-style';
            break;

        case 'view_menus': $url = 'pinegrap-design-' . $software_language_code . '#menus'; break;

        case 'add_menu':
        case 'edit_menu':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-menu';
            break;

        case 'view_menu_items': $url = 'pinegrap-design-' . $software_language_code . '#menu-items'; break;

        case 'add_menu_item':
        case 'edit_menu_item':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-menu-item';
            break;

        case 'view_regions':

            switch($_GET['filter']) {

                case 'all_common_regions': $url = 'pinegrap-design-' . $software_language_code . '#common-regions'; break;
                case 'all_designer_regions': $url = 'pinegrap-design-' . $software_language_code . '#designer-regions'; break;
                case 'all_ad_regions': $url = 'pinegrap-design-' . $software_language_code . '#ad-regions'; break;
                case 'all_dynamic_regions': $url = 'pinegrap-design-' . $software_language_code . '#dynamic-regions'; break;
                case 'all_login_regions': $url = 'pinegrap-design-' . $software_language_code . '#login-regions'; break;

                default: $url = 'pinegrap-design-' . $software_language_code . '#common-regions'; break;
            }

            break;

        case 'add_common_region':
        case 'edit_common_region':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-common-region';
            break;

        case 'add_designer_region':
        case 'edit_designer_region':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-designer-region';
            break;

        case 'add_ad_region':
        case 'edit_ad_region':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-ad-region';
            break;

        case 'add_dynamic_region':
        case 'edit_dynamic_region':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-dynamic-region';
            break;

        case 'add_login_region':
        case 'edit_login_region':
            $url = 'pinegrap-design-' . $software_language_code . '#create-edit-login-region';
            break;

        case 'view_themes': $url = 'pinegrap-design-' . $software_language_code . '#themes'; break;
        case 'add_theme_file': $url = 'pinegrap-design-' . $software_language_code . '#add-theme-file'; break;
        case 'edit_theme_file': $url = 'pinegrap-design-' . $software_language_code . '#edit-theme-file'; break;
        case 'edit_theme_css': $url = 'pinegrap-design-' . $software_language_code . '#edit-theme-css'; break;
        case 'theme_designer': $url = 'pinegrap-design-' . $software_language_code . '#theme-designer'; break;
        case 'view_design_files': $url = 'pinegrap-design-' . $software_language_code . '#design-files'; break;
        case 'add_design_file': $url = 'pinegrap-design-' . $software_language_code . '#upload-design-files'; break;
        case 'edit_design_file': $url = 'pinegrap-design-' . $software_language_code . '#edit-design-file'; break;
        case 'edit_javascript': $url = 'pinegrap-design-' . $software_language_code . '#edit-javascript'; break;
        case 'import_design': $url = 'pinegrap-design-' . $software_language_code . '#import-my-site'; break;
        case 'import_zip': $url = 'pinegrap-design-' . $software_language_code . '#import-zip-file'; break;

    }

    if ($url) {
        return 'http://www.kodpen.com/docs-'. $software_language_code . '/' . $url;
    } else {
        return 'http://www.kodpen.com/';
    }

}
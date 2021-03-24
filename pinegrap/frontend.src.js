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

var software_$ = jQuery.noConflict(true);

software_$(document).ready(function() {
    
    var payment_accept_installment = software_$('input[name=payment_gateway_installment_option]');
    if(payment_accept_installment.length){
        //INSTALLMENT CHECK
        //THIS METHOD WORK ONLY FOR IYZIPAY PAYMENT GATEWAY!.
        //Briefly its send request to iyzipay installment check services with credit card first six digits, and GET response as JSON output to installment prices and charges.
        //onload we check installment charges because of may this is a notice/error refresh.

        var card_number = software_$("#card_number");
        if(card_number.length){
            check_installment();
        }
        //state default radio ':checked' property for installment there:
        //here we save the state because if software sen back for notices we wanna get back to selected value
        //here we set it all
        $('.installment_box').click(function(){
            //if installment_box clicked than set checked to input radio in it.
            $(this).find('input').prop( "checked", true );
            //installment radio checked value remember with localstorage.
            //may software return user with error or notice, back to preview screen, than we auto set checkbox old status.
            $(this).find('input:radio[name=installment]').each(function() {
                var state = JSON.parse( localStorage.getItem('radio_installment_'  + this.id) );
                if (state) this.checked = state.checked;
                localStorage.setItem(
                    'radio_installment_' + this.id, JSON.stringify({checked: this.checked})
                );
            }); 
        });
        //if card number input change,paste,keyup than control installment check again.
        $("#card_number").on("change paste keyup",function(){
            if(card_number.length){
                check_installment();
                //if card number change than it will recheck installment options,
                //we make radio installment to default.
                $('input:radio[name=installment][value=1]').prop( "checked", true );

            }
        });

        //this function uses API and api.php page .
        //get only card number seven number because may card number has spaces
        //than api.php will remove spaces and use first six number dont worry about it.
        function check_installment(){
            var card_value = $("#card_number").val().substring(0, 7);
            //get total from checkout preview or express order page.
            //if total with surchare use it else use total
            if($("input[name=total_with_surcharge]").length ){
                var total = $("input[name=total_with_surcharge]").val();
            }else{
                var total = $("input[name=total]").val();
            }
            //if card number at least than seven number dont need to check this
            //if at least seven number than start function.
            if (card_value.length >= 7){
                //remove installment row styles to show it.
                $('.installment-row').attr('style','');


                // Use AJAX to get various card info.
                software_$.ajax({
                    contentType: 'application/json',
                    url: software_path + software_directory + '/api.php',
                    data: JSON.stringify({
                        action: 'get_installment_options',
                        card: card_value,
                        price: total
                    }),
                    type: 'POST',
                    success: function(response) {
                        // Check the values in console
                        console.log(response);
                        if(response.two_supported == '1'){
                            $('#twoinstallment').attr('style','');
                            $('#twoinstallment .installment_prices_here').html(response.monthlytwo + ' x 2 <br/>' + response.totaltwo);
                        }else{
                            $('#twoinstallment').attr('style','display:none;');
                        } 
                        if(response.three_supported == '1'){
                            $('#threeinstallment').attr('style','');
                            $('#threeinstallment .installment_prices_here').html(response.monthlythree + ' x 3 <br/>' + response.totalthree);
                        }else{
                            $('#threeinstallment').attr('style','display:none;');
                        }
                        if(response.six_supported == '1'){
                            $('#sixinstallment').attr('style','');
                            $('#sixinstallment .installment_prices_here').html(response.monthlysix + ' x 6 <br/> ' + response.totalsix);
                        }else{
                            $('#sixinstallment').attr('style','display:none;');
                        }
                        if(response.nine_supported == '1'){
                            $('#nineinstallment').attr('style','');
                            $('#nineinstallment .installment_prices_here').html(response.monthlynine + ' x 9 <br/>' + response.totalnine);
                        }else{
                            $('#nineinstallment').attr('style','display:none;');
                        }
                        if(response.twelve_supported == '1'){
                            $('#twelveinstallment').attr('style','');
                            $('#twelveinstallment .installment_prices_here').html(response.monthlytwelve + ' x 12 <br/>' + response.totaltwelve);
                        }else{
                            $('#twelveinstallment').attr('style','display:none;');
                        }

                    }
                });

            }else{
                //else card number not min 7 digit than hide installment row
                $('.installment-row').attr('style','display:none');
            }
        }
    }


    software.init_accordions();
    software.init_tabs();

    var toolbar_button = software_$('#software_fullscreen_toggle');
    
    // If the toolbar button exists, then init toolbar features.
    if (toolbar_button.length) {
        var toolbar = software_$('#software_toolbar');

        var toolbar_enabled = false;

        // If the toolbar needs to be expanded when this page first loads
        // (e.g. theme preview mode was just activated), then do that.
        if (toolbar_button.hasClass('up_button')) {
            // Once the toolbar DOM is ready, then continue to check when all content is ready.
            toolbar.on('load', function() {
                var count = 0;

                // Keep polling the toolbar on a set time interval,
                // until we determine that it is ready, so that we can get
                // an accurate height of the content and slide it down.
                // Using a load event on the iframe does not work,
                // because it does not detect when the iframe is fully loaded (images, etc.)
                var toolbar_polling = setInterval(function() {
                    count++;

                    // If the toolbar content is ready, then slide down the toolbar.
                    // There is a global variable on that toolbar page (i.e. "loaded")
                    // that keeps track when that page is fully loaded, including all images,
                    // scripts, and etc.
                    if ((typeof toolbar[0].contentWindow.loaded !== 'undefined') && (toolbar[0].contentWindow.loaded == true)) {
                        // We need to get the height, so we have to set display block
                        // to get that, but we don't want the toolbar to be shown yet,
                        // so set visibility to hidden.
                        toolbar.css({
                            'visibility': 'hidden',
                            'display': 'block',
                        });

                        // Update the height of the toolbar to the height of its content.
                        // This is necessary so that the slidedown call below
                        // will slide down for the necessary height.
                        toolbar.height(toolbar.contents().find('body').height());

                        // Set display back to none, so we can slidedown toolbar.
                        toolbar.css({
                            'display': 'none',
                            'visibility': 'visible'
                        });

                        // Now that the height and everything is ready, slide down the toolbar.
                        toolbar.slideDown('fast');

                        clearInterval(toolbar_polling);

                    // Otherwise if we have polled a large number of times,
                    // then something happened to the toolbar to prevent it from loading,
                    // so let's stop polling for performance reasons.
                    } else if (count >= 1000) {
                        clearInterval(toolbar_polling);
                    }

                }, 25);
            });
            
            // Remember that toolbar was expanded by default,
            // so later we know if the toolbar status has changed.
            toolbar_enabled = true;
        }
        
        // When the window is resized, then update the toolbar, if it is visible,
        // because the size of the toolbar might have changed.
        software_$(window).resize(function () {
            if (toolbar.is(':visible')) {
                toolbar.height(toolbar.contents().find('body').height());
            }
        });

        toolbar_button.click(function () {
            // If the toolbar is expanded, then collapse it.
            if (toolbar.is(':visible')) {
                toolbar.slideUp('fast');
                toolbar_button.removeClass('up_button');
                toolbar_button.addClass('down_button');
                toolbar_button.prop('title', 'Deactivate Fullscreen Mode (Ctrl+D | \u2318+D)');
                
            // Otherwise the toolbar is collapsed, so expand it.
            } else {
                // If the toolbar is fully loaded, then expand it now.
                if ((typeof toolbar[0].contentWindow.loaded !== 'undefined') && (toolbar[0].contentWindow.loaded == true)) {
                    toolbar.css({
                        'visibility': 'hidden',
                        'display': 'block',
                    });

                    toolbar.height(toolbar.contents().find('body').height());

                    toolbar.css({
                        'visibility': 'visible',
                        'display': 'none',
                    });

                    toolbar.slideDown('fast');
                    toolbar_button.removeClass('down_button');
                    toolbar_button.addClass('up_button');
                    toolbar_button.prop('title', 'Activate Fullscreen Mode (Ctrl+D | \u2318+D)');

                // Otherwise use polling to determine when the toolbar is fully ready.
                } else {
                    var count = 0;

                    // Keep polling the toolbar on a set time interval,
                    // until we determine that it is ready, so that we can get
                    // an accurate height of the content and slide it down.
                    // Using a load event on the iframe does not work,
                    // because it does not detect when the iframe is fully loaded (images, etc.)
                    var toolbar_polling = setInterval(function() {
                        count++;

                        // If the toolbar content is ready, then slide down the toolbar.
                        // There is a global variable on that toolbar page (i.e. "loaded")
                        // that keeps track when that page is fully loaded, including all images,
                        // scripts, and etc.
                        if ((typeof toolbar[0].contentWindow.loaded !== 'undefined') && (toolbar[0].contentWindow.loaded == true)) {
                            toolbar.css({
                                'visibility': 'hidden',
                                'display': 'block',
                            });

                            toolbar.height(toolbar.contents().find('body').height());

                            toolbar.css({
                                'visibility': 'visible',
                                'display': 'none',
                            });

                            toolbar.slideDown('fast');
                            toolbar_button.removeClass('down_button');
                            toolbar_button.addClass('up_button');
                            toolbar_button.prop('title', 'Activate Fullscreen Mode (Ctrl+D | \u2318+D)');

                            clearInterval(toolbar_polling);

                        // Otherwise if we have polled a large number of times,
                        // then something happened to the toolbar to prevent it from loading,
                        // so let's stop polling for performance reasons.
                        } else if (count >= 1000) {
                            clearInterval(toolbar_polling);
                        }

                    }, 25);
                }
            }
        });
    
        // Update toolbar properties when visitor leaves this page.
        software_$(window).on('beforeunload', function() {
            var new_toolbar_enabled = false;

            // If the toolbar is enabled, then store that.
            if (toolbar_button.hasClass('up_button')) {
                new_toolbar_enabled = true;
            }

            // If the status of the toolbar is different from when the page
            // first loaded, then send AJAX request to update status,
            // so that we can remember it for future page views.
            if (new_toolbar_enabled != toolbar_enabled) {
                software_$.ajax({
                    contentType: 'application/json',
                    url: software_path + software_directory + '/api.php',
                    data: JSON.stringify({
                        action: 'update_toolbar_properties',
                        token: software_token,
                        enabled: new_toolbar_enabled
                    }),
                    type: 'POST',
                    async: false
                });
            }
        });

        // Add keyboard shortcuts.
        software_$(window).bind('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                switch (String.fromCharCode(event.which).toLowerCase()) {
                    case 'd':
                        event.preventDefault();
                        toolbar_button.trigger('click');
                        break;

                    case 'e':
                        var grid_toggle = software_$('#grid_toggle');

                        if (grid_toggle.length) {
                            event.preventDefault();

                            // Timeout resolves Firefox bug.
                            setTimeout (function () {
                                grid_toggle.click();
                            }, 0);
                        }

                        break;

                    // Page designer shortcut (Ctrl+G).
                    case 'g':
                        var page_designer_button = toolbar.contents().find('.page_designer_button');

                        if (page_designer_button.length) {
                            event.preventDefault();

                            // Timeout is necessary in order to workaround Firefox bug
                            // with Ctrl+G where it would still open find area,
                            // even though we run preventDefault above.
                            setTimeout (function () {
                                page_designer_button[0].click();
                            }, 0);
                        }

                        break;

                    case 's':
                        var save_button = software_$('#software_inline_editing_save_button');

                        if (save_button.is(':visible')) {
                            event.preventDefault();
                            save_button.trigger('click');
                        }

                        break;
                }
            }
        });
    }

    // the following is a workaround for a mobile Safari bug where content is not correctly displayed
    // when an iPhone's orientation is changed (e.g. from landscape to portrait).
    if (document.getElementById('viewport')) {
        var mobile_timer = false,viewport = document.getElementById('viewport');
        if(navigator.userAgent.match(/iPhone/i)) {
            viewport.setAttribute('content','width=device-width,minimum-scale=1.0,maximum-scale=1.0,initial-scale=1.0');
            window.addEventListener('gesturestart',function () {
                clearTimeout(mobile_timer);
                viewport.setAttribute('content','width=device-width,minimum-scale=1.0,maximum-scale=10.0');
            },false);
            window.addEventListener('touchend',function () {
                clearTimeout(mobile_timer);
                mobile_timer = setTimeout(function () {
                    viewport.setAttribute('content','width=device-width,minimum-scale=1.0,maximum-scale=1.0,initial-scale=1.0');
                },1000);
            },false);
        }
    }

    // If kiosk mode is enabled, then deal with that.
    if ((typeof software_kiosk !== 'undefined') && (software_kiosk == true)) {
        software.kiosk.init();
    }
});

function change_quick_add_product_id(product_id)
{
    var selection_type;
    var default_quantity;
    var recipient_required;
    
    if (product_id) {
        selection_type = quick_add_products[product_id][0];
        default_quantity = quick_add_products[product_id][1];
        recipient_required = quick_add_products[product_id][2];        
    }
    
    // hide all quick add rows until we figure out which rows to show

    if (document.getElementById('quick_add_ship_to_row')) {
        document.getElementById('quick_add_ship_to_row').style.display = 'none';        
    }
    
    if (document.getElementById('quick_add_add_name_row')) {
        document.getElementById('quick_add_add_name_row').style.display = 'none';        
    }
    
    if (document.getElementById('quick_add_quantity_row')) {
        document.getElementById('quick_add_quantity_row').style.display = 'none';        
    }
    
    if (document.getElementById('quick_add_amount_row')) {
        document.getElementById('quick_add_amount_row').style.display = 'none';        
    }

    // if a recipient is required to be selected, then show ship to and add name rows
    if (recipient_required == true) {
        document.getElementById('quick_add_ship_to_row').style.display = '';
        document.getElementById('quick_add_add_name_row').style.display = '';
    }
    
    // if product has a quantity selection type, then prefill default quantity and show quantity row
    if (selection_type == 'quantity') {
        document.getElementById('quick_add_quantity').value = default_quantity;
        document.getElementById('quick_add_quantity_row').style.display = '';
    }
    
    // if product has a donation selection type, then show amount row
    if (selection_type == 'donation') {
        document.getElementById('quick_add_amount_row').style.display = '';
    }
}

// this function initializes a dynamic ad region and starts the animation
function software_initialize_dynamic_ad_region(ad_region_name, transition_type, transition_duration, slideshow, slideshow_interval, slideshow_continuous)
{
    // when the document is ready, then continue
    software_$(document).ready(function () {
        var ad_elements = software_$('#software_ad_region_' + ad_region_name + ' .items > div');
        var ads_element = software_$('#software_ad_region_' + ad_region_name + ' .items');
        
        // get the items container element and apply the hidden overflow in order to remove scrollbars
        var items_container_element = software_$('#software_ad_region_' + ad_region_name + ' .items_container').css('overflow', 'hidden');
        
        // get the menu item link element that has this target, select the menu item and it's corresponding ad
        function trigger(data) {
            var menu_item_link_element = software_$('#software_ad_region_' + ad_region_name + ' .menu').find('a[href$="' + data.id + '"]').get(0);
            
            // if this is a slide transition then call the function that updates it's menu items
            if (transition_type == 'slide') {
                software_update_current_ad_menu_item(menu_item_link_element);
                
            // else this is a fade transition type, so fade the content
            } else {
                software_fade_ads(menu_item_link_element, ad_region_name, transition_duration);
            }
        }

        // Update the CSS for all captions.
        software_$('#software_ad_region_' + ad_region_name + ' .caption').css({
            'display': 'block',
            'position': 'absolute',
            'z-index': '0',
            'filter:alpha': '(opacity=0)',
            '-moz-opacity': '0',
            '-khtml-opacity': '0',
            'opacity': '0',
            'width': '100%'
        });
        
        // if the transition type is set to slide, prepare and initialize the slide effect
        if (transition_type == 'slide') {
            // float the ads so they are in a horizontal line
            ad_elements.css({
                'float' : 'left',
                'position' : 'relative' // IE fix to ensure overflow is hidden
            });
            
            // calculate a new width for the container (so it holds all ads)
            ads_element.css('width', ad_elements[0].offsetWidth * ad_elements.length);
            
            // add click event handler to menu items
            software_$('#software_ad_region_' + ad_region_name + ' .menu').find('a').click(function(){software_update_current_ad_menu_item(this)});
            
            // Update the previous button so that the ad region will slide to the previous ad when it is clicked.
            software_$('#software_ad_region_' + ad_region_name + ' .previous').click(function(){
                items_container_element.trigger('prev');
            });

            // Update the next button so that the ad region will slide to the next ad when it is clicked.
            software_$('#software_ad_region_' + ad_region_name + ' .next').click(function(){
                items_container_element.trigger('next');
            });

            // if there is a bookmark in the location, then select the corresponding menu item
            if (window.location.hash) {
                trigger({ id : window.location.hash.substr(1) });
                
            // else there is not a bookmark in the location, so select the first menu item
            } else {
                software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first').click();
            }

            // Get the selected ad id which is the default ad that is shown when the page loads,
            // so that we can show caption for ad, if one exists. This will normally be the first ad,
            // unless a hash was supplied in the address.
            var selected_ad_id = software_$('#software_ad_region_' + ad_region_name + ' .menu a.current')[0].href.substr(software_$('#software_ad_region_' + ad_region_name + ' .menu a.current')[0].href.lastIndexOf('#') + 1);

            // If a caption exists for the default ad then fade it in.
            // We fade captions in/out for both slide and fade ad regions.
            // We never slide captions because that might be difficult to do since captions
            // are on a different layer than the ads, so the sliding might be out of sync.
            if (document.getElementById(selected_ad_id + '_caption')) {
                software_$('#' + selected_ad_id + '_caption').animate({
                    opacity: 1
                }, transition_duration, function () {
                    software_$('#' + selected_ad_id + '_caption').css('z-index', '1');
                });
            }
            
            // prepare the offset which is based on the padding of an element
            var offset = parseInt(ads_element.css('paddingTop') || 0) * -1;

            // If the transition duration is 0, then default it to half of a second.
            // We did not used to have to do this, but when we updated jQuery to 1.7.2,
            // a default of 0 caused the slide to be instant.
            if (transition_duration == 0) {
                transition_duration = 500;
            }
            
            // prepare the scroll options for the scroll plugin
            var scrollOptions = {
                // set the element that has the overflow
                target: items_container_element,
                
                // set the container for the ads
                items: ad_elements,
                
                // set where the menu is located
                navigation: '.menu a',
                
                // set that the scrolling should only work horizontally
                axis: 'x',

                // Fade in caption for new ad and fade out caption for old ad.
                // We fade captions in/out for both slide and fade ad regions.
                // We never slide captions because that might be difficult to do since captions
                // are on a different layer than the ads, so the sliding might be out of sync.
                onBefore: function(event, selected_element) {
                    var selected_ad_id = selected_element.id;

                    // If caption exists for new ad then fade it in.
                    if (document.getElementById(selected_ad_id + '_caption')) {
                        software_$('#' + selected_ad_id + '_caption').animate({
                            opacity: 1
                        }, transition_duration, function () {
                            software_$('#' + selected_ad_id + '_caption').css('z-index', '1');
                        });
                    }

                    // Loop through all captions in order to fade any out that are visible.
                    // We don't have a way of knowing the previous ad that was visible
                    // because of the way that the slide plugin works, so we have to check all captions.
                    software_$('#software_ad_region_' + ad_region_name + ' .caption').each(function() {
                        // If this caption is visible then fade it out.
                        if (software_$(this).css('z-index') == 1) {
                            software_$(this).animate({
                                opacity: 0
                            }, transition_duration, function () {
                                software_$(this).css('z-index', '0');
                            });
                        }
                    });
                },
                
                // set callback
                onAfter: trigger,
                
                // set offset based on padding
                offset: offset,

                // set the speed of the scroll effect
                duration: transition_duration,
                
                // easing - can be used with the easing plugin: 
                // http://gsgd.co.uk/sandbox/jquery/easing/
                easing: 'swing',

                // The following property allows the ad region to slide fast when going from an ad far away from another ad
                // (e.g. going from the last ad to the first ad)
                constant: false
            };
            
            // initialize the serialScroll plugin that handles the scrolling effect and allows the slideshow effect to work
            software_$('#software_ad_region_' + ad_region_name).serialScroll(scrollOptions);
        
        // else prepare and initialize the fade effect
        } else {
            // place the ads menu above the ads in the stack
            software_$('#software_ad_region_' + ad_region_name + ' .menu').css({'z-index' : '2'});

            // Place the previous and next buttons above the ads.
            software_$('#software_ad_region_' + ad_region_name + ' .previous').css({'z-index' : '2'});
            software_$('#software_ad_region_' + ad_region_name + ' .next').css({'z-index' : '2'});
            
            // update the ads CSS to stack them on top of each other, put them at the bottom of the stack, and hide them all
            software_$('#software_ad_region_' + ad_region_name + ' .items_container .item').css({
                'position' : 'absolute',
                'top' : '0px',
                'left' : '0px',
                'z-index' : '0',
                'float' : 'none',
                'filter:alpha' : '(opacity=0)',
                '-moz-opacity' : '0',
                '-khtml-opacity' : '0',
                'opacity' : '0'
            });
            
            // add click event handler to menu items, then onclick prepare the menu items and call the fade ads function
            software_$('#software_ad_region_' + ad_region_name + ' .menu').find('a').click(function(mouse_event){
                // prevent the link from reloading the page
                mouse_event.preventDefault();
                
                // call the fade function
                software_fade_ads(this, ad_region_name, transition_duration);
            });

            // Update the previous button so that the ad region will fade to the previous ad when it is clicked.
            software_$('#software_ad_region_' + ad_region_name + ' .previous').click(function(){
                // If we are currently on the first ad, then trigger with the last ad.
                if (software_$('#software_ad_region_' + ad_region_name + ' ul.menu li:first-child a').attr('class') == 'current') {
                    trigger({id : software_$('#software_ad_region_' + ad_region_name + ' ul.menu li:last-child a').attr('href').substr(1)});

                // Otherwise we are not currently on the first ad, so trigger with the previous ad.
                } else {
                    trigger({id: software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).prev('li')[0].firstChild.href.substr(software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).prev('li')[0].firstChild.href.lastIndexOf('#') + 1) });
                }
            });

            // Update the next button so that the ad region will fade to the next ad when it is clicked.
            software_$('#software_ad_region_' + ad_region_name + ' .next').click(function(){
                // If we have reached the last ad, then trigger with the first ad.
                if (software_$('#software_ad_region_' + ad_region_name + ' ul.menu li:last-child a').attr('class') == 'current') {
                    trigger({id : software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first-child').attr('href').substr(1)});

                // Otherwise we have not reached the last ad, so trigger with the next ad.
                } else {
                    trigger({ id : software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).next('li')[0].firstChild.href.substr(software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).next('li')[0].firstChild.href.lastIndexOf('#') + 1) });
                }
            });
            
            // if there is a bookmark in the location, then unhide it's ad and select the corresponding menu item
            if (window.location.hash) {
                // set the corresponding ad to be visable
                software_$('#software_ad_region_' + ad_region_name + ' .items_container #' + window.location.hash.substr(1)).css({
                    'filter:alpha' : '(opacity=1)',
                    '-moz-opacity' : '1',
                    '-khtml-opacity' : '1',
                    'opacity' : '1'
                });
                
                // trigger the change to update the menu
                trigger({ id : window.location.hash.substr(1) });
                
            // else there is not a bookmark in the location, so unhide the first ad and select the first menu item
            } else {
                // set the first ad to be visable
                software_$(software_$('#software_ad_region_' + ad_region_name + ' .items_container .item')[0]).css({
                    'filter:alpha' : '(opacity=1)',
                    '-moz-opacity' : '1',
                    '-khtml-opacity' : '1',
                    'opacity' : '1'
                });
                
                // trigger the change to update the menu
                trigger({ id : software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first')[0].href.substr(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first')[0].href.lastIndexOf('#') + 1) });
            }
        }
        
        // if slideshow is enabled for this ad region, then initialize slideshow
        if (slideshow == true) {
            // start the slideshow with the correct interval
            var cycle_timer = setInterval(function () {
                // if this transition type is a slide, then trigger the slide
                if (transition_type == 'slide') {
                    items_container_element.trigger('next');
                
                // else this is a fade so trigger the next fade
                } else {
                    // If we have reached the end of the slideshow, then trigger with the first ad.
                    if (software_$('#software_ad_region_' + ad_region_name + ' ul.menu li:last-child a').attr('class') == 'current') {
                        trigger({id : software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first-child').attr('href').substr(1)});

                    // Otherwise we have not reached the end of the slideshow, so trigger with the next ad.
                    } else {
                        trigger({ id : software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).next('li')[0].firstChild.href.substr(software_$(software_$('#software_ad_region_' + ad_region_name + ' ul.menu a.current')[0].parentNode).next('li')[0].firstChild.href.lastIndexOf('#') + 1) });
                    }
                }
                
                // If we have reached the end of the slideshow, and continuous is disabled, then stop the slideshow.
                if (
                    (software_$('#software_ad_region_' + ad_region_name + ' ul.menu li:last-child a').attr('class') == 'current')
                    && (slideshow_continuous == false)
                ) {
                    // if this is the fade transition type, then send the slideshow back to the first ad after the set amount of time has passed
                    if (transition_type == 'fade') {
                        setTimeout("software_fade_ads('', '" + ad_region_name + "', " + transition_duration + ");", slideshow_interval * 1000);
                    }

                    clearInterval(cycle_timer);
                }
               
            }, slideshow_interval * 1000);
            
            // set some trigger elements to stop the slideshow
            var stop_triggers =
                software_$('#software_ad_region_' + ad_region_name + ' .menu').find('a') // menu items
                .add('#software_ad_region_' + ad_region_name + ' .items_container') // ads container
                .add('#software_ad_region_' + ad_region_name + ' .previous') // previous button
                .add('#software_ad_region_' + ad_region_name + ' .next') // next button
            
            // create a function to stop the slideshow
            function stop_slideshow() {
                // remove the stop triggers
                stop_triggers.unbind('click.cycle');
                
                // stop the slideshow
                clearInterval(cycle_timer);
            }
            
            // bind the stop slideshow function to the stop triggers
            stop_triggers.bind('click.cycle', stop_slideshow);
        }
    });
}

// this function fades one ad in and one ad out
function software_fade_ads(selected_menu_item, ad_region_name, transition_duration) {
    // if there is not a selected menu item, then set it to the first menu item
    if ((!selected_menu_item) || (selected_menu_item == '')) {
        selected_menu_item = software_$('#software_ad_region_' + ad_region_name + ' ul.menu a:first')[0];
    }
    
    // get the selected ad id
    var selected_ad_id = selected_menu_item.href.substr(selected_menu_item.href.lastIndexOf('#') + 1);
    
    var current_ad_id = 0;
    
    // if there is a current menu item, then get the current ad's id
    if (software_$('#software_ad_region_' + ad_region_name + ' .menu a.current')[0]) {
        current_ad_id = software_$('#software_ad_region_' + ad_region_name + ' .menu a.current')[0].href.substr(software_$('#software_ad_region_' + ad_region_name + ' .menu a.current')[0].href.lastIndexOf('#') + 1);
    }
    
    // update the current menu item
    software_update_current_ad_menu_item(selected_menu_item);
    
    // if the transition duration is 0, then default it to one second
    if (transition_duration == 0) {
        transition_duration = 1000;
    }
    
    // fade in the new ad, and after the fade is complete set it's z-index to 1 so it's on top of the stack
    software_$('#software_ad_region_' + ad_region_name + ' .items_container #' + selected_ad_id).animate({
        opacity: 1
    }, transition_duration, function () {
        software_$('#software_ad_region_' + ad_region_name + ' .items_container #' + selected_ad_id).css('z-index', '1');
        
        // if the browser is IE 7 or IE 8
        // and if there is no background color or background image set (which is another fix for this issue),
        // then remove filter in order to workaround IE jagged text bug
        // setting a background color or image if possible is better
        // because it removes the jagged text, even during the transition,
        // so that is why we don't want to interfere with it
        if (
            (software_$.browser.msie == true)
            &&
            (
                (parseInt(software_$.browser.version, 10) == 7)
                || (parseInt(software_$.browser.version, 10) == 8)
            )
            && (software_$(this).css('background-color') == 'transparent')
            && (software_$(this).css('background-image') == 'none')
        ) {
            software_$(this).css('filter','');
        }
    });

    // If caption exists for new ad then fade it in.
    if (document.getElementById(selected_ad_id + '_caption')) {
        software_$('#' + selected_ad_id + '_caption').animate({
            opacity: 1
        }, transition_duration, function () {
            software_$('#' + selected_ad_id + '_caption').css('z-index', '1');
        });
    }
    
    // fade out the old ad, and after the fade is complete set it's z-index to 0 so that it is under the current ad
    software_$('#software_ad_region_' + ad_region_name + ' .items_container #' + current_ad_id).animate({
        opacity: 0
    }, transition_duration, function () {
        software_$('#software_ad_region_' + ad_region_name + ' .items_container #' + current_ad_id).css('z-index', '0');
    });

    // If caption exists for old ad then fade it out.
    if (document.getElementById(current_ad_id + '_caption')) {
        software_$('#' + current_ad_id + '_caption').animate({
            opacity: 0
        }, transition_duration, function () {
            software_$('#' + current_ad_id + '_caption').css('z-index', '0');
        });
    }
}

// this function updates the current menu item
function software_update_current_ad_menu_item(object)
{
    software_$(object)
        .parents('ul:first')
            .find('a')
                .removeClass('current')
            .end()
        .end()
        .addClass('current');
}

function prepare_content_for_html(content)
{
    // Convert content to a string, so that we don't receive an error
    // if content is an integer or other type.
    content = String(content);

    var chars = new Array ('&','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','\"','�','<',
                         '>','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�','�','�','�',
                         '�','�','�','�','�','�','�','�');

    var entities = new Array ('amp','agrave','aacute','acirc','atilde','auml','aring',
                            'aelig','ccedil','egrave','eacute','ecirc','euml','igrave',
                            'iacute','icirc','iuml','eth','ntilde','ograve','oacute',
                            'ocirc','otilde','ouml','oslash','ugrave','uacute','ucirc',
                            'uuml','yacute','thorn','yuml','Agrave','Aacute','Acirc',
                            'Atilde','Auml','Aring','AElig','Ccedil','Egrave','Eacute',
                            'Ecirc','Euml','Igrave','Iacute','Icirc','Iuml','ETH','Ntilde',
                            'Ograve','Oacute','Ocirc','Otilde','Ouml','Oslash','Ugrave',
                            'Uacute','Ucirc','Uuml','Yacute','THORN','euro','quot','szlig',
                            'lt','gt','cent','pound','curren','yen','brvbar','sect','uml',
                            'copy','ordf','laquo','not','shy','reg','macr','deg','plusmn',
                            'sup2','sup3','acute','micro','para','middot','cedil','sup1',
                            'ordm','raquo','frac14','frac12','frac34');

    for (var i = 0; i < chars.length; i++) {
        myRegExp = new RegExp();
        myRegExp.compile(chars[i],'g');
        content = content.replace (myRegExp, '&' + entities[i] + ';');
    }

    return content;
}

// this is the timer used for the image editor button animation
var software_edit_image_button_timer = Array;

function software_show_or_hide_image_edit_button(image_id, event) 
{
    clearTimeout(software_edit_image_button_timer[image_id]);
    
    image = document.getElementById(image_id);
    
    // if the edit button is hidden then update it's position and show it
    if (event.type == 'mouseover') {
        // if this is not a photo gallery page, then adjust the position of the image editor button
        if (!software_$('.software_photo_gallery_album')[0]) {
            // if the images parent's position is not relative then set the position to be relative,
            // and get the position of the parent and add it to the relative image position to get the screen position
            if (image.offsetParent.style.position != 'relative') {
                // get the orginal position styling for the object
                var orignal_position_styling = image.offsetParent.style.position;
                
                // set it's position to be relative
                image.offsetParent.style.position = 'relative';
                
                // get the image's dimensions
                var image_left_position = image.offsetParent.offsetLeft + image.offsetLeft;
                var image_top_position = image.offsetParent.offsetTop + image.offsetTop;
                
                // switch the position back to what it was originally
                image.offsetParent.style.position = orignal_position_styling;
                
            // else the parent is relative so we do not need to get the parent's position
            } else {
                var image_left_position = image.offsetLeft;
                var image_top_position = image.offsetTop;
            }
            
            // update button position
            document.getElementById("software_edit_button_for_" + image_id).style.left = image_left_position + "px";
            document.getElementById("software_edit_button_for_" + image_id).style.top = image_top_position + "px";
        }
        
        // show the image
        document.getElementById("software_edit_button_for_" + image_id).style.display = "block";
        
    // else if the event was a mouseout, then slide up the button to hide it.
    } else if (event.type == 'mouseout') {
        software_edit_image_button_timer[image_id] = setTimeout('software_$(document.getElementById("software_edit_button_for_' + image_id + '")).slideUp("fast");', 250);
    }
}

// this function is responsible for initializing the edit region dialog
function software_initialize_edit_region_dialog()
{
    var edit_region_dialog = software_$('#software_edit_region_dialog');

    // initialize the edit region dialog
    edit_region_dialog.dialog({
        autoOpen: false,
        modal: true,
        dialogClass: 'standard',
        open: function() {
            // set the dialog box's default width and height
            var dialog_width = 750;
            var dialog_height = 587;

            // Get window width and height.
            var window_width = software_$(window).width();
            var window_height = software_$(window).height();
            
            // if the dialog's new width is greater than the default, then set the width
            if ((window_width * .75) >= dialog_width) {
                dialog_width = window_width * .75;
            }
            
            // if the dialog's new height is greater than the default, then set the width
            if ((window_height * .75) >= dialog_height) {
                dialog_height = window_height * .75;
            }

            // Update dialog width and height and position it in the center.
            edit_region_dialog.dialog('option', 'width', dialog_width);
            edit_region_dialog.dialog('option', 'height', dialog_height);
            edit_region_dialog.dialog('option', 'position', 'center');
            
            // remove the close button from the dialog
            software_$('.ui-dialog-titlebar-close').css('display','none');
        },
        close: function() {
            if (software_editor_version == 'latest') {
                CKEDITOR.instances['software_edit_region_textarea'].destroy();
            } else {
                // destroy the text editor instance
                tinyMCE.execCommand('mceRemoveControl', false, 'software_edit_region_textarea');
            }
            
            // clear the content
            document.getElementById('software_edit_region_textarea').value = '';
        },
        resize: function() {
            // Get dialog element, which we will use below to get the width and height.
            var ui_dialog = software_$('.standard.ui-dialog');

            if (software_editor_version == 'previous') {
                // Update the width and height for the rich-text editor based on the size of the dialog.
                software_$('#software_edit_region_dialog #software_edit_region_textarea_ifr').css({
                    'width': parseInt(ui_dialog.width() - 60),
                    'height': parseInt(ui_dialog.height() - 240)
                });
            }
        }
    });
}

// this function is responsible for showing the edit region dialog
// which is used for editing page regions, common regions, and the system region header and footer
function software_open_edit_region_dialog(region_id, region_type, region_name, region_order)
{
    // open the dialog
    software_$('#software_edit_region_dialog').dialog('open');
    
    var region_name_for_title_bar = '';

    // prepare the region name differently based on the region type
    switch (region_type) {
        case 'pregion':
            region_name_for_title_bar = 'Page Region #' + region_order;
            break;
                
        case 'cregion':
            region_name_for_title_bar = 'Common Region: ' + region_name;
            break;
                
        case 'system_region_header':
            region_name_for_title_bar = 'System Region Header';
            break;
            
        case 'system_region_footer':
            region_name_for_title_bar = 'System Region Footer';
            break;
    }
    
    // add the text editor title content to the title bar
    software_$('#software_edit_region_dialog')[0].parentNode.firstChild.firstChild.innerHTML = '<table class="title_bar_table"><tr><td style="width: 33%;">Rich-text Editor</td><td style="width: 33%; text-align: center;">' + region_name_for_title_bar + '</td><td style="width: 33%; text-align: right"></td></tr></table>';
    
    // update the hidden form fields with the values for this region
    software_$('#software_edit_region_dialog #region_id')[0].value = region_id;
    software_$('#software_edit_region_dialog #region_type')[0].value = region_type;
    
    // if there is a region order, then update the region order hidden field
    if (region_order != '') {
        document.getElementById('region_order').value = region_order;
    }
    
    // make ajax call to get the content from the database and set content for textarea value
    document.getElementById('software_edit_region_textarea').value = software_$.ajax({
        type: 'GET',
        url: software_path + software_directory + '/get_region_content.php',
        data: 'page_id=' + software_$('#software_edit_region_dialog #page_id')[0].value + '&region_id=' + region_id + '&region_type=' + region_type,
        async: false
    }).responseText;

    if (software_editor_version == 'latest') {
        software_ckeditor_config.height = '400px';
        software_ckeditor_config.startupFocus = true;

        CKEDITOR.replace('software_edit_region_textarea', software_ckeditor_config);
    } else {
        // initiate the editor
        tinyMCE.execCommand('mceAddControl', false, 'software_edit_region_textarea');
    }
}

// this function is called once the editor is done loading
// the editor is resized to fit the dialog
// we have to have this separate function because we have to wait until the mceAddControl is completely done or the resize will not work
function software_activate_edit_region_dialog()
{
    // Get dialog element, which we will use below to get the width and height.
    var ui_dialog = software_$('.standard.ui-dialog');

    // Update the width and height for the rich-text editor based on the size of the dialog.
    software_$('#software_edit_region_dialog #software_edit_region_textarea_ifr').css({
        'width': parseInt(ui_dialog.width() - 60),
        'height': parseInt(ui_dialog.height() - 240)
    });
}

// this function initializes the photo gallery
function software_init_photo_gallery(thumbnails)
{
    // prepare the thumbnails
    software_$(thumbnails).each(function(index) {
        // save the thumbnail properties so that we can access them later
        var object_id = this[0];
        
        // get the image object
        var image = software_$('.software_photo_gallery_album #' + object_id)[0];
        
        // add mouseover and mouseout listeners to the image so that we can add a hover effect
        software_$(image).mouseover(function() { software_$(image).addClass('image_hover'); });
        software_$(image).mouseout(function() { software_$(image).removeClass('image_hover'); });
        
        // get the type of object (photo or album)
        var object_type = object_id.substr(0, object_id.lastIndexOf('_'));
        
        // if this is an album, then adjust it's frame's width and height, 
        // and then add a click event listener to send the browser to the next level of the photo gallery
        if (object_type == 'album') {
            // set a dealy to resize the album frames, 
            // this is required so that safari will correctly render the album frames
            setTimeout (function () {
                // adjust the width and height for the ablum frames
                software_$(software_$(software_$('.software_photo_gallery_album #' + object_id)[0].parentNode).find('#album_frame_1')[0]).css('width', software_$(image).outerWidth(true))
                software_$(software_$(software_$('.software_photo_gallery_album #' + object_id)[0].parentNode).find('#album_frame_1')[0]).css('height', software_$(image).outerHeight(true))
                software_$(software_$(software_$('.software_photo_gallery_album #' + object_id)[0].parentNode).find('#album_frame_2')[0]).css('width', software_$(image).outerWidth(true))
                software_$(software_$(software_$('.software_photo_gallery_album #' + object_id)[0].parentNode).find('#album_frame_2')[0]).css('height', software_$(image).outerHeight(true))
            }, 250);
            
            // add a click listener to send the browser to the next level of the photo gallery
            software_$(image).click(function() { 
                // get the album's folder id
                var folder_id = this.id.substr(this.id.lastIndexOf('_') + 1);
                
                // send the user to the next level of the photo gallery
                window.location = software_path + software_page_name + '?folder_id=' + folder_id;
            });
        }
    });
    
    // initialize lightbox for all photos
    software_$('.software_photo_gallery_album .photo a.link').lightBox();
}

function software_change_verified_country()
{
    // hide various containers until we find what should be displayed
    document.getElementById('verified_state_container').style.display = 'none';
    document.getElementById('verified_address_container').style.display = 'none';
    document.getElementById('verified_message').style.display = 'none';
    document.getElementById('verified_summary').style.display = 'none';
    document.getElementById('verified_button').style.display = 'none';
    
    // if a country was selected, then continue to show details for country
    if (document.getElementById('verified_country_id').value != '') {
        // remove existing options from state pick list
        document.getElementById('verified_state_id').options.length = 0;
        
        // add blank option to state pick list
        document.getElementById('verified_state_id').options[document.getElementById('verified_state_id').length] = new Option('', '');
        
        var states_exist = false;
        
        // loop through all of the states in order to add options to states pick list
        for (i = 0; i < software_verified_states.length; i++) {
            // if this state is in the selected country, then add option to states pick list
            if (software_verified_states[i]['country_id'] == document.getElementById('verified_country_id').value) {
                document.getElementById('verified_state_id').options[document.getElementById('verified_state_id').length] = new Option(software_verified_states[i]['name'], software_verified_states[i]['id']);
                states_exist = true;
            }
        }
        
        // if states exist for the selected country, then show states
        if (states_exist == true) {
            document.getElementById('verified_state_container').style.display = '';
            
        // else states do not exist for the selected country, so output error
        } else {
            document.getElementById('verified_message').innerHTML = 'Sorry, we don\'t have any verified addresses for that Country.';
            document.getElementById('verified_message').style.color = 'red';
            document.getElementById('verified_message').style.display = '';
        }
    }
}

function software_change_verified_state()
{
    // hide various containers until we find what should be displayed
    document.getElementById('verified_address_container').style.display = 'none';
    document.getElementById('verified_message').style.display = 'none';
    document.getElementById('verified_summary').style.display = 'none';
    document.getElementById('verified_button').style.display = 'none';
    
    // if a state was selected, then continue to show details for state
    if (document.getElementById('verified_state_id').value != '') {
        // remove existing options from address pick list
        document.getElementById('verified_address_id').options.length = 0;
        
        // add blank option to address pick list
        document.getElementById('verified_address_id').options[document.getElementById('verified_address_id').length] = new Option('', '');
        
        var addresses_exist = false;
        
        // loop through all of the addresses in order to add options to address pick list
        for (i = 0; i < software_verified_addresses.length; i++) {
            // if this address is in the selected state, then add option to address pick list
            if (software_verified_addresses[i]['state_id'] == document.getElementById('verified_state_id').value) {
                document.getElementById('verified_address_id').options[document.getElementById('verified_address_id').length] = new Option(software_verified_addresses[i]['label'], software_verified_addresses[i]['id']);
                addresses_exist = true;
            }
        }
        
        // if addresses exist for the selected state, then show addresses
        if (addresses_exist == true) {
            document.getElementById('verified_address_container').style.display = '';
            
        // else addresses do not exist for the selected state, so output error
        } else {
            document.getElementById('verified_message').innerHTML = 'Sorry, we don\'t have any verified addresses for that State.';
            document.getElementById('verified_message').style.color = 'red';
            document.getElementById('verified_message').style.display = '';
            
        }
    }
}

function software_change_verified_address()
{
    // hide various containers until we find what should be displayed
    document.getElementById('verified_message').style.display = 'none';
    document.getElementById('verified_summary').style.display = 'none';
    document.getElementById('verified_button').style.display = 'none';
    
    // if an address was selected, then continue to show details for address
    if (document.getElementById('verified_address_id').value != '') {
        // loop through all of the addresses in order to get the info for the selected address
        for (i = 0; i < software_verified_addresses.length; i++) {
            // if this address is the selected address then store info and break out of loop
            if (software_verified_addresses[i]['id'] == document.getElementById('verified_address_id').value) {
                var company = software_verified_addresses[i]['company'];
                var address_1 = software_verified_addresses[i]['address_1'];
                var address_2 = software_verified_addresses[i]['address_2'];
                var city = software_verified_addresses[i]['city'];
                var state_code = software_verified_addresses[i]['state_code'];
                var zip_code = software_verified_addresses[i]['zip_code'];
                var country_code = software_verified_addresses[i]['country_code'];
                var country_name = software_verified_addresses[i]['country_name'];
                
                break;
            }
        }
        
        var output_address = '';
        
        // if there is a company then add it to address
        if (company != '') {
            output_address += prepare_content_for_html(company) + '<br />';
        }
        
        // add address 1 to address
        output_address += prepare_content_for_html(address_1) + '<br />';
        
        // if there is an address 2 then add it to address
        if (address_2 != '') {
            output_address += prepare_content_for_html(address_2) + '<br />';
        }
        
        // add city, state, zip code, and country to address
        output_address +=
            prepare_content_for_html(city) + ', ' + prepare_content_for_html(state_code) + ' ' + prepare_content_for_html(zip_code) + '<br />' +
            prepare_content_for_html(country_name);
        
        document.getElementById('verified_summary').innerHTML = output_address;
        document.getElementById('verified_summary').style.display = '';
        document.getElementById('verified_button').style.display = '';
    }
}

function software_use_verified_address()
{
    // loop through all of the addresses in order to get the info for the selected address
    for (i = 0; i < software_verified_addresses.length; i++) {
        // if this address is the selected address then store info and break out of loop
        if (software_verified_addresses[i]['id'] == document.getElementById('verified_address_id').value) {
            var company = software_verified_addresses[i]['company'];
            var address_1 = software_verified_addresses[i]['address_1'];
            var address_2 = software_verified_addresses[i]['address_2'];
            var city = software_verified_addresses[i]['city'];
            var state_code = software_verified_addresses[i]['state_code'];
            var zip_code = software_verified_addresses[i]['zip_code'];
            var country_code = software_verified_addresses[i]['country_code'];
            
            break;
        }
    }
    
    // copy address info into shipping address fields
    document.getElementById('company').value = company;
    document.getElementById('address_1').value = address_1;
    document.getElementById('address_2').value = address_2;
    document.getElementById('city').value = city;
    document.getElementById('zip_code').value = zip_code;
    document.getElementById('country').value = country_code;

    // Trigger change event so that init_country will process new country.
    software_$('#country').trigger('change');
    
    software_$('[name=state]').val(state_code);
    
    // hide button
    document.getElementById('verified_button').style.display = 'none';
    
    // update verified address field so that no option is selected
    document.getElementById('verified_address_id').value = '';
    
    // add confirmation message
    document.getElementById('verified_message').innerHTML = 'The shipping address fields have been updated with:';
    document.getElementById('verified_message').style.color = '';
    document.getElementById('verified_message').style.display = '';
}

// Create global namespace object that all future variables and functions should go in.
var software = {
    // Create object to allow us to base64 encode and decode content.
    Base64: {
        // private property
        _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = this._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output +
                this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

            }

            return output;
        },

        // public method for decoding
        decode : function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = this._utf8_decode(output);

            return output;

        },

        // private method for UTF-8 encoding
        _utf8_encode : function (string) {
            string = string.replace(/\r\n/g,"\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode : function (utftext) {
            var string = "";
            var i = 0;
            var c = c1 = c2 = 0;

            while ( i < utftext.length ) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                }
                else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                }
                else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }
    },

    // Create function to allow us to rot13 content.
    get_rot13: function(string) {
        // If there is a string, then rot13 it.
        if (string) {
            return string.replace(/[a-zA-Z]/g, function(c){
                return String.fromCharCode((c <= "Z" ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
            });

        // Otherwise there is not a string, so return empty string.
        } else {
            return '';
        }
    },

    // Create a new HTML escaping function with a shorter name,
    // that is probably faster than prepare_content_for_html().
    h: function(content) {
        content = content.replace(/&/g, '&amp;');
        content = content.replace(/</g, '&lt;');
        content = content.replace(/>/g, '&gt;');
        content = content.replace(/"/g, '&quot;');

        return content;
    },

    init_accordions: function(selector) {
        if (typeof selector === 'undefined') {
            selector = 'ul.list-accordion, ol.list-accordion';
        }

        // Loop through all accordion unordered and ordered lists in order to prepare accordion effect.
        software_$(selector).each(function() {
            // Store list in variable because we will reference it multiple times.
            var list = software_$(this);

            // If accordion has already been enabled for this list, then skip to the next list.
            // This is necessary because on the catalog detail screen,
            // we dynamically load full description and etc. and then enable tabs,
            // however the default enable tabs process for a page also tries to enable
            // tabs, so tabs were being enabled twice before we added this check.
            if (list.hasClass('ui-accordion')) {
                return true;
            }

            // Loop through all list items in order to prepare them.
            list.children('li').each(function() {
                // Store list item in variable because we will reference it multiple times.
                var li = software_$(this);

                // Store anchor in variable before we remove it, because we will need to add it back later.
                var anchor = li.find('a:first');

                // Remove anchor so we can add container div.
                anchor.remove();

                // Remove line break that might exist at the beginning of the list item content.
                li.find(':first').filter('br').remove();

                // Wrap a container div around the list item content. We have to do this here,
                // because the jQuery accordion plugin requires it, and TinyMCE does not allow someone to add a div easily.
                li.wrapInner('<div class="item_content" />');

                // Add class to anchor so that jQuery will know which anchor is the heading.
                // This is necessary in order to support links in the item content.
                anchor.addClass('item_heading');

                // Add the anchor back to the beginning of the list item content.
                li.prepend(anchor);
            });
            
            // Add jQuery accordion effect to list.
            list.accordion({
                // Expand list item where the anchor class has a special class,
                // in order to expand a list item by default when the page first loads.
                active: '.list-accordion-expanded',

                // allow list item to be collapsed
                collapsible: true,

                // Don't reserve extra height for the tallest list item.
                // The page contents below accordion will move.
                autoHeight: false,

                // Set the class for headings so jQuery knows which anchors are the headings.
                // We have to do this in order to support links in the item content.
                header: 'a.item_heading'
            });
        });
    },

    // Warn visitor if they entered a comment and then attempt to browse away
    // from page before adding comment.
    init_add_comment: function(properties) {

        var comment_label = properties.comment_label;

        var add_comment_textarea = software_$('.add_comment_form textarea');

        // Once the visitor enters some text in the textarea,
        // then deal with this.
        add_comment_textarea.one('keyup', function() {

            var submitted = false;

            // If the add comment form is submitted, then remember that,
            // so the warning is not shown in that case.
            software_$('.add_comment_form').submit(function() {
                submitted = true;
                return true;
            });

            // When the visitor browses away from this page,
            // if the add comment form was not submitted,
            // and there is some text in the textarea, then show warning.
            software_$(window).on('beforeunload', function() {
                if (!submitted && add_comment_textarea.val()) {
                    return 'WARNING: If you leave this page, then your ' + comment_label + ' will NOT be added.';
                }
            });

        });

    },

    // Setup add comment publish pick list functionality and date/time picker.
    init_add_comment_publish: function() {
        var publish = software_$('#publish');
        var publish_date_and_time = software_$('#publish_date_and_time');
        var publish_schedule = software_$('.publish_schedule');

        // If the schedule option is selected by default when the page first loaded,
        // then show fields for it and init date/time picker.
        if (publish.val() == 'schedule') {
            publish_schedule.fadeIn();

            publish_date_and_time.datetimepicker({
                dateFormat: date_picker_format,
                timeFormat: "h:mm TT"
            });
        } 

        // When the publish pick list is changed, then update fields.
        publish.change(function() {
            // If the schedule option is selected, then show fields for it.
            if (publish.val() == 'schedule') {
                publish_schedule.fadeIn();

                publish_date_and_time.datetimepicker({
                    dateFormat: date_picker_format,
                    timeFormat: "h:mm TT"
                });

                // Place the focus in the date & time field,
                // so that the date/time picker automatically appears.
                publish_date_and_time.focus();

            // Otherwise the schedule option is not selected, so hide its fields.
            } else {
                publish_schedule.fadeOut();
            }
        });
    },

    init_auto_dialogs: function(properties) {
        var visit_length = properties.visit_length;
        var auto_dialogs = properties.auto_dialogs;
        var preview = properties.preview;

        // If this page is in an iframe, then it is likely that we don't
        // want to open auto dialogs for it, so return false.
        // For example, we don't want auto dialogs to appear in other auto dialogs.

        function in_iframe () {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
        }

        if (in_iframe()) {
            return false;
        }

        // If there is a class in the body tag that says to disable auto dialogs,
        // then abort and don't show auto dialogs on this page.
        if (software_$('body').hasClass('disable_auto_dialogs')) {
            return false;
        };

        software_$.each(auto_dialogs, function(index, auto_dialog) {
            // If there is a class in the body tag that says to specifically disable
            // this auto dialog then don't do anything for this auto dialog and skip to the next one.
            if (software_$('body').hasClass('disable_auto_dialog_' + auto_dialog.id)) {
                return true;
            };

            // If the visitor is previewing an auto dialog, then ignore the delay property
            // for the auto dialog, and just force the dialog to appear immediately.
            if (preview) {
                var delay = 0;

            // Otherwise the visitor is not previewing so calculate delay,
            // based on delay property for auto dialog and how long visitor has
            // been visiting site.
            } else {
                var delay = auto_dialog.delay - visit_length;
            }

            // If the auto dialog should be shown immediately, then set a minimum delay
            // of 1 second, because dialog position seems to be messed up, if opened too quickly.
            if (delay <= 0) {
                delay = 1;
            }

            // Convert the delay from seconds to milliseconds.
            delay = delay * 1000;

            // Open the dialog after the appropriate delay.
            setTimeout (function () {
                var dialog = {url: auto_dialog.url};

                var width = parseInt(auto_dialog.width);

                if (width) {
                    dialog.width = width;
                }

                var height = parseInt(auto_dialog.height);

                if (height) {
                    dialog.height = height;
                }

                // Add classes for both a general auto dialog and for this specific
                // auto dialog id so that this auto dialog can be targeted for styling.
                dialog.class_name = 'auto_dialog auto_dialog_' + auto_dialog.id;

                software.open_dialog(dialog);

                // If the visitor is not previewing an auto dialog, then store cookie,
                // to remember that the visitor saw this dialog and at what time.
                if (!preview) {
                    // If this is an old browser (e.g. IE 8), then create Date.now function,
                    // so that we can get the current timestamp below.
                    if (!Date.now) {
                        Date.now = function() { return new Date().getTime(); }
                    }

                    var current_timestamp = Math.floor(Date.now() / 1000);

                    document.cookie = 'software[auto_dialog_' + auto_dialog.id + ']=' + current_timestamp + '; expires=Tue, 01 Jan 2030 06:00:00 GMT; path=/';
                }

            }, delay);
        });
    },

    // Used by express order to allow visitor to copy shipping info to billing info

    init_billing_same_as_shipping: function(properties) {

        var id = properties.id;

        software_$('#billing_same_as_shipping').change(function () {

            if (!this.checked) {
                return;
            }

            software_$('[name=billing_salutation]').val(
                software_$('[name=shipping_' + id + '_salutation]').val());

            software_$('[name=billing_first_name]').val(
                software_$('[name=shipping_' + id + '_first_name]').val());

            software_$('[name=billing_last_name]').val(
                software_$('[name=shipping_' + id + '_last_name]').val());

            software_$('[name=billing_company]').val(
                software_$('[name=shipping_' + id + '_company]').val());

            software_$('[name=billing_address_1]').val(
                software_$('[name=shipping_' + id + '_address_1]').val());

            software_$('[name=billing_address_2]').val(
                software_$('[name=shipping_' + id + '_address_2]').val());

            software_$('[name=billing_city]').val(
                software_$('[name=shipping_' + id + '_city]').val());

            software_$('[name=billing_country]').val(
                software_$('[name=shipping_' + id + '_country]').val());

            // Trigger change event for country field so that separate functions will run that
            // will decide if state field needs to be a pick list or text box
            software_$('[name=billing_country]').trigger('change');

            software_$('[name=billing_state]').val(
                software_$('[name=shipping_' + id + '_state]').val());

            software_$('[name=billing_zip_code]').val(
                software_$('[name=shipping_' + id + '_zip_code]').val());

            software_$('[name=billing_phone_number]').val(
                software_$('[name=shipping_' + id + '_phone_number]').val());
        });

    },

    // Deals with updating state field pick list options or text box field when a country is
    // selected based on whether country has states or not.  It also updates zip code to be required
    // or not based on the country.

    init_country: function(properties) {

        var country_id = properties.country_id;
        var state_text_box_id = properties.state_text_box_id;
        var state_pick_list_id = properties.state_pick_list_id;
        var zip_code_id = properties.zip_code_id;
        
        var countries = software.countries;

        var first_time = true;

        var country_pick_list = software_$('#' + country_id);
        var state_text_box_label = software_$('label[for="' + state_text_box_id + '"]');
        var state_text_box = software_$('#' + state_text_box_id);
        var state_pick_list_label = software_$('label[for="' + state_pick_list_id + '"]');
        var state_pick_list = software_$('#' + state_pick_list_id);
        var state_field_name = state_text_box.attr('name');
        var zip_code_text_box = software_$('#' + zip_code_id);
        var zip_code_required = software_$('#' + zip_code_id + '_required');

        // When the user changes the country pick list, then update state and zip code.
        country_pick_list.change(function() {

            var selected_country = country_pick_list.val();

            // If a country has been selected and the selected country has states,
            // then show pick list for the state.
            if (selected_country && countries[selected_country].states) {

                state_pick_list.empty();

                state_pick_list.append('<option value=""></option>');

                // Loop through the states in order to add them to the pick list.
                software_$.each(countries[selected_country].states, function(index, state) {
                    state_pick_list.append('<option value="' + software.h(state.code) + '">' + software.h(state.name) + '</option>');
                });

                // If this is the first time this function is running during
                // the initial page load, then select the option in the pick list
                // based on the value in the text box.
                if (first_time) {
                    state_pick_list.val(state_text_box.val());
                }

                // Update the field names so that the correct value will be submitted.
                state_text_box.attr('name', '');
                state_pick_list.attr('name', state_field_name);

                // If the country is required, then require the state.
                if (country_pick_list.attr('required')) {
                    state_pick_list.attr('required', true);
                }

                state_text_box_label.fadeOut(function() {
                    state_pick_list_label.fadeIn();
                });
                
                state_text_box.fadeOut(function() {
                    state_pick_list.fadeIn();
                });

            // Otherwise the selected country does not have states, so show text box field.
            } else {

                // If this is not the first time this function has run,
                // then clear the state text box value.
                if (!first_time) {
                    state_text_box.val('');
                }

                // Update the field names so that the correct value will be submitted.
                state_text_box.attr('name', state_field_name);
                state_pick_list.attr('name', '');

                state_pick_list.attr('required', false);

                state_pick_list_label.fadeOut(function() {
                    state_text_box_label.fadeIn();
                });

                state_pick_list.fadeOut(function() {
                    state_text_box.fadeIn();
                });
            }

            // If the country is required and the visitor has selected a country and the country
            // requires a zip, then make zip code field required and fade in required content
            // (e.g. asterisk).
            if (
                country_pick_list.attr('required')
                && selected_country
                && countries[selected_country].zip == 1
            ) {
                zip_code_text_box.attr('required', true);
                zip_code_required.fadeIn();

            // Otherwise make field optional and fade out required content.
            } else {
                zip_code_text_box.attr('required', false);
                zip_code_required.fadeOut();
            }

            first_time = false;
            
        });

        // Trigger a change event so the fields will be updated during initial page load.
        country_pick_list.trigger('change');
    },

    // Updates the currency pick list so that it automatically submits
    // the update currency form when the pick list is changed.

    init_currency: function() {
        software_$('#currency_id').change(function() {
            this.form.submit();
        });
    },

    // Setup a custom arrival date field that appears on a shipping address & arrival page.
    init_custom_arrival_date: function(properties) {

        var radio_field_id = properties.radio_field_id;
        var date_field_id = properties.date_field_id;

        // Init the date-picker.
        software_$('#' + date_field_id).datepicker({
            dateFormat: date_picker_format
        });

        // When the radio button for this arrival date is selected,
        // then place the focus in the custom arrival date field,
        // so that the date-picker automatically appears.
        software_$('#' + radio_field_id).click(function() {
            if (software_$(this).attr('checked') == 'checked') {
                software_$('#' + date_field_id).focus();
            }
        });

        // When the custom date field value is changed,
        // then, if the field contains something, select the radio button
        // for this arrival date, because the visitor might forget to select it.
        software_$('#' + date_field_id).change(function() {
            if (software_$.trim(software_$(this).val()).length != 0) {
                software_$('#' + radio_field_id).attr('checked', 'checked');
            }
        });
    },

    // Show the list of contact groups when the opt-in check box is checked.
    init_email_preferences: function() {

        var contact_groups = software_$('.contact_groups');

        if (contact_groups.length) {

            var opt_in = software_$('input[name=opt_in]');

            opt_in.change(function() {

                if (opt_in.is(':checked')) {
                    contact_groups.fadeIn();
                } else {
                    contact_groups.fadeOut();
                }

            });

            // Trigger a change event so the fields will be updated during initial page load.
            opt_in.trigger('change');
        }

    },

    init_menu: function(properties) {
        var name = properties.name;
        var effect = properties.effect;
        var first_level_popup_position = properties.first_level_popup_position;
        var second_level_popup_position = properties.second_level_popup_position;

        var root_ul = software_$('#software_menu_' + name);

        // If this menu already has the requested effect, then do nothing and exit this function.
        // We do this check to improve performance, in case someone has added custom code for a responsive design
        // that calls this function when browser is resized.
        if (root_ul.hasClass('software_menu_' + effect) == true) {
            return;
        }

        // Remove all event listeners that might have already been added previously.
        root_ul.find('*').off();

        if (effect == 'popup') {
            // Update the CSS for all sub-menus so that the display is none and they are ready to be visible.
            // We have to do this because there might be incorrect or old CSS in the theme that sets visibility
            // to hidden, which causes the jQuery animations to not work.
            software_$('#software_menu_' + name + ' ul').css({
                'position': 'absolute',
                'display': 'none',
                'visibility': 'visible'
            });
            
            // display all popup menu items inline block
            software_$('#software_menu_' + name + ' li').css({'display': 'inline-block'});

            // Add event listeners to all li's for this menu.
            software_$('#software_menu_' + name + ' li').hover(
                // Add event listener for when a visitor hovers over this li.
                function () {
                    // Add on class to this li.
                    software_$(this).addClass('on');

                    // Add on class to the anchor under this li.
                    software_$(this).children('a').addClass('on');

                    // Store ul in variable because we will use it in several places below.
                    var ul = software_$(this).children('ul');

                    // If this menu item has a sub-menu, then prepare to show and animate sub-menu.
                    if (ul.length != 0) {
                        // We are going to update the position of the sub-menu before the delay
                        // in order to make sure the position is ready before the animation runs.

                        // If this li is a top level item, then set position to first level position.
                        if (software_$(this).hasClass('top_level') == true) {
                            var position = first_level_popup_position;

                        // Otherwise this li is a sub level item, so set position to second level position
                        } else {
                            var position = second_level_popup_position;
                        }

                        // Set the position of the ul differently based on the position setting for the menu.
                        switch (position) {
                            case 'top':
                                ul.css({
                                    'left': '0',
                                    'top': '-' + ul.outerHeight() + 'px'
                                });
                                break
                                    
                            case 'bottom':
                                ul.css({
                                    'left': '0',
                                    'top': software_$(this).outerHeight() + 'px'
                                });
                                break
                                    
                            case 'left':
                                ul.css({
                                    'left': '-' + ul.outerWidth() + 'px',
                                    'top': '0'
                                });
                                break;
                                
                            case 'right':
                                ul.css({
                                    'left': software_$(this).outerWidth() + 'px',
                                    'top': '0'
                                });
                                break;
                        }

                        // Store this li in a variable so we can access it in the setTimeout function below.
                        var li = this;

                        // Show and animate menu after a certain period of time.
                        // We add a delay in order to improve the UX and avoid flickering issues.
                        setTimeout (function () {
                            // If the visitor is still hovered over this li, then continue to show and animate sub-menu.
                            // We add this check in order to make sure that the visitor has not hovered off of this li,
                            // since the event was triggered.
                            if (software_$(li).hasClass('on') == true) {
                                // Update z-index for this li.  This is the only place that the z-index for pop-up menus is set.
                                // We have to put the z-index on the li and not the ul that is actually popping up,
                                // because IE 7 requires it.  Otherwise, IE 7 shows the pop-up menu behind other things (e.g. ad region).
                                software_$(li).css({
                                    'z-index': '4'
                                });

                                // Show and animate ul.
                                ul.slideDown(200);
                            }
                        }, 100);
                    }
                },
                
                // Add event listener for when a visitor hovers off this li.
                function () {
                    // Remove on class from this li.
                    software_$(this).removeClass('on');

                    // Remove on class from the anchor under this li.
                    software_$(this).children('a').removeClass('on');

                    // Store ul in variable because we will use it in several places below.
                    var ul = software_$(this).children('ul');

                    // If this menu item has a sub-menu, then prepare to hide and animate sub-menu.
                    if (ul.length != 0) {
                        // Store this li in a variable so we can access it in the setTimeout function below.
                        var li = this;

                        // Hide and animate menu after a certain period of time.
                        // We add a delay in order to improve the UX and avoid flickering issues.
                        setTimeout (function () {
                            // If the visitor is still hovered off of this li, then continue to hide and animate sub-menu.
                            // We add this check in order to make sure that the visitor has not hovered back on this li,
                            // since the event was triggered.
                            if (software_$(li).hasClass('on') == false) {
                                // Remove z-index because pop-up will no longer appear.
                                software_$(li).css({
                                    'z-index': '0'
                                });

                                ul.slideUp(200);
                            }
                        }, 250);
                    }
                }
            );

            // Add focus event listeners to all anchors for this menu.
            // This allows someone to tab into the menu for 508 compliance.
            software_$('#software_menu_' + name + ' li a').focus(function () {
                var li = software_$(this).parent();

                // Add on class to li above this anchor.
                li.addClass('on');

                // Add on class to this anchor.
                software_$(this).addClass('on');

                // Store ul in variable because we will use it in several places below.
                var ul = li.children('ul');

                // If this menu item has a sub-menu, then prepare to show and animate sub-menu.
                if (ul.length != 0) {
                    // We are going to update the position of the sub-menu before the delay
                    // in order to make sure the position is ready before the animation runs.

                    // If this li is a top level item, then set position to first level position.
                    if (li.hasClass('top_level') == true) {
                        var position = first_level_popup_position;

                    // Otherwise this li is a sub level item, so set position to second level position
                    } else {
                        var position = second_level_popup_position;
                    }

                    // Set the position of the ul differently based on the position setting for the menu.
                    switch (position) {
                        case 'top':
                            ul.css({
                                'left': '0',
                                'top': '-' + ul.outerHeight() + 'px'
                            });
                            break
                                
                        case 'bottom':
                            ul.css({
                                'left': '0',
                                'top': li.outerHeight() + 'px'
                            });
                            break
                                
                        case 'left':
                            ul.css({
                                'left': '-' + ul.outerWidth() + 'px',
                                'top': '0'
                            });
                            break;
                            
                        case 'right':
                            ul.css({
                                'left': li.outerWidth() + 'px',
                                'top': '0'
                            });
                            break;
                    }

                    // Show and animate menu after a certain period of time.
                    // We add a delay in order to improve the UX and avoid flickering issues.
                    setTimeout (function () {
                        // If the visitor is still hovered over this li, then continue to show and animate sub-menu.
                        // We add this check in order to make sure that the visitor has not hovered off of this li,
                        // since the event was triggered.
                        if (li.hasClass('on') == true) {
                            // Update z-index for this li.  This is the only place that the z-index for pop-up menus is set.
                            // We have to put the z-index on the li and not the ul that is actually popping up,
                            // because IE 7 requires it.  Otherwise, IE 7 shows the pop-up menu behind other things (e.g. ad region).
                            li.css({
                                'z-index': '4'
                            });

                            // Show and animate ul.
                            ul.slideDown(200);
                        }
                    }, 100);
                }
            });
            
            // Add focusout event listeners to all li's for this menu.
            // This allows someone to tab into/out of the menu for 508 compliance.
            // We use focusout instead of blur because if a visitor tabs out
            // of a deep child anchor, then we need events to fire for all parent li's
            // in order to determine if they need to be closed.
            software_$('#software_menu_' + name + ' li').focusout(function () {
                var li = software_$(this);

                // We found that we had to wrap all of this code in a setTimeout
                // in order to add a very slight delay so that when we check if any child items
                // are in focus below, the browser has had time to move the focus
                // to the next item and determine what is now in focus. We seem
                // to be able to get away with setting the delay to 0.  That appears
                // to add the tiny delay that we need for the focus check below to work.
                setTimeout (function () {
                    // If there are not any children of this li now in focus,
                    // then that means that the visitor has tabbed out to another menu item
                    // or outside the menu entirely, so update this li to have an off status.
                    if (li.find(':focus').length == 0) {
                        // Remove on class from this li.
                        li.removeClass('on');

                        // Remove on class from the anchor under this li.
                        li.children('a').removeClass('on');

                        // Store ul in variable because we will use it in several places below.
                        var ul = li.children('ul');

                        // If this menu item has a sub-menu, then prepare to hide and animate sub-menu.
                        if (ul.length != 0) {
                            // Hide and animate menu after a certain period of time.
                            // We add a delay in order to improve the UX and avoid flickering issues.
                            setTimeout (function () {
                                // If the visitor is still hovered off of this li, then continue to hide and animate sub-menu.
                                // We add this check in order to make sure that the visitor has not hovered back on this li,
                                // since the event was triggered.
                                if (li.hasClass('on') == false) {
                                    // Remove z-index because pop-up will no longer appear.
                                    li.css({
                                        'z-index': '0'
                                    });

                                    ul.slideUp(200);
                                }
                            }, 250);
                        }
                    }
                }, 0);
            });

            // Remove accordion class (if one exists) and
            // add class to mark that this menu is a popup menu,
            // so that if this function is run again, we know whether
            // or not the popup effect has already been added, which helps performance.
            root_ul.removeClass('software_menu_accordion');
            root_ul.addClass('software_menu_popup');

        } else if (effect == 'accordion') {
            // Hide all sub-menus by default and reset their positions.
            software_$('#software_menu_' + name + ' ul').css({
                'display': 'none',
                'position': 'static',
                'top': '0',
                'left': '0'
            });

            // display all accordion menu items as block
            software_$('#software_menu_' + name + ' li').css({'display': 'block'});

            // Loop through all the menu items in order to add click handler to ones
            // that contain a submenu.
            software_$('#software_menu_' + name + ' li:has(ul) > a').click(function(e) {
                // Disable link in case menu item has a link to another page,
                // because this menu item has a sub menu that needs to be shown when clicked.
                e.preventDefault();

                var anchor = software_$(this);

                var li = anchor.parent();

                // Remember whether this menu item was expanded or not,
                // before we possibly collapse it below (if it was expanded),
                // so later we know whether we need to expand it or not.
                var expanded = li.hasClass('expanded');

                // Get the first old expanded menu item that does not contain the menu item
                // that was clicked, so that we can collapse it (one might not exist).
                // This could even be the menu item that was clicked.
                var old_expanded_li = software_$('#software_menu_' + name + ' li.expanded:not(:has(#' + li.attr('id') + '))').first();

                // If an old expanded menu item was found, then collapse it.
                if (old_expanded_li.length) {
                    // Slide the old expanded menu item up, and after that finishes,
                    // collapse children menu items under it and remove expanded classes.
                    old_expanded_li.children('ul').slideUp('fast', function() {
                        old_expanded_li.find('ul').hide();
                        old_expanded_li.removeClass('expanded');
                        old_expanded_li.find('li').removeClass('expanded');
                        old_expanded_li.find('a').removeClass('expanded');
                    });
                }

                // If this menu item was not expanded before the visitor clicked it,
                // then expand it and add classes.
                if (expanded == false) {
                    li.children('ul').slideDown('fast', function() {
                        li.addClass('expanded');
                        anchor.addClass('expanded');
                    });
                }
            });
            
            // Expand to the menu item for the page that the visitor is currently on.
            software_$('#software_menu_' + name + ' li:has(ul):has(.current)').each(function() {
                var li = software_$(this);

                li.children('ul').slideDown('fast', function() {
                    li.addClass('expanded');
                    li.children('a').addClass('expanded');
                });
            });

            // Remove popup class (if one exists) and
            // add class to mark that this menu is an accordion menu,
            // so that if this function is run again, we know whether
            // or not the accordion effect has already been added, which helps performance.
            root_ul.removeClass('software_menu_popup');
            root_ul.addClass('software_menu_accordion');
        }
    },

    // Create a function that will prepare the search feature for a form list view.
    init_form_list_view: function (properties) {

        var page_id = properties.page_id;
        var dynamic_fields = properties.dynamic_fields;

        var browse_pick_list = software_$('#' + page_id + '_browse_field_id');
        var simple_query = software_$('#'+ page_id + '_query');
        var advanced_status_hidden_field = software_$('#'+ page_id + '_advanced');
        var advanced_toggle = software_$('.software_form_list_view .page_' + page_id + ' .advanced_toggle');
        var advanced_toggle_expand_label = software_$('.software_form_list_view .page_' + page_id + ' .advanced_toggle .expand_label');
        var advanced_toggle_collapse_label = software_$('.software_form_list_view .page_' + page_id + ' .advanced_toggle .collapse_label');
        var advanced = software_$('.software_form_list_view .page_' + page_id + ' .advanced');

        // If the browse pick list exists, then browse is enabled so initialize browse features.
        if (browse_pick_list.length) {
            // Remember the currently selected browse field id, so that we can close the correct filter container when it changes.
            // This value might change.
            var browse_field_id = software_$('#' + page_id + '_browse_field_id option:selected').val();

            // Remember the original browse field id value when the page first loaded,
            // so later we know if we need to submit the form/refresh the page when the browse toggle is clicked.
            // This value will not change.
            var original_browse_field_id = browse_field_id;

            var browse_toggle = software_$('.software_form_list_view .page_' + page_id + ' .browse_toggle');

            // Complete tasks when visitor changes the browse pick list selection.
            browse_pick_list.change(function () {
                var new_browse_field_id = software_$('#' + page_id + '_browse_field_id option:selected').val();

                // If search is enabled, then clear search field.
                if (simple_query.length) {
                    simple_query[0].value = '';

                    // Add default class so that text in field can appear lighter.
                    simple_query.addClass('default');
                }

                // If advanced search is enabled and it is expanded, then collapse it.
                if (
                    (advanced_toggle.length)
                    && (advanced_status_hidden_field[0].value == 'true')
                ) {
                    // Update advanced status hidden field so that when the form is submitted
                    // we can know that the advanced search was collapsed.
                    advanced_status_hidden_field[0].value = 'false';

                    advanced.slideUp('slow', function () {
                        // Remove advanced_expanded class from search container.
                        software_$('.software_form_list_view .page_' + page_id).removeClass('advanced_expanded');

                        // Update label for toggle.
                        advanced_toggle_collapse_label.hide();
                        advanced_toggle_expand_label.show();

                        // Update title for toggle.
                        advanced_toggle[0].title = 'Add Advanced Search';

                        // If there is a new browse field selected, then show browse toggle.
                        if (new_browse_field_id != '') {
                            browse_toggle.show();

                        // Otherwise there is not a new browse field selected, so hide browse toggle.
                        } else {
                            browse_toggle.hide();
                        }

                        // If there was a browse field selected before, then hide filter container for it.
                        if (browse_field_id != '') {
                            software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + browse_field_id).slideUp('slow', function () {
                                // If there is a new browse field selected, then show filter container for it.
                                if (new_browse_field_id != '') {
                                    software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + new_browse_field_id).slideDown('slow');
                                    software_$('.software_form_list_view .page_' + page_id).addClass('browse_expanded');
                                } else {
                                    software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');
                                }
                            });

                        } else {
                            // If there is a new browse field selected, then show filter container for it.
                            if (new_browse_field_id != '') {
                                software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + new_browse_field_id).slideDown('slow');
                                software_$('.software_form_list_view .page_' + page_id).addClass('browse_expanded');
                            } else {
                                software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');
                            }
                        }

                        browse_field_id = new_browse_field_id;
                    });

                } else {
                    // If there is a new browse field selected, then show browse toggle.
                    if (new_browse_field_id != '') {
                        browse_toggle.show();

                    // Otherwise there is not a new browse field selected, so hide browse toggle.
                    } else {
                        browse_toggle.hide();
                    }

                    // If there was a browse field selected before, then hide filter container for it.
                    if (browse_field_id != '') {
                        software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + browse_field_id).slideUp('slow', function () {
                            // If there is a new browse field selected, then show filter container for it.
                            if (new_browse_field_id != '') {
                                software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + new_browse_field_id).slideDown('slow');
                                software_$('.software_form_list_view .page_' + page_id).addClass('browse_expanded');
                            } else {
                                software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');

                                // If the browse was expanded when the page originally loaded
                                // then submit form in order to refresh page because results might be different
                                // without browse.  This also changes the address in the address bar
                                // so if the visitor browses away and comes back, the system will remember
                                // that the browse is collapsed.  This also allows a visitor to copy address
                                // in address bar and send to someone else and have the results be the same.
                                if (original_browse_field_id != '') {
                                    software_$('.software_form_list_view .browse_and_search_form.page_' + page_id).submit();
                                }
                            }
                        });

                    } else {
                        // If there is a new browse field selected, then show filter container for it.
                        if (new_browse_field_id != '') {
                            software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + new_browse_field_id).slideDown('slow');
                            software_$('.software_form_list_view .page_' + page_id).addClass('browse_expanded');
                        } else {
                            software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');
                        }
                    }

                    browse_field_id = new_browse_field_id;
                }
            });
            
            // Deactivate browse field when toggle is clicked.
            browse_toggle.click(function () {
                // Hide toggle.
                browse_toggle.hide();

                // Set browse pick list to default value.
                browse_pick_list.val('');

                // Slide filter container up and then remove browse expanded class.
                software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + browse_field_id).slideUp('slow', function () {
                    software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');

                    // If the browse was expanded when the page originally loaded
                    // then submit form in order to refresh page because results might be different
                    // without browse.  This also changes the address in the address bar
                    // so if the visitor browses away and comes back, the system will remember
                    // that the browse is collapsed.  This also allows a visitor to copy address
                    // in address bar and send to someone else and have the results be the same.
                    if (original_browse_field_id != '') {
                        software_$('.software_form_list_view .browse_and_search_form.page_' + page_id).submit();
                    }
                });

                browse_field_id = '';

                // Prevent link from going anywhere.
                return false;
            });
        }

        // If search is enabled, then prepare search.
        if (simple_query.length) {
            // If the simple query field does not have a value, then add class.
            if (simple_query[0].value == '') {
                // Add default class so that text in field can appear lighter.
                simple_query.addClass('default');
            };

            // Set simple query field so that the default value is removed when focus is placed on the field.
            simple_query[0].onfocus = function () {
                // If browse is enabled and browse is active, then deactivate it.
                if ((browse_pick_list.length) && (browse_field_id != '')) {
                    
                    browse_toggle.hide();

                    // Set browse pick list to default value.
                    browse_pick_list.val('');

                    // Slide filter container up and then remove browse expanded class.
                    software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + browse_field_id).slideUp('slow', function () {
                        software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');
                    });

                    // Remember that browse is inactive.
                    browse_field_id = '';
                }

                if (simple_query[0].value == '') {
                    // Remove default class so that text in field can appear darker now that default text is removed.
                    simple_query.removeClass('default');
                }
            };

            // Set simple query field so that the default value is restored when focus is removed from the field and the field is empty.
            simple_query[0].onblur = function () {
                if (simple_query[0].value == '') {
                    // Add default class so that text in field can appear lighter.
                    simple_query.addClass('default');
                }
            };

            // If the advanced toggle exists, then prepare it.
            if (advanced_toggle.length) {
                // Assume that the advanced search is not expanded until we find out otherwise.
                // We use this later in order to determine if we need to submit the form.
                var original_advanced_expanded = false;

                // If the advanced search is expanded, then remember that.
                if (advanced_status_hidden_field[0].value == 'true') {
                    original_advanced_expanded = true;
                }

                advanced_toggle.click(function () {
                    // If the advanced search is collapsed, then expand it.
                    if (advanced_status_hidden_field[0].value == 'false') {
                        // Update advanced status hidden field so that when the form is submitted
                        // we can know that the advanced search was expanded.
                        advanced_status_hidden_field[0].value = 'true';

                        // If browse is enabled and browse is active, then deactivate it and then activate advanced search.
                        if ((browse_pick_list.length) && (browse_field_id != '')) {
                            
                            browse_toggle.hide();

                            // Set browse pick list to default value.
                            browse_pick_list.val('');

                            // Slide filter container up and then remove browse expanded class and activate advanced search.
                            software_$('.software_form_list_view .page_' + page_id + ' .browse_filter_container.field_' + browse_field_id).slideUp('slow', function () {
                                software_$('.software_form_list_view .page_' + page_id).removeClass('browse_expanded');

                                // Add advanced_expanded class to search container.
                                software_$('.software_form_list_view .page_' + page_id).addClass('advanced_expanded');

                                // Update label for toggle.
                                advanced_toggle_expand_label.hide();
                                advanced_toggle_collapse_label.show();

                                // Update title for toggle.
                                advanced_toggle[0].title = 'Remove Advanced Search';

                                advanced.slideDown('slow');
                            });

                            // Remember that browse is inactive.
                            browse_field_id = '';

                        // Otherwise browse is not enabled or not active, so just deal with advanced search.
                        } else {
                            // Add advanced_expanded class to search container.
                            software_$('.software_form_list_view .page_' + page_id).addClass('advanced_expanded');

                            // Update label for toggle.
                            advanced_toggle_expand_label.hide();
                            advanced_toggle_collapse_label.show();

                            // Update title for toggle.
                            advanced_toggle[0].title = 'Remove Advanced Search';

                            advanced.slideDown('slow');
                        }

                    // Otherwise the advanced search is expanded, so collapse it.
                    } else {
                        // Update advanced status hidden field so that when the form is submitted
                        // we can know that the advanced search was collapsed.
                        advanced_status_hidden_field[0].value = 'false';

                        // Update label for toggle.
                        advanced_toggle_collapse_label.hide();
                        advanced_toggle_expand_label.show();

                        // Update title for toggle.
                        advanced_toggle[0].title = 'Add Advanced Search';

                        advanced.slideUp('slow', function () {
                            // Remove advanced_expanded class from search container.
                            software_$('.software_form_list_view .page_' + page_id).removeClass('advanced_expanded');

                            // If the advanced search was expanded when the page originally loaded
                            // then submit form in order to refresh page because results might be different
                            // without advanced search.  This also changes the address in the address bar
                            // so if the visitor browses away and comes back, the system will remember
                            // that the advanced search is collapsed.  This also allows a visitor to copy address
                            // in address bar and send to someone else and have the search be the same.
                            if (original_advanced_expanded == true) {
                                software_$('.software_form_list_view .browse_and_search_form.page_' + page_id).submit();
                            }
                        });
                    }

                    // Prevent link from going anywhere.
                    return false;
                });

                // If there are advanced search dynamic fields, then update them
                // so they only contain options that are still relevant.
                if (!software_$.isEmptyObject(dynamic_fields)) {

                    // Update all dynamic field pick lists when page is loaded.
                    update_dynamic_fields();

                    // Set dynamic fields so they are updated when they are changed.
                    software_$('.software_form_list_view .page_' + page_id + ' .dynamic').change(function() {
                        update_dynamic_fields();
                    });

                    function update_dynamic_fields() {

                        // Figure out which fields have a selected option.
                        var selected_fields = [];

                        software_$.each(dynamic_fields, function(index, field) {

                            var selected_filter = software_$('.software_form_list_view .page_' + page_id + ' #' + field.html_field_name).val();

                            // If a pick list could not be found for the dynamic
                            // field (e.g. missing in custom layout) or if the filter
                            // has not been selected for this field, then skip to the
                            // next field.
                            if (
                                (typeof selected_filter === 'undefined')
                                || (selected_filter == '')
                            ) {
                                return true;
                            }

                            // Get the forms that match this selected attribute.

                            var matched_forms = [];

                            // Loop through the filters for this field in order
                            // to find the forms for the selected filter.
                            software_$.each(field.filters, function(index, filter) {

                                // If this is the selected filter, then store forms.
                                if (filter.name == selected_filter) {

                                    matched_forms = filter.forms.slice();

                                    // Break out of loop because we found the forms.
                                    return false;
                                }

                            });

                            selected_fields.push({
                                name: field.name,
                                html_field_name: field.html_field_name,
                                filter: selected_filter,
                                matched_forms: matched_forms});

                        });

                        // Loop through all fields in order to determine which
                        // filters are valid based on which fields are selected,
                        // and determine if the field should be shown or hidden.
                        software_$.each(dynamic_fields, function(index, field) {

                            var filters = [];

                            // If there are no selected fields, or if this is the only selected field,
                            // then all of the filters are valid.
                            if (
                                (selected_fields.length == 0)
                                || ((selected_fields.length == 1) && (selected_fields[0].name == field.name))
                            ) {

                                filters = field.filters.slice();

                            // Otherwise there are selected fields that are not this field,
                            // so get all of the forms that are valid for those fields,
                            // in order to figure out which filters are valid for this field.
                            } else {

                                var matched_forms = [];

                                var first_field = true;

                                // Loop through the selected fields in order to find matched forms.
                                software_$.each(selected_fields, function(index, selected_field) {

                                    // If this field is the current field that we are dealing with,
                                    // then skip to the next field.  We don't care about the forms for
                                    // this field that we are dealing with.
                                    if (selected_field.name == field.name) {
                                        return true;
                                    }

                                    // If this is the first selected field, then set the matched forms
                                    // to all of the matched forms for this selected field.
                                    if (first_field) {

                                        matched_forms = selected_field.matched_forms.slice();

                                    // Otherwise, this is not the first field,
                                    // so determine which forms are matched forms.
                                    } else {

                                        // Loop through all of the forms that we have so far,
                                        // and see if they are also a matched form for this field.
                                        var index = matched_forms.length;

                                        // Go through the array backwards, so if we remove an item
                                        // while in the loop, it does not cause a problem to the loop.
                                        while (index--) {

                                            var form = matched_forms[index];

                                            var match = false;

                                            // Loop through the matched forms for this selected field,
                                            // in order to see if the form also appears in that array.
                                            software_$.each(selected_field.matched_forms, function(index, form_2) {
                                                // If we found the form in this selected field's matched forms
                                                // then remember that and break out of the loop.
                                                if (form == form_2) {
                                                    match = true;
                                                    return false;
                                                }
                                            });

                                            // If a match was not found, then the form is not a matched form,
                                            // so remove it from the array.
                                            if (match == false) {
                                                matched_forms.splice(index, 1);
                                            }

                                        }
                                    }

                                    first_field = false;

                                });

                                // Loop through the filters in order to determine if the filter
                                // is valid for any of the matched forms.
                                software_$.each(field.filters, function(index, filter) {

                                    // Loop through the forms for this filter, to determine
                                    // if form is a matched form.
                                    software_$.each(filter.forms, function(index, form){

                                        // If this form is a matched form, then this filter
                                        // is valid for the matched forms, so add
                                        // it to filters array and break out of the form loop.
                                        if (software_$.inArray(form, matched_forms) != -1){
                                            filters.push(filter);
                                            return false;
                                        }

                                    });

                                });

                            }

                            // If there are valid filters, then update attribute pick list filters,
                            // and show the attribute row.
                            if (filters.length) {

                                var pick_list = software_$('.software_form_list_view .page_' + page_id + ' #' + field.html_field_name);

                                var value = pick_list.val();

                                pick_list.empty();

                                var clear = software_$('.software_form_list_view .page_' + page_id + ' .' + field.html_field_name + '_clear');

                                // If a filter is not selected, or no clear button
                                // was found, then add blank option.  A clear button
                                // might not be found if a custom layout was used and
                                // the designer chose not to include a clear button.
                                if ((value == '') || !clear.length) {
                                    pick_list.append('<option value=""></option>');
                                }

                                // Loop through the filters in order to add them to the pick list.
                                software_$.each(filters, function(index, filter) {
                                    pick_list.append('<option value="' + software.h(filter.name) + '">' + software.h(filter.name) + '</option>');
                                });

                                // Select the filter that was previously selected before we updated the filters.
                                pick_list.val(value);

                                // If a clear button was found, then deal with it.
                                // A clear button might not be found if a custom layout
                                // was used and the designer chose not to include
                                // a clear button.
                                if (clear.length) {

                                    // Hide clear button and unbind any click events,
                                    // until we find out if it is necessary to show it.
                                    clear.hide();
                                    clear.unbind('click');

                                    // If a filter is selected then set what should
                                    // happen when it is clicked and show it.
                                    if (value != '') {

                                        // When the user clicks the clear button,
                                        // then reset the pick list.
                                        clear.click(function() {

                                            pick_list.prepend('<option value=""></option>');
                                            pick_list.val('');

                                            // A selection has changed so trigger
                                            // change event so all pick lists are
                                            // updated again.
                                            pick_list.trigger('change');

                                        });

                                        clear.show();
                                    }

                                }

                                software_$('.software_form_list_view .page_' + page_id + ' .' + field.html_field_name + '_row').fadeIn(400, function() {

                                    // Have to show it when done fading in for
                                    // some reason (maybe related to advanced
                                    // being expanded or collapsed).
                                    $(this).show();

                                });

                            // Otherwise, there are not any valid filters, so hide dynamic field row.
                            } else {

                                // Have to hide it when done fading out for
                                // some reason (maybe related to advanced
                                // being expanded or collapsed).
                                software_$('.software_form_list_view .page_' + page_id + ' .' + field.html_field_name + '_row').fadeOut(400, function() {
                                    $(this).hide();
                                });

                            }

                        });

                    }

                }

            }
        }
    },

    init_payment_method: function() {

        var card_number = software_$('input[name=card_number]');

        var payment_card = false;

        // If there is a card number field, then prepare payment card fields
        if (card_number.length) {

            // Remember that there are payment card fields for later.
            payment_card = true;

            var expiration = software_$('input[name=expiration]');
            var cardholder = software_$('input[name=cardholder]');
            var card_verification_number = software_$('input[name=card_verification_number]');
        }

        // Store purchase now button label in custom data attribute,
        // so that we can retrieve it in case the label gets changed for PayPal.

        var purchase_now_button = software_$('.purchase_button');

        // If the purchase button is an input button, then get label from the value attribute.
        if (purchase_now_button.is('input')) {
            var label = purchase_now_button.val();

        // Otherwise it should be a button element, so get html inside button element.
        } else {
            var label = purchase_now_button.html();
        }

        purchase_now_button.attr('data-label', label);

        function update() {

            // First check for a value from a hidden payment method field, in case there are no
            // radio buttons because there is only one payment method.
            var payment_method = software_$('input[type=hidden][name=payment_method]').val();

            // If a hidden value was not found, then there are multiple payment methods with radio
            // buttons, so get value for selected radio button.
            if (!payment_method) {
                payment_method = software_$('input[type=radio][name=payment_method]:checked').val();
            }

            if (payment_method == 'Credit/Debit Card') {

                software_$('.credit_debit_card').slideDown();

                card_number.prop('required', true);
                expiration.prop('required', true);

                if (cardholder.length) {
                    cardholder.prop('required', true);
                }

                if (card_verification_number.length) {
                    card_verification_number.prop('required', true);
                }

                // If a surcharge is enabled, then add it.
                if (software_$('.surcharge_row').length) {

                    // Hide total row that does not include surcharge.
                    software_$('.total_row').slideUp();

                    // Show surcharge row and total row that includes surcharge.
                    software_$('.surcharge_row').slideDown();
                    software_$('.surcharge_total_row').slideDown();
                }

            } else {

                software_$('.credit_debit_card').slideUp();

                card_number.prop('required', false);
                expiration.prop('required', false);

                if (cardholder.length) {
                    cardholder.prop('required', false);
                }

                if (card_verification_number.length) {
                    card_verification_number.prop('required', false);
                }

                // If a surcharge is enabled, then remove it.
                if (software_$('.surcharge_row').length) {

                    // Hide surcharge row and total row that includes surcharge.
                    software_$('.surcharge_row').slideUp();
                    software_$('.surcharge_total_row').slideUp();

                    // Show total row that does not include surcharge.
                    software_$('.total_row').slideDown();
                }
            }

            if (payment_method == 'PayPal Express Checkout') {
                var label = purchase_now_button.attr('data-paypal-label')
            } else {
                var label = purchase_now_button.attr('data-label');
            }

            // If the purchase button is an input button, then set label by setting value.
            if (purchase_now_button.is('input')) {
                purchase_now_button.val(label);

            // Otherwise it should be a button element, so set label by updating inner html.
            } else {
                purchase_now_button.html(label);
            }
        }

        // Add click event to all payment method radio buttons so update is run.
        software_$('input[type=radio][name=payment_method]').click(function () {
            update();
        });

        // Update for initial page load.
        update();

        // If there are payment card fields then use jQuery.payment library to enhance fields.
        if (payment_card) {

            card_number.payment('formatCardNumber');

            if (expiration.length) {

                expiration.payment('formatCardExpiry');

                // jQuery.payment adds spaces before and after the slash in the expiration.
                // This causes an issue where Chrome won't offer to save the payment card info,
                // because the spaces confuse Chrome. Therefore, once the form is submitted, remove
                // the spaces, so Chrome won't see them.

                var form = expiration.closest('form');

                form.submit(function() {
                    var expiration_value = expiration.val();
                    expiration_value = expiration_value.replace(/\s+/g, '');
                    expiration.val(expiration_value);
                });
            }

            if (card_verification_number.length) {
                card_verification_number.payment('formatCardCVC');
            }
        }
    },

    init_product_attributes: function(properties) {

        // Hide various content that was outputted by the server, as soon as possible, because we
        // are going to fade in the proper content once we figure out the matched products.
        // The ":first" part prevents updating other content areas, like cross-sell.
        // The "not cross-sell" is necessary for the image, because there might not be a .image
        // for the main item, so the first image might be the cross-sell, which we don't want to
        // update.
        software_$('.software_catalog_detail .image:first').not('.cross-sell .image').hide();
        software_$('.software_catalog_detail .short_description:first').html('').hide();
        software_$('.software_catalog_detail .price:first').html('').hide();
        software_$('.software_catalog_detail .full_description').html('').hide();
        software_$('.software_catalog_detail .details').html('').hide();
        software_$('.software_catalog_detail .code').html('').hide();
        
        var attributes = properties.attributes;
        var products = properties.products;
        var default_image_name = properties.default_image_name;
        var default_short_description = properties.default_short_description;
        var default_full_description = properties.default_full_description;
        var default_details = properties.default_details;
        var default_code = properties.default_code;
        var discounted_product_prices = properties.discounted_product_prices;
        var visitor_currency_symbol = properties.visitor_currency_symbol;
        var visitor_currency_exchange_rate = properties.visitor_currency_exchange_rate;
        var visitor_currency_code_for_output = properties.visitor_currency_code_for_output;

        var current_product_id = 0;
        var current_image_name = '';
        var current_short_description = '';
        var current_price = '';
        var current_full_description = '';
        var current_details = '';
        var current_code = '';

        // Loop through the attributes to prepare pick list or buttons.
        software_$.each(attributes, function(index, attribute) {

            // Check if there is a pick list for this attribute.
            var pick_list = software_$('.attribute_' + attribute.id + ' select');

            // If there is a pick list, then prepare it.
            if (pick_list.length) {

                // Update when the pick list is changed.
                pick_list.change(function() {

                    // Remember whether the user has unselected the pick list or not for this
                    // attribute. We do this so later we will know whether we should change the
                    // default selected option when there is only one option or not.

                    // If the user specifically unselected the pick list,
                    // then remember that.
                    if (pick_list.val() == '') {
                        attributes[index].unselected = true;

                    // Otherwise the user did not unselect the pick list,
                    // so remember that.
                    } else {
                        attributes[index].unselected = false;
                    }

                    update();
                });

            // Otherwise there is not a pick list, so prepare option buttons.
            } else {

                // Loop through the options for this attribute in order to prepare option buttons.
                software_$.each(attribute.options, function(option_index, option) {

                    var option_button = software_$('.option_' + option.id);

                    // When the customer clicks this option button, then update option and attributes.
                    option_button.click(function() {

                        // If the option button is already selected, then deselect it.
                        if (option_button.hasClass('selected')) {

                            option_button.removeClass('selected');
                            software_$('[name=attribute_' + attribute.id + ']').val('');
                            attributes[index].unselected = true;

                        // Otherwise the option button is not already selected, so select it.
                        } else {

                            option_button.addClass('selected');
                            software_$('[name=attribute_' + attribute.id + ']').val(option.id);
                            attributes[index].unselected = false;
                        }

                        update();
                    });
                });
            }
        });
        
        // Update all pick lists for the first time that this function is run.
        update();

        function update(number_of_updates) {

            number_of_updates = number_of_updates || 1;

            // Figure out which attributes have a selected option.
            var selected_attributes = [];

            software_$.each(attributes, function(index, attribute) {
                // If this attribute is not currently shown,
                // then skip to the next attribute.
                if (attribute.enabled == false) {
                    return true;
                }

                var selected_option = software_$('[name=attribute_' + attribute.id + ']').val();

                // If an option has not been selected for this attribute,
                // then skip to the next attribute.
                if (selected_option == '') {
                    return true;
                }

                // Assume the selected option is not a no value option,
                // until we find out otherwise.
                var no_value = 0;

                // Loop through the options for this attribute,
                // in order to determine if selected option is a no value option.
                software_$.each(attribute.options, function(index, option) {
                    // If this is the selected option, then continue.
                    if (option.id == selected_option) {
                        // If this is a no value option, then remember that.
                        if (option.no_value == 1) {
                            no_value = 1;
                        }

                        // Break out of loop because we found the option.
                        return false;
                    }
                });

                // Get the products that match this selected attribute.

                var matched_products = [];

                // Loop through the products to determine if product matches this attribute.
                software_$.each(products, function(index, product) {
                    var match = false;
                    var attribute_exists = false;

                    // Loop through the product's attributes to determine
                    // if this product matches the selected attribute.
                    software_$.each(product.attributes, function(index, product_attribute) {
                        if (product_attribute.id == attribute.id) {
                            // Remember that the attribute exists for this product,
                            // because we might need to know this later,
                            // when dealing with the no value feature.
                            attribute_exists = true;

                            // If this product has an attribute option that matches
                            // the selected attribute option, then remember that
                            // and break out of this loop.
                            if (product_attribute.option_id == selected_option) {
                                match = true;

                                // Break out of the loop
                                return false;
                            }
                        }
                    });

                    // If there was no attribute match, and the attribute did not exist
                    // for the product, but the selected attribute option is a no value option
                    // (e.g. "No Thanks", then consider that an attribute match.
                    if (
                        (match == false)
                        && (attribute_exists == false)
                        && (no_value == 1)
                    ) {
                        match = true;
                    }

                    if (match == true) {
                        matched_products.push(product);
                    }
                });

                selected_attributes.push({
                    id: attribute.id,
                    option_id: selected_option,
                    no_value: no_value,
                    matched_products: matched_products});
            });

            var matched_products = [];

            // If there are no selected attributes, then all products match,
            // because none have been filtered.
            if (selected_attributes.length == 0) {
                matched_products = products.slice();

            // Otherwise there is at least one selected attribute,
            // so figure out which are the matched products from all of the selected attributes.
            } else {
                var first_attribute = true;

                software_$.each(selected_attributes, function(index, selected_attribute) {
                    // If this is the first selected attribute, then set the matched products
                    // to all of the matched products for this selected attribute.
                    if (first_attribute == true) {
                        matched_products = selected_attribute.matched_products.slice();

                    // Otherwise, this is not the first attribute,
                    // so determine which products are matched products.
                    } else {
                        // Loop through all of the products that we have so far,
                        // and see if they are also a matched product for this attribute.
                        var index = matched_products.length;

                        // Go through the array backwards, so if we remove an item
                        // while in the loop, it does not cause a problem to the loop.
                        while (index--) {
                            var product = matched_products[index];

                            var match = false;

                            // Loop through the matched products for this selected attribute,
                            // in order to see if the product also appears in that array.
                            software_$.each(selected_attribute.matched_products, function(index, product_2) {
                                // If we found the product in this selected attribute's matched products
                                // then remember that and break out of the loop.
                                if (product.id == product_2.id) {
                                    match = true;
                                    return false;
                                }
                            });

                            // If a match was not found, then the product is not a matched product,
                            // so remove it from the array.
                            if (match == false) {
                                matched_products.splice(index, 1);
                            }
                        }
                    }

                    first_attribute = false;
                });
            }

            // Prepare variable that we will use to keep track of whether
            // the state of an attribute changed, so we will need to update again.
            var update_again = false;

            // Loop through all attributes in order to determine which options are valid
            // based on which attributes are selected, and determine if the attribute
            // should be shown or hidden.
            software_$.each(attributes, function(index, attribute) {

                var options = [];

                // If there are no selected attributes, or if this is the only selected attribute,
                // then all of the options are valid.
                if (
                    (selected_attributes.length == 0)
                    || ((selected_attributes.length == 1) && (selected_attributes[0].id == attribute.id))
                ) {
                    options = attribute.options.slice();

                // Otherwise there are selected attributes that are not this attribute,
                // so get all of the products that are valid for those attributes,
                // in order to figure out which options are valid for this attribute.
                } else {
                    var matched_products = [];

                    var first_attribute = true;

                    // Loop through the selected attributes in order to find matched products.
                    software_$.each(selected_attributes, function(index, selected_attribute) {
                        // If this attribute is the current attribute that we are dealing with,
                        // then skip to the next attribute.  We don't care about the products for
                        // this attribute that we are dealing with.
                        if (selected_attribute.id == attribute.id) {
                            return true;
                        }

                        // If this is the first selected attribute, then set the matched products
                        // to all of the matched products for this selected attribute.
                        if (first_attribute == true) {
                            matched_products = selected_attribute.matched_products.slice();

                        // Otherwise, this is not the first attribute,
                        // so determine which products are matched products.
                        } else {
                            // Loop through all of the products that we have so far,
                            // and see if they are also a matched product for this attribute.
                            var index = matched_products.length;

                            // Go through the array backwards, so if we remove an item
                            // while in the loop, it does not cause a problem to the loop.
                            while (index--) {
                                var product = matched_products[index];

                                var match = false;

                                // Loop through the matched products for this selected attribute,
                                // in order to see if the product also appears in that array.
                                software_$.each(selected_attribute.matched_products, function(index, product_2) {
                                    // If we found the product in this selected attribute's matched products
                                    // then remember that and break out of the loop.
                                    if (product.id == product_2.id) {
                                        match = true;
                                        return false;
                                    }
                                });

                                // If a match was not found, then the product is not a matched product,
                                // so remove it from the array.
                                if (match == false) {
                                    matched_products.splice(index, 1);
                                }
                            }
                        }

                        first_attribute = false;
                    });
                    
                    options = [];

                    // Assume there is not a real option, until we find out otherwise.
                    // A real option is an option that is not a no value option.
                    // We don't want to show a pick list with only no value options.
                    var real_option = false;

                    // Loop through the options in order to determine if the option
                    // is valid for any of the matched products.
                    software_$.each(attribute.options, function(index, option) {
                        // Loop through the matched products in order to determine
                        // if there is at least one matched product that has this attribute.
                        software_$.each(matched_products, function(index, product) {
                            var match = false;

                            // If this is a "no thanks" option, then check if product
                            // matches this option in a certain way.
                            if (option.no_value == 1) {
                                // Assume that this product does not have a real option,
                                // for this attribute, until we find out otherwise.
                                var product_has_real_option = false;

                                // Loop through the product's attributes to determine,
                                // if this product matches this option.
                                software_$.each(product.attributes, function(index, product_attribute) {
                                    // If this product has this "no thanks" option
                                    // then we know this product matches, so remember that
                                    // and break out of the attribute loop.
                                    if (product_attribute.option_id == option.id) {
                                        match = true;
                                        return false;
                                    }

                                    // If this attribute matches the attribute for
                                    // this "no thanks" option, then we know
                                    // that this product matches a real option,
                                    // so remember that for later.
                                    if (product_attribute.id == attribute.id) {
                                        product_has_real_option = true;
                                    }
                                });

                                // If this product does not have this "no thanks" option
                                // specifically set, however the product does not have a real option
                                // set for this attribute, then it still matches.
                                if (
                                    (match == false)
                                    && (product_has_real_option == false)
                                ) {
                                    match = true;
                                }

                            // Otherwise, this is NOT a "no thanks" option, so check
                            // in a different way.
                            } else {
                                // Loop through the product's attributes to determine
                                // if this product matches the selected attribute.
                                software_$.each(product.attributes, function(index, product_attribute) {
                                    // If this product has an attribute option that matches
                                    // this option then remember that and break out of this loop.
                                    if (product_attribute.option_id == option.id) {
                                        match = true;
                                        return false;
                                    }
                                });
                            }

                            // If this product matches this option, then we know we want
                            // to include this option, so we don't need to loop through
                            // any more products, so break out of loop.
                            if (match == true) {
                                options.push(option);

                                // If this is a real option (i.e. not a "no thanks" option)
                                // then remember that for later, because we only want
                                // to show a pick list if there is at least one real option.
                                if (option.no_value != 1) {
                                    real_option = true;
                                }

                                return false;
                            }
                        });
                    });

                    // If there are only no value options, then clear the array,
                    // because we don't want to show the pick list in that case.
                    if (real_option == false) {
                        options = [];
                    }
                }

                var attribute_enabled_before = attribute.enabled;

                // If there are valid options, then update attribute pick list options,
                // and show the attribute row.
                if (options.length > 0) {

                    attributes[index].enabled = true;

                    // Get the field that stores the selected option which is either the pick list
                    // of options or the hidden field for buttons.
                    var option_field = software_$('[name=attribute_' + attribute.id + ']');

                    // If the attribute was disabled before, then we don't care
                    // what was previously selected, so just set the value to blank.
                    if (attribute_enabled_before == false) {
                        var value = '';

                    // Otherwise the attribute was enabled before, so we do care
                    // what the previously value was, so store it, so we can set it later.
                    } else {
                        var value = option_field.val();
                    }

                    var default_option_exists = false;
                    var selected_option_exists = false;

                    // Loop through the options in order to determine if default option exists
                    // and if a selected option exists.
                    software_$.each(options, function(index, option) {
                        if (option.id == attribute.default_option_id) {
                            default_option_exists = true;
                        }

                        if (option.id == value) {
                            selected_option_exists = true;
                        }
                    });

                    var pick_list = software_$('.attribute_' + attribute.id + ' select');

                    if (pick_list.length) {

                        pick_list.empty();

                        // If a selected option does not exist and a default option does not exist,
                        // then add a blank option.
                        if ((selected_option_exists == false) && (default_option_exists == false)) {
                            pick_list.append('<option value=""></option>');
                        }

                        // Loop through the options in order to add them to the pick list.
                        software_$.each(options, function(index, option) {
                            pick_list.append('<option value="' + option.id + '">' + software.h(option.label) + '</option>');
                        });

                        // Select the option that was previously selected before we updated the options.
                        pick_list.val(value);

                        var clear = software_$('.attribute_' + attribute.id + ' .clear');

                        // Hide clear button unbind any click events,
                        // until we find out if it is necessary to show it.
                        clear.hide();
                        clear.unbind('click');

                        // If an option is selected that is not the blank option or the default option,
                        // then show clear button.
                        if (
                            (value != '')
                            && (value != attribute.default_option_id)
                        ) {
                            // When the user clicks the clear button, then reset the pick list.
                            clear.click(function() {
                                // If the default option exists in the pick list, then select that option.
                                if (default_option_exists == true) {
                                    pick_list.val(attribute.default_option_id);

                                // Otherwise the default option does not exist in the pick list,
                                // so add a blank option and select it.
                                } else {
                                    pick_list.prepend('<option value=""></option>');
                                    pick_list.val('');
                                }

                                // An attribute selection has changed so trigger change event
                                // so all pick lists are updated again.
                                pick_list.trigger('change');
                            });

                            // Show clear button.
                            clear.show();
                        }

                    // Otherwise there must be buttons for options instead of a pick list.
                    } else {

                        // Loop through all options to determine which should be shown or hidden.
                        software_$.each(attribute.options, function(index, option) {

                            var option_button = software_$('.option_' + option.id);

                            // If this is one of the valid options, then show option.
                            if (software.find(options, 'id', option.id)) {

                                // If this is the option that should be selected, then select it.
                                if (option.id == value) {
                                    option_button.addClass('selected');
                                } else {
                                    option_button.removeClass('selected');
                                }

                                option_button.fadeIn();

                            // Otherwise this option is not valid right now, so hide it.
                            } else {
                                option_button.fadeOut();
                            }
                        });

                        // Store the value in the hidden field.
                        option_field.val(value);
                    }

                    software_$('.attribute_' + attribute.id + '.attribute_row').fadeIn();

                    // If there is only one option, and it is not already selected,
                    // and the user has not specifically unselected the pick list recently,
                    // then select it by default, and remember that we need to update again.
                    if (
                        (options.length == 1)
                        && (value != options[0].id)
                        && (attribute.unselected == false)
                    ) {

                        if (pick_list.length) {
                            pick_list.val(options[0].id);

                        } else {
                            software_$('.option_' + options[0].id).addClass('selected');
                            option_field.val(options[0].id);
                        }

                        update_again = true;

                    // Otherwise if there is no selected option, and there is a default option,
                    // then select that option by default, and remember that we need to update again.
                    } else if (
                        (selected_option_exists == false)
                        && (default_option_exists == true)
                    ) {

                        if (pick_list.length) {
                            pick_list.val(attribute.default_option_id);

                        } else {
                            software_$('.option_' + attribute.default_option_id).addClass('selected');
                            option_field.val(attribute.default_option_id);
                        }

                        update_again = true;
                    }

                // Otherwise, there are not any valid options, so hide attribute row.
                } else {
                    attributes[index].enabled = false;

                    software_$('.attribute_' + attribute.id + '.attribute_row').fadeOut();
                }

                if (attribute_enabled_before != attributes[index].enabled) {
                    update_again = true;
                }
            });

            // If the state of an attribute was changed, and we have not
            // already updated too many times, then update everthing again,
            // and exit this function.  We added the number of updates check
            // because it is possible for an admin to setup default options for
            // attributes that conflict with each other which caused endless recursion.
            if ((update_again == true) && (number_of_updates < 5)) {
                update(number_of_updates + 1);
                return;
            }

            // If there is one matched product, then update hidden product field
            // so when the form is submitted, we know which product was matched.
            if (matched_products.length == 1) {
                software_$('.product_id').val(matched_products[0].id);

            // Otherwise one product has not been matched yet, so clear hidden product
            // field value.
            } else {
                software_$('.product_id').val('');
            }

            var image = software_$('.software_catalog_detail .image:first').not('.cross-sell .image');

            // If there is an image in the content, then determine if we should update it.
            if (image.length) {

                // If there is only one matched product, and that product has an image name,
                // then use that product's image name.
                if (
                    (matched_products.length == 1)
                    && (matched_products[0].image_name != '')
                ) {
                    var image_name = matched_products[0].image_name;

                // Otherwise use the default image name for the product group.
                } else {
                    var image_name = default_image_name;
                }

                // If the new image name is different from the current image name,
                // then update the image.
                if (image_name != current_image_name) {

                    // If an image name was found, then fade out the current image,
                    // update the source, and then fade in the new image.
                    if (image_name != '') {
                        image.fadeOut(function() {
                            image.attr('src', software_path + encodeURI(image_name));
                        }).fadeIn();

                    // Otherwise there is no image name, so hide the image.
                    } else {
                        image.fadeOut();
                    }

                    current_image_name = image_name;

                }
            }

            var short_description_container = software_$('.software_catalog_detail .short_description:first');

            // If there is a short description container in the content, then determine if we should update it.
            if (short_description_container.length) {

                // If there is only one matched product, and that product has a short description,
                // then use that product's short description.
                if (
                    (matched_products.length == 1)
                    && (matched_products[0].short_description != '')
                ) {
                    var short_description = matched_products[0].short_description;

                // Otherwise use the default short description for the product group.
                } else {
                    var short_description = default_short_description;
                }

                // If the new short description is different from the current short description,
                // then update the short description.
                if (short_description != current_short_description) {

                    // If a short description was found, then fade out the current short description,
                    // update the content, and then fade in the new short description.
                    if (short_description != '') {
                        short_description_container.fadeOut(function() {
                            short_description_container.html(software.h(short_description));
                            short_description_container.trigger('content_change');
                        }).fadeIn();

                    // Otherwise there is no short description, so hide it
                    // and then clear the content.
                    } else {
                        short_description_container.fadeOut(function() {
                            short_description_container.html('');
                            short_description_container.trigger('content_change');
                        });
                    }

                    current_short_description = short_description;

                }
            }

            // If there is only one matched product, and it is different from the
            // current matched product, then get product info.
            if (
                (matched_products.length == 1)
                && (matched_products[0].id != current_product_id)
            ) {
                var product = matched_products[0];

                // Use AJAX to get various product info.
                software_$.ajax({
                    contentType: 'application/json',
                    url: software_path + software_directory + '/api.php',
                    data: JSON.stringify({
                        action: 'get_product',
                        product: {id: product.id}
                    }),
                    type: 'POST',
                    success: function(response) {
                        product = response.product;

                        // If there is a full description for the product, then use it.
                        if ((product.full_description != '') && (product.full_description != '<p></p>')) {
                            var full_description = product.full_description;

                        // Otherwise the product does not have a full description so use the default.
                        } else  {
                            var full_description = default_full_description;
                        }

                        // if the inventory is enabled for the product,
                        // and the product is out of stock,
                        // and there is an out of stock message
                        // then append out of stock message to full description
                        if (
                            (product.inventory == 1)
                            && (product.inventory_quantity == 0)
                            && (product.out_of_stock_message != '')
                            && (product.out_of_stock_message != '<p></p>')
                        ) {
                            full_description += ' ' + product.out_of_stock_message;
                        }

                        full_description = full_description.replace(/{path}/g, software_path);

                        // If the full description is different from the current full description,
                        // then update the full description.
                        if (full_description != current_full_description) {
                            software_$('.software_catalog_detail .full_description').fadeOut(function() {
                                software_$(this).html(full_description);
                                software_$(this).trigger('content_change');
                                software.init_accordions('.software_catalog_detail .full_description ul.list-accordion, .software_catalog_detail .full_description ol.list-accordion');
                                software.init_tabs('.software_catalog_detail .full_description ul.list-tabs, .software_catalog_detail .full_description ol.list-tabs');
                            }).fadeIn();

                            current_full_description = full_description;
                        }

                        // If there is a details for the product, then use it.
                        if ((product.details != '') && (product.details != '<p></p>')) {
                            var details = product.details;

                        // Otherwise the product does not have a details so use the default.
                        } else  {
                            var details = default_details;
                        }

                        details = details.replace(/{path}/g, software_path);

                        // If the details is different from the current details,
                        // then update the details.
                        if (details != current_details) {
                            software_$('.software_catalog_detail .details').fadeOut(function() {
                                software_$(this).html(details);
                                software_$(this).trigger('content_change');
                                software.init_accordions('.software_catalog_detail .details ul.list-accordion, .software_catalog_detail .details ol.list-accordion');
                                software.init_tabs('.software_catalog_detail .details ul.list-tabs, .software_catalog_detail .details ol.list-tabs');
                            }).fadeIn();

                            current_details = details;
                        }

                        // If there is code for the product, then use it.
                        if (product.code != '') {
                            var code = product.code;

                        // Otherwise the product does not have code so use the default.
                        } else  {
                            var code = default_code;
                        }

                        code = code.replace(/{path}/g, software_path);

                        // If the code is different from the current code,
                        // then update the code.
                        if (code != current_code) {
                            software_$('.software_catalog_detail .code').fadeOut(function() {
                                software_$(this).html(code);
                                software_$(this).trigger('content_change');
                            }).fadeIn();

                            current_code = code;
                        }

                        // Remember that the full description, details, and code have been updated
                        // for this product, so we don't have to do it again for the same product.
                        current_product_id = product.id;
                    }
                });

            // Otherwise, if there is not one matched product, and this state is different
            // from last time, or this is the first time this function has run,
            // then update default values.
            } else if (
                (matched_products.length != 1)
                &&
                (
                    (current_product_id != 0)
                    ||
                    (
                        (current_full_description == '')
                        && (current_details == '')
                        && (current_code == '')
                    )
                )
            ) {
                var full_description = default_full_description;

                // If the full description is different from the current full description,
                // then update the full description.
                if (full_description != current_full_description) {
                    software_$('.software_catalog_detail .full_description').fadeOut(function() {
                        software_$(this).html(full_description);
                        software_$(this).trigger('content_change');
                        software.init_accordions('.software_catalog_detail .full_description ul.list-accordion, .software_catalog_detail .full_description ol.list-accordion');
                        software.init_tabs('.software_catalog_detail .full_description ul.list-tabs, .software_catalog_detail .full_description ol.list-tabs');
                    }).fadeIn();

                    current_full_description = full_description;
                }

                var details = default_details;

                // If the details is different from the current details,
                // then update the details.
                if (details != current_details) {
                    software_$('.software_catalog_detail .details').fadeOut(function() {
                        software_$(this).html(details);
                        software_$(this).trigger('content_change');
                        software.init_accordions('.software_catalog_detail .details ul.list-accordion, .software_catalog_detail .details ol.list-accordion');
                        software.init_tabs('.software_catalog_detail .details ul.list-tabs, .software_catalog_detail .details ol.list-tabs');
                    }).fadeIn();

                    current_details = details;
                }

                var code = default_code;

                // If the code is different from the current code,
                // then update the code.
                if (code != current_code) {
                    software_$('.software_catalog_detail .code').fadeOut(function() {
                        software_$(this).html(code);
                        software_$(this).trigger('content_change');
                    }).fadeIn();

                    current_code = code;
                }

                // Remember that the full description, details, and code have been updated
                // to the default, so we don't have to set the default values again.
                current_product_id = 0;
            }

            var output_price = '';

            // If there is at least one matched product, then get price.
            if (matched_products.length > 0) {
                var smallest_price = 0;
                var largest_price = 0;
                var first_product = true;
                var number_of_products = 0;
                var discounted_products_exist = false;
                var original_price = 0;

                // Loop through the matched products in order to calculate the new price range.
                software_$.each(matched_products, function(index, product) {
                    // If this is a donation product, then skip to next product.
                    if (product.selection_type == 'donation') {
                        return true;
                    }

                    number_of_products++;

                    var price = parseInt(product.price);

                    // If this product is discounted, then remember that.
                    if (discounted_product_prices[product.id]) {
                        discounted_products_exist = true;
                        original_price = price;
                        price = parseInt(discounted_product_prices[product.id]);
                    }

                    // If this is the first product,
                    // then set both the smallest and largest prices to this product's prices.
                    if (first_product == true) {
                        smallest_price = price;
                        largest_price = price;
                        first_product = false;

                    // Otherwise, this is not the first product,
                    // so determine if we should update prices.
                    } else {
                        if (price < smallest_price) {
                            smallest_price = price;
                        }

                        if (price > largest_price) {
                            largest_price = price;
                        }
                    }
                });

                // If there is at least one product that is not a donation product,
                // then get price.
                if (number_of_products > 0) {
                    // If the smallest and largest price is the same, then show one price.
                    if (smallest_price == largest_price) {
                        // If there is only one product and it is discounted,
                        // then show original price and discounted price.
                        if ((number_of_products == 1) && (discounted_products_exist == true)) {

                            var original_price = (original_price / 100 * visitor_currency_exchange_rate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                            var discounted_price = (smallest_price / 100 * visitor_currency_exchange_rate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

                            output_price =
                                '<span style="text-decoration: line-through; white-space: nowrap">' + visitor_currency_symbol + original_price + visitor_currency_code_for_output + '</span>\
                                <span style="white-space: nowrap" class="software_discounted_price">' + visitor_currency_symbol + discounted_price + visitor_currency_code_for_output + '</span>';
                            
                        // Otherwise there is more than one product or there are no discounted products,
                        // so prepare to just show original price.
                        } else {
                            var output_discount_class = '';

                            // If there is a discounted product, then add discount class.
                            if (discounted_products_exist == true) {
                                output_discount_class = ' class="software_discounted_price"';
                            }

                            var price = (smallest_price / 100 * visitor_currency_exchange_rate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

                            output_price = '<span style="white-space: nowrap"' + output_discount_class + '>' + visitor_currency_symbol + price + visitor_currency_code_for_output + '</span>';
                        }

                    // Otherwise the smallest and largest prices are different so show a range.
                    } else {
                        var output_discount_container_start = '';
                        var output_discount_container_end = '';

                        // If there is a discounted product, then output discount container.
                        if (discounted_products_exist == true) {
                            output_discount_container_start = '<span class="software_discounted_price">';
                            output_discount_container_end = '</span>';
                        }

                        var output_smallest_price = (smallest_price / 100 * visitor_currency_exchange_rate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                        var output_largest_price = (largest_price / 100 * visitor_currency_exchange_rate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

                        output_price =
                            output_discount_container_start +
                                '<span style="white-space: nowrap">' + visitor_currency_symbol + output_smallest_price + '</span> - \
                                <span style="white-space: nowrap">' + visitor_currency_symbol + output_largest_price + visitor_currency_code_for_output + '</span>' +
                            output_discount_container_end;
                    }
                }
            }

            var price_container = software_$('.software_catalog_detail .price:first');

            // If a price was found, then determine if we need to update price.
            if (output_price != '') {
                // If the price has changed then fade out current price and fade in new price.
                if (output_price != current_price) {
                    price_container.fadeOut(function() {
                        price_container.html(output_price);
                    }).fadeIn();

                    current_price = output_price;
                }

            // Otherwise there is no price info, so hide it, and remove content.
            } else {
                price_container.fadeOut(function() {
                    price_container.html('');
                });

                current_price = '';
            }

            // Store matched products globally and trigger event, so that cross-sell can update,
            // based on the new matched products from the customer's attribute selection.
            software.matched_products = matched_products;
            software_$('body').trigger('attribute_update');
    
            // If edit mode is enabled, then show attribute helper.
            if (software_$('.edit_mode').length) {
                // If the attribute helper does not exist yet, then create it.
                if (!software_$('.attribute_helper').length) {
                    var attribute_helper = software_$(
                        '<div class="attribute_helper" style="border: 2px #69A823 solid; margin: 2.5em .5em 1em .5em">\
                            <div class="title" style="background-color: #69A823; color: white; padding: .25em .5em">Attribute Helper: <span class="number_of_products"></span> &nbsp;<span class="arrow">&#9660;</span></div>\
                            <div class="products" style="display: none; background-color: white; padding: .25em .5em">\
                                <table style="width: 100%">\
                                    <tbody></tbody>\
                                </table>\
                            </div>\
                        </div>');

                    // Add the helper at the top of the catalog detail.
                    software_$('.software_catalog_detail').prepend(attribute_helper);

                    // Change the cursor to a pointer when the user hovers over the title.
                    software_$('.attribute_helper .title').css('cursor', 'pointer');

                    software_$('.attribute_helper .title').click(function() {
                        if (software_$('.attribute_helper .products').is(':visible')) {
                            software_$('.attribute_helper .products').slideUp();
                            software_$('.attribute_helper .arrow').html('&#9660;');

                        } else {
                            software_$('.attribute_helper .products').slideDown();
                            software_$('.attribute_helper .arrow').html('&#9650;');
                        }
                    });
                }

                var output_plural_suffix = '';

                if (matched_products.length != 1) {
                    output_plural_suffix = 's';
                }

                // Update the helper title so it contains the number of matched products.
                software_$('.attribute_helper .title .number_of_products').html(
                    matched_products.length + ' product' + output_plural_suffix + ' matched');

                // Clear the current list of products.
                software_$('.attribute_helper .products tbody').html('');

                // Loop through the matched products in order to add them to helper.
                software_$.each(matched_products, function(index, product) {
                    var output_product = software.h(product.name);

                    if (product.short_description != '') {
                        if (output_product != '') {
                            output_product += ' - ';
                        }

                        output_product += software.h(product.short_description);
                    }

                    // Add product to list of products.
                    software_$('.attribute_helper .products tbody').append(
                        '<tr class="product_' + product.id + '" style="color: #69A823; cursor: pointer">\
                            <td style="padding: .15em; text-align: left; vertical-align: middle">' + output_product + '</td>\
                            <td style="padding: .15em; text-align: left; vertical-align: middle">' + product.relative_time + '</td>\
                        </tr>');

                    // When a user clicks the product row, then send user to edit the product.
                    software_$('.attribute_helper .products .product_' + product.id).click(function() {
                        window.location.href = software_path + software_directory + '/edit_product.php?id=' + product.id + '&send_to=' + software.h(encodeURIComponent(window.location.pathname + window.location.search));
                    });
                });
            }
        }
    },

    init_quick_add: function() {

        software_$('#quick_add_product_id').change(function() {
            change_quick_add_product_id(this.options[this.selectedIndex].value);
        });

        // Remember the quantity so that we can set the quantity back.
        var quantity = software_$('#quick_add_quantity').val();

        software_$('#quick_add_product_id').trigger('change');

        // Set quantity back to what it was when page was originall loaded,
        // in case there was an error and we need to show the quantity that
        // the customer originally entered and not the default quantity
        // for the product.
        software_$('#quick_add_quantity').val(quantity);

    },

    init_share_comment: function(properties) {

        var comment_label = properties.comment_label;

        software_$('.comment .share').click(function () {

            var width = software_$(window).width() * .75;

            var id = software_$(this).attr('data-id');

            // Prepare URL by removing current fragment and adding comment fragment.
            var url = window.location.href.split('#')[0] + '#c-' + id;

            // Create unique id from number of milliseconds since epoch
            // so that multiple dialogs can exist at once.
            var date = new Date;
            var unique_id = date.getTime();

            var share_comment = software_$(
                '<div\
                    id="software_dialog_' + unique_id + '"\
                    style="display: none; padding: 1em"\
                >\
                    <div style="margin-bottom: 1em">\
                        <input type="text" style="width: 100%" value="' + software.h(url) + '">\
                    </div>\
                    \
                    <div>\
                        <button class="copy software_button_small_primary btn btn-sm btn-primary">\
                            Copy to Clipboard\
                        </button>&nbsp;\
                        <button class="cancel software_button_small_secondary btn btn-sm btn-default btn-secondary">\
                            Cancel\
                        </button>\
                    </div>\
                </div>');

            software_$('body').append(share_comment);

            // Open jQuery dialog.
            software_$('#software_dialog_' + unique_id).dialog({

                autoOpen: true,

                open: function() {

                    var text_box = software_$('.share_comment input');

                    text_box[0].select();

                    text_box.click(function () {
                        this.select();
                    });

                    var copy_button = software_$('.share_comment .copy');

                    copy_button.click(function () {

                        text_box[0].select();

                        document.execCommand('copy');

                        software_$('#software_dialog_' + unique_id).dialog('close');

                    });

                    var cancel_button = software_$('.share_comment .cancel');

                    cancel_button.click(function () {
                        software_$('#software_dialog_' + unique_id).dialog('close');
                    });

                    software_$('.ui-widget-overlay').bind('click', function() {
                        software_$('#software_dialog_' + unique_id).dialog('close');
                    });

                },

                close: function() {
                    // Remove iframe and iframe container.
                    software_$('#software_dialog_' + unique_id).remove();
                    software_$('#software_dialog_' + unique_id + '_container').remove();
                },

                dialogClass: 'software mobile_dialog share_comment',
                modal: true,
                title: 'Share ' + software.h(comment_label),
                width: width,
                show: {effect: 'fade'},
                hide: {effect: 'fade'}

            });

        });

    },

    // Used by express order to get shipping methods and show them for the address and arrival date
    // that the visitor entered.

    init_shipping: function(properties) {

        var ship_to_id = properties.ship_to_id;
        var prefix = properties.prefix;
        var selected_method_id = properties.selected_method_id;

        var message = software_$('#' + prefix + 'message');
        message.hide();

        var heading = software_$('#' + prefix + 'method_heading');
        heading.hide();

        var methods = software_$('#' + prefix + 'methods');
        methods.hide();

        // If the methods container is a table, then set the methods body to the tbody, so that we
        // can later easily remove all rows in the table without affecting the heading row, which
        // can be isolated in a thead.
        if (methods.is('table')) {
            var methods_body = software_$('tbody', methods);

        // Otherwise the methods container is some other type of element, like a div,
        // so set the methods body to the same methods container, because we don't need to worry
        // about preserving the heading row.
        } else {
            var methods_body = methods;
        }
        
        var row_content = software_$('.method_row', methods).prop('outerHTML');

        // Prepare a variable to remember the old request, so we can compare to the new request,
        // to determine if an API request is necessary.
        var old_request = '';

        function update_shipping_methods() {

            var address_1 = software_$('[name=' + prefix + 'address_1]').val();

            // Get state field, because we need to check later if it is required.
            var state_field = software_$('[name=' + prefix + 'state]');
            var state = state_field.val();
            
            // Get zip code field, because we need to check later if it is required.
            var zip_code_field = software_$('[name=' + prefix + 'zip_code]');
            var zip_code = zip_code_field.val();

            var country = software_$('[name=' + prefix + 'country]').val();

            // If any of the fields that affect shipping methods are not complete yet, then abort.
            // The special logic for state & zip is necessary because the state & zip is only
            // required for certain countries.
            if (
                !address_1
                || !country
                || (!state && state_field.prop('required'))
                || (!zip_code && zip_code_field.prop('required'))
            ) {
                return;
            }

            var arrival_date_id = 0
            var arrival_date = '';

            // If there are arrival dates then deal with them
            if (software_$('[name=' + prefix + 'arrival_date]').length) {

                arrival_date_id = software_$('[name=' + prefix + 'arrival_date]:checked').val();

                if (!arrival_date_id) {
                    return;
                }

                var custom_arrival_date_field =
                    software_$('[name=' + prefix + 'custom_arrival_date_' + arrival_date_id + ']');

                if (custom_arrival_date_field.length) {

                    arrival_date = custom_arrival_date_field.val();

                    if (!arrival_date) {
                        return;
                    }
                }
            }

            // Prepare data for API request.
            var request = JSON.stringify({
                action: 'get_shipping_methods',
                ship_to_id: ship_to_id,
                address_1: address_1,
                state: state,
                zip_code: zip_code,
                country: country,
                arrival_date_id: arrival_date_id,
                arrival_date: arrival_date});

            // If the new request is the same as the old request, then we don't need to do anything.
            if (request === old_request) {
                return;
            }

            old_request = request;

            // Send the address info and arrival date to the API in order to get shipping methods
            var get_shipping_methods = software_$.ajax({
                contentType: 'application/json',
                url: software_path + software_directory + '/api.php',
                data: request,
                type: 'POST'});

            get_shipping_methods.done(function(response) {

                message.fadeOut();
                heading.fadeOut();
                methods.fadeOut(function() {

                    if (response.status == 'error') {
                        message.html(software.h(response.message));
                        message.fadeIn();
                        return;
                    }

                    methods_body.empty();

                    var shipping_methods = response.shipping_methods;

                    software_$.each(shipping_methods, function(index, shipping_method) {

                        var row = software_$(row_content);

                        var radio_button = software_$('input', row);

                        radio_button.prop('name', prefix + 'method');
                        radio_button.prop('value', shipping_method.id);

                        // If this should be the selected method or there is only one method,
                        // then select this method.
                        if (
                            shipping_method.id == selected_method_id
                            || shipping_methods.length == 1
                        ) {
                            radio_button.prop('checked', true);
                            select_method(shipping_method.id);
                        }

                        radio_button.prop('required', true);

                        software_$('.name', row).html(software.h(shipping_method.name));
                        software_$('.cost', row).html(shipping_method.cost_info);
                        software_$('.description', row).html(software.h(shipping_method.description));

                        if (shipping_method.protected) {
                            row.addClass('protected');
                        }

                        // Hide delivery date container, so we don't show an empty container, until
                        // the delivery is requested further below.
                        software_$('.delivery_date', row).hide();

                        methods_body.append(row);

                        // If the arrival date is not "at once" or there is no service, or the
                        // service is UPS (we don't support real-time delivery date for UPS), then
                        // we are done (don't need to get delivery date), so move to next shipping
                        // method.
                        if (
                            (response.arrival_date != '0000-00-00')
                            || (!shipping_method.service)
                            || (shipping_method.service.substr(0, 3) == 'ups')
                        ) {
                            return true;
                        }

                        // Otherwise we need to get the delivery date.

                        var get_delivery_date = software_$.ajax({
                            contentType: 'application/json',
                            url: software_path + software_directory + '/api.php',
                            data: JSON.stringify({
                                action: 'get_delivery_date',
                                ship_to_id: ship_to_id,
                                shipping_method: {id: shipping_method.id},
                                zip_code: zip_code,
                                country: country,
                                only_from_carrier: true,
                                formatted: true}),
                            type: 'POST'});

                        get_delivery_date.done(function(response) {

                            // If there was a problem getting the delivery date, then just return
                            // and don't do anything.
                            if (!response.delivery_date_info) {
                                return;
                            }

                            // Fade out any content that designer has marked to be hidden after a
                            // delivery date is found.
                            software_$('.delivery_date_hide', row).fadeOut();

                            // Insert date into HTML.
                            software_$('.date', row).html(response.delivery_date_info);

                            // Fade in the delivery date container.
                            software_$('.delivery_date', row).fadeIn();
                        });
                    });

                    // When the visitor selects a shipping method, then remember that method
                    // for the future, so if the methods are updated, the selected method
                    // from the past can be auto-selected for the visitor.
                    software_$('[name=' + prefix + 'method]').change(function () {
                        selected_method_id = software_$('[name=' + prefix + 'method]:checked').val();
                        select_method(selected_method_id);
                    });

                    heading.fadeIn();
                    methods.fadeIn();

                    // Update shipping cost for recipient and then update totals.

                    function select_method(id) {

                        // If there is a gift card discount or surcharge, then we don't need to do
                        // anything because we don't currently support dynamically updating
                        // totals for those two features.
                        if (software.gift_card_discount || software.surcharge) {
                            return;
                        }

                        var method = software.find(shipping_methods, 'id', id);

                        software.recipients = software.recipients || {};
                        software.recipients[ship_to_id] = software.recipients[ship_to_id] || {};
                        software.recipients[ship_to_id].shipping_cost = method.cost;

                        update_totals();
                    }
                });
            });
        }

        // Update now for the first time when the page is first loaded
        update_shipping_methods();

        // When a field, that affects shipping methods, is changed, then update shipping methods.
        software_$(
            '[name=' + prefix + 'address_1],\
            #' + prefix + 'state_text_box,\
            #' + prefix + 'state_pick_list,\
            [name=' + prefix + 'zip_code],\
            [name=' + prefix + 'country],\
            [name=' + prefix + 'arrival_date],\
            [name^=' + prefix + 'custom_arrival_date]'
        ).change(update_shipping_methods);

        // JS only triggers a change event for text boxes when the user removes focus from the field.
        // A change event is not triggered as the user types or when the user is done typing. So,
        // we add a solution for them, using the input event, so that the methods are updated any
        // time a user types, pastes, deletes, and etc. in the field.  We only update after a 2
        // second delay of inactivity, when the user is done typing, so we don't update too often
        // for performance reasons.

        var typing_timer;

        software_$(
            '[name=' + prefix + 'address_1],\
            #' + prefix + 'state_text_box,\
            [name=' + prefix + 'zip_code],\
            [name^=' + prefix + 'custom_arrival_date]'
        ).on('input', function() {
            clearTimeout(typing_timer);
            typing_timer = setTimeout(update_shipping_methods, 2000);
        });

        // Update shipping and total in total area.

        function update_totals() {

            var shipping_container = software_$('.shipping');
            var total_container = software_$('.total');

            // If the shipping container or total container does not exist, then we don't need to
            // update totals.
            if (!shipping_container.length || !total_container.length) {
                return;
            }

            // Loop through the recipients in order to calculate the total shipping cost.

            var shipping = 0;

            software_$.each(software.recipients, function(index, recipient) {
                shipping += recipient.shipping_cost;
            });

            // If the total shipping cost is the same as before, then we don't need to do anything.
            if (shipping == software.shipping) {
                return;
            }

            // Fade out and then fade in new shipping total.

            software.shipping = shipping;

            shipping_container.fadeOut(function () {
                shipping_container.html(software.prepare_price({price: shipping}));
                shipping_container.fadeIn();
            });

            // Fade out and then fade in new total.

            var total = software.total_without_shipping + shipping;

            total_container.fadeOut(function () {
                total_container.html(software.prepare_price({price: total}));
                total_container.fadeIn();
            });

            // Fade out and then fade in new base currency total, if it exists.

            var base_currency_total_container = software_$('.base_currency_total');

            if (base_currency_total_container.length) {

                base_currency_total_container.fadeOut(function () {

                    base_currency_total_container.html(software.prepare_price({
                        symbol: software.base_currency_symbol,
                        price: total,
                        base_price: true,
                        code: software.base_currency_code}));

                    base_currency_total_container.fadeIn();
                });
            }

            // Update hidden field with total, so when the order is submitted, we can check if the
            // total has changed from what the customer saw, so we can alert customer.  We use
            // toFixed(2) in order to just include 2 decimal places because the total might be
            // a floating point number with many decimal places.
            software_$('input[name=total]').val(total.toFixed(2));
        }
    },

    tab_count: 0,

    init_tabs: function(selector) {
        if (typeof selector === 'undefined') {
            selector = 'ul.list-tabs, ol.list-tabs';
        }

        // Loop through all tab unordered and ordered lists in order to prepare tabs.
        software_$(selector).each(function() {
            // Store list in variable because we will reference it multiple times.
            var list = software_$(this);

            // If tabs have already been enabled for this list, then skip to the next list.
            // This is necessary because on the catalog detail screen,
            // we dynamically load full description and etc. and then enable tabs,
            // however the default enable tabs process for a page also tries to enable
            // tabs, so tabs were being enabled twice before we added this check.
            if (list.parent().hasClass('software_list_tabs_container')) {
                return true;
            }

            // Add a div around the list, because jQuery requires that for tabs.
            list.wrap('<div class="software_list_tabs_container" />');

            // Loop through all list items in order to prepare them.
            list.children('li').each(function() {
                // Store list item in variable because we will reference it multiple times.
                var li = software_$(this);

                // Store anchor in variable before we remove it, because we will need to add it back later.
                var anchor = li.find('a:first');

                // Remove anchor so we can isolate the rest of the content and move it into a div.
                anchor.remove();

                // Remove line break that might exist at the beginning of the list item content.
                li.find(':first').filter('br').remove();

                // Get item content without anchor. We will use this later.
                var item_content = li.html();

                // Remove all content from the list item.
                li.empty();

                // Increase the item count in order to have a unique ID for the item.
                software.tab_count++;

                // Prepare item id which will be used in anchor's href and the id of the item's div.
                var item_id = 'list_tab_item_' + software.tab_count;

                // Update href for anchor so that it will be connected to the item's div.
                anchor.attr('href', '#' + item_id);

                // Add anchor back into list item content. It will be the only content in the list item.
                li.append(anchor);

                // Prepare content for item's div.
                item_content = '<div id="' + item_id + '" class="item_content">' + item_content + '</div>';

                // Add div for item below list.
                list.parent().append(item_content);
            });
            
            // Add jQuery tab effect to list.
            list.parent().tabs();
        });
    },

    inline_editing: {
        cancel: function() {
            var length = software.inline_editing.regions.length;

            for (var i = 0; i < length; i++) {
                var container_id = software.inline_editing.regions[i].container_id;

                if (CKEDITOR.instances[container_id].checkDirty() == true) {
                    CKEDITOR.instances[container_id].setData(software.inline_editing.regions[i].content);
                    
                    CKEDITOR.instances[container_id].resetDirty();
                    

                    CKEDITOR.instances[container_id].once('change', function() {
                        software.inline_editing.show_buttons();
                    });
                }
            }

            // If there is an active confirmation, then remove it.
            if (software_$('#software_inline_editing_confirmation').length) {
                software_$('#software_inline_editing_confirmation').remove();
            }

            // Prepare cancel confirmation message.
            software_$('body').append('\
                <div id="software_inline_editing_confirmation" style="background-color: #edfced; border-bottom: 1px solid #428221; border-left: 1px solid #428221; border-right: 1px solid #428221; border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; color: #428221; display: none; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; left: 0; margin-left: auto; margin-right: auto; padding: .5em; position: fixed; right: 0; text-align: center; top: 0; width: 40%; z-index: 2147483647">\
                    <img src="' + software_path + software_directory + '/images/icon_notice.png" width="16" height="16" alt="Notice" title="" style="margin-right: 7px; margin-bottom: 2px; vertical-align: middle" /><span style="font-size: 14px; font-weight: bold">Notice:</span> <span style="font-size: 13px">Your updates have been <strong>canceled</strong>.</span>\
                </div>');

            // Slide down cancel confirmation message, wait a few seconds, slide it back up, and then remove it.
            software_$('#software_inline_editing_confirmation').slideDown('slow').delay(3000).slideUp('slow', function() {software_$(this).remove()});

            // Slide save & cancel buttons out of view, to the right.
            software_$('#software_inline_editing_buttons').hide('slide', {direction: 'right'});
        },

        init_region: function(properties) {
            var container_id = properties.container_id;
            var type = properties.type;
            var id = properties.id;
            var order = properties.order;
            var collection = properties.collection;
            var count = properties.count;

            switch (type) {
                case 'page':
                    software.inline_editing.regions.push({container_id: container_id, type: type, id: id, order: order, collection: collection, count: count, content: document.getElementById(container_id).innerHTML});
                    break;

                case 'common':
                case 'system_region_header':
                case 'system_region_footer':
                    software.inline_editing.regions.push({container_id: container_id, type: type, id: id, count: count, content: document.getElementById(container_id).innerHTML});
                    break;
            }

            software_$('#' + container_id).attr('contenteditable','true');

            CKEDITOR.inline(container_id, software_ckeditor_config);

            CKEDITOR.instances[container_id].once('change', function() {
                software.inline_editing.show_buttons();
            });

            switch (type) {
                case 'page':
                    var image_editor_object_type = 'pregion';
                    break;

                case 'common':
                    var image_editor_object_type = 'cregion';
                    break;

                case 'system_region_header':
                    var image_editor_object_type = 'system_region_header';
                    break;

                case 'system_region_footer':
                    var image_editor_object_type = 'system_region_footer';
                    break;
            }

            CKEDITOR.instances[container_id].on('instanceReady', function() {
                software_$('#' + container_id).on('mouseenter', 'img[data-cke-saved-src]', function () {
                    var image = software_$(this);

                    var src = image.attr('src');

                    // If there is a slash in the image source,
                    // then get image name by looking at content after last slash.
                    if (src.indexOf('/') > -1) {
                        var position_of_last_slash = src.lastIndexOf('/');

                        var image_name = src.substring(position_of_last_slash + 1).trim();

                    // Otherwise, the image name is the whole source.
                    } else {
                        var image_name = src.trim();
                    }

                    image_name = decodeURIComponent(image_name);

                    var extension = image_name.split('.').pop().toLowerCase();

                    // If the extension is a supported extension by image editor service,
                    // then continue to show image editor button.
                    if (
                        (extension == 'gif')
                        || (extension == 'jpg')
                        || (extension == 'jpeg')
                        || (extension == 'png')
                    ) {
                        // If the image does not have an image id, then give it an id.
                        // This is necessary because later we will use this id in order to 
                        // hide the corresponding image editor button.
                        if (!image.data('image_id')) {
                            var image_id = ++software.inline_editing.last_image_id;
                            image.data('image_id', image_id);

                        // Otherwise the image already has an image id, so store it.
                        } else {
                            var image_id = image.data('image_id');
                        }

                        // If there is not already an active image editor button for this image,
                        // then create one.
                        if (software_$('#software_image_editor_button_' + image_id).length == 0) {
                            var image_editor_button = software_$('<a href="' + software_path + software_directory + '/image_editor_edit.php?file_name=' + prepare_content_for_html(encodeURIComponent(image_name)) + '&amp;object_type=' + image_editor_object_type + '&amp;object_id=' + id + '&amp;send_to=' + prepare_content_for_html(encodeURIComponent(window.location.pathname + window.location.search)) + '" id="software_image_editor_button_' + image_id + '" style="border: 0; left: ' + image.offset().left + 'px; margin: 0 0 0 1.5em; padding: .15em; position: absolute; text-decoration: none; top: ' + image.offset().top + 'px; z-index: 9" title="Edit Image (' + prepare_content_for_html(image_name) + ') with Software Image Editor"><img src="' + software_path + software_directory + '/images/icon_image_editor.png" width="86" height="20" alt="Software Image Editor" /></a>').appendTo('body');

                            image_editor_button.on('mouseleave', function () {
                                setTimeout (function () {
                                    if (
                                        (image.is(':hover') == false)
                                        && (image_editor_button.is(':hover') == false)
                                    ) {
                                        image_editor_button.remove();
                                    }
                                }, 0);
                            });
                        }
                    }
                });

                software_$('#' + container_id).on('mouseleave', 'img[data-cke-saved-src]', function () {
                    var image = software_$(this);

                    var image_id = image.data('image_id');

                    var image_editor_button = software_$('#software_image_editor_button_' + image_id);

                    if (image_editor_button.length) {
                        setTimeout (function () {
                            if (
                                (image.is(':hover') == false)
                                && (image_editor_button.is(':hover') == false)
                            ) {
                                image_editor_button.remove();
                            }
                        }, 0);
                    }
                });
            });

            // If this is the first region, then setup a function that runs when a user
            // attempts to leave page in order to ask for confirmation if there are unsaved changes.
            if (software.inline_editing.regions.length == 1) {
                software_$(window).on('beforeunload', function() {
                    var length = software.inline_editing.regions.length;

                    // Loop through the regions in order to determine if there are unsaved changes.
                    for (var i = 0; i < length; i++) {
                        var container_id = software.inline_editing.regions[i].container_id;

                        if (CKEDITOR.instances[container_id].checkDirty() == true) {
                            return 'WARNING: If you leave this page, then your unsaved changes will be lost.';
                        }
                    }
                });
            }
        },

        last_image_id: 0,

        regions: [],

        save: function() {
            // Slide save & cancel buttons out of view, to the right.
            software_$('#software_inline_editing_buttons').hide('slide', {direction: 'right'});

            // If there is an active confirmation, then remove it.
            if (software_$('#software_inline_editing_confirmation').length) {
                software_$('#software_inline_editing_confirmation').remove();
            }

            // Prepare saving confirmation message.
            software_$('body').append('\
                <div id="software_inline_editing_confirmation" class="notice" style="background-color: #edfced; border-bottom: 1px solid #428221; border-left: 1px solid #428221; border-right: 1px solid #428221; border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; color: #428221; display: none; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; left: 0; margin-left: auto; margin-right: auto; padding: .5em; position: fixed; right: 0; text-align: center; top: 0; width: 40%; z-index: 2147483647">\
                    <img src="' + software_path + software_directory + '/images/icon_notice.png" width="16" height="16" alt="Notice" title="" style="margin-right: 7px; margin-bottom: 2px; vertical-align: middle" /><span style="font-size: 14px; font-weight: bold">Notice:</span> <span class="message" style="font-size: 13px">Saving... (please wait)</span>\
                </div>');

            // Slide down saving confirmation message.
            software_$('#software_inline_editing_confirmation').slideDown('slow');

            var modified_region_indexes = [];

            var length = software.inline_editing.regions.length;

            for (var index = 0; index < length; index++) {
                var container_id = software.inline_editing.regions[index].container_id;

                if (CKEDITOR.instances[container_id].checkDirty() == true) {
                    modified_region_indexes.push(index);
                }
            }

            var number_of_modified_regions = modified_region_indexes.length;

            if (number_of_modified_regions > 0) {
                var any_region_error;

                var number_of_processed_regions = 0;

                for (var i = 0; i < number_of_modified_regions; i++) {
                    var index = modified_region_indexes[i];

                    var container_id = software.inline_editing.regions[index].container_id;

                    var collection = '';

                    switch (software.inline_editing.regions[index].type) {
                        case 'page':
                            var region_type = 'pregion';
                            var region_order = software.inline_editing.regions[index].order;
                            collection = software.inline_editing.regions[index].collection;
                            break;

                        case 'common':
                            var region_type = 'cregion';
                            var region_order = '';
                            break;

                        case 'system_region_header':
                            var region_type = 'system_region_header';
                            var region_order = '';
                            break;

                        case 'system_region_footer':
                            var region_type = 'system_region_footer';
                            var region_order = '';
                            break;
                    }

                    var content = CKEDITOR.instances[container_id].getData();

                    software_$.ajax({
                        type: 'POST',
                        url: software_path + software_directory + '/save_region_content.php',
                        data: {
                            inline: 'true',
                            page_id: software_page_id,
                            region_content: content,
                            region_type: region_type,
                            region_id: software.inline_editing.regions[index].id,
                            region_order: region_order,
                            collection: collection,
                            token: software_token
                        },
                        async: true,
                        index: index,
                        complete: function (response, status) {
                            number_of_processed_regions++;

                            var index = this.index;

                            var container_id = software.inline_editing.regions[index].container_id;                            

                            var this_region_error = false;

                            if (status != 'success') {
                                this_region_error = true;
                                any_region_error = true;
                            }

                            if (this_region_error == false) {
                                CKEDITOR.instances[container_id].resetDirty();
                                software.inline_editing.regions[index].content = CKEDITOR.instances[container_id].getData();
                            }

                            // If this is the last AJAX request to complete,
                            // then deal with confirmation and errors.
                            if (number_of_processed_regions == number_of_modified_regions) {
                                if (any_region_error == true) {
                                    software_$('#software_inline_editing_confirmation').remove();

                                    // Prepare error message.
                                    software_$('body').append('\
                                        <div id="software_inline_editing_confirmation" class="error" style="background-color: #fdd5ce; border-bottom: 2px solid red; border-left: 2px solid red; border-right: 2px solid red; border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; color: red; display: none; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; left: 0; margin-left: auto; margin-right: auto; padding: .5em; position: fixed; right: 0; text-align: center; top: 0; width: 50%; z-index: 2147483647">\
                                            <img src="' + software_path + software_directory + '/images/icon_error.png" width="16" height="16" alt="Error" title="" style="margin-right: 7px; margin-bottom: 2px; vertical-align: middle" /><span style="font-size: 14px; font-weight: bold">An error occurred:</span> <span style="font-size: 13px">Sorry, your updates could not be saved. Please try again later.</span>\
                                        </div>');

                                    // Slide down saving confirmation message.
                                    software_$('#software_inline_editing_confirmation').slideDown('slow').delay(6000).slideUp('slow', function() {software_$(this).remove()});

                                    software_$('#software_inline_editing_buttons').show('slide', {direction: 'right'});

                                } else {
                                    software_$('#software_inline_editing_confirmation .message').html('Your updates have been <strong>saved</strong>.');

                                    software_$('#software_inline_editing_confirmation').delay(3000).slideUp('slow', function() {software_$(this).remove()});
                                }
                            }
                        }
                    });

                    CKEDITOR.instances[container_id].once('change', function() {
                        software.inline_editing.show_buttons();
                    });
                }
            }
        },

        show_buttons: function() {
            if (software_$('#software_inline_editing_buttons').length == 0) {
                // The "display: block" in the img tags is necessary to avoid extra space
                // around the save and cancel buttons when there is an HTML 5 doctype.
                software_$('body').append('\
                    <div id="software_inline_editing_buttons" style="display: none; position: fixed; right: 0; top: 45%; z-index: 2147483647">\
                        <div id="software_inline_editing_save_button" style="background-color: black; border: 1px solid #629d1f; cursor: pointer; margin-bottom: 10px; padding: 5px" title="Save"><img src="' + software_path + software_directory + '/images/inline_save.png" width="25" height="25" alt="Save current page edits (Ctrl+S | &#8984;+S)." title="Save current page edits (Ctrl+S | &#8984;+S)." border="0" style="display: block"></div>\
                        <div id="software_inline_editing_cancel_button" style="background-color: black; border: 1px solid #629d1f; cursor: pointer; padding: 5px" title="Cancel"><img src="' + software_path + software_directory + '/images/inline_cancel.png" width="25" height="25" alt="Cancel current page edits." title="Cancel current page edits." border="0" style="display: block"></div>\
                    </div>');

                software_$('#software_inline_editing_save_button').click(function() {
                    software.inline_editing.save();
                });

                software_$('#software_inline_editing_cancel_button').click(function() {
                    software.inline_editing.cancel();
                });
            }

            if (software_$('#software_inline_editing_buttons').css('display') == 'none') {
                software_$('#software_inline_editing_buttons').show('slide', {direction: 'right'});
            }
        }
    },

    kiosk: {
        init: function() {
            // If activity has not been detected yet for this session, then detect for it.
            if (software_kiosk_activity == false) {
                software_$(document).one('DOMMouseScroll keydown mousedown mousemove mousewheel', function() {
                    software_$.ajax(software_path + software_directory + '/kiosk.php?action=update_activity');
                    software_kiosk_activity = true;
                    software.kiosk.detect_inactivity();
                    software.kiosk.show_logout_button();
                });

            // Otherwise activity has been detected this session,
            // so detect for inactivity.
            } else {
                software.kiosk.detect_inactivity();
                software.kiosk.show_logout_button();
            }
        },

        detect_inactivity: function() {
            var inactivity_timeout = setTimeout(function () {
                software.kiosk.show_dialog();
            }, software_kiosk_inactivity_time * 1000);

            software_$(document).on('DOMMouseScroll keydown mousedown mousemove mousewheel', function() {
                clearTimeout(inactivity_timeout);

                inactivity_timeout = setTimeout(function () {
                    software.kiosk.show_dialog();
                }, software_kiosk_inactivity_time * 1000);
            });
        },

        logout: function() {
            window.location.href = software_path + software_directory + '/kiosk.php?action=logout';
        },

        show_dialog: function() {
            // If the dialog is not already open, then show it.
            if (software_$("#software_kiosk_dialog").dialog('isOpen') !== true) {
                software_$('body').append('\
                    <div id="software_kiosk_dialog">\
                        <div id="software_kiosk_dialog_message">' + prepare_content_for_html(software_kiosk_dialog_message) + '</div>\
                        <div id="software_kiosk_dialog_buttons"><a id="software_kiosk_dialog_continue_button" class="software_button_primary">' + prepare_content_for_html(software_kiosk_continue_button_label) + '</a><a id="software_kiosk_dialog_logout_button" class="software_button_secondary">' + prepare_content_for_html(software_kiosk_logout_button_label) + '</a></div>\
                        <div id="software_kiosk_dialog_countdown">Please make a choice within <span id="software_kiosk_dialog_countdown_seconds">' + software_kiosk_dialog_time + ' seconds</span>.</div>\
                    </div>');

                software_$('#software_kiosk_dialog_continue_button').on('click', function() {
                    software_$('#software_kiosk_dialog').dialog('close');
                });

                software_$('#software_kiosk_dialog_logout_button').on('click', function() {
                    software.kiosk.logout();
                });

                var dialog_timeout;
                var countdown_interval;

                software_$('#software_kiosk_dialog').dialog({
                    autoOpen: true,
                    close: function() {
                        clearTimeout(dialog_timeout);
                        clearInterval(countdown_interval);
                        software_$('#software_kiosk_dialog').remove();
                        software_$('#software_kiosk_logout_button').fadeIn();
                    },
                    closeOnEscape: false,
                    dialogClass: 'software mobile_dialog',
                    draggable: false,
                    modal: true,
                    open: function() {
                        software_$('.ui-dialog-titlebar-close').hide();

                        var countdown_seconds = software_kiosk_dialog_time;

                        countdown_interval = setInterval(function(){

                            countdown_seconds = countdown_seconds - 1;

                            if (countdown_seconds == 0) {
                                clearInterval(countdown_interval);

                            } else {
                                var plural_suffix = '';

                                if (countdown_seconds > 1) {
                                    plural_suffix = 's';
                                }

                                software_$('#software_kiosk_dialog_countdown_seconds').html(countdown_seconds + ' second' + plural_suffix);
                            }

                        }, 1000);

                        // If the visitor has not made a decision in the dialog within a certain amount of time,
                        // then auto-logout the visitor.
                        dialog_timeout = setTimeout(function () {
                            if (software_$("#software_kiosk_dialog").dialog('isOpen') === true) {
                                software.kiosk.logout();
                            }
                        }, software_kiosk_dialog_time * 1000);

                        software_$('#software_kiosk_logout_button').fadeOut();
                    },
                    resizable: false,
                    show: {effect: 'fade'},
                    width: 400
                });
            }
        },

        show_logout_button: function () {
            software_$('body').append('<a id="software_kiosk_logout_button" class="software_button_secondary" style="bottom: 0; display: none; margin: 1em; position: fixed; right: 0; z-index: 2147483647">' + prepare_content_for_html(software_kiosk_logout_button_label) + '</a>');

            software_$('#software_kiosk_logout_button').on('click', function() {
                software.kiosk.logout();
            });

            software_$('#software_kiosk_logout_button').fadeIn();
        }
    },

    // Create a function that will open a jQuery dialog that contains an iframe.
    open_dialog: function(properties) {
        var class_name = properties.class_name;
        var modal = properties.modal;
        var title = properties.title;
        var url = properties.url;
        var width = properties.width;
        var height = properties.height;

        if (class_name === undefined) {
            class_name = '';
        } else {
            class_name = ' ' + class_name;
        }

        // If the height is not set, or the visitor is using a mobile device,
        // then set height as a percentage of the browser height.
        if (
            (height === undefined)
            || (software_device_type == 'mobile')
        ) {
            height = software_$(window).height() * .75;

        // Otherwise the height is set and the visitor is using a desktop device,
        // so use height parameter and add 25px to make up for the jQuery dialog iframe height.
        } else {
            height = height + 25;
        }

        // If modal is not set, then enable it by default.
        if (modal === undefined) {
            modal = true;
        }

        // If the width is not set, or the visitor is using a mobile device,
        // then set width as a percentage of the browser width.
        if (
            (width === undefined)
            || (software_device_type == 'mobile')
        ) {
            width = software_$(window).width() * .75;
        }

        // Create unique id from number of milliseconds since epoch
        // so that multiple dialogs can exist at once.
        var date = new Date;
        var unique_id = date.getTime();

        // Add iframe to body.

        var iframe = software_$('<iframe id="software_dialog_' + unique_id + '" src="' + software.h(url) + '" frameBorder="0" style="border: none; display: block; margin: 0" allowfullscreen></iframe>');

        software_$('body').append(iframe);

        // Open jQuery dialog.
        software_$('#software_dialog_' + unique_id).dialog({
            autoOpen: true,

            open: function() {
                // Add container around iframe so we can set overflow and webkit overflow scrolling,
                // so that the iframe does not go outside the dialog horizontally in iOS and so
                // visitors can scroll in the iframe in iOS.
                // We have not yet solved the iframe being extended vertically in iOS.
                // We add the container div for other environments also just because it does not appear
                // to have any side effects.  We could probably add container up above when iframe is created,
                // instead of placing it in this open function, however we have not tested that.
                software_$('#software_dialog_' + unique_id).wrap('<div id="software_dialog_' + unique_id + '_container" />');
                software_$('#software_dialog_' + unique_id + '_container').css({
                    'overflow': 'auto',
                    '-webkit-overflow-scrolling': 'touch'
                });

                // If this is a modal dialog, then when the visitor clicks outside
                // of the dialog, close it.
                if (modal) {
                    software_$('.ui-widget-overlay').bind('click', function() {
                        software_$('#software_dialog_' + unique_id).dialog('close');
                    });
                }
            },

            // Add an overlay over the whole page while the user is dragging the dialog,
            // so that the drag will work correctly with an iframe.
            dragStart: function() {
                software_$('body').append('<div class="software_overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647"></div>');
            },

            dragStop: function() {
                software_$('.software_overlay').remove();
            },

            // Add an overlay over the whole page while the user is resizing the dialog,
            // so that the resize will work correctly with an iframe.
            resizeStart: function() {
                software_$('body').append('<div class="software_overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2147483647"></div>');
            },

            resizeStop: function() {
                software_$('.software_overlay').remove();
            },

            close: function() {
                // Remove iframe and iframe container.
                software_$('#software_dialog_' + unique_id).remove();
                software_$('#software_dialog_' + unique_id + '_container').remove();
            },

            dialogClass: 'software mobile_dialog' + class_name,
            height: height,
            modal: modal,
            title: title,
            width: width,
            show: {effect: 'fade'},
            hide: {effect: 'fade'}
        });
    },

    // Create a function that will be responsible for taking an encrypted email link, decrypting it,
    // and then outputting it.  This function is used in order to protect email addresses from harvesters.
    output_email_link: function(email_link) {
        document.write(this.get_rot13(this.Base64.decode(email_link)));

        // Remove script tag that called this function in order to avoid a bug, where a blank white page appears,
        // if this content is located in a dynamic area that is processed by jQuery (e.g. tab content).
        software_$('#software_email_link_script').remove();
    }

};

// Find an item with a certain property and value in an array or object.

software.find = function(items, property, value) {
    
    for (var i = 0; i < items.length; i++) {
        if (items[i][property] == value) {
            return items[i];
        }
    }
};

// Prepare a price for HTML with a negative sign (if necessary), symbol, and code ($100.00 USD).

software.prepare_price = function(properties) {

    var symbol = properties.symbol;
    var price = properties.price;
    var base_price = properties.base_price;
    var code = properties.code;

    // If we are not preparing a price for the base currency, then adjust price by exchange rate.
    if (!base_price) {
        price = price * software.visitor_currency_exchange_rate;
    }

    var negative_sign = '';

    // If the price is negative, then prepare to show negative sign before price, and convert price
    // to positive value.
    if (price < 0) {
        negative_sign = '-';
        price = Math.abs(price);
    }

    if (!symbol) {
        symbol = software.visitor_currency_symbol;
    }

    // Set price to have two decimal places and commas.
    price = price.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

    if (code) {
        code = ' ' + code;
    } else {
        code = software.visitor_currency_code_for_output;
    }

    price =
        '<span style="white-space: nowrap">' +
            negative_sign + symbol + price + software.h(code) +
        '</span>';

    return price;
};

software.load = function(request) {

    var module = request.module;
    var complete = request.complete;

    software.modules = software.modules || {};
    software.modules[module] = software.modules[module] || {};

    // If the module has already been loaded, then run complete function and return.
    if (software.modules[module].status == 'complete') {
        complete();
        return;
    }

    // Add complete function to the queue.  We use a queue because multiple load calls might be made
    // as the module is being loaded.  Once the module is loaded, then we can run all the queue jobs.
    software.modules[module].queue = software.modules[module].queue || [];
    software.modules[module].queue.push(complete);

    // If this module is already in the process of being loaded, then just return.
    if (software.modules[module].status == 'loading') {
        return;
    }

    // Otherwise, the module needs to be loaded, so load it.

    software.modules[module].status = 'loading';

    var script = document.createElement('script');

    // Once the module is finished loading, then update status and process queue.
    script.onload = function() {

        software.modules[module].status = 'complete';

        // Loop through the jobs in the queue to run them.
        software_$.each(software.modules[module].queue, function(index, job) {
            job();
        });

        // Clear the queue.
        software.modules[module].queue = [];
    };

    // Defer the script so it doesn't delay other page rendering.
    script.setAttribute('defer', 'defer');

    if (typeof software_environment !== 'undefined' && software_environment == 'development') {
        var environment_suffix = 'src';
    } else {
        var environment_suffix = 'min';
    }

    script.src = software_path + software_directory + '/' + module + '.' + environment_suffix + '.js';
    
    // Load the module.
    document.body.appendChild(script);
};
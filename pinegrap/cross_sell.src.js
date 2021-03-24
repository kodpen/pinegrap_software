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

software.cross_sell = software.cross_sell || {};

software.cross_sell.init = function(request) {

    var id = request.id;

    if (!id) {
        id = 'cross-sell';
    }

    var container = software_$('#' + id);

    // Hide the container as soon as possible, because we are not ready to show it yet.
    container.hide();

    // Also hide the items container, so the first time we show it, it is not open.
    var items_container = software_$('.items', container);
    items_container.hide();

    var number_of_items = request.number_of_items;

    var complete = request.complete;

    software.cross_sell.instances = software.cross_sell.instances || {};

    software.cross_sell.instances[id] = {};

    var cross_sell = software.cross_sell.instances[id];

    cross_sell.attribute_update = request.attribute_update;

    // If this cross-sell is connected to product attributes and the attribute system has already
    // filtered products, then use those products.
    if (cross_sell.attribute_update && typeof software.matched_products !== 'undefined') {
        cross_sell.products = software.matched_products;

    // Otherwise just use the products that were passed in the request.
    } else {
        cross_sell.products = request.products;
    }

    cross_sell.product_group = request.product_group;
    cross_sell.recipient = request.recipient;
    cross_sell.limit = request.limit;
    cross_sell.days = request.days;
    cross_sell.discounted = request.discounted;
    cross_sell.catalog_detail_page = request.catalog_detail_page;
    cross_sell.number_of_items = request.number_of_items;
    cross_sell.in_product_group = request.in_product_group;
    cross_sell.analytics = request.analytics;

    // Store item content so that we have content saved so once we update items, we still have the
    // original content, in case we need to update items again.
    cross_sell.item_content = software_$('.item', container).prop('outerHTML');

    // Now that we have saved the example item content, remove example item so there is nothing in
    // the items container to start.
    items_container.empty();

    if (typeof complete !== 'function') {
        complete = function() {};
    }

    cross_sell.complete = complete;

    cross_sell.update = function() {

        // Prepare data for API request.
        var api_request = JSON.stringify({
            action: 'get_cross_sell_items',
            products: cross_sell.products,
            product_group: cross_sell.product_group,
            recipient: cross_sell.recipient,
            limit: cross_sell.limit,
            days: cross_sell.days,
            discounted: cross_sell.discounted,
            catalog_detail_page: cross_sell.catalog_detail_page,
            number_of_items: cross_sell.number_of_items,
            in_product_group: cross_sell.in_product_group});

        // If this request is the same as the last request, then we don't need to do anything.
        if (api_request === cross_sell.api_request) {
            return;
        }

        // Store this request, so we know if future requests are different.
        cross_sell.api_request = api_request;

        var get_cross_sell_items = software_$.ajax({
            contentType: 'application/json',
            url: software_path + software_directory + '/api.php',
            data: api_request,
            type: 'POST'});

        get_cross_sell_items.done(function(response) {

            var api_response = JSON.stringify(response);

            // If this response is the same as the last response, then we don't need to do anything.
            if (api_response === cross_sell.api_response) {
                return;
            }

            // Store this response, so we know if a future response is different.
            cross_sell.api_response = api_response;        

            var items = response.items;

            // If there are no items, then fade out cross-sell, empty items container, and return.
            if (!items) {

                container.fadeOut(function() {
                    items_container.empty();
                });

                return;
            }

            container.fadeIn();

            var fade_out_items = items_container.fadeOut().promise();

            fade_out_items.done(function() {

                items_container.empty();

                var event = false;
                var tracker_prefix = '';

                // If analytics is enabled, then prepare to create a Google Analytics event when the
                // customer clicks an item.
                if (cross_sell.analytics !== false && window.ga && ga.create) {

                    event = true;

                    // Deal with issue where tracker might have a unique name (e.g. GTM does this).
                    // This unique name must be included in the event further below.  Otherwise,
                    // the event won't be created correctly.

                    var tracker_name = '';

                    if (typeof ga.getAll()[0] !== 'undefined') {
                        tracker_name = ga.getAll()[0].get('name');
                    }

                    tracker_prefix = tracker_name;

                    if (tracker_prefix != '') {
                        tracker_prefix += '.';
                    }
                }

                software_$.each(items, function(index, item) {

                    var item_element = software_$(cross_sell.item_content);

                    var link = software_$('a.link', item_element);
                    link.prop('href', item.url);

                    // If we should create a Google Analytics event when the customer clicks this
                    // item, then add click event.
                    if (event) {
                        link.click(function() {
                            ga(tracker_prefix + 'send', 'event', {
                                'eventCategory': id,
                                'eventAction': 'click',
                                'eventLabel': item.short_description,
                                // The following should help to make sure the event gets created even
                                // though the browser is in the process of exiting the current page
                                // and going to a new URL.
                                'transport': 'beacon'
                            });
                        });
                    }

                    software_$('img.image', item_element).prop('src', item.image_url);
                    software_$('.description', item_element).html(software.h(item.short_description));
                    software_$('.price', item_element).html(item.price_info);

                    items_container.append(item_element);
                });

                items_container.fadeIn(function() {
                    cross_sell.complete();
                });
            });
        });
    }

    // Update cross-sell for the first time now.
    cross_sell.update();

    // If this cross-sell is connected to product attributes, then update cross-sell when attributes
    // are updated.
    if (cross_sell.attribute_update) {

        // Once the attribute system triggers an update event, then update cross-sell.
        software_$('body').on('attribute_update', function() {

            // Loop through all of the matched products in order to update products for cross-sell,
            // and we also only want to include the product id.

            cross_sell.products = [];

            software_$.each(software.matched_products, function(index, product) {
                var cross_sell_product = {};
                cross_sell_product.id = product.id;
                cross_sell.products.push(cross_sell_product);
            });

            cross_sell.update();
        });
    }

    return cross_sell;
};
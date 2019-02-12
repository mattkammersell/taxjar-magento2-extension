/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define([
    'ko',
    'jquery',
    'Magento_Customer/js/customer-data'
], function (ko, $, customerData) {
    'use strict';

    $().ready(function () {

        // Watch for suggested address being selected
        $('#tj-suggested-addresses').on('change', 'input[name=suggested-address]:checked', function (event) {
            let data = $.data(document.body);
            let addr = data[this.id];

            if ($.isEmptyObject(addr)) {
                console.log('empty object');
                return;
            }

            $('input[name*="street"]:first').val(addr.street);
            $('input[name="city"]').val(addr.city);
            $('input[name="region_id"]').val(addr.state);
            $('input[name="postcode"]').val(addr.zip);
            $('input[name="country_id"]').val(addr.country);

            // Force KO to acknowledge the address data changed
            ko.utils.triggerEvent($('input[name="postcode"]').get(0), 'change');

            window.isValidated = true;
        });
    });


    return {

        initialize: function () {
            this._super();
            return this;
        },

        buildHtml: function (response, addr) {
            let addrHTML = '<div><input type="radio" name="suggested-address" id="tj-suggestion-0" value="0" checked="checked"/><label for="0">Original</label></div>';
            let responseJson = {};
            let n = 1;

            if (response.suggestions) {
                responseJson = Object.assign(responseJson, {"tj-suggestion-0": addr.original});

                for (let addr of response.suggestions) {
                    let suggestion = $.extend({}, addr.address, addr.changes);

                    addrHTML += '<div>';
                    addrHTML += '<input type="radio" name="suggested-address" id="tj-suggestion-' + n + '" value="' + n + '" />';
                    addrHTML += '<label for="' + n + '">';
                    addrHTML += '<div class="addr">' + suggestion.street + '</div>';
                    addrHTML += '<div class="city">' + suggestion.city + '</div>';
                    addrHTML += '<div class="state">' + suggestion.state + '</div>';
                    addrHTML += '<div class="postal">' + suggestion.zip + '</div>';
                    addrHTML += '<div class="country">' + suggestion.country + '</div>';
                    addrHTML += '</label></div>';

                    let key = "tj-suggestion-" + n;
                    responseJson = Object.assign(responseJson, {[key]: addr.address});

                    n++;
                }
            }

            $.data(document.body, responseJson);
            return addrHTML;
        },

        hideLoader: function (button) {
            $('body').trigger('processStop');
            button.attr('disabled', false);
        },

        displayError: function (msg) {

            console.log(msg);

            customerData.set('messages', {
                messages: [{
                    type: 'error',
                    text: msg
                }]
            });
        }
    };
});

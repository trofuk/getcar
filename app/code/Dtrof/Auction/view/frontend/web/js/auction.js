/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    $.widget('mage.auction', {
        options: {
            url:'',
            product_id: '',
            blocked_groups_id: ''
        },
        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            if(this.getCookie()=='1'){
                this.setCookie();
                window.location.href = window.location.href;
                return;
            }
            this._load();
        },
        _load: function(){
            $.ajax({
                url: this.options.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    product_id : this.options.product_id,
                    blocked_groups_id : this.options.blocked_groups_id
                },
                showLoader: false,
                statusCode:{
                    404:function() {},
                    500:function() {}
                }
            }).done(function(data) {
                $('#auction').removeClass('empty');
                if(data.refresh){
                    window.location.href = window.location.href;
                }
                if(data.success){
                    this.element.html(data.html);
                    $('#bid').trigger('contentUpdated');
                }
            }.bind(this));
        },
        setCookie: function(){
            var d = new Date();
            d.setTime(d.getTime() + (60*60*1000));
            var expires = "expires="+d.toUTCString();
            document.cookie = "auction_" + this.options.product_id +"=2; " + expires;
        },
        getCookie: function(){
            var name = "auction_" + this.options.product_id +"=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
    });
    return $.mage.auction;
});
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
    $.widget('mage.auctiongrid', {
        options: {
            update_url:'',
            update_time: ''
        },
        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            this._autoUpdate();
        },
        _update:function(data){
            $.each(data,function(index,value){
                $('#current_bid_id_'+index +' .price').html(value.price);
                $('#countdown_'+index).updateTimer(value.end_time,index);
            });
        },
        _autoUpdate: function(){
            var loadding = false;
            if($('.current_bid').length)
            setInterval(function(){
                if(!loadding){
                    loadding = true;
                    $.ajax({
                        url: this.options.update_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                        },
                        showLoader: false,
                        statusCode:{
                            404:function() {
                                loadding = false;
                            },
                            500:function() {
                                loadding = false;
                            }
                        }
                    }).done(function(data) {
                        this._update(data);
                        loadding = false;
                    }.bind(this));
                }
            }.bind(this), this.options.update_time * 1000);
        }
    });

    return $.mage.auctiongrid;
});
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
    $.widget('mage.auctionbox', {
        options: {
            successBox: '#bid_success',
            errorBox: '#bid_error',
            statusBox: '#bid_status',
            bidderNameBox: '#bidder_name',
            currentBidBox: '#current_bid',
            bid_type: 'autobid',
            price_input: '#bid_price',
            bid_url : '',
            autoBid_url : '',
            update_url:'',
            update_time: '',
            auction_id: '',
            timmer_box: '.auction_timer',
            suggess :'.suggess',
            product_id: '',
            auctionStatistic: '#auction-statistic',
            productPrice: '#product-price-',
            blocked_groups_id: ''
        },
        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            this.element.on('click', $.proxy(this._bid, this));
            this._autoUpdate();
        },

        _bid: function(e) {
            var url = jQuery('input[name='+this.options.bid_type+']:checked').get(0).value == '1' ? this.options.autoBid_url: this.options.bid_url;
            $(this.options.successBox).hide();
            $(this.options.errorBox).hide();
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    price: $(this.options.price_input).get(0).value,
                    auction_id: this.options.auction_id,
                    product_id: this.options.product_id,
                    blocked_groups_id: this.options.blocked_groups_id
                },
                showLoader: true,
                statusCode:{
                    404:function() {
                        alert($.mage.__("There are some problem when place bid."));
                    },
                    500:function() {
                        alert($.mage.__("There are some problem when place bid."));
                    }
                }
            }).done(function(data) {
                $(this.options.statusBox).hide();
                if(data.error){
                    $(this.options.successBox).hide();
                    $(this.options.errorBox).html(data.error).show(1000);
                    setTimeout(function(){ $(this.options.errorBox).hide(1000);}.bind(this), 5000);
                }else if(data.success){
                    $(this.options.errorBox).hide();
                    $(this.options.successBox).html(data.success).show(1000);
                    setTimeout(function(){ $(this.options.successBox).hide(1000);}.bind(this), 5000);
                }
                //this._update(data);
            }.bind(this));
        },
        _update:function(data){
            if(data.error){
                if(data.current_bidder_name) {
                    $(this.options.bidderNameBox + ' span').html(data.current_bidder_name);
                    $(this.options.currentBidBox + ' span a').html(data.total_bid);
                    $(this.options.currentBidBox + ' .price').html(data.current_price);
                    if(data.logged_in){
                        $('#bid').removeAttr("disabled");
                        $('#login_to_bid').hide();
                    }else{
                        $('#bid').attr('disabled',"disabled");
                        $('#login_to_bid').show();
                    }
                }
            }else if(data.success){
                $(this.options.bidderNameBox+' span').html(data.current_bidder_name);
                $(this.options.currentBidBox+' span a').html(data.total_bid);
                $(this.options.currentBidBox+' .price').html(data.current_price);
                $(this.options.auctionStatistic+' .total-bids').html(data.total_bids);
                $(this.options.auctionStatistic+' .total-bidders').html(data.total_bidders);
                $(this.options.productPrice+data.product_id).html(data.current_price);
                $(this.options.productPrice+data.product_id).attr('data-price-amount',data.price);
                if(data.logged_in){
                    $('#bid').removeAttr("disabled");
                    $('#login_to_bid').hide();
                }else{
                    $('#bid').attr('disabled',"disabled");
                    $('#login_to_bid').show();
                }
                if(data.message) {
                    $(this.options.statusBox + ' .message').html(data.message);
                    $(this.options.statusBox).show();
                } else {
                    $(this.options.statusBox).hide();
                }
                $('.auction-loading').hide();
                $('#auction_block').show();
                if(data.min_next_price && $(this.options.price_input).get(0).value<data.min_next_price)
                    $(this.options.price_input).get(0).value = data.min_next_price;
                $('#countdown_'+this.options.product_id).updateTimer(data.time_left,this.options.product_id);
                $(this.options.suggess).html(data.suggess);
            }
        },
        _autoUpdate: function(){
            var loadding = false;
            setInterval(function(){
                if(!loadding){
                    loadding = true;
                    $.ajax({
                        url: this.options.update_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: this.options.auction_id,
                            product_id: this.options.product_id,
                            blocked_groups_id: this.options.blocked_groups_id
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

    return $.mage.auctionbox;
});
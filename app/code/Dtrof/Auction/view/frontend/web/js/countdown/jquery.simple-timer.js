/*
 * jQuery-Simple-Timer
 *
 * Creates a countdown timer.
 *
 * Example:
 *   $('.timer').startTimer();
 *
 */
(function($){

    var timer;

    var endTime = [];

    var Timer = function(targetElement){
        this.targetElement = targetElement;
        return this;
    };

    Timer.start = function(options, targetElement){
        timer = new Timer(targetElement);
        return timer.start(options);
    };

    Timer.prototype.start = function(options) {
        this.targetElement.each(function(_index, timerBox) {
            var timerBoxElement = $(timerBox);
            var cssClassSnapshot = timerBoxElement.attr('class');

            timerBoxElement.on('complete', function() {
                clearInterval(timerBoxElement.intervalId);
            });

            timerBoxElement.on('complete', function() {
                timerBoxElement.onComplete(timerBoxElement);
            });
            timerBoxElement.on('complete', function() {
                timer.setAuctionCookie(timerBoxElement.attr('product-id'));
                window.location.href = window.location.href;
            });

            timerBoxElement.on('complete', function(){
                timerBoxElement.addClass('timeout');
            });

            timerBoxElement.on('complete', function(){
                if(options && options.loop === true) {
                    timer.resetTimer(timerBoxElement, options, cssClassSnapshot);
                }
            });

            //createSubDivs(timerBoxElement);
            return this.startCountdown(timerBoxElement, options);
        }.bind(this));
    };

    /**
     * Resets timer and add css class 'loop' to indicate the timer is in a loop.
     * $timerBox {jQuery object} - The timer element
     * options {object} - The options for the timer
     * css - The original css of the element
     */
    Timer.prototype.resetTimer = function($timerBox, options, css) {
        var interval = 0;
        if(options.loopInterval) {
            interval = parseInt(options.loopInterval, 10) * 1000;
        }
        setTimeout(function() {
            $timerBox.trigger('reset');
            $timerBox.attr('class', css + ' loop');
            timer.startCountdown($timerBox, options);
        }, interval);
    }

    Timer.prototype.fetchSecondsLeft = function(element){
        var secondsLeft = element.data('seconds-left');
        var minutesLeft = element.data('minutes-left');
        console.log('element Class - ',element.attr('class'),'\n secondsLeft - ',secondsLeft);
        if(secondsLeft){
            return parseInt(secondsLeft, 10);
        } else if(minutesLeft) {
            return parseFloat(minutesLeft) * 60;
        }else {
            throw 'Missing time data';
        }
    };

    Timer.prototype.startCountdown = function(element, options) {
        options = options || {};
        var intervalId = null;
        var defaultComplete = function(){
            //clearInterval(intervalId);
            return this.clearTimer(element);
        }.bind(this);

        element.onComplete = options.onComplete || defaultComplete;

        var secondsLeft = this.fetchSecondsLeft(element);

        var refreshRate = options.refreshRate || 1000;
        endTime[element.attr('product-id')] = secondsLeft + this.currentTime();
        var timeLeft = endTime[element.attr('product-id')] - this.currentTime();

        this.setFinalValue(this.formatTimeLeft(timeLeft), element, options.timeText);

        intervalId = setInterval((function() {
            timeLeft = endTime[element.attr('product-id')] - this.currentTime();
            this.setFinalValue(this.formatTimeLeft(timeLeft), element, options.timeText);
            if(timeLeft==0){clearInterval(intervalId);}
        }.bind(this)), refreshRate);

        element.intervalId = intervalId;
    };

    Timer.updateTime = function(SecondsLeft,ProductId){
        endTime[ProductId] = SecondsLeft + Math.round((new Date()).getTime() / 1000);
    };

    Timer.prototype.clearTimer = function(element){
        element.find('.seconds').text('00');
        element.find('.minutes').text('00:');
        element.find('.hours').text('00:');
        element.find('.days').text('');
    };

    Timer.prototype.currentTime = function() {
        return Math.round((new Date()).getTime() / 1000);
    };

    Timer.prototype.formatTimeLeft = function(timeLeft) {

        var lpad = function(n, width) {
            width = width || 2;
            n = n + '';

            var padded = null;

            if (n.length >= width) {
                padded = n;
            } else {
                padded = Array(width - n.length + 1).join(0) + n;
            }

            return padded;
        };

        var days, hours, minutes, remaining, seconds;
        remaining = new Date(timeLeft * 1000);
        days = remaining.getUTCDate() - 1;
        hours = remaining.getUTCHours();
        minutes = remaining.getUTCMinutes();
        seconds = remaining.getUTCSeconds();
        if(timeLeft<1){
            return [];
        }
        if (+days===0 && +hours === 0 && +minutes === 0 && +seconds === 0) {
            return [];
        } else {
            return [lpad(days), lpad(hours), lpad(minutes), lpad(seconds)];
        }
    };
    Timer.prototype.setAuctionCookie = function(productId){
        var d = new Date();
        d.setTime(d.getTime() + (60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = "auction_" + productId +"=1; " + expires;
    };

    Timer.prototype.setFinalValue = function(finalValues, element,text) {
        if(text) {
            var newtext = text.replace(/%d|%h|%m|%s/gi, function (x) {
                switch (x) {
                    case '%d':
                        return finalValues[0]?finalValues[0]:'';
                    case '%h':
                        return finalValues[1]?finalValues[1]:'00';
                    case '%m':
                        return finalValues[2]?finalValues[2]:'00';
                    case '%s':
                        return finalValues[3]?finalValues[3]:'00';

                }
            });
            element.html(newtext);
        }
        if(finalValues.length === 0){
            this.clearTimer(element);
            element.trigger('complete');
            return false;
        }
    };

    $.fn.updateTimer = function(secondleft,productId) {
        Timer.updateTime(secondleft,productId);
        return this;
    };

    $.fn.startTimer = function(options) {
        Timer.start(options, this);
        return this;
    };


})(jQuery);
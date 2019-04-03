/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            'magestore/auctionbox': 'Magestore_Auction/js/auctionbox',
            'magestore/auctiongrid':'Magestore_Auction/js/auctiongrid',
            'magestore/features': 'Magestore_Auction/js/features/owl.carousel',
            'magestore/auction': 'Magestore_Auction/js/auction',
        },
    },
    paths:{
        'magestore/countdown': 'Magestore_Auction/js/countdown/jquery.simple-timer',
    },
    shim: {
        'magestore/countdown': {
            deps: ['jquery']
        },
    }
};

/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            baseUrl: ''
        },

        /**
         * @param {String} value
         */
        prepareValue: function (value) {
            if (value) {
                this.value(this.baseUrl + $.trim(value));
            } else {
                this.value('');
            }
        },
    });
});
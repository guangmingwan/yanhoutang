define([
    'ko'
], function (ko) {
    'use strict';

    /**
     * States of Billing Address and Shipping Address Forms which can change checkout behaviour.
     * You cant change state of form by changing this observables (one way links)
     */
    return {
        /**
         * is Billing address form in edit mode
         */
        isBillingFormVisible: ko.observable(false),

        /**
         * is Billing Same As Shipping
         */
        isBillingSameAsShipping: ko.observable(true),

        /**
         * is Shipping address form in edit mode
         */
        isShippingFormVisible: ko.observable(false)
    };
});

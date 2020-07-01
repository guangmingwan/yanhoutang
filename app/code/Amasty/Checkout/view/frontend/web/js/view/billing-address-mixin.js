/**
 * Billing address view mixin for store flag is billing form in edit mode (visible)
 */
define([
    'Amasty_Checkout/js/model/address-form-state'
], function (formService) {
    'use strict';

    return function (billingAddress) {
        return billingAddress.extend({
            initObservable: function () {
                this._super();

                this.isAddressSameAsShipping.subscribe(this.billingSameAsShippingObserver, this);
                this.billingSameAsShippingObserver(this.isAddressSameAsShipping());
                this.isAddressDetailsVisible.subscribe(this.isBillingFormVisibleUpdate, this);
                this.isBillingFormVisibleUpdate(this.isAddressDetailsVisible());

                return this;
            },

            billingSameAsShippingObserver: function (state) {
                formService.isBillingSameAsShipping(Boolean(state));
            },

            isBillingFormVisibleUpdate: function (state) {
                formService.isBillingFormVisible(!state);
            }
        });
    };
});

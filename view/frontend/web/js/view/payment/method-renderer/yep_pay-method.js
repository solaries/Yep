define(
        [
            "jquery",
            'mage/url',
            "Magento_Checkout/js/view/payment/default",
            "Magento_Checkout/js/action/place-order",
            "Magento_Checkout/js/model/payment/additional-validators",
            "Magento_Checkout/js/model/quote",
            "Magento_Checkout/js/model/full-screen-loader",
            "Magento_Checkout/js/action/redirect-on-success",
        ],
        function (
                $,
                mageUrl,
                Component,
                placeOrderAction,
                additionalValidators,
                quote,
                fullScreenLoader,
                redirectOnSuccessAction
                ) {
            'use strict';

            return Component.extend({
                defaults: {
                    template: 'Yep_Pay/payment/yep_pay'
                },

                redirectAfterPlaceOrder: false,

                isActive: function () {
                    return true;
                },

                /**
                 * Provide redirect to page
                 */
                redirectToCustomAction: function (url) {
                    fullScreenLoader.startLoader();
                    window.location.replace(mageUrl.build(url));
                },

                /**
                 * @override
                 */
                afterPlaceOrder: function () {

                    var checkoutConfig = window.checkoutConfig;
                    var paymentData = quote.billingAddress();
                    var yep_payConfiguration = checkoutConfig.payment.yep_pay;

                   this.redirectToCustomAction(yep_payConfiguration.integration_type_standard_url);
                     
                },

            });
        }
);

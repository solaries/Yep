/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'yep_pay',
                component: 'Yep_Pay/js/view/payment/method-renderer/yep_pay-method'
            }
        );

        /** Add view logic here if needed */
        
        return Component.extend({});
    }
);
<?php

/**
 * Yep!Pay Magento2 Module using \Magento\Payment\Model\Method\AbstractMethod
 * Copyright (C) 2022 Yeppay.io
 * 
 * This file is part of Yep/Pay.
 * 
 * Yep/Pay is free software => you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http =>//www.gnu.org/licenses/>.
 */

namespace Yep\Pay\Controller\Payment;

class Setup extends AbstractYepPayStandard {

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        
        $message = '';
        $order = $this->orderInterface->loadByIncrementId($this->checkoutSession->getLastRealOrder()->getIncrementId());
        if ($order && $this->method->getCode() == $order->getPayment()->getMethod()) {

            try {
                return $this->processAuthorization($order);
            } catch (\Yep\Pay\External\Exception\ApiException $e) {
                $message = $e->getMessage();
                $order->addStatusToHistory($order->getStatus(), $message);
                $this->orderRepository->save($order);
            }
        }

        $this->redirectToFinal(false, $message);
    }

    protected function processAuthorization(\Magento\Sales\Model\Order $order) {
        // throw new \Exception(
        //     json_encode($this->yeppay->transaction)
        // );

        $tranx = $this->yeppay->transaction->initialize([
            // 'first_name' => $order->getCustomerFirstname(),
            // 'last_name' => $order->getCustomerLastname(),
            'amount' => $order->getGrandTotal() , // in kobo
            'email' => $order->getCustomerEmail(), // unique to customers
            'reference' => $order->getIncrementId(), // unique to transactions
           // 'currency' => $order->getCurrency(),
            'callback_url' => $this->configProvider->store->getBaseUrl() . "yeppay/payment/callback",
            // 'metadata' => array('custom_fields' => array(
            //     array(
            //         "display_name"=>"Plugin",
            //         "variable_name"=>"plugin",
            //         "value"=>"magento-2"
            //     )
            // )) 
        ]);

      //  var_dump($tranx); die();
      //console.log("Message here");

      //echo "<script>console.log('Debug Objects: " . $tranx . "' );</script>";
    //   throw new \InvalidArgumentException(
    //     json_encode($tranx)
    // );

        $redirectFactory = $this->resultRedirectFactory->create();
        // $redirectFactory->setUrl($tranx->data->authorization_url);
        $redirectFactory->setUrl($tranx->data->payment_url); 

        return $redirectFactory;
    }

}

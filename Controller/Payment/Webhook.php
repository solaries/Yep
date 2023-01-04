<?php

/**
 * Yep! Pay Magento2 Module using \Magento\Payment\Model\Method\AbstractMethod
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


use Magento\Sales\Model\Order;

class Webhook extends AbstractYepPayStandard
{

    public function execute() {
        $finalMessage = "failed";
        
        $resultFactory = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        try {

            // Retrieve the request's body and parse it as JSON
            $event = \Yep\Pay\External\Event::capture();
            http_response_code(200);
            
            /* It is a important to log all events received. Add code *
             * here to log the signature and body to db or file       */
            $this->logger->debug("YEPPAY_LOG: {$event->raw}");

            /* Verify that the signature matches one of your keys */
            $secretKey = $this->configProvider->getSecretKeyArray();
            $owner = $event->discoverOwner($secretKey);

            if (!$owner) {
                // None of the keys matched the event's signature
                $resultFactory->setContents("auth failed");
                return $resultFactory;
            }

            // Do something with $event->obj
            // Give value to your customer but don't give any output
            // Remember that this is a call from the servers and
            // Your customer is not seeing the response here at all
            switch ($event->obj->event) {
                // charge.success
                case 'charge.success':
                    if ('success' === $event->obj->data->status) {
                        $transactionDetails = $this->yeppay->transaction->verify([
                            'reference' => $event->obj->data->reference
                        ]);

                        $reference = $transactionDetails->data->reference;

                        $order = $this->orderInterface->loadByIncrementId($reference);

                        if((!$order || !$order->getId()) && isset($event->obj->data->metadata->quoteId)){


                    $reference = $transactionDetails->data->reference;
                    
                    log_transaction_success($reference);
                    //------------------------
                    $order = $this->orderInterface->loadByIncrementId($reference);
                    
                    if((!$order || !$order->getId()) && isset($event->obj->data->metadata->quoteId)){
                        
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $searchCriteriaBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
                        $searchCriteria = $searchCriteriaBuilder->addFilter('quote_id', $event->obj->data->metadata->quoteId, 'eq')->create();
                        $items = $this->orderRepository->getList($searchCriteria);
                        if($items->getTotalCount() == 1){
                            $order = $items->getFirstItem();

                        } 

                        if ($order && $order->getId()) {
                            // dispatch the `payment_verify_after` event to update the order status
                            $this->eventManager->dispatch('yeppay_payment_verify_after', [
                                "yeppay_order" => $order,
                            ]);

                            $resultFactory->setContents("success");
                            return $resultFactory;
                        }
                    }
                }
                    break;
            }
        }
        } catch (Exception $exc) {
            $finalMessage = $exc->getMessage();
        }
        
        $resultFactory->setContents($finalMessage);
        return $resultFactory;
    }

    function log_transaction_success($trx_ref){
        //send reference to logger along with plugin name and public key
        $url = "https://plugin-tracker.yeppayintegrations.com/log/charge_success";
        $plugin_name = 'magento-2';
        $public_key = $this->configProvider->getPublicKey();

        $fields = [
            'plugin_name'  => $plugin_name,
            'transaction_reference' => $trx_ref,
            'public_key' => $public_key
        ];

        $fields_string = http_build_query($fields);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

        //execute post
        $result = curl_exec($ch);
        //  echo $result;
    }
}

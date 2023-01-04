<?php

/**
 * Yep! Pay Magento2 Module using \Magento\Payment\Model\Method\AbstractMethod
 * Copyright (C) 2022 Yeppay.io
 * 
 * This file is part of Yep/Pay.
 * 
 * Yep/Pay  is free software => you can redistribute it and/or modify
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

class Callback extends AbstractYepPayStandard {

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() { 
    //   throw new \InvalidArgumentException(
    //     json_decode($_REQUEST["data"])->transaction_status =="success"
    // );
        
    if(json_decode($this->request->get("data"))->transaction_status =="success"){
        $this->messageManager->addSuccessMessage(__("Payment Successfully"));
        return $this->_redirect('checkout/onepage/success');
    } else {
        $this->messageManager->addErrorMessage(__("Payment Failed"));
        return $this->_redirect('checkout/onepage/failure');
    }




        $reference = $this->request->get('reference');
        $message = "";
        
        if (!$reference) {
            return $this->redirectToFinal(false, "No reference supplied");
        }
        
        try {
            $transactionDetails = $this->yeppay->transaction->verify([
                'reference' => $reference
            ]);
            
            $reference = explode('_', $transactionDetails->data->reference, 2);
            $reference = ($reference[0])?: 0;
            
            $order = $this->orderInterface->loadByIncrementId($reference);
            
            if ($order && $reference === $order->getIncrementId()) {
                // dispatch the `payment_verify_after` event to update the order status
                
                $this->eventManager->dispatch('yeppay_payment_verify_after', [
                    "yeppay_order" => $order,
                ]);

                return $this->redirectToFinal(true);
            }

            $message = "Invalid reference or order number";
            
        } catch (\Yep\Pay\External\Exception\ApiException $e) {
            $message = $e->getMessage();
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            
        }

        return $this->redirectToFinal(false, $message);
    }

}

<?php
/**
 * Yep! Pay Magento2 Module using \Magento\Payment\Model\Method\AbstractMethod
 * Copyright (C) 2022 YepPay.io
 * 
 * This file is part of Yep/YepPay.
 * 
 * Yep/YepPay is free software: you can redistribute it and/or modify
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Yep\Pay\Model;

use Exception;
use Magento\Payment\Helper\Data as PaymentHelper;
use Yep\Pay\Model\Payment\YepPay as YepPayModel;
use Yep\Pay\External\YepPay as YepPayLib;

class PaymentManagement implements \Yep\Pay\Api\PaymentManagementInterface
{

    protected $yeppayPaymentInstance;

    protected $yeppayLib;
    
    protected $orderInterface;
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Checkout\Model\Session $checkoutSession
            
    ) {
        $this->eventManager = $eventManager;
        $this->yeppayPaymentInstance = $paymentHelper->getMethodInstance(YepPayModel::CODE);
        
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;

        $secretKey = $this->yeppayPaymentInstance->getConfigData('live_secret_key');
        if ($this->yeppayPaymentInstance->getConfigData('test_mode')) {
            $secretKey = $this->yeppayPaymentInstance->getConfigData('test_secret_key');
        }

        $this->yeppayLib = new YepPayLib($secretKey);
    }

    /**
     * @param string $reference
     * @return bool
     */
    public function verifyPayment($reference)
    {
        
        // we are appending quoteid
        $ref = explode('_-~-_', $reference);
        $reference = $ref[0];
        $quoteId = $ref[1];
        
        try {
            $transaction_details = $this->yeppayLib->transaction->verify([
                'reference' => $reference
            ]);
            
            $order = $this->getOrder();
            //return json_encode($transaction_details);
            if ($order && $order->getQuoteId() === $quoteId && $transaction_details->data->metadata->quoteId === $quoteId) {
                
                $this->eventManager->dispatch('yeppay_payment_verify_after', [
                    "yeppay_order" => $order,
                ]);

                return json_encode($transaction_details);
            }
        } catch (Exception $e) {
            return json_encode([
                'status'=>0,
                'message'=>$e->getMessage()
            ]);
        }
        return json_encode([
            'status'=>0,
            'message'=>"quoteId doesn't match transaction"
        ]);
    }

    /**
     * Loads the order based on the last real order
     * @return boolean
     */
    private function getOrder()
    {
        // get the last real order id
        $lastOrder = $this->checkoutSession->getLastRealOrder();
        if($lastOrder){
            $lastOrderId = $lastOrder->getIncrementId();
        } else {
            return false;
        }
        
        if ($lastOrderId) {
            // load and return the order instance
            return $this->orderInterface->loadByIncrementId($lastOrderId);
        }
        return false;
    }

}

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

use Magento\Payment\Helper\Data as PaymentHelper;
use \Yep\Pay\External\YepPay;


abstract class AbstractYepPayStandard extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;
    
    /**
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface 
     */
    protected $orderRepository;
    
    /**
     *
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;
    protected $checkoutSession;
    protected $method;
    protected $messageManager;
    
    /**
     *
     * @var \Yep\Pay\Model\Ui\ConfigProvider 
     */
    protected $configProvider;
    
    /**
     *
     * @var \yeppay 
     */
    protected $yeppay;
    
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @var \Magento\Framework\App\Request\Http 
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Sales\Api\Data\OrderInterface $orderInterface,
            \Magento\Checkout\Model\Session $checkoutSession,
            PaymentHelper $paymentHelper,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Yep\Pay\Model\Ui\ConfigProvider $configProvider,
            \Magento\Framework\Event\Manager $eventManager,
            \Magento\Framework\App\Request\Http $request,
            \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;
        $this->method = $paymentHelper->getMethodInstance(\Yep\Pay\Model\Payment\YepPay::CODE);
        $this->messageManager = $messageManager;
        $this->configProvider = $configProvider;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->logger = $logger;
        
        $this->yeppay = $this->initYeppayPHP();
        
        
        parent::__construct($context);
    }
    
    protected function initYeppayPHP() {
        $secretKey = $this->method->getConfigData('live_secret_key');
        if ($this->method->getConfigData('test_mode')) {
            $secretKey = $this->method->getConfigData('test_secret_key');
        } 
        return new  YepPay($secretKey);
    }
    
    protected function redirectToFinal($successFul = true, $message="") {
        return $this->_redirect($message);

        if($successFul){
            if($message) $this->messageManager->addSuccessMessage(__($message));
            return $this->_redirect('checkout/onepage/success');
        } else {
            if($message) $this->messageManager->addErrorMessage(__($message));
            return $this->_redirect('checkout/onepage/failure');
        }
    }
}

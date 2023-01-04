<?php
namespace Yep\Pay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\Store as Store;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{

    protected $method;

    public function __construct(PaymentHelper $paymentHelper, Store $store)
    {
        $this->method = $paymentHelper->getMethodInstance(\Yep\Pay\Model\Payment\YepPay::CODE);
        $this->store = $store;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $publicKey = $this->method->getConfigData('live_public_key');
        if ($this->method->getConfigData('test_mode')) {
            $publicKey = $this->method->getConfigData('test_public_key');
        }
        
        // $integrationType = $this->method->getConfigData('integration_type')?: 'inline';
        //$integrationType = $this->method->getConfigData('integration_type')?: 'standard';
        $integrationType =  'standard';

        return [
            'payment' => [
                \Yep\Pay\Model\Payment\YepPay::CODE => [
                    'public_key' => $publicKey,
                    'integration_type' => $integrationType,
                    'api_url' => $this->store->getBaseUrl() . 'rest/',
                    'integration_type_standard_url' => $this->store->getBaseUrl() . 'yeppay/payment/setup',
                    'recreate_quote_url' => $this->store->getBaseUrl() . 'yeppay/payment/recreate',
                ]
            ]
        ];
    }
    
    public function getStore() {
        return $this->store;
    }
    
    /**
     * Get secret key for webhook process
     * 
     * @return array
     */
    public function getSecretKeyArray(){
        $data = ["live" => $this->method->getConfigData('live_secret_key')];
        if ($this->method->getConfigData('test_mode')) {
            $data = ["test" => $this->method->getConfigData('test_secret_key')];
        }
        
        return $data;
    }

    public function getPublicKey(){
        $publicKey = $this->method->getConfigData('live_public_key');
        if ($this->method->getConfigData('test_mode')) {
            $publicKey = $this->method->getConfigData('test_public_key');
        }
        return $publicKey;
    }
    
    
}

<?php
namespace Yep\Pay\Api;

/**
 * PaymentManagementInterface
 *
 * @api
 */
interface PaymentManagementInterface
{
    /**
     * @param string $reference
     * @return bool
     */
    public function verifyPayment($reference);
}

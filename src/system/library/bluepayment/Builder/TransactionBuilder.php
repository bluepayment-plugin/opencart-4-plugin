<?php

namespace BluePayment\Builder;

require_once DIR_SYSTEM . '/library/bluemedia-sdk-php/index.php';

use Registry;
use BlueMedia\OnlinePayments\Model\TransactionStandard;

final class TransactionBuilder
{
    private $registry;
    private $currency;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->registry->get('load')->library('bluepayment/Helper/ParamSuffixer');
        $this->currency = $registry->get('currency');
    }

    public function build(array $orderInfo, int $serviceId, array $requestParams = []): TransactionStandard
    {
        $order_id = $this->registry->get('ParamSuffixer')->addSuffix($orderInfo['order_id']);

        $transaction = (new TransactionStandard())->setServiceId($serviceId)
            ->setOrderId($order_id)
            ->setAmount($this->currency->format($orderInfo['total'], $orderInfo['currency_code'], $orderInfo['currency_value'], false))
            ->setCurrency($orderInfo['currency_code'])
            ->setCustomerEmail($orderInfo['email']);

        $this->setGatewayId($transaction, $requestParams);

        return $transaction;
    }

    public function setGatewayId(TransactionStandard $transactionStandard, array $requestParams = []): void
    {
        if (isset($requestParams['gateway_id'])) {
            $transactionStandard->setGatewayId($requestParams['gateway_id']);
        }
    }
}

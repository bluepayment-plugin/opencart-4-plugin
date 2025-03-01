<?php

namespace Opencart\System\Library\BluePayment\Builder;

require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

use Opencart\System\Engine\Registry;
use BlueMedia\OnlinePayments\Model\TransactionStandard;
use Opencart\System\Library\BluePayment\Helper\ParamSuffixer;

final class TransactionBuilder
{
    private $registry;
    private $currency;

	private ParamSuffixer $paramSuffixer;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
		$this->currency = $registry->get('currency');

		$this->paramSuffixer = $this->registry->get('load')->library('BluePayment/Helper/ParamSuffixer');
    }

    public function build(array $orderInfo, int $serviceId, array $requestParams = []): TransactionStandard
    {
        $order_id = $this->paramSuffixer->addSuffix($orderInfo['order_id']);

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

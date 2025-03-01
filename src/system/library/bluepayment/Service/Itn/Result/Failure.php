<?php

namespace Opencart\System\Library\BluePayment\Service\Itn\Result;

use BlueMedia\OnlinePayments\Model\ItnIn;

final class Failure extends Result
{
    public function canProcess(string $transactionStatus, int $orderStatusId): bool
    {
        return $transactionStatus === ItnIn::PAYMENT_STATUS_FAILURE &&
            $orderStatusId === $this->config->getStatusPending();
    }

    public function process(int $orderId): void
    {
        $this->model_checkout_order->addHistory(
            $orderId,
			$this->config->getStatusFailure(),
            $this->language->get('bluepayment_transaction_status_failed')
        );
    }
}

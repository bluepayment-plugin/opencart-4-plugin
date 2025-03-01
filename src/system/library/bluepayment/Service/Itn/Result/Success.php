<?php

namespace Opencart\System\Library\BluePayment\Service\Itn\Result;

use BlueMedia\OnlinePayments\Model\ItnIn;

final class Success extends Result
{
    public function canProcess(string $transactionStatus, int $orderStatusId): bool
    {
        return $transactionStatus === ItnIn::PAYMENT_STATUS_SUCCESS &&
            in_array($orderStatusId, [
				$this->config->getStatusPending(),
				$this->config->getStatusFailure()
            ]);
    }

    public function process(int $orderId): void
    {
        $this->model_checkout_order->addHistory(
            $orderId,
			$this->config->getStatusSuccess(),
            $this->language->get('bluepayment_transaction_status_success')
        );
    }
}

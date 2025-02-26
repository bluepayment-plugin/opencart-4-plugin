<?php

namespace BluePayment\Service\Itn\Result;

use BlueMedia\OnlinePayments\Model\ItnIn;

final class Success extends Result
{
    public function canProcess(string $transactionStatus, int $orderStatusId): bool
    {
        $config = $this->registry->get('ConfigProvider');

        return $transactionStatus === ItnIn::PAYMENT_STATUS_SUCCESS &&
            in_array($orderStatusId, [
                $config->getStatusPending(),
                $config->getStatusFailure()
            ]);
    }

    public function process(int $orderId): void
    {
        $this->model_checkout_order->addOrderHistory(
            $orderId,
            $this->registry->get('ConfigProvider')->getStatusSuccess(),
            $this->language->get('bluepayment_transaction_status_success')
        );
    }
}

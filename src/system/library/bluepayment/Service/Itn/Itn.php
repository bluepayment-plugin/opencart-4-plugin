<?php

namespace BluePayment\Service\Itn;

use bluepayment\Service\Itn\Result\ITNResponseType;

final class Itn
{
    private $itn_result = [];

    public function addResult(ITNResponseType $itn_result): void
    {
        $this->itn_result[] = $itn_result;
    }

    public function handle(string $transactionStatus, int $orderId, int $orderStatusId): void
    {
        foreach ($this->itn_result as $itn) {
            if ($itn->canProcess($transactionStatus, $orderStatusId)) {
                 $itn->process($orderId);
            }
        }
    }
}

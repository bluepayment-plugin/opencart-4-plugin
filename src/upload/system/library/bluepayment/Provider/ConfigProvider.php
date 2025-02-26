<?php

namespace BluePayment\Provider;

use BlueMedia\OnlinePayments\Gateway;

final class ConfigProvider
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function getGatewayMode()
    {
        return (int) $this->registry->get('config')->get('payment_bluepayment_test_mode') === 1
            ? Gateway::MODE_SANDBOX
            : Gateway::MODE_LIVE;
    }

    public function getStatusPending(): int
    {
        return (int) $this->registry->get('config')->get('payment_bluepayment_status_pending');
    }

    public function getStatusFailure(): int
    {
        return (int) $this->registry->get('config')->get('payment_bluepayment_status_failed');
    }

    public function getStatusSuccess(): int
    {
        return (int) $this->registry->get('config')->get('payment_bluepayment_status_success');
    }
}

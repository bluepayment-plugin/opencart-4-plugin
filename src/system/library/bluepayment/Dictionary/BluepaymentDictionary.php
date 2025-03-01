<?php

namespace Opencart\System\Library\BluePayment\Dictionary;

final class BluepaymentDictionary
{
    public const EXTENSION_VERSION = '1.0.8';
    private const EXTENSION_NAME = 'bluepayment';
    private const START_TRANSACTION_URI = 'index.php?route=extension/bluepayment/payment/bluepayment.processCheckout';

    public function getExtensionVersion(): string
    {
        return self::EXTENSION_VERSION;
    }

    public function getExtensionName(): string
    {
        return self::EXTENSION_NAME;
    }

    public function getStartTransactionUri(): string
    {
        return self::START_TRANSACTION_URI;
    }
}

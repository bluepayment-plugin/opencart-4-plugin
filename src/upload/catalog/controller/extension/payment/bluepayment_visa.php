<?php

require_once DIR_SYSTEM . '/library/bluemedia-sdk-php/index.php';
require_once __DIR__ . '/bluepayment_base.php';

use BlueMedia\OnlinePayments\Model\Gateway;

class ControllerExtensionPaymentBluepaymentVisa extends ControllerExtensionPaymentBluepaymentBase
{
    public function index(): string
    {
        $this->view = $this->BluepaymentDictionary->getExtensionPath() . '_separated';
        $this->view_data['gateway_id'] = Gateway::GATEWAY_ID_VISA_MOBILE;

        return parent::index();
    }
}

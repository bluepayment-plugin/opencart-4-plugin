<?php

require_once DIR_SYSTEM . '/library/bluemedia-sdk-php/index.php';

use BlueMedia\OnlinePayments\Model\Gateway;

class ControllerExtensionPaymentBluepaymentBase extends Controller
{
    protected $view_data = [];
    protected $view = '';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('bluepayment/Dictionary/BluepaymentDictionary');
        $this->load->library('bluepayment/Helper/Logger');
        $this->load->library('bluepayment/Provider/ServiceCredentialsProvider');
        $this->load->library('bluepayment/Helper/ParamSuffixer');
        $this->load->language($this->BluepaymentDictionary->getExtensionPath());
        $this->load->model('checkout/order');

        $this->view = $this->BluepaymentDictionary->getExtensionPath();
    }

    public function index(): string
    {
        $this->addLangContents();
        $this->view_data['start_transaction_uri'] = $this->BluepaymentDictionary->getStartTransactionUri();
        $this->view_data['gateway_id'] = Gateway::GATEWAY_ID_VISA_MOBILE;

        return $this->load->view($this->view, $this->view_data);
    }

    private function addLangContents(): void
    {
        $this->view_data['text_button_checkout'] = $this->language->get('text_button_checkout');
        $this->view_data['text_information_redirect'] = $this->language->get('text_information_redirect');
        $this->view_data['text_information_payment_regulations'] = $this->language->get('text_information_payment_regulations');
    }
}

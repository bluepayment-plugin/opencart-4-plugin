<?php

namespace Opencart\Catalog\Model\Extension\BluePayment\Payment;

use Opencart\System\Library\BluePayment\Dictionary\BluepaymentDictionary;
use Opencart\System\Library\BluePayment\Helper\Gateway;
use Opencart\System\Library\BluePayment\Provider\ServiceCredentialsProvider;

class BluePayment extends \Opencart\System\Engine\Model
{
	private ServiceCredentialsProvider $credentialsProvider;
	private BluepaymentDictionary $dictionary;
	private Gateway $gatewayHelper;

    public function __construct($registry)
    {
        parent::__construct($registry);

		require_once DIR_EXTENSION . 'bluepayment/system/library/bluepayment/index.php';
		require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

		$this->credentialsProvider = $this->registry->get('load')->library('BluePayment/Provider/ServiceCredentialsProvider', $this->registry);
		$this->dictionary = $this->load->library('BluePayment/Dictionary/BluepaymentDictionary', $this->registry);
		$this->gatewayHelper = $this->load->library('BluePayment/Helper/Gateway', $this->registry);
	}

    public function getMethods(array $address)
    {
		if (!$this->config->get('config_checkout_payment_address')) {
			$status = true;
		} elseif (!$this->config->get('payment_credit_card_geo_zone_id')) {
			$status = true;
		} else {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_credit_card_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

			if ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		}

        if (!$status) {
            return [];
        }

        // Verify currency
        if (!$this->credentialsProvider->currencyServiceExists()) {
            return [];
        }

        $this->load->language('extension/bluepayment/payment/bluepayment');

		$options = [
			'bluepayment' => [
				'code' => 'bluepayment.bluepayment',
				'name' => $this->language->get('text_title'),
			],
		];

		$currency = $this->credentialsProvider->getCurrentCurrency();
		if ($this->gatewayHelper->isVisaEnabled($currency)) {
			$options['bluepayment_visa'] = [
				'code' => 'bluepayment.bluepayment_visa',
				'name' => $this->language->get('text_title_visa'),
			];
		}

        return [
            'code' => 'bluepayment',
            'name' => $this->language->get('text_name'),
			'option' => $options,
            'sort_order' => $this->config->get('payment_bluepayment_sort_order'),
        ];
    }
}

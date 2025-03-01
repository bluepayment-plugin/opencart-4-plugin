<?php

namespace Opencart\System\Library\BluePayment\Provider;

use Opencart\System\Engine\Registry;
use Opencart\System\Library\BluePayment\ValueObject\ServiceCredentials;

final class ServiceCredentialsProvider
{
	private $registry;
	private $current_currency;

	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
		$this->current_currency = $this->registry->get('session')->data['currency'];

		$this->registry->get('load')->model('setting/setting');
	}

	public function currencyServiceExists(): bool
	{
		return isset($this->getAllServiceCredentials()[$this->current_currency]);
	}

	public function getCurrencyServiceCredentials(string $currency = ''): ServiceCredentials
	{
		$service_credentials = $this->getAllServiceCredentials();

		if (!empty($currency)) {
			$this->current_currency = $currency;
		}

		return new ServiceCredentials(
			(int) $service_credentials[$this->current_currency]['service_id'],
			$service_credentials[$this->current_currency]['shared_key']
		);
	}

	public function getCurrentCurrency(): string
	{
		return $this->current_currency;
	}

	private function getAllServiceCredentials(): array
	{
		$settings = $this->registry->get('model_setting_setting')->getSetting('payment_bluepayment');

		return $settings['payment_bluepayment_currency'] ?? [];
	}
}

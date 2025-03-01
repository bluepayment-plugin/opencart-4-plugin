<?php

namespace Opencart\Admin\Controller\Extension\BluePayment\Payment;

use Psr\Log\LogLevel;

class BluePayment extends \Opencart\System\Engine\Controller
{
	public const STATUS_PENDING = 1;
	public const STATUS_PROCESSING = 2;
	public const STATUS_FAILED = 10;

	private $inputs = [
		'payment_bluepayment_status',
		'payment_bluepayment_test_mode',
		'payment_bluepayment_status_pending',
		'payment_bluepayment_status_success',
		'payment_bluepayment_status_failed',
	];

	public function __construct($registry)
	{
		parent::__construct($registry);

		require_once DIR_EXTENSION . 'bluepayment/system/library/bluepayment/index.php';
		require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

		$this->load->library('BluePayment/Helper/Logger', $registry);
		$this->load->library('BluePayment/Validator/AdminFormValidator', $registry);
	}

	public function index()
	{
		$this->load->language('extension/bluepayment/payment/bluepayment');

		$this->load->model('localisation/currency');
		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->load->model('extension/bluepayment/payment/bluepayment');

		$this->model_extension_bluepayment_payment_bluepayment->checkUpdate();

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('../extension/bluepayment/admin/view/javascript/bluepayment/bluepayment.js');
		$this->document->addStyle('../extension/bluepayment/admin/view/stylesheet/bluepayment/bluepayment.css');

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$this->request->post['payment_bluepayment_currency'] = $this->prepareCurrenciesData(
				$this->request->post['payment_bluepayment_currency']
			);

			$data = $this->library_BluePayment_Validator_AdminFormValidator->validate();

			if (empty($data)) {
				$this->model_setting_setting->editSetting('payment_bluepayment', $this->request->post);

				$this->session->data['message_success'] = $this->language->get('text_success');
				$this->library_BluePayment_Helper_Logger->log(
					LogLevel::INFO,
					'[BM Bluepayment] Module settings updated with data',
					[
						'POST' => json_encode($this->request->post)
					]
				);

				$this->response->redirect(
					$this->url->link('extension/bluepayment/payment/bluepayment?user_token=' . $this->session->data['user_token'], true)
				);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['download'] = $this->url->link(
			'extension/bluepayment/payment/bluepayment/downloadLog', 'user_token=' . $this->session->data['user_token'], true
		);

		if (isset($this->session->data['message_warning'])) {
			$data['message_warning'] = $this->session->data['message_warning'];

			unset($this->session->data['message_warning']);
		}

		if (isset($this->session->data['message_success'])) {
			$data['message_success'] = $this->session->data['message_success'];

			unset($this->session->data['message_success']);
		}

		$data += $this->library_BluePayment_Helper_Logger->getRecentLog();
		$data += $this->generateBreadcrumbs();
		$data += $this->fillFormFields();
		$data['payment_bluepayment_version'] = $this->config->get('payment_bluepayment_version');

		$data['save'] = $this->url->link('extension/bluepayment/payment/bluepayment.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['currencies'] = $this->model_localisation_currency->getCurrencies();
		$data['log_files'] = $this->library_BluePayment_Helper_Logger->getFormattedFileList();
		$data['refresh_log_uri'] = $this->url->link(
			'extension/bluepayment/payment/bluepayment/refreshLog', 'user_token=' . $this->session->data['user_token'], true
		);
		$data['info_log_loading'] = $this->language->get('info_log_loading');

		$this->response->setOutput($this->load->view('extension/bluepayment/payment/bluepayment', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/bluepayment/payment/bluepayment');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bluepayment/payment/bluepayment')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('payment_bluepayment', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function install()
	{
		$this->load->model('extension/bluepayment/payment/bluepayment');
		$this->model_extension_bluepayment_payment_bluepayment->install();
	}

	public function uninstall()
	{
		$this->load->model('extension/bluepayment/payment/bluepayment');
		$this->model_extension_bluepayment_payment_bluepayment->uninstall();
	}

	public function refreshLog(): void
	{
		$this->library_BluePayment_Helper_Logger->refreshLog();
	}

	public function downloadLog(): void
	{
		$this->library_BluePayment_Helper_Logger->download();
	}

	private function prepareCurrenciesData($currencies)
	{
		return array_filter($currencies, function ($currency) {
			return empty($currency['service_id']) === false || empty($currency['shared_key']) === false;
		});
	}

	private function generateBreadcrumbs()
	{
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/bluepayment/payment/bluepayment', 'user_token=' . $this->session->data['user_token'], true)
		];

		return $data;
	}

	private function fillFormFields()
	{
		$data = [];

		foreach ($this->inputs as $input) {
			if (isset($this->request->post[$input])) {
				$data[$input] = $this->request->post[$input];
			} else {
				$data[$input] = $this->config->get($input);
			}
		}

		if (isset($this->request->post['payment_bluepayment_currency'])) {
			$currency_settings = $this->request->post['payment_bluepayment_currency'];
		} else {
			$currency_settings = $this->config->get('payment_bluepayment_currency');
		}

		if (!empty($currency_settings)) {
			foreach ($currency_settings as $currency_code => $currency_setting) {
				$data['payment_bluepayment_currency_' . $currency_code . '_service_id'] = $currency_setting['service_id'];
				$data['payment_bluepayment_currency_' . $currency_code . '_shared_key'] = $currency_setting['shared_key'];
			}
		}

		return $data;
	}
}

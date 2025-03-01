<?php

namespace Opencart\Catalog\Controller\Extension\BluePayment\Payment;

require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

use BlueMedia\OnlinePayments\Action\ITN\Transformer;
use Opencart\System\Library\BluePayment\Builder\ItnDataBuilder;
use Opencart\System\Library\BluePayment\Builder\TransactionBuilder;
use Opencart\System\Library\BluePayment\Dictionary\BluepaymentDictionary;
use Opencart\System\Library\BluePayment\Helper\Logger;
use Opencart\System\Library\BluePayment\Helper\ParamSuffixer;
use Opencart\System\Library\BluePayment\Provider\ServiceCredentialsProvider;
use Opencart\System\Library\BluePayment\Service\Itn\Itn;
use Opencart\System\Library\BluePayment\Service\Itn\Result\Result;
use Psr\Log\LogLevel;
use BlueMedia\OnlinePayments\Gateway;

class Bluepayment extends \Opencart\System\Engine\Controller
{
	protected BluepaymentDictionary $dictionary;
	protected ServiceCredentialsProvider $serviceCredentialsProvider;
	protected Logger $logger;
	protected ParamSuffixer $paramSuffixer;

	public function __construct($registry)
	{
		parent::__construct($registry);

		require_once DIR_EXTENSION . 'bluepayment/system/library/bluepayment/index.php';
		require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

		$this->dictionary = $this->load->library('BluePayment/Dictionary/BluepaymentDictionary', $registry);
		$this->serviceCredentialsProvider = $this->load->library('BluePayment/Provider/ServiceCredentialsProvider', $registry);
		$this->logger = $this->load->library('BluePayment/Helper/Logger', $registry);
		$this->paramSuffixer = $this->load->library('BluePayment/Helper/ParamSuffixer', $registry);

		$this->load->language('extension/bluepayment/payment/bluepayment');
		$this->load->model('checkout/order');
	}

	public function index(): string
	{
		$viewData = [
			'text_button_checkout' => $this->language->get('text_button_checkout'),
			'text_information_redirect' => $this->language->get('text_information_redirect'),
			'text_information_payment_regulations' => $this->language->get('text_information_payment_regulations'),
			'start_transaction_uri' => $this->dictionary->getStartTransactionUri(),
			'language' => $this->config->get('config_language'),
		];

		if (isset($this->session->data['payment_method'])) {
			$paymentMethod = explode('.', $this->session->data['payment_method']['code'] ?? '');
			if (count($paymentMethod) === 2) {
				$gatewayName = $paymentMethod[1];

				$gatewayId = false;
				switch ($gatewayName) {
					case 'bluepayment_visa':
						$gatewayId = \BlueMedia\OnlinePayments\Model\Gateway::GATEWAY_ID_VISA_MOBILE;
						break;
				}

				if ($gatewayId) {
					$viewData['gateway_id'] = $gatewayId;
				}
			}
		}

		return $this->load->view('extension/bluepayment/payment/bluepayment', $viewData);
	}

	public function processCheckout(): void
	{
		/** @var TransactionBuilder $transactionBuilder */
		$transactionBuilder = $this->load->library('BluePayment/Builder/TransactionBuilder', $this->registry);

		$json = [];

		if (isset($this->session->data['order_id'])) {
			$this->load->model('checkout/order');

			$orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if (!$orderInfo) {
				$json['redirect'] = $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true);

				unset($this->session->data['order_id']);
			}
		} else {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || strstr($orderInfo['payment_method']['code'], '.', true) != 'bluepayment') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			try {
				$credentials = $this->serviceCredentialsProvider->getCurrencyServiceCredentials();

				$gateway = new Gateway(
					$credentials->getServiceId(),
					$credentials->getSharedKey(),
					$this->getGatewayMode()
				);

				$transactionData = $transactionBuilder->build(
					$orderInfo,
					$credentials->getServiceId(),
					$this->request->post
				);

				$this->model_checkout_order->addHistory(
					$this->session->data['order_id'],
					$this->config->get('payment_bluepayment_status_pending'),
					$this->language->get('text_history_payment_pending')
				);

				$result = $gateway->doInitTransaction($transactionData);

				$redirectUrl = property_exists($result, 'redirecturl') ? (string)$result->redirecturl : null;

				// Clear cart and session data if correctly initialized transaction - user not always returning to TYP page, so we need to remove on this step
				if (isset($this->session->data['order_id'])) {
					$this->cart->clear();

					unset($this->session->data['order_id']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['comment']);
					unset($this->session->data['agree']);
					unset($this->session->data['coupon']);
					unset($this->session->data['reward']);
				}

				$json['redirect'] = $redirectUrl;
			} catch (\Throwable $e) {
				$this->logger->log(
					LogLevel::WARNING,
					'Exception in ' . __METHOD__,
					[
						'Order ID' => $this->session->data['order_id'],
						'data' => $e
					]
				);

				$json['redirect'] = $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function paymentReturn(): void
	{
		$this->response->redirect($this->url->link('checkout/success', '', true));
	}

	private function getGatewayMode(): string
	{
		return (int) $this->config->get('payment_bluepayment_test_mode') === 1
			? Gateway::MODE_SANDBOX
			: Gateway::MODE_LIVE;
	}

	public function processItn()
	{
		/** @var ItnDataBuilder $itnDataBuilder */
		$itnDataBuilder = $this->load->library('BluePayment/Builder/ItnDataBuilder', $this->registry);
		/** @var Result $result */
		$result = $this->load->library('BluePayment/Service/Itn/Result/Result', $this->registry);
		/** @var Itn $itn */
		$itn = $this->load->library('BluePayment/Service/Itn/Itn', $this->registry);

		try {
			$transactionConfirmed = true;

			$transaction = Gateway::doItnIn();
			$service_credentials = $this->serviceCredentialsProvider->getCurrencyServiceCredentials($transaction->getCurrency());

			$gateway = new Gateway(
				$service_credentials->getServiceId(),
				$service_credentials->getSharedKey(),
				$this->getGatewayMode()
			);

			$orderId = $this->paramSuffixer->removeSuffix($transaction->getOrderId());
			$order = $this->model_checkout_order->getOrder($orderId);

			$dataToHash = $itnDataBuilder->build(Gateway::getItnInXml());
			$generatedOrderHash = Gateway::generateHash($dataToHash);

			if ($generatedOrderHash !== $transaction->getHash()) {
				$transactionConfirmed = false;

				$this->logger->log(
					LogLevel::WARNING,
					'Hash mismatch',
					[
						'Order ID' => $orderId,
						'Generated hash' => $generatedOrderHash,
						'Received hash' => $transaction->getHash(),
						'Transaction data' => $dataToHash
					]
				);
			}

			if ($transactionConfirmed) {
				$success = $this->load->library('BluePayment/Service/Itn/Result/Success', $this->registry);
				$failure = $this->load->library('BluePayment/Service/Itn/Result/Failure', $this->registry);
				$pending = $this->load->library('BluePayment/Service/Itn/Result/Pending', $this->registry);

				$itn->addResult($failure);
				$itn->addResult($success);
				$itn->addResult($pending);
				$itn->handle($transaction->getPaymentStatus(), (int) $orderId, (int) $order['order_status_id']);
			}

			$response = $gateway->doItnInResponse($transaction, $transactionConfirmed);

			$this->logger->log(
				LogLevel::INFO,
				'ITN response in ' . __METHOD__,
				[
					'Order ID' => $orderId,
					'ITN Data' => json_encode(Transformer::modelToArray($transaction)),
					'Response' => json_encode($response)
				]
			);

			$this->response->addHeader('Content-Type: application/xml'); // Change to XML
			$this->response->setOutput($response);
		} catch (\Throwable $e) {
			$this->logger->log(
				LogLevel::WARNING,
				'Exception in ' . __METHOD__,
				[
					'Order ID' => $orderId,
					'data' => $e
				]
			);
			exit;
		}
	}
}

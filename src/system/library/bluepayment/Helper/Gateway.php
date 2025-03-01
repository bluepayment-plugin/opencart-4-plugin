<?php

namespace Opencart\System\Library\BluePayment\Helper;

require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

use BlueMedia\OnlinePayments\Model\Gateway as GatewwayModel;
use Opencart\System\Engine\Registry;
use BlueMedia\OnlinePayments\Gateway as BlueMediaGateway;
use Opencart\System\Library\BluePayment\Provider\ConfigProvider;
use Opencart\System\Library\BluePayment\Provider\ServiceCredentialsProvider;

final class Gateway
{
    private $registry;
    private $currency;

    private $gatewayList = null;

	private ConfigProvider $configProvider;
	private ServiceCredentialsProvider $credentialsProvider;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->currency = $registry->get('currency');

		$this->credentialsProvider = $this->registry->get('load')->library('BluePayment/Provider/ServiceCredentialsProvider', $this->registry);
        $this->configProvider = $this->registry->get('load')->library('BluePayment/Provider/ConfigProvider', $registry);
    }

    public function isVisaEnabled(string $currency): bool
    {
        $list = $this->getGatewayList($currency);

        if (! is_array($list)) {
            return false;
        }

		/** @var \BlueMedia\OnlinePayments\Model\Gateway $gateway */
		foreach ($list as $gateway) {
            if ($gateway->isGateway(GatewwayModel::GATEWAY_ID_VISA_MOBILE)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return GatewwayModel[]
     */
    private function getGatewayList(string $currency): array
    {
        if ($this->gatewayList === null) {
            $credentials = $this->credentialsProvider->getCurrencyServiceCredentials();

            $blueMediaGateway = new BlueMediaGateway(
                $credentials->getServiceId(),
                $credentials->getSharedKey(),
                $this->configProvider->getGatewayMode()
            );

            try {
                $this->gatewayList = $blueMediaGateway->doGatewayList($currency)->getGateways();
            } catch (\Exception $e) {
                echo 'x';
                var_dump($e->getMessage());
                die;
            }
        }

        return $this->gatewayList;
    }
}

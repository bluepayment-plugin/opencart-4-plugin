<?php

namespace BluePayment\Helper;

require_once DIR_SYSTEM . '/library/bluemedia-sdk-php/index.php';

use BlueMedia\OnlinePayments\Model\Gateway as GatewwayModel;
use Registry;
use BlueMedia\OnlinePayments\Gateway as BlueMediaGateway;

final class Gateway
{
    private $registry;
    private $currency;

    private $gatewayList = null;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->currency = $registry->get('currency');

        $this->registry->get('load')->library('bluepayment/Provider/ConfigProvider');
        $this->registry->get('load')->library('bluepayment/Helper/Logger');
    }

    public function isVisaEnabled(string $currency): bool
    {
        $list = $this->getGatewayList($currency);

        if (! is_array($list)) {
            return false;
        }

        foreach ($list as $payway) {
            if ($payway->isGateway(GatewwayModel::GATEWAY_ID_VISA_MOBILE)) {
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
            $credentials = $this->registry
                ->get('ServiceCredentialsProvider')
                ->getCurrencyServiceCredentials();

            $blueMediaGateway = new BlueMediaGateway(
                $credentials->getServiceId(),
                $credentials->getSharedKey(),
                $this->registry->get('ConfigProvider')->getGatewayMode()
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

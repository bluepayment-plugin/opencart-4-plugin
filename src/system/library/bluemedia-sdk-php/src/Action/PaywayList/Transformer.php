<?php

namespace BlueMedia\OnlinePayments\Action\PaywayList;

use BlueMedia\OnlinePayments\Gateway;
use BlueMedia\OnlinePayments\Model\Currency;
use BlueMedia\OnlinePayments\Model\Gateway as GatewayModel;
use BlueMedia\OnlinePayments\Model\GatewayList;
use DateTime;
use DateTimeZone;
use SimpleXMLElement;

class Transformer
{
    /**
     * Transforms model into an array.
     *
     * @param GatewayList $model
     *
     * @return array
     */
    public static function modelToArray(GatewayList $model)
    {
        $result              = [];
        $result['serviceID'] = $model->getServiceId();
        $result['messageID'] = $model->getMessageId();
        $result['gateway']   = [];

        if (is_array($model->getGateways())) {
            foreach ($model->getGateways() as $key => $gateway) {
                if ($gateway instanceof GatewayModel) {
                    $result['gateway'][] = [
                        'gatewayID'   => $gateway->getGatewayId(),
                        'gatewayName' => $gateway->getGatewayName(),
                        'gatewayType' => $gateway->getGatewayType(),
                        'bankName'    => $gateway->getBankName(),
                        'iconURL'     => $gateway->getIconUrl(),
                        'stateDate'  => ($gateway->getStateDate() instanceof DateTime) ?
                            $gateway->getStateDate()->format(Gateway::DATETIME_FORMAT_LONGER) : '',
                    ];
                }
            }
        }

        $result['hash'] = $model->getHash();

        return $result;
    }

    /**
     * Transforms XML to model.
     *
     * @param object $json
     *
     * @return GatewayList
     */
    public static function toModel(object $json)
    {
        $model = new GatewayList();

        if ($json->serviceID) {
            $model->setServiceId((string)$json->serviceID);
        }

        if ($json->messageID) {
            $model->setMessageId((string)$json->messageID);
        }

        if (isset($json->gatewayList)) {
            foreach ($json->gatewayList as $key => $gateway) {
                $gatewayModel = new GatewayModel();
                $gatewayModel->setGatewayId((string)$gateway->gatewayID)
                    ->setGatewayName((string)$gateway->gatewayName)
                    ->setState((string) $gateway->state);

                if (isset($gateway->gatewayType)) {
                    $gatewayModel->setGatewayType((string)$gateway->gatewayType);
                }

                if (isset($gateway->bankName)) {
                    $gatewayModel->setBankName((string)$gateway->bankName);
                }

                if (isset($gateway->iconURL)) {
                    $gatewayModel->setIconUrl((string)$gateway->iconURL);
                }

                if (isset($gateway->stateDate)) {
                    $gatewayModel->setStateDate(
                        DateTime::createFromFormat(
                            Gateway::DATETIME_FORMAT_LONGER,
                            (string)$gateway->stateDate,
                            new DateTimeZone(Gateway::DATETIME_TIMEZONE)
                        )
                    );
                }

                if (isset($gateway->gatewayDescription)) {
                    $gatewayModel->setGatewayDescription((string) $gateway->gatewayDescription);
                }

                if (isset($gateway->inBalanceAllowed)) {
                    $gatewayModel->setInBalanceAllowed((bool) $gateway->inBalanceAllowed);
                }

                foreach ($gateway->currencyList as $currency) {
                    $currencyModel = new Currency();
                    $currencyModel->setCurrency($currency->currency);

                    if (isset($currency->minAmount)) {
                        $currencyModel->setMinAmount((float) $currency->minAmount);
                    }

                    if (isset($currency->maxAmount)) {
                        $currencyModel->setMaxAmount((float) $currency->maxAmount);
                    }

                    $gatewayModel->addCurrency($currencyModel);
                }

                $model->addGateway($gatewayModel);
                unset($gatewayModel, $gateway);
            }
        }

        if ($json->hash) {
            $model->setHash((string)$json->hash);
        }

        return $model;
    }
}

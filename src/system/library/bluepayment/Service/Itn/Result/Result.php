<?php

namespace BluePayment\Service\Itn\Result;

require_once 'ITNResponseType.php';

class Result extends ITNResponseType
{
    protected $registry;
    protected $language;
    protected $model_checkout_order;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->model_checkout_order = $this->registry->get('model_checkout_order');
        $this->language = $this->registry->get('language');
        $this->registry->get('load')->library('bluepayment/Provider/ConfigProvider');
    }
}

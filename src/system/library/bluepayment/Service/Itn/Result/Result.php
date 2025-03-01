<?php

namespace Opencart\System\Library\BluePayment\Service\Itn\Result;

use Opencart\System\Library\BluePayment\Provider\ConfigProvider;

require_once 'ITNResponseType.php';

class Result extends ITNResponseType
{
    protected $registry;
    protected $language;
    protected $model_checkout_order;

	protected ConfigProvider $config;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->model_checkout_order = $this->registry->get('model_checkout_order');
        $this->language = $this->registry->get('language');

		$this->config = $this->registry->get('load')->library('BluePayment/Provider/ConfigProvider', $this->registry);
    }
}

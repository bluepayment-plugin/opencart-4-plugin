<?php

use BluePayment\Dictionary\BluepaymentDictionary;

class ModelExtensionPaymentBluepayment extends Model
{
    public const VERSION = BluepaymentDictionary::EXTENSION_VERSION;

    public const CONFIG_CODE = 'payment_bluepayment';
    public const VERSION_CONFIG_KEY = 'payment_bluepayment_version';

    public function install()
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(self::CONFIG_CODE, [
            'payment_bluepayment_status' => 0,
            'payment_bluepayment_test_mode' => 1,
            'payment_bluepayment_currency' => null,
            'payment_bluepayment_status_pending' => ControllerExtensionPaymentBluepayment::STATUS_PENDING,
            'payment_bluepayment_status_failed' => ControllerExtensionPaymentBluepayment::STATUS_FAILED,
            'payment_bluepayment_status_success' => ControllerExtensionPaymentBluepayment::STATUS_PROCESSING,
            self::VERSION_CONFIG_KEY => '1.0.0',
        ]);

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepayment_gateway` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`gateway_currency` VARCHAR(3) NOT NULL,
                `gateway_status` INT(1) NOT NULL DEFAULT 1,
                `gateway_id` INT(11) NOT NULL,
                `bank_name` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` VARCHAR(255) NOT NULL,
                `sort_order` VARCHAR(255) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `logo_url` VARCHAR(255) NOT NULL,
                `is_separated_method` INT(1) NOT NULL DEFAULT 0,
                `force_disable` INT(1) NOT NULL DEFAULT 0,
                `min_amount` DECIMAL(20, 4) NOT NULL,
                `max_amount` DECIMAL(20, 4) NOT NULL,
                `min_validity_time` INT(11) NOT NULL,
				PRIMARY KEY `id` (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepayment_gateway`;");


        $settings_to_delete = [
            'payment_bluepayment_status',
            'payment_bluepayment_test_mode',
            'payment_bluepayment_currency',
            'payment_bluepayment_status_pending',
            'payment_bluepayment_status_failed',
            'payment_bluepayment_status_success',
            self::VERSION_CONFIG_KEY,
        ];
        foreach ($settings_to_delete as $setting_to_delete) {
            $this->model_setting_setting->deleteSetting($setting_to_delete);
        }
    }

    public function checkUpdate()
    {
        $this->load->model('setting/setting');
        $version = $this->model_setting_setting->getSettingValue(self::VERSION_CONFIG_KEY);

        version_compare($version, '1.0.5', '<') === true && $this->update105();

        if ($version !== self::VERSION) {
            $this->model_setting_setting->editSettingValue(self::CONFIG_CODE, self::VERSION_CONFIG_KEY, self::VERSION);
        }
    }

    /**
     * Add VISA Mobile gateway
     *
     * @return void
     */
    public function update105(): void
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('payment', 'bluepayment_visa');");

        $this->model_setting_setting->editSetting(self::CONFIG_CODE . '_visa', [
            'payment_bluepayment_visa_status' => 1,
        ]);
    }

}
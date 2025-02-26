<?php

class ModelExtensionPaymentBluepaymentVisa extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('bluepayment/Dictionary/BluepaymentDictionary');
        $this->load->library('bluepayment/Helper/Gateway');
    }

    public function getMethod($address, $total)
    {
        // Verify zone
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_bluepayment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        if ($this->config->get('payment_bluepayment_total') > 0 && $this->config->get('payment_bluepayment_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_bluepayment_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        if (!$status) {
            return [];
        }

        // Verify currency
        $this->load->library('bluepayment/Provider/ServiceCredentialsProvider');
        if (!$this->ServiceCredentialsProvider->currencyServiceExists()) {
            return [];
        }

        $currency = $this->ServiceCredentialsProvider->getCurrentCurrency();
        // Is gateway available?
        if (!$currency || !$this->Gateway->isVisaEnabled($currency)) {
            return [];
        }

        $this->load->language($this->BluepaymentDictionary->getExtensionPath());

        return [
            'code' => $this->BluepaymentDictionary->getExtensionName() . '_visa',
            'title' => $this->language->get('text_title_visa'),
            'sort_order' => $this->config->get('payment_bluepayment_sort_order'),
            'terms' => '',
        ];
    }
}

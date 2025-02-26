<?php

class ModelExtensionPaymentBluepaymentHelper extends Model
{
    public function isVisaAvailable($address, $total)
    {
        // Verify zone

        return [
            'code' => $this->BluepaymentDictionary->getExtensionName(),
            'title' => $this->language->get('text_title'),
            'sort_order' => $this->config->get('payment_bluepayment_sort_order'),
            'terms' => '',
        ];
    }
}

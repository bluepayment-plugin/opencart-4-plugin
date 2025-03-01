<?php

namespace BlueMedia\OnlinePayments\Model;

use DomainException;

class Currency extends AbstractModel
{
    /**
     * Currency code.
     *
     * @var string
     */
    private $currency = '';

    /**
     * @var float|null
     */
    private $minAmount = null;

    /**
     * @var float|null
     */
    private $maxAmount = null;

    /**
     * Validates model.
     *
     * @throws DomainException
     */
    public function validate()
    {
        if (empty($this->currency)) {
            throw new DomainException('Currency cannot be empty');
        }
    }

    public function setCurrency(string $currency): Currency
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setMinAmount(?float $minAmount): Currency
    {
        $this->minAmount = $minAmount;

        return $this;
    }

    public function getMinAmount(): ?float
    {
        return $this->minAmount;
    }

    public function setMaxAmount(?float $maxAmount): Currency
    {
        $this->maxAmount = $maxAmount;

        return $this;
    }

    public function getMaxAmount(): ?float
    {
        return $this->maxAmount;
    }
}

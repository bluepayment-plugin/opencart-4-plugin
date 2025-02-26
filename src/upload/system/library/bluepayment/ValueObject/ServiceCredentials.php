<?php

namespace BluePayment\ValueObject;

final class ServiceCredentials
{
    private $service_id;
    private $shared_key;

    public function __construct(int $service_id, string $shared_key)
    {
        $this->service_id = $service_id;
        $this->shared_key = $shared_key;
    }

    public function getServiceId(): int
    {
        return $this->service_id;
    }

    public function getSharedKey(): string
    {
        return $this->shared_key;
    }
}

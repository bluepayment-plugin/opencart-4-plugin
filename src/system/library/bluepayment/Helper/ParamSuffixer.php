<?php

namespace BluePayment\Helper;

final class ParamSuffixer
{
    public function addSuffix(string $param): string
    {
        return sprintf('%s-%s', $param, time());
    }

    public function removeSuffix(string $param): string
    {
        $param_parts = explode('-', $param);

        return $param_parts[0];
    }
}

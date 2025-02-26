<?php

namespace BluePayment\Builder;

use bluepayment\ValueObject\ServiceCredentials;
use BlueMedia\OnlinePayments\Model\ItnIn;
use SimpleXMLElement;

final class ItnDataBuilder
{

    /**
     * @var string[]
     */
    private $checkHashArray = [];

    public function build(SimpleXMLElement $response): array
    {
        $this->checkInList($response);

        return $this->checkHashArray;
    }

    /**
     * @param  array|object  $list
     *
     * @return void
     */
    private function checkInList($list)
    {
        foreach ((array)$list as $key => $row) {
            if (strtolower($key) === 'hash') {
                continue;
            }

            if (is_object($row)) {
                $this->checkInList($row);
            } else {
                $this->checkHashArray[] = $row;
            }
        }
    }
}

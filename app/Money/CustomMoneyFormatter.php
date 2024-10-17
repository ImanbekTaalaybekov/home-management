<?php

namespace App\Money;

use Money\Money;
use Money\MoneyFormatter;

class CustomMoneyFormatter implements MoneyFormatter
{
    public function format(Money $money): string
    {
        $amount = $money->getAmount();

        $amount = number_format($amount / 100, 0, '.', ' ');

        $currencyCode = $money->getCurrency()->getCode();

        switch ($currencyCode) {
            case 'RUB':
                return $amount . '₽';
            case 'USD':
                return '$' . $amount;
            case 'KGS':
                return $amount . ' СОМ';
            default:
                return $amount . ' ' . $currencyCode;
        }
    }
}

<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum CurrencyEnum: string
{
    use EnumTrait;


    case KGS = 'KGS';
    case RUB = 'RUB';
    case USD = 'USD';
    case EUR = 'EUR';

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::KGS => 'Сом',
            self::RUB => 'Рубль',
            self::USD => 'Доллар',
            self::EUR => 'Евро',
        };

        return __($value, locale: $lang);
    }

}

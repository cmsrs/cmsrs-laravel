<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Shop;

class PaymentService
{
    const KEY_CASH = 'cash';

    const KEY_BANK = 'bank';

    const KEY_PAYU = 'payu';

    /**
     * @return array<string, array{name: string}>|array{name: string}
     */
    public static function getPayment(?string $key = null): array
    {
        $payments = [
            self::KEY_CASH => ['name' => 'Cash on delivery'],
            self::KEY_BANK => ['name' => 'Bank transfer'],
            self::KEY_PAYU => ['name' => 'PayU payment'],
        ];

        if (empty($key)) {
            return $payments;
        }

        if (empty($payments[$key])) {
            throw new \Exception('I cant get payment where key= '.$key);
        }

        return $payments[$key];
    }
}

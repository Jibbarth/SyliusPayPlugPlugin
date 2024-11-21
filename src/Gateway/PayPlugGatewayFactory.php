<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Gateway;

final class PayPlugGatewayFactory extends AbstractGatewayFactory
{
    public const FACTORY_NAME = 'payplug';
    public const FACTORY_TITLE = 'PayPlug';

    // Custom gateway configuration keys
    public const ONE_CLICK = 'oneClick';
    public const PAYMENT_INTEGRATED = 'paymentIntegrated';

    public const AUTHORIZED_CURRENCIES = [
        'EUR' => [
            'min_amount' => 99,
            'max_amount' => 2000000,
        ],
    ];
}

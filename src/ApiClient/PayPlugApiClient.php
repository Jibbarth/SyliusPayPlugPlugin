<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\ApiClient;

use Payplug\Resource\IVerifiableAPIResource;
use Payplug\Resource\Payment;
use Payplug\Resource\Refund;
use PayPlug\SyliusPayPlugPlugin\Gateway\PayPlugGatewayFactory;
use Webmozart\Assert\Assert;

class PayPlugApiClient implements PayPlugApiClientInterface
{
    private const CURRENT_API_VERSION = '2019-08-06';

    /** @var \Payplug\Payplug */
    private $configuration;
    /**
     * @var string
     */
    private $factoryName;

    public function __construct(string $secretKey, ?string $factoryName = null)
    {
        $this->configuration = \Payplug\Payplug::init([
            'secretKey' => $secretKey,
            'apiVersion' => self::CURRENT_API_VERSION,
        ]);
        $this->factoryName = $factoryName ?? PayPlugGatewayFactory::FACTORY_NAME;
    }

    /**
     * @deprecated use DI instead to get a pre-configured client
     */
    public function initialise(string $secretKey): void
    {
        \Payplug\Payplug::setSecretKey($secretKey);
    }

    public function getGatewayFactoryName(): string
    {
        return $this->factoryName;
    }

    public function getPermissions(): array
    {
        return \Payplug\Authentication::getPermissions($this->configuration) ?? [];
    }

    public function createPayment(array $data): Payment
    {
        $payment = \Payplug\Payment::create($data, $this->configuration);
        Assert::isInstanceOf($payment, Payment::class);

        return $payment;
    }

    public function refundPayment(string $paymentId): Refund
    {
        /** @var Refund|null $refund */
        $refund = \Payplug\Refund::create($paymentId, null, $this->configuration);
        Assert::isInstanceOf($refund, Refund::class);

        return $refund;
    }

    public function refundPaymentWithAmount(string $paymentId, int $amount, int $refundId): Refund
    {
        /** @var Refund|null $refund */
        $refund = \Payplug\Refund::create($paymentId, [
            'amount' => $amount,
            'metadata' => ['refund_from_sylius' => true],
        ], $this->configuration);
        Assert::isInstanceOf($refund, Refund::class);

        return $refund;
    }

    public function treat(string $input): IVerifiableAPIResource
    {
        return \Payplug\Notification::treat($input, $this->configuration);
    }

    public function retrieve(string $paymentId): Payment
    {
        $payment = \Payplug\Payment::retrieve($paymentId, $this->configuration);
        Assert::isInstanceOf($payment, Payment::class);

        return $payment;
    }
}

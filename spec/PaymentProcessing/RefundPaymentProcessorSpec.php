<?php

declare(strict_types=1);

namespace spec\PayPlug\SyliusPayPlugPlugin\PaymentProcessing;

use PayPlug\SyliusPayPlugPlugin\ApiClient\PayPlugApiClientInterface;
use PayPlug\SyliusPayPlugPlugin\PaymentProcessing\PaymentProcessorInterface;
use PayPlug\SyliusPayPlugPlugin\PaymentProcessing\RefundPaymentProcessor;
use PayPlug\SyliusPayPlugPlugin\PayPlugGatewayFactory;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RefundPaymentProcessorSpec extends ObjectBehavior
{
    function let(
        Session $session,
        PayPlugApiClientInterface $payPlugApiClient,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ): void {
        $this->beConstructedWith($session, $payPlugApiClient, $logger, $translator);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(RefundPaymentProcessor::class);
    }

    function it_implements_payment_processor_interface(): void
    {
        $this->shouldHaveType(PaymentProcessorInterface::class);
    }

    function it_processes(
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        PayPlugApiClientInterface $payPlugApiClient
    ): void {
        $gatewayConfig->getFactoryName()->willReturn(PayPlugGatewayFactory::FACTORY_NAME);
        $gatewayConfig->getConfig()->willReturn([
            'secretKey' => 'test',
        ]);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn([
            'payment_id' => 'test',
        ]);

        $payPlugApiClient->refundPayment('test')->shouldBeCalled();
        $payPlugApiClient->initialise('test')->shouldBeCalled();

        $this->process($payment);
    }
}

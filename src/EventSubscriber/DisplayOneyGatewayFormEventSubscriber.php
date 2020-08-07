<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\EventSubscriber;

use PayPlug\SyliusPayPlugPlugin\Checker\OneyCheckerInterface;
use PayPlug\SyliusPayPlugPlugin\Gateway\OneyGatewayFactory;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

final class DisplayOneyGatewayFormEventSubscriber implements EventSubscriberInterface
{
    /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface */
    private $flashBag;

    /** @var \Sylius\Component\Resource\Repository\RepositoryInterface */
    private $paymentMethodRepository;

    /** @var \PayPlug\SyliusPayPlugPlugin\Checker\OneyCheckerInterface */
    private $oneyChecker;

    public function __construct(
        FlashBagInterface $flashBag,
        RepositoryInterface $paymentMethodRepository,
        OneyCheckerInterface $oneyChecker
    ) {
        $this->flashBag = $flashBag;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->oneyChecker = $oneyChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'handle',
        ];
    }

    /**
     * @param ControllerEvent|FilterControllerEvent $event
     */
    public function handle($event): void
    {
        $this->checkEventType($event);
        if ($event->getRequest()->attributes->get('_route') !== 'sylius_admin_payment_method_update') {
            return;
        }

        /** @var \Sylius\Component\Core\Model\PaymentMethod|null $subject */
        $subject = $this->paymentMethodRepository->find($event->getRequest()->attributes->get('id'));
        if (null === $subject) {
            return;
        }

        if (false === $subject->isEnabled() ||
            null === $subject->getGatewayConfig() ||
            OneyGatewayFactory::FACTORY_NAME !== $subject->getGatewayConfig()->getFactoryName()) {
            return;
        }

        if (true === $this->oneyChecker->isEnabled()) {
            // Oney still enabled, do nothing
            return;
        }

        $this->flashBag->add('error', 'payplug_sylius_payplug_plugin.error.oney_not_enabled');
        $subject->disable();
        $this->paymentMethodRepository->add($subject);
    }

    /**
     * @param mixed $event
     */
    private function checkEventType($event): void
    {
        if ((\class_exists(ControllerEvent::class) && !$event instanceof ControllerEvent) ||
            // For sf 4.2 and lower compatibility
            (class_exists(FilterControllerEvent::class) && !$event instanceof FilterControllerEvent)) {
            throw new \UnexpectedValueException(\sprintf('Event class is not correct, %s given', \get_class($event)));
        }
    }
}

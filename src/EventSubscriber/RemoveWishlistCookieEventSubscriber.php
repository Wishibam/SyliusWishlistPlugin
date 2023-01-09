<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\EventSubscriber;

use BitBag\SyliusWishlistPlugin\Event\WishlistSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RemoveWishlistCookieEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $cookieWishlistTokenName,
        private SessionInterface $session,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $cookies = $this->requestStack->getMainRequest()->cookies;

        if (!$cookies->has($this->cookieWishlistTokenName) || !$this->session->has(WishlistSession::CLEAR_COOKIE_ON_KERNEL_RESPONSE)) {
            return;
        }

        $this->session->remove(WishlistSession::CLEAR_COOKIE_ON_KERNEL_RESPONSE);

        $event->getResponse()->headers->clearCookie($this->cookieWishlistTokenName);
    }
}

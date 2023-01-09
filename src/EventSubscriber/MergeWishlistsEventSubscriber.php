<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\EventSubscriber;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Event\SyliusEvent;
use BitBag\SyliusWishlistPlugin\Event\WishlistSession;
use BitBag\SyliusWishlistPlugin\Helper\WishlistsMerger;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\SecurityEvents;

final class MergeWishlistsEventSubscriber implements EventSubscriberInterface
{
    private const BEFORE_BIND_USER_LISTENER_PRIORITY = 1;

    public function __construct(
        private string $cookieWishlistTokenName,
        private SessionInterface $session,
        private Security $security,
        private RequestStack $requestStack,
        private WishlistRepositoryInterface $wishlistRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SyliusEvent::REGISTER => [['onRegisterOrLogin', self::BEFORE_BIND_USER_LISTENER_PRIORITY]],
            SecurityEvents::INTERACTIVE_LOGIN => [['onRegisterOrLogin', self::BEFORE_BIND_USER_LISTENER_PRIORITY]],
        ];
    }

    public function onRegisterOrLogin(): void
    {
        $user = $this->security->getUser();

        $cookies = $this->requestStack->getMainRequest()->cookies;

        if (!$user instanceof ShopUserInterface || !$cookies->has($this->cookieWishlistTokenName) || $this->session->has(WishlistSession::CLEAR_COOKIE_ON_KERNEL_RESPONSE)) {
            return;
        }

        $cookieWishlist = $this->wishlistRepository->findByToken($cookies->get($this->cookieWishlistTokenName));

        if (!$cookieWishlist instanceof WishlistInterface) {
            return;
        }

        $userWishlist = $this->wishlistRepository->findOneByShopUser($user);

        if (!$userWishlist instanceof WishlistInterface) {
            return;
        }

        $userWishlist = WishlistsMerger::mergeProducts($cookieWishlist, $userWishlist);

        $this->wishlistRepository->save($userWishlist);
        $this->wishlistRepository->delete($cookieWishlist);

        $this->session->set(WishlistSession::CLEAR_COOKIE_ON_KERNEL_RESPONSE, true);
    }
}

<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Context;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class WishlistContext implements WishlistContextInterface
{
    public function __construct(
        private string $cookieWishlistTokenName,
        private Security $security,
        private WishlistRepositoryInterface $wishlistRepository,
        private RequestStack $requestStack,
    ) {
    }

    public function getWishlist(): ?WishlistInterface
    {
        $user = $this->security->getUser();

        // if the user is connected, return its wishlist if he has one
        if ($user instanceof ShopUserInterface) {
            return $this->wishlistRepository->findOneByShopUser($user);
        }

        $cookies = $this->requestStack->getMainRequest()->cookies;

        // if not, return the wishlist referenced in its cookie
        $cookieWishlistToken = $cookies->get($this->cookieWishlistTokenName);

        if (!empty($cookieWishlistToken)) {
            return $this->wishlistRepository->findByToken($cookieWishlistToken);
        }

        return null;
    }
}

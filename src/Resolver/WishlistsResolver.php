<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Resolver;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;

final class WishlistsResolver implements WishlistsResolverInterface
{
    public function __construct(private WishlistContextInterface $wishlistContext)
    {
    }

    public function resolve(): array
    {
        return [
            $this->wishlistContext->getWishlist(),
        ];
    }
}

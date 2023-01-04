<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Helper;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;

final class WishlistsMerger
{
    public static function mergeProducts(WishlistInterface $fromWishlist, WishlistInterface $intoWishlist): WishlistInterface
    {
        /** @var WishlistProductInterface $cookieWishlistProduct */
        foreach ($fromWishlist->getWishlistProducts() as $cookieWishlistProduct) {
            if ($intoWishlist->getProducts()->contains($cookieWishlistProduct->getProduct())) {
                continue;
            }

            $intoWishlist->addWishlistProduct(clone $cookieWishlistProduct);
        }

        return $intoWishlist;
    }
}

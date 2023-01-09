<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Event;

interface WishlistSession
{
    public const CLEAR_COOKIE_ON_KERNEL_RESPONSE = 'clear-wishlist-cookie';
}

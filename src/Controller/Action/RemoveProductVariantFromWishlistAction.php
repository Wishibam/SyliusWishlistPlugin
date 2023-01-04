<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RemoveProductVariantFromWishlistAction
{
    public function __construct(
        private ProductVariantRepositoryInterface $productVariantRepository,
        private EntityManagerInterface $wishlistProductManager,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private WishlistContextInterface $wishlistContext
    ) {
    }

    public function __invoke(int $variantId, Request $request): Response
    {
        /** @var ProductVariantInterface|null $variant */
        $variant = $this->productVariantRepository->find($variantId);

        if (null === $variant) {
            throw new NotFoundHttpException();
        }

        $wishlist = $this->wishlistContext->getWishlist();

        if (null === $wishlist) {
            throw new WishlistNotFoundException(
                'Wishlist not found.'
            );
        }
        foreach ($wishlist->getWishlistProducts() as $wishlistProduct) {
            if ($variant === $wishlistProduct->getVariant()) {
                $this->wishlistProductManager->remove($wishlistProduct);
            }
        }

        $this->wishlistProductManager->flush();

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $session->getFlashBag()->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.removed_wishlist_item'));

        return new RedirectResponse(
            $this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products')
        );
    }
}

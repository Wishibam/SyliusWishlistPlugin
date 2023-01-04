<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Form\Type\WishlistCollectionType;
use BitBag\SyliusWishlistPlugin\Processor\WishlistCommandProcessorInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class ListWishlistProductsAction
{
    public function __construct(
        private CartContextInterface $cartContext,
        private FormFactoryInterface $formFactory,
        private Environment $twigEnvironment,
        private WishlistCommandProcessorInterface $wishlistCommandProcessor,
        private WishlistContextInterface $wishlistContext,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistContext->getWishlist();

        try {
            $cart = $this->cartContext->getCart();
        } catch (CartNotFoundException $exception) {
            $cart = null;
        }

        $commandsArray = $this->wishlistCommandProcessor->createWishlistItemsCollection($wishlist->getWishlistProducts());

        $form = $this->formFactory->create(WishlistCollectionType::class, ['items' => $commandsArray], [
            'cart' => $cart,
        ]);

        return new Response(
            $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/WishlistDetails/index.html.twig', [
                'wishlist' => $wishlist,
                'form' => $form->createView(),
            ])
        );
    }
}

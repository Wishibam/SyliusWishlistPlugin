<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Controller\Action\AddProductToWishlistAction;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use BitBag\SyliusWishlistPlugin\Resolver\WishlistsResolverInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AddProductToWishlistActionSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        WishlistProductFactoryInterface $wishlistProductFactory,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        WishlistsResolverInterface $wishlistsResolver,
        ObjectManager $wishlistManager,
        ChannelContextInterface $channelContext
    ): void {
        $this->beConstructedWith(
            $productRepository,
            $wishlistProductFactory,
            $requestStack,
            $translator,
            $wishlistsResolver,
            $wishlistManager,
            $channelContext
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AddProductToWishlistAction::class);
    }

    public function it_throws_404_when_product_is_not_found(Request $request, ProductRepositoryInterface $productRepository): void
    {
        $request->get('productId')->willReturn(1);
        $productRepository->find(1)->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request]);
    }

    public function it_handles_the_request_and_persist_new_wishlist_for_logged_shop_user(
        Request $request,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        WishlistsResolverInterface $wishlistsResolver,
        WishlistProductFactoryInterface $wishlistProductFactory,
        WishlistProductInterface $wishlistProduct,
        WishlistInterface $wishlist1,
        WishlistInterface $wishlist2,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        ObjectManager $wishlistManager,
        ChannelContextInterface $channelContext,
        ChannelInterface $channel,
        ParameterBag $headers,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $request->get('productId')->willReturn(1);

        $productRepository->find(1)->willReturn($product);

        $wishlistsResolver->resolve()
            ->willReturn([
                $wishlist1,
                $wishlist2,
            ]);

        $wishlistProductFactory->createForWishlistAndProduct($wishlist1, $product)->willReturn($wishlistProduct);
        $translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item')->willReturn('Product has been added to your wishlist.');
        $channelContext->getChannel()->willReturn($channel);
        $channel->getId()->willReturn(1);
        $wishlist1->getChannel()->willReturn($channel);

        $wishlist1->addWishlistProduct($wishlistProduct)->shouldBeCalled();
        $wishlistManager->flush()->shouldBeCalledOnce();

        $requestStack->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('success', 'Product has been added to your wishlist.')->shouldBeCalled();

        $request->headers = $headers;
        $headers->get('referer')->willReturn('value');

        $this->__invoke($request)->shouldHaveType(RedirectResponse::class);
    }

    public function it_handles_the_request_and_persist_new_wishlist_for_anonymous_user(
        Request $request,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        WishlistsResolverInterface $wishlistsResolver,
        WishlistInterface $wishlist1,
        WishlistInterface $wishlist2,
        WishlistProductFactoryInterface $wishlistProductFactory,
        WishlistProductInterface $wishlistProduct,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        ObjectManager $wishlistManager,
        ChannelContextInterface $channelContext,
        ChannelInterface $channel,
        ParameterBag $headers,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $request->get('productId')->willReturn(1);
        $productRepository->find(1)->willReturn($product);

        $wishlistsResolver->resolve()
            ->willReturn([
                $wishlist1,
                $wishlist2,
            ]);

        $wishlistProductFactory->createForWishlistAndProduct($wishlist1, $product)->willReturn($wishlistProduct);
        $translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item')->willReturn('Product has been added to your wishlist.');
        $channelContext->getChannel()->willReturn($channel);
        $channel->getId()->willReturn(1);
        $wishlist1->getChannel()->willReturn($channel);

        $wishlist1->addWishlistProduct($wishlistProduct)->shouldBeCalled();
        $wishlistManager->flush()->shouldBeCalledOnce();

        $requestStack->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('success', 'Product has been added to your wishlist.')->shouldBeCalled();

        $request->headers = $headers;
        $headers->get('referer')->willReturn('value');

        $this->__invoke($request)->shouldHaveType(RedirectResponse::class);
    }
}

<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Entity\Wishlist;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use BitBag\SyliusWishlistPlugin\Factory\WishlistFactoryInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use BitBag\SyliusWishlistPlugin\Resolver\WishlistsResolverInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AddProductToWishlistAction
{
    private ProductRepositoryInterface $productRepository;
    private WishlistProductFactoryInterface $wishlistProductFactory;
    private RequestStack $requestStack;
    private TranslatorInterface $translator;
    private WishlistsResolverInterface $wishlistsResolver;
    private ObjectManager $wishlistManager;
    private ChannelContextInterface $channelContext;
    private WishlistFactoryInterface $wishlistFactory;
    private Security $security;
    private string $wishlistCookieTokenName;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        WishlistProductFactoryInterface $wishlistProductFactory,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        WishlistsResolverInterface $wishlistsResolver,
        ObjectManager $wishlistManager,
        ChannelContextInterface $channelContext,
        WishlistFactoryInterface $wishlistFactory,
        Security $security,
        string $wishlistCookieTokenName,
    ) {
        $this->productRepository = $productRepository;
        $this->wishlistProductFactory = $wishlistProductFactory;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->wishlistsResolver = $wishlistsResolver;
        $this->wishlistManager = $wishlistManager;
        $this->channelContext = $channelContext;
        $this->wishlistFactory = $wishlistFactory;
        $this->security = $security;
        $this->wishlistCookieTokenName = $wishlistCookieTokenName;
    }

    public function __invoke(Request $request): Response
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->find($request->get('productId'));

        if (null === $product) {
            throw new NotFoundHttpException();
        }

        try {
            /** @var ChannelInterface $channel */
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException $exception) {
            $channel = null;
        }

        $wishlists = $this->wishlistsResolver->resolve();

        /** @var WishlistInterface $wishlist */
        $wishlist = array_shift($wishlists);

        if (null === $wishlist) {
            $user = $this->security->getUser();

            if ($user instanceof ShopUserInterface) {
                $wishlist = $this->wishlistFactory->createForUserAndChannel($user, $channel);
            } else {
                /** @var Wishlist $wishlist */
                $wishlist = $this->wishlistFactory->createNew();
                $wishlist->setChannel($channel);
            }

            $wishlist->setName('Wishlist');
        }

        if (null !== $channel && $wishlist->getChannel()->getId() !== $channel->getId()) {
            throw new WishlistNotFoundException(
                $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.wishlist_for_channel_not_found')
            );
        }

        /** @var WishlistProductInterface $wishlistProduct */
        $wishlistProduct = $this->wishlistProductFactory->createForWishlistAndProduct($wishlist, $product);

        $wishlist->addWishlistProduct($wishlistProduct);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $session->getFlashBag()->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item'));

        $referer = $request->headers->get('referer');
        $refererPathInfo = Request::create($referer)->getPathInfo();

        $response = new RedirectResponse($refererPathInfo);

        if (!$request->cookies->has($this->wishlistCookieTokenName)) {
            $cookie = new Cookie($this->wishlistCookieTokenName, $wishlist->getToken(), strtotime('+1 year'));

            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}

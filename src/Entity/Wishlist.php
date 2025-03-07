<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

class Wishlist implements WishlistInterface
{
    protected ?int $id = null;

    protected ?string $name;

    /** @var Collection|WishlistProductInterface[] */
    protected $wishlistProducts;

    protected ?ShopUserInterface $shopUser = null;

    /** @var WishlistTokenInterface|null */
    protected $token;

    protected ?ChannelInterface $channel;

    protected \DateTimeInterface $createdAt;

    protected \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->wishlistProducts = new ArrayCollection();
        $this->token = new WishlistToken();
        $this->id = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getProducts(): Collection
    {
        $products = [];

        foreach ($this->wishlistProducts as $wishlistProduct) {
            $products[] = $wishlistProduct->getProduct();
        }

        return new ArrayCollection($products);
    }

    /**
     * @return Collection<int,ProductVariantInterface|null>
     */
    public function getProductVariants(): Collection
    {
        $variants = [];

        foreach ($this->wishlistProducts as $wishlistProduct) {
            $variants[] = $wishlistProduct->getVariant();
        }

        return new ArrayCollection($variants);
    }

    public function hasProductVariant(ProductVariantInterface $productVariant): bool
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($productVariant === $wishlistProduct->getVariant()) {
                return true;
            }
        }

        return false;
    }

    public function getWishlistProducts(): Collection
    {
        return $this->wishlistProducts;
    }

    public function hasProduct(ProductInterface $product): bool
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($product === $wishlistProduct->getProduct()) {
                return true;
            }
        }

        return false;
    }

    public function setWishlistProducts(Collection $wishlistProducts): void
    {
        $this->wishlistProducts = $wishlistProducts;
    }

    public function hasWishlistProduct(WishlistProductInterface $wishlistProduct): bool
    {
        return $this->wishlistProducts->contains($wishlistProduct);
    }

    public function addWishlistProduct(WishlistProductInterface $wishlistProduct): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $wishlistProduct->getVariant();
        if (!$this->hasProductVariant($variant)) {
            $wishlistProduct->setWishlist($this);
            $this->wishlistProducts->add($wishlistProduct);
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getShopUser(): ?ShopUserInterface
    {
        return $this->shopUser;
    }

    public function setShopUser(ShopUserInterface $shopUser): void
    {
        $this->shopUser = $shopUser;
    }

    public function getToken(): string
    {
        return (string) $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = new WishlistToken($token);
    }

    public function removeProduct(WishlistProductInterface $product): self
    {
        if ($this->hasWishlistProduct($product)) {
            $this->wishlistProducts->removeElement($product);
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removeProductVariant(ProductVariantInterface $variant): self
    {
        foreach ($this->wishlistProducts as $wishlistProduct) {
            if ($wishlistProduct->getVariant() === $variant) {
                $this->wishlistProducts->removeElement($wishlistProduct);
                $this->updatedAt = new \DateTimeImmutable();
            }
        }

        return $this;
    }

    public function clear(): void
    {
        $this->wishlistProducts = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}

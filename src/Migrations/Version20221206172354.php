<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221206172354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add wishlist updated_at & created_at field to track old ones';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bitbag_wishlist ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bitbag_wishlist ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bitbag_wishlist DROP created_at');
        $this->addSql('ALTER TABLE bitbag_wishlist DROP updated_at');
    }
}

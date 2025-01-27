<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250124114653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing index on bitbag_wishlist token field';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf($schema->getTable('bitbag_wishlist')->hasIndex('token'), 'Index on token field already exists');
        $this->addSql('CREATE INDEX token ON bitbag_wishlist (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX token ON bitbag_wishlist');
    }
}

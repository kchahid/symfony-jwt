<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613152914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE identity (id UUID NOT NULL, issuer VARCHAR(10) NOT NULL, allowed_env JSON DEFAULT NULL, secret VARCHAR(100) NOT NULL, created_at VARCHAR(255) NOT NULL, status BOOLEAN NOT NULL, basic_key VARCHAR(20) NOT NULL, basic_secret VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN identity.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE identity');
    }
}

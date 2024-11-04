<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241017225825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_action CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE dispute CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payment CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE review CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE service CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_action CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE dispute CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE review CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at DATETIME NOT NULL');
    }
}

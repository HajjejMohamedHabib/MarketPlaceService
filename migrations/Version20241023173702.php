<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023173702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984C3A3BB');
        $this->addSql('DROP INDEX UNIQ_F52993984C3A3BB ON `order`');
        $this->addSql('ALTER TABLE `order` DROP payment_id, DROP payment_status');
        $this->addSql('ALTER TABLE payment ADD orderr_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D7742FDB3 FOREIGN KEY (orderr_id) REFERENCES `order` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D7742FDB3 ON payment (orderr_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD payment_id INT DEFAULT NULL, ADD payment_status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993984C3A3BB ON `order` (payment_id)');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D7742FDB3');
        $this->addSql('DROP INDEX UNIQ_6D28840D7742FDB3 ON payment');
        $this->addSql('ALTER TABLE payment DROP orderr_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129190223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, compte_id INT DEFAULT NULL, surname VARCHAR(20) NOT NULL, telephone VARCHAR(15) NOT NULL, adresse TINYTEXT NOT NULL, UNIQUE INDEX UNIQ_C7440455E7769B0F (surname), UNIQUE INDEX UNIQ_C7440455450FF010 (telephone), UNIQUE INDEX UNIQ_C7440455F2C56620 (compte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dette (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, montant DOUBLE PRECISION NOT NULL, date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', montant_verse DOUBLE PRECISION NOT NULL, INDEX IDX_831BC80819EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, dette_id INT DEFAULT NULL, montant DOUBLE PRECISION NOT NULL, date_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1DC7A1EE11400A1 (dette_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, login VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(24) NOT NULL, prenom VARCHAR(25) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_LOGIN (login), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F2C56620 FOREIGN KEY (compte_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dette ADD CONSTRAINT FK_831BC80819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EE11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F2C56620');
        $this->addSql('ALTER TABLE dette DROP FOREIGN KEY FK_831BC80819EB6921');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EE11400A1');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE dette');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

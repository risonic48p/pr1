<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614042550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id SERIAL NOT NULL, url VARCHAR(500) NOT NULL, title VARCHAR(255) NOT NULL, partner VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D34A04ADF47645AE ON product (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE review (id SERIAL NOT NULL, product_id INT NOT NULL, market_id VARCHAR(100) NOT NULL, author_name VARCHAR(255) NOT NULL, rate SMALLINT NOT NULL, comment TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_794381C64584665A ON review (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX IDX_UNIQUE_REVIEW ON review (product_id, author_name, created_at)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP CONSTRAINT FK_794381C64584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE review
        SQL);
    }
}

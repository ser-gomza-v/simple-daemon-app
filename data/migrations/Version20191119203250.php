<?php

declare(strict_types=1);

namespace SimpleDaemon\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191119203250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tasks
                    (
                      id           INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                      type         INT             NOT NULL,
                      status       INT             NOT NULL,
                      date_created TIMESTAMP                DEFAULT CURRENT_TIMESTAMP,
                      date_updated TIMESTAMP,
                      params       TEXT,
                      error        TEXT
                    );');
        $this->addSql('CREATE INDEX tasks_status_index ON tasks (status);');
        $this->addSql('CREATE INDEX tasks_type_index ON tasks (type);');
        $this->addSql('CREATE INDEX tasks_type_status_index ON tasks (type, status);');
        $this->addSql('CREATE INDEX tasks_date_created_index ON tasks (date_created);');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tasks');
    }
}

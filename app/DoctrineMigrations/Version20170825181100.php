<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20170825181100
 *
 * @package Application\Migrations
 */
class Version20170825181100 extends AbstractMigration
{
    /**
     * up
     *
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE book (id BIGSERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));');
        $this->addSql('CREATE UNIQUE INDEX name_idx ON book (name);');
        $this->addSql('CREATE TABLE word_stat (id BIGSERIAL NOT NULL, word VARCHAR(255) NOT NULL, val BIGINT NOT NULL, book_id BIGINT NOT NULL, PRIMARY KEY(id));');
        $this->addSql('CREATE UNIQUE INDEX word_stat_book_idx ON word_stat (word, book_id);');
    }

    /**
     * down
     *
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema)
    {
    }
}

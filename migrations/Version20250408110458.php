<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408110458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, author_id INT NOT NULL, editor_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL, content LONGTEXT NOT NULL, updated_at DATETIME DEFAULT NULL, is_deleted TINYINT(1) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, INDEX IDX_5A8A6C8D1F55203D (topic_id), INDEX IDX_5A8A6C8DF675F31B (author_id), INDEX IDX_5A8A6C8D6995AC4C (editor_id), INDEX IDX_5A8A6C8DC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, forum_id INT NOT NULL, author_id INT DEFAULT NULL, last_poster_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_closed TINYINT(1) NOT NULL, sticky_status INT NOT NULL, view_count INT NOT NULL, post_count INT NOT NULL, last_post_at DATETIME DEFAULT NULL, INDEX IDX_9D40DE1B29CCBAD0 (forum_id), INDEX IDX_9D40DE1BF675F31B (author_id), INDEX IDX_9D40DE1B4C79B303 (last_poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D6995AC4C FOREIGN KEY (editor_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B4C79B303 FOREIGN KEY (last_poster_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D1F55203D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D6995AC4C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC76F1F52
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B29CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B4C79B303
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE topic
        SQL);
    }
}

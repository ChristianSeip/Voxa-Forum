<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407152939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE forum (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, is_locked TINYINT(1) NOT NULL, is_hidden TINYINT(1) NOT NULL, INDEX IDX_852BBECD727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_moderator (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, forum_id INT NOT NULL, INDEX IDX_5479363EA76ED395 (user_id), INDEX IDX_5479363E29CCBAD0 (forum_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_permission (id INT AUTO_INCREMENT NOT NULL, forum_id INT NOT NULL, role_id INT NOT NULL, permission VARCHAR(100) NOT NULL, value INT NOT NULL, INDEX IDX_627F2FEF29CCBAD0 (forum_id), INDEX IDX_627F2FEFD60322AC (role_id), UNIQUE INDEX forum_permission_unique (forum_id, role_id, permission), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum ADD CONSTRAINT FK_852BBECD727ACA70 FOREIGN KEY (parent_id) REFERENCES forum (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_moderator ADD CONSTRAINT FK_5479363EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_moderator ADD CONSTRAINT FK_5479363E29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_permission ADD CONSTRAINT FK_627F2FEF29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_permission ADD CONSTRAINT FK_627F2FEFD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_moderator DROP FOREIGN KEY FK_5479363EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_moderator DROP FOREIGN KEY FK_5479363E29CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_permission DROP FOREIGN KEY FK_627F2FEF29CCBAD0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_permission DROP FOREIGN KEY FK_627F2FEFD60322AC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_moderator
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_permission
        SQL);
    }
}

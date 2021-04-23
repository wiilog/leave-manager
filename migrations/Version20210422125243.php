<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210422125243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $lignes= $this->connection->executeQuery("SELECT user.id FROM user LEFT JOIN user_user ON user.id = user_user.user_source WHERE user_user.user_source is NULL")->fetchAll();
        foreach ($lignes as $ligne){
            $id = $ligne["id"];
            $this->addSql("INSERT INTO user_user VALUE ('$id','9')");
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

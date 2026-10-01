<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925172605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categoria (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nombre VARCHAR(80) NOT NULL, slug VARCHAR(80) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E10122D3A909126 ON categoria (nombre)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E10122D989D9B62 ON categoria (slug)');
        $this->addSql('CREATE TABLE pedido (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nombre_cliente VARCHAR(120) NOT NULL, correo VARCHAR(180) NOT NULL, direccion VARCHAR(200) NOT NULL, ciudad VARCHAR(80) NOT NULL, total INTEGER NOT NULL, creado_en DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE pedido_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sku VARCHAR(20) NOT NULL, nombre_producto VARCHAR(150) NOT NULL, precio_unitario INTEGER NOT NULL, cantidad INTEGER NOT NULL, pedido_id INTEGER NOT NULL, CONSTRAINT FK_6E0730724854653A FOREIGN KEY (pedido_id) REFERENCES pedido (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6E0730724854653A ON pedido_item (pedido_id)');
        $this->addSql('CREATE TABLE producto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sku VARCHAR(20) NOT NULL, nombre VARCHAR(150) NOT NULL, descripcion CLOB NOT NULL, precio INTEGER NOT NULL, precio_oferta INTEGER DEFAULT NULL, stock INTEGER NOT NULL, tallas CLOB NOT NULL, categoria_id INTEGER NOT NULL, CONSTRAINT FK_A7BB06153397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7BB0615F9038C4 ON producto (sku)');
        $this->addSql('CREATE INDEX IDX_A7BB06153397707A ON producto (categoria_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE pedido');
        $this->addSql('DROP TABLE pedido_item');
        $this->addSql('DROP TABLE producto');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class Uuid extends Migration
{
    public function up()
    {
        // Projects1 table
        $this->forge->addField([
            'id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', false, true);
        $this->forge->createTable('projects1');

        // Projects2 table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('projects2');

        // Projects3 table
        $this->forge->addField([
            'id' => [
                'type'       => 'BINARY',
                'constraint' => 16,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', false, true);
        $this->forge->createTable('projects3');

        // Projects4 table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'       => 'BINARY',
                'constraint' => 16,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('projects4');
    }

    public function down()
    {
        $this->forge->dropTable('projects1');
        $this->forge->dropTable('projects2');
        $this->forge->dropTable('projects3');
        $this->forge->dropTable('projects4');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Urls extends Migration
{

    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'original_url' => [
                'type' => 'TEXT',
            ],
            'shortened_code' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
            ],
            'shortened_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'clicks' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('urls');
    }

    public function down()
    {
        $this->forge->dropTable('urls');
    }
}

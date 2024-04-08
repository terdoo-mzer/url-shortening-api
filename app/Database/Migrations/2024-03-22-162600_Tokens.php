<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tokens extends Migration
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
            'refresh_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tokens');
    }

    public function down()
    {
        $this->forge->dropTable('tokens');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UrlAnalytics extends Migration
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
            'url_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_ip_adr' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'lat' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'long' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'isp' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'browser' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'platform' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('url_id', 'urls', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('url_analytics');
    }

    public function down()
    {
        $this->forge->dropTable('url_analytics');
    }
}

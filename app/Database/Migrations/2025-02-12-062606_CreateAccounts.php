<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'resource_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'account_name'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'connection_type' => ['type' => 'ENUM', 'constraint' => ['SSH', 'RDP', 'VNC', 'OTH'], 'default' => 'RDP'],
            'port'            => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'on_update' => true],
            'deleted_at'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('resource_id', 'resources', 'id', 'CASCADE', 'CASCADE'); // 外部キーを定義

        $this->forge->createTable('accounts');
    }

    public function down()
    {
        $this->forge->dropTable('accounts');
    }
}

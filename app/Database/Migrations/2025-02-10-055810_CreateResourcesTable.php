<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResourcesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'type'        => ['type' => 'ENUM', 'constraint' => ['PC', 'Server', 'Network', 'Storage', 'Other'], 'default' => 'Other', 'null' => false],
            'os'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'mac_address' => ['type' => 'VARCHAR', 'constraint' => 17, 'null' => true],
            'cpu'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'memory'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'storage'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['available', 'in_use', 'maintenance', 'retired'], 'default' => 'available'],
            'location'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'on_update' => true],
            'deleted_at'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('resources');
    }

    public function down()
    {
        $this->forge->dropTable('resources');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFullnameToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'default'    => '',
                'after'      => 'username',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'fullname');
    }
}

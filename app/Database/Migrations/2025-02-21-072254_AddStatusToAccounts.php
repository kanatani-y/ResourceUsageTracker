<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToAccounts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('accounts', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'restricted', 'retired'],
                'default'    => 'available',
                'null'       => false,
                'comment'    => '利用可能: available, 利用禁止: restricted, 廃止: retired',
                'after'      => 'port'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('accounts', 'status');
    }
}

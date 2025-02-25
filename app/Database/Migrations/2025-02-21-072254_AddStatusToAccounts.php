<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyAccountsTable extends Migration
{
    public function up()
    {
        // `active` カラムの追加
        $this->forge->addColumn('accounts', [
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'port' // `port` の直後に追加
            ],
        ]);

        // `status` カラムの削除
        $this->forge->dropColumn('accounts', 'status');
    }

    public function down()
    {
        // `active` カラムの削除
        $this->forge->dropColumn('accounts', 'active');

        // `status` カラムの復元
        $this->forge->addColumn('accounts', [
            'status' => [
                'type'       => "ENUM('available', 'restricted', 'retired')",
                'default'    => 'available',
                'null'       => false,
                'comment'    => '使用可: available, 使用禁止: restricted, 廃止: retired',
                'after'      => 'port'
            ],
        ]);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHostnameToResources extends Migration
{
    public function up()
    {
        // 1. まず `mac_address` カラムを削除
        $this->forge->dropColumn('resources', 'mac_address');

        // 2. `hostname` カラムを追加
        $fields = [
            'hostname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'after'      => 'name', // `name` の後に配置
            ],
        ];
        $this->forge->addColumn('resources', $fields);

        // 3. `hostname` にユニーク制約を追加
        $this->forge->addKey('hostname', true);
    }

    public function down()
    {
        // `hostname` を削除
        $this->forge->dropColumn('resources', 'hostname');

        // `mac_address` を復活
        $fields = [
            'mac_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 17,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('resources', $fields);
    }
}

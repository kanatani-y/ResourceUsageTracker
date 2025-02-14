<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInitialResources extends Migration
{
    public function up()
    {
        $data = [
            [
                'name'        => '保守用PC1',
                'hostname'    => 'EVR2-MEN-CLI01',
                'type'        => 'PC',
                'status'      => 'available',
                'location'    => '保守VM1',
                'description' => '保守用クライアント1',
                'os'          => null,
                'ip_address'  => '172.18.100.180',
                'cpu'         => '4',
                'memory'      => '8',
                'storage'     => '100',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => '保守用PC2',
                'hostname'    => 'EVR2-MEN-CLI02',
                'type'        => 'PC',
                'status'      => 'available',
                'location'    => '保守VM2',
                'description' => '保守用クライアント2',
                'os'          => null,
                'ip_address'  => '172.18.100.181',
                'cpu'         => '4',
                'memory'      => '8',
                'storage'     => '100',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('resources')->insertBatch($data);
    }

    public function down()
    {
        $this->db->table('resources')->whereIn('hostname', ['EVR2-MEN-CLI01', 'EVR2-MEN-CLI02'])->delete();
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountIdToReservations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('reservations', [
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'after'      => 'resource_id' // resource_idの後に追加
            ],
        ]);
    }

    public function down()
    {
        //
    }
}

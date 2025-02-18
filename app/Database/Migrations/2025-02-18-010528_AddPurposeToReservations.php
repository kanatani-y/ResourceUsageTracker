<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurposeToReservations extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('reservations', 'status');

        $this->forge->addColumn('reservations', [
            'purpose' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'end_time'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('reservations', 'purpose');

        $this->forge->addColumn('reservations', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'cancelled'],
                'default'    => 'pending'
            ],
        ]);
    }
}

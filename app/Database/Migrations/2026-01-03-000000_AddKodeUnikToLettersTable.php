<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeUnikToLettersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('letters', [
            'kode_unik' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Buat index untuk kode_unik agar pencarian lebih cepat
        $this->forge->addKey('kode_unik');
    }

    public function down()
    {
        $this->forge->dropColumn('letters', 'kode_unik');
    }
}


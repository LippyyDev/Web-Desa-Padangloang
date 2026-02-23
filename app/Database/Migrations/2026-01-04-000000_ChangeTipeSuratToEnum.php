<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeTipeSuratToEnum extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('letters', [
            'tipe_surat' => [
                'type' => 'ENUM',
                'constraint' => [
                    'Keterangan Usaha',
                    'Keterangan Tidak Mampu',
                    'Keterangan Belum Menikah',
                    'Keterangan Domisili',
                    'Undangan',
                    'Lain Lain'
                ],
                'default' => 'Lain Lain',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('letters', [
            'tipe_surat' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
        ]);
    }
}


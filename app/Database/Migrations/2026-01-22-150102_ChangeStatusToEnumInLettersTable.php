<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeStatusToEnumInLettersTable extends Migration
{
    public function up()
    {
        // First, update any existing 'Terkirim' status to 'Menunggu'
        // to ensure compatibility with new ENUM values
        $this->db->query("UPDATE letters SET status = 'Menunggu' WHERE status = 'Terkirim'");
        
        // Update any existing 'Dibalas' status to 'Diterima'
        $this->db->query("UPDATE letters SET status = 'Diterima' WHERE status = 'Dibalas'");
        
        // Now modify the column to ENUM type with updated values
        $this->forge->modifyColumn('letters', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => [
                    'Menunggu',
                    'Dibaca',
                    'Diterima',
                    'Ditolak'
                ],
                'default' => 'Menunggu',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('letters', [
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
        ]);
    }
}

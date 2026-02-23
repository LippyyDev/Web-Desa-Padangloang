<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisKelaminToUserProfiles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_profiles', [
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['Laki-laki', 'Perempuan'],
                'null'       => true,
                'after'      => 'tanggal_lahir',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user_profiles', 'jenis_kelamin');
    }
}

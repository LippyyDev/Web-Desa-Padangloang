<?php

namespace App\Models;

use CodeIgniter\Model;

class DesaProfileModel extends Model
{
    protected $table            = 'desa_profile';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'visi',
        'misi',
        'jumlah_penduduk',
        'jumlah_kk',
        'penduduk_sementara',
        'jumlah_laki',
        'jumlah_perempuan',
        'mutasi_penduduk',
        'kontak_wa',
        'kontak_email',
        'alamat_kantor',
        'maps_url',
        'deskripsi_lokasi',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}



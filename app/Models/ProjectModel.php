<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'judul',
        'tanggal_waktu',
        'thumbnail',
        'deskripsi',
        'anggaran',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}



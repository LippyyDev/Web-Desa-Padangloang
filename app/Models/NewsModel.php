<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table            = 'news';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'judul',
        'tanggal_waktu',
        'thumbnail',
        'isi',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}



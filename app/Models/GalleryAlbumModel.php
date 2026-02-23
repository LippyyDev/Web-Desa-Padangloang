<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryAlbumModel extends Model
{
    protected $table            = 'gallery_albums';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nama_album',
        'deskripsi',
        'tanggal_waktu',
        'thumbnail',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}



<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryMediaModel extends Model
{
    protected $table            = 'gallery_media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'album_id',
        'media_type',
        'media_path',
        'created_at',
    ];
    protected $useTimestamps    = false;
}



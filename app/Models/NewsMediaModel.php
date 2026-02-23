<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsMediaModel extends Model
{
    protected $table            = 'news_media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'news_id',
        'media_type',
        'media_path',
        'created_at',
    ];
    protected $useTimestamps    = false;
}



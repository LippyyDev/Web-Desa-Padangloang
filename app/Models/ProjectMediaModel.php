<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectMediaModel extends Model
{
    protected $table            = 'project_media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'project_id',
        'media_type',
        'media_path',
        'created_at',
    ];
    protected $useTimestamps    = false;
}



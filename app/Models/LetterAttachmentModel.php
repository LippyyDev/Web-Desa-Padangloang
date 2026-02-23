<?php

namespace App\Models;

use CodeIgniter\Model;

class LetterAttachmentModel extends Model
{
    protected $table            = 'letter_attachments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'letter_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];
    protected $useTimestamps    = false;
}



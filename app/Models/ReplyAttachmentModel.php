<?php

namespace App\Models;

use CodeIgniter\Model;

class ReplyAttachmentModel extends Model
{
    protected $table            = 'reply_attachments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'reply_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];
    protected $useTimestamps    = false;
}



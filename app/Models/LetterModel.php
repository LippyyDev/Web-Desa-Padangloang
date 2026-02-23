<?php

namespace App\Models;

use CodeIgniter\Model;

class LetterModel extends Model
{
    protected $table            = 'letters';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kode_unik',
        'user_id',
        'assigned_staff_id',
        'judul_perihal',
        'tipe_surat',
        'isi_surat',
        'status',
        'sent_at',
        'read_at',
        'replied_at',
        'approval_at',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}
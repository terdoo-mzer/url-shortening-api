<?php

namespace App\Models;

use CodeIgniter\Model;

class UrlModel extends Model
{
    protected $table            = 'urls';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'original_url',
        'shortened_code',
        'isRevoked',
        'shortened_url',
        'clicks',
        'user_id',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeInsert'];
    protected $beforeUpdate   = ['beforeUpdate'];

    protected function beforeInsert(array $data)
    {
        return $data;
    }

    protected function beforeUpdate(array $data)
    {   
        if (array_key_exists('updated_at', $data)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
  
}

<?php

namespace App\Models;

use ArrayAccess;
use CodeIgniter\Model;

class TokenModel extends Model
{
    protected $table            = 'tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'refresh_token',
        'user_id'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    public function beforeInsert(array $data)
    {
        
        return $data;

    }

    public function beforeUpdate(array $data)
    {
        
        return $data;

    }


    // public function beforeInsert(array $data)
    // {

        
    //     return $data;
        
    //     if (isset($data['token'])) {
    //         $data['token'] = $this->hashToken($data['token']);
    //     }

    //     return $data;
    // }

    // protected function hashToken(string $token): string
    // {
    //     return password_hash($token, PASSWORD_DEFAULT);
    // }
}

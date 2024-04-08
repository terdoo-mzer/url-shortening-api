<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'first_name',
        'last_name',
        'email',
        'password',
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



    // This method will run before any record is committed to the database.
    // Here the hashpassword method is called to hash user password before saving to the database
    // The beforeInsert function allows you to perform an operation on the user entity before
    // committing it to the database
    
    protected function beforeInsert (array $data) : array  
    {

        if(isset($data['data']['password'])) {
            $plainPassword = $data['data']['password'];
            $data['data']['password'] = $this->hashUserPassword($plainPassword);
        }

        return $data;
    } 

    protected function beforeUpdate  ()
    {

    }

    // This  method is provided to hash passwords before saving in the database
    private function hashUserPassword (string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }


    // This method comes in handy at login
    // The email of the user attempting login is extracred and used to find if 
    // a record exists against the provided emaila address.
    public function findUserByEmail ( string $email ) 
    {
        return $this
        ->asArray()
        ->where(['email' => $email])
        ->first();
    }

    public function findUserById (int $user_id) 
    {
        return $this->find($user_id);
    }
 
}

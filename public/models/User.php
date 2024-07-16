<?php

namespace App\Models;

use SajedZarinpour\DB\Model;

class User extends Model implements \JsonSerializable
{
    protected static string $table = 'test';
    
    public int $id;
    public string $name;

    protected $fields = ['id', 'name'];

     

    public function __debugInfo() 
    {
        return [
            'attributes'=>[
                'id'=>$this->id??null,
                'name'=>$this->name??null,
            ],
            'connection info' => parent::__debugInfo(),
        ];
    }



    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        unset($vars['fields']);
        return $vars;
    }

}
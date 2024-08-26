<?php

namespace App\Models;

/**
 * this file uses base model to represent an entity.
 */
use SajedZarinpour\DB\Model;

class User extends Model implements \JsonSerializable
{
    /**
     * mendatory: the table that this object represents
     * @var string
     */
    protected static string $table = 'test';

    /**
     * data field, not mendatory but throws warning if not declared.
     * @var int
     */
    public int $id;
    /**
     * data field, not mendatory but throws warning if not declared.
     * @var string
     */
    public string $name;

    /**
     * data field
     * @var string
     */
    public string $is_admin;

    /**
     * mendatory: provides the list of columns which will be used later in queries.
     * 
     * @todo refactor the class to get this list automatically
     * @var array
     */
    protected $fields = ['id', 'name','is_admin'];

    /**
     * controls how this object will be printed using `print_r` or `var_dump`
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'attributes' => [
                'id' => $this->id ?? null,
                'name' => $this->name ?? null,
                'is_admin' => $this->is_admin ?? null,
            ],
            'connection info' => parent::__debugInfo(),
        ];
    }

    /**
     * controls how this object will be serialized using `json_encode` function.
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        unset($vars['fields']);
        return $vars;
    }

}
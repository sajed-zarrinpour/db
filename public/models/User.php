<?php

namespace App\Models;

/**
 * this file uses base model to represent an entity.
 */
use SajedZarinpour\DB\Model;
use SajedZarinpour\DB\Mysql;

class User extends Model implements \JsonSerializable
{
    use Mysql;
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
     * @var ?bool
     */
    public ?bool $is_admin;

    /**
     * optional: 
     * 
     * By default all public variables of models considered as equivalent to corresponding table columns.
     * if you have fields which are public but have no correspondance in your table,
     * name the in following array:
     * 
     * @var array $except 
     */
    // protected static $except = ['id'];

    /**
     * Specify name of fields you dont want to serilize in json
     * 
     * @var array $hiden_fields
     */
    // protected static $hiden_fields = ['id'];
    
}

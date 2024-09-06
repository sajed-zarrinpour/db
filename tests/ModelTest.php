<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testInsert(): void
    {
        $_ENV['TEST'] = true;
        $user = new User;
        $user->name = 'testUser';

        $this->assertTrue($user->save());
        $this->assertIsInt($user->id);
    }
}


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


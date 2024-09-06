<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testInsert(): void
    {
        $_ENV['TEST'] = true;

        create_test_table();
        
        $user = new User;
        $user->name = 'testUser';

        $this->assertTrue($user->save());
        $this->assertIsInt($user->id);
    }
}

use function SajedZarinpour\DB\config as config;
function create_test_table()
{
    $query = "
        CREATE TABLE IF NOT EXISTS `test` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(60) NOT NULL,
        `is_admin` tinyint(1) DEFAULT 0,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='to test crud';
    ";

    $host = config('host');
    $port = config('port');
    $user = config('user');
    $password = config('password');
    $database = config('database');
    
    // Create connection
    $conn = new mysqli(
        $host,
        $user,
        $password,
        $database,
        intval($port)
    );
    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }


    if ($conn->query($query) === TRUE) {
    echo "Table test created successfully";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
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



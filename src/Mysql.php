<?php
namespace SajedZarinpour\DB;
/**
 * This file contains the Mysql trait, which connects the application to mysql server.
 */

/** configurations */
use function SajedZarinpour\DB\config;

/**
 * Provides connection and functionality of mysql server for php application, built on top of php-mysqli
 * Please make sure the mysqli extension is available in your php.ini if you are going to use this file.
 * 
 */
trait Mysql {
    /**
     * mysql server host
     * @var string
     */
    private static string $host;
    /**
     * mysql server port, default 3306
     * @var string
     */
    private static string $port;
    /**
     * mysql-server user with sufficient privilages
     * @var string
     */
    private static string $user;
    /**
     * password of the mentioned user
     * @var string
     */
    private static string $password;
    /**
     * Your app database
     * @var string
     */
    private static string $database;

    /**
     * initializes the static variables with values from `config/config.php`.
     * @return void
     */
    private static function __init__statics()
    {
        self:: check_mysqli();

        self::$host = config('host');
        self::$port = config('port');
        self::$user = config('user');
        self::$password = config('password');
        self::$database = config('database');
    }

    /**
     * checks if the mysqli extension is installed on this server, exit upon failure.
     * @throws \Exception
     * @return void
     */
    private static function check_mysqli()
    {
        if (!function_exists('mysqli_init') && !extension_loaded('mysqli'))
        {
            throw new \Exception('mysqli is not installed on this server.');
        }
    }

    /**
     * construction, calls `self::__init__statics()`
     */
    function __construct()
    {
        self::__init__statics();
    }

    /**
     * specify what to print in `print_r` and `var_dump` functions.
     * @return array
     */
    public function __debugInfo() 
    {
        self::__init__statics();
        return [
            'host'=>self::$host,
            'port'=>self::$port,
            'database'=>self::$database,
        ];
    }

    /**
     * sanitizing the input, security measures goes here
     * 
     * @param string $input the input value
     * 
     * @return string the sanitized version of the input
     */
    private function sanitizer(string $input) :string 
    {
        return htmlspecialchars($input);
    } 

    private static function _queryVerb(string $query)
    {
        if(preg_match('/\bINSERT\b|\binsert\b/', $query))
        {
            return 'insert';
        }
        elseif (preg_match('/\bUPDATE\b|\bupdate\b/', $query)) 
        {
            return 'update';
        }
        elseif (preg_match('/\bDELETE\b|\bdelete\b/', $query)) 
        {
            return 'delete';
        }
        elseif (preg_match('/\bSELECT\b|\bselect\b/', $query)) 
        {
            return 'select';
        }
        else {
            return null;
        }
    }

    /**
     * connects to db and executes provided query
     * 
     * @param string $query the query to execute
     * 
     * @return 
     */
    public static function execute(string $query) 
    {
        self::__init__statics();

        $mysqli = new \mysqli(
            self::$host,
            self::$user,
            self::$password,
            self::$database,
            self::$port,
        );

        // Check connection
        if ($mysqli->connect_errno)
        {
            throw new \Exception("Failed to connect to MySQL: " . $mysqli->connect_error);
        }

        $result = $mysqli->query($query);
        $out = null;
        if(!is_bool($result))
        {
            if($result->num_rows>0)
            {
                // Fetch all the results as an associative array
                $out = $result -> fetch_all(MYSQLI_ASSOC);
                // Free result set
                $result -> free_result();
                
            }
            else
            {
                $out = $result;
            }
        }
        else 
        {
            if (self::_queryVerb($query)==='insert') 
            {
                $out = $mysqli->insert_id;
            }
            else
            {
                $out = $result;
            }
            
        }

        // closing the connection
        $mysqli -> close();

        return $out;
    }

    /**
     * prepare query for execution
     */
    protected final function query_builder($queryType)
    {
        if ($queryType === 'insert') 
        {
            $columns = '';
            $values = '';
            $query = 'insert into '. static::$table .' ';

            foreach ((object) $this->fields as $key) 
            {
                if ($key === 'id') 
                {
                    continue;
                }
                $columns .= '`'.$key . '`,';
                $values .= '"'.$this->sanitizer($this->$key). '",';
            }

            $columns = '('.substr($columns, 0, -1) . ')';
            $values = '('.substr($values, 0, -1) . ')';

            return $query . $columns . ' values ' . $values;
        } 
        else if($queryType === 'update')
        {
            $query = 'UPDATE '. static::$table .' SET ';

            foreach ((object) $this->fields as $key) 
            {
                if ($key === 'id') 
                {
                    continue;
                }
                $query .= ' `'.$key . '` = "' . $this->sanitizer($this->$key) . '",';
            }

            $query = substr($query, 0, -1);
            $query .= ' WHERE id='.$this->id;

            return $query;
        }
        else if($queryType === 'delete')
        {
            $query = 'DELETE FROM '. static::$table .' WHERE id='.$this->id.';';
            return $query;
        }
        
    }

    /**
     * save the changes of the object to the database, update if it exists.
     * @return array|bool|int|string|\mysqli_result
     */
    public function save()
    {
        if (empty($this->id)) 
        {
            try 
            {
                $query = $this->query_builder('insert');
                $this->id = $this->execute($query);
                return true;
            } 
            catch (\Throwable $th) 
            {
                throw $th;
            }
            
        } 
        else 
        {
            try 
            {
                $query = $this->query_builder('update');
                return $this->execute($query);
            } 
            catch (\Throwable $th) 
            {
                throw $th;
            }
        }
        
    }

    /**
     * deletes the corresponding record in the database
     * @throws \Exception
     * @return bool
     */
    public function delete()
    {
        if (!empty($this->id)) 
        {
            try 
            {
                $query = $this->query_builder('delete');
                $this->id = $this->execute($query);

                return true;
            } 
            catch (\Throwable $th) 
            {
                throw $th;
            }
            
        } 
        else 
        {
            throw new \Exception("Cannot DELETE a record without id.");
        }
    }


    /**
     * get the data record with the provided id, returns an instance of the desired entity
     * @param mixed $id
     * @return object
     */
    public static function find($id)
    {
        try 
        {
            // $query = $this->query_builder('select', [], $id);
            $entity = static::class;
            $instance = new $entity();

            $query = 'SELECT * FROM '. static::$table .' WHERE `id`='.$id;
            $data = (object) self::execute($query)[0];

            $instance->id = $data->id;
            $instance->name = $data->name;

            return $instance;

        } 
        catch (\Throwable $th) 
        {
            throw $th;
        }
    }

    /**
     * retrieves all the data related to this modle and convert it to object.
     * @return object[]
     */
    public static function all()
    {
        try 
        {

            $query = 'SELECT * FROM '. static::$table;
            $data = self::execute($query);

            $entity = static::class;

            $collection = [];
            foreach ($data as $item) 
            {
                $item = (object) $item;
                $instance = new $entity();

                $instance->id = $item->id;
                $instance->name = $item->name;

                $collection []= $instance;

                unset ($instance);
            }
            

            return $collection;

        } 
        catch (\Throwable $th) 
        {
            throw $th;
        }
    }

    // public static function where()
    // {
    //     try 
    //     {
    //         $instance = new self;

    //         $query = 'SELECT * FROM '. $instance->table;
    //         $data = $instance->execute($query);

    //         $collection = [];
    //         foreach ($data as $item) 
    //         {
    //             $item = (object) $item;
    //             $temp = new self;

    //             $temp->id = $item->id;
    //             $temp->name = $item->name;

    //             $collection []= $temp;

    //             unset ($item);
    //         }
            

    //         return $collection;

    //     } 
    //     catch (\Throwable $th) 
    //     {
    //         throw $th;
    //     }
    // }

    
}




// some idea for develope in future in case I wanted to ommit the $fields variable from my models
// trait par{
// 	private $p;
// 	protected $pt;
// 	public $inchild = null;
// 	function pr(){
// 		// return get_class_vars(__CLASS__);
// 		// return $this->inChild??'parent';
// 		$reflect = new ReflectionClass(__CLASS__);
// 		$props   = $reflect->getProperties(ReflectionProperty:: IS_PRIVATE | ReflectionProperty:: IS_PROTECTED);
// 		$filter = [];
// 		foreach($props as $prop)
// 		{
// 			$filter []= $prop->name;
// 		}
// 		return array_filter(array_diff_key(get_class_vars(__CLASS__), $filter));
		
// 	}
// }

// class chil{
// 	use par;
// 	private $cp;
// 	public $inChild='test';
// }

// $c = new chil;
// var_dump($c->pr());
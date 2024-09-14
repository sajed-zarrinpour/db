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
trait Mysql
{
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
        self::check_mysqli();

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
        if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
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
            'Object Info' => parent::__debugInfo(),
            'Connection Info' => [
                'host' => self::$host,
                'port' => self::$port,
                'database' => self::$database,
            ]
        ];
    }


    /**
     * controls what to print in json_encode
     * @return array
     */
    public function jsonSerialize()
    {
        return parent::jsonSerialize();
    }

    /**
     * sanitizing the input, security measures goes here
     * 
     * @param mixed $input the input value
     * 
     * @return string the sanitized version of the input
     */
    private static function sanitizer($input)
    {
        $type = gettype($input);

        if($type === 'integer')
        {
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            return intval($input);
        }
        else if($type === 'boolean')
        {
            return $input ? true : false;
        }
        else if($type==='double')
        {
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT);
            return floatval($input);
        }
        else if($type === 'string')
        {
            self::__init__statics();
            
            $mysqli = new \mysqli(
                self::$host,
                self::$user,
                self::$password,
                self::$database,
                self::$port,
            );

            return $mysqli->real_escape_string(htmlspecialchars($input));
        }
        
    }

    /**
     * returns the query verb
     * @param string $query
     * @return string|null
     */
    private static function _queryVerb(string $query)
    {
        if (preg_match('/\bINSERT\b|\binsert\b/', $query)) {
            return 'insert';
        } elseif (preg_match('/\bUPDATE\b|\bupdate\b/', $query)) {
            return 'update';
        } elseif (preg_match('/\bDELETE\b|\bdelete\b/', $query)) {
            return 'delete';
        } elseif (preg_match('/\bSELECT\b|\bselect\b/', $query)) {
            return 'select';
        } elseif (preg_match('/\bLIKE\b|\blike\b/', $query)) {
            return 'like';
        } else {
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
    public static function execute(string $typeStr, string $query, array $params)
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
        if ($mysqli->connect_errno) {
            throw new \Exception("Failed to connect to MySQL: " . $mysqli->connect_error);
        }

        // error handling, mysql 8.1+
        mysqli_report(MYSQLI_REPORT_ERROR);

        try 
        {
    
            // $result = $mysqli->query($query);
            $statement = $mysqli->prepare($query);
            if(strpos($query, '?'))
            {
                $statement->bind_param($typeStr, ...$params);
            }
            $statement->execute();
            $result = $statement->get_result();

            $affected_rows = $statement->affected_rows;
            // var_dump($affected_rows);
            preg_match_all('/(\S[^:]+): (\d+)/', $mysqli->info, $matches); 
            $infoArr = array_combine ($matches[1], $matches[2]);
            // var_dump($infoArr);

        } 
        catch (\mysqli_sql_exception $mysqli_sql_exception) 
        {
            unset($result);
            $mysqli->close();
            throw new \Exception($mysqli_sql_exception->getMessage());
        }
        
        $out = null;
        // var_dump('code is here',$result);
        if($result instanceof \mysqli_result && $result->num_rows == 0) 
        {
            // in case of searching for an id which is not exists
            return $out;
        }
        if ($result instanceof \mysqli_result && $result->num_rows > 0) {
            // Fetch all the results as an associative array
            $out = $result->fetch_all(MYSQLI_ASSOC);
            // var_dump($out);
            // Free result set
            $result->free_result();
        } else {
            if (self::_queryVerb($query) === 'insert') {
                $out = $mysqli->insert_id;
            } else {
                $out = $result;
            }

        }

        // closing the connection
        $mysqli->close();

        return $out;
    }

    /**
     * prepare query for execution
     */
    protected final function query_builder($queryType)
    {
        
        if ($queryType === 'insert') {
            $columns = '';
            $placeholders = '';
            $values = [];

            $query = 'insert into ' . static::$table . ' ';

            foreach ((object) $this->fields() as $key) {
                if ($key === 'id') {
                    continue;
                }
                if (!isset($this->$key)) {
                    continue;
                }
                $columns .= '`' . $key . '`,';
                
                $values []= self::sanitizer($this->$key);
                $placeholders .= ' ?,';
            }

            $columns = '(' . substr($columns, 0, -1) . ')';
            $placeholders = '(' . substr($placeholders, 0, -1) . ')';

            return (object)[
                'statement' => $query . $columns . ' values ' . $placeholders,
                'fields' => $values
            ];
        } else if ($queryType === 'update') {
            $values = [];
            $query = 'UPDATE ' . static::$table . ' SET ';

            foreach ((object) $this->fields() as $key) {
                if ($key === 'id') {
                    continue;
                }
                $query .= ' `' . $key . '` = ?,';
                $values []= self::sanitizer($this->$key);
            }

            $query = substr($query, 0, -1);
            $query .= ' WHERE id= ? ;';
            $values []= $this->id;
            return (object)[
                'statement' => $query,
                'fields' => $values
            ];
        } else if ($queryType === 'delete') {
            $query = 'DELETE FROM ' . static::$table . ' WHERE id= ?;';
            $values = [$this->id];
            return (object)[
                'statement' => $query,
                'fields' => $values
            ];
        }

    }

    /**
     * save the changes of the object to the database, update if it exists.
     * @return array|bool|int|string|\mysqli_result
     */
    public function save()
    {
        if (empty($this->id)) {
            try {
                $query = $this->query_builder('insert');
                $typeStr = $this->prepare_query('insert');
                $this->id = $this->execute($typeStr, $query->statement, $query->fields);
                return true;
            } catch (\Throwable $th) {
                throw $th;
            }

        } else {
            try {
                $query = $this->query_builder('update');
                $typeStr = $this->prepare_query('update');
    
                return $this->execute($typeStr, $query->statement, $query->fields);
            } catch (\Throwable $th) {
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
        if (!empty($this->id)) {
            try {
                $query = $this->query_builder('delete');
                $typeStr = $this->prepare_query('delete');

                $this->id = $this->execute($typeStr, $query->statement, $query->fields);

                return true;
            } catch (\Throwable $th) {
                throw $th;
            }

        } else {
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
        try {
            // $query = $this->query_builder('select', [], $id);
            $entity = static::class;
            $instance = new $entity();

            $query = 'SELECT * FROM ' . static::$table . ' WHERE `id`= ?;';
            /**
             * @todo, id might not be integer!
             */
            $data = self::execute('i', $query, [self::sanitizer($id)]);
            
            if(!empty($data))
            {
                $instance = self::cast(static::class, (object) $data[0]);
            }

            return $instance;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * retrieves all the data related to this modle and convert it to object.
     * @return object[]
     */
    public static function all()
    {
        try {

            $query = 'SELECT * FROM ' . static::$table;
            $data = self::execute('', $query,[]);

            // $entity = static::class;

            $collection = [];
            foreach ($data as $item) {
                $item = (object) $item;
                // $instance = new $entity();

                $instance = self::cast(static::class, $item);

                $collection[] = $instance;

                unset($instance);
            }


            return $collection;

        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * impelements where
     * 
     * please note that in case of where this functions returns all matches
     * 
     * @param string $field_name
     * @param mixed $value
     * @param string $operator
     * @return array
     */
    public static function where(string $field_name, $value, string $operator='=')
    {
        try 
        {

            $query = 'SELECT * FROM '. static::$table .' ';
            if (self::_queryVerb($operator) === 'like') {
                $query .='where `'.$field_name.'` '.$operator.' "%'.$value.'%";';
            } else {
                $query .='where `'.$field_name.'`'.$operator.'"'.$value.'";';
            }
            
            
            
            $data = self::execute($query);

            // $entity = static::class;

            $collection = [];
            foreach ($data as $item) 
            {
                $item = (object) $item;
                // $temp = new $entity;
                $temp = self::cast(static::class, $item);

                $collection []= $temp;

                unset ($item);
                unset($tmp);
            }


            return $collection;

        } 
        catch (\Throwable $th) 
        {
            throw $th;
        }
    }


    /**
     * prepares the query to be used in mysql prepare.
     * @return string
     */
    public function prepare_query($queryType):string
    {
        /**
         * for each query type, fields may vary. 
         * henceforth first for each types of queries we have to
         * compose an array of fields involved.
         * 
         * next we use reflections to get the type of each variable to compose the type string which 
         * will be used in bind_param function as the first arg.
         * 
         * here is the map:
         * i - integer
         * d - double
         * s - string
         * b - binary
         */
        $typeStr = '';

        $map = function($type){
            if ($type === 'int') {
                return 'i';
            }
            else if($type === 'double'){
                return 'd';
            }
            else if($type === 'string'){
                return 's';
            }
            else if($type === 'bool'){
                return 'b';
            }
            throw new \Exception('Undefined type.');
        };

        if ($queryType === 'insert') {

            foreach ((object) $this->fields() as $key) {
                if ($key === 'id') {
                    continue;
                }
                if (!isset($this->$key)) {
                    continue;
                }
                $type = gettype($key);
                $typeStr .= $map($type);
            }

            return $typeStr;
        } else if ($queryType === 'update') {

            foreach ((object) $this->fields() as $key) {
                if ($key === 'id') {
                    continue;
                }
                // if (!isset($this->$key)) {
                //     continue;
                // }
                $type = gettype($key);
                $typeStr .= $map($type);
            }

            // last parameter is id
            $type = gettype('id');
            $typeStr .= $map($type);

            return $typeStr;
        } else if ($queryType === 'delete') {
            $type = gettype('id');
            $typeStr .= $map($type);

            return $typeStr;
        }


    }

    /**
     * to handle validation error of parameters reported by php filter_var
     * 
     * to be used in pair with prepare_query()
     * @return void
     */
    protected static function sanitizer_error_callback()
    {

    }




}

<?php

namespace SajedZarinpour\DB;

/**
 * this file contains the base model which entities will extend. the important note here is the use of traits to controll database adaptors.
 */
abstract class Model implements \JsonSerializable
{
    // use Mysql;

    /**
     * Class casting which will be used by database adaptor traits later on.
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    protected final static function cast($destination, $sourceObject)
    {
        // instantiating based on class full name
        if (is_string($destination)) {
            $destination = new $destination();
        }

        $sourceReflection = new \ReflectionObject($sourceObject);
        $destinationReflection = new \ReflectionObject($destination);

        $sourceProperties = $sourceReflection->getProperties();

        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $type = $propDest->getType();
                if ($type) {
                    $typeName = $type->getName();

                    // Cast the value to the appropriate type
                    settype($value, $typeName);
                }
                $propDest->setAccessible(true);
                $propDest->setValue($destination,$value);
            } else {
                $destination->$name = $value;
            }
        }
        return $destination;
    }

    public final static function fields()
    {
        // get the namespaced name of the class, needed for consistency with php 7
        $class = static::class;
        // get a reflection of the class
        $reflection = new \ReflectionObject(new $class());
        // all public properties of the class are considered as the equivalent columns of related table unless you explicitly exclude them in $except variable in your class
        $fields =   array_column(
                        $reflection->getProperties(
                            \ReflectionProperty::IS_PUBLIC
                        ), 
                    'name');
        // exclude public fields which does not have equivalent column in corresponding table
        if(isset(static::$except)){
            foreach (static::$except as $value) {
                if (($key = array_search($value, $fields)) !== false) {
                    unset($fields[$key]);
                }
            }
        }
        
        return $fields;
    }

    public function __debugInfo()
    {
        return [
            'General Info' => [
                'table name' => (new \ReflectionProperty(static::class, 'table'))->getValue(),
                'visible fields to queries' => $this->fields(),
                'fields hidden to queries' => isset(static::$except) ? (new \ReflectionProperty(static::class, 'except'))->getValue() : [],
                'fields hidden to serilization' => isset(static::$hiden_fields) ? static::$hiden_fields : [],
            ],
            'attributes' => (function(){
                $all_properties = array_column(
                    (new \ReflectionClass(static::class)
                )->getProperties(), 
                'name');

                $statics = array_column(
                    (new \ReflectionClass(static::class)
                )->getProperties(\ReflectionProperty::IS_STATIC), 
                'name');
                
                foreach ($all_properties as $key) 
                {
                    if(in_array($key, $statics)) {
                        continue;
                    }
                    $fields []= [
                        // (string)$key => in_array($key, $statics) ? (new \ReflectionProperty(static::class, $key))->getValue() : $this->$key
                        (string)$key =>  isset($this->$key) ? $this->$key : null
                    ];
                }
                return $fields;
            })()
        ];
    }

        /**
     * controls how this object will be serialized using `json_encode` function.
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        if(isset(static::$hiden_fields))
        {
            foreach (static::$hiden_fields as $field) {
                unset($vars[$field]);
            }
        }
        return $vars;
    }
}
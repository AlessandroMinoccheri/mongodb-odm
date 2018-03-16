<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Types;

use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\Types;
use MongoDB\BSON\ObjectId;
use function end;
use function explode;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;
use function str_replace;

/**
 * The Type interface.
 *
 */
abstract class Type
{
    public const ID = 'id';
    public const INTID = 'int_id';
    public const CUSTOMID = 'custom_id';
    public const BOOL = 'bool';
    public const BOOLEAN = 'boolean';
    public const INT = 'int';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const DATE = 'date';
    public const KEY = 'key';
    public const TIMESTAMP = 'timestamp';
    public const BINDATA = 'bin';
    public const BINDATAFUNC = 'bin_func';
    public const BINDATABYTEARRAY = 'bin_bytearray';
    public const BINDATAUUID = 'bin_uuid';
    public const BINDATAUUIDRFC4122 = 'bin_uuid_rfc4122';
    public const BINDATAMD5 = 'bin_md5';
    public const BINDATACUSTOM = 'bin_custom';
    public const HASH = 'hash';
    public const COLLECTION = 'collection';
    public const OBJECTID = 'object_id';
    public const RAW = 'raw';

    /** Map of already instantiated type objects. One instance per type (flyweight). */
    private static $typeObjects = [];

    /** The map of supported doctrine mapping types. */
    private static $typesMap = [
        self::ID => Types\IdType::class,
        self::INTID => Types\IntIdType::class,
        self::CUSTOMID => Types\CustomIdType::class,
        self::BOOL => Types\BooleanType::class,
        self::BOOLEAN => Types\BooleanType::class,
        self::INT => Types\IntType::class,
        self::INTEGER => Types\IntType::class,
        self::FLOAT => Types\FloatType::class,
        self::STRING => Types\StringType::class,
        self::DATE => Types\DateType::class,
        self::KEY => Types\KeyType::class,
        self::TIMESTAMP => Types\TimestampType::class,
        self::BINDATA => Types\BinDataType::class,
        self::BINDATAFUNC => Types\BinDataFuncType::class,
        self::BINDATABYTEARRAY => Types\BinDataByteArrayType::class,
        self::BINDATAUUID => Types\BinDataUUIDType::class,
        self::BINDATAUUIDRFC4122 => Types\BinDataUUIDRFC4122Type::class,
        self::BINDATAMD5 => Types\BinDataMD5Type::class,
        self::BINDATACUSTOM => Types\BinDataCustomType::class,
        self::HASH => Types\HashType::class,
        self::COLLECTION => Types\CollectionType::class,
        self::OBJECTID => Types\ObjectIdType::class,
        self::RAW => Types\RawType::class,
    ];

    /** Prevent instantiation and force use of the factory method. */
    final private function __construct()
    {
    }

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value)
    {
        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The PHP representation of the value.
     */
    public function convertToPHPValue($value)
    {
        return $value;
    }

    public function closureToMongo()
    {
        return '$return = $value;';
    }

    public function closureToPHP()
    {
        return '$return = $value;';
    }

    /**
     * Register a new type in the type map.
     *
     * @param string $name  The name of the type.
     * @param string $class The class name.
     */
    public static function registerType($name, $class)
    {
        self::$typesMap[$name] = $class;
    }

    /**
     * Get a Type instance.
     *
     * @param string $type The type name.
     * @return \Doctrine\ODM\MongoDB\Types\Type $type
     * @throws \InvalidArgumentException
     */
    public static function getType($type)
    {
        if (! isset(self::$typesMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid type specified "%s".', $type));
        }
        if (! isset(self::$typeObjects[$type])) {
            $className = self::$typesMap[$type];
            self::$typeObjects[$type] = new $className();
        }
        return self::$typeObjects[$type];
    }

    /**
     * Get a Type instance based on the type of the passed php variable.
     *
     * @param mixed $variable
     * @return \Doctrine\ODM\MongoDB\Types\Type $type
     * @throws \InvalidArgumentException
     */
    public static function getTypeFromPHPVariable($variable)
    {
        if (is_object($variable)) {
            if ($variable instanceof \DateTimeInterface) {
                return self::getType('date');
            } elseif ($variable instanceof ObjectId) {
                return self::getType('id');
            }
        } else {
            $type = gettype($variable);
            switch ($type) {
                case 'integer':
                    return self::getType('int');
            }
        }
        return null;
    }

    public static function convertPHPToDatabaseValue($value)
    {
        $type = self::getTypeFromPHPVariable($value);
        if ($type !== null) {
            return $type->convertToDatabaseValue($value);
        }
        return $value;
    }

    /**
     * Adds a custom type to the type map.
     *
     * @static
     * @param string $name      Name of the type. This should correspond to what getName() returns.
     * @param string $className The class name of the custom type.
     * @throws MappingException
     */
    public static function addType($name, $className)
    {
        if (isset(self::$typesMap[$name])) {
            throw MappingException::typeExists($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Checks if exists support for a type.
     *
     * @static
     * @param string $name Name of the type
     * @return bool TRUE if type is supported; FALSE otherwise
     */
    public static function hasType($name)
    {
        return isset(self::$typesMap[$name]);
    }

    /**
     * Overrides an already defined type to use a different implementation.
     *
     * @static
     * @param string $name
     * @param string $className
     * @throws MappingException
     */
    public static function overrideType($name, $className)
    {
        if (! isset(self::$typesMap[$name])) {
            throw MappingException::typeNotFound($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Get the types array map which holds all registered types and the corresponding
     * type class
     *
     * @return array $typesMap
     */
    public static function getTypesMap()
    {
        return self::$typesMap;
    }

    public function __toString()
    {
        $e = explode('\\', get_class($this));
        return str_replace('Type', '', end($e));
    }
}

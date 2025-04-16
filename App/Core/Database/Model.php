<?php

namespace App\Core\Database;

use App\Core\Caching\Cache;
use App\Core\Caching\FileCachingStrategy;
use JsonSerializable;
use App\Core\AppException;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\UnicodeString;

class Model implements JsonSerializable
{
    /**
     * Constructs the model and initializes the properties and table name.
     *
     * This method calls `setupProperties` to populate the `$properties` array
     * with the object's protected properties and their values, and then
     * resolves the table name for the class using `getTableName`.
     *
     */
    public function __construct()
    {
        static::getTableName();
    }

    /**
     * Creates a new instance of the model without invoking the constructor,
     * populating it directly with the provided values.
     *
     * This method bypasses setter methods and is primarily used by the
     * `Database` class to instantiate a model with original, unprocessed data.
     *
     * @param array $params Associative array of property values to set on the instance.
     * @return static An instance of the model populated with raw values.
     * @throws \ReflectionException If reflection fails during instantiation.
     * @see \App\Core\Database\Database
     */
    public static function rawInstance(array $params): static
    {
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $reflectionProperties = $reflection->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectionAttributes = $reflectionProperty->getAttributes(Field::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($reflectionAttributes)) {
                $value = $params[$reflectionProperty->getName()];
                $reflectionProperty->setValue($instance, $value);
            }
        }

        static::getTableName();

        return $instance;
    }

    /**
     * Saves the current model data to the database.
     *
     * This method prepares an SQL query to insert or update the model's data,
     * using the column names derived from the model's properties, converted to snake_case.
     *
     */
    public function save(): void
    {
        $properties = $this->toArray();

        $propertyNames = array_keys($properties);
        $columnNames = array_map(fn($name) => (new UnicodeString($name))->snake()->toString(), $propertyNames);
        $columnNames = implode(', ', $columnNames);

        $columnValuePlaceholders = array_map(fn(string $key) => ":$key", array_keys($properties));
        $columnValuePlaceholders = implode(', ', $columnValuePlaceholders);

        $updateColumns = array_map(function (string $key) {
            $columnName = (new UnicodeString($key))
                ->snake()
                ->toString();

            return "$columnName = VALUES($columnName)";
        }, array_keys($properties));

        $updateColumns = implode(", ", $updateColumns);

        $tableName = static::getTableName();

        $sql = "INSERT INTO $tableName ($columnNames)
                VALUES ($columnValuePlaceholders)
                ON DUPLICATE KEY UPDATE $updateColumns";

        Database::execWithParams($sql, $properties);
    }

    /**
     * Finds and returns a model instance by its ID.
     *
     * This method retrieves a single record from the database, matching the given
     * `id`, and returns it as an instance of the model.
     *
     * @param int|string $id The ID of the record to retrieve.
     * @return static|null The populated model instance.
     * @throws \ReflectionException If reflection fails during the model's instantiation.
     */
    public static function find(int|string $id): ?static
    {
        $tableName = static::getTableName();

        $sql = "SELECT * FROM $tableName WHERE id = :id";
        $params = ['id' => $id];

        return Database::fetchObject($sql, $params, static::class);
    }

    /**
     * Returns an associative array of property values initialized by the `setupProperties` method.
     *
     * This method provides access to the raw values stored in the `$properties` array,
     * without using getter methods or applying any data processing.
     *
     * @return array Associative array of raw property values.
     * @see \App\Core\Model::setupProperties
     */
    public function toArray(): array
    {
        $cache = new Cache(new FileCachingStrategy());

        $class = static::class;
        $cacheKey = "{$class}_properties";

        $mappedProperties = $cache->fetch($cacheKey);

        if (!$mappedProperties) {
            $reflection = new ReflectionClass($this);

            $propertiesReflections = $reflection->getProperties();

            $mappedProperties = [];

            foreach ($propertiesReflections as $propertyReflection) {
                $reflectionAttributes = $propertyReflection->getAttributes(Field::class, ReflectionAttribute::IS_INSTANCEOF);

                if (count($reflectionAttributes)) {
                    $mappedProperties[] = $propertyReflection->getName();
                }
            }

            $cache->store($cacheKey, $mappedProperties);
        }

        $properties = [];

        foreach ($mappedProperties as $propertyName) {
            // in case of FatalError in here, the problem is likely the cache. clean it and try again
            $properties[$propertyName] = $this->$propertyName;
        }

        return $properties;
    }

    /**
     * Converts the model's properties to an associative array using their getter methods.
     *
     * This ensures that the returned values are the processed or formatted versions,
     * such as decrypted data, rather than the raw values stored in the object.
     *
     * @return array Associative array with processed property values via getters.
     * @throws \App\Core\AppException If a getter method is missing for any property.
     */
    public function jsonSerialize(): array
    {
        $properties = $this->toArray();

        $propertyNames = array_keys($properties);

        foreach ($propertyNames as $propertyName) {
            $getterName = 'get' . ucfirst($propertyName);

            if (!method_exists($this, $getterName)) {
                throw new AppException("Getter method `" . static::class . "::$getterName()` not found.");
            }

            $properties[$propertyName] = call_user_func([$this, $getterName]);
        }

        return $properties;
    }

    /**
     * Returns the table name for the given class, converting the class name to snake_case and pluralizing it.
     *
     * This method uses reflection to derive the class name, applies a snake_case transformation,
     * and pluralizes the result using the English pluralization rules.
     * The table name is cached in a static property for efficiency.
     *
     * @return string The generated table name in snake_case and pluralized form.
     */
    public static function getTableName(): string
    {
        $cache = new Cache(new FileCachingStrategy());

        $class = static::class;
        $cacheKey = "{$class}_table_name";
        $tableName = $cache->fetch($cacheKey);

        if (!$tableName) {
            $reflection = new ReflectionClass(static::class);
            $className = $reflection->getShortName();

            $snakeCaseName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));

            $inflector = new EnglishInflector();
            $tableName = $inflector->pluralize($snakeCaseName)[0];

            $cache->store($cacheKey, $tableName);
        }

        return $tableName;
    }
}

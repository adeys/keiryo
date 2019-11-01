<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08/02/2019
 * Time: 02:36
 */

namespace Keiryo\DataMapper;

class ChangeTracker
{

    /**
     * @var \ReflectionClass[]
     */
    private static $reflected = [];

    /**
     * Get changes within two objects
     *
     * @param object $original
     * @param object $current
     * @return array
     * @throws \ReflectionException
     */
    public static function getChanges(object $original, object $current): array
    {
        $class = get_class($original);
        if ($class === get_class($current)) {
            if (!($reflection = self::$reflected[$class] ?? null)) {
                $reflection = new \ReflectionClass($class);
                foreach ($reflection->getProperties() as $property) {
                    $property->setAccessible(true);
                }

                self::$reflected[$class] = $reflection;
            }

            $data = $originalData = [];
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $name = $property->getName();
                $data[$name] = $property->getValue($current);
                $originalData[$name] = $property->getValue($original);
            }

            $changes = [];
            foreach ($data as $key => $value) {
                if (($value !== ($originalData[$key] ?? null))) {
                    $changes[$key] = $value;
                }
            }

            return $changes;
        }

        return [];
    }
}

<?php

namespace Core;

class Obj
{
    /**
     * Convert object to array recursively
     *
     * @param $object
     * @param bool $nonRecursive
     * @return array
     */
    public static function toArray($object, bool $nonRecursive = false) :array
    {
        if (!is_object($object)) {
            throw new Exception('Var is not an object: ' . gettype($object));
        }

        if (method_exists($object, 'toArray')) {
            $arr = $object->toArray();
        } else {
            $arr = (array)$object;
        }

        if (!$nonRecursive) {
            foreach ($arr as $key => $value) {
                if (is_object($value)) {
                    $arr[$key] = self::toArray($value);
                }
            }
        }

        return $arr;
    }
}
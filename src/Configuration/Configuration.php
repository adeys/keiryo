<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 13:51
 */

namespace Keiryo\Configuration;

use Symfony\Component\Yaml\Yaml;

class Configuration
{

    private $defaults = [];

    private $values = [];

    /**
     * Configuration constructor.
     * @param array $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->values = $defaults;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        $parts = explode('.', $key);
        $temp = $this->values[array_shift($parts)] ?? null;

        foreach ($parts as $part) {
            if (!$temp) {
                break;
            }
            $temp = $temp[$part] ?? null;
        }

        return $temp ?? $default;
    }

    /**
     * Add values to a preregistered key
     *
     * @param string $key
     * @param $value
     */
    public function add(string $key, $value)
    {
        $old = $this->get($key);
        if ($old && is_array($old)) {
            $value = array_merge($old, is_array($value) ? $value : [$value]);
            $this->_set($key, $value, false);
        }
    }

    /**
     * Override a predefined value
     *
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value)
    {
        $this->_set($key, $value, true);
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $replace
     */
    private function _set(string $key, $value, bool $replace = false)
    {
        $parts = explode('.', $key);
        $root = array_shift($parts);

        if (empty($parts)) {
            $temp = $value;
        } else {
            $temp = $value;
            while (count($parts) > 0) {
                $part = array_pop($parts);
                $temp = [$part => $temp];
            }
        }

        $this->values[$root] = $replace
            ? $temp
            : (array_merge($this->values[$root] ?? [], $temp));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $parts = explode('.', $key);
        $temp = $this->values[array_shift($parts)] ?? [];

        foreach ($parts as $part) {
            if (!isset($temp[$part])) {
                return false;
            }

            $temp = $temp[$part];
        }

        return true;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function addDefault(string $key, $value)
    {
        $this->defaults[$key] = $value;
    }

    /**
     * @param string $file
     * @param string|null $namespace
     * @return bool
     */
    public function load(string $file, ?string $namespace = null): bool
    {
        if (!file_exists($file)) {
            return false;
        }

        $type = (new \SplFileInfo($file))->getExtension();
        switch ($type) {
            case 'yml':
            case 'yaml':
                $values = Yaml::parseFile($file) ?? [];
                break;
            case 'json':
                $values = json_decode(file_get_contents($file), true);
                break;
            case 'php':
                $values = require $file;
                break;
            default:
                $values = [];
                break;
        }

        $values = $this->parse($values);
        $this->values = array_merge($this->values, $namespace ? [$namespace => $values] : $values);
        return true;
    }

    /**
     * @param array $input
     * @return array
     */
    private function parse(array $input): array
    {
        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                $input[$key] = $this->parse($value);
            }

            $regex = array_reduce(array_keys($this->defaults), function ($initial, $current) {
                return "$initial|$current";
            }, '');
            $regex = trim($regex, '|');
            $regex = "~%($regex)%~";

            if (is_string($value)) {
                while (preg_match($regex, $value, $matches)) {
                    $param = $matches[1];
                    $value = preg_replace("~%$param%~", $this->defaults[$param], $value);
                }
            }
        }
        return $input;
    }
}

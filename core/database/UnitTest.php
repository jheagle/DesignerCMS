<?php
/**
 * Created by PhpStorm.
 * User: joshuaheagle
 * Date: 2019-06-04
 * Time: 9:26 PM
 */

namespace Core\Database;

use Core\DataTypes\Potential;

/**
 * Class UnitTest
 *
 * @package Core\Database
 */
class UnitTest implements Potential
{

    private static $instance;

    private static $breakpoints;

    private static $origFiles;

    private static $copyFiles;

    private $filename;

    private $db;

    private $currFunction;

    private $prevData;

    private $curreData;

    private $prevLine;

    private $currLine;

    private $pause;

    private function __construct(&$db, $filename = __FILE__)
    {
        $this->db = $db;
        $this->filename = $filename;
        if (!is_array($this->origFiles) || !is_array($this->copyFiles)) {
            $this->origFiles = $this->copyFiles = [];
        }
        if (strpos($filename, 'utest') === false) {
            $this->origFiles[$filename] = $filename;
            $this->copyFiles[$filename] = preg_replace(
                '/\\.[^.\\s]{3,4}$/',
                '.utest.php',
                $filename
            );
            if (!copy(
                $this->origFiles[$filename],
                $this->copyFiles[$filename]
            )) {
                $this->db->consoleOut(
                    "~!Failed to Copy File: [{$filename}]!~",
                    'PHP'
                );
            } else {
                $this->db->consoleOut(
                    "Created File Copy: [{$filename}]",
                    'PHP'
                );
            }
            header('Location: ' . basename($this->copyFiles[$filename]));
            die();
        }
    }

    private function __destruct()
    {
        if (strpos($this->filename, 'utest') === true) {
            self::$instance = null;
            foreach ($this->copyFiles as &$file) {
                $this->db->consoleOut(
                    unlink($file)
                        ? "Removed File: [{$file}]"
                        : "~!Failed to Remove File: [{$file}]!~",
                    'PHP'
                );
                unset($file);
            }
            $this->copyFile = null;
        }
    }

    public static function instantiateTest(&$db, $filename = __FILE__)
    {
        if (self::$instance == null) {
            self::$instance = new self($db, $filename);
        }

        return self::$instance;
    }

    public function __get($property)
    {
        if (!isset($this->{$property})) {
            return;
        }
        if (is_array($this->{$property})) {
            $new_output = [];
            foreach ($this->{$property} as $key => $value) {
                if (is_array($value)) {
                    $new_output[$key] = $this->{$property}[$key];
                } else {
                    $new_output[$key] = stripslashes(
                        htmlentities(
                            str_replace('\r', '', $value),
                            ENT_HTML5,
                            'UTF-8',
                            false
                        )
                    );
                }
            }

            return $new_output;
        }

        return stripslashes(
            htmlentities(
                str_replace('\r', '', $this->{$property}),
                ENT_HTML5,
                'UTF-8',
                false
            )
        );
    }

    public function set($property, $input)
    {
        if (!property_exists($this, $property)) {
            return;
        }
        if (is_array($input)) {
            $new_input = [];
            foreach ($input as $key => $value) {
                $new_input[$key] = addslashes(
                    html_entity_decode(trim($value), ENT_HTML5, 'UTF-8')
                );
            }
            $this->{$property} = $new_input;
        }
        $this->{$property} = addslashes(
            html_entity_decode(trim($input), ENT_HTML5, 'UTF-8')
        );
    }

    public function traceProcesses()
    {
        echo '<br/>CLASS: ' . __CLASS__;
        echo '<br/>DIR: ' . __DIR__;
        echo '<br/>FILE: ' . __FILE__;
        echo '<br/>FUNCTION: ' . __FUNCTION__;
        echo '<br/>LINE: ' . __LINE__;
        echo '<br/>METHOD: ' . __METHOD__;
        echo '<br/>NAMESPACE: ' . __NAMESPACE__;
        echo '<br/>TRAIT: ' . __TRAIT__;
    }

    private function __clone()
    {

    }

    public function __toString()
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}

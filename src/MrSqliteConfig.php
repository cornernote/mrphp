<?php
require_once(dirname(__FILE__) . '/MrInstance.php');

/**
 * MrSqliteConfig implements protocols for accessing configuration keys using properties.
 *
 *
 * Accessing Configuration Keys Using Properties
 *
 * Config keys can be accessed in the way like accessing normal object members.
 * Reading or writing a config key will cause the invocation of the corresponding getter or setter method:
 * <pre>
 * $config = MrConfig::createInstance(array('file'=>'/path/to/config.json'));
 * $a=$config->text;     // equivalent to $a=$config->getConfig('text');
 * $config->text='abc';  // equivalent to $config->setConfig('text','abc');
 * </pre>
 *
 *
 * MrSqliteConfig instance is available through the static instance() method.
 * @method static MrSqliteConfig instance()
 *
 *
 * Credits
 *
 * This class was written and compiled by Brett O'Donnell and Zain ul abidin.
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain ul abidin <zainengineer@gmail.com>
 * @copyright Copyright (c) 2013, Brett O'Donnell and Zain ul abidin
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class MrSqliteConfig extends MrInstance
{

    /**
     * @var string filename of the sqlite database
     */
    public $database;

    /**
     * @var array mode to use when creating files and folders
     */
    public $mode = array('folder' => 0777, 'file' => 0666); // file folder

    /**
     * @var array to store the config keys and values
     */
    private $_configs = array();

    /**
     * @var SQLiteDatabase
     */
    private $_sqlite;

    /**
     * PHP getter magic method.
     * This method is overridden so that config keys can be accessed like properties.
     * @param string $name config key
     * @return mixed config value
     * @see getAttribute
     */
    public function __get($name)
    {
        if (isset($this->_configs[$name]))
            return $this->_configs[$name];
        else
            return parent::__get($name);
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that config keys can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if ($name != 'configs' && $this->setConfig($name, $value) !== false)
            return true;
        return parent::__set($name, $value);
    }

    /**
     *
     */
    public function init()
    {
        $configs = $this->sqlite->query("SELECT config_key, config_value FROM config")->fetchAll(SQLITE_ASSOC);
        foreach ($configs as $config)
            $this->_configs[$config['config_key']] = $this->unserialize($config['config_value']);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getConfig($name)
    {
        return $this->_configs[$name];
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->_configs;
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    public function setConfig($name, $value)
    {
        return $this->setConfigs(array(
            $name => $value,
        ));
    }

    /**
     * @param $configs
     * @return bool
     */
    public function setConfigs($configs)
    {
        foreach ($configs as $name => $value) {
            if ($value !== null) {
                $this->_configs[$name] = $value;
                $this->saveConfig($name, $value);
            }
            else {
                unset($this->_configs[$name]);
                $this->deleteConfig($name);
            }
        }
        return true;
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    private function saveConfig($name, $value)
    {
        $sql = "SELECT config_value FROM config WHERE config_key='" . $this->escape($name) . "'";
        $config = $this->sqlite->query($sql);
        if ($config->numRows()) {
            if ($config->fetchSingle() != $value)
                $this->updateConfig($name, $value);
        }
        else
            $this->insertConfig($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    private function insertConfig($name, $value)
    {
        $sql = "INSERT INTO config (config_key, config_value) VALUES ('" . $this->escape($name) . "', '" . $this->escape($this->serialize($value)) . "')";
        $this->sqlite->queryExec($sql);
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    private function updateConfig($name, $value)
    {
        $sql = "UPDATE config SET config_value='" . $this->escape($this->serialize($value)) . "' WHERE config_key='" . $this->escape($name) . "'";
        $this->sqlite->queryExec($sql);
    }

    /**
     * @param $name
     * @return bool
     */
    private function deleteConfig($name)
    {
        $sql = "DELETE FROM config WHERE config_key='" . $this->escape($name) . "'";
        $this->sqlite->queryExec($sql);
    }

    /**
     * @return bool|SQLiteDatabase
     * @throws Exception
     */
    public function getSqlite()
    {
        // return existing object
        if ($this->_sqlite)
            return $this->_sqlite;

        // get the database name
        if (!$this->database)
            $this->database = dirname(dirname(__FILE__)) . '/data/' . get_class($this) . '.db';

        // create the folder
        if (!file_exists(dirname($this->database)))
            if (!mkdir(dirname($this->database), $this->mode['folder'], true))
                return false;

        // connect to the database
        $this->_sqlite = new SQLiteDatabase($this->database, $this->mode['file']);
        if (!$this->_sqlite)
            throw new Exception(strtr('Unable to create sqlite database for {className}', array(
                '{className}' => get_class($this),
            )));

        // create the table if needed
        if (!$this->_sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name='config'")->numRows())
            $this->sqlite->queryExec("CREATE TABLE config(config_key text UNIQUE NOT NULL PRIMARY KEY, config_value text)");

        return $this->_sqlite;
    }

    /**
     * @param $data
     * @return array|string
     */
    private function escape($data)
    {
        if (is_array($data))
            return array_map('sqlite_escape_string', $data);
        return sqlite_escape_string($data);
    }

    /**
     * @param $data
     * @return string
     */
    private function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function unserialize($data)
    {
        return unserialize($data);
    }

}

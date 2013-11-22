<?php
require_once(dirname(__FILE__) . '/MrInstance.php');

/**
 * MrConfig implements protocols for accessing configuration data using properties.
 *
 *
 * Accessing Configuration Keys Using Properties
 *
 * Config data can be accessed in the way like accessing normal object members.
 * Reading or writing a config key will cause the invocation of the corresponding getter or setter method:
 * <pre>
 * $config = MrConfig::createInstance(array('file'=>'/path/to/config.json'));
 * $a=$config->text;     // equivalent to $a=$config->getConfig('text');
 * $config->text='abc';  // equivalent to $config->setConfig('text','abc');
 * </pre>
 *
 *
 * MrConfig instance is available through the static instance() method.
 * @method static MrConfig instance()
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
class MrConfig extends MrInstance
{

    /**
     * @var string full path to the json config file
     */
    public $file;

    /**
     * @var array config keys and values
     */
    private $_configs = array();

    /**
     * Return the value of a config key
     *
     * @param $name
     * @return mixed
     */
    public function getConfig($name)
    {
        return $this->_configs[$name];
    }

    /**
     * Return an array of all config keys and values
     *
     * @return array
     */
    public function getConfigs()
    {
        return $this->_configs;
    }

    /**
     * Set the value of a config key
     *
     * @param $name
     * @param $value
     */
    public function setConfig($name, $value)
    {
        $this->setConfigs(array($name => $value));
    }

    /**
     * Set the value of all config keys and values and writes to the config file
     *
     * @param $configs
     */
    public function setConfigs($configs)
    {
        foreach ($configs as $name => $value)
            if ($value !== null)
                $this->_configs[$name] = $value;
            elseif (isset($this->_configs[$name]))
                unset($this->_configs[$name]);
        file_put_contents($this->file, json_encode($this->_configs));
    }

    /**
     * Initializes the instance, loading data from the config file into the config array.
     */
    public function init()
    {
        // return existing object
        if ($this->_configs)
            return;

        // get the database name
        if (!$this->file)
            $this->file = dirname(dirname(__FILE__)) . '/data/' . get_class($this) . '.json';

        // create the folder
        if (!file_exists(dirname($this->file)))
            if (!mkdir(dirname($this->file), 0777, true))
                throw new Exception(strtr('Could not create directory for {class}.', array(
                    '{class}' => get_class($this),
                )));

        // create the file
        if (!file_exists($this->file))
            if (!file_put_contents($this->file, json_encode($this->_configs)))
                throw new Exception(strtr('Could not create file for {class}.', array(
                    '{class}' => get_class($this),
                )));

        $this->_configs = json_decode(file_get_contents($this->file), true);
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that config keys can be accessed like properties.
     *
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
     *
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
     * PHP isset magic method.
     * This method is overridden to allow using isset() to detect if a config key is set or not.
     *
     * @param string $name the property name or the event name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->_configs[$name]))
            return true;
        return parent::__get($name);
    }

    /**
     * PHP unset magic method.
     * This is a PHP magic method that we override to allow using unset() to set a config key to be null.
     *
     * @param string $name the property name or the event name
     * @return mixed
     */
    public function __unset($name)
    {
        if (isset($this->_configs[$name]))
            $this->setConfig($name, null);
        else
            parent::__unset($name);
    }

}

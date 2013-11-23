<?php

/**
 * MrConfigLite implements protocols for accessing configuration data.
 *
 *
 * Accessing Configuration Data
 *
 * <pre>
 * $config = new MrConfigLite('/path/to/config.json');
 * $a=$config->getConfig('text');
 * $config->setConfig('text','abc');
 * </pre>
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
class MrConfigLite
{

    /**
     * @var string full path to the json config file
     */
    public $file;

    /**
     * @var string php or json
     */
    public $storage = 'php';

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

        if ($this->storage == 'json')
            file_put_contents($this->file, json_encode($this->_configs));
        else
            file_put_contents($this->file, '<?php return ' . var_export($this->_configs, true) . ';');
    }

    /**
     * Initializes the instance, loading data from the config file into the config array.
     *
     * @param null|string $file
     * @throws Exception
     */
    public function __construct($file = null)
    {
        // get the database name
        $this->file = $file ? $file : dirname(dirname(__FILE__)) . '/data/' . get_class($this) . '.' . $this->storage;

        // create the folder
        if (!file_exists(dirname($this->file)))
            if (!mkdir(dirname($this->file), 0777, true))
                throw new Exception(strtr('Could not create directory for {class}.', array(
                    '{class}' => get_class($this),
                )));

        // create the file
        if (!file_exists($this->file)) {
            if ($this->storage == 'json')
                $contents = json_encode(array());
            else
                $contents = '<?php return ' . var_export(array(), true) . ';';
            if (!file_put_contents($this->file, $contents))
                throw new Exception(strtr('Could not create file for {class}.', array(
                    '{class}' => get_class($this),
                )));
        }

        if ($this->storage == 'json')
            $this->_configs = json_decode(file_get_contents($this->file), true);
        else
            $this->_configs = require($this->file);
    }

}

<?php
require_once(dirname(__FILE__) . '/MrInstance.php');

/**
 * Class MrConfig
 *
 * @method static MrConfig instance()
 */
class jsonConfig extends MrInstance
{

    /**
     * @var string filename of the json
     */
    public $jsonPath;

    /**
     * @var array mode to use when creating files and folders
     */
    public $mode = array('folder' => 0777, 'file' => 0666); // file folder

    /**
     * @var array
     */
    private $jsonDecoded = null;

    /**
     * PHP getter magic method.
     * This method is overridden so that config keys can be accessed like properties.
     * @param string $name config key
     * @return mixed config value
     * @see getAttribute
     */
    public function __get($name)
    {
        if (isset($this->jsonDecoded[$name]))
            return $this->jsonDecoded[$name];
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
        $this->loadJsonData();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getConfig($name)
    {
        return $this->jsonDecoded[$name];
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->jsonDecoded;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setConfig($name, $value)
    {
        $this->setConfigs(array(
            $name => $value,
        ));
    }

    /**
     * @param $configs
     */
    public function setConfigs($configs)
    {
        foreach ($configs as $name => $value) {
            if ($value !== null) {
                $this->jsonDecoded[$name] = $value;
            }
            else {
                unset($this->jsonDecoded[$name]);
            }
            $this->saveConfig($name, $value);
        }
    }

    /**
     */
    private function saveConfig()
    {
        $encoded = json_encode($this->jsonDecoded);
        file_put_contents($this->jsonPath, $encoded);
    }

    /**
     * @throws Exception
     */
    public function loadJsonData()
    {
        // return existing object
        if ($this->jsonDecoded)
            return;

        // get the database name
        if (!$this->jsonPath)
            $this->jsonPath = dirname(dirname(__FILE__)) . '/json/' . get_class($this) . '.json';

        // create the folder
        if (!file_exists(dirname($this->jsonPath)))
            if (!mkdir(dirname($this->jsonPath), $this->mode['folder'], true)) {
                throw new Exception('Could not create directory ' . $this->jsonPath);
            }

        $contents = file_get_contents($this->jsonPath);
        $this->jsonDecoded = json_decode($contents, true);
    }
}

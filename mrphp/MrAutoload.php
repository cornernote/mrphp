<?php
require_once(dirname(__FILE__) . '/MrInstance.php');

/**
 * MrAutoload implements protocols for autoloading other classes.
 *
 *
 * NOTE - this is a work in progress and not intended for production use
 *
 *
 * Credits
 *
 * This class was written and compiled by Brett O'Donnell and Zain ul abidin.
 * Some code was derived from Yii Framework written by Qiang Xue.
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain ul abidin <zainengineer@gmail.com>
 * @copyright Copyright (c) 2013, Brett O'Donnell and Zain ul abidin
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @copyright Copyright (c) 2013, Yii Software LLC
 *
 * @license BSD-3-Clause https://raw.github.com/cornernote/mrphp/master/LICENSE
 */
class MrAutoload
{

    /**
     * @var array class map used by the autoload mechanism.
     * The array keys are the class names and the array values are the corresponding class file paths.
     */
    public $classMap = array();

    /**
     * @var array
     */
    public $_includePaths = array();

    /**
     * @var bool
     */
    public $throw = true;

    /**
     * @var bool
     */
    public $prepend = false;

    /**
     * @var bool
     */
    public $enableIncludePath = true;

    /**
     * @var array
     */
    private static $_aliases = array('system' => MR_PATH); // alias => path

    /**
     *
     */
    public function init()
    {
        spl_autoload_register(array($this, 'autoload'), $this->throw, $this->prepend);
    }

    /**
     * @return array
     */
    public function getIncludePaths()
    {
        return $this->_includePaths;
    }

    /**
     * @param $includePaths
     */
    public function setIncludePaths($includePaths)
    {
        $this->_includePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includePaths as $includePath)
            $this->_includePaths[] = $includePath;
        if ($this->enableIncludePath) {
            if (set_include_path('.' . PATH_SEPARATOR . implode(PATH_SEPARATOR, array_unique($this->_includePaths))) === false)
                $this->enableIncludePath = false;
        }
    }

    /**
     * @param $className
     * @return bool
     */
    public function autoload($className)
    {
        // class in classMap
        if (isset($this->classMap[$className])) {
            include($this->classMap[$className]);
            return true;
        }
        // class without namespace
        if (strpos($className, '\\') === false) {
            // use include path
            if ($this->enableIncludePath === false) {
                foreach ($this->includePaths as $includePath) {
                    $classFile = $includePath . DIRECTORY_SEPARATOR . $className . '.php';
                    if (is_file($classFile)) {
                        include($classFile);
                        return true;
                    }
                }
            }
            // rely on php to find the file
            else {
                include($className . '.php');
                return true;
            }
        }
        // class name with namespace in PHP 5.3
        else {
            $namespace = str_replace('\\', '.', ltrim($className, '\\'));
            if (($path = self::getPathOfAlias($namespace)) !== false) {
                include($path . '.php');
                return true;
            }
        }
        // class not found
        return false;
    }

    /**
     * Translates an alias into a file path.
     * Note, this method does not ensure the existence of the resulting file path.
     * It only checks if the root alias is valid or not.
     *
     * @param string $alias alias (e.g. system.web.CController)
     * @return mixed file path corresponding to the alias, false if the alias is invalid.
     */
    public static function getPathOfAlias($alias)
    {
        if (isset(self::$_aliases[$alias]))
            return self::$_aliases[$alias];
        elseif (($pos = strpos($alias, '.')) !== false) {
            $rootAlias = substr($alias, 0, $pos);
            if (isset(self::$_aliases[$rootAlias]))
                return self::$_aliases[$alias] = rtrim(self::$_aliases[$rootAlias] . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, substr($alias, $pos + 1)), '*' . DIRECTORY_SEPARATOR);
        }
        return false;
    }

    /**
     * Create a path alias.
     * Note, this method neither checks the existence of the path nor normalizes the path.
     *
     * @param string $alias alias to the path
     * @param string $path the path corresponding to the alias. If this is null, the corresponding path alias will be removed.
     */
    public static function setPathOfAlias($alias, $path)
    {
        if (empty($path))
            unset(self::$_aliases[$alias]);
        else
            self::$_aliases[$alias] = rtrim($path, '\\/');
    }

}



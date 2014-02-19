<?php

/**
 * MrInstance implements protocols for static instance methods and defining using properties.
 *
 *
 * Static Instance Methods
 * Methods can be called through a static instance.
 *
 * Instantiation is performed by calling the static createInstance() method, and passing a config array:
 * <pre>
 * $fooBar=FooBar::createInstance(array('foo'=>'bar', ... )); // equivalent to $fooBar=new FooBar(array('foo'=>'bar', ... ))
 * </pre>
 * The static instance of the object can be called globally:
 * <pre>
 * $a=FooBar::instance()->foo; // equivalent to $a=$fooBar->foo
 * </pre>
 * The signature of the FooBar class is as follows:
 * <pre>
 * class FooBar extends MrInstance { public $foo; ... }
 * </pre>
 *
 *
 * Defining Using Properties
 * Automatic calling of getter/setter when accessing an undefined property.
 *
 * A property is defined by a getter method, and/or a setter method.
 * Properties can be accessed in the way like accessing normal object members.
 * Reading or writing a property will cause the invocation of the corresponding getter or setter method:
 * <pre>
 * $a=$instance->text;     // equivalent to $a=$instance->getText();
 * $instance->text='abc';  // equivalent to $instance->setText('abc');
 * </pre>
 * The signatures of getter and setter methods are as follows:
 * <pre>
 * // getter, defines a readable property 'text'
 * public function getText() { ... }
 * // setter, defines a writable property 'text' with $value to be set to the property
 * public function setText($value) { ... }
 * </pre>
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
abstract class MrInstance
{

    /**
     * An array containing instantiated objects
     *
     * @var MrInstance[]
     * @see instance
     */
    static public $instances;

    /**
     * Returns a static instance with the config values assigned to properties.
     * It is provided for preparing the instance for static instance methods.
     *
     * @param array $config the values to be assigned to object properties
     * @param null|string $id the id of the instance, defaults to the called class name
     * @return MrInstance the instantiated object
     * @see __construct
     */
    public static function createInstance($config = array(), $id = null)
    {
        $class = get_called_class();
        if (!$id)
            $id = $class;
        $instance = new $class($config, $id);
        foreach ($config as $k => $v)
            $instance->$k = $v;
        return self::$instances[$id] = $instance;
    }

    /**
     * Returns a static instance.
     * It is provided for invoking static instance methods.
     *
     * @param null|string $id the id of the instance, defaults to the called class name
     * @return MrInstance the instantiated object
     * @throws Exception if the instance has not been created
     * @see createInstance
     */
    public static function instance($id = null)
    {
        if (!$id)
            $id = get_called_class();
        if (isset(self::$instances[$id]))
            return self::$instances[$id];
        throw new Exception(strtr('Instance "{id}" has not been created.', array(
            '{id}' => $id,
        )));
    }

    /**
     * Returns a property value.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using the following syntax to read a property:
     * <pre>
     * $value=$this->propertyName;
     * </pre>
     *
     * @param string $name the property name
     * @return mixed the property value
     * @throws Exception if the property or event is not defined
     * @see __set
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter();
        throw new Exception(strtr('Property "{class}.{property}" is not defined.', array(
            '{class}' => get_class($this),
            '{property}' => $name,
        )));
    }

    /**
     * Sets value of a property.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using the following syntax to set a property:
     * <pre>
     * $this->propertyName=$value;
     * </pre>
     *
     * @param string $name the property name
     * @param mixed $value the property value
     * @return mixed
     * @throws Exception if the property is not defined or is read only
     * @see __get
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);
        if (method_exists($this, 'get' . $name))
            throw new Exception(strtr('Property "{class}.{property}" is read only.', array(
                '{class}' => get_class($this),
                '{property}' => $name,
            )));
        else
            throw new Exception(strtr('Property "{class}.{property}" is not defined.', array(
                '{class}' => get_class($this),
                '{property}' => $name,
            )));
    }

    /**
     * Checks if a property value is null.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using isset() to detect if a property is set or not.
     *
     * @param string $name the property name or the event name
     * @return boolean
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter() !== null;
        return false;
    }

    /**
     * Sets a property to be null.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using unset() to set a property to be null.
     *
     * @param string $name the property name or the event name
     * @throws Exception if the property is read only.
     * @return mixed
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            $this->$setter(null);
        elseif (method_exists($this, 'get' . $name))
            throw new Exception(strtr('Property "{class}.{property}" is read only.', array(
                '{class}' => get_class($this),
                '{property}' => $name,
            )));
    }

}

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
 * FooBar::createInstance(array('propertyName' => 'propertyValue', ...));
 * </pre>
 * The static instance of the object can be called globally:
 * <pre>
 * echo FooBar::instance()->propertyName;
 * </pre>
 * The signature of the FooBar class is as follows:
 * <pre>
 * class FooBar extends MrInstance { public $propertyName; ... }
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
 * This class was compiled by Brett O'Donnell, the concepts came from Yii Framework written by Qiang Xue.
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @copyright 2013, All Rights Reserved
 */
abstract class MrInstance
{

    /**
     * An array containing instantiated objects
     *
     * @var MrInstance[]
     * @see createInstance
     * @see instance
     */
    static private $_instances;

    /**
     * Returns an instantiated object with the config values assigned to object properties
     * It is provided for preparing the object for static instance methods.
     *
     * @param array $config the values to be assigned to object properties
     * @param null|string $id the id of the instance, defaults to the called class name
     * @return MrInstance the instantiated object
     * @see instance
     */
    public static function createInstance($config = array(), $id = null)
    {
        if (!$id)
            $id = get_called_class();
        self::$_instances[$id] = new $id($config);
        foreach ($config as $k => $v)
            self::$_instances[$id]->$k = $v;
        return self::$_instances[$id];
    }

    /**
     * Returns a static instance of the specified class.
     * It is provided for invoking instance methods.
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
        if (self::$_instances[$id])
            return self::$_instances[$id];
        throw new Exception(strtr('Instance "{id}" has not been created.', array(
            '{id}' => $id,
        )));
    }

    /**
     * Returns a property value.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using the following syntax to read a property:
     * <pre>
     * $value=$instance->propertyName;
     * </pre>
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
     * Sets value of an instance property.
     * Do not call this method.
     * This is a PHP magic method that we override to allow using the following syntax to set a property:
     * <pre>
     * $this->propertyName=$value;
     * </pre>
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

}
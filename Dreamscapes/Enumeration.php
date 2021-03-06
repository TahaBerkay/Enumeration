<?php

/**
 * Dreamscapes\Enumeration
 *
 * Licensed under the BSD (3-Clause) license
 * For full copyright and license information, please see the LICENSE file
 *
 * @copyright   2014 Robert Rossmann
 * @author      Robert Rossmann <rr.rossmann@me.com>
 * @link        https://github.com/Dreamscapes/Enumeration
 * @license     http://choosealicense.com/licenses/bsd-3-clause   BSD (3-Clause) License
 */


namespace Dreamscapes;

/**
 * The Enumeration class
 *
 * All enumerations should extend this class. Enumerated members
 * should be defined as class constants.
 *
 * @package     Enumeration
 */
class Enumeration
{
    // Instances of enumeration members are cached here
    private static $instances = [];


    // Each instance of an Enumeration holds the member's value here
    private $value;


    /**
     * Instances are not allowed to be created outside of this class
     *
     * @param     string      $name       The enumerated member's name
     * @param     mixed       $value      The enumerated member's value
     */
    final private function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the name of the member that holds given value
     *
     * <p class="alert">This method is type-sensitive - see the example below.</p>
     * <h3>Use case</h3>
     * You have a value that is defined in an enumeration and you would like
     * to know the name of the enumeration member that holds this value.
     *
     * <h3>Example</h3>
     * ```
     * class Animal extends Dreamscapes\Enumeration
     * {
     *   const Horse = 0;
     *   const Dog = 1;
     * }
     *
     * echo Animal::getName(0); // Prints 'Horse'
     * echo Animal::getName('0'); // Returns null, method is type-sensitive
     * ```
     *
     * @param     string    $value      The member's expected value (type-sensitive)
     * @return    string    The name of the member that holds this value or null if not defined
     */
    public static function getName($value)
    {
        $key = array_search($value, static::toArray(), true);  // Search using strict comparison

        if ($key === false) {
            static::triggerUndefinedConstantError($value);
        }

        return $key;
    }

    /**
     * Semantic alias for Enumeration::getName()
     *
     * @param     string    $value      The member's expected value (type-sensitive)
     * @return    string    The name of the member that holds this value or null if not defined
     */
    public static function withValue($value)
    {
        return static::getName($value);
    }

    /**
     * Get the value of a given member's name
     *
     * <h3>Use case</h3>
     * You have a string representation of the Enumeration member and you would
     * like to know the value that member holds.
     *
     * <h3>Example</h3>
     * ```
     * class Animal extends Dreamscapes\Enumeration
     * {
     *   const Horse = 0;
     *   const Dog = 1;
     * }
     *
     * echo Animal::getValue('Dog'); // Prints an integer, 1
     * ```
     *
     * @param     string|Enumeration      $member     The member's expected name
     * @return    mixed                               The value of the member
     */
    public static function getValue($member)
    {
        // Typecast to string (we could be getting either a string or an instance of
        // Enumeration in $member)
        $member = (string)$member;
        if (! static::isDefined($member)) {
            static::triggerUndefinedConstantError($member);
        }

        return static::toArray()[$member];
    }

    /**
     * Semantic alias for Enumeration::getValue()
     *
     * @param     string|Enumeration      $member     The member's expected name
     * @return    mixed                               The value of the member
     */
    public static function named($member)
    {
        return static::getValue($member);
    }

    /**
     * Does a member with this name exist in the enumeration?
     *
     * <h3>Example</h3>
     * ```
     * class Animal extends Dreamscapes\Enumeration
     * {
     *   const Horse = 0;
     *   const Dog = 1;
     * }
     *
     * echo Animal::isDefined('Dog'); // Prints an integer, 1 (bool true)
     * echo Animal::isDefined('Cat'); // Prints nothing (bool false)
     * ```
     *
     * @param     string      $member     The member's expected name
     * @return    bool                    **true** if such member is defined, **false** otherwise
     */
    public static function isDefined($member)
    {
        return array_key_exists((string)$member, static::toArray());
    }

    /**
     * Semantic alias for Enumeration::isDefined()
     *
     * @param     string      $member     The member's expected name
     * @return    bool                    **true** if such member is defined, **false** otherwise
     */
    public static function contains($member)
    {
        return static::isDefined($member);
    }

    /**
     * Semantic alias for Enumeration::isDefined()
     *
     * @param     string      $member     The member's expected name
     * @return    bool                    **true** if such member is defined, **false** otherwise
     */
    public static function has($member)
    {
        return static::isDefined($member);
    }

    /**
     * Semantic alias for Enumeration::isDefined()
     *
     * @param     string      $member     The member's expected name
     * @return    bool                    **true** if such member is defined, **false** otherwise
     */
    public static function defines($member)
    {
        return static::isDefined($member);
    }

    /**
     * Get all members defined in this Enumeration
     *
     * <p class="alert">The returned array's order is determined by the order
     * in which the constants are defined in the class.</p>
     *
     * <h3>Example</h3>
     * ```
     * class Animal extends Dreamscapes\Enumeration
     * {
     *   const Horse = 0;
     *   const Dog = 1;
     * }
     *
     * print_r(Animal::allMembers());
     * // Array
     * // (
     * //   0 => 'Horse'
     * //   1 => 'Dog'
     * // )
     * ```
     *
     * @return    array     An ordered list of all Enumeration members
     */
    public static function allMembers()
    {
        return array_keys(static::toArray());
    }

    /**
     * Convert the Enumeration into an array
     *
     * <h3>Example</h3>
     * ```
     * class Animal extends Dreamscapes\Enumeration
     * {
     *   const Horse = 0;
     *   const Dog = 1;
     * }
     *
     * print_r(Animal::toArray());
     * // Array
     * // (
     * //   'Horse' => 0
     * //   'Dog' => 1
     * // )
     * ```
     *
     * @return    array
     */
    public static function toArray()
    {
        $enumClass = new \ReflectionClass(get_called_class());

        return $enumClass->getConstants();
    }

    /**
     * Get the string representation of the Enumeration, without namespace
     *
     * <h3>Example</h3>
     * ```
     * namespace Fauna;
     *
     * class Animal extends \Dreamscapes\Enumeration {}
     *
     * echo Animal::getType(); // Animal
     * echo \Dreamscapes\Enumeration::getType(); // Enumeration
     * ```
     *
     * @return    string      The name of the Enumeration class, without namespace
     */
    public static function getType()
    {
        $type = explode("\\", get_called_class());

        return end($type);
    }

    /**
     * Maps static method calls to defined enumeration members
     *
     * @internal
     * @return    Enumeration     Instance of the Enumeration subclass
     */
    public static function __callStatic($method, $args)
    {
        return static::getMemberInstance(get_called_class(), $method);
    }

    /**
     * Get the value of the enumerated member represented by this instance
     *
     * @return    mixed         Value of the enumerated member
     */
    final public function value()
    {
        return $this->value;
    }

    /**
     * Allow enumeration members to be typecast to strings
     *
     * @return    string      The value of the enumeration member in a string representation
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * Factory for enumeration members' instance representations
     *
     * @param     string        $enumeration    The class for which the instance should be obtained
     * @param     string        $member         The enumerated member's name to retrieve
     * @return    Enumeration   An instance of Enumeration, representing the given member's value
     */
    final private static function getMemberInstance($enumeration, $member)
    {
        if (! isset(self::$instances[$enumeration]) ||
            ! isset(self::$instances[$enumeration][$member])
        ) {
            // Instance of this enumeration member does not exist yet - let's create one
            self::$instances[$enumeration][$member] =
                new $enumeration($member, static::getValue($member));
        }

        return self::$instances[$enumeration][$member];
    }

    /**
     * Trigger an error that php triggers when attempting to access undefined class constant
     *
     * @param     string        $const        The name of the constant being accessed
     */
    final private static function triggerUndefinedConstantError($const)
    {
        $trace = debug_backtrace();
        trigger_error(
            'Undefined class constant ' . $const .
            ' in ' . $trace[1]['file'] .
            ' on line ' . $trace[1]['line'],
            E_USER_ERROR
        );
    }
}

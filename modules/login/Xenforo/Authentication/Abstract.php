<?php

/**
 * Abstract authentication base.
 *
 * @package XenForo_Authentication
 */
abstract class XenForo_Authentication_Abstract
{
	/**
	 * Default Salt Length
	 *
	 * @var integer
	 */
	const DEFAULT_SALT_LENGTH = 30;

	/**
	 * Initialize data for the authentication object.
	 *
	 * @param string   Binary data from the database
	 */
	abstract public function setData($data);

	/**
	 * Perform authentication against the given password
	 *
	 * @param integer $userId The user ID we're trying to authenticate as. This may not be needed, but can be used to "upgrade" auth schemes.
	 * @param string $password Password (plain text)
	 *
	 * @return bool True if the authentication is successful
	 */
	abstract public function authenticate($userId, $password);

	/**
	* Generate new authentication data for the given password
	*
	* @param string $password Password (plain text)
	*
	* @return false|string The result will be stored in a binary result
	*/
	abstract public function generate($password);

	/**
	 * Returns true if the auth method provides a password. A user can switch away
	 * from this auth by requesting a password be emailed to him/her. An example of
	 * this situation is FB registrations.
	 *
	 * @return boolean
	 */
	public function hasPassword()
	{
		return true;
	}

	/**
	 * Indicates whether or not this authentication method can be upgraded to a different, better method.
	 *
	 * @return boolean
	 */
	public function isUpgradable()
	{
		return true;
	}

	/**
	 * Gets the upgraded authentication object if there is one.
	 *
	 * @return XenForo_Authentication_Abstract|false
	 */
	public function getUpgradedAuthenticationObject()
	{
		return $this->isUpgradable() ? self::createDefault() : false;
	}

	/**
	 * Returns the name of the authentication class being used.
	 *
	 * @return string
	 */
	public function getClassName()
	{
		return XenForo_Application::resolveDynamicClassToRoot($this);
	}

	/**
	* Generates an arbtirary length salt
	*
	* @return string
	*/
	public static function generateSalt($length = null)
	{
		if (!$length)
		{
			$length = self::DEFAULT_SALT_LENGTH;
		}

		return XenForo_Application::generateRandomString($length);
	}

	/**
	* Factory method to get the named authentication module. The class must exist or be autoloadable
	* or an exception will be thrown.
	*
	* @param string Class to load
	*
	* @return XenForo_Authentication_Abstract
	*/
	public static function create($class)
	{
		if (!$class)
		{
			return self::createDefault();
		}

		$originalClass = $class;
		$class = XenForo_Application::resolveDynamicClass($class);

		if (XenForo_Application::autoload($class))
		{
			$obj = new $class;
			if ($obj instanceof XenForo_Authentication_Abstract)
			{
				return $obj;
			}
		}

		throw new XenForo_Exception("Invalid authentication module '$class' ($originalClass) specified");
	}

	/**
	 * Factory method to create the default authentication handler.
	 *
	 * @return XenForo_Authentication_Abstract
	 */
	public static function createDefault()
	{
		return self::create('XenForo_Authentication_Core12');
	}
}

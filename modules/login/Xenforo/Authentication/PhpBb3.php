<?php

/**
 * phpBB 3 authentication method.
 *
 * @package XenForo_Authentication
 */
class XenForo_Authentication_PhpBb3 extends XenForo_Authentication_Abstract
{
	/**
	* Password info for this authentication object
	*
	* @var array
	*/
	protected $_data = array();

	/**
	* Initialize data for the authentication object.
	*
	* @param string   Binary data from the database
	*/
	public function setData($data)
	{
		$this->_data = unserialize($data);
	}

	/**
	* Generate new authentication data
	* @see XenForo_Authentication_Abstract::generate()
	*/
	public function generate($password)
	{
		throw new XenForo_Exception('Cannot generate authentication for this type.');
	}

	/**
	* Authenticate against the given password
	* @see XenForo_Authentication_Abstract::authenticate()
	*/
	public function authenticate($userId, $password)
	{
		if (!is_string($password) || $password === '' || empty($this->_data))
		{
			return false;
		}

		$passwordHash = new XenForo_PasswordHash(8, true);
		return $passwordHash->CheckPassword($password, $this->_data['hash']);
	}
}
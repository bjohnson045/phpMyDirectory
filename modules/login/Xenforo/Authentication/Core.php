<?php

/**
 * Core authentication method.
 *
 * @package XenForo_Authentication
 */
class XenForo_Authentication_Core extends XenForo_Authentication_Abstract
{
	/**
	* Password info for this authentication object
	*
	* @var array
	*/
	protected $_data = array();

	/**
	* Hash function to use for generating salts and passwords
	*
	* @var string
	*/
	protected $_hashFunc = '';

	/**
	* Setup the hash function
	*/
	protected function _setupHash()
	{
		if ($this->_hashFunc)
		{
			return;
		}

		if (extension_loaded('hash'))
		{
			$this->_hashFunc = 'sha256';
		}
		else
		{
			$this->_hashFunc = 'sha1';
		}
	}

	/**
	* Perform the hashing based on the function set
	*
	* @param string
	*
	* @return string The new hashed string
	*/
	protected function _createHash($data)
	{
		$this->_setupHash();
		switch ($this->_hashFunc)
		{
			case 'sha256':
				return hash('sha256', $data);
			case 'sha1':
				return sha1($data);
			default:
				throw new XenForo_Exception("Unknown hash type");
		}
	}

	protected function _newPassword($password, $salt)
	{
		$hash = $this->_createHash($this->_createHash($password) . $salt);
		return array('hash' => $hash, 'salt' => $salt, 'hashFunc' => $this->_hashFunc);
	}

	/**
	* Initialize data for the authentication object.
	*
	* @param string   Binary data from the database
	*/
	public function setData($data)
	{
		$this->_data = unserialize($data);
		$this->_hashFunc = $this->_data['hashFunc'];
	}

	/**
	* Generate new authentication data
	* @see XenForo_Authentication_Abstract::generate()
	*/
	public function generate($password)
	{
		if (!is_string($password) || $password === '')
		{
			return false;
		}

		$salt = $this->_createHash(self::generateSalt());
		$data = $this->_newPassword($password, $salt);
		return serialize($data);
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

		$userHash = $this->_createHash($this->_createHash($password) . $this->_data['salt']);
		return ($userHash === $this->_data['hash']);
	}
}
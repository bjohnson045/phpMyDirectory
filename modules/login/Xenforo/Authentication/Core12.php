<?php
/**
* XenForo_Application::getConfig()->passwordIterations
* Changed to 10 from Application.php
*/

/**
 * Core authentication method from 1.2 (PHPass).
 *
 * @package XenForo_Authentication
 */
class XenForo_Authentication_Core12 extends XenForo_Authentication_Abstract
{
	/**
	* Password info for this authentication object
	*
	* @var array
	*/
	protected $_data = array();
    var $passwordIterations = 10;

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
		$passwordHash = new XenForo_PasswordHash($this->passwordIterations, false);
		$output = array('hash' => $passwordHash->HashPassword($password));
		return serialize($output);
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

		$passwordHash = new XenForo_PasswordHash($this->passwordIterations, false);
		return $passwordHash->CheckPassword($password, $this->_data['hash']);
	}

	public function isUpgradable()
	{
		if (!empty($this->_data['hash']))
		{
			$passwordHash = new XenForo_PasswordHash($this->passwordIterations, false);
			$expectedIterations = min(intval($this->passwordIterations), 30);
			$iterations = null;

			if (preg_match('/^\$(P|H)\$(.)/i',  $this->_data['hash'], $match))
			{
				$iterations = $passwordHash->reverseItoA64($match[2]) - 5; // 5 iterations removed in PHP 5
			}
			else if (preg_match('/^\$2a\$(\d+)\$.*$/i', $this->_data['hash'], $match))
			{
				$iterations = intval($match[1]);
			}

			return $expectedIterations !== $iterations;
		}

		return true;
	}
}
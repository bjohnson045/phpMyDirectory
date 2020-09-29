<?php
/**
*
* PMD auth plug-in for phpBB3
*
*
* @package login
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Connect to pmd
* Called in acp_board while setting authentication plugins
*/
function init_pmd()
{
	global $db, $config, $user;

    /** @var dbal_mysql */
    $result = $db->sql_query("SELECT login FROM ".$config['pmd_user_table']." LIMIT 1");
	if (!$result) {
		return 'No PMD users table found.  Users table must have at least one user!';
	}

	return false;
}

/**
* Login function
*/
function login_pmd(&$username, &$password)
{
	global $db, $config, $user;

	// do not allow empty password
	if (!$password)
	{
		return array(
			'status'	=> LOGIN_BREAK,
			'error_msg'	=> 'NO_PASSWORD_SUPPLIED',
		);
	}

    $result = $db->sql_query("SELECT login FROM ".$config['pmd_user_table']." LIMIT 1");
    if (!$result) {
        return array(
            'status'        => LOGIN_ERROR_EXTERNAL_AUTH,
            'error_msg'        => 'Unable to connect to PMD database.',
            'user_row'        => array('user_id' => ANONYMOUS),
        );
    }

    $result = $db->sql_query("SELECT login, pass FROM ".$config['pmd_user_table']." WHERE login='".$username."'");
    $pmd_row = $db->sql_fetchrow($result);

	if ($pmd_row) {
        $result = $db->sql_query("SELECT * FROM ".$config['pmd_user_table']." WHERE login='".$username."'");
        if($pmd_row = $db->sql_fetchrow($result)) {
            if(isset($pmd_row['password_hash'])) {
                if($pmd_row['pass'] != hash($pmd_row['password_hash'],htmlspecialchars_decode($password).$pmd_row['password_salt'])) {
                    $pmd_row = false;
                }
            } elseif($pmd_row['pass'] != md5(htmlspecialchars_decode($password))) {
                $pmd_row = false;
            }
        }

        if ($pmd_row) {
			$sql ='SELECT user_id, username, user_password, user_passchg, user_email, user_type
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row){
				// User inactive...
				if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) {
					return array(
						'status'		=> LOGIN_ERROR_ACTIVE,
						'error_msg'		=> 'ACTIVE_ERROR',
						'user_row'		=> $row,
					);
				}

				// Successful login... set user_login_attempts to zero...
				return array(
					'status'		=> LOGIN_SUCCESS,
					'error_msg'		=> false,
					'user_row'		=> $row,
				);
			} else {
				// retrieve default group id
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . $db->sql_escape('REGISTERED') . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row) {
					trigger_error('NO_GROUP');
				}

				// generate user account data
				$pmd_user_row = array(
					'username'		=> $username,
					'user_password'	=> phpbb_hash($password),
					'user_email'	=> $pmd_row['user_email'],
					'group_id'		=> (int) $row['group_id'],
					'user_type'		=> USER_NORMAL,
					'user_ip'		=> $user->ip,
				);

				// this is the user's first login so create an empty profile
				return array(
					'status'		=> LOGIN_SUCCESS_CREATE_PROFILE,
					'error_msg'		=> false,
					'user_row'		=> $pmd_user_row,
				);
			}
		} else {
			// Give status about wrong password...
			return array(
				'status'		=> LOGIN_ERROR_PASSWORD,
				'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}
	}

	return array(
		'status'	=> LOGIN_ERROR_USERNAME,
		'error_msg'	=> 'LOGIN_ERROR_USERNAME',
		'user_row'	=> array('user_id' => ANONYMOUS),
	);
}

/**
* This function is used to output any required fields in the authentication
* admin panel. It also defines any required configuration table fields.
*/
function acp_pmd(&$new)
{
	global $user;

	$tpl = '
    <dl>
        <dt><label for="ldap_uid">PMD Users Table Name:</label><br /><span>Description</span></dt>
        <dd><input type="text" id="pmd_user_table" size="40" name="config[pmd_user_table]" value="' . $new['pmd_user_table'] . '" /></dd>
    </dl>
	';

	// These are fields required in the config table
	return array(
		'tpl'		=> $tpl,
		'config'	=> array('pmd_user_table')
	);
}

?>
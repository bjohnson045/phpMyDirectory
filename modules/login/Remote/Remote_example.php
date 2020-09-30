<?php
// This file must be served by an SSL enabled page.

// Find the potential user
$user = find_remote_user($_POST['username']);
$password = $_POST['password'];

// Compile Result Array
$user_data = array('success' => false);

// Check if user was found and verify password match
if($user AND $user['password'] == $password) {

	// PMD Requires a username and email
	$user_data = array(
	    'success' => true,
		'login' => $user['username'],
		'user_email' => $user['email']
	);
}

// Encode and return results.
echo json_encode($user_data);

// Dummy method for authenticating user.
function find_remote_user($user) {
	$dummy_db = array(
		array('username' => 'remoteguest', 'password' => 'remoteguest', 'email' => 'remoteguest@demo.com'),
		array('username' => 'remotedemo', 'password' => 'remotedemo', 'email' => 'remotedemo@demo.com'),
		array('username' => 'remoteadmin', 'password' => 'remoteadmin', 'email' => 'remoteadmin@demo.com')
	);

	foreach($dummy_db as $user) {
		if($user['username'] == $_POST['username']) {
    		return $user;
        }
	}

	return false;
}
?>
<?php
error_reporting(E_ALL);
require_once 'client/JasperClient.php';
	// Initialize the JasperClient object
$jc = new Jasper\JasperClient('localhost', 8080, 'jasperadmin', 'jasperadmin', '/jasperserver-pro', 'organization_1');

function createNewUser($data) {
	global $jc;
	$newUser = new Jasper\User($data['username'], $data['password'], $data['email'], 'New User', 'organization_1', array('externallyDefined' => 'false', 'roleName' => 'ROLE_USER'), 'true', 'false');
	$jc->putUsers($newUser);
}

if (isset($_GET['deluser'])) {
	$users = $jc->getUsers($_GET['deluser']);
	if(is_array($users) && count($users)>0) {
		$jc->deleteUser($users[0]);
		header('Location: /rest');
	} else {
		$jc->deleteUser($users);
		header('Location: /rest');
	}
}

if (isset($_POST['func'])) {
	switch ($_POST['func']) {
		case 'create':
			createNewUser($_POST);
			$_POST['createSuccess'] = true;
			break;
		case 'delete':
			deleteUser($_POST);
			$_POST['deleteSuccess'] = true;
			break;
		default:
		/*
			if($_POST['createSuccess']) {
				echo "User created! <br />";
		*/
			}
}

?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title> JasperServer Sample API client </title>

	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<script type="application/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"> </script>
	<script type="application/javascript" src="js/main.js"> </script>
</head>


<body id="home">
	<button id="userButton">Fetch Users</button>
			<ul id="userlist">
			</ul>
	<button id="newUser">Create New User</button>
		<div id="userForm">

			<form method="post" id="newUserForm">
				Username: <input type="text" name="username" /> <br />
				Password: <input type="text" name="password" /> <br />
				Email: <input type="text" name="email" /> <br />
				<input type="hidden" id="func" name="func" value="create" />
				<button id="submitNew">Submit</button>
			</form>
		</div>

<!-- SANDBOX -->
<hr />

<?php ?> 



<hr />
<!-- END SANDBOX -->


</body>
</html>
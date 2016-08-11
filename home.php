<?php
	# home.php
	# Interview task for Flobox
	# Created by Christopher Chaaya 11/08/2016

	session_start();

	include_once "database.php";
	
	# Abraham's TwitterOAuth API
	require "vendor/autoload.php";
	use Abraham\TwitterOAuth\TwitterOAuth;

	# Twitter keys for this application
	define('CONSUMER_KEY', "7LoKcX0B42glF2liUTSOHCUhj");
	define('CONSUMER_SECRET', "Ts2HGXSaR0hXbUharTMY2a9zjRPd87mfLQ2hkg1A7wjvWp8u1Z");
	define('OAUTH_CALLBACK', "http://localhost:8000/home.php");

	# if someone tries to come here without logging in, redirect them back to the login page.
	if (!isset($_SESSION['login_type']) || isset($_POST['logout'])) {
		session_destroy();
		echo '<script>window.location="login.php"</script>';
		die();
	
	# if someone's logging in with twitter
	} else if ($_SESSION['login_type'] == "twitter") {
		
		# Get temporary tokens from session
		$request_token = [];
		$request_token['oauth_token'] = $_SESSION['oauth_token'];
		$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

		# Check if token is in db already (note: it might be the short-lived token, or the permanent one we've saved in the session)
		# We do this so we don't have to go through the conditional below every time.
		$tmp_oauth_token = $_SESSION['oauth_token'];
		$result = pg_query($db, "SELECT EXISTS(select 1 FROM users WHERE oauth_token = '$tmp_oauth_token');");
		$row = pg_fetch_row($result);

		# If it's not in the db, we need to get a permanent one and store it in the db and the session for future use.
		if ($row[0] == "f") {
			# Get permanent access_token
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
			$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);

			# Get the twitter user's username (and use that in our web-app)
			$username = $access_token['screen_name'];

			# Get user details
			$user = $connection->get("account/verify_credentials");

			# Place tokens into variable to concatenate into query string
			$perm_oauth_token = $access_token['oauth_token'];
			$perm_oauth_token_secret = $access_token['oauth_token_secret'];

			# Check for token repetition (don't insert token combo twice)
			$result = pg_query($db, "SELECT EXISTS(select 1 FROM users WHERE oauth_token = '$perm_oauth_token' AND oauth_token_secret = '$perm_oauth_token_secret');");
			$row = pg_fetch_row($result);

			# If the token doesn't exist in the db, insert it and
			# Set null password -- not sure what best practice for this is.
			# Alternatives?: randomise password; create a different table for twitter users; etc?
			if ($row[0] == "f") {
				$result = pg_query($db, "INSERT INTO users (username, password, oauth_token, oauth_token_secret) 
			    	VALUES('$username', NULL, '$perm_oauth_token', '$perm_oauth_token_secret');");
			}

			# so that we can keep the session going with the current user
			$_SESSION['oauth_token'] = $perm_oauth_token;
			$_SESSION['user'] = $username;
		}
	}
?>
<html>
	<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
	<body style="background-image:url('http://wallpapersrang.com/wp-content/uploads/2016/02/images-desktop-backgrounds-3d-box-wallpapers-hd-wa.jpg');")>
		<center style="padding-top: 15%; padding-right: 15%; padding-left: 15%">
			<span style="color:white">Hi there, 
			<?php
				echo $_SESSION['user'];

				if (isset($_POST['logout'])) {
					session_destroy();
					echo '<script>window.location="login.php"</script>';
					die();
				}
			?>
			</span>
			<form method="POST">
				<input type="submit" name="logout" class="ui primary button" value="Logout" style="display: block; width: 50%">
			</form>
		</center>
	</body>
	<script src="semantic/dist/semantic.min.js"></script>
</html>
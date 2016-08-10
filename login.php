<?php
	# login.php
	# Interview task for Flobox
	# Created by Christopher Chaaya 11/08/2016

	session_start();
	include_once "database.php";
	require "vendor/autoload.php";
	use Abraham\TwitterOAuth\TwitterOAuth;

	# For Twitter OAuth
	define('CONSUMER_KEY', "7LoKcX0B42glF2liUTSOHCUhj");
	define('CONSUMER_SECRET', "Ts2HGXSaR0hXbUharTMY2a9zjRPd87mfLQ2hkg1A7wjvWp8u1Z");
	define('OAUTH_CALLBACK', "http://localhost:8000/home.php");

?>
<html>
	<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
		<body style="background-image:url('http://wallpapersrang.com/wp-content/uploads/2016/02/images-desktop-backgrounds-3d-box-wallpapers-hd-wa.jpg');")>
		<center style="padding-top: 15%; padding-right: 15%; padding-left: 15%">
			<div class="ui three column middle aligned very relaxed grid container" style="position: relative">
				<div class="column">
					<form method="POST">
						<div class="ui form">
							<p style="font-size:20px; bold=true; color: white">Login (Locally)</p>
							<div class="field">
								<div class="ui left icon input">
									<input type="text" name="username" placeholder="Enter your username" style="display: block; align:right" required>
									<i class="user icon"></i>
								</div>
							</div>
							<div class="field">
								<div class="ui left icon input">
									<input type="password" name="password" placeholder="Enter your password" style="display: block" required>
									<i class="lock icon"></i>
								</div>
							</div>
							<input type="submit" name="login" class="ui primary button" value="Login" style="display: block; width: 50%">
						</div>
					</form>
				</div>
				<div class="column">
					<form method="POST">
						<div class="ui form">
							<p style="font-size:20px; bold=true; color: white">Register</p>
							<div class="field">
								<div class="ui left icon input">
									<input type="text" name="re_username" placeholder="Enter a username" style="display: block; align:right" required>
									<i class="user icon"></i>
								</div>
							</div>
							<div class="field">
								<div class="ui left icon input">
									<input type="password" name="re_password" placeholder="Enter a password" style="display: block" required>
									<i class="lock icon"></i>
								</div>
							</div>
							<input type="submit" name="register" class="ui primary button" value="Register" style="display: block; width: 50%">
						</div>
					</form>
				</div>
				<div class="column">
					<form method="POST">
						<div class="ui form">
							<img src="https://cdn3.iconfinder.com/data/icons/social-icons-5/607/Twitterbird.png" style="width: 50px; height: 50px; margin-bottom: 10px">
							<p style="font-size:20px; bold=true; margin-top: 0px; color: white">Login with Twitter</p>
							<input type="submit" name="twitter" class="ui primary button" value="Go" style="display: block; width: 50%; margin-bottom: -60px">
						</div>
					</form>
				</div>
			</div>
		</center>

		<?php
			# if register button is clicked
			if (isset($_POST['re_username'], $_POST['re_password'], $_POST['register'])) {

				$username = $_POST['re_username'];
				$password = $_POST['re_password'];

				# check if username existing
				$result = pg_query($db, "SELECT EXISTS(select 1 FROM users WHERE username = '$username');");
				$row = pg_fetch_row($result);

				if ($row[0] == "t") {
					echo '<p style="color:white">Sorry, that username exists. Try again.</p>';
				} else {
					# store them into database
					$result = pg_query($db, "INSERT INTO users (username, password, oauth_token, oauth_token_secret) 
		                  VALUES('$username', '$password', NULL, NULL);");

					# send to home page
					$_SESSION['user'] = $username;
					$_SESSION['login_type'] = "local";
					echo '<script>window.location="home.php"</script>';
					die();
				}
				
			# if login button is clicked
			} else if (isset($_POST['username'], $_POST['password'], $_POST['login'])) {

				$username = $_POST['username'];
				$password = $_POST['password'];

				# check database
				$result = pg_query($db, "SELECT EXISTS(select 1 FROM users WHERE username = '$username' AND password = '$password');");
				$row = pg_fetch_row($result);

				if ($row[0] == "t") {
					$_SESSION['user'] = $username;
					$_SESSION['login_type'] = "local";
					echo '<script>window.location="home.php"</script>';
					die();
				} else {
					echo "Invalid!";
				}

			# if twitter button clicked
			} else if (isset($_POST['twitter'])) {
				$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

				$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));
				
				$_SESSION['oauth_token'] = $request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

				$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
				$_SESSION['login_type'] = "twitter";

				echo "<script>window.location='$url'</script>";
				die();
			}
		?>
	</body>
	<script src="semantic/dist/semantic.min.js"></script>
</html>
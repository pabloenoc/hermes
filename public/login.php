<?php

// Check if user is logged in already
session_start();
require_once(__DIR__.'/../app/includes/database.php');

if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['hermes_user_id'])) {
    header('Location: index.php');
    die;
}

function validate_user_email($db, $email) {
    $errors = [];
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindValue(':email', $_POST['email']);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$user) {
	$errors[] = "Email or password invalid.";
    } 

    return $errors;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']))
{
    $errors = validate_user_email($db, $_POST['email']);

    if (empty($errors)) {	
		$stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
		$stmt->bindValue(':email', $_POST['email']);
		$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

		if (password_verify($_POST['password'], $user['password_hash'])) {
		    $_SESSION['hermes_user_id'] = $user['id'];
		    header('Location: ' . $_SERVER['REQUEST_URI']);
		    exit;
		} else {
		    $errors[] = 'Email or password invalid.';
		}
    }
}

?>

<!doctype html>
<html>
    <head>
	<title>Hermes - Login</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<link rel="icon" type="image/png" href="/images/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/images/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png" />
	<meta name="apple-mobile-web-app-title" content="Hermes" />
	<link rel="manifest" href="/site.webmanifest" />

	<meta property="og:title" content="Hermes - Login">
	<meta property="og:description" content="Keep an eye on what matters to you.">
	<meta property="og:image" content="/images/og_image.png">
	<meta property="og:url" content="https://hrmss.enocc.com/register.php">
	<meta property="og:type" content="website">

    </head>
    <body>
	<navbar>
	    <a href="/" class="flex" style="gap: 0.5rem; align-items: center; text-decoration: none; color: inherit;">
		<img src="/images/hermes.webp" id="logo">
		<h1>Account</h1>
	    </a>

	    <a href="/settings.php">
		<!--Gear icon-->
		<svg class="ui-icon"
		     xmlns="http://www.w3.org/2000/svg"
		     width="32"
		     height="32"
		     viewBox="0 0 24 24"
		     fill="none"
		     stroke="currentColor"
		     stroke-width="1.5"
		     stroke-linecap="round"
		     stroke-linejoin="round"
		>
		    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
		    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
		</svg>
	    </a>
	</navbar>

	<main>
	    <h2>Login</h2>

	    <!-- Display form errors -->
	    <?php if (!empty($errors)): ?>
		<div class="" style="color: var(--color-error); padding-left: 1rem;">
		    <p>There was an issue accessing your account.</p>
		    <ul>
			<?php foreach ($errors as $error): ?>
			    <li>
				<?= $error ?>
			    </li>
			<?php endforeach; ?>
		    </ul>
		</div>
	    <?php endif; ?>

	    <form class="signup-form" method="post">
		<div class="form__field">
		    <label for="email">Email</label>
		    <input id="email" type="email" name="email" placeholder="your@email.com">
		</div>
		
		<div class="form__field">
		    <label for="password">Password</label>
		    <input id="password" type="password" name="password" placeholder="Your password" autocomplete="false">
		</div>

		<div class="form__field">
		    <input type="submit" value="Login" class="btn-submit">
		</div>		
	    </form>
	</main>


	<script>
	 // JS goes here...
	</script>



    </body>
</html>


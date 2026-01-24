<?php

// Check if user is logged in already
require_once(__DIR__.'/../app/includes/authentication.php');
require_once(__DIR__.'/../app/includes/database.php');

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

$page_title = 'Login';
require __DIR__ . '/../app/shared/_head.php';
require __DIR__ . '/../app/shared/_navbar.php';
?>

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

    </body>
</html>


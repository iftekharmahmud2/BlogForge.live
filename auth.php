<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect(app_url());
}

$register = ($_GET['mode'] ?? '') === 'register';
$pageTitle = ($register ? 'Create account' : 'Sign in') . ' — BlogForge';

$safeNext = safe_next_url(isset($_GET['next']) && is_string($_GET['next']) ? $_GET['next'] : null, app_url());

require __DIR__ . '/partials/header.php';
?>

<section class="auth-page">
    <div class="auth-card<?= $register ? ' auth-card-wide' : '' ?>">
        <div class="auth-logo">
            <img src="<?= e(app_url('assets/img/blogforge-icon.png')) ?>" alt="BlogForge">
        </div>
        <h1><?= $register ? 'Join BlogForge' : 'Welcome back' ?></h1>
        <p><?= $register ? 'Create your writer account to publish stories.' : 'Sign in to continue reading, reacting, and writing.' ?></p>

        <div class="error-box" data-auth-error role="alert"></div>

        <form data-auth-form data-mode="<?= $register ? 'register' : 'login' ?>" data-next-url="<?= e($safeNext) ?>" novalidate>
            <?php if ($register): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First name</label>
                        <input class="input" id="first_name" name="first_name" required maxlength="40" autocomplete="given-name" placeholder="Alex">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last name</label>
                        <input class="input" id="last_name" name="last_name" required maxlength="40" autocomplete="family-name" placeholder="Rivers">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email address</label>
                <input class="input" id="email" type="email" name="email" required autocomplete="email" placeholder="alex@example.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="pw-field">
                    <input class="input" id="password" type="password" name="password" minlength="8" required autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" placeholder="Minimum 8 characters">
                    <button class="pw-toggle" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <?php if ($register): ?>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <div class="pw-field">
                        <input class="input" id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Re-enter your password">
                        <button class="pw-toggle" type="button" data-pw-toggle aria-label="Show password">Show</button>
                    </div>
                </div>

                <label class="check-row">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="<?= e(app_url('terms.php#terms')) ?>" target="_blank" rel="noopener">Terms of Service</a> and <a href="<?= e(app_url('terms.php#privacy')) ?>" target="_blank" rel="noopener">Privacy Policy</a>.</span>
                </label>
            <?php endif; ?>

            <button class="btn btn-primary" type="submit"><?= $register ? 'Create account' : 'Sign in' ?></button>
        </form>

        <div class="auth-switch">
            <?= $register ? 'Already have an account?' : 'New to BlogForge?' ?>
            <a href="<?= e(app_url($register ? 'auth.php?next=' . urlencode($safeNext) : 'auth.php?mode=register&next=' . urlencode($safeNext))) ?>">
                <b><?= $register ? 'Sign in' : 'Create one' ?></b>
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

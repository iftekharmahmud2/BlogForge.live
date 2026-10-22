<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$sessionUser = current_user();
$u = BlogForge\Auth::fetchUser((string)$sessionUser['id']);

if (!$u) {
    // Session points to an account that no longer exists
    redirect(app_url('api/auth/logout.php'));
}

$pageTitle = 'Edit profile — BlogForge';
$displayName = trim((string)($u['name'] ?? ''));

require __DIR__ . '/partials/header.php';
?>

<section class="editor-page">
    <div class="settings-shell">
        <div class="editor-top">
            <h1>Edit profile</h1>
            <a class="btn btn-secondary btn-sm" href="<?= e(app_url('profile.php')) ?>">← Back to profile</a>
        </div>

        <!-- Profile details -->
        <form class="settings-card" data-profile-form novalidate>
            <div class="settings-head">
                <div class="profile-avatar profile-avatar-sm" data-avatar-preview>
                    <?php if (!empty($u['avatar'])): ?>
                        <img src="<?= e((string)$u['avatar']) ?>" alt="">
                    <?php else: ?>
                        <?= e(initials($displayName)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h2>Your details</h2>
                    <p class="muted">This is what readers see on your stories and responses.</p>
                </div>
            </div>

            <div class="error-box" data-profile-error role="alert"></div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First name</label>
                    <input class="input" id="first_name" name="first_name" required maxlength="40" autocomplete="given-name" value="<?= e((string)($u['first_name'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last name</label>
                    <input class="input" id="last_name" name="last_name" required maxlength="40" autocomplete="family-name" value="<?= e((string)($u['last_name'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="handle">Username</label>
                <div class="input-prefix">
                    <span>@</span>
                    <input class="input" id="handle" name="handle" required minlength="3" maxlength="30" autocomplete="username" value="<?= e((string)($u['handle'] ?? '')) ?>">
                </div>
                <small class="hint">3–30 characters. Letters, numbers, dashes and underscores.</small>
            </div>

            <div class="form-group">
                <label for="email_ro">Email address</label>
                <input class="input" id="email_ro" value="<?= e((string)($u['email'] ?? '')) ?>" disabled>
                <small class="hint">Your email is used to sign in and can’t be changed here.</small>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea class="input" id="bio" name="bio" rows="4" maxlength="280" placeholder="Tell readers a little about yourself…" data-bio><?= e((string)($u['bio'] ?? '')) ?></textarea>
                <small class="hint"><span data-bio-count>0</span> / 280</small>
            </div>

            <div class="form-group">
                <label for="avatar">Profile photo URL <span class="optional">(optional)</span></label>
                <input class="input" id="avatar" name="avatar" type="url" maxlength="500" placeholder="https://…" value="<?= e((string)($u['avatar'] ?? '')) ?>">
            </div>

            <div class="settings-actions">
                <a class="btn btn-secondary" href="<?= e(app_url('profile.php')) ?>">Cancel</a>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
        </form>

        <!-- Password -->
        <form class="settings-card" data-password-form novalidate>
            <div class="settings-head">
                <div>
                    <h2>Change password</h2>
                    <p class="muted">Use at least 8 characters.</p>
                </div>
            </div>

            <div class="error-box" data-password-error role="alert"></div>

            <div class="form-group">
                <label for="current_password">Current password</label>
                <div class="pw-field">
                    <input class="input" id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    <button class="pw-toggle" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">New password</label>
                    <div class="pw-field">
                        <input class="input" id="new_password" type="password" name="new_password" minlength="8" required autocomplete="new-password">
                        <button class="pw-toggle" type="button" data-pw-toggle aria-label="Show password">Show</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Confirm new password</label>
                    <div class="pw-field">
                        <input class="input" id="confirm_new_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password">
                        <button class="pw-toggle" type="button" data-pw-toggle aria-label="Show password">Show</button>
                    </div>
                </div>
            </div>

            <div class="settings-actions">
                <button class="btn btn-dark" type="submit">Update password</button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

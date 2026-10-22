<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'BlogForge — Ideas into impact.';
$isHome = true;
$user = current_user();

require __DIR__ . '/partials/header.php';
?>

<section class="hero cover">
    <div class="container">
        <div class="hero-shell cover-shell">
            <div class="hero-copy">
                <span class="eyebrow">BlogForge.live</span>
                <h1>Ideas<br>into <em>Impact.</em></h1>
                <p>A home for curious minds, bold ideas, and meaningful stories. Read, write, and be part of a growing community of thinkers.</p>

                <div class="hero-actions">
                    <?php if ($user): ?>
                        <a class="btn btn-primary" href="<?= e(app_url('explore.php')) ?>">Start reading →</a>
                        <a class="btn btn-light" href="<?= e(app_url('write.php')) ?>">Write a story</a>
                        <a class="btn btn-ghost" href="<?= e(app_url('profile.php')) ?>">My profile</a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started →</a>
                        <a class="btn btn-light" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                        <a class="btn btn-ghost" href="<?= e(app_url('explore.php')) ?>">Browse stories</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-art">
                <img src="<?= e(app_url('assets/img/blogforge-icon.png')) ?>" alt="BlogForge emblem">
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

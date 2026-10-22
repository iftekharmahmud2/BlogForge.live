<?php
$user = current_user();
$pageTitle = $pageTitle ?? 'BlogForge — Ideas into impact.';
$isHome = $isHome ?? false;
$bodyClasses = trim((isset($postId) ? 'page-article ' : '') . ($isHome ? 'page-home' : ''));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="BlogForge.live — read, write and share meaningful stories.">
    <link rel="icon" href="<?= e(app_url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
    <script>
        window.BLOGFORGE = {
            baseUrl: <?= json_encode(rtrim(app_url(), '/')) ?>,
            csrf: <?= json_encode(csrf_token()) ?>,
            loggedIn: <?= is_logged_in() ? 'true' : 'false' ?>
        };
    </script>
</head>
<body class="<?= e($bodyClasses) ?>">
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(app_url()) ?>">
            <img src="<?= e(app_url('assets/img/blogforge-icon.png')) ?>" alt="BlogForge Logo">
            <span><b>Blog</b><strong>Forge</strong></span>
        </a>

        <nav class="desktop-nav">
            <a href="<?= e(app_url()) ?>">Home</a>
            <a href="<?= e(app_url('explore.php')) ?>">Explore</a>
            <a href="<?= e(app_url('write.php')) ?>">Write</a>
            <a href="<?= e(app_url('about.php')) ?>">About</a>
        </nav>

        <?php if (!$isHome): ?>
        <form class="nav-search" method="GET" action="<?= e(app_url('explore.php')) ?>">
            <svg viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
            <input name="q" placeholder="Search stories, topics…" value="<?= e(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>">
        </form>
        <?php endif; ?>

        <div class="nav-actions<?= $isHome ? ' push-right' : '' ?>">
            <?php if ($user): ?>
                <a class="write-link" href="<?= e(app_url('write.php')) ?>">Write</a>
                <button class="avatar-btn" data-menu-toggle aria-label="Account Menu"><?= e(initials($user['name'])) ?></button>
                <div class="account-menu" data-account-menu>
                    <b><?= e($user['name']) ?></b>
                    <small>@<?= e($user['handle'] ?? 'author') ?></small>
                    <a href="<?= e(app_url('profile.php')) ?>">Profile & stories</a>
                    <a href="<?= e(app_url('profile-edit.php')) ?>">Edit profile</a>
                    <a href="<?= e(app_url('write.php')) ?>">New story</a>
                    <?php if (is_admin()): ?>
                        <a href="<?= e(app_url('admin/')) ?>">Admin dashboard</a>
                    <?php endif; ?>
                    <a href="<?= e(app_url('api/auth/logout.php')) ?>">Sign out</a>
                </div>
            <?php else: ?>
                <a class="signin" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                <a class="btn btn-primary btn-sm" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started</a>
            <?php endif; ?>
            <button class="mobile-toggle" data-mobile-toggle aria-label="Toggle navigation menu">☰</button>
        </div>
    </div>

    <div class="mobile-nav" data-mobile-nav>
        <a href="<?= e(app_url()) ?>">Home</a>
        <a href="<?= e(app_url('explore.php')) ?>">Explore</a>
        <a href="<?= e(app_url('write.php')) ?>">Write</a>
        <a href="<?= e(app_url('about.php')) ?>">About</a>
        <?php if ($user): ?>
            <a href="<?= e(app_url('profile.php')) ?>">Profile & stories</a>
            <a href="<?= e(app_url('profile-edit.php')) ?>">Edit profile</a>
            <?php if (is_admin()): ?>
                <a href="<?= e(app_url('admin/')) ?>">Admin dashboard</a>
            <?php endif; ?>
            <a href="<?= e(app_url('api/auth/logout.php')) ?>">Sign out</a>
        <?php else: ?>
            <a href="<?= e(app_url('auth.php')) ?>">Sign in</a>
            <a href="<?= e(app_url('auth.php?mode=register')) ?>">Get started</a>
        <?php endif; ?>
    </div>
</header>
<main>

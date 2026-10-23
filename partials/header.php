<?php
$user = current_user();
$pageTitle = $pageTitle ?? 'BlogForge — Ideas into impact.';
$isHome = $isHome ?? false;
$bodyClasses = trim('bg-[#fffdfa] text-[#11151b] font-sans antialiased min-h-screen flex flex-col ' . (isset($postId) ? 'page-article ' : '') . ($isHome ? 'page-home' : ''));
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="BlogForge.live — read, write and share meaningful stories.">
    <link rel="icon" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Immediate Tailwind CDN Fallback for Production Deployments -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#ff6b13',
                            600: '#ea580c',
                            700: '#c2410c',
                            DEFAULT: '#ff6b13',
                        },
                        dark: {
                            DEFAULT: '#0e1218',
                            card: '#161a21',
                            border: '#232936',
                        }
                    }
                }
            }
        };
    </script>
    <script>
        window.BLOGFORGE = {
            baseUrl: <?= json_encode(rtrim(app_url(), '/')) ?>,
            csrf: <?= json_encode(csrf_token()) ?>,
            loggedIn: <?= is_logged_in() ? 'true' : 'false' ?>
        };
    </script>
</head>
<body class="<?= e($bodyClasses) ?>">
<header class="sticky top-0 z-50 bg-[#fffdfa]/95 backdrop-blur-md border-b border-[#e9e5df]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Brand Logo & Name (Always visible) -->
        <a class="flex items-center gap-2.5 shrink-0 group" href="<?= e(app_url()) ?>">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge Logo" class="w-9 h-9 object-contain group-hover:scale-105 transition-transform duration-200">
            <span class="text-xl font-bold tracking-tight text-[#11151b]">Blog<strong class="text-[#ff6b13] font-bold">Forge</strong></span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-700">
            <a href="<?= e(app_url()) ?>" class="hover:text-[#ff6b13] transition">Home</a>
            <a href="<?= e(app_url('explore.php')) ?>" class="hover:text-[#ff6b13] transition">Explore</a>
            <a href="<?= e(app_url('write.php')) ?>" class="hover:text-[#ff6b13] transition">Write</a>
            <a href="<?= e(app_url('about.php')) ?>" class="hover:text-[#ff6b13] transition">About</a>
        </nav>

        <!-- Desktop Search Bar -->
        <?php if (!$isHome): ?>
        <form class="hidden lg:flex items-center gap-2 bg-[#f4f2ee] rounded-full px-4 py-2 w-48 xl:w-60 text-sm" method="GET" action="<?= e(app_url('explore.php')) ?>">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
            <input name="q" class="bg-transparent border-0 outline-none w-full text-sm placeholder-gray-500" placeholder="Search stories, topics…" value="<?= e(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>">
        </form>
        <?php endif; ?>

        <!-- Right Side Unified Actions (Tight gap between profile & 3-dot button!) -->
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <?php if ($user): ?>
                <!-- Write CTA (Desktop) -->
                <a class="hidden sm:inline-flex text-sm font-medium text-gray-700 hover:text-[#ff6b13] transition px-2 py-1 mr-0.5" href="<?= e(app_url('write.php')) ?>">Write</a>

                <!-- Profile Avatar Button & Dropdown -->
                <div class="relative">
                    <button class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white font-bold text-xs flex items-center justify-center cursor-pointer shadow-sm hover:opacity-90 focus:outline-none" data-menu-toggle aria-label="Account Menu" type="button">
                        <?= e(initials($user['name'])) ?>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-56 bg-white border border-[#e9e5df] rounded-2xl shadow-xl p-2 z-50 text-sm" data-account-menu>
                        <div class="px-3 py-2 border-b border-gray-100 mb-1">
                            <b class="block text-gray-900 truncate font-semibold"><?= e($user['name']) ?></b>
                            <small class="text-xs text-gray-500">@<?= e($user['handle'] ?? 'author') ?></small>
                        </div>
                        <a href="<?= e(app_url('profile.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">Profile & stories</a>
                        <a href="<?= e(app_url('profile-edit.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">Edit profile</a>
                        <a href="<?= e(app_url('write.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">New story</a>
                        <?php if (is_admin()): ?>
                            <a href="<?= e(app_url('admin/')) ?>" class="block px-3 py-2 rounded-xl text-orange-600 font-medium hover:bg-orange-50 transition">Admin dashboard</a>
                        <?php endif; ?>
                        <a href="<?= e(app_url('api/auth/logout.php')) ?>" class="block px-3 py-2 rounded-xl text-red-600 hover:bg-red-50 mt-1 border-t border-gray-100 transition font-medium">Sign out</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Auth buttons for guests -->
                <a class="hidden sm:inline-flex text-sm font-medium text-gray-700 hover:text-[#ff6b13] px-2.5 py-1.5 transition" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                <a class="hidden sm:inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold px-4 py-2 shadow-sm hover:shadow-md hover:brightness-105 transition mr-0.5" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started</a>
            <?php endif; ?>

            <!-- 3-Dot / Menu Button & its Short Dropdown (Positioned right here!) -->
            <div class="relative">
                <button id="mobile-toggle" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:bg-orange-50 hover:text-[#ff6b13] flex items-center justify-center text-gray-700 transition shadow-sm cursor-pointer focus:outline-none" data-mobile-toggle aria-label="Menu" aria-expanded="false" type="button">
                    <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Short, Compact Dropdown Menu directly under 3-dot button -->
                <div id="mobile-menu" class="hidden absolute right-0 mt-2 w-56 sm:w-64 bg-white border border-[#e9e5df] rounded-2xl shadow-xl p-2 z-50 text-sm flex-col gap-1" data-mobile-nav>
                    <a href="<?= e(app_url()) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="<?= e(app_url('explore.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                        <span>Explore stories</span>
                    </a>
                    <a href="<?= e(app_url('write.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Write a story</span>
                    </a>
                    <a href="<?= e(app_url('about.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>About</span>
                    </a>

                    <?php if (!$user): ?>
                        <div class="sm:hidden border-t border-gray-100 mt-1 pt-1.5 flex flex-col gap-1.5">
                            <a href="<?= e(app_url('auth.php')) ?>" class="w-full text-center py-2 rounded-xl border border-gray-300 font-medium text-xs text-gray-700 hover:bg-gray-50 transition">Sign in</a>
                            <a href="<?= e(app_url('auth.php?mode=register')) ?>" class="w-full text-center py-2 rounded-xl bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-xs shadow-sm hover:brightness-105 transition">Get started</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="grow">

<?php
$user = current_user();
$pageTitle = $pageTitle ?? 'BlogForge — Ideas into impact.';
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="BlogForge.live — read, write and share meaningful stories with a modern, distraction-free publishing platform.">
    <link rel="icon" href="/assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Production Tailwind CDN Fallback -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        serif: ['"Instrument Serif"', 'Georgia', 'serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#ff6b13',
                            600: '#ea580c',
                            700: '#c2410c',
                            DEFAULT: '#ff6b13',
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
<body class="font-sans antialiased min-h-screen flex flex-col bg-[#fffdfa] text-[#11151b]">
<?php if (is_admin() && (string)get_setting('maintenance_mode', '0') === '1'): ?>
    <!-- Admin-only Maintenance Mode Notification Banner -->
    <aside class="bg-gradient-to-r from-amber-500 to-amber-600 text-black text-xs font-semibold px-4 py-2 border-b border-amber-600/30 relative z-50 shadow-sm" role="status" aria-live="polite">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-amber-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong>Maintenance Mode Active:</strong> Public visitors see the under-maintenance screen. Admins retain full access.</span>
            </div>
            <a href="<?= e(app_url('admin/settings.php')) ?>" class="inline-flex items-center gap-1 font-bold text-amber-950 hover:text-white transition underline underline-offset-2">
                <span>Manage in Settings</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </aside>
<?php endif; ?>
<header class="sticky top-0 z-50 bg-[#fffdfa]/90 backdrop-blur-md border-b border-[#e9e5df]/80 text-[#11151b] transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Brand Logo & Name -->
        <a class="flex items-center gap-2.5 shrink-0 group" href="<?= e(app_url()) ?>">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge Logo" class="w-9 h-9 object-contain group-hover:scale-105 transition-transform duration-200">
            <span class="text-xl font-bold tracking-tight text-gray-900">Blog<strong class="text-[#ff6b13] font-bold">Forge</strong></span>
        </a>

        <!-- Desktop Navigation Links: Home, About, Explore, FAQ, Contact (No hamburger on desktop!) -->
        <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-600">
            <a href="<?= e(app_url()) ?>" class="<?= $currentScript === 'index.php' ? 'text-[#ff6b13] font-semibold' : 'hover:text-[#ff6b13]' ?> transition">Home</a>
            <a href="<?= e(app_url('about.php')) ?>" class="<?= $currentScript === 'about.php' ? 'text-[#ff6b13] font-semibold' : 'hover:text-[#ff6b13]' ?> transition">About</a>
            <a href="<?= e(app_url('explore.php')) ?>" class="<?= $currentScript === 'explore.php' ? 'text-[#ff6b13] font-semibold' : 'hover:text-[#ff6b13]' ?> transition">Explore</a>
            <a href="<?= e(app_url('faq.php')) ?>" class="<?= $currentScript === 'faq.php' ? 'text-[#ff6b13] font-semibold' : 'hover:text-[#ff6b13]' ?> transition">FAQ</a>
            <a href="<?= e(app_url('contact.php')) ?>" class="<?= $currentScript === 'contact.php' ? 'text-[#ff6b13] font-semibold' : 'hover:text-[#ff6b13]' ?> transition">Contact</a>
        </nav>

        <!-- Right Side Dynamic Actions -->
        <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
            <?php if ($user): ?>
                <!-- Signed-in Author: Drafts & Write CTA -->
                <a class="hidden sm:inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium <?= $currentScript === 'drafts.php' ? 'text-[#ff6b13] font-semibold' : 'text-gray-600 hover:text-[#ff6b13]' ?> transition px-2 py-1" href="<?= e(app_url('drafts.php')) ?>">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Drafts</span>
                </a>

                <a class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold px-4 py-2 shadow-sm hover:shadow hover:brightness-105 transition" href="<?= e(app_url('write.php')) ?>">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Write</span>
                </a>

                <!-- Profile Avatar Button & Dropdown -->
                <?php $headerAvatar = avatar_url((string)($user['avatar'] ?? '')); ?>
                <div class="relative">
                    <button class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white font-bold text-xs flex items-center justify-center cursor-pointer shadow-sm hover:opacity-90 focus:outline-none overflow-hidden <?= $headerAvatar !== '' ? 'ring-2 ring-orange-100' : '' ?>" data-menu-toggle aria-label="Account Menu" type="button">
                        <?= avatar_inner($headerAvatar, (string)($user['name'] ?? 'Author'), false) ?>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-100 text-gray-700 rounded-2xl shadow-xl p-2 z-50 text-sm" data-account-menu>
                        <div class="px-3 py-2 border-b border-gray-100 mb-1 flex items-center gap-2.5">
                            <?= avatar_badge($headerAvatar, (string)($user['name'] ?? 'Author'), 'w-9 h-9 text-xs', '', false) ?>
                            <div class="min-w-0">
                                <b class="block text-gray-900 truncate font-semibold"><?= e($user['name']) ?></b>
                                <small class="block text-xs text-gray-500 truncate">@<?= e($user['handle'] ?? 'author') ?></small>
                            </div>
                        </div>
                        <a href="<?= e(app_url('profile.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">Profile &amp; stories</a>
                        <a href="<?= e(app_url('drafts.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">My Drafts</a>
                        <a href="<?= e(app_url('write.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">New story</a>
                        <a href="<?= e(app_url('profile-edit.php')) ?>" class="block px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">Edit profile</a>
                        <?php if (is_admin()): ?>
                            <a href="<?= e(app_url('admin/')) ?>" class="block px-3 py-2 rounded-xl text-orange-600 font-medium hover:bg-orange-50 transition">Admin dashboard</a>
                        <?php endif; ?>
                        <a href="<?= e(app_url('api/auth/logout.php')) ?>" class="block px-3 py-2 rounded-xl text-red-500 hover:bg-red-50 mt-1 border-t border-gray-100 transition font-medium">Sign out</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Auth buttons for guests -->
                <a class="text-xs sm:text-sm font-medium text-gray-700 hover:text-[#ff6b13] px-2.5 py-1.5 transition" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold px-4 py-2 shadow-sm hover:shadow-md hover:brightness-105 transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started</a>
            <?php endif; ?>

            <!-- Navigation Menu Toggle Button (Visible on both Web View and Mobile) -->
            <div class="relative">
                <button id="mobile-toggle" class="w-9 h-9 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] flex items-center justify-center transition shadow-sm cursor-pointer focus:outline-none" data-mobile-toggle aria-label="Open Navigation Menu" aria-expanded="false" type="button">
                    <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Navigation Dropdown Menu -->
                <div id="mobile-menu" class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-100 text-gray-700 rounded-2xl shadow-xl p-2 z-50 text-sm flex-col gap-1" data-mobile-nav>
                    <a href="<?= e(app_url()) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="<?= e(app_url('about.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>About</span>
                    </a>
                    <a href="<?= e(app_url('explore.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                        <span>Explore stories</span>
                    </a>
                    <a href="<?= e(app_url('faq.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>FAQ</span>
                    </a>
                    <a href="<?= e(app_url('contact.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Contact</span>
                    </a>

                    <?php if ($user): ?>
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="<?= e(app_url('drafts.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>My Drafts</span>
                            </a>
                            <a href="<?= e(app_url('write.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Write a story</span>
                            </a>
                            <a href="<?= e(app_url('profile.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Profile &amp; stories</span>
                            </a>
                            <a href="<?= e(app_url('profile-edit.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit profile</span>
                            </a>
                            <?php if (is_admin()): ?>
                                <a href="<?= e(app_url('admin/')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-orange-600 hover:bg-orange-50 transition font-medium">
                                    <svg class="w-4 h-4 text-[#ff6b13]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Admin dashboard</span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= e(app_url('api/auth/logout.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-500 hover:bg-red-50 mt-1 border-t border-gray-100 transition font-medium">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Sign out</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="<?= e(app_url('auth.php')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition font-medium">
                                <span>Sign in</span>
                            </a>
                            <a href="<?= e(app_url('auth.php?mode=register')) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[#ff6b13] font-semibold hover:bg-orange-50 transition">
                                <span>Get started</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="grow">

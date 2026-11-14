<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

http_response_code(503);
$siteTitle = (string)get_setting('site_title', 'BlogForge');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance — <?= e($siteTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-[#fbfaf8] text-gray-900 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full text-center bg-white border border-[#e9e5df] rounded-3xl p-8 sm:p-10 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#ff6b13] flex items-center justify-center mx-auto mb-6">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        </div>
        <span class="text-xs font-bold uppercase tracking-widest text-[#ff6b13] block mb-2">Scheduled Maintenance</span>
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-3"><?= e($siteTitle) ?> is taking a quick break</h1>
        <p class="text-sm text-gray-600 leading-relaxed mb-6">We are currently performing routine upgrades and system maintenance to improve your publishing experience. We'll be back online in just a few minutes.</p>
        <div class="pt-4 border-t border-gray-100 flex items-center justify-center gap-2 text-xs text-gray-400">
            <span>&copy; <?= date('Y') ?> <?= e($siteTitle) ?></span>
            <span>&bull;</span>
            <a href="mailto:support@blogforge.live" class="hover:text-gray-600 transition">Contact Support</a>
        </div>
    </div>
</body>
</html>


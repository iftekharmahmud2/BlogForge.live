<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'BlogForge — Ideas into impact.';
$isHome = true;
$user = current_user();

require __DIR__ . '/partials/header.php';
?>

<section class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center bg-neutral-950 text-white overflow-hidden w-full px-4 sm:px-8 lg:px-16 py-12 lg:py-20">
    <!-- Subtle ambient glow matching the image backdrop -->
    <div class="absolute -top-36 -right-36 w-[34rem] h-[34rem] bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-36 -left-36 w-[30rem] h-[30rem] bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 items-center gap-10 lg:gap-14 relative z-10">
        <!-- Hero Text & CTAs (Left) -->
        <div class="lg:col-span-7 flex flex-col items-start text-left">
            <span class="inline-block uppercase tracking-widest text-xs font-bold text-orange-400 mb-4">BlogForge.live</span>
            <h1 class="text-5xl sm:text-7xl lg:text-8xl font-black tracking-tight leading-[1.08] mb-6 text-white">
                Ideas<br>into <span class="text-[#ff7b22]">Impact.</span>
            </h1>
            <p class="text-base sm:text-xl text-neutral-400 max-w-xl leading-relaxed mb-10 font-normal">
                A home for curious minds, bold ideas, and meaningful stories. Read, write, and be part of a growing community of thinkers.
            </p>

            <div class="flex flex-wrap items-center gap-3.5">
                <?php if ($user): ?>
                    <a class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-xl shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('explore.php')) ?>">Start reading →</a>
                    <a class="inline-flex items-center justify-center bg-neutral-900 hover:bg-neutral-800 text-neutral-200 border border-neutral-800 font-medium text-sm sm:text-base px-6 py-3.5 rounded-xl transition" href="<?= e(app_url('write.php')) ?>">Write a story</a>
                    <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium text-sm sm:text-base px-4 py-3.5 transition" href="<?= e(app_url('profile.php')) ?>">My profile</a>
                <?php else: ?>
                    <a class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold text-sm sm:text-base px-7 py-3.5 rounded-xl shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started →</a>
                    <a class="inline-flex items-center justify-center bg-neutral-900 hover:bg-neutral-800 text-neutral-200 border border-neutral-800 font-medium text-sm sm:text-base px-6 py-3.5 rounded-xl transition" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                    <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium text-sm sm:text-base px-4 py-3.5 transition" href="<?= e(app_url('explore.php')) ?>">Browse stories</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3D Logo Emblem (Right) -->
        <div class="lg:col-span-5 flex items-center justify-center lg:justify-end">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge emblem" class="w-64 sm:w-80 lg:w-[26rem] max-w-full object-contain drop-shadow-[0_30px_45px_rgba(0,0,0,0.7)] select-none">
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

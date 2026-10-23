<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'BlogForge — Ideas into impact.';
$isHome = true;
$user = current_user();

require __DIR__ . '/partials/header.php';
?>

<section class="py-10 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-neutral-950 text-white shadow-2xl border border-neutral-800">
            <!-- Subtle background accent glow -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 items-center relative z-10">
                <div class="lg:col-span-7 p-8 sm:p-12 lg:p-16">
                    <span class="inline-block uppercase tracking-widest text-xs font-bold text-orange-400 mb-3">BlogForge.live</span>
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6 text-white">
                        Ideas<br>into <em class="not-italic text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-amber-500">Impact.</em>
                    </h1>
                    <p class="text-base sm:text-lg text-neutral-400 max-w-xl leading-relaxed mb-8">
                        A home for curious minds, bold ideas, and meaningful stories. Read, write, and be part of a growing community of thinkers.
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <?php if ($user): ?>
                            <a class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-600 text-white font-medium px-6 py-3 rounded-xl shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('explore.php')) ?>">Start reading →</a>
                            <a class="inline-flex items-center justify-center bg-neutral-800 hover:bg-neutral-700 text-neutral-200 border border-neutral-700 font-medium px-6 py-3 rounded-xl transition" href="<?= e(app_url('write.php')) ?>">Write a story</a>
                            <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium px-4 py-3 transition" href="<?= e(app_url('profile.php')) ?>">My profile</a>
                        <?php else: ?>
                            <a class="inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-600 text-white font-medium px-6 py-3 rounded-xl shadow-lg hover:brightness-110 hover:scale-[1.02] transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started →</a>
                            <a class="inline-flex items-center justify-center bg-neutral-800 hover:bg-neutral-700 text-neutral-200 border border-neutral-700 font-medium px-6 py-3 rounded-xl transition" href="<?= e(app_url('auth.php')) ?>">Sign in</a>
                            <a class="inline-flex items-center justify-center text-neutral-400 hover:text-white font-medium px-4 py-3 transition" href="<?= e(app_url('explore.php')) ?>">Browse stories</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-5 p-8 lg:p-12 flex items-center justify-center">
                    <img src="/assets/img/blogforge-icon.png" alt="BlogForge emblem" class="w-48 sm:w-64 lg:w-80 object-contain drop-shadow-[0_25px_35px_rgba(0,0,0,0.5)]">
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

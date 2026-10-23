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

<section class="min-h-[calc(100vh-140px)] flex items-center justify-center px-4 py-12 bg-radial from-orange-50/50 via-[#fffdfa] to-[#fffdfa]">
    <div class="w-full <?= $register ? 'max-w-lg' : 'max-w-md' ?> bg-white border border-[#e9e5df] rounded-3xl p-8 sm:p-10 shadow-xl transition-all">
        <div class="text-center mb-6">
            <img src="/assets/img/blogforge-icon.png" alt="BlogForge" class="w-14 h-14 mx-auto mb-3 object-contain">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight"><?= $register ? 'Join BlogForge' : 'Welcome back' ?></h1>
            <p class="text-sm text-gray-500 mt-1.5"><?= $register ? 'Create your writer account to publish stories.' : 'Sign in to continue reading, reacting, and writing.' ?></p>
        </div>

        <div class="hidden bg-red-50 text-red-700 border border-red-200 text-sm rounded-2xl p-3.5 mb-5 font-medium" data-auth-error role="alert"></div>

        <form class="space-y-4" data-auth-form data-mode="<?= $register ? 'register' : 'login' ?>" data-next-url="<?= e($safeNext) ?>" novalidate>
            <?php if ($register): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-semibold text-gray-700 mb-1.5">First name</label>
                        <input class="w-full bg-white border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="first_name" name="first_name" required maxlength="40" autocomplete="given-name" placeholder="Alex">
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-semibold text-gray-700 mb-1.5">Last name</label>
                        <input class="w-full bg-white border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="last_name" name="last_name" required maxlength="40" autocomplete="family-name" placeholder="Rivers">
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">Email address</label>
                <input class="w-full bg-white border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="email" type="email" name="email" required autocomplete="email" placeholder="alex@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">Password</label>
                <div class="relative">
                    <input class="w-full bg-white border border-[#e9e5df] rounded-xl pl-4 pr-16 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="password" type="password" name="password" minlength="8" required autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" placeholder="Minimum 8 characters">
                    <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 hover:text-[#ff6b13] px-2 py-1 rounded transition cursor-pointer" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <?php if ($register): ?>
                <div>
                    <label for="confirm_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm password</label>
                    <div class="relative">
                        <input class="w-full bg-white border border-[#e9e5df] rounded-xl pl-4 pr-16 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Re-enter your password">
                        <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 hover:text-[#ff6b13] px-2 py-1 rounded transition cursor-pointer" type="button" data-pw-toggle aria-label="Show password">Show</button>
                    </div>
                </div>

                <label class="flex items-start gap-2.5 text-xs text-gray-600 cursor-pointer pt-1">
                    <input type="checkbox" name="terms" class="w-4 h-4 rounded text-[#ff6b13] focus:ring-[#ff6b13] mt-0.5" required>
                    <span>I agree to the <a href="<?= e(app_url('terms.php#terms')) ?>" target="_blank" rel="noopener" class="text-[#ff6b13] font-semibold hover:underline">Terms of Service</a> and <a href="<?= e(app_url('terms.php#privacy')) ?>" target="_blank" rel="noopener" class="text-[#ff6b13] font-semibold hover:underline">Privacy Policy</a>.</span>
                </label>
            <?php endif; ?>

            <button class="w-full mt-2 inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm py-3 shadow-md hover:shadow-orange-500/20 hover:brightness-105 transition cursor-pointer" type="submit"><?= $register ? 'Create account' : 'Sign in' ?></button>
        </form>

        <div class="text-center text-xs text-gray-500 mt-6 pt-5 border-t border-gray-100">
            <?= $register ? 'Already have an account?' : 'New to BlogForge?' ?>
            <a href="<?= e(app_url($register ? 'auth.php?next=' . urlencode($safeNext) : 'auth.php?mode=register&next=' . urlencode($safeNext))) ?>" class="text-[#ff6b13] font-semibold hover:underline ml-1">
                <?= $register ? 'Sign in' : 'Create one' ?>
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

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

<section class="min-h-[calc(100vh-140px)] flex items-center justify-center px-4 py-12 bg-gradient-to-b from-orange-50/40 via-[#fffdfa] to-[#fffdfa]">
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

                <div>
                    <label for="handle" class="block text-xs font-semibold text-gray-700 mb-1.5">Username</label>
                    <div data-input-wrapper class="flex items-center border border-[#e9e5df] rounded-xl bg-white focus-within:border-[#ff9a3c] focus-within:ring-2 focus-within:ring-orange-100 transition shadow-sm overflow-hidden">
                        <span class="pl-3.5 pr-1 text-gray-400 text-sm font-medium select-none">@</span>
                        <input class="w-full bg-transparent border-0 outline-none px-2 py-2.5 text-sm" id="handle" name="handle" required minlength="3" maxlength="30" pattern="^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$" autocomplete="username" placeholder="alexrivers" autocapitalize="none" spellcheck="false">
                    </div>
                    <div class="flex items-center justify-between text-[11px] mt-1 gap-2">
                        <small class="text-gray-400">Letters, numbers, dashes, and underscores. 3–30 chars.</small>
                        <span id="handle-feedback" class="hidden font-medium shrink-0"></span>
                    </div>
                </div>

                <div>
                    <label for="dob" class="block text-xs font-semibold text-gray-700 mb-1.5">Date of birth</label>
                    <div data-input-wrapper data-dob-wrapper class="relative flex items-center border border-[#e9e5df] rounded-xl bg-white focus-within:border-[#ff9a3c] focus-within:ring-2 focus-within:ring-orange-100 transition shadow-sm">
                        <input class="w-full bg-transparent border-0 outline-none px-4 py-2.5 text-sm" id="dob" name="dob" required placeholder="DD/MM/YY" maxlength="10" autocomplete="bday" inputmode="numeric" data-dob-input>
                        <button type="button" data-dob-toggle class="pr-3 pl-2 text-gray-400 hover:text-[#ff6b13] transition focus:outline-none cursor-pointer" title="Select date" aria-label="Open date picker">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </button>
                        <input type="date" data-dob-native tabindex="-1" class="sr-only" max="<?= date('Y-m-d') ?>" min="1920-01-01">

                        <!-- Polished Birthday Dropdown Picker Popover -->
                        <div data-dob-popover class="hidden absolute right-0 top-full mt-2 w-72 bg-white border border-[#e9e5df] rounded-2xl shadow-xl p-4 z-50 text-xs">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-gray-100">
                                <span class="font-bold text-gray-800 text-xs">Select Date of Birth</span>
                                <button type="button" data-dob-close class="text-gray-400 hover:text-gray-700 text-sm font-bold p-1 cursor-pointer">✕</button>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Day</label>
                                    <select data-dob-day class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2 py-1.5 text-xs text-gray-800 outline-none focus:border-[#ff9a3c]">
                                        <option value="">DD</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Month</label>
                                    <select data-dob-month class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2 py-1.5 text-xs text-gray-800 outline-none focus:border-[#ff9a3c]">
                                        <option value="">MM</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Year</label>
                                    <select data-dob-year class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-lg px-2 py-1.5 text-xs text-gray-800 outline-none focus:border-[#ff9a3c]">
                                        <option value="">YY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <button type="button" data-dob-calendar-btn class="text-[11px] text-[#ff6b13] hover:underline font-medium cursor-pointer">Calendar view</button>
                                <button type="button" data-dob-apply class="px-3 py-1.5 rounded-lg bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-xs shadow-sm hover:brightness-105 cursor-pointer">Set date</button>
                            </div>
                        </div>
                    </div>
                    <small class="block text-[11px] text-gray-400 mt-1">Format: DD/MM/YY (type directly or click calendar to select)</small>
                </div>
            <?php endif; ?>

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5"><?= $register ? 'Email address' : 'Email address or username' ?></label>
                <input class="w-full bg-white border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition shadow-sm" id="email" type="<?= $register ? 'email' : 'text' ?>" name="email" required autocomplete="<?= $register ? 'email' : 'username' ?>" placeholder="<?= $register ? 'alex@example.com' : 'alex@example.com or @alexrivers' ?>">
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
                    <span>I agree to the <a href="<?= e(app_url('terms.php')) ?>" target="_blank" rel="noopener" class="text-[#ff6b13] font-semibold hover:underline">Terms &amp; Conditions</a>, <a href="<?= e(app_url('privacy.php')) ?>" target="_blank" rel="noopener" class="text-[#ff6b13] font-semibold hover:underline">Privacy Policy</a>, and <a href="<?= e(app_url('cookies.php')) ?>" target="_blank" rel="noopener" class="text-[#ff6b13] font-semibold hover:underline">Cookie Policy</a>.</span>
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

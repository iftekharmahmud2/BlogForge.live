<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$sessionUser = current_user();
$u = BlogForge\Auth::fetchUser((string)$sessionUser['id']);

if (!$u) {
    redirect(app_url('api/auth/logout.php'));
}

$pageTitle = 'Edit profile — BlogForge';
$displayName = trim((string)($u['name'] ?? ''));

require __DIR__ . '/partials/header.php';
?>

<section class="py-10 sm:py-14 bg-[#fbfaf8] min-h-[calc(100vh-140px)]">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between gap-4 mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Edit profile</h1>
            <a class="inline-flex items-center text-xs font-semibold text-gray-700 bg-white border border-[#e9e5df] rounded-full px-4 py-2 hover:border-gray-400 transition" href="<?= e(app_url('profile.php')) ?>">← Back to profile</a>
        </div>

        <!-- Profile details -->
        <form class="bg-white border border-[#e9e5df] rounded-3xl p-6 sm:p-8 shadow-sm mb-8 space-y-5" data-profile-form novalidate>
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-base shrink-0 overflow-hidden shadow-sm" data-avatar-preview>
                    <?php if (!empty($u['avatar'])): ?>
                        <img src="<?= e((string)$u['avatar']) ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= e(initials($displayName)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Your details</h2>
                    <p class="text-xs text-gray-500">This is what readers see on your stories and responses.</p>
                </div>
            </div>

            <div class="hidden bg-red-50 text-red-700 border border-red-200 text-sm rounded-xl p-3 font-medium" data-profile-error role="alert"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-semibold text-gray-700 mb-1.5">First name</label>
                    <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="first_name" name="first_name" required maxlength="40" autocomplete="given-name" value="<?= e((string)($u['first_name'] ?? '')) ?>">
                </div>
                <div>
                    <label for="last_name" class="block text-xs font-semibold text-gray-700 mb-1.5">Last name</label>
                    <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="last_name" name="last_name" required maxlength="40" autocomplete="family-name" value="<?= e((string)($u['last_name'] ?? '')) ?>">
                </div>
            </div>

            <div>
                <label for="handle" class="block text-xs font-semibold text-gray-700 mb-1.5">Username</label>
                <div class="flex items-center border border-[#e9e5df] rounded-xl bg-[#fbfaf8] focus-within:border-[#ff9a3c] focus-within:ring-2 focus-within:ring-orange-100 transition overflow-hidden">
                    <span class="pl-3.5 pr-1 text-gray-400 text-sm font-medium">@</span>
                    <input class="w-full bg-transparent border-0 outline-none px-2 py-2.5 text-sm" id="handle" name="handle" required minlength="3" maxlength="30" pattern="^[a-zA-Z0-9_\.]+$" autocomplete="username" value="<?= e((string)($u['handle'] ?? '')) ?>">
                </div>
                <small class="block text-[11px] text-gray-400 mt-1">Letters, numbers, underscores and dots. 3–30 chars.</small>
            </div>

            <div>
                <label for="bio" class="block text-xs font-semibold text-gray-700 mb-1.5">Bio <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition resize-y min-h-[90px]" id="bio" name="bio" maxlength="280" placeholder="A sentence or two about what you write about…"><?= e((string)($u['bio'] ?? '')) ?></textarea>
                <small class="block text-[11px] text-gray-400 mt-1">Short bio shown on your stories and public profile. Max 280 characters.</small>
            </div>

            <div>
                <label for="avatar" class="block text-xs font-semibold text-gray-700 mb-1.5">Avatar image URL <span class="text-gray-400 font-normal">(optional)</span></label>
                <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="avatar" name="avatar" type="url" placeholder="https://images.unsplash.com/…" value="<?= e((string)($u['avatar'] ?? '')) ?>">
                <small class="block text-[11px] text-gray-400 mt-1">Direct link to an image (HTTPS). Leave blank to use your name initials.</small>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">Email address</label>
                <input class="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed" id="email" type="email" value="<?= e((string)($u['email'] ?? '')) ?>" disabled>
                <small class="block text-[11px] text-gray-400 mt-1">Email address cannot be changed right now.</small>
            </div>

            <div class="flex justify-end pt-3">
                <button class="rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-2.5 text-sm font-semibold shadow-sm hover:shadow transition cursor-pointer" type="submit">Save changes</button>
            </div>
        </form>

        <!-- Change password -->
        <form class="bg-white border border-[#e9e5df] rounded-3xl p-6 sm:p-8 shadow-sm space-y-5" data-password-form novalidate>
            <div class="pb-3 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Change password</h2>
                <p class="text-xs text-gray-500">Keep your account secure with a strong password.</p>
            </div>

            <div class="hidden bg-red-50 text-red-700 border border-red-200 text-sm rounded-xl p-3 font-medium" data-password-error role="alert"></div>

            <div>
                <label for="current_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Current password</label>
                <div class="relative">
                    <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl pl-4 pr-16 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="••••••••">
                    <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 hover:text-[#ff6b13] px-2 py-1 rounded transition cursor-pointer" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <div>
                <label for="new_password" class="block text-xs font-semibold text-gray-700 mb-1.5">New password</label>
                <div class="relative">
                    <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl pl-4 pr-16 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="new_password" name="new_password" type="password" minlength="8" required autocomplete="new-password" placeholder="Minimum 8 characters">
                    <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 hover:text-[#ff6b13] px-2 py-1 rounded transition cursor-pointer" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <div>
                <label for="confirm_new_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm new password</label>
                <div class="relative">
                    <input class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl pl-4 pr-16 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" id="confirm_new_password" name="confirm_new_password" type="password" minlength="8" required autocomplete="new-password" placeholder="Re-enter your new password">
                    <button class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 hover:text-[#ff6b13] px-2 py-1 rounded transition cursor-pointer" type="button" data-pw-toggle aria-label="Show password">Show</button>
                </div>
            </div>

            <div class="flex justify-end pt-3">
                <button class="rounded-full bg-gray-900 text-white px-6 py-2.5 text-sm font-semibold hover:bg-black transition cursor-pointer" type="submit">Update password</button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

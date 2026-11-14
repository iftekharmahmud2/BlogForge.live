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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-lg shrink-0 overflow-hidden shadow-sm border-2 border-white ring-2 ring-orange-100" data-avatar-preview data-initials="<?= e(initials($displayName)) ?>">
                        <?= avatar_inner((string)($u['avatar'] ?? ''), $displayName, false) ?>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Your profile photo</h2>
                        <p class="text-xs text-gray-500">Upload a photo under 2MB or provide an image link.</p>
                        <span data-avatar-status class="text-xs font-semibold text-[#ff6b13] hidden mt-0.5 block"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="file" id="avatar_file" data-avatar-file accept="image/png,image/jpeg,image/webp,image/gif" class="sr-only">
                    <button type="button" data-avatar-upload-btn class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-xs font-semibold shadow-sm hover:brightness-105 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Upload photo</span>
                    </button>
                    <button type="button" data-avatar-remove-btn class="inline-flex items-center px-3.5 py-2 rounded-full border border-gray-200 text-gray-600 hover:text-red-600 hover:border-red-200 text-xs font-medium transition cursor-pointer <?= empty($u['avatar']) ? 'hidden' : '' ?>">
                        Remove
                    </button>
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
                    <input class="w-full bg-transparent border-0 outline-none px-2 py-2.5 text-sm" id="handle" name="handle" required minlength="3" maxlength="30" pattern="^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$" autocomplete="username" value="<?= e(ltrim((string)($u['handle'] ?? ''), '@')) ?>">
                </div>
                <small class="block text-[11px] text-gray-400 mt-1">Letters, numbers, dashes, and underscores. 3–30 chars.</small>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="bio" class="block text-xs font-semibold text-gray-700">Bio <span class="text-gray-400 font-normal">(optional)</span></label>
                    <span class="text-[11px] text-gray-400"><span data-bio-count>0</span>/280</span>
                </div>
                <textarea class="w-full bg-[#fbfaf8] border border-[#e9e5df] rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition resize-y min-h-[90px]" id="bio" name="bio" data-bio maxlength="280" placeholder="A sentence or two about what you write about…"><?= e((string)($u['bio'] ?? '')) ?></textarea>
                <small class="block text-[11px] text-gray-400 mt-1">Short bio shown on your stories and public profile.</small>
            </div>

            <div>
                <label for="avatar" class="block text-xs font-semibold text-gray-700 mb-1.5">Avatar image <span class="text-gray-400 font-normal">(upload above or paste URL)</span></label>
                <div class="relative flex items-center border border-[#e9e5df] rounded-xl bg-[#fbfaf8] focus-within:border-[#ff9a3c] focus-within:ring-2 focus-within:ring-orange-100 transition shadow-sm overflow-hidden">
                    <input class="w-full bg-transparent border-0 outline-none px-4 py-2.5 text-sm" id="avatar" name="avatar" placeholder="https://images.unsplash.com/… or uploaded path" value="<?= e((string)($u['avatar'] ?? '')) ?>" data-avatar-input>
                    <button type="button" data-avatar-browse-btn class="px-3 py-1.5 mr-2 rounded-lg bg-orange-50 hover:bg-orange-100 text-[#ff6b13] text-xs font-semibold transition cursor-pointer shrink-0">
                        Choose file…
                    </button>
                </div>
                <small class="block text-[11px] text-gray-400 mt-1">Upload directly (under 2MB: JPG, PNG, WebP, GIF) or paste an image URL. Leave blank for initials.</small>
            </div>

            <div>
                <label for="dob" class="block text-xs font-semibold text-gray-700 mb-1.5">Date of birth <span class="text-gray-400 font-normal">(optional)</span></label>
                <div data-input-wrapper data-dob-wrapper class="relative flex items-center border border-[#e9e5df] rounded-xl bg-[#fbfaf8] focus-within:border-[#ff9a3c] focus-within:ring-2 focus-within:ring-orange-100 transition shadow-sm">
                    <input class="w-full bg-transparent border-0 outline-none pl-4 pr-11 py-2.5 text-sm" id="dob" name="dob" placeholder="DD/MM/YYYY" maxlength="10" autocomplete="bday" inputmode="numeric" value="<?= e((string)($u['dob'] ?? '')) ?>" data-dob-input>
                    <button type="button" data-dob-cal-btn data-dob-cal-wrapper class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center cursor-pointer text-gray-400 hover:text-[#ff6b13] transition focus:outline-none group/cal z-10" title="Choose date from calendar" aria-label="Open date calendar">
                        <svg class="w-5 h-5 text-gray-400 group-hover/cal:text-[#ff6b13] pointer-events-none transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </button>
                    <input type="date" data-dob-native class="absolute right-0 top-0 w-11 h-full opacity-0 pointer-events-none" tabindex="-1" aria-hidden="true" max="<?= date('Y-m-d') ?>" min="1920-01-01">
                </div>
                <small class="block text-[11px] text-gray-400 mt-1">Format: DD/MM/YYYY · Private &amp; never shown publicly on your profile.</small>
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

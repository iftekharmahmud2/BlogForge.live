<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Terms & Privacy — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">Legal</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Terms &amp; Privacy</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">The short, plain-language version of how BlogForge.live works.</p>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-8 text-gray-700 leading-relaxed">
        <div>
            <h2 id="terms" class="text-2xl font-bold text-gray-900 tracking-tight mb-3">Terms of Service</h2>
            <p class="mb-4">By creating an account on BlogForge.live you agree to use the service responsibly and lawfully.</p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><b>Your content.</b> You own the stories and responses you publish. You give BlogForge permission to display them on the site.</li>
                <li><b>Your conduct.</b> Don’t post anything unlawful, hateful, harassing, misleading, or that infringes someone else’s rights.</li>
                <li><b>Your account.</b> Keep your password private. You are responsible for activity on your account.</li>
                <li><b>Moderation.</b> We may remove content or suspend accounts that break these terms.</li>
                <li><b>Changes.</b> We may update these terms; continued use means you accept the updated version.</li>
            </ul>
        </div>

        <div>
            <h2 id="privacy" class="text-2xl font-bold text-gray-900 tracking-tight mb-3">Privacy Policy</h2>
            <p class="mb-4">We collect only what we need to run your account.</p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><b>What we store.</b> Your name, email address, a securely hashed password, your optional bio and photo link, and the stories and responses you publish.</li>
                <li><b>How we use it.</b> To sign you in, show your public author profile, and operate the site. We never store your password in plain text.</li>
                <li><b>Sharing.</b> We don’t sell your personal data. Your name, username, bio and stories are public; your email address is not.</li>
                <li><b>Your choices.</b> You can edit your profile at any time from <i>Edit profile</i>, and delete your stories whenever you like.</li>
            </ul>
        </div>

        <p class="text-xs text-gray-400">Questions about these terms? Reach out through the BlogForge.live site owner.</p>

        <div class="pt-4">
            <?php if (is_logged_in()): ?>
                <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-3 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('write.php')) ?>">Start Writing</a>
            <?php else: ?>
                <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-3 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Create an account</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

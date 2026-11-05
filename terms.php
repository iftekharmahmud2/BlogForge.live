<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Terms & Conditions — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">Legal</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Terms &amp; Conditions</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">Please read these Terms and Conditions carefully before reading, writing, or publishing on BlogForge.live.</p>
        <span class="inline-block text-xs font-medium text-gray-400 mt-2">Last updated: November 2026</span>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-10 text-gray-700 leading-relaxed">
        
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">1. Acceptance of Terms</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                By accessing, browsing, creating an account on, or contributing content to <strong>BlogForge.live</strong> (referred to as "BlogForge", "we", "us", or "our"), you agree to be bound by these Terms &amp; Conditions and our <a href="<?= e(app_url('privacy.php')) ?>" class="text-[#ff6b13] font-semibold hover:underline">Privacy Policy</a> and <a href="<?= e(app_url('cookies.php')) ?>" class="text-[#ff6b13] font-semibold hover:underline">Cookie Policy</a>. If you do not agree with any part of these terms, you must not use our platform.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">2. Author Accounts &amp; Security</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                To publish stories, submit comments, or react to articles, you must register for an author account. When creating an account:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li>You must provide accurate, current, and complete information, including a valid email address.</li>
                <li>You are solely responsible for maintaining the confidentiality of your account credentials, password, and session access.</li>
                <li>You agree to immediately notify us if you suspect any unauthorized access to or breach of your account.</li>
                <li>Accounts registered by automated methods, bots, or fraudulent scripts are strictly prohibited and will be terminated immediately.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">3. Content Ownership &amp; Intellectual Property</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                At BlogForge, we believe creators should maintain sovereignty over their work:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><strong>You retain full ownership:</strong> You own all intellectual property rights to the stories, writing, excerpts, responses, and imagery you post to BlogForge.</li>
                <li><strong>License to BlogForge:</strong> By publishing content on BlogForge.live, you grant us a worldwide, non-exclusive, royalty-free license to host, store, cache, display, format, distribute, and promote your content across the site solely for operating and promoting the platform.</li>
                <li><strong>Warranty of Originality:</strong> You represent and warrant that your content is original to you or that you have obtained all necessary licenses, permissions, and rights to publish it, without infringing any third-party rights.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">4. Community Guidelines &amp; Prohibited Conduct</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                BlogForge is committed to thoughtful, constructive discourse. You agree that you will not post content or engage in activities that:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li>Contain hate speech, harassment, defamation, threats, extortion, or violence directed at individuals or groups.</li>
                <li>Infringe upon copyrights, trademarks, patents, trade secrets, or privacy rights of any person or organization.</li>
                <li>Distribute spam, mass automated solicitations, malware, phishing links, or unauthorized advertising.</li>
                <li>Attempt to reverse engineer, disrupt, tamper with, or compromise the platform's security, database, or infrastructure.</li>
                <li>Impersonate any person, entity, author, or BlogForge team member.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">5. Moderation &amp; Account Termination</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                We reserve the right, but do not assume the obligation, to monitor, review, unpublish, or permanently delete any content that violates these Terms or harms our community. We may suspend or terminate author accounts without notice for severe or repeated violations.
            </p>
            <p class="text-sm sm:text-base leading-relaxed">
                You may delete your stories or close your account at any time via your author profile.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">6. Disclaimers &amp; Limitation of Liability</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                BlogForge is provided on an "as is" and "as available" basis without warranties of any kind, whether express or implied. The views and opinions expressed in articles published on BlogForge belong solely to their respective authors and do not reflect the opinions of BlogForge.live or its operators.
            </p>
            <p class="text-sm sm:text-base leading-relaxed">
                To the fullest extent permitted by law, BlogForge shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of or inability to use the service.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">7. Modifications to Terms</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                We reserve the right to amend these Terms &amp; Conditions at our discretion. Any revisions will be published on this page with an updated timestamp. Your continued use of BlogForge.live following the publication of changes signifies your agreement to the revised terms.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">8. Contact Us</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                If you have questions or concerns regarding these Terms &amp; Conditions, please contact us at <a href="mailto:admin@blogforge.live" class="text-[#ff6b13] font-semibold hover:underline">admin@blogforge.live</a>.
            </p>
        </div>

        <div class="pt-6 border-t border-[#e9e5df] flex flex-wrap items-center gap-3">
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-3 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Get started on BlogForge</a>
            <a class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('privacy.php')) ?>">Read Privacy Policy →</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

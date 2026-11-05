<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Privacy Policy — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">Privacy</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Privacy Policy</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">We respect your privacy. Here is a clear, transparent explanation of how BlogForge.live collects, uses, and safeguards your data.</p>
        <span class="inline-block text-xs font-medium text-gray-400 mt-2">Last updated: November 2026</span>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-10 text-gray-700 leading-relaxed">
        
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">1. Our Commitment to Privacy</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                At <strong>BlogForge.live</strong> ("BlogForge", "we", "us"), privacy is built into our architecture. We collect only what is strictly necessary to authenticate your account, display your public writing, and deliver a clean, fast editorial experience. <strong>We do not sell, rent, or trade your personal data with advertisers or data brokers.</strong>
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">2. Information We Collect</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                Depending on how you interact with our platform, we collect the following types of information:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><strong>Account Credentials:</strong> First name, last name, unique author handle/username, email address, and a securely salted password hash. <em>We never store your actual password in plain text.</em></li>
                <li><strong>Public Profile Details:</strong> Your optional biographical description (bio) and avatar photo link.</li>
                <li><strong>Published Stories &amp; Drafts:</strong> Titles, excerpts, body content, categories, tags, reading time, published dates, and cover images you create or upload.</li>
                <li><strong>Community Activity:</strong> Responses/comments you post on stories, and story likes/reactions.</li>
                <li><strong>Technical Information:</strong> IP addresses, browser user agent, device screen dimensions, and HTTP headers collected automatically for security logging, error tracking, and DDoS mitigation.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">3. How We Use Your Information</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                We use your data exclusively for the following operational purposes:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li>To authenticate your identity, establish secure web sessions, and keep you signed in.</li>
                <li>To format and attribute your published stories, articles, and comments with your author byline.</li>
                <li>To maintain platform security, prevent brute-force attacks, and detect spam or abusive behavior.</li>
                <li>To calculate aggregate platform engagement statistics (such as reading time, story views, and like counts).</li>
                <li>To communicate critical service updates or legal notices regarding your account.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">4. Information Sharing &amp; Public Visibility</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                <strong>Public vs. Private Information:</strong>
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><strong>Public Information:</strong> Your author name, username/handle, public avatar, bio, published stories, likes, and approved responses are visible to anyone visiting the site.</li>
                <li><strong>Private Information:</strong> Your email address, unpublished draft stories, and account password hashes are strictly private and never exposed to other users or public visitors.</li>
                <li><strong>No Sale of Data:</strong> We will never sell, lease, or monetize your personal information to third parties.</li>
                <li><strong>Legal Disclosures:</strong> We will disclose personal information only if required by lawful court order, subpoena, or applicable law to protect life, safety, or legal rights.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">5. Data Security &amp; Storage</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                We implement robust technical and operational security controls to protect your data. All web traffic is encrypted in transit using industry-standard HTTPS / TLS. User passwords are protected using one-way bcrypt hashing algorithms. Our database infrastructure operates with strict access controls, isolated networks, and parameterized MongoDB queries to protect against NoSQL injection.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">6. Your Rights &amp; Data Control</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                You maintain direct control over your personal data:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm sm:text-base">
                <li><strong>Edit Profile:</strong> You can update your display name, username, bio, and avatar link at any time from your <a href="<?= e(app_url('profile-edit.php')) ?>" class="text-[#ff6b13] font-semibold hover:underline">Edit Profile</a> dashboard.</li>
                <li><strong>Edit &amp; Delete Content:</strong> You have full rights to edit, unpublish, or permanently delete any story or comment you have posted.</li>
                <li><strong>Account Deletion:</strong> If you wish to permanently delete your account and all associated data, you may submit a request to <a href="mailto:admin@blogforge.live" class="text-[#ff6b13] font-semibold hover:underline">admin@blogforge.live</a>.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">7. Cookies &amp; Tracking Technologies</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                BlogForge uses strictly necessary cookies to manage authentication sessions and protect against Cross-Site Request Forgery (CSRF). We do not use third-party tracking pixels or invasive advertising cookies. For full details on how we utilize cookies, please review our dedicated <a href="<?= e(app_url('cookies.php')) ?>" class="text-[#ff6b13] font-semibold hover:underline">Cookie Policy</a>.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">8. Changes to this Privacy Policy</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                We may periodically update this Privacy Policy to reflect technical enhancements or regulatory changes. The "Last updated" date at the top of this document indicates the effective date of the latest revisions.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">9. Contact Us</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                If you have questions, feedback, or requests regarding this Privacy Policy or your personal information, please reach out via email at <a href="mailto:admin@blogforge.live" class="text-[#ff6b13] font-semibold hover:underline">admin@blogforge.live</a>.
            </p>
        </div>

        <div class="pt-6 border-t border-[#e9e5df] flex flex-wrap items-center gap-3">
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-3 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('auth.php?mode=register')) ?>">Join BlogForge</a>
            <a class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('terms.php')) ?>">Read Terms &amp; Conditions →</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

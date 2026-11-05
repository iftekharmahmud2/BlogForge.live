<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Cookie Policy — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="border-b border-[#e9e5df] bg-[#fbfaf8] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="uppercase tracking-widest text-xs font-bold text-[#ff6b13] block mb-2">Transparency</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-3">Cookie Policy</h1>
        <p class="text-gray-500 text-base sm:text-lg max-w-2xl leading-relaxed">Everything you need to know about how and why BlogForge.live uses cookies to keep your account safe and your reading seamless.</p>
        <span class="inline-block text-xs font-medium text-gray-400 mt-2">Last updated: November 2026</span>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-10 text-gray-700 leading-relaxed">
        
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">1. What Are Cookies?</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                Cookies are small text files placed on your computer or mobile device when you visit a website. They are widely used to make websites work efficiently, provide security, and remember your authenticated session across multiple pages.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">2. How BlogForge Uses Cookies</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-4">
                At <strong>BlogForge.live</strong>, we use cookies with a strict principle of data minimization: <strong>we only use cookies essential to operating the platform and securing your account. We do not use third-party advertising cookies or cross-site tracking pixels.</strong>
            </p>
            
            <div class="space-y-4">
                <div class="p-4 sm:p-5 rounded-2xl border border-[#e9e5df] bg-white shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-1.5 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Strictly Necessary / Essential Cookies
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-2">
                        These cookies are mandatory for the website to function. They handle authentication and platform security. Without these cookies, you cannot log in, post articles, leave comments, or use author tools.
                    </p>
                    <ul class="list-disc pl-5 text-xs sm:text-sm text-gray-600 space-y-1">
                        <li><strong>blogforge_session:</strong> Secure PHP session identifier that maintains your logged-in session. It is flagged with <code>HttpOnly</code>, <code>SameSite=Lax</code>, and <code>Secure</code> attributes to prevent unauthorized script access.</li>
                        <li><strong>CSRF Security Token:</strong> A cryptographically secure random token generated per session to protect your account against Cross-Site Request Forgery attacks when submitting forms.</li>
                    </ul>
                </div>

                <div class="p-4 sm:p-5 rounded-2xl border border-[#e9e5df] bg-white shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-1.5 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        Functional &amp; Preference Cookies
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        These cookies remember non-essential preferences, such as your temporary reading list bookmarks or interactive menu states, ensuring your experience remains consistent throughout your browsing session.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">3. Summary Table of Cookies Used</h2>
            <div class="w-full overflow-x-auto border border-[#e9e5df] rounded-2xl bg-white shadow-sm">
                <table class="w-full min-w-[500px] text-left text-xs sm:text-sm divide-y divide-[#e9e5df]">
                    <thead class="bg-[#fbfaf8] text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="py-3 px-4">Cookie Name</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Duration</th>
                            <th class="py-3 px-4">Purpose</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4 font-mono font-medium text-gray-900">blogforge_session</td>
                            <td class="py-3 px-4 text-emerald-700 font-medium">Essential</td>
                            <td class="py-3 px-4 text-gray-600">Session</td>
                            <td class="py-3 px-4 text-gray-600">Author authentication &amp; secure session state</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-mono font-medium text-gray-900">_csrf / token</td>
                            <td class="py-3 px-4 text-emerald-700 font-medium">Security</td>
                            <td class="py-3 px-4 text-gray-600">Session</td>
                            <td class="py-3 px-4 text-gray-600">Protection against Cross-Site Request Forgery</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">4. Third-Party Tracking &amp; Advertising</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                <strong>BlogForge does NOT deploy third-party advertising cookies, retargeting pixels, or behavioral marketing trackers.</strong> We respect your attention and do not sell your activity data to ad exchanges.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">5. How to Manage or Disable Cookies</h2>
            <p class="text-sm sm:text-base leading-relaxed mb-3">
                Most modern browsers allow you to control and block cookies through their settings. You can configure your browser to reject all cookies, accept only first-party cookies, or alert you whenever a cookie is set.
            </p>
            <p class="text-xs sm:text-sm text-amber-700 bg-amber-50 p-4 rounded-xl border border-amber-200 leading-relaxed mb-3">
                <strong>Please note:</strong> Because BlogForge relies on essential session cookies for authentication, blocking or deleting all cookies will prevent you from signing in, publishing stories, and managing your author profile.
            </p>
            <p class="text-xs text-gray-500">
                To learn how to manage cookies in your browser, visit the official support pages for Google Chrome, Mozilla Firefox, Apple Safari, or Microsoft Edge.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">6. Updates to this Cookie Policy</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                We may revise this Cookie Policy when updating platform features or compliance obligations. Any modifications will be posted here with an updated revision date.
            </p>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-3">7. Inquiries</h2>
            <p class="text-sm sm:text-base leading-relaxed">
                If you have questions regarding our use of cookies or privacy practices, feel free to contact us at <a href="mailto:admin@blogforge.live" class="text-[#ff6b13] font-semibold hover:underline">admin@blogforge.live</a>.
            </p>
        </div>

        <div class="pt-6 border-t border-[#e9e5df] flex flex-wrap items-center gap-3">
            <a class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white px-6 py-3 text-sm font-semibold shadow-sm hover:shadow transition" href="<?= e(app_url('privacy.php')) ?>">Read Privacy Policy</a>
            <a class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:border-gray-400 transition" href="<?= e(app_url('terms.php')) ?>">Read Terms &amp; Conditions →</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

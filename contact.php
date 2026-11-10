<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Contact Us — BlogForge';
$messageSent = false;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    check_rate_limit('contact_form', 5, 300);

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name === '' || $email === '' || $message === '') {
        $errorMessage = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please provide a valid email address.';
    } elseif (mb_strlen($message) < 10) {
        $errorMessage = 'Message must be at least 10 characters long.';
    } else {
        // Record in database if available or log safely
        $database = db();
        if ($database) {
            try {
                $database->contact_messages->insertOne([
                    'name' => mb_substr($name, 0, 100),
                    'email' => mb_substr($email, 0, 150),
                    'subject' => mb_substr($subject, 0, 150),
                    'message' => mb_substr($message, 0, 2000),
                    'ip' => get_client_ip(),
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                ]);
            } catch (\Throwable $e) {
                error_log('[BlogForge Contact Error] ' . $e->getMessage());
            }
        }
        $messageSent = true;
    }
}

require __DIR__ . '/partials/header.php';
?>

<!-- Hero Banner -->
<section class="border-b border-gray-100 bg-[#fbfaf8] py-14 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-[#ff6b13] mb-4 border border-orange-100">Get in Touch</span>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-gray-900 mb-4">We’d love to hear from you</h1>
        <p class="text-gray-600 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
            Have an inquiry, feedback, or a partnership proposal? Reach out and our team will get back to you within 24 hours.
        </p>
    </div>
</section>

<!-- Content Grid -->
<section class="py-14 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
            
            <!-- Left Info Cards -->
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-3">Direct Support Channels</h2>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6">
                        Whether you are an active writer, reader, or publisher, we are committed to making your publishing journey smooth.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-[#fbfaf8] border border-gray-200">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-[#ff6b13] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Email Support</h3>
                            <p class="text-gray-500 text-xs mb-1.5">For general inquiries, account support, or press:</p>
                            <a href="mailto:support@blogforge.live" class="text-sm font-semibold text-[#ff6b13] hover:underline">support@blogforge.live</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-[#fbfaf8] border border-gray-200">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Response Time</h3>
                            <p class="text-gray-500 text-xs mb-1.5">Typical turnaround time:</p>
                            <span class="text-sm font-semibold text-gray-800">Within 24 business hours</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-[#fbfaf8] border border-gray-200">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Security &amp; Bug Bounty</h3>
                            <p class="text-gray-500 text-xs mb-1.5">Found a vulnerability? Report responsibly:</p>
                            <a href="mailto:security@blogforge.live" class="text-sm font-semibold text-[#ff6b13] hover:underline">security@blogforge.live</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Contact Form -->
            <div class="lg:col-span-7 bg-[#fbfaf8] border border-gray-200 rounded-3xl p-6 sm:p-10 shadow-sm">
                <?php if ($messageSent): ?>
                    <div class="text-center py-10 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                        <h3 class="text-2xl font-bold text-gray-900">Thank you for your message!</h3>
                        <p class="text-gray-600 text-sm max-w-md mx-auto">
                            We have received your note and will review it promptly. We appreciate you reaching out to BlogForge.
                        </p>
                        <a href="<?= e(app_url('explore.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white text-sm font-semibold px-6 py-2.5 mt-2 shadow-sm hover:shadow transition">
                            Back to explore
                        </a>
                    </div>
                <?php else: ?>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Send us a message</h2>
                    <p class="text-gray-500 text-sm mb-6">Fill out the form below and we will respond via email.</p>

                    <?php if ($errorMessage): ?>
                        <div class="p-4 mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                            <?= e($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= e(app_url('contact.php')) ?>" class="space-y-4">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Your Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" placeholder="Alex Morgan" value="<?= e($_POST['name'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" placeholder="alex@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Subject</label>
                            <input type="text" name="subject" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition" placeholder="Feature suggestion, partnership, question…" value="<?= e($_POST['subject'] ?? '') ?>">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
                            <textarea name="message" rows="5" required class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-[#ff9a3c] focus:ring-2 focus:ring-orange-100 transition resize-y" placeholder="Tell us how we can help you…"><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm px-8 py-3 shadow-sm hover:shadow hover:brightness-105 transition cursor-pointer">
                                Send message →
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Frequently Asked Questions — BlogForge';
require __DIR__ . '/partials/header.php';

$faqs = [
    'General' => [
        [
            'q' => 'What is BlogForge?',
            'a' => 'BlogForge is a modern, distraction-free publishing platform built for thinkers, writers, and curious minds. We prioritize readability, clean typography, and genuine human writing without clickbait or algorithmic noise.'
        ],
        [
            'q' => 'Is BlogForge completely free to use?',
            'a' => 'Yes! Reading and publishing stories on BlogForge is 100% free. There are no paywalls, mandatory subscriptions, or hidden charges to access articles or publish your own.'
        ],
        [
            'q' => 'Do I need an account to read stories?',
            'a' => 'No. All published stories are open to everyone. You only need a free account if you want to write stories, save drafts, like posts, or participate in discussions.'
        ],
    ],
    'For Writers' => [
        [
            'q' => 'How does the writing editor work?',
            'a' => 'BlogForge features a visual WYSIWYG editor. You format text in real time with headings, bold, italics, blockquotes, code, and images without having to see or memorize raw markdown tags.'
        ],
        [
            'q' => 'What is the draft system?',
            'a' => 'Whenever you write a story, you can save it as an unpublished draft. Drafts are completely private to you and will not appear on the home page or in search until you click Publish. You can manage all your drafts on the dedicated Drafts dashboard.'
        ],
        [
            'q' => 'What image formats and sizes are supported?',
            'a' => 'You can paste links from Unsplash or upload your own images (PNG, JPEG, WebP) up to 2MB. Our system automatically optimizes and compresses images on upload for lightning-fast loading across all devices.'
        ],
        [
            'q' => 'Who owns the content I publish on BlogForge?',
            'a' => 'You retain 100% copyright and ownership of everything you write on BlogForge. You can edit, unpublish, or delete your stories at any time.'
        ],
    ],
    'Privacy & Security' => [
        [
            'q' => 'How is my personal data protected?',
            'a' => 'We protect user data using modern cryptographic standards, secure HTTP-only cookies, robust CSRF protection, and zero third-party ad tracking. Read our Privacy Policy for full details.'
        ],
        [
            'q' => 'How do I delete my account or stories?',
            'a' => 'You can delete any of your stories directly from your profile or the story page. To delete your account and all associated data, visit your Profile Settings or contact our support team.'
        ],
    ],
];
?>

<!-- Hero Banner -->
<section class="border-b border-gray-100 bg-[#fbfaf8] py-14 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-[#ff6b13] mb-4 border border-orange-100">Help Center &amp; FAQ</span>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-gray-900 mb-4">Frequently Asked Questions</h1>
        <p class="text-gray-600 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
            Have questions about reading, publishing, or our platform? Find answers to the most common questions below.
        </p>
    </div>
</section>

<!-- FAQ Accordion Sections -->
<section class="py-14 sm:py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-12">
        <?php foreach ($faqs as $category => $items): ?>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2.5 pb-2 border-b border-gray-100">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b13]"></span>
                    <?= e($category) ?>
                </h2>
                <div class="space-y-4">
                    <?php foreach ($items as $idx => $item): ?>
                        <details class="group border border-gray-200 rounded-2xl p-5 sm:p-6 bg-white hover:border-gray-300 transition-all [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex items-center justify-between cursor-pointer list-none gap-4">
                                <span class="font-semibold text-gray-900 text-base sm:text-lg group-open:text-[#ff6b13] transition-colors">
                                    <?= e($item['q']) ?>
                                </span>
                                <span class="shrink-0 w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-open:rotate-180 group-open:bg-orange-50 group-open:text-[#ff6b13] transition-transform duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </summary>
                            <p class="mt-4 text-gray-600 text-sm sm:text-base leading-relaxed border-t border-gray-100 pt-4">
                                <?= e($item['a']) ?>
                            </p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Contact CTA -->
        <div class="mt-12 p-8 rounded-3xl bg-gradient-to-br from-orange-50/80 via-white to-amber-50/50 border border-orange-100 text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-2">Still have questions?</h3>
            <p class="text-gray-600 text-sm max-w-md mx-auto mb-6">Can’t find the answer you’re looking for? Our friendly team is here to help.</p>
            <a href="<?= e(app_url('contact.php')) ?>" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff6b13] to-[#ff9a3c] text-white font-semibold text-sm px-6 py-3 shadow-sm hover:shadow transition">
                Get in touch with us →
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

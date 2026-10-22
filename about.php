<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'About — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">About BlogForge</span>
        <h1>Built for ideas that deserve to travel.</h1>
        <p>BlogForge.live is a clean publishing platform for writers and readers who value useful, thoughtful stories over noise.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:780px;">
        <h2>Read deeply. Write clearly. Connect thoughtfully.</h2>
        <p style="font-size:19px;color:var(--muted);line-height:1.7;margin-top:16px;">
            The product is intentionally editorial: strong typography, focused reading, topic discovery, author profiles, responses, bookmarks, and a distraction-free writing experience.
        </p>
        <p style="font-size:17px;color:var(--muted);line-height:1.7;margin-top:16px;">
            Under the hood, BlogForge is built on pure semantic HTML5, modern responsive CSS3, vanilla JavaScript, PHP, and MongoDB. It features complete CRUD workflows, author session management, and robust NoSQL protections.
        </p>
        <div style="margin-top:35px;">
            <a class="btn btn-primary" href="<?= e(app_url('explore.php')) ?>">Explore stories</a>
            <a class="btn" href="<?= e(app_url('write.php')) ?>" style="margin-left:12px;">Start writing</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

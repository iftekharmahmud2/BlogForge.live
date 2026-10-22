<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pageTitle = 'Terms & Privacy — BlogForge';
require __DIR__ . '/partials/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Legal</span>
        <h1>Terms &amp; Privacy</h1>
        <p>The short, plain-language version of how BlogForge.live works.</p>
    </div>
</section>

<section class="section">
    <div class="container legal">
        <h2 id="terms">Terms of Service</h2>
        <p>By creating an account on BlogForge.live you agree to use the service responsibly and lawfully.</p>
        <ul>
            <li><b>Your content.</b> You own the stories and responses you publish. You give BlogForge permission to display them on the site.</li>
            <li><b>Your conduct.</b> Don’t post anything unlawful, hateful, harassing, misleading, or that infringes someone else’s rights.</li>
            <li><b>Your account.</b> Keep your password private. You are responsible for activity on your account.</li>
            <li><b>Moderation.</b> We may remove content or suspend accounts that break these terms.</li>
            <li><b>Changes.</b> We may update these terms; continued use means you accept the updated version.</li>
        </ul>

        <h2 id="privacy">Privacy Policy</h2>
        <p>We collect only what we need to run your account.</p>
        <ul>
            <li><b>What we store.</b> Your name, email address, a securely hashed password, your optional bio and photo link, and the stories and responses you publish.</li>
            <li><b>How we use it.</b> To sign you in, show your public author profile, and operate the site. We never store your password in plain text.</li>
            <li><b>Sharing.</b> We don’t sell your personal data. Your name, username, bio and stories are public; your email address is not.</li>
            <li><b>Your choices.</b> You can edit your profile at any time from <i>Edit profile</i>, and delete your stories whenever you like.</li>
        </ul>

        <p class="muted">Questions about these terms? Reach out through the BlogForge.live site owner.</p>

        <div style="margin-top:28px;">
            <?php if (is_logged_in()): ?>
                <a class="btn btn-primary" href="<?= e(app_url('write.php')) ?>">Start Writing</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(app_url('auth.php?mode=register')) ?>">Create an account</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

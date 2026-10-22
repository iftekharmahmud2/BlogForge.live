</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <a class="brand" href="<?= e(app_url()) ?>">
            <img src="<?= e(app_url('assets/img/blogforge-icon.png')) ?>" alt="BlogForge Logo">
            <span><b>Blog</b><strong>Forge</strong></span>
        </a>
        <div class="footer-links">
            <a href="<?= e(app_url('about.php')) ?>">About</a>
            <a href="<?= e(app_url('explore.php')) ?>">Explore</a>
            <a href="<?= e(app_url('write.php')) ?>">Write</a>
            <a href="<?= e(app_url('terms.php')) ?>">Terms & Privacy</a>
        </div>
        <small>© <?= date('Y') ?> BlogForge.live — All rights reserved.</small>
    </div>
</footer>

<div class="toast" data-toast></div>
<script src="<?= e(app_url('assets/js/app.js')) ?>"></script>
</body>
</html>

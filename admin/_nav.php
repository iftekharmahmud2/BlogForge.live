<aside class="admin-side">
    <nav>
        <a class="<?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= e(app_url('admin/')) ?>">Overview</a>
        <a class="<?= ($adminPage ?? '') === 'posts' ? 'active' : '' ?>" href="<?= e(app_url('admin/posts.php')) ?>">Posts</a>
        <a class="<?= ($adminPage ?? '') === 'comments' ? 'active' : '' ?>" href="<?= e(app_url('admin/comments.php')) ?>">Comments</a>
        <a class="<?= ($adminPage ?? '') === 'categories' ? 'active' : '' ?>" href="<?= e(app_url('admin/categories.php')) ?>">Categories</a>
        <a href="<?= e(app_url('profile.php')) ?>">My Profile</a>
        <a href="<?= e(app_url()) ?>">Back to Site</a>
    </nav>
</aside>

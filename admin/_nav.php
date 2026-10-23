<aside class="w-full md:w-60 border-b md:border-b-0 md:border-r border-[#e9e5df] bg-[#fbfaf8] p-3 md:p-6 shrink-0 md:min-h-[calc(100vh-73px)] sticky top-[73px] md:top-[73px] z-20 md:z-0">
    <nav class="flex md:flex-col overflow-x-auto md:overflow-visible gap-2 no-scrollbar">
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition shrink-0 <?= ($adminPage ?? '') === 'dashboard' ? 'bg-orange-100/70 text-[#ff6b13] font-semibold' : 'text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13]' ?>" href="<?= e(app_url('admin/')) ?>">Overview</a>
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition shrink-0 <?= ($adminPage ?? '') === 'posts' ? 'bg-orange-100/70 text-[#ff6b13] font-semibold' : 'text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13]' ?>" href="<?= e(app_url('admin/posts.php')) ?>">Posts</a>
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition shrink-0 <?= ($adminPage ?? '') === 'comments' ? 'bg-orange-100/70 text-[#ff6b13] font-semibold' : 'text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13]' ?>" href="<?= e(app_url('admin/comments.php')) ?>">Comments</a>
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition shrink-0 <?= ($adminPage ?? '') === 'categories' ? 'bg-orange-100/70 text-[#ff6b13] font-semibold' : 'text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13]' ?>" href="<?= e(app_url('admin/categories.php')) ?>">Categories</a>
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition shrink-0" href="<?= e(app_url('profile.php')) ?>">My Profile</a>
        <a class="whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-[#ff6b13] transition shrink-0" href="<?= e(app_url()) ?>">Back to Site</a>
    </nav>
</aside>

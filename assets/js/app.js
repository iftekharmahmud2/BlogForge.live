/**
 * BlogForge Frontend Application Script
 * Pure Vanilla JavaScript (Fetch API, DOM Manipulation, Async Handlers)
 */

(() => {
    'use strict';

    // Safe DOM helpers
    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];

    // Toast Notification System
    const toast = (message) => {
        const t = $('[data-toast]');
        if (!t) return;
        t.textContent = message;
        t.classList.remove('opacity-0', 'translate-y-3', 'pointer-events-none');
        t.classList.add('opacity-100', 'translate-y-0');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => {
            t.classList.add('opacity-0', 'translate-y-3', 'pointer-events-none');
            t.classList.remove('opacity-100', 'translate-y-0');
        }, 2800);
    };

    // Global API Helper
    async function api(url, options = {}) {
        const config = { ...options };
        config.headers = {
            'Accept': 'application/json',
            ...(config.headers || {}),
        };

        if (window.BLOGFORGE && window.BLOGFORGE.csrf) {
            config.headers['X-CSRF-Token'] = window.BLOGFORGE.csrf;
        }

        if (config.body && typeof config.body !== 'string' && !(config.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(url, config);
            let data = {};
            try {
                data = await response.json();
            } catch {
                data = { error: 'Invalid server response. Please try again.' };
            }

            if (!response.ok) {
                throw new Error(data.error || `Request failed with status ${response.status}`);
            }

            return data;
        } catch (err) {
            console.error('[BlogForge API Error]', err);
            throw err;
        }
    }

    // Export api to window for external page scripts if needed
    window.api = api;
    window.toast = toast;

    document.addEventListener('DOMContentLoaded', () => {
        const baseUrl = window.BLOGFORGE?.baseUrl || '';
        const isLoggedIn = Boolean(window.BLOGFORGE?.loggedIn);

        // ----------------------------------------------------
        // 1. Navigation & Dropdowns
        // ----------------------------------------------------
        const accountMenuToggle = $('[data-menu-toggle]');
        const accountMenu = $('[data-account-menu]');
        if (accountMenuToggle && accountMenu) {
            accountMenuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                accountMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!accountMenu.contains(e.target) && !accountMenuToggle.contains(e.target)) {
                    accountMenu.classList.add('hidden');
                }
            });
        }

        const mobileToggle = $('[data-mobile-toggle]') || document.getElementById('mobile-toggle');
        const mobileNav = $('[data-mobile-nav]') || document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        if (mobileToggle && mobileNav) {
            mobileToggle.addEventListener('click', () => {
                const isOpen = !mobileNav.classList.contains('hidden');
                if (isOpen) {
                    mobileNav.classList.add('hidden');
                    mobileNav.classList.remove('flex');
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
                    if (closeIcon) closeIcon.classList.add('hidden');
                } else {
                    mobileNav.classList.remove('hidden');
                    mobileNav.classList.add('flex');
                    mobileToggle.setAttribute('aria-expanded', 'true');
                    if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
                    if (closeIcon) closeIcon.classList.remove('hidden');
                }
            });
        }

        // ----------------------------------------------------
        // 2. Bookmarks
        // ----------------------------------------------------
        $$('[data-bookmark]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!isLoggedIn) {
                    toast('Sign in to save stories to your reading list.');
                } else {
                    btn.classList.toggle('active');
                    const isActive = btn.classList.contains('active');
                    btn.classList.toggle('text-[#ff6b13]', isActive);
                    btn.classList.toggle('bg-orange-50', isActive);
                    btn.classList.toggle('border-orange-200', isActive);
                    btn.textContent = isActive ? '♥' : '♡';
                    toast(isActive ? 'Saved to reading list' : 'Removed from reading list');
                }
            });
        });

        // ----------------------------------------------------
        // 3. Reactions (Likes)
        // ----------------------------------------------------
        $$('[data-like]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!isLoggedIn) {
                    location.href = baseUrl + '/auth.php';
                    return;
                }

                const postId = btn.dataset.like;
                if (!postId) return;

                btn.disabled = true;
                try {
                    const data = await api(baseUrl + '/api/reactions.php', {
                        method: 'POST',
                        body: { post_id: postId },
                    });

                    const counter = btn.querySelector('span');
                    if (counter) counter.textContent = data.likes;
                    btn.classList.toggle('liked', data.liked);
                    btn.classList.toggle('text-red-500', data.liked);
                    btn.classList.toggle('bg-red-50', data.liked);
                    btn.classList.toggle('border-red-200', data.liked);
                    toast(data.liked ? 'Story liked' : 'Like removed');
                } catch (err) {
                    toast(err.message);
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // ----------------------------------------------------
        // 4. Comments (Submission & Dynamic Rendering)
        // ----------------------------------------------------
        const commentForm = $('[data-comment-form]');
        const commentsContainer = $('[data-comments-container]');

        if (commentForm) {
            commentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!isLoggedIn) {
                    location.href = baseUrl + '/auth.php';
                    return;
                }

                const textarea = $('textarea', commentForm);
                const body = textarea ? textarea.value.trim() : '';
                const postId = commentForm.dataset.postId;

                if (!body) {
                    toast('Please type a comment before posting.');
                    return;
                }

                const submitBtn = $('button[type=submit]', commentForm);
                if (submitBtn) submitBtn.disabled = true;

                try {
                    const res = await api(baseUrl + '/api/comments.php', {
                        method: 'POST',
                        body: { post_id: postId, body },
                    });

                    toast('Response published!');
                    textarea.value = '';

                    // Dynamic DOM update
                    if (commentsContainer && res.comment) {
                        const newComment = document.createElement('div');
                        newComment.className = 'py-5 space-y-1.5 border-b border-gray-100';
                        newComment.innerHTML = `
                            <b class="block text-sm font-semibold text-gray-900">${escapeHtml(res.comment.author.name)}</b>
                            <p class="text-sm text-gray-700 leading-relaxed">${escapeHtml(res.comment.body)}</p>
                            <small class="text-xs text-gray-400 block">Just now</small>
                        `;
                        commentsContainer.prepend(newComment);

                        const emptyNotice = $('[data-no-comments]');
                        if (emptyNotice) emptyNotice.remove();
                    }
                } catch (err) {
                    toast(err.message);
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // ----------------------------------------------------
        // 5. Story Editor (Create & Edit Mode)
        // ----------------------------------------------------
        const editor = $('[data-editor-form]');
        if (editor) {
            const textarea = $('[name=body]', editor);

            // Formatting toolbar
            $$('[data-format]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!textarea) return;
                    const mark = btn.dataset.format;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const sel = textarea.value.substring(start, end);

                    // Block formats: Heading and Blockquote
                    if (mark.endsWith(' ')) {
                        // Prepend prefix to lines
                        const lines = (sel || 'Text').split('\n');
                        const formatted = lines.map(l => mark + l).join('\n');
                        textarea.setRangeText(formatted, start, end, 'end');
                    } else {
                        // Inline wrap: **bold**, _italic_, `code`
                        textarea.setRangeText(mark + (sel || 'text') + mark, start, end, 'end');
                    }
                    textarea.focus();
                });
            });

            // Form submission
            editor.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(editor);
                const payload = Object.fromEntries(fd.entries());

                payload.tags = (payload.tags || '')
                    .split(',')
                    .map((x) => x.trim())
                    .filter(Boolean);

                // Determine submitter button value (draft vs published)
                payload.status = e.submitter?.value || editor.dataset.defaultStatus || 'draft';

                const isEdit = Boolean(editor.dataset.postId);
                const endpoint = baseUrl + '/api/posts.php';

                try {
                    let result;
                    if (isEdit) {
                        payload.id = editor.dataset.postId;
                        result = await api(endpoint, {
                            method: 'PUT',
                            body: payload,
                        });
                        toast(payload.status === 'published' ? 'Story published!' : 'Draft updated.');
                    } else {
                        result = await api(endpoint, {
                            method: 'POST',
                            body: payload,
                        });
                        toast(payload.status === 'published' ? 'Story published!' : 'Draft saved.');
                    }

                    setTimeout(() => {
                        const target = result.slug || result.id || '';
                        location.href = baseUrl + '/article.php?id=' + encodeURIComponent(target);
                    }, 600);
                } catch (err) {
                    toast(err.message);
                }
            });
        }

        // ----------------------------------------------------
        // 6. Story Deletion Handlers
        // ----------------------------------------------------
        $$('[data-delete-post]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const postId = btn.dataset.deletePost;
                const postTitle = btn.dataset.postTitle || 'this story';

                if (!confirm(`Are you sure you want to permanently delete "${postTitle}"?`)) {
                    return;
                }

                try {
                    await api(baseUrl + '/api/posts.php?id=' + encodeURIComponent(postId), {
                        method: 'DELETE',
                    });
                    toast('Story deleted successfully.');

                    // If inside an article view, redirect to home/profile
                    if (document.body.classList.contains('page-article')) {
                        setTimeout(() => {
                            location.href = baseUrl + '/profile.php';
                        }, 500);
                    } else {
                        // Dynamically remove card/row from DOM
                        const targetElement = btn.closest('.story-card, .admin-table tr, .explore-row');
                        if (targetElement) {
                            targetElement.style.opacity = '0';
                            targetElement.style.transition = 'opacity 0.3s ease';
                            setTimeout(() => targetElement.remove(), 300);
                        }
                    }
                } catch (err) {
                    toast(err.message);
                }
            });
        });

        // ----------------------------------------------------
        // 7. Admin Comment Moderation Handlers
        // ----------------------------------------------------
        $$('[data-moderate-comment]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const commentId = btn.dataset.moderateComment;
                const action = btn.dataset.action; // 'approved', 'spam', 'delete'

                btn.disabled = true;
                try {
                    if (action === 'delete') {
                        if (!confirm('Permanently delete this comment?')) {
                            btn.disabled = false;
                            return;
                        }
                        await api(baseUrl + '/api/admin/comment-status.php', {
                            method: 'POST',
                            body: { id: commentId, action: 'delete' },
                        });
                        const row = btn.closest('tr');
                        if (row) row.remove();
                        toast('Comment deleted.');
                    } else {
                        await api(baseUrl + '/api/admin/comment-status.php', {
                            method: 'POST',
                            body: { id: commentId, status: action },
                        });
                        const statusBadge = btn.closest('tr')?.querySelector('.status');
                        if (statusBadge) {
                            statusBadge.textContent = action;
                            statusBadge.className = 'status ' + action;
                        }
                        toast(`Comment marked as ${action}.`);
                    }
                } catch (err) {
                    toast(err.message);
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // ----------------------------------------------------
        // 8. Password show/hide toggles
        // ----------------------------------------------------
        $$('[data-pw-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('input');
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? 'Hide' : 'Show';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });

        // Small helpers for inline form errors
        const showError = (box, message) => {
            if (!box) { toast(message); return; }
            box.textContent = message;
            box.classList.remove('hidden');
            box.style.display = 'block';
        };
        const clearError = (box, form) => {
            if (box) {
                box.classList.add('hidden');
                box.style.display = 'none';
            }
            if (form) $$('.invalid, [data-invalid]', form).forEach((el) => {
                el.classList.remove('invalid', 'border-red-500', 'ring-2', 'ring-red-100');
            });
        };
        const markInvalid = (el) => {
            if (el) {
                el.classList.add('invalid', 'border-red-500', 'ring-2', 'ring-red-100');
                el.focus();
            }
        };

        // ----------------------------------------------------
        // 9. Auth Form Handler (Email sign-in & registration)
        // ----------------------------------------------------
        const authForm = $('[data-auth-form]');
        if (authForm) {
            authForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errBox = $('[data-auth-error]');
                clearError(errBox, authForm);

                const mode = authForm.dataset.mode || 'login';
                const field = (name) => authForm.elements[name];
                const val = (name) => (field(name)?.value || '').trim();

                // Client-side validation (the server validates again)
                if (mode === 'register') {
                    if (!val('first_name')) { markInvalid(field('first_name')); return showError(errBox, 'Please enter your first name.'); }
                    if (!val('last_name')) { markInvalid(field('last_name')); return showError(errBox, 'Please enter your last name.'); }
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val('email'))) {
                    markInvalid(field('email'));
                    return showError(errBox, 'Please enter a valid email address.');
                }
                if (mode === 'register') {
                    const pw = field('password').value;
                    if (pw.length < 8) { markInvalid(field('password')); return showError(errBox, 'Password must be at least 8 characters long.'); }
                    if (pw !== field('confirm_password').value) { markInvalid(field('confirm_password')); return showError(errBox, 'Passwords do not match.'); }
                    if (!field('terms').checked) { field('terms').focus(); return showError(errBox, 'Please agree to the Terms of Service and Privacy Policy to continue.'); }
                } else if (!field('password').value) {
                    markInvalid(field('password'));
                    return showError(errBox, 'Please enter your password.');
                }

                const fd = new FormData(authForm);
                const payload = Object.fromEntries(fd.entries());
                payload.action = mode;
                payload.terms = Boolean(field('terms')?.checked);

                const submitBtn = $('button[type=submit]', authForm);
                if (submitBtn) submitBtn.disabled = true;

                try {
                    await api(baseUrl + '/api/auth/email.php', { method: 'POST', body: payload });
                    location.href = authForm.dataset.nextUrl || baseUrl + '/';
                } catch (err) {
                    showError(errBox, err.message);
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // ----------------------------------------------------
        // 10. Profile editing (details + password)
        // ----------------------------------------------------
        const profileForm = $('[data-profile-form]');
        if (profileForm) {
            const bio = $('[data-bio]', profileForm);
            const bioCount = $('[data-bio-count]', profileForm);
            const syncCount = () => { if (bio && bioCount) bioCount.textContent = bio.value.length; };
            if (bio) { bio.addEventListener('input', syncCount); syncCount(); }

            const avatarInput = profileForm.elements.avatar;
            const avatarPreview = $('[data-avatar-preview]');
            const initialsText = avatarPreview ? avatarPreview.textContent.trim() : '';
            if (avatarInput && avatarPreview) {
                avatarInput.addEventListener('change', () => {
                    const url = avatarInput.value.trim();
                    avatarPreview.textContent = '';
                    if (/^https?:\/\//i.test(url)) {
                        const img = document.createElement('img');
                        img.alt = '';
                        img.src = url;
                        img.onerror = () => { avatarPreview.textContent = initialsText; };
                        avatarPreview.appendChild(img);
                    } else {
                        avatarPreview.textContent = initialsText;
                    }
                });
            }

            profileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errBox = $('[data-profile-error]');
                clearError(errBox, profileForm);

                const payload = Object.fromEntries(new FormData(profileForm).entries());
                payload.action = 'update';

                if (!String(payload.first_name || '').trim()) { markInvalid(profileForm.elements.first_name); return showError(errBox, 'First name is required.'); }
                if (!String(payload.last_name || '').trim()) { markInvalid(profileForm.elements.last_name); return showError(errBox, 'Last name is required.'); }
                if (!/^@?[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/.test(String(payload.handle || '').trim())) {
                    markInvalid(profileForm.elements.handle);
                    return showError(errBox, 'Username must be 3–30 characters: letters, numbers, dashes or underscores.');
                }

                const submitBtn = $('button[type=submit]', profileForm);
                if (submitBtn) submitBtn.disabled = true;

                try {
                    await api(baseUrl + '/api/profile.php', { method: 'POST', body: payload });
                    toast('Profile updated.');
                    setTimeout(() => { location.href = baseUrl + '/profile.php'; }, 700);
                } catch (err) {
                    showError(errBox, err.message);
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        const passwordForm = $('[data-password-form]');
        if (passwordForm) {
            passwordForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errBox = $('[data-password-error]');
                clearError(errBox, passwordForm);

                const f = passwordForm.elements;
                if (!f.current_password.value) { markInvalid(f.current_password); return showError(errBox, 'Enter your current password.'); }
                if (f.new_password.value.length < 8) { markInvalid(f.new_password); return showError(errBox, 'New password must be at least 8 characters long.'); }
                if (f.new_password.value !== f.confirm_password.value) { markInvalid(f.confirm_password); return showError(errBox, 'New passwords do not match.'); }

                const submitBtn = $('button[type=submit]', passwordForm);
                if (submitBtn) submitBtn.disabled = true;

                try {
                    await api(baseUrl + '/api/profile.php', {
                        method: 'POST',
                        body: {
                            action: 'password',
                            current_password: f.current_password.value,
                            new_password: f.new_password.value,
                            confirm_password: f.confirm_password.value,
                        },
                    });
                    passwordForm.reset();
                    toast('Password updated.');
                } catch (err) {
                    showError(errBox, err.message);
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();

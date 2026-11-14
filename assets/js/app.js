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

        // Safe Avatar URL Resolver (HTTPS, local /uploads/avatars/..., and data URI)
        const resolveAvatarUrl = (url) => {
            if (!url || typeof url !== 'string') return '';
            const clean = url.trim();
            if (!clean) return '';
            if (/^https?:\/\//i.test(clean) || clean.startsWith('data:image/')) return clean;
            if (/^\/?uploads\//i.test(clean) || /^\/?assets\//i.test(clean)) {
                return baseUrl + '/' + clean.replace(/^\/+/, '');
            }
            return clean;
        };

        // ----------------------------------------------------
        // 0. Graceful Image Fallbacks (Cover & Avatar safety net)
        // ----------------------------------------------------
        const applyImgFallback = (img) => {
            if (!(img instanceof HTMLImageElement)) return;
            if (img.dataset.fallbackCover && !img.dataset.fallbackApplied) {
                img.dataset.fallbackApplied = '1';
                img.src = img.dataset.fallbackCover;
                return;
            }
            if (img.hasAttribute('data-avatar-img')) {
                img.classList.add('hidden');
                const fallbackEl = img.nextElementSibling;
                if (fallbackEl && fallbackEl.hasAttribute('data-avatar-fallback')) {
                    fallbackEl.classList.remove('hidden');
                    fallbackEl.classList.add('flex');
                }
            }
        };

        document.addEventListener('error', (e) => {
            if (e.target instanceof HTMLImageElement) {
                applyImgFallback(e.target);
            }
        }, true);

        $$('img[data-fallback-cover], img[data-avatar-img]').forEach((img) => {
            if (img.complete && img.naturalWidth === 0) {
                applyImgFallback(img);
            }
        });

        // ----------------------------------------------------
        // 1. Navigation & Dropdowns
        // ----------------------------------------------------
        const accountMenuToggle = document.querySelector('[data-menu-toggle]');
        const accountMenu = document.querySelector('[data-account-menu]');
        const mobileToggle = document.querySelector('[data-mobile-toggle]') || document.getElementById('mobile-toggle');
        const mobileNav = document.querySelector('[data-mobile-nav]') || document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        const closeMobileNav = () => {
            if (mobileNav && !mobileNav.classList.contains('hidden')) {
                mobileNav.classList.add('hidden');
                mobileNav.classList.remove('flex');
                if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
                if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
                if (closeIcon) closeIcon.classList.add('hidden');
            }
        };

        const closeAccountMenu = () => {
            if (accountMenu && !accountMenu.classList.contains('hidden')) {
                accountMenu.classList.add('hidden');
            }
        };

        if (accountMenuToggle && accountMenu) {
            accountMenuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeMobileNav();
                accountMenu.classList.toggle('hidden');
            });
        }

        if (mobileToggle && mobileNav) {
            mobileToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeAccountMenu();
                const willOpen = mobileNav.classList.contains('hidden');
                if (willOpen) {
                    mobileNav.classList.remove('hidden');
                    mobileNav.classList.add('flex');
                    mobileToggle.setAttribute('aria-expanded', 'true');
                    if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
                    if (closeIcon) closeIcon.classList.remove('hidden');
                } else {
                    closeMobileNav();
                }
            });
        }

        // Click outside closes both dropdowns
        document.addEventListener('click', (e) => {
            if (accountMenu && !accountMenu.classList.contains('hidden')) {
                if (!accountMenu.contains(e.target) && !accountMenuToggle.contains(e.target)) {
                    closeAccountMenu();
                }
            }
            if (mobileNav && !mobileNav.classList.contains('hidden')) {
                if (!mobileNav.contains(e.target) && !mobileToggle.contains(e.target)) {
                    closeMobileNav();
                }
            }
        });

        // ----------------------------------------------------
        // 2. Bookmarks & Reading List
        // ----------------------------------------------------
        const savedPostsKey = 'blogforge_saved_posts';
        const getSavedPosts = () => {
            try { return JSON.parse(localStorage.getItem(savedPostsKey) || '[]'); } catch { return []; }
        };
        const isPostSaved = (id) => getSavedPosts().includes(id);

        $$('[data-bookmark]').forEach((btn) => {
            const articleUrl = location.href;
            const articleId = new URLSearchParams(location.search).get('id') || articleUrl;
            const labelSpan = btn.querySelector('.bookmark-text') || btn;
            const svgIcon = btn.querySelector('.bookmark-svg');

            if (isPostSaved(articleId)) {
                btn.classList.add('active', 'border-orange-300', 'bg-orange-50', 'text-[#ff6b13]');
                if (labelSpan && labelSpan.classList.contains('bookmark-text')) labelSpan.textContent = 'Saved';
                if (svgIcon) svgIcon.setAttribute('fill', 'currentColor');
            }

            btn.addEventListener('click', () => {
                let saved = getSavedPosts();
                if (saved.includes(articleId)) {
                    saved = saved.filter(x => x !== articleId);
                    localStorage.setItem(savedPostsKey, JSON.stringify(saved));
                    btn.classList.remove('active', 'border-orange-300', 'bg-orange-50', 'text-[#ff6b13]');
                    if (labelSpan && labelSpan.classList.contains('bookmark-text')) labelSpan.textContent = 'Save';
                    if (svgIcon) svgIcon.setAttribute('fill', 'none');
                    toast('Story removed from reading list');
                } else {
                    saved.push(articleId);
                    localStorage.setItem(savedPostsKey, JSON.stringify(saved));
                    btn.classList.add('active', 'border-orange-300', 'bg-orange-50', 'text-[#ff6b13]');
                    if (labelSpan && labelSpan.classList.contains('bookmark-text')) labelSpan.textContent = 'Saved';
                    if (svgIcon) svgIcon.setAttribute('fill', 'currentColor');
                    toast('Story saved to reading list!');
                }
            });
        });

        // ----------------------------------------------------
        // 3. Reactions (Likes)
        // ----------------------------------------------------
        $$('[data-like]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (!isLoggedIn) {
                    location.href = baseUrl + '/auth.php?next=' + encodeURIComponent(location.pathname + location.search);
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
                    btn.classList.toggle('text-red-600', data.liked);
                    btn.classList.toggle('bg-red-50', data.liked);
                    btn.classList.toggle('border-red-300', data.liked);
                    toast(data.liked ? 'Story liked!' : 'Like removed');
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
                        const author = res.comment.author || {};
                        const authorName = String(author.name || 'Reader');
                        const authorHandle = String(author.handle || '').replace(/^@+/, '');
                        const profileHref = (author.profile_url && typeof author.profile_url === 'string')
                            ? author.profile_url
                            : (authorHandle
                                ? `${baseUrl}/profile.php?handle=${encodeURIComponent(authorHandle)}`
                                : `${baseUrl}/profile.php?id=${encodeURIComponent(String(author.id || ''))}`);
                        const safeAvatar = resolveAvatarUrl(author.avatar);
                        const nameParts = authorName.trim().split(/\s+/).filter(Boolean);
                        const authorInitials = ((nameParts[0]?.[0] || '') + (nameParts[1]?.[0] || nameParts[0]?.[1] || '')).toUpperCase() || 'A';

                        const avatarHtml = safeAvatar
                            ? `<img data-avatar-img src="${escapeHtml(safeAvatar)}" alt="${escapeHtml(authorName)}" class="w-9 h-9 rounded-full object-cover border border-gray-200 shadow-sm" loading="eager" decoding="async"><span data-avatar-fallback class="hidden w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white items-center justify-center font-bold text-xs shadow-sm">${escapeHtml(authorInitials)}</span>`
                            : `<span class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-900 to-[#ff7b22] text-white flex items-center justify-center font-bold text-xs shadow-sm">${escapeHtml(authorInitials)}</span>`;

                        const newComment = document.createElement('div');
                        newComment.className = 'py-5 flex items-start gap-3.5 border-b border-gray-100';
                        newComment.innerHTML = `
                            <a href="${escapeHtml(profileHref)}" class="shrink-0 group" title="View ${escapeHtml(authorName)}'s profile">${avatarHtml}</a>
                            <div class="flex-1 min-w-0 space-y-1.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <a href="${escapeHtml(profileHref)}" class="inline-flex items-center gap-1.5 group">
                                        <b class="text-sm font-semibold text-gray-900 group-hover:text-[#ff6b13] transition">${escapeHtml(authorName)}</b>
                                        ${authorHandle ? `<span class="text-xs text-gray-400 group-hover:text-[#ff6b13] transition">@${escapeHtml(authorHandle)}</span>` : ''}
                                    </a>
                                    <small class="text-xs text-gray-400">Just now</small>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">${escapeHtml(res.comment.body)}</p>
                            </div>
                        `;
                        commentsContainer.prepend(newComment);

                        const countEl = $('[data-comments-count]');
                        if (countEl) {
                            const nextCount = parseInt(countEl.textContent || '0', 10) + 1;
                            if (!Number.isNaN(nextCount)) countEl.textContent = String(nextCount);
                        }

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
        // 5. Client-Side Image Compression (Canvas WebP ~120-140KB)
        // ----------------------------------------------------
        async function compressImageToWebP(file, maxWidth = 1200, quality = 0.82) {
            return new Promise((resolve, reject) => {
                if (!file.type.match(/image.*/)) {
                    return reject(new Error('File is not an image'));
                }
                const reader = new FileReader();
                reader.onerror = () => reject(new Error('Failed to read image file'));
                reader.onload = (e) => {
                    const img = new Image();
                    img.onerror = () => reject(new Error('Failed to decode image'));
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Export as high-efficiency WebP (~120-140KB)
                        const webpData = canvas.toDataURL('image/webp', quality);
                        resolve(webpData);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        // ----------------------------------------------------
        // 6. Visual WYSIWYG Story Editor (Create & Edit Mode)
        // ----------------------------------------------------
        const editor = $('[data-editor-form]');
        if (editor) {
            const wysiwyg = $('#wysiwyg-editor', editor);
            const bodyHidden = $('#body-hidden-input', editor);
            const coverInput = $('#cover-input', editor);
            const coverUpload = $('#cover-file-upload', editor);
            const coverPreviewContainer = $('#cover-preview-container', editor);
            const coverPreviewImg = $('#cover-preview-img', editor);
            const removeCoverBtn = $('#remove-cover-btn', editor);
            const uploadStatus = $('#upload-status', editor);

            // Sync visual editor content to hidden textarea
            const syncBody = () => {
                if (wysiwyg && bodyHidden) {
                    bodyHidden.value = wysiwyg.innerHTML.trim();
                }
            };

            if (wysiwyg) {
                wysiwyg.addEventListener('input', syncBody);
                wysiwyg.addEventListener('blur', syncBody);

                // Smart Paste Handler: Strips proprietary data attributes (data-*, styles, classes)
                // and automatically renders markdown (**bold**, ## headings) into clean visual HTML
                wysiwyg.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const clipboard = e.clipboardData;
                    if (!clipboard) return;

                    const html = clipboard.getData('text/html');
                    const text = clipboard.getData('text/plain');

                    if (html && html.trim()) {
                        // Parse clipboard HTML with DOMParser
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Remove dangerous and non-content tags
                        doc.querySelectorAll('script, style, meta, link, noscript, iframe').forEach((el) => el.remove());

                        // Strip all proprietary attributes (e.g. data-path-to-node, data-index-in-node, style, class)
                        doc.querySelectorAll('*').forEach((el) => {
                            const tag = el.tagName.toLowerCase();
                            const keepAttrs = tag === 'a' ? ['href', 'target', 'rel'] : (tag === 'img' ? ['src', 'alt'] : []);
                            [...el.attributes].forEach((attr) => {
                                if (!keepAttrs.includes(attr.name.toLowerCase())) {
                                    el.removeAttribute(attr.name);
                                }
                            });
                            // Unwrap useless empty span/font tags
                            if (['span', 'font'].includes(tag) && el.attributes.length === 0) {
                                el.replaceWith(...el.childNodes);
                            }
                        });

                        let cleanHtml = doc.body.innerHTML;
                        // Auto-convert any markdown bold inside pasted HTML
                        cleanHtml = cleanHtml
                            .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                            .replace(/__(.*?)__/g, '<b>$1</b>');

                        document.execCommand('insertHTML', false, cleanHtml);
                    } else if (text) {
                        // Plain text: convert markdown to HTML if markdown syntax is detected
                        if (/(\*\*|__|\#\#|\*|_|\[.*\]\(.*\))/.test(text)) {
                            const converted = text
                                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                                .replace(/^# (.*$)/gim, '<h2>$1</h2>')
                                .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
                                .replace(/\*\*(.*?)\*\*/gim, '<b>$1</b>')
                                .replace(/__(.*?)__/gim, '<b>$1</b>')
                                .replace(/\*(.*?)\*/gim, '<i>$1</i>')
                                .replace(/_(.*?)_/gim, '<i>$1</i>')
                                .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2">$1</a>')
                                .split(/\r?\n\r?\n/)
                                .map((p) => p.trim())
                                .filter(Boolean)
                                .map((p) => (p.startsWith('<h') || p.startsWith('<blockquote') ? p : `<p>${p.replace(/\r?\n/g, '<br>')}</p>`))
                                .join('');
                            document.execCommand('insertHTML', false, converted);
                        } else {
                            // Normal plain text paragraphs
                            const paras = text.split(/\r?\n\r?\n/).map((p) => p.trim()).filter(Boolean);
                            if (paras.length > 1) {
                                const htmlParas = paras.map((p) => `<p>${escapeHtml(p).replace(/\r?\n/g, '<br>')}</p>`).join('');
                                document.execCommand('insertHTML', false, htmlParas);
                            } else {
                                document.execCommand('insertText', false, text);
                            }
                        }
                    }
                    syncBody();
                });
            }

            // WYSIWYG Formatting Toolbar commands
            $$('[data-cmd]', editor).forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (!wysiwyg) return;
                    wysiwyg.focus();
                    const cmd = btn.dataset.cmd;
                    const val = btn.dataset.val || null;
                    document.execCommand(cmd, false, val);
                    syncBody();
                });
            });

            // Insert Link in WYSIWYG Editor
            const wysiwygLinkBtn = $('#wysiwyg-link-btn', editor);
            if (wysiwygLinkBtn) {
                wysiwygLinkBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = prompt('Enter web address (https://...):', 'https://');
                    if (url && url.trim() !== '' && url !== 'https://') {
                        wysiwyg.focus();
                        document.execCommand('createLink', false, url.trim());
                        syncBody();
                    }
                });
            }

            // Insert Image in WYSIWYG Editor
            const wysiwygImgBtn = $('#wysiwyg-img-btn', editor);
            if (wysiwygImgBtn) {
                wysiwygImgBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = prompt('Enter image URL (https://images.unsplash.com/...):', 'https://');
                    if (url && url.trim() !== '' && url !== 'https://') {
                        wysiwyg.focus();
                        document.execCommand('insertImage', false, url.trim());
                        syncBody();
                    }
                });
            }

            // Cover Image Input live change
            if (coverInput && coverPreviewImg && coverPreviewContainer) {
                coverInput.addEventListener('input', () => {
                    const val = coverInput.value.trim();
                    if (val) {
                        coverPreviewImg.src = val;
                        coverPreviewContainer.classList.remove('hidden');
                    } else {
                        coverPreviewContainer.classList.add('hidden');
                    }
                });
            }

            // Remove Cover Button
            if (removeCoverBtn && coverInput && coverPreviewContainer) {
                removeCoverBtn.addEventListener('click', () => {
                    coverInput.value = '';
                    coverPreviewContainer.classList.add('hidden');
                    if (coverPreviewImg) coverPreviewImg.src = '';
                });
            }

            // Client-side WebP Compression & Upload for Cover Image
            if (coverUpload) {
                coverUpload.addEventListener('change', async (e) => {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (file.size > 2 * 1024 * 1024) {
                        toast('Image exceeds 2MB limit. Please choose a smaller file.');
                        return;
                    }

                    if (uploadStatus) uploadStatus.textContent = 'Compressing image…';

                    try {
                        // Compress to WebP format (~120-140KB)
                        const compressedWebP = await compressImageToWebP(file, 1200, 0.82);

                        if (uploadStatus) uploadStatus.textContent = 'Saving image…';

                        // Upload via backend API (Cloudinary if available or optimized WebP base64)
                        const uploadRes = await api(baseUrl + '/api/upload.php', {
                            method: 'POST',
                            body: { image: compressedWebP },
                        });

                        const finalUrl = uploadRes.url || compressedWebP;

                        if (coverInput) coverInput.value = finalUrl;
                        if (coverPreviewImg) coverPreviewImg.src = finalUrl;
                        if (coverPreviewContainer) coverPreviewContainer.classList.remove('hidden');

                        toast('Cover image compressed & attached!');
                        if (uploadStatus) uploadStatus.textContent = '✓ Ready';
                    } catch (err) {
                        console.error('Image upload error:', err);
                        toast('Failed to process image: ' + err.message);
                        if (uploadStatus) uploadStatus.textContent = 'Error';
                    }
                });
            }

            // Form submission
            editor.addEventListener('submit', async (e) => {
                e.preventDefault();
                syncBody();

                const fd = new FormData(editor);
                const payload = Object.fromEntries(fd.entries());

                if (!payload.title || !payload.title.trim()) {
                    toast('Please enter a title for your story.');
                    return;
                }

                if (!payload.body || !payload.body.trim()) {
                    toast('Story body cannot be empty.');
                    return;
                }

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
                        if (payload.status === 'draft') {
                            location.href = baseUrl + '/drafts.php';
                        } else {
                            location.href = baseUrl + '/article.php?id=' + encodeURIComponent(target);
                        }
                    }, 500);
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
                        const targetElement = btn.closest('[data-post-card], tr, article, .story-card, .admin-table tr, .explore-row');
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
                        const statusBadge = btn.closest('tr')?.querySelector('[data-comment-status-badge], .status');
                        if (statusBadge) {
                            statusBadge.textContent = action;
                            const badgeColor = action === 'approved' 
                                ? 'bg-emerald-50 text-emerald-700' 
                                : (action === 'spam' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700');
                            statusBadge.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize ${badgeColor}`;
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
                const target = el.closest('[data-input-wrapper]') || el;
                target.classList.add('invalid', 'border-red-500', 'ring-2', 'ring-red-100');
                el.focus();
            }
        };

        // ----------------------------------------------------
        // 8.5 Date of Birth Helper & Picker (DD/MM/YY)
        // ----------------------------------------------------
        const parseDob = (str) => {
            if (!str || typeof str !== 'string') return null;
            const clean = str.trim();
            const m = clean.match(/^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2}|\d{4})$/);
            if (!m) return null;
            const day = parseInt(m[1], 10);
            const month = parseInt(m[2], 10);
            let year = parseInt(m[3], 10);

            if (month < 1 || month > 12 || day < 1 || day > 31) return null;

            const now = new Date();
            const currentYear = now.getFullYear();
            const current2Digit = currentYear % 100;

            let fullYear = year;
            let yy = year;
            const isFourDigit = (m[3].length === 4);

            if (year < 100) {
                fullYear = (year <= current2Digit) ? (2000 + year) : (1900 + year);
                yy = year;
            } else {
                yy = year % 100;
            }

            if (fullYear < currentYear - 120) return null;

            const daysInMonth = new Date(fullYear, month, 0).getDate();
            if (day > daysInMonth) return null;

            const dateObj = new Date(fullYear, month - 1, day);
            if (dateObj > now) return null;

            const ddStr = String(day).padStart(2, '0');
            const mmStr = String(month).padStart(2, '0');
            const yyStr = String(yy).padStart(2, '0');
            const yyyyStr = String(fullYear);

            return {
                day,
                month,
                year: fullYear,
                yy: yyStr,
                yyyy: yyyyStr,
                formatted: isFourDigit ? `${ddStr}/${mmStr}/${yyyyStr}` : `${ddStr}/${mmStr}/${yyStr}`,
                iso: `${fullYear}-${mmStr}-${ddStr}`
            };
        };

        const initDobPickers = () => {
            const wrappers = $$('[data-dob-wrapper]');
            if (!wrappers.length) return;

            wrappers.forEach((wrapper) => {
                const input = $('[data-dob-input]', wrapper);
                const nativeInput = $('[data-dob-native]', wrapper);
                const calBtn = $('[data-dob-cal-btn]', wrapper) || $('[data-dob-cal-wrapper]', wrapper);

                if (!input) return;

                const syncToNative = () => {
                    if (!nativeInput || !input.value) return;
                    const parsed = parseDob(input.value);
                    if (parsed && parsed.iso) {
                        nativeInput.value = parsed.iso;
                    }
                };

                // Calendar opener
                const openCalendar = () => {
                    if (!nativeInput) return;
                    syncToNative();
                    if (typeof nativeInput.showPicker === 'function') {
                        try {
                            nativeInput.showPicker();
                            return;
                        } catch {
                            // fallback
                        }
                    }
                    nativeInput.focus();
                    try {
                        nativeInput.click();
                    } catch {}
                };

                if (calBtn) {
                    calBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        openCalendar();
                    });
                }

                // If native calendar value changes, convert YYYY-MM-DD -> DD/MM/YYYY
                if (nativeInput) {
                    nativeInput.addEventListener('change', () => {
                        if (!nativeInput.value) return;
                        const parts = nativeInput.value.split('-');
                        if (parts.length === 3) {
                            const [yyyy, mm, dd] = parts;
                            input.value = `${dd}/${mm}/${yyyy}`;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                }

                // Keyboard typing auto-formatter: auto-inserts '/' after 2 digits
                let isDeleting = false;
                input.addEventListener('keydown', (e) => {
                    isDeleting = (e.key === 'Backspace' || e.key === 'Delete');
                });

                input.addEventListener('input', () => {
                    if (isDeleting) return;

                    let v = input.value;
                    v = v.replace(/[-.]/g, '/').replace(/[^\d/]/g, '').replace(/\/+/g, '/');

                    const parts = v.split('/');

                    if (parts.length === 1) {
                        const d = parts[0];
                        if (d.length === 2) {
                            v = d + '/';
                        } else if (d.length > 2 && d.length <= 4) {
                            v = d.slice(0, 2) + '/' + d.slice(2);
                            if (d.length === 4) v += '/';
                        } else if (d.length > 4) {
                            v = d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4, 8);
                        }
                    } else if (parts.length === 2) {
                        const d = parts[0].slice(0, 2);
                        const m = parts[1];
                        if (m.length === 2) {
                            v = d + '/' + m + '/';
                        } else if (m.length > 2) {
                            v = d + '/' + m.slice(0, 2) + '/' + m.slice(2, 6);
                        } else {
                            v = d + '/' + m;
                        }
                    } else if (parts.length >= 3) {
                        const d = parts[0].slice(0, 2);
                        const m = parts[1].slice(0, 2);
                        const y = parts.slice(2).join('').replace(/\D/g, '').slice(0, 4);
                        v = d + '/' + m + '/' + y;
                    }

                    if (input.value !== v) {
                        input.value = v;
                    }

                    const parsed = parseDob(v);
                    if (parsed && nativeInput) {
                        nativeInput.value = parsed.iso;
                    }
                });

                input.addEventListener('blur', () => {
                    const v = input.value.trim();
                    if (!v) return;
                    const parsed = parseDob(v);
                    if (parsed) {
                        input.value = parsed.formatted;
                        if (nativeInput) nativeInput.value = parsed.iso;
                    }
                });
            });
        };

        initDobPickers();

        // ----------------------------------------------------
        // 9. Auth Form Handler (Email sign-in & registration)
        // ----------------------------------------------------
        const authForm = $('[data-auth-form]');
        if (authForm) {
            const handleInput = authForm.elements.handle || authForm.elements.username;
            const handleFeedback = $('#handle-feedback', authForm);
            if (handleInput && handleFeedback) {
                let debounceTimer = null;
                const checkHandle = async () => {
                    const rawVal = handleInput.value.replace(/^@/, '').trim();
                    if (!rawVal) {
                        handleFeedback.className = 'hidden font-medium shrink-0';
                        handleFeedback.textContent = '';
                        return;
                    }
                    if (!/^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/.test(rawVal)) {
                        handleFeedback.className = 'text-amber-600 font-medium shrink-0';
                        handleFeedback.textContent = '3–30 chars: a-z, 0-9, _, -';
                        return;
                    }
                    try {
                        const res = await api(baseUrl + '/api/auth/email.php', {
                            method: 'POST',
                            body: { action: 'check_handle', handle: rawVal.toLowerCase() }
                        });
                        if (res.available) {
                            handleFeedback.className = 'text-emerald-600 font-medium shrink-0';
                            handleFeedback.textContent = '✓ Available';
                        } else {
                            handleFeedback.className = 'text-red-600 font-medium shrink-0';
                            handleFeedback.textContent = '✗ Already taken';
                        }
                    } catch {
                        // Ignore transient network errors during typing check
                    }
                };

                handleInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(checkHandle, 350);
                });
                handleInput.addEventListener('blur', checkHandle);
            }

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
                    const hInput = field('handle') || field('username');
                    const cleanH = (val('handle') || val('username')).replace(/^@/, '');
                    if (!cleanH) {
                        markInvalid(hInput);
                        return showError(errBox, 'Please choose a username.');
                    }
                    if (!/^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/.test(cleanH)) {
                        markInvalid(hInput);
                        return showError(errBox, 'Username must be 3–30 characters: letters, numbers, dashes or underscores.');
                    }
                    const dobInput = field('dob');
                    const rawDob = val('dob');
                    if (!rawDob) {
                        markInvalid(dobInput);
                        return showError(errBox, 'Please enter your date of birth (DD/MM/YY).');
                    }
                    const parsedDob = parseDob(rawDob);
                    if (!parsedDob) {
                        markInvalid(dobInput);
                        return showError(errBox, 'Please enter a valid date of birth in DD/MM/YY format (e.g. 24/05/98).');
                    }
                    dobInput.value = parsedDob.formatted;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val('email')) && (mode === 'register' || val('email').includes('@'))) {
                    markInvalid(field('email'));
                    return showError(errBox, 'Please enter a valid email address.');
                }
                if (mode !== 'register' && !val('email')) {
                    markInvalid(field('email'));
                    return showError(errBox, 'Please enter your email or username.');
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
                if (payload.handle) {
                    payload.handle = String(payload.handle).replace(/^@/, '').trim().toLowerCase();
                }
                if (payload.dob) {
                    const parsedDob = parseDob(payload.dob);
                    if (parsedDob) payload.dob = parsedDob.formatted;
                }

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
            const initialsText = avatarPreview ? (avatarPreview.dataset.initials || avatarPreview.textContent.trim()) : '';
            const uploadBtn = $('[data-avatar-upload-btn]', profileForm) || $('[data-avatar-upload-btn]');
            const browseBtn = $('[data-avatar-browse-btn]', profileForm);
            const fileInput = $('[data-avatar-file]', profileForm) || $('[data-avatar-file]');
            const removeBtn = $('[data-avatar-remove-btn]', profileForm) || $('[data-avatar-remove-btn]');
            const statusEl = $('[data-avatar-status]', profileForm) || $('[data-avatar-status]');

            const syncAvatarPreview = () => {
                const raw = avatarInput ? avatarInput.value.trim() : '';
                const resolved = resolveAvatarUrl(raw);
                if (avatarPreview) {
                    avatarPreview.textContent = '';
                    if (resolved) {
                        const img = document.createElement('img');
                        img.alt = '';
                        img.className = 'w-full h-full object-cover';
                        img.src = resolved;
                        img.onerror = () => { avatarPreview.textContent = initialsText; };
                        avatarPreview.appendChild(img);
                    } else {
                        avatarPreview.textContent = initialsText;
                    }
                }
                if (removeBtn) {
                    if (raw) {
                        removeBtn.classList.remove('hidden');
                    } else {
                        removeBtn.classList.add('hidden');
                    }
                }
            };

            if (avatarInput) {
                avatarInput.addEventListener('change', syncAvatarPreview);
                avatarInput.addEventListener('input', syncAvatarPreview);
            }

            const openAvatarPicker = () => {
                if (fileInput) fileInput.click();
            };

            if (uploadBtn) uploadBtn.addEventListener('click', openAvatarPicker);
            if (browseBtn) browseBtn.addEventListener('click', openAvatarPicker);

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    if (avatarInput) avatarInput.value = '';
                    if (fileInput) fileInput.value = '';
                    syncAvatarPreview();
                    toast('Profile photo removed. Click "Save changes" to apply.');
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', async (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    if (file.size > 2 * 1024 * 1024) {
                        toast('Image exceeds 2MB limit. Please choose a file under 2MB.');
                        fileInput.value = '';
                        return;
                    }

                    if (!file.type.match(/^image\/(jpeg|png|webp|gif)$/i)) {
                        toast('Please select a valid image file (JPG, PNG, WebP, or GIF).');
                        fileInput.value = '';
                        return;
                    }

                    if (statusEl) {
                        statusEl.textContent = 'Optimizing image…';
                        statusEl.classList.remove('hidden');
                    }

                    try {
                        // High-speed compression into 256x256 WebP (~15KB)
                        const compressedWebP = await compressImageToWebP(file, 256, 0.85);

                        // Instant local preview in circular avatar
                        if (avatarPreview) {
                            avatarPreview.textContent = '';
                            const img = document.createElement('img');
                            img.alt = '';
                            img.className = 'w-full h-full object-cover';
                            img.src = compressedWebP;
                            avatarPreview.appendChild(img);
                        }

                        if (statusEl) statusEl.textContent = 'Uploading…';

                        const res = await api(baseUrl + '/api/upload.php', {
                            method: 'POST',
                            body: { image: compressedWebP, type: 'avatar' },
                        });

                        const finalUrl = res.url || compressedWebP;
                        if (avatarInput) avatarInput.value = finalUrl;
                        if (removeBtn) removeBtn.classList.remove('hidden');

                        if (statusEl) {
                            statusEl.textContent = '✓ Ready to save';
                            setTimeout(() => { statusEl.classList.add('hidden'); }, 3000);
                        }
                        toast('Photo uploaded & optimized! Click "Save changes" to apply.');
                    } catch (err) {
                        console.error('Avatar upload error:', err);
                        toast('Upload failed: ' + err.message);
                        if (statusEl) statusEl.classList.add('hidden');
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
                payload.handle = String(payload.handle || '').replace(/^@+/, '').trim().toLowerCase();
                if (!/^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/.test(payload.handle)) {
                    markInvalid(profileForm.elements.handle);
                    return showError(errBox, 'Username must be 3–30 characters: letters, numbers, dashes or underscores.');
                }
                if (payload.dob && String(payload.dob).trim() !== '') {
                    const parsedDob = parseDob(String(payload.dob));
                    if (!parsedDob) {
                        markInvalid(profileForm.elements.dob);
                        return showError(errBox, 'Please enter a valid date of birth in DD/MM/YY format (e.g. 24/05/98).');
                    }
                    payload.dob = parsedDob.formatted;
                }

                if (payload.avatar !== undefined) {
                    payload.avatar = String(payload.avatar).trim();
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

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form instanceof HTMLFormElement && form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();

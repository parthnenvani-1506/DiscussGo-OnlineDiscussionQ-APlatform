/**
 * DiscussHub - Frontend Application Core Script
 */

// Base URL helper — reads from meta tag set by Laravel
function url(path) {
    const base = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    return base + '/' + path.replace(/^\//, '');
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Theme Management (Light / Dark mode)
    initTheme();

    // 2. AJAX Voting System
    initVoting();

    // 3. AJAX Bookmarking
    initBookmarks();

    // 4. AI Duplicate Detection & Tag Suggestions
    initAIQuestionAssistance();

    // 5. AI Answer Summarizer
    initAISummarizer();

    // 6. Flash Alerts Auto-dismiss
    initFlashAlerts();

    // 7. 3D Navbar Motion & Keyboard Shortcuts
    initNavbarMotion();

    // 8. Reputation Badge Number Formatting + Tooltips
    initReputationBadges();
});

/* ==========================================================================
   0. 3D Navbar Motion & Micro-Interactions
   ========================================================================== */
function initNavbarMotion() {
    const navbar = document.querySelector('.dg-navbar');
    const searchInput = document.querySelector('.dg-search-input');
    const brand = document.querySelector('.dg-brand');
    const brandLogo = document.querySelector('.dg-brand-logo');

    // 1. Dynamic Scroll Compaction & Elevation
    if (navbar) {
        const handleScroll = () => {
            if (window.scrollY > 15) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    // 2. Global Keyboard Shortcut for Search (Ctrl+K or /)
    if (searchInput) {
        document.addEventListener('keydown', (e) => {
            // Ignore if active in another input/textarea
            const tag = document.activeElement?.tagName.toLowerCase();
            const isEditing = tag === 'input' || tag === 'textarea' || document.activeElement?.isContentEditable;

            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            } else if (e.key === '/' && !isEditing) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    // 3. 3D Perspective Tilt on Brand Logo (Desktop)
    if (brand && brandLogo && window.matchMedia('(hover: hover)').matches) {
        brand.addEventListener('mousemove', (e) => {
            const rect = brand.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            const rotateY = (x / (rect.width / 2)) * 10;
            const rotateX = -(y / (rect.height / 2)) * 10;
            brandLogo.style.transform = `perspective(600px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px) scale(1.04)`;
        });

        brand.addEventListener('mouseleave', () => {
            brandLogo.style.transform = '';
        });
    }

    // 4. Mobile Drawer Auto-Close on Link Click
    const mobileCollapse = document.getElementById('navbarContent');
    if (mobileCollapse && window.bootstrap) {
        const navLinks = mobileCollapse.querySelectorAll('.nav-link, .dg-btn-cta, .dropdown-item');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                const bsCollapse = bootstrap.Collapse.getInstance(mobileCollapse);
                if (bsCollapse && window.innerWidth < 992) {
                    bsCollapse.hide();
                }
            });
        });
    }
}

/* ==========================================================================
   1. Theme Management
   ========================================================================== */
function initTheme() {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const htmlElement = document.documentElement;

    // Check saved preference or system preference
    const savedTheme = localStorage.getItem('discusshub_theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    htmlElement.setAttribute('data-theme', savedTheme);
    htmlElement.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            htmlElement.setAttribute('data-theme', newTheme);
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('discusshub_theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-toggle-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill text-secondary';
    }
}

/* ==========================================================================
   2. AJAX Like System (Quora-style — hearts only, no downvote)
   ========================================================================== */
function initVoting() {
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const btn = e.currentTarget;
            const likeableType = btn.dataset.type;
            const likeableId   = btn.dataset.id;
            const countEl      = btn.querySelector('.like-count');
            const iconEl       = btn.querySelector('i');
            const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(url('/like'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        likeable_type: likeableType,
                        likeable_id:   likeableId,
                    })
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const data = await response.json();

                if (response.ok && data.success) {
                    // Update count
                    if (countEl) countEl.textContent = data.new_count;

                    // Toggle heart icon
                    if (data.liked) {
                        btn.classList.add('liked');
                        if (iconEl) { iconEl.className = 'bi bi-heart-fill'; }
                        showToast('Liked!', 'success');
                    } else {
                        btn.classList.remove('liked');
                        if (iconEl) { iconEl.className = 'bi bi-heart'; }
                        showToast('Like removed.', 'info');
                    }
                } else {
                    showToast(data.error || 'Unable to register like.', 'warning');
                }
            } catch (err) {
                console.error(err);
                showToast('Network error.', 'danger');
            }
        });
    });
}

/* ==========================================================================
   3. AJAX Bookmarking
   ========================================================================== */
function initBookmarks() {
    document.querySelectorAll('.btn-bookmark-toggle').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const questionId = btn.dataset.questionId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(url('/bookmarks/toggle'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ question_id: questionId })
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    const icon = btn.querySelector('i');
                    const text = btn.querySelector('.bookmark-text');

                    if (data.bookmarked) {
                        if (icon) icon.className = 'bi bi-bookmark-fill text-primary';
                        if (text) text.textContent = 'Saved';
                        btn.classList.add('bookmarked');
                        showToast('Question saved to bookmarks!', 'success');
                    } else {
                        if (icon) icon.className = 'bi bi-bookmark';
                        if (text) text.textContent = 'Save';
                        btn.classList.remove('bookmarked');
                        showToast('Removed from bookmarks.', 'info');
                    }
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to toggle bookmark.', 'danger');
            }
        });
    });
}

/* ==========================================================================
   4. AI Question Creation Assistant (Duplicates, Tags, Quality Score)
   ========================================================================== */
function initAIQuestionAssistance() {
    const titleInput = document.getElementById('question-title-input');
    const descInput = document.getElementById('question-desc-input');
    const duplicateBox = document.getElementById('ai-duplicate-results');
    const tagSuggestionBox = document.getElementById('ai-suggested-tags-box');
    const qualityScoreBar = document.getElementById('ai-quality-score-bar');
    const qualityScoreText = document.getElementById('ai-quality-score-text');
    const qualityTipsList = document.getElementById('ai-quality-tips-list');

    if (!titleInput) return;

    let debounceTimer;

    const performAIChecks = () => {
        const title = titleInput.value.trim();
        const desc = descInput ? descInput.value.trim() : '';

        if (title.length < 6) {
            if (duplicateBox) duplicateBox.classList.add('d-none');
            return;
        }

        // 1. Duplicate Check
        fetch(url('/api/ai/check-duplicate'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ title: title, description: desc })
        })
        .then(res => res.json())
        .then(data => {
            if (!duplicateBox) return;

            if (data.similar_questions && data.similar_questions.length > 0) {
                duplicateBox.classList.remove('d-none');
                let html = `
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-secondary small"><i class="bi bi-diagram-3 text-primary me-1"></i> Similar Discussions Found (${data.similar_questions.length})</span>
                        ${data.is_duplicate ? '<span class="badge bg-danger">Potential Duplicate (' + data.max_score + '%)</span>' : ''}
                    </div>
                    <ul class="list-unstyled mb-0 small">
                `;

                data.similar_questions.forEach(q => {
                    html += `
                        <li class="py-1 d-flex align-items-center justify-content-between border-bottom">
                            <a href="${q.url}" target="_blank" class="text-decoration-none text-truncate me-2">${q.title}</a>
                            <span class="badge bg-light text-dark border">${q.similarity}% match</span>
                        </li>
                    `;
                });

                html += `</ul>`;
                duplicateBox.innerHTML = html;
            } else {
                duplicateBox.classList.add('d-none');
            }
        })
        .catch(console.error);

        // 2. Tag Suggestions
        if (tagSuggestionBox) {
            fetch(url('/api/ai/suggest-tags'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title: title, description: desc })
            })
            .then(res => res.json())
            .then(data => {
                if (data.tags && data.tags.length > 0) {
                    tagSuggestionBox.classList.remove('d-none');
                    let tagHtml = `<span class="small fw-bold text-secondary me-2"><i class="bi bi-tags text-primary"></i> Suggested Tags:</span>`;
                    data.tags.forEach(t => {
                        tagHtml += `
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 me-1 mb-1 btn-add-tag rounded-pill" data-tag-id="${t.id}" data-tag-name="${t.name}">
                                + #${t.name}
                            </button>
                        `;
                    });
                    tagSuggestionBox.innerHTML = tagHtml;

                    // Bind tag click handlers
                    tagSuggestionBox.querySelectorAll('.btn-add-tag').forEach(tagBtn => {
                        tagBtn.addEventListener('click', () => {
                            const tagSelect = document.getElementById('tags-select');
                            if (tagSelect) {
                                const option = tagSelect.querySelector(`option[value="${tagBtn.dataset.tagId}"]`);
                                if (option) {
                                    option.selected = true;
                                    tagBtn.classList.remove('btn-outline-primary');
                                    tagBtn.classList.add('btn-primary', 'disabled');
                                }
                            }
                        });
                    });
                }
            })
            .catch(console.error);
        }

        // 3. Realtime Quality Evaluator
        if (qualityScoreBar && qualityScoreText) {
            fetch(url('/api/ai/quality-check'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title: title, description: desc })
            })
            .then(res => res.json())
            .then(data => {
                const score = data.score || 50;
                qualityScoreBar.style.width = score + '%';
                qualityScoreBar.className = 'progress-bar ' + (score >= 80 ? 'bg-success' : (score >= 50 ? 'bg-primary' : 'bg-warning'));
                qualityScoreText.textContent = `${score}/100`;

                if (qualityTipsList && data.tips) {
                    if (data.tips.length > 0) {
                        qualityTipsList.innerHTML = data.tips.map(t => `<li class="small text-muted"><i class="bi bi-info-circle text-primary me-1"></i> ${t}</li>`).join('');
                    } else {
                        qualityTipsList.innerHTML = '<li class="small text-success"><i class="bi bi-check-circle-fill me-1"></i> Excellent question structure! Ready to publish.</li>';
                    }
                }
            })
            .catch(console.error);
        }
    };

    titleInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performAIChecks, 600);
    });

    if (descInput) {
        descInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performAIChecks, 800);
        });
    }
}

/* ==========================================================================
   5. AI Answer Summarization Trigger
   ========================================================================== */
function initAISummarizer() {
    const summarizeBtn = document.getElementById('btn-generate-ai-summary');
    const summaryContainer = document.getElementById('ai-summary-content');
    const summarySpinner = document.getElementById('ai-summary-spinner');

    if (!summarizeBtn || !summaryContainer) return;

    summarizeBtn.addEventListener('click', async () => {
        const questionId = summarizeBtn.dataset.questionId;
        summarizeBtn.disabled = true;
        if (summarySpinner) summarySpinner.classList.remove('d-none');

        try {
            const response = await fetch(url(`/api/ai/summarize/${questionId}?force=1`), {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                summaryContainer.innerHTML = `<p class="mb-0 text-secondary">${data.summary}</p>`;
                showToast('AI Summary generated successfully!', 'success');
            } else {
                showToast(data.message || 'Could not summarize answers.', 'warning');
            }
        } catch (err) {
            console.error(err);
            showToast('AI service currently unavailable.', 'danger');
        } finally {
            summarizeBtn.disabled = false;
            if (summarySpinner) summarySpinner.classList.add('d-none');
        }
    });
}

/* ==========================================================================
   6. Notification Toast Helper
   ========================================================================== */
function showToast(message, type = 'info') {
    let container = document.getElementById('dg-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'dg-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `dg-toast border-${type}`;

    let icon = 'bi-info-circle-fill text-primary';
    if (type === 'success') icon = 'bi-check-circle-fill text-success';
    if (type === 'warning') icon = 'bi-exclamation-triangle-fill text-warning';
    if (type === 'danger') icon = 'bi-x-circle-fill text-danger';

    toast.innerHTML = `
        <i class="bi ${icon} fs-5"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'all 0.4s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

function initFlashAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
}

/* ==========================================================================
   Follow / Unfollow Toggle
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-follow-toggle').forEach(btn => {
        btn.addEventListener('click', async () => {
            const userId = btn.dataset.userId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(url(`/follow/${userId}`), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const data = await response.json();

                if (data.following !== undefined) {
                    if (data.following) {
                        btn.innerHTML = '<i class="bi bi-person-check me-1"></i> Following';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-secondary');
                        showToast('You are now following this user.', 'success');
                    } else {
                        btn.innerHTML = '<i class="bi bi-person-plus me-1"></i> Follow';
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-primary');
                        showToast('You unfollowed this user.', 'info');
                    }
                } else if (data.error) {
                    showToast(data.error, 'warning');
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to update follow status.', 'danger');
            }
        });
    });
});

/* ==========================================================================
   AI Generate Answer Button
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const generateBtn = document.getElementById('btn-generate-answer');
    if (!generateBtn) return;

    generateBtn.addEventListener('click', async () => {
        const questionId = generateBtn.dataset.questionId;
        const generateUrl = generateBtn.dataset.url;
        const spinner = document.getElementById('generate-spinner');
        const disclaimer = document.getElementById('ai-generate-disclaimer');
        const answerTextarea = document.querySelector('textarea[name="answer"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        generateBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');

        try {
            const response = await fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const data = await response.json();

            if (data.success && data.draft) {
                if (answerTextarea) {
                    answerTextarea.value = data.draft;
                    answerTextarea.focus();
                }
                if (disclaimer) disclaimer.classList.remove('d-none');
                showToast('AI draft generated! Review and edit before posting.', 'success');
            } else {
                showToast(data.error || 'Could not generate AI draft.', 'warning');
            }
        } catch (err) {
            console.error(err);
            showToast('AI service unavailable. Please write your answer manually.', 'info');
        } finally {
            generateBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
        }
    });
});

/* ==========================================================================
   Share Link (Copy to Clipboard)
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const shareBtn = document.getElementById('btn-share-question');
    if (shareBtn) {
        shareBtn.addEventListener('click', () => {
            const url = shareBtn.dataset.url || window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Link copied to clipboard!', 'success');
                    shareBtn.innerHTML = '<i class="bi bi-check2 text-success"></i> Copied!';
                    setTimeout(() => { shareBtn.innerHTML = '<i class="bi bi-share"></i> Share'; }, 2500);
                });
            } else {
                // Fallback for older browsers
                const el = document.createElement('textarea');
                el.value = url;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                showToast('Link copied!', 'success');
            }
        });
    }
});

/* ==========================================================================
   8. Reputation Badge Formatting + Tooltips
   Converts raw numbers to compact form: 1 000 → 1k, 1 500 000 → 1.5m, etc.
   Full exact number is always shown in the Bootstrap tooltip on hover.
   ========================================================================== */
function formatReputation(n) {
    n = parseInt(n, 10);
    if (isNaN(n)) return n;

    if (n >= 1_000_000_000) {
        const v = n / 1_000_000_000;
        return (Number.isInteger(v) ? v : v.toFixed(1).replace(/\.0$/, '')) + 'b';
    }
    if (n >= 1_000_000) {
        const v = n / 1_000_000;
        return (Number.isInteger(v) ? v : v.toFixed(1).replace(/\.0$/, '')) + 'm';
    }
    if (n >= 1_000) {
        const v = n / 1_000;
        return (Number.isInteger(v) ? v : v.toFixed(1).replace(/\.0$/, '')) + 'k';
    }
    return n.toString();
}

function initReputationBadges() {
    // Format all reputation display values
    document.querySelectorAll('.rep-badge-fmt').forEach(badge => {
        const raw = parseInt(badge.getAttribute('data-rep'), 10);
        if (isNaN(raw)) return;

        const valueEl = badge.querySelector('.rep-value');
        if (valueEl) {
            valueEl.textContent = formatReputation(raw);
        }

        // Ensure tooltip title uses the full comma-formatted exact number
        const exact = raw.toLocaleString('en-US');
        badge.setAttribute('title', exact + ' reputation points');

        // Initialise Bootstrap 5 tooltip — let data-bs-placement drive direction
        // so each badge appears in the correct position for its context.
        if (window.bootstrap && window.bootstrap.Tooltip) {
            // Destroy any existing instance first (safe for re-init)
            const existing = bootstrap.Tooltip.getInstance(badge);
            if (existing) existing.dispose();

            new bootstrap.Tooltip(badge, {
                trigger:   'hover focus',
                html:      false,
                animation: true,
            });
        }
    });
}

/* ==========================================================================
   Tag Deduplication UI (FR-05-AI)
   Fires on the Ask Question / Edit Question tag input
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const tagsSelect = document.getElementById('tags-select');
    if (!tagsSelect) return;

    // Create a text input for manual tag entry above the select
    const tagInputWrapper = document.createElement('div');
    tagInputWrapper.className = 'mb-2';
    tagInputWrapper.innerHTML = `
        <input type="text" id="tag-check-input" class="form-control form-control-dg"
            placeholder="Type a tag name to check for duplicates (then select from list below)">
        <div id="tag-merge-suggestion" class="mt-2 d-none"></div>
    `;
    tagsSelect.parentNode.insertBefore(tagInputWrapper, tagsSelect);

    const tagCheckInput = document.getElementById('tag-check-input');
    const suggestionBox = document.getElementById('tag-merge-suggestion');
    let debounceTimer;

    tagCheckInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const name = tagCheckInput.value.trim();
        if (name.length < 2) { suggestionBox.classList.add('d-none'); return; }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(url('/api/tags/check-duplicate'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ name })
                });
                const data = await res.json();

                if (data.exact_match && data.canonical_tag) {
                    suggestionBox.classList.remove('d-none');
                    suggestionBox.innerHTML = `
                        <div class="p-2 rounded bg-success-subtle border border-success d-flex align-items-center justify-content-between gap-2 small">
                            <span><i class="bi bi-check-circle text-success me-1"></i>
                                <strong>"${data.canonical_tag.name}"</strong> already exists — using it.</span>
                            <button type="button" class="btn btn-sm btn-success py-0 px-2" onclick="selectTagInList(${data.canonical_tag.id})">
                                Add Tag
                            </button>
                        </div>`;
                } else if (data.suggestions && data.suggestions.length > 0) {
                    const s = data.suggestions[0];
                    suggestionBox.classList.remove('d-none');
                    suggestionBox.innerHTML = `
                        <div class="p-2 rounded bg-warning-subtle border border-warning small">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            <strong>"${name}"</strong> is ${s.score}% similar to existing tag <strong>"${s.tag.name}"</strong>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-warning py-0 px-2" onclick="selectTagInList(${s.tag.id}); document.getElementById('tag-check-input').value=''; document.getElementById('tag-merge-suggestion').classList.add('d-none');">
                                    Use "${s.tag.name}"
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="document.getElementById('tag-merge-suggestion').classList.add('d-none');">
                                    Keep mine
                                </button>
                            </div>
                        </div>`;
                } else {
                    suggestionBox.classList.add('d-none');
                }
            } catch (e) { suggestionBox.classList.add('d-none'); }
        }, 600);
    });
});

function selectTagInList(tagId) {
    const select = document.getElementById('tags-select');
    if (!select) return;
    const option = select.querySelector(`option[value="${tagId}"]`);
    if (option) option.selected = true;
    showToast('Tag added to selection.', 'success');
}

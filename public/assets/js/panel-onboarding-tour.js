(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let uiBound = false;
    let activeTarget = null;
    let activeFocus = null;

    function post(url) {
        if (!url) {
            return Promise.resolve();
        }

        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).catch(function () {
            return null;
        });
    }

    function ensureOverlay() {
        let overlay = document.getElementById('panel-tour-overlay');

        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'panel-tour-overlay';
            overlay.className = 'panel-tour-overlay';

            const spotlight = document.createElement('div');
            spotlight.id = 'panel-tour-spotlight';
            spotlight.className = 'panel-tour-spotlight';
            overlay.appendChild(spotlight);

            document.body.appendChild(overlay);
        }

        return overlay;
    }

    function getSpotlight() {
        return document.getElementById('panel-tour-spotlight');
    }

    function hideSpotlight() {
        const spotlight = getSpotlight();

        if (spotlight) {
            spotlight.classList.remove('is-visible');
        }

        activeTarget = null;
        activeFocus = null;
    }

    function findTarget(selector) {
        if (!selector) {
            return null;
        }

        const parts = String(selector).split(',').map(function (s) {
            return s.trim();
        });

        for (let i = 0; i < parts.length; i++) {
            const el = document.querySelector(parts[i]);

            if (el) {
                return el;
            }
        }

        return null;
    }

    function collapseAllMenuItems() {
        const menuRoot = document.querySelector('#layout-menu');

        if (!menuRoot) {
            return;
        }

        menuRoot.querySelectorAll('.menu-item.open').forEach(function (item) {
            item.classList.remove('open');
        });
    }

    function openMenuByKey(key) {
        if (!key) {
            return;
        }

        collapseAllMenuItems();

        const menuItem = document.querySelector('[data-menu-key="' + key + '"]');

        if (menuItem) {
            menuItem.classList.add('open');
        }
    }

    function focusMenuLink(element) {
        if (!element) {
            return null;
        }

        const menuItem = element.matches('[data-menu-key]')
            ? element
            : element.closest('[data-menu-key]');

        if (!menuItem) {
            return element;
        }

        return menuItem.querySelector(':scope > .menu-link') || menuItem;
    }

    function activateTabTrigger(selector) {
        if (!selector) {
            return;
        }

        const el = document.querySelector(selector);

        if (!el) {
            return;
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(el).show();
        } else {
            el.click();
        }
    }

    function getFixedHeaderOffset() {
        const navbar = document.getElementById('layout-navbar');

        if (!navbar) {
            return 20;
        }

        const style = window.getComputedStyle(navbar);

        if (style.position === 'fixed' || style.position === 'sticky') {
            return navbar.getBoundingClientRect().height + 16;
        }

        return 20;
    }

    function isElementInViewport(element, headerOffset) {
        const rect = element.getBoundingClientRect();
        const topBound = headerOffset + 12;
        const bottomBound = window.innerHeight - 32;

        return rect.top >= topBound && rect.bottom <= bottomBound;
    }

    function findScrollRoot(element) {
        let node = element && element.parentElement;

        while (node && node !== document.body) {
            const style = window.getComputedStyle(node);
            const overflowY = style.overflowY;

            if ((overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'overlay')
                && node.scrollHeight > node.clientHeight + 2) {
                return node;
            }

            node = node.parentElement;
        }

        return document.scrollingElement || document.documentElement;
    }

    function isDocumentScrollRoot(scrollRoot) {
        return scrollRoot === document.documentElement
            || scrollRoot === document.body
            || scrollRoot === document.scrollingElement;
    }

    function getScrollTop(scrollRoot) {
        if (isDocumentScrollRoot(scrollRoot)) {
            return window.pageYOffset || document.documentElement.scrollTop || 0;
        }

        return scrollRoot.scrollTop;
    }

    function setScrollTop(scrollRoot, top, behavior) {
        const scrollBehavior = behavior || 'auto';

        if (isDocumentScrollRoot(scrollRoot)) {
            window.scrollTo({ top: top, behavior: scrollBehavior });
            return;
        }

        scrollRoot.scrollTo({ top: top, behavior: scrollBehavior });
    }

    function getViewportHeight(scrollRoot) {
        return isDocumentScrollRoot(scrollRoot) ? window.innerHeight : scrollRoot.clientHeight;
    }

    function clearTargetHighlight() {
        document.querySelectorAll('.panel-tour-highlight').forEach(function (node) {
            node.classList.remove('panel-tour-highlight');
        });
    }

    function waitForScrollSettle(scrollRoot, callback) {
        let lastY = getScrollTop(scrollRoot);
        let stableFrames = 0;
        const startedAt = Date.now();

        const tick = function () {
            const currentY = getScrollTop(scrollRoot);

            if (Math.abs(currentY - lastY) < 2) {
                stableFrames += 1;
            } else {
                stableFrames = 0;
                lastY = currentY;
            }

            if (stableFrames >= 6 || Date.now() - startedAt > 2200) {
                window.setTimeout(callback, 120);
                return;
            }

            window.requestAnimationFrame(tick);
        };

        window.setTimeout(tick, 80);
    }

    function applyWindowScrollTop(top) {
        const value = Math.max(0, top);
        window.scrollTo(0, value);
        document.documentElement.scrollTop = value;
        document.body.scrollTop = value;
    }

    function markTargetHighlight(element) {
        clearTargetHighlight();

        if (element && !element.closest('#layout-menu')) {
            element.classList.add('panel-tour-highlight');
        }
    }

    function layoutStep(card, focus) {
        const highlightEl = focus && focus.highlightTarget ? focus.highlightTarget : null;
        const positionEl = highlightEl || (focus && focus.scrollTarget ? focus.scrollTarget : null);

        markTargetHighlight(highlightEl);
        activeFocus = focus;
        activeTarget = highlightEl || positionEl;
        updateSpotlight(highlightEl || positionEl);
        positionCard(card, positionEl);
    }

    function relayoutStep(card, focus) {
        if (!focus || (!focus.highlightTarget && !focus.scrollTarget)) {
            layoutStep(card, null);
            return;
        }

        window.requestAnimationFrame(function () {
            layoutStep(card, focus);
        });
    }

    function getTourFocusTargets(element) {
        if (!element) {
            return { scrollTarget: null, highlightTarget: null };
        }

        if (element.closest('#layout-menu')) {
            return { scrollTarget: element, highlightTarget: element };
        }

        return { scrollTarget: element, highlightTarget: element };
    }

    function scrollToElement(element, scrollMode) {
        return new Promise(function (resolve) {
            if (!element || element.closest('#layout-menu')) {
                resolve();
                return;
            }

            const mode = scrollMode || 'center';
            const headerOffset = getFixedHeaderOffset();
            const scrollRoot = findScrollRoot(element);
            const viewportHeight = getViewportHeight(scrollRoot);

            if (mode === 'nearest' && isElementInViewport(element, headerOffset)) {
                resolve();
                return;
            }

            const block = mode === 'start' || mode === 'top' ? 'start' : 'center';

            if (!isDocumentScrollRoot(scrollRoot)) {
                const rootRect = scrollRoot.getBoundingClientRect();
                const rect = element.getBoundingClientRect();
                const offsetInRoot = rect.top - rootRect.top + scrollRoot.scrollTop;
                const elementHeight = Math.min(Math.max(rect.height, 48), viewportHeight * 0.7);
                let targetTop;

                if (block === 'start') {
                    targetTop = offsetInRoot - 16;
                } else {
                    targetTop = offsetInRoot - (viewportHeight / 2) + (elementHeight / 2);
                }

                targetTop = Math.max(0, Math.min(targetTop, scrollRoot.scrollHeight - viewportHeight));
                scrollRoot.scrollTop = targetTop;
                waitForScrollSettle(scrollRoot, resolve);
                return;
            }

            element.scrollIntoView({ behavior: 'auto', block: block, inline: 'nearest' });

            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    const rect = element.getBoundingClientRect();
                    const currentY = getScrollTop(scrollRoot);
                    let targetY = currentY;

                    if (block === 'start') {
                        targetY = currentY + rect.top - headerOffset - 20;
                    } else {
                        const visibleHeight = Math.min(Math.max(rect.height, 56), viewportHeight * 0.55);
                        const idealTop = Math.max(headerOffset + 12, (viewportHeight - visibleHeight) / 2);
                        targetY = currentY + rect.top - idealTop;
                    }

                    const maxScroll = Math.max(0, document.documentElement.scrollHeight - viewportHeight);
                    targetY = Math.max(0, Math.min(targetY, maxScroll));

                    if (Math.abs(targetY - currentY) > 4) {
                        applyWindowScrollTop(targetY);
                    }

                    waitForScrollSettle(scrollRoot, function () {
                        const finalRect = element.getBoundingClientRect();
                        const stillHidden = finalRect.top < headerOffset
                            || finalRect.bottom > viewportHeight - 24;

                        if (stillHidden) {
                            const retryY = getScrollTop(scrollRoot)
                                + finalRect.top
                                - Math.max(headerOffset + 20, (viewportHeight - Math.min(finalRect.height, 120)) / 2);
                            applyWindowScrollTop(retryY);
                            waitForScrollSettle(scrollRoot, resolve);
                            return;
                        }

                        resolve();
                    });
                });
            });
        });
    }

    function resolveTarget(selector, expand, openMenu, activateTab, scrollMode) {
        if (openMenu) {
            openMenuByKey(openMenu);
        }

        if (activateTab) {
            activateTabTrigger(activateTab);
        }

        let element = findTarget(selector);

        if (!element) {
            return Promise.resolve({ scrollTarget: null, highlightTarget: null });
        }

        if (element.closest('#layout-menu')) {
            if (!openMenu) {
                collapseAllMenuItems();
            }
            element = focusMenuLink(element);
            return Promise.resolve({ scrollTarget: element, highlightTarget: element });
        }

        if (expand) {
            const toggle = element.closest('.menu-item')?.querySelector('.menu-toggle');

            if (toggle && !element.closest('.menu-item')?.classList.contains('open')) {
                toggle.click();
            }
        }

        const focus = getTourFocusTargets(element);

        return scrollToElement(focus.scrollTarget || element, scrollMode).then(function () {
            return focus;
        });
    }

    function updateSpotlight(targetEl) {
        const spotlight = getSpotlight();

        if (!spotlight) {
            return;
        }

        if (!targetEl) {
            spotlight.classList.remove('is-visible');
            activeTarget = null;
            return;
        }

        activeTarget = targetEl;
        spotlight.classList.add('is-snapping');

        const rect = targetEl.getBoundingClientRect();
        const pad = 10;
        const radius = window.getComputedStyle(targetEl).borderRadius;
        const numericRadius = parseFloat(radius);

        spotlight.style.top = Math.max(0, rect.top - pad) + 'px';
        spotlight.style.left = Math.max(0, rect.left - pad) + 'px';
        spotlight.style.width = Math.max(24, rect.width + pad * 2) + 'px';
        spotlight.style.height = Math.max(24, rect.height + pad * 2) + 'px';
        spotlight.style.borderRadius = Number.isFinite(numericRadius) && numericRadius > 0
            ? (numericRadius + pad) + 'px'
            : '.75rem';
        spotlight.classList.add('is-visible');

        window.requestAnimationFrame(function () {
            spotlight.classList.remove('is-snapping');
        });
    }

    function positionCard(card, targetEl) {
        const margin = 16;
        const gap = 14;
        const arrowSize = 10;
        const cardWidth = card.offsetWidth || 360;
        const cardHeight = card.offsetHeight || 200;

        if (!targetEl) {
            card.className = 'panel-tour-card';
            card.style.bottom = '2rem';
            card.style.top = '';
            card.style.left = '50%';
            card.style.right = '';
            card.style.transform = 'translateX(-50%)';
            card.style.removeProperty('--arrow-offset');
            return;
        }

        const rect = targetEl.getBoundingClientRect();
        const inMenu = !!targetEl.closest('#layout-menu');
        const targetCenterX = rect.left + rect.width / 2;
        const targetCenterY = rect.top + rect.height / 2;

        let placement = 'bottom';
        let top = rect.bottom + gap + arrowSize;
        let left = targetCenterX - cardWidth / 2;

        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        const spaceLeft = rect.left;
        const spaceRight = window.innerWidth - rect.right;

        if (inMenu) {
            if (spaceLeft >= cardWidth + gap + margin) {
                placement = 'left';
                left = rect.left - cardWidth - gap - arrowSize;
                top = targetCenterY - cardHeight / 2;
            } else {
                placement = 'right';
                left = rect.right + gap + arrowSize;
                top = targetCenterY - cardHeight / 2;
            }
        } else if (spaceBelow < cardHeight + gap + margin && spaceAbove > spaceBelow) {
            placement = 'top';
            top = rect.top - cardHeight - gap - arrowSize;
            left = targetCenterX - cardWidth / 2;
        } else if (spaceRight < cardWidth / 2 + margin && spaceLeft > spaceRight) {
            placement = 'left';
            left = rect.left - cardWidth - gap - arrowSize;
            top = targetCenterY - cardHeight / 2;
        } else if (spaceLeft < cardWidth / 2 + margin) {
            placement = 'right';
            left = rect.right + gap + arrowSize;
            top = targetCenterY - cardHeight / 2;
        }

        top = Math.max(margin, Math.min(top, window.innerHeight - cardHeight - margin));
        left = Math.max(margin, Math.min(left, window.innerWidth - cardWidth - margin));

        let arrowOffset;

        if (placement === 'left' || placement === 'right') {
            arrowOffset = Math.max(28, Math.min(cardHeight - 28, targetCenterY - top));
            card.style.setProperty('--arrow-offset', arrowOffset + 'px');
        } else {
            arrowOffset = Math.max(28, Math.min(cardWidth - 28, targetCenterX - left));
            card.style.setProperty('--arrow-offset', arrowOffset + 'px');
        }

        card.className = 'panel-tour-card panel-tour-card--arrow-' + placement;
        card.style.bottom = '';
        card.style.transform = 'none';
        card.style.top = top + 'px';
        card.style.left = left + 'px';
    }

    function showStep(step, index, total, onNext, onSkip) {
        const overlay = ensureOverlay();
        let card = overlay.querySelector('.panel-tour-card');

        if (!card) {
            card = document.createElement('div');
            card.className = 'panel-tour-card';
            overlay.appendChild(card);
        }

        card.innerHTML =
            '<div class="panel-tour-card__arrow" aria-hidden="true"></div>' +
            '<div class="panel-tour-card__head">' +
            '<span class="panel-tour-progress">مرحله ' + (index + 1) + ' از ' + total + '</span>' +
            '<button type="button" class="panel-tour-close" aria-label="بستن">' + (window.uiIcon ? window.uiIcon('x') : '') + '</button>' +
            '</div>' +
            '<h5 class="panel-tour-title">' + step.title + '</h5>' +
            '<p class="panel-tour-text">' + step.text + '</p>' +
            '<div class="panel-tour-actions">' +
            '<button type="button" class="btn btn-label-secondary btn-sm panel-tour-skip">رد کردن</button>' +
            '<button type="button" class="btn btn-primary btn-sm panel-tour-next">' +
            (index + 1 === total ? 'پایان تور' : 'بعدی') +
            ' ' + (window.uiIcon ? window.uiIcon('arrow-left') : '') + '</button></div>';

        overlay.classList.remove('is-active');
        document.body.classList.remove('panel-tour-active');
        hideSpotlight();
        clearTargetHighlight();

        const bindStepActions = function () {
            card.querySelector('.panel-tour-next').addEventListener('click', onNext);
            card.querySelector('.panel-tour-skip').addEventListener('click', onSkip);
            card.querySelector('.panel-tour-close').addEventListener('click', onSkip);
        };

        const presentStep = function (focus) {
            overlay.classList.add('is-active');
            document.body.classList.add('panel-tour-active');
            layoutStep(card, focus);
            bindStepActions();

            if (focus && (focus.highlightTarget || focus.scrollTarget)) {
                window.setTimeout(function () {
                    relayoutStep(card, focus);
                }, 180);
            }
        };

        const scrollMode = step.scroll || 'center';

        if (step.target) {
            resolveTarget(step.target, step.expand, step.openMenu, step.activateTab, scrollMode).then(presentStep);
        } else {
            presentStep(null);
        }

        if (!overlay.dataset.resizeBound) {
            overlay.dataset.resizeBound = '1';

            window.addEventListener('resize', function () {
                if (!overlay.classList.contains('is-active')) {
                    return;
                }

                const cardEl = overlay.querySelector('.panel-tour-card');

                if (cardEl && activeFocus) {
                    layoutStep(cardEl, activeFocus);
                }
            });
        }
    }

    function closeTour() {
        hideSpotlight();
        clearTargetHighlight();
        activeFocus = null;
        collapseAllMenuItems();
        document.body.classList.remove('panel-tour-active');

        const overlay = document.getElementById('panel-tour-overlay');

        if (overlay) {
            overlay.classList.remove('is-active');
            const card = overlay.querySelector('.panel-tour-card');

            if (card) {
                card.remove();
            }
        }
    }

    function purgeModalDom() {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');

        document.querySelectorAll('.modal-backdrop').forEach(function (node) {
            node.remove();
        });

        const welcomeModal = document.getElementById('panelWelcomeModal');

        if (welcomeModal) {
            welcomeModal.classList.remove('show');
            welcomeModal.style.display = 'none';
            welcomeModal.setAttribute('aria-hidden', 'true');
            welcomeModal.removeAttribute('aria-modal');
        }
    }

    function cleanupModal(callback) {
        const welcomeModal = document.getElementById('panelWelcomeModal');
        const done = function () {
            purgeModalDom();

            if (typeof callback === 'function') {
                callback();
            }
        };

        if (!welcomeModal || !welcomeModal.classList.contains('show')) {
            done();
            return;
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const instance = bootstrap.Modal.getInstance(welcomeModal) || bootstrap.Modal.getOrCreateInstance(welcomeModal);
            let finished = false;

            const finish = function () {
                if (finished) {
                    return;
                }

                finished = true;
                welcomeModal.removeEventListener('hidden.bs.modal', finish);
                done();
            };

            welcomeModal.addEventListener('hidden.bs.modal', finish);
            instance.hide();
            window.setTimeout(finish, 400);
            return;
        }

        done();
    }

    function buildMenuSteps() {
        const texts = window.panelTourConfig?.menuTexts || {};
        const steps = [];
        const menuRoot = document.querySelector('#layout-menu');

        if (!menuRoot) {
            return steps;
        }

        menuRoot.querySelectorAll('[data-menu-key]').forEach(function (el) {
            const key = el.getAttribute('data-menu-key');
            const meta = texts[key] || {};
            const labelEl = el.querySelector('.menu-link > div');
            const label = labelEl ? labelEl.textContent.trim() : '';

            steps.push({
                title: meta.title || label || key,
                text: meta.text || ('دسترسی به بخش «' + (label || key) + '» از منوی کناری.'),
                target: '[data-menu-key="' + key + '"] > .menu-link, [data-menu-key="' + key + '"]',
                expand: false,
            });
        });

        return steps;
    }

    function resolveSteps(rawSteps) {
        const steps = [];
        const list = Array.isArray(rawSteps) ? rawSteps : [];

        list.forEach(function (step) {
            if (!step.optional || findTarget(step.target)) {
                steps.push(step);
            }
        });

        return steps;
    }

    function startTour(rawSteps, options) {
        const opts = options || {};
        const steps = resolveSteps(rawSteps);

        if (!steps.length) {
            if (window.DokanToast) {
                DokanToast.info('مرحله‌ای برای نمایش در این صفحه یافت نشد.');
            }
            return false;
        }

        closeTour();

        let index = 0;

        const finish = function () {
            closeTour();

            if (opts.markOnboardingComplete && window.panelTourConfig?.onboardingRoutes?.tour) {
                post(window.panelTourConfig.onboardingRoutes.tour).then(function () {
                    if (window.DokanToast) {
                        DokanToast.success('تور آموزشی با موفقیت به پایان رسید.');
                    }
                });
            } else if (opts.showDoneToast && window.DokanToast) {
                DokanToast.success('تور این صفحه به پایان رسید.');
            }
        };

        const next = function () {
            index += 1;

            if (index >= steps.length) {
                finish();
                return;
            }

            showStep(steps[index], index, steps.length, next, skip);
        };

        const skip = function () {
            closeTour();

            if (opts.markOnboardingComplete && window.panelTourConfig?.onboardingRoutes?.tour) {
                post(window.panelTourConfig.onboardingRoutes.tour);
            }
        };

        showStep(steps[0], 0, steps.length, next, skip);
        return true;
    }

    function startFullMenuTour(options) {
        const dashboard = window.panelTourConfig?.dashboardIntro || [];
        const menuSteps = buildMenuSteps();

        return startTour(dashboard.concat(menuSteps), Object.assign({
            markOnboardingComplete: true,
            showDoneToast: true,
        }, options || {}));
    }

    function startPageTour() {
        const pageSteps = window.panelTourConfig?.pageSteps || [];
        const intro = (window.panelTourConfig?.intro || []).filter(function (step) {
            return !step.optional || findTarget(step.target);
        });

        return startTour(intro.concat(pageSteps), { showDoneToast: true });
    }

    function beginFromWelcome() {
        const routes = window.panelTourConfig?.onboardingRoutes || {};
        post(routes.welcome);

        cleanupModal(function () {
            window.setTimeout(function () {
                startFullMenuTour();
            }, 150);
        });
    }

    function dismissWelcome() {
        const routes = window.panelTourConfig?.onboardingRoutes || {};
        post(routes.welcome);
        cleanupModal();
    }

    function shouldAutoShowWelcome() {
        const cfg = window.panelTourConfig || {};
        const el = document.getElementById('panelWelcomeModal');

        if (!el) {
            return false;
        }

        if (cfg.showWelcomeModal === true) {
            return true;
        }

        const flag = el.getAttribute('data-panel-welcome-show');
        return flag === '1' || flag === 'true';
    }

    function showWelcomeModal() {
        const el = document.getElementById('panelWelcomeModal');

        if (!shouldAutoShowWelcome()) {
            return;
        }

        bindWelcomeModalButtons();

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }

        el.classList.add('show');
        el.style.display = 'block';
        el.setAttribute('aria-modal', 'true');
        el.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');

        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    function bindWelcomeModalButtons() {
        const tourBtn = document.getElementById('panel-welcome-tour-btn');
        const skipBtn = document.getElementById('panel-welcome-skip-btn');
        const closeBtn = document.getElementById('panel-welcome-close-btn');

        if (tourBtn && !tourBtn.dataset.panelTourBound) {
            tourBtn.dataset.panelTourBound = '1';
            tourBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                beginFromWelcome();
            });
        }

        [skipBtn, closeBtn].forEach(function (btn) {
            if (!btn || btn.dataset.panelTourBound) {
                return;
            }

            btn.dataset.panelTourBound = '1';
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dismissWelcome();
            });
        });
    }

    function isDashboardPage() {
        const cfg = window.panelTourConfig || {};
        return cfg.route === 'index' || cfg.isDashboard === true;
    }

    function startHeaderTour() {
        if (isDashboardPage()) {
            startFullMenuTour();
            return;
        }

        startPageTour();
    }

    function bindUi() {
        if (uiBound) {
            return;
        }

        uiBound = true;
        bindWelcomeModalButtons();

        document.addEventListener('click', function (e) {
            const headerTour = e.target.closest('#panel-tour-trigger');

            if (headerTour) {
                e.preventDefault();
                startHeaderTour();
            }
        });

        document.getElementById('panel-setup-complete-btn')?.addEventListener('click', function () {
            const routes = window.panelTourConfig?.onboardingRoutes || {};

            post(routes.complete).then(function (response) {
                if (!response || !response.json) {
                    return null;
                }

                return response.json();
            }).then(function (payload) {
                if (payload && window.DokanToast) {
                    DokanToast.success(payload.message || 'راه‌اندازی تکمیل شد.');
                }

                window.setTimeout(function () {
                    window.location.reload();
                }, 900);
            });
        });
    }

    function init() {
        if (!window.panelTourConfig) {
            return;
        }

        bindUi();

        if (shouldAutoShowWelcome()) {
            showWelcomeModal();
        }

        if (window.panelTourConfig.autoStartTour) {
            window.setTimeout(function () {
                startFullMenuTour();
            }, 500);
        }
    }

    window.PanelTour = {
        close: closeTour,
        start: startTour,
        startPage: startPageTour,
        startFullMenu: startFullMenuTour,
        startHeader: startHeaderTour,
        beginFromWelcome: beginFromWelcome,
        showWelcome: showWelcomeModal,
        buildMenuSteps: buildMenuSteps,
    };

    window.PanelOnboardingTour = window.PanelTour;

    init();
})();

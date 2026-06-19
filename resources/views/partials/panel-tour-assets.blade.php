@auth
    @include('partials.ui-icons-runtime')
    @once('panel-tour-assets')
    <style>
        #panel-tour-trigger { color: #543C92 !important; }
        #panel-tour-trigger:hover { color: #3f2d6f !important; background: rgba(84, 60, 146, .08); border-radius: .5rem; }

        [id^="tour-setting-"],
        [id^="tour-feature-"],
        [id^="tour-settings-group-"],
        #tour-settings-features,
        #tour-settings-save {
            scroll-margin-top: 6.5rem;
            scroll-margin-bottom: 2rem;
        }

        .panel-tour-overlay {
            position: fixed;
            inset: 0;
            z-index: 20000;
            display: none;
            pointer-events: none;
            isolation: isolate;
        }
        .panel-tour-overlay.is-active {
            display: block;
            pointer-events: auto;
        }

        /* سوراخ spotlight — فقط اطراف تاریک می‌شود */
        .panel-tour-spotlight {
            position: fixed;
            display: none;
            border-radius: .75rem;
            pointer-events: none;
            z-index: 1;
            box-shadow:
                0 0 0 3px rgba(255,255,255,.95),
                0 0 0 9999px rgba(15,13,28,.72);
            transition: top .18s ease, left .18s ease, width .18s ease, height .18s ease, border-radius .18s ease;
        }
        .panel-tour-spotlight.is-snapping {
            transition: none !important;
        }
        .panel-tour-spotlight.is-visible {
            display: block;
        }

        .panel-tour-highlight {
            position: relative;
            z-index: 2 !important;
            outline: 3px solid #fff !important;
            outline-offset: 3px;
            border-radius: .65rem;
            box-shadow:
                0 0 0 6px rgba(84, 60, 146, .45),
                0 12px 32px rgba(84, 60, 146, .25) !important;
            isolation: isolate;
        }

        .panel-tour-card {
            position: fixed;
            width: min(380px, calc(100vw - 2rem));
            background: #fff;
            border-radius: 1rem;
            padding: 1.15rem 1.3rem 1.2rem;
            z-index: 10;
            box-shadow: 0 20px 50px rgba(20,18,32,.28);
            border: 1px solid rgba(82,69,149,.14);
            transition: top .22s ease, left .22s ease;
        }
        .panel-tour-card__arrow {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #fff;
            border: 1px solid rgba(82,69,149,.14);
            transform: rotate(45deg);
            pointer-events: none;
        }
        .panel-tour-card--arrow-bottom .panel-tour-card__arrow {
            top: -7px;
            left: var(--arrow-offset, 50%);
            margin-left: -7px;
            border-bottom: 0;
            border-right: 0;
        }
        .panel-tour-card--arrow-top .panel-tour-card__arrow {
            bottom: -7px;
            left: var(--arrow-offset, 50%);
            margin-left: -7px;
            border-top: 0;
            border-left: 0;
        }
        .panel-tour-card--arrow-left .panel-tour-card__arrow {
            right: -7px;
            top: var(--arrow-offset, 50%);
            margin-top: -7px;
            border-left: 0;
            border-bottom: 0;
        }
        .panel-tour-card--arrow-right .panel-tour-card__arrow {
            left: -7px;
            top: var(--arrow-offset, 50%);
            margin-top: -7px;
            border-right: 0;
            border-top: 0;
        }

        .panel-tour-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .65rem; }
        .panel-tour-progress { font-size: .78rem; font-weight: 700; color: #524595; background: rgba(82,69,149,.08); padding: .25rem .65rem; border-radius: 999px; }
        .panel-tour-close { display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border: 0; border-radius: .5rem; background: #f4f3f8; color: #6e6b7b; cursor: pointer; }
        .panel-tour-close .ui-icon { width: 1.1rem; height: 1.1rem; }
        .panel-tour-next .ui-icon { width: 1rem; height: 1rem; vertical-align: -0.12em; }
        .panel-tour-title { margin: 0 0 .5rem; font-size: 1.05rem; font-weight: 800; color: #2f2b3d; }
        .panel-tour-text { margin: 0 0 1rem; font-size: .88rem; line-height: 1.85; color: #6e6b7b; }
        .panel-tour-actions { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    </style>
    @php
        $tourService = app(\App\Services\PanelTourService::class);
        $tourPayload = $tourService->tourPayload();
    @endphp
    <script>
        window.panelTourConfig = {
            menuTexts: @json($tourPayload['menuTexts']),
            pageSteps: @json($tourPayload['pageSteps']),
            intro: @json($tourService->introSteps()),
            dashboardIntro: @json($tourService->fullDashboardTourSteps()),
            route: @json($tourPayload['route']),
            onboardingRoutes: @json($tourPayload['onboardingRoutes']),
            autoStartTour: @json(isset($panelOnboarding) && !empty($panelOnboarding['show_tour']) && empty($panelOnboarding['show_welcome_modal'])),
            showWelcomeModal: @json(!empty($panelOnboarding['show_welcome_modal'])),
            isDashboard: @json(request()->routeIs('index')),
        };
    </script>
    @endonce
@endauth

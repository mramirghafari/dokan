@if (!empty($panelOnboarding['show_setup_card']))
    @php
        $percent = (int) ($panelOnboarding['completion_percent'] ?? 0);
        $doneCount = collect($panelOnboarding['steps'] ?? [])->where('done', true)->count();
        $remainCount = collect($panelOnboarding['steps'] ?? [])->where('done', false)->count();
        $firstPending = collect($panelOnboarding['steps'] ?? [])->first(fn ($s) => empty($s['done']));
    @endphp

    <div class="ob-shell">
        <section class="ob-hero">
            <div class="ob-hero__inner">
                <div class="ob-hero__badge">
                    <x-ui.icon name="sparkles" />
                    <span>راه‌اندازی هوشمند پنل</span>
                </div>
                <div class="ob-hero__summary">
                    <div class="ob-hero__col ob-hero__col--right">
                        <h2 class="ob-hero__title">راه‌اندازی {{ $panelOnboarding['panel_name'] ?? 'پنل شما' }}</h2>
                        <p class="ob-hero__text">
                            این چک‌لیست اختصاصی، تنظیمات ضروری پنل — از تیم و محصولات تا فروش، انبار و مالی —
                            را گام‌به‌گام دنبال می‌کند. تا پایان راه‌اندازی، ویجت‌های عملیاتی داشبورد مخفی می‌مانند.
                        </p>
                        <div class="ob-hero__progress-wrap">
                            <div class="ob-hero__progress-label">
                                <span>پیشرفت کلی</span>
                                <span>{{ $percent }}٪ تکمیل شده</span>
                            </div>
                            <div class="progress ob-hero__progress" role="progressbar"
                                aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $percent }}%">{{ $percent }}٪</div>
                            </div>
                        </div>
                        <div class="ob-hero__actions">
                            <a href="{{ route('setup-guide.index') }}" class="btn ob-btn ob-btn--primary">
                                <x-ui.icon name="map-2" class="me-1" />
                                راهنمای مرحله‌ای راه‌اندازی
                            </a>

                            @if ($percent >= 100)
                                <button type="button" class="btn ob-btn ob-btn--accent" id="panel-setup-complete-btn">
                                    <x-ui.icon name="layout-dashboard" class="me-1" />
                                    فعال‌سازی داشبورد کامل
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="ob-hero__col ob-hero__col--left">
                        <div class="ob-hero__graphic" aria-hidden="true">
                            <svg viewBox="0 0 220 120" xmlns="http://www.w3.org/2000/svg" focusable="false">
                                <g fill="none" fill-rule="evenodd">
                                    <circle cx="40" cy="30" r="18" fill="rgba(255,255,255,.22)" />
                                    <path d="M100 20h72c8 0 14 6 14 14v28c0 8-6 14-14 14h-72c-8 0-14-6-14-14V34c0-8 6-14 14-14z"
                                        fill="rgba(255,255,255,.17)" />
                                    <path d="M112 35h42M112 49h28M112 63h50" stroke="rgba(255,255,255,.62)" stroke-width="4"
                                        stroke-linecap="round" />
                                    <path d="M39 61l16 16 34-34" stroke="#F9BA16" stroke-width="8" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <rect x="18" y="82" width="74" height="18" rx="9" fill="rgba(249,186,22,.24)" />
                                </g>
                            </svg>
                        </div>
                        <div class="ob-hero__side">
                            <ul class="ob-hero__stats list-unstyled mb-0">
                                <li>
                                    <span class="ob-hero__stat-label">
                                        <x-ui.icon name="circle-check" />
                                        انجام‌شده
                                    </span>
                                    <strong>{{ $doneCount }}</strong>
                                </li>
                                <li>
                                    <span class="ob-hero__stat-label">
                                        <x-ui.icon name="clock" />
                                        باقی‌مانده
                                    </span>
                                    <strong>{{ $remainCount }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="ob-grid">
            @foreach ($panelOnboarding['steps'] as $index => $step)
                @php
                    $isDone = !empty($step['done']);
                    $isActive = !$isDone && ($firstPending['key'] ?? null) === ($step['key'] ?? null);
                    $stateClass = $isDone ? 'ob-step--done' : ($isActive ? 'ob-step--active' : '');
                @endphp
                <article class="ob-step {{ $stateClass }}">
                    <div class="ob-step__num">
                        @if ($isDone)
                            <x-ui.icon name="check" />
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="ob-step__main">
                        <div class="ob-step__head">
                            <div class="ob-step__icon"><x-ui.icon :name="$step['icon']" /></div>
                            <div>
                                <h5>{{ $step['title'] }}</h5>
                                <p>{{ $step['description'] }}</p>
                            </div>
                        </div>
                        <div class="ob-step__foot">
                            @if ($isDone)
                                <span class="ob-badge ob-badge--done"><x-ui.icon name="check" /> تکمیل شد</span>
                                <a href="{{ $step['route'] }}" class="ob-link">بازبینی</a>
                            @elseif ($isActive)
                                <span class="ob-badge ob-badge--active"><x-ui.icon name="bolt" /> مرحله فعلی</span>
                                <a href="{{ $step['route'] }}" class="ob-link ob-link--primary">
                                    شروع <x-ui.icon name="arrow-left" />
                                </a>
                            @else
                                <span class="ob-badge ob-badge--wait"><x-ui.icon name="clock" /> در انتظار</span>
                                <a href="{{ $step['route'] }}" class="ob-link">مشاهده</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endif

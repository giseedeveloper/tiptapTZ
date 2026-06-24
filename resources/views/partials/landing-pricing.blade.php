{{-- Pricing with monthly/annual toggle, business anchor, and enterprise "starts from" --}}
@php
    $pricing = $landing['pricing'];
    $hasBillablePlans = $plans->contains(fn ($plan) => $plan->supportsAnnualBilling());
@endphp

<section id="pricing" class="py-20 lg:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-5">
        <div class="text-center mb-10 reveal">
            <span class="section-label">Pricing</span>
            <h2 class="text-3xl lg:text-4xl font-light text-fin-ink mt-4">{{ $pricing['title'] }}</h2>
        </div>

        @if ($hasBillablePlans)
            <div class="flex flex-col items-center gap-2 mb-12 reveal">
                <div class="pricing-billing-toggle inline-flex items-center rounded-full border border-fin-ink/8 bg-fin-surface p-1 shadow-sm" role="group" aria-label="Billing period">
                    <button type="button" class="pricing-billing-btn is-active" data-billing="monthly">
                        {{ $pricing['billing_monthly'] }}
                    </button>
                    <button type="button" class="pricing-billing-btn" data-billing="annual">
                        {{ $pricing['billing_annual'] }}
                    </button>
                </div>
                <p class="pricing-annual-note hidden text-xs font-medium text-fin-primary-dark">
                    {{ $pricing['annual_promo'] }}
                    <span class="text-fin-muted">&middot; {{ $pricing['annual_savings'] }}</span>
                </p>
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto items-stretch">
            @forelse ($plans as $plan)
                @php
                    $isFeatured = $plan->is_featured;
                    $isBusiness = $plan->slug === $pricing['business_slug'];
                    $isEnterprise = $plan->isEnterprisePlan();
                    $supportsAnnual = $plan->supportsAnnualBilling();
                @endphp
                <div class="{{ $isFeatured ? 'pricing-featured scale-[1.02]' : 'fin-card' }} rounded-3xl p-8 flex flex-col relative reveal">
                    @if ($isFeatured)
                        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-full shadow-lg">Most popular</span>
                    @endif

                    <h3 class="font-semibold text-fin-ink text-lg">{{ $plan->name }}</h3>

                    @if ($isEnterprise)
                        <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.14em] text-fin-muted">{{ $pricing['enterprise_from_label'] }}</p>
                    @endif

                    <div class="mt-2" data-pricing-card data-supports-annual="{{ $supportsAnnual ? 'true' : 'false' }}">
                        <p class="pricing-price-monthly {{ $supportsAnnual ? '' : '' }}">
                            <span class="text-4xl font-light {{ $isFeatured ? 'text-hero-accent' : 'text-fin-ink' }} tracking-tight">{{ $plan->priceLabel() }}</span>
                            <span class="text-sm text-fin-muted ml-1">{{ $plan->periodLabel() }}</span>
                        </p>

                        @if ($supportsAnnual)
                            <p class="pricing-price-annual hidden">
                                <span class="text-4xl font-light {{ $isFeatured ? 'text-hero-accent' : 'text-fin-ink' }} tracking-tight">{{ $plan->annualPriceLabel() }}</span>
                                <span class="text-sm text-fin-muted ml-1">/ year</span>
                            </p>
                            <p class="pricing-price-annual-equiv hidden text-xs text-fin-muted mt-2">
                                Equivalent to {{ $plan->annualMonthlyEquivalentLabel() }} / month
                            </p>
                        @endif
                    </div>

                    @if ($isBusiness)
                        <p class="mt-3 text-xs leading-relaxed text-fin-muted border-l-2 border-fin-primary/30 pl-3">
                            {{ $pricing['business_anchor'] }}
                        </p>
                    @endif

                    @if ($isEnterprise)
                        <p class="mt-3 text-xs text-fin-muted">{{ $pricing['enterprise_note'] }}</p>
                    @endif

                    <ul class="mt-8 space-y-3.5 text-sm {{ $isFeatured ? 'text-fin-ink' : 'text-fin-muted' }} flex-1">
                        @foreach ($plan->features ?? [] as $feature)
                            <li class="flex gap-2.5 items-center">
                                <span class="w-5 h-5 rounded-full {{ $isFeatured ? 'bg-fin-primary/15' : 'bg-fin-mist' }} flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 {{ $isFeatured ? 'text-fin-primary-dark' : 'text-fin-primary' }}"></i>
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    @if ($isFeatured)
                        <a href="{{ route('restaurant.register') }}" class="btn-glow mt-8 block text-center rounded-xl py-3.5 text-sm font-bold text-white">Get {{ $plan->name }}</a>
                    @else
                        <a href="{{ route('restaurant.register') }}" class="mt-8 block text-center rounded-xl border-2 border-fin-ink/8 py-3.5 text-sm font-bold text-fin-ink hover:bg-fin-surface transition-colors">
                            {{ $plan->isFree() ? 'Start trial' : ($isEnterprise ? 'Contact sales' : 'Get '.$plan->name) }}
                        </a>
                    @endif
                </div>
            @empty
                <div class="fin-card rounded-3xl p-8 flex flex-col reveal md:col-span-3 text-center">
                    <h3 class="font-semibold text-fin-ink text-lg">Plans coming soon</h3>
                    <p class="mt-3 text-sm text-fin-muted">Start your free trial today and pick a plan once you're set up.</p>
                    <a href="{{ route('restaurant.register') }}" class="btn-glow mt-6 inline-block mx-auto text-center rounded-xl px-8 py-3.5 text-sm font-bold text-white">Start free trial</a>
                </div>
            @endforelse
        </div>

        @include('partials.landing-pricing-nurture')
    </div>
</section>

@if ($hasBillablePlans)
    <script>
        (() => {
            const toggle = document.querySelector('.pricing-billing-toggle');
            if (!toggle) {
                return;
            }

            const annualNote = document.querySelector('.pricing-annual-note');
            const buttons = toggle.querySelectorAll('.pricing-billing-btn');
            const cards = document.querySelectorAll('[data-pricing-card][data-supports-annual="true"]');

            const setBilling = (period) => {
                buttons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.billing === period);
                });

                annualNote?.classList.toggle('hidden', period !== 'annual');

                cards.forEach((card) => {
                    card.querySelector('.pricing-price-monthly')?.classList.toggle('hidden', period === 'annual');
                    card.querySelector('.pricing-price-annual')?.classList.toggle('hidden', period !== 'annual');
                    card.querySelector('.pricing-price-annual-equiv')?.classList.toggle('hidden', period !== 'annual');
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => setBilling(button.dataset.billing));
            });
        })();
    </script>
@endif

{{-- tp:block
{
    "name": "Pricing Cards",
    "description": "Tiered pricing cards with optional featured plan.",
    "version": 1,
    "fields": [
        { "key": "title", "label": "Title", "type": "text" },
        { "key": "subtitle", "label": "Subtitle", "type": "textarea", "rows": 3 },
        { "key": "plans", "label": "Plans JSON", "type": "textarea", "rows": 12, "help": "JSON array of {key,name,price,period,description,features,cta_label,cta_url}." },
        { "key": "featured_key", "label": "Featured Plan Key", "type": "text" },
        {
            "key": "billing_mode",
            "label": "Billing Mode",
            "type": "select",
            "options": [
                { "value": "monthly", "label": "Monthly" },
                { "value": "yearly", "label": "Yearly" }
            ]
        }
    ],
    "defaults": {
        "title": "Simple pricing",
        "subtitle": "Pick the plan that matches your team.",
        "plans": [
            {
                "key": "starter",
                "name": "Starter",
                "price": "$19",
                "period": "/month",
                "description": "For solo builders and side projects.",
                "features": ["1 site", "Basic blocks", "Email support"],
                "cta_label": "Choose Starter",
                "cta_url": "#"
            },
            {
                "key": "pro",
                "name": "Pro",
                "price": "$49",
                "period": "/month",
                "description": "For teams shipping weekly campaigns.",
                "features": ["10 sites", "Advanced blocks", "Priority support"],
                "cta_label": "Choose Pro",
                "cta_url": "#"
            },
            {
                "key": "scale",
                "name": "Scale",
                "price": "$99",
                "period": "/month",
                "description": "For high-volume content operations.",
                "features": ["Unlimited sites", "Custom workflows", "Dedicated support"],
                "cta_label": "Contact Sales",
                "cta_url": "#"
            }
        ],
        "featured_key": "pro",
        "billing_mode": "monthly"
    }
}
--}}
@php
    $title = trim((string) ($props['title'] ?? ''));
    $subtitle = trim((string) ($props['subtitle'] ?? ''));
    $featuredKey = trim((string) ($props['featured_key'] ?? ''));
    $billingMode = trim((string) ($props['billing_mode'] ?? 'monthly'));

    $rawPlans = $props['plans'] ?? [];
    if (is_string($rawPlans)) {
        $trim = trim($rawPlans);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        $plans = is_array($decoded) ? $decoded : [];
    } elseif (is_array($rawPlans)) {
        $plans = $rawPlans;
    } else {
        $plans = [];
    }

    $plans = array_values(array_filter($plans, static fn ($plan): bool => is_array($plan) && trim((string) ($plan['name'] ?? '')) !== ''));
@endphp

<section class="py-14 sm:py-20">
    <div class="mx-auto max-w-7xl px-6">
        @if ($title !== '')
            <h2 class="text-center font-display text-3xl font-semibold text-surface-900 sm:text-5xl">{{ $title }}</h2>
        @endif
        @if ($subtitle !== '')
            <p class="mx-auto mt-3 max-w-2xl text-center text-pretty text-surface-600">{{ $subtitle }}</p>
        @endif

        @if ($plans === [])
            <div class="mt-8 rounded-2xl border border-dashed border-black/15 bg-white/70 p-6 text-center text-sm text-surface-500">
                Add plans in JSON to render pricing cards.
            </div>
        @else
            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($plans as $plan)
                    @php
                        $key = trim((string) ($plan['key'] ?? ''));
                        $name = trim((string) ($plan['name'] ?? ''));
                        $price = trim((string) ($plan['price'] ?? ''));
                        $period = trim((string) ($plan['period'] ?? ($billingMode === 'yearly' ? '/year' : '/month')));
                        $description = trim((string) ($plan['description'] ?? ''));
                        $features = $plan['features'] ?? [];
                        if (! is_array($features)) {
                            $features = [];
                        }
                        $features = array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $features), static fn ($item): bool => $item !== ''));
                        $ctaLabel = trim((string) ($plan['cta_label'] ?? 'Get started'));
                        $ctaUrl = trim((string) ($plan['cta_url'] ?? '#'));
                        $featured = $featuredKey !== '' && $key !== '' && $featuredKey === $key;
                    @endphp

                    <article class="rounded-2xl border p-6 {{ $featured ? 'border-surface-900 bg-surface-900 text-white shadow-lg' : 'border-black/8 bg-white text-surface-900 shadow-sm' }}">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="font-display text-2xl font-semibold">{{ $name }}</h3>
                            @if ($featured)
                                <span class="rounded-full border border-white/30 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]">Popular</span>
                            @endif
                        </div>

                        @if ($price !== '')
                            <div class="flex items-end gap-1">
                                <span class="text-4xl font-semibold">{{ $price }}</span>
                                <span class="pb-1 text-sm {{ $featured ? 'text-white/75' : 'text-surface-500' }}">{{ $period }}</span>
                            </div>
                        @endif

                        @if ($description !== '')
                            <p class="mt-3 text-sm {{ $featured ? 'text-white/80' : 'text-surface-600' }}">{{ $description }}</p>
                        @endif

                        @if ($features !== [])
                            <ul class="mt-5 space-y-2 text-sm">
                                @foreach ($features as $feature)
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full {{ $featured ? 'bg-white/80' : 'bg-surface-400' }}"></span>
                                        <span class="{{ $featured ? 'text-white/90' : 'text-surface-700' }}">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a
                            href="{{ $ctaUrl }}"
                            class="mt-6 inline-flex w-full items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $featured ? 'bg-white text-surface-900 hover:bg-white/90' : 'bg-surface-900 text-white hover:opacity-85' }}">
                            {{ $ctaLabel }}
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

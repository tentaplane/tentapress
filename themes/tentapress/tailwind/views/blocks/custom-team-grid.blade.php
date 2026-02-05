{{-- tp:block
{
    "name": "Team Grid",
    "description": "Grid of team members with name, role, bio, photo, and profile link.",
    "version": 1,
    "fields": [
        { "key": "title", "label": "Title", "type": "text" },
        { "key": "intro", "label": "Intro", "type": "textarea", "rows": 3 },
        { "key": "members", "label": "Members JSON", "type": "textarea", "rows": 12, "help": "JSON array of {name,role,bio,photo,profile_url}." },
        {
            "key": "columns",
            "label": "Columns",
            "type": "select",
            "options": [
                { "value": "2", "label": "2" },
                { "value": "3", "label": "3" },
                { "value": "4", "label": "4" }
            ]
        },
        { "key": "compact", "label": "Compact cards", "type": "toggle" }
    ],
    "defaults": {
        "title": "Meet the team",
        "intro": "People behind the product.",
        "members": [
            { "name": "Alex Chen", "role": "Founder", "bio": "Product and platform strategy.", "photo": "", "profile_url": "#" },
            { "name": "Maya Patel", "role": "Design Lead", "bio": "UI systems and brand.", "photo": "", "profile_url": "#" },
            { "name": "Ethan Ross", "role": "Engineering", "bio": "Editor and infra.", "photo": "", "profile_url": "#" }
        ],
        "columns": "3",
        "compact": false
    }
}
--}}
@php
    $title = trim((string) ($props['title'] ?? ''));
    $intro = trim((string) ($props['intro'] ?? ''));
    $columns = trim((string) ($props['columns'] ?? '3'));
    $compact = (bool) ($props['compact'] ?? false);

    $rawMembers = $props['members'] ?? [];
    if (is_string($rawMembers)) {
        $trim = trim($rawMembers);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        $members = is_array($decoded) ? $decoded : [];
    } elseif (is_array($rawMembers)) {
        $members = $rawMembers;
    } else {
        $members = [];
    }

    $members = array_values(array_filter($members, static fn ($item): bool => is_array($item) && trim((string) ($item['name'] ?? '')) !== ''));

    $gridClass = match ($columns) {
        '2' => 'md:grid-cols-2',
        '4' => 'md:grid-cols-2 xl:grid-cols-4',
        default => 'md:grid-cols-2 xl:grid-cols-3',
    };
@endphp

<section class="py-14 sm:py-20">
    <div class="mx-auto max-w-7xl px-6">
        @if ($title !== '')
            <h2 class="text-center font-display text-3xl font-semibold text-surface-900 sm:text-5xl">{{ $title }}</h2>
        @endif
        @if ($intro !== '')
            <p class="mx-auto mt-3 max-w-2xl text-center text-pretty text-surface-600">{{ $intro }}</p>
        @endif

        @if ($members === [])
            <div class="mt-8 rounded-2xl border border-dashed border-black/15 bg-white/70 p-6 text-center text-sm text-surface-500">
                Add members in JSON to render this team grid.
            </div>
        @else
            <div class="mt-10 grid gap-5 {{ $gridClass }}">
                @foreach ($members as $member)
                    @php
                        $name = trim((string) ($member['name'] ?? ''));
                        $role = trim((string) ($member['role'] ?? ''));
                        $bio = trim((string) ($member['bio'] ?? ''));
                        $photo = trim((string) ($member['photo'] ?? ''));
                        $profileUrl = trim((string) ($member['profile_url'] ?? ''));
                    @endphp

                    <article class="rounded-2xl border border-black/8 bg-white p-5 shadow-sm {{ $compact ? 'space-y-3' : 'space-y-4' }}">
                        <div class="flex items-center gap-3">
                            @if ($photo !== '')
                                <img src="{{ $photo }}" alt="" class="h-14 w-14 rounded-full object-cover" />
                            @else
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-lg font-semibold text-slate-500">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <h3 class="font-semibold text-surface-900">{{ $name }}</h3>
                                @if ($role !== '')
                                    <p class="text-sm text-surface-500">{{ $role }}</p>
                                @endif
                            </div>
                        </div>

                        @if (! $compact && $bio !== '')
                            <p class="text-sm leading-relaxed text-surface-600">{{ $bio }}</p>
                        @endif

                        @if ($profileUrl !== '')
                            <a href="{{ $profileUrl }}" class="inline-flex items-center text-sm font-semibold text-surface-900 hover:text-surface-700">
                                View profile
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

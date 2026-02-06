@extends('tentapress-admin::layouts.shell')

@section('title', 'Users')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Users</h1>
            <p class="tp-description">Manage users who can access the admin.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.users.create') }}" class="tp-button-primary">Add New</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span>All users</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                    {{ $users->total() }}
                </span>
            </div>
            <form method="GET" class="flex w-full items-center gap-2 md:w-auto">
                <label class="sr-only" for="roles-search">Search roles</label>
                <input
                    id="roles-search"
                    name="s"
                    type="search"
                    value="{{ request('s') }}"
                    placeholder="Search roles"
                    class="w-full rounded-md border border-slate-200 px-3 py-1.5 text-sm md:w-56" />
                <button type="submit" class="tp-button-secondary">Search</button>
            </form>
        </div>

        @if ($users->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No users yet.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">ID</th>
                            <th class="tp-table__th">Name</th>
                            <th class="tp-table__th">Email</th>
                            <th class="tp-table__th">Super Admin</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($users as $user)
                            <tr class="tp-table__row">
                                <td class="tp-table__td">{{ $user->id }}</td>
                                <td class="tp-table__td">
                                    <div class="font-semibold">
                                        <a
                                            class="tp-button-link"
                                            href="{{ route('tp.users.edit', ['user' => $user->id]) }}">
                                            {{ $user->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="tp-table__td">{{ $user->email }}</td>
                                <td class="tp-table__td">
                                    {{ $user->is_super_admin ? 'Yes' : 'No' }}
                                </td>

                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.users.edit', ['user' => $user->id]) }}">
                                            Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('tp.users.destroy', ['user' => $user->id]) }}"
                                            data-confirm="Delete this user?">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="tp-button-link text-red-600 hover:text-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'create' ? 'Add New User' : 'Edit User')

@section('content')
    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">{{ $mode === 'create' ? 'Add New User' : 'Edit User' }}</h1>
                <p class="tp-description">
                    {{ $mode === 'create' ? 'Create a user who can access the admin.' : 'Update details, roles and access.' }}
                </p>
            </div>
        </div>

        @php
            $selected = is_array($selected ?? null) ? $selected : [];
            $roles = $roles ?? collect();
        @endphp

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="tp-metabox">
                    <div class="tp-metabox__body">
                        <form
                            method="POST"
                            action="{{ $mode === 'create' ? route('tp.users.store') : route('tp.users.update', ['user' => $user->id]) }}"
                            class="space-y-5"
                            id="user-form">
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                <div class="tp-field">
                                    <label class="tp-label">Name</label>
                                    <input name="name" class="tp-input" value="{{ old('name', $user->name) }}" required />
                                </div>

                                <div class="tp-field">
                                    <label class="tp-label">Email</label>
                                    <input
                                        name="email"
                                        type="email"
                                        class="tp-input"
                                        value="{{ old('email', $user->email) }}"
                                        required />
                                </div>
                            </div>

                            <div class="tp-field">
                                <label class="tp-label">Password</label>
                                <input
                                    name="password"
                                    type="password"
                                    class="tp-input"
                                    {{ $mode === 'create' ? 'required' : '' }} />
                                <div class="tp-help">
                                    @if ($mode === 'create')
                                        Minimum 8 characters.
                                    @else
                                        Leave blank to keep the current password.
                                    @endif
                                </div>
                            </div>

                            <div class="tp-panel">
                                <label class="flex items-center gap-3">
                                    <input
                                        type="checkbox"
                                        class="tp-checkbox"
                                        name="is_super_admin"
                                        value="1"
                                        @checked(old('is_super_admin', $user->is_super_admin) ? true : false) />
                                    <span>
                                        <span class="block text-sm font-semibold">Super Admin</span>
                                        <span class="tp-muted mt-1 block text-xs">Bypasses all capability checks.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="tp-divider"></div>

                            <div>
                                <div class="tp-label mb-2">Roles</div>

                                @if ($roles->count() === 0)
                                    <div class="tp-muted text-sm">
                                        No roles available. Run
                                        <code class="tp-code">php artisan tp:permissions seed</code>
                                        .
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        @foreach ($roles as $role)
                                            @php
                                                $id = (int) $role->id;
                                                $isChecked =
                                                    in_array($id, $selected, true) ||
                                                    in_array((string) $id, (array) old('roles', []), true);
                                            @endphp

                                            <label class="tp-panel flex cursor-pointer items-start gap-3">
                                                <input
                                                    type="checkbox"
                                                    class="tp-checkbox mt-1"
                                                    name="roles[]"
                                                    value="{{ $id }}"
                                                    @checked($isChecked) />
                                                <span class="block">
                                                    <span class="block text-sm font-semibold">{{ $role->name }}</span>
                                                    <span class="tp-muted mt-1 block text-xs">
                                                        <code class="tp-code">{{ $role->slug }}</code>
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Actions</div>
                    <div class="tp-metabox__body space-y-2 text-sm">
                        <button type="submit" form="user-form" class="tp-button-primary w-full justify-center">
                            {{ $mode === 'create' ? 'Create User' : 'Save Changes' }}
                        </button>
                        <a href="{{ route('tp.users.index') }}" class="tp-button-secondary w-full justify-center">
                            Back
                        </a>

                        @if ($mode === 'edit')
                            <form
                                method="POST"
                                action="{{ route('tp.users.destroy', ['user' => $user->id]) }}"
                                data-confirm="Delete this user?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tp-button-danger w-full justify-center">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

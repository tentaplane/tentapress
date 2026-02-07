@extends('tentapress-admin::layouts.shell')

@section('title', 'Import')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Import</h1>
            <p class="tp-description">Upload an export file from another TentaPress site.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Upload export file</div>
        <div class="tp-metabox__body space-y-5">
            <form
                method="POST"
                action="{{ route('tp.import.analyze') }}"
                enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div
                    class="tp-field"
                    x-data="{
                        isDragging: false,
                        fileName: '',
                        setFileName(input) {
                            const file = input.files && input.files[0] ? input.files[0] : null;
                            this.fileName = file ? file.name : '';
                        },
                        handleDrop(event) {
                            this.isDragging = false;
                            const files = event.dataTransfer ? event.dataTransfer.files : null;
                            if (!files || files.length === 0) {
                                return;
                            }

                            const transfer = new DataTransfer();
                            transfer.items.add(files[0]);
                            this.$refs.fileInput.files = transfer.files;
                            this.setFileName(this.$refs.fileInput);
                        }
                    }">
                    <label class="tp-label">Export file (.zip)</label>
                    <label
                        class="group relative mt-2 flex min-h-44 cursor-pointer flex-col items-center justify-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 via-white to-sky-50 px-6 py-8 text-center transition hover:border-sky-400 hover:from-sky-50 hover:to-indigo-50"
                        :class="isDragging ? 'border-sky-500 ring-4 ring-sky-100' : ''"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)">
                        <input
                            x-ref="fileInput"
                            type="file"
                            name="bundle"
                            accept=".zip,application/zip,application/x-zip-compressed"
                            class="sr-only"
                            required
                            @change="setFileName($event.target)" />

                        <div class="rounded-full bg-sky-100 p-3 text-sky-700 ring-1 ring-sky-200">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true">
                                <path
                                    d="M11 3a1 1 0 0 1 2 0v8.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 3.99a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.41l2.3 2.29V3ZM5 15a1 1 0 0 1 1 1v2a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-2a1 1 0 1 1 2 0v2a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-2a1 1 0 0 1 1-1Z" />
                            </svg>
                        </div>

                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-slate-800">
                                Drop your export zip here or click to browse
                            </p>
                            <p class="text-xs text-slate-500">
                                Only
                                <code class="tp-code">.zip</code>
                                files are accepted
                            </p>
                        </div>

                        <div
                            class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm"
                            x-show="fileName"
                            x-cloak
                            x-text="'Selected: ' + fileName"></div>
                    </label>
                    <div class="tp-help">Choose the `.zip` file created from the Export screen.</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Review file</button>
                </div>

                <div class="tp-muted text-xs">You'll review everything before any changes are made.</div>
            </form>
        </div>
    </div>
@endsection

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Forms\Http\Public\SubmitFormController;

Route::middleware(['web', 'throttle:tp.forms.submit'])->group(function (): void {
    Route::post('/forms/submit/{formKey}', SubmitFormController::class)
        ->where('formKey', '^[A-Za-z0-9._-]+$')
        ->name('tp.forms.submit');
});

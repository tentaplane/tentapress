<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:view_workflow_queue')->group(function (): void {
        Route::get('/workflow', 'TentaPress\\Workflow\\Http\\Admin\\IndexController@__invoke')->name('workflow.index');
    });

    Route::prefix('/workflow/{resourceType}/{resourceId}')
        ->whereIn('resourceType', ['pages', 'posts'])
        ->whereNumber('resourceId')
        ->group(function (): void {
            Route::post('/assign', 'TentaPress\\Workflow\\Http\\Admin\\UpdateAssignmentsController@__invoke')->name('workflow.assign');
            Route::post('/submit', 'TentaPress\\Workflow\\Http\\Admin\\SubmitForReviewController@__invoke')->name('workflow.submit');
            Route::post('/changes', 'TentaPress\\Workflow\\Http\\Admin\\RequestChangesController@__invoke')->name('workflow.changes');
            Route::post('/approve', 'TentaPress\\Workflow\\Http\\Admin\\ApproveController@__invoke')->name('workflow.approve');
            Route::post('/revoke', 'TentaPress\\Workflow\\Http\\Admin\\RevokeApprovalController@__invoke')->name('workflow.revoke');
            Route::post('/schedule', 'TentaPress\\Workflow\\Http\\Admin\\ScheduleController@__invoke')->name('workflow.schedule');
            Route::post('/publish', 'TentaPress\\Workflow\\Http\\Admin\\PublishController@__invoke')->name('workflow.publish');
        });
});

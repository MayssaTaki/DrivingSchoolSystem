<?php
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
 Schedule::command('archive:exam-attempts')
        ->daily()
        ->before(function () {
            Log::channel('scheduler')->info('🔁 بدء أرشفة محاولات الاختبار القديمة...');
        })
        ->after(function () {
            Log::channel('scheduler')->info('✅ الانتهاء من عملية الأرشفة.');
        })
        ->onSuccess(function () {
            Log::channel('scheduler')->info('✅ تمت أرشفة محاولات الاختبار القديمة بنجاح.');
        })
        ->onFailure(function () {
            Log::channel('scheduler')->error('❌ فشل في أرشفة محاولات الاختبار القديمة.');
        });
Schedule::command('training:dispatch-monthly-jobs')
    ->daily() 
    ->before(function () {
        Log::channel('scheduler')->info('Starting monthly training jobs dispatch');
    })
    ->after(function () {
        Log::channel('scheduler')->info('Finished monthly training jobs dispatch');
    })
    ->onSuccess(function () {
        Log::channel('scheduler')->info('Monthly training jobs dispatched successfully');
    })
    ->onFailure(function () {
        Log::channel('scheduler')->error('Failed to dispatch monthly training jobs');
    });
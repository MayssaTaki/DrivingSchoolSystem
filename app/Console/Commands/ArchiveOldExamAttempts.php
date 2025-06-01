<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveOldExamAttempts extends Command
{
    
    protected $signature = 'archive:exam-attempts';

   
    protected $description = 'أرشفة أسئلة المحاولات القديمة من جدول exam_attempt_questions إلى جدول archived_exam_attempt_questions';

   
    public function handle()
    {
        $days = 30;

        $oldAttemptIds = DB::table('exam_attempts')
            ->whereNotNull('finished_at')
            ->where('finished_at', '<', now()->subDays($days))
            ->pluck('id')
            ->toArray();

        if (empty($oldAttemptIds)) {
            $this->info('لا توجد محاولات قديمة للأرشفة.');
            return;
        }

        $records = DB::table('exam_attempt_questions')
            ->whereIn('exam_attempt_id', $oldAttemptIds)
            ->get();

        foreach ($records as $record) {
            DB::table('archived_exam_attempt_questions')->insert([
                'exam_attempt_id' => $record->exam_attempt_id,
                'question_id' => $record->question_id,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
        }

        DB::table('exam_attempt_questions')
            ->whereIn('exam_attempt_id', $oldAttemptIds)
            ->delete();

        $this->info('✅ تمت أرشفة ' . count($records) . ' سجل بنجاح.');
    }
}

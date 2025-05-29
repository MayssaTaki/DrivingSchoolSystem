<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        

        $trainers = Trainer::all();

        if ($trainers->isEmpty()) {
            $this->command->warn('⚠️ لا يوجد مدربين في قاعدة البيانات.');
            return;
        }

        $examTypes = [
            'driving',
            'traffic_rules',
            'traffic_signs',
            'mechanics',
            'first_aid',
            'special_conditions',
            'accident_handling',
        ];

        $questionPool = [
            'ما هو العمر الأدنى للحصول على رخصة قيادة؟',
            'ما معنى الإشارة التي تحتوي على مثلث أحمر؟',
            'متى يجب استخدام إشارات الانعطاف؟',
            'ما هو الحد الأقصى للسرعة في الطرق السريعة؟',
            'كيف تتعامل مع سيارة تقطع طريقك فجأة؟',
            'ما هي فائدة زيت المحرك؟',
            'كيف تتحقق من ضغط الإطارات؟',
            'متى يجب استبدال مسّاحات الزجاج؟',
            'ما هو الإجراء الصحيح عند تقاطع بدون إشارات؟',
            'ماذا تعني الأضواء الحمراء الوامضة في السيارة؟',
            'ما هو السبب الأكثر شيوعاً لحوادث السير؟',
            'كيف يجب أن تتصرف في الظروف الجوية السيئة؟',
            'ما الفرق بين الدفع الأمامي والخلفي؟',
            'ماذا تفعل إذا انفجر إطار السيارة؟',
            'متى يجب استخدام أضواء الطوارئ؟',
            'ما وظيفة المكابح ABS؟',
            'ماذا يعني ضوء فحص المحرك؟',
            'كيف يتم تشغيل الأضواء في الطقس الضبابي؟',
            'ماذا تعني الإشارة الزرقاء المستديرة؟',
            'متى يجب إجراء صيانة دورية للسيارة؟',
        ];

        foreach ($trainers as $trainer) {
            foreach ($examTypes as $type) {
                $exam = Exam::create([
                    'trainer_id' => $trainer->id,
                    'type' => $type,
                    'duration_minutes' => 30,
                ]);

                for ($i = 1; $i <= 40; $i++) {
                    $text = $questionPool[array_rand($questionPool)] . " (سؤال رقم $i)";

                    $question = Question::create([
                        'exam_id' => $exam->id,
                        'question_text' => $text,
                    ]);

                    $correctIndex = rand(0, 3);
                    for ($j = 0; $j < 4; $j++) {
                        Choice::create([
                            'question_id' => $question->id,
                            'choice_text' => "خيار رقم " . ($j + 1),
                            'is_correct' => $j === $correctIndex,
                        ]);
                    }
                }
            }
        }

    }
}

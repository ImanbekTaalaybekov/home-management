<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->truncate();
        DB::table('tests')->truncate();
        DB::table('phrasal_verbs_questions')->truncate();
        DB::table('true_false_questions')->truncate();
        DB::table('vocabulary_matching_questions')->truncate();
        DB::table('fill_in_blanks_questions')->truncate();

        $categories = [
            'Phrasal Verbs' => 'phrasal_verbs_questions',
            'True/False' => 'true_false_questions',
            'Vocabulary Matching' => 'vocabulary_matching_questions',
            'Fill in the Blanks' => 'fill_in_blanks_questions'
        ];

        $categoryIds = [];
        foreach ($categories as $name => $table) {
            $categoryIds[$name] = DB::table('categories')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $levels = ['Beginner', 'Intermediate', 'Advanced'];
        $testIds = [];

        foreach ($levels as $level) {
            for ($i = 1; $i <= 2; $i++) {
                $testIds[] = DB::table('tests')->insertGetId([
                    'title' => "Test $i - $level",
                    'level' => $level,
                    'description' => "Test $i for $level level",
                    'questions' => json_encode([]),
                    'video' => "https://example.com/video$i.mp4",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($testIds as $testId) {
            $questions = [];

            foreach ($categories as $category => $table) {
                for ($j = 1; $j <= 2; $j++) {
                    $data = [
                        'category_id' => $categoryIds[$category],
                        'explanation' => "Explanation for $category question $j",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($category === 'True/False') {
                        $data['question'] = "Sample question $j for $category";
                        $data['correct_answer'] = (bool) rand(0, 1);
                    } elseif ($category === 'Fill in the Blanks') {
                        $data['sentence'] = "Sample sentence $j for Fill in the Blanks";
                        $data['correct_answer'] = "Correct Answer";
                        $data['wrong_answers'] = json_encode(['Wrong 1', 'Wrong 2', 'Wrong 3']);
                    } else {
                        $data['question'] = "Sample question $j for $category";
                        $data['correct_answer'] = "Correct Answer";
                        $data['wrong_answers'] = json_encode(['Wrong 1', 'Wrong 2', 'Wrong 3']);
                    }

                    $questionId = DB::table($table)->insertGetId($data);

                    $questions[] = [
                        'category' => $category,
                        'question_id' => $questionId,
                    ];
                }
            }

            DB::table('tests')->where('id', $testId)->update([
                'questions' => json_encode($questions),
                'updated_at' => now(),
            ]);
        }
    }
}
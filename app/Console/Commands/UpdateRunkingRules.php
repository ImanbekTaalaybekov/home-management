<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Meilisearch\Client;

class UpdateRunkingRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:runking-rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update MeiliSearch Runking Rules';

    /**
     * Execute the console command.
     */
    public function handle(Client $client)
    {
        $indexRules = [
             'buildings' => [
                 "typo",
                 "words",
                 "proximity",
                 "attribute",
                 "exactness",
                 "title:desc",
             ],
             'resumes' => [
                "typo",
                "words",
                "proximity",
                "attribute",
                "exactness",
                "fullname:desc",
             ],

            'knowledge_categories' => [
                "typo",
                "words",
                "proximity",
                "attribute",
                "exactness",
                "name:desc",
                "title:desc",
            ],

            'vacancies' => [
                "typo",
                "words",
                "proximity",
                "attribute",
                "exactness",
                "title:desc",
            ],
        ];

        foreach ($indexRules as $indexName => $rankingRules) {
            $client->index($indexName)->updateRankingRules($rankingRules);
        }
    }
}

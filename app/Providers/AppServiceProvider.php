<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\Company;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeContact;
use App\Models\KnowledgeDocument;
use App\Models\News;
use App\Models\Resume;
use App\Models\Vacancy;
use App\Observers\CompanyObserver;
use App\Observers\KnowledgeArticleObserver;
use App\Observers\KnowledgeContactObserver;
use App\Observers\KnowledgeDocumentObserver;
use App\Observers\NewsObserver;
use App\Observers\ResumeObserver;
use App\Observers\SearchableObserver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::macro('orderByPosition', function (array $values, string $field = 'id') {
            if (count($values) > 0) {
                $this->orderByRaw(sprintf("position(%s::text in '%s')", $field, implode(', ', $values)));
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}

<?php

namespace App\Providers;

use App\Policies\ContentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(\App\Providers\RepositoryServiceProvider::class);
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Write operations on all content are admin-only; reads stay public.
        $contentModels = [
            \App\Models\Category::class,
            \App\Models\Subcategory::class,
            \App\Models\Disease::class,
            \App\Models\DiseaseAlias::class,
            \App\Models\Recording::class,
            \App\Models\Favorite::class,
            \App\Models\AdhkarCategory::class,
            \App\Models\AdhkarSection::class,
            \App\Models\AdhkarItem::class,
            \App\Models\TahsinatCategory::class,
            \App\Models\TahsinatItem::class,
            \App\Models\Course::class,
            \App\Models\Sponsor::class,
            \App\Models\SponsorScreenConfig::class,
            \App\Models\Feedback::class,
            \App\Models\FeatureFlag::class,
            \App\Models\Surah::class,
            \App\Models\Verse::class,
            \App\Models\Reciter::class,
            \App\Models\Recitation::class,
        ];

        foreach ($contentModels as $model) {
            Gate::policy($model, ContentPolicy::class);
        }

        Gate::policy(\App\Models\User::class, UserPolicy::class);
    }
}

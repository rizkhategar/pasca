<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // scramble
        Gate::define('viewApiDocs', function (User $user) {
            return $user->hasRole(['super_admin', 'admin', 'apiuser']);
        });

        $this->registerActivityLogger();
    }

    private function registerActivityLogger(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $eventName, array $payload) use ($event): void {
                $model = $payload[0] ?? null;

                if (! $model instanceof Model || ! $this->shouldLogModelActivity($model)) {
                    return;
                }

                $this->recordModelActivity($event, $model);
            });
        }
    }

    private function shouldLogModelActivity(Model $model): bool
    {
        if ($model instanceof ActivityLog || ! Schema::hasTable('activity_logs')) {
            return false;
        }

        $class = $model::class;

        return Str::startsWith($class, 'App\\Models\\')
            || Str::startsWith($class, 'Spatie\\Permission\\Models\\');
    }

    private function recordModelActivity(string $event, Model $model): void
    {
        $changes = $this->resolveActivityChanges($event, $model);

        if ($event === 'updated' && empty($changes['attributes'])) {
            return;
        }

        $causer = auth()->user();
        $request = request();

        ActivityLog::query()->create([
            'log_name' => 'system',
            'event' => $event,
            'description' => sprintf('%s %s %s', class_basename($model), $model->getKey(), $event),
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'properties' => $changes,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function resolveActivityChanges(string $event, Model $model): array
    {
        $hiddenKeys = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        if ($event === 'updated') {
            $changes = Arr::except($model->getChanges(), array_merge($hiddenKeys, ['updated_at']));
            $old = Arr::only($model->getOriginal(), array_keys($changes));

            return [
                'attributes' => $changes,
                'old' => $old,
            ];
        }

        if ($event === 'deleted') {
            return [
                'old' => Arr::except($model->getOriginal(), $hiddenKeys),
            ];
        }

        return [
            'attributes' => Arr::except($model->getAttributes(), $hiddenKeys),
        ];
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Memastikan Activity class tersedia
        $this->app->bind(Activity::class, function ($app) {
            return new Activity;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bagikan settings ke semua halaman Inertia
        Inertia::share([
            'settings' => function () {
                // Tambahkan logging untuk debugging
                $currentRoute = request()->route() ? request()->route()->getName() : 'unknown';
                \Log::info("Sharing settings to Inertia for route: {$currentRoute}");
                
                // Check cache first and log if we're using cached data
                if (Cache::has('app_settings')) {
                    \Log::info("Using cached settings for route: {$currentRoute}");
                    $cachedSettings = Cache::get('app_settings');
                    \Log::info("Cached settings: " . json_encode($cachedSettings));
                    return $cachedSettings;
                }
                
                \Log::info("Cache miss - loading settings from database for route: {$currentRoute}");
                
                // Get settings from database
                try {
                    $settings = Setting::all();
                    
                    if ($settings->isEmpty()) {
                        \Log::warning("No settings found in database!");
                        return [];
                    }
                    
                    \Log::info("Found " . $settings->count() . " settings in database");
                    
                    $mappedSettings = $settings->mapWithKeys(function ($setting) {
                        $value = $setting->value;
                        
                        // Handle media items
                        if (in_array($setting->key, ['logo', 'favicon', 'thumbnail'])) {
                            $value = $setting->getFirstMediaUrl($setting->key);
                            \Log::info("Media URL for {$setting->key}: {$value}");
                        }
                        
                        return [$setting->key => $value];
                    });

                    \Log::info('Settings loaded from database:', $mappedSettings->toArray());
                    
                    // Store in cache
                    Cache::put('app_settings', $mappedSettings, 3600);
                    
                    return $mappedSettings;
                } catch (\Exception $e) {
                    \Log::error("Error loading settings: " . $e->getMessage());
                    return [];
                }
            },
        ]);

        // Register policies manually
        Gate::define('viewAny', function ($user, $model) {
            $className = class_basename($model);
            if ($className === 'Role') {
                return $user->can('view roles');
            } elseif ($className === 'Permission') {
                return $user->can('view permissions');
            }
            return false;
        });

        Gate::define('create', function ($user, $model) {
            $className = class_basename($model);
            if ($className === 'Role') {
                return $user->can('create roles');
            } elseif ($className === 'Permission') {
                return $user->can('create permissions');
            }
            return false;
        });

        Gate::define('update', function ($user, $model) {
            $className = get_class($model);
            if ($className === 'Spatie\\Permission\\Models\\Role') {
                return $user->can('edit roles');
            } elseif ($className === 'Spatie\\Permission\\Models\\Permission') {
                return $user->can('edit permissions');
            }
            return false;
        });

        Gate::define('delete', function ($user, $model) {
            $className = get_class($model);
            if ($className === 'Spatie\\Permission\\Models\\Role') {
                return $user->can('delete roles');
            } elseif ($className === 'Spatie\\Permission\\Models\\Permission') {
                return $user->can('delete permissions');
            }
            return false;
        });
    }
}

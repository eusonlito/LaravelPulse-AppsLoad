<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\AppsLoad;

use EuSonLito\LaravelPulse\AppsLoad\Livewire\AppsLoad;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class AppsLoadServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'apps-load');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('apps-load', AppsLoad::class);
        });
    }
}

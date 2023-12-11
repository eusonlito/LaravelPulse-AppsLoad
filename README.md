# AppsLoad card for Laravel Pulse

This card will show you system apps load.

![Screenshot from 2023-12-11 16-18-49](https://github.com/eusonlito/LaravelPulse-AppsLoad/assets/644551/9a501cde-0055-457c-b863-ed063bad84cf)

## Installation

Require the package with Composer:

```shell
composer require eusonlito/laravel-pulse-apps-load:dev-master
```

## Register the recorder

```diff
return [
    // ...
    
    'recorders' => [
+        \EuSonLito\LaravelPulse\AppsLoad\Recorders\AppsLoadRecorder::class => [
+            'enabled' => env('PULSE_APPS_LOAD_ENABLED', true),
+            'sample_rate' => env('PULSE_APPS_LOAD_SAMPLE_RATE', 1),
+            'limit' => env('PULSE_APPS_LOAD_LIMIT', 10),
+            'ignore' => [
+                '#^/pulse$#', // Pulse dashboard...
+            ],
+        ],
    ]
]
```

You also need to be running [the `pulse:check` command](https://laravel.com/docs/10.x/pulse#dashboard-cards).

## Add to your dashboard

To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/10.x/pulse#dashboard-customization).

Then, you can modify the `dashboard.blade.php` file:

```diff
<x-pulse>
+    <livewire:apps-load cols="4" rows="8" />

    <livewire:pulse.servers cols="full" />

    <livewire:pulse.usage cols="4" rows="2" />

    <livewire:pulse.queues cols="4" />

    <livewire:pulse.cache cols="4" />

    <livewire:pulse.slow-queries cols="8" />

    <livewire:pulse.exceptions cols="6" />

    <livewire:pulse.slow-requests cols="6" />

    <livewire:pulse.slow-jobs cols="6" />

    <livewire:pulse.slow-outgoing-requests cols="6" />
</x-pulse>
```

That's it!


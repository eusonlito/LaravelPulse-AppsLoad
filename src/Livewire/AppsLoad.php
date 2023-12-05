<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\AppsLoad\Livewire;

use Illuminate\Support\Facades\View as ViewFacace;
use Illuminate\View\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

class AppsLoad extends Card
{
    /**
     * @return \Illuminate\View\View
     */
    #[Lazy]
    public function render(): View
    {
        return ViewFacace::make('apps-load::livewire.card', [
            'apps' => $this->apps(),
        ]);
    }

    /**
     * @return array
     */
    protected function apps(): array
    {
        return ($apps = Pulse::values('apps-load', ['result'])->first())
            ? json_decode($apps->value, true, JSON_THROW_ON_ERROR)
            : [];
    }
}

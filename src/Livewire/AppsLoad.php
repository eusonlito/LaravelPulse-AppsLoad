<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\AppsLoad\Livewire;

use Illuminate\Support\Facades\View as ViewFacace;
use Illuminate\View\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;

class AppsLoad extends Card
{
    /**
     * @var 'memory'|'cpu'
     */
    #[Url(as: 'apps-load')]
    public string $orderBy = 'memory';

    /**
     * @return \Illuminate\View\View
     */
    #[Lazy]
    public function render(): View
    {
        return ViewFacace::make('apps-load::livewire.card', [
            'apps' => $this->apps($this->appsLoad()),
        ]);
    }

    /**
     * @return array
     */
    protected function appsLoad(): array
    {
        return ($values = Pulse::values('apps-load', ['result'])->first())
            ? json_decode($values->value, true, JSON_THROW_ON_ERROR)
            : [];
    }

    /**
     * @param array $apps
     *
     * @return array
     */
    protected function apps(array $apps): array
    {
        return $apps[$this->orderBy()] ?? $apps;
    }

    /**
     * @return string
     */
    protected function orderBy(): string
    {
        return match ($this->orderBy) {
            'cpu' => 'cpu',
            default => 'memory',
        };
    }
}

<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\AppsLoad\Recorders;

use RuntimeException;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Process;
use Laravel\Pulse\Events\SharedBeat;
use Laravel\Pulse\Pulse;

class AppsLoadRecorder
{
    /**
     * @var class-string
     */
    public string $listen = SharedBeat::class;

    /**
     * @var int
     */
    protected int $cpus;

    /**
     * @param \Laravel\Pulse\Pulse $pulse
     * @param \Illuminate\Config\Repository $config
     *
     * @return self
     */
    public function __construct(protected Pulse $pulse, protected Repository $config)
    {
    }

    /**
     * @param \Laravel\Pulse\Events\SharedBeat $event
     *
     * @return void
     */
    public function record(SharedBeat $event): void
    {
        if ($this->config('enabled') === false) {
            return;
        }

        $this->cpus();
        $this->set();
    }

    /**
     * @return void
     */
    protected function cpus(): void
    {
        $this->cpus = intval(Process::run('getconf _NPROCESSORS_ONLN')->output());
    }

    /**
     * @return void
     */
    protected function set(): void
    {
        $this->pulse->set('apps-load', 'result', $this->result());
    }

    /**
     * @return string
     */
    protected function result(): string
    {
        $process = Process::run('ps -eo pid,ppid,rss,pcpu,comm:50');

        if ($process->failed()) {
            throw new RuntimeException(sprintf('Apps Load failed: %s', $process->errorOutput()));
        }

        $apps = $this->lines($process->output());
        $apps = $this->acum($apps);
        $apps = $this->group($apps);

        return $this->encode($apps);
    }

    /**
     * @param string $output
     *
     * @return array
     */
    protected function lines(string $output): array
    {
        return array_filter(explode("\n", trim($output)));
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    protected function acum(array $lines): array
    {
        $apps = [];

        foreach ($lines as $line) {
            $parts = explode(' ', trim(preg_replace('/\s+/', ' ', $line)), 5);

            if (count($parts) !== 5) {
                continue;
            }

            $pid = intval($parts[0]);
            $ppid = intval($parts[1]);

            if (($pid === 0) || ($ppid === 0)) {
                continue;
            }

            $app = trim($parts[4]);
            $id = ($ppid === 1) ? $pid : $app;

            if (empty($apps[$id])) {
                $apps[$id] = [
                    'ppid' => $ppid,
                    'app' => $app,
                    'count' => 0,
                    'memory' => 0,
                    'cpu' => 0,
                ];
            }

            $apps[$id]['memory'] += intval($parts[2]);
            $apps[$id]['cpu'] += floatval($parts[3]);
            $apps[$id]['count']++;
        }

        return $apps;
    }

    /**
     * @param array $apps
     *
     * @return array
     */
    protected function group(array $apps): array
    {
        $groups = [];

        foreach (['memory', 'cpu'] as $group) {
            $groups[$group] = $this->groupApps($apps, $group);
        }

        return $groups;
    }

    /**
     * @param array $apps
     * @param string $group
     *
     * @return array
     */
    protected function groupApps(array $apps, string $group): array
    {
        $apps = $this->groupAppsSort($apps, $group);
        $apps = $this->groupAppsLimit($apps);
        $apps = $this->groupAppsSummary($apps);

        return $apps;
    }

    /**
     * @param array $apps
     * @param string $group
     *
     * @return array
     */
    protected function groupAppsSort(array $apps, string $group): array
    {
        usort($apps, static fn ($a, $b) => $b[$group] <=> $a[$group]);

        return $apps;
    }

    /**
     * @param array $apps
     *
     * @return array
     */
    protected function groupAppsLimit(array $apps): array
    {
        return array_slice($apps, 0, $this->config('limit', 10));
    }

    /**
     * @param array $apps
     *
     * @return array
     */
    protected function groupAppsSummary(array $apps): array
    {
        return array_map(function ($app) {
            $app['memory'] = sprintf('%.2f', $app['memory'] / 1024 / 1024);
            $app['cpu'] = sprintf('%.2f', $app['cpu'] / $this->cpus);

            return $app;
        }, $apps);
    }

    /**
     * @param array $apps
     *
     * @return string
     */
    protected function encode(array $apps): string
    {
        return json_encode($apps);
    }

    /**
     * @param string $key
     * @param mixed $default = null
     *
     * @return mixed
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        static $config;

        $config ??= $this->config->get('pulse.recorders.'.__CLASS__, []);

        return $config[$key] ?? $default;
    }
}

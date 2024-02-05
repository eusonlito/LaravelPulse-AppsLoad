<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="Apps Load">
        <x-slot:icon>
            <x-dynamic-component :component="'pulse::icons.server'" />
        </x-slot:icon>

        <x-slot:actions>
            <x-pulse::select wire:model.live="orderBy" label="Sort by" :options="['memory' => 'memory','cpu' => 'cpu']" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.1m="">
        @if (empty($apps))

        <x-pulse::no-results />

        @else

            <x-pulse::table>
                <colgroup>
                    <col width="100%" />
                    <col width="0%" />
                    <col width="0%" />
                </colgroup>

                <x-pulse::thead>
                    <tr>
                        <x-pulse::th>App</x-pulse::th>
                        <x-pulse::th class="text-right">Memory</x-pulse::th>
                        <x-pulse::th class="text-right">CPU</x-pulse::th>
                    </tr>
                </x-pulse::thead>

                <tbody>
                    @foreach ($apps as $app)

                    <tr wire:key="{{ $app['app'] }}">
                        <x-pulse::td class="max-w-[1px]">
                            <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="{{ $app['app'] }}">
                                {{ $app['app'] }}
                            </code>
                        </x-pulse::td>

                        <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                            {{ $app['memory'] }} GB
                        </x-pulse::td>

                        <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                            {{ $app['cpu'] }}%
                        </x-pulse::td>
                    </tr>

                    @endforeach
                </tbody>
            </x-pulse::table>

        @endif
    </x-pulse::scroll>
</x-pulse::card>

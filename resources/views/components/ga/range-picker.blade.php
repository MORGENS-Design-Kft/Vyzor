{{-- Reusable date-range selector for GA browsing pages.

     Binds directly to the parent Livewire component's `rangePreset`,
     `dateFrom`, `dateTo` properties and triggers its `forceRefresh()`
     action. Every GA page uses these names by convention, so dropping
     this component in is enough — no per-page wiring required.

     Props:
       presets   — list of preset values to show (subset of today/last_7/last_28/last_30/custom)
       rangePreset — current preset value, used to toggle custom inputs
       rangeFrom / rangeTo — resolved range strings (Y-m-d) shown as the label
       dateFrom / dateTo — fallbacks for the label before the first GA fetch
--}}

@props([
    'presets' => ['today', 'last_7', 'last_28', 'last_30', 'custom'],
    'rangePreset' => 'last_7',
    'rangeFrom' => null,
    'rangeTo' => null,
    'dateFrom' => null,
    'dateTo' => null,
    'showRefresh' => true,
])

@php
    $labels = [
        'today'   => __('Today'),
        'last_7'  => __('Last 7 days'),
        'last_28' => __('Last 28 days'),
        'last_30' => __('Last 30 days'),
        'custom'  => __('Custom'),
    ];
    $hasCustom = in_array('custom', $presets, true);
    $isCustom = $rangePreset === 'custom';
@endphp

<div {{ $attributes->class(['flex items-center gap-3 flex-wrap']) }}>
    <x-ui.radio.group wire:model.live="rangePreset" direction="horizontal" variant="segmented">
        @foreach ($presets as $p)
            <x-ui.radio.item :value="$p" :label="$labels[$p] ?? $p" />
        @endforeach
    </x-ui.radio.group>

    @if ($hasCustom && $isCustom)
        <div class="flex items-center gap-2">
            <x-ui.label>{{ __('From') }}</x-ui.label>
            <x-ui.input type="date" wire:model.live="dateFrom" class="w-44" />
        </div>
        <div class="flex items-center gap-2">
            <x-ui.label>{{ __('To') }}</x-ui.label>
            <x-ui.input type="date" wire:model.live="dateTo" class="w-44" />
        </div>
    @else
        <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
            {{ $rangeFrom ?? $dateFrom }} → {{ $rangeTo ?? $dateTo }}
        </span>
    @endif

    <span wire:loading.delay.short class="inline-flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
        <x-ui.icon name="circle-notch" class="size-3.5 animate-spin" />
        {{ __('Refreshing...') }}
    </span>

    @if ($showRefresh)
        <x-ui.button type="button" wire:click="forceRefresh" wire:loading.attr="disabled"
            variant="outline" color="neutral" size="sm" icon="arrow-clockwise">
            {{ __('Refresh') }}
        </x-ui.button>
    @endif
</div>

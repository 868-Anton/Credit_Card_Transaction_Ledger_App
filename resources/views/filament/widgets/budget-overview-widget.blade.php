<x-filament-widgets::widget>
    <x-filament::section :heading="'Budget — ' . $monthLabel">
        @if (! $month)
            {{-- No budget exists for this month --}}
            <div class="flex items-center gap-4 py-2">
                <x-filament::icon
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedExclamationCircle"
                    class="h-6 w-6 text-warning-500"
                />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    No budget found for {{ $monthLabel }}.
                </span>
                <x-filament::button
                    tag="a"
                    :href="$createUrl"
                    color="primary"
                    size="sm"
                >
                    Create Budget
                </x-filament::button>
            </div>
        @else
            {{-- Stats grid --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Proj. Income</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['projectedIncome'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Proj. Expenses</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['projectedExpenses'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Proj. Remainder</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['projectedRemainder'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        ${{ number_format($stats['projectedRemainder'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Live Income</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($stats['liveIncome'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment Due</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['paymentDue'] > 0 ? 'text-warning-600' : 'text-success-600' }}">
                        ${{ number_format($stats['paymentDue'], 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Live Remainder</p>
                    <p class="mt-1 text-lg font-semibold {{ $stats['liveRemainder'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        ${{ number_format($stats['liveRemainder'], 2) }}
                    </p>
                </div>
            </div>

            {{-- Footer link --}}
            <div class="mt-4 flex justify-end">
                <x-filament::button
                    tag="a"
                    :href="$viewUrl"
                    color="gray"
                    size="sm"
                >
                    View Full Budget →
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

<x-filament::widget>
    <x-filament::section>
        <x-slot name="heading">
            أحدث الشكاوى
        </x-slot>

        <div class="fi-table-wrap">
            <table class="fi-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">المواطن</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">النوع</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">الحالة</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="px-4 py-3">{{ $complaint->id }}</td>
                            <td class="px-4 py-3">{{ $complaint->citizen?->name }}</td>
                            <td class="px-4 py-3">{{ $complaint->type?->name }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'معلق',
                                        'processing' => 'قيد المعالجة',
                                        'completed' => 'مكتمل',
                                        'rejected' => 'مرفوض',
                                    ];
                                    $color = $statusColors[$complaint->status] ?? 'gray';
                                    $label = $statusLabels[$complaint->status] ?? $complaint->status;
                                @endphp
                                <x-filament::badge :color="$color">
                                    {{ $label }}
                                </x-filament::badge>
                            </td>
                            <td class="px-4 py-3">{{ $complaint->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::widget>

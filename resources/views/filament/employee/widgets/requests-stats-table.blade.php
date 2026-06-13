<x-filament::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('تفصيل الطلبات حسب الحالة') }}
        </x-slot>

        <div class="fi-table-wrap overflow-x-auto">
            <table class="fi-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('النوع') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">
                            <x-filament::badge color="warning">{{ __('filament.status.pending') }}</x-filament::badge>
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">
                            <x-filament::badge color="info">{{ __('filament.status.processing') }}</x-filament::badge>
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">
                            <x-filament::badge color="success">{{ __('filament.status.completed_f') }}</x-filament::badge>
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">
                            <x-filament::badge color="danger">{{ __('filament.status.rejected_f') }}</x-filament::badge>
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">{{ __('الإجمالي') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rows = [
                            ['label' => __('الشكاوى'), 'data' => $complaintStatuses],
                            ['label' => __('الاستفسارات'), 'data' => $inquiryStatuses],
                        ];
                    @endphp
                    @foreach ($rows as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="px-4 py-3 font-medium">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['data']['pending'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['data']['processing'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['data']['completed'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['data']['rejected'] }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $row['data']['total'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50 dark:bg-gray-900 font-bold">
                        <td class="px-4 py-3">{{ __('المجموع الكلي') }}</td>
                        <td class="px-4 py-3 text-center">{{ $complaintStatuses['pending'] + $inquiryStatuses['pending'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $complaintStatuses['processing'] + $inquiryStatuses['processing'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $complaintStatuses['completed'] + $inquiryStatuses['completed'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $complaintStatuses['rejected'] + $inquiryStatuses['rejected'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $complaintStatuses['total'] + $inquiryStatuses['total'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::widget>

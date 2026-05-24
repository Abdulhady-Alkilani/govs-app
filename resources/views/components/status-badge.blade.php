@props(['status' => ''])

@php
    $map = [
        'pending'    => ['label' => __('Pending'), 'class' => 'bg-yellow-100 text-yellow-800'],
        'processing' => ['label' => __('Processing'), 'class' => 'bg-blue-100 text-blue-800'],
        'completed'  => ['label' => __('Completed'), 'class' => 'bg-green-100 text-green-800'],
        'rejected'   => ['label' => __('Rejected'), 'class' => 'bg-red-100 text-red-800'],
        'unpaid'     => ['label' => __('Unpaid'), 'class' => 'bg-yellow-100 text-yellow-800'],
        'paid'       => ['label' => __('Paid'), 'class' => 'bg-green-100 text-green-800'],
    ];
    $badge = $map[$status] ?? ['label' => $status, 'class' => 'bg-gray-100 text-gray-800'];
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge['class'] }}">
    {{ $badge['label'] }}
</span>

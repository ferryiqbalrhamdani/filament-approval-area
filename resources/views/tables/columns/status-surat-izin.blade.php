<x-filament::badge :color="
    $getState() === 0 ? 'warning' : ($getState() === 1 ? 'success' : ($getState() === 2 ? 'danger' : 'gray'))
">
    @if ($getState() === 0)
    Processing
    @elseif ($getState() === 1)
    Approved
    @elseif ($getState() === 2)
    Rejected
    @else
    Not Yet
    @endif
</x-filament::badge>
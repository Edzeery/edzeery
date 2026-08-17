@props([
    'headers' => [],
    'emptyText' => 'No data available.',
])

<div class="edz-card">
    @if (isset($header) && $header)
        <div class="edz-card__header">
            {{ $header }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="edz-table">
            @if (count($headers))
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th @if (isset($header['class'])) class="{{ $header['class'] }}" @endif
                                @if (isset($header['width'])) style="width: {{ $header['width'] }}" @endif>
                                {{ $header['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if (isset($footer) && $footer)
        <div class="edz-card__footer">
            {{ $footer }}
        </div>
    @endif
</div>

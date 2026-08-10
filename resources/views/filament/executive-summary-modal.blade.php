<div class="space-y-4">
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>{{ $summary->providerName }}</span>
        <span>{{ $summary->generatedAt->format('d/m/Y H:i') }}</span>
    </div>

    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
        {{ $summary->headline }}
    </h3>

    <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
        {{ $summary->body }}
    </p>

    @if (count($summary->highlights))
        <ul class="space-y-1.5">
            @foreach ($summary->highlights as $highlight)
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <span class="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-primary-500"></span>
                    <span>{{ $highlight }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

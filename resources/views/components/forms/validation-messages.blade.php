@props(['errors' => []])

@if (!empty($errors))
    <div class="mt-2 rounded-2xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700">
        <p class="font-semibold">Perlu diperbaiki:</p>
        <ul class="mt-1 list-disc space-y-1 pl-4">
            @foreach ($errors as $error)
                <li>{{ is_array($error) ? implode(', ', $error) : $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

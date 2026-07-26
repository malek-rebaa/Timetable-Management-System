@props(['headers' => []])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-theme-sm">
    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="w-full table-auto text-left">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 dark:bg-gray-800/50 dark:border-gray-800">
                    @foreach($headers as $header)
                        <th class="px-5 py-4 text-sm font-medium uppercase text-gray-500 dark:text-gray-400">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

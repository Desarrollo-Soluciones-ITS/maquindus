<x-filament-panels::page>
    @if ($readOnly)
        <div class="mb-4 px-4 py-3 bg-warning-50 text-warning-700 rounded-lg">
            <strong>Modo solo lectura:</strong> No puedes modificar archivos en este momento.
        </div>
    @endif

    <div class="overflow-hidden">
        <!-- Header with path and navigation -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                    @if($currentPath !== $rootPath)
                        <button wire:click="navigateUp"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Atrás
                        </button>
                    @endif

                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span class="font-mono">{{ $currentPath }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb navigation -->
        @if(count($breadcrumb) > 0)
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <nav class="flex items-center gap-2 text-sm">
                    @foreach($breadcrumb as $index => $crumb)
                        @if($index > 0)
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <button wire:click="navigateToBreadcrumb({{ $index }})"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                            {{ $crumb['name'] }}
                        </button>
                    @endforeach
                </nav>
            </div>
        @endif

        <!-- File table -->
        <div class="overflow-x-auto">
            @if(count($folders) > 0 || count($files) > 0)
                <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="p-1">
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8">
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tamaño
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Modificado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @if($currentPath !== $rootPath)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors select-none"
                                wire:click="navigateTo(@js($parentPath))">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">..</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Carpeta
                                    anterior</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">-</td>
                            </tr>
                        @endif

                        @foreach($folders as $folder)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors select-none"
                                wire:click="navigateTo(@js($folder['path']))">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <svg class="w-5 h-5 text-yellow-500 dark:text-yellow-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $folder['name'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-600 dark:text-zinc-200">
                                        Carpeta
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $folder['modified'] }}
                                </td>
                            </tr>
                        @endforeach

                        @foreach($files as $file)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors select-none cursor-pointer"
                                wire:click.stop="downloadFile('{{ addslashes($file['path']) }}', '{{ addslashes($file['name']) }}')"
                                title="Descargar {{ $file['name'] }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors cursor-pointer">
                                        @if($this->isImage($file['mime']))
                                            <svg class="w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'pdf'))
                                            <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'msword') || str_contains($file['mime'], 'document'))
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-500" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'spreadsheet') || str_contains($file['mime'], 'excel'))
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-500" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'presentation') || str_contains($file['mime'], 'powerpoint'))
                                            <svg class="w-5 h-5 text-orange-500 dark:text-orange-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'text'))
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif(str_contains($file['mime'], 'zip') || str_contains($file['mime'], 'rar') || str_contains($file['mime'], 'compressed'))
                                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-500" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="group relative" title="{{ $file['name'] }}">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate block max-w-xs"
                                            style="max-width: 300px;">
                                            {{ $file['name'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getFileBadgeClass($file['mime']) }}">
                                        {{ $this->getFileCategoryLabel($file['mime']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $file['size'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $file['modified'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay archivos o carpetas</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Esta carpeta está vacía.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('downloadFile', (event) => {
                const dataUrl = event[0].dataUrl;
                const filename = event[0].filename;

                const link = document.createElement('a');
                link.href = dataUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</x-filament-panels::page>
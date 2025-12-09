<x-filament-panels::page class="px-0! py-6!">
    <div class="max-w-3xl w-full mx-auto px-4 sm:px-6">
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-4">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-6 h-6" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Buscador Global</h1>
            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                Encuentra equipos, proyectos, clientes, documentos y más en segundos.
            </p>
        </div>

        <div class="mb-8">
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                {{ $this->form }}
            </div>
        </div>

        @if($isLoading)
            <div class="text-center py-16">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-8 h-8 animate-spin" />
                </div>
                <p class="text-gray-500">Cargando datos de búsqueda...</p>
            </div>
        @else
            <div>
                @if(count($searchResults) > 0)
                    <div class="space-y-3">
                        @foreach($searchResults as $result)
                            @php
                                $isUser = $result['model_type'] === 'App\Models\User';
                            @endphp

                            @if($isUser)
                                <div class="block">
                            @else
                                    <a href="{{ $this->getResourceUrl($result['model_type'], $result['uuid']) }}" class="block">
                                @endif
                                    <div
                                        class="group p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm {{ !$isUser ? 'hover:shadow-md cursor-pointer' : '' }} transition-all duration-200">
                                        <div class="flex justify-between items-start">
                                            <div class="min-w-0">
                                                <div
                                                    class="text-lg font-semibold text-gray-900 dark:text-white {{ !$isUser ? 'group-hover:text-blue-600 dark:group-hover:text-blue-400' : '' }} transition-colors wrap-break-words">
                                                    {!! $this->highlightText($result['name'], $query) !!}
                                                </div>
                                                <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                                                    <span class="font-mono">{{ $result['created_at'] }}</span>
                                                    <span>•</span>
                                                    <span class="font-mono">{{ $result['updated_at'] }}</span>
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                        {{ $this->modelToSpanish($result['model_type']) }}
                                                    </span>
                                                </div>
                                                @if(!empty($result['description']))
                                                    <div
                                                        class="text-gray-700 dark:text-gray-300 mt-2 text-sm leading-relaxed wrap-break-words">
                                                        {!! $this->highlightText($result['description'], $query) !!}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($isUser)
                                        </div>
                                    @else
                                </a>
                            @endif
                        @endforeach

                        <div class="mt-4">
                            <x-filament::pagination :paginator="$this->paginator" previous-page-label="Página anterior"
                                next-page-label="Siguiente página" />
                        </div>
                    </div>
                @else
                    @if(!empty($query) && !empty($selectedModels))
                        <div class="text-center py-16">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-8 h-8" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No se encontraron resultados</h3>
                            <p class="text-gray-500 max-w-md mx-auto">
                                Intenta con otros términos, revisa faltas de ortografía o elimina los filtros.
                            </p>
                            <p class="mt-3 text-sm text-gray-500">
                                Buscaste: <span
                                    class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $query }}</span>
                            </p>
                        </div>
                    @elseif(empty($selectedModels))
                        <div class="text-center py-16 text-gray-500">
                            <p>Selecciona tipos de contenido para comenzar a buscar</p>
                        </div>
                    @else
                        <div class="text-center py-16 text-gray-500">
                            <p>Escribe en el buscador para encontrar resultados</p>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        @if(!$isLoading && empty($selectedModels) && empty($searchResults))
            <div class="text-center py-16">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
                    <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-8 h-8" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Selecciona tipos de contenido</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Elige qué tipos de elementos quieres buscar (clientes, proyectos, equipos, etc.) para comenzar.
                </p>
            </div>
        @endif
    </div>

    <script>
        // Add a small JavaScript to handle smooth scrolling and focus
        document.addEventListener('livewire:initialized', () => {
            // Focus search input on page load
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput)
            {
                setTimeout(() => {
                    searchInput.focus();
                }, 100);
            }
        });
    </script>
</x-filament-panels::page>
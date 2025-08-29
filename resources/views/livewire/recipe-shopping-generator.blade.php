<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête simple -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Liste des courses</h1>
                <p class="text-gray-600">
                    @if($showIngredients && $fromMenuPlanner)
                        Vos ingrédients calculés selon votre planification de menus
                    @else
                        Sélectionnez une recette pour générer automatiquement les quantités
                    @endif
                </p>
            </div>

            @if($showIngredients)
                <button wire:click="resetSelection"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                    Nouvelle Recette
                </button>
            @endif
        </div>

        <!-- Messages flash -->
        @if (session()->has('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                {{ session('info') }}
            </div>
        @endif

        @if(!$showIngredients)
            <!-- Sélection de recette -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Choisir une recette</h2>

                <!-- Barre de recherche -->
                <div class="mb-4">
                    <input type="text"
                           wire:model.live="searchTerm"
                           placeholder="Rechercher une recette..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Liste des recettes -->
            @if(count($recipes) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recipes as $recipe)
                        <div wire:click="selectRecipe({{ $recipe->id }})"
                             class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:shadow-lg transition-all duration-200">

                            @if($recipe->image)
                                <img src="{{ asset('storage/' . $recipe->image) }}" alt="{{ $recipe->name }}"
                                     class="w-full h-32 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-32 bg-gray-200 rounded-lg mb-3 flex items-center justify-center">
                                    <span class="text-gray-400 text-2xl">Image</span>
                                </div>
                            @endif

                            <h3 class="font-semibold text-lg text-gray-800 mb-2">{{ $recipe->name }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ $recipe->ingredients->count() }} ingrédients</p>

                            @if($recipe->description)
                                <p class="text-xs text-gray-500 line-clamp-2">{{ $recipe->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-6xl mb-4">🔍</div>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">Aucune recette trouvée</h3>
                        <p class="text-gray-500">Essayez de modifier votre recherche ou créez une nouvelle recette.</p>
                    </div>
                @endif
            </div>
        @else
            <!-- Recette sélectionnée et configuration -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        @if($selectedRecipe && $selectedRecipe->image)
                            <img src="{{ asset('storage/' . $selectedRecipe->image) }}" alt="{{ $selectedRecipe->name }}"
                                 class="w-16 h-16 object-cover rounded-lg">
                        @endif
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                {{ $selectedRecipe ? $selectedRecipe->name : 'Ingrédients sélectionnés' }}
                            </h2>
                            <p class="text-gray-600">{{ count($ingredientsList) }} ingrédients</p>
                            @if($fromMenuPlanner)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                    Depuis le planificateur
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Sélecteur de portions (seulement pour une recette spécifique) -->
                    @if($selectedRecipe)
                        <div class="flex items-center space-x-3">
                            <label class="text-sm font-medium text-gray-700">Nombre de personnes :</label>
                            <select wire:model.live="servings"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @for($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }} personne{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                    @else
                        <!-- Message pour les ingrédients du planificateur -->
                        <div class="text-sm text-gray-600">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                                Depuis le planificateur de menus
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            @if(count($ingredientsList) > 0)
                <!-- Liste des ingrédients -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        @if($fromMenuPlanner)
                            Liste des ingrédients (quantités calculées selon vos menus)
                        @else
                            Liste des ingrédients pour {{ $servings }} personne{{ $servings > 1 ? 's' : '' }}
                        @endif
                    </h3>
                    <!-- Liste des ingrédients calculée -->
                    <div class="space-y-3">
                        @foreach($ingredientsList as $index => $ingredient)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-white hover:shadow-md transition-shadow">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 text-lg">
                                        {{ $ingredient['adjusted_quantity'] }} {{ $ingredient['unit'] }} {{ $ingredient['name'] }}
                                    </div>
                                    @if($servings > 1 && $selectedRecipe)
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                                Calcul : {{ $ingredient['original_quantity'] }} × {{ $servings }} = {{ $ingredient['adjusted_quantity'] }} {{ $ingredient['unit'] }}
                                            </span>
                                        </div>
                                    @endif

                                    @if(!$selectedRecipe && isset($ingredient['recipes']) && !empty($ingredient['recipes']))
                                        <div class="text-sm text-gray-500 mt-1">
                                            <span class="text-xs">Utilisé dans : </span>
                                            @foreach($ingredient['recipes'] as $recipeName)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 mr-1">
                                                    {{ $recipeName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Actions d'export -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-center space-x-4">
                            <button wire:click="exportToPdf"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                Export PDF
                            </button>
                            <button wire:click="exportToExcel"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

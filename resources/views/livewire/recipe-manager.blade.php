@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Recettes</h2>
            <button wire:click="create" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Ajouter une recette
            </button>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Barre de recherche -->
        <div class="mb-4">
            <input wire:model.live="search" type="text" placeholder="Rechercher une recette..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Liste des recettes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($recipes as $recipe)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <img src="{{ $recipe->image ? Storage::url($recipe->image) : $recipe->image }}" alt="{{ $recipe->name }}"
                         class="w-full h-48 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition-opacity"
                         wire:click="showRecipeDetails({{ $recipe->id }})">

                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $recipe->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($recipe->description, 100) }}</p>

                        <!-- Ingrédients -->
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Ingrédients :</h4>
                            <div class="text-xs text-gray-600">
                                @if($recipe->ingredients->count() > 0)
                                    @foreach($recipe->ingredients->take(3) as $ingredient)
                                        <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1">
                                            {{ $ingredient->name }}
                                        </span>
                                    @endforeach
                                    @if($recipe->ingredients->count() > 3)
                                        <span class="text-gray-500">+{{ $recipe->ingredients->count() - 3 }} autres</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Aucun ingrédient ajouté</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Publié par : {{ $recipe->author_name }}</span>
                            <div class="space-x-2">
                                @if($recipe->canEdit())
                                    <button wire:click="edit({{ $recipe->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        Modifier
                                    </button>
                                @endif

                                @if($recipe->canDelete())
                                    <button wire:click="delete({{ $recipe->id }})"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette recette ?')"
                                            class="text-red-600 hover:text-red-900 text-sm">
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $recipes->links() }}
        </div>
    </div>

    <!-- Modal pour créer/modifier -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:click="closeModal"
             x-data
             x-on:keydown.escape.window="$wire.closeModal()">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editingId ? 'Modifier la recette' : 'Ajouter une recette' }}
                    </h3>

                    <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Informations de base -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nom de la recette</label>
                                <input wire:model="name" type="text"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                                <textarea wire:model="description" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Instructions de préparation</label>
                                <textarea wire:model="instructions" rows="6"
                                          placeholder="Décrivez les étapes de préparation de votre recette..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                                @error('instructions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    Image de la recette <span class="text-red-500">*</span>
                                </label>

                                <!-- Affichage de l'image actuelle en mode édition -->
                                @if($editingId && $image)
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
                                        <img src="{{ Storage::url($image) }}" alt="Image actuelle"
                                             class="w-32 h-32 object-cover rounded-lg border">
                                        <p class="text-xs text-gray-500 mt-1">Choisissez un nouveau fichier pour remplacer cette image</p>
                                    </div>
                                @endif

                                <!-- Input file -->
                                <input wire:model="imageFile" type="file" accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF (max 2MB)</p>
                                @error('imageFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <!-- Prévisualisation de la nouvelle image -->
                                @if($imageFile)
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-600 mb-2">Aperçu :</p>
                                        <img src="{{ $imageFile->temporaryUrl() }}" alt="Aperçu"
                                             class="w-32 h-32 object-cover rounded-lg border">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Sélection des ingrédients -->
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Ingrédients</h4>

                            <!-- Barre de recherche pour les ingrédients -->
                            <div class="mb-3">
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live="ingredientSearch"
                                           placeholder="Rechercher un ingrédient..."
                                           class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3">
                                @if($ingredients->count() > 0)
                                    @foreach($ingredients as $ingredient)
                                        <div class="py-2 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model.live="selectedIngredients"
                                                           value="{{ $ingredient->id }}"
                                                           class="mr-3">
                                                    <span class="text-sm">{{ $ingredient->name }} ({{ $ingredient->unite }})</span>
                                                </div>

                                                @if(in_array($ingredient->id, $selectedIngredients))
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number"
                                                               wire:model.live="ingredientQuantities.{{ $ingredient->id }}"
                                                               placeholder="Qté"
                                                               step="0.1"
                                                               min="0"
                                                               class="w-20 px-2 py-1 text-xs border border-gray-300 rounded">
                                                        <select wire:model.live="ingredientUnits.{{ $ingredient->id }}"
                                                                class="w-20 px-2 py-1 text-xs border border-gray-300 rounded">
                                                            <option value="g">g</option>
                                                            <option value="kg">kg</option>
                                                            <option value="ml">ml</option>
                                                            <option value="l">l</option>
                                                            <option value="pièce">pièce</option>
                                                            <option value="cuillère">cuillère</option>
                                                        </select>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <p class="text-sm">Aucun ingrédient trouvé</p>
                                        @if(!empty($ingredientSearch))
                                            <p class="text-xs mt-1">Essayez un autre terme de recherche</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 mt-6">
                            <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                {{ $editingId ? 'Modifier' : 'Ajouter' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal détaillée de la recette -->
    @if($showDetailModal && $selectedRecipe)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:click="closeDetailModal"
             x-data
             x-on:keydown.escape.window="$wire.closeDetailModal()">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- En-tête -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $selectedRecipe->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Publié par : {{ $selectedRecipe->author_name }}</p>
                        </div>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Image -->
                        <div>
                            <img src="{{ $selectedRecipe->image ? Storage::url($selectedRecipe->image) : $selectedRecipe->image }}" alt="{{ $selectedRecipe->name }}"
                                 class="w-full h-64 object-cover rounded-lg">
                        </div>

                        <!-- Ingrédients -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Ingrédients</h4>
                            @if($selectedRecipe->ingredients->count() > 0)
                                <div class="space-y-2">
                                    @foreach($selectedRecipe->ingredients as $ingredient)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <span class="font-medium">{{ $ingredient->name }}</span>
                                            <span class="text-gray-600">
                                                {{ $ingredient->pivot->quantity }} {{ $ingredient->pivot->unit }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Aucun ingrédient ajouté</p>
                            @endif
                        </div>
                    </div>

                    <!-- Préparation -->
                    @if($selectedRecipe->instructions)
                        <div class="mt-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Préparation</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $selectedRecipe->instructions }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex justify-end mt-6">
                        <button wire:click="closeDetailModal"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

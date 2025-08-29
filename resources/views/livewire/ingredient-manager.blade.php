<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Ingrédients</h2>
            <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Ajouter un ingrédient
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
            <input wire:model.live="search" type="text" placeholder="Rechercher un ingrédient..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Liste des ingrédients -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publié par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ingredients as $ingredient)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $ingredient->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ingredient->unite }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ingredient->author_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($ingredient->canEdit())
                                    <button wire:click="edit({{ $ingredient->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Modifier
                                    </button>
                                @endif

                                @if($ingredient->canDelete())
                                    <button wire:click="delete({{ $ingredient->id }})"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ingrédient ?')"
                                            class="text-red-600 hover:text-red-900">
                                        Supprimer
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $ingredients->links() }}
        </div>
    </div>

    <!-- Modal pour créer/modifier -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:click="closeModal"
             x-data
             x-on:keydown.escape.window="$wire.closeModal()">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editingId ? 'Modifier l\'ingrédient' : 'Ajouter un ingrédient' }}
                    </h3>

                    <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                            <input wire:model="name" type="text"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Unité</label>
                            <select wire:model="unite"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Sélectionner une unité</option>
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="l">l</option>
                                <option value="ml">ml</option>
                                <option value="pièce">pièce</option>
                                <option value="cuillère">cuillère</option>
                            </select>
                            @error('unite') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(auth()->user()->isAdmin() && !$editingId)
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="isGlobal"
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">
                                        Ingrédient global (disponible pour tous les utilisateurs)
                                    </span>
                                </label>
                            </div>
                        @endif

                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ $editingId ? 'Modifier' : 'Ajouter' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

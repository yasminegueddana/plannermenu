@php
use Illuminate\Support\Facades\Auth;
@endphp

<div>
    <!-- Messages Flash -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tableau des utilisateurs -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Utilisateurs du système</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $user->isAdmin() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($user->id !== Auth::id())
                                    <button wire:click="openEditModal({{ $user->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Modifier
                                    </button>
                                    <button wire:click="toggleUserStatus({{ $user->id }})"
                                            class="text-orange-600 hover:text-orange-900 mr-3">
                                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                    <button wire:click="openDeleteModal({{ $user->id }})"
                                            class="text-red-600 hover:text-red-900">
                                        Supprimer
                                    </button>
                                @else
                                    <span class="text-gray-400">Vous</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-semibold text-blue-800">Total Utilisateurs</h4>
            <p class="text-2xl font-bold text-blue-600">{{ $totalUsers }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h4 class="font-semibold text-green-800">Utilisateurs Actifs</h4>
            <p class="text-2xl font-bold text-green-600">{{ $activeUsers }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <h4 class="font-semibold text-purple-800">Utilisateurs Désactivés</h4>
            <p class="text-2xl font-bold text-purple-600">{{ $desactiveUsers }}</p>
        </div>
    </div>

    <!-- Modal d'édition -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
             wire:click="closeEditModal">
            <div class="mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
                 wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Modifier l'utilisateur
                    </h3>

                    <form wire:submit.prevent="updateUser">
                        <div class="mb-4">
                            <label for="editName" class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text"
                                   wire:model="editName"
                                   id="editName"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('editName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email"
                                   wire:model="editEmail"
                                   id="editEmail"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('editEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="editRole" class="block text-sm font-medium text-gray-700">Rôle</label>
                            <select wire:model="editRole"
                                    id="editRole"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="user">Utilisateur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                            @error('editRole') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       wire:model="editIsActive"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Compte actif</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3 mt-4">
                            <button type="submit"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Sauvegarder
                            </button>
                            <button type="button"
                                    wire:click="closeEditModal"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de suppression -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:click="closeDeleteModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
                 wire:click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">
                        Confirmer la suppression
                    </h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Êtes-vous sûr de vouloir supprimer l'utilisateur
                            <strong>{{ $deletingUser?->name }}</strong> ?
                            Cette action est irréversible.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="closeDeleteModal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Annuler
                        </button>
                        <button wire:click="deleteUser"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

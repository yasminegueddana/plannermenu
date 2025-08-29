<div class="container mx-auto px-4 py-6">
    <!-- En-tête avec navigation du calendrier -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Planification des Menus</h2>

            @if(!$currentMenu)
                <button wire:click="$set('showCreateMenu', true)"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Créer un nouveau menu
                </button>
            @endif
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Navigation du calendrier -->
        <div class="flex justify-between items-center">
            <button wire:click="changeMonth('prev')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                ← Mois précédent
            </button>

            <div class="text-center">
                <h3 class="text-xl font-semibold capitalize">{{ $monthName }}</h3>
                @if($currentMenu)
                    <p class="text-sm text-gray-600">Menu actuel : {{ $currentMenu->name }}</p>
                @endif
            </div>

            <button wire:click="changeMonth('next')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Mois suivant →
            </button>
        </div>
    </div>

    @if($currentMenu)
      

        <!-- Calendrier amélioré -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- En-têtes des jours de la semaine -->
            <div class="grid grid-cols-7 gap-2 mb-4">
                @foreach($weekDays as $dayName)
                    <div class="text-center font-semibold text-gray-700 py-3 bg-gray-50 rounded">{{ $dayName }}</div>
                @endforeach
            </div>

            <!-- Sélection rapide de semaines (en mode sélection) -->
            @if($showDateSelection)
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">Sélection rapide par semaine :</h4>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $weeks = [];
                            $currentWeek = [];
                            $weekStart = null;

                            foreach($calendarDays as $index => $day) {
                                if($index % 7 === 0) {
                                    if(!empty($currentWeek)) {
                                        $weeks[] = ['start' => $weekStart, 'days' => $currentWeek];
                                    }
                                    $currentWeek = [];
                                    $weekStart = $day['fullDate'];
                                }
                                $currentWeek[] = $day;
                            }
                            if(!empty($currentWeek)) {
                                $weeks[] = ['start' => $weekStart, 'days' => $currentWeek];
                            }
                        @endphp

                        @foreach($weeks as $weekIndex => $week)
                            @php
                                $weekHasPlannedMeals = false;
                                foreach($week['days'] as $day) {
                                    if($day['isCurrentMonth'] && isset($plannedMeals[$day['fullDate']])) {
                                        $weekHasPlannedMeals = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if($weekHasPlannedMeals)
                                <button wire:click="selectWeek('{{ $week['start'] }}')"
                                        class="px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-full border border-blue-300 transition-colors">
                                    Semaine {{ $weekIndex + 1 }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Grille du calendrier -->
            <div class="grid grid-cols-7 gap-2">
                @foreach($calendarDays as $day)
                    <div class="border-2 border-gray-200 min-h-40 p-2 rounded-lg {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500 border-blue-300' : '' }} hover:shadow-md transition-shadow">
                        <!-- Numéro du jour -->
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400' }} {{ $day['isToday'] ? 'text-blue-600' : '' }}">
                                {{ $day['dayNumber'] }}
                            </div>
                            @if($day['isCurrentMonth'] && isset($plannedMeals[$day['fullDate']]))
                                <div class="flex items-center space-x-1">
                                    <div class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        {{ count(array_filter($plannedMeals[$day['fullDate']])) }}
                                    </div>

                                    @if($showDateSelection)
                                        <input type="checkbox"
                                               wire:model.live="selectedDates"
                                               value="{{ $day['fullDate'] }}"
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Repas planifiés pour ce jour -->
                        @if($day['isCurrentMonth'])
                            @foreach($mealTypes as $mealType)
                                <div class="mb-2">
                                    <!-- Icône et nom du repas -->
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="text-xs font-medium text-gray-600 flex items-center">
                                            @if($mealType === 'Petit-déjeuner')
                                                🌅
                                            @elseif($mealType === 'Déjeuner')
                                                ☀️
                                            @elseif($mealType === 'Dîner')
                                                🌙
                                            @else
                                                🍽️
                                            @endif
                                            <span class="ml-1">{{ substr($mealType, 0, 4) }}</span>
                                        </div>
                                    </div>

                                    <!-- Zone cliquable pour ajouter un repas -->
                                    <div wire:click="selectDateAndMeal('{{ $day['fullDate'] }}', '{{ $mealType }}')"
                                         class="min-h-8 border-2 border-dashed border-gray-300 rounded-md p-1 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">

                                        @if(isset($plannedMeals[$day['fullDate']][$mealType]))
                                            @foreach($plannedMeals[$day['fullDate']][$mealType] as $meal)
                                                <div class="bg-gradient-to-r from-green-100 to-green-200 text-green-800 text-xs p-2 rounded mb-1 shadow-sm relative group">
                                                    <!-- Nom du menu et infos principales -->
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="truncate font-bold">{{ $meal['menu_name'] }}</span>
                                                        @if(isset($meal['servings']) && $meal['servings'] > 1)
                                                            <span class="bg-gray-800 text-white px-2 py-1 rounded-full text-xs ml-1 font-bold shadow-sm">
                                                                {{ $meal['servings'] }}p
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Horaires -->
                                                    @if(isset($meal['start_time']) && isset($meal['end_time']))
                                                        <div class="text-green-600 text-xs mb-1">
                                                            {{ \Carbon\Carbon::parse($meal['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($meal['end_time'])->format('H:i') }}
                                                        </div>
                                                    @endif

                                                    <!-- Liste des recettes -->
                                                    @if(isset($meal['recipes']) && count($meal['recipes']) > 0)
                                                        <div class="text-green-700 text-xs">
                                                            @foreach($meal['recipes'] as $index => $recipe)
                                                                <span>{{ $recipe }}</span>@if($index < count($meal['recipes']) - 1)<span class="text-green-500"> • </span>@endif
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <button wire:click.stop="removeMeal('{{ $day['fullDate'] }}', '{{ $mealType }}', {{ implode(',', $meal['day_menu_ids'] ?? []) }})"
                                                            class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                                        ×
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-gray-400 text-xs text-center py-1 group-hover:text-blue-500 transition-colors">
                                                + Ajouter
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions du menu -->
        <div class="mt-6 flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
            @if(!$showDateSelection)
                <!-- Mode normal : seulement sélection de dates -->
                <button wire:click="toggleDateSelectionMode"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Sélectionner des dates pour générer les ingrédients
                </button>
            @else
                <!-- Mode sélection : génération pour dates spécifiques -->
                <div class="text-center mb-4">
                    <p class="text-gray-600 mb-2">
                        Sélectionnez les jours pour lesquels générer les ingrédients
                        @if(count($selectedDates) > 0)
                            <span class="text-blue-600 font-semibold">({{ count($selectedDates) }} jour(s) sélectionné(s))</span>
                        @endif
                    </p>
                </div>

                <button wire:click="generateIngredientsForSelectedDates"
                        @if(count($selectedDates) === 0) disabled @endif
                        class="@if(count($selectedDates) > 0) bg-green-500 hover:bg-green-600 @else bg-gray-400 cursor-not-allowed @endif text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Générer les ingrédients ({{ count($selectedDates) }} jour(s))
                </button>

                <button wire:click="toggleDateSelectionMode"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Annuler la sélection
                </button>
            @endif

            <button wire:click="clearAllMeals"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                🗑️ Vider le menu
            </button>
        </div>
    @else
        <!-- Message si aucun menu n'existe -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <h3 class="text-xl font-semibold mb-4">Aucun menu créé</h3>
            <p class="text-gray-600 mb-6">Créez un nouveau menu pour commencer la planification</p>
            <button wire:click="$set('showCreateMenu', true)"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Créer un nouveau menu
            </button>
        </div>
    @endif

    <!-- Modal pour sélectionner une recette -->
    @if($showMealModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative w-full max-w-6xl bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-hidden">
                <!-- En-tête du modal -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold">
                                @if($selectedMealType === 'Petit-déjeuner')
                                    🌅 Petit-déjeuner
                                @elseif($selectedMealType === 'Déjeuner')
                                    ☀️ Déjeuner
                                @elseif($selectedMealType === 'Dîner')
                                    🌙 Dîner
                                @else
                                    🍽️ {{ $selectedMealType }}
                                @endif
                            </h3>
                            @if($selectedDate)
                                <p class="text-blue-100 text-sm">
                                    {{ \Carbon\Carbon::parse($selectedDate)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Barre de recherche et sélection du nombre de personnes -->
                <div class="p-4 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Recherche -->
                        <div class="relative">
                            <input type="text"
                                   wire:model.live="searchRecipes"
                                   placeholder="Rechercher une recette..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Nombre de personnes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de personnes</label>
                            <select wire:model="servings"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }} personne{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    @if(count($selectedRecipeIds) > 0)
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-800 font-medium">Recette sélectionnée pour {{ $servings }} personne{{ $servings > 1 ? 's' : '' }}</span>
                                <button wire:click="addSelectedRecipeToMeal"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Ajouter au menu
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Liste des recettes -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    @if($recipes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($recipes as $recipe)
                                <div wire:click="selectRecipeForMeal({{ $recipe->id }})"
                                     class="bg-white border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-500 hover:shadow-lg transform hover:scale-105 transition-all duration-200 group {{ in_array($recipe->id, $selectedRecipeIds) ? 'border-blue-500 bg-blue-50' : '' }}">

                                    <!-- Image de la recette -->
                                    @if($recipe->image)
                                        <img src="{{ $recipe->image }}" alt="{{ $recipe->name }}"
                                             class="w-full h-40 object-cover rounded-lg mb-4 group-hover:brightness-110 transition-all">
                                    @else
                                        <div class="w-full h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-4 flex items-center justify-center group-hover:from-blue-50 group-hover:to-purple-50 transition-all">
                                            <span class="text-gray-400 text-4xl group-hover:text-blue-500 transition-colors">🍽️</span>
                                        </div>
                                    @endif

                                    <!-- Informations de la recette -->
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">
                                            {{ $recipe->name }}
                                        </h4>
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                            {{ Str::limit($recipe->description, 100) }}
                                        </p>

                                        <!-- Badges d'informations -->
                                        <div class="flex flex-wrap gap-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                {{ $recipe->ingredients->count() }} ingrédients
                                            </span>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                Par {{ $recipe->user->name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">🔍</div>
                            <h3 class="text-lg font-semibold text-gray-600 mb-2">Aucune recette trouvée</h3>
                            <p class="text-gray-500">Essayez de modifier votre recherche ou créez une nouvelle recette.</p>
                        </div>
                    @endif
                </div>

                <!-- Pied du modal -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            {{ $recipes->count() }} recette(s) disponible(s)
                        </p>
                        <button wire:click="$set('showMealModal', false)"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal pour créer un nouveau menu -->
    @if($showCreateMenu)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Créer un nouveau menu</h3>

                    <form wire:submit.prevent="createNewMenu">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nom du menu</label>
                            <input wire:model="menuName" type="text"
                                   placeholder="Ex: Menu {{ $monthName }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            @error('menuName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="$set('showCreateMenu', false)"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal pour gérer les menus d'un type de repas -->
    @if($showMealTypeModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4">
            <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-2xl my-4 min-h-[90vh] max-h-[95vh] overflow-hidden flex flex-col">
                <!-- En-tête du modal -->
                <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $selectedMealType }}</h3>
                            @if($selectedDate)
                                <p class="text-orange-100 text-sm">
                                    {{ \Carbon\Carbon::parse($selectedDate)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                                </p>
                            @endif
                        </div>
                        <button wire:click="closeMealTypeModal"
                                class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <!-- Formulaire pour créer un nouveau menu -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Créer un nouveau menu</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du menu</label>
                                <input type="text" wire:model="newMenuName"
                                       placeholder="Ex: Menu Continental"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <input type="time" wire:model="startTime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                                <input type="time" wire:model="endTime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de personnes</label>
                                <select wire:model="servings"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    @for($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}">{{ $i }} personne{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Sélection de recette -->
                        <div class="mt-4">
                            <h5 class="text-md font-medium text-gray-700 mb-3">Choisir une recette</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-60 overflow-y-auto">
                                @foreach($recipes as $recipe)
                                    <div wire:click="toggleRecipeSelection({{ $recipe->id }})"
                                         class="bg-white border-2 border-gray-200 rounded-lg p-3 cursor-pointer hover:border-orange-500 hover:shadow-md transition-all duration-200 {{ in_array($recipe->id, $selectedRecipeIds) ? 'border-orange-500 bg-orange-50' : '' }}">

                                        @if($recipe->image)
                                            <img src="{{ asset('storage/' . $recipe->image) }}" alt="{{ $recipe->name }}"
                                                 class="w-full h-24 object-cover rounded-lg mb-2">
                                        @else
                                            <div class="w-full h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-2 flex items-center justify-center">
                                                <span class="text-gray-400 text-2xl">🍽️</span>
                                            </div>
                                        @endif

                                        <h5 class="font-medium text-gray-800 text-sm mb-1">{{ $recipe->name }}</h5>
                                        <p class="text-xs text-gray-600">{{ $recipe->ingredients->count() }} ingrédients</p>

                                        @if(in_array($recipe->id, $selectedRecipeIds))
                                            <div class="mt-2 text-center">
                                                <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">✓ Sélectionnée</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if(count($selectedRecipeIds) > 0 && $newMenuName && $startTime && $endTime && $servings)
                            <div class="mt-4 p-4 bg-green-50 border-2 border-green-300 rounded-lg">
                                <div class="text-center">
                                    <h4 class="text-lg font-bold text-green-800 mb-3">✅ Prêt à créer le menu !</h4>
                                    <p class="text-green-700 mb-2">
                                        Menu "{{ $newMenuName }}" pour {{ $servings }} personne{{ $servings > 1 ? 's' : '' }}<br>
                                        de {{ $startTime }} à {{ $endTime }}
                                    </p>
                                    <p class="text-green-600 text-sm mb-4">
                                        {{ count($selectedRecipeIds) }} recette{{ count($selectedRecipeIds) > 1 ? 's' : '' }} sélectionnée{{ count($selectedRecipeIds) > 1 ? 's' : '' }}
                                    </p>
                                    <button wire:click="createMealMenuWithRecipe"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition-colors text-lg">
                                        🍽️ CRÉER LE MENU ({{ count($selectedRecipeIds) }} recette{{ count($selectedRecipeIds) > 1 ? 's' : '' }})
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-yellow-800 text-center">
                                    ⚠️ Veuillez remplir tous les champs et sélectionner au moins une recette
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Liste des menus existants -->
                    @if(count($currentMealMenus) > 0)
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800">Menus existants</h4>

                            @foreach($currentMealMenus as $menu)
                                <div class="bg-white border-2 border-gray-200 rounded-lg p-4 {{ $menu['is_active'] ? 'border-green-500 bg-green-50' : '' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h5 class="text-lg font-semibold text-gray-800">{{ $menu['menu_name'] }}</h5>
                                                @if($menu['is_active'])
                                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">ACTIF</span>
                                                @endif
                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                    {{ $menu['servings'] }} personne{{ $menu['servings'] > 1 ? 's' : '' }}
                                                </span>
                                            </div>

                                            @if($menu['start_time'] && $menu['end_time'])
                                                <p class="text-sm text-gray-600 mb-2">
                                                    🕐 {{ $menu['start_time'] }} - {{ $menu['end_time'] }}
                                                </p>
                                            @endif

                                            @if(isset($menu['recipes']) && count($menu['recipes']) > 0)
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <!-- Afficher chaque recette avec ses ingrédients -->
                                                    @foreach($menu['recipes'] as $recipe)
                                                        <div class="mb-4 {{ !$loop->last ? 'pb-4 border-b border-gray-200' : '' }}">
                                                            <!-- En-tête de la recette -->
                                                            <div class="flex items-center gap-3 mb-3">
                                                                @if($recipe['image'])
                                                                    <img src="{{ asset('storage/' . $recipe['image']) }}" alt="{{ $recipe['name'] }}"
                                                                         class="w-12 h-12 object-cover rounded-lg">
                                                                @endif
                                                                <div>
                                                                    <h6 class="font-medium text-gray-800">{{ $recipe['name'] }}</h6>
                                                                    <p class="text-xs text-gray-600">{{ count($recipe['ingredients']) }} ingrédients</p>
                                                                </div>
                                                            </div>

                                                            <!-- Ingrédients de cette recette -->
                                                            @if(count($recipe['ingredients']) > 0)
                                                                <div class="ml-4">
                                                                    <h6 class="text-sm font-medium text-gray-700 mb-2">
                                                                        Ingrédients pour {{ $menu['servings'] }} personne{{ $menu['servings'] > 1 ? 's' : '' }} :
                                                                    </h6>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                                                        @foreach($recipe['ingredients'] as $ingredient)
                                                                            <div class="text-xs bg-white rounded p-2 border">
                                                                                <div class="font-medium text-gray-800">
                                                                                    {{ $ingredient['adjusted_quantity'] }} {{ $ingredient['unit'] }} {{ $ingredient['name'] }}
                                                                                </div>
                                                                                @if($ingredient['servings'] > 1)
                                                                                    <div class="text-gray-500 mt-1">
                                                                                        ({{ $ingredient['original_quantity'] }} × {{ $ingredient['servings'] }})
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="ml-4">
                                            <button wire:click="deleteMealMenu({{ $menu['id'] }})"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')"
                                                    class="bg-red-500 hover:bg-red-700 text-white text-sm font-bold py-1 px-3 rounded transition-colors">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <h4 class="text-lg font-semibold text-gray-600 mb-2">Aucun menu pour ce {{ strtolower($selectedMealType) }}</h4>
                            <p class="text-gray-500">Créez votre premier menu avec le formulaire ci-dessus</p>
                        </div>
                    @endif
                </div>

                <!-- Pied de page -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button wire:click="closeMealTypeModal"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Fermer
                            </button>


                        </div>

                        @if(count($selectedRecipeIds) > 0 && $newMenuName && $startTime && $endTime && $servings)
                            <button wire:click="createMealMenuWithRecipe"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Ajouter au menu
                            </button>
                        @else
                            <button disabled
                                    class="bg-gray-300 text-gray-500 font-bold py-2 px-6 rounded-lg cursor-not-allowed flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Ajouter au menu
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

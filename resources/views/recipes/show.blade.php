<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recipe->name }} - Menu Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fadeIn': 'fadeIn 0.3s ease-out',
                        'slideUp': 'slideUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                        'scaleIn': 'scaleIn 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .backdrop-blur {
            backdrop-filter: blur(8px);
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation avec retour -->
    <nav class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center group">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                        </div>
                        <span class="ml-3 text-lg font-bold text-gray-900">Menu Planner</span>
                    </a>
                </div>
                <a href="/" class="group flex items-center text-gray-600 hover:text-blue-600 font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal avec déclenchement du modal automatique -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 animate-pulse">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="text-gray-600">Chargement de la recette...</p>
        </div>
    </div>

    <!-- Modal de la recette -->
    <div id="recipeModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur z-50 opacity-0 pointer-events-none transition-all ">
        <div class="min-h-screen px-4 py-6 flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform scale-95 transition-all duration-300" id="modalContent">
                
                <!-- Header -->
                <div class="flex justify-between items-start p-6 border-b border-gray-100">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $recipe->name }}</h1>
                        <p class="text-sm text-gray-500">Publié par : {{ $recipe->user->name ?? 'Auteur inconnu' }}</p>
                    </div>
                    <button onclick="window.history.back()" class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenu scrollable -->
                <div class="overflow-y-auto scrollbar-hide" style="max-height: calc(90vh - 140px);">
                    <div class="grid lg:grid-cols-5 gap-0">
                        
                        <!-- Image principale -->
                        <div class="lg:col-span-2">
                            <div class="relative h-80 lg:h-full">
                                @if($recipe->image)
                                    <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <div class="text-gray-400 text-4xl">🍽️</div>
                                    </div>
                                @endif
                                
                                <!-- Badge temps de cuisson -->
                                <div class="absolute top-4 right-4 bg-white bg-opacity-90 backdrop-blur text-gray-800 px-3 py-2 rounded-full text-sm font-medium shadow-lg">
                                    <svg class="w-4 h-4 inline mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $recipe->cook_time ?? '30 min' }}
                                </div>
                            </div>
                        </div>

                        <!-- Ingrédients -->
                        <div class="lg:col-span-3 p-6">
                            <div class="mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    Ingrédients
                                </h2>
                                
                                <div class="space-y-3">
                                    @if($recipe->ingredients->count() > 0)
                                        @foreach($recipe->ingredients as $ingredient)
                                            <div class="flex justify-between items-center py-3 px-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                                                <span class="font-medium text-gray-800">{{ $ingredient->name }}</span>
                                                <span class="text-gray-600 bg-white px-3 py-1 rounded-full text-sm font-medium">
                                                    {{ $ingredient->pivot->quantity }} {{ $ingredient->pivot->unit }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-8 text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <p class="italic text-lg">Aucun ingrédient ajouté</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions de préparation -->
                    @if($recipe->instructions)
                        <div class="px-6 pb-6">
                            <div class="border-t border-gray-100 pt-6">
                                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    Préparation
                                </h2>
                                
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                                    <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $recipe->instructions }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Description si pas d'instructions -->
                    @if(!$recipe->instructions && $recipe->description)
                        <div class="px-6 pb-6">
                            <div class="border-t border-gray-100 pt-6">
                                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    Description
                                </h2>
                                
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
                                    <div class="text-gray-700 leading-relaxed">{{ $recipe->description }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer avec boutons d'action -->
                <div class="border-t border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row gap-3 justify-end">
                        <a href="/" class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200 text-center">
                            Retour à l'accueil
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Ouvrir automatiquement le modal au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('recipeModal');
            const modalContent = document.getElementById('modalContent');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100');
                
                setTimeout(() => {
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                }, 10);
            }, 300);
        });

        // Gestion de la fermeture
        function closeModal() {
            window.history.back();
        }

        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Fermer en cliquant à l'extérieur
        document.getElementById('recipeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
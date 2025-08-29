<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu Planner - Découvrez des saveurs exceptionnelles</title>
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
                        'float': 'float 6s ease-in-out infinite',
                        'fadeIn': 'fadeIn 1s ease-out',
                        'slideUp': 'slideUp 0.8s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .recipe-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="font-inter bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-sm shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Menu Planner
                        </span>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="#accueil" class="text-gray-900 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium transition-all duration-300">Accueil</a>
                        <a href="#recettes" class="text-gray-500 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium transition-all duration-300">Recettes</a>
                        <a href="#apropos" class="text-gray-500 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium transition-all duration-300">À propos</a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-500 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium transition-all duration-300">Connexion</a>
                    <a href="/register" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-2 rounded-full text-sm font-medium transition-all duration-300 transform hover:scale-105">
                        S'inscrire
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Formes décoratives -->
        <div class="absolute top-10 left-10 w-72 h-72 bg-white/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
        <div class="absolute top-0 right-4 w-72 h-72 bg-pink-300/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-yellow-300/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 4s;"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-16">
            <div class="text-center animate-fadeIn">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight">
                    Découvrez des
                    <span class="bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                        saveurs
                    </span>
                    <br>exceptionnelles
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Explorez notre collection de recettes authentiques, partagez vos créations culinaires et rejoignez une communauté passionnée de cuisine.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-6 justify-center mb-16">
                    <a href="/register" class="bg-white text-purple-600 px-8 py-4 rounded-full text-lg font-semibold shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                        Commencer l'aventure
                    </a>
                    <a href="#recettes" class="border-2 border-white text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white hover:text-purple-600 transition-all duration-300">
                        Voir les recettes
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-7 mt-20 animate-slideUp">
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">{{ $totalRecipes }}+</div>
                    <div class="text-white/80">Recettes</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">{{ $totalUsers }}+</div>
                    <div class="text-white/80">Utilisateurs</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">{{ $totalIngredients }}+</div>
                    <div class="text-white/80">Ingrédients</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Recettes Populaires -->
    <section id="recettes" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Nos délicieuses
                    <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        recettes
                    </span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Découvrez toutes les recettes partagées par notre communauté
                </p>
            </div>

            <!-- Liste des recettes de la base de données -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($recipes as $recipe)
                <div class="recipe-card bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="relative h-64">
                        @if($recipe->image)
                            <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                                <span class="text-white text-6xl">🍽️</span>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-gray-800 text-xs px-3 py-1 rounded-full font-medium">
                            {{ $recipe->cook_time ?? '30min' }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $recipe->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit($recipe->description, 100) }}</p>
                        
                        <!-- Ingrédients -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Ingrédients :</h4>
                            <div class="flex flex-wrap gap-2">
                                @if($recipe->ingredients->count() > 0)
                                    @foreach($recipe->ingredients->take(3) as $ingredient)
                                        <span class="inline-block bg-gray-100 rounded-full px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ $ingredient->name }}
                                        </span>
                                    @endforeach
                                    @if($recipe->ingredients->count() > 3)
                                        <span class="inline-block bg-gray-100 rounded-full px-3 py-1 text-xs font-medium text-gray-700">
                                            +{{ $recipe->ingredients->count() - 3 }} autres
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic text-xs">Aucun ingrédient ajouté</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Par {{ $recipe->user->name ?? 'Auteur inconnu' }}</span>
                            <a href="{{ route('recipes.show', $recipe->id) }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                                Voir la recette →
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($recipes->hasPages())
            <div class="mt-12">
                {{ $recipes->links() }}
            </div>
            @endif

            <!-- Message si aucune recette -->
            @if($recipes->count() == 0)
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune recette disponible</h3>
                <p class="text-gray-500">Soyez le premier à partager une recette !</p>
                <a href="/register" class="mt-4 inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                    Commencer maintenant
                </a>
            </div>
            @endif
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Pourquoi choisir notre
                    <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        plateforme ?
                    </span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Recettes authentiques</h3>
                    <p class="text-gray-600 leading-relaxed">Des recettes partagées par une communauté passionnée de cuisine</p>
                </div>

                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2V2h2v2h4V2h2v2zm-2 2H10v2H8V6H6v14h12V6h-2v2h-2V6z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Instructions détaillées</h3>
                    <p class="text-gray-600 leading-relaxed">Des étapes claires et précises pour réussir vos plats à coup sûr</p>
                </div>

                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Communauté active</h3>
                    <p class="text-gray-600 leading-relaxed">Échangez avec d'autres passionnés et partagez vos propres créations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="apropos" class="py-20 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-8">
                Prêt à commencer votre
                <span class="bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                    aventure culinaire ?
                </span>
            </h2>
            <p class="text-xl text-white/90 mb-12 leading-relaxed">
                Rejoignez notre communauté et partagez vos propres recettes avec des passionnés de cuisine
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/register" class="bg-white text-purple-600 px-8 py-4 rounded-full text-lg font-semibold shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                    Créer mon compte gratuit
                </a>
                <a href="/login" class="border-2 border-white text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white hover:text-purple-600 transition-all duration-300">
                    Se connecter
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <svg class="h-8 w-8 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                            Menu Planner
                        </span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Votre destination pour découvrir, créer et partager des recettes exceptionnelles avec une communauté passionnée de cuisine.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Liens rapides</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#accueil" class="hover:text-white transition-colors">Accueil</a></li>
                        <li><a href="#recettes" class="hover:text-white transition-colors">Recettes</a></li>
                        <li><a href="#apropos" class="hover:text-white transition-colors">À propos</a></li>
                        <li><a href="/login" class="hover:text-white transition-colors">Connexion</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Aide</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Conditions</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Confidentialité</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Menu Planner. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slideUp');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.recipe-card').forEach(card => {
            observer.observe(card);
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
<x-app-layout>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
    </style>
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">Tableau de Bord</h1>

        
        <!-- Cartes KPI -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-gray-500 mb-2">Total Ingrédients</span>
                <span class="text-3xl font-bold text-blue-500">{{ $totalIngredients }}</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-gray-500 mb-2">Total Utilisateurs</span>
                <span class="text-3xl font-bold text-pink-500">{{ $totalUsers }}</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-gray-500 mb-2">Jours couverts</span>
                <span class="text-3xl font-bold text-yellow-500">{{ $coveredDays }}</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-gray-500 mb-2">Menus Créés</span>
                <span class="text-3xl font-bold text-purple-500">{{ $totalMenus }}</span>
            </div>
        </div>


        <!-- Grille des graphiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Cartes de statistiques -->
   
            <div class="chart-container">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Répartition des Rôles</h3>
                <canvas id="roleChart"></canvas>
            </div>

            <div class="chart-container">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Repas Planifiés</h3>
                <canvas id="mealsChart"></canvas>
            </div>

            
            <div class="chart-container">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Recettes par Utilisateur</h3>
                <canvas id="recipesPerUserChart"></canvas>
            </div>
        </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="chart-container h-[450px]">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Top 5 Recettes</h3>
                <canvas id="topRecipesChart"></canvas>
            </div>

            <div class="chart-container h-[450px]">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Évolution Mensuelle</h3>
                <canvas id="recipesByMonthChart"></canvas>
            </div>
        </div>
    </div>

        
    <script>  
    document.addEventListener('livewire:navigated', function() {
                    // Données PHP passées au JavaScript
        const data = {
            totalIngredients: {{ $totalIngredients }},
            totalUsers: {{ $totalUsers }},
            roleDistribution: @json($roleDistribution),
            totalMenus: {{ $totalMenus }},
            mealsPerWeek: {{ $mealsPerWeek }},
            mealsPerMonth: {{ $mealsPerMonth }},
            topRecipes: @json($topRecipes),
            recipesPerUser: @json($recipesPerUser),
            recipesByMonth: @json($recipesByMonth)
        };
      

        console.log('Données du dashboard:', data);

       

        // 3. Graphique Répartition des rôles
        if (document.getElementById('roleChart') && data.roleDistribution.length > 0) {
            new Chart(document.getElementById('roleChart'), {
                type: 'pie',
                data: {
                    labels: data.roleDistribution.map(r => r.role),
                    datasets: [{
                        data: data.roleDistribution.map(r => r.total),
                        backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }


        // 4. Graphique Repas planifiés

        if (document.getElementById('mealsChart')) {
    new Chart(document.getElementById('mealsChart'), {
        type: 'pie',
        data: {
            labels: ['Cette semaine', 'Ce mois'],
            datasets: [{
                data: [data.mealsPerWeek, data.mealsPerMonth],
                backgroundColor: ['#FF9F40', '#FF6384']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

        // 5. Graphique Top recettes
        if (document.getElementById('topRecipesChart') && data.topRecipes.length > 0) {
            new Chart(document.getElementById('topRecipesChart'), {
                type: 'bar',
                data: {
                    labels: data.topRecipes.map(r => r.recipe ? r.recipe.name : 'Recette inconnue'),
                    datasets: [{
                        label: 'Nombre d\'utilisations',
                        data: data.topRecipes.map(r => r.total),
                        backgroundColor: '#36A2EB'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }


        if (document.getElementById('recipesPerUserChart') && data.recipesPerUser.length > 0) {
    // Génère une couleur différente pour chaque utilisateur
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8BC34A', '#E91E63', '#00BCD4', '#CDDC39'
    ];
    const userColors = data.recipesPerUser.map((u, i) => colors[i % colors.length]);


            //6 graphique Recettes par Utilisateur
    new Chart(document.getElementById('recipesPerUserChart'), {
        type: 'bar',
        data: {
            labels: data.recipesPerUser.map(u => u.name),
            datasets: [{
                label: 'Nombre de recettes',
                data: data.recipesPerUser.map(u => u.recipes_count),
                backgroundColor: userColors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

        // 7. Graphique Évolution mensuelle
        if (document.getElementById('recipesByMonthChart') && data.recipesByMonth.length > 0) {
            new Chart(document.getElementById('recipesByMonthChart'), {
                type: 'line',
                data: {
                    labels: data.recipesByMonth.map(r => r.month + '/' + r.year),
                    datasets: [{
                        label: 'Recettes créées',
                        data: data.recipesByMonth.map(r => r.total),
                        borderColor: '#4BC0C0',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
    </script>

</x-app-layout>
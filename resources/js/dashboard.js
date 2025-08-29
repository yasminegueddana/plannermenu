console.log("Dashboard JS loaded");

document.addEventListener('DOMContentLoaded', function () {
    const dataDiv = document.getElementById('dashboard-data');
    if (!dataDiv) return;

    // Récupération des données depuis les attributs data
    const totalIngredients = Number(dataDiv.dataset.totalIngredients);
    const activeUsers = Number(dataDiv.dataset.activeUsers);
    const roleDistribution = JSON.parse(dataDiv.dataset.roleDistribution || '[]');
    const totalMenus = Number(dataDiv.dataset.totalMenus);
    const mealsPerWeek = Number(dataDiv.dataset.mealsPerWeek);
    const mealsPerMonth = Number(dataDiv.dataset.mealsPerMonth);
    const daysWithMeals = Number(dataDiv.dataset.daysWithMeals);
    const topRecipes = JSON.parse(dataDiv.dataset.topRecipes || '[]');
    const recipesPerUser = JSON.parse(dataDiv.dataset.recipesPerUser || '[]');
    const recipesByMonth = JSON.parse(dataDiv.dataset.recipesByMonth || '[]');

    // Ingrédients
    new Chart(document.getElementById('ingredientsChart'), {
        type: 'doughnut',
        data: {
            labels: ['Ingrédients'],
            datasets: [{
                data: [totalIngredients],
                backgroundColor: ['#36A2EB']
            }]
        }
    });

    // Utilisateurs actifs
    new Chart(document.getElementById('activeUsersChart'), {
        type: 'doughnut',
        data: {
            labels: ['Utilisateurs actifs'],
            datasets: [{
                data: [activeUsers],
                backgroundColor: ['#FF6384']
            }]
        }
    });

    // Répartition des rôles
    new Chart(document.getElementById('roleChart'), {
        type: 'pie',
        data: {
            labels: roleDistribution.map(r => r.role),
            datasets: [{
                data: roleDistribution.map(r => r.total),
                backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384']
            }]
        }
    });

    // Menus créés
    new Chart(document.getElementById('menusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Menus créés'],
            datasets: [{
                data: [totalMenus],
                backgroundColor: ['#4BC0C0']
            }]
        }
    });

    // Nombre moyen de repas planifiés
    new Chart(document.getElementById('mealsChart'), {
        type: 'bar',
        data: {
            labels: ['Semaine', 'Mois'],
            datasets: [{
                label: 'Repas planifiés',
                data: [mealsPerWeek, mealsPerMonth],
                backgroundColor: ['#9966FF', '#FF9F40']
            }]
        }
    });

    // Jours couverts par les menus
    new Chart(document.getElementById('daysChart'), {
        type: 'doughnut',
        data: {
            labels: ['Jours couverts'],
            datasets: [{
                data: [daysWithMeals],
                backgroundColor: ['#FFCD56']
            }]
        }
    });

    // Top recettes utilisées
    new Chart(document.getElementById('topRecipesChart'), {
        type: 'bar',
        data: {
            labels: topRecipes.map(r => r.recipe ? r.recipe.name : 'Recette'),
            datasets: [{
                label: 'Utilisation',
                data: topRecipes.map(r => r.total),
                backgroundColor: '#36A2EB'
            }]
        }
    });

    // Nombre total de recettes créées par utilisateur
    new Chart(document.getElementById('recipesPerUserChart'), {
        type: 'bar',
        data: {
            labels: recipesPerUser.map(u => u.name),
            datasets: [{
                label: 'Recettes créées',
                data: recipesPerUser.map(u => u.recipes_count),
                backgroundColor: '#FF6384'
            }]
        }
    });

    // Évolution du nombre de recettes créées par mois
    new Chart(document.getElementById('recipesByMonthChart'), {
        type: 'line',
        data: {
            labels: recipesByMonth.map(r => r.month),
            datasets: [{
                label: 'Recettes créées',
                data: recipesByMonth.map(r => r.total),
                borderColor: '#4BC0C0',
                backgroundColor: 'rgba(75,192,192,0.2)',
                fill: true
            }]
        }
    });
});
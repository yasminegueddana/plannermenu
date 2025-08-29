<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de Courses - {{ $recipe->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #6B7280;
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        .recipe-info {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .recipe-info h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 20px;
        }
        .recipe-info p {
            margin: 5px 0;
            color: #6B7280;
        }
        .ingredients-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .ingredients-table th {
            background-color: #4F46E5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .ingredients-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        .ingredients-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        .ingredients-table tr:hover {
            background-color: #F3F4F6;
        }
        .quantity {
            font-weight: bold;
            color: #059669;
        }

        .calculation {
            font-size: 12px;
            color: #6B7280;
            font-style: italic;
        }
        .total-section {
            background-color: #FEF3C7;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }
        .total-section h3 {
            margin: 0;
            color: #92400E;
            font-size: 18px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6B7280;
            font-size: 12px;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
        }
 
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste de Courses</h1>
        <h2>{{ $recipe->name }}</h2>
    </div>

    <div class="recipe-info">
        <h3>Informations de la recette</h3>
        <p><strong>Recette :</strong> {{ $recipe->name }}</p>
        <p><strong>Nombre de personnes :</strong> {{ $servings }} personne{{ $servings > 1 ? 's' : '' }}</p>
        <p><strong>Nombre d'ingrédients :</strong> {{ count($ingredients) }}</p>
        <p><strong>Généré le :</strong> {{ $generatedAt }}</p>
    </div>

    <table class="ingredients-table">
        <thead>
            <tr>
                <th>Ingrédient</th>
                <th style="width: 100px;">Quantité</th>
                <th style="width: 60px;">Unité</th>
                <th style="width: 120px;">Calcul</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingredients as $ingredient)
                <tr>
                    <td>{{ $ingredient['name'] }}</td>
                    <td class="quantity">{{ $ingredient['adjusted_quantity'] }}</td>
                    <td>{{ $ingredient['unit'] }}</td>
                    <td class="calculation">
                        {{ $ingredient['original_quantity'] }} × {{ $servings }} = {{ $ingredient['adjusted_quantity'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

 
    <div class="footer">
        <p>Liste générée automatiquement par MenuPlanner - {{ $generatedAt }}</p>
        <p>Cochez les cases lors de vos achats pour suivre votre progression</p>
    </div>
</body>
</html>

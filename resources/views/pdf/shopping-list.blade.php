<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de Courses - {{ $menuName }}</title>
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
        .menu-info {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .menu-info h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 20px;
        }
        .menu-info p {
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
            font-size: 13px;
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
        .recipes {
            color: #6B7280;
            font-style: italic;
            font-size: 12px;
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
        <h2>{{ $menuName }}</h2>
    </div>

    <div class="menu-info">
        <h3>Informations du menu</h3>
        <p><strong>Menu :</strong> {{ $menuName }}</p>
        <p><strong>Nombre total d’ingrédients :</strong> {{ count($ingredientsList) }}</p>
        <p><strong>Généré le :</strong> {{ $date->format('d/m/Y à H:i') }}</p>
    </div>

    <table class="ingredients-table">
        <thead>
            <tr>
                <th style="width: 30%;">Ingrédient</th>
                <th style="width: 15%;">Quantité</th>
                <th style="width: 10%;">Unité</th>
                <th style="width: 45%;">Utilisé dans</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingredientsList as $ingredient)
                <tr>
                    <td>{{ $ingredient['name'] }}</td>
                    <td class="quantity">{{ $ingredient['quantity'] }}</td>
                    <td>{{ $ingredient['unit'] }}</td>
                    <td class="recipes">
                        {{ implode(', ', array_slice($ingredient['recipes'], 0, 3)) }}
                        @if(count($ingredient['recipes']) > 3)
                            <br><small>+{{ count($ingredient['recipes']) - 3 }} autres...</small>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Liste générée automatiquement par MenuPlanner - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

# Menu Planner

Menu Planner est une application web développée avec Laravel permettant de gérer des menus, des recettes, des ingrédients et des listes de courses. Elle propose un tableau de bord interactif avec des statistiques et graphiques pour faciliter l’organisation des repas.

## Fonctionnalités

- Tableau de bord avec indicateurs et graphiques (Chart.js)
- Gestion des ingrédients
- Gestion des recettes
- Création et planification de menus
- Génération automatique de listes de courses
- Export PDF des listes de courses
- Gestion des utilisateurs et rôles (admin, user)
- Authentification sécurisée
- Interface moderne avec Tailwind CSS

## Prérequis

- PHP >= 8.1
- Composer
- Node.js & npm
- MySQL ou MariaDB
- Docker (optionnel, pour un déploiement rapide)

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-utilisateur/menu-planner.git
   cd menu-planner
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Installer les dépendances JS**
   ```bash
   npm install
   ```

4. **Copier le fichier d’environnement**
   ```bash
   cp .env.example .env
   ```

5. **Configurer la base de données dans `.env`**

6. **Générer la clé d’application**
   ```bash
   php artisan key:generate
   ```

7. **Lancer les migrations**
   ```bash
   php artisan migrate
   ```

8. **Compiler les assets**
   ```bash
   npm run dev
   ```
   Pour la production :
   ```bash
   npm run build
   ```

9. **Lancer le serveur**
   ```bash
   php artisan serve
   ```

10. *(Optionnel)* **Utiliser Docker**
    ```bash
    docker-compose up --build
    ```

## Utilisation

- Accédez à l’application sur [http://localhost:8000](http://localhost:8000)
- Inscrivez-vous ou connectez-vous pour accéder à toutes les fonctionnalités
- Naviguez via le menu pour gérer les ingrédients, recettes, menus et listes de courses

## Structure du projet

- `app/` : logique métier, Livewire, contrôleurs, modèles
- `resources/views/` : vues Blade, composants, layouts
- `resources/js/` : scripts JS (dashboard, etc.)
- `resources/css/` : styles Tailwind CSS
- `routes/` : routes web et API

## Contribution

Les contributions sont les bienvenues !  
Forkez le projet, créez une branche et proposez une pull request.

## Licence

Ce projet est sous licence MIT.

---

**Développé avec Laravel, Livewire, Tailwind CSS et Chart.js.**

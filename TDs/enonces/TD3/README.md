# Réponses aux questions écrites du TD3

## 2. Conception REST

### 2.1 - Lister les collections de ressources accessibles dans cette API

La collection de 'Categorie', 'annonce', 'photo', 'Region', 'Departement'.

### 2.2 - Lister les URI pour l'ensemble des collections et pour les ressources de ces collections

| Method | URL           |
| :----- | :------------ |
| GET    | /annonces     |
| GET    | /categories   |
| GET    | /photos       |
| GET    | /departements |
| GET    | /regions      |

### 2.3 - Lister les annonces

| Method | URL       |
| :----- | :-------- |
| GET    | /annonces |

### 2.4 - Lister les annonces du 54

| Method | URL                       |
| :----- | :------------------------ |
| GET    | /Departements/54/annonces |

### 2.5 - Lister les annonces de la catégorie "voitures"

| Method | URL                           |
| :----- | :---------------------------- |
| GET    | /categories/voitures/annonces |

### 2.6 - Créer une catégorie

| Method | URL                        |
| :----- | :------------------------- |
| PUT    | /categories/voiture/{data} |

### 2.7 - Modifier une annonce existante

| Method | URL            |
| :----- | :------------- |
| PUT    | /annonces/{id} |

### 2.8 - Créer une annonce, l'associer à une catégorie et un département

#### Étape 1 : Créer l'annonce

| Method | URL                               |
| :----- | :-------------------------------- |
| PUT    | /categories/voiture/annonces/{id} |

#### Étape 2 : Associer l'annonce à une catégorie et un département

| Method | URL                                                 |
| :----- | :-------------------------------------------------- |
| PUT    | /categories/{id}/annonces/{id_etape_1}/departement/{numero} |

## 3. Conception sur le projet


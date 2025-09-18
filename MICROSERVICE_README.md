# User Management Microservice - DDD Architecture 2025

## 🎯 **Vue d'ensemble**

Ce projet implémente un **microservice de gestion d'utilisateurs** suivant les **standards professionnels 2025** avec :

- **Domain-Driven Design (DDD)** complet
- **Architecture CQRS** avec séparation Command/Query
- **Event Sourcing** pour traçabilité complète
- **Bounded Context** User Management
- **Laravel 11** avec patterns avancés

## 🏗️ **Architecture DDD**

### **Structure des couches**

```
app/
├── Domain/User/              # 🎯 DOMAIN LAYER (Logique métier pure)
│   ├── Entities/
│   │   └── User.php         # Entité racine d'agrégat
│   ├── ValueObjects/
│   │   ├── UserId.php       # Identifiant unique
│   │   ├── Email.php        # Email avec validation
│   │   └── UserName.php     # Nom utilisateur
│   ├── Events/
│   │   ├── UserRegistered.php
│   │   └── UserEmailVerified.php
│   ├── Contracts/
│   │   └── UserRepositoryInterface.php
│   └── Services/
│       └── UserDomainService.php
│
├── Application/User/         # 🚀 APPLICATION LAYER (Cas d'usage)
│   ├── Commands/            # Commandes (CQRS Write)
│   │   ├── RegisterUserCommand.php
│   │   └── VerifyEmailCommand.php
│   ├── Queries/             # Requêtes (CQRS Read)
│   │   ├── GetUserQuery.php
│   │   └── GetUsersQuery.php
│   ├── Handlers/            # Gestionnaires CQRS
│   │   ├── RegisterUserHandler.php
│   │   ├── GetUserHandler.php
│   │   └── ...
│   ├── DTOs/               # Objets de transfert
│   └── EventHandlers/      # Gestionnaires d'événements
│
└── Infrastructure/User/     # 🔧 INFRASTRUCTURE LAYER (Laravel)
    ├── Repositories/
    │   └── EloquentUserRepository.php
    ├── Models/
    │   └── UserEloquentModel.php
    ├── Controllers/
    │   └── UserController.php
    ├── EventStore/
    │   └── EventStore.php
    ├── ReadModels/
    │   └── UserReadModel.php
    └── Providers/
        └── UserServiceProvider.php
```

## ⚡ **Patterns Implémentés**

### **1. CQRS (Command Query Responsibility Segregation)**
- **Commands** : Opérations d'écriture (Register, VerifyEmail)
- **Queries** : Opérations de lecture (GetUser, GetUsers)
- **Séparation** complète des responsabilités

### **2. Event Sourcing**
- Tous les événements stockés dans `event_store`
- Reconstruction d'état possible
- Traçabilité complète des changements

### **3. Domain Events**
- `UserRegistered` : Utilisateur créé
- `UserEmailVerified` : Email vérifié
- **Dispatch automatique** via EventDispatcher

### **4. Repository Pattern**
- Interface dans Domain Layer
- Implémentation Eloquent dans Infrastructure
- **Inversion de dépendance** respectée

## 🚀 **API Endpoints**

### **Health Checks**
```
GET /api/health          # État du microservice
GET /api/ready           # Vérification de disponibilité
GET /api/docs            # Documentation API
```

### **User Management (v1)**
```
POST   /api/v1/users                    # Créer utilisateur
GET    /api/v1/users                    # Liste utilisateurs
GET    /api/v1/users/{id}               # Détails utilisateur
PATCH  /api/v1/users/{id}/verify-email  # Vérifier email
```

## 📊 **Event Sourcing & CQRS**

### **Write Side (Commands)**
```php
// Exemple : Enregistrer un utilisateur
POST /api/v1/users
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}

// 1. Command → RegisterUserHandler
// 2. Domain Logic → User::register()
// 3. Events → UserRegistered
// 4. EventStore → Persist events
// 5. ReadModel → Update projection
```

### **Read Side (Queries)**
```php
// Exemple : Récupérer un utilisateur
GET /api/v1/users/{id}

// 1. Query → GetUserHandler
// 2. Repository → Fetch from ReadModel
// 3. DTO → Transform response
```

## 🗄️ **Base de données**

### **Tables principales**
- `users` : Modèle d'écriture (Write Model)
- `user_read_models` : Modèle de lecture (Read Model)
- `event_store` : Stockage des événements

### **Migrations**
```bash
php artisan migrate
```

## 🔧 **Configuration & Installation**

### **1. Installation**
```bash
# Installer les dépendances
composer install

# Générer la clé
php artisan key:generate

# Migrations
php artisan migrate
```

### **2. Variables d'environnement**
```env
# Microservice
APP_NAME="User Microservice"
APP_ENV=production

# Database
DB_CONNECTION=mysql
DB_DATABASE=user_microservice

# Events & Cache
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

### **3. Lancement**
```bash
# Serveur de développement
php artisan serve

# Avec Octane (production)
php artisan octane:start
```

## 🧪 **Tests**

### **Structure des tests**
```
tests/
├── Unit/Domain/         # Tests unitaires domaine
├── Feature/Application/ # Tests d'intégration application
└── Integration/         # Tests end-to-end
```

### **Exécution**
```bash
# Tests complets
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📈 **Monitoring & Observabilité**

### **Métriques**
- Health checks automatiques
- Logging structuré (JSON)
- Événements tracés

### **Commandes utiles**
```bash
# Vérifier la configuration DDD
composer run ddd:check

# Liste des routes API
php artisan route:list --path=api

# Liste des événements
php artisan event:list
```

## 🔄 **Extension du Microservice**

### **Ajouter un nouveau cas d'usage**
1. **Command/Query** dans Application Layer
2. **Handler** correspondant
3. **Route** dans Infrastructure
4. **Tests** appropriés

### **Ajouter un événement**
1. **Event** dans Domain/Events
2. **Handler** dans Application/EventHandlers
3. **Listener** enregistré dans ServiceProvider

## 🎯 **Conformité Standards 2025**

✅ **Domain-Driven Design** complet
✅ **CQRS** avec séparation lecture/écriture
✅ **Event Sourcing** implémenté
✅ **Bounded Context** bien défini
✅ **Microservice autonome**
✅ **Laravel 11** patterns avancés
✅ **Architecture en couches** respectée
✅ **Inversion de dépendance**
✅ **Tests automatisés**

## 📞 **Support**

Pour toute question sur l'architecture DDD ou les patterns microservices, consultez :
- Documentation Domain-Driven Design
- Patterns CQRS et Event Sourcing
- Bonnes pratiques Laravel 11

---

**🏆 Ce microservice respecte les standards professionnels 2025 pour Laravel avec DDD, CQRS et Event Sourcing.**
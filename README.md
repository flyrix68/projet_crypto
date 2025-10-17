# Système d'Échange de Messages Cryptés RSA

## Description
Application web sécurisée permettant l'échange de messages cryptés entre deux utilisateurs utilisant l'algorithme de cryptographie RSA.

## Fonctionnalités
- Authentification utilisateur sécurisée
- Génération automatique de paires de clés RSA (2048 bits)
- Cryptage des messages avec la clé publique du destinataire
- Décryptage automatique des messages reçus
- Interface utilisateur intuitive et responsive
- Historique des conversations
- Protection contre les attaques courantes

## Architecture
- **Backend**: PHP 7.4+ avec extension OpenSSL
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Base de données**: MySQL 5.7+
- **Pattern**: MVC (Modèle-Vue-Contrôleur)

## Installation

### Prérequis
- PHP 7.4 ou supérieur avec extension OpenSSL
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx) ou PHP built-in server

### Étapes d'installation
1. Cloner le repository
2. Créer la base de données MySQL
3. Importer le schéma: `mysql -u root -p crypto_chat < schema.sql`
4. Configurer la connexion à la base de données dans `config/database.php`
5. Démarrer le serveur PHP: `php -S localhost:8000`
6. Accéder à l'application via `http://localhost:8000`

## Utilisation
1. Créer un compte utilisateur
2. Se connecter
3. Sélectionner un utilisateur dans la liste pour commencer une conversation
4. Écrire et envoyer des messages (cryptés automatiquement)
5. Les messages reçus sont décryptés automatiquement

## Sécurité
- Cryptographie RSA 2048 bits
- Clés privées chiffrées avec AES-256-CBC
- Protection contre les injections SQL
- Sessions sécurisées
- Validation des entrées utilisateur

## Structure du Projet
```
/
├── config/
│   └── database.php          # Configuration base de données
├── models/
│   ├── User.php             # Modèle utilisateur
│   └── Message.php          # Modèle message
├── views/
│   ├── login.php            # Page de connexion
│   ├── register.php         # Page d'inscription
│   └── dashboard.php        # Interface principale
├── controllers/
│   ├── UserController.php   # Contrôleur utilisateur
│   └── MessageController.php # Contrôleur messages
├── schema.sql               # Schéma base de données
├── index.php                # Point d'entrée
└── README.md               # Documentation
```

## API Endpoints
- `POST /login.php` - Connexion utilisateur
- `POST /register.php` - Inscription utilisateur
- `POST /send_message.php` - Envoi de message
- `GET /get_messages.php` - Récupération des messages
- `POST /logout.php` - Déconnexion

## Développement
Pour contribuer au projet:
1. Fork le repository
2. Créer une branche feature
3. Commiter les changements
4. Push et créer une Pull Request

## Licence
Ce projet est sous licence MIT.
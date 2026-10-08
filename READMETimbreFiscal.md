# Timbre Fiscal — README

Application Laravel de gestion d'une demande de timbre fiscal avec simulation d'un paiement mobile MTN/MOOV, gestion des transactions opérateur et API callback sécurisée par HMAC-SHA256.

## 1. Technologies

- PHP / Laravel
- MySQL
- Blade / HTML / CSS
- API REST Laravel
- HMAC-SHA256

## 2. Installation

### Prérequis

Installer PHP, Composer et MySQL.

Vérifier :

```bash
php -v
composer -V
```

### Installer les dépendances

Depuis le dossier du projet :

```bash
composer install
```

Créer `.env` si nécessaire :

```bash
copy .env.example .env
```

Linux/macOS :

```bash
cp .env.example .env
```

Puis :

```bash
php artisan key:generate
```

## 3. Base de données

Créer une base MySQL :

```text
timbre_fiscal
```

Dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=timbre_fiscal
DB_USERNAME=root
DB_PASSWORD=

OPERATOR_SECRET=secret-operateur-2026
```

Dans `config/services.php` :

```php
'operator' => [
    'secret' => env('OPERATOR_SECRET'),
],
```

Créer les tables :

```bash
php artisan migrate
```

Si des données doivent être conservées, ne pas utiliser `php artisan migrate:fresh`.

## 4. Données de test

Lancer :

```bash
php artisan db:seed
```

Utilisateur de test :

```text
email    : test@example.com
password : password
```

Actes de test :

```text
Acte de naissance       : 1000 FCFA
Casier judiciaire       : 1500 FCFA
Certificat de résidence : 500 FCFA
```

Le montant est :

```text
tarif unitaire × nombre de copies + 100 FCFA de frais de service
```

Le seeder utilise `updateOrCreate` pour éviter les doublons de l'utilisateur de test.

## 5. Démarrer l'application

```bash
php artisan serve
```

Application :

```text
http://127.0.0.1:8000
```

Création d'une demande :

```text
http://127.0.0.1:8000/demande
```

Opérateur simulé :

```text
http://127.0.0.1:8000/operator
```

## 6. Fonctionnement général

```text
Demande
   ↓
Calcul du montant
   ↓
Création du paiement
   ↓
EN_ATTENTE
   ↓
Opérateur MTN/MOOV simulé
   ├── Accepter → REUSSI
   └── Refuser  → ECHEC
```

## 7. Architecture

```text
app/
├── Http/Controllers/
│   ├── DemandeController.php
│   ├── PaymentController.php
│   ├── CallbackController.php
│   └── OperatorController.php
├── Models/
│   ├── Acte.php
│   ├── Demande.php
│   ├── Payment.php
│   └── OperatorTransaction.php
└── Services/
    ├── DemandeService.php
    ├── PaymentService.php
    └── OperatorService.php

resources/views/
├── layouts/
├── demandes/
├── payments/
└── operator/

routes/
├── web.php
└── api.php
```

## 8. Rôle des services

### DemandeService

Crée une demande et calcule le montant :

```text
tarif_unitaire × nombre_copies + 100 FCFA
```

### PaymentService

Gère :

- création du paiement ;
- contrôle de propriété de la demande ;
- idempotence avec `Idempotency-Key` ;
- traitement du callback ;
- protection contre le retraitement d'un paiement terminé.

Un paiement dans `REUSSI`, `ECHEC` ou `EXPIRE` n'est plus modifié par un callback ultérieur.

### OperatorService

Simule la réception du débit par l'opérateur.

Il crée `operator_transactions` avec une référence opérateur et est idempotent sur `payment_reference`.

## 9. API

Vérifier les routes :

```bash
php artisan route:list
```

Routes principales :

| Méthode | URL | Rôle |
|---|---|---|
| POST | `/api/demandes` | Créer une demande |
| POST | `/api/payments` | Créer un paiement |
| POST | `/api/payments/{id}/launch` | Simuler le débit opérateur |
| POST | `/api/operator/callback` | Recevoir un callback signé |
| POST | `/api/operator/result` | Simuler l'envoi d'un résultat |

### Création d'une demande

```text
POST /api/demandes
```

Headers :

```text
X-User-Id: ID_UTILISATEUR
Content-Type: application/json
```

Body :

```json
{
    "acte_id": 1,
    "nombre_copies": 2
}
```

### Création d'un paiement

```text
POST /api/payments
```

Headers :

```text
X-User-Id: ID_UTILISATEUR
Idempotency-Key: cle-unique
Content-Type: application/json
```

Body :

```json
{
    "demande_id": 1,
    "telephone": "0101020304",
    "operateur": "MTN"
}
```

Le téléphone doit respecter :

```text
01XXXXXXXX
```

Le paiement commence avec :

```text
EN_ATTENTE
```

## 10. API Callback

Endpoint :

```text
POST /api/operator/callback
```

Body :

```json
{
    "reference": "PAY-XXXXXXXX",
    "statut": "REUSSI",
    "operateur_reference": "OP-XXXXXXXX",
    "signature": "SIGNATURE_HMAC"
}
```

Statuts acceptés :

```text
REUSSI
ECHEC
```

### Calcul de la signature

La signature est calculée sur :

```text
reference|statut|operateur_reference
```

Exemple :

```text
PAY-123|REUSSI|OP-456
```

Puis HMAC-SHA256 avec :

```env
OPERATOR_SECRET=secret-operateur-2026
```

Le contrôleur vérifie la signature avec `hash_equals`.

## 11. Tester le callback avec Tinker

Il faut d'abord avoir un paiement et une transaction opérateur.

Lancer :

```bash
php artisan tinker
```

Afficher la dernière transaction :

```php
\App\Models\OperatorTransaction::latest()->first();
```

Exemple :

```text
payment_reference: PAY-123
operator_reference: OP-456
```

Calculer la signature :

```php
hash_hmac(
    'sha256',
    'PAY-123|REUSSI|OP-456',
    config('services.operator.secret')
);
```

Copier la valeur retournée.

Quitter :

```php
exit
```

## 12. Tester avec Postman

Requête :

```text
POST http://127.0.0.1:8000/api/operator/callback
```

Body → raw → JSON :

```json
{
    "reference": "PAY-123",
    "statut": "REUSSI",
    "operateur_reference": "OP-456",
    "signature": "SIGNATURE_CALCULEE"
}
```

Utiliser les vraies valeurs de la base.

Résultat attendu :

```json
{
    "message": "Résultat enregistré"
}
```

Le paiement passe à :

```text
REUSSI
```

## 13. Tester un résultat ECHEC

Utiliser :

```json
{
    "reference": "PAY-123",
    "statut": "ECHEC",
    "operateur_reference": "OP-456",
    "signature": "SIGNATURE_CALCULEE"
}
```

La signature doit être recalculée avec :

```text
PAY-123|ECHEC|OP-456
```

Résultat :

```text
HTTP 200
statut = ECHEC
```

## 14. Tester un résultat envoyé deux fois

Envoyer deux fois exactement le même callback valide :

```json
{
    "reference": "PAY-123",
    "statut": "REUSSI",
    "operateur_reference": "OP-456",
    "signature": "SIGNATURE_CORRECTE"
}
```

Première fois :

```text
EN_ATTENTE → REUSSI
```

Deuxième fois :

- le paiement est déjà `REUSSI` ;
- `PaymentService` détecte l'état final ;
- aucun second traitement métier n'est effectué.

La protection utilisée est :

```php
if (in_array($payment->statut, ['REUSSI','ECHEC','EXPIRE'])) {
    return;
}
```

## 15. Tester une signature fausse

Envoyer :

```json
{
    "reference": "PAY-123",
    "statut": "REUSSI",
    "operateur_reference": "OP-456",
    "signature": "FAUSSE"
}
```

Résultat attendu :

```text
HTTP 401
```

Réponse :

```json
{
    "message": "Signature invalide"
}
```

Le paiement ne doit pas être modifié.

## 16. Test depuis l'interface Web

Ouvrir :

```text
http://127.0.0.1:8000/demande
```

Créer une demande puis un paiement.

Ouvrir :

```text
http://127.0.0.1:8000/operator
```

Pour un paiement `EN_ATTENTE` :

```text
[ Accepter ] [ Refuser ]
```

Accepter :

```text
EN_ATTENTE → REUSSI
```

Refuser :

```text
EN_ATTENTE → ECHEC
```

## 17. Ce qui fonctionne actuellement

### Demandes

- [x] Création d'une demande
- [x] Choix du type d'acte
- [x] Nombre de copies
- [x] Calcul du montant
- [x] Frais de service de 100 FCFA
- [x] Association utilisateur / acte / demande

### Paiements

- [x] Création du paiement
- [x] Statut `EN_ATTENTE`
- [x] Validation du téléphone
- [x] Choix MTN / MOOV
- [x] Référence unique
- [x] `Idempotency-Key` pour l'API de création
- [x] Contrôle de propriété de la demande

### Opérateur

- [x] Simulation MTN / MOOV
- [x] Table `operator_transactions`
- [x] Référence opérateur
- [x] Accepter
- [x] Refuser
- [x] Protection contre la création répétée de la même transaction

### Callback

- [x] Endpoint `/api/operator/callback`
- [x] Validation des données
- [x] Signature HMAC-SHA256
- [x] Résultat `REUSSI`
- [x] Résultat `ECHEC`
- [x] Signature invalide → `401`
- [x] Callback répété sans second traitement métier

## 18. Ce qui manque

### 18.1 Authentification réelle

Certaines API utilisent actuellement :

```text
X-User-Id
```

C'est acceptable pour la démonstration, mais pas pour la production.

À faire :

- Laravel Sanctum ou système de tokens ;
- contrôle d'accès réel ;
- suppression du mécanisme `X-User-Id` comme moyen d'authentification.

### 18.2 Vrai opérateur MTN/MOOV

MTN et MOOV sont actuellement simulés.

Il manque :

- API réelle de paiement ;
- authentification opérateur ;
- création réelle du paiement ;
- référence opérateur réelle ;
- callback réel ;
- gestion des erreurs réseau ;
- timeout ;
- règles de signature propres à l'opérateur.

### 18.3 Interface complète

L'interface actuelle est une interface de démonstration.

À améliorer :

- connexion utilisateur ;
- tableau de bord ;
- historique des demandes ;
- historique des paiements ;
- reçu ;
- design professionnel ;
- messages d'erreur plus propres.

### 18.4 Gestion avancée des erreurs

À compléter :

- opérateur indisponible ;
- timeout ;
- paiement expiré ;
- montant incorrect ;
- callback inconnu ;
- référence inconnue ;
- transaction déjà traitée ;
- erreurs réseau.

### 18.5 Tests automatisés

À ajouter :

- création d'une demande ;
- calcul du montant ;
- création d'un paiement ;
- idempotence ;
- callback `REUSSI` ;
- callback `ECHEC` ;
- mauvaise signature ;
- callback envoyé deux fois ;
- contrôle d'accès.

## 19. Pourquoi ces éléments manquent ?

Le projet actuel est une version fonctionnelle de démonstration. Le fonctionnement métier principal a été construit avant les éléments de production :

```text
Demande
  ↓
Paiement
  ↓
Opérateur simulé
  ↓
Résultat
  ↓
Callback signé
```

Pour une vraie mise en production, il faut ensuite :

```text
Authentification réelle
        ↓
API opérateur réelle
        ↓
Sécurité renforcée
        ↓
Gestion avancée des erreurs
        ↓
Tests automatisés
        ↓
Production
```

## 20. Commandes utiles

Nettoyer les caches :

```bash
php artisan optimize:clear
```

Afficher les routes :

```bash
php artisan route:list
```

Lancer le serveur :

```bash
php artisan serve
```

Migrations :

```bash
php artisan migrate
```

Seeders :

```bash
php artisan db:seed
```

Tinker :

```bash
php artisan tinker
```

## 21. Résumé

Le projet possède actuellement un parcours fonctionnel de démonstration :

```text
                 DEMANDE
                    ↓
                 PAIEMENT
                    ↓
               EN_ATTENTE
                    ↓
             OPÉRATEUR SIMULÉ
                ↙       ↘
          ACCEPTER     REFUSER
              ↓           ↓
           REUSSI        ECHEC
                ↘       ↙
                  CALLBACK
                     ↓
                HMAC-SHA256
```

Les tests importants sont :

```text
Callback correct       → résultat accepté
Callback envoyé 2 fois → pas de second traitement métier
Signature fausse       → HTTP 401
```

La prochaine étape majeure est l'authentification réelle et l'intégration d'un véritable opérateur de paiement.

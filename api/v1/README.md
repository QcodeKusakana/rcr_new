# API mobile RCR — v1

Base : `https://rcr.cd/api/v1` (ou `/api/v1/index.php/<route>` si la réécriture Apache est indisponible).
JSON uniquement. Authentification : `Authorization: Bearer <jeton>` (jeton obtenu par `/auth/login` ou `/adhesion`).
Réponses : `{"ok":true,...}` ou `{"ok":false,"code":"...","message":"..."}` (le `code` est stable).

| Méthode | Route | Auth | Rôle |
|---|---|---|---|
| GET | `/ping`, `/config`, `/tarifs` | — | état, coordonnées, barème (catégories > grades, périodes) |
| GET | `/localisation/provinces`, `/territoires?province=`, `/secteurs?territoire=` | — | listes déroulantes |
| POST | `/auth/login` `{identifiant, mot_de_passe, appareil}` | — | connexion (code d'adhésion ou e-mail) |
| POST | `/adhesion` (multipart) | — | création du compte + photo ; renvoie un jeton |
| POST | `/auth/logout` | ✔ | révoque le jeton |
| GET | `/me` | ✔ | profil, statut, échéance, tarif de renouvellement |
| POST | `/me/mot-de-passe` `{actuel, nouveau}` | ✔ | change le mot de passe (déconnecte les autres appareils) |
| GET | `/me/carte` | ✔ | carte de membre PDF (cotisation à jour) |
| POST | `/paiements` `{canal, telephone, id_periode}` | ✔ | adhésion (1er paiement) ou renouvellement ; carte → `redirect_url` |
| GET | `/paiements` | ✔ | historique |
| GET/POST | `/paiements/{id}/statut` · `/expirer` · GET `/recu` | ✔ | suivi (vérification FlexPay serveur), reçu PDF |
| POST | `/dons` | optionnel | don ponctuel/régulier (sans compte : renvoie `suivi`) |
| GET | `/dons` | ✔ | dons du membre, `a_renouveler` |
| POST | `/dons/{id}/renouveler` | ✔ | renouvelle un don régulier |
| GET/POST | `/dons/{id}/statut` · `/expirer` (`?k=<suivi>` si sans compte) · GET `/recu` | ✔/clé | suivi et reçu |

Sécurité : jetons hachés (SHA-256) en base, verrouillage des essais par (IP + identifiant), 5 adhésions / 15 min / IP,
montants recalculés serveur, propriété des paiements vérifiée (404 sur la ressource d'un autre membre), aucune session PHP.

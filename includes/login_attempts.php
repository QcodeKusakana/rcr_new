<?php
/**
 * Protection anti-brute-force pour la connexion "Espace Membre"
 * (pages/login.php / functions/login.funct.php).
 *
 * Contexte sécurité : depuis que la connexion se fait avec le SEUL
 * code d'adhésion (`adhesion.codes`), et que ce code suit un format
 * prévisible (voir adhere/adhesion.funct.php : 1 lettre + id_ad sur
 * 6 chiffres + 1 caractère + 1 lettre), il faut empêcher qu'un
 * script fasse défiler des codes en boucle jusqu'à trouver un
 * compte valide. Ce fichier verrouille temporairement une adresse
 * IP après plusieurs échecs rapprochés, via la table
 * `login_attempts` (voir migrations/003_create_login_attempts.sql).
 *
 * Fenêtre glissante simple : si le dernier échec date de plus de
 * LOGIN_ATTEMPTS_WINDOW_MINUTES, le compteur repart de zéro plutôt
 * que de rester bloqué indéfiniment par de vieilles tentatives.
 *
 * ⚠️ IMPORTANT : tant que la migration
 * migrations/003_create_login_attempts.sql n'a pas été exécutée, la
 * table `login_attempts` n'existe pas. Toutes les fonctions
 * ci-dessous interceptent alors la PDOException et se comportent
 * comme si la protection était simplement désactivée (connexion
 * toujours possible, juste sans anti-brute-force) — pour ne jamais
 * bloquer la connexion des membres à cause d'une migration pas
 * encore appliquée. L'erreur est tout de même journalisée via
 * error_log() pour rester visible côté serveur.
 */

if (!defined('LOGIN_ATTEMPTS_MAX')) {
    define('LOGIN_ATTEMPTS_MAX', 5);
}
if (!defined('LOGIN_ATTEMPTS_WINDOW_MINUTES')) {
    define('LOGIN_ATTEMPTS_WINDOW_MINUTES', 15);
}
if (!defined('LOGIN_ATTEMPTS_LOCKOUT_MINUTES')) {
    define('LOGIN_ATTEMPTS_LOCKOUT_MINUTES', 15);
}

if (!function_exists('login_attempts_client_ip')) {
    function login_attempts_client_ip(): string
    {
        // REMOTE_ADDR uniquement : les en-têtes type X-Forwarded-For
        // sont librement falsifiables par le client tant qu'aucun
        // reverse proxy de confiance n'est configuré côté serveur.
        return $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
    }
}

if (!function_exists('login_attempts_check')) {
    /**
     * Retourne un message d'erreur si l'IP est actuellement
     * verrouillée, sinon null.
     */
    function login_attempts_check(PDO $bdd, string $ip): ?string
    {
        try {
            $stmt = $bdd->prepare("SELECT bloque_jusqu_a FROM login_attempts WHERE ip_adresse = ? LIMIT 1");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[login_attempts] table login_attempts indisponible (migration 003 exécutée ?) : ' . $e->getMessage());
            return null;
        }

        if (!$row || $row['bloque_jusqu_a'] === null) {
            return null;
        }

        $bloqueJusqua = new DateTime($row['bloque_jusqu_a']);
        $maintenant = new DateTime();

        if ($bloqueJusqua <= $maintenant) {
            return null;
        }

        $minutesRestantes = (int) ceil(($bloqueJusqua->getTimestamp() - $maintenant->getTimestamp()) / 60);

        return "Trop de tentatives échouées. Réessayez dans {$minutesRestantes} minute(s).";
    }
}

if (!function_exists('login_attempts_record_failure')) {
    /**
     * Enregistre un échec de connexion pour cette IP et déclenche un
     * verrouillage temporaire si le seuil est atteint.
     */
    function login_attempts_record_failure(PDO $bdd, string $ip): void
    {
        try {
            $stmt = $bdd->prepare("SELECT tentatives, derniere_tentative FROM login_attempts WHERE ip_adresse = ? LIMIT 1");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $ins = $bdd->prepare("INSERT INTO login_attempts (ip_adresse, tentatives, derniere_tentative) VALUES (?, 1, NOW())");
                $ins->execute([$ip]);
                return;
            }

            $derniereTentative = new DateTime($row['derniere_tentative']);
            $maintenant = new DateTime();
            $ecartMinutes = ($maintenant->getTimestamp() - $derniereTentative->getTimestamp()) / 60;

            // Fenêtre expirée : on repart d'un compteur à 1 plutôt que de
            // punir pour des tentatives trop anciennes.
            if ($ecartMinutes > LOGIN_ATTEMPTS_WINDOW_MINUTES) {
                $upd = $bdd->prepare("UPDATE login_attempts SET tentatives = 1, derniere_tentative = NOW(), bloque_jusqu_a = NULL WHERE ip_adresse = ?");
                $upd->execute([$ip]);
                return;
            }

            $nouvellesTentatives = (int) $row['tentatives'] + 1;

            if ($nouvellesTentatives >= LOGIN_ATTEMPTS_MAX) {
                $bloqueJusqua = (clone $maintenant)->modify('+' . LOGIN_ATTEMPTS_LOCKOUT_MINUTES . ' minutes')->format('Y-m-d H:i:s');
                $upd = $bdd->prepare("UPDATE login_attempts SET tentatives = ?, derniere_tentative = NOW(), bloque_jusqu_a = ? WHERE ip_adresse = ?");
                $upd->execute([$nouvellesTentatives, $bloqueJusqua, $ip]);
            } else {
                $upd = $bdd->prepare("UPDATE login_attempts SET tentatives = ?, derniere_tentative = NOW() WHERE ip_adresse = ?");
                $upd->execute([$nouvellesTentatives, $ip]);
            }
        } catch (PDOException $e) {
            error_log('[login_attempts] table login_attempts indisponible (migration 003 exécutée ?) : ' . $e->getMessage());
        }
    }
}

if (!function_exists('login_attempts_reset')) {
    /**
     * Efface l'historique d'échecs pour cette IP après une connexion
     * réussie.
     */
    function login_attempts_reset(PDO $bdd, string $ip): void
    {
        try {
            $stmt = $bdd->prepare("DELETE FROM login_attempts WHERE ip_adresse = ?");
            $stmt->execute([$ip]);
        } catch (PDOException $e) {
            error_log('[login_attempts] table login_attempts indisponible (migration 003 exécutée ?) : ' . $e->getMessage());
        }
    }
}

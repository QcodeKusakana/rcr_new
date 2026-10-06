<?php
declare(strict_types=1);

/**
 * ============================================================
 * RCR - IMPORT DU DUMP LEGACY
 * ============================================================
 *
 * Base cible :
 *     rcr_db
 *
 * Dump attendu :
 *     migrations/rcr_legacy.sql
 *
 * Fonctionnement :
 * - Lit le dump SQL legacy
 * - Se connecte à rcr_db
 * - Détecte les tables déjà existantes
 * - Ignore complètement les tables déjà existantes
 * - N'insère aucune donnée legacy dans une table existante
 * - Importe uniquement les tables absentes
 * - Respecte l'ordre des dépendances
 * - Désactive temporairement les contraintes FK
 *
 * Exécution :
 *
 *     php migrations/import_rcr_legacy.php
 *
 * ============================================================
 */


// ============================================================
// CONFIGURATION AFFICHAGE ERREURS
// ============================================================

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);


// ============================================================
// FONCTION D'AFFICHAGE
// ============================================================

function out(string $message = ''): void
{
    echo $message . PHP_EOL;
}


// ============================================================
// TITRE
// ============================================================

out('');
out('============================================================');
out(' RCR - IMPORT DU DUMP LEGACY');
out('============================================================');
out('');


// ============================================================
// CHEMINS
// ============================================================

$root = dirname(__DIR__);

$configFile = $root . '/config/database.php';

$sqlFile = __DIR__ . '/rcr_legacy.sql';


// ============================================================
// VÉRIFICATION DATABASE.PHP
// ============================================================

if (!is_file($configFile)) {

    out('[ERREUR] Fichier database.php introuvable.');
    out('');
    out('Fichier recherché :');
    out($configFile);
    out('');

    exit(1);
}


// ============================================================
// CHARGEMENT DATABASE.PHP
// ============================================================

require_once $configFile;


// ============================================================
// VÉRIFICATION DES CONSTANTES
// ============================================================

$requiredConstants = [
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS'
];

foreach ($requiredConstants as $constant) {

    if (!defined($constant)) {

        out('[ERREUR] Constante manquante : ' . $constant);
        out('');
        out('Vérifie :');
        out($configFile);
        out('');

        exit(1);
    }
}


// ============================================================
// INFORMATIONS BASE
// ============================================================

$targetDb = DB_NAME;


// ============================================================
// VÉRIFICATION BASE CIBLE
// ============================================================

if ($targetDb !== 'rcr_db') {

    out('[AVERTISSEMENT] DB_NAME est actuellement : ' . $targetDb);
    out('[INFO] La base attendue pour cet import est : rcr_db');
    out('');

    exit(1);
}


// ============================================================
// CONNEXION PDO
// ============================================================

$dsn = 'mysql:host=' . DB_HOST .
       ';dbname=' . DB_NAME .
       ';charset=utf8mb4';


try {

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    out('');
    out('============================================================');
    out(' ERREUR DE CONNEXION MYSQL');
    out('============================================================');
    out('');

    out('[ERREUR] Impossible de se connecter à MySQL.');
    out('');

    out('Hôte : ' . DB_HOST);
    out('Base : ' . DB_NAME);
    out('Utilisateur : ' . DB_USER);
    out('');

    out('Message MySQL :');
    out($e->getMessage());
    out('');

    exit(1);
}


out('[OK] Connexion PDO établie.');
out('[OK] Base cible : ' . DB_NAME);
out('');


// ============================================================
// FORCER LA BASE CIBLE
// ============================================================

try {

    $pdo->exec(
        'USE `' . str_replace('`', '``', $targetDb) . '`'
    );

} catch (PDOException $e) {

    out('[ERREUR] Impossible de sélectionner la base ' . $targetDb);
    out($e->getMessage());

    exit(1);
}


// ============================================================
// VÉRIFICATION BASE ACTIVE
// ============================================================

try {

    $currentDb = $pdo->query(
        'SELECT DATABASE()'
    )->fetchColumn();

} catch (PDOException $e) {

    out('[ERREUR] Impossible de vérifier la base active.');
    out($e->getMessage());

    exit(1);
}


if ($currentDb !== $targetDb) {

    out('[ERREUR] Mauvaise base active.');
    out('Base attendue : ' . $targetDb);
    out('Base actuelle : ' . ($currentDb ?: 'aucune'));
    out('');

    exit(1);
}


// ============================================================
// VÉRIFICATION DU DUMP
// ============================================================

if (!is_file($sqlFile)) {

    out('[ERREUR] Dump SQL introuvable.');
    out('');
    out('Fichier attendu :');
    out($sqlFile);
    out('');

    out('Crée ou copie ton dump legacy ici :');
    out('migrations/rcr_legacy.sql');
    out('');

    exit(1);
}


// ============================================================
// LECTURE DU DUMP
// ============================================================

$sql = file_get_contents($sqlFile);


if ($sql === false) {

    out('[ERREUR] Impossible de lire le dump SQL.');
    out($sqlFile);
    out('');

    exit(1);
}


if (trim($sql) === '') {

    out('[ERREUR] Le fichier SQL est vide.');
    out($sqlFile);
    out('');

    exit(1);
}


// ============================================================
// SUPPRESSION BOM UTF-8
// ============================================================

$sql = preg_replace(
    '/^\xEF\xBB\xBF/',
    '',
    $sql
);


out('[OK] Dump SQL chargé.');
out('[OK] Taille : ' . strlen($sql) . ' octets.');
out('');


// ============================================================
// FONCTION DE DÉCOUPAGE SQL
// ============================================================

function splitSqlStatements(string $sql): array
{
    $statements = [];

    $length = strlen($sql);

    $buffer = '';

    $inSingleQuote = false;

    $inDoubleQuote = false;

    $inBacktick = false;

    $inLineComment = false;

    $inBlockComment = false;


    for ($i = 0; $i < $length; $i++) {

        $char = $sql[$i];

        $next = ($i + 1 < $length)
            ? $sql[$i + 1]
            : '';


        // ----------------------------------------------------
        // COMMENTAIRE DE LIGNE
        // ----------------------------------------------------

        if ($inLineComment) {

            if ($char === "\n") {

                $inLineComment = false;

                $buffer .= "\n";
            }

            continue;
        }


        // ----------------------------------------------------
        // COMMENTAIRE BLOC
        // ----------------------------------------------------

        if ($inBlockComment) {

            if ($char === '*' && $next === '/') {

                $inBlockComment = false;

                $i++;
            }

            continue;
        }


        // ----------------------------------------------------
        // COMMENTAIRE --
        // ----------------------------------------------------

        if (
            !$inSingleQuote &&
            !$inDoubleQuote &&
            !$inBacktick &&
            $char === '-' &&
            $next === '-'
        ) {

            $inLineComment = true;

            $i++;

            continue;
        }


        // ----------------------------------------------------
        // COMMENTAIRE #
        // ----------------------------------------------------

        if (
            !$inSingleQuote &&
            !$inDoubleQuote &&
            !$inBacktick &&
            $char === '#'
        ) {

            $inLineComment = true;

            continue;
        }


        // ----------------------------------------------------
        // COMMENTAIRE /*
        // ----------------------------------------------------

        if (
            !$inSingleQuote &&
            !$inDoubleQuote &&
            !$inBacktick &&
            $char === '/' &&
            $next === '*'
        ) {

            $inBlockComment = true;

            $i++;

            continue;
        }


        // ----------------------------------------------------
        // QUOTE SIMPLE '
        // ----------------------------------------------------

        if (
            !$inDoubleQuote &&
            !$inBacktick &&
            $char === "'"
        ) {

            if (
                $i > 0 &&
                $sql[$i - 1] === '\\'
            ) {

                $buffer .= $char;

                continue;
            }

            $inSingleQuote = !$inSingleQuote;

            $buffer .= $char;

            continue;
        }


        // ----------------------------------------------------
        // QUOTE DOUBLE "
        // ----------------------------------------------------

        if (
            !$inSingleQuote &&
            !$inBacktick &&
            $char === '"'
        ) {

            if (
                $i > 0 &&
                $sql[$i - 1] === '\\'
            ) {

                $buffer .= $char;

                continue;
            }

            $inDoubleQuote = !$inDoubleQuote;

            $buffer .= $char;

            continue;
        }


        // ----------------------------------------------------
        // BACKTICK `
        // ----------------------------------------------------

        if (
            !$inSingleQuote &&
            !$inDoubleQuote &&
            $char === '`'
        ) {

            $inBacktick = !$inBacktick;

            $buffer .= $char;

            continue;
        }


        // ----------------------------------------------------
        // FIN DU STATEMENT
        // ----------------------------------------------------

        if (
            $char === ';' &&
            !$inSingleQuote &&
            !$inDoubleQuote &&
            !$inBacktick
        ) {

            $statement = trim($buffer);

            if ($statement !== '') {

                $statements[] = $statement;
            }

            $buffer = '';

            continue;
        }


        // ----------------------------------------------------
        // AJOUT AU BUFFER
        // ----------------------------------------------------

        $buffer .= $char;
    }


    // --------------------------------------------------------
    // DERNIER STATEMENT
    // --------------------------------------------------------

    $statement = trim($buffer);

    if ($statement !== '') {

        $statements[] = $statement;
    }


    return $statements;
}


// ============================================================
// EXTRACTION DU NOM DE TABLE
// ============================================================

function extractTableName(string $statement): ?string
{
    $patterns = [

        // CREATE TABLE
        '/\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`([^`]+)`/i',

        // INSERT INTO
        '/\bINSERT\s+(?:LOW_PRIORITY\s+|IGNORE\s+)?INTO\s+`([^`]+)`/i',

        // ALTER TABLE
        '/\bALTER\s+TABLE\s+`([^`]+)`/i',

        // UPDATE
        '/\bUPDATE\s+`([^`]+)`/i',

        // DELETE FROM
        '/\bDELETE\s+FROM\s+`([^`]+)`/i'
    ];


    foreach ($patterns as $pattern) {

        if (
            preg_match(
                $pattern,
                $statement,
                $matches
            )
        ) {

            return $matches[1];
        }
    }


    return null;
}


// ============================================================
// DÉTERMINATION DU TYPE DE STATEMENT
// ============================================================

function getStatementType(string $statement): string
{
    $statement = ltrim($statement);


    if (
        preg_match(
            '/^CREATE\s+TABLE/i',
            $statement
        )
    ) {

        return 'CREATE';
    }


    if (
        preg_match(
            '/^INSERT\s+/i',
            $statement
        )
    ) {

        return 'INSERT';
    }


    if (
        preg_match(
            '/^ALTER\s+TABLE/i',
            $statement
        )
    ) {

        return 'ALTER';
    }


    if (
        preg_match(
            '/^DROP\s+/i',
            $statement
        )
    ) {

        return 'DROP';
    }


    if (
        preg_match(
            '/^CREATE\s+DATABASE/i',
            $statement
        )
    ) {

        return 'CREATE_DATABASE';
    }


    if (
        preg_match(
            '/^USE\s+/i',
            $statement
        )
    ) {

        return 'USE';
    }


    if (
        preg_match(
            '/^SET\s+/i',
            $statement
        )
    ) {

        return 'SET';
    }


    if (
        preg_match(
            '/^LOCK\s+TABLES/i',
            $statement
        )
    ) {

        return 'LOCK';
    }


    if (
        preg_match(
            '/^UNLOCK\s+TABLES/i',
            $statement
        )
    ) {

        return 'UNLOCK';
    }


    if (
        preg_match(
            '/^START\s+TRANSACTION/i',
            $statement
        )
    ) {

        return 'TRANSACTION';
    }


    if (
        preg_match(
            '/^COMMIT/i',
            $statement
        )
    ) {

        return 'COMMIT';
    }


    return 'OTHER';
}


// ============================================================
// RÉCUPÉRATION DES TABLES EXISTANTES
// ============================================================

try {

    $stmt = $pdo->query(
        "
        SELECT TABLE_NAME
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        "
    );


    $existingTables = [];


    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $existingTables[
            strtolower($row['TABLE_NAME'])
        ] = true;
    }


} catch (PDOException $e) {

    out('[ERREUR] Impossible de récupérer les tables existantes.');
    out($e->getMessage());

    exit(1);
}


out(
    '[INFO] Tables déjà présentes dans rcr_db : ' .
    count($existingTables)
);

out('');


// ============================================================
// DÉCOUPAGE DU DUMP
// ============================================================

$statements = splitSqlStatements($sql);


out(
    '[OK] Statements SQL détectés : ' .
    count($statements)
);

out('');


// ============================================================
// ORGANISATION DES STATEMENTS
// ============================================================

$tables = [];

$otherStatements = [];


foreach ($statements as $statement) {

    $type = getStatementType($statement);


    // --------------------------------------------------------
    // COMMANDES À IGNORER
    // --------------------------------------------------------

    if (
        $type === 'DROP' ||
        $type === 'CREATE_DATABASE' ||
        $type === 'USE' ||
        $type === 'LOCK' ||
        $type === 'UNLOCK' ||
        $type === 'TRANSACTION' ||
        $type === 'COMMIT'
    ) {

        continue;
    }


    // --------------------------------------------------------
    // SET
    // --------------------------------------------------------

    if ($type === 'SET') {

        continue;
    }


    // --------------------------------------------------------
    // TABLE
    // --------------------------------------------------------

    $tableName = extractTableName($statement);


    if ($tableName !== null) {

        $key = strtolower($tableName);


        if (!isset($tables[$key])) {

            $tables[$key] = [
                'name'       => $tableName,
                'statements' => []
            ];
        }


        $tables[$key]['statements'][] = [
            'type' => $type,
            'sql'  => $statement
        ];


    } else {

        $otherStatements[] = $statement;
    }
}


// ============================================================
// ORDRE DES DÉPENDANCES RCR
// ============================================================
//
// Les tables sont placées dans un ordre qui limite les
// problèmes de clés étrangères du dump legacy.
// ============================================================

$dependencyOrder = [

    'provinces',

    'territoires',

    'secteurs',

    'qualites',

    'grades',

    'categorie',

    'sous_categorie',

    'adhesion',

    'admin',

    'activite',

    'commentaire',

    'likes',

    'dislike',

    'vu',

    'cotisation',

    'dons',

    'payments',

    'encadreur',

    'equipes',

    'historiquepaiement',

    'horsline',

    'onlines',

    'operateurs',

    'parner',

    'partenaire'
];


// ============================================================
// CONSTRUCTION DE L'ORDRE FINAL
// ============================================================

$orderedTables = [];


// ------------------------------------------------------------
// Tables connues
// ------------------------------------------------------------

foreach ($dependencyOrder as $tableName) {

    $key = strtolower($tableName);


    if (isset($tables[$key])) {

        $orderedTables[$key] = $tables[$key];
    }
}


// ------------------------------------------------------------
// Tables non prévues dans la liste
// ------------------------------------------------------------

foreach ($tables as $key => $tableData) {

    if (!isset($orderedTables[$key])) {

        $orderedTables[$key] = $tableData;
    }
}


// ============================================================
// COMPTEURS
// ============================================================

$importedCount = 0;

$skippedCount = 0;

$errorCount = 0;


// ============================================================
// IMPORT
// ============================================================

try {

    // --------------------------------------------------------
    // UTF-8
    // --------------------------------------------------------

    try {

        $pdo->exec(
            "SET NAMES utf8mb4"
        );

    } catch (Throwable $e) {

        // Pas bloquant
    }


    // --------------------------------------------------------
    // DÉSACTIVATION DES FOREIGN KEYS
    // --------------------------------------------------------

    $pdo->exec(
        "SET FOREIGN_KEY_CHECKS = 0"
    );


    out('------------------------------------------------------------');
    out(' DÉBUT DE L IMPORT');
    out('------------------------------------------------------------');
    out('');


    // ========================================================
    // TRAITEMENT DES TABLES
    // ========================================================

    foreach ($orderedTables as $key => $tableData) {

        $tableName = $tableData['name'];

        $tableStatements = $tableData['statements'];


        // ----------------------------------------------------
        // TABLE EXISTANTE
        // ----------------------------------------------------

        if (isset($existingTables[$key])) {

            out(
                '[SKIP] ' .
                $tableName .
                ' existe déjà — structure + données conservées.'
            );

            $skippedCount++;

            continue;
        }


        // ----------------------------------------------------
        // TABLE ABSENTE
        // ----------------------------------------------------

        out(
            '[IMPORT] ' .
            $tableName
        );


        // ----------------------------------------------------
        // GROUPES
        // ----------------------------------------------------

        $creates = [];

        $inserts = [];

        $alters = [];

        $others = [];


        foreach ($tableStatements as $item) {

            switch ($item['type']) {

                case 'CREATE':

                    $creates[] = $item['sql'];

                    break;


                case 'INSERT':

                    $inserts[] = $item['sql'];

                    break;


                case 'ALTER':

                    $alters[] = $item['sql'];

                    break;


                default:

                    $others[] = $item['sql'];

                    break;
            }
        }


        // ----------------------------------------------------
        // CREATE TABLE
        // ----------------------------------------------------

        foreach ($creates as $createSql) {

            $pdo->exec($createSql);
        }


        // ----------------------------------------------------
        // INSERT DATA
        // ----------------------------------------------------

        foreach ($inserts as $insertSql) {

            $pdo->exec($insertSql);
        }


        // ----------------------------------------------------
        // ALTER TABLE
        // ----------------------------------------------------

        foreach ($alters as $alterSql) {

            $pdo->exec($alterSql);
        }


        // ----------------------------------------------------
        // AUTRES
        // ----------------------------------------------------

        foreach ($others as $otherSql) {

            $pdo->exec($otherSql);
        }


        // ----------------------------------------------------
        // MARQUER TABLE COMME IMPORTÉE
        // ----------------------------------------------------

        $existingTables[$key] = true;

        $importedCount++;


        out(
            '[OK] ' .
            $tableName
        );

        out('');
    }


    // ========================================================
    // RÉACTIVER FOREIGN KEYS
    // ========================================================

    $pdo->exec(
        "SET FOREIGN_KEY_CHECKS = 1"
    );


} catch (Throwable $e) {

    $errorCount++;


    // --------------------------------------------------------
    // TOUJOURS RÉACTIVER LES FK
    // --------------------------------------------------------

    try {

        $pdo->exec(
            "SET FOREIGN_KEY_CHECKS = 1"
        );

    } catch (Throwable $ignored) {
    }


    out('');
    out('============================================================');
    out(' ERREUR PENDANT L IMPORT');
    out('============================================================');
    out('');

    out(
        '[ERREUR] ' .
        $e->getMessage()
    );

    out('');

    exit(1);
}


// ============================================================
// RÉSUMÉ FINAL
// ============================================================

out('');
out('============================================================');
out(' IMPORT TERMINÉ');
out('============================================================');
out('');

out(
    '[OK] Tables importées : ' .
    $importedCount
);

out(
    '[--] Tables ignorées : ' .
    $skippedCount
);

out(
    '[ERREUR] Erreurs : ' .
    $errorCount
);

out('');

out(
    '[OK] Base utilisée : ' .
    $targetDb
);

out('');

out(
    'Les tables déjà présentes dans rcr_db ont été conservées.'
);

out(
    'Leurs structures et leurs données n ont pas été modifiées.'
);

out('');
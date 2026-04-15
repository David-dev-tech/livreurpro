<?php
/**
 * proxy.php — Proxy Nominatim pour la carte Cameroun
 *
 * Ce fichier fait le relai entre le navigateur et l'API Nominatim
 * (OpenStreetMap) afin d'éviter les problèmes CORS côté client.
 *
 * Usage :
 *   GET proxy.php?action=search&q=Yaoundé&limit=5
 *   GET proxy.php?action=reverse&lat=3.848&lon=11.502
 */

// ===========================
// EN-TÊTES
// ===========================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300'); // Cache 5 minutes

// ===========================
// VALIDATION DE LA REQUÊTE
// ===========================

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if (!in_array($action, ['search', 'reverse'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Action invalide. Utilisez: search ou reverse.']);
    exit;
}

// ===========================
// PARAMÈTRES COMMUNS
// ===========================

$nominatimBase = 'https://nominatim.openstreetmap.org';
$userAgent     = 'CarteInteractiveCameroun/1.0 (contact@monsite.cm)'; // à personnaliser

// ===========================
// ACTION : RECHERCHE
// ===========================

if ($action === 'search') {

    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètre "q" manquant.']);
        exit;
    }

    // Sécurisation
    $limit = max(1, min(10, $limit));

    $params = http_build_query([
        'q'            => $query,
        'countrycodes' => 'cm',      // Restreint au Cameroun
        'format'       => 'json',
        'limit'        => $limit,
        'addressdetails' => 1,
        'accept-language' => 'fr',
    ]);

    $url = $nominatimBase . '/search?' . $params;

    $response = nominatimRequest($url, $userAgent);
    echo $response;
    exit;
}

// ===========================
// ACTION : GÉOCODAGE INVERSE
// ===========================

if ($action === 'reverse') {

    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;

    if ($lat === null || $lon === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres "lat" et "lon" requis.']);
        exit;
    }

    // Vérification que les coordonnées sont au Cameroun
    if ($lat < 1.65 || $lat > 13.08 || $lon < 8.4 || $lon > 16.19) {
        http_response_code(400);
        echo json_encode(['error' => 'Coordonnées hors du Cameroun.']);
        exit;
    }

    $params = http_build_query([
        'lat'            => $lat,
        'lon'            => $lon,
        'format'         => 'json',
        'addressdetails' => 1,
        'accept-language' => 'fr',
    ]);

    $url = $nominatimBase . '/reverse?' . $params;

    $response = nominatimRequest($url, $userAgent);
    echo $response;
    exit;
}

// ===========================
// FONCTION : REQUÊTE HTTP
// ===========================

/**
 * Envoie une requête GET à l'API Nominatim via cURL ou file_get_contents.
 *
 * @param string $url       URL complète de l'API
 * @param string $userAgent User-Agent à envoyer (requis par Nominatim)
 * @return string           Réponse JSON brute
 */
function nominatimRequest(string $url, string $userAgent): string
{
    // Priorité : cURL (plus rapide et configurable)
    if (function_exists('curl_init')) {

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => $userAgent,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            http_response_code(502);
            return json_encode(['error' => 'Erreur cURL : ' . $curlError]);
        }

        if ($httpCode !== 200) {
            http_response_code(502);
            return json_encode(['error' => 'Nominatim a répondu avec le code HTTP ' . $httpCode]);
        }

        return $response ?: '[]';

    } else {
        // Fallback : file_get_contents
        $context = stream_context_create([
            'http' => [
                'method'     => 'GET',
                'header'     => "User-Agent: $userAgent\r\nAccept: application/json\r\n",
                'timeout'    => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            http_response_code(502);
            return json_encode(['error' => 'Impossible de contacter l\'API Nominatim.']);
        }

        return $response;
    }
}
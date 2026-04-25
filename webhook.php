<?php 

    // 1. Jouw geheime token (ingesteld in GitHub)
    $secret = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "cgi-bin" . DIRECTORY_SEPARATOR . "key.txt");

    // 2. Haal de signature header op
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

    if (empty($signature)) {
        http_response_code(403);
        die('Geen signature gevonden.');
    }

    // 3. Haal de RAW payload op
    $payload = file_get_contents('php://input');

    // 4. Bereken de verwachte hash
    // GitHub stuurt de header in het formaat: sha256=hashwaarde
    $expected_signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    // 5. Veilig vergelijken (hash_equals beschermt tegen timing attacks)
    if (hash_equals($expected_signature, $signature)) {
        // Validatie geslaagd!
        echo "Payload geverifieerd.<br/>";
        
        // Verwerk de data
        $data = json_decode($payload, true);
        
        // Voorbeeld: log de actie
        file_put_contents(__DIR__.DIRECTORY_SEPARATOR."cgi-bin".DIRECTORY_SEPARATOR.'webhook.log', "Event: " . $_SERVER['HTTP_X_GITHUB_EVENT'] . PHP_EOL, FILE_APPEND);
        
        http_response_code(200);

        $ophaalurl = "https://raw.githubusercontent.com/" . $data["repository"]["full_name"] . "/" . $data["repository"]["master_branch"] . "/";
        
        foreach($data["head_commit"]["added"] as $file){
            echo "File $file added!<br/>";
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $file, file_get_contents($ophaalurl . $file));
        }

        foreach($data["head_commit"]["removed"] as $file){
            echo "File $file removed!<br/>";
            unlink(__DIR__ . DIRECTORY_SEPARATOR . $file);
        }

        foreach($data["head_commit"]["modified"] as $file){
            echo "File $file updated!<br/>";
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $file, file_get_contents($ophaalurl . $file));
        }

    } else {
        // Validatie mislukt
        http_response_code(403);
        die('Ongeldige signature.');
    }
?>
<?php
$token = '6436258610:AAEmTT34E-b5rGuL0EfbVF58nnINeWa8QBE';
$website = 'https://api.telegram.org/bot'.$token;

$lastUpdateId = 0; // Inicializa el último ID de actualización

// Agrega el código para el método de polling
while (true) {
    $result = file_get_contents($GLOBALS['website'].'/getUpdates?offset=' . ($lastUpdateId + 1));
    $updates = json_decode($result, true);

    if (!empty($updates['result'])) {
        foreach ($updates['result'] as $update) {
            // Obtén el ID de la actualización
            $currentUpdateId = $update['update_id'];

            // Verifica si ya procesaste esta actualización
            if ($currentUpdateId > $lastUpdateId) {
                // Actualiza el último ID de actualización
                $lastUpdateId = $currentUpdateId;

                // Procesa cada actualización
                $chatId = $update['message']['chat']['id'];
                $message = $update['message']['text'];

                switch ($message) {
                    case '/start':
                        $response = 'Buenos días camarada, soy mas o menos Pedro Piqueras. Puedes usar el comando /info para saber más de mis funcionalidades';
                        sendMessage($chatId, $response);
                        break;
                    case '/info':
                        $response = 'Soy Pedro Piqueton, usa el comando /hora para saber la hora de casi cualquier pais del mundo!';
                        sendMessage($chatId, $response);
                        break;
                    case '/hora':
                        $response = '¿De qué país quieres saber la hora?';
                        sendMessage($chatId, $response);

                        // Espera la respuesta del usuario
                        $respuestaUsuario = obtenerRespuestaUsuario($chatId);

                        // Obtiene la hora del país indicado
                        if ($respuestaUsuario) {
                            $responseHora = obtenerHoraEnOtroPais($respuestaUsuario);
                            sendMessage($chatId, $responseHora);
                        }
                        break;
                    default:
                        $response = 'No se de qué me hablas, por favor, sé más específic@';
                        sendMessage($chatId, $response);
                        break;
                }
            }
        }
    }

    // Agrega un pequeño retraso para evitar consumir demasiados recursos del servidor
    sleep(1);
}

function sendMessage($chatId, $response)
{
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&parse_mode=HTML&text='.urlencode($response);
    file_get_contents($url);
}

function obtenerHoraEnOtroPais($pais)
{
    // Mapeo de países y sus zonas horarias
    $zonasHorarias = [
        'argentina' => 'America/Argentina/Buenos_Aires',
        'australia' => 'Australia/Sydney',
        'brasil' => 'America/Sao_Paulo',
        'canada' => 'America/Toronto',
        'china' => 'Asia/Shanghai',
        'espana' => 'Europe/Madrid',
        'francia' => 'Europe/Paris',
        'india' => 'Asia/Kolkata',
        'italia' => 'Europe/Rome',
        'japon' => 'Asia/Tokyo',
        'mexico' => 'America/Mexico_City',
        'reino_unido' => 'Europe/London',
        'rusia' => 'Europe/Moscow',
        'sudafrica' => 'Africa/Johannesburg',
        'turquia' => 'Europe/Istanbul',
        'estados_unidos' => 'America/New_York',
        // Puedes agregar más países aquí
    ];

    // Convierte el país a minúsculas para hacer la búsqueda insensible a mayúsculas y minúsculas
    $pais = strtolower($pais);

    // Verifica si existe una zona horaria para el país indicado
    if (isset($zonasHorarias[$pais])) {
        $timezone = $zonasHorarias[$pais];
        $apiUrl = 'http://worldtimeapi.org/api/timezone/'.$timezone;
        $data = json_decode(file_get_contents($apiUrl), true);

        if ($data && isset($data['utc_datetime'])) {
            // Obtén la hora local del país
            $horaLocal = new DateTime($data['utc_datetime']);
            $horaLocal->setTimezone(new DateTimeZone($timezone));
            
            // Formatea la respuesta
            $horaFormateada = $horaLocal->format('H:i');
            return 'En '.$pais.' son las '.$horaFormateada;
        } else {
            return 'No se pudo obtener la hora en '.$pais;
        }
    } else {
        return 'No se encontró información de la zona horaria para '.$pais;
    }
}


function obtenerRespuestaUsuario($chatId)
{
    $startTime = time();
    $timeout = 10; // Define un límite de tiempo en segundos para esperar la respuesta del usuario

    while (time() - $startTime <= $timeout) {
        $result = file_get_contents($GLOBALS['website'].'/getUpdates?offset=' . ($GLOBALS['lastUpdateId'] + 1));
        $updates = json_decode($result, true);

        if (!empty($updates['result'])) {
            // Obtiene el último mensaje del usuario
            $lastMessage = end($updates['result']);
            $currentUpdateId = $lastMessage['update_id'];

            // Verifica si ya procesaste este mensaje
            if ($currentUpdateId > $GLOBALS['lastUpdateId']) {
                // Actualiza el último ID de actualización
                $GLOBALS['lastUpdateId'] = $currentUpdateId;

                return $lastMessage['message']['text'];
            }
        }

        // Agrega un pequeño retraso para evitar consumir demasiados recursos del servidor
        sleep(1);
    }

    return ''; // Devuelve una cadena vacía si no se recibe respuesta dentro del tiempo límite
}


?>

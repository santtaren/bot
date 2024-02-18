<?php
$token = '6436258610:AAEmTT34E-b5rGuL0EfbVF58nnINeWa8QBE';
$website = 'https://api.telegram.org/bot'.$token;

$iput = file_get_contents('php://input');
$update = json_decode($input, TRUE);

$chatId = $update['message']['chat']['id'];
$message = $update['message']['text'];

switch($message){
    case '/start':
        $response = 'Buenos dias camarada';
        sendMessage($chatId, $response);
        break;
    case    '/info':
        $response = 'Soy PEGATINO, mandame cualquier imagen para convertirla en un sticker!';
        sendMessage($chatId, $response);
        break;
    default:
        $response = 'No se de que me hablas';
        sendMessage($chatId, $response);
        break;
}
function sendMessage($chatId, $response){
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&parse_mode=HTML&text='.urlencode($response);
    file_get_contents($url);
}
?>
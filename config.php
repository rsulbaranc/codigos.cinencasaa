<?php
$CUENTAS_IMAP = [
    'cinencasaa@gmail.com' => [
        'host' => '{imap.gmail.com:993/imap/ssl}',
        'username' => 'cinencasaa@gmail.com',
        'password' => 'eoyh xbnd qgns aygk'
    ],
    'correo4' => [
        'host' => '{imap.gmail.com:993/imap/ssl}',
        'username' => 'kasteracc@gmail.com',
        'password' => 'gfvr mqte gfbo jsfb'
    ]
];
/**************************CONFIGURACIÓN EXTRA*******************************/
/*$PLATFORM_KEYWORDS = [
    "netflix" => [
        "SUBJECT_KEYWORDS" => ["tu hogar Netflix", "Tu código de acceso temporal",  "Netflix: Nueva solicitud de inicio de sesión", "Aprueba la nueva solicitud de inicio de sesión", "tu hogar con Netflix", "Completa tu solicitud de restablecimiento de contraseña", "Restablece tu contraseña"],
        "BODY_KEYWORDS" => ["tu hogar Netflix", "Tu código de acceso temporal",  "Netflix: Nueva solicitud de inicio de sesión", "Aprueba la nueva solicitud de inicio de sesión", "tu hogar con Netflix", "Completa tu solicitud de restablecimiento de contraseña", "Restablece tu contraseña"]
    ],
    "disney" => [
        "SUBJECT_KEYWORDS" => ["Disney+", "Es necesario que verifiques la dirección de correo electrónico asociada a tu cuenta de MyDisney con este código de acceso que vencerá en 15 minutos.", "Tu código de acceso único para Disney+"],
        "BODY_KEYWORDS" => ["Disney+", "Es necesario que verifiques la dirección de correo electrónico asociada a tu cuenta de MyDisney con este código de acceso que vencerá en 15 minutos.", "Tu código de acceso único para Disney+"]
    ],
    "prime" => [
        "SUBJECT_KEYWORDS" => ["amazon.com: Sign-in", "amazon.com: Inicio de sesión", "amazon.com: Sign-in attempt", "amazon.co.jp: Sign-in attempt", "amazon.com：サインイン試行", "amazon.com","amazon.com: Intento de inicio","amazon.co.uk"],
        "BODY_KEYWORDS" => ["amazon.com: Sign-in", "amazon.com: Inicio de sesión", "amazon.com: Sign-in attempt", "Si eras tú, tu código de verificación es:", "Alguien que conoce tu contraseña está intentando ingresar a tu cuenta.","amazon.com: Intento de inicio"]
    ]
];*/

$json_file_path = __DIR__ . '/json/platform_keywords.json';

if (file_exists($json_file_path)) {
    $json_content = file_get_contents($json_file_path);
    $PLATFORM_KEYWORDS = json_decode($json_content, true);

    if ($PLATFORM_KEYWORDS === null) {
        echo "Error al decodificar JSON.";
    }
} else {
    echo "El archivo JSON no existe.";
}

$ACCESOS_CORREO = [];

$jsonData = file_get_contents('users.json');
$users = json_decode($jsonData, true);

foreach ($users as $usuario => $correos) {
    // Añadir los correos al array ACCESOS_CORREO
    $ACCESOS_CORREO[$usuario] = array_map("strtolower",array_values($correos));
}

$GLOBAL_LINK_1_TEXTO='Productos';
$GLOBAL_LINK_1='https://cinencasaa.com/';
$GLOBAL_LINK_2_TEXTO='Instagram';
$GLOBAL_LINK_2='https://www.instagram.com/cinencasaa/';
$GLOBAL_NUMERO_WHATSAPP='kasteracc';
$GLOBAL_TEXTO_WHATSAPP='Hola, tengo una consulta sobre sus servicios.';
?>

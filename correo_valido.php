<?php
// Obtener parámetros desde la URL
$consulted_email = strtolower(trim($_GET["email"] ?? ""));
$password = $_GET["password"] ?? "";

// Cargar datos desde JSON
//$users = json_decode(file_get_contents('json/users.json'), true);

$users = json_decode(file_get_contents('users.json'), true);

$plataforma = isset($_GET["p"]) ? $_GET["p"] : "";
$consulted_email = isset($_GET["email"]) ? strtolower(trim($_GET["email"])) : null;
$password = isset($_GET["password"]) ? $_GET["password"] : null;

// Función para encontrar identificadores que contienen el chatId
function obtenerCuentasPorChatId($chatId, $users) {
    $cuentas = [];
    foreach ($users as $identificador => $cuenta) {
        if (in_array($chatId, explode(',', $identificador))) {
            $cuentas[] = $cuenta;
            foreach ($cuenta as $fruta) { $cuentas[] = $fruta; }
        }
    }
    return $cuentas;
}

// Verificar cuentas basadas en el chatId
$cuentas = obtenerCuentasPorChatId($password, $users);

// Convertir cuentas a string para la salida
if(empty($cuentas)||!in_array($consulted_email, $cuentas)){
    echo json_encode(["error" => "No tienes acceso a este correo o no ha sido agregado al bot.".$password]); exit;
}else{
    echo json_encode(["response" => "OK", "cuentas" => $cuentas]);
}
?>

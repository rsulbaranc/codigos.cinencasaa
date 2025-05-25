<?php
header('Content-Type: application/json');  // Indica que se devolverá JSON

$usersFile = 'json/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

$users = json_decode(file_get_contents($usersFile), true);

$newUser = $_POST['new_user'];
$emails = explode(',', $_POST['emails']);
$emails = array_map('trim', $emails); // Limpiar los correos

$response = [];

if ($emails && $newUser) {
    if (!isset($users[$newUser])) {
        $users[$newUser] = $emails;
        $response['status'] = "success";
        $response['message'] = "Usuario agregado con correos.";
    } else {
        foreach ($emails as $email) {
            if (!in_array($email, $users[$newUser])) {
                $users[$newUser][] = $email;
            }
        }
        $response['status'] = "success";
        $response['message'] = "Correos actualizados para el usuario existente.";
    }
    file_put_contents($usersFile, json_encode($users));
} else if($emails) {
    $response['status'] = "error";
    $response['message'] = "Faltan datos (usuario).";
}else{
    $response['status'] = "error";
    $response['message'] = "Faltan datos (correos).";
}

echo json_encode($response);
?>

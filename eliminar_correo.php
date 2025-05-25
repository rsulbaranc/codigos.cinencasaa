<?php
header('Content-Type: application/json');  // Indica que la respuesta será JSON

$usersFile = 'users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

$users = json_decode(file_get_contents($usersFile), true);

$emailToDelete = $_POST['delete_emails'];
$user = $_POST['user_id'];
$response = [];

if ($emailToDelete && $user){ // && isset($users[$user])) {
    $emails = explode(',', $emailToDelete);
    $emails = array_map('trim', $emails);  // Limpiar los correos
    $deletedEmails = [];

    foreach ($emails as $email) {
        $key = array_search($email, $users[$user]);
        if ($key !== false) {
            unset($users[$user][$key]);
            $deletedEmails[] = $email;
        }
    }

    // Guardar los cambios solo si se eliminó algún correo
    if (!empty($deletedEmails)) {
        // Reindexar el array de correos para el usuario después de eliminar elementos
        $users[$user] = array_values($users[$user]);
        file_put_contents($usersFile, json_encode($users));

        $response['status'] = "success";
        $response['message'] = "Correos eliminados correctamente.";
        $response['deleted_emails'] = $deletedEmails;
    } else {
        $response['status'] = "error";
        $response['message'] = "No se encontró ningún correo para eliminar.";
    }
} else {
    $response['status'] = "error";
    $response['message'] = "Faltan datos (usuario o correos) o el usuario no existe.";
}

echo json_encode($response);
?>

<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
$config_file_path = 'config-panel.php';
include $config_file_path;

// Cargar usuarios desde users.json
$usersFile = 'users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}
$users = json_decode(file_get_contents($usersFile), true);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        // Obtiene la nueva contraseña desde el formulario
        $new_password = $_POST['new_password'];

        // Genera el hash de la nueva contraseña
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Crea el nuevo contenido del archivo PHP con el nuevo hash
        $new_content = "<?php\n";
        $new_content .= "// Usuario único (puedes cambiar esto)\n";
        $new_content .= "define('USERNAME', 'admin');\n\n";
        $new_content .= "// Contraseña encriptada con password_hash().\n";
        $new_content .= "define('PASSWORD_HASH', '$new_hash');\n";
        $new_content .= "?>";

        // Ruta al archivo PHP de configuración de contraseña
        $config_file_path = 'config-panel.php';  // Cambiado a config-panel.php

        // Escribe el nuevo contenido en el archivo de configuración
        if (file_put_contents($config_file_path, $new_content) !== false) {
            $message = '¡Contraseña actualizada con éxito!';
        } else {
            $message = 'Error: No se pudo actualizar la contraseña.';
        }
    }
    //Modificar nombre de mi usuario
    if (isset($_POST['new_username']) && !empty($_POST['new_username'])) {
        // Obtiene el nuevo nombre de usuario desde el formulario
        $new_username = $_POST['new_username'];

        $new_content = "<?php\n";
        $new_content .= "// Usuario único (puedes cambiar esto)\n";
        $new_content .= "define('USERNAME', '$new_username');\n\n"; // Actualiza el nombre de usuario
        $new_content .= "// Contraseña encriptada con password_hash().\n";
        $new_content .= "define('PASSWORD_HASH', '" . PASSWORD_HASH . "');\n"; // Mantiene el hash de contraseña actual
        $new_content .= "?>";
        if (file_put_contents($config_file_path, $new_content) !== false) {
            $message = '¡Nombre de usuario actualizado con éxito!';
        } else {
            $message = 'Error: No se pudo actualizar el nombre de usuario';
        }
    }
    // Añadir usuario y correos
    if (isset($_POST['new_user'])) {
        $newUser = $_POST['new_user'];
        $idTg = $_POST['tg_id']? $_POST['tg_id'] : "";
        $emails = explode(',', $_POST['emails']);
        $emails = array_map('trim', $emails); // Limpiar los correos
        $newUser = $newUser.",".$idTg;

        if (!isset($users[$newUser])) {
            $users[$newUser] = $emails;
            $message = 'Usuario añadido';
        } else {
            // Añadir correos a usuario existente
            foreach ($emails as $email) {
                if (!in_array($email, $users[$newUser])) {
                    $users[$newUser][] = $email;
                }
            }
            $message = 'Cuentas añadidas';
        }

        file_put_contents($usersFile, json_encode($users));
    }

    // Cambiar correo de un usuario a otro
    if (isset($_POST['change_email']) && isset($_POST['new_user'])) {
        $oldEmail = $_POST['change_email'];
        $newUser = $_POST['new_user'];
        if (!isset($users[$newUser])) {
            $users[$newUser] = [];
        }
        if(strlen($oldEmail)>3){
        $users[$newUser][] = $oldEmail;
        $message = "Correo $oldEmail añadido a $newUser";
        file_put_contents($usersFile, json_encode($users));

        $key = array_search("", $users[$newUser]);
        unset($users[$user][$key]);
        file_put_contents($usersFile, json_encode($users));
        }
    }

    // Eliminar un correo
    if (isset($_POST['delete_email'])) {
        $emailToDelete = $_POST['delete_email'];
        $user = $_POST['user_id'];
        $key = array_search($emailToDelete, $users[$user]);
        unset($users[$user][$key]);
        $message = "Correo $emailToDelete eliminado de $user";

        file_put_contents($usersFile, json_encode($users));
    }

    // Eliminar un usuario
    if (isset($_POST['delete_user'])) {
        $username = $_POST['delete_user']; // Cambiado a POST para evitar confusiones con GET

        if (isset($users[$username])) {
            unset($users[$username]);

            // Guardar los usuarios actualizados en el archivo JSON
            file_put_contents($usersFile, json_encode($users));
            $message = "Usuario '$username' eliminado.";
        }
    }
}

function renderUsers($users) {
    foreach ($users as $user => $emails) {
        $usr = explode(",",$user);
        $name = $usr[0];
        $tgId = $usr[1];
	echo '<tr class="max_width">';
        echo '<td  style="padding-right:50px; width: 50%; padding-left: 20px;">' . htmlspecialchars($name) . '</td>';
        echo '<td>';
        echo '<button class="show-hide" onclick="toggleEmails(\'' . htmlspecialchars($user) . '\')">Mostrar</button>';
        echo '</td>';
        echo '<td>';
        echo '<button class="show-hide" onclick="toggleAddEmails(\'' . htmlspecialchars($user) . '\')">Añadir Correo</button>';
        echo '</td>';
        echo '<td>';
        echo '<button class="delete" type="button" onclick="deleteUser(\''.htmlspecialchars($user).'\')">Eliminar</button>'; // Cambiado a type="button"
        echo '</td>';
        echo '</tr>';
        echo '<tr class="max_width" id="add-emails-'.htmlspecialchars($user).'" style="display:none;">';
        echo '<td class="max_width" style="text-align: center; padding: 20px 0;">';
             echo '<form method="POST">';
                echo '<input type="hidden" name="new_user" value="'.htmlspecialchars($name).'">';
                echo '<input type="hidden" name="tg_id" placeholder="Id telegram usuario" style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;" value='.$tgId.'>';
                echo '<input type="text" name="emails" placeholder="Correos separados por comas" required style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;"><br>';
                echo '<button type="submit">Añadir Correos</button>';
             echo '</form>';
        echo '</td>';
        echo '</tr>';
        echo '<tr id="emails-' . htmlspecialchars($user) . '" style="text-align: center; display: none; width: 100%; margin: 20px auto;" class="max_width">';
        echo '<td style="width: 10%; margin: 0 auto;">';
        echo '<table style="width: 100%; text-align: center; border-radius: 5px;">';
        foreach ($emails as $email) {
            echo '<tr class="max_width" style="margin: 0 auto; width: 65%; padding: 3px 25px; background: #e8f9fa;">';
            echo '<td style="padding-right:50px; width: 100%;">' . htmlspecialchars($email) . '</td>';

            echo '<td><button class="delete" onclick="deleteEmail(\''.htmlspecialchars($user).'\',\'' . htmlspecialchars($email) . '\')">Eliminar</button></td>';
         //   echo '<td><button class="change" onclick="openChangeUserModal(\'' . htmlspecialchars($email) . '\')">Añadir a</button></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</td>';
        echo '</tr>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Ventana emergente */
        td:last-child, td:nth-last-child(2) {
            text-align: right; /* Alinear botones a la derecha */
        }
        .delete{
            background: #ea6060;
            float: right;
        }

        .delete:hover {
            background: #c85151;
        }

        .max_width{
            display: block;
            width: 100%;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0 auto;
            gravity: center;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            display: block;
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            width: 30%;
            border: 1px solid #888;
            border-radius: 20px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <a href="logout.php" style="float: right; margin: 20px; background: #575e7a; padding: 5px 15px 5px 15px; border-radius: 20px; font-weight: bold; color: white;">Cerrar sesión</a>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
    <div class="container">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

        <form method="POST">
            <input type="text" name="new_user" placeholder="Nombre de nuevo usuario" required style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;">
            <input type="text" name="tg_id" placeholder="Id telegram usuario" style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;">
            <input type="text" name="emails" placeholder="Correos separados por comas" required style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;">
            <br>
            <button type="submit">Añadir Correos</button>
        </form>


        <h2>Usuarios:</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <?php renderUsers($users); ?>
        </table>
 <!--       <h2>Correos Principales(reenviados):</h2>
        <form method="POST">
            <input type="text" name="new_user" placeholder="Correo" required style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;">
            <input type="text" name="emails" placeholder="Contraseña" required style="margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;">
            <br>
            <button type="submit">Añadir Correos</button>
        </form>
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
        </table>
        <h2>Configuración de plataformas</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
        </table> -->
    <h2>Modificar usuario:</h2>
    <form method="post" action="">
        <label for="new_password">Nueva Contraseña:</label>
        <input type="password" id="new_password" name="new_password" style="padding: 5px; margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;" placeholder="Nueva contraseña" required>
        <button type="submit">Actualizar Contraseña</button>
    </form>
    <form method="post" action="">
        <label for="new_username">Nuevo Usuario:</label>
        <input id="new_username" name="new_username" style="padding: 5px; margin-bottom: 10px; border-radius: 20px; border: 2px solid #007bff;" placeholder="Nuevo nombre" required>
        <button type="submit">Actualizar Usuario</button>
    </form>
    </div>

    <!-- Modal para cambiar usuario -->
    <div id="changeUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeChangeUserModal()">&times;</span>
            <h2>Cambiar Correo</h2>
            <p>Selecciona un usuario:</p>
            <select id="userSelect">
                <?php foreach ($users as $user => $emails): ?>
                    <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                <?php endforeach; ?>
            </select>
            <button onClick="añadir()" id="changeUserConfirm">Confirmar</button>
        </div>
    </div>

    <script>
        let emailToChange = '';

        function toggleEmails(user) {
            const table = document.getElementById('emails-' + user);
            if (table.style.display === "none") {
                table.style.display = "block"; // Cambiar a "table" para mostrar
            } else {
                table.style.display = "none"; // Cambiar a "none" para ocultar
            }
        }
        function toggleAddEmails(user){
            const table = document.getElementById('add-emails-' + user);
            if (table.style.display === "none") {
                table.style.display = "block"; // Cambiar a "table" para mostrar
            } else {
                table.style.display = "none"; // Cambiar a "none" para ocultar
            }
        }

        // Abrir el modal
        function openChangeUserModal(email) {
            emailToChange = email; // Guardar el correo a cambiar
            document.getElementById('changeUserModal').style.display = "block";
        }

        function closeChangeUserModal() {
            document.getElementById('changeUserModal').style.display = "none";
        }

        function añadir() {
            const newUser = document.getElementById('userSelect').value;

            // Crear un formulario oculto para enviar la solicitud POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Acción vacía para enviar al mismo script

            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'change_email';
            emailInput.value = emailToChange;
            // El correo a cambiar
            const userInput = document.createElement('input');
            userInput.type = 'hidden';
            userInput.name = 'new_user';
            userInput.value = newUser; // El nuevo usuario

            form.appendChild(emailInput);
            form.appendChild(userInput);
            document.body.appendChild(form);
            if(emailInput.value!=''){
                    form.submit(); // Enviar el formulario
            }
        };

        function deleteEmail(user_id,email) {
            if (confirm('¿Estás seguro de que deseas eliminar este correo: ' + email + '?')) {
                // Crear un formulario oculto para enviar la solicitud POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Acción vacía para enviar al mismo script

                const usernameInput = document.createElement('input');
                usernameInput.type = 'hidden';
                usernameInput.name = 'user_id';
                usernameInput.value = user_id; // El usuario a eliminar

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'delete_email';
                emailInput.value = email; // El correo a eliminar

                form.appendChild(usernameInput);
                form.appendChild(emailInput);
                document.body.appendChild(form);
                form.submit(); // Enviar el formulario
            }
        }

        function deleteUser(username) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario: ' + username + '?')) {
                // Crear un formulario oculto para enviar la solicitud POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Acción vacía para enviar al mismo script

                const usernameInput = document.createElement('input');
                usernameInput.type = 'hidden';
                usernameInput.name = 'delete_user';
                usernameInput.value = username; // El usuario a eliminar

                form.appendChild(usernameInput);
                document.body.appendChild(form);
                form.submit(); // Enviar el formulario
            }
        }
    </script>
</body>
</html>

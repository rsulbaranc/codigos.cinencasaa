<?php

include_once __DIR__ . "/config.php";
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\r\n    <meta name=\"viewport\" content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">\r\n\r\n    <link rel=\"icon\" href=\"assets/img/logocinencasaa.webp\">\r\n    <meta name=\"description\" content=\"Consulta de Correo.\">\r\n \r\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"\" crossorigin=\"anonymous\">\r\n\t    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css\" rel=\"stylesheet\">\r\n    <link rel=\"icon\" type=\"image/png\" href=\"assets/img/favicon.png\">\r\n    <link rel=\"stylesheet\" href=\"assets/css/global.css\">\r\n    <link rel=\"stylesheet\" href=\"assets/css/home.css\">\r\n     <link rel=\"stylesheet\" href=\"assets/css/miestilo.css\">\r\n\r\n    <title>Consulta de Correo Electr&oacute;nico</title>\r\n</head>  \r\n \r\n";

        $tipoPlataforma = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
        echo "<body>\r\n\r\n \r\n    <!--Navbar-->\r\n    <div class=\"navbar\">\r\n        <div class=\"menu-mobile\">\r\n            <a href='index.php' title=\"Verificador de Email\">\r\n                <div class=\"d-flex justify-content-center align-items-center gap-1\">\r\n                     <img src=\"assets/img/logo2.png\" alt=\"Logo Verificador\" class=\"rounded mx-auto d-block\" style=\"height:27px;\">    <h5 style=\"margin:0;\">\r\n    Codigos Cine en Casa </h5> \r\n                     \r\n                </div>\r\n            </a>\r\n\r\n            <div class=\"hamburger\">\r\n                <i class=\"bi bi-list\"></i>\r\n            </div>\r\n        </div>\r\n";
        echo " <div class=\"links\">\r\n            <a href=\"index.php\" class=\"link active\"><i class=\"bi bi-house\"></i> Inicio</a>\r\n            <a class=\"link\" href=\"" .
            $GLOBAL_LINK_1 .
            "\"><i class=\"bi bi-bookmark\"></i> " .
            $GLOBAL_LINK_1_TEXTO .
            "</a>\r\n            <a class=\"link\" href=\"" .
            $GLOBAL_LINK_2 .
            "\"><i class=\"bi bi-instagram\"></i> " .
            $GLOBAL_LINK_2_TEXTO .
            "</a>\r\n            <a class=\"link\" target=\"_blank\" href=\"https://t.me/" .
            $GLOBAL_NUMERO_WHATSAPP .
            "?text=" .
            $GLOBAL_TEXTO_WHATSAPP .
            "\"><i class=\"bi bi-whatsapp\"></i> Contacto</a>\r\n        </div>";
        echo "        \r\n    </div>\r\n \r\n\r\n<section id=\"fondo1\">\r\n<div class=\"container\" >\r\n   <div class=\"row\"><h2 class=\"section-title\">Consulta de Correo Electrónico</h2>\r\n    <div class=\"col-sm-4\">\r\n\t\r\n\t<div class=\"   \">\r\n                <a href=\"index.php?tipo=netflix\" class=\"url\"   >\r\n                    <div class=\"link-plataformas animacionnetflix\">\r\n                        <h5 class=\"tituloplataformas\">Netflix</h5>\r\n                        <div class=\"link-description\">\r\n                            <p class=\"description\">Recupera tu codigo de acceso temporal.</p>\r\n                            <div class=\"icono-netflix\">\r\n\t\t\t\t\t\t\t<i class=\"bi bi-badge-hd\"></i>\r\n \r\n                            </div>\r\n                        </div>\r\n                    </div>\r\n                </a>\r\n\r\n                <a href=\"index.php?tipo=disney\" class=\"url\">\r\n                    <div class=\"link-plataformas animaciondisney\">\r\n                        <h5 class=\"tituloplataformas\">Disney</h5>\r\n                        <div class=\"link-description\">\r\n                            <p class=\"description\">Recupera Acceso único para Disney+</p>\r\n                            <div class=\"icono-disney\">\r\n                                <i class=\"bi bi-tv-fill\"></i>\r\n                            </div>\r\n                        </div>\r\n                    </div>\r\n                </a>\r\n\t\t\t\t
        <a href=\"index.php?tipo=prime\" class=\"url\">\r\n                    <div class=\"link-plataformas animacionstar\">\r\n                        <h5 class=\"tituloplataformas\">Prime Video</h5>\r\n                        <div class=\"link-description\">\r\n                            <p class=\"description\">Intento de inicio de sesión en Amazon Prime Video</p>\r\n                            <div class=\"icono-primevideo\">\r\n                                <i class=\"bi bi-cast\"></i>\r\n                            </div>\r\n                        </div>


        </div>\r\n                </a>\r\n\t\t\t\t \r\n  </div>\r\n  \r\n\t\r\n\t</div>\r\n\t<div class=\"col-sm-8\"> ";

function buscarCorreoEnCuenta($host, $username, $password, $consulted_email, $plataforma) {
    global $PLATFORM_KEYWORDS;

    // Asegurarse de que la plataforma existe en el array de palabras clave
    if (!isset($PLATFORM_KEYWORDS[$plataforma])) {
        return null;
    }

    // Obtener las palabras clave específicas para la plataforma
    $subject_keywords_groups = $PLATFORM_KEYWORDS[$plataforma]["SUBJECT_KEYWORDS"];
    $body_keywords_groups = $PLATFORM_KEYWORDS[$plataforma]["BODY_KEYWORDS"];

    $subject_keywords = [];

    foreach ($subject_keywords_groups as $keywords) {
        $subject_keywords = array_merge($subject_keywords, $keywords);
    }
    $body_keywords = [];
    foreach ($body_keywords_groups as $keywords) {
        $body_keywords = array_merge($body_keywords, $keywords);
    }

    $inbox = imap_open($host, $username, $password);
    if (!$inbox) {
        $error = imap_last_error();
        echo $error." ".$username;
        return null;
    }

    $time_limit = time() - 9000; // Últimos 150 minutos
    $search_criteria = "SINCE \"" . date("d-M-Y H:i:s", $time_limit) . "\"";
    $emails = imap_search($inbox, $search_criteria);
    if (!$emails) {
        imap_close($inbox);
        return null;
    }

    $emails_by_sender = [];
    $emails_by_recipient = [];

    foreach ($emails as $email) {
        $overview = imap_fetch_overview($inbox, $email, 0);
        $from_email = strtolower(trim(imap_rfc822_parse_adrlist($overview[0]->from, "")[0]->mailbox)) . "@" .
                      imap_rfc822_parse_adrlist($overview[0]->from, "")[0]->host;
        $to_email = strtolower(trim(imap_rfc822_parse_adrlist($overview[0]->to, "")[0]->mailbox)) . "@" .
                    imap_rfc822_parse_adrlist($overview[0]->to, "")[0]->host;

        if (!isset($emails_by_sender[$from_email])) {
            $emails_by_sender[$from_email] = $email;
        } else {
            $existing_email_date = strtotime($overview[0]->date);
            $latest_email_date = strtotime(imap_fetch_overview($inbox, $emails_by_sender[$from_email], 0)[0]->date);
            if ($latest_email_date < $existing_email_date) {
                $emails_by_sender[$from_email] = $email;
            }
        }

        if (!isset($emails_by_recipient[$to_email])) {
            $emails_by_recipient[$to_email] = $email;
        } else {
            $existing_email_date = strtotime($overview[0]->date);
            $latest_email_date = strtotime(imap_fetch_overview($inbox, $emails_by_recipient[$to_email], 0)[0]->date);
            if ($latest_email_date < $existing_email_date) {
                $emails_by_recipient[$to_email] = $email;
            }
        }
    }

    $last_sender_email_number = $emails_by_sender[$consulted_email] ?? null;
    $last_recipient_email_number = $emails_by_recipient[$consulted_email] ?? null;

    $last_email_number = $last_sender_email_number ?: $last_recipient_email_number;
    if ($last_email_number) {
        $overview = imap_fetch_overview($inbox, $last_email_number, 0);
        $structure = imap_fetchstructure($inbox, $last_email_number);
        $message_body = "";

        if (!empty($structure->parts)) {
            foreach ($structure->parts as $part_number => $part) {
                if ($part->type == TYPETEXT && $part->subtype == "HTML") {
                    $message_part = imap_fetchbody($inbox, $last_email_number, $part_number + 1);
                    switch ($part->encoding) {
                        case 0:
                        case 1:
                            $message_body .= $message_part;
                            break;
                        case 3:
                            $message_body .= base64_decode($message_part);
                            break;
                        case 4:
                            $message_body .= quoted_printable_decode($message_part);
                            break;
                        default:
                            $message_body .= $message_part;
                    }
                }
            }
        } else {
            $message_body = quoted_printable_decode(imap_body($inbox, $last_email_number));
        }

        // Verificar si el asunto contiene alguna de las palabras clave
        $subject_contains_keyword = false;
        foreach ($subject_keywords as $keyword) {
            $encoded_string = $overview[0]->subject;
            $nonencoded_string = iconv_mime_decode($encoded_string, 0, "UTF-8");
            if (stripos($nonencoded_string, $keyword) !== false) {
                $subject_contains_keyword = true;
                break;
            }
        }

        // Verificar si el cuerpo contiene alguna de las palabras clave
        $body_contains_keyword = false;
        foreach ($body_keywords as $keyword) {
            if (stripos($message_body, $keyword) !== false) {
                $body_contains_keyword = true;
                break;
            }
        }

        // Si se encuentra alguna coincidencia en el asunto o cuerpo
        if ($subject_contains_keyword || $body_contains_keyword) {
            imap_close($inbox);
            return $message_body;
        }
    }

    imap_close($inbox);
    return null;
}

function obtenerCuentasPorChatId($chatId, $users) {
    $cuentas = [];
    foreach ($users as $identificador => $cuenta) {
        if (in_array($chatId, explode(',', $identificador))) {
            foreach ($cuenta as $fruta) { $cuentas[] = $fruta; }
        }
    }
    return $cuentas;
}

//$users = json_decode(file_get_contents('users.json'), true);

$plataforma = isset($_GET["p"]) ? $_GET["p"] : "";
$consulted_email = isset($_GET["email"]) ? strtolower(trim($_GET["email"])) : null;
$password = isset($_GET["password"]) ? $_GET["password"] : null;

$cuentas = obtenerCuentasPorChatId($password, $users);

if (($plataforma && $consulted_email)&&(!$password || empty($cuentas))) {
    echo "<div align='center' class='alertanocorreoborde'><div align='center' class='alertanocorreo'>Contraseña incorrecta.</div></div>";
    exit;
}

else if (($plataforma && $consulted_email)&&(!in_array($consulted_email, $cuentas))) {
    echo "<div align='center' class='alertanocorreoborde'><div align='center' class='alertanocorreo'>No tienes acceso a este correo o no existe.</div></div>";
    exit;
}else if ($plataforma && $consulted_email) {
    $correo_encontrado = false;
    foreach ($CUENTAS_IMAP as $cuenta) {
        $resultado = buscarCorreoEnCuenta($cuenta['host'], $cuenta['username'], $cuenta['password'], $consulted_email, $plataforma);
        if ($resultado) {
            echo '<div style="background-color: white; padding: 20px; border: 1px solid #ddd;"> <h1>'.$resultado.'</h1> </div>';
            $correo_encontrado = true;
            break;
        }
    }
    if (!$correo_encontrado) {
        echo "<div align='center' class='alertanocorreoborde'><div align='center' class='alertanocorreo'>No se encontraron correos recientes relacionados con la plataforma solicitada.</div></div>";
    }
}        if ($tipoPlataforma) {
            echo "   \r\n           \r\n            \r\n            <br>\r\n<div id=\"emailForm\"  >\r\n        <h2 class=\"section-title\"> " .
                strtoupper(str_replace("-", " ", $_GET["tipo"])) .
                " - Consulta de Correo Electrónico</h2>\r\n            <form id=\"emailFormInner\" action=\"\" method=\"GET\"><br>\r\n              <div align=\"center\">  <label for=\"email\" style=\"color:#ffffff;\">Correo Electrónico:</label></div><br>\r\n                 <input type=\"hidden\"  name=\"p\" value =\"" .
                $tipoPlataforma .
                "\">\r\n                <input type=\"email\" id=\"buscar-email\" name=\"email\" class=\"input_Text form-control\" required placeholder=\"correo@tudominio.com\"><br> \r\n <input type=\"password\" id=\"password-placeholder\" name=\"password\" class=\"input_Text form-control\" required placeholder=\"TuContraseñaAqui\"><br> \r\n                <div align=\"center\"><button class=\"btn btn-success\" type=\"submit\"><i class=\"bi bi-search\"></i>  Consultar</button> <a class=\"btn btn-primary\" href=\"index.php\" role=\"button\"> <i class=\"bi bi-arrow-return-left\"></i> Volver</a>\r\n                  \r\n                  \r\n                    </div>\r\n            </form>\r\n</div>\r\n        ";
        } else {
{
                echo "\r\n            \r\n            \r\n            \t\r\n\t  <div align=\"center\">\r\n\t      </div>\r\n\t  <h2 class=\"section-title\">Full <span class=\"text-cambios\"><span id=\"dynamic-word\" class=\"dynamic-text\">Peliculas</span></span>  para toda tu Familia</h2><hr>\r\n\t\r\n\t\r\n\t\r\n\t\r\n\t<div class=\"row\"> \r\n    <div class=\"col texto-contenido\">\r\n      Gracias a nuestra herramienta de consulta de correo electrónico podras validar, recuperar,etc los datos de tu cuenta streaming de tu plataforma favorita.\r\n    </div>\r\n    <div class=\"col\">\r\n    <img src=\"assets/img/logocinencasaa.webp\" class=\"img-fluid imgyorobot\"  alt=\"Pelicuas en HD\">\r\n    </div>\r\n            \r\n";
            }
        }
        echo "\t\r\n\t \r\n \r\n\r\n \r\n</div>\r\n \r\n\t\r\n\t\r\n\t\r\n\t\r\n\t</div>\r\n  </div>\r\n</div>\r\n\r\n\r\n</section>\r\n\r\n\r\n      ";

        echo "\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n   \r\n\r\n    <!--Footer-->\r\n    <footer id=\"footer\"  style=\"background:#212529\">\r\n        <p class=\"copyright\">&copy; 2025 <span class=\"server-name-footer\"> <a href=\"https://codigos.cinencasaa.xyz/\">Codigos cinencasaa</a></span></p>\r\n        <div class=\"social-links\">\r\n            <a href=\"#\" class=\"link tiktok-link\"><i class=\"fa-brands fa-tiktok\"></i></a>\r\n            <a href=\"#\" class=\"link instagram-link\"><i class=\"fa-brands fa-square-instagram\"></i></a>\r\n            <a href=\"#\" class=\"link discord-link-footer\"><i class=\"fa-brands fa-discord\"></i></a>\r\n        </div>\r\n    </footer>\r\n</body>\r\n \r\n<script src=\"assets/js/scripts_of.js\"></script>\r\n<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js\"></script>\r\n<script src=\"assets/js/firefly_of.js\"></script>\r\n<script src=\"assets/js/main_of.js\" type=\"text/javascript\"></script>\r\n    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"   crossorigin=\"anonymous\"></script>\r\n \r\n \r\n\r\n</html>";
        exit("Consulta Realizada");

?>

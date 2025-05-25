<?php
include_once __DIR__ . "/config.php";

$json_file_path = 'json/platform_explodes.json';
$json = file_get_contents($json_file_path);
$platforms_explode = json_decode($json, true);


function buscarCorreoEnCuenta($host, $username, $password, $consulted_email, $plataforma, $tipo) {
    global $PLATFORM_KEYWORDS;

    // Verificar que la plataforma existe en el array de palabras clave
    if (!isset($PLATFORM_KEYWORDS[$plataforma])) {
        echo "Plataforma no soportada.";
        return null;
    }

    // Obtener las palabras clave específicas para la plataforma
    $subject_keywords = $PLATFORM_KEYWORDS[$plataforma]["SUBJECT_KEYWORDS"][$tipo];
    $body_keywords = $PLATFORM_KEYWORDS[$plataforma]["BODY_KEYWORDS"][$tipo];

    $inbox = imap_open($host, $username, $password);
    if (!$inbox) {
        $error = imap_last_error();
        echo $error . " " . $username;
        return null;
    }

    date_default_timezone_set('UTC');
    $time_limit = time()-60*30; // Últimos 150 minutos
    $search_criteria = 'SINCE "' . date('d-M-Y H:i', $time_limit) . '"';
    $emails = imap_search($inbox, $search_criteria);
    if (!$emails) {
        imap_close($inbox);
        return null;
    }

    $filtered_emails = [];
    foreach ($emails as $email) {
        $overview = imap_fetch_overview($inbox, $email, 0);
        $email_date = strtotime($overview[0]->date);
        if ($email_date >= $time_limit) {
            $filtered_emails[] = $email;
        }
    }

    if (empty($filtered_emails)) {
        imap_close($inbox);
        return null;
    }

    rsort($filtered_emails);

    $emails_by_sender = [];
    $emails_by_recipient = [];

    foreach ($filtered_emails as $email) {
        $overview = imap_fetch_overview($inbox, $email, 0);
        $from_email = strtolower(trim(imap_rfc822_parse_adrlist($overview[0]->from, "")[0]->mailbox)) . "@" .
                      imap_rfc822_parse_adrlist($overview[0]->from, "")[0]->host;
        $to_email = strtolower(trim(imap_rfc822_parse_adrlist($overview[0]->to, "")[0]->mailbox)) . "@" .
                    imap_rfc822_parse_adrlist($overview[0]->to, "")[0]->host;
        if (!isset($emails_by_sender[$from_email])) {
            $emails_by_sender[$from_email] = [];
        };
        $emails_by_sender[$from_email][] = $email;
        if (!isset($emails_by_recipient[$to_email])) {
            $emails_by_recipient[$to_email] = [];
        }
        $emails_by_recipient[$to_email][] = $email;
    }
    $last_sender_email_numbers = $emails_by_sender[$consulted_email] ?? [];
    $last_recipient_email_numbers = $emails_by_recipient[$consulted_email] ?? [];
    $last_email_numbers = array_merge($last_sender_email_numbers, $last_recipient_email_numbers);
    rsort($last_email_numbers);
    if ($last_email_numbers) {
        foreach ($last_email_numbers as $last_email_number) {
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
                $decoded_subject = '';
                $mime_parts = imap_mime_header_decode($overview[0]->subject);

                foreach ($mime_parts as $part) {
                    $decoded_subject .= $part->text;
                }
                if (stripos($decoded_subject, $keyword) !== false) {
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
    }


    imap_close($inbox);
    return null;
}

$plataforma = isset($_GET["p"]) ? $_GET["p"] : "";
$plataforma_sub = isset($_GET["type"]) ? $_GET["type"] : "";
$consulted_email = isset($_GET["email"]) ? strtolower(trim($_GET["email"])) : null;

if ($plataforma && $consulted_email) {
    $correo_encontrado = false;
    foreach ($CUENTAS_IMAP as $cuenta) {
        $resultado = buscarCorreoEnCuenta($cuenta['host'], $cuenta['username'], $cuenta['password'], $consulted_email, $plataforma, $plataforma_sub);
        if ($resultado) {
            $explode = $platforms_explode[$plataforma][$plataforma_sub];
            $parte1 = str_replace("LINK__", "", $explode[0]);
            $parte2 = "\"";
            $TIPO = explode("__",$explode[0])[0];
            if($TIPO=="CODE"){
                echo "IMAGEN1234".$resultado;
            }else{
                $resultado_final = explode($parte1,$resultado)[1];
                $resultado_final = explode($parte2,$resultado_final)[0];
                $resultado_final = $parte1.$resultado_final;
                echo $resultado_final;
            }
            $correo_encontrado = true;
            break;
        }
    }
    if (!$correo_encontrado) {
        echo "No se encontraron correos recientes relacionados con la plataforma solicitada.";
    }
}
?>

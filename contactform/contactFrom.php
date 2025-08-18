<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptchaSecret = '6LcUp8cqAAAAAJNAvGJyWKbdV1TR48qa08CcIyY3';
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);
    if (intval($responseKeys["success"]) !== 1) {
        http_response_code(400);
        echo 'Por favor, complete el reCAPTCHA.';
        die();
    }
    $para = 'warriols@warriol.site, warriol@hotmail.com';

    // título
    $titulo = 'Enviado desde mi sitio...';

    // mensaje
    $mensaje = '
        <html>
        <head>
          <title>Mensaje del webmaster</title>
        </head>
        <body>
          <p>!Aviso Importante!</p>
          <table>
            <tr>
              <th>nombre</th><th>correo</th>
            </tr>';

    if($_POST)
    {
        foreach ($_POST as $clave=>$valor)
        {
            if ($clave == 'email'){
                $clean_para = "";
                $valort = $valor;
                if (filter_var($valort,FILTER_VALIDATE_EMAIL)){
                    $clean_para = filter_var($valort,FILTER_SANITIZE_EMAIL);
                }
                $valor = $clean_para;
            } else {
                if (is_array($valor)) {
                    $valor = implode(", ", array_map('htmlspecialchars', $valor));
                } else {
                    $valor = filter_var($valor, FILTER_SANITIZE_STRING);
                }
            }
            if ($clave != 'g-recaptcha-response') {
                $mensaje .= "<tr><td>$clave</td><td>$valor</td></tr>";
            }
        }
    }

    $mensaje .= '
      </table>
    </body>
    </html>
    ';

    //$para = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);

    // Para enviar un correo HTML con un archivo adjunto, debe establecerse la cabecera Content-type
    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type: multipart/mixed; boundary="boundary"' . "\r\n";

    // Cabeceras adicionales
    $cabeceras .= 'To: Webmaster <warriols@warriol.site >' . "\r\n";
    $cabeceras .= 'From: Webmaster <admin@warriol.site>' . "\r\n";

    // Mensaje en formato MIME
    $mensaje_mime = '--boundary' . "\r\n";
    $mensaje_mime .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n\r\n";
    $mensaje_mime .= $mensaje . "\r\n\r\n";

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['attachment']['tmp_name'];
        $filename = $_FILES['attachment']['name'];
        $filedata = file_get_contents($file);
        $filedata = chunk_split(base64_encode($filedata));

        $mensaje_mime .= '--boundary' . "\r\n";
        $mensaje_mime .= 'Content-Type: application/octet-stream; name="' . $filename . '"' . "\r\n";
        $mensaje_mime .= 'Content-Transfer-Encoding: base64' . "\r\n";
        $mensaje_mime .= 'Content-Disposition: attachment; filename="' . $filename . '"' . "\r\n\r\n";
        $mensaje_mime .= $filedata . "\r\n\r\n";
    }

    $mensaje_mime .= '--boundary--';

    $res = mail($para, $titulo, $mensaje_mime, $cabeceras);

    if ($res) {
        echo 'Correo enviado exitosamente.';
    } else {
        http_response_code(500);
        echo 'Hubo un error al enviar el correo.';
    }
    exit;
}
http_response_code(400);
echo 'Solicitud inválida.';
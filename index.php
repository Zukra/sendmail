<?php

$msg = '';

if (array_key_exists('emailTo', $_POST)) {
    require './vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

    $login = empty($_POST['emailFrom']) ? '' : $_POST['emailFrom'];
    $psw = empty($_POST['psw']) ? '' : $_POST['psw'];

    $mail = getGMailPHPMailerConfig($login, $psw);

    $mail->setFrom($login);    //Set who the message is to be sent from

    if ($mail->addAddress($_POST['emailTo'])) { // Add a recipient
        $mail->isHTML(true);                     // Set email format to HTML
        $mail->Subject = empty($_POST['subject']) ? '' : $_POST['subject'];
        $mail->Body = $_POST['message'];         //Build a simple message body

        // multiple files upload
        if (array_key_exists('userFile', $_FILES)) {
            //Attach multiple files one by one
            for ($ct = 0; $ct < count($_FILES['userFile']['tmp_name']); $ct++) {
                $uploadFile = tempnam(sys_get_temp_dir(), sha1($_FILES['userFile']['name'][$ct]));
                $filename = $_FILES['userFile']['name'][$ct];
                if (move_uploaded_file($_FILES['userFile']['tmp_name'][$ct], $uploadFile)) {
                    $mail->addAttachment($uploadFile, $filename);  // Add attachments
                } else {
                    $msg .= 'Failed to move file to ' . $uploadFile;
                }
            }
        }

        //Send the message, check for errors
        if (!$mail->send()) {
            $msg = 'Sorry, something went wrong. Please try again later.'
                . '<br>' . $mail->ErrorInfo;
        } else {
            $msg = 'Message sent!' . '<br>' . $mail->ErrorInfo;
        }
    } else {
        $msg = 'Invalid email address, message ignored.' . '<br>' . $mail->ErrorInfo;
    }
    $mail->clearAddresses();
    $mail->clearAttachments();
    $mail->smtpClose();
}

function getGMailPHPMailerConfig($login = '', $psw = '') {
    $mail = new PHPMailer(); //Create a new PHPMailer instance

    $mail->isSMTP();          // Set mailer to use SMTP
    $mail->setLanguage('ru'); // To load the French version
    $mail->CharSet = 'UTF-8';

    $mail->SMTPDebug = 0; // 0 = off (for production use) 1 = client messages 2 = client and server messages
    $mail->Debugoutput = 'html'; //Ask for HTML-friendly debug output

    $mail->Host = 'smtp.gmail.com';      // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;              // Enable SMTP authentication
    $mail->Username = $login;            // SMTP username
    $mail->Password = $psw;              // SMTP password
    $mail->SMTPSecure = 'tls';           // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;

    return $mail;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--        <meta http-equiv="X-UA-Compatible" content="IE=edge">-->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="./vendor/twitter/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Send email</title>
</head>
<body>
<div class="container">
    <?php if (!empty($msg)) {
        echo "<h2>$msg</h2>";
    } ?>

    <form class="form-horizontal" method="POST" role="form" enctype="multipart/form-data">
        <legend>Sending mail from the server gmail.com</legend>

        <div class="form-group">
            <label for="inputEmailFrom" class="col-sm-2 control-label">Email From</label>
            <div class="col-sm-3">
                <input type="email" class="form-control" id="inputEmailFrom" name="emailFrom" placeholder="Gmail address">
            </div>

            <label for="inputPassword" class="col-sm-2 control-label">Password</label>
            <div class="col-sm-3">
                <input type="password" class="form-control" id="inputPassword" name="psw" placeholder="Gmail password">
            </div>
        </div>

        <div class="form-group">
            <label for="inputEmailTo" class="col-sm-2 control-label">Email To</label>
            <div class="col-sm-3">
                <input type="email" class="form-control" id="inputEmailTo" name="emailTo" placeholder="Email address">
            </div>
        </div>

        <div class="form-group">
            <label for="inputSubject" class="col-sm-2 control-label">Subject</label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="inputSubject" name="subject" placeholder="Subject" value="Test PHPMailer & CKeditor">
            </div>

            <label for="inputFiles" class="col-sm-2 control-label">Attachments</label>
            <div class="col-sm-3">
                <input type="hidden" name="MAX_FILE_SIZE" value="‪10241024‬"> <!-- 1024 * 1024 = ‪10.241024‬(Mb)-->
                <input type="file" id="inputFiles" multiple="multiple" name="userFile[]">
            </div>
        </div>

        <div class="form-group">
            <label for="message" class="control-label">Message</label>
            <textarea class="form-control" name="message" id="message"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send</button>
    </form>

</div>
<script src="./vendor/components/jquery/jquery.min.js"></script>
<script src="./vendor/twitter/bootstrap/dist/js/bootstrap.min.js"></script>

<script src="./vendor/ckeditor/ckeditor/ckeditor.js"></script>

<script>
    CKEDITOR.replace('message', {
        language: 'ru',
        uiColor: '#9AB8F3',
        height: 300,
        content: 'rtrtytyuyuiui'
    });

    var data = '<p><a href="https://support.google.com/mail/answer/6590?p=BlockedMessage&amp;visit_id=0-636263109271422529-1758983511&amp;rd=1">Gmail.com blocks some file types</a></p><br>'
        + '<h3>File types you can&#39;t include as attachments</h3>'
        + '<p><code>.ADE, .ADP, .BAT, .CHM, .CMD, .COM, .CPL, .EXE, .HTA, .INS, .ISP, .JAR, .JS </code></p>'
        + '<p><span class="red-text"><strong>(NEW)</strong></span><code>, .JSE, .LIB, .LNK, .MDE, .MSC, .MSI, .MSP, .MST,&nbsp;.NSH .PIF, .SCR, .SCT, </code></p>'
        + '<p><code>.SHB, .SYS, .VB, .VBE, .VBS, .VXD, .WSC, .WSF, .WSH</code></p>';
    CKEDITOR.instances.message.setData(data)
</script>
</body>
</html>
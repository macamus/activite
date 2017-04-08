<?php

$servername = "local";
$username   = "root";
$dbpass     = "";
$db_name    = "base";

session_start();

$code = $_SESSION['code'];

if (empty($_SESSION['login_user'])) {

	header('Location:index.php');
	exit();
} else {

	$link = @mysql_connect($servername, $username, $dbpass);
	$db   = @mysql_select_db($db_name);

	$sql = "SELECT email FROM clients where code='$code'";
	$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	while ($row = @mysql_fetch_array($req)) {
		$to_email = $row['email'];

		$lien = $code.".pdf";

		$name        = "name";
		$email       = "$to_email";
		$to          = "$name <$email>";
		$from        = "name ";
		$subject     = "facture";
		$mainMessage = "Hi, here's the file.";
		$fileatt     = "facture/".$lien;
		$fileatttype = "application/pdf";
		$fileattname = "facture.pdf";
		$headers     = "From: $from";

		// File
		$file = fopen($fileatt, 'rb');
		$data = fread($file, filesize($fileatt));
		fclose($file);

		// This attaches the file
		$semi_rand     = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		$headers .= "\nMIME-Version: 1.0\n".
		"Content-Type: multipart/mixed;\n".
		" boundary=\"{$mime_boundary}\"";
		$message = "This is a multi-part message in MIME format.\n\n".
		"-{$mime_boundary}\n" .
		"Content-Type: text/plain; charset=\"iso-8859-1\n".
		"Content-Transfer-Encoding: 7bit\n\n".
		$mainMessage."\n\n";

		$data = chunk_split(base64_encode($data));
		$message .= "--{$mime_boundary}\n" .
		"Content-Type: {$fileatttype};\n" .
		" name=\"{$fileattname}\"\n" .
		"Content-Disposition: attachment;\n".
		" filename=\"{$fileattname}\"\n" .
		"Content-Transfer-Encoding: base64\n\n".
		$data."\n\n".
		"-{$mime_boundary}-\n";

		// Send the email
		if (mail($to, $subject, $message, $headers)) {

			echo "The email was sent.";

			unlink('facture/'.$lien);

		} else {

			echo "There was an error sending the mail.";
		}

		header('Location:index.php');

		session_destroy();
	}

}
?>

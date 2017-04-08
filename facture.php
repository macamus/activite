<?php

session_start();

if (empty($_SESSION['login_user'])) {

	header('Location:index.php');
	exit();
} else {

	require ('fpdf/barcode.php');

	//====================================================declaration des variable
	$date      = date("d/m/y");
	$db_server = "localhost";
	$db_user   = "root";
	$db_pass   = "";
	$db_name   = "base";
	$dcode     = $_SESSION['code'];
	//$code ="201220161708";
	$i = 0;
	$s = 0;

	//====================================================creation une nouvelle page pdf
	$pdf = new PDF_Code128();
	$pdf->AddPage();
	$pdf->SetFont('Arial', '', 10);
	$pdf->Image('facture.png', 0, 0, 210, 297);

	//====================================================connexion a la base de donnees
	$link = @mysql_connect($db_server, $db_user, $db_pass);
	mysql_select_db($db_name);

	//===================================================inserer la date
	$pdf->SetXY(175, 50);
	$pdf->SetFont('times', '', 11);
	$texte = "date : ".$date.".";
	$pdf->MultiCell(80, 4, utf8_decode($texte));

	//==============================================================================selectionner la base de donnees
	$sql = "SELECT * FROM clients where code='$dcode'";
	$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	//==========================================================================================inserer les coordonnees du client

	while ($row = @mysql_fetch_array($req)) {

		$nom      = $row['nom'];
		$adresse  = $row['adresse'];
		$cp       = $row['cp'];
		$ville    = $row['ville'];
		$tel      = $row['tel'];
		$paiement = $row['paiement'];

		$pdf->SetXY(125, 55);
		$pdf->SetFont('times', '', 11);
		$texte = "".$nom."\n\n".$adresse."\n".$cp." ".$ville."\n\ntel : ".$tel."\n\npaiement : ".$paiement;
		$pdf->MultiCell(80, 4, utf8_decode($texte));

	}

	//==============================================================================selectionner la base de donnees
	$sql = "SELECT * FROM pieces where code='$dcode'";
	$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	//==========================================================================================inserer les coordonnees du client

	while ($row = @mysql_fetch_array($req)) {

		$type   = $row['type'];
		$marque = $row['marque'];
		$serie  = $row['serie'];

		$pdf->SetXY(125, 90);
		$pdf->SetFont('times', 'b', 11);
		$texte = "Type : ".$type."\nMarque : ".$marque."\nSerie : ".$serie;
		$pdf->MultiCell(80, 4, utf8_decode($texte));

	}

	//=======================================================================================inserer le code barre
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('times', '', 14);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetXY(117, 21);
	$pdf->MultiCell(50, 2, "".$dcode, 0, "R");
	$pdf->Code128(10, 277, $dcode, 60, 15);

	//=======================================================================================selectioner base de donnees pieces
	$result = "SELECT * FROM pieces where code='$dcode'";
	$req    = mysql_query($result) or die('Erreur SQL !'.$result.'<br />'.mysql_error());

	//=======================================================================================afficher les resultats
	while ($row = @mysql_fetch_array($req)) {

		//===================================================================================declarer les variables du resultat
		$cmc   = $row['carte_m'];
		$almc  = $row['alimentation'];
		$crc   = $row['carte_r'];
		$mdmc  = $row['modem'];
		$cgc   = $row['carte_g'];
		$cdc   = $row['cd'];
		$ddc   = $row['dd'];
		$proc  = $row['pro'];
		$ventc = $row['vent'];
		$cblc  = $row['cbl'];
		$bmc   = $row['b_memoir'];

		$cmt   = "carte mere";
		$almt  = "alimentation";
		$crt   = "carte reseau";
		$mdmt  = "modem";
		$cgt   = "carte graphique";
		$cdt   = "lecteur CD";
		$ddt   = "disque dure";
		$prot  = "processeur";
		$ventt = "ventilateur";
		$cblt  = "cable";
		$bmt   = "barrette de memoire";

		//=========================================================================================creation des tableau
		$tableau_1 = array($cmt, $almt, $crt, $mdmt, $cgt, $cdt, $ddt, $prot, $ventt, $cblt, $bmt);
		$tableau_2 = array($cmc, $almc, $crc, $mdmc, $cgc, $cdc, $ddc, $proc, $ventc, $cblc, $bmc);

		for ($s = 0; $s <= 10; $s++) {
			$tab_1  = $tableau_1[$s];
			$tab    = $tableau_2[$s];
			$totale = array_sum($tableau_2);
			if ($tab != 0) {

				$i     = $i+8;
				$tab_2 = number_format($tab, 2, ',', '');
				$tva   = number_format($tab_2*(20/120), 2, ',', ' ');
				$prix  = number_format($tab_2-($tab_2*(20/120)), 2, ',', ' ');

				$pdf->SetFont('times', 'B', 12);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY(10, 140+$i);
				$pdf->MultiCell(50, 2, $tab_1, 0, "L");

				$pdf->SetXY(90, 140+$i);
				$pdf->MultiCell(50, 2, $prix, 0, "R");

				$pdf->SetXY(110, 140+$i);
				$pdf->MultiCell(50, 2, $tva, 0, "R");

				$pdf->SetXY(145, 140+$i);
				$pdf->MultiCell(50, 2, $tab_2, 0, "R");

			}

		}

	}

	$sql = "SELECT * FROM programme where code='$dcode'";
	$req = mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

	while ($row = @mysql_fetch_array($req)) {

		$prog_1 = $row['prog1'];
		$prog_2 = $row['prog2'];
		$prog_3 = $row['prog3'];

		$prix = $prog_1;
		switch ($prix) {
			case "Windows, Pilote":
				$log = 69.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet":
				$log = 79.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet, Sans effacer fichiers":
				$log = 99.90;
				break;
			case "Installation de périphérique (imprimante, webcam...)":
				$log = 29.90;
				break;
			case "Installation pilotes":
				$log = 29.90;
				break;
			case "Réparation/configuration internet":
				$log = 49.90;
				break;
			case "Nétoyage et optimisation windows":
				$log = 49.90;
				break;
			case "Installation logiciel":
				$log = 29.90;
				break;
			case "":
				$log = "";
				break;
		}

		$prix_2 = $prog_2;
		switch ($prix_2) {
			case "Windows, Pilote":
				$log2 = 69.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet":
				$log2 = 79.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet, Sans effacer fichiers":
				$log2 = 99.90;
				break;
			case "Installation de périphérique (imprimante, webcam...)":
				$log2 = 29.90;
				break;
			case "Installation pilotes":
				$log2 = 29.90;
				break;
			case "Réparation/configuration internet":
				$log2 = 49.90;
				break;
			case "Nétoyage et optimisation windows":
				$log2 = 49.90;
				break;
			case "Installation logiciel":
				$log2 = 29.90;
				break;
			case "":
				$log2 = "";
				break;
		}

		$prix_3 = $prog_3;
		switch ($prix_3) {
			case "Windows, Pilote":
				$log3 = 69.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet":
				$log3 = 79.90;
				break;
			case "Windows, Pilote, Antivirus, Configuration internet, Sans effacer fichiers":
				$log3 = 99.90;
				break;
			case "Installation de périphérique (imprimante, webcam...)":
				$log3 = 29.90;
				break;
			case "Installation pilotes":
				$log3 = 29.90;
				break;
			case "Réparation/configuration internet":
				$log3 = 49.90;
				break;
			case "Nétoyage et optimisation windows":
				$log3 = 49.90;
				break;
			case "Installation logiciel":
				$log3 = 29.90;
				break;
			case "":
				$log3 = "";
				break;
		}

		$log_1 = number_format($log, 2, ',', '');
		$log_2 = number_format($log2, 2, ',', '');
		$log_3 = number_format($log3, 2, ',', '');
		$pdf->SetXY(10, 220);
		$pdf->SetFont('times', 'b', 11);
		$texte = "======================================PROGRAMME===================================";
		$pdf->MultiCell(200, 4, utf8_decode($texte));

		$pdf->SetXY(10, 230);
		$pdf->SetFont('times', 'b', 12);
		$texte = "".$prog_1."\n\n".$prog_2."\n\n".$prog_3;
		$pdf->MultiCell(150, 4, utf8_decode($texte));

		$pdf->SetXY(185, 230);
		$pdf->SetFont('times', 'b', 12);
		$texte = "".$log_1."\n\n".$log_2."\n\n".$log_3;
		$pdf->MultiCell(80, 4, utf8_decode($texte));

	}

	$ttotale = $totale+$log+$log2+$log3;

	$pdf->SetXY(145, 263);
	$pdf->MultiCell(50, 2, $ttotale." EURO ", 0, "R");

	mysql_close();

	$facture = "facture/".$dcode.".pdf";
	$pdf->Output($facture, "f");

	$_SESSION['code'] = $dcode;

	header('Location:mail.php');
//=========================================== Fin de programme ==========================================
}
?>

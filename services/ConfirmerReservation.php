<?php
// Service web du projet Réservations M2L
// Ecrit le 05/12/2017 par Pierre
// Modifié le 05/12/2017 par Pierre

// Ce service web permet à un utilisateur de confirmer sa déclaration
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 2 paramètres : nom, mdp
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://localhost/ws-php-delaunay/m.m2l/services/confirmerReservations.php?nom=admin&mdp=admin&id=id

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterReservations.php

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["mdp"]) == true)  $id = "";  else   $id = $_GET ["id"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["id"]) == true)  $id = "";  else   $id = $_POST ["id"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
		$msg = "Erreur : authentification incorrecte.";
	else 
	{	
	    if ($id == "") {
	        $msg = "Erreur : numéro de réservation inexistant.";
	    }
	    else {
	        $ok = $dao->estLeCreateur($nom, $id);
	        
	        if ( $ok == false ) {
	            $msg = "Erreur : vous n'êtes pas l'auteur de cette réservation.";
	        }
	        else {
	            
	            $res = $dao->getReservation($id);

	            if ($res->getStatus() == 4) {
	                $msg = "Erreur : cette réservation est déjà confirmée.";
	            }
	            else {
	                
	            }
	        }
	    }
	}
	// ferme la connexion à MySQL
	unset($dao);
}
// création du flux XML en sortie
creerFluxXML ($msg, $lesReservations);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
 


// création du flux XML en sortie
function creerFluxXML($msg, $lesReservations)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	//$doc->encoding = 'ISO-8859-1';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en ISO
	$elt_commentaire = $doc->createComment('Service web ConsulterReservations - BTS SIO - Lycée De La Salle - Rennes');
	// place ce commentaire à la racine du document XML
	$doc->appendChild($elt_commentaire);
	
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	
	// place l'élément 'reponse' dans l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	
	// place l'élément 'donnees' dans l'élément 'data'
	$elt_donnees = $doc->createElement('donnees');
	$elt_data->appendChild($elt_donnees);
	
	// traitement des réservations
	if (sizeof($lesReservations) > 0) {
		foreach ($lesReservations as $uneReservation)
		{
			// crée un élément vide 'reservation'
			$elt_reservation = $doc->createElement('reservation');
			// place l'élément 'reservation' dans l'élément 'donnees'
			$elt_donnees->appendChild($elt_reservation);
		
			// crée les éléments enfants de l'élément 'reservation'
			$elt_id         = $doc->createElement('id', $uneReservation->getId());
			$elt_reservation->appendChild($elt_id);
			$elt_timestamp  = $doc->createElement('timestamp', $uneReservation->getTimestamp());
			$elt_reservation->appendChild($elt_timestamp);
			$elt_start_time = $doc->createElement('start_time', date('Y-m-d H:i:s', $uneReservation->getStart_time()));
			$elt_reservation->appendChild($elt_start_time);
			$elt_end_time   = $doc->createElement('end_time', date('Y-m-d H:i:s', $uneReservation->getEnd_time()));
			$elt_reservation->appendChild($elt_end_time);
			$elt_room_name  = $doc->createElement('room_name', $uneReservation->getRoom_name());
			$elt_reservation->appendChild($elt_room_name);
			$elt_status     = $doc->createElement('status', $uneReservation->getStatus());
			$elt_reservation->appendChild($elt_status);
		
			// le digicode n'est renseigné que pour les réservations confirmées
			if ( $uneReservation->getStatus() == "0")		// réservation confirmée
				$elt_digicode = $doc->createElement('digicode', utf8_encode($uneReservation->getDigicode()));
			else										// réservation provisoire
				$elt_digicode = $doc->createElement('digicode', "");
			$elt_reservation->appendChild($elt_digicode);
		}
	}
	
	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	echo $doc->saveXML();
	return;
}
?>

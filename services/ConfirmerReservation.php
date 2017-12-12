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
if ( empty ($_GET ["id"]) == true)  $id = "";  else   $id = $_GET ["id"];
if ( empty ($_GET ["numreservation"]) == true)  $id = "";  else   $id = $_GET ["numreservation"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["numreservation"]) == true)  $id = "";  else   $id = $_POST ["numreservation"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	// Contrôle de l'authentification avec les paramètres
	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
		$msg = "Erreur : authentification incorrecte.";
	else 
	{	
	    // Contrôle du numéro de réservation
	    if ($id == "") {
	        $msg = "Erreur : numéro de réservation inexistant.";
	    }
	    else {
	        // Contrôle de l'auteur de la réservation
	        $ok = $dao->estLeCreateur($nom, $id);
	        
	        if ( $ok == false ) {
	            $msg = "Erreur : vous n'êtes pas l'auteur de cette réservation.";
	        }
	        else {
	            
	            // Contrôle du statut de la réservation : on vérifie si elle est déjà confirmée
	            $res = $dao->getReservation($id);

	            if ($res->getStatus() == 0) {
	                $msg = "Erreur : cette réservation est déjà confirmée.";
	            }
	            else {
	                
	                // Contrôle du temps : on vérifie si la réservation est déjà passée
	                
	                $endTime = $res->getEnd_time();
	                
	                // différence entre date actuel (unix) et la date de la réserv (stockée en unix)
	                // si la différence est positive alors la réservation n'est pas passée
	                // si la différence est négative alors la réservation est déja passée
	                
	                $diff = ($endTime - time() );
	                
	                if ($diff < 0){
	                    // la réservation est déjà passée
	                    $msg = "Erreur : cette réservation est déjà passée.";
	                    
	                }
	                else {
	                    
	                    // tout est ok, on peut confirmer la réservation et envoyer le mail à l'utilisateur
	                    
	                    $confirm = $dao->confirmerReservation($id);
	                    
	                    // on récupère l'email de l'utilisateur via le getter
	                    $user = $dao->getUtilisateur($nom);
	                    $mail = $user->getEmail();
	                    
	                    $sujet = "Confirmation réservation n° ".$id;
	                    $adresseEmetteur = "delasalle.sio.eleves@gmail.com";
	                    $message = "La réservation n° ".$id." a bien été confirmée ! "."Bonne journée ".$nom." ! ";
	                    $ok = Outils::envoyerMail($mail, $sujet, $message, $adresseEmetteur);
	                    
	                    if ( $ok ) {
	                        $msg = "Enregistrement effectué : vous allez recevoir un mail de confirmation.";
	                    }
	                    else {
	                        $msg = "Enregistrement effectué, cependant l'envoi de mail a échoué.";
	                    }
	                    
	                    
	                    
	                }
	                
	            }
	        }
	    }
	}
	// ferme la connexion à MySQL
	unset($dao);
}
// création du flux XML en sortie
creerFluxXML ($msg);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
 


// création du flux XML en sortie
function creerFluxXML($msg)
{	// crée une instance de DOMdocument (DOM : Document Object Model)

    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    //$doc->encoding = 'ISO-8859-1';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en ISO
    $elt_commentaire = $doc->createComment('Service web ConfirmerReservation - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}
?>

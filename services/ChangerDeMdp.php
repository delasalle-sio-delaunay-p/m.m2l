<?php
// Service web du projet Réservations M2L
// Ecrit le 05/12/2017 par Pierre
// Modifié le 05/12/2017 par Pierre

// Ce service web permet à un utilisateur de changer son mot de passe
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 2 paramètres : nom, mdp
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://localhost/ws-php-delaunay/m.m2l/services/ChangerDeMdp.php?nom=zenelsy&ancienMdp=ab&nouveauMdp=123&confirmationMdp=1234


// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_GET ["ancienMdp"];
if ( empty ($_GET ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_GET ["nouveauMdp"];
if ( empty ($_GET ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_GET ["confirmationMdp"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "")
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
    if ( empty ($_POST ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_POST ["ancienMdp"];
    if ( empty ($_POST ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_POST ["nouveauMdp"];
    if ( empty ($_POST ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_POST ["confirmationMdp"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $ancienMdp == "" || $nouveauMdp == "" || $confirmationMdp == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	if ( $dao->getNiveauUtilisateur($nom, $ancienMdp) == "inconnu" ) {
	    $msg = "Erreur : authentification incorrecte.";
	}
	else
	 {	
	    if ( $nouveauMdp != $confirmationMdp) {
	        $msg = "Erreur : le nouveau mot de passe et sa confirmation sont différents.";
	    }
	    else {
	        
	        $change = $dao->modifierMdpUser($nom, $nouveauMdp);
	        
	        // on récupère l'email de l'utilisateur via le getter
	        $user = $dao->getUtilisateur($nom);
	        $mail = $user->getEmail();
	        
	        // envoi d'un mail de confirmation du changement
	        $sujet = "Changement de mot de passe dans le système de réservation de M2L";
	        $contenuMail = "Votre nouveau mot de passe a bien été modifié par : ".$nouveauMdp."\n\n";
	        
	        $ok = Outils::envoyerMail($mail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
	        if ( ! $ok ) {
	            // l'envoi de mail a échoué
	            $msg = "Enregistrement effectué ; l'envoi du mail de confirmation a rencontré un problème.";
	        }
	        else {
	            // tout a bien fonctionné
	            $msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation.";
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

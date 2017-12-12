<?php
// Service web du projet Réservations M2L
// Ecrit le 05/12/2017 par Pierre
// Modifié le 05/12/2017 par Pierre

// Ce service web permet à un utilisateur de demander un nouveau mot de passe
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 2 paramètres : nom, mdp
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://localhost/ws-php-delaunay/m.m2l/services/DemanderMdp.php?nom=fonfecs

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];

// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
}

// Contrôle de la présence des paramètres
if ( $nom == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	$existUser = $dao->existeUtilisateur($nom);
	
	if ($existUser == false) 
	{
	    $msg = "Erreur : nom d'utilisateur inexistant.";
	}
	else {
	    // l'utilisateur existe, on peut poursuivre (création de mdp puis envoie de mail)
	    
	    $nouveauMdp = Outils::creerMdp();
	    
	    // enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir codé en MD5
	    $dao->modifierMdpUser ($nom, $nouveauMdp);
	    
	    // envoi d'un mail à l'utilisateur avec son nouveau mot de passe
	    $ok = $dao->envoyerMdp ($nom, $nouveauMdp);
	    
	    if ( $ok ) {
	        $msg = "Vous allez recevoir un mail avec votre nouveau mot de passe.";
	    }
	    else {
	        $msg = "Erreur : échec lors de l'envoi du mail.";
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

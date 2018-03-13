<?php
// Projet Réservations M2L
// fichier : services/ChangerDeMdp.php
// Dernière mise à jour : 13/03/2018 par Pierre

// Rôle : ce service web permet à un utilisateur de changer son mot de passe
// Le service web doit recevoir 5 paramètres : nom, ancienMdp, nouveauMdp, confirmationMdp, lang
//     nom : le nom (ou login) de connexion de l'utilisateur
//     ancienMdp : le mot de passe de connexion de l'utilisateur
//     nouveauMdp : 
//     confirmationMdp : 
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service fournit un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/CreerUtilisateur.php?nomAdmin=admin&mdpAdmin=admin&name=test&lang=json

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/CreerUtilisateur.php

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST["nom"]) == true)  $nom = "";  else   $nom = $_REQUEST["nom"];
if ( empty ($_REQUEST["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_REQUEST["ancienMdp"];
if ( empty ($_REQUEST["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_REQUEST["nouveauMdp"];
if ( empty ($_REQUEST["confirmationMdp"]) == true) $confirmationMdp = "";  else   $confirmationMdp = $_REQUEST["confirmationMdp"];
if ( empty ($_REQUEST["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

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
// création du flux en sortie
if ($lang == "xml")
    creerFluxXML ($msg);
    else
        creerFluxJSON ($msg);
        
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
    $elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
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

function creerFluxJSON($msg)
{
    
    // construction de l'élément "reservation"
    //$elt_reservation = ["reservation" => $lesLignesDuTableau];
    
    // construction de l'élément "data"
    //$elt_data = ["reponse" => $msg, "donnees" => $elt_reservation];
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
    
}
?>

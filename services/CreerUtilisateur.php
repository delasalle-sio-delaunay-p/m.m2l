<?php
// Projet Réservations M2L
// fichier : services/CreerUtilisateur.php
// Dernière mise à jour : 21/2/2018 par Jim

// Rôle : ce service web permet à un administrateur authentifié d'enregistrer un nouvel utilisateur
// Le service web doit recevoir 6 paramètres : nomAdmin, mdpAdmin, name, level, email, lang
//     nomAdmin : le nom (ou login) de connexion de l'administrateur
//     mdpAdmin : le mot de passe de connexion de l'administrateur
//     name : le nom de l'utilisateur à créer
//     level : le niveau de l'utilisateur à créer (0, 1 ou 2)
//     email : l'adresse mail de l'utilisateur à créer
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service fournit un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/CreerUtilisateur.php?nomAdmin=admin&mdpAdmin=admin&name=jim&level=1&email=jean.michel.cartron@gmail.com&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/CreerUtilisateur.php

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_REQUEST["nomAdmin"];
if ( empty ($_REQUEST["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_REQUEST["mdpAdmin"];
if ( empty ($_REQUEST["name"]) == true)  $name = "";  else   $name = $_REQUEST["name"];
if ( empty ($_REQUEST["level"]) == true)  $level = "";  else   $level = $_REQUEST["level"];
if ( empty ($_REQUEST["email"]) == true)  $email = "";  else   $email = $_REQUEST["email"];
if ( empty ($_REQUEST["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Contrôle de la présence des paramètres
if ( $nomAdmin == "" || $mdpAdmin == "" || $name == "" || $level == "" || $email == "" || Outils::estUneAdrMailValide ($email) == false ) {
	$msg = "Erreur : données incomplètes ou incorrectes.";
}
else {
	if ( $level != "0" && $level != "1" && $level != "2" ) {
		$msg = "Erreur : le niveau doit être 0, 1 ou 2.";
	}
	else {
		if ( $dao->getNiveauUtilisateur($nomAdmin, $mdpAdmin) != "administrateur" ) {
			$msg = "Erreur : authentification incorrecte.";
		}
		else
		{	
			if ( $dao->existeUtilisateur($name) ) {
				$msg = "Erreur : nom d'utilisateur déjà existant.";
			}
			else {
				// création d'un mot de passe aléatoire de 8 caractères
				$password = Outils::creerMdp();
				// enregistre l'utilisateur dans la bdd
				$unUtilisateur = new Utilisateur(0, $level, $name, $password, $email);
				$ok = $dao->creerUtilisateur($unUtilisateur);
				if ( ! $ok ) {
					$msg = "Erreur : problème lors de l'enregistrement du nouveau utilisateur.";
				}
				else {
					// envoi d'un mail de confirmation de l'enregistrement
					$sujet = "Création de votre compte dans le système de réservation de M2L";
					$contenuMail = "L'administrateur du système de réservations de la M2L vient de vous créer un compte utilisateur.\n\n";
					$contenuMail .= "Les données enregistrées sont :\n\n";
					$contenuMail .= "Votre nom : " . $name . "\n";
					$contenuMail .= "Votre mot de passe : " . $password . " (nous vous conseillons de le changer lors de la première connexion)\n";
					$contenuMail .= "Votre niveau d'accès (0 : invité    1 : utilisateur    2 : administrateur) : " . $level . "\n";
					
					$ok = Outils::envoyerMail($email, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
					if ( ! $ok ) {
						// l'envoi de mail a échoué
						$msg = "Enregistrement effectué ; l'envoi du mail à l'utilisateur a rencontré un problème.";
					}
					else {
						// tout a bien fonctionné
						$msg = "Enregistrement effectué ; un mail va être envoyé à l'utilisateur.";
					}
				}
			}
		}
	}
}

// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml")
    creerFluxXML ($msg);
else
    creerFluxJSON ($msg);
    
// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;


// création du flux XML en sortie
function creerFluxXML($msg)
{	
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web CreerUtilisateur - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
          <reponse>Erreur : données incomplètes ou incorrectes.</reponse>
        </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();	

	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en ISO
	$elt_commentaire = $doc->createComment('Service web CreerUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
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

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
     {
         "data":{
            "reponse":"Erreur : donn\u00e9es incompl\u00e8tes ou incorrectes."
         }
     }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}
?>
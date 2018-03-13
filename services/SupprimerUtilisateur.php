<?php
// Projet Réservations M2L
// fichier : services/CreerUtilisateur.php
// Dernière mise à jour : 13/03/2018 par Pierre

// Rôle : ce service web permet à un administrateur de supprimer un utilisateur
// Le service web doit recevoir 4 paramètres : nomAdmin, mdpAdmin, name, lang
//     nomAdmin : le nom (ou login) de connexion de l'administrateur
//     mdpAdmin : le mot de passe de connexion de l'administrateur
//     name : le nom de l'utilisateur à créer
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
if ( empty ($_REQUEST["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_REQUEST["nomAdmin"];
if ( empty ($_REQUEST["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_REQUEST["mdpAdmin"];
if ( empty ($_REQUEST["name"]) == true)  $name = "";  else   $name = $_REQUEST["name"];
if ( empty ($_REQUEST["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

include_once ('../modele/DAO.class.php');
$dao = new DAO();
// Contrôle de la présence des paramètres
if ( $nomAdmin == "" || $mdpAdmin == "" || $name == "") {
    $msg = "Erreur : données incomplètes ou incorrectes.";
}
else {
    if ( !$dao->getUtilisateur($nomAdmin))
    {
        $msg = "Erreur : authentification incorrecte.";
    }
    else {
        // connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
        include_once ('../modele/DAO.class.php');
        $dao = new DAO();
        
        if ( $dao->getNiveauUtilisateur($nomAdmin, $mdpAdmin) != "administrateur" ) // ERREUR A CORRIGER
        {
            $msg = "Erreur : authentification incorrecte.";
        }
        else {
            if ( !$dao->existeUtilisateur($name) )
            {
                $msg = "Erreur : nom d'utilisateur inexistant.";
            }
            else {
                $lesReservations = $dao->getLesReservations($name);
                if (sizeof($lesReservations) != 0)
                {
                    $msg = "Erreur : cet utilisateur a passé des réservations à venir.";
                }
                else {
                    $utilisateur = $dao->getUtilisateur($name);
                    $email = $utilisateur->getEmail();
                    
                    $dao->supprimerUtilisateur($name);
                    // envoi d'un mail de confirmation de la suppression
                    $sujet = "Suppression de votre compte dans le système de réservation de M2L";
                    $contenuMail = "L'administrateur du système de réservations de la M2L vient de supprimer votre compte utilisateur.\n\n";
                    
                    $ok = Outils::envoyerMail($email, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
                    if ( ! $ok ) {
                        // l'envoi de mail a échoué
                        $msg = "Suppression  effectuée ; l'envoi du mail à l'utilisateur a rencontré un problème.";
                    }
                    else {
                        // tout a bien fonctionné
                        $msg = "Suppression  effectuée ; un mail va être envoyé à l'utilisateur.";
                    }
                }
            }
        }
        // ferme la connexion à MySQL :
        unset($dao);
    }
}

// création du flux en sortie
if ($lang == "xml")
    creerFluxXML ($msg);
    else
        creerFluxJSON ($msg);
        
exit;
// création du flux XML en sortie
function creerFluxXML($msg)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
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
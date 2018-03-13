<?php
// Projet Réservations M2L
// fichier : services/ConsulterReservations.php
// Dernière mise à jour : 21/2/2018 par Jim

// Rôle : ce service permet à un utilisateur de consulter ses réservations à venir
// Le service web doit recevoir 3 paramètres : nom, mdp, lang
//     nom  : le nom (ou login) de connexion
//     mdp  : le mot de passe de connexion
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant la liste des réservations

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConsulterReservations.php?nom=zenelsy&mdp=passe&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterReservations.php

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST["nom"]) == true)  $nom = "";  else   $nom = $_REQUEST["nom"];
if ( empty ($_REQUEST["mdp"]) == true)  $mdp = "";  else   $mdp = $_REQUEST["mdp"];
if ( empty ($_REQUEST["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');

// initialisation du nombre de réservations
$nbReponses = 0;
$lesReservations = array();

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
		$msg = "Erreur : authentification incorrecte.";
	else 
	{	// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
		$dao->creerLesDigicodesManquants();
		
		// récupération des réservations à venir créées par l'utilisateur
		$lesReservations = $dao->getLesReservations($nom);
		$nbReponses = sizeof($lesReservations);
	
		if ($nbReponses == 0)
			$msg = "Erreur : vous n'avez aucune réservation.";
		else
			$msg = "Vous avez effectué " . $nbReponses . " réservation(s).";
	}
}

// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml")
    creerFluxXML ($msg, $lesReservations);
else
    creerFluxJSON ($msg, $lesReservations);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
 

// création du flux XML en sortie
function creerFluxXML($msg, $lesReservations)
{	
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web ConsulterReservations - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
          <reponse>Vous avez effectué 2 réservation(s).</reponse>
          <donnees>
            <reservation>
              <id>1</id>
              <timestamp>2018-02-21 11:26:10</timestamp>
              <start_time>2018-06-21 05:00:00</start_time>
              <end_time>2018-06-21 06:00:00</end_time>
              <room_name>Multimédia</room_name>
              <status>0</status>
              <digicode>4A3DDB</digicode>
            </reservation>
            <reservation>
              <id>2</id>
              <timestamp>2018-02-21 11:26:26</timestamp>
              <start_time>2018-06-21 05:00:00</start_time>
              <end_time>2018-06-21 06:00:00</end_time>
              <room_name>Salle informatique</room_name>
              <status>4</status>
              <digicode/>
            </reservation>
          </donnees>
        </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
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

// création du flux JSON en sortie
function creerFluxJSON($msg, $lesReservations)
{
    /* Exemple de code JSON
     {
         "data":{
             "reponse":"Vous avez effectu\u00e9 2 r\u00e9servation(s).",
             "donnees":{
                 "reservation":[
                     {
                         "id":"1",
                         "timestamp":"2018-02-21 11:26:10",
                         "start_time":"2018-06-21 05:00:00",
                         "end_time":"2018-06-21 06:00:00",
                         "room_name":"Multim\u00e9dia",
                         "status":"0",
                         "digicode":"4A3DDB"
                     },
                     {
                         "id":"2",
                         "timestamp":"2018-02-21 11:26:26",
                         "start_time":"2018-06-21 05:00:00",
                         "end_time":"2018-06-21 06:00:00",
                         "room_name":"Salle informatique",
                         "status":"4",
                         "digicode":""
                     }
                 ]
             }
         }
     }
     */
    
    // construction d'un tableau contenant les réservations
    $lesLignesDuTableau = array();
    if (sizeof($lesReservations) > 0) {
        foreach ($lesReservations as $uneReservation)
        {	// crée une ligne dans le tableau
            $uneLigne = array();
            $uneLigne["id"] = $uneReservation->getId();
            $uneLigne["timestamp"] = $uneReservation->getTimestamp();
            $uneLigne["start_time"] = date('Y-m-d H:i:s', $uneReservation->getStart_time());
            $uneLigne["end_time"] = date('Y-m-d H:i:s', $uneReservation->getEnd_time());
            $uneLigne["room_name"] = $uneReservation->getRoom_name();
            $uneLigne["status"] = $uneReservation->getStatus();
            // le digicode n'est renseigné que pour les réservations confirmées
            if ( $uneReservation->getStatus() == "0")   // réservation confirmée
                $uneLigne["digicode"] = utf8_encode($uneReservation->getDigicode());
            else                                        // réservation provisoire
                $uneLigne["digicode"] = "";
            
            $lesLignesDuTableau[] = $uneLigne;
        }
    }
    // construction de l'élément "reservation"
    $elt_reservation = ["reservation" => $lesLignesDuTableau];
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg, "donnees" => $elt_reservation];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}
?>

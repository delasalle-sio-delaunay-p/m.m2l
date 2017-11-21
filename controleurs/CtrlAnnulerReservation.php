<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlAnnulerReservation.php
// Rôle : traiter la demande de annuler une réservation d'un utilisateur
// écrit par Leilla le 17/10/2017
// modifié par Pierre le 07/11/2017

include_once ('modele/DAO.class.php');
$dao = new DAO();

// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauUtilisateur'] != 'utilisateur' && $_SESSION['niveauUtilisateur'] != 'administrateur') {
    // si le demandeur n'est pas authentifié, il s'agit d'une tentative d'accès frauduleux
    // dans ce cas, on provoque une redirection vers la page de connexion
    header ("Location: index.php?action=Deconnecter");
}
else {
    
    if ( ! isset ($_POST ["txtReservation"]) ) {
        // si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
        $message = '';
        $typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
        $themeFooter = $themeNormal;
        include_once ('vues/VueAnnulerReservation.php');
    }
    else {
 
        // Premier test : Pour savoir si les données sont incomplètes/incorrectes
    
        // récupération des données postées
        if ( empty ($_POST["txtReservation"]) == true)  $idReservation = "";  else $idReservation = $_POST["txtReservation"];
        
        $nomUtilisateur = $_SESSION['nom'];
        
        if (is_numeric($idReservation) == false || $idReservation == "")
        {
            $message = "Données incomplètes ou incorrectes !";
            $typeMessage = 'avertissement';                           // 2 valeurs possibles : 'information' ou 'avertissement'
            $themeFooter = $themeProbleme;
            include_once ('vues/VueAnnulerReservation.php');
        }
        else {
            
            // Deuxième test : On teste si la réservation existe
            if (!$dao->existeReservation($idReservation)){
                $message = "Numéro de réservation inexistant !";
                $typeMessage = 'avertissement';                           // 2 valeurs possibles : 'information' ou 'avertissement'
                $themeFooter = $themeProbleme;
                include_once ('vues/VueAnnulerReservation.php');
            }
            else {
                // Troisième test : On teste si la réservation est déjà passée ou non
                
                $laReservation = $dao->getReservation($idReservation);
                $laDateReservation = $laReservation->getEnd_time();
                
                if ($laDateReservation <= time()){
                    $message = "Cette réservation est déjà passée !";
                    $typeMessage = 'avertissement';
                    $themeFooter = $themeProbleme;
                    include_once ('vues/VueAnnulerReservation.php');
                }
                else {
                    // Quatrième test : On test si l'utilisateur est bien le créateur de cette réservation
                    
                    if ( !$dao->estLeCreateur($nomUtilisateur,$idReservation)){
                        $message = "Vous n'êtes pas l'auteur de cette réservation !";
                        $typeMessage = 'avertissement';
                        $themeFooter = $themeProbleme;
                        include_once ('vues/VueAnnulerReservation.php');
                    }
                    else {
                        
                        // Tout est bon, on annule la réservation et on procède à l'envoi du mail
                        
                        $ok = $dao->annulerReservation($idReservation);
                        
                        if ($ok) {
                            
                            // Récupère les informations de l'utilisateur
                            $utilisateur = $dao->getUtilisateur($nomUtilisateur);
                            $mail = $utilisateur->getEmail();
                            // Inclusion de la classe Outils pour utiliser la méthode envoyer mail
                            include_once ('modele/Outils.class.php');
                            
                            // Envoi d'un mail de confirmation de l'enregistrement
                            $sujet = "Annulation de votre réservation dans le système de réservation de M2L";
                            $contenuMail = "L'administrateur du système de réservations de la M2L vient d'annuler la réservation : " . $idReservation . "\n\n";
                            $adresseEmetteur = "delasalle.sio.eleves@gmail.com";
                            $envoi = Outils::envoyerMail($mail, $sujet, $contenuMail, $adresseEmetteur);
                            
                            if ( ! $envoi ) {
                                // Si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
                                $message = "Enregistrement effectué.<br>L'envoi du mail de confirmation a rencontré un problème !";
                                $typeMessage = 'avertissement';
                                $themeFooter = $themeProbleme;
                                include_once ('vues/VueAnnulerReservation.php');
                            }
                            else {
                                // Tout a fonctionné
                                $message = "Enregistrement effectué.<br>Vous allez recevoir un mail de confirmation !";
                                $typeMessage = 'information';
                                $themeFooter = $themeNormal;
                                include_once ('vues/VueAnnulerReservation.php');
                            }
                            
                        }                    
                    }       
                }
            }       
        }  
    }
}
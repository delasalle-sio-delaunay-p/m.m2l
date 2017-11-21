<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlConfirmerReservation.php
// Rôle : traiter la demande de confirmation d'une réservation d'un utilisateur
// écrit par Pierre le 17/10/2017
// modifié par Pierre le 17/10/2017


// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauUtilisateur'] != 'utilisateur' && $_SESSION['niveauUtilisateur'] != 'administrateur') {
    // si le demandeur n'est pas authentifié, il s'agit d'une tentative d'accès frauduleux
    // dans ce cas, on provoque une redirection vers la page de connexion
    header ("Location: index.php?action=Deconnecter");
}
else {
    
    if ( ! isset ($_POST ["numReservation"]) ) {
        // si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
        $message = '';
        $typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
        $themeFooter = $themeNormal;
        include_once ('vues/VueConfirmerReservation.php');
    }
    else {
        // récupération des données postées
        if ( empty ($_POST["numReservation"]) == true)  $numReservation = "";  else   $numReservation = $_POST["numReservation"];
        
        // première vérification
        if ( $numReservation == "" ) {
            // si les données sont incomplètes, réaffichage de la vue avec un message explicatif
            $message = 'Données incomplètes !';
            $typeMessage = 'avertissement';
            $themeFooter = $themeProbleme;
            include_once ('vues/VueConfirmerReservation.php');
        }
        else {
            // deuxième vérification : on vérifie si la réservation existe (grâce à la méthode existeReservation)
            
            // connexion du serveur web à la base MySQL
            include_once ('modele/DAO.class.php');
            // classe Reservation
            include_once ('modele/Reservation.class.php');
            // classe Outils
            include_once ('modele/Outils.class.php');
            $dao = new DAO();
            
            $existeReservation = $dao->existeReservation($numReservation);
            $nom = $_SESSION['nom'];
            if ($existeReservation == false ) {
                // la réservation n'existe pas
                $message = "La réservation n'existe pas !";
                $typeMessage = 'avertissement';
                $themeFooter = $themeProbleme;
                include_once ('vues/VueConfirmerReservation.php');
                
            }
            else {
                // la réservation existe
                
                // troisième vérification : on vérifie si l'utilisateur est l'auteur de cette réservation
                
                $isCreator = $dao->estLeCreateur($nom, $numReservation);
                
                if ($isCreator == false) {
                    // l'utilisateur n'est pas l'auteur
                    $message = "Vous n'êtes pas l'auteur de cette réservation !";
                    $typeMessage = 'avertissement';
                    $themeFooter = $themeProbleme;
                    include_once ('vues/VueConfirmerReservation.php');
                    
                } 
                else {
                    // l'utilisateur est l'auteur de cette déclaration
                    
                    // quatrième vérification : on vérifie si la réservation n'est pas déjà confirmée
                    // on récupère la réservation sous forme d'objet
                
                    $laReservation = $dao->getReservation($numReservation);
                    
                    // on va check le status avec le getter
                    
                    $status = $laReservation->getStatus();
                    
                    if ($status == 0){
                        // la réservation est déjà confirmée
                        $message = "La réservation est déjà confirmée !";
                        $typeMessage = 'avertissement';
                        $themeFooter = $themeProbleme;
                        include_once ('vues/VueConfirmerReservation.php');
                    }
                    else {
                        // la réservation n'est pas confirmée
                        
                        $endTime = $laReservation->getEnd_time();
                        
                        // différence entre date actuel (unix) et la date de la réserv (stockée en unix)
                        // si la différence est positive alors la réservation n'est pas passée
                        // si la différence est négative alors la réservation est déja passée
                        $diff = ($endTime - time() );
                        if ($diff < 0){
                            // la réservation est déjà passée
                            $message = "La réservation est déjà passée !";
                            $typeMessage = 'avertissement';
                            $themeFooter = $themeProbleme;
                            include_once ('vues/VueConfirmerReservation.php');
    
                        }
                        else {
                            // tout est ok, on peut confirmer la réservation et envoyer le mail à l'utilisateur
                            
                            $confirm = $dao->confirmerReservation($numReservation);
                            
                            // on récupère l'email de l'utilisateur via le getter
                            $user = $dao->getUtilisateur($nom);
                            $mail = $user->getEmail();
                            
                            $sujet = "Confirmation réservation n° ".$numReservation;
                            $adresseEmetteur = "delasalle.sio.eleves@gmail.com";
                            $message = "La réservation n° ".$numReservation." a bien été confirmée ! "."Bonne journée ".$nom." ! ";
                            $ok = Outils::envoyerMail($mail, $sujet, $message, $adresseEmetteur);
                            
                            if ( $ok ) {
                                $message = "Enregistrement effectué.<br>Vous allez recevoir un mail de confirmation.";
                                $typeMessage = 'information';
                                $themeFooter = $themeNormal;
                            }
                            else {
                                $message = "Enregistrement effectué.<br>L'envoi du mail de confirmation a rencontré un problème.";
                                $typeMessage = 'avertissement';
                                $themeFooter = $themeProbleme;
                            }
                            unset($dao);		// fermeture de la connexion à MySQL
                            include_once ('vues/VueConfirmerReservation.php');
                        }
                    }
            
                }
                
            }
        }
    }
}
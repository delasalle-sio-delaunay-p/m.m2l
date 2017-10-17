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
        $nouveauMdp = '';
        $confirmationMdp = '';
        $afficherMdp = 'off';
        $message = '';
        $typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
        $themeFooter = $themeNormal;
        include_once ('vues/VueConfirmerReservation.php');
    }
    else {
        // récupération des données postées
        if ( empty ($_POST ["numReservation"]) == true)  $numReservation = "";  else   $numReservation = $_POST ["numReservation"];
        
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
                    
                    
                    
                }
                
            }
        }
    }
}
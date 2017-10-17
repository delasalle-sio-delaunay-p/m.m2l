<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlConsulterReservations.php
// Rôle : traiter la demande de suppression d'un utilisateur
// écrit par Mickaël Coubrun le 17/10/2017

// on vérifie si le demandeur est admin

if($_SESSION['niveauUtilisateur'] != 'administrateur'){
    // si ce n'est pas l'admin il est renvoyé à la page de connexion
    header ("location: index.php?action=Deconnecter");
}
else {
    if(! isset ($_POST["txtName"])){
        // si données non postées, appel du formulaire, affichage vue sans erreur
        $name = '';
        $message = '';
        $typeMessage = '';      // information ou avertissement
        $themeFooter = $themeNormal;
        include_once ('vues/VueSupprimerUtilisateur.php');
    }
    else {
        // récupération des donées
        if(empty ($_POST["txtName"]) == true) $name; else $name = $_POST["txtName"];
        
        if($name == '') {
            // si données incorrectes ou incomplètes, réafichage vue avec message
            $message = 'Données incomplètes ou incorrectes !';
            $typeMessage ='avertissement';
            $themeFooter = $themeProbleme;
            include_once ('vues/VueSupprimerUtilisateur.php');
        }
        else {
            // connexion à la BDD
            include_once ('modele/DAO.class.php');
            $dao = new DAO();
            
            if( ! $dao->existeUtilisateur($name)){
                // si le nom n'existe pas, réaffichage vue
                $message = "Nom d'utilisateur inexistant !";
                $typeMessage= 'avertissement';
                $themeFooter= $themeProbleme;
                include_once ('vues/VueSupprimerUtilisateur.php');
            }
            else {
                // si l'utilisateur a passé des réservations, suppression refusée
                if ( $dao->aPasseDesReservations($name) ) {
                    $message = "Cet utilisateur a passé des réservations à venir !";
                    $typeMessage = 'avertissement';
                    $themeFooter = $themeProbleme;
                    include_once ('vues/VueSupprimerUtilisateur.php');
                }
                else {
                    // recherche adresse mail utilisateur (avant suppression)
                    $adrMail = $dao->getUtilisateur($name)->getEmail();
                    
                    // suppression utilisateur dans la BDD
                    $ok = $dao->supprimerUtilisateur($name);
                    if ( ! $ok ) {
                        // si suppression échoué, réaffichage vue avec message explicatif
                        $message = "Problème lors de la suppression de l'utilisateur !";
                        $typeMessage = 'avertissement';
                        $themeFooter = $themeProbleme;
                        include_once ('vues/VueSupprimerUtilisateur.php');
                    }
                    else {
                        // envoi mail confirmation de la suppression
                        $sujet = "Suppression de votre compte dans le système de réservation de M2L";
                        $contenuMail = "Bonjour " . $name . "\n\nL'administrateur du système de réservations de la M2L vient de supprimer votre compte utilisateur.\n";
                        
                        $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
                        if ( ! $ok ) {
                            // si envoi mail a échoué, réaffichage vue avec message explicatif
                            $message = "Suppression effectuée.<br>L'envoi du mail à l'utilisateur a rencontré un problème !";
                            $typeMessage = 'avertissement';
                            $themeFooter = $themeProbleme;
                            include_once ('vues/VueSupprimerUtilisateur.php');
                        }
                        else {
                            // tout marche
                            $message = "Suppression effectuée.<br>Un mail va être envoyé à l'utilisateur !";
                            $typeMessage = 'information';
                            $themeFooter = $themeNormal;
                            include_once ('vues/VueSupprimerUtilisateur.php');
                        }
                    }
                }
            }
            unset($dao);		// fermeture de la connexion
        }
    }
}
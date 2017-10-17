<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlDemanderMdp.php
// Rôle : traiter la demande de mot de passe d'un utilisateur
// écrit par Pierre le 10/10/2017
// modifié par Pierre le 17/10/2017

    if ( ! isset ($_POST ["txtNom"]) ) {
        // si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
        $nom = '';
        $message = '';
        $typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
        $themeFooter = $themeNormal;
        include_once ('vues/VueDemanderMdp.php');
    }
    else {
        // récupération des données postées
        if ( empty ($_POST ["txtNom"]) == true)  $nom = "";  else   $nom = $_POST ["txtNom"];
        
        if ( $nom == "" ) {
            // si les données sont incomplètes, réaffichage de la vue avec un message explicatif
            $message = 'Données incomplètes ou incorrectes !';
            $typeMessage = 'avertissement';
            $themeFooter = $themeProbleme;
            include_once ('vues/VueDemanderMdp.php');
        }
        else {
            
            // classe Outils
            include_once ('modele/Outils.class.php');
            // connexion du serveur web à la base MySQL
            include_once ('modele/DAO.class.php');
            $dao = new DAO();
            
            $existeUser = $dao->existeUtilisateur($nom);
            
            if ($existeUser == false) {
               // l'utilisateur n'existe pas, réaffichage de la vue avec un message explicatif

                $message = "Nom d'utilisateur inexistant !";
                $typeMessage = 'avertissement';
                $themeFooter = $themeProbleme;
                include_once ('vues/VueDemanderMdp.php');
                
            }
            else {
                // l'utilisateur existe, on peut poursuivre (création de mdp puis envoie de mail)
                    
                $nouveauMdp = Outils::creerMdp();
                
                // enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir codé en MD5
                $dao->modifierMdpUser ($nom, $nouveauMdp);
                
                // envoi d'un mail à l'utilisateur avec son nouveau mot de passe
                $ok = $dao->envoyerMdp ($nom, $nouveauMdp);
                
                
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
                include_once ('vues/VueDemanderMdp.php');
                
                
            }
            
        }
    }
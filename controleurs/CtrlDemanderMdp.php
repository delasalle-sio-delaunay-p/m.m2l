<?php
// Projet Réservations M2L - version web mobile
// fichier : controleurs/CtrlDemanderMdp.php
// Rôle : traiter la demande de mot de passe d'un utilisateur
// écrit par Pierre le 10/10/2017
// modifié par Pierre le 10/10/2017

// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauUtilisateur'] != 'utilisateur' && $_SESSION['niveauUtilisateur'] != 'administrateur') {
	// si le demandeur n'est pas authentifié, il s'agit d'une tentative d'accès frauduleux
	// dans ce cas, on provoque une redirection vers la page de connexion
	header ("Location: index.php?action=Deconnecter");
}
else {
	// connexion du serveur web à la base MySQL
	include_once ('modele/DAO.class.php');
	$dao = new DAO();
	

	
	
	// affichage de la vue
	include_once ('vues/VueDemanderMdp.php');
	
	unset($dao);		// fermeture de la connexion à MySQL
}
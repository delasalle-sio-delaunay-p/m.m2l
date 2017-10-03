<?php
// Projet Réservations M2L - version web mobile
// fichier : modele/Salle.class.php
// Rôle : la classe Salle représente l'ensemble des salles
// Création : 03/10/2017 par l'équipe
// Mise à jour : 03/10/2017 par Pierre

class Salle {

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres privés de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id;        // identifiant de la salle
    private $room_name; // nom de la salle
    private $capacity;  // capacité de la salle
    private $area_name; // nom de la zone
    
    
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function Salle($unId, $unRoomName, $uneCapacity, $unAreaName) {
        $this->id = $unId;
        $this->room_name = $unRoomName;
        $this->capacity = $uneCapacity;
        $this->area_name = $unAreaName;
    }
    
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId()	{return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getRoom_name()	{return $this->room_name;}
    public function setRoom_name($unRoomName) {$this->room_name = $unRoomName;}
    
    public function getCapacity()	{return $this->capacity;}
    public function setCapacity($uneCapacity) {$this->capacity = $uneCapacity;}
    
    public function getAreaName()	{return $this->area_name;}
    public function setAreaName($unAreaName) {$this->area_name = $unAreaName;}
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Méthodes d'instances ----------------------------------------
    // ------------------------------------------------------------------------------------------------------

    public function toString() {
        $msg = "Salle : <br>";
        $msg .= "id : " . $this->id . "<br>";
        $msg .= "room_name : " . $this->room_name . "<br>";
        $msg .= "capacity : " . $this->capacity . "<br>";
        $msg .= "area_name : " . $this->area_name . "<br>";
        return $msg;
    }

} // fin de la classe Salle







// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
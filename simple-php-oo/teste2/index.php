<?php
// 1. On inclut le fichier de la classe
require_once 'cv.php';

// 2. On prépare de fausses données (Mock Data) pour tester
$donneesSimulees = [
    'prenom'      => 'Alex',
    'nom'         => 'Martin',
    'titre'       => 'Développeur Web Fullstack',
    'contact'     => "Email: alex@example.com\nTél: 06 00 00 00 00\nParis, France",
    'experience'  => "- 2024 : Développeur chez TechCompany\n- 2022 : Stage chez WebAgency",
    'competences' => "PHP, OOP, HTML, CSS, JavaScript",
    'loisirs'     => "Code, Randonnée, Jeux vidéo"
];

// Optionnel : Une fausse image vide ou pixel blanc en Base64 pour tester la photo
$faussePhotoBase64 = "R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"; 

// 3. On instancie la classe avec nos données
$cv = new CvRenderer($donneesSimulees, $faussePhotoBase64);

// 4. On affiche le résultat directement sur la page
echo $cv->genererHtml();
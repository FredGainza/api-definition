<?php
function printPre($x)
{
    echo '<pre>';
    print_r($x);
    echo '</pre>';
}

function valid_donnees($donnees)
{
    $donnees = trim($donnees);
    $donnees = stripslashes($donnees);
    $donnees = htmlspecialchars($donnees);
    return $donnees;
}

$_SESSION['errors'] = '';
$_SESSION['motInput'] = '';
$errors='';
$motValid='';

// On vérifie les données
if (isset($_POST['motInput']) && !empty($_POST['motInput'])) {
    // Vérification des données passées en POST
    $motInput = $_POST['motInput'];
    $validInput = valid_donnees($motInput);

    // On vérifie le pattern
    $pattern = '/^[a-zA-ZàâäçéèêëîïôöùûüÀÂÄÇÉÈÊËÎÏÔÖÙÛÜ-]{1,26}$/';
    $subject = $validInput;

    // Si c'est ok
    if (preg_match($pattern, $subject)) {
        // On passe l'input en variable de session
        $motValid = $validInput;

    // Sinon Notif si input trop long (+ de 26 caractères)
    } else if (strlen($motInput) > 26) {
        $errors="<i class=\"fas fa-exclamation-triangle fz110p fa-sm text-danger mr-3\"></i>Vous devez saisir un mot de 26 caractères au maximum.";
    
    // Sinon Notif si des caractères autres que des lettres ou un tiret sont saisis
    } else {
        $errors = "<i class=\"fas fa-exclamation-triangle fz110p fa-sm text-danger mr-3\"></i>Vous ne pouvez saisir que des lettres, accentuées ou non, ainsi que le tiret";
    }

// Sinon, notif si input empty
} else {
    $errors = "<i class=\"fas fa-exclamation-triangle fz110p fa-sm text-danger mr-3\"></i>Vous devez sasir un mot à définir.";
}
$dataInput = array();
$dataInput["motValid"]=$motValid;
$dataInput["error"]=$errors;
echo json_encode($dataInput);
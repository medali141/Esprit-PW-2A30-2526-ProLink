    <?php
include '../../Controller/UserP.php';

$userP = new UserP();

if (isset($_GET['id'])) {
    $user = $userP->showUser($_GET['id']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Détail utilisateur</title>
    <style>
        .card {
            width: 300px;
            margin: auto;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 10px;
            text-align: center;
        }
        p { font-size: 16px; }
    </style>
</head>

<body>

<div class="card">
    <h2>Détail utilisateur</h2>
    <p><b>ID:</b> <?= $user['iduser'] ?></p>
    <p><b>Nom:</b> <?= $user['nom'] ?></p>
    <p><b>Prénom:</b> <?= $user['prenom'] ?></p>
    <p><b>Email:</b> <?= $user['email'] ?></p>
    <p><b>Type:</b> <?= $user['type'] ?></p>
    <p><b>Age:</b> <?= $user['age'] ?></p>
</div>

</body>
</html>
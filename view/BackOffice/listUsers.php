<?php
include '../../Controller/UserP.php';

$userP = new UserP();
$list = $userP->listUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            width: 90%;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background: #ddd;
        }

        .btn {
            padding: 6px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin: 2px;
            display: inline-block;
            font-size: 13px;
        }

        .delete { background: red; }
        .edit { background: orange; }
        .view { background: #17a2b8; }
        .add { background: green; margin-bottom: 15px; }

    </style>
</head>

<body>

<div class="container">
    <h1>Liste des utilisateurs</h1>

    <!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>
    <!-- CREATE -->
    <a href="addUser.php" class="btn add">+ Ajouter utilisateur</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Type</th>
            <th>Age</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($list as $user) { ?>
        <tr>
            <td><?= $user['iduser']; ?></td>
            <td><?= $user['nom']; ?></td>
            <td><?= $user['prenom']; ?></td>
            <td><?= $user['email']; ?></td>
            <td><?= $user['type']; ?></td>
            <td><?= $user['age']; ?></td>

            <td>
                <!-- READ (DETAIL) -->
                <a class="btn view" href="detailUser.php?id=<?= $user['iduser']; ?>">
                    Voir
                </a>

                <!-- UPDATE -->
                <a class="btn edit" href="updateUser.php?id=<?= $user['iduser']; ?>">
                    Modifier
                </a>

                <!-- DELETE -->
                <a class="btn delete"
                   href="deleteUser.php?id=<?= $user['iduser']; ?>"
                   onclick="return confirm('Supprimer cet utilisateur ?');">
                    Supprimer
                </a>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>

</body>
</html>
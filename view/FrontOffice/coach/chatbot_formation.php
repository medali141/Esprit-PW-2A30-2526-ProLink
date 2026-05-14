<?php
header('Content-Type: application/json');

$message = $_POST['message'] ?? '';
$formations = getFormations();

function getFormations() {
    try {
        $pdo = new PDO('mysql:host=localhost;port=3308;dbname=prolink;charset=utf8', 'root', '');
        $stmt = $pdo->query("SELECT titre, type, date_debut, places_max FROM formation WHERE statut = 'inscrit' LIMIT 5");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return [];
    }
}

function genererReponse($message, $formations) {
    $msg = strtolower(trim($message));
    
    if(strpos($msg, 'formation') !== false || strpos($msg, 'disponible') !== false) {
        if(count($formations) > 0) {
            $reponse = "📚 **Voici nos formations disponibles :**\n\n";
            foreach($formations as $f) {
                $reponse .= "• **" . $f['titre'] . "** (" . ($f['type'] == 'presentiel' ? '🏢 Présentiel' : '💻 En ligne') . ")\n";
                $reponse .= "  📅 Début : " . date('d/m/Y', strtotime($f['date_debut'])) . "\n";
                $reponse .= "  👥 Places : " . $f['places_max'] . "\n\n";
            }
            $reponse .= "👉 Cliquez sur 'Voir détails' pour vous inscrire !";
        } else {
            $reponse = "📚 Aucune formation n'est actuellement programmée. Revenez bientôt !";
        }
        return $reponse;
    }
    
    if(strpos($msg, 'inscrire') !== false || strpos($msg, 'inscription') !== false) {
        return "📝 **Comment s'inscrire ?**\n\n1️⃣ Parcourez la liste des formations\n2️⃣ Cliquez sur 'Voir détails / S'inscrire'\n3️⃣ Remplissez le formulaire d'inscription\n4️⃣ Validez votre inscription\n\n✅ Un email de confirmation vous sera envoyé !";
    }
    
    if(strpos($msg, 'prix') !== false || strpos($msg, 'tarif') !== false) {
        return "💰 **Tarifs des formations :**\n\n• 💻 Formations en ligne : à partir de 199 TND\n• 🏢 Formations présentiel : à partir de 399 TND\n• 🎓 Certifications : 499 TND\n\n🎁 Réductions pour les groupes (3+ personnes) !";
    }
    
    if(strpos($msg, 'certif') !== false) {
        return "🎓 **Obtenir une certification :**\n\n1️⃣ Suivez la formation complète\n2️⃣ Préparez-vous avec nos ressources\n3️⃣ Passez le quiz de certification\n4️⃣ Obtenez au moins 70%\n5️⃣ Téléchargez votre certificat PDF !";
    }
    
    if(strpos($msg, 'motiv') !== false) {
        return "💪 **Vous êtes capable de grandes choses !**\n\n🌟 Chaque formation est un pas vers votre réussite\n📚 Investir dans vos compétences, c'est investir dans votre avenir\n🚀 Allez-y, vous allez y arriver !";
    }
    
    if(strpos($msg, 'bonjour') !== false || strpos($msg, 'salut') !== false) {
        return "Bonjour ! 👋 Comment puis-je vous aider avec nos formations ?";
    }
    
    return "🤔 Je n'ai pas bien compris.\n\n**Essayez de demander :**\n• Quelles formations sont disponibles ?\n• Comment s'inscrire ?\n• Quels sont les prix ?\n• Obtenir une certification ?\n• Besoin de motivation ?";
}

$reponse = genererReponse($message, $formations);
echo json_encode(['reponse' => $reponse]);
?>
<?php
declare(strict_types=1);

/**
 * Quiz statique servi pour les formations en front-office.
 *
 * Les questions ne sont pas en base : chaque catégorie de formation a son
 * propre jeu de 5 questions QCM (4 choix, 1 bonne réponse). C'est suffisant
 * pour gating la délivrance d'un certificat dans un projet pédagogique.
 *
 * Format d'une question :
 *     [
 *         'q'       => 'Question lisible ?',
 *         'options' => ['Choix A', 'Choix B', 'Choix C', 'Choix D'],
 *         'correct' => 2,           // index 0-based de la bonne réponse
 *     ]
 */
class FormationQuiz
{
    public const PASS_THRESHOLD = 4;
    public const TOTAL_QUESTIONS = 5;

    /** @var array<string, list<array{q:string,options:list<string>,correct:int}>> */
    private static array $banks = [
        'Développement Web' => [
            ['q' => 'Quel langage s\'exécute principalement côté serveur ?', 'options' => ['HTML', 'CSS', 'PHP', 'jQuery'], 'correct' => 2],
            ['q' => 'Que signifie HTML ?', 'options' => ['Hyper Tool Markup Language', 'HyperText Markup Language', 'Home Tool Multi Language', 'Hyperlinks Text Markup'], 'correct' => 1],
            ['q' => 'Sélecteur CSS pour l\'élément avec id="main" ?', 'options' => ['.main', '#main', '*main', 'main:'], 'correct' => 1],
            ['q' => 'Quelle méthode HTTP sert à récupérer une ressource ?', 'options' => ['POST', 'PUT', 'GET', 'DELETE'], 'correct' => 2],
            ['q' => 'Lequel est un framework PHP ?', 'options' => ['jQuery', 'Laravel', 'Bootstrap', 'React'], 'correct' => 1],
        ],
        'Développement Mobile' => [
            ['q' => 'Langage moderne recommandé pour Android natif :', 'options' => ['Swift', 'Kotlin', 'Dart', 'PHP'], 'correct' => 1],
            ['q' => 'Framework mobile cross-platform de Google :', 'options' => ['React Native', 'Flutter', 'Xamarin', 'Ionic'], 'correct' => 1],
            ['q' => 'Apple utilise quel langage pour iOS natif ?', 'options' => ['Java', 'Kotlin', 'Swift', 'C#'], 'correct' => 2],
            ['q' => 'Le guide de design Apple s\'appelle ?', 'options' => ['Material Design', 'Human Interface Guidelines', 'Bootstrap', 'Tailwind'], 'correct' => 1],
            ['q' => 'Le store officiel Android est :', 'options' => ['App Store', 'Google Play', 'Microsoft Store', 'Steam'], 'correct' => 1],
        ],
        'Data Science / IA' => [
            ['q' => 'Bibliothèque Python pour le calcul scientifique :', 'options' => ['Pillow', 'NumPy', 'Flask', 'PyGame'], 'correct' => 1],
            ['q' => 'Type d\'apprentissage avec données étiquetées :', 'options' => ['Non supervisé', 'Supervisé', 'Par renforcement', 'Hors ligne'], 'correct' => 1],
            ['q' => 'Prédire une valeur continue s\'appelle :', 'options' => ['Classification', 'Régression', 'Clustering', 'Hashing'], 'correct' => 1],
            ['q' => 'Algorithme de regroupement non supervisé :', 'options' => ['SVM', 'Régression linéaire', 'K-Means', 'Naïve Bayes'], 'correct' => 2],
            ['q' => 'Bibliothèque de deep learning popularisée par Google :', 'options' => ['PyTorch', 'TensorFlow', 'XGBoost', 'pandas'], 'correct' => 1],
        ],
        'Design / UX' => [
            ['q' => 'UX signifie :', 'options' => ['User XML', 'User Experience', 'Unified Experience', 'User XPath'], 'correct' => 1],
            ['q' => 'Outil de prototypage très populaire :', 'options' => ['VSCode', 'Figma', 'Photoshop', 'Word'], 'correct' => 1],
            ['q' => 'La loi de Fitts concerne :', 'options' => ['La couleur', 'La typographie', 'La taille et distance des cibles', 'L\'audio'], 'correct' => 2],
            ['q' => 'Élément essentiel pour l\'accessibilité visuelle :', 'options' => ['Couleurs flashy', 'Contraste suffisant', 'Animations rapides', 'Pop-ups multiples'], 'correct' => 1],
            ['q' => 'Un wireframe est :', 'options' => ['Le code source final', 'Une maquette basse fidélité', 'Un test utilisateur', 'Une couleur'], 'correct' => 1],
        ],
        'Marketing Digital' => [
            ['q' => 'Le SEO concerne :', 'options' => ['Les paiements', 'Le référencement naturel', 'La sécurité', 'Les bases de données'], 'correct' => 1],
            ['q' => 'CPC signifie :', 'options' => ['Coût Par Client', 'Coût Par Clic', 'Code Parental Commun', 'Centre Point Cible'], 'correct' => 1],
            ['q' => 'Plateforme publicitaire de Meta :', 'options' => ['LinkedIn Ads', 'Facebook Ads', 'TikTok Ads', 'Google Ads'], 'correct' => 1],
            ['q' => 'Un funnel marketing décrit :', 'options' => ['La couleur d\'un logo', 'Le parcours d\'achat', 'Un slogan', 'Un tarif'], 'correct' => 1],
            ['q' => 'KPI signifie :', 'options' => ['Kit Pour Internet', 'Key Performance Indicator', 'Knowledge Of Page', 'Key Public Idea'], 'correct' => 1],
        ],
        'Business / Entrepreneuriat' => [
            ['q' => 'MVP signifie :', 'options' => ['Most Valuable Product', 'Minimum Viable Product', 'Maximum Value Position', 'Main Vision Plan'], 'correct' => 1],
            ['q' => 'Auteur popularisant le Business Model Canvas :', 'options' => ['Steve Jobs', 'Alexander Osterwalder', 'Elon Musk', 'Jeff Bezos'], 'correct' => 1],
            ['q' => '« Pivoter » une startup signifie :', 'options' => ['L\'arrêter', 'Changer de direction stratégique', 'L\'introduire en bourse', 'La vendre'], 'correct' => 1],
            ['q' => 'Indicateur classique de fidélité client :', 'options' => ['SEO', 'NPS', 'DNS', 'CDN'], 'correct' => 1],
            ['q' => 'Le bootstrapping correspond à :', 'options' => ['Lever beaucoup d\'argent', 'S\'auto-financer', 'Racheter une concurrente', 'Une IPO'], 'correct' => 1],
        ],
        'Langues' => [
            ['q' => '"Hello" en français se dit :', 'options' => ['Au revoir', 'Bonjour', 'Merci', 'Pardon'], 'correct' => 1],
            ['q' => 'Capitale de l\'Espagne :', 'options' => ['Barcelone', 'Madrid', 'Séville', 'Valence'], 'correct' => 1],
            ['q' => 'Langue officielle du Brésil :', 'options' => ['Espagnol', 'Portugais', 'Italien', 'Français'], 'correct' => 1],
            ['q' => '"Thank you" en allemand :', 'options' => ['Bitte', 'Danke', 'Hallo', 'Tschüss'], 'correct' => 1],
            ['q' => 'Le mot "kanji" est associé au :', 'options' => ['Coréen', 'Japonais', 'Vietnamien', 'Thaï'], 'correct' => 1],
        ],
        'Soft Skills' => [
            ['q' => 'L\'écoute active consiste à :', 'options' => ['Couper la parole', 'Reformuler et clarifier', 'Imposer son avis', 'Ignorer l\'autre'], 'correct' => 1],
            ['q' => 'Un feedback constructif est :', 'options' => ['Très personnel', 'Spécifique et orienté solution', 'Vague', 'Sarcastique'], 'correct' => 1],
            ['q' => 'La méthode STAR aide à :', 'options' => ['Programmer', 'Designer', 'Structurer une réponse en entretien', 'Cuisiner'], 'correct' => 2],
            ['q' => 'Le leadership repose surtout sur :', 'options' => ['L\'autorité brute', 'L\'écoute et l\'inspiration', 'La peur', 'Le secret'], 'correct' => 1],
            ['q' => 'Bien gérer son temps c\'est :', 'options' => ['Tout faire à la fois', 'Prioriser les tâches', 'Éviter de planifier', 'Repousser sans cesse'], 'correct' => 1],
        ],
        'default' => [
            ['q' => 'ProLink est :', 'options' => ['Un jeu vidéo', 'Une plateforme pro & formation', 'Un OS', 'Un réseau social pour chats'], 'correct' => 1],
            ['q' => 'Le but principal d\'une formation :', 'options' => ['Vendre des produits', 'Apprendre de nouvelles compétences', 'Faire du sport', 'Voyager'], 'correct' => 1],
            ['q' => 'Un certificat sert à :', 'options' => ['Décorer un bureau', 'Attester d\'une compétence', 'Manger', 'Conduire'], 'correct' => 1],
            ['q' => 'La meilleure façon d\'apprendre :', 'options' => ['Sans pratique', 'Avec une pratique régulière', 'En dormant', 'En oubliant'], 'correct' => 1],
            ['q' => 'Sur ProLink, le forum sert à :', 'options' => ['Acheter', 'Discuter et s\'entraider', 'Faire du sport', 'Voter'], 'correct' => 1],
        ],
    ];

    /**
     * Retourne le quiz associé à une catégorie. Si la catégorie est inconnue
     * ou vide, on renvoie le quiz par défaut.
     *
     * @return list<array{q:string,options:list<string>,correct:int}>
     */
    public static function getQuiz(?string $categorie): array
    {
        $cat = trim((string) ($categorie ?? ''));
        if ($cat !== '' && isset(self::$banks[$cat])) {
            return self::$banks[$cat];
        }
        return self::$banks['default'];
    }

    /**
     * Calcule le nombre de bonnes réponses.
     *
     * @param list<array{q:string,options:list<string>,correct:int}> $quiz
     * @param array<int|string, mixed> $answers réponses utilisateur indexées par n° de question (0-based)
     */
    public static function score(array $quiz, array $answers): int
    {
        $ok = 0;
        foreach ($quiz as $i => $q) {
            $ans = $answers[(string) $i] ?? $answers[$i] ?? null;
            if ($ans === null) continue;
            if ((int) $ans === (int) $q['correct']) {
                $ok++;
            }
        }
        return $ok;
    }

    public static function isPassing(int $score): bool
    {
        return $score >= self::PASS_THRESHOLD;
    }
}

\<?php
class SkillExtractor {
    // Liste élargie pour une détection locale plus riche
    private static array $fallbackSkills = [
        "php", "laravel", "symfony", "javascript", "typescript", "react", "vue", "angular", 
        "node", "express", "mysql", "postgresql", "mongodb", "html", "css", "tailwind", 
        "bootstrap", "sass", "python", "django", "flask", "java", "spring", "git", "github", 
        "docker", "kubernetes", "aws", "azure", "gcp", "linux", "nginx", "apache", "rest", 
        "api", "graphql", "json", "xml", "ajax", "jquery", "npm", "yarn", "composer", 
        "webpack", "vite", "wordpress", "drupal", "joomla", "figma", "photoshop", 
        "illustrator", "xd", "sketch", "ci/cd", "jenkins", "github actions", "gitlab ci", 
        "terraform", "ansible", "redis", "rabbitmq", "kafka", "elasticsearch", "android", 
        "ios", "react native", "flutter", "kotlin", "swift", "ionic", "cordova",
        "pandas", "numpy", "scikit-learn", "tensorflow", "pytorch", "machine learning", 
        "deep learning", "data analysis", "data science", "power bi", "tableau", "excel",
        "communication", "teamwork", "problem solving", "critical thinking", "leadership", 
        "project management", "agile", "scrum", "kanban", "time management", "adaptability",
        "creativity", "autonomy", "collaboration", "empathy", "negotiation",
        "english", "french", "spanish", "german", "chinese", "arabic"
    ];

    public static function extract(string $text): array {
        $apiKey = self::getOpenAIKey();
        if (empty($apiKey)) return self::extractLocalFallback($text);
        return self::extractViaGroq($text, $apiKey);
    }

    private static function extractViaGroq(string $text, string $apiKey): array {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $prompt = "Tu es un parseur RH. Analyse ce CV et extrait les mots-clés de compétences (hard & soft skills).\n"
                . "Réponds STRICTEMENT sous forme d'un tableau JSON de chaînes en minuscules, sans markdown.\n"
                . "Exemple : [\"php\", \"react\", \"git\"]\n\n"
                . "CV :\n" . substr($text, 0, 6000);

        $data = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.1,
            'max_tokens' => 300
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return self::extractLocalFallback($text);

        $resData = json_decode($response, true);
        if (isset($resData['error'])) return self::extractLocalFallback($text);

        $content = trim(str_replace(['```json', '```'], '', $resData['choices'][0]['message']['content'] ?? ''));
        $skills = json_decode($content, true);
        if (is_array($skills)) {
            return array_values(array_unique(array_map('strtolower', array_map('trim', $skills))));
        }
        return self::extractLocalFallback($text);
    }

    private static function extractLocalFallback(string $text): array {
        $text = strtolower($text);
        $found = [];
        foreach (self::$fallbackSkills as $skill) {
            if (stripos($text, $skill) !== false) $found[] = $skill;
        }
        return $found;
    }

    private static function getOpenAIKey(): ?string {
        if (!empty($_ENV['OPENAI_API_KEY'])) return $_ENV['OPENAI_API_KEY'];
        if (!empty($_SERVER['OPENAI_API_KEY'])) return $_SERVER['OPENAI_API_KEY'];
        $val = getenv('OPENAI_API_KEY');
        if (!empty($val)) return $val;

        $paths = [__DIR__ . '/../.env', __DIR__ . '/../../.env', getcwd() . '/.env', '/opt/lampp/htdocs/Projet_Stage/.env'];
        foreach ($paths as $path) {
            if (!file_exists($path)) continue;
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, 'OPENAI_API_KEY=') === 0) return trim(substr($line, 15));
            }
        }
        return null;
    }
}
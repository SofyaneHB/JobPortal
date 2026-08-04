<?php
class JobMatcher {
    private PDO $pdo;
    
    // Liste de compétences élargie pour le fallback local
    private static array $skillsList = [
        "php","laravel","symfony","javascript","typescript","react","vue","angular","node",
        "express","mysql","postgresql","mongodb","html","css","tailwind","bootstrap","sass",
        "python","django","flask","java","spring","git","github","docker","kubernetes","aws",
        "azure","gcp","linux","nginx","apache","rest","api","graphql","json","xml","ajax","jquery",
        "npm","composer","webpack","vite","wordpress","drupal","joomla","figma","photoshop",
        "illustrator","xd","ci/cd","jenkins","github actions","gitlab ci","terraform","ansible",
        "redis","rabbitmq","kafka","elasticsearch","android","ios","react native","flutter",
        "kotlin","swift","ionic","cordova","pandas","numpy","machine learning","data analysis",
        "power bi","tableau","excel","communication","teamwork","problem solving","leadership",
        "project management","agile","scrum","kanban","time management","adaptability","creativity",
        "autonomy","collaboration","english","french","spanish","german"
    ];

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

        public function findMatches(int $userId, $skills, int $limit = 10): array {
        if (is_string($skills)) $skills = array_filter(array_map('trim', explode(",", $skills)));
        $skills = is_array($skills) ? $skills : [];
        $skillsLower = array_map('strtolower', $skills);
        
        $stmt = $this->pdo->prepare("
            SELECT j.id, j.title, j.description, j.skills, j.location, j.type, j.salary, 
                   COALESCE(c.company_name, 'Entreprise') as company_name
            FROM jobs j
            LEFT JOIN companies c ON j.company_id = c.id
            WHERE j.status = 'active'
        ");
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];
        foreach ($jobs as $job) {
            $jobSkills = array_filter(array_map('trim', explode(",", strtolower($job["skills"] ?? ""))));
            $intersect = array_intersect($skillsLower, $jobSkills);
            $score = !empty($jobSkills) ? round((count($intersect) / count($jobSkills)) * 100) : 0;
            $results[] = ["job" => $job, "score" => (int)$score, "matched_skills" => count($intersect)];
        }
        usort($results, fn($a, $b) => $b["score"] <=> $a["score"]);
        return array_slice($results, 0, $limit);
    }

    public function matchSpecificJob(array $jobData, array $candidateSkills): array {
        $candidateSkillsLower = array_map('strtolower', $candidateSkills);
        $targetRequirements = [];
        if (!empty($jobData['requirements']) && is_array($jobData['requirements'])) {
            $targetRequirements = $jobData['requirements'];
        } elseif (!empty($jobData['skills'])) {
            $targetRequirements = explode(',', $jobData['skills']);
        } else {
            $targetRequirements = [$jobData['title'] ?? ''];
        }
        $matched = []; $totalCriteria = 0;
        foreach ($targetRequirements as $req) {
            $reqClean = trim(strtolower($req));
            if (empty($reqClean)) continue;
            $totalCriteria++;
            foreach ($candidateSkillsLower as $skill) {
                if (stripos($reqClean, $skill) !== false || stripos($skill, $reqClean) !== false) {
                    $matched[] = $skill; break;
                }
            }
        }
        $matched = array_unique($matched);
        $score = $totalCriteria > 0 ? round((count($matched) / $totalCriteria) * 100) : 50;
        $score = max(10, min(100, $score));
        if ($score >= 75) $feedback = "Excellent profile";
        elseif ($score >= 40) $feedback = "Interesting profile";
        else $feedback = "Additional skills would be an asset";
        return ["score" => (int)$score, "feedback" => $feedback];
    }

    public function matchSpecificJobWithAI(string $cvText, array $jobData): array {
        $apiKey = $this->getAPIKey();
        if (!$apiKey) return $this->fallbackLocal($cvText, $jobData, "No API key");

        $cvText = substr($cvText, 0, 8000);
        $targetRequirements = [];
        if (!empty($jobData['requirements']) && is_array($jobData['requirements'])) {
            $targetRequirements = $jobData['requirements'];
        } elseif (!empty($jobData['skills'])) {
            $targetRequirements = explode(',', $jobData['skills']);
        } else {
            $targetRequirements = [$jobData['title'] ?? 'Poste'];
        }
        $requirementsStr = implode(", ", $targetRequirements);
        $jobTitle = $jobData['title'] ?? 'Poste';

        $url = "https://api.groq.com/openai/v1/chat/completions";
        $model = "llama-3.3-70b-versatile";

        $prompt = "You are an ATS system. Evaluate this CV for the position of {$jobTitle}.\n\n"
                . "REQUIREMENTS: {$requirementsStr}\n\n"
                . "CV :\n{$cvText}\n\n"
                . "Reply ONLY in this exact format:\n"
                . "SCORE: [number between 0 and 100]\n"
                . "FEEDBACK: [short English text]";

        $payload = [
            "model" => $model,
            "temperature" => 0.0,
            "messages" => [["role" => "user", "content" => $prompt]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return $this->fallbackLocal($cvText, $jobData, "API Error HTTP $httpCode");
        }

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            return $this->fallbackLocal($cvText, $jobData, "API Error");
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        
        $score = 0;
        $feedback = "No Available Analyse";
        
        if (preg_match('/SCORE:\s*(\d+)/i', $content, $m)) {
            $score = (int)$m[1];
        }
        if (preg_match('/FEEDBACK:\s*(.+)/i', $content, $m)) {
            $feedback = trim($m[1]);
        }

        if ($score === 0) {
            return $this->fallbackLocal($cvText, $jobData, "Invalid Response ");
        }

        return ["score" => $score, "feedback" => $feedback];
    }

    private function fallbackLocal(string $cvText, array $jobData, string $reason): array {
        $skills = $this->extractSkillsLocal($cvText);
        $local = $this->matchSpecificJob($jobData, $skills);
        return ["score" => $local['score'], "feedback" => "[$reason] " . $local['feedback']];
    }

    private function extractSkillsLocal(string $text): array {
        $text = strtolower($text); $found = [];
        foreach (self::$skillsList as $skill) {
            if (stripos($text, $skill) !== false) $found[] = $skill;
        }
        return $found;
    }

    private function getAPIKey(): ?string {
        $paths = [__DIR__ . '/../.env', __DIR__ . '/../../.env', getcwd() . '/.env', '/opt/lampp/htdocs/Projet_Stage/.env'];
        foreach ($paths as $path) {
            if (!file_exists($path)) continue;
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, 'GROQ_API_KEY=') === 0) return trim(substr($line, 13));
                if (strpos($line, 'OPENAI_API_KEY=') === 0) return trim(substr($line, 15));
            }
        }
        return null;
    }
}
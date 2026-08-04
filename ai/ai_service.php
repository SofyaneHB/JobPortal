\<?php

require_once __DIR__ . "/cv_parser.php";
require_once __DIR__ . "/skill_extractor.php";
require_once __DIR__ . "/job_matcher.php";
require_once __DIR__ . "/recommendation_engine.php"; // NOUVEAU

class AIService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Analyse globale : Extrait les compétences, cherche les meilleures offres ET génère des recommandations
    public function analyzeCV(int $userId, string $filePath, string $mimeType): array {
        $text = CVParser::extractText($filePath, $mimeType);

        if (empty(trim($text))) {
            return ["success" => false, "error" => "Unable to extract text from the CV."];
        }

        $skills = SkillExtractor::extract($text);
        $matcher = new JobMatcher($this->pdo);
        $matches = $matcher->findMatches($userId, $skills);

        // NOUVEAU : Moteur de recommandations
        $recommendationEngine = new RecommendationEngine($this->pdo);
        $recommendations = $recommendationEngine->generateRecommendations($skills, $matches, $text);

        return [
            "success" => true,
            "skills" => $skills,
            "matches" => $matches,
            "recommendations" => $recommendations // NOUVEAU
        ];
    }

    // Analyse ciblée : Calcule l'affinité du CV par rapport à l'offre + recommandations spécifiques
    public function analyzeCVForJob(string $filePath, string $mimeType, array $jobData): array {
        $text = CVParser::extractText($filePath, $mimeType);

        if (empty(trim($text))) {
            return ["success" => false, "error" => "The document is empty or unreadable."];
        }

        $skills = SkillExtractor::extract($text);
        $matcher = new JobMatcher($this->pdo);
        $matchDetails = $matcher->matchSpecificJobWithAI($text, $jobData);

        // NOUVEAU : Recommandations ciblées pour ce poste
        $recommendationEngine = new RecommendationEngine($this->pdo);
        $jobRecommendations = $recommendationEngine->generateJobSpecificRecommendations(
            $skills,
            $jobData,
            $matchDetails['score'] ?? 0
        );

        return array_merge(
            ["success" => true, "extracted_skills" => $skills],
            $matchDetails,
            ["recommendations" => $jobRecommendations] // NOUVEAU
        );
    }
}
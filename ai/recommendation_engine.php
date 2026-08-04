<?php

class RecommendationEngine {
    private PDO $pdo;

    private array $skillsByCategory = [
        'frontend' => ['html', 'css', 'javascript', 'typescript', 'react', 'vue', 'angular', 'svelte', 'jquery', 'bootstrap', 'tailwind', 'sass', 'less', 'webpack', 'vite', 'npm', 'responsive design', 'accessibility', 'seo'],
        'backend' => ['php', 'laravel', 'symfony', 'node', 'express', 'python', 'django', 'flask', 'java', 'spring', 'ruby', 'rails', 'go', 'rust', 'mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch', 'rest', 'graphql', 'soap', 'api', 'json', 'xml', 'oauth', 'jwt'],
        'devops' => ['docker', 'kubernetes', 'aws', 'azure', 'gcp', 'linux', 'nginx', 'apache', 'ci/cd', 'jenkins', 'github actions', 'gitlab ci', 'terraform', 'ansible', 'redis', 'rabbitmq', 'kafka', 'elasticsearch', 'prometheus', 'grafana'],
        'mobile' => ['react native', 'flutter', 'swift', 'kotlin', 'android', 'ios', 'ionic', 'cordova', 'xamarin'],
        'data' => ['sql', 'pandas', 'numpy', 'scikit-learn', 'tensorflow', 'pytorch', 'machine learning', 'deep learning', 'data analysis', 'data science', 'power bi', 'tableau', 'excel', 'spark', 'hadoop', 'kafka'],
        'soft' => ['communication', 'teamwork', 'problem solving', 'critical thinking', 'leadership', 'project management', 'agile', 'scrum', 'kanban', 'time management', 'adaptability', 'creativity', 'autonomy', 'collaboration', 'empathy', 'negotiation'],
        'languages' => ['english', 'french', 'spanish', 'german', 'chinese', 'arabic', 'italian', 'portuguese', 'russian', 'japanese'],
        'tools' => ['git', 'github', 'gitlab', 'bitbucket', 'jira', 'trello', 'notion', 'slack', 'teams', 'figma', 'photoshop', 'illustrator', 'xd', 'sketch', 'wordpress', 'drupal', 'joomla']
    ];

    private array $learningDifficulty = [
        'html' => 1, 'css' => 2, 'git' => 2, 'communication' => 2, 'teamwork' => 2,
        'json' => 1, 'rest' => 2, 'wordpress' => 2, 'bootstrap' => 2, 'jquery' => 2,
        'javascript' => 3, 'php' => 3, 'python' => 3, 'mysql' => 3, 'ajax' => 3,
        'react' => 4, 'vue' => 3, 'node' => 4, 'laravel' => 4, 'symfony' => 4,
        'docker' => 3, 'linux' => 3, 'aws' => 4, 'typescript' => 3, 'tailwind' => 2,
        'graphql' => 4, 'kubernetes' => 5, 'terraform' => 4, 'machine learning' => 5,
        'agile' => 2, 'scrum' => 2, 'problem solving' => 3, 'leadership' => 4
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function generateRecommendations(array $skills, array $jobMatches, string $cvText = ''): array {
        $skillsLower = array_map('strtolower', array_map('trim', $skills));
        $topMatches = array_slice($jobMatches, 0, 5);

        return [
            'profile_analysis' => $this->analyzeProfile($skillsLower),
            'skill_gaps' => $this->analyzeSkillGaps($skillsLower, $topMatches),
            'cv_improvements' => $this->suggestCVImprovements($cvText, $skillsLower),
            'learning_path' => $this->buildLearningPath($skillsLower, $topMatches),
            'market_insights' => $this->getMarketInsights($skillsLower),
            'enriched_matches' => $this->enrichMatches($topMatches, $skillsLower),
            'quick_wins' => $this->identifyQuickWins($skillsLower, $topMatches),
            'career_directions' => $this->suggestCareerDirections($skillsLower)
        ];
    }

    public function generateJobSpecificRecommendations(array $skills, array $jobData, int $currentScore): array {
        $skillsLower = array_map('strtolower', array_map('trim', $skills));
        $jobSkills = $this->extractJobSkills($jobData);

        $matched = array_values(array_intersect($skillsLower, $jobSkills));
        $missing = array_values(array_diff($jobSkills, $skillsLower));

        $recommendations = [];
        $priorityActions = [];

        if ($currentScore >= 85) {
            $recommendations[] = [
                'type' => 'success',
                'message' => "Excellent match! Your profile fits this position very well"
            ];
            if (!empty($matched)) {
                $recommendations[] = [
                    'type' => 'tip',
                    'message' => "Highlight your skills in " . $this->formatList(array_slice($matched, 0, 3)) . "in your cover letter "
                ];
                $priorityActions[] = "Apply quickly, you are an ideal candidate";
                $priorityActions[] = "Prepare concrete project examples using " . $this->formatList(array_slice($matched, 0, 3));
            }
        } elseif ($currentScore >= 60) {
            $recommendations[] = [
                'type' => 'info',
                'message' => "Good match. You have the key skills for this position"
            ];
            if (!empty($missing)) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => "To strengthen your application, mention any experience with " . $this->formatList(array_slice($missing, 0, 3)) . "."
                ];
            }
            if (!empty($matched)) {
                $priorityActions[] = "Emphasize your expertise in " . $this->formatList($matched);
            }
            if (!empty($missing)) {
                $priorityActions[] = "Be prepared to explain how you could fill the gaps in " . $this->formatList(array_slice($missing, 0, 2));
            }
        } elseif ($currentScore >= 40) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => "Partial match. You have some required skills but gaps exist "
            ];
            $recommendations[] = [
                'type' => 'action',
                'message' => "Priority skills to develop : " . $this->formatList(array_slice($missing, 0, 5)) . "."
            ];
            if (!empty($missing)) {
                $priorityActions[] = "Take a quick course on " . $this->formatList(array_slice($missing, 0, 2));
            }
            $priorityActions[] = "Highlight your transferable skills";
        } else {
            $recommendations[] = [
                'type' => 'danger',
                'message' => "Weak match. This position requires skills you haven't mastered yet "
            ];
            $recommendations[] = [
                'type' => 'action',
                'message' => "To target this type of position, focus on: " . $this->formatList(array_slice($missing, 0, 5)) . "."
            ];
            $priorityActions[] = "Consider a junior position or apprenticeship in this field ";
            if (!empty($missing)) {
                $priorityActions[] = "Create a personal project using " . $this->formatList(array_slice($missing, 0, 3));
            }
        }

        return [
            'match_level' => $this->getMatchLevel($currentScore),
            'match_score' => $currentScore,
            'matched_skills' => $matched,
            'missing_skills' => $missing,
            'missing_count' => count($missing),
            'matched_count' => count($matched),
            'general_advice' => $recommendations,
            'specific_tips' => $this->generateRoleSpecificTips($jobData, $matched, $missing),
            'priority_actions' => $priorityActions,
            'estimated_learning_time' => $this->estimateLearningTime($missing),
            'competitiveness' => $this->assessCompetitiveness($currentScore, count($matched), count($missing))
        ];
    }

    private function analyzeProfile(array $skills): array {
        $categories = [];
        $totalSkills = count($skills);

        foreach ($this->skillsByCategory as $cat => $catSkills) {
            $found = array_intersect($skills, $catSkills);
            $categories[$cat] = [
                'count' => count($found),
                'skills' => array_values($found),
                'percentage' => $totalSkills > 0 ? round(count($found) / $totalSkills * 100) : 0
            ];
        }

        $dominant = 'frontend';
        $maxCount = 0;
        foreach ($categories as $cat => $data) {
            if ($data['count'] > $maxCount) {
                $maxCount = $data['count'];
                $dominant = $cat;
            }
        }

        $strength = min(100, max(20, $totalSkills * 8));
        if ($totalSkills > 15) $strength = min(100, $strength + 10);
        if (!empty(array_intersect($skills, $this->skillsByCategory['soft']))) $strength += 5;

        return [
            'strength_score' => min(100, $strength),
            'strength_label' => $this->getStrengthLabel($strength),
            'total_skills_detected' => $totalSkills,
            'dominant_category' => $dominant,
            'category_breakdown' => $categories,
            'skill_diversity' => $this->calculateDiversity($categories, $totalSkills)
        ];
    }

    private function analyzeSkillGaps(array $skills, array $jobMatches): array {
        $gaps = [];
        foreach (array_slice($jobMatches, 0, 3) as $match) {
            $job = $match['job'];
            $jobSkills = $this->extractJobSkills($job);
            $missing = array_values(array_diff($jobSkills, $skills));
            $matched = array_values(array_intersect($skills, $jobSkills));

            $gaps[] = [
                'job_id' => $job['id'],
                'job_title' => $job['title'],
                'match_score' => $match['score'],
                'missing_skills' => $missing,
                'missing_count' => count($missing),
                'matched_skills' => $matched,
                'critical_gap' => count($missing) > count($matched)
            ];
        }
        return $gaps;
    }

    private function suggestCVImprovements(string $cvText, array $skills): array {
        $tips = [];
        $textLower = strtolower($cvText);

        $sections = [
            'experience' => ['experience', 'parcours professionnel', 'stage', 'stages'],
            'education' => ['formation', 'education', 'diplome', 'diplomes', 'etudes'],
            'skills' => ['competences', 'skills', 'technologies', 'stack technique'],
            'projects' => ['projets', 'projects', 'portfolio', 'realisations'],
            'summary' => ['profil', 'resume', 'summary', 'about', 'a propos']
        ];

        foreach ($sections as $section => $keywords) {
            $found = false;
            foreach ($keywords as $kw) {
                if (stripos($textLower, $kw) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $tips[] = [
                    'type' => 'structure',
                    'priority' => 'high',
                    'message' => "Ajoutez une section '" . ucfirst($section) . "' a votre CV."
                ];
            }
        }

        $wordCount = str_word_count($cvText);
        if ($wordCount < 150) {
            $tips[] = [
                'type' => 'content',
                'priority' => 'high',
                'message' => "Votre CV semble court ({$wordCount} mots). Detaillez davantage."
            ];
        } elseif ($wordCount > 800) {
            $tips[] = [
                'type' => 'content',
                'priority' => 'medium',
                'message' => "Votre CV est tres detaille ({$wordCount} mots). Privilegiez la concision."
            ];
        }

        $actionVerbs = ['developpe', 'concu', 'implemente', ' gere', 'cree', 'optimise', 'automatise', 'dirige', 'conception'];
        $foundVerbs = array_filter($actionVerbs, function($v) use ($textLower) {
            return stripos($textLower, $v) !== false;
        });
        if (count($foundVerbs) < 3) {
            $tips[] = [
                'type' => 'writing',
                'priority' => 'medium',
                'message' => "Utilisez plus de verbes d'action pour dynamiser vos descriptions."
            ];
        }

        if (count($skills) < 5) {
            $tips[] = [
                'type' => 'skills',
                'priority' => 'high',
                'message' => "Peu de competences detectees. Listez explicitement vos technologies."
            ];
        }

        if (!preg_match('/\d+/', $cvText)) {
            $tips[] = [
                'type' => 'content',
                'priority' => 'medium',
                'message' => "Ajoutez des chiffres et resultats quantifiables."
            ];
        }

        return $tips;
    }

    private function buildLearningPath(array $skills, array $jobMatches): array {
        $allMissing = [];
        foreach (array_slice($jobMatches, 0, 5) as $match) {
            $jobSkills = $this->extractJobSkills($match['job']);
            $missing = array_diff($jobSkills, $skills);
            foreach ($missing as $skill) {
                $allMissing[$skill] = ($allMissing[$skill] ?? 0) + 1;
            }
        }
        arsort($allMissing);

        $prioritized = [];
        foreach (array_slice($allMissing, 0, 8, true) as $skill => $frequency) {
            $difficulty = $this->learningDifficulty[$skill] ?? 3;
            if ($difficulty <= 2) {
                $timeEstimate = '1-2 semaines';
            } elseif ($difficulty <= 3) {
                $timeEstimate = '2-4 semaines';
            } elseif ($difficulty <= 4) {
                $timeEstimate = '1-3 mois';
            } else {
                $timeEstimate = '3-6 mois';
            }

            $impact = $frequency >= 3 ? 'high' : ($frequency >= 2 ? 'medium' : 'low');

            $prioritized[] = [
                'skill' => $skill,
                'demand_frequency' => $frequency,
                'difficulty_level' => $difficulty,
                'estimated_time' => $timeEstimate,
                'impact' => $impact,
                'category' => $this->getSkillCategory($skill)
            ];
        }

        return [
            'priority_skills' => $prioritized,
            'total_missing_across_jobs' => count($allMissing),
            'recommended_focus' => array_slice(array_keys($allMissing), 0, 3)
        ];
    }

    private function getMarketInsights(array $skills): array {
        $marketSkills = $this->getAllMarketSkills();
        $topDemand = array_slice($marketSkills, 0, 10, true);

        $candidateHas = array_intersect_key($marketSkills, array_flip($skills));
        $candidateMissing = array_diff_key($topDemand, array_flip($skills));

        $marketScore = 0;
        if (!empty($topDemand)) {
            $maxPossible = array_sum(array_slice($marketSkills, 0, 10));
            $candidateScore = array_sum($candidateHas);
            $marketScore = $maxPossible > 0 ? round(($candidateScore / $maxPossible) * 100) : 0;
        }

        return [
            'market_alignment_score' => min(100, $marketScore),
            'in_demand_skills_you_have' => array_keys($candidateHas),
            'trending_skills_to_consider' => array_slice(array_keys($candidateMissing), 0, 5),
            'top_skills_in_market' => array_keys($topDemand),
            'market_demand_stats' => array_slice($marketSkills, 0, 10, true)
        ];
    }

    private function enrichMatches(array $jobMatches, array $skills): array {
        $enriched = [];
        foreach ($jobMatches as $match) {
            $job = $match['job'];
            $jobSkills = $this->extractJobSkills($job);
            $matched = array_intersect($skills, $jobSkills);
            $missing = array_diff($jobSkills, $skills);

            $advice = [];
            if ($match['score'] >= 75) {
                $advice[] = "Poste ideal pour votre profil — postulez en priorite";
                $advice[] = "Mentionnez " . $this->formatList(array_slice(array_values($matched), 0, 3)) . " dans votre lettre";
            } elseif ($match['score'] >= 50) {
                $advice[] = "Bon potentiel — postulez avec une lettre motivee";
                if (!empty($missing)) {
                    $advice[] = "Mettez en avant votre capacite d'apprentissage pour " . $this->formatList(array_slice(array_values($missing), 0, 3));
                }
            } else {
                $advice[] = "Necessite un investissement en formation";
                $advice[] = "Competences cles a acquérir : " . $this->formatList(array_slice(array_values($missing), 0, 4));
            }

            $enriched[] = array_merge($match, [
                'advice' => $advice,
                'matched_skills_list' => array_values($matched),
                'missing_skills_list' => array_values($missing),
                'application_priority' => $match['score'] >= 75 ? 'high' : ($match['score'] >= 50 ? 'medium' : 'low')
            ]);
        }
        return $enriched;
    }

    private function identifyQuickWins(array $skills, array $jobMatches): array {
        $wins = [];

        $commonlyForgotten = ['git', 'agile', 'scrum', 'rest', 'json', 'html', 'css', 'linux', 'communication', 'teamwork'];
        $potentiallyOwned = array_diff($commonlyForgotten, $skills);
        if (!empty($potentiallyOwned)) {
            $wins[] = [
                'type' => 'cv_keyword',
                'action' => 'Verifiez si vous maitrisez ' . $this->formatList(array_slice($potentiallyOwned, 0, 4)) . ' et ajoutez-les a votre CV.',
                'effort' => 'low',
                'impact' => 'medium'
            ];
        }

        if (!in_array('communication', $skills) && !in_array('teamwork', $skills)) {
            $wins[] = [
                'type' => 'soft_skill',
                'action' => "Ajoutez des soft skills comme 'Communication' et 'Travail d'equipe' a votre CV.",
                'effort' => 'low',
                'impact' => 'medium'
            ];
        }

        $highImpactQuick = array_filter($this->learningDifficulty, function($d) {
            return $d <= 2;
        });
        foreach (array_slice($jobMatches, 0, 3) as $match) {
            $jobSkills = $this->extractJobSkills($match['job']);
            $missingEasy = array_intersect(array_keys($highImpactQuick), array_diff($jobSkills, $skills));
            if (!empty($missingEasy)) {
                $wins[] = [
                    'type' => 'quick_learning',
                    'action' => 'Pour "' . $match['job']['title'] . '", apprenez ' . $this->formatList(array_slice(array_values($missingEasy), 0, 3)) . ' (1-2 semaines).',
                    'effort' => 'low',
                    'impact' => 'high',
                    'target_job' => $match['job']['title']
                ];
            }
        }

        return $wins;
    }

    private function suggestCareerDirections(array $skills): array {
        $directions = [];

        $hasFrontend = count(array_intersect($skills, $this->skillsByCategory['frontend'])) >= 3;
        $hasBackend = count(array_intersect($skills, $this->skillsByCategory['backend'])) >= 3;
        $hasDevOps = count(array_intersect($skills, $this->skillsByCategory['devops'])) >= 2;
        $hasData = count(array_intersect($skills, $this->skillsByCategory['data'])) >= 2;
        $hasMobile = count(array_intersect($skills, $this->skillsByCategory['mobile'])) >= 2;

        if ($hasFrontend && $hasBackend) {
            $directions[] = ['role' => 'Developpeur Full Stack', 'fit_score' => 95, 'reason' => 'Frontend + Backend', 'next_skills' => ['typescript', 'docker', 'ci/cd']];
        } elseif ($hasFrontend) {
            $directions[] = ['role' => 'Developpeur Frontend', 'fit_score' => 90, 'reason' => 'Profil UI/UX', 'next_skills' => ['typescript', 'testing', 'performance']];
        }

        if ($hasBackend) {
            $directions[] = ['role' => 'Developpeur Backend', 'fit_score' => 90, 'reason' => 'Logique metier', 'next_skills' => ['microservices', 'message queue', 'testing']];
        }

        if ($hasDevOps) {
            $directions[] = ['role' => 'DevOps Engineer', 'fit_score' => 85, 'reason' => 'Deploiement & Cloud', 'next_skills' => ['kubernetes', 'terraform', 'monitoring']];
        }

        if ($hasData) {
            $directions[] = ['role' => 'Data Engineer', 'fit_score' => 85, 'reason' => 'Profil data', 'next_skills' => ['spark', 'airflow', 'visualization']];
        }

        if ($hasMobile) {
            $directions[] = ['role' => 'Developpeur Mobile', 'fit_score' => 85, 'reason' => 'Mobile natif/cross', 'next_skills' => ['state management', 'performance', 'testing']];
        }

        if (empty($directions)) {
            $directions[] = ['role' => 'Developpeur Web Junior', 'fit_score' => 70, 'reason' => 'Generaliste recommande', 'next_skills' => ['javascript', 'php', 'mysql', 'git']];
        }

        usort($directions, function($a, $b) {
            return $b['fit_score'] <=> $a['fit_score'];
        });

        return array_slice($directions, 0, 3);
    }

    private function extractJobSkills(array $jobData): array {
        $skills = [];
        if (!empty($jobData['skills'])) {
            $skills = array_map('trim', explode(',', strtolower($jobData['skills'])));
        }
        if (!empty($jobData['requirements']) && is_array($jobData['requirements'])) {
            foreach ($jobData['requirements'] as $req) {
                $reqSkills = array_map('trim', explode(',', strtolower($req)));
                $skills = array_merge($skills, $reqSkills);
            }
        }
        return array_values(array_unique(array_filter($skills)));
    }

    private function getMatchLevel(int $score): string {
        if ($score >= 85) return 'excellent';
        if ($score >= 70) return 'good';
        if ($score >= 50) return 'average';
        if ($score >= 30) return 'weak';
        return 'poor';
    }

    private function getStrengthLabel(int $score): string {
        if ($score >= 80) return 'Profil tres solide';
        if ($score >= 60) return 'Profil interessant';
        if ($score >= 40) return 'Profil a completer';
        return 'Profil debutant';
    }

    private function formatList(array $items): string {
        $items = array_values(array_filter(array_map('trim', $items)));
        if (empty($items)) return 'ces competences';
        if (count($items) === 1) return $items[0];
        $last = array_pop($items);
        return implode(', ', $items) . ' et ' . $last;
    }

    private function getSkillCategory(string $skill): string {
        foreach ($this->skillsByCategory as $cat => $skills) {
            if (in_array($skill, $skills)) return $cat;
        }
        return 'other';
    }

    private function calculateDiversity(array $categories, int $totalSkills): array {
        if ($totalSkills === 0) return ['score' => 0, 'label' => 'Aucune competence', 'categories_covered' => 0];
        $nonEmpty = 0;
        foreach ($categories as $c) {
            if ($c['count'] > 0) $nonEmpty++;
        }
        $diversityScore = min(100, $nonEmpty * 20 + ($totalSkills > 10 ? 10 : 0));
        return [
            'score' => $diversityScore,
            'label' => $diversityScore >= 80 ? 'Tres diversifie' : ($diversityScore >= 50 ? 'Diversifie' : 'Specialise'),
            'categories_covered' => $nonEmpty
        ];
    }

    private function getAllMarketSkills(): array {
        $stmt = $this->pdo->prepare("SELECT skills FROM jobs WHERE status = 'active' AND skills IS NOT NULL AND skills != ''");
        $stmt->execute();
        $allSkills = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $jobSkills = array_filter(array_map('trim', explode(',', strtolower($row['skills']))));
            foreach ($jobSkills as $skill) {
                $allSkills[$skill] = ($allSkills[$skill] ?? 0) + 1;
            }
        }
        arsort($allSkills);
        return $allSkills;
    }

    private function generateRoleSpecificTips(array $jobData, array $matched, array $missing): array {
        $tips = [];
        $title = strtolower($jobData['title'] ?? '');

        if (strpos($title, 'senior') !== false || strpos($title, 'lead') !== false || strpos($title, 'chef') !== false) {
            $tips[] = "Pour un poste senior, mettez en avant votre experience en architecture, mentorat et prise de decision.";
        }
        if (strpos($title, 'full stack') !== false) {
            $tips[] = "Demontrez votre capacite a naviguer entre frontend et backend.";
        }
        if (strpos($title, 'devops') !== false || strpos($title, 'sre') !== false) {
            $tips[] = "Mettez en avant votre experience en automatisation et monitoring.";
        }
        if (strpos($title, 'mobile') !== false) {
            $tips[] = "Incluez des liens vers vos applications (App Store, Play Store).";
        }
        if (!empty($missing) && !empty($matched)) {
            $tips[] = "Dans votre lettre, expliquez comment votre experience avec " . $this->formatList(array_slice($matched, 0, 2)) . " vous prepare a apprendre " . $this->formatList(array_slice($missing, 0, 2)) . ".";
        }

        return $tips;
    }

    private function estimateLearningTime(array $missingSkills): string {
        if (empty($missingSkills)) return 'Aucune formation necessaire';
        $totalDifficulty = 0;
        foreach ($missingSkills as $skill) {
            $totalDifficulty += $this->learningDifficulty[$skill] ?? 3;
        }
        $avg = $totalDifficulty / count($missingSkills);
        if ($avg <= 2) return '2-4 semaines de formation ciblee';
        if ($avg <= 3) return '1-2 mois de pratique reguliere';
        if ($avg <= 4) return '3-6 mois d\'apprentissage structure';
        return '6+ mois pour maitrise complete';
    }

    private function assessCompetitiveness(int $score, int $matched, int $missing): array {
        $ratio = $missing > 0 ? $matched / $missing : 10;
        return [
            'level' => $score >= 70 ? 'high' : ($score >= 50 ? 'medium' : 'low'),
            'advice' => $ratio >= 1 
                ? 'You are competitive for this position ' 
                : 'Strengthen your profile before applying',
            'ratio' => round($ratio, 2)
        ];
    }
}
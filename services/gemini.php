<?php

/**
 * SafeSignal AI - Gemini AI Service
 * Handles AI classification, summarization, and recommended actions.
 */

require_once __DIR__ . '/../config/config.php';

class GeminiService
{

    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_ENDPOINT;
    }

    /**
     * Analyze a report using Gemini AI
     * Returns structured classification data
     */
    public function analyzeReport(string $title, string $description, ?string $userCategory = null, ?string $userSeverity = null): array
    {
        if (empty($this->apiKey)) {
            return $this->fallbackAnalysis($title, $description, $userCategory, $userSeverity);
        }

        $prompt = $this->buildPrompt($title, $description, $userCategory, $userSeverity);

        try {
            $response = $this->callGeminiAPI($prompt);
            $result = $this->parseResponse($response);
            if ($result) {
                return $result;
            }
        } catch (Exception $e) {
            error_log("Gemini API Error: " . $e->getMessage());
        }

        return $this->fallbackAnalysis($title, $description, $userCategory, $userSeverity);
    }

    /**
     * Build the structured prompt for Gemini
     */
    private function buildPrompt(string $title, string $description, ?string $userCategory, ?string $userSeverity): string
    {
        $categoryHint = $userCategory ? "The user suggested the category is: {$userCategory}." : "";
        $severityHint = $userSeverity ? "The user suggested the severity is: {$userSeverity}." : "";

        return <<<PROMPT
You are an AI safety analyst for SafeSignal, a community safety and hazard reporting platform.

Analyze the following incident report and return a STRICT JSON response with no additional text or markdown.

INCIDENT TITLE: {$title}
INCIDENT DESCRIPTION: {$description}
{$categoryHint}
{$severityHint}

Classify this incident and provide the following JSON structure (return ONLY valid JSON, no markdown, no explanation):

{
  "category": "<one of: Crime, Flood, Fire, Harassment, Accident, Infrastructure Damage, Pollution, Medical Emergency, General Safety>",
  "severity": "<one of: Low, Medium, High, Critical>",
  "summary": "<2-3 sentence professional summary of the incident>",
  "recommended_actions": ["<action 1>", "<action 2>", "<action 3>", "<action 4>", "<action 5>"],
  "sdg_mapping": ["<SDG11 or SDG16 or both as array of strings>"],
  "tags": ["<keyword1>", "<keyword2>", "<keyword3>", "<keyword4>", "<keyword5>"]
}

Classification Guidelines:
- Severity CRITICAL: Immediate threat to life, active emergency
- Severity HIGH: Significant danger, needs urgent response within hours
- Severity MEDIUM: Serious but manageable, needs response within 24 hours
- Severity LOW: Minor issue, can be addressed within days

SDG Mapping:
- SDG11 (Sustainable Cities): Infrastructure, flood, pollution, accidents, environmental hazards
- SDG16 (Peace & Justice): Crime, harassment, violence, governance issues
- Both SDGs can apply: Safety incidents affecting both urban sustainability and community peace

Return ONLY the JSON object, nothing else.
PROMPT;
    }

    /**
     * Call the Gemini API
     */
    private function callGeminiAPI(string $prompt): string
    {
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.8,
                'maxOutputTokens' => 1024,
            ]
        ]);

        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("cURL Error: {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new Exception("Gemini API returned HTTP {$httpCode}: {$response}");
        }

        return $response;
    }

    /**
     * Parse the Gemini API response
     */
    private function parseResponse(string $rawResponse): ?array
    {
        $data = json_decode($rawResponse, true);

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return null;
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];

        // Strip markdown code blocks if present
        $text = preg_replace('/```json\s*/i', '', $text);
        $text = preg_replace('/```\s*/i', '', $text);
        $text = trim($text);

        $parsed = json_decode($text, true);

        if (!$parsed || !isset($parsed['category'])) {
            // Try to extract JSON from mixed content
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
            }
        }

        if (!$parsed) {
            return null;
        }

        // Validate and sanitize the response
        $validCategories = ['Crime', 'Flood', 'Fire', 'Harassment', 'Accident', 'Infrastructure Damage', 'Pollution', 'Medical Emergency', 'General Safety'];
        $validSeverities  = ['Low', 'Medium', 'High', 'Critical'];

        $category = in_array($parsed['category'] ?? '', $validCategories) ? $parsed['category'] : 'General Safety';
        $severity  = in_array($parsed['severity'] ?? '', $validSeverities)  ? $parsed['severity']  : 'Medium';
        $summary   = substr($parsed['summary'] ?? 'Incident reported by community member.', 0, 1000);

        $actions = [];
        if (isset($parsed['recommended_actions']) && is_array($parsed['recommended_actions'])) {
            $actions = array_slice(array_map('strval', $parsed['recommended_actions']), 0, 8);
        }

        $sdgMapping = [];
        if (isset($parsed['sdg_mapping']) && is_array($parsed['sdg_mapping'])) {
            $sdgMapping = array_filter($parsed['sdg_mapping'], fn($s) => strpos($s, 'SDG') !== false);
        }
        if (empty($sdgMapping)) {
            $sdgMapping = ['SDG11', 'SDG16'];
        }

        $tags = [];
        if (isset($parsed['tags']) && is_array($parsed['tags'])) {
            $tags = array_slice(array_map('strval', $parsed['tags']), 0, 10);
        }

        return [
            'category'             => $category,
            'severity'             => $severity,
            'summary'              => $summary,
            'recommended_actions'  => $actions,
            'sdg_mapping'          => array_values($sdgMapping),
            'tags'                 => $tags,
        ];
    }

    /**
     * Fallback analysis when Gemini API is unavailable
     */
    private function fallbackAnalysis(string $title, string $description, ?string $userCategory, ?string $userSeverity): array
    {
        $text = strtolower($title . ' ' . $description);

        // Auto-detect category from keywords
        $category = $userCategory ?? 'General Safety';
        if (!$userCategory) {
            $rules = [
                'Crime'                => ['robbery', 'theft', 'assault', 'attack', 'murder', 'drug', 'gang', 'crime', 'violence', 'abduction', 'kidnap'],
                'Flood'                => ['flood', 'water', 'submerge', 'overflowing', 'waterlogged', 'rain'],
                'Fire'                 => ['fire', 'burning', 'smoke', 'flames', 'blaze', 'explosion'],
                'Harassment'           => ['harass', 'molest', 'intimidate', 'threat', 'bully', 'sexual'],
                'Accident'             => ['accident', 'collision', 'crash', 'hit', 'vehicle', 'car', 'motorcycle'],
                'Infrastructure Damage' => ['pothole', 'bridge', 'road', 'collapse', 'structure', 'building', 'infrastructure', 'damaged'],
                'Pollution'            => ['pollution', 'smoke', 'contamination', 'waste', 'sewage', 'chemical', 'toxic', 'dumping'],
                'Medical Emergency'    => ['medical', 'injured', 'hospital', 'ambulance', 'health', 'sick', 'unconscious'],
            ];
            foreach ($rules as $cat => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $category = $cat;
                        break 2;
                    }
                }
            }
        }

        // Auto-detect severity
        $severity = $userSeverity ?? 'Medium';
        if (!$userSeverity) {
            $criticalKw = ['critical', 'fatal', 'death', 'dying', 'kidnap', 'abduct', 'fire', 'explosion', 'armed'];
            $highKw     = ['urgent', 'severe', 'serious', 'flooding', 'collapsed', 'injured', 'blockage'];
            $lowKw      = ['minor', 'small', 'pothole', 'noise', 'littering', 'graffiti'];
            foreach ($criticalKw as $kw) {
                if (str_contains($text, $kw)) {
                    $severity = 'Critical';
                    break;
                }
            }
            if ($severity === 'Medium') {
                foreach ($highKw as $kw) {
                    if (str_contains($text, $kw)) {
                        $severity = 'High';
                        break;
                    }
                }
            }
            if ($severity === 'Medium') {
                foreach ($lowKw as $kw) {
                    if (str_contains($text, $kw)) {
                        $severity = 'Low';
                        break;
                    }
                }
            }
        }

        $sdgMap = [
            'Crime'                 => ['SDG16'],
            'Harassment'            => ['SDG16', 'SDG5'],
            'Flood'                 => ['SDG11', 'SDG13'],
            'Fire'                  => ['SDG11'],
            'Accident'              => ['SDG11', 'SDG3'],
            'Infrastructure Damage' => ['SDG11', 'SDG9'],
            'Pollution'             => ['SDG11', 'SDG3'],
            'Medical Emergency'     => ['SDG3'],
            'General Safety'        => ['SDG11', 'SDG16'],
        ];

        $sentences = preg_split('/(?<=[.!?])\s+/', $description);
        $summary   = implode(' ', array_slice($sentences, 0, 2));
        if (strlen($summary) > 300) $summary = substr($summary, 0, 300) . '...';

        return [
            'category'            => $category,
            'severity'            => $severity,
            'summary'             => $summary ?: 'Community-reported safety incident requiring attention.',
            'recommended_actions' => [
                'Contact local emergency services if there is immediate danger',
                'Document the incident with photos or videos',
                'Alert nearby community members',
                'Follow official safety protocols for this type of incident',
                'Update authorities with any new developments',
            ],
            'sdg_mapping'         => $sdgMap[$category] ?? ['SDG11', 'SDG16'],
            'tags'                => array_slice(array_unique(array_filter(explode(' ', preg_replace('/[^a-z ]/', '', $text)))), 0, 8),
        ];
    }
}

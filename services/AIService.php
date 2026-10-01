<?php
// ============================================================
// FarmersBD — AI Service (Google Gemini Integration)
// Expert Agronomist and Aquaculture Specialist in Bangladesh
// ============================================================

class AIService {

    private string $apiKey;
    private array  $models;
    private int    $timeout;
    private string $systemInstruction;

    public function __construct() {
        $this->apiKey = defined('AI_API_KEY') && !empty(AI_API_KEY) 
            ? AI_API_KEY 
            : '';
        
        $this->models = [

            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.5-flash',
            'gemini-flash-latest'
        ];
        
        $this->timeout = defined('AI_API_TIMEOUT') ? AI_API_TIMEOUT : 45;
        
        $this->systemInstruction = "You are an expert Agronomist and Aquaculture Specialist in Bangladesh. Analyze the uploaded image carefully. Identify the plant disease, shrimp/fish disease, or water-related issues (like algae bloom or virus symptoms). Provide the disease name and a simple, step-by-step remedy in fluent Bangla. Keep the tone helpful and friendly for local farmers.";
    }

    /**
     * Check if the AI service is configured.
     */
    public function is_configured(): bool {
        return !empty($this->apiKey);
    }

    /**
     * Send an image to Gemini AI and return structured result.
     *
     * @param  string $imagePath  Absolute path to the uploaded image
     * @return array  ['success' => bool, 'label' => string, 'disease_name' => string, 'analysis_text' => string, 'confidence' => float, 'raw' => array, 'error' => string]
     */
    public function predict(string $imagePath): array {
        if (!$this->is_configured()) {
            return [
                'success'       => false,
                'label'         => null,
                'disease_name'  => null,
                'analysis_text' => null,
                'confidence'    => null,
                'raw'           => [],
                'error'         => 'AI_NOT_CONFIGURED',
            ];
        }

        if (!file_exists($imagePath) || !is_readable($imagePath)) {
            return [
                'success'       => false,
                'label'         => null,
                'disease_name'  => null,
                'analysis_text' => null,
                'confidence'    => null,
                'raw'           => [],
                'error'         => 'IMAGE_NOT_FOUND',
            ];
        }

        try {
            $raw = $this->call_gemini_api($imagePath);
            return $this->parse_gemini_response($raw);
        } catch (Throwable $e) {
            error_log('[AIService] predict() failed: ' . $e->getMessage());
            return [
                'success'       => false,
                'label'         => null,
                'disease_name'  => null,
                'analysis_text' => null,
                'confidence'    => null,
                'raw'           => [],
                'error'         => 'API_ERROR: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send the image to Google Gemini generateContent API via cURL with model fallbacks.
     */
    private function call_gemini_api(string $imagePath): array {
        $imgData = file_get_contents($imagePath);
        $base64  = base64_encode($imgData);
        
        // Detect MIME Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $imagePath);
        finfo_close($finfo);
        if (!$mime || !str_starts_with($mime, 'image/')) {
            $mime = 'image/jpeg';
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->systemInstruction]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'এই ছবিটি গভীরভাবে বিশ্লেষণ করুন। মাছের রোগ, চিংড়ির রোগ, গাছের সমস্যা বা পুকুরের পানির গুণাগুণ সমস্যা বিস্তারিত চিহ্নিত করুন। সহজ ও প্রাঞ্জল বাংলায় রোগের নাম, প্রধান লক্ষণসমূহ এবং স্থানীয় খামারিদের জন্য ধাপে ধাপে কার্যকর ঘরোয়া ও ওষুধ প্রয়োগের সঠিক প্রতিকার দিন।'
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data'      => $base64
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'topP'            => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];

        $jsonPayload = json_encode($payload);
        $lastError   = '';

        foreach ($this->models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonPayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'X-goog-api-key: ' . $this->apiKey
                ],
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                $lastError = "cURL error on model {$model}: {$curlErr}";
                continue;
            }

            if ($httpCode === 200) {
                $decoded = json_decode($response, true);
                if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                    $decoded['_model_used'] = $model;
                    return $decoded;
                }
            }

            $lastError = "HTTP {$httpCode} on model {$model}: " . substr($response, 0, 200);
        }

        throw new RuntimeException($lastError ?: 'Failed to get valid response from Gemini API');
    }

    /**
     * Parse the raw Gemini response into a normalized structure.
     */
    private function parse_gemini_response(array $raw): array {
        $text = $raw['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            return [
                'success'       => false,
                'label'         => null,
                'disease_name'  => null,
                'analysis_text' => null,
                'confidence'    => null,
                'raw'           => $raw,
                'error'         => 'EMPTY_RESPONSE',
            ];
        }

        // Extract a clean disease/problem title if present in bold or first line
        $diseaseName = 'মৎস্য/কৃষি রোগ ও সমাধান';
        if (preg_match('/\*\*(.*?)\*\*/u', $text, $matches)) {
            $diseaseName = trim($matches[1]);
        } elseif (preg_match('/^#+\s*(.+)$/m', $text, $matches)) {
            $diseaseName = trim($matches[1]);
        }

        return [
            'success'       => true,
            'label'         => $diseaseName,
            'disease_name'  => $diseaseName,
            'analysis_text' => $text,
            'confidence'    => null,
            'raw'           => $raw,
            'error'         => null,
        ];
    }

    /**
     * Look up the disease record in the database matching keywords.
     */
    public function find_disease_by_label(string $label): ?array {
        // Direct match
        $found = db_query_one("SELECT * FROM diseases WHERE (name LIKE ? OR ai_label LIKE ?) AND is_active = 1 LIMIT 1", ["%{$label}%", "%{$label}%"]);
        if ($found) return $found;

        // Keyword based match
        $keywords = ['লেজ', 'পাখনা', 'ক্ষত', 'ফুলকা', 'সাদা', 'ড্রপসি', 'গিল', 'অ্যালগাল', 'উকুন'];
        foreach ($keywords as $kw) {
            if (mb_stripos($label, $kw) !== false) {
                $match = db_query_one("SELECT * FROM diseases WHERE (name LIKE ? OR symptoms LIKE ?) AND is_active = 1 LIMIT 1", ["%{$kw}%", "%{$kw}%"]);
                if ($match) return $match;
            }
        }

        // Return first general disease as fallback context
        return db_query_one("SELECT * FROM diseases WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
    }

    /**
     * Get related products for a disease or general remedies.
     */
    public function get_related_products(int $diseaseId): array {
        $products = db_query(
            "SELECT p.id, p.name, p.slug, p.image, p.price, p.discount_price, p.stock
             FROM disease_products dp
             JOIN products p ON p.id = dp.product_id
             WHERE dp.disease_id = ? AND p.is_active = 1 LIMIT 4",
            [$diseaseId]
        );

        if (empty($products)) {
            $products = db_query(
                "SELECT p.id, p.name, p.slug, p.image, p.price, p.discount_price, p.stock
                 FROM products p
                 WHERE p.is_active = 1 ORDER BY p.id DESC LIMIT 4"
            );
        }

        return $products;
    }

    /**
     * Save a diagnosis record to the database.
     */
    public function save_diagnosis(
        ?int    $userId,
        string  $imagePath,
        ?string $label,
        ?float  $confidence,
        ?int    $diseaseId,
        ?string $apiResponse,
        string  $ipAddress = ''
    ): int {
        return db_insert(
            "INSERT INTO ai_diagnoses 
             (user_id, image_path, prediction, confidence, disease_id, api_response, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $imagePath, $label, $confidence, $diseaseId, $apiResponse, $ipAddress]
        );
    }
}

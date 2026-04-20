<?php

namespace App\Services;

use App\Repositories\Tickets\TicketIntelligenceRepository;
use Illuminate\Support\Collection;

class SmartTicketIntakeService
{
    /**
     * @var array<string, array<int, string>>
     */
    private const TYPE_KEYWORDS = [
        'hardware' => [
            'printer',
            'laptop',
            'desktop',
            'pc',
            'komputer',
            'monitor',
            'keyboard',
            'mouse',
            'scanner',
            'hard disk',
            'ssd',
            'ram',
        ],
        'software' => [
            'software',
            'aplikasi',
            'application',
            'install',
            'update',
            'patch',
            'license',
            'outlook',
            'excel',
            'word',
            'browser',
        ],
        'network' => [
            'network',
            'jaringan',
            'wifi',
            'internet',
            'vpn',
            'lan',
            'router',
            'switch',
            'dns',
            'ip',
            'connection',
            'koneksi',
        ],
        'access' => [
            'password',
            'akun',
            'account',
            'login',
            'signin',
            'sign in',
            'permission',
            'izin',
            'akses',
            'role',
            'unlock',
            'reset',
        ],
        'security' => [
            'virus',
            'malware',
            'phishing',
            'ransomware',
            'breach',
            'hacked',
            'attack',
            'serangan',
            'suspicious',
            'keamanan',
        ],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PRIORITY_KEYWORDS = [
        'urgent' => [
            'urgent',
            'darurat',
            'emergency',
            'critical',
            'kritis',
            'production down',
            'system down',
            'sistem mati',
            'service down',
            'cannot work',
            'tidak bisa kerja',
        ],
        'high' => [
            'down',
            'failed',
            'gagal',
            'cannot',
            'tidak bisa',
            'crash',
            'error',
            'blocking',
            'terhambat',
        ],
        'medium' => [
            'slow',
            'lambat',
            'intermittent',
            'bug',
            'issue',
            'gangguan',
            'degraded',
            'unstable',
        ],
        'low' => [
            'request',
            'permintaan',
            'question',
            'pertanyaan',
            'info',
            'informasi',
            'enhancement',
            'improvement',
            'minor',
        ],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PRIORITY_NAME_PREFERENCES = [
        'urgent' => ['urgent', 'critical'],
        'high' => ['high'],
        'medium' => ['normal', 'medium'],
        'low' => ['low'],
    ];

    private TicketIntelligenceRepository $repository;

    public function __construct(TicketIntelligenceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate Smart ITSM recommendations for ticket intake.
     *
     * @return array<string, mixed>
     */
    public function analyze(string $subject, string $description, bool $includeKnowledgeSuggestions = true): array
    {
        $combinedText = mb_strtolower(trim($subject . ' ' . $description));

        $typeScores = $this->scoreKeywords($combinedText, self::TYPE_KEYWORDS);
        $priorityScores = $this->scoreKeywords($combinedText, self::PRIORITY_KEYWORDS);

        $topTypeCategory = $this->resolveTopCategory($typeScores, 'software');
        $topPriorityCategory = $this->resolveTopCategory($priorityScores, 'medium');

        $ticketTypes = $this->repository->getTicketTypes();
        $ticketPriorities = $this->repository->getTicketPriorities();

        $recommendedType = $this->resolveRecommendedType($ticketTypes, $topTypeCategory);
        $recommendedPriority = $this->resolveRecommendedPriority($ticketPriorities, $topPriorityCategory);

        $tokens = $this->extractSearchTokens($combinedText);
        $knowledgeBaseSuggestions = $includeKnowledgeSuggestions
            ? $this->repository->findKnowledgeBaseSuggestions($tokens, 5)
            : collect();

        return [
            'message' => 'Smart ticket recommendations generated successfully.',
            'recommended' => [
                'ticket_type_id' => $recommendedType['id'],
                'ticket_type_name' => $recommendedType['name'],
                'ticket_priority_id' => $recommendedPriority['id'],
                'ticket_priority_name' => $recommendedPriority['name'],
                'type_confidence' => $this->calculateConfidence($typeScores),
                'priority_confidence' => $this->calculateConfidence($priorityScores),
            ],
            'matched_signals' => [
                'type_category' => $topTypeCategory,
                'priority_category' => $topPriorityCategory,
                'type_score_breakdown' => $typeScores,
                'priority_score_breakdown' => $priorityScores,
            ],
            'knowledge_base_suggestions' => $knowledgeBaseSuggestions,
            'metadata' => [
                'api_version' => 'v1',
                'timezone' => 'Asia/Jakarta',
                'generated_at' => now('Asia/Jakarta')->toIso8601String(),
                'token_count' => count($tokens),
            ],
        ];
    }

    /**
     * @param array<string, array<int, string>> $dictionary
     * @return array<string, int>
     */
    private function scoreKeywords(string $text, array $dictionary): array
    {
        $scores = [];

        foreach ($dictionary as $label => $keywords) {
            $score = 0;

            foreach ($keywords as $keyword) {
                if (str_contains($text, mb_strtolower($keyword))) {
                    $score++;
                }
            }

            $scores[$label] = $score;
        }

        return $scores;
    }

    /**
     * @param array<string, int> $scores
     */
    private function resolveTopCategory(array $scores, string $fallback): string
    {
        if (empty($scores)) {
            return $fallback;
        }

        arsort($scores);
        $topCategory = array_key_first($scores);

        if ($topCategory === null || (int) $scores[$topCategory] === 0) {
            return $fallback;
        }

        return $topCategory;
    }

    /**
     * @param array<string, int> $scores
     */
    private function calculateConfidence(array $scores): float
    {
        if (empty($scores)) {
            return 0.0;
        }

        $maxScore = max($scores);

        if ($maxScore <= 0) {
            return 0.2;
        }

        return min(1.0, round(0.2 + ($maxScore * 0.2), 2));
    }

    /**
     * @return array{id: int|null, name: string|null}
     */
    private function resolveRecommendedType(Collection $ticketTypes, string $typeCategory): array
    {
        $normalizedTypes = $ticketTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->type,
                'name_lower' => mb_strtolower((string) $type->type),
            ];
        });

        $candidates = [
            $typeCategory,
            $typeCategory . ' issue',
            $typeCategory . ' problem',
        ];

        foreach ($candidates as $candidate) {
            $found = $normalizedTypes->first(function (array $row) use ($candidate) {
                return str_contains($row['name_lower'], mb_strtolower($candidate));
            });

            if ($found !== null) {
                return [
                    'id' => $found['id'],
                    'name' => $found['name'],
                ];
            }
        }

        $fallback = $ticketTypes->first();

        return [
            'id' => $fallback->id ?? null,
            'name' => $fallback->type ?? null,
        ];
    }

    /**
     * @return array{id: int|null, name: string|null}
     */
    private function resolveRecommendedPriority(Collection $priorities, string $priorityCategory): array
    {
        $normalizedPriorities = $priorities->map(function ($priority) {
            return [
                'id' => $priority->id,
                'name' => $priority->priority,
                'name_lower' => mb_strtolower((string) $priority->priority),
            ];
        });

        $candidates = self::PRIORITY_NAME_PREFERENCES[$priorityCategory] ?? self::PRIORITY_NAME_PREFERENCES['medium'];

        foreach ($candidates as $candidate) {
            $found = $normalizedPriorities->first(function (array $row) use ($candidate) {
                return $row['name_lower'] === mb_strtolower($candidate)
                    || str_contains($row['name_lower'], mb_strtolower($candidate));
            });

            if ($found !== null) {
                return [
                    'id' => $found['id'],
                    'name' => $found['name'],
                ];
            }
        }

        $fallback = $priorities->first();

        return [
            'id' => $fallback->id ?? null,
            'name' => $fallback->priority ?? null,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractSearchTokens(string $text): array
    {
        $tokens = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/i', ' ', $text) ?? '') ?: [];

        $stopWords = [
            'the',
            'and',
            'for',
            'with',
            'this',
            'that',
            'from',
            'yang',
            'dan',
            'untuk',
            'dengan',
            'pada',
            'atau',
            'saya',
            'kami',
        ];

        $filtered = collect($tokens)
            ->map(fn (string $token) => trim($token))
            ->filter(fn (string $token) => mb_strlen($token) >= 3)
            ->reject(fn (string $token) => in_array($token, $stopWords, true))
            ->unique()
            ->take(8)
            ->values()
            ->all();

        return $filtered;
    }
}

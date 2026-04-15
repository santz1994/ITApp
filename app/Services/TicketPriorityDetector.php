<?php

namespace App\Services;

use App\TicketsPriority;

class TicketPriorityDetector
{
    /**
     * Automatically detect ticket priority based on subject and description
     * 
     * @param string $subject
     * @param string $description
     * @return int Priority ID
     */
    public static function detectPriority($subject, $description)
    {
        $text = strtolower($subject . ' ' . $description);
        
        // High Priority Keywords (Urgent)
        $highKeywords = [
            'urgent', 'mendesak', 'segera', 'darurat', 'emergency',
            'down', 'mati', 'crash', 'error', 'gagal total',
            'tidak bisa', 'rusak parah', 'critical', 'kritis',
            'server down', 'network down', 'sistem mati',
            'tidak berfungsi sama sekali', 'production down'
        ];
        
        // Medium Priority Keywords (Normal)
        $mediumKeywords = [
            'lambat', 'slow', 'lag', 'lemot',
            'masalah', 'problem', 'issue', 'bug',
            'tidak lancar', 'gangguan', 'error kadang',
            'perlu bantuan', 'help', 'tolong',
            'maintenance', 'update', 'upgrade'
        ];
        
        // Low Priority Keywords
        $lowKeywords = [
            'request', 'permintaan', 'mohon', 'bisa',
            'perlu', 'ingin', 'minta', 'tolong buatkan',
            'pertanyaan', 'tanya', 'bagaimana', 'cara',
            'informasi', 'panduan', 'tutorial',
            'improvement', 'enhancement', 'saran'
        ];
        
        // Check for high priority
        foreach ($highKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                // Get High priority ID
                $priority = TicketsPriority::where('priority', 'High')->first();
                return $priority ? $priority->id : 2;
            }
        }
        
        // Check for low priority
        foreach ($lowKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                // Get Low priority ID
                $priority = TicketsPriority::where('priority', 'Low')->first();
                return $priority ? $priority->id : 4;
            }
        }
        
        // Check for medium priority
        foreach ($mediumKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                // Get Medium priority ID
                $priority = TicketsPriority::where('priority', 'Medium')->first();
                return $priority ? $priority->id : 3;
            }
        }
        
        // Default to Medium if no keywords matched
        $priority = TicketsPriority::where('priority', 'Medium')->first();
        return $priority ? $priority->id : 3;
    }
}

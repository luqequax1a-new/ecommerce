<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\District;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TurkishLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Turkish Location Data Import...');
        
        // Clear cache before starting
        Cache::forget('provinces_list');
        for ($i = 1; $i <= 81; $i++) {
            Cache::forget("districts_province_{$i}");
        }
        
        $startTime = microtime(true);
        $jsonPath = database_path('seed-data/locations/all-city-district.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("JSON file not found: {$jsonPath}");
            return;
        }
        
        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON format: ' . json_last_error_msg());
            return;
        }
        
        $this->command->info("Loaded data: {$data['cityCount']} cities, {$data['totalDistrictCount']} districts");
        
        $stats = [
            'provinces_added' => 0,
            'provinces_skipped' => 0,
            'districts_added' => 0,
            'districts_skipped' => 0,
            'errors' => []
        ];
        
        DB::transaction(function () use ($data, &$stats) {
            $this->seedProvinces($data['city'], $stats);
            $this->seedDistricts($data['city'], $stats);
        });
        
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        
        $this->command->info("\n=== IMPORT SUMMARY ===");
        $this->command->info("Execution Time: {$executionTime} seconds");
        $this->command->info("Provinces Added: {$stats['provinces_added']}");
        $this->command->info("Provinces Skipped: {$stats['provinces_skipped']}");
        $this->command->info("Districts Added: {$stats['districts_added']}");
        $this->command->info("Districts Skipped: {$stats['districts_skipped']}");
        
        if (!empty($stats['errors'])) {
            $this->command->warn("Errors encountered: " . count($stats['errors']));
            foreach (array_slice($stats['errors'], 0, 5) as $error) {
                $this->command->error("- {$error}");
            }
        }
        
        // Log summary
        Log::info('Turkish Location Import completed', $stats);
        
        $this->command->info("\nTurkish location data imported successfully!");
    }
    
    private function seedProvinces(array $cities, array &$stats): void
    {
        $this->command->info('Processing provinces...');
        
        foreach ($cities as $city) {
            try {
                $plateCode = (int) $city['plateCode'];
                $name = $this->normalizeText($city['name']);
                
                // Check if province already exists
                $existing = Province::find($plateCode);
                
                if ($existing) {
                    $stats['provinces_skipped']++;
                    continue;
                }
                
                Province::create([
                    'id' => $plateCode,
                    'name' => $name,
                    'region' => null, // Will be populated later if needed
                    'is_active' => true
                ]);
                
                $stats['provinces_added']++;
                
                if ($stats['provinces_added'] % 20 === 0) {
                    $this->command->info("Processed {$stats['provinces_added']} provinces...");
                }
                
            } catch (\Exception $e) {
                $stats['errors'][] = "Province {$city['name']}: " . $e->getMessage();
            }
        }
    }
    
    private function seedDistricts(array $cities, array &$stats): void
    {
        $this->command->info('Processing districts...');
        
        foreach ($cities as $city) {
            try {
                $plateCode = (int) $city['plateCode'];
                
                foreach ($city['discrits'] as $districtName) {
                    $normalizedName = $this->normalizeText($districtName);
                    
                    // Check if district already exists for this province
                    $existing = District::where('province_id', $plateCode)
                                      ->where('name', $normalizedName)
                                      ->first();
                    
                    if ($existing) {
                        $stats['districts_skipped']++;
                        continue;
                    }
                    
                    District::create([
                        'province_id' => $plateCode,
                        'name' => $normalizedName,
                        'is_active' => true
                    ]);
                    
                    $stats['districts_added']++;
                    
                    if ($stats['districts_added'] % 100 === 0) {
                        $this->command->info("Processed {$stats['districts_added']} districts...");
                    }
                }
                
            } catch (\Exception $e) {
                $stats['errors'][] = "Districts for {$city['name']}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Normalize Turkish text to Title Case and clean formatting
     */
    private function normalizeText(string $text): string
    {
        // Remove extra spaces and trim
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        // Convert to Title Case with proper Turkish character handling
        $text = mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
        
        // Fix common Turkish uppercase/lowercase issues
        $text = str_replace(['İ', 'I'], ['İ', 'I'], $text);
        $text = str_replace(['ı'], ['ı'], $text);
        
        return $text;
    }
}

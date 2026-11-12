<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Individual;
use App\Models\Tree;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Advanced reporting service with PDF generation and customizable templates
 */
final class ReportService
{
    public function __construct(
        private readonly GraphAnalyticsService $graphService,
        private readonly PrivacyService $privacyService,
        private readonly CacheService $cacheService
    ) {}

    /**
     * Generate family tree chart report
     *
     * @param  array  $options  Configuration options
     * @return array Report data and metadata
     */
    public function generateFamilyTreeChart(Tree $tree, array $options = []): array
    {
        $defaultOptions = [
            'format' => 'pdf', // pdf, png, svg
            'orientation' => 'landscape',
            'include_photos' => false,
            'include_dates' => true,
            'include_locations' => false,
            'generations' => 4,
            'style' => 'traditional', // traditional, modern, compact
            'color_scheme' => 'default',
        ];

        $config = array_merge($defaultOptions, $options);

        // Get tree individuals with privacy filtering
        $individuals = $this->getTreeIndividuals($tree, $config['generations']);

        // Generate chart data structure
        $chartData = $this->buildChartStructure($individuals, $config);

        // Create the report
        $reportData = [
            'title' => "Family Tree: {$tree->name}",
            'generated_at' => now(),
            'tree' => $tree,
            'chart_data' => $chartData,
            'statistics' => $this->graphService->calculateTreeStatistics($tree->id),
            'options' => $config,
        ];

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($reportData, 'reports.family-tree-chart');
        }

        return $reportData;
    }

    /**
     * Generate ancestor report
     */
    public function generateAncestorReport(Individual $individual, array $options = []): array
    {
        $defaultOptions = [
            'generations' => 5,
            'include_siblings' => false,
            'include_sources' => true,
            'include_notes' => false,
            'format' => 'pdf',
            'detailed' => true,
        ];

        $config = array_merge($defaultOptions, $options);

        // Get ancestors
        $ancestors = $this->getAncestors($individual, $config['generations']);

        // Get additional data if requested
        $additionalData = [];
        if ($config['include_siblings']) {
            $additionalData['siblings'] = $this->getSiblings($ancestors);
        }

        $reportData = [
            'title' => "Ancestor Report: {$individual->first_name} {$individual->last_name}",
            'subject' => $individual,
            'ancestors' => $ancestors,
            'additional_data' => $additionalData,
            'generated_at' => now(),
            'options' => $config,
        ];

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($reportData, 'reports.ancestor-report');
        }

        return $reportData;
    }

    /**
     * Generate descendant report
     */
    public function generateDescendantReport(Individual $individual, array $options = []): array
    {
        $defaultOptions = [
            'generations' => 5,
            'include_spouses' => true,
            'include_statistics' => true,
            'format' => 'pdf',
            'group_by_generation' => true,
        ];

        $config = array_merge($defaultOptions, $options);

        // Get descendants using graph service
        $descendants = $this->graphService->findDescendants($individual->id, $config['generations']);

        // Group by generation if requested
        if ($config['group_by_generation']) {
            $descendants = collect($descendants)->groupBy('generation')->toArray();
        }

        $reportData = [
            'title' => "Descendant Report: {$individual->first_name} {$individual->last_name}",
            'subject' => $individual,
            'descendants' => $descendants,
            'statistics' => $this->calculateDescendantStatistics($descendants),
            'generated_at' => now(),
            'options' => $config,
        ];

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($reportData, 'reports.descendant-report');
        }

        return $reportData;
    }

    /**
     * Generate comprehensive tree statistics report
     */
    public function generateStatisticsReport(Tree $tree, array $options = []): array
    {
        $defaultOptions = [
            'include_charts' => true,
            'include_surname_analysis' => true,
            'include_geographic_analysis' => false,
            'include_timeline' => true,
            'format' => 'pdf',
        ];

        $config = array_merge($defaultOptions, $options);

        // Gather comprehensive statistics
        $statistics = $this->graphService->calculateTreeStatistics($tree->id);
        $surnameDistribution = $this->graphService->analyzeSurnameDistribution($tree->id);

        $reportData = [
            'title' => "Statistics Report: {$tree->name}",
            'tree' => $tree,
            'statistics' => $statistics,
            'surname_distribution' => $surnameDistribution,
            'demographic_data' => $this->calculateDemographics($tree),
            'timeline_data' => $this->generateTimelineData($tree),
            'generated_at' => now(),
            'options' => $config,
        ];

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($reportData, 'reports.statistics-report');
        }

        return $reportData;
    }

    /**
     * Generate family history book
     */
    public function generateFamilyBook(Tree $tree, array $options = []): array
    {
        $defaultOptions = [
            'include_photos' => true,
            'include_stories' => true,
            'include_timeline' => true,
            'include_charts' => true,
            'chapters' => [
                'introduction',
                'family_tree',
                'individuals',
                'timeline',
                'statistics',
                'appendix',
            ],
            'format' => 'pdf',
            'style' => 'professional',
        ];

        $config = array_merge($defaultOptions, $options);

        // Build comprehensive family book data
        $bookData = [
            'title' => "Family History: {$tree->name}",
            'tree' => $tree,
            'chapters' => $this->buildBookChapters($tree, $config),
            'table_of_contents' => $this->generateTableOfContents($tree, $config),
            'generated_at' => now(),
            'options' => $config,
        ];

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($bookData, 'reports.family-book', [
                'orientation' => 'portrait',
                'paper' => 'a4',
                'margin' => '20mm',
            ]);
        }

        return $bookData;
    }

    /**
     * Generate custom report based on template
     */
    public function generateCustomReport(string $templateName, array $data, array $options = []): array
    {
        $defaultOptions = [
            'format' => 'pdf',
            'orientation' => 'portrait',
            'paper' => 'a4',
        ];

        $config = array_merge($defaultOptions, $options);

        $reportData = array_merge($data, [
            'generated_at' => now(),
            'options' => $config,
        ]);

        if ($config['format'] === 'pdf') {
            return $this->generatePdfReport($reportData, "reports.custom.{$templateName}");
        }

        return $reportData;
    }

    /**
     * Generate PDF report from data and template
     */
    private function generatePdfReport(array $data, string $template, array $pdfOptions = []): array
    {
        $defaultPdfOptions = [
            'orientation' => 'portrait',
            'paper' => 'a4',
            'margin' => '15mm',
        ];

        $options = array_merge($defaultPdfOptions, $pdfOptions);

        // Generate PDF
        $pdf = Pdf::loadView($template, $data)
            ->setPaper($options['paper'], $options['orientation']);

        if (isset($options['margin'])) {
            $pdf->setOption('margin', $options['margin']);
        }

        // Generate filename and store
        $filename = $this->generateReportFilename($data['title']);
        $path = "reports/{$filename}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'size' => Storage::disk('local')->size($path),
            'url' => Storage::disk('local')->url($path),
            'generated_at' => $data['generated_at'],
            'data' => $data,
        ];
    }

    private function getTreeIndividuals(Tree $tree, int $generations): Collection
    {
        return $tree->individuals()
            ->with(['parents', 'children', 'spouses'])
            ->get()
            ->map(function ($individual) {
                return $this->privacyService->applyPrivacyFilter($individual);
            });
    }

    private function buildChartStructure(Collection $individuals, array $config): array
    {
        // Build hierarchical chart structure
        $chart = [
            'nodes' => [],
            'connections' => [],
            'layout' => $config['style'],
        ];

        foreach ($individuals as $individual) {
            $chart['nodes'][] = [
                'id' => $individual['id'],
                'name' => "{$individual['first_name']} {$individual['last_name']}",
                'birth_year' => $individual['birth_year'],
                'death_year' => $individual['death_year'],
                'gender' => $individual['sex'],
                'generation' => $this->calculateGenerationLevel($individual),
            ];
        }

        return $chart;
    }

    private function getAncestors(Individual $individual, int $generations): Collection
    {
        // This would integrate with the graph service to get ancestors
        return collect($this->graphService->findAncestors($individual->id, $generations));
    }

    private function getSiblings(Collection $ancestors): array
    {
        // Get siblings for each ancestor
        $siblings = [];

        foreach ($ancestors as $ancestor) {
            if (isset($ancestor['parents'])) {
                // Find other children of the same parents
                $siblings[$ancestor['id']] = $this->findSiblingsOfIndividual($ancestor);
            }
        }

        return $siblings;
    }

    private function calculateDescendantStatistics(array $descendants): array
    {
        if (is_array($descendants) && isset($descendants[0]) && ! is_array($descendants[0])) {
            // Flat array
            $total = count($descendants);
        } else {
            // Grouped by generation
            $total = array_sum(array_map('count', $descendants));
        }

        return [
            'total_descendants' => $total,
            'generations' => is_array($descendants) && isset($descendants[0]) && is_array($descendants[0])
                ? count($descendants) : 0,
            'average_per_generation' => $total > 0 ? round($total / max(1, count($descendants))) : 0,
        ];
    }

    private function calculateDemographics(Tree $tree): array
    {
        $individuals = $tree->individuals;

        return [
            'gender_distribution' => [
                'male' => $individuals->where('sex', 'M')->count(),
                'female' => $individuals->where('sex', 'F')->count(),
                'unknown' => $individuals->whereNotIn('sex', ['M', 'F'])->count(),
            ],
            'birth_year_range' => [
                'earliest' => $individuals->whereNotNull('birth_year')->min('birth_year'),
                'latest' => $individuals->whereNotNull('birth_year')->max('birth_year'),
            ],
            'completeness' => $this->calculateDataCompleteness($individuals),
        ];
    }

    private function generateTimelineData(Tree $tree): array
    {
        $events = [];

        foreach ($tree->individuals as $individual) {
            if ($individual->birth_year) {
                $events[] = [
                    'year' => $individual->birth_year,
                    'type' => 'birth',
                    'individual' => "{$individual->first_name} {$individual->last_name}",
                    'description' => "Birth of {$individual->first_name}",
                ];
            }

            if ($individual->death_year) {
                $events[] = [
                    'year' => $individual->death_year,
                    'type' => 'death',
                    'individual' => "{$individual->first_name} {$individual->last_name}",
                    'description' => "Death of {$individual->first_name}",
                ];
            }
        }

        return collect($events)->sortBy('year')->values()->toArray();
    }

    private function buildBookChapters(Tree $tree, array $config): array
    {
        $chapters = [];

        foreach ($config['chapters'] as $chapterType) {
            switch ($chapterType) {
                case 'introduction':
                    $chapters['introduction'] = $this->buildIntroductionChapter($tree);
                    break;
                case 'family_tree':
                    $chapters['family_tree'] = $this->buildFamilyTreeChapter($tree);
                    break;
                case 'individuals':
                    $chapters['individuals'] = $this->buildIndividualsChapter($tree);
                    break;
                case 'timeline':
                    $chapters['timeline'] = $this->buildTimelineChapter($tree);
                    break;
                case 'statistics':
                    $chapters['statistics'] = $this->buildStatisticsChapter($tree);
                    break;
                case 'appendix':
                    $chapters['appendix'] = $this->buildAppendixChapter($tree);
                    break;
            }
        }

        return $chapters;
    }

    private function buildIntroductionChapter(Tree $tree): array
    {
        return [
            'title' => 'Introduction',
            'content' => "This family history book contains the genealogical research for the {$tree->name} family tree.",
            'tree_overview' => $this->graphService->calculateTreeStatistics($tree->id),
        ];
    }

    private function buildFamilyTreeChapter(Tree $tree): array
    {
        return [
            'title' => 'Family Tree',
            'chart_data' => $this->generateFamilyTreeChart($tree, ['format' => 'data']),
            'description' => 'Visual representation of the family relationships',
        ];
    }

    private function buildIndividualsChapter(Tree $tree): array
    {
        $individuals = $tree->individuals()
            ->orderBy('birth_year')
            ->get()
            ->map(function ($individual) {
                return $this->privacyService->applyPrivacyFilter($individual);
            });

        return [
            'title' => 'Individual Profiles',
            'individuals' => $individuals,
            'total_count' => $individuals->count(),
        ];
    }

    private function buildTimelineChapter(Tree $tree): array
    {
        return [
            'title' => 'Family Timeline',
            'timeline_data' => $this->generateTimelineData($tree),
            'description' => 'Chronological view of family events',
        ];
    }

    private function buildStatisticsChapter(Tree $tree): array
    {
        return [
            'title' => 'Family Statistics',
            'statistics' => $this->graphService->calculateTreeStatistics($tree->id),
            'demographics' => $this->calculateDemographics($tree),
            'surname_analysis' => $this->graphService->analyzeSurnameDistribution($tree->id),
        ];
    }

    private function buildAppendixChapter(Tree $tree): array
    {
        return [
            'title' => 'Appendix',
            'sources' => [], // Would include sources if implemented
            'notes' => [],   // Would include notes if implemented
            'technical_info' => [
                'generated_date' => now()->toDateString(),
                'software' => 'LEG - Lineage Exploration & Genealogy',
                'version' => config('app.version', '1.0.0'),
            ],
        ];
    }

    private function generateTableOfContents(Tree $tree, array $config): array
    {
        $toc = [];
        $pageNumber = 1;

        foreach ($config['chapters'] as $chapter) {
            $toc[] = [
                'title' => ucwords(str_replace('_', ' ', $chapter)),
                'page' => $pageNumber,
            ];
            $pageNumber += $this->estimateChapterPages($chapter, $tree);
        }

        return $toc;
    }

    private function estimateChapterPages(string $chapter, Tree $tree): int
    {
        return match ($chapter) {
            'introduction' => 2,
            'family_tree' => 3,
            'individuals' => ceil($tree->individuals()->count() / 10),
            'timeline' => 2,
            'statistics' => 3,
            'appendix' => 2,
            default => 1
        };
    }

    private function calculateDataCompleteness(Collection $individuals): float
    {
        $fields = ['first_name', 'last_name', 'birth_date', 'death_date', 'birth_location', 'death_location'];
        $totalFields = $individuals->count() * count($fields);

        if ($totalFields === 0) {
            return 0.0;
        }

        $filledFields = 0;
        foreach ($individuals as $individual) {
            foreach ($fields as $field) {
                if (! empty($individual->$field)) {
                    $filledFields++;
                }
            }
        }

        return round(($filledFields / $totalFields) * 100, 1);
    }

    private function calculateGenerationLevel(array $individual): int
    {
        // This would calculate the generation level based on relationships
        return 0; // Placeholder
    }

    private function findSiblingsOfIndividual(array $individual): array
    {
        // Find siblings based on shared parents
        return []; // Placeholder
    }

    private function generateReportFilename(string $title): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "{$slug}_{$timestamp}";
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\Imports\GoogleWorldCupHtmlParser;
use App\Services\Imports\WorldCupFixtureImportService;
use Illuminate\Console\Command;

class ImportWorldCupMatchesFromHtmlCommand extends Command
{
    protected $signature = 'mundial:import-matches-html {file?} {--tournament_id=} {--dry-run}';

    protected $description = 'Import World Cup 2026 match schedule from a saved Google HTML file.';

    public function handle(GoogleWorldCupHtmlParser $parser, WorldCupFixtureImportService $importer): int
    {
        $file = (string) ($this->argument('file') ?? resource_path('matches.html'));
        $tournamentId = (int) $this->option('tournament_id');

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        if ($tournamentId < 1 || ! Tournament::query()->whereKey($tournamentId)->exists()) {
            $this->error('A valid --tournament_id option is required.');

            return self::FAILURE;
        }

        $fixtures = $parser->parse((string) file_get_contents($file));

        $this->info('Parsed fixtures: '.count($fixtures));
        $this->table(
            ['match_number', 'stage', 'group_name', 'home', 'away', 'starts_at', 'stadium'],
            collect($fixtures)->take(20)->map(fn (array $fixture): array => [
                $fixture['match_number'] ?? '-',
                $fixture['stage'] ?? '-',
                $fixture['group_name'] ?? '-',
                $fixture['home_name'] ?? $fixture['home_placeholder'] ?? '-',
                $fixture['away_name'] ?? $fixture['away_placeholder'] ?? '-',
                $fixture['starts_at'] ?? '-',
                $fixture['stadium'] ?? '-',
            ])->all(),
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run enabled. No records were written.');
            $this->line('created: 0');
            $this->line('updated: 0');
            $this->line('skipped: 0');

            return self::SUCCESS;
        }

        $summary = $importer->import($tournamentId, $fixtures);

        $this->info('Import summary');
        $this->line("created: {$summary['created']}");
        $this->line("updated: {$summary['updated']}");
        $this->line("skipped: {$summary['skipped']}");

        return self::SUCCESS;
    }
}

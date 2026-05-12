<?php

namespace App\Services\Imports;

use Carbon\CarbonImmutable;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Throwable;

class GoogleWorldCupHtmlParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $html): array
    {
        $fixtures = [];

        foreach ($this->htmlFragments($html) as $fragment) {
            foreach ($this->extractFixtures($fragment) as $fixture) {
                $key = implode('|', [
                    $fixture['stage'],
                    $fixture['starts_at'],
                    $fixture['home_name'] ?? $fixture['home_placeholder'],
                    $fixture['away_name'] ?? $fixture['away_placeholder'],
                ]);

                $fixtures[$key] = $fixture;
            }
        }

        return array_values($fixtures);
    }

    /**
     * @return array<int, string>
     */
    private function htmlFragments(string $html): array
    {
        return array_values(array_filter([
            $html,
            ...$this->decodeGoogleScriptHtml($html),
        ]));
    }

    /**
     * @return array<int, string>
     */
    private function decodeGoogleScriptHtml(string $html): array
    {
        $fragments = [];
        $offset = 0;

        while (($start = strpos($html, 'window.jsl.dh(', $offset)) !== false) {
            $comma = strpos($html, ',', $start);
            $quote = $comma === false ? false : strpos($html, '"', $comma);

            if ($quote === false) {
                $offset = $start + 1;

                continue;
            }

            [$encoded, $nextOffset] = $this->readJavascriptString($html, $quote);
            $offset = $nextOffset;

            if ($encoded !== '') {
                $fragments[] = stripcslashes($encoded);
            }
        }

        return $fragments;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function readJavascriptString(string $html, int $quotePosition): array
    {
        $value = '';
        $escaped = false;

        for ($index = $quotePosition + 1, $length = strlen($html); $index < $length; $index++) {
            $char = $html[$index];

            if ($escaped) {
                $value .= '\\'.$char;
                $escaped = false;

                continue;
            }

            if ($char === '\\') {
                $escaped = true;

                continue;
            }

            if ($char === '"') {
                return [$value, $index + 1];
            }

            $value .= $char;
        }

        return [$value, strlen($html)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractFixtures(string $html): array
    {
        $document = new DOMDocument;

        try {
            libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } catch (Throwable) {
            return [];
        } finally {
            libxml_clear_errors();
        }

        $xpath = new DOMXPath($document);
        $tiles = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " liveresults-sports-immersive__match-tile ")]');
        $fixtures = [];

        foreach ($tiles ?: [] as $tile) {
            if (! $tile instanceof DOMElement) {
                continue;
            }

            $fixture = $this->fixtureFromTile($tile, $xpath);

            if ($fixture !== null) {
                $fixtures[] = $fixture;
            }
        }

        return $fixtures;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fixtureFromTile(DOMElement $tile, DOMXPath $xpath): ?array
    {
        $rawText = $this->normalizeText($tile->textContent);

        if (! preg_match('/(?<day>\d{1,2})\/(?<month>\d{1,2})\s*,?\s*(?<hour>\d{1,2}):(?<minute>\d{2})/u', $rawText, $dateMatch)) {
            return null;
        }

        $participants = $this->participantsForTile($tile, $xpath);

        if (count($participants) < 2) {
            return null;
        }

        $startsAt = CarbonImmutable::create(
            year: 2026,
            month: (int) $dateMatch['month'],
            day: (int) $dateMatch['day'],
            hour: (int) $dateMatch['hour'],
            minute: (int) $dateMatch['minute'],
            second: 0,
            timezone: 'Asia/Tashkent',
        )->utc();

        $matchNumber = $this->matchNumber($rawText);
        $stage = $this->stage($rawText, $matchNumber);

        if ($stage === null) {
            return null;
        }

        [$homeName, $homePlaceholder] = $this->participantValues($participants[0]);
        [$awayName, $awayPlaceholder] = $this->participantValues($participants[1]);

        return [
            'match_number' => $matchNumber,
            'stage' => $stage,
            'group_name' => $this->groupName($rawText),
            'home_name' => $homeName,
            'away_name' => $awayName,
            'home_placeholder' => $homePlaceholder,
            'away_placeholder' => $awayPlaceholder,
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'timezone' => 'UTC',
            'stadium' => null,
            'city' => null,
            'raw_text' => $rawText,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function participantsForTile(DOMElement $tile, DOMXPath $xpath): array
    {
        $nodes = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " xNfnlf ")]', $tile);

        if ($nodes !== false && $nodes->length >= 2) {
            return $this->nodeTexts($nodes, unique: true);
        }

        $nodes = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " ellipsisize ")]', $tile);

        return $nodes === false ? [] : $this->nodeTexts($nodes, unique: false);
    }

    /**
     * @return array<int, string>
     */
    private function nodeTexts(iterable $nodes, bool $unique): array
    {
        $values = [];

        foreach ($nodes as $node) {
            if (! $node instanceof DOMNode) {
                continue;
            }

            $value = $this->normalizeParticipant($node->textContent);

            if ($value !== '' && (! $unique || ! in_array($value, $values, true))) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function participantValues(string $value): array
    {
        return $this->isPlaceholder($value)
            ? [null, $value]
            : [$value, null];
    }

    private function isPlaceholder(string $value): bool
    {
        return (bool) preg_match('/^(Н\/Д|N\/A|TBD|To be decided|Winner .+|Runner-up .+|\d[A-H])$/iu', $value);
    }

    private function matchNumber(string $text): ?int
    {
        return preg_match('/(?:match\s*(?:number|#)?|match#)\s*(\d{1,3})/iu', $text, $match)
            ? (int) $match[1]
            : null;
    }

    private function stage(string $text, ?int $matchNumber): ?string
    {
        $lower = mb_strtolower($text);

        return match (true) {
            str_contains($lower, "3-o'rin"), str_contains($lower, 'thirdplace') => 'third_place',
            str_contains($lower, '1/2final'), str_contains($lower, 'semi') => 'semi_final',
            str_contains($lower, '1/4final'), str_contains($lower, 'quarter') => 'quarter_final',
            str_contains($lower, '1/8final'), str_contains($lower, 'roundof16') => 'round_16',
            str_contains($lower, '1/16final'), str_contains($lower, 'roundof32') => 'round_32',
            preg_match('/(^|[^a-z])final([^a-z]|$)/iu', $text) === 1 => 'final',
            str_contains($lower, 'guruh'), str_contains($lower, 'group') => 'group',
            $matchNumber !== null && $matchNumber <= 72 => 'group',
            default => null,
        };
    }

    private function groupName(string $text): ?string
    {
        return preg_match('/\b([A-L])\s*(?:guruhi|group)/iu', $text, $match)
            ? mb_strtoupper($match[1])
            : null;
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', '', $text));
    }

    private function normalizeParticipant(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        return preg_match('/^(.+)\1$/u', $text, $match) ? trim($match[1]) : $text;
    }
}

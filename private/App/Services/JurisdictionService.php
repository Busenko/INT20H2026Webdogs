<?php
declare(strict_types=1);

namespace App\Services;

class JurisdictionService
{
    private array $counties = [];
    private static array $cache = [];

    public function __construct()
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Counties.json';
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            $features = $data['features'] ?? [];
            
            foreach ($features as $f) {
                $this->counties[] = [
                    'name'  => $f['properties']['NAME'] ?? null,
                    'type'  => $f['geometry']['type'],
                    'coords'=> $f['geometry']['coordinates'],
                    'bbox'  => $this->calculateBBox($f['geometry']['coordinates'])
                ];
            }
        }
    }

    public function prepareCacheForCoordinates(array $coordinateList): void
    {
        echo "Pre-calculating for " . count($coordinateList) . " points..." . PHP_EOL;
        foreach ($coordinateList as $point) {
            $lat = (float)$point['lat'];
            $lon = (float)$point['lon'];
            $key = "{$lat}_{$lon}";
            if (!isset(self::$cache[$key])) {
                self::$cache[$key] = $this->findJurisdictionLocally($lat, $lon);
            }
        }
    }

    public function getJurisdictionByCoordinates(float $lat, float $lon): ?string
    {
        $key = "{$lat}_{$lon}";
        return self::$cache[$key] ?? $this->findJurisdictionLocally($lat, $lon);
    }

    private function findJurisdictionLocally(float $lat, float $lon): ?string
    {
        foreach ($this->counties as $county) {
            // Перевірка BBox для швидкості
            if ($lon < $county['bbox']['minX'] || $lon > $county['bbox']['maxX'] ||
                $lat < $county['bbox']['minY'] || $lat > $county['bbox']['maxY']) continue;

            if ($county['type'] === 'Polygon') {
                if ($this->isPointInPolygon($lon, $lat, $county['coords'][0])) return $county['name'];
            } else {
                foreach ($county['coords'] as $p) {
                    if ($this->isPointInPolygon($lon, $lat, $p[0])) return $county['name'];
                }
            }
        }
        return null;
    }

    private function calculateBBox(array $coords): array 
    {
        $flat = is_array($coords[0][0][0]) ? array_merge(...array_merge(...$coords)) : array_merge(...$coords);
        return [
            'minX' => min(array_column($flat, 0)), 'maxX' => max(array_column($flat, 0)),
            'minY' => min(array_column($flat, 1)), 'maxY' => max(array_column($flat, 1))
        ];
    }

    private function isPointInPolygon(float $x, float $y, array $polygon): bool 
    {
        $inside = false;
        $count = count($polygon);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            if ((($polygon[$i][1] > $y) != ($polygon[$j][1] > $y)) &&
                ($x < ($polygon[$j][0] - $polygon[$i][0]) * ($y - $polygon[$i][1]) / ($polygon[$j][1] - $polygon[$i][1]) + $polygon[$i][0])) {
                $inside = !$inside;
            }
        }
        return $inside;
    }
}
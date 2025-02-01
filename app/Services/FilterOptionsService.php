<?php

namespace App\Services;

use App\Models\BadanUsaha;
use App\Models\Division;
use App\Models\Region;
use App\Models\Cluster;

class FilterOptionsService
{
    public function getOptionsByFilterType(string $filterType)
    {
        switch ($filterType) {
            case '\App\Models\BadanUsaha':
                return BadanUsaha::orderBy('name')->pluck('name', 'id');

            case '\App\Models\Division':
                return Division::with(['badanusaha'])
                    ->orderBy('badanusaha_id')
                    ->get()
                    ->mapWithKeys(function ($division) {
                        $badanusahaName = $division->badanusaha ? $division->badanusaha->name : 'Tidak ada badan usaha';
                        return [$division->id => "[{$badanusahaName}] {$division->name}"];
                    });

            case '\App\Models\Region':
                return Region::with(['badanusaha'])
                    ->orderBy('badanusaha_id')
                    ->get()
                    ->mapWithKeys(function ($region) {
                        $badanusahaName = $region->badanusaha ? $region->badanusaha->name : 'Tidak ada badan usaha';
                        $divisiName = $region->divisi ? $region->divisi->name : 'Tidak ada divisi';
                        return [$region->id => "[{$badanusahaName}/{$divisiName}] {$region->name}"];
                    });

            case '\App\Models\Cluster':
                return Cluster::with(['badanusaha', 'divisi', 'region'])
                    ->orderBy('badanusaha_id')
                    ->get()
                    ->mapWithKeys(function ($cluster) {
                        $badanusahaName = $cluster->badanusaha ? $cluster->badanusaha->name : 'Tidak ada badan usaha';
                        $divisiName = $cluster->divisi ? $cluster->divisi->name : 'Tidak ada divisi';
                        $regionName = $cluster->region ? $cluster->region->name : 'Tidak ada region';
                        return [$cluster->id => " [{$badanusahaName}/{$divisiName}] {$regionName} - {$cluster->name}"];
                    });

            default:
                return [];
        }
    }
}

<?php

namespace App\Services;

use App\Models\BadanUsaha;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class OrganizationalStructureService
{
    private function getFilteredOptions($model, $filterType, $filterData, $relasi = [], $badanUsahaId = null, $divisiId = null, $regionId = null)
    {
        if ($filterType && ! empty($filterData)) {
            $filterData = is_string($filterData) ? json_decode($filterData) : $filterData;

            if (! is_array($filterData)) {
                return [];
            }

            if (class_exists($filterType)) {
                $query = $model::query();

                if ($filterType === 'App\Models\BadanUsaha' && $model === \App\Models\Division::class) {
                    $divisions = \App\Models\Division::whereIn('badanusaha_id', $filterData);
                    if ($badanUsahaId) {
                        $divisions->where('badanusaha_id', $badanUsahaId);
                    }
                    $divisions = $divisions->with('badanusaha')->get();

                    return [
                        'badanusahas' => $divisions->pluck('badanusaha.name', 'badanusaha_id')->toArray(),
                        'divisions' => $divisions->pluck('name', 'id')->toArray(),
                    ];
                }

                if ($filterType === 'App\Models\BadanUsaha') {
                    $badanUsahas = \App\Models\BadanUsaha::whereIn('id', $filterData)->get();

                    return $badanUsahas->pluck('name', 'id');
                }

                if ($filterType === 'App\Models\Division') {
                    $divisions = \App\Models\Division::whereIn('id', $filterData);
                    if ($badanUsahaId) {
                        $divisions->where('badanusaha_id', $badanUsahaId);
                    }
                    $divisions = $divisions->with('badanusaha')->get();

                    return [
                        'badanusahas' => $divisions->pluck('badanusaha.name', 'badanusaha_id')->toArray(),
                        'divisions' => $divisions->pluck('name', 'id')->toArray(),
                    ];
                }

                if ($filterType === 'App\Models\Region') {
                    $regions = \App\Models\Region::whereIn('id', $filterData);
                    if ($badanUsahaId) {
                        $regions->where('badanusaha_id', $badanUsahaId);
                    }
                    if ($divisiId) {
                        $regions->where('divisi_id', $divisiId);
                    }
                    $regions = $regions->with(['badanusaha', 'divisi'])->get();

                    return [
                        'badanusahas' => $regions->pluck('badanusaha.name', 'badanusaha_id')->toArray(),
                        'divisions' => $regions->pluck('divisi.name', 'divisi_id')->toArray(),
                        'regions' => $regions->pluck('name', 'id'),
                    ];
                }

                if ($filterType === 'App\Models\Cluster') {
                    $clusters = \App\Models\Cluster::whereIn('id', $filterData);
                    if ($badanUsahaId) {
                        $clusters->where('badanusaha_id', $badanUsahaId);
                    }
                    if ($divisiId) {
                        $clusters->where('divisi_id', $divisiId);
                    }
                    if ($regionId) {
                        $clusters->where('region_id', $regionId);
                    }
                    $clusters = $clusters->with(['badanusaha', 'divisi', 'region'])->get();

                    return [
                        'badanusahas' => $clusters->pluck('badanusaha.name', 'badanusaha_id')->toArray(),
                        'divisions' => $clusters->pluck('divisi.name', 'divisi_id')->toArray(),
                        'regions' => $clusters->pluck('region.name', 'region_id')->toArray(),
                        'clusters' => $clusters->pluck('name', 'id'),
                    ];
                }

                $query->whereIn('id', $filterData);
                foreach ($relasi as $relation => $relationValue) {
                    $relationValue = is_string($relationValue) ? json_decode($relationValue) : $relationValue;
                    if (is_array($relationValue)) {
                        $query->whereHas($relation, function ($query) use ($relationValue) {
                            $query->whereIn('id', $relationValue);
                        });
                    }
                }

                return $query->orderBy('name')->pluck('name', 'id');
            }
        }

        return $model::orderBy('name')->pluck('name', 'id');
    }

    public function getBadanUsahaOptions()
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role->filter_type === 'App\Models\BadanUsaha') {
            return $this->getFilteredOptions(BadanUsaha::class, $role->filter_type, $role->filter_data);
        }

        if ($role->filter_type === 'App\Models\Division') {
            return $this->getFilteredOptions(BadanUsaha::class, $role->filter_type, $role->filter_data)['badanusahas'];
        }

        if ($role->filter_type === 'App\Models\Region') {
            return $this->getFilteredOptions(BadanUsaha::class, $role->filter_type, $role->filter_data)['badanusahas'];
        }

        if ($role->filter_type === 'App\Models\Cluster') {
            return $this->getFilteredOptions(BadanUsaha::class, $role->filter_type, $role->filter_data)['badanusahas'];
        }

        return BadanUsaha::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getDivisiOptions($badanUsahaId)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role->filter_type === 'App\Models\BadanUsaha') {
            return $this->getFilteredOptions(Division::class, $role->filter_type, $role->filter_data, [], $badanUsahaId)['divisions'];
        }

        if ($role->filter_type === 'App\Models\Division') {
            return $this->getFilteredOptions(Division::class, $role->filter_type, $role->filter_data, [], $badanUsahaId)['divisions'];
        }

        if ($role->filter_type === 'App\Models\Region') {
            return $this->getFilteredOptions(Division::class, $role->filter_type, $role->filter_data, [], $badanUsahaId)['divisions'];
        }

        if ($role->filter_type === 'App\Models\Cluster') {
            return $this->getFilteredOptions(Division::class, $role->filter_type, $role->filter_data, [], $badanUsahaId)['divisions'];
        }

        return Division::where('badanusaha_id', $badanUsahaId)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getRegionOptions($divisiId)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role->filter_type === 'App\Models\Region') {
            return $this->getFilteredOptions(Region::class, $role->filter_type, $role->filter_data, [], null, $divisiId)['regions'];
        }

        if ($role->filter_type === 'App\Models\Cluster') {
            return $this->getFilteredOptions(Region::class, $role->filter_type, $role->filter_data, [], null, $divisiId)['regions'];
        }

        return Region::where('divisi_id', $divisiId)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getClusterOptions($regionId)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role->filter_type === 'App\Models\Cluster') {
            return $this->getFilteredOptions(Cluster::class, $role->filter_type, $role->filter_data, [], null, null, $regionId)['clusters'];
        }

        return Cluster::where('region_id', $regionId)->orderBy('name')->pluck('name', 'id')->toArray();
    }
}

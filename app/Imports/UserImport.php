<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Models\Region;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\BadanUsaha;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Check if user already exists
            $user = User::where('username', strtolower($row['username']))->first();
            if ($user) {
                // User exists, update if necessary
                $this->updateUser($user, $row);
                return null; // Skip creation
            } else {
                // User does not exist, create new user
                return $this->createUser($row);
            }
        } catch (\Exception $e) {
            // Log the error and skip the row
            error_log("Error processing row for username {$row['username']}: " . $e->getMessage());
            return null; // Skip this row
        }
    }

    private function updateUser($user, $row)
    {
        $badanusaha_id = $this->getBadanUsahaId($row['badan_usaha']);
        $divisi_id = $this->getDivisionId($row['divisi'], $badanusaha_id);
        $region_id = strtoupper($row['role']) === 'TM' ? null : $this->getRegionId($row['region'], $divisi_id, $badanusaha_id);
        $cluster_id = $this->getClusterId($row['cluster']);
        $tm_id = $this->getTmId($row['tm']);

        $user->update([
            'nama_lengkap' => strtoupper($row['nama_lengkap']),
            'username' => strtolower($row['username']),
            'role_id' => $this->getRoleId($row['role']),
            'badanusaha_id' => $badanusaha_id,
            'divisi_id' => $divisi_id,
            'region_id' => $region_id,
            'cluster_id' => $cluster_id,
            'tm_id' => $tm_id,
        ]);
    }

    private function createUser($row)
    {
        $badanusaha_id = $this->getBadanUsahaId($row['badan_usaha']);
        $divisi_id = $this->getDivisionId($row['divisi'], $badanusaha_id);
        $region_id = strtoupper($row['role']) === 'TM' ? null : $this->getRegionId($row['region'], $divisi_id, $badanusaha_id);
        $cluster_id = $this->getClusterId($row['cluster']);
        $tm_id = $this->getTmId($row['tm']);

        return new User([
            'nama_lengkap' => strtoupper($row['nama_lengkap']),
            'username' => strtolower($row['username']),
            'role_id' => $this->getRoleId($row['role']),
            'badanusaha_id' => $badanusaha_id,
            'divisi_id' => $divisi_id,
            'region_id' => $region_id,
            'cluster_id' => $cluster_id,
            'tm_id' => $tm_id,
            'password' => $row['password'] ? bcrypt($row['password']) : bcrypt('complete123'),
        ]);
    }

    private function getBadanUsahaId($name)
    {
        return BadanUsaha::where('name', preg_replace('/\s+/', '', $name))->first()->id ?? null;
    }

    private function getDivisionId($name, $badanusaha_id)
    {
        return Division::where('name', preg_replace('/\s+/', '', $name))
            ->where('badanusaha_id', $badanusaha_id)
            ->first()->id ?? null;
    }

    private function getRegionId($name, $divisi_id, $badanusaha_id)
    {
        return Region::where('name', preg_replace('/\s+/', '', $name))
            ->where('divisi_id', $divisi_id)
            ->where('badanusaha_id', $badanusaha_id)
            ->first()->id ?? null;
    }

    private function getClusterId($name)
    {
        return Cluster::where('name', preg_replace('/\s+/', '', $name))->first()->id ?? null;
    }

    private function getRoleId($name)
    {
        return Role::where('name', preg_replace('/\s+/', '', $name))->first()->id ?? null;
    }

    private function getTmId($name)
    {
        return User::where('nama_lengkap', $name)->first()->id ?? null;
    }
}

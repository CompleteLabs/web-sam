<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Noo;
use App\Models\Outlet;
use App\Models\Visit;
use App\Models\VisitNoo;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{

    /**
     * Visit - Monitoring visiting sales ✅
     */
    public function monitor(Request $request)
    {
        try {
            $user = Auth::user();
            $date = $request->date ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');

            $visitQuery = Visit::with([
                'outlet.badanusaha',
                'outlet.region',
                'outlet.divisi',
                'outlet.cluster',
                'user.badanusaha',
                'user.region',
                'user.divisi',
                'user.cluster',
                'user.role',
            ]);

            // Logika untuk setiap role
            if ($user->id == 2 && $user->role_id == 1) {
                // Robby (GM ZTE)
                $visitQuery->whereHas('user', function ($query) {
                    $query->where('divisi_id', '8')
                        ->whereIn('region_id', [63, 64, 66, 67, 68, 78, 79, 80, 81]);
                });
            } elseif ($user->id == 689 && $user->role_id == 1) {
                // Hendra Setia (GM Techno)
                $visitQuery->whereHas('user', function ($query) {
                    $query->where('divisi_id', '11');
                });
            } elseif ($user->role_id == 1 || $user->role_id == 9) {
                // ASM || RKAM
                $visitQuery->whereHas('user', function ($query) {
                    $query->where('tm_id', Auth::user()->id);
                });
            } elseif ($user->role_id == 6) {
                // COO
                // Tidak ada filter khusus untuk COO
            } elseif ($user->role_id == 8) {
                // CSO
                $visitQuery->whereHas('outlet', function ($query) {
                    $query->whereIn('divisi_id', [4, 8, 11]);
                });
            } elseif ($user->role_id == 11) {
                // CSO FAST EV
                $visitQuery->whereHas('outlet', function ($query) {
                    $query->where('divisi_id', 7);
                });
            } else {
                // Default: Semua user dengan region_id yang sesuai
                $visitQuery->whereHas('user', function ($query) {
                    $query->where('region_id', Auth::user()->region_id);
                });
            }

            $visitQuery->whereDate('tanggal_visit', $date)
                ->latest();

            $visit = $visitQuery->get();

            return ResponseFormatter::success(
                $visit->map->formatForAPI(),
                'Fetch monitoring visit success'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    /**
     * Visit - Fetch data visit ✅
     */
    public function fetch(Request $request)
    {
        try {
            $date = $request->date ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');

            $visit = Visit::with([
                'outlet.badanusaha',
                'outlet.region',
                'outlet.divisi',
                'outlet.cluster',
                'user.badanusaha',
                'user.region',
                'user.divisi',
                'user.cluster',
                'user.role'
            ])
                ->where('user_id', Auth::user()->id)
                ->whereDate('tanggal_visit', $date)
                ->latest()
                ->get();

            return ResponseFormatter::success(
                $visit->map->formatForAPI(),
                'Fetch visit success'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], 'Error: ' . $error->getMessage(), 500);
        }
    }

    public function check(Request $request)
    {
        try {
            $request->validate([
                'kode_outlet' => ['required'],
            ]);

            $outlet = Outlet::where('kode_outlet', $request->kode_outlet)
                ->where('divisi_id', Auth::user()->divisi_id)
                ->first();

            if (!$outlet) {
                return ResponseFormatter::error([
                    'message' => 'Kode outlet tidak ditemukan di divisi ini.',
                ], 'Outlet tidak valid', 400);
            }

            $lastDataVisit = Visit::whereDate('tanggal_visit', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->latest()
                ->first();

            if ($request->check_in) {
                if ($lastDataVisit) {
                    if ($lastDataVisit->check_out_time) {
                        return ResponseFormatter::success(null, 'Ok');
                    } else {
                        return ResponseFormatter::error([
                            'message' => 'error',
                        ], "Belum check out dari outlet " . $lastDataVisit->outlet->kode_outlet, 400);
                    }
                } else {
                    return ResponseFormatter::success(null, 'Ok');
                }
            }

            return ResponseFormatter::error([
                'message' => 'Periksa kembali status kunjungan Anda.',
            ], 'Invalid request', 400);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function submit(Request $request)
    {
        try {
            $checkIn = $request->latlong_in;
            $checkOut = $request->latlong_out;

            if ($checkIn) {
                $request->validate([
                    'kode_outlet' => ['required'],
                    'picture_visit' => ['required', 'mimes:jpg,jpeg,png'],
                    'latlong_in' => ['required', 'string'],
                    'tipe_visit' => ['required'],
                ]);

                $outlet = Outlet::where('kode_outlet', $request->kode_outlet)
                    ->where('divisi_id', Auth::user()->divisi_id)
                    ->first();

                if (!$outlet) {
                    return ResponseFormatter::error([
                        'message' => 'Outlet tidak ditemukan atau tidak sesuai dengan divisi Anda.'
                    ], 'Invalid Outlet', 404);
                }

                $imageName = date('Y-m-d') . '-' . Auth::user()->username . '-' . 'IN-' . Carbon::parse(time())->getPreciseTimestamp(3) . '.' . $request->picture_visit->extension();
                $request->picture_visit->move(storage_path('app/public/'), $imageName);

                $visit = Visit::create([
                    'tanggal_visit' => date('Y-m-d'),
                    'user_id' => Auth::user()->id,
                    'outlet_id' => $outlet->id,
                    'tipe_visit' => $request->tipe_visit,
                    'latlong_in' => $request->latlong_in,
                    'check_in_time' => Carbon::now(),
                    'picture_visit_in' => $imageName,
                ]);

                return ResponseFormatter::success([
                    'visit' => $visit
                ], 'Berhasil check-in');
            }

            if ($checkOut) {
                $lastDataVisit = Visit::whereDate('tanggal_visit', date('Y-m-d'))
                    ->where('user_id', Auth::user()->id)
                    ->latest()
                    ->first();

                if ($lastDataVisit != null) {
                    $request->validate([
                        'latlong_out' => ['required'],
                        'laporan_visit' => ['required'],
                        'picture_visit' => ['required', 'mimes:jpg,jpeg,png'],
                        'transaksi' => ['required'],
                    ]);

                    $awal = Carbon::parse($lastDataVisit->check_in_time);
                    $akhir = Carbon::now();
                    $durasi = $awal->diffInMinutes($akhir);

                    $imageName = date('Y-m-d') . '-' . Auth::user()->username . '-' . 'OUT-' . Carbon::now()->getPreciseTimestamp(3) . '.' . $request->picture_visit->extension();
                    $request->picture_visit->move(storage_path('app/public/'), $imageName);

                    $data = [
                        'tanggal_visit' => date('Y-m-d'),
                        'latlong_out' => $request->latlong_out,
                        'check_out_time' => now(),
                        'laporan_visit' => $request->laporan_visit,
                        'durasi_visit' => $durasi,
                        'picture_visit_out' => $imageName,
                        'transaksi' => $request->transaksi,
                    ];
                    $lastDataVisit->update($data);

                    return ResponseFormatter::success([
                        'visit' => $data
                    ], 'Berhasil check-out');
                } else {
                    return ResponseFormatter::error([
                        'message' => 'Tidak ada data kunjungan untuk check-out.'
                    ], 'No Visit Found', 404);
                }
            }

            return ResponseFormatter::error([
                'message' => 'Periksa kembali data yang Anda masukkan.'
            ], 'Invalid Input', 400);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }
}

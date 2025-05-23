<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Visit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
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
            if ($user->id == 2 && $user->role_id == 8) {
                // Robby (GM ZTE)
                $visitQuery->whereHas('user', function ($query) {
                    $query->where('divisi_id', '8')
                        ->whereIn('region_id', [63, 64, 66, 67, 68, 78, 79, 80, 81]);
                });
            } elseif ($user->id == 689 && $user->role_id == 8) {
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
                'message' => 'Maaf, terjadi kendala saat memproses permintaan Anda. Silakan coba lagi.',
            ], $error->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            $sortColumn = $request->input('sort_column', 'tanggal_visit');
            $sortDirection = $request->input('sort_direction', 'desc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $query = Visit::with([
                'outlet.badanusaha',
                'outlet.region',
                'outlet.divisi',
                'outlet.cluster',
                'user.badanusaha',
                'user.region',
                'user.divisi',
                'user.cluster',
                'user.role',
            ])->where('user_id', Auth::user()->id);

            // Search global (nama outlet, kode outlet)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('outlet', function ($qo) use ($search) {
                        $qo->where('nama_outlet', 'like', "%$search%")
                            ->orWhere('kode_outlet', 'like', "%$search%");
                    });
                });
            }

            // Filter dinamis: hanya month, date, tipe_visit
            if (! empty($filters)) {
                foreach ($filters as $key => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    if ($key === 'date') {
                        $query->whereDate('tanggal_visit', $value);
                    } elseif ($key === 'month') {
                        $query->whereMonth('tanggal_visit', $value);
                    } elseif ($key === 'tipe_visit') {
                        if (is_array($value)) {
                            $query->whereIn('tipe_visit', $value);
                        } else {
                            $query->where('tipe_visit', $value);
                        }
                    }
                }
            }

            // Sorting dinamis (whitelist kolom untuk keamanan)
            $allowedSorts = ['tanggal_visit', 'check_in_time', 'check_out_time', 'tipe_visit', 'durasi_visit'];
            if (! in_array($sortColumn, $allowedSorts)) {
                $sortColumn = 'tanggal_visit';
            }
            $query->orderBy($sortColumn, $sortDirection);

            $visit = $query->paginate($perPage, ['*'], 'page', $page);

            return ResponseFormatter::success(
                collect($visit->items())->map->formatForAPI(),
                'Data visit berhasil diambil',
                [
                    'current_page' => $visit->currentPage(),
                    'last_page' => $visit->lastPage(),
                    'total' => $visit->total(),
                    'per_page' => $visit->perPage(),
                ]
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Maaf, terjadi kendala saat mengambil data kunjungan. Silakan coba lagi.',
            ], 'Error: '.$error->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $visit = Visit::with([
                'outlet.badanusaha',
                'outlet.region',
                'outlet.divisi',
                'outlet.cluster',
                'user.badanusaha',
                'user.region',
                'user.divisi',
                'user.cluster',
                'user.role',
            ])->findOrFail($id);

            return ResponseFormatter::success(
                $visit->formatForAPI(),
                'Detail visit success'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Maaf, data visit tidak ditemukan.',
            ], $error->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'kode_outlet' => ['required'],
                'picture_visit' => ['required', 'mimes:jpg,jpeg,png'],
                'latlong_in' => ['required', 'string'],
                'tipe_visit' => ['required'],
            ]);

            $outlet = Outlet::where('kode_outlet', $request->kode_outlet)
                ->where('divisi_id', Auth::user()->divisi_id)
                ->first();

            if (! $outlet) {
                DB::rollBack();

                return ResponseFormatter::error([
                    'message' => 'Maaf, data outlet tidak ditemukan. Pastikan kode outlet dan divisi sudah benar.',
                ], 'Invalid Outlet', 404);
            }

            $imageName = date('Y-m-d').'-'.Auth::user()->username.'-'.'IN-'.Carbon::parse(time())->getPreciseTimestamp(3).'.'.$request->picture_visit->extension();
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

            DB::commit();

            return ResponseFormatter::success([
                'visit' => $visit,
            ], 'Berhasil check-in');
        } catch (Exception $error) {
            DB::rollBack();

            return ResponseFormatter::error([
                'message' => 'Maaf, terjadi kendala saat memproses permintaan Anda. Silakan coba lagi.',
            ], $error->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $visit = Visit::findOrFail($id);
            $request->validate([
                'latlong_out' => ['required'],
                'laporan_visit' => ['required'],
                'picture_visit' => ['required', 'mimes:jpg,jpeg,png'],
                'transaksi' => ['required'],
            ]);

            $awal = Carbon::parse($visit->check_in_time);
            $akhir = Carbon::now();
            $durasi = $awal->diffInMinutes($akhir);

            $imageName = date('Y-m-d').'-'.Auth::user()->username.'-'.'OUT-'.Carbon::now()->getPreciseTimestamp(3).'.'.$request->picture_visit->extension();
            $request->picture_visit->move(storage_path('app/public/'), $imageName);

            $visit->latlong_out = $request->latlong_out;
            $visit->check_out_time = now();
            $visit->laporan_visit = $request->laporan_visit;
            $visit->durasi_visit = $durasi;
            $visit->picture_visit_out = $imageName;
            $visit->transaksi = $request->transaksi;
            $visit->save();

            DB::commit();

            return ResponseFormatter::success($visit->formatForAPI(), 'Berhasil check-out');
        } catch (Exception $error) {
            DB::rollBack();

            return ResponseFormatter::error([
                'message' => 'Maaf, data visit tidak dapat diperbarui saat ini. Silakan coba lagi.',
            ], $error->getMessage(), 400);
        }
    }

    public function destroy($id)
    {
        try {
            $visit = Visit::findOrFail($id);
            $visit->delete();

            return ResponseFormatter::success(null, 'Delete visit success');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Maaf, data visit tidak dapat dihapus saat ini. Silakan coba lagi.',
            ], $error->getMessage(), 400);
        }
    }

    public function check(Request $request)
    {
        try {
            $request->validate([
                'kode_outlet' => ['required'],
            ]);

            // Cek visit aktif (belum checkout) di outlet manapun hari ini
            $visitAktif = Visit::whereDate('tanggal_visit', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->whereNull('check_out_time')
                ->first();
            if ($visitAktif) {
                // Jika visit aktif di outlet yang sama
                if ($visitAktif->outlet->kode_outlet == $request->kode_outlet) {
                    return ResponseFormatter::success([
                        'checked_in' => true,
                        'checked_out' => false,
                        'visit_id' => $visitAktif->id,
                        'outlet_aktif' => $visitAktif->outlet->kode_outlet,
                        'nama_outlet_aktif' => $visitAktif->outlet->nama_outlet,
                        'alamat_outlet_aktif' => $visitAktif->outlet->alamat,
                        'waktu_checkin' => $visitAktif->check_in_time ? $visitAktif->check_in_time->format('d-m-Y H:i') : null,
                    ], 'Anda sudah check-in di outlet ini hari ini (Outlet: '.$visitAktif->outlet->nama_outlet.', Alamat: '.$visitAktif->outlet->alamat.'). Silakan lakukan check-out sebelum membuat kunjungan baru.');
                } else {
                    return ResponseFormatter::error([
                        'checked_in' => true,
                        'checked_out' => false,
                        'visit_id' => $visitAktif->id,
                        'outlet_aktif' => $visitAktif->outlet->kode_outlet,
                        'nama_outlet_aktif' => $visitAktif->outlet->nama_outlet,
                        'alamat_outlet_aktif' => $visitAktif->outlet->alamat,
                        'waktu_checkin' => $visitAktif->check_in_time ? $visitAktif->check_in_time->format('d-m-Y H:i') : null,
                    ], 'Anda masih memiliki kunjungan yang belum selesai (belum check-out) di outlet lain (Outlet: '.$visitAktif->outlet->nama_outlet.', Alamat: '.$visitAktif->outlet->alamat.'). Silakan selesaikan (check-out) kunjungan sebelumnya sebelum melakukan check-in baru.');
                }
            }

            // Jika tidak ada visit aktif, cek status visit di outlet ini
            $outlet = Outlet::where('kode_outlet', $request->kode_outlet)
                ->where('divisi_id', Auth::user()->divisi_id)
                ->first();

            if (! $outlet) {
                return ResponseFormatter::error([
                    'message' => 'Maaf, data outlet tidak ditemukan. Pastikan kode outlet dan divisi sudah benar.',
                ], 'Outlet tidak valid', 400);
            }

            $visit = Visit::whereDate('tanggal_visit', date('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->where('outlet_id', $outlet->id)
                ->latest()
                ->first();

            if (! $visit) {
                return ResponseFormatter::success([
                    'checked_in' => false,
                    'checked_out' => false,
                    'visit_id' => null,
                    'outlet_diminta' => $outlet->kode_outlet,
                    'nama_outlet_diminta' => $outlet->nama_outlet,
                    'alamat_outlet_diminta' => $outlet->alamat,
                ], 'Anda belum melakukan check-in di outlet ini hari ini (Outlet: '.$outlet->nama_outlet.', Alamat: '.$outlet->alamat.'). Silakan lakukan check-in terlebih dahulu.');
            }

            if ($visit->check_out_time) {
                return ResponseFormatter::success([
                    'checked_in' => true,
                    'checked_out' => true,
                    'visit_id' => $visit->id,
                    'outlet_diminta' => $outlet->kode_outlet,
                    'nama_outlet_diminta' => $outlet->nama_outlet,
                    'alamat_outlet_diminta' => $outlet->alamat,
                    'waktu_checkin' => $visit->check_in_time ? $visit->check_in_time->format('d-m-Y H:i') : null,
                    'waktu_checkout' => $visit->check_out_time ? $visit->check_out_time->format('d-m-Y H:i') : null,
                ], 'Visit Anda ke outlet ini hari ini sudah selesai (sudah check-out di '.$outlet->nama_outlet.').');
            } else {
                return ResponseFormatter::success([
                    'checked_in' => true,
                    'checked_out' => false,
                    'visit_id' => $visit->id,
                    'outlet_diminta' => $outlet->kode_outlet,
                    'nama_outlet_diminta' => $outlet->nama_outlet,
                    'alamat_outlet_diminta' => $outlet->alamat,
                    'waktu_checkin' => $visit->check_in_time ? $visit->check_in_time->format('d-m-Y H:i') : null,
                ], 'Anda sudah check-in di outlet ini hari ini (Outlet: '.$outlet->nama_outlet.', Alamat: '.$outlet->alamat.'). Silakan lakukan check-out sebelum membuat kunjungan baru.');
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.',
            ], $error->getMessage(), 500);
        }
    }
}

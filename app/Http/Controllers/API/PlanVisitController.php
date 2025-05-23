<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\PlanVisit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanVisitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search');
            $month = $request->input('month');
            $date = $request->input('date');
            $outlet = $request->input('outlet');

            $query = PlanVisit::with([
                'outlet.badanusaha',
                'outlet.region',
                'outlet.divisi',
                'outlet.cluster',
                'user.badanusaha',
                'user.region',
                'user.divisi',
                'user.cluster',
                'user.role'
            ])->where('user_id', Auth::user()->id);

            if ($search) {
                $query->whereHas('outlet', function ($q) use ($search) {
                    $q->where('nama_outlet', 'like', "%$search%")
                        ->orWhere('kode_outlet', 'like', "%$search%");
                });
            }
            if ($month) {
                $query->whereMonth('tanggal_visit', $month);
            }
            if ($date) {
                $query->whereDate('tanggal_visit', $date);
            }
            if ($outlet) {
                $query->whereHas('outlet', function ($q) use ($outlet) {
                    $q->where('kode_outlet', $outlet);
                });
            }

            $planVisit = $query->orderBy('tanggal_visit')->paginate($perPage);

            return ResponseFormatter::success(
                collect($planVisit->items()),
                'Data plan visit berhasil diambil',
                [
                    'current_page' => $planVisit->currentPage(),
                    'last_page' => $planVisit->lastPage(),
                    'total' => $planVisit->total(),
                    'per_page' => $planVisit->perPage(),
                ]
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Maaf, terjadi kendala saat mengambil data plan visit. Silakan coba lagi.'
            ], $error->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tanggal_visit' => 'required|date|after_or_equal:today',
                'kode_outlet' => 'required',
            ]);

            $idOutlet = Outlet::where('kode_outlet', $request->kode_outlet)->firstOrFail();

            // Validasi terkait tanggal dan divisi
            if ((Auth::user()->divisi_id == 4 || $idOutlet->divisi_id == 4) &&
                Carbon::now()->isAfter(Carbon::parse($request->tanggal_visit)->startOfWeek()->addDay(1)->addHour(10))
            ) {
                return ResponseFormatter::error(null, 'Tidak bisa menambahkan plan visit kurang dari minggu yang berjalan');
            }

            if ((Auth::user()->divisi_id != 4 && $idOutlet->divisi_id != 4) &&
                Carbon::now()->isAfter(Carbon::parse($request->tanggal_visit)->addDays(3))
            ) {
                return ResponseFormatter::error(null, 'Tidak bisa menambahkan plan visit kurang dari h-3 visit');
            }

            // Cek apakah sudah ada plan visit untuk user, outlet, dan tanggal yang sama
            $cekData = PlanVisit::whereDate('tanggal_visit', Carbon::parse($request->tanggal_visit))
                ->where('user_id', Auth::user()->id)
                ->where('outlet_id', $idOutlet->id)
                ->exists();

            if ($cekData) {
                return ResponseFormatter::error(null, 'Data sebelumnya sudah ada');
            }

            // Tambahkan plan visit baru
            $addPlan = PlanVisit::create([
                'user_id' => Auth::user()->id,
                'outlet_id' => $idOutlet->id,
                'tanggal_visit' => Carbon::parse($request->tanggal_visit),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return ResponseFormatter::success($addPlan, 'Plan visit berhasil ditambahkan');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $planVisit = PlanVisit::where('id', $id)
                ->where('user_id', Auth::user()->id)
                ->first();

            if (!$planVisit) {
                return ResponseFormatter::error(null, 'Plan visit tidak ditemukan', 404);
            }

            // Tidak bisa hapus jika sudah H-3 ke bawah dari tanggal_visit
            $tanggalVisit = Carbon::parse($planVisit->tanggal_visit);
            $now = Carbon::now();
            // Jika hari ini >= tanggal_visit - 2 (artinya sudah H-2, H-1, atau hari H)
            if ($now->greaterThanOrEqualTo($tanggalVisit->copy()->subDays(2))) {
                return ResponseFormatter::error(null, 'Tidak bisa menghapus plan visit pada H-2, H-1, atau hari H. Minimal hanya bisa dihapus sebelum H-3 dari tanggal visit.');
            }

            $planVisit->delete();

            return ResponseFormatter::success(null, 'Plan visit berhasil dihapus');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Maaf, terjadi kendala saat menghapus plan visit. Silakan coba lagi.'
            ], $error->getMessage(), 500);
        }
    }
}

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
    public function fetch(Request $request)
    {
        try {
            $planVisit = PlanVisit::with([
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
                ->whereDate('tanggal_visit', Carbon::today())
                ->get();

            return ResponseFormatter::success($planVisit, 'Data plan visit berhasil diambil');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function bymonth(Request $request)
    {
        try {
            $request->validate([
                'bulan' => 'required|numeric',
                'tahun' => 'required|numeric',
            ]);

            $plan = PlanVisit::with([
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
                ->whereYear('tanggal_visit', $request->tahun)
                ->whereMonth('tanggal_visit', $request->bulan)
                ->where('user_id', Auth::user()->id)
                ->orderBy('tanggal_visit')
                ->get();

            return ResponseFormatter::success($plan, 'Berhasil mengambil data berdasarkan bulan');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function add(Request $request)
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

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'bulan' => 'required|numeric',
                'tahun' => 'required|numeric',
                'kode_outlet' => 'required',
            ]);

            $outlet = Outlet::where('kode_outlet', $request->kode_outlet)->firstOrFail();

            $planVisit = PlanVisit::where('outlet_id', $outlet->id)
                ->whereYear('tanggal_visit', $request->tahun)
                ->whereMonth('tanggal_visit', $request->bulan)
                ->where('user_id', Auth::user()->id)
                ->first();

            if (!$planVisit) {
                return ResponseFormatter::error(null, 'Plan visit tidak ditemukan', 404);
            }

            // Validasi tanggal
            if (Carbon::now()->isAfter(Carbon::createFromTimestamp($planVisit->tanggal_visit)->startOfMonth()->subDays(5))) {
                return ResponseFormatter::error(null, 'Tidak bisa menghapus plan visit kurang dari h-5 bulan visit');
            }

            $delete = $planVisit->delete();

            return ResponseFormatter::success($delete, 'Plan visit berhasil dihapus');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function deleterealme(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:plan_visits,id',
            ]);

            $planVisit = PlanVisit::where('id', $request->id)
                ->where('user_id', Auth::user()->id)
                ->firstOrFail();

            if (Carbon::now()->isAfter(Carbon::parse($planVisit->tanggal_visit)->startOfWeek()->addDay(1)->addHour(10))) {
                return ResponseFormatter::error(null, 'Tidak bisa menghapus plan visit kurang dari atau dalam minggu yang berjalan');
            }

            $delete = $planVisit->delete();

            return ResponseFormatter::success($delete, 'Plan visit berhasil dihapus');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }
}

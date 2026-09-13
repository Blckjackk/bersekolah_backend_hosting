<?php

namespace App\Http\Controllers;

use App\Models\Beswan;
use App\Models\InterviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InterviewScheduleController extends Controller
{
    // =========================================================
    // PESERTA ENDPOINTS (auth required)
    // =========================================================

    /**
     * GET /interview-schedule – Jadwal wawancara user yang sedang login (peserta)
     */
    public function mySchedule(Request $request)
    {
        try {
            $user = Auth::user();
            $beswan = Beswan::where('user_id', $user->id)->first();

            if (!$beswan) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada data beswan',
                    'data'    => [],
                ]);
            }

            $schedules = InterviewSchedule::where('beswan_id', $beswan->id)
                ->orderBy('tanggal_wawancara')
                ->orderBy('jam_mulai')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $schedules,
            ]);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule mySchedule error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil jadwal wawancara'], 500);
        }
    }

    // =========================================================
    // ADMIN ENDPOINTS
    // =========================================================

    /**
     * GET /admin/interview-schedules – Semua jadwal wawancara (admin)
     */
    public function index(Request $request)
    {
        try {
            $query = InterviewSchedule::with(['beswan.user', 'application'])
                ->orderBy('tanggal_wawancara')
                ->orderBy('jam_mulai');

            // Filter by date
            if ($request->has('date') && !empty($request->date)) {
                $query->whereDate('tanggal_wawancara', $request->date);
            }

            // Filter by month + year (for calendar view)
            if ($request->has('month') && $request->has('year')) {
                $query->whereMonth('tanggal_wawancara', $request->month)
                      ->whereYear('tanggal_wawancara', $request->year);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by peserta name
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->whereHas('beswan.user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $schedules = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $schedules,
            ]);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule index error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil jadwal wawancara'], 500);
        }
    }

    /**
     * POST /admin/interview-schedules – Buat jadwal wawancara baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'beswan_id'         => 'required|exists:beswan,id',
            'application_id'    => 'nullable|exists:beasiswa_applications,id',
            'tanggal_wawancara' => 'required|date',
            'jam_mulai'         => 'required|date_format:H:i',
            'jam_selesai'       => 'nullable|date_format:H:i|after:jam_mulai',
            'lokasi_atau_link'  => 'nullable|string|max:500',
            'catatan'           => 'nullable|string',
            'status'            => 'nullable|in:scheduled,done,cancelled,rescheduled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['created_by'] = Auth::id();

            $schedule = InterviewSchedule::create($data);
            $schedule->load(['beswan.user', 'application']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal wawancara berhasil dibuat',
                'data'    => $schedule,
            ], 201);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat jadwal wawancara'], 500);
        }
    }

    /**
     * PUT /admin/interview-schedules/{id} – Update jadwal wawancara
     */
    public function update(Request $request, $id)
    {
        $schedule = InterviewSchedule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'beswan_id'         => 'sometimes|required|exists:beswan,id',
            'application_id'    => 'nullable|exists:beasiswa_applications,id',
            'tanggal_wawancara' => 'sometimes|required|date',
            'jam_mulai'         => 'sometimes|required|date_format:H:i',
            'jam_selesai'       => 'nullable|date_format:H:i',
            'lokasi_atau_link'  => 'nullable|string|max:500',
            'catatan'           => 'nullable|string',
            'status'            => 'nullable|in:scheduled,done,cancelled,rescheduled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $schedule->update($validator->validated());
            $schedule->load(['beswan.user', 'application']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal wawancara berhasil diperbarui',
                'data'    => $schedule,
            ]);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui jadwal wawancara'], 500);
        }
    }

    /**
     * DELETE /admin/interview-schedules/{id} – Hapus jadwal
     */
    public function destroy($id)
    {
        try {
            $schedule = InterviewSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal wawancara berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule destroy error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus jadwal wawancara'], 500);
        }
    }

    /**
     * GET /admin/interview-schedules/calendar – Data kalender per bulan (admin)
     */
    public function calendarData(Request $request)
    {
        try {
            $month = $request->get('month', now()->month);
            $year  = $request->get('year', now()->year);

            $schedules = InterviewSchedule::with(['beswan.user'])
                ->whereMonth('tanggal_wawancara', $month)
                ->whereYear('tanggal_wawancara', $year)
                ->orderBy('tanggal_wawancara')
                ->orderBy('jam_mulai')
                ->get();

            // Group by tanggal untuk tampilan kalender
            $grouped = $schedules->groupBy(function ($item) {
                return $item->tanggal_wawancara->format('Y-m-d');
            });

            return response()->json([
                'success' => true,
                'data'    => $grouped,
                'meta'    => [
                    'month' => $month,
                    'year'  => $year,
                    'total' => $schedules->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('InterviewSchedule calendarData error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data kalender'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\EmployeeSchedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $schedules = EmployeeSchedule::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
            
        return view('schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'remark' => 'nullable|string'
        ]);

        EmployeeSchedule::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'location' => $request->location,
            'remark' => $request->remark
        ]);

        return back()->with('success', 'Agenda individu berhasil dijadwalkan.');
    }

    public function destroy($id)
    {
        $schedule = EmployeeSchedule::where('user_id', auth()->id())->findOrFail($id);
        $schedule->delete();
        return back()->with('success', 'Agenda individu berhasil dihapus.');
    }
}

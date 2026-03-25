<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;

class DashboardController extends Controller
{
    public function index()
    {
        $bookings = Booking::query()->with('package')->latest()->paginate(30);
        $packages = Package::query()->latest()->paginate(20);

        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'Pending')->count();
        $approvedBookings = Booking::where('status', 'Approved')->count();

        $totalRevenue = Booking::query()
            ->with('package')
            ->where('status', 'Approved')
            ->get()
            ->sum(fn (Booking $b) => (int) $b->pax * (int) ($b->package?->price ?? 0));

        return view('admin.dashboard', compact(
            'bookings',
            'packages',
            'totalBookings',
            'pendingBookings',
            'approvedBookings',
            'totalRevenue'
        ));
    }
}

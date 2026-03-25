<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $q = Booking::query()->with('package')->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $bookings = $q->paginate(30);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function approve(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'Approved']);

        return redirect()->back();
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'Cancelled']);

        return redirect()->back();
    }

    public function markPaid(Booking $booking): RedirectResponse
    {
        $booking->update(['payment_status' => 'Paid']);

        return redirect()->back();
    }

    public function markSubmitted(Booking $booking): RedirectResponse
    {
        $booking->update(['payment_status' => 'Submitted']);

        return redirect()->back();
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->back();
    }
}

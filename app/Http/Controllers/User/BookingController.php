<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function dashboard()
    {
        $packages = Package::query()->orderBy('title')->get();

        $bookings = Booking::query()
            ->with('package')
            ->where('customer_name', session('username'))
            ->latest()
            ->get();

        return view('user.dashboard', compact('packages', 'bookings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'date' => ['required', 'date'],
            'pax' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string'],
        ]);

        $data['customer_name'] = (string) session('username');

        Booking::create($data);

        return redirect()->route('user.dashboard');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->customer_name !== session('username')) {
            abort(403);
        }

        if ($booking->status !== 'Pending') {
            return redirect()->back();
        }

        $booking->update(['status' => 'Cancelled']);

        return redirect()->back();
    }

    public function submitPayment(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->customer_name !== session('username')) {
            abort(403);
        }

        if ($booking->status === 'Cancelled') {
            return redirect()->back();
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_proof' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $booking->update([
            'payment_status' => 'Submitted',
            'payment_method' => $data['payment_method'],
            'payment_proof_path' => $path,
        ]);

        return redirect()->back();
    }
}

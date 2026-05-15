<?php

use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

Route::prefix('api')->group(function () {
    $audit = function (Request $request, ?\App\Models\User $user, string $action, ?string $entityType = null, ?int $entityId = null, array $metadata = []): void {
        try {
            \App\Models\AuditLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'metadata' => $metadata,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            return;
        }
    };

    $notifyUser = function (int $userId, string $title, string $message): void {
        try {
            \App\Models\InAppNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            return;
        }
    };

    $notifyAdmins = function (string $title, string $message) use ($notifyUser): void {
        \App\Models\User::query()
            ->where('role', 'admin')
            ->pluck('id')
            ->each(fn ($id) => $notifyUser((int) $id, $title, $message));
    };

    $notifyCustomerByEmail = function (?string $email, string $title, string $message) use ($notifyUser): void {
        if (!$email) {
            return;
        }

        $userId = \App\Models\User::query()->where('email', $email)->value('id');

        if ($userId) {
            $notifyUser((int) $userId, $title, $message);
        }
    };

    Route::get('/me', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'role' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    });

    Route::post('/register', function (Request $request) use ($audit): array {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $notifyUser((int) $user->id, 'Welcome', 'Welcome to Prime Surigao! You can view updates here any time.');

        $audit($request, $user, 'auth.register', User::class, (int) $user->id, [
            'email' => $user->email,
        ]);

        return [
            'role' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
        ];
    });

    Route::post('/login', function (Request $request) use ($audit): array {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'intended_role' => ['nullable', 'in:customer,admin'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        if (!empty($data['intended_role']) && $user->role !== $data['intended_role']) {
            throw ValidationException::withMessages([
                'email' => 'Account role does not match selected login type.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $audit($request, $user, 'auth.login', User::class, (int) $user->id, [
            'intended_role' => $data['intended_role'] ?? null,
        ]);

        return [
            'role' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
        ];
    });

    Route::post('/logout', function (Request $request) use ($audit): array {
        $user = $request->user();
        if ($user) {
            $audit($request, $user, 'auth.logout', User::class, (int) $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ['ok' => true];
    });

    Route::get('/notifications', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notifications = \App\Models\InAppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        $unreadCount = \App\Models\InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    });

    Route::post('/notifications/read-all', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        \App\Models\InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    });

    Route::post('/notifications/{notification}/read', function (Request $request, \App\Models\InAppNotification $notification) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ((int) $notification->user_id !== (int) $user->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    });

    Route::get('/packages', function () {
        $packages = Package::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();

        return response()->json(['packages' => $packages]);
    });

    Route::middleware(['role:customer'])->group(function () use ($notifyCustomerByEmail, $notifyAdmins, $audit) {
        Route::get('/user/dashboard', function (Request $request) {
            $user = $request->user();
            $packages = Package::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get();

            $bookings = Booking::query()
                ->with(['package', 'documents'])
                ->where('customer_name', $user?->email)
                ->latest()
                ->get();

            return response()->json([
                'packages' => $packages,
                'bookings' => $bookings,
            ]);
        });

        Route::put('/user/profile', function (Request $request) use ($audit) {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ]);

            $oldEmail = (string) $user->email;

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if ($oldEmail !== $user->email) {
                Booking::query()
                    ->where('customer_name', $oldEmail)
                    ->update(['customer_name' => $user->email]);
            }

            $audit($request, $user, 'user.profile.update', User::class, (int) $user->id);

            return response()->json([
                'ok' => true,
                'role' => $user->role,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        });

        Route::put('/user/profile/password', function (Request $request) use ($audit) {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $data = $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            ]);

            if (!Hash::check($data['current_password'], (string) $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }

            $user->update([
                'password' => $data['password'],
            ]);

            $audit($request, $user, 'user.profile.password.update', User::class, (int) $user->id);

            return response()->json(['ok' => true]);
        });

        Route::get('/bookings/{booking}/documents', function (Request $request, Booking $booking) {
            if ($booking->customer_name !== $request->user()?->email) {
                abort(403);
            }

            $docs = BookingDocument::query()
                ->where('booking_id', $booking->id)
                ->latest()
                ->get();

            return response()->json(['documents' => $docs]);
        });

        Route::post('/bookings', function (Request $request) use ($notifyCustomerByEmail, $notifyAdmins, $audit) {
            $data = $request->validate([
                'package_id' => ['required', 'exists:packages,id'],
                'date' => ['required', 'date'],
                'pax' => ['required', 'integer', 'min:1'],
                'note' => ['nullable', 'string'],
            ]);

            $package = Package::query()->findOrFail($data['package_id']);

            if (!$package->is_active) {
                throw ValidationException::withMessages([
                    'package_id' => 'Selected package is not available.',
                ]);
            }

            if ((int) $package->slots_per_day > 0) {
                $date = Carbon::parse($data['date'])->toDateString();

                $used = (int) Booking::query()
                    ->where('package_id', $package->id)
                    ->whereDate('date', $date)
                    ->whereNotIn('status', ['Cancelled'])
                    ->sum('pax');

                $requested = (int) $data['pax'];
                $remaining = (int) $package->slots_per_day - $used;

                if ($requested > $remaining) {
                    throw ValidationException::withMessages([
                        'pax' => "Not enough slots for this date. Remaining: {$remaining}.",
                    ]);
                }
            }

            $data['customer_name'] = (string) $request->user()?->email;

            $booking = Booking::create($data);

            $audit($request, $request->user(), 'booking.create', Booking::class, (int) $booking->id, [
                'package_id' => (int) $booking->package_id,
                'date' => (string) $booking->date,
                'pax' => (int) $booking->pax,
            ]);

            $customerEmail = (string) $request->user()?->email;
            $packageTitle = (string) ($package->title ?? 'Package');
            $notifyCustomerByEmail($customerEmail, 'Booking Created', "Your booking for {$packageTitle} was created and is pending approval.");
            $notifyAdmins('New Booking', "New booking created by {$customerEmail} for {$packageTitle}.");

            return response()->json(['booking' => $booking], 201);
        });

        Route::post('/bookings/{booking}/payment', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $notifyAdmins, $audit) {
            if ($booking->customer_name !== $request->user()?->email) {
                abort(403);
            }

            if ($booking->status === 'Cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Booking is cancelled.',
                ]);
            }

            $data = $request->validate([
                'payment_method' => ['required', 'in:cash,gcash,bank_transfer,card'],
                'payment_reference' => ['nullable', 'string', 'max:255'],
                'payment_proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            ]);

            $method = (string) $data['payment_method'];
            $proofPath = null;

            if ($method !== 'cash') {
                if (!$request->hasFile('payment_proof')) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'Payment proof is required for online payments.',
                    ]);
                }

                $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
            }

            $booking->update([
                'payment_method' => $method,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => $proofPath,
                'payment_status' => $method === 'cash' ? 'Cash Pending' : 'Submitted',
            ]);

            $audit($request, $request->user(), 'payment.submit', Booking::class, (int) $booking->id, [
                'payment_method' => $method,
                'payment_reference' => $data['payment_reference'] ?? null,
                'has_proof' => (bool) $proofPath,
            ]);

            $customerEmail = (string) $request->user()?->email;
            $notifyCustomerByEmail($customerEmail, 'Payment Submitted', 'Your payment details were submitted and are awaiting verification.');
            $notifyAdmins('Payment Submitted', "Payment submitted by {$customerEmail} for booking #{$booking->id}.");

            return response()->json(['booking' => $booking]);
        });

        Route::post('/bookings/{booking}/cancel', function (Request $request, Booking $booking) use ($audit) {
            if ($booking->customer_name !== $request->user()?->email) {
                abort(403);
            }

            if ($booking->status !== 'Pending') {
                return response()->json(['ok' => true]);
            }

            $booking->update(['status' => 'Cancelled']);

            $audit($request, $request->user(), 'booking.cancel', Booking::class, (int) $booking->id);

            return response()->json(['ok' => true]);
        });
    });

    Route::prefix('admin')->middleware(['role:admin'])->group(function () use ($notifyCustomerByEmail, $audit) {
        Route::get('/dashboard', function () {
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();

            $bookings = Booking::query()->with('package')->latest()->limit(30)->get();

            $totalBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'Pending')->count();
            $approvedBookings = Booking::where('status', 'Approved')->count();
            $cancelledBookings = Booking::where('status', 'Cancelled')->count();

            $totalRevenue = Booking::query()
                ->with('package')
                ->where('status', 'Approved')
                ->get()
                ->sum(fn (Booking $b) => (int) $b->pax * (int) ($b->package?->price ?? 0));

            $todaysBookings = Booking::query()
                ->where('created_at', '>=', $today)
                ->count();

            $weekBookings = Booking::query()
                ->where('created_at', '>=', $weekStart)
                ->count();

            $weekRevenue = Booking::query()
                ->with('package')
                ->where('status', 'Approved')
                ->where('created_at', '>=', $weekStart)
                ->get()
                ->sum(fn (Booking $b) => (int) $b->pax * (int) ($b->package?->price ?? 0));

            $submittedPayments = Booking::query()
                ->whereIn('payment_status', ['Submitted', 'Cash Pending'])
                ->count();

            $paidBookings = Booking::query()->where('payment_status', 'Paid')->count();

            $cancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0.0;

            $topPackage = Booking::query()
                ->selectRaw('package_id, COUNT(*) as c')
                ->whereNotNull('package_id')
                ->groupBy('package_id')
                ->orderByDesc('c')
                ->first();

            $topPackageData = null;
            if ($topPackage?->package_id) {
                $p = Package::query()->find($topPackage->package_id);
                if ($p) {
                    $topPackageData = [
                        'package_id' => (int) $p->id,
                        'title' => (string) $p->title,
                        'count' => (int) ($topPackage->c ?? 0),
                    ];
                }
            }

            $trend = [];
            $trendRevenue = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $label = $d->format('D');
                $count = Booking::query()
                    ->whereDate('created_at', $d->toDateString())
                    ->count();
                $rev = (int) Booking::query()
                    ->with('package')
                    ->where('status', 'Approved')
                    ->whereDate('created_at', $d->toDateString())
                    ->get()
                    ->sum(fn (Booking $b) => (int) $b->pax * (int) ($b->package?->price ?? 0));
                $trend[] = ['label' => $label, 'value' => $count];
                $trendRevenue[] = ['label' => $label, 'value' => $rev];
            }

            $paymentBreakdown = Booking::query()
                ->selectRaw('COALESCE(NULLIF(payment_method, ""), "unknown") as method, COUNT(*) as c')
                ->whereIn('payment_status', ['Paid', 'Submitted', 'Cash Pending', 'Rejected'])
                ->groupBy('method')
                ->orderByDesc('c')
                ->get()
                ->map(fn ($row) => [
                    'method' => (string) $row->method,
                    'count' => (int) $row->c,
                ])
                ->values();

            $recentActivity = [];
            try {
                $recentActivity = \App\Models\AuditLog::query()
                    ->with('user')
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($l) => [
                        'id' => (int) $l->id,
                        'created_at' => (string) $l->created_at,
                        'action' => (string) $l->action,
                        'user' => $l->user ? [
                            'id' => (int) $l->user->id,
                            'name' => (string) $l->user->name,
                        ] : null,
                        'entity_type' => $l->entity_type,
                        'entity_id' => $l->entity_id,
                    ])
                    ->toArray();
            } catch (Throwable $e) {
                $recentActivity = [];
            }

            return response()->json([
                'summary' => [
                    'totalBookings' => $totalBookings,
                    'pendingBookings' => $pendingBookings,
                    'approvedBookings' => $approvedBookings,
                    'totalRevenue' => $totalRevenue,
                ],
                'bookings' => $bookings,
                'metrics' => [
                    'todaysBookings' => $todaysBookings,
                    'weekBookings' => $weekBookings,
                    'weekRevenue' => $weekRevenue,
                    'submittedPayments' => $submittedPayments,
                    'paidBookings' => $paidBookings,
                    'cancelledBookings' => $cancelledBookings,
                    'cancellationRate' => $cancellationRate,
                    'topPackage' => $topPackageData,
                ],
                'charts' => [
                    'bookings7d' => $trend,
                    'revenue7d' => $trendRevenue,
                    'paymentMethods' => $paymentBreakdown,
                ],
                'recentActivity' => $recentActivity,
            ]);
        });

        Route::get('/bookings', function (Request $request) {
            $q = Booking::query()->with(['package', 'documents'])->latest();

            if ($request->filled('status')) {
                $q->where('status', $request->string('status'));
            }

            $bookings = $q->paginate(30);

            return response()->json(['bookings' => $bookings->items()]);
        });

        Route::get('/bookings/{booking}/payment-proof', function (Request $request, Booking $booking) {
            $path = (string) ($booking->payment_proof_path ?? '');
            if ($path === '') {
                abort(404);
            }

            $disk = Storage::disk('public');
            if (!$disk->exists($path)) {
                abort(404);
            }

            return $disk->download($path);
        });

        Route::get('/bookings/{booking}/documents', function (Booking $booking) {
            $docs = BookingDocument::query()
                ->where('booking_id', $booking->id)
                ->latest()
                ->get();

            return response()->json(['documents' => $docs]);
        });

        Route::post('/bookings/{booking}/documents', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $data = $request->validate([
                'type' => ['required', 'in:voucher,eticket,summary,requirement'],
                'title' => ['required', 'string', 'max:255'],
                'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            ]);

            $path = $request->file('file')->store('booking-documents', 'public');

            $doc = BookingDocument::create([
                'booking_id' => $booking->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'file_path' => $path,
            ]);

            $audit($request, $request->user(), 'document.upload', Booking::class, (int) $booking->id, [
                'document_id' => (int) $doc->id,
                'type' => (string) $doc->type,
                'title' => (string) $doc->title,
            ]);

            $notifyCustomerByEmail($booking->customer_name, 'New Document Available', "A new document was uploaded for booking #{$booking->id}: {$doc->title}.");

            return response()->json(['document' => $doc], 201);
        });

        Route::post('/bookings/{booking}/approve', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $booking->update(['status' => 'Approved']);
            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Booking Approved', "Your booking #{$booking->id} was approved.");

            $audit($request, $request->user(), 'booking.approve', Booking::class, (int) $booking->id);
            return response()->json(['ok' => true]);
        });

        Route::post('/bookings/{booking}/cancel', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $booking->update(['status' => 'Cancelled']);
            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Booking Cancelled', "Your booking #{$booking->id} was cancelled by admin.");

            $audit($request, $request->user(), 'booking.admin_cancel', Booking::class, (int) $booking->id);
            return response()->json(['ok' => true]);
        });

        Route::post('/bookings/{booking}/mark-paid', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $booking->update([
                'payment_status' => 'Paid',
                'paid_at' => now(),
            ]);
            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Payment Verified', "Your payment for booking #{$booking->id} was marked as paid.");

            $audit($request, $request->user(), 'payment.mark_paid', Booking::class, (int) $booking->id);
            return response()->json(['ok' => true]);
        });

        Route::post('/bookings/{booking}/mark-submitted', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $booking->update(['payment_status' => 'Submitted']);
            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Payment Status Updated', "Payment status updated for booking #{$booking->id}.");

            $audit($request, $request->user(), 'payment.mark_submitted', Booking::class, (int) $booking->id);
            return response()->json(['ok' => true]);
        });

        Route::post('/bookings/{booking}/verify-payment', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $booking->update([
                'payment_status' => 'Paid',
                'paid_at' => now(),
            ]);

            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Payment Verified', "Your payment for booking #{$booking->id} was verified.");

            $audit($request, $request->user(), 'payment.verify', Booking::class, (int) $booking->id);

            return response()->json(['ok' => true]);
        });

        Route::post('/bookings/{booking}/reject-payment', function (Request $request, Booking $booking) use ($notifyCustomerByEmail, $audit) {
            $data = $request->validate([
                'reason' => ['nullable', 'string', 'max:255'],
            ]);

            $booking->update([
                'payment_status' => 'Rejected',
                'paid_at' => null,
            ]);

            $reason = $data['reason'] ?? 'Payment rejected.';
            $customerEmail = $booking->customer_name;
            $notifyCustomerByEmail($customerEmail, 'Payment Rejected', "Payment for booking #{$booking->id} was rejected. {$reason}");

            $audit($request, $request->user(), 'payment.reject', Booking::class, (int) $booking->id, [
                'reason' => $reason,
            ]);

            return response()->json(['ok' => true]);
        });

        Route::delete('/bookings/{booking}', function (Booking $booking) {
            $booking->delete();
            return response()->json(['ok' => true]);
        });

        Route::get('/packages', function () {
            $packages = Package::query()->latest()->get();
            return response()->json(['packages' => $packages]);
        });

        Route::get('/reports/summary', function (Request $request) {
            $data = $request->validate([
                'from' => ['nullable', 'date'],
                'to' => ['nullable', 'date'],
            ]);

            $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : null;
            $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : null;

            $q = Booking::query()
                ->with('package')
                ->where('status', 'Approved');

            if ($from) {
                $q->where('date', '>=', $from->toDateString());
            }
            if ($to) {
                $q->where('date', '<=', $to->toDateString());
            }

            $bookings = $q->get();

            $totalApprovedBookings = $bookings->count();
            $totalPax = (int) $bookings->sum('pax');
            $totalRevenue = (int) $bookings->sum(fn (Booking $b) => (int) $b->pax * (int) ($b->package?->price ?? 0));

            $byPackage = $bookings
                ->groupBy('package_id')
                ->map(function ($rows) {
                    $first = $rows->first();
                    $price = (int) ($first?->package?->price ?? 0);
                    $pax = (int) $rows->sum('pax');
                    return [
                        'package_id' => (int) ($first?->package_id ?? 0),
                        'package_title' => (string) ($first?->package?->title ?? '-'),
                        'approved_bookings' => (int) $rows->count(),
                        'total_pax' => $pax,
                        'revenue' => (int) ($pax * $price),
                    ];
                })
                ->values();

            return response()->json([
                'summary' => [
                    'totalApprovedBookings' => $totalApprovedBookings,
                    'totalPax' => $totalPax,
                    'totalRevenue' => $totalRevenue,
                ],
                'byPackage' => $byPackage,
            ]);
        });

        Route::get('/reports/summary.csv', function (Request $request) {
            $data = $request->validate([
                'from' => ['nullable', 'date'],
                'to' => ['nullable', 'date'],
            ]);

            $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : null;
            $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : null;

            $q = Booking::query()
                ->with('package')
                ->where('status', 'Approved');

            if ($from) {
                $q->where('date', '>=', $from->toDateString());
            }
            if ($to) {
                $q->where('date', '<=', $to->toDateString());
            }

            $bookings = $q->get();

            $byPackage = $bookings
                ->groupBy('package_id')
                ->map(function ($rows) {
                    $first = $rows->first();
                    $price = (int) ($first?->package?->price ?? 0);
                    $pax = (int) $rows->sum('pax');
                    return [
                        'package_id' => (int) ($first?->package_id ?? 0),
                        'package_title' => (string) ($first?->package?->title ?? '-'),
                        'approved_bookings' => (int) $rows->count(),
                        'total_pax' => $pax,
                        'revenue' => (int) ($pax * $price),
                    ];
                })
                ->values();

            $filename = 'reports_summary_' . now()->format('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($byPackage) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Package ID', 'Package', 'Approved Bookings', 'Total Pax', 'Revenue']);
                foreach ($byPackage as $row) {
                    fputcsv($out, [
                        $row['package_id'] ?? '',
                        $row['package_title'] ?? '',
                        $row['approved_bookings'] ?? 0,
                        $row['total_pax'] ?? 0,
                        $row['revenue'] ?? 0,
                    ]);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        });

        Route::get('/audit-logs', function (Request $request) {
            $data = $request->validate([
                'action' => ['nullable', 'string', 'max:100'],
                'user_id' => ['nullable', 'integer'],
                'from' => ['nullable', 'date'],
                'to' => ['nullable', 'date'],
            ]);

            $q = \App\Models\AuditLog::query()->with('user')->latest();

            if (!empty($data['action'])) {
                $q->where('action', $data['action']);
            }
            if (!empty($data['user_id'])) {
                $q->where('user_id', (int) $data['user_id']);
            }
            if (!empty($data['from'])) {
                $q->where('created_at', '>=', Carbon::parse($data['from'])->startOfDay());
            }
            if (!empty($data['to'])) {
                $q->where('created_at', '<=', Carbon::parse($data['to'])->endOfDay());
            }

            $logs = $q->paginate(50);

            return response()->json([
                'logs' => $logs->items(),
            ]);
        });

        Route::post('/packages', function (Request $request) {
            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'integer', 'min:0'],
                'slots_per_day' => ['required', 'integer', 'min:0'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('package-images', 'public');
                $data['image_url'] = Storage::url($path);
            }

            $package = Package::create($data);
            return response()->json(['package' => $package], 201);
        });

        Route::get('/packages/{package}', function (Package $package) {
            return response()->json(['package' => $package]);
        });

        Route::put('/packages/{package}', function (Request $request, Package $package) {
            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'integer', 'min:0'],
                'slots_per_day' => ['required', 'integer', 'min:0'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            if ($request->hasFile('image')) {
                $prev = (string) ($package->image_url ?? '');
                if (str_starts_with($prev, '/storage/package-images/')) {
                    $rel = ltrim(str_replace('/storage/', '', $prev), '/');
                    if ($rel !== '') {
                        try {
                            Storage::disk('public')->delete($rel);
                        } catch (Throwable $e) {
                        }
                    }
                }

                $path = $request->file('image')->store('package-images', 'public');
                $data['image_url'] = Storage::url($path);
            }

            $package->update($data);
            return response()->json(['package' => $package]);
        });

        Route::post('/packages/{package}/activate', function (Request $request, Package $package) use ($audit) {
            $package->update(['is_active' => true]);
            $audit($request, $request->user(), 'package.activate', Package::class, (int) $package->id);
            return response()->json(['package' => $package]);
        });

        Route::post('/packages/{package}/deactivate', function (Request $request, Package $package) use ($audit) {
            $package->update(['is_active' => false]);
            $audit($request, $request->user(), 'package.deactivate', Package::class, (int) $package->id);
            return response()->json(['package' => $package]);
        });

        Route::delete('/packages/{package}', function (Package $package) {
            $package->delete();
            return response()->json(['ok' => true]);
        });
    });
});

Route::get('/{any?}', function () {
    return response()->file(public_path('index.html'));
})->where('any', '^(?!api|sanctum).*$');

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!empty(config('app.url')) && config('app.url') !== 'http://localhost' && config('app.url') !== 'http://127.0.0.1:8000') {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            if (str_starts_with(config('app.url'), 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            $notifications = [];

            // Get pending registration requests
            $pendingRegistrations = \App\Models\RegistrationRequest::with(['student', 'branch'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            foreach ($pendingRegistrations as $reg) {
                $notifications[] = [
                    'title' => 'New QR Registration',
                    'time' => $reg->created_at ? $reg->created_at->diffForHumans() : 'Just now',
                    'message' => ($reg->student?->full_name ?? 'A student') . ' submitted KYC documents for ' . ($reg->branch?->name ?? 'a branch') . '.',
                    'created_at' => $reg->created_at ?? now(),
                    'link' => route('sub_admin.verifications'),
                ];
            }

            // Get pending payment proofs
            $pendingPayments = \App\Models\PaymentProof::with(['payment.student'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            foreach ($pendingPayments as $proof) {
                $payment = $proof->payment;
                $notifications[] = [
                    'title' => 'Payment UTR Proof',
                    'time' => $proof->created_at ? $proof->created_at->diffForHumans() : 'Just now',
                    'message' => 'UPI payment ₹' . number_format($payment?->amount ?? 0, 2) . ' proof uploaded by ' . ($payment?->student?->full_name ?? 'a student') . '.',
                    'created_at' => $proof->created_at ?? now(),
                    'link' => '#',
                ];
            }

            // Get pending complaints
            $pendingComplaints = \App\Models\Complaint::with(['student', 'branch'])
                ->whereNotIn('status', ['RESOLVED', 'CLOSED', 'Resolved', 'Solved'])
                ->latest()
                ->take(5)
                ->get();

            foreach ($pendingComplaints as $complaint) {
                $notifications[] = [
                    'title' => 'New Support Ticket',
                    'time' => $complaint->created_at ? $complaint->created_at->diffForHumans() : 'Just now',
                    'message' => ($complaint->student?->full_name ?? 'A student') . ' opened a complaint ticket for ' . ($complaint->category ?? 'support') . '.',
                    'created_at' => $complaint->created_at ?? now(),
                    'link' => route('sub_admin.complaints'),
                ];
            }

            // Sort notifications by created_at descending
            usort($notifications, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            // Limit to top 5 recent notifications overall
            $notifications = array_slice($notifications, 0, 5);

            $view->with('systemNotifications', $notifications);
            $view->with('pendingRegistrationCount', \App\Models\RegistrationRequest::whereIn('status', ['PENDING', 'pending'])->count());
            $view->with('pendingPaymentCount', \App\Models\PaymentProof::whereIn('status', ['PENDING', 'pending'])->count());
            $view->with('pendingComplaintsCount', \App\Models\Complaint::whereNotIn('status', ['RESOLVED', 'CLOSED', 'Resolved', 'Solved'])->count());
        });
    }
}

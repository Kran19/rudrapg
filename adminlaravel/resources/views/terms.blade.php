<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions of Stay - Rudra Group PG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 font-extrabold text-lg">
                    R
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 leading-tight">Rudra Group PG</h1>
                    <p class="text-xs text-slate-500">Resident Companion & Hostel Operations</p>
                </div>
            </div>
            <a href="{{ url('/login') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-3.5 py-2 rounded-xl transition-colors">
                Admin Portal &rarr;
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-6 py-10 flex-1">
        <div class="bg-white p-8 md:p-12 rounded-3xl border border-slate-200 shadow-sm">
            <div class="border-b border-slate-100 pb-6 mb-8">
                <span class="text-[11px] font-extrabold text-blue-600 bg-blue-50 uppercase px-3 py-1 rounded-full tracking-wider">Hostel Rules & Policy</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-3">Terms & Conditions of Stay</h2>
                <p class="text-xs text-slate-500 mt-1">Effective Date: January 1, 2026 &bull; Last Updated: September 3, 2026</p>
            </div>

            <div class="space-y-8 text-sm leading-relaxed text-slate-600">
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-blue-600"></i> 1. Rent & Monthly Dues
                    </h3>
                    <p>
                        Monthly hostel accommodation rent is payable in advance on or before the 5th calendar day of each month. Payments must be initiated via digital channels (UPI, IMPS, NEFT) or verified cash deposit with official digital receipt acknowledgment issued in the resident application.
                    </p>
                </section>

                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-vault text-blue-600"></i> 2. Security Deposit & Refund Policy
                    </h3>
                    <p>
                        Security deposits are maintained as a guarantee against property damage, unannounced departure, and pending electricity meter consumption. Deposits are fully refundable upon check-out after deduction of outstanding dues, verification of room condition, and handover of room keys.
                    </p>
                </section>

                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-blue-600"></i> 3. Vacating & Notice Period
                    </h3>
                    <p>
                        Residents intending to vacate their allocated room must serve a mandatory 30-day written notice via the resident portal or directly to the branch manager. Failure to provide 30 days notice will result in security deposit forfeiture equivalent to one month's rent.
                    </p>
                </section>

                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-blue-600"></i> 4. Electricity Sub-Meter Consumption
                    </h3>
                    <p>
                        Each room is fitted with a dedicated sub-meter. Residents are required to submit monthly photographic meter readings on the 1st of each month. Units consumed are billed at the prevailing branch electricity rate and split equally among room occupants.
                    </p>
                </section>

                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-building-user text-blue-600"></i> 5. Code of Conduct & House Rules
                    </h3>
                    <p>
                        Residents must maintain discipline, observe designated night entry curfews, avoid disturbance to fellow occupants, and strictly respect prohibited substance laws on all premises. Violation of hostel rules may result in immediate termination of stay.
                    </p>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-4xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} Rudra Group PG. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="{{ url('/privacy-policy') }}" class="hover:text-blue-600 underline font-medium">Privacy Policy</a>
                <a href="{{ url('/terms') }}" class="hover:text-blue-600 underline font-medium">Terms of Stay</a>
            </div>
        </div>
    </footer>
</body>
</html>

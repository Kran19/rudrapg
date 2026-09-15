<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account & Data Deletion Request - Rudra Group PG</title>
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
            <a href="{{ url('/privacy-policy') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-3.5 py-2 rounded-xl transition-colors">
                Privacy Policy &rarr;
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-6 py-10 flex-1">
        <div class="bg-white p-8 md:p-12 rounded-3xl border border-slate-200 shadow-sm">
            <div class="border-b border-slate-100 pb-6 mb-8">
                <span class="text-[11px] font-extrabold text-rose-600 bg-rose-50 uppercase px-3 py-1 rounded-full tracking-wider">User Rights & Data Control</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-3">Account & Data Deletion Request</h2>
                <p class="text-xs text-slate-500 mt-1">Google Play Store & Apple App Store Developer Policy Compliance &bull; Last Updated: September 2026</p>
            </div>

            <div class="space-y-8 text-sm leading-relaxed text-slate-600">
                <!-- Overview -->
                <section>
                    <p>
                        At <strong>Rudra Group PG</strong>, we respect your right to privacy and give you full control over your personal data. If you are a past, current, or prospective resident and wish to delete your account and associated personal data from our systems, you can initiate a deletion request through the methods described below.
                    </p>
                </section>

                <!-- Steps to Request Deletion -->
                <section class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-blue-600"></i> How to Request Account Deletion
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">1</div>
                            <div>
                                <h4 class="font-bold text-slate-900">Directly Inside the Mobile App (Instant)</h4>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Open the <strong>Rudra PG</strong> resident app &rarr; Go to <strong>Settings</strong> &rarr; Select <strong>Delete Resident Account</strong> &rarr; Confirm your request. Your session tokens are invalidated immediately.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="font-bold text-slate-900">Via Dedicated Email Request</h4>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Send an email from your registered email address or provide your registered mobile number to:
                                </p>
                                <div class="mt-2 inline-block bg-white border border-slate-200 rounded-xl px-4 py-2 font-mono text-xs text-blue-700 font-semibold">
                                    <a href="mailto:support@rudrapg.com?subject=Account%20Deletion%20Request">support@rudrapg.com</a>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Please include: Full Name, Registered Mobile Number, and Branch Name.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">3</div>
                            <div>
                                <h4 class="font-bold text-slate-900">Direct Contact with Branch Warden</h4>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Upon completed checkout/move-out from your PG branch, you can request the property warden or manager to process the final ledger clearance and resident record archiving.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- What Data is Deleted vs Retained -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Deleted -->
                    <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5">
                        <h4 class="font-bold text-rose-950 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-trash-can text-rose-600"></i> What Data Is Deleted
                        </h4>
                        <ul class="text-xs space-y-2 text-rose-900 list-disc list-inside">
                            <li><strong>Account Credentials:</strong> Mobile number login records, password hashes, and biometric / Sanctum session tokens.</li>
                            <li><strong>Identity Documents:</strong> Uploaded Aadhaar card and PAN card KYC image files.</li>
                            <li><strong>Profile Details:</strong> Emergency contact information, permanent residential addresses, and guardian details.</li>
                            <li><strong>Complaints & Tickets:</strong> In-app maintenance ticket messages, photos, and chat history.</li>
                        </ul>
                    </div>

                    <!-- Retained -->
                    <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5">
                        <h4 class="font-bold text-amber-950 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-shield text-amber-600"></i> What Data Is Retained (Legal Compliance)
                        </h4>
                        <ul class="text-xs space-y-2 text-amber-900 list-disc list-inside">
                            <li><strong>Financial Invoices:</strong> Historical transaction records of completed rent and security deposit receipts (Retained for statutory tax and accounting audit compliance under Indian law).</li>
                            <li><strong>Police Verification Registers:</strong> Official tenant entry logs previously submitted to local municipal authorities as mandated by law.</li>
                        </ul>
                    </div>
                </div>

                <!-- Retention Period & Processing -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-blue-600"></i> Processing Timeframe
                    </h3>
                    <p class="text-xs text-slate-600">
                        Upon receiving your verified deletion request, all active app login sessions are terminated immediately. Permanent deletion and anonymization of personal identifiers across our databases is processed within <strong>7 business days</strong>. A confirmation email or SMS will be sent once the process is complete.
                    </p>
                </section>

                <!-- Support Details -->
                <section class="border-t border-slate-100 pt-6">
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-headset text-blue-600"></i> Need Assistance?
                    </h3>
                    <p class="text-xs text-slate-600">
                        If you encounter any issues submitting your request, please contact our Data Protection Office:
                    </p>
                    <div class="mt-3 p-4 bg-slate-100 rounded-xl text-xs space-y-1">
                        <p><strong>Rudra Group PG — Administration & Compliance</strong></p>
                        <p>Email: <a href="mailto:support@rudrapg.com" class="text-blue-600 font-semibold">support@rudrapg.com</a></p>
                        <p>Location: Ahmedabad, Gujarat, India</p>
                    </div>
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

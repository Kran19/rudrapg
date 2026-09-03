<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Rudra Group PG</title>
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
                <span class="text-[11px] font-extrabold text-blue-600 bg-blue-50 uppercase px-3 py-1 rounded-full tracking-wider">Legal Compliance</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-3">Privacy Policy</h2>
                <p class="text-xs text-slate-500 mt-1">Effective Date: January 1, 2026 &bull; Last Updated: September 3, 2026</p>
            </div>

            <div class="space-y-8 text-sm leading-relaxed text-slate-600">
                <!-- Section 1 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-blue-600"></i> 1. Introduction & Overview
                    </h3>
                    <p>
                        Welcome to <strong>Rudra Group PG</strong> ("we", "our", or "us"). We operate modern paying guest (PG) accommodations, student hostels, and the <strong>Rudra Group PG Resident Application</strong>. This Privacy Policy informs our residents, prospective applicants, and visitors regarding our policies with the collection, use, disclosure, and protection of Personal Identifiable Information (PII).
                    </p>
                </section>

                <!-- Section 2 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-blue-600"></i> 2. Information We Collect
                    </h3>
                    <p class="mb-3">When you register or use the Rudra Group PG Resident Companion application, we collect the following categories of data:</p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2 text-slate-700">
                        <li><strong>Personal Identification:</strong> Full name, verified mobile phone number, email address, and permanent residential address.</li>
                        <li><strong>Government Identity & KYC:</strong> Aadhaar number, PAN card number, and document photographs for mandatory statutory PG identity verification and local police tenant compliance.</li>
                        <li><strong>Emergency Contact Information:</strong> Parent or guardian full name and emergency phone contact numbers.</li>
                        <li><strong>Stay & Allocation Data:</strong> Assigned PG branch, room number, bed number, monthly rent rate, security deposit balance, and joining date.</li>
                        <li><strong>Utility & Meter Records:</strong> Electricity sub-meter readings, monthly meter photographic captures, and unit consumption calculations.</li>
                        <li><strong>Financial Records:</strong> Transaction reference IDs (e.g., UPI UTR numbers), bank transfer receipts, payment screenshots, and rent invoices.</li>
                        <li><strong>Support Tickets:</strong> Maintenance complaints, category choices (plumbing, electrical, Wi-Fi, cleaning), and resolution logs.</li>
                    </ul>
                </section>

                <!-- Section 3 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-gear text-blue-600"></i> 3. How We Use Your Information
                    </h3>
                    <p class="mb-3">We strictly process collected information for legitimate hostel operations and statutory safety purposes:</p>
                    <ul class="list-disc list-inside space-y-1.5 pl-2 text-slate-700">
                        <li>Facilitating seamless QR-code resident onboarding and hostel admission.</li>
                        <li>Executing KYC document audits and bed allocation management.</li>
                        <li>Generating transparent digital rent ledgers, deposit receipts, and utility bills.</li>
                        <li>Contacting nominated emergency guardians in case of medical emergencies or safety alerts.</li>
                        <li>Resolving maintenance and service complaints submitted through the resident app.</li>
                        <li>Ensuring physical premises safety, security audit logs, and fraud prevention.</li>
                    </ul>
                </section>

                <!-- Section 4 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-blue-600"></i> 4. Data Security & Storage
                    </h3>
                    <p>
                        We apply enterprise-grade technical and organizational measures to safeguard your information. All API communications between the mobile application and our servers are transmitted through TLS/HTTPS encrypted connections. Personal credentials and tokens are secured via Laravel Sanctum bearer authentication. Uploaded documents are stored in secure cloud storage buckets with access controls restricted to authorized branch managers and super administrators.
                    </p>
                </section>

                <!-- Section 5 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-handshake-slash text-blue-600"></i> 5. Third-Party Sharing
                    </h3>
                    <p>
                        We <strong>never sell, trade, or rent</strong> resident personal data to marketing aggregators or third-party advertisers. Data is only disclosed when required by law, such as presenting mandatory tenant registers to local law enforcement or municipal authorities upon lawful request.
                    </p>
                </section>

                <!-- Section 6 -->
                <section class="bg-rose-50/70 border border-rose-200 rounded-2xl p-5 text-rose-900">
                    <h3 class="text-base font-bold mb-2 flex items-center gap-2 text-rose-950">
                        <i class="fa-solid fa-user-xmark text-rose-600"></i> 6. Account Deletion & Data Retention Rights
                    </h3>
                    <p class="mb-2 text-xs leading-relaxed">
                        In full compliance with Google Play Store & Apple App Store developer policies, residents retain the right to request deletion of their account and associated personal data at any time.
                    </p>
                    <div class="space-y-1 text-xs text-rose-800">
                        <p><strong>How to delete your account:</strong></p>
                        <ul class="list-disc list-inside pl-2 space-y-1">
                            <li><strong>Inside the Mobile App:</strong> Navigate to <em>Settings &gt; Delete Resident Account</em> and confirm the action.</li>
                            <li><strong>Via Email Request:</strong> Send an email from your registered email address to <a href="mailto:support@rudrapg.com" class="font-bold underline">support@rudrapg.com</a> with the subject line <em>"Account Deletion Request"</em>.</li>
                        </ul>
                        <p class="pt-2">
                            Upon initiation, all active session tokens are revoked immediately. If there are no active room allocations or pending liabilities, personal identifying information is permanently anonymized or deleted from our active databases.
                        </p>
                    </div>
                </section>

                <!-- Section 7 -->
                <section>
                    <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-phone text-blue-600"></i> 7. Contact Us
                    </h3>
                    <p>If you have any questions, concerns, or requests regarding this Privacy Policy or your data, please contact our Data Protection Officer:</p>
                    <div class="mt-3 p-4 bg-slate-100 rounded-xl text-xs space-y-1">
                        <p><strong>Rudra Group PG Management</strong></p>
                        <p>Email: <a href="mailto:support@rudrapg.com" class="text-blue-600 font-semibold">support@rudrapg.com</a></p>
                        <p>Operational Base: Ahmedabad, Gujarat, India</p>
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Process all students and link them to clean user accounts
        $students = Student::all();

        foreach ($students as $student) {
            if (!$student->phone) {
                continue;
            }

            $last10 = substr(preg_replace('/[^0-9]/', '', $student->phone), -10);
            if (strlen($last10) < 10) {
                continue;
            }

            // Find all candidate users by phone
            $matchingUsers = User::whereRaw('SUBSTR(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), -10) = ?', [$last10])->get();

            if ($matchingUsers->isNotEmpty()) {
                // Determine primary user
                $primary = $matchingUsers->firstWhere('id', $student->user_id)
                        ?? $matchingUsers->firstWhere('role', 'STUDENT')
                        ?? $matchingUsers->first();

                // If user had a demo placeholder name or email, overwrite with real resident details
                if (str_contains(strtolower($primary->name), 'demo') || str_contains(strtolower($primary->email), 'demo')) {
                    $primary->name = $student->full_name;
                    if ($student->email && !str_contains($student->email, 'demo')) {
                        $primary->email = $student->email;
                    }
                    $primary->save();
                }

                // Link student to primary user
                if ($student->user_id !== $primary->id) {
                    $student->user_id = $primary->id;
                    $student->save();
                }

                // Handle any duplicate accounts
                if ($matchingUsers->count() > 1) {
                    foreach ($matchingUsers as $other) {
                        if ($other->id !== $primary->id) {
                            if (str_contains(strtolower($other->name), 'demo') || str_contains(strtolower($other->email), 'demo')) {
                                $other->delete();
                            } else {
                                $other->password = $primary->password;
                                $other->save();
                            }
                        }
                    }
                }
            } else {
                // Create user account if student has none
                $newUser = User::create([
                    'name' => $student->full_name,
                    'email' => $student->email ?: ($student->phone . '@rudrapg.com'),
                    'phone' => $student->phone,
                    'password' => Hash::make('RudraStay@2026'),
                    'role' => 'STUDENT',
                    'status' => 'ACTIVE',
                ]);
                $student->user_id = $newUser->id;
                $student->save();
            }
        }

        // 2. Specific fix for Karan (phone 6354351080): activate password '123456' immediately
        $karanStudent = Student::where('phone', 'like', '%6354351080%')
            ->orWhere('full_name', 'like', '%karan%')
            ->first();

        if ($karanStudent) {
            $last10 = substr(preg_replace('/[^0-9]/', '', $karanStudent->phone), -10);
            $karanUsers = User::whereRaw('SUBSTR(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), -10) = ?', [$last10])
                ->orWhere('id', $karanStudent->user_id)
                ->get();

            foreach ($karanUsers as $u) {
                $u->name = $karanStudent->full_name;
                $u->password = Hash::make('123456');
                $u->status = 'ACTIVE';
                $u->tokens()->delete();
                $u->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};


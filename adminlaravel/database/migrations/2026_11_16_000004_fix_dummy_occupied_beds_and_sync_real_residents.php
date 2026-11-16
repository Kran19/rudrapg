<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Student;
use App\Models\RoomAllocation;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Reconcile real resident students
        $students = Student::with(['room', 'bed'])->get();
        foreach ($students as $student) {
            if ($student->bed_id) {
                // Ensure the assigned bed is OCCUPIED
                Bed::where('id', $student->bed_id)->update(['status' => 'OCCUPIED']);

                // Ensure an active room allocation exists
                RoomAllocation::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'bed_id' => $student->bed_id,
                    ],
                    [
                        'branch_id' => $student->branch_id,
                        'room_id' => $student->room_id,
                        'start_date' => $student->joining_date ?? now()->toDateString(),
                        'monthly_rent' => $student->bed?->monthly_rent ?? 6000.00,
                        'security_deposit' => $student->bed?->security_deposit ?? 10000.00,
                        'status' => 'ACTIVE',
                    ]
                );
            } elseif ($student->room_id) {
                // Student has room but no bed assigned, assign the first available bed in this room
                $bed = Bed::where('room_id', $student->room_id)->where('status', 'AVAILABLE')->first()
                    ?? Bed::where('room_id', $student->room_id)->first();

                if ($bed) {
                    $student->update(['bed_id' => $bed->id]);
                    $bed->update(['status' => 'OCCUPIED']);

                    RoomAllocation::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'bed_id' => $bed->id,
                        ],
                        [
                            'branch_id' => $student->branch_id,
                            'room_id' => $student->room_id,
                            'start_date' => $student->joining_date ?? now()->toDateString(),
                            'monthly_rent' => $bed->monthly_rent,
                            'security_deposit' => $bed->security_deposit,
                            'status' => 'ACTIVE',
                        ]
                    );
                }
            }
        }

        // 2. Clean up dummy "OCCUPIED" beds that have NO student and NO allocation
        $validBedIdsWithStudents = Student::whereNotNull('bed_id')->pluck('bed_id')->toArray();
        $validBedIdsWithAllocations = RoomAllocation::where('status', 'ACTIVE')->pluck('bed_id')->toArray();
        $validBedIds = array_unique(array_merge($validBedIdsWithStudents, $validBedIdsWithAllocations));

        // Any bed marked OCCUPIED or RESERVED without an actual customer was from the dummy seeder
        Bed::whereNotIn('id', $validBedIds)
            ->whereIn('status', ['OCCUPIED', 'RESERVED'])
            ->update(['status' => 'AVAILABLE']);

        // 3. Recalculate room statuses
        $rooms = Room::with('beds')->get();
        foreach ($rooms as $room) {
            $totalBeds = $room->beds->count();
            $occupiedCount = $room->beds->where('status', 'OCCUPIED')->count();
            $room->update([
                'status' => ($occupiedCount >= $totalBeds && $totalBeds > 0) ? 'FULL' : 'AVAILABLE'
            ]);
        }
    }

    public function down(): void
    {
        // Safe no-op
    }
};


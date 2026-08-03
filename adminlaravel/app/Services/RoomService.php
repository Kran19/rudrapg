<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $room = Room::create([
                'branch_id' => $data['branch_id'],
                'floor_number' => $data['floor_number'],
                'room_number' => $data['room_number'],
                'sharing_type' => $data['sharing_type'] ?? '2 Sharing AC',
                'max_beds' => $data['max_beds'] ?? 2,
                'is_ac' => $data['is_ac'] ?? true,
                'description' => $data['description'] ?? null,
                'facilities' => $data['facilities'] ?? ['WiFi', 'AC', 'Housekeeping'],
                'status' => 'AVAILABLE',
            ]);

            // Automatically create beds for this room
            $maxBeds = $room->max_beds;
            $rent = $data['monthly_rent'] ?? 6500.00;
            $deposit = $data['security_deposit'] ?? 10000.00;

            for ($b = 1; $b <= $maxBeds; $b++) {
                $bedLetter = chr(64 + $b);
                Bed::create([
                    'room_id' => $room->id,
                    'bed_code' => 'Bed '.$room->floor_number.$bedLetter,
                    'monthly_rent' => $rent,
                    'security_deposit' => $deposit,
                    'status' => 'AVAILABLE',
                ]);
            }

            return $room->load('beds');
        });
    }

    public function updateRoom(int $id, array $data): Room
    {
        $room = Room::findOrFail($id);
        $room->update($data);

        return $room->load('beds');
    }

    public function updateBedStatus(int $bedId, string $status): Bed
    {
        $bed = Bed::findOrFail($bedId);
        $bed->update(['status' => $status]);

        // Auto calculate room status based on beds
        $room = $bed->room;
        $occupiedCount = $room->beds()->where('status', 'OCCUPIED')->count();
        if ($occupiedCount >= $room->max_beds) {
            $room->update(['status' => 'FULL']);
        } else {
            $room->update(['status' => 'AVAILABLE']);
        }

        return $bed;
    }
}

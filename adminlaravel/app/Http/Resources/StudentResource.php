<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'app_reference' => $this->app_reference,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'aadhaar_number' => $this->aadhaar_number,
            'pan_number' => $this->pan_number,
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'emergency_contact' => $this->emergency_contact,
            'current_address' => $this->current_address,
            'joining_date' => $this->joining_date?->format('d M Y'),
            'kyc_status' => $this->kyc_status,
            'rent_status' => $this->rent_status,
            'deposit_status' => $this->deposit_status,
            'status' => $this->status,
            'is_room_assigned' => !is_null($this->bed_id),
            'allocation_status' => is_null($this->bed_id) ? 'PENDING_ROOM_ALLOCATION' : 'ROOM_ALLOCATED',
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'room' => $this->whenLoaded('room', function () {
                return [
                    'floor_number' => $this->room->floor_number,
                    'room_number' => $this->room->room_number,
                    'sharing_type' => $this->room->sharing_type,
                    'is_ac' => $this->room->is_ac,
                ];
            }),
            'bed' => $this->whenLoaded('bed', function () {
                return [
                    'bed_code' => $this->bed->bed_code,
                    'monthly_rent' => $this->bed->monthly_rent,
                    'security_deposit' => $this->bed->security_deposit,
                ];
            }),
        ];
    }
}

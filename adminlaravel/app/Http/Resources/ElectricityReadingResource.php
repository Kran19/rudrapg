<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectricityReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rawStatus = strtoupper(trim($this->status ?? 'PENDING'));
        $isApproved = in_array($rawStatus, ['APPROVED', 'CONFIRMED', 'PAID', 'VERIFIED', 'AUDITED', 'SOLVED', 'RESOLVED']);
        $isRejected = in_array($rawStatus, ['REJECTED', 'DECLINED']);

        $statusLabel = 'PENDING AUDIT';
        if ($isApproved) {
            $statusLabel = 'APPROVED & CONFIRMED';
        } elseif ($isRejected) {
            $statusLabel = 'REJECTED';
        }

        return [
            'id' => $this->id,
            'reading_month' => $this->reading_month,
            'current_reading' => $this->current_reading,
            'previous_reading' => $this->previous_reading,
            'units_consumed' => $this->units_consumed,
            'unit_rate' => $this->unit_rate,
            'total_amount' => $this->total_amount,
            'meter_photo_path' => $this->meter_photo_path,
            'status' => $isApproved ? 'APPROVED' : ($isRejected ? 'REJECTED' : 'PENDING'),
            'raw_status' => $this->status,
            'status_label' => $statusLabel,
            'is_approved' => $isApproved,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectricityReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reading_month' => $this->reading_month,
            'current_reading' => $this->current_reading,
            'previous_reading' => $this->previous_reading,
            'units_consumed' => $this->units_consumed,
            'unit_rate' => $this->unit_rate,
            'total_amount' => $this->total_amount,
            'meter_photo_path' => $this->meter_photo_path,
            'status' => $this->status,
        ];
    }
}

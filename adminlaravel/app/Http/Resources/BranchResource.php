<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_name' => $this->manager_name,
            'manager_phone' => $this->manager_phone,
            'electricity_unit_rate' => $this->electricity_unit_rate,
            'qr_code_hash' => $this->qr_code_hash,
            'status' => $this->status,
        ];
    }
}

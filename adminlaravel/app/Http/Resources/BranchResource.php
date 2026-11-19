<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $managerName = $this->manager_name ?: ($this->subAdmins()->first()?->name ?? 'Branch Manager');
        $managerPhone = $this->manager_phone ?: ($this->subAdmins()->first()?->phone ?? ($this->phone ?? 'N/A'));

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_name' => $managerName,
            'manager_phone' => $managerPhone,
            'electricity_unit_rate' => $this->electricity_unit_rate,
            'qr_code_hash' => $this->qr_code_hash,
            'status' => $this->status,
        ];
    }
}

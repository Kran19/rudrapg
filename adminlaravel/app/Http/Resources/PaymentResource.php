<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tx_reference' => $this->tx_reference,
            'payment_type' => $this->payment_type,
            'amount' => (float)$this->amount,
            'total_due' => $this->total_due !== null ? (float)$this->total_due : (float)$this->amount,
            'remaining_amount' => (float)($this->remaining_amount ?? 0),
            'amount_left' => (float)($this->remaining_amount ?? 0),
            'is_partial' => (float)($this->remaining_amount ?? 0) > 0,
            'payment_mode' => $this->payment_mode,
            'payment_date' => $this->payment_date?->format('d M Y'),
            'status' => $this->status,
            'proof' => $this->whenLoaded('proof', function () {
                return [
                    'utr_number' => $this->proof->utr_number,
                    'screenshot_path' => $this->proof->screenshot_path,
                    'status' => $this->proof->status,
                ];
            }),
        ];
    }
}

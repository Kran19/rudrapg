<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rawStatus = strtoupper(trim($this->status ?? 'PENDING'));
        $isResolved = in_array($rawStatus, ['RESOLVED', 'SOLVED', 'APPROVED', 'CONFIRMED', 'CLOSED', 'DONE']);
        $isInProgress = in_array($rawStatus, ['IN_PROGRESS', 'INPROGRESS', 'IN PROGRESS']);
        $isOpen = in_array($rawStatus, ['OPEN']);

        $statusLabel = 'PENDING';
        if ($isResolved) {
            $statusLabel = 'APPROVED & SOLVED';
        } elseif ($isInProgress) {
            $statusLabel = 'IN PROGRESS';
        } elseif ($isOpen) {
            $statusLabel = 'OPEN';
        }

        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $isResolved ? 'SOLVED' : ($isInProgress ? 'IN_PROGRESS' : ($isOpen ? 'OPEN' : 'PENDING')),
            'raw_status' => $this->status,
            'status_label' => $statusLabel,
            'is_resolved' => $isResolved,
            'resolution_remarks' => $this->resolution_remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

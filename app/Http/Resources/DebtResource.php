<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'name' => $this->name,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'overdue' => $this->calculateOverdue(),
        ];
    }

    protected function calculateOverdue()
    {
        if ($this->type === 'Alseco') {
            $dueDate = Carbon::parse($this->due_date);
            return $dueDate->diffInMonths(now()) > 2;
        }

        if ($this->type === 'IVC') {
            return $this->amount > 20000;
        }

        return false;
    }
}

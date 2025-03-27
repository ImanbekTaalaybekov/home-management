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
            'deeplink'  => "kaspi://payment?service={$this->type}&amount={$this->amount}"
        ];
    }

    protected function calculateOverdue()
    {
        if ($this->type === 'Alseco') {
            if (is_null($this->due_date)) {
                return false;
            }
            $dueDate = Carbon::parse($this->due_date);
            return $dueDate->diffInDays(now()) > 60;
        }

        if ($this->type === 'IVC') {
            if (is_null($this->amount)) {
                return false;
            }
            return $this->amount > 20000;
        }
        return false;
    }
}

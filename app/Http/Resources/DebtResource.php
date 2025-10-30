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
            'current_charges' => $this->current_charges,
            'opening_balance' => round($this->amount - $this->current_charges, 2),
            'due_date' => $this->due_date,
            'overdue' => $this->calculateOverdue(),
            'deeplink'  => "kaspi://",
            'period_start_balance' => $this->period_start_balance,
            'initial_amount' => $this->initial_amount,
            'payment_amount' => $this->payment_amount,
            'payment_date' => $this->due_date
            //'deeplink'  => "kaspi://payment?service={$this->type}&amount={$this->amount}"
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

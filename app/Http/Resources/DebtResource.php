<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray($request)
    {
        $amount = (float) $this->amount;
        $currentCharges = (float) $this->current_charges;

        return [
            'name'            => $this->name,
            'type'            => $this->type,
            'amount'          => $amount,
            'current_charges' => $currentCharges,
            'opening_balance' => $amount - $currentCharges,
            'due_date'        => $this->due_date,
            'overdue'         => $this->calculateOverdue(),
            'deeplink'        => "kaspi://payment?service={$this->type}&amount={$amount}",
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

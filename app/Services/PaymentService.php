<?php
namespace App\Services;

use App\Models\Debt;
use App\Models\Notification;

class PaymentService
{
    public function processPayments($filePath)
    {
        $file = fopen($filePath, 'r');
        while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
            $userId = $data[0];
            $type = $data[1];
            $name = $data[2];
            $amount = $data[3];
            $dueDate = $data[4];

            Debt::create([
                'user_id' => $userId,
                'type' => $type,
                'name' => $name,
                'amount' => $amount,
                'due_date' => $dueDate,
            ]);
        }
        fclose($file);

        $this->sendPaymentNotifications();
    }

    public function sendPaymentNotifications()
    {
        $debts = Debt::where('due_date', '<=', now()->addMonth())
        ->get();

        foreach ($debts as $debt) {
            $user = $debt->user;
            $message = "Уважаемый {$user->name}, ваш долг за {$debt->type} ({$debt->name}) составляет {$debt->amount} тенге. Срок оплаты до {$debt->due_date}.";

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Напоминание о долге',
                'message' => $message,
                'type' => 'personal',
            ]);
        }
    }
}

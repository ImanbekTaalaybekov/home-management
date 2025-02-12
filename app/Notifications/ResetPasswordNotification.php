<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * Токен для сброса пароля.
     *
     * @var string
     */
    public $token;

    /**
     * Создайте экземпляр уведомления.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Укажите каналы доставки уведомления.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // Указываем, что уведомление будет отправлено по email
    }

    /**
     * Уведомление отправляется по email.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ваш код для сброса пароля')
            ->line('Вы запросили сброс пароля. Ваш код:')
            ->line("**{$this->token}**")
            ->line('Если вы не запрашивали сброс пароля, просто проигнорируйте это сообщение.');
    }
}


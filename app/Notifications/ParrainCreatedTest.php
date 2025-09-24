<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParrainCreatedTest extends Notification
{
    use Queueable;

    public $user;
    public $lienParrainage;
    public $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $lienParrainage, $password)
    {
        $this->user = $user;
        $this->lienParrainage = $lienParrainage;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification using a Blade template.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue comme Parrain sur Vendeur Pro !')
            ->view('notifications.parrain_created', [ // <-- chemin correct
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'password' => $this->password,
                'lienParrainage' => $this->lienParrainage,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'lienParrainage' => $this->lienParrainage,
            'password' => $this->password,
        ];
    }
}

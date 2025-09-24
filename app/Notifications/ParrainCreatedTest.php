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
    public $password; // ✅ ajout du mot de passe

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $lienParrainage, $password)
    {
        $this->user = $user;
        $this->lienParrainage = $lienParrainage;
        $this->password = $password; // ✅ stocker le mot de passe
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue comme Parrain (Test)')
            ->greeting('Bonjour ' . $this->user->name)
            ->line('Vous avez été inscrit en tant que Parrain sur notre site.')
            ->line('Voici vos coordonnées :')
            ->line('📧 Email : ' . $this->user->email)
            ->line('📱 Téléphone : ' . $this->user->phone)
            ->line('🔑 Mot de passe temporaire : ' . $this->password) // ✅ affichage du mot de passe
            ->action('Lien de parrainage', $this->lienParrainage)
            ->line('Merci de votre engagement !');
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
            'password' => $this->password, // ✅ ajouté ici aussi
        ];
    }
}

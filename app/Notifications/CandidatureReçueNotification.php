<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidatureReçueNotification extends Notification
{
    use Queueable;

    protected $formationTitre;

    /**
     * Create a new notification instance.
     */
    public function __construct($formationTitre)
    {
        $this->formationTitre = $formationTitre;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Candidature reçue')
            ->greeting('Bonjour ' . $notifiable->nom)
            ->line("Nous avons bien reçu votre candidature pour la formation : **{$this->formationTitre}**.")
            ->line('Notre équipe va l\'étudier et vous recontactera très bientôt.')
            ->salutation('Cordialement, l\'équipe VendeurPro');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'formation' => $this->formationTitre,
        ];
    }
}

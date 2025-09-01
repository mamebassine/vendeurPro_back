<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StatutCandidatureModifie extends Notification
{
    use Queueable;

    protected $statut;

    public function __construct($statut)
    {
        $this->statut = $statut;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = new MailMessage();
        $message->subject('Mise à jour de votre candidature')
                ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom . ',')
                ->line("Le statut de votre candidature a été mis à jour.")
                ->line("Nouveau statut : **" . strtoupper($this->statut) . "**");

        if ($this->statut === 'acceptée') {
            $message->line('Félicitations ! Votre candidature a été acceptée.');
        } elseif ($this->statut === 'refusée') {
            $message->line('Nous sommes désolés, votre candidature a été refusée.');
        } else {
            $message->line('Votre candidature est en attente de traitement.');
        }

        $message->line('Merci de votre confiance.');

        return $message;
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UserRegisterNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $urlToActivation = 'https://wagedollar.io/confirmAccount?activation_code='.$this->data['activation_code'];

        return (new MailMessage)
                    ->greeting('Hola '.$this->data['user'].'!')
                    ->line(($this->data['cp_next_login']??false)?'Tu clave temporal para iniciar sesion por primera vez se encuentra a continuacion:':'')
                    ->line(($this->data['cp_next_login']??false)?new HtmlString('Clave temporal: '.'<b>'.$this->data['temporal_password'].'</b>'):'')
                    ->line('Por favor, verifica tu cuenta haciendo click en el botón de abajo.')
                    ->action('Verificar Cuenta', $urlToActivation)
                    ->line('Si usted no creo la cuenta, por favor ignore este correo.')
                    ->subject('Wage Dollar - Activación de cuenta');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

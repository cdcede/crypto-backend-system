<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class APIPasswordResetNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    //protected $reset_code;
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
        $urlToResetForm = 'https://wagedollar.io/reset-password?email_token='.$this->data['email_token'];

        return (new MailMessage)
                    ->greeting('Hola '.$this->data['name'].'!')
                    ->line('Se ha solicitado un restablecimiento de contraseña para la cuenta asociada a este correo electrónico.')
                    ->line('Por favor ingrese el codigo de abajo en la página de reestablecimiento de contraseña.')
                    ->line(new HtmlString('<h1 class=text-center>'.$this->data['secret_code'].'</h1>'))
                    ->action('Reestablecer Contraseña', $urlToResetForm)
                    //->action('Reestablecer Contraseña', url('app.wagedollars.com/reset-password-token?token='.$this->data['email_url']))
                    //->action('Reestablecer Contraseña', new HtmlString('app.wagedollars.com/reset-password-token?token='.$this->data['email_url']))
                    ->line('Si usted no realizó esta solicitud, por favor ignore este mensaje.')
                    ->subject('Wage Dollar - Reseteo de contraseña');
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

<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvoiceNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Invoice $invoice)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'invoice',
            'title' => 'Tagihan Baru: '.($this->invoice->title ?? 'Sewa Kamar'),
            'message' => 'Anda memiliki tagihan baru sebesar Rp'.number_format($this->invoice->amount, 0, ',', '.').' dengan jatuh tempo '.$this->invoice->due_date->format('d M Y').'.',
            'url' => route('tenant.invoices.show', $this->invoice),
        ];
    }
}

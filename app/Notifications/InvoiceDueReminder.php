<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceDueReminder extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice, public int $daysBeforeDue) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $boardingHouse = $this->invoice->lease->boardingHouse;

        return (new MailMessage)
            ->subject("Pengingat tagihan {$this->invoice->number}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Tagihan kos {$boardingHouse->name} akan jatuh tempo dalam {$this->daysBeforeDue} hari.")
            ->line('Nominal: Rp'.number_format($this->invoice->amount, 0, ',', '.'))
            ->line('Jatuh tempo: '.$this->invoice->due_date->format('d M Y'))
            ->action('Lihat Tagihan', route('tenant.invoices.show', $this->invoice))
            ->line('Abaikan pesan ini jika pembayaran sudah dikonfirmasi.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'days_before_due' => $this->daysBeforeDue,
            'amount' => $this->invoice->amount,
            'due_date' => $this->invoice->due_date->toDateString(),
        ];
    }
}

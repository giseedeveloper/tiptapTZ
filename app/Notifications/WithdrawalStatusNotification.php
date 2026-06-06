<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal,
        public string $status,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = config('tiptap.currency_symbol');
        $amount = number_format((float) $this->withdrawal->amount, 0);
        $subject = $this->status === 'approved'
            ? 'Withdrawal approved'
            : 'Withdrawal rejected';

        $mail = (new MailMessage)
            ->subject($subject.' · '.$symbol.' '.$amount)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->messageText());

        if ($this->status === 'rejected' && filled($this->withdrawal->admin_note)) {
            $mail->line('Admin note: '.$this->withdrawal->admin_note);
        }

        return $mail
            ->action('View wallet', route('manager.wallet.index'))
            ->line('Thank you for using TIPTAP.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'restaurant_id' => $this->withdrawal->restaurant_id,
            'amount' => (float) $this->withdrawal->amount,
            'status' => $this->status,
            'admin_note' => $this->withdrawal->admin_note,
            'message' => $this->messageText(),
            'type' => 'withdrawal_status',
        ];
    }

    private function messageText(): string
    {
        $symbol = config('tiptap.currency_symbol');
        $amount = number_format((float) $this->withdrawal->amount, 0);

        if ($this->status === 'approved') {
            return "Your withdrawal request of {$symbol} {$amount} was approved. Admin will process the payout.";
        }

        $base = "Your withdrawal request of {$symbol} {$amount} was rejected.";

        if (filled($this->withdrawal->admin_note)) {
            return $base.' Reason: '.$this->withdrawal->admin_note;
        }

        return $base;
    }
}

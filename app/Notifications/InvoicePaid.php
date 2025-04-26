<?php

namespace App\Notifications;

use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;
use App\Models\UserContract;
use App\Models\User;

class InvoicePaid extends Notification
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        if (empty($notifiable->telegram_id)) {
            return null;
        }
        $userContract = UserContract::where('id', $this->data->user_contract_id)->first();
        $user = User::where('id', $userContract->user_id)->first();
        // Create URLs for viewing and downloading the invoice
        $viewUrl = route('payment-details.show', ['id' => $user->id]);
        $downloadUrl = route('invoice.download', ['id' => $user->id]);
        
        // Format amount with currency symbol if available
        $amount = number_format($this->data->amount, 2);
        $currency = $this->data->currency ?? '$';
        $formattedAmount = "{$currency}{$amount}";
        
        // Get invoice number or ID
        $invoiceNumber = $this->data->invoice_no ?? $this->data->id;
        
        return TelegramMessage::create()
            ->to($notifiable->telegram_id)
            ->content("📋 *Invoice #{$invoiceNumber}*")
            ->line("Hello {$notifiable->name},")
            ->line("Your new invoice has been generated.")
            ->line("*Amount Due:* {$formattedAmount}")
            ->line("*Due Date:* " . ($this->data->payment_date ? date('M d, Y', strtotime($this->data->payment_date)) : 'N/A'))
            ->line("Please check the details and make your payment on time.")
            ->line("Thank you!")
            ->button('View Invoice', $viewUrl)
            ->button('Download Invoice', $downloadUrl);
    }
}
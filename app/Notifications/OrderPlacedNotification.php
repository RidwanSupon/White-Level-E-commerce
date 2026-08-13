<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order Confirmation #{$this->order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Thank you for your order with " . setting('site_name', 'LuxeCart') . "!")
            ->line("Order Number: #{$this->order->order_number}")
            ->line("Total Amount: " . format_price($this->order->grand_total))
            ->action('Track Your Order', route('customer.orders.show', $this->order->id))
            ->line('We are preparing your items for shipment.');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->grand_total,
        ];
    }
}

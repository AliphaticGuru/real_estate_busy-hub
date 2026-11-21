<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class InquiryReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry)
    {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = Str::headline($this->inquiry->type->value);
        $firstName = Str::before($this->inquiry->name, ' ');
        $excerpt = Str::limit($this->inquiry->message, 200);

        return (new MailMessage)
            ->subject(__('New :type inquiry', ['type' => $type]))
            ->greeting(__('New inquiry received'))
            ->line(__('Listing: :title', ['title' => $this->inquiry->listing?->title ?? __('General inquiry')]))
            ->line(__('Type: :type', ['type' => $type]))
            ->line(__('From: :name', ['name' => $firstName]))
            ->line(__('Message: :excerpt', ['excerpt' => $excerpt]))
            ->action(__('View inquiry'), route('inquiries.show', $this->inquiry))
            ->salutation(__('Regards, :app', ['app' => config('app.name')]));
    }
}

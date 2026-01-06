<?php

namespace App\Jobs;

use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendContactEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContactMessage $contactMessage
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationEmail = config('contact.notification_email');

        if (!$notificationEmail) {
            Log::warning('Contact notification email not configured. Message ID: ' . $this->contactMessage->id);
            return;
        }

        try {
            Mail::to($notificationEmail)->send(new NewContactMessageMail($this->contactMessage));
            Log::info('Contact email sent for Message ID: ' . $this->contactMessage->id);
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
            throw $e; // Retry job
        }
    }
}

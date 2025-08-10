
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCampaign extends Mailable
{
    use Queueable, SerializesModels;

    protected $campaign;
    protected $user;

    /**
     * Create a new message instance.
     */
    public function __construct($campaign, $user)
    {
        $this->campaign = $campaign;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->from($this->campaign->sender_email, $this->campaign->sender_name)
            ->subject($this->campaign->subject)
            ->view('emails.campaign')
            ->with([
                'campaign' => $this->campaign,
                'user' => $this->user,
            ]);

        // Attach any files if they exist
        if ($this->campaign->attachments) {
            foreach ($this->campaign->attachments as $attachment) {
                $email->attach($attachment['path'], [
                    'as' => $attachment['name'] ?? null,
                    'mime' => $attachment['mime'] => null,
                ]);
            }
        }

        return $email;
    }
}

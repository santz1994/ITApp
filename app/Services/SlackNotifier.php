<?php

namespace App\Services;

class SlackNotifier
{
    protected $webhook;
    protected $enabled;
    protected $defaultChannel;
    protected $defaultUsername;

    public function __construct()
    {
    $this->webhook = \getenv('SLACK_WEBHOOK');
    $this->enabled = filter_var(\getenv('SLACK_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN);
    $this->defaultChannel = \getenv('SLACK_CHANNEL');
    $this->defaultUsername = \getenv('SLACK_BOT_NAME');
    }

    public function notify(string $message, string $channel = null, string $username = null)
    {
        if (! $this->enabled) {
            return false;
        }

        if (empty($this->webhook)) {
            return false;
        }

        $payload = ['text' => $message];
    $channel = $channel ?? $this->defaultChannel;
    $username = $username ?? $this->defaultUsername;
    if ($channel) $payload['channel'] = $channel;
    if ($username) $payload['username'] = $username;

        $json = json_encode($payload);

        $ch = curl_init($this->webhook);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => $json]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result !== false;
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use Illuminate\Support\Facades\Http;

class Webhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 25;

    protected $post;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Webhook job: start');

        try {
            $url = 'localhost';

            $data = [
                'post_id' => $this->post->id,
                'title' => $this->post->title,
                'body' => $this->post->body,
            ];

            // http timeout
            $response = Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            Log::info('Webhook job: end');
        } catch (Exception $e) {
            Log::info('Webhook job: exception');
        }

    }

    public function failed(\Exception $exception)
    {
        // job timeout

        Log::info('Webhook job: failed');
    }
}

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

        // $url = 'http://127.0.0.1:5050/receive';
        // Docker for Macの場合、ホストマシンを指すIPアドレスは host.docker.internal
        $url = 'http://host.docker.internal:5050/receive';
        $data = [
            'post_id' => $this->post->id,
            'title' => $this->post->title,
            'body' => $this->post->body,
        ];

        $key = 'test_key';
        $response = Http::timeout(10)
        ->withHeaders([
            'Content-Type' => 'application/json',
            'x-api-key' => $key,
        ])->post($url, $data);

        Log::info($response);
        $statusCode = $response->getStatusCode();
        $body = $response->getBody();
        // Log::info($statusCode);
        // Log::info($body);

        if ($response->successful()) {
            $recoveryData = json_encode([
                // 'type' => 'recovery_webhook_target',
                'endpoint_url' => $url,
                'api_key' => $key,
                'data' => $data,
            ]);

            // ジョブ実施でエラーになれば、復旧ログに出力
            Log::channel('recoveryWebhook')->info($recoveryData);

        }

        // if ($response->failed()) {
        //     // エラー情報を取得
        //     $error = $response->body(); // レスポンスボディの取得
        //     Log::error("Request failed with response: $error");
        // }

        Log::info('Webhook job: end');
    }

    public function failed(\Exception $exception)
    {
        // job timeout

        Log::info('Webhook job: failed');
    }
}

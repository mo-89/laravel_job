<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecoveryWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recovery-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('recoveryWebhook')->info('start recovery webhook script');

        //ログファイルを選択
        $dir = '';
        $now = new Carbon();
        $today = $now->format('Y-m-d');
        $laravelLogFilePath = storage_path("logs/laravel-{$today}.log");
        if (!file_exists($laravelLogFilePath)) {
            Log::channel('recoveryWebhook')->info('file not exist');
        }

        Log::channel('recoveryWebhook')->info($laravelLogFilePath);

        // 読み込んで、POSTデータの取得

        // 直近2時間分のログを取得と仮定
        // 探索範囲は定数にする
        $searchRangeTimestamp = Carbon::now()->subHours(2)->timestamp;

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/';
        $handle = fopen($laravelLogFilePath, 'r');
        if ($handle) {
            while(($line = fgets($handle)) !== false) {
                if (preg_match($pattern, $line, $matches)) {
                    $logTimestamp = Carbon::parse($matches[1])->timestamp;
                    if ($logTimestamp >= $searchRangeTimestamp) {

                        //todo エラーの場合データを取得
                        Log::channel('recoveryWebhook')->info($line);
                    }
                }
            }
            fclose($handle);
        } else {

        }

        // job再実行

        Log::channel('recoveryWebhook')->info('finished recovery webhook script');
    }
}

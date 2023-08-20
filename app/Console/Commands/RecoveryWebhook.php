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

        $now = new Carbon();
        $today = $now->format('Y-m-d');
        $recoveryLogFilePath = storage_path("logs/recovery-webhook-{$today}.log");
        if (!file_exists($recoveryLogFilePath)) {
            Log::channel('recoveryWebhook')->info('file not exist');
        }

        // 直近2時間分のログを取得と仮定
        // 探索範囲は定数にする
        $searchRangeHours = 1;
        $searchRangeTimestamp = Carbon::now()->subHours($searchRangeHours)->timestamp;

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/';
        $handle = fopen($recoveryLogFilePath, 'r');
        if ($handle) {
            while(($line = fgets($handle)) !== false) {
                if (preg_match($pattern, $line, $matches)) {
                    $logTimestamp = Carbon::parse($matches[1])->timestamp;
                    if ($logTimestamp < $searchRangeTimestamp) {
                        // 対象期間より前のログのためスキップ
                        continue;
                    }
                } else {
                    // 日時の部分がマッチしないためスキップ
                    continue;
                }

                $jsonPart = strstr($line, '{');
                if ($jsonPart === false) {
                    continue;
                }

                $decodedRecoveryData = json_decode($jsonPart, true);
                Log::channel('recoveryWebhook')->info($decodedRecoveryData);

                // ジョブの再実施
            }
            fclose($handle);
        } else {

        }

        // job再実行

        Log::channel('recoveryWebhook')->info('finished recovery webhook script');
    }
}

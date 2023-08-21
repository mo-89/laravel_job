<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\PostRequest;
use App\Jobs\Webhook;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
// use GuzzleHttp\Exception\ConnectException;

class PostController extends Controller
{
    public function index() {
        $posts = Post::latest()->get();

        return view('index')
            ->with(['posts' => $posts]);
    }

    public function show(Post $post)
    {
        return view('posts.show')
            ->with(['post' => $post]);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->body = $request->body;
        $post->save();

        Log::debug('PostController::store()');
        try {
            Webhook::dispatch($post);
        } catch (ConnectException $e){
            // 例外でキャッチできない
            Log::channel('recoveryWebhook')->info('接続例外');
            // 接続できない例外時も復旧用ログファイルに出力
            // webhook情報はどうやって取得するか？
            //   -> dispatchを設定情報でループしてるから、そこから取得できそう
            // Postを渡してOKか？

            $recoveryData = json_encode([
                // 'endpoint_url' => $url,
                // 'api_key' => $key,
                'data' => $post,
            ]);

            // ジョブ実施でエラーになれば、復旧ログに出力
            Log::channel('recoveryWebhook')->info($recoveryData);

        } catch (Exception $e){
            // その他のエラー処理
            Log::channel('recoveryWebhook')->info('その他の例外');


            // ジョブ実施でエラーになれば、復旧ログに出力
            Log::channel('recoveryWebhook')->info($recoveryData);
        }

        return redirect()
            ->route('posts.index');
    }

    public function edit(Post $post)
    {
        return view('posts.edit')
            ->with(['post' => $post]);
    }


    public function update(PostRequest $request, Post $post)
    {
        $post->title = $request->title;
        $post->body = $request->body;
        $post->save();

        return redirect()
            ->route('posts.show', $post);
    }

    public function destroy(Post $post)
    {
        Log::error('PostController::destroy()');
        Log::info($post);

        $post->delete();

        return redirect()
            ->route('posts.index');
    }

}

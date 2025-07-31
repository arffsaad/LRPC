<?php

namespace ArffSaad\LRPC\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use JsonRPC\Server;

class LRPCController
{
    public function handle(Request $request)
    {
        $server = new Server;

        // Register the "metadata" method
        $server->register('metadata', function () {
            $internalNamespace = config('lrpc.namespaces.internal', 'App\\Lrpc\\Internal');
            $path = base_path(str_replace('\\', '/', $internalNamespace).'/.metadata.json');

            if (! File::exists($path)) {
                throw new \Exception('.metadata.json file is missing.', -32603);
            }

            return json_decode(File::get($path), true);
        });

        $response = $server->execute();

        return response($response, 200, ['Content-Type' => 'application/json']);
    }
}

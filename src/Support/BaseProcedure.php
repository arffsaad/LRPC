<?php

namespace ArffSaad\LRPC\Support;

use Spatie\LaravelData\Data;
use JsonRPC\Client as RPCClient;
use Illuminate\Support\Facades\Http;

abstract class BaseProcedure
{
    /**
     * Return the request DTO class used by this procedure.
     *
     * @return class-string<Data>
     */
    abstract protected static function requestType(): string;

    /**
     * Return the response DTO class used by this procedure.
     *
     * @return class-string<Data>
     */
    abstract protected static function responseType(): string;

    /**
     * Perform a typed external RPC call to the target service.
     *
     * @template TRequest of Data
     * @template TResponse of Data
     *
     * @param TRequest $request
     * @return TResponse
     */
    public static function call(Data $request): Data
    {
        $service = static::targetServiceName();
        $method = static::methodName();

        $url = config("lrpc.services.$service.url");

        $authHeader = config("lrpc.services.$service.auth");

        if (! $url) {
            throw new \RuntimeException("No URL defined for service: $service");
        }

        // Setup JSON-RPC client
        $client = new RPCClient($url);

        $client->withHttpClient(function ($url, $payload) use ($authHeader): string {
            $req = Http::withHeaders([
                'Content-Type' => 'application/json',
            ]);

            if ($authHeader && $authHeader !== "") {
                $req = $req->withHeaders([
                    'Authorization' => $authHeader,
                ]);
            }

            return $req->post($url, $payload)->body();
        });

        // Make the JSON-RPC call
        $responseData = $client->execute($method, $request->toArray());

        $responseClass = static::responseType();

        return $responseClass::from($responseData);
    }

    /**
     * Handle an internal request. Useful to avoid coding twice for the same logic.
     *
     * @param array $payload
     * @return Data
     */
    public static function handle(array $payload): Data
    {
        $requestClass = static::requestType();
        $responseClass = static::responseType();

        /** @var Data $request */
        $request = $requestClass::from($payload);

        /** @var Data $response */
        $response = static::process($request);

        if (! $response instanceof $responseClass) {
            throw new \RuntimeException(
                'Response returned from process() does not match declared response type.'
            );
        }

        return $response;
    }

    /**
     * Internal logic that executes on the provider service.
     *
     * @param Data $request
     * @return Data
     */
    abstract protected static function process(Data $request): Data;

    /**
     * Used in external procedures to determine which service to contact.
     */
    protected static function targetServiceName(): string
    {
        return static::$service ?? throw new \RuntimeException('Missing $service static property.');
    }

    /**
     * Used in JSON-RPC to determine method name (e.g., procedure name).
     */
    protected static function methodName(): string
    {
        return static::$method ?? class_basename(static::class);
    }
}

<?php declare(strict_types=1);

namespace Bref\Lambda;

use AsyncAws\Lambda\LambdaClient;

/**
 * A simpler alternative to the official LambdaClient from the AWS SDK.
 */
final class SimpleLambdaClient
{
    /** @var LambdaClient */
    private $lambda;

    public function __construct(string $region)
    {
        $this->lambda = new LambdaClient([
            'region' => $region,
        ]);
    }

    /**
     * Synchronously invoke a function.
     *
     * @param mixed $event Event data (can be null).
     * @throws InvocationFailed
     */
    public function invoke(string $functionName, $event = null): InvocationResult
    {
        $rawResult = $this->lambda->invoke([
            'FunctionName' => $functionName,
            'LogType' => 'Tail',
            'Payload' => $event ?? '',
        ]);

        $resultPayload = json_decode($rawResult->getPayload(), true);
        $invocationResult = new InvocationResult($rawResult, $resultPayload);

        $error = $rawResult->getFunctionError();
        if ($error) {
            throw new InvocationFailed($invocationResult);
        }

        return $invocationResult;
    }
}

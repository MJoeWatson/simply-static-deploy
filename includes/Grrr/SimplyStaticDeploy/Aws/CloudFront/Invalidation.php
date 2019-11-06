<?php namespace Grrr\SimplyStaticDeploy\Aws\CloudFront;

use Sentry;
use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\Exception\CloudFrontException;
use Aws\Exception\AwsException;

class Invalidation {

    protected $_region;
    protected $_distributionId;

    public function __construct(CloudFrontClient $client, string $distributionId) {
        $this->_client = $client;
        $this->_distributionId = $distributionId;
    }

    /**
     * Invalidate the distribution.
     *
     * @return WP_Error|bool
     */
    public function invalidate(array $items) {
        try {
            $this->_client->createInvalidation([
                'DistributionId' => $this->_distributionId,
                'InvalidationBatch' => [
                    'CallerReference' => $this->_distributionId . ' ' . time(),
                    'Paths' => [
                        'Items' => $items,
                        'Quantity' => count($items),
                    ],
                ],
            ]);
            return true;
        } catch (CloudFrontException $error) {
            Sentry\captureException($error);
            $message = $error->getMessage();
        } catch (AwsException $error) {
            Sentry\captureException($error);
            $message = $error->getAwsRequestId() . PHP_EOL;
            $message .= $error->getAwsErrorType() . PHP_EOL;
            $message .= $error->getAwsErrorCode() . PHP_EOL;
        } catch (\Exception $error) {
            Sentry\captureException($error);
            $message = $error;
        }

        return new \WP_Error('cloudfront_invalidation_error', sprintf( __("Could not invalidate CloudFront distribution: %s", 'grrr'), $message), [
            'status' => 400,
        ]);
    }
}


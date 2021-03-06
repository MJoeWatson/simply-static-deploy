<?php namespace Grrr\SimplyStaticDeploy\Aws;

use Aws\Sdk;
use Aws\S3\S3Client;
use Grrr\SimplyStaticDeploy\Config;
use Aws\CloudFront\CloudFrontClient;

class ClientProvider {

    protected $sdk;

    public function __construct(Config $config) {
        // Allow empty credentials when an IAM role is assigned (e.g. on an EC2 instance).
        $credentials = $config->key && $config->secret
            ? (new CredentialsProvider($config->key, $config->secret))->getCredentials()
            : null;

        // The SDK which creates clients.
        $this->sdk = new Sdk([
            'credentials' => null,
            'endpoint' => $config->endpoint ?: null,
            'region' => $config->region,
            'version' => 'latest',
            'http' => [
                'timeout' => 30,
            ],
        ]);
    }

    public function getS3Client(): S3Client {
        return $this->sdk->createS3();
    }

    public function getCloudFrontClient(): CloudFrontClient {
        return $this->sdk->createCloudFront();
    }

}

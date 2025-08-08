<?php
/*
Copyright 2017 Amazon.com, Inc. or its affiliates. All Rights Reserved.
Licensed under the Apache License, Version 2.0 (the "License"). You may not use this file except in compliance with the License. A copy of the License is located at
    http://aws.amazon.com/apache2.0/
or in the "license" file accompanying this file. This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
*/
require __DIR__ . '/vendor/autoload.php';
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
class TwitchProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;
    private $urlAuthorize;
    private $urlAccessToken;
    private $urlResourceOwnerDetails;
    private $accessTokenMethod;
    private $accessTokenResourceOwnerId;
    private $scopeSeparator;
    private $scopes = null;
    private $responseError = 'error';
    private $responseCode;
    private $responseResourceOwnerId;
    public function __construct(array $options = [])
    {
        $possible   = $this->getConfigurableOptions();
        $configured = array_intersect_key($options, array_flip($possible));
        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }
        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);
        parent::__construct($options);
    }
    protected function getConfigurableOptions()
    {
        return ['accessTokenMethod',
                'accessTokenResourceOwnerId',
                'scopeSeparator',
                'responseError',
                'responseCode',
                'responseResourceOwnerId',
                'scopes',
        ];
    }
    public function getBaseAuthorizationUrl()
    {
        return 'https://id.twitch.tv/oauth2/authorize';
    }
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://id.twitch.tv/oauth2/token';
    }
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.twitch.tv/helix/users';
    }
    public function getDefaultScopes()
    {
        return $this->scopes;
    }
    protected function getScopeSeparator()
    {
        return ' ';
    }
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            $code  = $this->responseCode ? $data[$this->responseCode] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }
    protected function getDefaultHeaders()
    {
        return ['Client-ID' => $this->clientId, 'Accept' => 'application/vnd.twitchtv.v5+json'];
    }
    protected function getAuthorizationHeaders($token = NULL)
    {
        return ['Authorization' => 'Bearer '.$token];
    }
}
?>
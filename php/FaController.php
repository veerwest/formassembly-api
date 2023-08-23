<?php

use YourFramework\Request;
use YourFramework\CredentialService;
use YourFramework\SomeRedirectResponse;
use YourFramework\ObjectStore;
use Fathershawn\OAuth2\Client\Provider\FormAssembly as OauthProvider;

/**
 * This example code assumes that you are working in a framework or bespoke object oriented context with access to
 * objects that model the current request and other services.  It demonstrates the use of the FormAssembly
 * Oauth2 Provider package, which is part of the League of Extraordinary Packages ecosystem.
 *
 * If you are using the Composer package manager for PHP, you can add the FormAssembly Oauth2 Provider:
 * `composer require fathershawn/oauth2-formassembly`
 *
 * @see https://github.com/thephpleague/oauth2-client
 * @see https://github.com/FatherShawn/oauth2-formassembly
 * @see https://getcomposer.org
 * @see https://packagist.org/packages/fathershawn/oauth2-formassembly
 */
class FaController {

	/**
	 * An object modeling the current request.
	 */
	protected Request $currentRequest;

	/**
	 * A FormAssembly Oauth Provider.
	 */
	protected OauthProvider $provider;

    /**
     * An object storage service of some kind.
     */
    protected ObjectStore $storage;

	/**
	 * The return Url
	 */
	protected string $returnUrl;

	public function __construct(Request $request, CredentialService $credentialService, ObjectStore $storage)
	{
        $this->returnUrl = 'https://www.example.com/some/path/to/capture/method';
        $this->currentRequest = $request;
        $this->storage = $storage;
		// You have provided some form or configuration file that captured and stored the client ID and secret.
		$credentials = $credentialService->getOauthKeys();
		$this->provider = new OauthProvider([
			'clientId' => $credentials['cid'],
			'clientSecret' => $credentials['secret'],
			'redirectUri' => $this->returnUrl,
			'baseUrl' => 'https://url-to-formassembly-instance',
		]);
	}

	/**
	 * Assembles the authorization response and issues a redirect to the user.
	 */
	public function authorize(): SomeRedirectResponse
    {
		$url = $this->provider->getAuthorizationUrl();
		return new SomeRedirectResponse($url);
	}

    /**
     * Captures and stores the authorization code.
     */
    public function code()
    {
        try {
            $code = $this->currentRequest->query->get('code');
            if (empty($code)) {
                throw new \UnexpectedValueException("The authorization_code query parameter is missing.");
            }

            /**
             * @var \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
             */
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
            // Store the access token in some object store.
            $this->storage->store('faAccess', $accessToken);
        }
        catch (\Exception $e) {
            print('FormAssembly failed to authorize:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a token value from the current AccessToken or execute a Refresh if it has expired.
     */
    public function getToken()
    {
        /**
         * @var \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
         */
        $accessToken = $this->storage->get('faAccess');
        // If the token is still current, return it.
        if (!$accessToken->hasExpired()) {
            return $accessToken->getToken();
        }
        // Use the refresh token.
        try {
            $newAccessToken = $this->provider->getAccessToken(
                'refresh_token',
                [
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]
            );
            // Store the replacement access token.
            $this->storage->store('faAccess', $newAccessToken);
        }
        catch (\Exception $e) {
            print('FormAssembly new token request failed with Exception:' . get_class($e));
            throw $e;
        }
        return $newAccessToken->getToken();
    }

}
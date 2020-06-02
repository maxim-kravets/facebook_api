<?php

namespace App\Controller;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    private $fb;

    public function __construct()
    {
        try {
            $this->fb = new Facebook([
                'app_id' => $_ENV['APP_API_ID'],
                'app_secret' => $_ENV['APP_API_SECRET'],
                'default_graph_version' => 'v2.10'
            ]);

        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            die();
        }
    }

    /**
     * @Route("/oauth/token-request", name="oauth_token_request")
     */
    public function tokenRequest()
    {
        $helper = $this->fb->getRedirectLoginHelper();

        $permissions = ['email'];
        $login_url = $helper->getLoginUrl('https://'.$_SERVER['SERVER_NAME'].'/oauth/token-response', $permissions);

        return $this->redirect($login_url);
    }

    /**
     * @Route("/oauth/token-response", name="oauth_token_response")
     */
    public function tokenResponse()
    {
        $helper = $this->fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();

            if ( ! empty($accessToken)) {
                dd($accessToken->getValue());
            }

        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();

            die();
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();

            die();
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }

            die();
        }
    }
}

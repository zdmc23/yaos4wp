<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}

include __DIR__ . '/../vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;

// TODO: DB
require_once( YAOS4WP__PLUGIN_DIR . 'AccessTokenRepository.php' ); 
require_once( YAOS4WP__PLUGIN_DIR . 'AuthCodeRepository.php' );
require_once( YAOS4WP__PLUGIN_DIR . 'ClientRepository.php' );
require_once( YAOS4WP__PLUGIN_DIR . 'RefreshTokenRepository.php' );
require_once( YAOS4WP__PLUGIN_DIR . 'ScopeRepository.php' );
//
require_once( YAOS4WP__PLUGIN_DIR . 'UserEntity.php' );
require_once( YAOS4WP__PLUGIN_DIR . 'AuthCodeEntity.php' );

class OAuth2Server {
  public $authorizationServer;
  public $resourceServer;

  /**
   * Call this method to get singleton
   */
  public static function instance() {
    static $instance = false;
    if( $instance === false ) {
      $instance = new static();
      $instance->register_hooks();
    }
    return $instance;
  }
  /**
   * Make constructor private, so nobody can call "new Class".
   */
  private function __construct() {}

  /**
   * Make clone magic method private, so nobody can clone instance.
   */
  private function __clone() {}

  /**
   * Make sleep magic method private, so nobody can serialize instance.
   */
  private function __sleep() {}

  /**
   * Make wakeup magic method private, so nobody can unserialize instance.
   */
  private function __wakeup() {}

  /**
  * Transforms $_SERVER HTTP headers into a nice associative array. For example:
  *   array(
  *       'Referer' => 'example.com',
  *       'X-Requested-With' => 'XMLHttpRequest'
  *   )
  */
  function get_request_headers() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
      if(strpos($key, 'HTTP_') === 0) {
        $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
      }
    }
    return $headers;
  }
  private function register_hooks() {
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('parse_request', array($this, 'handle_request'));
    //add_action('init', array($this,'_wo_server_register_query_vars'));
    add_filter('determine_current_user', [$this, 'authenticate'], 20);

    $accessTokenRepository = new AccessTokenRepository();
    $refreshTokenRepository = new RefreshTokenRepository();
    // Setup the authorization server
    $authorization_server = new AuthorizationServer(
      new ClientRepository(),
      $accessTokenRepository,
      new ScopeRepository(),
      'file://' . __DIR__ . '/private.key',
      'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
    );
    // Enable the authentication code grant on the server with a token TTL of 1 hour
    $authorization_server->enableGrantType(
        new AuthCodeGrant(
            new AuthCodeRepository(),
            $refreshTokenRepository,
            new \DateInterval('PT10M')
        ),
        new \DateInterval('PT1H')
    );
    // Enable the refresh token grant on the server
    $grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant($refreshTokenRepository);
    $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month
    $authorization_server->enableGrantType(
      $grant,
      new \DateInterval('PT1H') // new access tokens will expire after an hour
    );
    $this->authorizationServer = $authorization_server;
    // Setup the resource server
    $resource_server = new ResourceServer(
        $accessTokenRepository,               // instance of AccessTokenRepositoryInterface
        'file://' . __DIR__ . '/public.key'   // the authorization server's public key
    );
    $this->resourceServer = $resource_server;
  }
  public static function activation() {
    flush_rewrite_rules();
  }
  public static function deactivation() {
    flush_rewrite_rules();
  }
  function add_menu() {
    $title_page = esc_html__('YAOS4WP', 'oauth2-server');
    $title_menu = esc_html__('YAOS4WP', 'oauth2-server');
    add_options_page($title_page, $title_menu, 'manage_options', 'yaos4wp', array($this, 'display_settings'));
    add_menu_page($title_page, $title_menu, 'administrator', 'yaos4wp-oauth2-server-settings', array($this, 'menu_page'), plugin_dir_url(__FILE__) . 'images/oauth2logo.png');
  }
  public function menu_page() {
    //echo 'test123';
    include_once('oauth2-server-settings.php');
  }
  /**
   *
   */
  public function authenticate($user) {
    if (strpos($_SERVER["REQUEST_URI"], '/wp-json') !== false) {
      try {
        $version = explode('/', $_SERVER['SERVER_PROTOCOL'])[1];
        $psr7ServerRequest = new ServerRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $this->get_request_headers(), file_get_contents("php://input"), $version, $_SERVER);
        $psr7Response = $this->resourceServer->validateAuthenticatedRequest($psr7ServerRequest);
        //var_dump($psr7Response);
        $oauth_user_id = $psr7Response->getAttribute('oauth_user_id');
        return $oauth_user_id; //1;
      } catch (OAuthServerException $exception) {
        //return $exception->generateHttpResponse($response);
        var_dump($exception);
      } catch (Exception $exception) {
        //return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))->generateHttpResponse($response);
        var_dump($exception);
      }
    } else {
      return $user;
    }
  }
  public function handle_request() {
    $request_handler = new RequestHandler;
    if (strpos($_SERVER["REQUEST_URI"], '/yaos4wp/authorize') !== false) {
      $request_handler->handle_authorization_request();
    } 
    if (strpos($_SERVER["REQUEST_URI"], '/yaos4wp/token') !== false) {
      $request_handler->handle_token_request();
    }
    if (strpos($_SERVER["REQUEST_URI"], '/yaos4wp/callback') !== false) {
      $request_handler->handle_callback_request();
    }
    /*
    if (strpos($_SERVER["REQUEST_URI"], '/yaos4wp/nonce') !== false) {
      $request_handler->handle_nonce_request();
    } 
    */
  }
}

class RequestHandler {
  public function __construct() { }
  /**
  * Transforms $_SERVER HTTP headers into a nice associative array. For example:
  *   array(
  *       'Referer' => 'example.com',
  *       'X-Requested-With' => 'XMLHttpRequest'
  *   )
  */
  function get_request_headers() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
      if(strpos($key, 'HTTP_') === 0) {
        $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
      }
    }
    return $headers;
  }
  public function handle_authorization_request() {
    // Validate the HTTP request and return an AuthorizationRequest object.
    // The auth request object can be serialized into a user's session
    $yaos4wp = OAuth2Server::instance(); 
    $version = explode('/', $_SERVER['SERVER_PROTOCOL'])[1];
    // http://localhost:8000/yaos4wp/authorize?response_type=code&redirect_uri=paraguide://oauth2&client_id=myawesomeapp&scope=basic&state=zz
    /*
    $params = [
      'response_type' => 'code',
      'client_id'     => 'myawesomeapp',
      //'redirect_uri'  => 'https://duckduckgo.com',
      'scope'         => 'basic',
      'state'         => 'zz',
    ];
    */
    $psr7ServerRequest = new ServerRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $this->get_request_headers(), file_get_contents("php://input"), $version, $_SERVER);
    //$psr7ServerRequestWithQueryParams = $psr7ServerRequest->withQueryParams($params);
    $psr7ServerRequestWithQueryParams = $psr7ServerRequest->withQueryParams($_GET);
    try {
      // Validate the HTTP request and return an AuthorizationRequest object.
      // The auth request object can be serialized into a user's session
      $authRequest = $yaos4wp->authorizationServer->validateAuthorizationRequest($psr7ServerRequestWithQueryParams);
      // Once the user has logged in set the user on the AuthorizationRequest
      // TODO: read WP user id and set it here
      $authRequest->setUser(new UserEntity());
      // Once the user has approved or denied the client update the status
      // (true = approved, false = denied)
      $authRequest->setAuthorizationApproved(true);
      // Return the HTTP redirect response
      $psr7Response = $yaos4wp->authorizationServer->completeAuthorizationRequest($authRequest, new Response());
      $redirect_uri = $psr7Response->getHeader('Location')[0];
      wp_redirect($redirect_uri);
      exit;
    } catch (OAuthServerException $exception) {
      //echo "\nAUTHORIZE OAUTH EXCEPTION\n";
      //return $exception->generateHttpResponse(new Response());
      var_dump($exception);
    } catch (\Exception $exception) {
      $body = new Stream('php://temp', 'r+');
      $body->write($exception->getMessage());
      //echo "\nAUTHORIZE EXCEPTION\n";
      //return $response->withStatus(500)->withBody($body);
      var_dump($exception);
    }
  }
  public function handle_token_request() {
    $yaos4wp = OAuth2Server::instance(); 
    $version = explode('/', $_SERVER['SERVER_PROTOCOL'])[1];
    $psr7ServerRequest = new ServerRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $this->get_request_headers(), file_get_contents("php://input"), $version, $_SERVER);
    $psr7ServerRequestWithParsedBody = $psr7ServerRequest->withParsedBody($_POST); //file_get_contents("php://input"));
    $response = new Response();
    try {
      // Try to respond to the request
      $psr7Response = $yaos4wp->authorizationServer->respondToAccessTokenRequest($psr7ServerRequestWithParsedBody, $response);
      header('Content-type: application/json');
      echo (string)$psr7Response->getBody();
      exit;
    } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
      // All instances of OAuthServerException can be formatted into a HTTP response
      //return $exception->generateHttpResponse($response);
      //var_dump($exception);
      //echo $exception->getMessage() . $exception->getHint() . $exception->getPayload();
      var_dump($exception->getPayload());
      exit;
    } catch (\Exception $exception) {
      // Unknown exception
      $body = new Stream(fopen('php://temp', 'r+'));
      $body->write($exception->getMessage());
      //return $response->withStatus(500)->withBody($body);
      var_dump($exception);
      exit;
    }
  }
  public function handle_callback_request() {
    echo $_GET['code'];
    exit;
  }
  public function handle_nonce_request() {
    echo wp_create_nonce('wp_rest');
    exit;
  }
}

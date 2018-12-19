<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
#namespace OAuth2ServerExamples\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
#use OAuth2ServerExamples\Entities\ClientEntity;

require_once( YAOS4WP__PLUGIN_DIR . 'ClientEntity.php' );

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
    {
        $clients = [
            'myawesomeapp' => [
                'secret'          => password_hash('abc123', PASSWORD_BCRYPT),
                'name'            => 'My Awesome App',
                //'redirect_uri'    => 'https://zdmc.info',
                //'is_confidential' => true,
                'is_confidential' => false,
            ],
        ];

        // Check if client is registered
        if (array_key_exists($clientIdentifier, $clients) === false) {
            return;
        }

        if (
            $mustValidateSecret === true
            && $clients[$clientIdentifier]['is_confidential'] === true
            && password_verify($clientSecret, $clients[$clientIdentifier]['secret']) === false
        ) {
            return;
        }

        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($clients[$clientIdentifier]['name']);
        //$client->setRedirectUri($clients[$clientIdentifier]['redirect_uri']);

        return $client;
    }
}

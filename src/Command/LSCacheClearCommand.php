<?php

namespace ACPL\FlarumCache\Command;

use Flarum\Api\ApiKey;
use Flarum\Console\AbstractCommand;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LSCacheClearCommand extends AbstractCommand
{
    protected UrlGenerator $url;

    public function __construct(UrlGenerator $url, SettingsRepositoryInterface $settings)
    {
        parent::__construct();
        $this->url = $url;
        $this->settings = $settings;
    }

    public function configure()
    {
        $this->setName('lscache:clear')->setDescription('Purge all LsCache.');
    }

    protected function fire()
    {
        $this->info('Sending a request to purge all LSCache entries...');

        //Create a temporary API key to authorize a request
        $apiKey = new ApiKey();
        $key = $apiKey::generate()->key;
        //The key is saved temporarily in the settings. The native Flarum API key is not used because it requires a user ID and in the case of the command the user is not logged in.
        $this->settings->set('acpl-lscache.purgeKey', $key);

        $client = new Client();

        try {
            //GET does not require the Flarum API key
            $client->request('GET', $this->url->to('api')->route('lscache.purge'), [
                'headers' => ['LSCachePurgeKey' => $key],
            ]);
        } catch (GuzzleException $exception) {
            $this->deleteKey();
            $this->error('Something went wrong while sending the request.');

            return 1;
        }

        $this->deleteKey();
        $this->info('Notified LiteSpeed Web Server to purge all LSCache entries');
    }

    private function deleteKey()
    {
        $this->settings->delete('acpl-lscache.purgeKey');
    }
}

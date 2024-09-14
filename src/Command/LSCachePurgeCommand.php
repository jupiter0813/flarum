<?php

namespace ACPL\FlarumLSCache\Command;

use Flarum\Api\ApiKey;
use Flarum\Console\AbstractCommand;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputOption;

class LSCachePurgeCommand extends AbstractCommand
{
    protected UrlGenerator $url;
    private SettingsRepositoryInterface $settings;

    public function __construct(UrlGenerator $url, SettingsRepositoryInterface $settings)
    {
        parent::__construct();
        $this->url = $url;
        $this->settings = $settings;
    }

    public function configure(): void
    {
        $this->setName('lscache:clear')
            ->setDescription('Purge LsCache.')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'The path whose cache you want to purge. E.g. "--path=/test"',
            )
            ->addOption(
                'tag',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Tge cache tag whose cache you want to purge. E.g. "--tag=test"',
            );
    }

    protected function fire(): int
    {
        $this->info('Sending a request to purge LSCache entries...');

        //Create a temporary API key to authorize a request
        $apiKey = new ApiKey();
        $key = $apiKey::generate()->key;
        //The key is saved temporarily in the settings.
        //The native Flarum API key is not used because it requires a user ID but in the case of the command, the user is not logged in.
        $this->settings->set('acpl-lscache.purgeKey', $key);

        $options = [
            'headers' => ['LSCachePurgeKey' => $key],
        ];

        $paths = $this->input->getOption('path');
        if (! empty($paths)) {
            $options['query']['paths'] = $paths;
        }

        $tags = $this->input->getOption('tag');
        if (! empty($tags)) {
            $options['query']['tags'] = $tags;
        }

        $client = new Client();

        try {
            //GET does not require the Flarum API key
            $client->request('GET', $this->url->to('api')->route('lscache.purge'), $options);
        } catch (GuzzleException $exception) {
            $this->error('Something went wrong while sending the request.');
            $this->error($exception->getMessage());

            return 1;
        } finally {
            $this->settings->delete('acpl-lscache.purgeKey');
        }

        $this->info('Notified LiteSpeed Web Server to purge'.((empty($paths) && empty($tags)) ? ' all' : '').' LSCache entries');

        return 0;
    }
}

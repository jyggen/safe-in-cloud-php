<?php
namespace Graceland\SafeInCloud\Command;

use Graceland\SafeInCloud\ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AccountCommand extends Command
{
    protected $client;

    public function __construct(ApiClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    protected function configure()
    {
        $this->setName('accounts');
        $this->setDescription('Retrieve web accounts associated with an URL');
        $this->addArgument('url', InputArgument::REQUIRED, 'The URL you want to retrieve associated accounts for');
        $this->addOption('token', null, InputOption::VALUE_REQUIRED, 'Your authentication token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client->setAuthToken($input->getOption('token'));
        $accounts = $this->client->getWebAccounts($input->getArgument('url'));
        $output->writeln(json_encode($accounts));
        return 0;
    }
}

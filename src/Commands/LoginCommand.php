<?php
namespace Graceland\SafeInCloud\Commands;

use Graceland\SafeInCloud\ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoginCommand extends Command
{
    protected $client;

    public function __construct(ApiClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    protected function configure()
    {
        $this->setName('logins');
        $this->setDescription('Retrieve web accounts associated with an URL');
        $this->addOption('token', null, InputOption::VALUE_REQUIRED, 'Your authentication token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client->setAuthToken($input->getOption('token'));
        $this->client->doHandshake();

        $logins = $this->client->getLogins();

        $output->writeln(json_encode($logins));
        return 0;
    }
}

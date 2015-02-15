<?php
namespace Graceland\SafeInCloud\Command;

use Graceland\SafeInCloud\ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AuthenticateCommand extends Command
{
    protected $client;

    public function __construct(ApiClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    protected function configure()
    {
        $this->setName('authenticate')->setDescription('Authenticate against SafeInCloud');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new Question('Enter your password:');

        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        if ($this->client->authenticate($password) === false) {
            $formatter      = $this->getHelper('formatter');
            $errorMessages  = ['An error has occurred!', 'Unable to authenticate, did you enter the correct password?'];
            $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);

            $output->writeln('');
            $output->writeln($formattedBlock);
            return 1;
        }

        return 0;
    }
}

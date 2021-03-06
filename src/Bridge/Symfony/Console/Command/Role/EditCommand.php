<?php

declare(strict_types=1);

namespace Damax\User\Bridge\Symfony\Console\Command\Role;

use Damax\User\Application\Command\UpdateRole;
use Damax\User\Application\Dto\RoleInfoDto;
use Damax\User\Application\Service\RoleService;
use Damax\User\Bridge\Symfony\Bundle\Form\Type\RoleType;
use Damax\User\Bridge\Symfony\Console\Style;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class EditCommand extends Command
{
    protected static $defaultName = 'damax:user:role:edit';

    private $service;

    public function __construct(RoleService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    protected function configure()
    {
        $this
            ->setDescription('Edit role.')
            ->addArgument('code', InputArgument::REQUIRED, 'Role code.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new Style($input, $output);
        $io->title('Edit role');

        /** @var RoleInfoDto $info */
        $info = $this->getHelper('form')->interactUsingForm(RoleType::class, $input, $output, ['full' => false]);

        $command = new UpdateRole();
        $command->code = $input->getArgument('code');
        $command->info = $info;

        $io->newLine();

        try {
            $io->role($this->service->update($command));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
        }
    }
}

<?php

namespace Application\AppBundle\SlackCommand\Absence;

use Application\AppBundle\SlackCommand\TempAbstractCommand;
use Domain\Exception\AbsenceException;
use Domain\UseCase\Absence\TakeDelegation;
use Domain\UseCase\Absence\TakeHoliday;
use Domain\UseCase\Absence\TakeSickLeave;
use Domain\UseCase\Absence\WorkFromHome;
use Infrastructure\File\AbsenceStorage;
use Slack\Channel;
use Slack\User;

class WorkFromHomeCommand extends TempAbstractCommand implements WorkFromHome\Responder
{
    public function configure()
    {
        $this->setRegex('/wfh (.+)/iu');
    }

    public function execute(string $message, User $user, Channel $channel)
    {
        parent::execute($message, $user, $channel);

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new WorkFromHome(new AbsenceStorage());
        $useCase->execute(
            new WorkFromHome\Command(
                $user->getUsername(),
                $period['startDate'],
                $period['endDate']
            ),
            $this
        );
    }

    public function failedToWorkFormHome(AbsenceException $exception)
    {
        $this->reply('Nie poradzimy sobie BEZ Ciebie!');
    }

    public function workFromHomeSuccessfully()
    {
        $this->reply('Mam nadzieję, że jednak będziesz pracował ;) :house:');
    }
}

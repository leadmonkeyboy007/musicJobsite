<?php

namespace App\Command;

use Slot\MandrillBundle\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailRegoFollowUpCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        // How often do we run this script
        $this->_timeAgo = '24 hours';

        $this->setName('vocalizr:email-rego-follow-up')
             ->setDescription('Email a registration follow to users. The email should be sent 3 days after they complete their signup.  [Cronjob: Every ' . $this->_timeAgo . ']');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container        = $this->container;
        $this->em         = $container->get('doctrine')->getManager();
        $this->dispatcher = $container->get('hip_mandrill.dispatcher');

        echo "SCRIPT START - Email Rego Follow Up\n";
        $this->checkDate = date('Y-m-d', strtotime('-3 days'));

        $this->processVocalists();

        $this->processProducers();

        $this->processUsersBoth();

//        if (count($this->vocalists) == 0) {
//
//            print "NO VOCALISTS FOUND\n";
//            print "SCRIPT END - Email Vocalist Rego Follow Up\n\n";
//            return;
//        }
        echo "SCRIPT END - Email Vocalist Rego Follow Up\n\n";

        return 1;
    }

    private function processVocalists()
    {
        $message = new Message();
        $message->setFromEmail('help@vocalizr.com');
        $message->setFromName('Luke Chable');
        $message->setSubject('Welcome, and thanks for signing up to Vocalizr!');
        $message->setPreserveRecipients(false);
        $message->setTrackOpens(true)
                ->setTrackClicks(true);

        // get users that are vocalists and have completed registration 3 days ago

        $q = $this->em->getRepository('App:UserInfo')
                ->createQueryBuilder('ui')
                ->where('ui.is_vocalist = 1')
                ->andWhere('ui.is_producer = 0')
                ->andWhere('ui.is_active = 1')
                ->andWhere("DATE_FORMAT(ui.date_activated, '%Y-%m-%d') = :date")
                ->setParameter(':date', $this->checkDate);

        $vocalists = $q->getQuery()->execute();
        $this->addRecipientsAndSend($message, $vocalists, 'regoFollowUpVocalist');
    }

    private function processProducers()
    {
        $message = new Message();
        $message->setFromEmail('help@vocalizr.com');
        $message->setFromName('Luke Chable');
        $message->setSubject('Welcome, and thanks for signing up to Vocalizr!');
        $message->setPreserveRecipients(false);
        $message->setTrackOpens(true)
                ->setTrackClicks(true);

        // get users that are producers and have completed registration 3 days ago

        $q = $this->em->getRepository('App:UserInfo')
                ->createQueryBuilder('ui')
                ->where('ui.is_vocalist = 0')
                ->andWhere('ui.is_producer = 1')
                ->andWhere('ui.is_active = 1')
                ->andWhere("DATE_FORMAT(ui.date_activated, '%Y-%m-%d') = :date")
                ->setParameter('date', $this->checkDate);

        $producers = $q->getQuery()->execute();
        $this->addRecipientsAndSend($message, $producers, 'regoFollowUpProducer');
    }

    private function processUsersBoth()
    {
        $message = new Message();
        $message->setFromEmail('help@vocalizr.com');
        $message->setFromName('Luke Chable');
        $message->setSubject('Welcome, and thanks for signing up to Vocalizr!');
        $message->setPreserveRecipients(false);
        $message->setTrackOpens(true)
                ->setTrackClicks(true);

        // get users that are both producers and vocalists and have completed registration 3 days ago
        $q = $this->em->getRepository('App:UserInfo')
                ->createQueryBuilder('ui')
                ->where('ui.is_vocalist = 1')
                ->andWhere('ui.is_producer = 1')
                ->andWhere('ui.is_active = 1')
                ->andWhere("DATE_FORMAT(ui.date_activated, '%Y-%m-%d') = :date")
                ->setParameter('date', $this->checkDate);

        $users = $q->getQuery()->execute();
        $this->addRecipientsAndSend($message, $users, 'regoFollowUpBoth');
    }

    private function addRecipientsAndSend($message, $recipients, $template)
    {
        echo count($recipients) . "\n";
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $message->addTo($recipient->getEmail());
                $body = $this->container->get('twig')->render('Mail:' . $template . 'connection.html.twig', [
                    'userInfo' => $recipient,
                ]);
                $message->addMergeVar($recipient->getEmail(), 'BODY', $body);
            }

            echo 'SENDING ' . $template . ' EMAILS...';
            $this->sendEmail($message, 'default-luke');
            echo "DONE\n\n";
        }
    }

    private function sendEmail($message, $template)
    {
        $this->dispatcher->send($message, $template);
    }
}
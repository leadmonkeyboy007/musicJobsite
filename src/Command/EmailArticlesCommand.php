<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailArticlesCommand extends Command
{
    protected function configure()
    {
        // How often do we run this script
        $this->_timeAgo = '7 days';

        $this->setName('vocalizr:email-articles')
             ->setDescription('Email new articles to subscribed users. [Cronjob: Every ' . $this->_timeAgo . ']');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container  = $container  = $this->getContainer();
        $this->em         = $container->get('doctrine')->getEntityManager();
        $this->dispatcher = $container->get('hip_mandrill.dispatcher');

        echo "SCRIPT START - Email Articles\n";
        $this->checkDate = date('Y-m-d', strtotime('-' . $this->_timeAgo));

        $this->process();

        echo "SCRIPT END - Email Articles\n\n";
    }

    private function process()
    {
        $em = $this->em;

        $message = new \Hip\MandrillBundle\Message();
        $message->setPreserveRecipients(false);
        $message->setTrackOpens(true)
                ->setTrackClicks(true);

        // Check if there are any articles
        $q = $em->getRepository('App:Article')
                ->createQueryBuilder('a')
                ->where("DATE_FORMAT(a.published_at,'%Y-%m-%d') >= :now")
                ->orderBy('a.published_at', 'DESC')
                ;
        $params = [
            ':now' => $this->checkDate,
        ];
        $q->setParameters($params);
        $articles = $q->getQuery()->execute();

        if (!$articles) {
            echo "No Artilces published\r\n";
            return true;
        }
        $this->articles = $articles;

        // Find subscribed members
        $q = $this->em->getRepository('App:MagUser')
                ->createQueryBuilder('u')
                ->where('u.unsubscribe_at IS NULL');

        $results = $q->getQuery()->execute();

        $this->addRecipientsAndSend($message, $results, 'articles');
    }

    private function addRecipientsAndSend($message, $recipients, $template)
    {
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $message->addTo($recipient->getEmail());
                $body = $this->container->get('templating')->render('VocalizrAppBundle:Mail:' . $template . 'connection.html.twig', [
                    'articles' => $this->articles,
                    'user'     => $recipient,
                ]);
                $message->addGlobalMergeVar('BODY', $body);
                $message->addMergeVar($recipient->getEmail(), 'UNSUBSCRIBELINK', 'https://vocalizr.com/vmag/unsub/' . $recipient->getUid());
            }

            echo 'SENDING ' . $template . ' EMAILS...';
            $this->sendEmail($message, 'vmag-default');
            echo "DONE\n\n";
        }
    }

    private function sendEmail($message, $template)
    {
        $this->dispatcher->send($message, $template);
    }
}

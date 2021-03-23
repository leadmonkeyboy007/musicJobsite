<?php

namespace Vocalizr\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vocalizr\AppBundle\Entity\UserSetting;

class EmailNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
                ->setName('vocalizr:email-notifications')
                ->setDescription('Email notifcations to users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $doctrine  = $container->get('doctrine');
        $em        = $doctrine->getEntityManager();
        $userPr    = $doctrine->getRepository('VocalizrAppBundle:ProjectFeed');

        /**
         * First get users who
         */

        /**
         * Get users that has unread project feed items to them
         */
        $q = $em->getRepository('VocalizrAppBundle:UserInfo')
            ->createQueryBuilder('ui')
            ->select('ui, us, pf, p, fui')
            ->leftJoin('ui.user_pref', 'uf') // join user preferences for that user
            ->leftJoin('ui.user_settings', 'us', "us.name = 'project_digest_last_sent'")
            ->innerJoin('ui.project_feeds', 'pf')
            ->innerJoin('pf.project', 'p')
            ->innerJoin('pf.from_user_info', 'fui')
            ->where('pf.feed_read = 0 AND pf.notified = 0 AND ui.is_active = 1')
            ->andWhere('pf.created_at < :now')
            ->orderBy('pf.user_info', 'ASC')
            ->addOrderBy('p.title', 'ASC')
            ->addOrderBy('pf.id', 'DESC')
        ;

        $params = [
            ':now' => date('Y-m-d H:i:s'),
        ];
        $q->setParameters($params);

        $results = $q->getQuery()->execute();

        if (count($results)) {
            foreach ($results as $user) {
                $userPref           = $user->getUserPref();
                $emailProjectDigest = 'instantly';
                if (count($userPref)) {
                    $emailProjectDigest = $userPref->getEmailProjectDigest();
                }

                // Check when digest was last sent
                $userSettings          = $user->getUserSettings();
                $projectDigestLastSent = date('Y-m-d H:i:s');
                if (count($userSettings)) {
                    $userSetting           = $userSettings[0];
                    $projectDigestLastSent = $userSetting->getValue();
                }

                if ($emailProjectDigest != 'instantly') {
                    $dt = new \DateTime($projectDigestLastSent);
                    $dt->modify('+' . $emailProjectDigest);

                    // Check last time was checked
                    if ($dt->getTimestamp() > time()) {
                        continue;
                    }
                }

                // Update email last sent
                if (!isset($userSetting)) {
                    $userSetting = new UserSetting();
                    $userSetting->setName('project_digest_last_sent');
                    $userSetting->setValue(date('Y-m-d H:i:s'));
                    $userSetting->setPublic(false);
                }
                $userSetting->setValue(date('Y-m-d H:i:s'));
                $em->persist($userSetting);

                // Mark feed items as notified
                foreach ($user->getProjectFeeds() as $feed) {
                    $feed->setNotified(true);
                    $em->persist($feed);
                }
                $em->flush();

                // Send email with project feed
            }
            $em->flush();
        }

        /**
         * - Check project feeds for unread items
         *   inner join project
         *   inner join project.user
         *   inner join project.user_pref
         *   left join project.user_settings
         *
         *   Group by user_info id
         * -
         *
         * $q = $em->getRepository('VocalizrAppBundle:ProjectFeed')->createQueryBuilder('pf')
         * ->select('pf, ui, fui, p, uf')
         * ->innerJoin('pf.user_info', 'ui') // join user info for who the feed is for
         * ->innerJoin('pf.project', 'p') // join project
         * ->innerJoin('pf.from_user_info', 'fui') // join from user info for feed
         * ->innerJoin('ui.user_pref', 'uf') // join user preferences for that user
         * ->leftJoin('ui.user_setting', 'us', 'us.name = :userSettingName')
         * ->where('pf.feed_read = 0') // unread items only
         * ->orderBy('pf.created_at', 'DESC')
         * ;
         *
         * $params = array(
         * ':userSettingName' => 'project_digest_last_sent'
         * );
         * $q->setParameters($params);
         */
    }
}
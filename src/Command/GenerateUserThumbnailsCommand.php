<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\UserInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenerateUserThumbnailsCommand
 * @package App\Command
 */
class GenerateUserThumbnailsCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('vocalizr:generate-user-thumbnails')
            ->setDescription('Generate avatar thumbnails for all users.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = 100;

        $model = $this->container->get('vocalizr_app.model.user_info');

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->getRepository('App:UserInfo')->createQueryBuilder('u');
        $qb->where('u.path is not null');

        $offset = 0;
        do {
            $qb
                ->setMaxResults($limit)
                ->setFirstResult($offset)
            ;

            /** @var UserInfo[] $resultUsers */
            $resultUsers = $qb->getQuery()->getResult();

            foreach ($resultUsers as $resultUser) {
                $output->writeln('Generate thumbnails for user ' . $resultUser->getUsername());
                $model->generateThumbnails($resultUser);
            }

            if ($resultUsers) {
                $em->clear();
                $output->writeln('Entity cache has been cleared');
            }
            $offset += $limit;
        } while ($resultUsers);

        $output->writeln('Process finished.');

        return 1;
    }
}
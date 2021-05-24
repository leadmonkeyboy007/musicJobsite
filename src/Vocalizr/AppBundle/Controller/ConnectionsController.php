<?php

namespace Vocalizr\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

class ConnectionsController extends Controller
{
    /**
     * @Route("/connections", name="connections")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        // Get any pending requests
        $user = $this->getUser();
        $em   = $this->getDoctrine()->getManager();

        // Get connections for logged in user
        $q = $em->getRepository('VocalizrAppBundle:UserConnectInvite')
                ->createQueryBuilder('uc');
        $q->select('uc, fui, tui');
        $q->innerJoin('uc.to', 'tui');
        $q->innerJoin('uc.from', 'fui');
        $q->where('uc.to = :user');
        $q->andWhere('uc.connected_at IS NULL AND uc.status IS NULL AND fui.is_active = 1');

        $params = [
            ':user' => $user,
        ];

        $q->setParameters($params);
        $query = $q->getQuery();

        $pendingRequests = $query->execute();

        return [
            'pendingRequests' => $pendingRequests,
        ];
    }

    /**
     * @Route("/connections/list", name="connect_list")
     * @Template()
     *
     * @param Request $reqest
     */
    public function connectRowsAction()
    {
        $user = $this->getUser();
        $em   = $this->getDoctrine()->getManager();

        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'date';

        // Get connections for logged in user
        $q = $em->getRepository('VocalizrAppBundle:UserConnect')->findUserConnectionsQb($user, $filter);

        if ($search && !empty($search)) {
            $q->andWhere("(fui.username LIKE :search OR fui.display_name LIKE :search)");
            $q->setParameter('search', "%$search%");
        }

        $query = $q->getQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1)/*page number*/,
            20// limit per page
        );

        //$connections = $pagination;

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * Remove connection
     *
     * @Route("/connections/remove/{username}", name="connect_remove")
     *
     * @param Request $request
     */
    public function removeAction(Request $request)
    {
        $em              = $this->getDoctrine()->getManager();
        $user            = $this->getUser();
        $userConnectRepo = $em->getRepository('VocalizrAppBundle:UserConnectInvite');

        // Get user who we are wanting to remove connection with
        $connectUser = $em->getRepository('VocalizrAppBundle:UserInfo')->findOneBy([
            'username' => $request->get('username'),
        ]);
        if (!$connectUser) {
            return $this->forward('VocalizrAppBundle:Default:error', [
                'error' => 'Invalid member',
            ]);
        }

        // Check to see if they are connect
        $connect = $userConnectRepo->findOneBy([
            'from' => $connectUser,
            'to'   => $user,
        ]);
        // Get other connection to remove
        $connect2 = $userConnectRepo->findOneBy([
            'to'   => $connectUser,
            'from' => $user,
        ]);

        if (!$connect && !$connect2) {
            return $this->forward('VocalizrAppBundle:Default:error', [
                'error' => 'There is no connection to remove',
            ]);
        }

        if ($connect) {
            $em->remove($connect);
        }
        if ($connect2) {
            $em->remove($connect2);
        }

        // Find any chats, and close related to that user
        $em->getRepository('VocalizrAppBundle:MessageThread')
                ->closeOpenThreadsBetweenUsers($user, $connectUser);

        // Events will remove notification and update counts
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'You are no longer connected with ' . $connectUser->getDisplayName()]);
    }
}
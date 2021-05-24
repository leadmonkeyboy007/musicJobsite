<?php


namespace Vocalizr\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminPasswordChangeController extends Controller
{

    /**
     * @Route("/admin/password_change", name="admin_password_change")
     *
     * @param Request $request
     * @return Response
     */
    public function adminPasswordChange(Request $request)
    {
        if (!$this->getUser() || !$this->getUser()->getIsAdmin()) {
            return $this->redirect($this->generateUrl('dashboard'));
        }

        return $this->render('@VocalizrApp/Admin/user_password_change.html.twig');
    }


    /**
     * @Route("/admin/password_change/list", name="admin_password_change_list")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminPasswordChangeList(Request $request)
    {
        if (!$this->getUser() || !$this->getUser()->getIsAdmin()) {
            throw new AccessDeniedHttpException();
        }

        $searchTerm = trim($request->get('search-term'));

        if ($searchTerm == '') {

            $responseData = [
                'success' => false,
                'message' => 'No search string defined'
            ];

            return new JsonResponse($responseData);
        }

        $users = $this->getDoctrine()->getManager()->getRepository('VocalizrAppBundle:UserInfo')
            ->findUser($searchTerm);


        return new JsonResponse([
            'success'    => true,
            'numResults' => count($users),
            'html'       => $this->renderView(
                'VocalizrAppBundle:Admin:adminPasswordResults.html.twig',
                ['results' => $users]
            ),
        ]);
    }

    /**
     * @Route("/admin/password-change/save", name="admin_password_change_save")
     * @param Request $request
     * @return JsonResponse
     */
    public function adminPasswordChangeSave(Request $request)
    {
        if (!$this->getUser() || !$this->getUser()->getIsAdmin()) {
            throw new AccessDeniedHttpException();
        }

        $userId = $request->get('id');
        if (!$userId) {
            throw new BadRequestHttpException('Field id is not specified');
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('VocalizrAppBundle:UserInfo')->find($userId);
        if (!$user) {
            throw new BadRequestHttpException('Review not found');
        }

        $validator = $this->get('validator');
        $violations = $validator->validateValue($password = $request->get('password'), [
            new NotBlank(),
        ]);

        if ($violations->count() > 0) {
            throw new BadRequestHttpException('Entered password is not valid.');
        }
        $encoder  = new MessageDigestPasswordEncoder('sha1', false, 1);
        $password = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
<?php

namespace Vocalizr\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\Validator\Constraints\Email;

use Vocalizr\AppBundle\Entity\ResetPassRequest as ResetPassRequest;

class LoginController extends Controller
{
    /**
     * Login page
     *
     * @Route("/login", name="login")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('dashboard'));
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        $response = new Response();
        $response->setPublic();

        // If no content, then generate view
        if (!isset($content)) {
            $content = $this->renderView('VocalizrAppBundle:Login:index.html.twig', [
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            ]);
        }
        $response->setContent($content);
        $response->setEtag(md5($content));

        return $response;
    }

    /**
     * Redirect to facebook oauth page
     *
     * @param Request $request
     * @Route("/login/facebook", name="login_facebook")
     */
    public function facebookAction(Request $request)
    {
        $session       = $request->getSession();
        $facebookState = 'vocalizr' . (time() + rand(0, 999999));

        $session->set('facebookState', $facebookState);

        $facebookAppId    = $this->container->getParameter('facebook_app_id');
        $facebookAppScope = $this->container->getParameter('facebook_app_scope');

        $url  = 'http://www.facebook.com/dialog/oauth/?';
        $data = [
            'scope'        => $facebookAppScope,
            'client_id'    => $facebookAppId,
            'redirect_uri' => $this->generateUrl('login_facebook_success', [], true),
            'state'        => $facebookState,
        ];

        return $this->redirect($url . http_build_query($data));
        exit;
    }

    /**
     * This page will contain javascript to reload page to log them in
     *
     * @param Request $request
     * @Route("/login/facebook/success", name="login_facebook_success")
     * @Template()
     */
    public function facebookSuccessAction(Request $request)
    {
        return [];
    }

    /**
     * Reset password form
     *
     * @Route("/login/resetpass", name="_reset_pass")
     * @Template()
     */
    public function resetPassAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            // Check email address
            if (!$email = $request->get('email', false)) {
                $request->query->set('error', 'Email address field is blank');
                return [];
            }

            // lets check if the email address exists
            $em = $this->getDoctrine()->getEntityManager();

            // lets check if the email address exists
            if (!$userInfo = $em->getRepository('VocalizrAppBundle:UserInfo')->findFirstByEmail($email)) {
                $request->query->set('notice', 'If your account exists, you will receive an email with instructions to reset your password');
                return [];
            }

            if (!$userInfo->getIsActive()) {
                $request->query->set('error', 'This account is not active, please write to support at help@vocalizr.com to reactivate');
                return [];
            }

            if (!$obj = $em->getRepository('VocalizrAppBundle:ResetPassRequest')->findOneBy(['user_info' => $userInfo->getId()])) {
                // Save reset password request
                $obj = new ResetPassRequest();
                $obj->setUniqueKey($obj->generateUniqueKey());
                $obj->setUserInfo($userInfo);

                $em->persist($obj);
                $em->flush();
            }
            $key = $obj->getUniqueKey();

            // Send email
            $dispatcher = $this->get('hip_mandrill.dispatcher');
            $message    = new \Hip\MandrillBundle\Message();
            $message
                ->addTo($userInfo->getEmail())
                ->addGlobalMergeVar('USER', $userInfo->getUsernameOrFirstName())
                ->addGlobalMergeVar('RESETURL', $this->generateUrl('reset_pass_confirm', [
                    'key' => $key,
                ], true))
                ->setTrackOpens(true)
                ->setTrackClicks(true);

            $dispatcher->send($message, 'member-forgot-password');

            $this->getRequest()->query->set('notice', 'Reset password request has been emailed');
        }
    }

    /**
     * Confirmation link from reset password email
     *
     * @Route("/login/resetpass/{key}", name="reset_pass_confirm")
     * @Template()
     */
    public function confirmResetPassAction(Request $request)
    {
        // Check if email change request is valid
        $em = $this->getDoctrine()->getEntityManager();

        if (!$result = $em->getRepository('VocalizrAppBundle:ResetPassRequest')->findOneBy(['unique_key' => $request->get('key')])) {
            throw $this->createNotFoundException('Invalid reset pass request');
        }
        $userInfo = $result->getUserInfo();

        $form = $this->createForm(new \Vocalizr\AppBundle\Form\Type\ResetPasswordType());

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                // set new password
                $data = $form->getData();

                // Encode password
                $encoder  = new MessageDigestPasswordEncoder('sha1', false, 1);
                $password = $encoder->encodePassword($data->getPassword(), $userInfo->getSalt());
                $userInfo->setPassword($password);

                $em->persist($userInfo);
                $em->remove($result); // remove reset password request
                $em->flush();

                $this->get('session')->setFlash('notice', 'Your password has successfully been reset');

                return $this->redirect($this->generateUrl('login'));
            } else {
                $request->query->set('error', 'Please fix error below');
            }
        }

        return ['form' => $form->createView(), 'userInfo' => $userInfo, 'key' => $result->getUniqueKey()];
    }

    /**
     * Confirmation link from reset password email
     *
     * @Route("/login/resend-activation", name="resend_activation")
     * @Template()
     */
    public function resendActivationAction(Request $request)
    {
        $session      = $request->getSession();
        $em           = $this->getDoctrine()->getManager();
        $userInfoRepo = $em->getRepository('VocalizrAppBundle:UserInfo');

        $userInfoId = $session->get('activate_user_id', false);

        if (!$userInfoId) {
            throw $this->createNotFoundException('Something went wrong, please try again later');
        }

        if (!$userInfo = $userInfoRepo->find($userInfoId)) {
            throw $this->createNotFoundException('Ooops. An error has occured. Please try again later.');
        }

        $email = $userInfo->getEmail();

        if ($request->getMethod() == 'POST') {
            // Validate email
            $emailConstraint          = new Email();
            $emailConstraint->message = 'Invalid email address';

            $errorList = $this->get('validator')->validateValue($request->get('email'), $emailConstraint);
            $newEmail  = $request->get('email');
            if (empty($newEmail)) {
                $request->query->set('error', 'Email address required');
                return ['email' => $request->get('email')];
            } elseif (count($errorList) > 0) {
                $request->query->set('error', $errorList[0]->getMessage());
                return ['email' => $request->get('email')];
            }

            // Check if email already exists, if they change their email
            if ($userInfo->getEmail() != $request->get('email') && $userInfoRepo->findFirstByEmail($request->get('email'))) {
                $request->query->set('error', 'Email address already exists');
                return ['email' => $request->get('email')];
            }

            $userInfo->setEmail($request->get('email'));
            $em->persist($userInfo);
            $em->flush();

            // Send email
            $this->get('service.mail')->sendRegisterActivate(['userInfo' => $userInfo]);

            $request->getSession()->remove('activate_user_id');

            $this->get('session')->getFlashBag()->add('notice', 'Activation email has been resent. Please check your email');

            return $this->redirect($this->generateUrl('login'));
        }

        return ['email' => $email];
    }

    /**
     * @Route("/login/fb", defaults={"_format"="json"})
     * @Template()
     */
    public function fbAction(Request $request)
    {
        $this->em = $this->getDoctrine()->getEntityManager();

        $facebook = new \Facebook_Facebook();

        $user = $this->get('security.context')->getToken()->getUser();

        try {
            $fbUser = $facebook->getUser();
        } catch (Exception $e) {
            $fbUser = null;
        }

        if ($fbUser) {
            try {
                $userProfile = $facebook->api('/me');

                // Check to see if id exists
                if (!isset($userProfile['id']) || empty($userProfile['id'])) {
                    $json = json_encode(['error' => true]);
                    return ['json' => $json];
                }
                $fbUserId = $userProfile['id'];

                // Get member from facebook id
                if (!$member = $this->em->getRepository('VocalizrAppBundle:Member')->findOneBy(['facebook' => $fbUserId])) {
                    $json = json_encode(['error' => true]);
                    return ['json' => $json];
                }

                $token = new UsernamePasswordToken($member->getUsername(), null, 'members', $member->getRoles());
                $this->get('security.context')->setToken($token);

                $result = ['ok' => true];
            } catch (FacebookApiException $e) {
                $json = json_encode(['error' => true]);
                return ['json' => $json];
            }
        }

        $json = json_encode($result);

        return ['json' => $json];
    }
}
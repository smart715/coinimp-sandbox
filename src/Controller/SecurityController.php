<?php

namespace App\Controller;

use App\Form\CaptchaLoginType;
use FOS\UserBundle\Controller\SecurityController as SecurityBaseController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends SecurityBaseController
{
    /** @var FormInterface|null */
    private $form;

    public function loginAction(Request $request): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('dashboard');
        }
        $this->form = $this->createForm(CaptchaLoginType::class);
        $this->form->handleRequest($request);
        if ($this->form->isSubmitted() && $this->form->isValid()) {
            return $this->redirectToRoute('fos_user_security_check', [
                'request' => $request,
            ], 307);
        }
        return parent::loginAction($request);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function renderLogin(array $data): Response
    {
        return $this->render('@FOSUser/Security/login.html.twig', array_merge($data, [
            'form' => $this->form->createView(),
        ]));
    }
}

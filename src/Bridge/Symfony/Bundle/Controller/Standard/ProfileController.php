<?php

declare(strict_types=1);

namespace Damax\User\Bridge\Symfony\Bundle\Controller\Standard;

use Damax\User\Application\Command\ChangePassword;
use Damax\User\Application\Command\UpdateUser;
use Damax\User\Application\Dto\UserInfoDto;
use Damax\User\Application\Service\PasswordService;
use Damax\User\Application\Service\UserService;
use Damax\User\Bridge\Symfony\Bundle\Form\Type\ChangePasswordType;
use Damax\User\Bridge\Symfony\Bundle\Form\Type\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 * @Security("has_role('ROLE_MEMBER')")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("", methods={"GET"}, name="profile_view")
     */
    public function viewAction(UserService $service): Response
    {
        $user = $service->fetch($this->getUser()->getUsername());

        return $this->render('@DamaxUser/profile/view.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/edit", name="profile_edit")
     */
    public function editAction(Request $request, UserService $service): Response
    {
        $user = $service->fetch($this->getUser()->getUsername());

        $form = $this->createForm(ProfileType::class, UserInfoDto::fromUserDto($user))->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new UpdateUser();
            $command->info = $form->getData();
            $command->userId = $user->id;

            $service->update($command);

            $message = $this->get('translator')->trans('profile.message.updated', [], 'damax-user');

            $this->addFlash('success', $message);

            return $this->redirectToRoute('profile_view');
        }

        return $this->render('@DamaxUser/profile/edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    /**
     * @Route("/change-password", name="profile_change_password")
     */
    public function changePasswordAction(Request $request, PasswordService $service): Response
    {
        $form = $this->createForm(ChangePasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new ChangePassword();
            $command->password = $form->getData()->newPassword;
            $command->userId = $this->getUser()->getUsername();

            $service->changePassword($command);

            $message = $this->get('translator')->trans('password.message.changed', [], 'damax-user');

            $this->addFlash('success', $message);

            return $this->redirectToRoute('profile_view');
        }

        return $this->render('@DamaxUser/profile/change_password.html.twig', ['form' => $form->createView()]);
    }
}

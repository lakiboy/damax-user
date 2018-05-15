<?php

declare(strict_types=1);

namespace Damax\User\Bridge\Symfony\Bundle\Controller\Api;

use Damax\Common\Bridge\Symfony\Bundle\Annotation\Command;
use Damax\Common\Bridge\Symfony\Bundle\Annotation\Serialize;
use Damax\User\Application\Command\DisableUser;
use Damax\User\Application\Command\EnableUser;
use Damax\User\Application\Command\UpdateUser;
use Damax\User\Application\Dto\UserDto;
use Damax\User\Application\Dto\UserLoginDto;
use Damax\User\Application\Exception\UserNotFound;
use Damax\User\Application\Service\UserLoginService;
use Damax\User\Application\Service\UserService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/user")
 */
class UserController
{
    private $service;
    private $tokenStorage;

    public function __construct(UserService $service, TokenStorageInterface $tokenStorage)
    {
        $this->service = $service;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @OpenApi\Get(
     *     tags={"user"},
     *     summary="List users.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Parameter(
     *         name="page",
     *         type="integer",
     *         in="query",
     *         default=1
     *     ),
     *     @OpenApi\Response(
     *         response=200,
     *         description="Users list.",
     *         @OpenApi\Schema(type="array", @OpenApi\Items(ref=@Model(type=UserDto::class)))
     *     )
     * )
     *
     * @Method("GET")
     * @Route("/users")
     * @Serialize()
     */
    public function listAction(Request $request): Pagerfanta
    {
        return $this->service
            ->fetchRange()
            ->setAllowOutOfRangePages(true)
            ->setMaxPerPage(20)
            ->setCurrentPage($request->query->getInt('page', 1))
        ;
    }

    /**
     * @OpenApi\Get(
     *     tags={"user"},
     *     summary="Get user.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Response(
     *         response=200,
     *         description="User.",
     *         @OpenApi\Schema(ref=@Model(type=UserDto::class))
     *     ),
     *     @OpenApi\Response(
     *         response=404,
     *         description="User not found."
     *     )
     * )
     *
     * @Method("GET")
     * @Route("/users/{id}")
     * @Serialize()
     *
     * @throws NotFoundHttpException
     */
    public function getAction(string $id): UserDto
    {
        try {
            return $this->service->fetch($id);
        } catch (UserNotFound $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @OpenApi\Put(
     *     tags={"user"},
     *     summary="Enable user.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Response(response=204, description="User enabled."),
     *     @OpenApi\Response(response=404, description="User not found.")
     * )
     *
     * @Method("PUT")
     * @Route("/users/{id}/enable")
     *
     * @throws NotFoundHttpException
     */
    public function enableAction(string $id): Response
    {
        $command = new EnableUser();
        $command->userId = $id;
        $command->editorId = $this->tokenStorage->getToken()->getUsername();

        try {
            $this->service->enable($command);
        } catch (UserNotFound $e) {
            throw new NotFoundHttpException();
        }

        return Response::create('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OpenApi\Delete(
     *     tags={"user"},
     *     summary="Disable user.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Response(response=204, description="User disabled."),
     *     @OpenApi\Response(response=404, description="User not found.")
     * )
     *
     * @Method("DELETE")
     * @Route("/users/{id}/disable")
     *
     * @throws NotFoundHttpException
     */
    public function disableAction(string $id): Response
    {
        $command = new DisableUser();
        $command->userId = $id;
        $command->editorId = $this->tokenStorage->getToken()->getUsername();

        try {
            $this->service->disable($command);
        } catch (UserNotFound $e) {
            throw new NotFoundHttpException();
        }

        return Response::create('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OpenApi\Get(
     *     tags={"user"},
     *     summary="List user logins.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Parameter(
     *         name="page",
     *         type="integer",
     *         in="query",
     *         default=1
     *     ),
     *     @OpenApi\Response(
     *         response=200,
     *         description="User logins list.",
     *         @OpenApi\Schema(type="array", @OpenApi\Items(ref=@Model(type=UserLoginDto::class)))
     *     )
     * )
     *
     * @Method("GET")
     * @Route("/users/{id}/logins")
     * @Serialize()
     */
    public function loginsAction(Request $request, string $id, UserLoginService $service): Pagerfanta
    {
        return $service
            ->fetchRangeByUser($id)
            ->setAllowOutOfRangePages(true)
            ->setMaxPerPage(20)
            ->setCurrentPage($request->query->getInt('page', 1))
        ;
    }

    /**
     * @OpenApi\Get(
     *     tags={"user"},
     *     summary="Get authenticated user.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Response(
     *         response=200,
     *         description="Authenticated user.",
     *         @OpenApi\Schema(ref=@Model(type=UserDto::class, groups={"profile"}))
     *     )
     * )
     *
     * @Method("GET")
     * @Route("")
     * @Serialize({"profile"})
     */
    public function getAuthenticatedAction(): UserDto
    {
        return $this->service->fetch($this->tokenStorage->getToken()->getUsername());
    }

    /**
     * @OpenApi\Patch(
     *     tags={"user"},
     *     summary="Update authenticated user.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @OpenApi\Schema(ref=@Model(type=UpdateUser::class, groups={"profile"}))
     *     ),
     *     @OpenApi\Response(
     *         response=200,
     *         description="Authenticated user.",
     *         @OpenApi\Schema(ref=@Model(type=UserDto::class, groups={"profile"}))
     *     )
     * )
     *
     * @Method("PATCH")
     * @Route("")
     * @Command(UpdateUser::class, validate=true, groups={"profile"})
     * @Serialize({"profile"})
     */
    public function patchAuthenticatedAction(UpdateUser $command): UserDto
    {
        $command->userId = $this->tokenStorage->getToken()->getUsername();

        return $this->service->update($command);
    }
}

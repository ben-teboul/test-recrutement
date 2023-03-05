<?php

namespace App\Controller;

use App\Repository\DepartmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DepartmentsController extends AbstractController
{
    public function __invoke(
        Request $request,
        DepartmentRepository $departmentRepository,
        RouterInterface $router,
        TranslatorInterface $translator,
        SluggerInterface $slugger
    ) : Response {
        $queryString = '';
        if (!empty($request->getQueryString())) {
            $queryString = '?' . $request->getQueryString();
        }

        $trueUrl = $router->generate('departments', [], UrlGeneratorInterface::ABSOLUTE_URL) . $queryString;

        if ($trueUrl !== $request->getUri()) {
            return $this->redirect($trueUrl, Response::HTTP_MOVED_PERMANENTLY);
        }

        $response = new Response();
        $response
            ->setLastModified($departmentRepository->getLastModified())
            ->setPublic()
            ->setMaxAge(0)
        ;
        $response->headers->addCacheControlDirective('no-cache');

        if ($response->isNotModified($request)) {
            return $response;
        }

        $departments = $departmentRepository->findAllByName();
        $departmentsUrl = [];
        foreach ($departments as $department) {
            $departmentsUrl[$department->getId()] = $router->generate(
                'department',
                [
                    'code' => $department->getCode(),
                    'name' => strtolower($slugger->slug($department->getName()))
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $viewParameters = [
            'departments' => $departments,
            'description' => $translator->trans(
                'departments.description'
            ),
            'departmentsUrl' => $departmentsUrl
        ];

        return $this->render('departments.html.twig', $viewParameters, $response);
    }
}

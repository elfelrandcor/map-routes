<?php


namespace App\Controller\Route;

use App\Repository\RouteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/route/{id}", name="route", requirements={"id"="\d+"})
 */
final class RouteController extends AbstractController {

    public function __invoke(
        RouteRepository $repository,
        string $id
    ) {
        $route = $repository->find($id);

        return $this->render(
            'route/route.html.twig',
            ['route' => $route]
        );
    }
}
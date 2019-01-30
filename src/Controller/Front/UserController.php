<?php

namespace App\Controller\Front;


use App\Entity\Game;
use App\Entity\GamePlatform;
use App\Entity\Platform;
use App\Entity\User;
use App\Form\Front\UserGameType;
use App\Repository\GamePlatformRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller\Front
 * @Route(path="/user", name="user_")
 */
class UserController extends AbstractController {


	/**
	 * @Route(path="/", name="profile")
	 * @return Response
	 */
	public function profile(): Response {
		return $this->render('Front/users/profile.html.twig', ['user' => $this->getUser()]);
	}

	/**
	 * @Route(path="/game/add", name="game_add", methods={"POST", "GET"})
	 * @param Request $request
	 * @param ObjectManager $em
	 *
	 * @return Response
	 */
	public function addGame(Request $request, ObjectManager $em): Response {
		$user = $this->getUser();
		$gamePlatform = new GamePlatform();
		$form = $this->createForm(UserGameType::class, $gamePlatform);
		$req = $request->request;
		$p = $req->get('user_game')['platform'] ?? false;
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid() && $p){
			$platform = $em->getRepository(Platform::class)->find( $req->get('user_game')['platform']);
			$game = $em->getRepository(Game::class)->find( $req->get('user_game')['game']);
			$gamePlatform = $em->getRepository(GamePlatform::class)->findOneBy(['platform' => $platform, 'game' => $game]);
			$user->addGamePlatform($gamePlatform);
			$em->persist($user);
			$em->flush();
			return $this->redirectToRoute('user_game_add');
		}
		return $this->render('Front/users/add_game.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route(path="/game/edit/{gamePlatform}", name="game_edit", methods={"POST", "GET"})
	 * @param Request $request
	 * @param GamePlatform $gamePlatform
	 *
	 * @param GamePlatformRepository $repository
	 *
	 * @param ObjectManager $em
	 *
	 * @return Response
	 */
	public function editGame(Request $request, GamePlatform $gamePlatform, GamePlatformRepository $repository, ObjectManager $em) {
		/** @var $user User  **/
		$user = $this->getUser();
		$form = $this->createForm(UserGameType::class, $gamePlatform );
		$form->handleRequest($request);
		if($request->isXmlHttpRequest()){
			return $this->render('Front/users/edit_game.html.twig', ['form' => $form->createView()]);
		}
		if($form->isSubmitted() && $form->isValid()){
			$game = $form->get('game')->getData();
			$platform = $form->get('platform')->getData();
			$gp = $repository->findOneBy(['game' => $game, 'platform' => $platform]);
			$user->addGamePlatform($gp);
			$user->removeGamePlatform($gamePlatform);
			$em->flush();
			return $this->redirectToRoute('user_profile');
		}
		return $this->render('Front/users/edit_game.html.twig', ['form' => $form->createView()]);
	}






}
<?php

namespace App\Controller\Front;


use App\Entity\Advert;
use App\Entity\GamePlatform;
use App\Form\Front\AdvertType;
use App\Repository\AdvertKindRepository;
use App\Repository\AdvertRepository;
use App\Repository\AdvertStatusRepository;
use App\Repository\OfferRepository;
use App\Repository\PlatformRepository;
use App\Security\AdvertVoter;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdvertController
 * @package App\Controller\Front
 * @Route(path="/advert", name="advert_")
 */
class AdvertController extends AbstractController
{
    /**
     * @Route(path="/", name="list")
     * @param Request $request
     * @return Response
     */
    public function showAll(Request $request,
                            AdvertRepository $advertRepository,
                            PlatformRepository $platformRepository,
                            AdvertKindRepository $advertKindRepository,
                            PaginatorInterface $paginator
    ): Response
    {
        $user = $this->getUser() ?? null;
    	if(is_object($user)){
		    $userId = $this->getUser()->getId();
		    $lat = $user->getLat();
		    $lon = $user->getLon();
	    }
        $platforms = $platformRepository->findAll();
        $advertKinds = $advertKindRepository->findAll();
        $distances = [5,10,15,20];
        if($request->getMethod() == 'POST'){
            $req = $request->request;
            $params = [
                'game' => $req->get('game', null),
                'userId' => $userId ?? null,
                'platform' => $req->get('platform', []),
                'advert_kind' => $req->get('advert_kind', []),
                'distance' => $req->get('distance'),
                'category' => null,
            ];
            if(is_object($user)){
                $params['lat'] = $this->getUser()->getLat();
                $params['lon'] = $this->getUser()->getLon();
            }
            $adverts = $advertRepository->search($params);
            $adverts = $paginator->paginate($adverts, $request->query->get('page', 1), 4);
            return $this->render('Front/adverts/show_all.html.twig', [
                'adverts' => $adverts,
                'platforms' => $platforms,
                'advertKinds' => $advertKinds,
                'distances' => $distances,
                'filters' => $params
            ]);
        }

//        $adverts = $advertRepository->findAll();
    	$adverts = $advertRepository->all($userId ?? null, true, $lat ?? null, $lon ?? null );
        $adverts = $paginator->paginate($adverts, $request->query->get('page', 1), 4);
        $filters = [
            'game' => null,
            'userId' => null,
            'platform' => null,
            'advert_kind' => null,
            'distance' => null,
            'category' => null,
        ];
        return $this->render('Front/adverts/show_all.html.twig', [
            'adverts' => $adverts,
            'platforms' => $platforms,
            'advertKinds' => $advertKinds,
            'distances' => $distances,
            "filters" => $filters
        ]);
    }

	/**
	 * @Route(path="/show/{id}", name="show")
	 */
	public function show($id, AdvertRepository $advertRepository, OfferRepository $offerRepository)
    {
		$advert = $advertRepository->findOneId($id);
		if($this->getUser()){
            $canOffer = $offerRepository->findByUserAndAdvert($this->getUser()->getId(), $id);
            if($advert["user_id"] == $this->getUser()->getId()){
                $offers = $offerRepository->findOffersByAdvert($id);
                $cntOffers = count($offers);
            }
		}
		return $this->render('Front/adverts/show.html.twig', [
		    'advert' => $advert,
            'offers' => $offers ?? [],
            'can_offer' => $canOffer ?? [],
            'cnt_offers' => $cntOffers ?? 0
        ]);
    }


	/**
	 * @Route(path="/new", name="new", methods={"POST", "GET"})
	 * @param Request $request
	 * @param ObjectManager $em
	 * @param AdvertStatusRepository $advertStatusRepository
	 *
	 * @return Response
	 */
	public function new(Request $request, ObjectManager $em, AdvertStatusRepository $advertStatusRepository)
	{
        $advert = new Advert();
        $user = $this->getUser();
        $advert->setCreatedBy($user);
        $form = $this->createForm(AdvertType::class, $advert);
		$form->handleRequest($request);
		if($request->isXmlHttpRequest()){
			return $this->render('Front/adverts/new.html.twig', ['form' => $form->createView()]);
		}
		if($form->isSubmitted() && $form->isValid()){
			$status = $advertStatusRepository->findOneBy(['name' => 'Ouvert']);
			$advert->setAdvertStatus($status);
			$this->saveGamePlatform($em, $form, $advert);
			$em->persist($advert);
			$em->flush();
			return $this->redirectToRoute('user_profile', ['slug' => $user->getSlug()]);
		}
		return $this->render('Front/adverts/new.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route(path="/edit/{id}", name="edit", methods={"POST", "GET"})
	 * @param Request $request
	 * @param Advert $advert
	 * @param ObjectManager $em
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function edit(Request $request, Advert $advert, ObjectManager $em): Response {
		$this->denyAccessUnlessGranted(AdvertVoter::OWNER, $advert);
		$form = $this->createForm(AdvertType::class, $advert);
		$form->handleRequest($request);
        if($request->isXmlHttpRequest()){
            return $this->render('Front/adverts/edit.html.twig', ['form' => $form->createView()]);
        }
		if($form->isSubmitted() && $form->isValid()){
			$this->saveGamePlatform($em, $form, $advert);
			$em->flush();
			return $this->redirectToRoute('user_profile', ['slug' => $advert->getCreatedBy()->getSlug()]);
		}
		return $this->render('Front/adverts/edit.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route(path="/{id}", name="delete", methods={"DELETE"})
	 * @param Request $request
	 * @param Advert $advert
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function delete(Request $request, Advert $advert) {
		$this->denyAccessUnlessGranted(AdvertVoter::OWNER, $advert);
		if ($this->isCsrfTokenValid('delete'.$advert->getId(), $request->request->get('_token'))) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->remove($advert);
			$entityManager->flush();
		}

		return $this->redirectToRoute('user_profile', ['slug' => $advert->getCreatedBy()->getSlug()]);
	}




	private function saveGamePlatform(ObjectManager &$em, FormInterface &$form, Advert &$advert) {
		$repo = $em->getRepository( GamePlatform::class );
		$gameOwned = $repo->findOneByGameAndUser($form->get('gameOwned')->getData(), $this->getUser());
		$advert->setGameOwned($gameOwned);
		if($form->get('advertKind')->getData()->getName() == 'Echange' && $form->get('gameWanted') && $form->get('platform')){
			$gameWanted = $repo->findOneBy(['game' => $form->get('gameWanted')->getData(), 'platform' => $form->get('platform')->getData()]);
			$advert->setGameWanted($gameWanted);
		}
	}

}































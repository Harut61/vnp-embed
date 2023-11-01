<?php

namespace App\Controller;

use App\Services\BaseService;
use App\Services\EmbedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmbedController extends AbstractController
{
    /**
     * @Route("/embed", name="embed")
     */
    public function embed(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "//{$_ENV['APP_NAME']}-{$_ENV['APP_ENV']}-player.{$_ENV['PLAYER_HOST']}";

        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
            'video_url' => 'https://ivn-dev-player.b-cdn.net/latest/demo/lineup/lineup_original.m3u8'
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->json( [
            'message' => "embed"
        ]);
    }

    /**
     * @Route("/embed/gm", name="embed_gm")
     */
    public function embedGoodMorning(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "//{$_ENV['APP_NAME']}-{$_ENV['APP_ENV']}-player.{$_ENV['PLAYER_HOST']}";
        
        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
            'video_url' => 'https://ivn-dev-player.b-cdn.net/latest/demo/intro-m/vod-2e3506e1-16e1-4dc3-94f6-e1d540c6cc1c.m3u8'
        ]);
    }

    /**
     * @Route("/embed/ga", name="embed_ga")
     */
    public function embedGoodAfterNoon(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "https://bs-vod.{$_ENV['PLAYER_HOST']}/Main_Intro%20Morning.mp4";

        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
        ]);
    }

    /**
     * @Route("/embed/ge", name="embed_ge")
     */
    public function embedGoodEvening(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "//{$_ENV['APP_NAME']}-{$_ENV['APP_ENV']}-player.{$_ENV['PLAYER_HOST']}";

        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
            'video_url' => 'https://ivn-dev-player.b-cdn.net/latest/demo/intro/vod-008efa09-a393-45fb-b2fd-4f5010805f24.m3u8'
        ]);
    }


    /**
     * @Route("/embed/or", name="embed_or")
     */
    public function embedOutRo(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "https://bs-vod.{$_ENV['PLAYER_HOST']}/Main_Intro%20Morning.mp4";

        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
        ]);
    }

    /**
     * @Route("/embed/ir", name="embed_ir")
     */
    public function embedIntRo(Request $request, EmbedService $embedService): Response
    {
        $token = $request->get("token", null);
        if(empty($token)){
            return $this->render('embed/invalid_request.html.twig');
        }
        $authResponse = $embedService->embedAuthentication($token);

        if(!$authResponse["status"]){
            return $this->render('embed/invalid_request.html.twig');
        }

        $baseUrl = "https://bs-vod.{$_ENV['PLAYER_HOST']}/Main_Intro%20Morning.mp4";

        return $this->render('embed/index.html.twig', [
            'base_url' => $baseUrl,
        ]);
    }
}
<?php
namespace Drupal\smsgate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class SMSGateController extends ControllerBase
{

    private static $input;

    public static function defaultController($page=null)
    {
        $data = [];
        $data['input'] = self::input();
        if ( ! self::checkLogin($data) ) return self::theme($data);

        /** @note default value for page */
        if ( $page == null ) $page = 'index';
        else if ( $page == 'list' ) $page = 'collect';

        $data['page'] = $page;

        if ( $render = self::$page($data) ) return $render;
        else {
            $theme = self::theme($data);
            return $theme;
        }
    }


    private static function theme($data) {
        return [
            '#theme' => 'smsgate.layout',
            '#data' => $data,
        ];
    }


    private static function input() {

        if ( empty(self::$input) ) {
            $request = \Drupal::request();
            $get = $request->query->all();
            $post = $request->request->all();
            self::$input = array_merge( $get, $post );
        }
        return self::$input;
    }

    private static function checkLogin(&$data) {
        if ( $uid = self::uid() ) {
            return $uid;
        }
        else {
            $data['error'] = 'Please, login first to access this page.';
            return false;
        }
    }

    private static function uid() {
        return \Drupal::currentUser()->getAccount()->id();
    }

    private static function index( &$data ) {
        $data['title'] = "index page";
    }

    private static function send( &$data ) {
        $re = [];
        $re['code'] = 0;
        $response = new JsonResponse( $re );
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

}
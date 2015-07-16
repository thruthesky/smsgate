<?php
namespace Drupal\smsgate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\smsgate\Entity\Data;
use Drupal\user\UserAuth;
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


    /**
     *
     * It only checks if the password is right or not.
     * @note it does not login.
     * @param $name
     * @param $password
     * @return mixed
     *
     *      - User ID on success.
     *      - FALSE on failure
     */
    public static function checkPassword($name, $password)
    {
        $userStorage = \Drupal::entityManager();
        $passwordChecker = \Drupal::service('password');
        $auth = new UserAuth($userStorage, $passwordChecker);
        return $auth->authenticate($name, $password);
    }


    private static function index( &$data ) {
        $data['title'] = "index page";
    }

    private static function send( &$data ) {
        $request = \Drupal::request();
        $re = [];
        if ( self::validateInput($re) ) {
            if ( $uid = self::checkUserInfo($re) ) {
                $data = Data::create();
                $data->setOwnerId($uid);
                $data->set('number', $request->get('number'));
                $data->set('message', $request->get('message'));
                $data->set('stamp_record', time());
                $data->set('stamp_send', 0);
                $data->set('result', '');
                $data->save();
                $re['code'] = 0;
                $re['id'] = $data->id();
            }
        }
        $response = new JsonResponse( $re );
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

    private static function validateInput(&$re)
    {
        $request = \Drupal::request();
        if ( ! $request->get('number') ) {
            $re['code'] = -409;
            $re['message'] = "Number is missing.";
            return false;
        }
        else if ( ! $request->get('message') ) {
            $re['code'] = -410;
            $re['message'] = 'Message is empty.';
            return false;
        }

        if ( strlen($request->get('message')) > 80 ) {
            $re['code'] = -411;
            $re['message'] = 'Message is longer than 80 characters';
            return false;
        }
        return true;
    }

    private static function checkUserInfo(&$re)
    {
        $request = \Drupal::request();
        if ( $uid = self::checkPassword($request->get('username'), $request->get('password')) ) return $uid;
        else {
            $re['code'] = -1;
            $re['message'] = "Login failed";
            return false;
        }
    }

}
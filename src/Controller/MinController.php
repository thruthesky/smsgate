<?php
namespace Drupal\min\Controller;

use Drupal\Core\Controller\ControllerBase;

class MinController extends ControllerBase
{

    private static $input;

    private static function theme($data) {
        return [
            '#theme' => 'message.layout',
            '#data' => $data,
        ];
    }

    public static function defaultController($page)
    {
        $data = ['page' => $page];
        $data['input'] = self::input();
        if (!self::checkLogin($data)) return self::theme($data);
        if ($page == 'list') $page = 'collect';
        if ($render = self::$page($data)) return $render;
        else return self::theme($data);
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

}
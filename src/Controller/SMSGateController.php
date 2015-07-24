<?php
/**
 *
 * https://docs.google.com/document/d/1KDT0XGtfjeelYr7F01RPyNI0qNf_LRxL9UXSwp6Mz0U/edit#heading=h.5wq4irb6gdmr
 * https://docs.google.com/document/d/1eKMqEP2JZXZVry-xQO7rc7IcNaFlQroetYv4WwhuZC4/edit#heading=h.z9quhudk3pel
 */
namespace Drupal\smsgate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\library\Library;
use Drupal\smsgate\Entity\Data;
use Drupal\smsgate\Entity\Sent;
use Drupal\user\UserAuth;
use Symfony\Component\HttpFoundation\JsonResponse;

class SMSGateController extends ControllerBase
{

    const no_message = -40123;
    const number_is_string = -40124;
    const no_number = -40125;
    private static $input;

    public static function getErrorMessage($code) {
        if ( $code >= 0 ) return null;
        $msg = "Unknown";
        switch( $code ) {
            case self::no_message : return "No message.";
            case self::number_is_string : return "The number is string. It should be numeric.";
            case self::no_number : return "No number. Number is not provided or wrong number(string only) is provided.";
        }
        return $msg;
    }

    public static function defaultController($page=null)
    {
        $data = [];
        $data['input'] = self::input();

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
        $request = \Drupal::request();
        if ( $uid = self::uid() ) {
            return $uid;
        }
        else if ( $uid = self::checkUserInfo($data) ) {
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

    private static function json($re) {
        $response = new JsonResponse( $re );
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
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
                self::createDataRecord($re, $uid);
            }
        }
        return self::json($re);
    }

    private static function write( &$data ) {
	self::checkLogin($data);
    }
    private static function write_submit( &$data ) {
        if ( self::validateInput($data) ) {
            $request = \Drupal::request();
            $_numbers = $request->get('numbers');
            $numbers = explode("\n", $_numbers);
            foreach($numbers as $number) {
                $id = self::insertData(self::uid(), $number, $request->get('message'), $request->get('priority'));
                if ( $id < 0 ) {
                    $data['list'][$id]['number'] = "$number - ERROR($id) : " . self::getErrorMessage($id);
                }
                else $data['list'][$id]['number'] = $number;
            }
        }
    }

    private static function mass_write_submit( &$data ) {
        $request = \Drupal::request();

        /// Check Input
        $message = $request->get('message');
        $howmany = $request->get('howmany');
        $day = $request->get('day');
        $last_number = $request->get('last_number');
        $err = [];
        if ( empty($message) ) $err = ['error'=>-4001, 'message'=>'No message'];
        if ( strlen($message) < 10 ) $err = ['error'=>-4001, 'message'=>'Too short message'];
        if ( strlen($message) > 159 ) $err = ['error'=>-4001, 'message'=>'Too long message'];
        if ( empty($howmany) || $howmany == 0 ) $err = ['error'=>-4001, 'message'=>'Empty how many numbers'];
        if ( empty($day) ) $err = ['error'=>-4001, 'message'=>'Empty days ago'];
        if ( $err ) {
            $data['error'] = $err['error'];
            $data['message'] = $err['message'];
            return;
        }

        /// GET NUMBERS

        $stamp = time() - $day * 60 * 24;
        $db = db_select("sms_numbers");
        $db->fields(null, ['idx','count_sent','mobile_number']);
        $db->condition('stamp_last_sent', $stamp, '<');
        $db->range(0, $howmany);
        $result = $db->execute();
        $list = [];
        while ( $row = $result->fetchAssoc(\PDO::FETCH_ASSOC) ) {
            $idx = $row['idx'];
            $count_sent = $row['count_sent'] + 1;
            $number = $row['mobile_number'];
            $id = self::insertData(self::uid(), $number, $message, 0);
            db_update('sms_numbers')
                ->fields(['stamp_last_sent'=>time(), 'count_sent'=>$count_sent])
                ->condition('idx', $idx)
                ->execute();
            $list[] = [
                'number' => $number,
                'id' => $id,
                'message' => self::getErrorMessage($id),
            ];
        }

        if ( ! empty($last_number) ) {
            $id = self::insertData(self::uid(), $last_number, "Mass Message Sending Complete. how may: $howmany, days ago: $day", 0);
            $list[] = [
                'number' => $last_number,
                'id' => 0,
                'message' => "This is the last number!",
            ];
        }

        $data['list'] = $list;
    }


    private static function schedule( &$data ) {
        $data['list'] = Data::loadMultiple();
    }
    private static function sent( &$data ) {
        $data['list'] = Sent::loadMultiple();
    }



    /**
     * @param $data
     * @return JsonResponse
     *
     *
     */
    private static function loadData( &$data ) {
        $re = Data::getDataNextTry();
        return self::json($re);
    }

    private static function validateInput(&$re)
    {
        $request = \Drupal::request();
        $number = $request->get('number');
        $numbers = $request->get('numbers');
        $message = $request->get('message');

        $number = trim($number);
        $message = trim($message);
        if ( empty($number) && empty($numbers) ) {
            $re['error'] = -409;
            $re['message'] = "Number is missing.";
            return false;
        }
        else if ( empty($message) ) {
            $re['error'] = -410;
            $re['message'] = 'Message is empty.';
            return false;
        }

        if ( strlen($request->get('message')) > 159 ) {
            $re['error'] = -411;
            $re['message'] = 'Message is longer than 159 characters';
            return false;
        }
        return true;
    }

    private static function checkUserInfo(&$re)
    {
        $request = \Drupal::request();
        $username = $request->get('username');
        $password = $request->get('password');
        if ( empty($username) || empty($password) ) {
            $re['error'] = -2;
            $re['message'] = "Username or password is empty";
            return false;
        }
        else {
            if ( $uid = self::checkPassword($request->get('username'), $request->get('password')) ) return $uid;
            else {
                $re['error'] = -1;
                $re['message'] = "Login failed";
                return false;
            }
        }
    }

    private static function createDataRecord(&$re,$uid)
    {
        $request = \Drupal::request();
        $id = self::insertData($uid, $request->get('number'), $request->get('message'), $request->get('priority'));
        if ( $id < 0 ) {
            $re['error'] = $id;
            $re['message'] = "ERROR($id) : " . self::getErrorMessage($id);
        }
        else {
            $re['error'] = 0;
            $re['id'] = $id;
        }
    }
    private static function insertData($uid, $number, $message, $priority) {

        $number = self::adjustNumber($number);
        if ( empty($number) ) {
            return self::no_number;
        }
        else if ( ! is_numeric($number) ) {
            return self::number_is_string;
        }
        else if ( empty($message) ) {
            return self::no_message;
        }

        $data = Data::create();
        $data->setOwnerId($uid);
        $data->set('number', $number);
        $data->set('message', $message);
        $data->set('stamp_record', time());
        $data->set('stamp_next_send', 0);
        $data->set('no_send_try', 0);
        if ( empty($priority) ) $priority = 0;
        $data->set('priority', $priority);
        $data->save();
        return $data->id();
    }

    /**
     * @return JsonResponse
     *
     * @see https://docs.google.com/document/d/1jFAlx74PJV_KkkAmPAL0q9oCewBdHv2Av6_CpzIQelg/edit#heading=h.jqu73lxtk4ao
     *
     */
    private static function record_send_result() {
        $request = \Drupal::request();
        $id = $request->get('id');
        if ( is_numeric($id) ) {
            $data = Data::load($id);
            $result = $request->get('result');
            if ( $result == 'Y' ) {
                $sent = Sent::create();
                $sent->setOwnerId($data->getOwnerId());
                $sent->set('stamp_record', $data->get('stamp_record')->value);
                $sent->set('no_send_try', $data->get('no_send_try')->value);
                $sent->set('sender', $request->get('sender'));
                $sent->set('number', $data->get('number')->value);
                $sent->set('message', $data->get('message')->value);
                $sent->save();
                $data->delete();
            }
            else {
                $no = $data->get('no_send_try')->value;
                $no ++;
                Library::log("no:$no");
                $data->set('no_send_try', $no);
                $data->set('sender', $request->get('sender'));
                $data->save();
            }
        }
        else $id = 0;
        return self::json(['error'=>0, 'id'=>$id]);
    }

    private static function adjustNumber($number)
    {
        $number = preg_replace("/[^0-9]/", '', $number); // remove all characters
        $number = str_replace("639", "09", $number);
        $number = str_replace("630", "0", $number);
        $number = str_replace("63", "0", $number);
        return $number;
    }


    private static function mass() {

    }

}
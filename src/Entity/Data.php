<?php
namespace Drupal\smsgate\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the CategoryLog entity.
 *
 * @note refer https://docs.google.com/document/d/1jFAlx74PJV_KkkAmPAL0q9oCewBdHv2Av6_CpzIQelg/edit
 *
 * @ContentEntityType(
 *   id = "smsgate_data",
 *   label = @Translation("SMSGate Data entity"),
 *   base_table = "smsgate_data",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "number",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Data extends ContentEntityBase {

    /**
     *
     * @see https://docs.google.com/document/d/1jFAlx74PJV_KkkAmPAL0q9oCewBdHv2Av6_CpzIQelg/edit#heading=h.e95bj4yvltvz
     *
     *
     * This will extract an SMS data to deliver to SMSGate client(sender)
     *
     * Once it is extracted, it sets 5 minutes for the next try if every it needs to be re-send.
     *
     */
    public static function getDataNextTry()
    {
        self::moveOldFailData();
        $request = \Drupal::request();
        $result = db_select('smsgate_data')
            ->fields(null, ['id'])
            ->condition('stamp_next_send', time(), '<')
            ->orderBy('priority', 'DESC')
            ->orderBy('stamp_next_send', 'ASC')
            ->orderBy('id', 'ASC')
            ->range(0, 1)
            ->execute();
        $row = $result->fetchAssoc(\PDO::FETCH_ASSOC);
        $re = [];
        if ( $row ) {
            $data = self::load($row['id']);
            $data
                ->set('stamp_next_send', time() + 60 * 5)
                ->set('sender', $request->get('sender'))
                ->save();
            $re['id'] = $data->id();
            $re['number'] = $data->get('number')->value;
            $re['message'] = $data->get('message')->value;



            /**
             *
             * @note Delete if the data is not valid and return empty array.
             */
            if ( ! is_numeric($re['number']) ) $re = self::wrongData($data, "Number is not Numeric");
            else if ( strlen($re['message']) > 159 ) $re = self::wrongData($data, "Message must be shorter than 159 letters.");

        }

        return $re;
    }

    /**
     * @param $data
     * @param $error_message
     * @return array
     */
    private static function wrongData($data, $error_message) {
        $request = \Drupal::request();
        $fail = Fail::create();
        $fail->setOwnerId($data->getOwnerId());
        $fail->set('stamp_record', $data->get('stamp_record')->value);
        $fail->set('no_send_try', $data->get('no_send_try')->value);
        $fail->set('sender', $request->get('sender'));
        $fail->set('number', $data->get('number')->value);
        $fail->set('message', $data->get('message')->value);
        $fail->set('reason', $error_message);
        $fail->save();
        $data->delete();

        return ['error'=>-4012, 'message'=>$error_message];
    }

    /**
     * @todo check if this function is needed. Mostly mobile numbers will be delivered.
     * Transfer data which is older than 5 days and failed more than 100 times.
     *
     */
    private static function moveOldFailData()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime() {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangedTime() {
        return $this->get('changed')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner() {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId() {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid) {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account) {
        $this->set('user_id', $account->id());
        return $this;
    }


    public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the  entity.'))
            ->setReadOnly(TRUE);

        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the  entity.'))
            ->setReadOnly(TRUE);


        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Drupal User ID'))
            ->setDescription(t('The Drupal User ID who owns the forum.'))
            ->setSetting('target_type', 'user');


        $fields['langcode'] = BaseFieldDefinition::create('language')
            ->setLabel(t('Language code'))
            ->setDescription(t('The language code of entity.'));


        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        $fields['number'] = BaseFieldDefinition::create('string')
            ->setLabel(t('IP'))
            ->setDescription(t('Number of the message'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 32,
            ));



        $fields['priority'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Priority'))
            ->setDescription(t('Priority to send SMS'))
            ->setDefaultValue(0);


        $fields['stamp_record'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Stamp Record'))
            ->setDescription(t('the stamp of the time that this SMS was received to be scheduled'))
            ->setDefaultValue(0);


        $fields['stamp_next_send'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Stamp Send Try'))
            ->setDescription(t('the stamp of the time that the gate tried last.'))
            ->setDefaultValue(0);

        $fields['no_send_try'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('No Send Try'))
            ->setDescription(t('The number of send. It is the number of failure also.'))
            ->setDefaultValue(0);


        $fields['sender'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Sender'))
            ->setDescription(t('The mobile which sent this sms.'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 255,
            ));

        $fields['message'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Message'))
            ->setDescription(t('SMS Message'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 1024,
            ));

        return $fields;
    }
}

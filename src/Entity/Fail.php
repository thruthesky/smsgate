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
 *   id = "smsgate_fail",
 *   label = @Translation("SMSGate Fail entity"),
 *   base_table = "smsgate_fail",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "number",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Fail extends ContentEntityBase {

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

        $fields['stamp_record'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Stamp Record'))
            ->setDescription(t('the stamp of the time that this SMS was received to be scheduled'))
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


        $fields['reason'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Reason'))
            ->setDescription(t('Reason why it failed.'))
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

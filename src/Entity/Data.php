<?php
namespace Drupal\smsgate\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the CategoryLog entity.
 *
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

        $fields['stamp_sent'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Sent'))
            ->setDescription(t('the stamp of the time of SMS sent for the message'))
            ->setDefaultValue(0);

        $fields['sent_result'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Result'))
            ->setDescription(t('Result of SMS sending. Y for success, N for failure, Empty for not sending yet.'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 1,
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

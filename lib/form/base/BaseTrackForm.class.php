<?php

/**
 * Track form base class.
 *
 * @method Track getObject() Returns the current form's model object
 *
 * @package    peoplesradio
 * @subpackage form
 * @author     Your name here
 */
abstract class BaseTrackForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'filename'        => new sfWidgetFormInputText(),
      'name'            => new sfWidgetFormInputText(),
      'artist'          => new sfWidgetFormInputText(),
      'time'            => new sfWidgetFormInputText(),
      'genre'           => new sfWidgetFormInputText(),
      'cover'           => new sfWidgetFormInputText(),
      'community_list'  => new sfWidgetFormPropelChoice(array('multiple' => true, 'model' => 'Channel')),
      'track_vote_list' => new sfWidgetFormPropelChoice(array('multiple' => true, 'model' => 'Channel')),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->getId()), 'empty_value' => $this->getObject()->getId(), 'required' => false)),
      'filename'        => new sfValidatorString(array('max_length' => 255)),
      'name'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'artist'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'time'            => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'genre'           => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'cover'           => new sfValidatorString(array('max_length' => 300, 'required' => false)),
      'community_list'  => new sfValidatorPropelChoice(array('multiple' => true, 'model' => 'Channel', 'required' => false)),
      'track_vote_list' => new sfValidatorPropelChoice(array('multiple' => true, 'model' => 'Channel', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('track[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Track';
  }


  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['community_list']))
    {
      $values = array();
      foreach ($this->object->getCommunitys() as $obj)
      {
        $values[] = $obj->getChannelId();
      }

      $this->setDefault('community_list', $values);
    }

    if (isset($this->widgetSchema['track_vote_list']))
    {
      $values = array();
      foreach ($this->object->getTrackVotes() as $obj)
      {
        $values[] = $obj->getChannelId();
      }

      $this->setDefault('track_vote_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveCommunityList($con);
    $this->saveTrackVoteList($con);
  }

  public function saveCommunityList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['community_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(CommunityPeer::TRACK_ID, $this->object->getPrimaryKey());
    CommunityPeer::doDelete($c, $con);

    $values = $this->getValue('community_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new Community();
        $obj->setTrackId($this->object->getPrimaryKey());
        $obj->setChannelId($value);
        $obj->save();
      }
    }
  }

  public function saveTrackVoteList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['track_vote_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(TrackVotePeer::TRACK_ID, $this->object->getPrimaryKey());
    TrackVotePeer::doDelete($c, $con);

    $values = $this->getValue('track_vote_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new TrackVote();
        $obj->setTrackId($this->object->getPrimaryKey());
        $obj->setChannelId($value);
        $obj->save();
      }
    }
  }

}

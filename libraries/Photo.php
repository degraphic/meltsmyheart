<?php
class Photo
{
  public $id, $internalId, $thumbUrl, $mediumUrl, $originalUrl, $dateCreated, $dateTaken, $title;
  public function __construct($id, $internalId, $thumbUrl, $mediumUrl, $originalUrl, $dateCreated, $dateTaken, $title)
  {
    $this->id = $id;
    $this->internalId = $internalId;
    $this->thumbUrl = $thumbUrl;
    $this->mediumUrl = $mediumUrl;
    $this->originalUrl = $originalUrl;
    $this->dateCreated = $dateCreated;
    $this->dateTaken = $dateTaken;
    $this->title = $title;
  }

  public static function add($userId, $key, $value)
  {
    $id = getDatabase()->execute('INSERT INTO photo(p_u_id, p_key, p_meta, p_dateCreated) VALUES(:userId, :key, :meta, NOW()) ON DUPLICATE KEY UPDATE p_meta=:meta',
      array(':userId' => $userId, ':key' => $key, ':meta' => json_encode($value)));
    if(!$id)
    {
      $photo = self::one($userId, $key);
      $id = $photo['p_id'];
    }
    return $id;
  }

  public static function all($userId, $key)
  {
    $retval = getDatabase()->all('SELECT * FROM photo WHERE p_u_id=:userId AND p_key=:key', 
      array(':userId' => $userId, ':key' => $key));
    foreach($retval as $key => $value)
      $retval[$key]['p_meta'] = json_decode($value['p_meta'], 1);

    return $retval;
  }

  public static function deleteOne($userId, $id)
  {

  }

  public static function deleteAll($userId, $key)
  {

  }

  public static function one($userId, $key)
  {
    $retval = getDatabase()->one('SELECT * FROM photo WHERE p_u_id=:userId AND p_key=:key', 
      array(':userId' => $userId, ':key' => $key));
    $retval['p_meta'] = json_decode($retval['p_meta'], 1);
    return $retval;
  }

}
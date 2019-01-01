<?php
/* 设置电梯 */
namespace yxmingy\YElevator\manager;
use pocketmine\utils\Config;
use yxmingy\YElevator\Main;
use pocketmine\event\Listener;
use pocketmine\level\Level;

class Manager implements Listener
{
  protected static $conf,$pos;
  public function __construct()
  {
    @mkdir(self::getDataPath());
    self::$pos = new Config(Main::getInstance()->getDataFolder()."/positions.yml",Config::YAML,array());
  }
  public static function getDataPath():string
  {
    return (Main::getInstance()->getDataFolder()."/elevators/");
  }
  /* Elevator Config Structure
  [
    name=>(name)
    type=>Type::(name)
    points=>
    [
      (name)=>x:y:z:level,
      ......
    ]
  ],
  ......
  */
  public static function getElevatorbyPosition(int $x,int $y,int $z,Level $l):?array
  {
    $pos = $x.$y.$z.$l->getName();
    if(!self::$pos->exises($pos)) return null;
    return self::getElevator(self::$pos->get($pos));
  }
  public static function getElevator(string $name):?array
  {
    $ele = new Config(self::getDataPath().$name.".yml",Config::YAML,array());
    return empty($ele->getAll()) ? null : $ele->getAll();
  }
  public static function setElevator(string $name,array $data):void
  {
    $ele = new Config(self::getDataPath().$name.".yml",Config::YAML,array());
    $ele->setAll($data);
    foreach($ele->get('points') as $pos)
    {
      self::$pos->set($pos,$name);
    }
  }
  public static function addElevator(string $name,int $type,array $points):void
  {
    self::setElevator($name,array(
    "name"=>$name,
    "type"=>$type,
    "points"=>$points
    ));
  }
  public static function removePoint(int $x,int $y,int $z,Level $l):void
  {
  }
  public static function removeElevator($name)
  {
  }
}
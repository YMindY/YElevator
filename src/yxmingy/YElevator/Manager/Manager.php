<?php
/* 设置电梯 */
namespace yxmingy\YElevator\Manager;
use pocketmine\utils\Config;
use yxmingy\YElevator\Main;
use pocketmine\event\Listener;

class Manager implements Listener
{
  protected static $conf;
  public function __construct()
  {
    @mkdir(self::getDataPath());
    //self::$conf = new Config(Main::getInstance()->getDataFolder()."/Config.yml",Config::YAML,array());
  }
  public static function getDataPath():string
  {
    return (Main::getInstance()->getDataFolder()."/elevators/");
  }
  public static function getElevator(string $name):?array
  {
    return new Config(self::getDataPath().$name.".yml",Config::YAML);
  }
  public static function setElevator(string $name,array $data):void
  {
    
  }
}
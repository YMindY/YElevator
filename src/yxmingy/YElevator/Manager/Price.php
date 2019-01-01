<?php
/* 分管价格 */
namespace yxmingy\YElevator\Manager;
use pocketmine\utils\Config;
use yxmingy\YElevator\Main;

class Price
{
  protected static $conf;
  public function __construct()
  {
    self::$conf = new Config(Main::getInstance()->getDataFolder()."/Config.yml",Config::YAML,array("楼层电梯价格"=>50,"格数电梯价格"=>75,"时空电梯价格"=>200));
  }
  public static function getPrice(string $type):?int
  {
    return self::$conf->get($type);
  }
}
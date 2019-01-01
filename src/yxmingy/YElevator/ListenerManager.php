<?php
/* 注册监听器 */
namespace yxmingy\YElevator;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use yxmingy\YElevator\manager\Manager;
abstract class ListenerManager extends PluginBase
{
  use starter\Starter;
  protected static $namelist = 
  [
    'manager'=>Manager::class,
  ];
  protected static $listeners = [];
  final protected static function register(Listener $listener):void
  {
    self::$instance->getServer()->getPluginManager()->registerEvents($listener,self::$instance);
  }
  final protected static function registerListeners():void
  {
    foreach(self::$namelist as $name)
    {
      self::register(self::$listeners[$name]=new $name());
    }
  }
  public static function getListener(string $name):Listener
  {
    return self::$listeners[$name];
  }
  protected function assignInstance()
  {
    self::$instance=$this;
  }
}
?>
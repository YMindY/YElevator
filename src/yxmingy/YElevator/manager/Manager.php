<?php
/* 管理电梯数据,设置电梯 */
namespace yxmingy\YElevator\manager;
use pocketmine\utils\Config;
use yxmingy\YElevator\Main;
use pocketmine\level\Level;
use pocketmine\event\Listener;
use yxmingy\YElevator\starter\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;

class Manager implements Listener,CommandExecutor
{
  protected static $conf,$pos = null;
  private $setters = array();
  /*
  [
    playername=>
    [
      x=>(num)
      z=>(num)
      base_y=>(num)
      now_y=>(num)
      floor=>(num)
      status=>(hign/low)
      ele=>(array)
    ],
    ......
  ]
  */
  public function __construct()
  {
    @mkdir(self::getDataPath());
    if(self::$pos==null) self::$pos = new Config(Main::getInstance()->getDataFolder()."/positions.yml",Config::YAML,array());
  }
  public function handleCommand(array $args,CommandSender $sender):bool
  {
    if(!isset($args[0])){
      $sender->sendMessage("用法:/dt set (add/remove)");
      return true;
    }
    $name = $sender->getName();
    switch($args[0])
    {
    case "add":
      if(!isset($args[1]) || !isset($args[2]))
      {
        $sender->sendMessage("用法:/dt set add [电梯类型] [电梯名字]\n电梯类型有floor(楼层电梯)，block(格数电梯)，tp(时空电梯)");
        return true;
      }
      if(($ele=self::getElevator($args[2]))!==null)
      {
        $sender->sendMessage("这个名字被人抢了，换一个吧!\nps:主人是{$ele['owner']}，你可以找他py");
        return true;
      }
      if(!$sender instanceof \pocketmine\Player)
      {
        $sender->sendMessage("控制台滚");
        return true;
      }
      $types = array(
      "floor"=>Type::FLOOR,
      "block"=>Type::BLOCK,
      "tp"=>Type::TELEPORT
      );
      if(!in_array($args[1],array_keys($types)))
      {
        $sender->sendMessage("没你这个电梯类型吧。。检查一下");
        return true;
      }
      $ele = array(
      'master'=>$name,
      'type'=>$types[$args[1]],
      'points'=>[]
      );
      switch($args[1])
      {
      case "floor":
        $sender->sendMessage("开始设置层数电梯!点一个告示牌设置为1楼\n取消设置使用/dt set cancel");
        $this->setters[$name] = array(
        'status'=>'high',
        'ele'=>$ele
        );
      break;
      default:
        $sender->sendMessage("你是不是打错指令了?");
      break;
      }
    break;
    case "low":
    
    break;
    case "ok":
    
    break;
    case "cancel":
    
    break;
    case "remove":
    
    break;
    default:
      $sender->sendMessage("用法:/dt set (add/remove)");
      return true;
    }
  }
  public function playerBlockTouch(PlayerInteractEvent $event){
    if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68)
    {
      $player = $event->getPlayer();
      $name = $player->getName();
      $block = $event->getBlock();
      $x = (int)$block->getX();
      $y = (int)$block->getY();
      $z = (int)$block->getZ();
      //这个人正在设置
      if(in_array($name,array_keys($this->setters)))
      {
        $setter = &$this->setters[$name];
        if(!isset($setter['base_y']))
        {
          $setter = array_merge($setter,array(
            'x'=>$x,
            'z'=>$z,
            'base_y'=>$y,
            'now_y'=>$y,
            'floor'=>1
          ));
        }elseif($setter['x']==$x && $setter['z']==$z){
          if($setter['status']=='high')
          {
            if($y <= $setter['now_y'])
            {
              $player->sendMessage("有没有搞错！？".($setter['floor']+1)."楼没有{$setter['floor']}楼高？");
              return;
            }
            $setter = array_merge($setter,array(
              'now_y'=>$y,
              'floor'=>($setter['floor']+1)
            ));
          }elseif($setter['status']=='low')
          {
            if($y >= $setter['base_y'] || $y >= $setter['now_y'])
            {
              $player->sendMessage("有没有搞错！？".($setter['floor']+1)."楼没有{$setter['floor']}楼高？");
              return;
            }
            $setter = array_merge($setter,array(
              'now_y'=>$y,
              'floor'=>($setter['floor']+1)
            ));
          }
          
        }
        $floor = $this->setters[$name]['floor'];
        $sender->sendMessage("设置成功，当前y轴高度为{$y}，层数为{$floor}请点击同一纵轴此处之上的木牌设置".($floor+1)."楼\n若要开始设置负数层则使用指令/dt low\n完成设置使用指令 /dt ok");
      }
    }
  }
  public static function getDataPath():string
  {
    return (Main::getInstance()->getDataFolder()."/elevators/");
  }
  /* Elevator Config Structure
  [
    master=>(name)
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
    if(!self::isPoint($x,$y,$z,$l)) return null;
    return self::getElevator(self::$pos->get($pos));
  }
  public static function getElevator(string $name):?array
  {
    $ele = new Config(self::getDataPath()."{$name}.yml",Config::YAML,array());
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
    $ele->save();
  }
  public static function addElevator(string $owner,string $name,int $type,array $points):void
  {
    self::setElevator($name,array(
    "owner"=>$owner,
    "type"=>$type,
    "points"=>$points
    ));
  }
  public static function isPoint(int $x,int $y,int $z,Level $l):bool
  {
    $pos = $x.$y.$z.$l->getName();
    //return in_array($pos,array_keys(self::$pos->getAll()));
    return self::$pos->exists($pos);
  }
  public static function removePoint(int $x,int $y,int $z,Level $l):void
  {
    if(!self::isPoint($x,$y,$z,$l)) return;
    $pos = $x.$y.$z.$l->getName();
    $name = self::$pos->get($pos);
    self::$pos->remove($pos);
    self::$pos->save();
    $ele = self::getElevator($name);
    unset($ele['points'][$pos]);
    self::setElevator($name,$ele);
  }
  public static function removeElevator($name):void
  {
    if(($ele=self::getElevator($name))===null) return;
    foreach($ele['points'] as $pos)
    {
      self::$pos->remove($pos);
    }
    unlink(self::getDataPath().$name.".yml");
  }
}
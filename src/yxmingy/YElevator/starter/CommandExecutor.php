<?php
namespace yxmingy\YElevator\starter;
use pocketmine\command\CommandSender;
interface CommandExecutor
{
  public function handleCommand(array $args,CommandSender $sender):bool;
}
<?php

declare(strict_types=1);

namespace cosmicpe\blockdatatest;

use cosmicpe\blockdata\BlockDataFactory;
use cosmicpe\blockdata\world\BlockDataWorldManager;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

final class BlockDataTestPlugin extends PluginBase implements Listener{

	private const OBSIDIAN_DURABILITY = "cosmicpe:durable";

	/** @var BlockDataWorldManager */
	private $manager;

	protected function onEnable() : void{
		$this->setupBlockDataVirion();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function setupBlockDataVirion() : void{
		BlockDataFactory::register(self::OBSIDIAN_DURABILITY, DurableBlockData::class);
		$this->manager = BlockDataWorldManager::create($this);
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $event->getItem()->getId() === ItemIds::POTATO){
			$block = $event->getBlock();
			if($block->getId() === BlockLegacyIds::GLASS){
				$pos = $block->getPos();

				/** @var DurableBlockData $data */
				$data = $this->manager->get($pos->getWorld())->getBlockDataAt($pos->x, $pos->y, $pos->z) ?? new DurableBlockData();

				$event->getPlayer()->sendMessage(TextFormat::LIGHT_PURPLE . "Durability of this block is: " . $data->getDurability());
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 * @priority HIGH
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		$block = $event->getBlock();
		if($block->getId() === BlockLegacyIds::GLASS){
			$pos = $block->getPos();
			$this->manager->get($pos->getWorld())->setBlockDataAt($pos->x, $pos->y, $pos->z, new DurableBlockData());
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGH
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		if($block->getId() === BlockLegacyIds::GLASS){
			$pos = $block->getPos();
			$world = $this->manager->get($pos->getWorld());

			/** @var DurableBlockData $data */
			$data = $world->getBlockDataAt($pos->x, $pos->y, $pos->z);
			if(!($data instanceof DurableBlockData)){
				$data = new DurableBlockData();
			}

			if($data->decreaseDurability()){
				$event->setCancelled();
			}

			$world->setBlockDataAt($pos->x, $pos->y, $pos->z, $data);
		}
	}
}